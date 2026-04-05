@extends('super-admin.layouts.app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Universities Management</h1>
    <a href="{{ route('super-admin.tenants.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add University
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Filter</h6>
    </div>
    <div class="card-body">
        <form method="GET" class="row">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" 
                       placeholder="Search name or domain" value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('super-admin.tenants.index') }}" class="btn btn-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">All Universities</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                     <tr>
                        <th>ID</th>
                        <th>University</th>
                        <th>Domain</th>
                        <th>Login Link</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tenants as $tenant)
                    <tr>
                        <td>{{ $tenant->id }}</td>
                        <td><strong>{{ $tenant->name }}</strong></td>
                        <td>{{ $tenant->domain }}</td>
                        <td>
                            <a href="http://{{ $tenant->slug }}.localhost:8000" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-external-link-alt"></i> Login
                            </a>
                        </td>
                        <td>
                            @if($tenant->activeSubscription)
                                <span class="badge bg-info">{{ $tenant->activeSubscription->plan->name }}</span>
                            @else
                                <span class="badge bg-secondary">No Plan</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $tenant->status == 'active' ? 'success' : 'warning' }}">
                                {{ ucfirst($tenant->status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('super-admin.tenants.show', $tenant) }}" 
                               class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('super-admin.tenants.edit', $tenant) }}" 
                               class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('super-admin.tenants.toggle-status', $tenant) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-{{ $tenant->status == 'active' ? 'warning' : 'success' }}">
                                    <i class="fas fa-{{ $tenant->status == 'active' ? 'pause' : 'play' }}"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center">
            {{ $tenants->links() }}
        </div>
    </div>
</div>
@endsection