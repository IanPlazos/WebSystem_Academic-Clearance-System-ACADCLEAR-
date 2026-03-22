@extends('super-admin.layouts.app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Payments</h1>
    <a href="{{ route('super-admin.payments.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Record Payment
    </a>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Revenue</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">₱{{ number_format($stats['total_revenue'], 2) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line fa-2x text-gray-300"></i>
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
                            Monthly Revenue</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">₱{{ number_format($stats['monthly_revenue'], 2) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
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
                            Yearly Revenue</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">₱{{ number_format($stats['yearly_revenue'], 2) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-bar fa-2x text-gray-300"></i>
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
                            Pending Payments</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pending_payments'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Filter Payments</h6>
    </div>
    <div class="card-body">
        <form method="GET" class="row">
            <div class="col-md-3">
                <select name="tenant_id" class="form-select">
                    <option value="">All Universities</option>
                    @foreach($tenants as $tenant)
                        <option value="{{ $tenant->id }}" {{ request('tenant_id') == $tenant->id ? 'selected' : '' }}>
                            {{ $tenant->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="From">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" placeholder="To">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
            <div class="col-md-1">
                <a href="{{ route('super-admin.payments.index') }}" class="btn btn-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Payments Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Payment History</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>University</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th>Reference #</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr>
                        <td>#{{ $payment->id }}</td>
                        <td>{{ $payment->tenant->name }}</td>
                        <td class="font-weight-bold">₱{{ number_format($payment->amount, 2) }}</td>
                        <td>{{ strtoupper(str_replace('_', ' ', $payment->payment_method)) }}</td>
                        <td>{{ $payment->reference_number ?? 'N/A' }}</td>
                        <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                        <td>
                            <span class="badge bg-{{ 
                                $payment->status == 'completed' ? 'success' : 
                                ($payment->status == 'pending' ? 'warning' : 
                                ($payment->status == 'failed' ? 'danger' : 'secondary')) 
                            }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('super-admin.payments.show', $payment) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('super-admin.payments.receipt', $payment) }}" class="btn btn-sm btn-secondary" target="_blank">
                                <i class="fas fa-print"></i>
                            </a>
                         </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">No payments found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center">
            {{ $payments->links() }}
        </div>
    </div>
</div>
@endsection