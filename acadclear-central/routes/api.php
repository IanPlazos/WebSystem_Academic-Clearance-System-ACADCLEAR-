<?php

use Illuminate\Support\Facades\Route;
use App\Models\Tenant;
use App\Models\Subscription;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Tenant Verification API
Route::prefix('tenants')->group(function () {
    
    // Check if tenant is active (main verification endpoint)
    Route::get('/{slug}/status', function ($slug) {
        // Find tenant by slug or domain
        $tenant = Tenant::where('slug', $slug)
            ->orWhere('domain', $slug)
            ->first();
        
        // If tenant not found
        if (!$tenant) {
            return response()->json([
                'active' => false,
                'status' => 'not_found',
                'message' => 'University not found'
            ], 404);
        }
        
        // Check if tenant has active subscription
        $activeSubscription = $tenant->activeSubscription;
        
        // Determine if tenant should be considered active
        $isActive = $tenant->status === 'active' && $activeSubscription !== null;
        
        return response()->json([
            'active' => $isActive,
            'status' => $tenant->status,
            'tenant_id' => $tenant->id,
            'name' => $tenant->name,
            'slug' => $tenant->slug,
            'domain' => $tenant->domain,
            'database' => $tenant->database,
            'plan' => $activeSubscription ? $activeSubscription->plan->name : null,
            'plan_slug' => $activeSubscription ? $activeSubscription->plan->slug : null,
            'subscription_ends_at' => $activeSubscription ? $activeSubscription->ends_at->format('Y-m-d') : null,
            'message' => $isActive ? 'Active' : 'Subscription expired or suspended'
        ]);
    });
    
    // Get full tenant details
    Route::get('/{slug}/details', function ($slug) {
        $tenant = Tenant::with(['activeSubscription.plan'])
            ->where('slug', $slug)
            ->orWhere('domain', $slug)
            ->first();
        
        if (!$tenant) {
            return response()->json([
                'error' => 'Tenant not found'
            ], 404);
        }
        
        $activeSubscription = $tenant->activeSubscription;
        
        return response()->json([
            'id' => $tenant->id,
            'name' => $tenant->name,
            'slug' => $tenant->slug,
            'domain' => $tenant->domain,
            'database' => $tenant->database,
            'logo' => $tenant->logo,
            'logo_url' => $tenant->logo
                ? (str_starts_with($tenant->logo, 'http://') || str_starts_with($tenant->logo, 'https://')
                    ? $tenant->logo
                    : asset('storage/' . ltrim($tenant->logo, '/')))
                : null,
            'status' => $tenant->status,
            'is_active' => $tenant->status === 'active' && $activeSubscription !== null,
            'plan' => $activeSubscription ? [
                'name' => $activeSubscription->plan->name,
                'slug' => $activeSubscription->plan->slug,
                'price' => $activeSubscription->plan->price,
                'max_students' => $activeSubscription->plan->max_students,
                'features' => json_decode($activeSubscription->plan->features ?? '[]'),
                'has_advanced_reports' => $activeSubscription->plan->has_advanced_reports,
                'has_multi_campus' => $activeSubscription->plan->has_multi_campus,
                'has_custom_branding' => $activeSubscription->plan->has_custom_branding,
                'has_api_access' => $activeSubscription->plan->has_api_access,
            ] : null,
            'subscription' => $activeSubscription ? [
                'starts_at' => $activeSubscription->starts_at->format('Y-m-d'),
                'ends_at' => $activeSubscription->ends_at->format('Y-m-d'),
                'status' => $activeSubscription->status,
            ] : null,
        ]);
    });
    
    // Get plan features for a tenant
    Route::get('/{slug}/features', function ($slug) {
        $tenant = Tenant::with('activeSubscription.plan')
            ->where('slug', $slug)
            ->orWhere('domain', $slug)
            ->first();
        
        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }
        
        $activeSubscription = $tenant->activeSubscription;
        
        if (!$activeSubscription) {
            return response()->json([
                'has_advanced_reports' => false,
                'has_multi_campus' => false,
                'has_custom_branding' => false,
                'has_api_access' => false,
                'max_students' => 0,
                'features' => []
            ]);
        }
        
        $plan = $activeSubscription->plan;
        
        return response()->json([
            'has_advanced_reports' => $plan->has_advanced_reports,
            'has_multi_campus' => $plan->has_multi_campus,
            'has_custom_branding' => $plan->has_custom_branding,
            'has_api_access' => $plan->has_api_access,
            'max_students' => $plan->max_students,
            'features' => json_decode($plan->features ?? '[]'),
            'plan_name' => $plan->name,
            'plan_slug' => $plan->slug,
        ]);
    });
    
    // Simple ping/health check
    Route::get('/health', function () {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'message' => 'Central App API is running'
        ]);
    });
});

Route::post('/plan-requests', [App\Http\Controllers\SuperAdmin\PlanRequestController::class, 'store']);
Route::get('/support-chat/{tenantSlug}/summary', [App\Http\Controllers\Api\SupportChatApiController::class, 'summary']);
Route::get('/support-chat/{tenantSlug}/messages', [App\Http\Controllers\Api\SupportChatApiController::class, 'messages']);
Route::post('/support-chat/{tenantSlug}/messages', [App\Http\Controllers\Api\SupportChatApiController::class, 'store']);

// Global API health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toISOString(),
        'message' => 'Central App API is running'
    ]);
});

// Add a test endpoint to verify API is working
Route::get('/test', function () {
    return response()->json([
        'message' => 'API is working!',
        'timestamp' => now()->toISOString(),
        'version' => '1.0'
    ]);
});