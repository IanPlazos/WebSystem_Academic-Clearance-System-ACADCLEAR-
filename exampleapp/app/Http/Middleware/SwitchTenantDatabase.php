<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SwitchTenantDatabase
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Get current tenant slug from request attributes
        $tenantSlug = $request->attributes->get('tenant_slug');
        
        if (!$tenantSlug) {
            // No tenant detected, use default database
            return $next($request);
        }
        
        // Get tenant details from request (set by DetectTenant middleware)
        $tenantDetails = $request->attributes->get('tenant_details');
        
        if (!$tenantDetails || !isset($tenantDetails['database'])) {
            // No tenant database info, use default
            return $next($request);
        }
        
        $databaseName = $tenantDetails['database'];
        
        // Check if we're already on this database
        $currentDatabase = DB::connection()->getDatabaseName();
        
        if ($currentDatabase === $databaseName) {
            // Already on correct database
            return $next($request);
        }
        
        // Log database switching
        Log::info("Switching database", [
            'tenant' => $tenantSlug,
            'from' => $currentDatabase,
            'to' => $databaseName
        ]);
        
        try {
            // Check if database exists
            $exists = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$databaseName]);
            
            if (empty($exists)) {
                Log::warning("Database not found", ['database' => $databaseName]);
                return $next($request);
            }
            
            // Switch database connection
            Config::set('database.connections.tenant', [
                'driver' => 'mysql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => $databaseName,
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', ''),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
            ]);
            
            // Set as default connection
            Config::set('database.default', 'tenant');
            
            // Purge old connection to force fresh connection
            DB::purge('mysql');
            DB::purge('tenant');
            
            // Reconnect to tenant database
            DB::reconnect('tenant');
            
            Log::info("Database switched successfully", ['database' => $databaseName]);
            
        } catch (\Exception $e) {
            Log::error("Failed to switch database", [
                'database' => $databaseName,
                'error' => $e->getMessage()
            ]);
        }
        
        return $next($request);
    }
}