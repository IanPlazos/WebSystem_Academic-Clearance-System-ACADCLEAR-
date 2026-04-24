

@extends('layouts.app')

@section('content')
@php
    $canManageStaff = auth()->user()->hasPermission('tenant.staff.manage');
@endphp
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Staff Members</h1>
    @if($canManageStaff)
        <a href="{{ route('admin.staff.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Staff
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

@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        {{ session('warning') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">All Staff</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>College</th>
                        <th>Department</th>
                        <th>Office Role</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staff as $member)
                    <tr>
                        <td>{{ $member->id }}</td>
                        <td>{{ $member->name }}</td>
                        <td>{{ $member->email }}</td>
                        <td>{{ $member->college->name ?? 'N/A' }}</td>
                        <td>{{ $member->department->name ?? 'N/A' }}</td>
                        <td>{{ $member->office_role_label ?? 'Not set' }}</td>
                        <td>{{ $member->created_at->format('M d, Y') }}</td>
                        <td>
                            @if($canManageStaff)
                                <a href="{{ route('admin.staff.edit', $member) }}" 
                                   class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.staff.destroy', $member) }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" 
                                            onclick="return confirm('Delete this staff member?')">
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
                        <td colspan="8" class="text-center">No staff found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-center">
            {{ $staff->links() }}
        </div>
    </div>
</div>
@endsection