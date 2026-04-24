

@extends('layouts.app')

@section('content')
@php
    $canManageStudents = auth()->user()->hasPermission('tenant.students.manage');
@endphp
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Students</h1>
    @if($canManageStudents)
        <a href="{{ route('admin.students.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Student
        </a>
    @endif
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">All Students</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>College</th>
                        <th>Clearances</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                    <tr>
                        <td>{{ $student->id }}</td>
                        <td>{{ $student->name }}</td>
                        <td>{{ $student->email }}</td>
                        <td>{{ $student->college->name ?? 'N/A' }}</td>
                        <td>
                            @php
                                $total = $student->clearances->count();
                                $approved = $student->clearances->where('status', 'approved')->count();
                                $pending = $student->clearances->where('status', 'pending')->count();
                            @endphp
                            <span class="badge bg-success">{{ $approved }} Approved</span>
                            <span class="badge bg-warning">{{ $pending }} Pending</span>
                            <span class="badge bg-info">Total: {{ $total }}</span>
                        </td>
                        <td>{{ $student->created_at->format('M d, Y') }}</td>
                        <td>
                            @if($canManageStudents)
                                <a href="{{ route('admin.students.edit', $student) }}" 
                                   class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.students.destroy', $student) }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" 
                                            onclick="return confirm('Delete this student? This will also delete all their clearances.')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @else
                                <span class="text-muted">No actions</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No students found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-center">
            {{ $students->links() }}
        </div>
    </div>
</div>
@endsection