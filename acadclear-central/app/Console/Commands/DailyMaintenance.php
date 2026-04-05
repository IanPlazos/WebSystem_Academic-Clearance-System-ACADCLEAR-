<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DailyMaintenance extends Command
{
    protected $signature = 'maintenance:daily 
                            {--dry-run : Run without making changes}
                            {--skip-backup : Skip database backup}
                            {--skip-report : Skip report generation}';
    
    protected $description = 'Run all daily maintenance tasks';

    public function handle()
    {
        $this->info('=== Starting Daily Maintenance ===');
        $this->info('Started at: ' . now()->format('Y-m-d H:i:s'));
        
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }
        
        // Task 1: Check expired subscriptions
        $this->line('');
        $this->info('Task 1: Checking expired subscriptions...');
        $this->call('subscriptions:check-expiry', [
            '--dry-run' => $isDryRun
        ]);
        
        // Task 2: Backup tenant databases
        if (!$this->option('skip-backup')) {
            $this->line('');
            $this->info('Task 2: Backing up tenant databases...');
            $this->call('tenant:backup', ['--all' => true]);
        } else {
            $this->line('');
            $this->info('Task 2: Skipping backup (--skip-backup flag used)');
        }
        
        // Task 3: Generate daily report
        if (!$this->option('skip-report')) {
            $this->line('');
            $this->info('Task 3: Generating daily report...');
            $this->call('report:daily', ['--save' => true]);
        } else {
            $this->line('');
            $this->info('Task 3: Skipping report (--skip-report flag used)');
        }
        
        $this->line('');
        $this->info('=== Daily Maintenance Complete ===');
        $this->info('Completed at: ' . now()->format('Y-m-d H:i:s'));
        
        return Command::SUCCESS;
    }
}