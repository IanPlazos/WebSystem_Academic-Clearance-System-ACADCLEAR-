<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckExpiredSubscriptions extends Command
{
    protected $signature = 'subscriptions:check-expiry 
                            {--dry-run : Run without making changes}
                            {--tenant= : Check specific tenant only}';
    
    protected $description = 'Check for expired subscriptions and suspend tenants';

    public function handle()
    {
        $this->info('=== Subscription Expiry Check ===');
        $this->info('Started at: ' . now()->format('Y-m-d H:i:s'));
        
        $isDryRun = $this->option('dry-run');
        $specificTenant = $this->option('tenant');
        
        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }
        
        // Get expired subscriptions
        $query = Subscription::with('tenant')
            ->where('status', 'active')
            ->where('ends_at', '<', now());
        
        if ($specificTenant) {
            $query->whereHas('tenant', function($q) use ($specificTenant) {
                $q->where('slug', $specificTenant)
                  ->orWhere('id', $specificTenant);
            });
        }
        
        $expiredSubscriptions = $query->get();
        
        if ($expiredSubscriptions->isEmpty()) {
            $this->info('No expired subscriptions found.');
            return Command::SUCCESS;
        }
        
        $this->info("Found {$expiredSubscriptions->count()} expired subscription(s).");
        
        $expiredCount = 0;
        $suspendedCount = 0;
        
        foreach ($expiredSubscriptions as $subscription) {
            $tenant = $subscription->tenant;
            
            $this->line('');
            $this->line("Processing: {$tenant->name}");
            $this->line("  Subscription ID: {$subscription->id}");
            $this->line("  Ended: {$subscription->ends_at->format('Y-m-d')}");
            
            if (!$isDryRun) {
                // Mark subscription as expired
                $subscription->update(['status' => 'expired']);
                $expiredCount++;
                $this->info("  ✓ Subscription marked as expired");
                
                // Only suspend if tenant is still active
                if ($tenant->status === 'active') {
                    $tenant->update([
                        'status' => 'expired',
                        'suspended_at' => now(),
                        'suspension_reason' => 'Subscription expired on ' . $subscription->ends_at->format('Y-m-d')
                    ]);
                    $suspendedCount++;
                    $this->info("  ✓ Tenant suspended");
                    
                    // Log the suspension
                    Log::info("Tenant suspended due to expired subscription", [
                        'tenant_id' => $tenant->id,
                        'tenant_name' => $tenant->name,
                        'subscription_id' => $subscription->id,
                        'expired_date' => $subscription->ends_at
                    ]);
                } else {
                    $this->line("  Tenant already has status: {$tenant->status}");
                }
            } else {
                $this->line("  [DRY RUN] Would expire subscription");
                $this->line("  [DRY RUN] Would suspend tenant");
            }
        }
        
        $this->line('');
        $this->info('=== Summary ===');
        $this->info("Expired subscriptions: {$expiredCount}");
        $this->info("Suspended tenants: {$suspendedCount}");
        
        if ($isDryRun) {
            $this->info("(Dry run - no actual changes made)");
        }
        
        $this->info('Completed at: ' . now()->format('Y-m-d H:i:s'));
        
        return Command::SUCCESS;
    }
}