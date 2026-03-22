<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

// Super Admin Routes (protected by auth and role)
Route::middleware(['auth'])->group(function () {
    
    // Super Admin Dashboard and Management
    Route::prefix('super-admin')->name('super-admin.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\SuperAdmin\DashboardController::class, 'index'])
            ->name('dashboard');
        
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