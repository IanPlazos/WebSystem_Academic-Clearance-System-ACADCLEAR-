@extends('layouts.app')

@section('content')
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
    $schoolName = $student->college->name ?? config('app.name', 'AcadClear');
    $departmentName = $clearance->department->name ?? 'Department';
@endphp

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

    .dark-mode .border.rounded.p-2.bg-white {
        background: #1f2937 !important;
        color: #e5e7eb !important;
        border-color: #374151 !important;
    }

    @media print {
        .topbar, .sidebar, .btn, .alert, .progress, .shadow-sm, .shadow, .pagination {
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
    }
</style>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800">Clearance View</h1>
        <p class="mb-0 text-muted">Printable sheet view for this clearance request.</p>
    </div>
    <div>
        <a href="{{ route('student.clearances.index') }}" class="btn btn-outline-secondary mr-2">Back</a>
        <button type="button" class="btn btn-primary" onclick="window.print()">
            <i class="fas fa-print mr-1"></i> Print
        </button>
    </div>
</div>

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
@endsection