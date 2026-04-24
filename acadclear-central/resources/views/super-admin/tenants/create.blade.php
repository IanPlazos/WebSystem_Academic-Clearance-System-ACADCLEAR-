
@extends('super-admin.layouts.app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Add New University</h1>
    <a href="{{ route('super-admin.tenants.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">University Information</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('super-admin.tenants.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">University Name *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Slug *</label>
                    <input type="text" name="slug" class="form-control" value="{{ old('slug') }}" 
                           placeholder="bukidnon-state-u" required
                           pattern="^[a-z0-9][a-z0-9\-]*[a-z0-9]$"
                           title="Lowercase letters, numbers and hyphens only (3-64 chars)">
                    <small class="text-muted">Used in URL: https://slug.localhost:8000 - Only lowercase letters, numbers, and hyphens</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Domain *</label>
                    <input type="text" name="domain" class="form-control" value="{{ old('domain') }}" 
                           placeholder="bukidnon.acadclear.com" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Database Name *</label>
                    <input type="text" name="database" class="form-control" value="{{ old('database') }}" 
                           placeholder="acadclear_bukidnon" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Initial Plan *</label>
                    <select name="plan_id" class="form-select" required>
                        <option value="">Select Plan</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                                {{ $plan->name }} - ₱{{ number_format($plan->price, 2) }}/month
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">University Logo</label>
                    <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/*">
                    @error('logo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">PNG, JPG, GIF, or WebP. This will appear in the tenant sidebar.</small>
                </div>
            </div>

            <hr>
            <h6 class="m-0 font-weight-bold text-primary mb-3">University Admin Account</h6>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">University Admin Email *</label>
                    <input type="email" name="admin_email" class="form-control" value="{{ old('admin_email') }}"
                           placeholder="admin@university.edu" required>
                    <small>This account will be created in the university tenant system. Credentials and subscription details will be emailed automatically.</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">University Admin Password *</label>
                    <input type="password" name="admin_password" class="form-control"
                           placeholder="Minimum 8 characters" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Create University
            </button>
        </form>
    </div>
</div>
@endsection