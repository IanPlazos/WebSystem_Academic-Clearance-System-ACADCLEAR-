

@extends('layouts.app')

@section('content')
@php
    $canManageStaff = auth()->user()->hasPermission('tenant.staff.manage');
    $selectedOfficeRole = old('office_role', $user->office_role);
    $customOfficeRoleValue = old('custom_office_role');

    if ($selectedOfficeRole !== 'custom' && !array_key_exists($selectedOfficeRole, $officeRoles)) {
        $customOfficeRoleValue = $customOfficeRoleValue ?: ucwords(str_replace('_', ' ', $selectedOfficeRole));
        $selectedOfficeRole = 'custom';
    }
@endphp
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800">Edit Staff</h1>
        <p class="mb-0 text-muted">Update profile, assignment, and module access for this staff account.</p>
    </div>
    <a href="{{ route('admin.staff.index') }}" class="btn btn-outline-secondary btn-sm mt-3 mt-sm-0">
        <i class="fas fa-arrow-left mr-1"></i> Back to List
    </a>
</div>

<div class="card shadow-sm mb-4 border-0">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Staff Information</h6>
        <span class="badge badge-light border text-primary">Required fields marked *</span>
    </div>
    <div class="card-body">
        @if($canManageStaff)
        <form action="{{ route('admin.staff.update', $user) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $user->name) }}" 
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               value="{{ old('email', $user->email) }}" 
                               required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="college_id" class="form-label">College <span class="text-danger">*</span></label>
                        <select class="form-control @error('college_id') is-invalid @enderror" 
                                id="college_id" 
                                name="college_id" 
                                required>
                            <option value="">Select College</option>
                            @foreach($colleges as $college)
                                <option value="{{ $college->id }}" 
                                    {{ old('college_id', $user->college_id) == $college->id ? 'selected' : '' }}>
                                    {{ $college->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('college_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="department_id" class="form-label">Department <span class="text-danger">*</span></label>
                        <select class="form-control @error('department_id') is-invalid @enderror" 
                                id="department_id" 
                                name="department_id" 
                                required>
                            <option value="">Select Department</option>
                        </select>
                        @error('department_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="office_role" class="form-label">Office Role <span class="text-danger">*</span></label>
                        <select class="form-control @error('office_role') is-invalid @enderror"
                                id="office_role"
                                name="office_role"
                                required>
                            <option value="">Select Office Role</option>
                            @foreach($officeRoles as $roleKey => $roleLabel)
                                <option value="{{ $roleKey }}" {{ $selectedOfficeRole === $roleKey ? 'selected' : '' }}>
                                    {{ $roleLabel }}
                                </option>
                            @endforeach
                            <option value="custom" {{ $selectedOfficeRole === 'custom' ? 'selected' : '' }}>Other (Add New Role)</option>
                        </select>
                        @error('office_role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6" id="customOfficeRoleWrapper" style="display: {{ $selectedOfficeRole === 'custom' ? 'block' : 'none' }};">
                    <div class="mb-3">
                        <label for="custom_office_role" class="form-label">New Office Role <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('custom_office_role') is-invalid @enderror"
                               id="custom_office_role"
                               name="custom_office_role"
                               value="{{ $customOfficeRoleValue }}"
                               placeholder="e.g., Clinic Officer"
                               maxlength="255">
                        @error('custom_office_role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="mb-3">
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
                            <label class="form-label mb-0">Module Access</label>
                            <div class="mt-2 mt-sm-0">
                                <button type="button" class="btn btn-sm btn-outline-primary mr-1" id="selectAllModulesEdit">Select All</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="clearAllModulesEdit">Clear</button>
                            </div>
                        </div>
                        @php
                            $oldModules = old('modules');
                            $effectiveModules = is_array($oldModules) ? $oldModules : ($selectedModules ?? []);
                        @endphp
                        <div class="border rounded p-3 bg-light">
                            <div class="row">
                                @foreach($availableModules as $moduleKey => $permissions)
                                    @php
                                        $label = ucwords(str_replace('_', ' ', $moduleKey));
                                        $checked = in_array($moduleKey, $effectiveModules, true);
                                    @endphp
                                    <div class="col-md-4 mb-2">
                                        <div class="custom-control custom-checkbox border rounded px-3 py-2 bg-white">
                                            <input type="checkbox"
                                                   class="custom-control-input staff-module-checkbox"
                                                   id="module_{{ $moduleKey }}"
                                                   name="modules[]"
                                                   value="{{ $moduleKey }}"
                                                   {{ $checked ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="module_{{ $moduleKey }}">{{ $label }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <small class="text-muted d-block mt-2">Update which modules this staff member can access.</small>
                        </div>
                        @error('modules')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                        @error('modules.*')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Staff
                </button>
            </div>
        </form>
        @else
        <div class="alert alert-warning mb-0">You do not have permission to edit staff.</div>
        @endif
    </div>
</div>

    @endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var collegeSelect = document.getElementById('college_id');
        var departmentSelect = document.getElementById('department_id');
        var currentDepartment = {{ $user->department_id }};
        var selectAllModulesBtn = document.getElementById('selectAllModulesEdit');
        var clearAllModulesBtn = document.getElementById('clearAllModulesEdit');
        var officeRoleSelect = document.getElementById('office_role');
        var customOfficeRoleWrapper = document.getElementById('customOfficeRoleWrapper');
        var customOfficeRoleInput = document.getElementById('custom_office_role');

        function toggleCustomOfficeRole() {
            if (!officeRoleSelect || !customOfficeRoleWrapper) {
                return;
            }

            var isCustom = officeRoleSelect.value === 'custom';
            customOfficeRoleWrapper.style.display = isCustom ? 'block' : 'none';

            if (customOfficeRoleInput) {
                customOfficeRoleInput.required = isCustom;
                if (!isCustom) {
                    customOfficeRoleInput.value = '';
                }
            }
        }

        function loadDepartments(collegeId) {
            if (!collegeId) {
                departmentSelect.innerHTML = '<option value="">Select Department</option>';
                return;
            }

            departmentSelect.innerHTML = '<option value="">Loading...</option>';

            fetch('/admin/get-departments/' + collegeId)
                .then(response => response.json())
                .then(data => {
                    departmentSelect.innerHTML = '<option value="">Select Department</option>';
                    data.forEach(department => {
                        var selected = (department.id == currentDepartment) ? 'selected' : '';
                        departmentSelect.innerHTML += `<option value="${department.id}" ${selected}>${department.name}</option>`;
                    });
                })
                .catch(() => {
                    departmentSelect.innerHTML = '<option value="">Error loading departments</option>';
                });
        }

        collegeSelect.addEventListener('change', function() {
            loadDepartments(this.value);
        });

        if (collegeSelect.value) {
            loadDepartments(collegeSelect.value);
        }

        officeRoleSelect?.addEventListener('change', toggleCustomOfficeRole);
        toggleCustomOfficeRole();

        function setAllModules(checked) {
            document.querySelectorAll('.staff-module-checkbox').forEach(function (checkbox) {
                checkbox.checked = checked;
            });
        }

        selectAllModulesBtn?.addEventListener('click', function () {
            setAllModules(true);
        });

        clearAllModulesBtn?.addEventListener('click', function () {
            setAllModules(false);
        });
    });
</script>
@endpush