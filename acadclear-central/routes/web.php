<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Test subdomain detection (TEMPORARY - Remove after testing)
Route::get('/test-subdomain', function () {
    $host = request()->getHost();
    $parts = explode('.', $host);
    
    return [
        'host' => $host,
        'full_url' => request()->fullUrl(),
        'parts' => $parts,
        'subdomain' => count($parts) > 2 ? $parts[0] : ($host != 'localhost' ? $parts[0] : null),
        'is_localhost' => $host === 'localhost',
        'tenant_slug' => ($host !== 'localhost' && $host !== '127.0.0.1') ? explode('.', $host)[0] : 'default',
        'timestamp' => now()->toISOString(),
    ];
});

Route::get('/', function () {
    return view('auth.login');
});

// Super Admin Routes (protected by auth and role)
Route::middleware(['auth'])->group(function () {
    // Keep Laravel auth scaffolding redirects working.
    Route::get('/dashboard', function () {
        return redirect()->route('super-admin.dashboard');
    })->name('dashboard');
    
    // Super Admin Dashboard and Management
    Route::prefix('super-admin')->name('super-admin.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\SuperAdmin\DashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/plan-requests', [App\Http\Controllers\SuperAdmin\PlanRequestController::class, 'index'])
            ->name('plan-requests.index');
        Route::post('/plan-requests/{planRequest}/approve', [App\Http\Controllers\SuperAdmin\PlanRequestController::class, 'approve'])
            ->name('plan-requests.approve');
        Route::post('/plan-requests/{planRequest}/reject', [App\Http\Controllers\SuperAdmin\PlanRequestController::class, 'reject'])
            ->name('plan-requests.reject');
        
        // Tenant Management
        Route::resource('tenants', App\Http\Controllers\SuperAdmin\TenantController::class);
        Route::post('tenants/{tenant}/toggle-status', [App\Http\Controllers\SuperAdmin\TenantController::class, 'toggleStatus'])
            ->name('tenants.toggle-status');
        
        // Plan Management
        Route::resource('plans', App\Http\Controllers\SuperAdmin\PlanController::class);
        
        // Subscription Management
        Route::resource('subscriptions', App\Http\Controllers\SuperAdmin\SubscriptionController::class);
        Route::post('subscriptions/{subscription}/renew', [App\Http\Controllers\SuperAdmin\SubscriptionController::class, 'renew'])
            ->name('subscriptions.renew');
        
        // Payment Management
        Route::resource('payments', App\Http\Controllers\SuperAdmin\PaymentController::class);
        Route::get('payments/{payment}/receipt', [App\Http\Controllers\SuperAdmin\PaymentController::class, 'getReceipt'])
            ->name('payments.receipt');
        
        // Analytics
        Route::get('analytics', [App\Http\Controllers\SuperAdmin\AnalyticsController::class, 'index'])
            ->name('analytics.index');
        Route::get('analytics/export', [App\Http\Controllers\SuperAdmin\AnalyticsController::class, 'export'])
            ->name('analytics.export');
    });

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';