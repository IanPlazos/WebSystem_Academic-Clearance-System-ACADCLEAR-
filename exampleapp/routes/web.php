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

    Route::get('/support/chat', [App\Http\Controllers\SupportChatController::class, 'index'])
        ->middleware('permission:tenant.support_chat.access')
        ->name('support.chat');
    Route::get('/support/chat/messages', [App\Http\Controllers\SupportChatController::class, 'messages'])
        ->middleware('permission:tenant.support_chat.access')
        ->name('support.chat.messages');
    Route::post('/support/chat/messages', [App\Http\Controllers\SupportChatController::class, 'store'])
        ->middleware('permission:tenant.support_chat.access')
        ->name('support.chat.store');
    
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

    // Tenant Admin Routes (permission-driven)
    Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])
            ->middleware('permission:tenant.dashboard.view')
            ->name('dashboard');

        // Plan Requests
        Route::get('/plan-requests', [App\Http\Controllers\Admin\PlanRequestController::class, 'index'])
            ->middleware('permission:tenant.plan_requests.view')
            ->name('plan-requests.index');
        
        // College Management
        Route::resource('colleges', App\Http\Controllers\Admin\CollegeController::class)
            ->except(['index'])
            ->middleware('permission:tenant.colleges.manage');

        Route::get('/colleges', [App\Http\Controllers\Admin\CollegeController::class, 'index'])
            ->middleware('permission:tenant.colleges.manage')
            ->name('colleges.index');
        
        // Department Management
        Route::resource('departments', App\Http\Controllers\Admin\DepartmentController::class)
            ->except(['index'])
            ->middleware('permission:tenant.departments.manage');

        Route::get('/departments', [App\Http\Controllers\Admin\DepartmentController::class, 'index'])
            ->middleware('permission:tenant.departments.manage')
            ->name('departments.index');
        
        // Student Management
        Route::get('/students', [App\Http\Controllers\Admin\UserController::class, 'students'])
            ->middleware('permission:tenant.students.manage')
            ->name('students.index');
        Route::get('/students/create', [App\Http\Controllers\Admin\UserController::class, 'createStudent'])
            ->middleware('permission:tenant.students.manage')
            ->name('students.create');
        Route::post('/students', [App\Http\Controllers\Admin\UserController::class, 'storeStudent'])
            ->middleware('permission:tenant.students.manage')
            ->name('students.store');
        Route::get('/students/{user}/edit', [App\Http\Controllers\Admin\UserController::class, 'editStudent'])
            ->middleware('permission:tenant.students.manage')
            ->name('students.edit');
        Route::put('/students/{user}', [App\Http\Controllers\Admin\UserController::class, 'updateStudent'])
            ->middleware('permission:tenant.students.manage')
            ->name('students.update');
        Route::delete('/students/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroyStudent'])
            ->middleware('permission:tenant.students.manage')
            ->name('students.destroy');
        
        // Staff Management
        Route::get('/staff', [App\Http\Controllers\Admin\UserController::class, 'staff'])
            ->middleware('permission:tenant.staff.manage')
            ->name('staff.index');
        Route::get('/staff/create', [App\Http\Controllers\Admin\UserController::class, 'createStaff'])
            ->middleware('permission:tenant.staff.manage')
            ->name('staff.create');
        Route::post('/staff', [App\Http\Controllers\Admin\UserController::class, 'storeStaff'])
            ->middleware('permission:tenant.staff.manage')
            ->name('staff.store');
        Route::get('/staff/{user}/edit', [App\Http\Controllers\Admin\UserController::class, 'editStaff'])
            ->middleware('permission:tenant.staff.manage')
            ->name('staff.edit');
        Route::put('/staff/{user}', [App\Http\Controllers\Admin\UserController::class, 'updateStaff'])
            ->middleware('permission:tenant.staff.manage')
            ->name('staff.update');
        Route::delete('/staff/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroyStaff'])
            ->middleware('permission:tenant.staff.manage')
            ->name('staff.destroy');
        
        // AJAX route for departments (used in staff creation)
        Route::get('/get-departments/{college}', [App\Http\Controllers\Admin\UserController::class, 'getDepartments'])
            ->middleware('permission:tenant.departments.manage|tenant.staff.manage')
            ->name('get.departments');
        
        // ============ NEW: REPORTS ROUTES ============
        Route::get('/reports', [App\Http\Controllers\Admin\ReportController::class, 'index'])
            ->middleware('permission:tenant.reports.view')
            ->name('reports.index');
        Route::get('/reports/export-pdf', [App\Http\Controllers\Admin\ReportController::class, 'exportPdf'])
            ->middleware('permission:tenant.reports.export')
            ->name('reports.export.pdf');
        Route::get('/reports/export-csv', [App\Http\Controllers\Admin\ReportController::class, 'exportCsv'])
            ->middleware('permission:tenant.reports.export')
            ->name('reports.export.csv');
        Route::get('/reports/data', [App\Http\Controllers\Admin\ReportController::class, 'getData'])
            ->middleware('permission:tenant.reports.view')
            ->name('reports.data');

        // Clearance Management (Tenant Admin)
        Route::get('/clearances', [App\Http\Controllers\Staff\ClearanceController::class, 'index'])
            ->middleware('permission:tenant.clearances.view')
            ->name('clearances.index');
        Route::get('/clearances/create', [App\Http\Controllers\Staff\ClearanceController::class, 'create'])
            ->middleware('permission:tenant.clearances.create')
            ->name('clearances.create');
        Route::post('/clearances', [App\Http\Controllers\Staff\ClearanceController::class, 'store'])
            ->middleware('permission:tenant.clearances.create')
            ->name('clearances.store');
        Route::post('/clearances/{clearance}/checklist/{item}', [App\Http\Controllers\Staff\ClearanceController::class, 'updateChecklistItem'])
            ->middleware('permission:tenant.clearances.update')
            ->name('clearances.checklist.update');
        Route::post('/clearances/{clearance}/approve', [App\Http\Controllers\Staff\ClearanceController::class, 'approve'])
            ->middleware('permission:tenant.clearances.update')
            ->name('clearances.approve');
        Route::post('/clearances/{clearance}/reject', [App\Http\Controllers\Staff\ClearanceController::class, 'reject'])
            ->middleware('permission:tenant.clearances.update')
            ->name('clearances.reject');
        Route::post('/clearances/bulk-approve', [App\Http\Controllers\Staff\ClearanceController::class, 'bulkApprove'])
            ->middleware('permission:tenant.clearances.update')
            ->name('clearances.bulk-approve');
        Route::get('/clearances/export', [App\Http\Controllers\Staff\ClearanceController::class, 'export'])
            ->middleware('permission:tenant.clearances.export')
            ->name('clearances.export');
    });

    // Staff Routes
    Route::middleware(['role:staff'])->prefix('staff')->name('staff.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [App\Http\Controllers\Staff\DashboardController::class, 'index'])->name('dashboard');

        // Staff-scoped aliases for sidebar modules (keep URL under /staff)
        Route::get('/plan-requests', [App\Http\Controllers\Admin\PlanRequestController::class, 'index'])
            ->middleware('permission:tenant.plan_requests.view')
            ->name('plan-requests.index');

        Route::get('/colleges', [App\Http\Controllers\Admin\CollegeController::class, 'index'])
            ->middleware('permission:tenant.colleges.manage')
            ->name('colleges.index');

        Route::get('/departments', [App\Http\Controllers\Admin\DepartmentController::class, 'index'])
            ->middleware('permission:tenant.departments.manage')
            ->name('departments.index');

        Route::get('/students', [App\Http\Controllers\Admin\UserController::class, 'students'])
            ->middleware('permission:tenant.students.manage')
            ->name('students.index');

        Route::get('/staff', [App\Http\Controllers\Admin\UserController::class, 'staff'])
            ->middleware('permission:tenant.staff.manage')
            ->name('staff.index');
        Route::get('/staff/create', [App\Http\Controllers\Admin\UserController::class, 'createStaff'])
            ->middleware('permission:tenant.staff.manage')
            ->name('staff.create');

        Route::get('/reports', [App\Http\Controllers\Admin\ReportController::class, 'index'])
            ->middleware('permission:tenant.reports.view')
            ->name('reports.index');

        Route::get('/clearances', [App\Http\Controllers\Staff\ClearanceController::class, 'index'])
            ->middleware('permission:tenant.clearances.view')
            ->name('clearances.index');
        Route::get('/clearances/export', [App\Http\Controllers\Staff\ClearanceController::class, 'export'])
            ->middleware('permission:tenant.clearances.export')
            ->name('clearances.export');
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