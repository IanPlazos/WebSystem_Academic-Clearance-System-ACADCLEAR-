<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class CloneTenantDatabase extends Command
{
    protected $signature = 'tenant:clone-database {slug} {database_name?}';
    protected $description = 'Clone the base database structure for a new tenant';

    public function handle()
    {
        $slug = $this->argument('slug');
        $databaseName = $this->argument('database_name') ?: "acadclear_{$slug}";
        
        $sourceDatabase = env('DB_DATABASE', 'finalwebsys');
        
        $this->info("Cloning database for tenant: {$slug}");
        $this->info("Source database: {$sourceDatabase}");
        $this->info("Target database: {$databaseName}");
        
        // Check if target database already exists
        $exists = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$databaseName]);
        
        if (!empty($exists)) {
            $this->warn("Database {$databaseName} already exists!");
            if (!$this->confirm('Do you want to drop and recreate it?', false)) {
                $this->info("Operation cancelled.");
                return;
            }
            
            // Drop existing database
            DB::statement("DROP DATABASE IF EXISTS {$databaseName}");
            $this->info("Dropped existing database.");
        }
        
        // Create new database
        DB::statement("CREATE DATABASE {$databaseName}");
        $this->info("Created new database: {$databaseName}");
        
        // Get the SQL dump of source database structure
        $this->info("Exporting structure from source database...");
        
        // Run migration on new database
        config(['database.connections.tenant' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $databaseName,
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
        ]]);
        
        $this->info("Running migrations on new database...");
        
        // Run migrations on the new database
        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations',
            '--force' => true,
        ]);
        
        $this->info(Artisan::output());
        
        // Seed default data for the tenant
        $this->seedDefaultData($slug, $databaseName);
        
        $this->info("✓ Database {$databaseName} created successfully!");
        $this->info("");
        $this->info("Next steps:");
        $this->info("1. Update your Central App with this tenant's database name: {$databaseName}");
        $this->info("2. Test accessing: http://{$slug}.localhost:8000");
    }
    
    private function seedDefaultData($slug, $databaseName)
    {
        $this->info("Seeding default data...");
        
        // Create default admin user
        DB::connection('tenant')->table('users')->insert([
            'name' => 'System Administrator',
            'email' => "admin@{$slug}.acadclear.com",
            'password' => bcrypt('password'),
            'role' => 'school_admin',
            'tenant_id' => $slug,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Create default colleges
        $colleges = [
            'College of Technology',
            'College of Arts and Sciences',
            'College of Business',
            'College of Education',
            'College of Public Administration and Governance'
        ];
        
        foreach ($colleges as $collegeName) {
            DB::connection('tenant')->table('colleges')->insert([
                'name' => $collegeName,
                'tenant_id' => $slug,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        $this->info("Default data seeded successfully!");
        $this->info("Admin credentials:");
        $this->info("  Email: admin@{$slug}.acadclear.com");
        $this->info("  Password: password");
    }
}