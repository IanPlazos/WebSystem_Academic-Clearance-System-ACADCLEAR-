

@extends('layouts.app')

@section('content')
@php
    $actor = auth()->user();
    $canCreateClearance = $actor->hasPermission('tenant.clearances.create');
    $canExportClearance = $actor->hasPermission('tenant.clearances.export');
    $canUpdateClearance = $actor->hasPermission('tenant.clearances.update');
    $actorOfficeRole = $actor->office_role;
    $actorDepartmentId = $actor->department_id;
@endphp
@push('styles')
<style>
    .clearance-page {
        --panel-bg: rgba(18, 36, 59, 0.65);
        --panel-border: rgba(138, 180, 248, 0.18);
        --text-soft: #9bb4d3;
        --accent: #32c8ff;
    }

    .clearance-page .page-title {
        font-weight: 700;
        letter-spacing: 0.02em;
        color: #f5fbff;
    }

    .clearance-page .card {
        background: linear-gradient(160deg, rgba(20, 41, 67, 0.9), var(--panel-bg));
        border: 1px solid var(--panel-border);
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.22);
    }

    .clearance-page .card-header {
        border-bottom: 1px solid rgba(150, 180, 220, 0.16);
        background: linear-gradient(90deg, rgba(11, 29, 52, 0.7), rgba(29, 52, 84, 0.52));
    }

    .clearance-page .card-header h6 {
        color: #68dbff !important;
        letter-spacing: 0.02em;
    }

    .clearance-page .filters-form .form-control,
    .clearance-page .filters-form .btn {
        min-height: 42px;
        border-radius: 10px;
    }

    .clearance-page .table {
        color: #eef5ff;
        border-color: rgba(150, 180, 220, 0.2);
        margin-bottom: 0;
    }

    .clearance-page .table thead th {
        background: rgba(17, 37, 62, 0.96);
        color: #dcecff;
        font-weight: 600;
        border-bottom-width: 1px;
        position: sticky;
        top: 0;
        z-index: 1;
    }

    .clearance-page .table tbody td {
        padding-top: 1rem;
        padding-bottom: 1rem;
        vertical-align: top;
        border-color: rgba(150, 180, 220, 0.18);
    }

    .clearance-page .table tbody tr:hover {
        background: rgba(90, 180, 255, 0.08);
    }

    .clearance-page .muted-note {
        color: var(--text-soft) !important;
    }

    .clearance-page .checklist-card {
        margin-bottom: 0.65rem;
        border: 1px solid rgba(132, 176, 232, 0.22);
        border-radius: 10px;
        padding: 0.65rem;
        background: rgba(16, 31, 52, 0.75);
    }

    .clearance-page .checklist-card:last-child {
        margin-bottom: 0;
    }

    .clearance-page .checklist-more-toggle {
        margin-top: 0.55rem;
        font-size: 0.78rem;
        font-weight: 600;
        color: #8fd5ff;
    }

    .clearance-page .checklist-more-toggle:hover {
        color: #b8e8ff;
        text-decoration: none;
    }

    .clearance-page .student-block strong {
        display: block;
        font-size: 0.98rem;
        line-height: 1.25rem;
        margin-bottom: 0.15rem;
    }

    .clearance-page .student-block .student-id {
        color: #8ec7ff;
        font-size: 0.78rem;
        display: block;
        margin-bottom: 0.1rem;
    }

    .clearance-page .table-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.22rem 0.55rem;
        border-radius: 999px;
        background: rgba(90, 180, 255, 0.12);
        border: 1px solid rgba(132, 176, 232, 0.25);
        color: #cfe8ff;
        font-size: 0.76rem;
        font-weight: 600;
        margin-bottom: 0.45rem;
    }

    .clearance-page .table-main {
        color: #f5fbff;
        font-size: 0.9rem;
        line-height: 1.25rem;
        font-weight: 600;
    }

    .clearance-page .table-sub {
        color: #9bb4d3;
        font-size: 0.78rem;
        line-height: 1.2rem;
        margin-top: 0.2rem;
    }

    .clearance-page .checklist-card-title {
        margin-bottom: 0.25rem;
        font-size: 0.9rem;
        line-height: 1.2rem;
    }

    .clearance-page .checklist-meta {
        margin: 0;
        font-size: 0.78rem;
        line-height: 1.2rem;
        color: #9bb4d3;
    }

    .clearance-page .cell-muted {
        color: #9bb4d3;
        font-size: 0.82rem;
    }

    .clearance-page .request-date {
        color: #dcecff;
        font-size: 0.82rem;
        white-space: nowrap;
    }

    .clearance-page .actions-cell {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        flex-wrap: wrap;
    }

    .clearance-page .actions-cell .btn {
        min-width: 36px;
    }

    .clearance-page .checklist-card .badge {
        min-width: 68px;
        text-align: center;
    }

    .clearance-page .status-pill {
        padding: 0.4rem 0.65rem;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.02em;
    }

    .clearance-page .remark-preview {
        color: #f7b3b3;
        cursor: help;
    }

    .clearance-page .approved-stamp-preview {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 54px;
        min-width: 190px;
        border: 3px solid #d03535;
        border-radius: 6px;
        color: #d03535;
        font-weight: 800;
        font-size: 1.05rem;
        letter-spacing: 0.12em;
        transform: rotate(-6deg);
        text-transform: uppercase;
        background: rgba(255, 245, 245, 0.8);
        margin-bottom: 0.75rem;
    }

    @media (max-width: 992px) {
        .clearance-page .d-sm-flex {
            gap: 0.65rem;
        }

        .clearance-page .table-responsive {
            border-radius: 10px;
        }
    }
