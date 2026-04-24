<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BackfillTenantAccess extends Command
{
    protected $signature = 'tenant:backfill-access
                            {--tenant= : Tenant slug or ID}
                            {--all : Backfill all tenants}
                            {--password=password : Default password for newly created admin users}';

    protected $description = 'Backfill tenant database/schema/admin access for existing universities';

    public function handle(): int
    {
        $tenants = $this->resolveTenants();

        if ($tenants->isEmpty()) {
            $this->warn('No tenants found for backfill.');
            return Command::SUCCESS;
        }

        $templateDatabase = $this->resolveTemplateDatabase();

        if (!$templateDatabase) {
            $this->error('Could not resolve a template database with users table.');
            $this->line('Set TENANT_TEMPLATE_DATABASE in .env or ensure at least one tenant DB has schema.');
            return Command::FAILURE;
        }

        $this->info("Using template database: {$templateDatabase}");

        $success = 0;
        $failed = 0;

        foreach ($tenants as $tenant) {
            $this->line('');
            $this->info("Processing tenant: {$tenant->name} ({$tenant->slug})");

            try {
                $database = $tenant->database;

                if (!$this->databaseExists($database)) {
                    DB::statement("CREATE DATABASE `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    $this->line("  Created database: {$database}");
                } else {
                    $this->line("  Database exists: {$database}");
                }

                if (!$this->tableExists($database, 'users')) {
                    $this->cloneSchema($templateDatabase, $database);
                    $this->line('  Cloned schema from template database');
                } else {
                    $this->line('  Users table already exists, skipping schema clone');
                }

                $adminEmail = data_get($tenant->settings, 'admin_email') ?: "admin@{$tenant->slug}.acadclear.com";
                $defaultPassword = (string) $this->option('password');

                $createdAdmin = $this->ensureAdminUser($tenant, $adminEmail, $defaultPassword);

                $settings = is_array($tenant->settings) ? $tenant->settings : [];
                if (empty($settings['admin_email'])) {
                    $settings['admin_email'] = $adminEmail;
                    $tenant->settings = $settings;
                    $tenant->save();
                }

                if ($createdAdmin) {
                    $this->line("  Created school admin: {$adminEmail}");
                    $this->line("  Temporary password: {$defaultPassword}");
                } else {
                    $this->line("  School admin already exists. Email target: {$adminEmail}");
                }

                $this->ensureStaffPermissionsColumn($tenant);
                $updatedStaff = $this->backfillStaffModuleAccess($tenant, $this->resolveDefaultStaffPermissions());
                $this->line("  Staff module access synced for {$updatedStaff} staff account(s)");

                $success++;
            } catch (\Throwable $e) {
                $failed++;
                $this->error('  Failed: ' . $e->getMessage());
            }
        }

        $this->line('');
        $this->info('Backfill completed.');
        $this->info("Successful: {$success}");
        $this->info("Failed: {$failed}");

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    private function resolveTenants()
    {
        if ($this->option('all')) {
            return Tenant::orderBy('id')->get();
        }

        if ($this->option('tenant')) {
            return Tenant::query()
                ->where('slug', $this->option('tenant'))
                ->orWhere('id', $this->option('tenant'))
                ->get();
        }

        return collect();
    }

    private function resolveTemplateDatabase(): ?string
    {
        $configured = env('TENANT_TEMPLATE_DATABASE');

        if (!empty($configured) && $this->databaseExists($configured) && $this->tableExists($configured, 'users')) {
            return $configured;
        }

        $databases = Tenant::query()->orderBy('id')->pluck('database');

        foreach ($databases as $database) {
            if ($this->databaseExists($database) && $this->tableExists($database, 'users')) {
                return $database;
            }
        }

        return null;
    }

    private function databaseExists(string $database): bool
    {
        $exists = DB::select(
            'SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?',
            [$database]
        );

        return !empty($exists);
    }

    private function tableExists(string $database, string $table): bool
    {
        $exists = DB::select(
            'SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?',
            [$database, $table]
        );

        return !empty($exists);
    }

    private function cloneSchema(string $sourceDatabase, string $targetDatabase): void
    {
        $tables = DB::select(
            "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = 'BASE TABLE'",
            [$sourceDatabase]
        );

        if (empty($tables)) {
            throw new \RuntimeException("Template database {$sourceDatabase} has no tables.");
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            foreach ($tables as $row) {
                $table = $row->TABLE_NAME;
                DB::statement("CREATE TABLE IF NOT EXISTS `{$targetDatabase}`.`{$table}` LIKE `{$sourceDatabase}`.`{$table}`");
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    private function ensureAdminUser(Tenant $tenant, string $adminEmail, string $password): bool
    {
        $database = $tenant->database;
        $usersTable = "{$database}.users";

        $existingAdmin = DB::table($usersTable)->where('role', 'school_admin')->first();

        if ($existingAdmin) {
            return false;
        }

        $columns = collect(DB::select(
            'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?',
            [$database, 'users']
        ))->pluck('COLUMN_NAME')->all();

        $payload = [];

        if (in_array('name', $columns, true)) {
            $payload['name'] = $tenant->name . ' Administrator';
        }
        if (in_array('email', $columns, true)) {
            $payload['email'] = $adminEmail;
        }
        if (in_array('password', $columns, true)) {
            $payload['password'] = Hash::make($password);
        }
        if (in_array('role', $columns, true)) {
            $payload['role'] = 'school_admin';
        }
        if (in_array('tenant_id', $columns, true)) {
            $payload['tenant_id'] = $tenant->slug;
        }
        if (in_array('created_at', $columns, true)) {
            $payload['created_at'] = now();
        }
        if (in_array('updated_at', $columns, true)) {
            $payload['updated_at'] = now();
        }

        DB::table($usersTable)->insert($payload);

        return true;
    }

    private function ensureStaffPermissionsColumn(Tenant $tenant): void
    {
        $database = $tenant->database;

        $columns = collect(DB::select(
            'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?',
            [$database, 'users']
        ))->pluck('COLUMN_NAME')->all();

        if (in_array('permissions', $columns, true)) {
            return;
        }

        DB::statement("ALTER TABLE `{$database}`.`users` ADD COLUMN `permissions` JSON NULL AFTER `office_role`");
    }

    /**
     * @return array<int, string>
     */
    private function resolveDefaultStaffPermissions(): array
    {
        return [
            'tenant.dashboard.view',
            'tenant.profile.manage',
            'tenant.colleges.manage',
            'tenant.departments.manage',
            'tenant.students.manage',
            'tenant.staff.manage',
            'tenant.reports.view',
            'tenant.clearances.view',
            'tenant.clearances.create',
            'tenant.clearances.update',
            'tenant.clearances.export',
        ];
    }

    /**
     * Sync the same module access set to every staff account in a tenant database.
     */
    private function backfillStaffModuleAccess(Tenant $tenant, array $permissions): int
    {
        $database = $tenant->database;

        return DB::table("{$database}.users")
            ->where('role', 'staff')
            ->update([
                'permissions' => json_encode(array_values(array_unique($permissions))),
                'updated_at' => now(),
            ]);
    }
}
