
@extends('layouts.app')

@section('content')
<style>
    .print-clearance {
        background: #fff;
        color: #111827;
        border: 1px solid #d1d5db;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
    }

    .clearance-header .seal {
        width: 58px;
        height: 58px;
        border-radius: 50%;
        border: 2px solid #cbd5e1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: #0f766e;
        background: #f8fafc;
        flex: 0 0 58px;
    }

    .clearance-title {
        letter-spacing: 0.35rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .line-field {
        display: inline-block;
        border-bottom: 1px solid #111827;
        min-height: 1.15rem;
        vertical-align: bottom;
        min-width: 120px;
        padding: 0 0.15rem;
    }

    .line-field.wide { min-width: 260px; }
    .line-field.medium { min-width: 180px; }

    .stamp-box {
        border: 1px solid #9ca3af;
        border-radius: 0.5rem;
        padding: 0.85rem 1rem;
        min-height: 100%;
        background: #fafafa;
    }

    .status-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem 1rem;
    }

    .status-option {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.92rem;
        white-space: nowrap;
    }

    .status-box {
        width: 18px;
        height: 18px;
        border: 1.75px solid #111827;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 18px;
        background: #fff;
    }

    .status-box.filled {
        background: #111827;
        color: #fff;
    }

    .approval-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
    }

    .approval-item {
        text-align: center;
    }

    .approval-line {
        border-top: 1.5px solid #111827;
        height: 0;
        margin-bottom: 0.35rem;
    }

    .approval-label {
        font-size: 0.84rem;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.02em;
    }

    .approval-meta {
        font-size: 0.78rem;
        color: #6b7280;
        margin-top: 0.15rem;
    }

    .sheet-section {
        page-break-after: always;
    }

    .sheet-section:last-child {
        page-break-after: auto;
    }

    .dark-mode .print-clearance {
        background: #1f2937;
        color: #e5e7eb;
        border-color: #374151;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
    }

    .dark-mode .clearance-header .seal {
        border-color: #4b5563;
        background: #0f172a;
        color: #34d399;
    }

    .dark-mode .line-field,
    .dark-mode .approval-line {
        border-color: #e5e7eb;
    }

    .dark-mode .stamp-box {
        border-color: #4b5563;
        background: #111827;
    }

    .dark-mode .status-box {
        border-color: #e5e7eb;
        background: transparent;
    }

    .dark-mode .status-box.filled {
        background: #e5e7eb;
        color: #111827;
    }

    .dark-mode .approval-meta,
    .dark-mode .text-muted,
    .dark-mode .small.text-muted {
        color: #9ca3af !important;
    }

    .dark-mode .alert-light,
    .dark-mode .border.rounded.p-2.bg-white {
        background: #1f2937 !important;
        color: #e5e7eb !important;
        border-color: #374151 !important;
    }

    @media print {
        .topbar, .sidebar, .btn, .alert, .progress, .shadow-sm, .shadow, .pagination, .page-heading-actions {
            display: none !important;
        }

        #content-wrapper, #content, .container-fluid {
            margin: 0 !important;
            padding: 0 !important;
        }

        .print-clearance {
            box-shadow: none;
            border: 0;
        }

        .sheet-section {
            margin: 0;
            padding: 0;
        }
    }
</style>

<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 page-heading-actions">
    <div>
        <h1 class="h3 mb-1 text-gray-800">My Clearance</h1>
        <p class="mb-0 text-muted">A printable clearance form laid out like the office sheet you shared.</p>
    </div>
    <div class="mt-3 mt-md-0">
        <button class="btn btn-outline-secondary mr-2" type="button" onclick="window.print()">
            <i class="fas fa-print mr-1"></i> Print
        </button>
        <a href="#clearance-progress" class="btn btn-primary">
            <i class="fas fa-chart-bar mr-1"></i> Progress
        </a>
    </div>
</div>

@if($clearances->isEmpty())
    <div class="alert alert-info">
        <i class="fas fa-info-circle mr-1"></i> No clearance sheet has been assigned yet.
    </div>
@endif

