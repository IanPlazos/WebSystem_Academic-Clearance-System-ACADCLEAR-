
@extends('super-admin.layouts.app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        Edit Plan: {{ $plan->name }}
        @if(in_array($plan->slug, ['basic', 'standard', 'premium', 'enterprise']))
            <span class="badge bg-secondary ms-2" style="font-size:0.7rem;">
                <i class="fas fa-lock me-1"></i>System Plan
            </span>
        @endif
    </h1>
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

            {{-- Basic Info --}}
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="edit-plan-name" class="form-label">Plan Name *</label>
                    <input type="text" id="edit-plan-name" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $plan->name) }}" required autocomplete="off">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="edit-plan-slug" class="form-label">Slug</label>
                    <input type="text" id="edit-plan-slug" class="form-control bg-light" value="{{ $plan->slug }}" disabled>
                    <small class="text-muted">Slug cannot be changed after creation</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="edit-plan-price" class="form-label">Price (₱) *</label>
                    <input type="number" id="edit-plan-price" name="price" class="form-control @error('price') is-invalid @enderror"
                           value="{{ old('price', $plan->price) }}" required step="0.01" min="0" autocomplete="off">
                    @error('price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Set to 0 for custom pricing</small>
                </div>

                <div class="col-md-3 mb-3">
                    <label for="edit-plan-max-students" class="form-label">Max Students</label>
                    <input type="number" id="edit-plan-max-students" name="max_students" class="form-control @error('max_students') is-invalid @enderror"
                           value="{{ old('max_students', $plan->max_students) }}" placeholder="Leave empty for unlimited" min="0" autocomplete="off">
                    @error('max_students')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 mb-3">
                    <label for="edit-plan-subscribers" class="form-label">Active Subscribers</label>
                    <input type="text" id="edit-plan-subscribers" class="form-control bg-light" value="{{ $plan->subscriptions_count }}" disabled>
                </div>
            </div>

            {{-- Features & Permissions --}}
            @php
                $savedFeatures = is_array($plan->features) ? $plan->features : json_decode($plan->features ?? '[]', true) ?? [];
                $oldFeatures   = old('features', $savedFeatures);
            @endphp

            <div class="card mb-4 border">
                <div class="card-header bg-light">
                    <h6 class="m-0 font-weight-bold text-dark">
                        <i class="fas fa-list-check me-1"></i> Features &amp; Permissions
                    </h6>
                    <small class="text-muted">Check all features included in this plan</small>
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
                                       {{ in_array($feature, $oldFeatures) ? 'checked' : '' }}>
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
                                       {{ in_array($feature, $oldFeatures) ? 'checked' : '' }}>
                                <label class="form-check-label" for="feat_{{ Str::slug($feature) }}">
                                    {{ $feature }}
                                </label>
                            </div>
                        </div>
                        @endforeach

                        {{-- Advanced Reports FLAG --}}
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input type="checkbox" name="has_advanced_reports" class="form-check-input"
                                       id="has_advanced_reports" value="1"
                                       {{ old('has_advanced_reports', $plan->has_advanced_reports) ? 'checked' : '' }}>
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
                                       {{ in_array($feature, $oldFeatures) ? 'checked' : '' }}>
                                <label class="form-check-label" for="feat_{{ Str::slug($feature) }}">
                                    {{ $feature }}
                                </label>
                            </div>
                        </div>
                        @endforeach

                        {{-- API Access FLAG --}}
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input type="checkbox" name="has_api_access" class="form-check-input"
                                       id="has_api_access" value="1"
                                       {{ old('has_api_access', $plan->has_api_access) ? 'checked' : '' }}>
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
                                       {{ in_array($feature, $oldFeatures) ? 'checked' : '' }}>
                                <label class="form-check-label" for="feat_{{ Str::slug($feature) }}">
                                    {{ $feature }}
                                </label>
                            </div>
                        </div>
                        @endforeach

                        {{-- Multi-Campus FLAG --}}
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input type="checkbox" name="has_multi_campus" class="form-check-input"
                                       id="has_multi_campus" value="1"
                                       {{ old('has_multi_campus', $plan->has_multi_campus) ? 'checked' : '' }}>
                                <label class="form-check-label" for="has_multi_campus">
                                    <strong>Multi-Campus Support</strong>
                                    <span class="badge bg-info text-dark ms-1" style="font-size:0.7rem;">FLAG</span>
                                </label>
                            </div>
                        </div>

                        {{-- Custom Branding FLAG --}}
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input type="checkbox" name="has_custom_branding" class="form-check-input"
                                       id="has_custom_branding" value="1"
                                       {{ old('has_custom_branding', $plan->has_custom_branding) ? 'checked' : '' }}>
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
                                       {{ in_array($feature, $oldFeatures) ? 'checked' : '' }}>
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
                <i class="fas fa-save"></i> Update Plan
            </button>
            <a href="{{ route('super-admin.plans.index') }}" class="btn btn-secondary ms-2">Cancel</a>
        </form>
    </div>
</div>

{{-- Danger Zone — hidden for system plans --}}
@if(!in_array($plan->slug, ['basic', 'standard', 'premium', 'enterprise']))
<div class="card shadow mb-4 border-danger">
    <div class="card-header py-3 bg-danger text-white">
        <h6 class="m-0 font-weight-bold">
            <i class="fas fa-exclamation-triangle me-1"></i> Danger Zone
        </h6>
    </div>
    <div class="card-body">
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Warning:</strong> Deleting this plan will affect all institutions currently using it.
        </div>

        <form action="{{ route('super-admin.plans.destroy', $plan) }}" method="POST"
              onsubmit="return confirm('Are you sure you want to delete {{ $plan->name }}? This action cannot be undone.')">
            @csrf
            @method('DELETE')
            @if($plan->subscriptions_count > 0)
                <button type="submit" class="btn btn-danger" disabled>
                    <i class="fas fa-trash"></i> Delete Plan
                </button>
                <small class="text-muted ms-2">Cannot delete — {{ $plan->subscriptions_count }} active subscriber(s).</small>
            @else
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Delete Plan
                </button>
            @endif
        </form>
    </div>
</div>
@else
<div class="alert alert-secondary d-flex align-items-center">
    <i class="fas fa-lock me-2 fs-5"></i>
    <div>
        <strong>System Plan</strong> — Basic, Standard, and Premium plans are constants and cannot be deleted.
        You can still edit their name, price, and features above.
    </div>
</div>
@endif

@endsection
