@extends('layouts.app')

@section('content')
@php
    $canManageStaff = auth()->user()->hasPermission('tenant.staff.manage');
@endphp
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800">Add New Staff</h1>
        <p class="mb-0 text-muted">Create a staff account and assign the correct college and department. Password is auto-generated and sent via email.</p>
    </div>
    <a href="{{ route('admin.staff.index') }}" class="btn btn-outline-secondary btn-sm mt-3 mt-sm-0">
        <i class="fas fa-arrow-left mr-1"></i> Back to List
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger shadow-sm" role="alert">
        <div class="font-weight-bold mb-1">Please fix the following:</div>
        <ul class="mb-0 pl-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card shadow-sm mb-4 border-0">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Staff Information</h6>
        <span class="badge badge-light border text-primary">Required fields marked *</span>
    </div>
    <div class="card-body">
        @if($canManageStaff)
        <form action="{{ route('admin.staff.store') }}" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="font-weight-bold small text-uppercase text-gray-700 mb-2">Full Name <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}" 
                               placeholder="Enter full name"
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="email" class="font-weight-bold small text-uppercase text-gray-700 mb-2">Email Address <span class="text-danger">*</span></label>
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               placeholder="name@university.edu"
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
                        <label for="college_id" class="font-weight-bold small text-uppercase text-gray-700 mb-2">College <span class="text-danger">*</span></label>
                        <select class="form-control @error('college_id') is-invalid @enderror" 
                                id="college_id" 
                                name="college_id" 
                                required>
                            <option value="">Select College</option>
                            @foreach($colleges as $college)
                                <option value="{{ $college->id }}" {{ old('college_id') == $college->id ? 'selected' : '' }}>
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
                        <label for="department_id" class="font-weight-bold small text-uppercase text-gray-700 mb-2">Department <span class="text-danger">*</span></label>
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
                        <label for="office_role" class="font-weight-bold small text-uppercase text-gray-700 mb-2">Office Role <span class="text-danger">*</span></label>
                        <select class="form-control @error('office_role') is-invalid @enderror"
                                id="office_role"
                                name="office_role"
                                required>
                            <option value="">Select Office Role</option>
                            @foreach($officeRoles as $roleKey => $roleLabel)
                                <option value="{{ $roleKey }}" {{ old('office_role') === $roleKey ? 'selected' : '' }}>
                                    {{ $roleLabel }}
                                </option>
                            @endforeach
                            <option value="custom" {{ old('office_role') === 'custom' ? 'selected' : '' }}>Other (Add New Role)</option>
                        </select>
                        @error('office_role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6" id="customOfficeRoleWrapper" style="display: {{ old('office_role') === 'custom' ? 'block' : 'none' }};">
                    <div class="mb-3">
                        <label for="custom_office_role" class="font-weight-bold small text-uppercase text-gray-700 mb-2">New Office Role <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('custom_office_role') is-invalid @enderror"
                               id="custom_office_role"
                               name="custom_office_role"
                               value="{{ old('custom_office_role') }}"
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
                            <label class="font-weight-bold small text-uppercase text-gray-700 mb-0">Module Access</label>
                            <div class="mt-2 mt-sm-0">
                                <button type="button" class="btn btn-sm btn-outline-primary mr-1" id="selectAllModulesCreate">Select All</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="clearAllModulesCreate">Clear</button>
                            </div>
                        </div>
                        <div class="border rounded p-3 bg-light">
                            <div class="row">
                                @foreach($availableModules as $moduleKey => $permissions)
                                    @php
                                        $checked = in_array($moduleKey, old('modules', []), true);
                                        $label = ucwords(str_replace('_', ' ', $moduleKey));
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
                            <small class="text-muted d-block mt-2">Select modules this staff member can access from the sidebar.</small>
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

            <div class="d-flex flex-wrap align-items-center mt-2">
                <button type="submit" class="btn btn-primary mr-2 mb-2">
                    <i class="fas fa-save mr-1"></i> Create Staff
                </button>
                <a href="{{ route('admin.staff.index') }}" class="btn btn-light border mb-2">Cancel</a>
            </div>
        </form>
        @else
        <div class="alert alert-warning mb-0">You do not have permission to create staff.</div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var collegeSelect = document.getElementById('college_id');
        var departmentSelect = document.getElementById('department_id');
        var oldDepartmentId = '{{ old('department_id') }}';
        var selectAllModulesBtn = document.getElementById('selectAllModulesCreate');
        var clearAllModulesBtn = document.getElementById('clearAllModulesCreate');
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

        function resetDepartment(message) {
            departmentSelect.innerHTML = '<option value="">' + message + '</option>';
        }

        function loadDepartments(collegeId, selectedDepartmentId) {
            if (!collegeId) {
                resetDepartment('Select Department');
                return;
            }

            resetDepartment('Loading...');

            fetch('/admin/get-departments/' + collegeId)
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Failed to load departments');
                    }
                    return response.json();
                })
                .then(function (data) {
                    resetDepartment('Select Department');
                    data.forEach(function (department) {
                        var option = document.createElement('option');
                        option.value = department.id;
                        option.textContent = department.name;
                        if (String(selectedDepartmentId) === String(department.id)) {
                            option.selected = true;
                        }
                        departmentSelect.appendChild(option);
                    });
                })
                .catch(function () {
                    resetDepartment('Error loading departments');
                });
        }

        collegeSelect.addEventListener('change', function () {
            loadDepartments(this.value, null);
        });

        if (collegeSelect.value) {
            loadDepartments(collegeSelect.value, oldDepartmentId);
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