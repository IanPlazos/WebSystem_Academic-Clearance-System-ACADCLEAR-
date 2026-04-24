@extends('layouts.app')

@section('content')
@php
    $canManageColleges = auth()->user()->hasPermission('tenant.colleges.manage');
@endphp
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Colleges</h1>
    @if($canManageColleges)
        <a href="{{ route('admin.colleges.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New College
        </a>
    @endif
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h6 class="m-0 font-weight-bold text-primary mb-0">All Colleges</h6>
        <form action="{{ route('admin.colleges.index') }}" method="GET" class="d-flex flex-wrap align-items-center gap-2">
            <div class="input-group input-group-sm" style="max-width: 220px;">
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Search by name..." 
                       value="{{ request('search') }}">
                <div class="input-group-append">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            <select name="sort" class="form-control form-control-sm" style="width: auto;" onchange="this.form.submit()">
                <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>Name (Z-A)</option>
                <option value="departments_desc" {{ request('sort') === 'departments_desc' ? 'selected' : '' }}>Most departments</option>
                <option value="departments_asc" {{ request('sort') === 'departments_asc' ? 'selected' : '' }}>Fewest departments</option>
                <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Newest first</option>
                <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest first</option>
            </select>
            @if(request()->hasAny(['search', 'sort']))
                <a href="{{ route('admin.colleges.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
            @endif
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>College Name</th>
                        <th>Departments</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($colleges as $college)
                    <tr>
                        <td>{{ $college->id }}</td>
                        <td>{{ $college->name }}</td>
                        <td>
                            <span class="badge bg-info">{{ $college->departments_count }}</span>
                        </td>
                        <td>{{ $college->created_at->format('M d, Y') }}</td>
                        <td>
                            @if($canManageColleges)
                                <a href="{{ route('admin.colleges.edit', $college) }}" 
                                   class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form action="{{ route('admin.colleges.destroy', $college) }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" 
                                            onclick="return confirm('Are you sure? This will also delete all departments in this college.')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            @else
                                <span class="text-muted">No actions</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">No colleges found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection