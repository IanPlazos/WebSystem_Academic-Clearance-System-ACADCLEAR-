@extends('super-admin.layouts.app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Subscriptions</h1>
    <a href="#" class="btn btn-primary" onclick="alert('This feature is coming soon!')">
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
                    @php
                        $subscriptions = App\Models\Subscription::with(['tenant', 'plan'])->latest()->take(10)->get();
                    @endphp
                    
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
                            <a href="#" class="btn btn-sm btn-info" onclick="alert('View subscription details coming soon!')">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($sub->status == 'active')
                            <a href="#" class="btn btn-sm btn-warning" onclick="alert('Renew subscription coming soon!')">
                                <i class="fas fa-sync"></i>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">No subscriptions found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
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
                    @php
                        $expiring = App\Models\Subscription::with(['tenant', 'plan'])
                            ->where('status', 'active')
                            ->where('ends_at', '<=', now()->addDays(30))
                            ->where('ends_at', '>=', now())
                            ->get();
                    @endphp
                    
                    @forelse($expiring as $sub)
                    <tr>
                        <td>{{ $sub->tenant->name }}</td>
                        <td>{{ $sub->plan->name }}</td>
                        <td>{{ now()->diffInDays($sub->ends_at) }} days</td>
                        <td class="text-danger">{{ $sub->ends_at->format('M d, Y') }}</td>
                        <td>
                            <a href="#" class="btn btn-sm btn-primary" onclick="alert('Renewal feature coming soon!')">
                                <i class="fas fa-sync"></i> Renew
                            </a>
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
    </div>
</div>
@endsection