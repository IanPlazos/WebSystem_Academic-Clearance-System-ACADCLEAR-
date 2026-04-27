@extends('super-admin.layouts.app')

@section('content')
@push('styles')
<style>
    .universities-page .table {
        margin-bottom: 0;
    }

    .universities-page .table thead th {
        white-space: nowrap;
    }

    .universities-page .pagination-wrap {
        margin-top: 1rem;
    }

    .universities-page .pagination-wrap .d-md-flex {
        justify-content: center !important;
        align-items: center !important;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .universities-page .pagination-wrap .small.text-muted {
        color: #b9cde3 !important;
        margin-bottom: 0;
        font-weight: 500;
    }

    .universities-page .pagination {
        margin-bottom: 0;
        gap: 0.3rem;
    }

    .universities-page .pagination .page-item .page-link {
        border-radius: 0.5rem;
        border: 1px solid rgba(124, 157, 195, 0.45);
        background-color: rgba(18, 39, 67, 0.86);
        color: #d3e7ff;
        min-width: 2.2rem;
        text-align: center;
        box-shadow: none;
    }

    .universities-page .pagination .page-item .page-link:hover {
        background-color: rgba(32, 86, 156, 0.7);
        border-color: rgba(80, 159, 255, 0.85);
        color: #ffffff;
    }

    .universities-page .pagination .page-item.active .page-link {
        background: linear-gradient(135deg, #2d7cff, #29a9ff);
        border-color: #2d7cff;
        color: #fff;
        font-weight: 700;
    }

    .universities-page .pagination .page-item.disabled .page-link {
        background-color: rgba(98, 118, 145, 0.35);
        border-color: rgba(124, 157, 195, 0.35);
        color: #8fa5bf;
    }
</style>
@endpush

<div class="universities-page">
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
                <label for="tenant-search" class="visually-hidden">Search universities</label>
                <input type="text" id="tenant-search" name="search" class="form-control"
                       placeholder="Search name or domain" value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label for="tenant-status-filter" class="visually-hidden">Filter by status</label>
                <select id="tenant-status-filter" name="status" class="form-select">
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
        <div class="pagination-wrap">
            {{ $tenants->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
</div>
@endsection
