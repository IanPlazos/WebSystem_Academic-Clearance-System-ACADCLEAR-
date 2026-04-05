<?php
namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class TenantDatabaseManager
{
    /**
     * Create everything for a new tenant
     */
    public function createTenant($tenant, array $adminCredentials = [])
    {
        $this->log("========================================");
        $this->log("Creating tenant: {$tenant->name}");
        $this->log("========================================");
        
        try {
            // Step 1: Create database
            $this->createDatabase($tenant);
            
            // Step 2: Run migrations
            $this->runMigrations($tenant);
            
            // Step 3: Seed default data
            $this->seedDefaultData($tenant, $adminCredentials);
            
            $this->log("");
            $this->log("✓✓✓ Tenant {$tenant->name} created successfully! ✓✓✓");
            $this->log("");
            return true;
            
        } catch (\Exception $e) {
            $this->log("✗✗✗ Error creating tenant: " . $e->getMessage() . " ✗✗✗");
            return false;
        }
    }
    
    /**
     * Create the database
     */
    private function createDatabase($tenant)
    {
        $databaseName = $tenant->database;
        
        $this->log("Step 1: Creating database: {$databaseName}");
        
        // Check if database exists
        $exists = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$databaseName]);
        
        if (!empty($exists)) {
            $this->log("  Database already exists, dropping...");
            DB::statement("DROP DATABASE IF EXISTS {$databaseName}");
        }
        
        // Create new database
        DB::statement("CREATE DATABASE {$databaseName} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $this->log("  ✓ Database created: {$databaseName}");
    }
    
    /**
     * Run migrations on tenant database
     */
    private function runMigrations($tenant)
    {
        $databaseName = $tenant->database;
        
        $this->log("Step 2: Running migrations on: {$databaseName}");
        
        // Resolve tenant migration source. Prefer local tenant migrations,
        // then fall back to sibling exampleapp migrations.
        $migrationPath = $this->resolveTenantMigrationPath();

        if ($migrationPath === null) {
            $this->log('  ⚠ No tenant migration files found');
            $this->log('  Falling back to schema clone from template/existing tenant database...');
            $this->cloneSchemaFromTemplate($databaseName);
            $this->log('  ✓ Schema cloned successfully');
            return;
        }

        $this->log("  Using migrations from: {$migrationPath}");
        
        // Configure temporary connection
        Config::set('database.connections.tenant_migration', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $databaseName,
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]);

        // Ensure migration uses the latest configured database instead of a cached connection.
        DB::purge('tenant_migration');
        DB::reconnect('tenant_migration');
        
        // Run migrations
        try {
            Artisan::call('migrate', [
                '--database' => 'tenant_migration',
                '--path' => $migrationPath,
                '--realpath' => true,
                '--force' => true,
            ]);
            
            $output = Artisan::output();
            if ($output) {
                $this->log("  Migration output: " . trim($output));
            }

            $hasUsersTable = DB::connection('tenant_migration')
                ->getSchemaBuilder()
                ->hasTable('users');

            if (!$hasUsersTable) {
                throw new \RuntimeException('Tenant migrations completed but users table was not created.');
            }

            $this->log("  ✓ Migrations completed");
            
        } catch (\Exception $e) {
            $this->log("  ✗ Migration failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Resolve where tenant migrations should be loaded from.
     */
    private function resolveTenantMigrationPath(): ?string
    {
        $tenantMigrationPath = database_path('migrations/tenant');

        if (!is_dir($tenantMigrationPath)) {
            mkdir($tenantMigrationPath, 0755, true);
            $this->log("  Created tenant migration directory: {$tenantMigrationPath}");
        }

        $tenantMigrationFiles = glob($tenantMigrationPath . '/*.php');
        if (!empty($tenantMigrationFiles)) {
            return $tenantMigrationPath;
        }

        $exampleAppMigrationPath = base_path('../exampleapp/database/migrations');
        if (is_dir($exampleAppMigrationPath)) {
            $exampleMigrationFiles = glob($exampleAppMigrationPath . '/*.php');
            if (!empty($exampleMigrationFiles)) {
                return $exampleAppMigrationPath;
            }
        }

        return null;
    }

    /**
     * Clone table structure from a template/existing tenant database.
     */
    private function cloneSchemaFromTemplate(string $targetDatabase): void
    {
        $sourceDatabase = $this->resolveTemplateDatabase($targetDatabase);

        if (!$sourceDatabase) {
            throw new \RuntimeException('No template tenant database found for schema cloning.');
        }

        $tables = DB::select(
            "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = 'BASE TABLE'",
            [$sourceDatabase]
        );

        if (empty($tables)) {
            throw new \RuntimeException("Template database {$sourceDatabase} has no tables to clone.");
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            foreach ($tables as $row) {
                $tableName = $row->TABLE_NAME;
                DB::statement("CREATE TABLE `{$targetDatabase}`.`{$tableName}` LIKE `{$sourceDatabase}`.`{$tableName}`");
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->log("  Source template database: {$sourceDatabase}");
        $this->log('  Cloned tables: ' . count($tables));
    }

    /**
     * Resolve a source database to use as schema template.
     */
    private function resolveTemplateDatabase(string $targetDatabase): ?string
    {
        $configuredTemplate = env('TENANT_TEMPLATE_DATABASE');

        if (!empty($configuredTemplate)
            && $configuredTemplate !== $targetDatabase
            && $this->databaseExists($configuredTemplate)) {
            return $configuredTemplate;
        }

        $candidates = Tenant::query()
            ->where('database', '!=', $targetDatabase)
            ->orderBy('id')
            ->pluck('database');

        foreach ($candidates as $candidateDatabase) {
            if ($this->databaseExists($candidateDatabase)) {
                return $candidateDatabase;
            }
        }

        return null;
    }

    private function databaseExists(string $databaseName): bool
    {
        $exists = DB::select(
            'SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?',
            [$databaseName]
        );

        return !empty($exists);
    }
    
    /**
     * Seed default data for the tenant
     */
    private function seedDefaultData($tenant, array $adminCredentials = [])
    {
        $databaseName = $tenant->database;
        $slug = $tenant->slug;
        
        $this->log("Step 3: Seeding default data for: {$tenant->name}");
        
        // Configure connection for seeding
        Config::set('database.connections.tenant_seed', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $databaseName,
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
        ]);
        
        // Store original connection
        $originalConnection = Config::get('database.default');
        Config::set('database.default', 'tenant_seed');
        DB::reconnect('tenant_seed');
        
        try {
            // Check if users table exists (migrations ran successfully)
            $tables = DB::select('SHOW TABLES');
            $tableNames = array_map(static function ($row) {
                return (string) array_values((array) $row)[0];
            }, $tables);
            
            if (!in_array('users', $tableNames, true)) {
                throw new \RuntimeException('Tenant users table is missing after migration.');
            }
            
            // Create admin user
            $adminEmail = $adminCredentials['email'] ?? "admin@{$slug}.acadclear.com";
            $adminPassword = $adminCredentials['password'] ?? 'password';
            $adminName = $tenant->name . ' Administrator';
            
            DB::table('users')->insert([
                'name' => $adminName,
                'email' => $adminEmail,
                'password' => bcrypt($adminPassword),
                'role' => 'school_admin',
                'tenant_id' => $slug,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->log("  ✓ Admin user created: {$adminEmail}");
            
            // Keep new tenants clean by default. Optional demo data can be enabled via env.
            if (filter_var(env('SEED_TENANT_DEMO_DATA', false), FILTER_VALIDATE_BOOLEAN)) {
                if (in_array('colleges', $tableNames)) {
                    $colleges = [
                        'College of Technology',
                        'College of Arts and Sciences',
                        'College of Business',
                        'College of Education',
                        'College of Public Administration and Governance'
                    ];

                    $collegeIds = [];
                    foreach ($colleges as $collegeName) {
                        $id = DB::table('colleges')->insertGetId([
                            'name' => $collegeName,
                            'tenant_id' => $slug,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $collegeIds[$collegeName] = $id;
                    }
                    $this->log("  ✓ Created " . count($colleges) . " demo colleges");

                    if (in_array('departments', $tableNames)) {
                        $departments = [
                            'College of Technology' => ['Computer Science', 'Information Technology', 'Engineering'],
                            'College of Arts and Sciences' => ['Mathematics', 'Physics', 'English', 'Biology'],
                            'College of Business' => ['Accountancy', 'Business Administration', 'Entrepreneurship'],
                            'College of Education' => ['Elementary Education', 'Secondary Education', 'Physical Education'],
                            'College of Public Administration and Governance' => ['Public Administration', 'Political Science', 'Local Governance'],
                        ];

                        $deptCount = 0;
                        foreach ($departments as $collegeName => $deptList) {
                            if (isset($collegeIds[$collegeName])) {
                                $collegeId = $collegeIds[$collegeName];
                                foreach ($deptList as $deptName) {
                                    DB::table('departments')->insert([
                                        'college_id' => $collegeId,
                                        'name' => $deptName,
                                        'tenant_id' => $slug,
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ]);
                                    $deptCount++;
                                }
                            }
                        }
                        $this->log("  ✓ Created {$deptCount} demo departments");
                    }

                    DB::table('users')->insert([
                        'name' => 'Sample Student',
                        'email' => 'student@' . $slug . '.acadclear.com',
                        'password' => bcrypt('password'),
                        'role' => 'student',
                        'college_id' => $collegeIds['College of Technology'] ?? null,
                        'tenant_id' => $slug,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $firstDept = DB::table('departments')->where('college_id', $collegeIds['College of Technology'] ?? 0)->first();

                    DB::table('users')->insert([
                        'name' => 'Sample Staff',
                        'email' => 'staff@' . $slug . '.acadclear.com',
                        'password' => bcrypt('password'),
                        'role' => 'staff',
                        'college_id' => $collegeIds['College of Technology'] ?? null,
                        'department_id' => $firstDept->id ?? null,
                        'tenant_id' => $slug,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $this->log('  ✓ Demo data enabled and seeded');
                }
            } else {
                $this->log('  ✓ Demo seed skipped (clean tenant mode)');
            }
            
            $this->log("  ✓ Seeding completed!");
            
        } catch (\Exception $e) {
            $this->log("  ✗ Seeding failed: " . $e->getMessage());
            throw $e;
        } finally {
            // Restore original connection
            Config::set('database.default', $originalConnection);
            DB::reconnect($originalConnection);
        }
    }
    
    private function log($message)
    {
        echo $message . "\n";
        Log::info($message);
    }
}