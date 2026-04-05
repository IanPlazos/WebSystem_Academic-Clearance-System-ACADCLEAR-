@extends('super-admin.layouts.app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800">Plan Requests</h1>
        <p class="mb-0 text-muted">Requests submitted from the tenant landing page.</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card shadow h-100 py-2 border-left-primary">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">All</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $counts['all'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card shadow h-100 py-2 border-left-warning">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $counts['pending'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card shadow h-100 py-2 border-left-success">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Approved</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $counts['approved'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card shadow h-100 py-2 border-left-danger">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Rejected</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $counts['rejected'] }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <h6 class="m-0 font-weight-bold text-primary">Plan Request Queue</h6>
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
                        <th>University</th>
                        <th>Plan</th>
                        <th>Contact</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($planRequests as $planRequest)
                        <tr>
                            <td>{{ $planRequest->created_at->format('M d, Y h:i A') }}</td>
                            <td>
                                <strong>{{ $planRequest->institution_name }}</strong>
                                <div class="text-muted small">{{ $planRequest->tenant_name ?: $planRequest->tenant_slug ?: 'N/A' }}</div>
                            </td>
                            <td>{{ $planRequest->plan_name }}</td>
                            <td>
                                {{ $planRequest->contact_person }}
                                <div class="text-muted small">{{ $planRequest->contact_number }}</div>
                                <div class="text-muted small">{{ $planRequest->email }}</div>
                            </td>
                            <td>
                                <span class="badge badge-info text-uppercase">{{ str_replace('_', ' ', $planRequest->payment_method) }}</span>
                                <div class="small text-muted mt-1">{{ $planRequest->amount }}</div>
                                @if($planRequest->payment_reference)
                                    <div class="small mt-1">Ref: {{ $planRequest->payment_reference }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $planRequest->status === 'approved' ? 'success' : ($planRequest->status === 'rejected' ? 'danger' : 'warning') }} text-uppercase">
                                    {{ $planRequest->status }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @if($planRequest->status !== 'approved')
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-success"
                                            data-bs-toggle="modal"
                                            data-bs-target="#approveModal{{ $planRequest->id }}"
                                        >
                                            Approve
                                        </button>
                                    @endif
                                    @if($planRequest->status !== 'rejected')
                                        <form action="{{ route('super-admin.plan-requests.reject', $planRequest) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @if($planRequest->notes)
                            <tr>
                                <td colspan="7" class="bg-light small text-muted">
                                    <strong>Notes:</strong> {{ $planRequest->notes }}
                                </td>
                            </tr>
                        @endif

                        @if($planRequest->status !== 'approved')
                            <div class="modal fade" id="approveModal{{ $planRequest->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('super-admin.plan-requests.approve', $planRequest) }}" method="POST">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Approve Request: {{ $planRequest->institution_name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p class="mb-3 text-muted small">
                                                    Enter the domain. On approval, the system will automatically create tenant database access,
                                                    subscription, and send credentials to {{ $planRequest->email }}.
                                                </p>

                                                <div class="mb-3">
                                                    <label class="form-label">Domain <span class="text-danger">*</span></label>
                                                    <input
                                                        type="text"
                                                        name="domain"
                                                        class="form-control"
                                                        placeholder="example-university.localhost"
                                                        required
                                                    >
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-success">Confirm Approval</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No plan requests found.</td>
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
