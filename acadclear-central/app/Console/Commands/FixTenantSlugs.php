<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use Illuminate\Support\Str;

class FixTenantSlugs extends Command
{
    protected $signature = 'tenant:fix-slugs';
    protected $description = 'Fix invalid tenant slugs (remove dots and spaces)';

    public function handle()
    {
        $tenants = Tenant::all();
        $fixed = 0;

        foreach ($tenants as $tenant) {
            $originalSlug = $tenant->slug;
            
            // Remove any dots and spaces, convert to lowercase
            $newSlug = str_replace(['.', ' '], '-', $tenant->slug);
            $newSlug = Str::lower($newSlug);
            
            // Remove consecutive hyphens
            $newSlug = preg_replace('/-+/', '-', $newSlug);
            
            // Trim hyphens from start/end
            $newSlug = trim($newSlug, '-');
            
            if ($newSlug !== $originalSlug) {
                $this->info("Fixing: $originalSlug → $newSlug");
                $tenant->update(['slug' => $newSlug]);
                $fixed++;
            }
        }

        $this->info("\n✓ Fixed $fixed tenant slugs");
    }
}
