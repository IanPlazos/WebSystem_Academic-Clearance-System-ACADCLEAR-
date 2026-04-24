@extends('layouts.app')

@section('content')
@php
    $canExportReports = auth()->user()->hasPermission('tenant.reports.export');
@endphp
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Reports & Analytics</h1>
    <div>
        @if($canExportReports)
            <a href="{{ route('admin.reports.export.pdf', request()->all()) }}" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
            <a href="{{ route('admin.reports.export.csv', request()->all()) }}" class="btn btn-success">
                <i class="fas fa-file-csv"></i> Export CSV
            </a>
        @endif
    </div>
</div>

<!-- Filter Form -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Filter Reports</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="row g-3">
            <div class="col-md-4">
                <label for="college_id" class="form-label">College</label>
                <select name="college_id" id="college_id" class="form-select">
                    <option value="">All Colleges</option>
                    @foreach($colleges as $college)
                        <option value="{{ $college->id }}" {{ $selectedCollege == $college->id ? 'selected' : '' }}>
                            {{ $college->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="date_from" class="form-label">Date From</label>
                <input type="date" name="date_from" id="date_from" class="form-control" 
                       value="{{ $dateFrom }}">
            </div>
            <div class="col-md-3">
                <label for="date_to" class="form-label">Date To</label>
                <input type="date" name="date_to" id="date_to" class="form-control" 
                       value="{{ $dateTo }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <!-- Total Clearances -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Clearances</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_clearances'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-file-signature fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Students Served -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Students Served</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['students_served'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Completion Rate -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Completion Rate</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['completion_rate'] }}%</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Clearances -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Pending</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pending'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row">
    <!-- Status Distribution Chart -->
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Status Distribution</h6>
            </div>
            <div class="card-body">
                <canvas id="statusChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Daily Trend Chart -->
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Daily Trend (Last 7 Days)</h6>
            </div>
            <div class="card-body">
                <canvas id="trendChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- College Distribution Chart (if no college selected) -->
@if(!$selectedCollege && !empty($chartData['college']['labels']))
<div class="row">
    <div class="col-xl-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Clearances by College</h6>
            </div>
            <div class="card-body">
                <canvas id="collegeChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Department Performance Table -->
<div class="row">
    <div class="col-xl-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Department Performance</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>College</th>
                                <th>Department</th>
                                <th>Total</th>
                                <th>Approved</th>
                                <th>Pending</th>
                                <th>Rejected</th>
                                <th>Completion Rate</th>
                                <th>Avg Response Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($departmentPerformance as $dept)
                            <tr>
                                <td>{{ $dept['college'] }}</td>
                                <td>{{ $dept['name'] }}</td>
                                <td>{{ $dept['total'] }}</td>
                                <td class="text-success">{{ $dept['approved'] }}</td>
                                <td class="text-warning">{{ $dept['pending'] }}</td>
                                <td class="text-danger">{{ $dept['rejected'] }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 mr-2" style="height: 8px;">
                                            <div class="progress-bar bg-success" 
                                                 style="width: {{ $dept['rate'] }}%"></div>
                                        </div>
                                        <span>{{ $dept['rate'] }}%</span>
                                    </div>
                                </td>
                                <td>{{ $dept['avg_response_time'] }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">No data available</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Status Distribution Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($chartData['status']['labels']) !!},
            datasets: [{
                data: {!! json_encode($chartData['status']['data']) !!},
                backgroundColor: {!! json_encode($chartData['status']['colors']) !!},
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Daily Trend Chart
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartData['trend']['labels']) !!},
            datasets: [{
                label: 'Clearances',
                data: {!! json_encode($chartData['trend']['data']) !!},
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.05)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    @if(!$selectedCollege && !empty($chartData['college']['labels']))
    // College Distribution Chart
    const collegeCtx = document.getElementById('collegeChart').getContext('2d');
    new Chart(collegeCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartData['college']['labels']) !!},
            datasets: [{
                label: 'Clearances',
                data: {!! json_encode($chartData['college']['data']) !!},
                backgroundColor: '#36b9cc',
                borderColor: '#36b9cc',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
    @endif
</script>
@endpush
@endsection