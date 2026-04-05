<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant;

class CheckTenant extends Command
{
    protected $signature = 'check:tenant {id?}';
    protected $description = 'Check tenant details';

    public function handle()
    {
        if ($this->argument('id')) {
            $tenant = Tenant::find($this->argument('id'));
            if ($tenant) {
                $this->info("Tenant ID: " . $tenant->id);
                $this->info("Name: " . $tenant->name);
                $this->info("Slug: " . $tenant->slug);
                $this->info("Database: " . $tenant->database);
                $this->info("Status: " . $tenant->status);
                $this->info("Settings: " . json_encode($tenant->settings));
                
                $subs = $tenant->subscriptions()->with('plan')->get();
                $this->info("Subscriptions: " . $subs->count());
                foreach ($subs as $sub) {
                    $this->info("  - Plan: " . $sub->plan->name . " | Status: " . $sub->status . " | Ends: " . $sub->ends_at);
                }
            } else {
                $this->error("Tenant not found");
            }
        } else {
            // List all tenants
            $tenants = Tenant::all();
            $this->info("Total tenants: " . $tenants->count());
            foreach ($tenants as $t) {
                $this->info("ID: " . $t->id . " | Name: " . $t->name . " | Slug: " . $t->slug . " | DB: " . $t->database . " | Status: " . $t->status);
            }
        }
    }
}
