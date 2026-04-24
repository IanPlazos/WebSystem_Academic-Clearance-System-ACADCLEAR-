@extends('super-admin.layouts.app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit University</h1>
    <a href="{{ route('super-admin.tenants.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Edit University Information</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('super-admin.tenants.update', $tenant) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">University Name *</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                           value="{{ old('name', $tenant->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Domain *</label>
                    <input type="text" name="domain" class="form-control @error('domain') is-invalid @enderror" 
                           value="{{ old('domain', $tenant->domain) }}" required>
                    @error('domain')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small>Example: bukidnon-state-u.acadclear.com</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status *</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="active" {{ old('status', $tenant->status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="suspended" {{ old('status', $tenant->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                        <option value="expired" {{ old('status', $tenant->status) == 'expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Suspension Reason</label>
                    <textarea name="suspension_reason" class="form-control @error('suspension_reason') is-invalid @enderror" 
                              rows="3" placeholder="Reason for suspension (if applicable)">{{ old('suspension_reason', $tenant->suspension_reason) }}</textarea>
                    @error('suspension_reason')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">University Logo</label>
                    <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/*">
                    @error('logo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted d-block mt-1">Upload a new logo to replace the current one.</small>
                    @if($tenant->logo)
                        <div class="mt-3 p-2 border rounded d-inline-block bg-white">
                            <img src="{{ str_starts_with($tenant->logo, 'http://') || str_starts_with($tenant->logo, 'https://') ? $tenant->logo : asset('storage/' . ltrim($tenant->logo, '/')) }}"
                                 alt="{{ $tenant->name }} logo"
                                 style="max-height: 72px; max-width: 200px; object-fit: contain;">
                        </div>
                    @endif
                </div>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                <strong>Note:</strong> Changing the status will affect the university's access to the system.
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update University
            </button>
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
            <strong>Warning:</strong> This action cannot be undone. Deleting a university will remove all its data permanently.
        </div>
        
        <form action="{{ route('super-admin.tenants.destroy', $tenant) }}" method="POST" 
              onsubmit="return confirm('Are you sure you want to delete {{ $tenant->name }}? This will permanently delete all data for this university.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash"></i> Delete University
            </button>
        </form>
    </div>
</div>
@endsection