@extends('super-admin.layouts.app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Super Admin Dashboard</h1>
    <a href="{{ route('super-admin.tenants.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New University
    </a>
</div>

<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Universities</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_tenants'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-university fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Active</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active_tenants'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Suspended</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['suspended_tenants'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-pause-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Monthly Revenue</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">₱{{ number_format($stats['monthly_revenue'], 2) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-money-bill fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Recently Added Universities</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Domain</th>
                                <th>Status</th>
                                <th>Added</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recent_tenants as $tenant)
                            <tr>
                                <td>{{ $tenant->name }}</td>
                                <td>{{ $tenant->domain }}</td>
                                <td>
                                    <span class="badge bg-{{ $tenant->status == 'active' ? 'success' : 'warning' }}">
                                        {{ ucfirst($tenant->status) }}
                                    </span>
                                </td>
                                <td>{{ $tenant->created_at->diffForHumans() }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-warning">Expiring Soon (7 Days)</h6>
            </div>
            <div class="card-body">
                @forelse($expiring_soon as $sub)
                    <div class="alert alert-warning">
                        <strong>{{ $sub->tenant->name }}</strong> - 
                        Expires {{ $sub->ends_at->format('M d, Y') }}
                    </div>
                @empty
                    <p class="text-muted mb-0">No subscriptions expiring soon.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection