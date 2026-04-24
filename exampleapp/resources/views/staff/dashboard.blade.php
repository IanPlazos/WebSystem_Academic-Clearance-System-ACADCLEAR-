@extends('layouts.app')

@section('content')
@php
    $user = auth()->user();
    $canViewClearances = $user->hasPermission('tenant.clearances.view');
@endphp
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Staff Dashboard</h1>
    <span class="btn btn-outline-secondary disabled" aria-disabled="true">
        <i class="fas fa-info-circle"></i> Clearancing managed by Tenant Admin
    </span>
</div>

@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        {{ session('warning') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@php
    $staffModules = [
        ['permission' => 'tenant.plan_requests.view', 'label' => 'Plan Requests', 'icon' => 'fa-clipboard-list', 'route' => 'staff.plan-requests.index'],
        ['permission' => 'tenant.colleges.manage', 'label' => 'Colleges', 'icon' => 'fa-university', 'route' => 'staff.colleges.index'],
        ['permission' => 'tenant.departments.manage', 'label' => 'Departments', 'icon' => 'fa-building', 'route' => 'staff.departments.index'],
        ['permission' => 'tenant.students.manage', 'label' => 'Students', 'icon' => 'fa-users', 'route' => 'staff.students.index'],
        ['permission' => 'tenant.staff.manage', 'label' => 'Staff', 'icon' => 'fa-user-tie', 'route' => 'staff.staff.index'],
        ['permission' => 'tenant.reports.view', 'label' => 'Reports', 'icon' => 'fa-file-alt', 'route' => 'staff.reports.index'],
        ['permission' => 'tenant.clearances.view', 'label' => 'Clearances', 'icon' => 'fa-list', 'route' => 'staff.clearances.index'],
        ['permission' => 'tenant.profile.manage', 'label' => 'Profile Settings', 'icon' => 'fa-cog', 'route' => 'profile.edit'],
    ];

    $visibleStaffModules = collect($staffModules)->filter(function ($module) use ($user) {
        return $user->hasPermission($module['permission']);
    })->values();
@endphp

@if($visibleStaffModules->isNotEmpty())
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Your Module Access</h6>
    </div>
    <div class="card-body">
        <div class="row">
            @foreach($visibleStaffModules as $module)
                <div class="col-xl-3 col-md-4 col-sm-6 mb-3">
                    <a href="{{ route($module['route']) }}" class="text-decoration-none">
                        <div class="card border-left-primary h-100 shadow-sm">
                            <div class="card-body py-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="font-weight-bold text-primary small text-uppercase">{{ $module['label'] }}</span>
                                    <i class="fas {{ $module['icon'] }} text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<!-- Statistics Cards -->
@if($canViewClearances)
<div class="row">
    <!-- Total Clearances -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Clearances</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-file-signature fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Clearances -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Pending</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pending'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <span class="small text-muted">Tenant Admin manages approvals</span>
            </div>
        </div>
    </div>

    <!-- Approved Clearances -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Approved</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['approved'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <span class="small text-muted">Tenant Admin manages approvals</span>
            </div>
        </div>
    </div>

    <!-- Rejected Clearances -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Rejected</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['rejected'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <span class="small text-muted">Tenant Admin manages approvals</span>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Recent Clearances -->
@if($canViewClearances)
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Recent Clearance Requests</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Checklist Item</th>
                                <th>Status</th>
                                <th>Request Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentClearances as $clearance)
                            @php
                                $roleItem = $clearance->checklistItems->first();
                                $displayStatus = $roleItem->status ?? $clearance->status;
                                $badgeClass = [
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    'pending' => 'warning'
                                ][$displayStatus] ?? 'secondary';
                            @endphp
                            <tr>
                                <td>{{ $clearance->student->name }}</td>
                                <td>
                                    {{ $roleItem->item_name ?? ($clearance->clearance_title ?? 'Assigned Item') }}
                                </td>
                                <td>
                                    <span class="badge bg-{{ $badgeClass }}">
                                        {{ ucfirst($displayStatus) }}
                                    </span>
                                </td>
                                <td>{{ $clearance->created_at->diffForHumans() }}</td>
                                <td>
                                    <span class="text-muted">Managed by admin</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">No recent clearances</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Department Info -->
@if($canViewClearances)
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Your Department Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Department:</strong> {{ auth()->user()->department->name ?? 'N/A' }}</p>
                        <p><strong>College:</strong> {{ auth()->user()->college->name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Total Students in Department:</strong> 
                            {{ \App\Models\Clearance::where('department_id', auth()->user()->department_id)
                                ->distinct('student_id')->count('student_id') }}
                        </p>
                        <p><strong>Completion Rate:</strong> 
                            @php
                                $total = $stats['total'];
                                $approved = $stats['approved'];
                                $rate = $total > 0 ? round(($approved / $total) * 100, 2) : 0;
                            @endphp
                            {{ $rate }}%
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if(!$canViewClearances)
<div class="card shadow mb-4">
    <div class="card-body text-center py-5">
        <i class="fas fa-shield-alt fa-2x text-gray-400 mb-3"></i>
        <p class="mb-0 text-muted">Clearance analytics are hidden because this module is not assigned to your account.</p>
    </div>
</div>
@endif
@endsection