@foreach($clearances as $clearance)
    @php
        $items = $clearance->checklistItems->isNotEmpty()
            ? $clearance->checklistItems
            : collect([
                (object) [
                    'item_name' => $clearance->department->name ?? ($clearance->clearance_title ?? 'Clearance Item'),
                    'contact_person' => $clearance->office_or_instructor ?? 'Assigned staff',
                    'location' => $clearance->approval_location ?? null,
                    'status' => $clearance->status,
                    'approved_at' => $clearance->updated_at,
                ],
            ]);
        $student = auth()->user();
        $departmentName = $clearance->department->name ?? 'Department';
        $schoolName = $student->college->name ?? config('app.name', 'AcadClear');
    @endphp

    <div class="sheet-section mb-4">
        <div class="card print-clearance">
            <div class="card-body p-4 p-lg-5">
                <div class="clearance-header text-center mb-4">
                    <div class="d-flex align-items-start justify-content-center mb-3">
                        <div class="seal mr-3"><i class="fas fa-university"></i></div>
                        <div>
                            <div class="small font-weight-bold text-uppercase">Department of Education</div>
                            <div class="small">Region VIII</div>
                            <div class="small">Division of Southern Leyte</div>
                            <div class="small">District of Libagon</div>
                        </div>
                        <div class="seal ml-3"><i class="fas fa-seedling"></i></div>
                    </div>

                    <div class="font-weight-bold text-success text-uppercase">{{ $schoolName }}</div>
                    <div class="small text-muted">{{ $student->college->address ?? $departmentName }}</div>
                    <div class="small text-muted">-o0o-</div>

                    <h2 class="clearance-title mt-4 mb-0">Clearance</h2>
                </div>

                <div class="mb-4">
                    <div class="font-weight-bold mb-2">TO WHOM IT MAY CONCERN:</div>
                    <p class="mb-2 font-weight-bold">
                        THIS IS TO CERTIFY that
                        <span class="line-field wide">{{ $student->name }}</span>
                        of
                        <span class="line-field medium">{{ $departmentName }}</span>
                        has been cleared in all his/her obligations in this S.Y. {{ now()->year }}-{{ now()->year + 1 }}.
                    </p>
                </div>

                <div class="approval-grid mb-4">
                    @foreach($items->take(8) as $item)
                        <div class="approval-item">
                            <div class="approval-line"></div>
                            <div class="approval-label">{{ $item->item_name }}</div>
                            <div class="approval-meta">{{ $item->contact_person ?? 'Assigned staff' }}</div>
                            @if($item->location)
                                <div class="approval-meta">{{ $item->location }}</div>
                            @endif
                            <div class="approval-meta mt-1">
                                @if(($item->status ?? 'pending') === 'approved')
                                    Cleared on {{ optional($item->approved_at)->format('m/d/Y') ?? 'N/A' }}
                                @else
                                    Pending approval
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($items->count() > 8)
                    <div class="alert alert-light border small mb-4">
                        Additional checklist items are shown in the student summary below.
                    </div>
                @endif

                <div class="row">
                    <div class="col-lg-8 mb-4">
                        <div class="stamp-box h-100">
                            <div class="font-weight-bold mb-3">Clearance Checklist</div>
                            <div class="status-grid">
                                @foreach($items as $item)
                                    @php $isApproved = ($item->status ?? 'pending') === 'approved'; @endphp
                                    <div class="status-option">
                                        <span class="status-box {{ $isApproved ? 'filled' : '' }}">{{ $isApproved ? '✓' : '' }}</span>
                                        <span>{{ $item->item_name }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 mb-4">
                        <div class="stamp-box h-100">
                            <div class="font-weight-bold mb-3">Status</div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="small text-muted">Overall</span>
                                    <span class="font-weight-bold text-uppercase">{{ ucfirst($clearance->status) }}</span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $clearance->status === 'approved' ? '100' : ($clearance->status === 'pending' ? '50' : '25') }}%;"></div>
                                </div>
                            </div>

                            <div class="small text-muted mb-2">Remarks</div>
                            <div class="border rounded p-2 bg-white" style="min-height: 90px;">
                                {{ $clearance->remarks ?? 'No remarks provided.' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="approval-grid">
                    @foreach($items->take(4) as $item)
                        <div class="approval-item">
                            <div class="approval-line"></div>
                            <div class="approval-label">{{ $item->item_name }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endforeach

<div id="clearance-progress" class="card shadow-sm">
    <div class="card-header py-3 d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Clearance Progress</h6>
        <span class="badge badge-primary">{{ $stats['approved'] }}/{{ $stats['total'] }} Approved</span>
    </div>
    <div class="card-body">
        <div class="d-flex justify-content-between mb-2">
            <span>Overall completion</span>
            <span class="font-weight-bold">{{ $stats['progress'] }}%</span>
        </div>
        <div class="progress mb-4" style="height: 18px;">
            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $stats['progress'] }}%;"></div>
        </div>

        <div class="row text-center">
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="border rounded p-3 bg-success text-white h-100">
                    <h3 class="mb-0">{{ $stats['approved'] }}</h3>
                    <small>Approved</small>
                </div>
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="border rounded p-3 bg-warning text-white h-100">
                    <h3 class="mb-0">{{ $stats['pending'] }}</h3>
                    <small>Pending</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded p-3 bg-danger text-white h-100">
                    <h3 class="mb-0">{{ $stats['rejected'] }}</h3>
                    <small>Rejected</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection