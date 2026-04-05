<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\Subscription;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class GenerateDailyReport extends Command
{
    protected $signature = 'report:daily 
                            {--email= : Send report to specific email}
                            {--save : Save report to storage}';
    
    protected $description = 'Generate daily system report';

    public function handle()
    {
        $this->info('Generating daily report...');
        
        $report = [
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'date' => now()->subDay()->format('Y-m-d'),
            'statistics' => $this->getStatistics(),
            'subscriptions' => $this->getSubscriptionReport(),
            'recent_activity' => $this->getRecentActivity(),
        ];
        
        // Display report
        $this->displayReport($report);
        
        // Save to file if requested
        if ($this->option('save')) {
            $this->saveReport($report);
        }
        
        // Send email if requested
        if ($this->option('email')) {
            $this->sendReport($report, $this->option('email'));
        }
        
        $this->info('Report generated successfully!');
        
        return Command::SUCCESS;
    }
    
    private function getStatistics()
    {
        return [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('status', 'active')->count(),
            'suspended_tenants' => Tenant::where('status', 'suspended')->count(),
            'expired_tenants' => Tenant::where('status', 'expired')->count(),
            'total_subscriptions' => Subscription::count(),
            'active_subscriptions' => Subscription::where('status', 'active')->count(),
            'revenue_today' => Payment::whereDate('created_at', today())->sum('amount'),
            'revenue_month' => Payment::whereMonth('created_at', now()->month)->sum('amount'),
            'revenue_year' => Payment::whereYear('created_at', now()->year)->sum('amount'),
        ];
    }
    
    private function getSubscriptionReport()
    {
        $expiringSoon = Subscription::with('tenant')
            ->where('status', 'active')
            ->where('ends_at', '<=', now()->addDays(7))
            ->where('ends_at', '>=', now())
            ->get();
        
        $expiredToday = Subscription::with('tenant')
            ->where('status', 'active')
            ->whereDate('ends_at', today())
            ->get();
        
        return [
            'expiring_soon_count' => $expiringSoon->count(),
            'expiring_soon' => $expiringSoon->map(function($sub) {
                return [
                    'tenant' => $sub->tenant->name,
                    'plan' => $sub->plan->name,
                    'ends_at' => $sub->ends_at->format('Y-m-d'),
                    'days_left' => now()->diffInDays($sub->ends_at)
                ];
            }),
            'expired_today_count' => $expiredToday->count(),
            'expired_today' => $expiredToday->map(function($sub) {
                return [
                    'tenant' => $sub->tenant->name,
                    'plan' => $sub->plan->name,
                ];
            }),
        ];
    }
    
    private function getRecentActivity()
    {
        return [
            'new_tenants_last_7_days' => Tenant::where('created_at', '>=', now()->subDays(7))->count(),
            'new_subscriptions_last_7_days' => Subscription::where('created_at', '>=', now()->subDays(7))->count(),
            'payments_last_7_days' => Payment::where('created_at', '>=', now()->subDays(7))->count(),
            'payments_last_7_days_total' => Payment::where('created_at', '>=', now()->subDays(7))->sum('amount'),
        ];
    }
    
    private function displayReport($report)
    {
        $this->line('');
        $this->line('╔════════════════════════════════════════════════════════════╗');
        $this->line('║              ACADCLEAR DAILY SYSTEM REPORT                ║');
        $this->line('╚════════════════════════════════════════════════════════════╝');
        $this->line('');
        $this->line("Generated: {$report['generated_at']}");
        $this->line("Report Date: {$report['date']}");
        $this->line('');
        
        $this->line('📊 STATISTICS');
        $this->line('─────────────────────────────────────────────────────────────');
        $this->line("Total Universities:    {$report['statistics']['total_tenants']}");
        $this->line("Active:                {$report['statistics']['active_tenants']}");
        $this->line("Suspended:             {$report['statistics']['suspended_tenants']}");
        $this->line("Expired:               {$report['statistics']['expired_tenants']}");
        $this->line('');
        $this->line("Total Subscriptions:   {$report['statistics']['total_subscriptions']}");
        $this->line("Active Subscriptions:  {$report['statistics']['active_subscriptions']}");
        $this->line('');
        $this->line("💰 REVENUE");
        $this->line('─────────────────────────────────────────────────────────────');
        $this->line("Today:                 ₱" . number_format($report['statistics']['revenue_today'], 2));
        $this->line("This Month:            ₱" . number_format($report['statistics']['revenue_month'], 2));
        $this->line("This Year:             ₱" . number_format($report['statistics']['revenue_year'], 2));
        $this->line('');
        
        $this->line('⚠️  EXPIRING SOON (Next 7 Days)');
        $this->line('─────────────────────────────────────────────────────────────');
        if ($report['subscriptions']['expiring_soon_count'] > 0) {
            foreach ($report['subscriptions']['expiring_soon'] as $item) {
                $this->line("  • {$item['tenant']} - {$item['plan']} (expires in {$item['days_left']} days)");
            }
        } else {
            $this->line("  No subscriptions expiring soon.");
        }
        $this->line('');
        
        $this->line('📈 RECENT ACTIVITY (Last 7 Days)');
        $this->line('─────────────────────────────────────────────────────────────');
        $this->line("New Universities:      {$report['recent_activity']['new_tenants_last_7_days']}");
        $this->line("New Subscriptions:     {$report['recent_activity']['new_subscriptions_last_7_days']}");
        $this->line("Payments:              {$report['recent_activity']['payments_last_7_days']}");
        $this->line("Payment Total:         ₱" . number_format($report['recent_activity']['payments_last_7_days_total'], 2));
        $this->line('');
        $this->line('════════════════════════════════════════════════════════════');
    }
    
    private function saveReport($report)
    {
        $filename = storage_path("reports/daily_report_" . now()->format('Y-m-d') . ".json");
        
        if (!File::exists(storage_path('reports'))) {
            File::makeDirectory(storage_path('reports'), 0755, true);
        }
        
        File::put($filename, json_encode($report, JSON_PRETTY_PRINT));
        $this->info("Report saved to: {$filename}");
    }
    
    private function sendReport($report, $email)
    {
        // You can implement email sending here
        $this->info("Report would be sent to: {$email}");
        $this->warn("Email functionality needs to be configured first.");
    }
}