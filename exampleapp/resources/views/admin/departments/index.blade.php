@extends('layouts.app')

@section('content')
@php
    $canManageDepartments = auth()->user()->hasPermission('tenant.departments.manage');
@endphp
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Departments</h1>
    @if($canManageDepartments)
        <a href="{{ route('admin.departments.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Department
        </a>
    @endif
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">All Departments</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Department Name</th>
                        <th>College</th>
                        <th>Staff Count</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments as $department)
                    <tr>
                        <td>{{ $department->id }}</td>
                        <td>{{ $department->name }}</td>
                        <td>{{ $department->college->name }}</td>
                        <td>
                            <span class="badge badge-info">{{ $department->staff_count }}</span>
                        </td>
                        <td>{{ $department->created_at->format('M d, Y') }}</td>
                        <td>
                            @if($canManageDepartments)
                                <a href="{{ route('admin.departments.edit', $department) }}" 
                                   class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.departments.destroy', $department) }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" 
                                            onclick="return confirm('Delete this department? This action cannot be undone.')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @else
                                <span class="text-muted">No actions</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">No departments found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection