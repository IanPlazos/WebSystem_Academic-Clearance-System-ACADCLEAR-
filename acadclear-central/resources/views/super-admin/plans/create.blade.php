
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

            {{-- Basic Info --}}
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="plan-name" class="form-label">Plan Name *</label>
                    <input type="text" id="plan-name" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" required placeholder="e.g., Professional, Premium" autocomplete="off">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="plan-slug" class="form-label">Slug *</label>
                    <input type="text" id="plan-slug" name="slug" class="form-control @error('slug') is-invalid @enderror"
                           value="{{ old('slug') }}" required placeholder="e.g., professional"
                           oninput="this.value=this.value.toLowerCase().replace(/\s+/g,'-')" autocomplete="off">
                    @error('slug')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Lowercase, no spaces. Cannot use: basic, standard, premium</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="plan-price" class="form-label">Price (₱) *</label>
                    <input type="number" id="plan-price" name="price" class="form-control @error('price') is-invalid @enderror"
                           value="{{ old('price') }}" required step="0.01" min="0" autocomplete="off">
                    @error('price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Set to 0 for custom pricing</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="plan-max-students" class="form-label">Max Students</label>
                    <input type="number" id="plan-max-students" name="max_students" class="form-control @error('max_students') is-invalid @enderror"
                           value="{{ old('max_students') }}" placeholder="Leave empty for unlimited" min="0" autocomplete="off">
                    @error('max_students')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Leave blank for unlimited</small>
                </div>
            </div>

            {{-- Features & Permissions --}}
            <div class="card mb-4 border">
                <div class="card-header bg-light">
                    <h6 class="m-0 font-weight-bold text-dark">
                        <i class="fas fa-list-check me-1"></i> Features &amp; Permissions
                    </h6>
                    <small class="text-muted">Select all features included in this plan</small>
                </div>
                <div class="card-body">

                    {{-- ── CORE CLEARANCE ── --}}
                    <h6 class="border-bottom pb-1 mb-3" style="color: #122C4F; font-weight: bold; font-size: 1.15rem;">
                        <i class="fas fa-graduation-cap me-1"></i> Core Clearance
                    </h6>
                    <div class="row mb-4">
                        @php
                            $coreFeatures = [
                                'Standard clearance workflow',
                                'Department approval/rejection',
                                'Basic dashboard overview',
                                'Student progress tracking',
                                'Email notifications',
                                'Basic PDF summary',
                            ];
                        @endphp
                        @foreach($coreFeatures as $feature)
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input type="checkbox" name="features[]" class="form-check-input"
                                       id="feat_{{ Str::slug($feature) }}" value="{{ $feature }}"
                                       {{ in_array($feature, old('features', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="feat_{{ Str::slug($feature) }}">
                                    {{ $feature }}
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- ── REPORTING & ANALYTICS ── --}}
                    <h6 class="border-bottom pb-1 mb-3" style="color: #122C4F; font-weight: bold; font-size: 1.15rem;">
                        <i class="fas fa-chart-bar me-1"></i> Reporting &amp; Analytics
                    </h6>
                    <div class="row mb-4">
                        @php
                            $reportingFeatures = [
                                'Advanced reporting',
                                'Department performance reports',
                                'Pending clearance statistics',
                                'Export to Excel/PDF',
                            ];
                        @endphp
                        @foreach($reportingFeatures as $feature)
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input type="checkbox" name="features[]" class="form-check-input"
                                       id="feat_{{ Str::slug($feature) }}" value="{{ $feature }}"
                                       {{ in_array($feature, old('features', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="feat_{{ Str::slug($feature) }}">
                                    {{ $feature }}
                                </label>
                            </div>
                        </div>
                        @endforeach

                        {{-- Advanced Reports toggle --}}
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input type="checkbox" name="has_advanced_reports" class="form-check-input"
                                       id="has_advanced_reports" value="1"
                                       {{ old('has_advanced_reports') ? 'checked' : '' }}>
                                <label class="form-check-label" for="has_advanced_reports">
                                    <strong>Advanced Reports &amp; Analytics</strong>
                                    <span class="badge bg-info text-dark ms-1" style="font-size:0.7rem;">FLAG</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- ── ACCESS & ROLES ── --}}
                    <h6 class="border-bottom pb-1 mb-3" style="color: #122C4F; font-weight: bold; font-size: 1.15rem;">
                        <i class="fas fa-key me-1"></i> Access &amp; Roles
                    </h6>
                    <div class="row mb-4">
                        @php
                            $accessFeatures = [
                                'Role-based access',
                                'Customizable requirements',
                                'Custom workflow',
                                'Priority email management',
                            ];
                        @endphp
                        @foreach($accessFeatures as $feature)
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input type="checkbox" name="features[]" class="form-check-input"
                                       id="feat_{{ Str::slug($feature) }}" value="{{ $feature }}"
                                       {{ in_array($feature, old('features', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="feat_{{ Str::slug($feature) }}">
                                    {{ $feature }}
                                </label>
                            </div>
                        </div>
                        @endforeach

                        {{-- API Access toggle --}}
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input type="checkbox" name="has_api_access" class="form-check-input"
                                       id="has_api_access" value="1"
                                       {{ old('has_api_access') ? 'checked' : '' }}>
                                <label class="form-check-label" for="has_api_access">
                                    <strong>API Access</strong>
                                    <span class="badge bg-info text-dark ms-1" style="font-size:0.7rem;">FLAG</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- ── MULTI-CAMPUS & BRANDING ── --}}
                    <h6 class="border-bottom pb-1 mb-3" style="color: #122C4F; font-weight: bold; font-size: 1.15rem;">
                        <i class="fas fa-university me-1"></i> Campus &amp; Branding
                    </h6>
                    <div class="row mb-4">
                        @php
                            $campusFeatures = [
                                'Multi-campus support',
                                'Full customization',
                                'Institution branding',
                                'Unlimited students',
                            ];
                        @endphp
                        @foreach($campusFeatures as $feature)
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input type="checkbox" name="features[]" class="form-check-input"
                                       id="feat_{{ Str::slug($feature) }}" value="{{ $feature }}"
                                       {{ in_array($feature, old('features', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="feat_{{ Str::slug($feature) }}">
                                    {{ $feature }}
                                </label>
                            </div>
                        </div>
                        @endforeach

                        {{-- Multi-Campus flag --}}
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input type="checkbox" name="has_multi_campus" class="form-check-input"
                                       id="has_multi_campus" value="1"
                                       {{ old('has_multi_campus') ? 'checked' : '' }}>
                                <label class="form-check-label" for="has_multi_campus">
                                    <strong>Multi-Campus Support</strong>
                                    <span class="badge bg-info text-dark ms-1" style="font-size:0.7rem;">FLAG</span>
                                </label>
                            </div>
                        </div>

                        {{-- Custom Branding flag --}}
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input type="checkbox" name="has_custom_branding" class="form-check-input"
                                       id="has_custom_branding" value="1"
                                       {{ old('has_custom_branding') ? 'checked' : '' }}>
                                <label class="form-check-label" for="has_custom_branding">
                                    <strong>Custom Branding (Logo &amp; Theme)</strong>
                                    <span class="badge bg-info text-dark ms-1" style="font-size:0.7rem;">FLAG</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- ── SUPPORT ── --}}
                    <h6 class="border-bottom pb-1 mb-3" style="color: #122C4F; font-weight: bold; font-size: 1.15rem;">
                        <i class="fas fa-headset me-1"></i> Support &amp; Services
                    </h6>
                    <div class="row mb-2">
                        @php
                            $supportFeatures = [
                                'Email support',
                                'Priority support',
                                'Dedicated support',
                                'Data backup service',
                                'Onboarding assistance',
                            ];
                        @endphp
                        @foreach($supportFeatures as $feature)
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input type="checkbox" name="features[]" class="form-check-input"
                                       id="feat_{{ Str::slug($feature) }}" value="{{ $feature }}"
                                       {{ in_array($feature, old('features', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="feat_{{ Str::slug($feature) }}">
                                    {{ $feature }}
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="alert alert-info mt-3 mb-0 py-2">
                        <i class="fas fa-info-circle me-1"></i>
                        <small><strong>FLAG</strong> items are programmatic toggles used by the system for feature gating. Check them alongside their matching feature label.</small>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Create Plan
            </button>
            <a href="{{ route('super-admin.plans.index') }}" class="btn btn-secondary ms-2">Cancel</a>
        </form>
    </div>
</div>
@endsection
