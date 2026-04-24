
@extends('layouts.app')

@section('content')
@php
    $canManageColleges = auth()->user()->hasPermission('tenant.colleges.manage');
@endphp
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Add New College</h1>
    <a href="{{ route('admin.colleges.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">College Information</h6>
    </div>
    <div class="card-body">
        @if($canManageColleges)
        <form action="{{ route('admin.colleges.store') }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label for="name" class="form-label">College Name <span class="text-danger">*</span></label>
                <input type="text" 
                       class="form-control @error('name') is-invalid @enderror" 
                       id="name" 
                       name="name" 
                       value="{{ old('name') }}" 
                       required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Example: College of Technology, College of Arts and Sciences</small>
            </div>

            <div class="mb-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save College
                </button>
            </div>
        </form>
        @else
        <div class="alert alert-warning mb-0">You do not have permission to create colleges.</div>
        @endif
    </div>
</div>
@endsection