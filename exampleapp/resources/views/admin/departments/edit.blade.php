@extends('layouts.app')

@section('content')
@php
    $canManageDepartments = auth()->user()->hasPermission('tenant.departments.manage');
@endphp
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Department</h1>
    <a href="{{ route('admin.departments.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Department Information</h6>
    </div>
    <div class="card-body">
        @if($canManageDepartments)
        <form action="{{ route('admin.departments.update', $department) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-3">
                <label for="college_id" class="form-label">College <span class="text-danger">*</span></label>
                <select class="form-control @error('college_id') is-invalid @enderror" 
                        id="college_id" 
                        name="college_id" 
                        required>
                    <option value="">Select College</option>
                    @foreach($colleges as $college)
                        <option value="{{ $college->id }}" 
                            {{ old('college_id', $department->college_id) == $college->id ? 'selected' : '' }}>
                            {{ $college->name }}
                        </option>
                    @endforeach
                </select>
                @error('college_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="name" class="form-label">Department Name <span class="text-danger">*</span></label>
                <input type="text" 
                       class="form-control @error('name') is-invalid @enderror" 
                       id="name" 
                       name="name" 
                       value="{{ old('name', $department->name) }}" 
                       required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Department
                </button>
            </div>
        </form>
        @else
        <div class="alert alert-warning mb-0">You do not have permission to edit departments.</div>
        @endif
    </div>
</div>
@endsection