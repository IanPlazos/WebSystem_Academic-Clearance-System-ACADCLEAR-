
@extends('layouts.app')

@section('content')
@php
    $canManageStudents = auth()->user()->hasPermission('tenant.students.manage');
@endphp
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Student</h1>
    <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Student Information</h6>
    </div>
    <div class="card-body">
        @if($canManageStudents)
        <form action="{{ route('admin.students.update', $user) }}" method="POST">
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
                <div class="col-md-12">
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
                        <small class="text-muted">Warning: changing college or department will reset the student’s clearance record.</small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
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
                        <small class="text-muted">Choose the department where the student belongs.</small>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Student
                </button>
            </div>
        </form>
        @else
        <div class="alert alert-warning mb-0">You do not have permission to edit students.</div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var collegeSelect = document.getElementById('college_id');
        var departmentSelect = document.getElementById('department_id');
        var currentDepartment = '{{ old('department_id', $user->department_id) }}';

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
            loadDepartments(collegeSelect.value, currentDepartment);
        }
    });
</script>
@endpush