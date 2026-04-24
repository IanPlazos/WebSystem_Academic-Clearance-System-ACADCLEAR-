@extends('layouts.app')

@section('content')
@php
    $canManageColleges = auth()->user()->hasPermission('tenant.colleges.manage');
    $canManageDepartments = auth()->user()->hasPermission('tenant.departments.manage');
    $canManageStudents = auth()->user()->hasPermission('tenant.students.manage');
    $canManageStaff = auth()->user()->hasPermission('tenant.staff.manage');
    $canViewReports = auth()->user()->hasPermission('tenant.reports.view');
@endphp
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Admin Dashboard</h1>
</div>

<!-- Statistics Cards -->
<div class="row">
    <!-- Colleges Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Colleges</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ \App\Models\College::count() }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-university fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                @if($canManageColleges)
                    <a href="{{ route('admin.colleges.index') }}" class="small">Manage Colleges →</a>
                @else
                    <span class="small text-muted">Not allowed</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Departments Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Departments</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ \App\Models\Department::count() }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-building fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                @if($canManageDepartments)
                    <a href="{{ route('admin.departments.index') }}" class="small">Manage Departments →</a>
                @else
                    <span class="small text-muted">Not allowed</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Students Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Total Students</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ \App\Models\User::where('role', 'student')->count() }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                @if($canManageStudents)
                    <a href="{{ route('admin.students.index') }}" class="small">Manage Students →</a>
                @else
                    <span class="small text-muted">Not allowed</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Staff Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Total Staff</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ \App\Models\User::where('role', 'staff')->count() }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-tie fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                @if($canManageStaff)
                    <a href="{{ route('admin.staff.index') }}" class="small">Manage Staff →</a>
                @else
                    <span class="small text-muted">Not allowed</span>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
            </div>
            <div class="card-body">
                @if($canManageColleges)
                    <a href="{{ route('admin.colleges.create') }}" class="btn btn-primary mb-2">
                        <i class="fas fa-plus"></i> Add College
                    </a>
                @endif
                @if($canManageDepartments)
                    <a href="{{ route('admin.departments.create') }}" class="btn btn-success mb-2">
                        <i class="fas fa-plus"></i> Add Department
                    </a>
                @endif
                @if($canManageStudents)
                    <a href="{{ route('admin.students.create') }}" class="btn btn-info mb-2">
                        <i class="fas fa-user-plus"></i> Add Student
                    </a>
                @endif
                @if($canManageStaff)
                    <a href="{{ route('admin.staff.create') }}" class="btn btn-warning mb-2">
                        <i class="fas fa-user-tie"></i> Add Staff
                    </a>
                @endif
                @if($canViewReports)
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-danger mb-2">
                        <i class="fas fa-chart-bar"></i> View Reports
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Recent Students -->
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Recently Added Students</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>College</th>
                                <th>Added</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(\App\Models\User::where('role', 'student')->latest()->take(5)->get() as $student)
                            <tr>
                                <td>{{ $student->name }}</td>
                                <td>{{ $student->email }}</td>
                                <td>{{ $student->college->name ?? 'N/A' }}</td>
                                <td>{{ $student->created_at->diffForHumans() }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection