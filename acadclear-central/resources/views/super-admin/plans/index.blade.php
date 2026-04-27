@extends('super-admin.layouts.app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Pricing Plans</h1>
    <a href="{{ route('super-admin.plans.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Plan
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    @foreach($plans as $plan)
    <div class="col-md-4 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3 bg-{{ 
                $plan->slug == 'basic' ? 'primary' : 
                ($plan->slug == 'standard' ? 'success' : 
                (in_array($plan->slug, ['premium', 'enterprise']) ? 'info' : 'secondary')) 
            }} text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="m-0 font-weight-bold">{{ $plan->name }}</h5>
                    @if($plan->slug == 'standard')
                        <span class="badge bg-warning">POPULAR</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                @if($plan->price == 0)
                    <h2 class="text-{{ $plan->slug == 'basic' ? 'primary' : ($plan->slug == 'standard' ? 'success' : 'info') }}">
                        Custom Pricing
                    </h2>
                @else
                    <h2 class="text-{{ $plan->slug == 'basic' ? 'primary' : ($plan->slug == 'standard' ? 'success' : 'info') }}">
                        ₱{{ number_format($plan->price, 2) }}
                        <span class="small">/month</span>
                    </h2>
                @endif
                
                <p class="text-muted">
                    {{ $plan->max_students ? 'Up to ' . number_format($plan->max_students) . ' students' : 'Unlimited students' }}
                </p>
                <hr>
                <ul class="list-unstyled">
                    @foreach(json_decode($plan->features ?? '[]') as $feature)
                        <li><i class="fas fa-check text-success"></i> {{ $feature }}</li>
                    @endforeach
                    
                    @if($plan->has_advanced_reports)
                        <li><i class="fas fa-check text-success"></i> Advanced Reports & Analytics</li>
                    @endif
                    
                    @if($plan->has_multi_campus)
                        <li><i class="fas fa-check text-success"></i> Multi-Campus Support</li>
                    @endif
                    
                    @if($plan->has_custom_branding)
                        <li><i class="fas fa-check text-success"></i> Custom Branding</li>
                    @endif
                    
                    @if($plan->has_api_access)
                        <li><i class="fas fa-check text-success"></i> API Access</li>
                    @endif
                </ul>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="badge bg-{{ 
                        $plan->slug == 'basic' ? 'primary' : 
                        ($plan->slug == 'standard' ? 'success' : 'info') 
                    }}">
                        {{ $plan->subscriptions_count }} active subscribers
                    </span>
                    <div>
                        <a href="{{ route('super-admin.plans.edit', $plan) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                        @if($plan->subscriptions_count == 0)
                        <form action="{{ route('super-admin.plans.destroy', $plan) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" 
                                    onclick="return confirm('Delete this plan?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Plan Features Comparison Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Plan Features Comparison</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Feature</th>
                        @foreach($plans as $plan)
                            <th class="text-center">{{ $plan->name }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Price</strong></td>
                        @foreach($plans as $plan)
                            <td class="text-center">
                                @if($plan->price == 0)
                                    Custom
                                @else
                                    ₱{{ number_format($plan->price, 2) }}/month
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td><strong>Max Students</strong></td>
                        @foreach($plans as $plan)
                            <td class="text-center">
                                {{ $plan->max_students ? number_format($plan->max_students) : 'Unlimited' }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td><strong>Advanced Reports</strong></td>
                        @foreach($plans as $plan)
                            <td class="text-center">
                                @if($plan->has_advanced_reports)
                                    <i class="fas fa-check text-success"></i>
                                @else
                                    <i class="fas fa-times text-danger"></i>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td><strong>Multi-Campus Support</strong></td>
                        @foreach($plans as $plan)
                            <td class="text-center">
                                @if($plan->has_multi_campus)
                                    <i class="fas fa-check text-success"></i>
                                @else
                                    <i class="fas fa-times text-danger"></i>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td><strong>Custom Branding</strong></td>
                        @foreach($plans as $plan)
                            <td class="text-center">
                                @if($plan->has_custom_branding)
                                    <i class="fas fa-check text-success"></i>
                                @else
                                    <i class="fas fa-times text-danger"></i>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td><strong>API Access</strong></td>
                        @foreach($plans as $plan)
                            <td class="text-center">
                                @if($plan->has_api_access)
                                    <i class="fas fa-check text-success"></i>
                                @else
                                    <i class="fas fa-times text-danger"></i>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection