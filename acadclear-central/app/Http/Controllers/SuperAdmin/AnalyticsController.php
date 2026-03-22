<?php
namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Subscription;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $dateRange = $request->get('range', 'month');
        
        $stats = $this->getStats($dateRange);
        $revenueData = $this->getRevenueData($dateRange);
        $tenantGrowth = $this->getTenantGrowth();
        $subscriptionDistribution = $this->getSubscriptionDistribution();
        $paymentMethods = $this->getPaymentMethods();

        return view('super-admin.analytics.index', compact(
            'stats', 'revenueData', 'tenantGrowth', 
            'subscriptionDistribution', 'paymentMethods', 'dateRange'
        ));
    }

    private function getStats($range)
    {
        $dateCondition = $this->getDateCondition($range);

        return [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('status', 'active')->count(),
            'total_revenue' => Payment::where('status', 'completed')->sum('amount'),
            'monthly_revenue' => Payment::where('status', 'completed')
                ->whereMonth('payment_date', now()->month)
                ->sum('amount'),
            'active_subscriptions' => Subscription::where('status', 'active')->count(),
            'renewal_rate' => $this->calculateRenewalRate(),
            'average_subscription_value' => Subscription::avg('amount_paid') ?? 0,
            'churn_rate' => $this->calculateChurnRate(),
        ];
    }

    private function getRevenueData($range)
    {
        $query = Payment::where('status', 'completed');
        $driver = DB::connection()->getDriverName();
        $dateExpression = "DATE(payment_date)";
        $format = 'Y-m-d';
        
        switch ($range) {
            case 'week':
                $query->where('payment_date', '>=', now()->subDays(7));
                $format = 'Y-m-d';
                $dateExpression = $driver === 'sqlite'
                    ? "strftime('%Y-%m-%d', payment_date)"
                    : "DATE(payment_date)";
                break;
            case 'month':
                $query->where('payment_date', '>=', now()->subDays(30));
                $format = 'Y-m-d';
                $dateExpression = $driver === 'sqlite'
                    ? "strftime('%Y-%m-%d', payment_date)"
                    : "DATE(payment_date)";
                break;
            case 'year':
                $query->whereYear('payment_date', now()->year);
                $format = 'Y-m';
                $dateExpression = $driver === 'sqlite'
                    ? "strftime('%Y-%m', payment_date)"
                    : "DATE_FORMAT(payment_date, '%Y-%m')";
                break;
            default:
                $query->where('payment_date', '>=', now()->subDays(30));
                $format = 'Y-m-d';
                $dateExpression = $driver === 'sqlite'
                    ? "strftime('%Y-%m-%d', payment_date)"
                    : "DATE(payment_date)";
        }

        $data = $query->selectRaw("{$dateExpression} as date, SUM(amount) as total")
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return [
            'labels' => $data->pluck('date')->map(function($date) use ($format) {
                if (empty($date)) {
                    return null;
                }

                try {
                    return \Carbon\Carbon::createFromFormat($format, (string) $date)->format($format);
                } catch (\Throwable $e) {
                    try {
                        return \Carbon\Carbon::parse((string) $date)->format($format);
                    } catch (\Throwable $e) {
                        return (string) $date;
                    }
                }
            })->values(),
            'data' => $data->pluck('total')->map(fn ($total) => (float) $total)->values(),
        ];
    }

    private function getTenantGrowth()
    {
        $monthlyGrowth = Tenant::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        return [
            'labels' => $monthlyGrowth->map(function($item) {
                return date('M Y', mktime(0, 0, 0, $item->month, 1, $item->year));
            }),
            'data' => $monthlyGrowth->pluck('count'),
        ];
    }

    private function getSubscriptionDistribution()
    {
        $distribution = Subscription::select(
                'plans.name as plan_name',
                DB::raw('COUNT(*) as count')
            )
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->where('subscriptions.status', 'active')
            ->groupBy('plans.name')
            ->get();

        return [
            'labels' => $distribution->pluck('plan_name'),
            'data' => $distribution->pluck('count'),
            'colors' => ['#4e73df', '#1cc88a', '#36b9cc'],
        ];
    }

    private function getPaymentMethods()
    {
        $methods = Payment::where('status', 'completed')
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->get();

        return [
            'labels' => $methods->pluck('payment_method')->map(function($method) {
                return strtoupper(str_replace('_', ' ', $method));
            }),
            'data' => $methods->pluck('total'),
            'counts' => $methods->pluck('count'),
        ];
    }

    private function calculateRenewalRate()
    {
        $expired = Subscription::where('status', 'expired')->count();
        $renewed = Subscription::whereNotNull('created_at')
            ->whereRaw('id IN (SELECT subscription_id FROM payments WHERE status = "completed")')
            ->count();

        if ($expired == 0) return 100;
        return round(($renewed / ($expired + $renewed)) * 100, 2);
    }

    private function calculateChurnRate()
    {
        $active = Subscription::where('status', 'active')->count();
        $expired = Subscription::where('status', 'expired')
            ->where('ends_at', '>=', now()->subMonths(3))
            ->count();

        if ($active == 0) return 0;
        return round(($expired / $active) * 100, 2);
    }

    private function getDateCondition($range)
    {
        switch ($range) {
            case 'week':
                return now()->subDays(7);
            case 'month':
                return now()->subDays(30);
            case 'year':
                return now()->subYear();
            default:
                return now()->subDays(30);
        }
    }

    public function export(Request $request)
    {
        $type = $request->get('type', 'csv');
        $range = $request->get('range', 'month');

        $stats = $this->getStats($range);
        $revenueData = $this->getRevenueData($range);
        
        if ($type == 'csv') {
            $filename = "analytics-report-" . now()->format('Y-m-d') . ".csv";
            $handle = fopen('php://output', 'w');
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            fputcsv($handle, ['AcadClear Analytics Report']);
            fputcsv($handle, ['Generated:', now()->format('F d, Y H:i:s')]);
            fputcsv($handle, []);
            
            fputcsv($handle, ['Key Metrics']);
            fputcsv($handle, ['Metric', 'Value']);
            fputcsv($handle, ['Total Universities', $stats['total_tenants']]);
            fputcsv($handle, ['Active Universities', $stats['active_tenants']]);
            fputcsv($handle, ['Total Revenue', '₱' . number_format($stats['total_revenue'], 2)]);
            fputcsv($handle, ['Monthly Revenue', '₱' . number_format($stats['monthly_revenue'], 2)]);
            fputcsv($handle, ['Active Subscriptions', $stats['active_subscriptions']]);
            fputcsv($handle, ['Renewal Rate', $stats['renewal_rate'] . '%']);
            fputcsv($handle, []);
            
            fputcsv($handle, ['Revenue Data']);
            fputcsv($handle, ['Date', 'Amount']);
            foreach ($revenueData['labels'] as $index => $label) {
                fputcsv($handle, [$label, '₱' . number_format($revenueData['data'][$index] ?? 0, 2)]);
            }
            
            fclose($handle);
            exit;
        }
        
        return response()->json(['stats' => $stats, 'revenue' => $revenueData]);
    }
}