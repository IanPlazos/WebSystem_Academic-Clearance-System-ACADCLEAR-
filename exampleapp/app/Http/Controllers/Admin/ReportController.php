<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clearance;
use App\Models\College;
use App\Models\Department;
use App\Services\TenantService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private readonly TenantService $tenantService)
    {
    }

    /**
     * Display main reports dashboard
     */
    public function index(Request $request)
    {
        $colleges = College::with('departments')->get();
        $selectedCollege = $request->get('college_id');
        $dateFrom = $request->get('date_from', now()->subMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        $stats = $this->getStatistics($selectedCollege, $dateFrom, $dateTo);
        $chartData = $this->getChartData($selectedCollege, $dateFrom, $dateTo);
        $departmentPerformance = $this->getDepartmentPerformance($selectedCollege, $dateFrom, $dateTo);

        return view('admin.reports.index', compact(
            'colleges',
            'selectedCollege',
            'dateFrom',
            'dateTo',
            'stats',
            'chartData',
            'departmentPerformance'
        ));
    }

    /**
     * Export reports as PDF
     */
    public function exportPdf(Request $request)
    {
        $selectedCollege = $request->get('college_id');
        $dateFrom = $request->get('date_from', now()->subMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        $tenantName = $this->resolveTenantName();

        $stats = $this->getStatistics($selectedCollege, $dateFrom, $dateTo);
        $departmentPerformance = $this->getDepartmentPerformance($selectedCollege, $dateFrom, $dateTo);
        $college = $selectedCollege ? College::find($selectedCollege) : null;

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('admin.reports.pdf', compact(
            'stats',
            'departmentPerformance',
            'college',
            'dateFrom',
            'dateTo',
            'tenantName'
        ));

        return $pdf->download('clearance-report-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export reports as Excel/CSV
     */
    public function exportCsv(Request $request)
    {
        $selectedCollege = $request->get('college_id');
        $dateFrom = $request->get('date_from', now()->subMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        $tenantName = $this->resolveTenantName();

        $filename = 'clearance-report-' . now()->format('Y-m-d') . '.csv';
        $handle = fopen('php://output', 'w');

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        fputcsv($handle, ['Institution:', $tenantName]);
        fputcsv($handle, ['Report Generated:', now()->format('F d, Y H:i:s')]);
        fputcsv($handle, ['Date Range:', $dateFrom, 'to', $dateTo]);
        if ($selectedCollege) {
            $college = College::find($selectedCollege);
            fputcsv($handle, ['College:', $college?->name ?? 'Unknown College']);
        }
        fputcsv($handle, []);

        $stats = $this->getStatistics($selectedCollege, $dateFrom, $dateTo);
        fputcsv($handle, ['OVERALL STATISTICS']);
        fputcsv($handle, ['Total Clearances', $stats['total_clearances']]);
        fputcsv($handle, ['Approved', $stats['approved']]);
        fputcsv($handle, ['Pending', $stats['pending']]);
        fputcsv($handle, ['Rejected', $stats['rejected']]);
        fputcsv($handle, ['Completion Rate', $stats['completion_rate'] . '%']);
        fputcsv($handle, []);

        $departmentPerformance = $this->getDepartmentPerformance($selectedCollege, $dateFrom, $dateTo);
        fputcsv($handle, ['DEPARTMENT PERFORMANCE']);
        fputcsv($handle, ['Department', 'Total', 'Approved', 'Pending', 'Rejected', 'Rate']);

        foreach ($departmentPerformance as $dept) {
            fputcsv($handle, [
                $dept['name'],
                $dept['total'],
                $dept['approved'],
                $dept['pending'],
                $dept['rejected'],
                $dept['rate'] . '%',
            ]);
        }

        fclose($handle);
        exit;
    }

    /**
     * Get detailed clearance data for API
     */
    public function getData(Request $request)
    {
        $selectedCollege = $request->get('college_id');
        $dateFrom = $request->get('date_from', now()->subMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        $query = Clearance::with(['student', 'department.college'])
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59']);

        if ($selectedCollege) {
            $query->whereHas('department', function ($q) use ($selectedCollege) {
                $q->where('college_id', $selectedCollege);
            });
        }

        $clearances = $query->get();

        return response()->json([
            'total' => $clearances->count(),
            'approved' => $clearances->where('status', 'approved')->count(),
            'pending' => $clearances->where('status', 'pending')->count(),
            'rejected' => $clearances->where('status', 'rejected')->count(),
            'data' => $clearances->map(function ($c) {
                return [
                    'id' => $c->id,
                    'student' => $c->student->name,
                    'department' => $c->department->name,
                    'college' => $c->department->college->name,
                    'status' => $c->status,
                    'remarks' => $c->remarks,
                    'created_at' => $c->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $c->updated_at->format('Y-m-d H:i:s'),
                ];
            }),
        ]);
    }

    /**
     * Get statistics based on filters
     */
    private function getStatistics($collegeId = null, $dateFrom = null, $dateTo = null)
    {
        $query = Clearance::query();

        if ($dateFrom && $dateTo) {
            $query->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59']);
        }

        if ($collegeId) {
            $query->whereHas('department', function ($q) use ($collegeId) {
                $q->where('college_id', $collegeId);
            });
        }

        $total = $query->count();
        $approved = (clone $query)->where('status', 'approved')->count();
        $pending = (clone $query)->where('status', 'pending')->count();
        $rejected = (clone $query)->where('status', 'rejected')->count();

        return [
            'total_clearances' => $total,
            'approved' => $approved,
            'pending' => $pending,
            'rejected' => $rejected,
            'completion_rate' => $total > 0 ? round(($approved / $total) * 100, 2) : 0,
            'students_served' => (clone $query)->distinct('student_id')->count('student_id'),
        ];
    }

    /**
     * Get chart data
     */
    private function getChartData($collegeId = null, $dateFrom = null, $dateTo = null)
    {
        $statusQuery = Clearance::query();
        if ($dateFrom && $dateTo) {
            $statusQuery->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59']);
        }
        if ($collegeId) {
            $statusQuery->whereHas('department', function ($q) use ($collegeId) {
                $q->where('college_id', $collegeId);
            });
        }

        $statusData = [
            'labels' => ['Approved', 'Pending', 'Rejected'],
            'data' => [
                (clone $statusQuery)->where('status', 'approved')->count(),
                (clone $statusQuery)->where('status', 'pending')->count(),
                (clone $statusQuery)->where('status', 'rejected')->count(),
            ],
            'colors' => ['#28a745', '#ffc107', '#dc3545'],
        ];

        $trendQuery = Clearance::query();
        if ($collegeId) {
            $trendQuery->whereHas('department', function ($q) use ($collegeId) {
                $q->where('college_id', $collegeId);
            });
        }

        $trendData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $count = (clone $trendQuery)
                ->whereDate('created_at', $date)
                ->count();
            $trendData['labels'][] = now()->subDays($i)->format('D');
            $trendData['data'][] = $count;
        }

        $collegeDistribution = [];
        if (!$collegeId) {
            $colleges = College::withCount(['departments' => function ($q) use ($dateFrom, $dateTo) {
                $q->whereHas('clearances', function ($cq) use ($dateFrom, $dateTo) {
                    if ($dateFrom && $dateTo) {
                        $cq->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59']);
                    }
                });
            }])->get();

            foreach ($colleges as $college) {
                $collegeDistribution['labels'][] = $college->name;
                $collegeDistribution['data'][] = $college->departments_count;
            }
        }

        return [
            'status' => $statusData,
            'trend' => $trendData,
            'college' => $collegeDistribution,
        ];
    }

    /**
     * Get department performance metrics
     */
    private function getDepartmentPerformance($collegeId = null, $dateFrom = null, $dateTo = null)
    {
        $query = Department::with('college');

        if ($collegeId) {
            $query->where('college_id', $collegeId);
        }

        $departments = $query->get();
        $performance = [];

        foreach ($departments as $dept) {
            $clearanceQuery = Clearance::where('department_id', $dept->id);

            if ($dateFrom && $dateTo) {
                $clearanceQuery->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59']);
            }

            $total = (clone $clearanceQuery)->count();
            $approved = (clone $clearanceQuery)->where('status', 'approved')->count();
            $pending = (clone $clearanceQuery)->where('status', 'pending')->count();
            $rejected = (clone $clearanceQuery)->where('status', 'rejected')->count();

            $performance[] = [
                'id' => $dept->id,
                'name' => $dept->name,
                'college' => $dept->college->name,
                'total' => $total,
                'approved' => $approved,
                'pending' => $pending,
                'rejected' => $rejected,
                'rate' => $total > 0 ? round(($approved / $total) * 100, 2) : 0,
                'avg_response_time' => $this->calculateAvgResponseTime($dept->id, $dateFrom, $dateTo),
            ];
        }

        usort($performance, function ($a, $b) {
            return $b['rate'] <=> $a['rate'];
        });

        return $performance;
    }

    /**
     * Calculate average response time for a department
     */
    private function calculateAvgResponseTime($departmentId, $dateFrom, $dateTo)
    {
        $clearances = Clearance::where('department_id', $departmentId)
            ->whereIn('status', ['approved', 'rejected'])
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->get();

        if ($clearances->isEmpty()) {
            return 'N/A';
        }

        $totalHours = 0;
        foreach ($clearances as $clearance) {
            $hours = $clearance->created_at->diffInHours($clearance->updated_at);
            $totalHours += $hours;
        }

        $avgHours = round($totalHours / $clearances->count(), 1);

        if ($avgHours < 24) {
            return $avgHours . ' hours';
        }

        return round($avgHours / 24, 1) . ' days';
    }

    private function resolveTenantName(): string
    {
        $tenantDetails = $this->tenantService->getTenantDetails();
        $tenantName = is_array($tenantDetails) ? trim((string) ($tenantDetails['name'] ?? '')) : '';

        if ($tenantName !== '') {
            return $tenantName;
        }

        return (string) config('app.name', 'AcadClear');
    }
}