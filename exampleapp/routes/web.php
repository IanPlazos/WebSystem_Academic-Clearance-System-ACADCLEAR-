<?php



use App\Http\Controllers\LandingController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

// Test TenantService - Temporary route for testing
Route::get('/test-tenant', function () {
    $tenantService = app(\App\Services\TenantService::class);
    
    $tenantSlug = $tenantService->getCurrentTenant();
    $isActive = $tenantService->isTenantActive($tenantSlug);
    
    return [
        'tenant_slug' => $tenantSlug,
        'is_active' => $isActive,
        'host' => request()->getHost(),
        'full_url' => request()->fullUrl(),
        'central_app_url' => env('CENTRAL_APP_URL'),
        'message' => 'TenantService is working!'
    ];
});

// Test database switching
Route::get('/test-db', function () {
    return [
        'current_database' => DB::connection()->getDatabaseName(),
        'tenant_slug' => request()->attributes->get('tenant_slug'),
        'tenant_details' => request()->attributes->get('tenant_details'),
    ];
});

Route::get('/', [LandingController::class, 'index'])->name('landing.index');
Route::middleware('guest')
    ->post('/plan-request', [LandingController::class, 'store'])
    ->withoutMiddleware([VerifyCsrfToken::class])
    ->name('landing.store');

// Tenant suspended pages (used by CheckTenantStatus middleware)
Route::get('/suspended', function () {
    return view('errors.tenant-suspended');
})->name('tenant.suspended');

Route::get('/suspended/page', function () {
    return view('errors.tenant-suspended');
})->name('tenant.suspended.page');

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    
    // Generic dashboard redirect (by role)
    Route::get('/dashboard', function () {
        $user = auth()->user();
        if ($user->role === 'school_admin') {
            return redirect()->route('admin.dashboard');
        }
        if ($user->role === 'staff') {
            return redirect()->route('staff.dashboard');
        }
        if ($user->role === 'student') {
            return redirect()->route('student.dashboard');
        }
        return redirect()->route('profile.edit');
    })->name('dashboard');

    // School Admin Routes (ALL admin routes go here)
    Route::middleware(['role:school_admin'])->prefix('admin')->name('admin.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

        // Plan Requests
        Route::get('/plan-requests', [App\Http\Controllers\Admin\PlanRequestController::class, 'index'])->name('plan-requests.index');
        
        // College Management
        Route::resource('colleges', App\Http\Controllers\Admin\CollegeController::class);
        
        // Department Management
        Route::resource('departments', App\Http\Controllers\Admin\DepartmentController::class);
        
        // Student Management
        Route::get('/students', [App\Http\Controllers\Admin\UserController::class, 'students'])->name('students.index');
        Route::get('/students/create', [App\Http\Controllers\Admin\UserController::class, 'createStudent'])->name('students.create');
        Route::post('/students', [App\Http\Controllers\Admin\UserController::class, 'storeStudent'])->name('students.store');
        Route::get('/students/{user}/edit', [App\Http\Controllers\Admin\UserController::class, 'editStudent'])->name('students.edit');
        Route::put('/students/{user}', [App\Http\Controllers\Admin\UserController::class, 'updateStudent'])->name('students.update');
        Route::delete('/students/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroyStudent'])->name('students.destroy');
        
        // Staff Management
        Route::get('/staff', [App\Http\Controllers\Admin\UserController::class, 'staff'])->name('staff.index');
        Route::get('/staff/create', [App\Http\Controllers\Admin\UserController::class, 'createStaff'])->name('staff.create');
        Route::post('/staff', [App\Http\Controllers\Admin\UserController::class, 'storeStaff'])->name('staff.store');
        Route::get('/staff/{user}/edit', [App\Http\Controllers\Admin\UserController::class, 'editStaff'])->name('staff.edit');
        Route::put('/staff/{user}', [App\Http\Controllers\Admin\UserController::class, 'updateStaff'])->name('staff.update');
        Route::delete('/staff/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroyStaff'])->name('staff.destroy');
        
        // AJAX route for departments (used in staff creation)
        Route::get('/get-departments/{college}', [App\Http\Controllers\Admin\UserController::class, 'getDepartments'])
            ->name('get.departments');
        
        // ============ NEW: REPORTS ROUTES ============
        Route::get('/reports', [App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export-pdf', [App\Http\Controllers\Admin\ReportController::class, 'exportPdf'])->name('reports.export.pdf');
        Route::get('/reports/export-csv', [App\Http\Controllers\Admin\ReportController::class, 'exportCsv'])->name('reports.export.csv');
        Route::get('/reports/data', [App\Http\Controllers\Admin\ReportController::class, 'getData'])->name('reports.data');
    });

    // Staff Routes
    Route::middleware(['role:staff'])->prefix('staff')->name('staff.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [App\Http\Controllers\Staff\DashboardController::class, 'index'])->name('dashboard');
        
        // Clearance Management
        Route::get('/clearances', [App\Http\Controllers\Staff\ClearanceController::class, 'index'])->name('clearances.index');
        Route::post('/clearances/{clearance}/approve', [App\Http\Controllers\Staff\ClearanceController::class, 'approve'])->name('clearances.approve');
        Route::post('/clearances/{clearance}/reject', [App\Http\Controllers\Staff\ClearanceController::class, 'reject'])->name('clearances.reject');
        Route::post('/clearances/bulk-approve', [App\Http\Controllers\Staff\ClearanceController::class, 'bulkApprove'])->name('clearances.bulk-approve');
        Route::get('/clearances/export', [App\Http\Controllers\Staff\ClearanceController::class, 'export'])->name('clearances.export');
    });

    // Student Routes
    Route::middleware(['role:student'])->prefix('student')->name('student.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [App\Http\Controllers\Student\DashboardController::class, 'index'])->name('dashboard');
        
        // Clearance Views
        Route::get('/clearances', [App\Http\Controllers\Student\ClearanceController::class, 'index'])->name('clearances.index');
        Route::get('/clearances/{id}', [App\Http\Controllers\Student\ClearanceController::class, 'show'])->name('clearances.show');
        Route::get('/clearances-summary', [App\Http\Controllers\Student\ClearanceController::class, 'summary'])->name('clearances.summary');
    });

    // Profile routes (for all authenticated users)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';