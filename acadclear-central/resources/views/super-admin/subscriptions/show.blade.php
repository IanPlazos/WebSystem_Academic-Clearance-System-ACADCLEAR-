@extends('super-admin.layouts.app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Subscription Details</h1>
    <div>
        <a href="{{ route('super-admin.subscriptions.edit', $subscription) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="{{ route('super-admin.subscriptions.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Subscription Information</h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Subscription ID:</strong>
                    </div>
                    <div class="col-md-8">
                        #{{ $subscription->id }}
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>University:</strong>
                    </div>
                    <div class="col-md-8">
                        <a href="{{ route('super-admin.tenants.show', $subscription->tenant) }}">
                            {{ $subscription->tenant->name }}
                        </a>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Plan:</strong>
                    </div>
                    <div class="col-md-8">
                        {{ $subscription->plan->name }}
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Amount Paid:</strong>
                    </div>
                    <div class="col-md-8">
                        ₱{{ number_format($subscription->amount_paid, 2) }}
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Payment Method:</strong>
                    </div>
                    <div class="col-md-8">
                        {{ strtoupper(str_replace('_', ' ', $subscription->payment_method)) }}
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Transaction ID:</strong>
                    </div>
                    <div class="col-md-8">
                        {{ $subscription->transaction_id ?? 'N/A' }}
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Start Date:</strong>
                    </div>
                    <div class="col-md-8">
                        {{ $subscription->starts_at->format('F d, Y') }}
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>End Date:</strong>
                    </div>
                    <div class="col-md-8">
                        {{ $subscription->ends_at->format('F d, Y') }}
                        @if($subscription->ends_at->isPast())
                            <span class="badge bg-danger">Expired</span>
                        @elseif($subscription->ends_at->diffInDays(now()) <= 30)
                            <span class="badge bg-warning">Expiring Soon ({{ $subscription->ends_at->diffInDays(now()) }} days)</span>
                        @else
                            <span class="badge bg-success">Active</span>
                        @endif
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Status:</strong>
                    </div>
                    <div class="col-md-8">
                        <span class="badge bg-{{ $subscription->status == 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($subscription->status) }}
                        </span>
                    </div>
                </div>
                
                @if($subscription->meta && json_decode($subscription->meta, true)['notes'] ?? false)
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Notes:</strong>
                    </div>
                    <div class="col-md-8">
                        {{ json_decode($subscription->meta, true)['notes'] }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
            </div>
            <div class="card-body">
                @if($subscription->status == 'active')
                <button class="btn btn-primary btn-block w-100 mb-2" type="button" data-bs-toggle="modal" data-bs-target="#renewModal">
                    <i class="fas fa-sync"></i> Renew Subscription
                </button>
                @endif
                
                <a href="{{ route('super-admin.tenants.show', $subscription->tenant) }}" class="btn btn-info btn-block w-100 mb-2">
                    <i class="fas fa-building"></i> View University
                </a>
                
                <button class="btn btn-danger btn-block w-100" onclick="confirmDelete()">
                    <i class="fas fa-trash"></i> Delete Record
                </button>
                <form id="delete-form" action="{{ route('super-admin.subscriptions.destroy', $subscription) }}" method="POST" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>
        
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Plan Features</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    @foreach(json_decode($subscription->plan->features ?? '[]') as $feature)
                        <li><i class="fas fa-check text-success"></i> {{ $feature }}</li>
                    @endforeach
                    
                    @if($subscription->plan->has_advanced_reports)
                        <li><i class="fas fa-check text-success"></i> Advanced Reports & Analytics</li>
                    @endif
                    
                    @if($subscription->plan->has_multi_campus)
                        <li><i class="fas fa-check text-success"></i> Multi-Campus Support</li>
                    @endif
                    
                    @if($subscription->plan->has_custom_branding)
                        <li><i class="fas fa-check text-success"></i> Custom Branding</li>
                    @endif
                    
                    @if($subscription->plan->has_api_access)
                        <li><i class="fas fa-check text-success"></i> API Access</li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Renew Modal -->
<div class="modal fade" id="renewModal" tabindex="-1" role="dialog" aria-labelledby="renewModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('super-admin.subscriptions.renew', $subscription) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="renewModalLabel">Renew Subscription</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Plan</label>
                        <select name="plan_id" class="form-select" required>
                            @foreach(App\Models\Plan::all() as $plan)
                                <option value="{{ $plan->id }}" {{ $subscription->plan_id == $plan->id ? 'selected' : '' }}>
                                    {{ $plan->name }} - ₱{{ number_format($plan->price, 2) }}/month
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Months</label>
                        <select name="months" class="form-select" required>
                            <option value="1">1 Month</option>
                            <option value="3">3 Months (5% discount)</option>
                            <option value="6">6 Months (10% discount)</option>
                            <option value="12">12 Months (15% discount)</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Amount Paid (₱)</label>
                        <input type="number" name="amount_paid" class="form-control" required step="0.01">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="gcash">GCash</option>
                            <option value="paymaya">PayMaya</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Renew Subscription</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function confirmDelete() {
        if(confirm('Are you sure you want to delete this subscription record? This action cannot be undone.')) {
            document.getElementById('delete-form').submit();
        }
    }
</script>
@endpush
@endsection