@extends('super-admin.layouts.app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">University Details</h1>
    <a href="{{ route('super-admin.tenants.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">University Information</h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Name:</strong>
                    </div>
                    <div class="col-md-8">
                        {{ $tenant->name }}
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Slug:</strong>
                    </div>
                    <div class="col-md-8">
                        {{ $tenant->slug }}
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Domain:</strong>
                    </div>
                    <div class="col-md-8">
                        {{ $tenant->domain }}
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Database:</strong>
                    </div>
                    <div class="col-md-8">
                        <code>{{ $tenant->database }}</code>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Login Link:</strong>
                    </div>
                    <div class="col-md-8">
                        <div class="input-group">
                            <input type="text" class="form-control form-control-sm" id="loginUrl" value="http://{{ $tenant->slug }}.localhost:8000" readonly>
                            <button class="btn btn-outline-primary btn-sm" type="button" onclick="copyToClipboard('loginUrl')">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                            <a href="http://{{ $tenant->slug }}.localhost:8000" target="_blank" class="btn btn-primary btn-sm">
                                <i class="fas fa-external-link-alt"></i> Open
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Admin Email:</strong>
                    </div>
                    <div class="col-md-8">
                        <code>{{ $tenant->settings['admin_email'] ?? 'N/A' }}</code>
                    </div>
                </div>
                    <div class="col-md-8">
                        <span class="badge bg-{{ $tenant->status == 'active' ? 'success' : 'warning' }}">
                            {{ ucfirst($tenant->status) }}
                        </span>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Created:</strong>
                    </div>
                    <div class="col-md-8">
                        {{ $tenant->created_at->format('F d, Y H:i:s') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Current Subscription</h6>
            </div>
            <div class="card-body">
                @if($currentSubscription)
                    <div class="mb-3">
                        <strong>Plan:</strong> {{ $currentSubscription->plan->name }}
                    </div>
                    <div class="mb-3">
                        <strong>Starts:</strong> {{ $currentSubscription->starts_at->format('M d, Y') }}
                    </div>
                    <div class="mb-3">
                        <strong>Ends:</strong> {{ $currentSubscription->ends_at->format('M d, Y') }}
                    </div>
                    <div class="mb-3">
                        <strong>Status:</strong> 
                        <span class="badge bg-success">Active</span>
                    </div>
                @else
                    <p class="text-muted">No active subscription</p>
                @endif
            </div>
        </div>
        
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
            </div>
            <div class="card-body">
                <a href="{{ route('super-admin.tenants.edit', $tenant) }}" class="btn btn-warning btn-block w-100 mb-2">
                    <i class="fas fa-edit"></i> Edit University
                </a>
                
                <form action="{{ route('super-admin.tenants.toggle-status', $tenant) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-{{ $tenant->status == 'active' ? 'warning' : 'success' }} btn-block w-100 mb-2">
                        <i class="fas fa-{{ $tenant->status == 'active' ? 'pause' : 'play' }}"></i>
                        {{ $tenant->status == 'active' ? 'Suspend' : 'Activate' }} University
                    </button>
                </form>

                <form action="{{ route('super-admin.tenants.destroy', $tenant) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this university? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block w-100">
                        <i class="fas fa-trash"></i> Delete University
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Subscription History</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Plan</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subscriptionHistory as $sub)
                            <tr>
                                <td>{{ $sub->plan->name }}</td>
                                <td>{{ $sub->starts_at->format('M d, Y') }}</td>
                                <td>{{ $sub->ends_at->format('M d, Y') }}</td>
                                <td>₱{{ number_format($sub->amount_paid, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $sub->status == 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($sub->status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">No subscription history</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    const text = element.value;
    
    navigator.clipboard.writeText(text).then(() => {
        const btn = event.target.closest('button');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        btn.classList.add('btn-success');
        btn.classList.remove('btn-outline-primary');
        
        setTimeout(() => {
            btn.innerHTML = originalHtml;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-primary');
        }, 2000);
    });
}
</script>
@endsection