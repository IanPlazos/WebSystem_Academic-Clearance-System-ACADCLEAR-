@extends('layouts.app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800">Add New Staff</h1>
        <p class="mb-0 text-muted">Create a staff account and assign the correct college and department.</p>
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
                        <label for="password" class="font-weight-bold small text-uppercase text-gray-700 mb-2">Password <span class="text-danger">*</span></label>
                        <input type="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               id="password" 
                               name="password" 
                               placeholder="At least 8 characters"
                               required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="password_confirmation" class="font-weight-bold small text-uppercase text-gray-700 mb-2">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" 
                               class="form-control" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               placeholder="Re-enter password"
                               required>
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
            </div>

            <div class="d-flex flex-wrap align-items-center mt-2">
                <button type="submit" class="btn btn-primary mr-2 mb-2">
                    <i class="fas fa-save mr-1"></i> Create Staff
                </button>
                <a href="{{ route('admin.staff.index') }}" class="btn btn-light border mb-2">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var collegeSelect = document.getElementById('college_id');
        var departmentSelect = document.getElementById('department_id');
        var oldDepartmentId = '{{ old('department_id') }}';

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
    });
</script>
@endpush