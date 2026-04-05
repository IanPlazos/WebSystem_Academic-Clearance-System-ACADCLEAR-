@extends('layouts.app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800">Plan Requests</h1>
        <p class="mb-0 text-muted">Review submissions from the public landing page.</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">All Requests</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $counts['all'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $counts['pending'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Approved</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $counts['approved'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Rejected</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $counts['rejected'] }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Requests</h6>
        <form method="GET" class="mt-3 mt-md-0">
            <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="">All statuses</option>
                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Plan</th>
                        <th>Institution</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Payment</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($planRequests as $requestItem)
                        <tr>
                            <td>{{ $requestItem->created_at->format('M d, Y h:i A') }}</td>
                            <td>{{ $requestItem->plan_name }}</td>
                            <td>
                                <strong>{{ $requestItem->institution_name }}</strong>
                                @if ($requestItem->tenant_name)
                                    <div class="text-muted small">{{ $requestItem->tenant_name }}</div>
                                @endif
                            </td>
                            <td>{{ $requestItem->contact_person }}<br><span class="text-muted small">{{ $requestItem->contact_number }}</span></td>
                            <td>{{ $requestItem->email }}</td>
                            <td>
                                <span class="badge badge-info text-uppercase">{{ str_replace('_', ' ', $requestItem->payment_method) }}</span>
                                <div class="small text-muted mt-1">{{ $requestItem->amount }}</div>
                            </td>
                            <td>
                                <span class="badge badge-{{ $requestItem->status === 'approved' ? 'success' : ($requestItem->status === 'rejected' ? 'danger' : 'warning') }} text-uppercase">
                                    {{ $requestItem->status }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="7" class="bg-light">
                                <div class="row small text-muted">
                                    <div class="col-md-4"><strong>Reference:</strong> {{ $requestItem->payment_reference ?: 'N/A' }}</div>
                                    <div class="col-md-4"><strong>GCash:</strong> {{ $requestItem->gcash_number ?: 'N/A' }}</div>
                                    <div class="col-md-4"><strong>Bank:</strong> {{ $requestItem->bank_name ?: 'N/A' }}</div>
                                </div>
                                @if ($requestItem->notes)
                                    <div class="mt-2 small"><strong>Notes:</strong> {{ $requestItem->notes }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No plan requests yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-3">
            {{ $planRequests->links() }}
        </div>
    </div>
</div>
@endsection
