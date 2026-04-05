@extends('layouts.app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Clearance Summary</h1>
    <a href="{{ route('student.clearances.index') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-list mr-1"></i> View All Clearances
    </a>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Progress Overview</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Completion</span>
                    <span class="font-weight-bold">{{ $stats['approved'] }}/{{ $stats['total'] }} ({{ $stats['progress'] }}%)</span>
                </div>
                <div class="progress mb-4" style="height: 22px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $stats['progress'] }}%;" aria-valuenow="{{ $stats['progress'] }}" aria-valuemin="0" aria-valuemax="100">
                        {{ $stats['progress'] }}%
                    </div>
                </div>

                <div class="row text-center">
                    <div class="col-md-3 mb-2">
                        <div class="border rounded p-3 bg-primary text-white">
                            <h4 class="mb-1">{{ $stats['total'] }}</h4>
                            <small>Total</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="border rounded p-3 bg-success text-white">
                            <h4 class="mb-1">{{ $stats['approved'] }}</h4>
                            <small>Approved</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="border rounded p-3 bg-warning text-white">
                            <h4 class="mb-1">{{ $stats['pending'] }}</h4>
                            <small>Pending</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="border rounded p-3 bg-danger text-white">
                            <h4 class="mb-1">{{ $stats['rejected'] }}</h4>
                            <small>Rejected</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Department Status</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Remarks</th>
                        <th>Last Updated</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($summary['departments'] as $department)
                        @php
                            $badgeClass = [
                                'approved' => 'success',
                                'pending' => 'warning',
                                'rejected' => 'danger',
                            ][$department['status']] ?? 'secondary';
                        @endphp
                        <tr>
                            <td>{{ $department['name'] }}</td>
                            <td><span class="badge badge-{{ $badgeClass }}">{{ ucfirst($department['status']) }}</span></td>
                            <td>{{ $department['remarks'] ?: '—' }}</td>
                            <td>{{ $department['updated_at'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No clearance data found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
