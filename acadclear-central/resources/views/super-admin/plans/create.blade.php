
@extends('super-admin.layouts.app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Create New Plan</h1>
    <a href="{{ route('super-admin.plans.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Plans
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Plan Information</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('super-admin.plans.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Plan Name *</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                           value="{{ old('name') }}" required placeholder="e.g., Professional, Premium">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Slug *</label>
                    <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" 
                           value="{{ old('slug') }}" required placeholder="e.g., professional">
                    @error('slug')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small>Used in URLs: /plans/professional</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Price (₱) *</label>
                    <input type="number" name="price" class="form-control @error('price') is-invalid @enderror" 
                           value="{{ old('price') }}" required step="0.01">
                    @error('price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small>Set to 0 for custom pricing</small>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Max Students</label>
                    <input type="number" name="max_students" class="form-control @error('max_students') is-invalid @enderror" 
                           value="{{ old('max_students') }}" placeholder="Leave empty for unlimited">
                    @error('max_students')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Plan Type</label>
                    <select name="slug" class="form-select" disabled>
                        <option value="">Select from above</option>
                        <option value="basic">Basic</option>
                        <option value="standard">Standard</option>
                        <option value="premium">Premium</option>
                    </select>
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
                                    <input type="checkbox" name="has_advanced_reports" class="form-check-input" id="has_advanced_reports" value="1">
                                    <label class="form-check-label" for="has_advanced_reports">
                                        <i class="fas fa-chart-line"></i> Advanced Reports & Analytics
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="has_multi_campus" class="form-check-input" id="has_multi_campus" value="1">
                                    <label class="form-check-label" for="has_multi_campus">
                                        <i class="fas fa-university"></i> Multi-Campus Support
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="has_custom_branding" class="form-check-input" id="has_custom_branding" value="1">
                                    <label class="form-check-label" for="has_custom_branding">
                                        <i class="fas fa-palette"></i> Custom Branding (Logo & Theme)
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="has_api_access" class="form-check-input" id="has_api_access" value="1">
                                    <label class="form-check-label" for="has_api_access">
                                        <i class="fas fa-code"></i> API Access
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Additional Features (JSON format)</label>
                                <textarea name="features" class="form-control" rows="5" placeholder='["Feature 1", "Feature 2", "Feature 3"]'>{{ old('features') }}</textarea>
                                <small>Enter features as JSON array</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Create Plan
            </button>
        </form>
    </div>
</div>
@endsection