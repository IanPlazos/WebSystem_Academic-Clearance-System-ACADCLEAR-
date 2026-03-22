
@extends('super-admin.layouts.app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Plan: {{ $plan->name }}</h1>
    <a href="{{ route('super-admin.plans.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Plans
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Plan Information</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('super-admin.plans.update', $plan) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Plan Name *</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                           value="{{ old('name', $plan->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Slug</label>
                    <input type="text" class="form-control" value="{{ $plan->slug }}" disabled>
                    <small>Slug cannot be changed after creation</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Price (₱) *</label>
                    <input type="number" name="price" class="form-control @error('price') is-invalid @enderror" 
                           value="{{ old('price', $plan->price) }}" required step="0.01">
                    @error('price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Max Students</label>
                    <input type="number" name="max_students" class="form-control @error('max_students') is-invalid @enderror" 
                           value="{{ old('max_students', $plan->max_students) }}" placeholder="Leave empty for unlimited">
                    @error('max_students')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Active Subscribers</label>
                    <input type="text" class="form-control" value="{{ $plan->subscriptions_count }}" disabled>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold">Features & Permissions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="has_advanced_reports" class="form-check-input" id="has_advanced_reports" 
                                           value="1" {{ $plan->has_advanced_reports ? 'checked' : '' }}>
                                    <label class="form-check-label" for="has_advanced_reports">
                                        <i class="fas fa-chart-line"></i> Advanced Reports & Analytics
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="has_multi_campus" class="form-check-input" id="has_multi_campus" 
                                           value="1" {{ $plan->has_multi_campus ? 'checked' : '' }}>
                                    <label class="form-check-label" for="has_multi_campus">
                                        <i class="fas fa-university"></i> Multi-Campus Support
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="has_custom_branding" class="form-check-input" id="has_custom_branding" 
                                           value="1" {{ $plan->has_custom_branding ? 'checked' : '' }}>
                                    <label class="form-check-label" for="has_custom_branding">
                                        <i class="fas fa-palette"></i> Custom Branding
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="has_api_access" class="form-check-input" id="has_api_access" 
                                           value="1" {{ $plan->has_api_access ? 'checked' : '' }}>
                                    <label class="form-check-label" for="has_api_access">
                                        <i class="fas fa-code"></i> API Access
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Features List</label>
                                <textarea name="features" class="form-control" rows="5" 
                                          placeholder='["Feature 1", "Feature 2"]'>{{ json_encode(json_decode($plan->features ?? '[]'), JSON_PRETTY_PRINT) }}</textarea>
                                <small>Enter features as JSON array</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Plan
            </button>
            
            <a href="{{ route('super-admin.plans.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-danger">Danger Zone</h6>
    </div>
    <div class="card-body">
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Warning:</strong> Deleting this plan will affect all universities using it.
        </div>
        
        <form action="{{ route('super-admin.plans.destroy', $plan) }}" method="POST" 
              onsubmit="return confirm('Are you sure you want to delete {{ $plan->name }}? This will affect all universities on this plan.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash"></i> Delete Plan
            </button>
        </form>
    </div>
</div>
@endsection