<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class BackupTenantDatabase extends Command
{
    protected $signature = 'tenant:backup 
                            {--tenant= : Specific tenant slug or ID}
                            {--all : Backup all tenants}
                            {--output= : Output directory for backups}';
    
    protected $description = 'Backup tenant database(s)';

    public function handle()
    {
        $outputDir = $this->option('output') ?: storage_path('backups');

        $mysqldumpPath = $this->resolveMysqldumpPath();

        if (!$mysqldumpPath) {
            $this->error('mysqldump not found. Install MySQL client tools or add mysqldump to PATH.');
            $this->warn('Tip: set MYSQLDUMP_PATH in your .env, e.g. MYSQLDUMP_PATH="C:\\xampp\\mysql\\bin\\mysqldump.exe"');
            return Command::FAILURE;
        }

        $this->line("Using mysqldump: {$mysqldumpPath}");
        
        // Create backup directory if it doesn't exist
        if (!File::exists($outputDir)) {
            File::makeDirectory($outputDir, 0755, true);
            $this->info("Created backup directory: {$outputDir}");
        }
        
        $tenants = collect();
        
        if ($this->option('all')) {
            $tenants = Tenant::all();
            $this->info("Backing up all {$tenants->count()} tenants...");
        } elseif ($this->option('tenant')) {
            $tenant = Tenant::where('slug', $this->option('tenant'))
                ->orWhere('id', $this->option('tenant'))
                ->first();
            
            if ($tenant) {
                $tenants->push($tenant);
            } else {
                $this->error("Tenant not found: {$this->option('tenant')}");
                return Command::FAILURE;
            }
        } else {
            $this->error("Please specify --tenant or --all");
            return Command::FAILURE;
        }
        
        $backupCount = 0;
        
        foreach ($tenants as $tenant) {
            $this->line("");
            $this->info("Backing up: {$tenant->name}");
            
            $databaseName = $tenant->database;
            $backupFile = $outputDir . "/{$tenant->slug}_backup_" . now()->format('Y-m-d_H-i-s') . ".sql";
            
            try {
                // Check if database exists
                $exists = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$databaseName]);
                
                if (empty($exists)) {
                    $this->warn("  Database {$databaseName} does not exist, skipping...");
                    continue;
                }
                
                // Create backup using mysqldump
                $host = env('DB_HOST', '127.0.0.1');
                $username = env('DB_USERNAME', 'root');
                $password = env('DB_PASSWORD', '');
                $arguments = [
                    $mysqldumpPath,
                    '--host=' . $host,
                    '--user=' . $username,
                ];

                if ($password !== '') {
                    $arguments[] = '--password=' . $password;
                }

                $arguments[] = '--single-transaction';
                $arguments[] = '--quick';
                $arguments[] = $databaseName;

                $process = new Process($arguments);
                $process->setTimeout(120);
                $process->run();

                if ($process->isSuccessful()) {
                    File::put($backupFile, $process->getOutput());
                    $size = round(File::size($backupFile) / 1024, 2);
                    $this->info("  ✓ Backup saved: {$backupFile} ({$size} KB)");
                    $backupCount++;
                } else {
                    $this->error("  ✗ Backup failed for {$databaseName}");
                    $errorText = trim($process->getErrorOutput() ?: $process->getOutput());

                    if ($errorText !== '') {
                        $this->line('    ' . str_replace(["\r\n", "\n"], "\n    ", $errorText));
                    }
                }
                
            } catch (\Exception $e) {
                $this->error("  Error: " . $e->getMessage());
            }
        }
        
        $this->line("");
        $this->info("=== Backup Summary ===");
        $this->info("Backups created: {$backupCount}");
        $this->info("Location: {$outputDir}");
        
        return Command::SUCCESS;
    }

    private function resolveMysqldumpPath(): ?string
    {
        $configuredPath = env('MYSQLDUMP_PATH');

        if (!empty($configuredPath) && File::exists($configuredPath)) {
            return $configuredPath;
        }

        $process = new Process(['where', 'mysqldump']);
        $process->setTimeout(5);
        $process->run();

        if ($process->isSuccessful()) {
            $found = trim(explode(PHP_EOL, trim($process->getOutput()))[0] ?? '');

            if ($found !== '' && File::exists($found)) {
                return $found;
            }
        }

        $candidates = [
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
            'C:\\Program Files\\MariaDB 10.6\\bin\\mysqldump.exe',
            'C:\\Program Files\\MariaDB 10.5\\bin\\mysqldump.exe',
            'C:\\Program Files\\MariaDB 10.4\\bin\\mysqldump.exe',
        ];

        foreach ($candidates as $path) {
            if (File::exists($path)) {
                return $path;
            }
        }

        return null;
    }
}