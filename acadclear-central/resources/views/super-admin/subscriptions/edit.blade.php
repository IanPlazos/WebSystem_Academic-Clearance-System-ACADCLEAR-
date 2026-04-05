@extends('super-admin.layouts.app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Subscription #{{ $subscription->id }}</h1>
    <a href="{{ route('super-admin.subscriptions.show', $subscription) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Details
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Subscription Information</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('super-admin.subscriptions.update', $subscription) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">University</label>
                    <input type="text" class="form-control" value="{{ $subscription->tenant->name }}" disabled>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Current Start Date</label>
                    <input type="text" class="form-control" value="{{ optional($subscription->starts_at)->format('M d, Y') }}" disabled>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Plan *</label>
                    <select name="plan_id" class="form-select @error('plan_id') is-invalid @enderror" required>
                        <option value="">Select Plan</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" {{ old('plan_id', $subscription->plan_id) == $plan->id ? 'selected' : '' }}>
                                {{ $plan->name }} - ₱{{ number_format($plan->price, 2) }}/month
                            </option>
                        @endforeach
                    </select>
                    @error('plan_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">End Date *</label>
                    <input
                        type="date"
                        name="ends_at"
                        class="form-control @error('ends_at') is-invalid @enderror"
                        value="{{ old('ends_at', optional($subscription->ends_at)->format('Y-m-d')) }}"
                        required
                    >
                    @error('ends_at')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Status *</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="active" {{ old('status', $subscription->status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="expired" {{ old('status', $subscription->status) == 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="cancelled" {{ old('status', $subscription->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Subscription
            </button>
            <a href="{{ route('super-admin.subscriptions.show', $subscription) }}" class="btn btn-light border">Cancel</a>
        </form>
    </div>
</div>
@endsection