</style>
@endpush

<div class="clearance-page">
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 page-title">Clearance Requests</h1>
    <div class="d-flex flex-wrap align-items-center" style="gap: 0.5rem;">
        @if($canCreateClearance)
            <a href="{{ route('admin.clearances.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Clearance
            </a>
        @endif
        @if($canExportClearance)
            <a href="{{ route('admin.clearances.export', request()->all()) }}" class="btn btn-success">
                <i class="fas fa-download"></i> Export
            </a>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        {{ session('warning') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<!-- Filter Form -->
<div class="card shadow mb-4 clearance-filters">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Filter Clearances</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.clearances.index') }}" class="row filters-form">
            <div class="col-md-3">
                <select name="status" class="form-control">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" 
                       placeholder="Search by student name..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.clearances.index') }}" class="btn btn-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Actions -->
@if($canUpdateClearance && (request('status') == 'pending' || !request('status')))
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Bulk Actions</h6>
    </div>
    <div class="card-body d-flex flex-wrap align-items-center" style="gap: 0.75rem;">
        <button class="btn btn-success" onclick="bulkApprove()">
            <i class="fas fa-check-double"></i> Approve Selected
        </button>
        <span class="muted-note">Select multiple clearances using checkboxes</span>
    </div>
</div>
@endif

<!-- Clearances Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Clearance Requests</h6>
        <span class="badge bg-info">{{ $clearances->total() }} total</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        @if($canUpdateClearance && (request('status') == 'pending' || !request('status')))
                        <th width="20">
                            <input type="checkbox" id="selectAll">
                        </th>
                        @endif
                        <th style="min-width: 240px;">Student</th>
                        <th style="min-width: 150px;">Department</th>
                        <th style="min-width: 170px;">Clearance</th>
                        <th style="min-width: 170px;">Office / Instructor</th>
                        <th style="min-width: 150px;">Location</th>
                        <th style="min-width: 300px;">Checklist</th>
                        <th>Status</th>
                        <th>Remarks</th>
                        <th style="min-width: 120px;">Request Date</th>
                        <th style="min-width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clearances as $clearance)
                    @php
                        $canActOnClearance = $canUpdateClearance && $actorOfficeRole
                            && $clearance->checklistItems->contains(function ($checklistItem) use ($actorOfficeRole) {
                                return $checklistItem->office_role === $actorOfficeRole;
                            })
                            && (blank($actorDepartmentId) || (int) $clearance->department_id === (int) $actorDepartmentId);
                    @endphp
                    <tr>
                        @if($canUpdateClearance && (request('status') == 'pending' || !request('status')))
                        <td>
                            @if($canActOnClearance)
                                <input type="checkbox" class="clearance-checkbox" value="{{ $clearance->id }}">
                            @endif
                        </td>
                        @endif
                        <td>
                            <div class="student-block">
                                <strong>{{ $clearance->student?->name ?? 'N/A' }}</strong>
                                @php
                                    $studentEmail = $clearance->student?->email;
                                    $studentNumber = null;
                                    if ($studentEmail && str_contains($studentEmail, '@')) {
                                        $studentNumber = explode('@', $studentEmail)[0];
                                    }
                                @endphp
                                @if($studentNumber)
                                    <span class="student-id">{{ $studentNumber }}</span>
                                @endif
                                <small class="muted-note">{{ $studentEmail ?? 'N/A' }}</small>
                            </div>
                        </td>
                        <td>
                            <div class="table-chip"><i class="fas fa-building"></i> Department</div>
                            <div class="table-main">{{ $clearance->department?->name ?? 'N/A' }}</div>
                        </td>
                        <td>
                            <div class="table-chip"><i class="fas fa-file-alt"></i> Type</div>
                            <div class="table-main">{{ $clearance->clearance_title ?? 'Department Clearance' }}</div>
                        </td>
                        <td>
                            <div class="table-chip"><i class="fas fa-user-tie"></i> Assigned To</div>
                            <div class="table-main">{{ $clearance->office_or_instructor ?? 'Assigned staff' }}</div>
                        </td>
                        <td>
                            <div class="table-chip"><i class="fas fa-map-marker-alt"></i> Office</div>
                            <div class="table-main">{{ $clearance->approval_location ?? 'Department Office' }}</div>
                        </td>
                        <td>
                            @if($clearance->checklistItems->isNotEmpty())
                                @php
                                    $checklistItems = $clearance->checklistItems->values();
                                    $visibleChecklistItems = $checklistItems->take(2);
                                    $hiddenChecklistItems = $checklistItems->slice(2);
                                    $collapseId = 'checklistMore' . $clearance->id;
                                @endphp
                                <ul class="list-unstyled mb-0">
                                    @foreach($visibleChecklistItems as $item)
                                        <li class="checklist-card">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <strong class="checklist-card-title">{{ $item->item_name }}</strong>
                                                    @if($item->contact_person)
                                                        <p class="checklist-meta">
                                                            <i class="fas fa-user mr-1"></i>{{ $item->contact_person }}
                                                        </p>
                                                    @endif
                                                    @if($item->location)
                                                        <p class="checklist-meta">
                                                            <i class="fas fa-map-marker-alt mr-1"></i>{{ $item->location }}
                                                        </p>
                                                    @endif
                                                    @if($item->approved_by_name)
                                                        <p class="checklist-meta text-success mb-0">
                                                            <i class="fas fa-signature mr-1"></i>Signed by: {{ $item->approved_by_name }}
                                                        </p>
                                                    @endif
                                                </div>
                                                <span class="badge bg-{{ $item->status === 'approved' ? 'success' : 'secondary' }}">
                                                    {{ ucfirst($item->status) }}
                                                </span>
                                            </div>
                                            @php
                                                $canActOnChecklistItem = $canUpdateClearance
                                                    && $actorOfficeRole
                                                    && $item->office_role === $actorOfficeRole
                                                    && (blank($actorDepartmentId) || (int) $clearance->department_id === (int) $actorDepartmentId);
                                            @endphp
                                            @if($canActOnChecklistItem)
                                                <form action="{{ route('admin.clearances.checklist.update', ['clearance' => $clearance->id, 'item' => $item->id]) }}" method="POST" class="mt-2">
                                                    @csrf
                                                    @if($item->status === 'approved')
                                                        <input type="hidden" name="status" value="pending">
                                                        <button type="submit" class="btn btn-sm btn-outline-warning">
                                                            Mark Pending
                                                        </button>
                                                    @else
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-outline-success"
                                                            data-action="{{ route('admin.clearances.checklist.update', ['clearance' => $clearance->id, 'item' => $item->id]) }}"
                                                            data-item-name="{{ $item->item_name }}"
                                                            data-staff-name="{{ auth()->user()->name }}"
                                                            onclick="openApproveItemModal(this)"
                                                        >
                                                            Mark Approved
                                                        </button>
                                                    @endif
                                                </form>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                                @if($hiddenChecklistItems->isNotEmpty())
                                    <div class="collapse mt-2" id="{{ $collapseId }}">
                                        <ul class="list-unstyled mb-0">
                                            @foreach($hiddenChecklistItems as $item)
                                                <li class="checklist-card">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <strong class="checklist-card-title">{{ $item->item_name }}</strong>
                                                            @if($item->contact_person)
                                                                <p class="checklist-meta">
                                                                    <i class="fas fa-user mr-1"></i>{{ $item->contact_person }}
                                                                </p>
                                                            @endif
                                                            @if($item->location)
                                                                <p class="checklist-meta">
                                                                    <i class="fas fa-map-marker-alt mr-1"></i>{{ $item->location }}
                                                                </p>
                                                            @endif
                                                            @if($item->approved_by_name)
                                                                <p class="checklist-meta text-success mb-0">
                                                                    <i class="fas fa-signature mr-1"></i>Signed by: {{ $item->approved_by_name }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                        <span class="badge bg-{{ $item->status === 'approved' ? 'success' : 'secondary' }}">
                                                            {{ ucfirst($item->status) }}
                                                        </span>
                                                    </div>
                                                    @php
                                                        $canActOnChecklistItem = $canUpdateClearance
                                                            && $actorOfficeRole
                                                            && $item->office_role === $actorOfficeRole
                                                            && (blank($actorDepartmentId) || (int) $clearance->department_id === (int) $actorDepartmentId);
                                                    @endphp
                                                    @if($canActOnChecklistItem)
                                                        <form action="{{ route('admin.clearances.checklist.update', ['clearance' => $clearance->id, 'item' => $item->id]) }}" method="POST" class="mt-2">
                                                            @csrf
                                                            @if($item->status === 'approved')
                                                                <input type="hidden" name="status" value="pending">
                                                                <button type="submit" class="btn btn-sm btn-outline-warning">
                                                                    Mark Pending
                                                                </button>
                                                            @else
                                                                <button
                                                                    type="button"
                                                                    class="btn btn-sm btn-outline-success"
                                                                    data-action="{{ route('admin.clearances.checklist.update', ['clearance' => $clearance->id, 'item' => $item->id]) }}"
                                                                    data-item-name="{{ $item->item_name }}"
                                                                    data-staff-name="{{ auth()->user()->name }}"
                                                                    onclick="openApproveItemModal(this)"
                                                                >
                                                                    Mark Approved
                                                                </button>
                                                            @endif
                                                        </form>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <a
                                        href="#{{ $collapseId }}"
                                        class="checklist-more-toggle d-inline-block"
                                        data-toggle="collapse"
                                        data-bs-toggle="collapse"
                                        role="button"
                                        aria-expanded="false"
                                        aria-controls="{{ $collapseId }}"
                                    >
                                        Show {{ $hiddenChecklistItems->count() }} more item{{ $hiddenChecklistItems->count() > 1 ? 's' : '' }}
                                    </a>
                                @endif
                            @else
                                <span class="cell-muted">No checklist</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $statusClass = [
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    'pending' => 'warning'
                                ][$clearance->status];
                            @endphp
                            <span class="badge status-pill bg-{{ $statusClass }}">
                                {{ ucfirst($clearance->status) }}
                            </span>
                        </td>
                        <td>
                            @if($clearance->remarks)
                                <span class="remark-preview" title="{{ $clearance->remarks }}">
                                    <i class="fas fa-comment"></i> View
                                </span>
                            @else
                                <span class="cell-muted">No remarks</span>
                            @endif
                        </td>
                        <td>
                            <div class="request-date">{{ $clearance->created_at->format('M d, Y') }}</div>
                            <div class="table-sub">{{ $clearance->created_at->format('h:i A') }}</div>
                        </td>
                        <td>
                            <div class="actions-cell">
                            @if($canActOnClearance && $clearance->status == 'pending')
                                <button class="btn btn-success btn-sm approve-btn" 
                                        data-id="{{ $clearance->id }}"
                                        title="Approve this clearance"
                                        onclick="approveClearance({{ $clearance->id }})">
                                    <i class="fas fa-check"></i>
                                </button>
                                
                                <button class="btn btn-danger btn-sm reject-btn" 
                                        data-id="{{ $clearance->id }}"
                                        title="Reject this clearance"
                                        type="button"
                                        onclick="rejectClearance({{ $clearance->id }})">
                                    <i class="fas fa-times"></i>
                                </button>
                            @else
                                <span class="cell-muted">No actions</span>
                            @endif
                            </div>
                        </td>
                    </tr>

                    <!-- Reject Modal for each clearance -->
                    @if($canUpdateClearance)
                    <div class="modal fade" id="rejectModal{{ $clearance->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('admin.clearances.reject', $clearance) }}" 
                                      method="POST" id="rejectForm{{ $clearance->id }}">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title">Reject Clearance</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p><strong>Student:</strong> {{ $clearance->student->name }}</p>
                                        <div class="mb-3">
                                            <label for="remarks{{ $clearance->id }}" class="form-label">Remarks <span class="text-danger">*</span></label>
                                            <textarea name="remarks" 
                                                      id="remarks{{ $clearance->id }}" 
                                                      class="form-control" 
                                                      rows="3" 
                                                      required 
                                                      placeholder="Please specify reason for rejection"></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger">
                                            Reject Clearance
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endif
                    @empty
                    <tr>
                        <td colspan="{{ $canUpdateClearance && (request('status') == 'pending' || !request('status')) ? '12' : '11' }}" 
                            class="text-center">
                            No clearances found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-center">
            {{ $clearances->links() }}
        </div>
    </div>
</div>
</div>

@if($canUpdateClearance)
<div class="modal fade" id="approveItemModal" tabindex="-1" role="dialog" aria-labelledby="approveItemModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="approveItemForm" method="POST">
                @csrf
                <input type="hidden" name="status" value="approved">
                <div class="modal-header">
                    <h5 class="modal-title" id="approveItemModalLabel">Approve Checklist Item</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="approved-stamp-preview">Approved</div>
                    <p class="mb-2">
                        You are approving: <strong id="approveItemName">Checklist Item</strong>
                    </p>
                    <div class="form-group mb-0">
                        <label for="approved_by_name" class="font-weight-bold">Signer Name</label>
                        <input
                            type="text"
                            class="form-control"
                            id="approved_by_name"
                            name="approved_by_name"
                            maxlength="255"
                            required
                        >
                        <small class="form-text text-muted">This name will be saved as the staff signatory for this approval.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check mr-1"></i> Confirm Approval
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
    @if($canUpdateClearance)
    let approveItemModalRef = null;

    function openApproveItemModal(button) {
        const action = button.getAttribute('data-action');
        const itemName = button.getAttribute('data-item-name') || 'Checklist Item';
        const staffName = button.getAttribute('data-staff-name') || '';
        const form = document.getElementById('approveItemForm');
        const itemNameNode = document.getElementById('approveItemName');
        const signerInput = document.getElementById('approved_by_name');
        const modalNode = document.getElementById('approveItemModal');

        if (!form || !modalNode) {
            return;
        }

        form.setAttribute('action', action);
        if (itemNameNode) {
            itemNameNode.textContent = itemName;
        }
        if (signerInput) {
            signerInput.value = staffName;
            signerInput.focus();
            signerInput.select();
        }

        if (window.bootstrap && window.bootstrap.Modal) {
            approveItemModalRef = approveItemModalRef || new window.bootstrap.Modal(modalNode);
            approveItemModalRef.show();
            return;
        }

        if (window.jQuery) {
            window.jQuery(modalNode).modal('show');
        }
    }

    // Select all checkboxes
    document.getElementById('selectAll')?.addEventListener('change', function() {
        var checkboxes = document.getElementsByClassName('clearance-checkbox');
        for(var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = this.checked;
        }
    });

    // Approve single clearance
    function approveClearance(id) {
        if(confirm('Approve this clearance?')) {
            fetch('{{ url('/admin/clearances') }}/' + id + '/approve', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
    }

    // Bulk approve
    function bulkApprove() {
        var checkboxes = document.getElementsByClassName('clearance-checkbox');
        var selectedIds = [];
        
        for(var i = 0; i < checkboxes.length; i++) {
            if(checkboxes[i].checked) {
                selectedIds.push(checkboxes[i].value);
            }
        }
        
        if(selectedIds.length === 0) {
            alert('Please select at least one clearance');
            return;
        }
        
        if(confirm('Approve ' + selectedIds.length + ' selected clearances?')) {
            fetch('{{ route("admin.clearances.bulk-approve") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({clearance_ids: selectedIds})
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
    }

    // Reject single clearance
    function rejectClearance(id) {
        var remarks = prompt('Please provide the reason for rejection:');

        if (remarks === null) {
            return;
        }

        remarks = remarks.trim();

        if (!remarks) {
            alert('Remarks are required to reject a clearance.');
            return;
        }

        fetch('{{ url('/admin/clearances') }}/' + id + '/reject', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ remarks: remarks })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Unable to reject clearance'));
            }
        })
        .catch(() => {
            alert('Error: Unable to reject clearance');
        });
    }
    @endif
</script>
@endpush

@endsection