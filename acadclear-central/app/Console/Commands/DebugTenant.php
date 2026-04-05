<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;

class DebugTenant extends Command
{
    protected $signature = 'debug:tenant {id}';
    protected $description = 'Debug tenant loading';

    public function handle()
    {
        $tenantId = $this->argument('id');
        $tenant = Tenant::find($tenantId);
        
        if (!$tenant) {
            $this->error("Tenant not found");
            return;
        }

        $this->info("Loading tenant {$tenantId}...");
        
        // Test the active subscription method
        $tenant->load(['subscriptions.plan']);
        $this->info("Loaded subscriptions.plan");
        
        $currentSubscription = $tenant->activeSubscription;
        $this->info("activeSubscription type: " . get_class($currentSubscription));
        $this->info("activeSubscription value: " . ($currentSubscription ? $currentSubscription->id : 'null'));
        
        if ($currentSubscription) {
            $this->info("Current subscription details:");
            $this->info("  ID: {$currentSubscription->id}");
            $this->info("  Plan ID: {$currentSubscription->plan_id}");
            $this->info("  Plan Name: {$currentSubscription->plan->name}");
            $this->info("  Status: {$currentSubscription->status}");
            $this->info("  Starts: {$currentSubscription->starts_at}");
            $this->info("  Ends: {$currentSubscription->ends_at}");
        } else {
            $this->warn("No active subscription found!");
            $this->info("All subscriptions:");
            foreach ($tenant->subscriptions as $sub) {
                $this->info("  - ID: {$sub->id} | Status: {$sub->status} | Starts: {$sub->starts_at} | Ends: {$sub->ends_at}");
            }
        }
        
        $this->info("\nTesting view rendering...");
        try {
            $subscriptionHistory = $tenant->subscriptions()->with('plan')->latest()->get();
            $this->info("Subscription history loaded: " . $subscriptionHistory->count() . " records");
            
            // Try rendering the view
            $view = view('super-admin.tenants.show', compact('tenant', 'currentSubscription', 'subscriptionHistory'));
            $this->info("View rendered successfully!");
        } catch (\Exception $e) {
            $this->error("View render error: " . $e->getMessage());
            $this->error($e->getFile() . ":" . $e->getLine());
        }
    }
}
