@extends('super-admin.layouts.app')

@section('content')
@push('styles')
<style>
    .subscriptions-page .pagination-wrap {
        margin-top: 1rem;
    }

    .subscriptions-page .pagination-wrap .d-md-flex {
        justify-content: center !important;
        align-items: center !important;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .subscriptions-page .pagination-wrap .small.text-muted {
        color: #b9cde3 !important;
        margin-bottom: 0;
        font-weight: 500;
    }

    .subscriptions-page .pagination {
        margin-bottom: 0;
        gap: 0.3rem;
    }

    .subscriptions-page .pagination .page-item .page-link {
        border-radius: 0.5rem;
        border: 1px solid rgba(124, 157, 195, 0.45);
        background-color: rgba(18, 39, 67, 0.86);
        color: #d3e7ff;
        min-width: 2.2rem;
        text-align: center;
        box-shadow: none;
    }

    .subscriptions-page .pagination .page-item .page-link:hover {
        background-color: rgba(32, 86, 156, 0.7);
        border-color: rgba(80, 159, 255, 0.85);
        color: #ffffff;
    }

    .subscriptions-page .pagination .page-item.active .page-link {
        background: linear-gradient(135deg, #2d7cff, #29a9ff);
        border-color: #2d7cff;
        color: #fff;
        font-weight: 700;
    }

    .subscriptions-page .pagination .page-item.disabled .page-link {
        background-color: rgba(98, 118, 145, 0.35);
        border-color: rgba(124, 157, 195, 0.35);
        color: #8fa5bf;
    }
</style>
@endpush

<div class="subscriptions-page">
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Subscriptions</h1>
    <a href="{{ route('super-admin.subscriptions.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Subscription
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Active Subscriptions</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>University</th>
                        <th>Plan</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $sub)
                    <tr>
                        <td>{{ $sub->id }}</td>
                        <td>{{ $sub->tenant->name ?? 'N/A' }}</td>
                        <td>{{ $sub->plan->name ?? 'N/A' }}</td>
                        <td>{{ $sub->starts_at->format('M d, Y') }}</td>
                        <td>{{ $sub->ends_at->format('M d, Y') }}</td>
                        <td>₱{{ number_format($sub->amount_paid, 2) }}</td>
                        <td>
                            <span class="badge bg-{{ $sub->status == 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($sub->status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('super-admin.subscriptions.show', $sub) }}" class="btn btn-sm btn-info" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($sub->status == 'active')
                            <button
                                type="button"
                                class="btn btn-sm btn-warning"
                                title="Renew Subscription"
                                data-toggle="modal"
                                data-target="#renewModal{{ $sub->id }}"
                            >
                                <i class="fas fa-sync"></i>
                            </button>
                            @endif
                        </td>
                    </tr>

                    @if($sub->status == 'active')
                    <div class="modal fade" id="renewModal{{ $sub->id }}" tabindex="-1" role="dialog" aria-labelledby="renewModalLabel{{ $sub->id }}" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <form action="{{ route('super-admin.subscriptions.renew', $sub) }}" method="POST">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="renewModalLabel{{ $sub->id }}">Renew Subscription - {{ $sub->tenant->name }}</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label for="renew-plan-{{ $sub->id }}">Plan</label>
                                            <select id="renew-plan-{{ $sub->id }}" name="plan_id" class="form-control" required>
                                                @foreach(App\Models\Plan::all() as $plan)
                                                    <option value="{{ $plan->id }}" {{ $sub->plan_id == $plan->id ? 'selected' : '' }}>
                                                        {{ $plan->name }} - ₱{{ number_format($plan->price, 2) }}/month
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="renew-months-{{ $sub->id }}">Months</label>
                                            <select id="renew-months-{{ $sub->id }}" name="months" class="form-control" required>
                                                <option value="1">1 Month</option>
                                                <option value="3">3 Months</option>
                                                <option value="6">6 Months</option>
                                                <option value="12">12 Months</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="renew-amount-{{ $sub->id }}">Amount Paid (₱)</label>
                                            <input type="number" id="renew-amount-{{ $sub->id }}" name="amount_paid" class="form-control" min="0" step="0.01" value="{{ $sub->plan->price }}" required>
                                        </div>

                                        <div class="form-group mb-0">
                                            <label for="renew-payment-method-{{ $sub->id }}">Payment Method</label>
                                            <select id="renew-payment-method-{{ $sub->id }}" name="payment_method" class="form-control" required>
                                                <option value="cash">Cash</option>
                                                <option value="bank_transfer">Bank Transfer</option>
                                                <option value="credit_card">Credit Card</option>
                                                <option value="gcash">GCash</option>
                                                <option value="paymaya">PayMaya</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Renew Subscription</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endif
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">No subscriptions found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">
            {{ $subscriptions->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-warning">Expiring Soon</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>University</th>
                        <th>Plan</th>
                        <th>Ends In</th>
                        <th>End Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expiring as $sub)
                    <tr>
                        <td>{{ $sub->tenant->name }}</td>
                        <td>{{ $sub->plan->name }}</td>
                        <td>{{ now()->diffInDays($sub->ends_at) }} days</td>
                        <td class="text-danger">{{ $sub->ends_at->format('M d, Y') }}</td>
                        <td>
                            <button
                                type="button"
                                class="btn btn-sm btn-primary"
                                data-toggle="modal"
                                data-target="#renewModal{{ $sub->id }}"
                            >
                                <i class="fas fa-sync"></i> Renew
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">No subscriptions expiring soon.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">
            {{ $expiring->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
</div>
@endsection
