
@extends('super-admin.layouts.app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Create New Subscription</h1>
    <a href="{{ route('super-admin.subscriptions.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Subscription Information</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('super-admin.subscriptions.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">University *</label>
                    <select name="tenant_id" class="form-select @error('tenant_id') is-invalid @enderror" required>
                        <option value="">Select University</option>
                        @foreach($tenants as $tenant)
                            <option value="{{ $tenant->id }}" {{ old('tenant_id') == $tenant->id ? 'selected' : '' }}>
                                {{ $tenant->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('tenant_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Plan *</label>
                    <select name="plan_id" class="form-select @error('plan_id') is-invalid @enderror" required>
                        <option value="">Select Plan</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                                {{ $plan->name }} - ₱{{ number_format($plan->price, 2) }}/month
                                ({{ $plan->max_students ? 'Up to ' . number_format($plan->max_students) . ' students' : 'Unlimited' }})
                            </option>
                        @endforeach
                    </select>
                    @error('plan_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Start Date *</label>
                    <input type="date" name="starts_at" class="form-control @error('starts_at') is-invalid @enderror" 
                           value="{{ old('starts_at', date('Y-m-d')) }}" required>
                    @error('starts_at')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">End Date *</label>
                    <input type="date" name="ends_at" class="form-control @error('ends_at') is-invalid @enderror" 
                           value="{{ old('ends_at', date('Y-m-d', strtotime('+1 month'))) }}" required>
                    @error('ends_at')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Amount Paid (₱) *</label>
                    <input type="number" name="amount_paid" class="form-control @error('amount_paid') is-invalid @enderror" 
                           value="{{ old('amount_paid') }}" required step="0.01">
                    @error('amount_paid')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Payment Method *</label>
                    <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                        <option value="">Select Payment Method</option>
                        <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="credit_card" {{ old('payment_method') == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                        <option value="gcash" {{ old('payment_method') == 'gcash' ? 'selected' : '' }}>GCash</option>
                        <option value="paymaya" {{ old('payment_method') == 'paymaya' ? 'selected' : '' }}>PayMaya</option>
                    </select>
                    @error('payment_method')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Transaction ID</label>
                    <input type="text" name="transaction_id" class="form-control" value="{{ old('transaction_id') }}">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Create Subscription
            </button>
        </form>
    </div>
</div>
@endsection