@extends('shared::layouts.app')
@section('content')

    @php
    $isFilterActive = request()->filled('rs_code')
    || request()->filled('user')
    || request()->filled('status');
    @endphp

    {{-- PAGE HEADER --}}
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Request Slip</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="/dashboard">Home</a>
                </li>
                <li class="breadcrumb-item active">Request Slip</li>
            </ul>
        </div>

        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">

                {{-- Mobile back (when right panel open) --}}
                <div class="d-flex d-md-none">
                    <a href="javascript:void(0)" class="page-header-right-close-toggle">
                        <i class="feather-arrow-left me-2"></i>
                        <span>Back</span>
                    </a>
                </div>

                {{-- Right side buttons --}}
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">

                    {{-- Filter / Collapse trigger --}}
                    <a href="javascript:void(0)"
                        class="btn btn-icon btn-light-brand {{ $isFilterActive ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseOne"
                        aria-expanded="{{ $isFilterActive ? 'true' : 'false' }}"
                        aria-controls="collapseOne">
                        <i class="feather-filter"></i>
                    </a>

                    {{-- Create Request Slip --}}

                    <a href="{{ route('request-slip.safety.create') }}" class="btn btn-primary">
                        <i class="feather-plus me-2"></i>
                        <span>Create Request Slip Safety</span>
                    </a>

                </div>

            </div>

            {{-- Mobile open toggle (hamburger right panel) --}}
            <div class="d-md-none d-flex align-items-center">
                <a href="javascript:void(0)" class="page-header-right-open-toggle">
                    <i class="feather-align-right fs-20"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- FILTER COLLAPSE (STAYS OPEN IF FILTER APPLIED) --}}
    <div id="collapseOne"
        class="accordion-collapse collapse page-header-collapse {{ $isFilterActive ? 'show' : '' }}">
        <div class="accordion-body pb-2">
            <form method="GET" action="{{ route('request-slip.safety.index') }}">
                <div class="row">

                    {{-- Status --}}
                    <div class="col-lg-3 mb-3">
                        <label class="form-label fw-semibold mb-1">Search by Status</label>
                        <select name="status" class="form-control" data-select2-selector="status">
                            <option value="" data-bg="bg-indigo"
                                {{ request('status') == '' ? 'selected' : '' }}>
                                All Status
                            </option>
                            <option value="Pending" data-bg="bg-warning"
                                {{ request('status') == 'Pending' ? 'selected' : '' }}>
                                Pending
                            </option>
                            <option value="Rejected" data-bg="bg-danger"
                                {{ request('status') == 'Rejected' ? 'selected' : '' }}>
                                Rejected
                            </option>
                            <option value="pending_store" data-bg="bg-indigo"
                                {{ request('status') == 'pending_store' ? 'selected' : '' }}>
                                Pending Store
                            </option>
                            <option value="Approved" data-bg="bg-success"
                                {{ request('status') == 'Approved' ? 'selected' : '' }}>
                                Completed
                            </option>
                        </select>
                    </div>

                    {{-- RS Code --}}
                    <div class="col-lg-3 mb-3">
                        <label class="form-label fw-semibold mb-1">Search by RS Code</label>
                        <div class="input-group">
                            <input
                                type="text"
                                name="rs_code"
                                class="form-control"
                                placeholder="e.g. 005 or #005"
                                value="{{ request('rs_code') }}">
                        </div>
                    </div>

                    {{-- Created By (User dropdown) --}}
                    <div class="col-lg-3 mb-3">
                        <label class="form-label fw-semibold mb-1">Search by User</label>
                        <select name="user" class="form-control" data-select2-selector="user">
                            <option value="">All Users</option>
                            @foreach($users as $userOption)
                            <option value="{{ $userOption->id }}"
                                {{ (string)request('user') === (string)$userOption->id ? 'selected' : '' }}>
                                {{ $userOption->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Buttons --}}
                    <div class="col-12">
                        <div class="d-flex justify-content-end gap-2 mt-1">
                            <a href="{{ route('request-slip.safety.index') }}" class="btn btn-light">
                                Reset
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="feather-search me-1"></i>
                                Apply Filters
                            </button>
                        </div>
                    </div>

                </div> {{-- row --}}
            </form>
        </div> {{-- accordion-body --}}
    </div> {{-- collapseOne --}}

    {{-- MAIN CONTENT --}}
    <div class="main-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full">
                    <div class="card-body">

                        <div class="table-responsive">
                            <table class="table table-striped" id="requestSlipList">
                                <thead>
                                    <tr>
                                        <th>Sr</th>
                                        <th>RS Code</th>
                                        <th>Created By</th>
                                        <th>Created Date</th>
                                        <th>Status</th>
                                        <th>Issue Status </th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($requestSlips as $index => $rs)
                                    <tr class="single-item">

                                        {{-- Serial No with Pagination --}}
                                        <td>{{ $requestSlips->firstItem() + $index }}</td>

                                        {{-- RS Code --}}
                                        <td>#{{ $rs->requisition_slip_no}}</td>
                                        
                                        {{-- Created By --}}
                                        <td>{{ $rs->creator?->name ?? 'N/A' }}</td>

                                        <td>{{ \Carbon\Carbon::parse($rs->created_on)->format('d-m-Y') }}</td>

                                        {{-- Status Badge --}}
                                        <td>
                                            @switch($rs->status)

                                            @case('Pending')
                                            <span class="badge bg-soft-warning text-warning">Pending</span>
                                            @break

                                            @case('Rejected')
                                            <span class="badge bg-soft-danger text-danger">Rejected</span>
                                            @break

                                            @case('Approved')
                                            <span class="badge bg-soft-success text-success">Approved</span>
                                            @break

                                            @case('Hold')
                                            <span class="badge bg-soft-danger text-danger">Hold</span>
                                            @break

                                            @default
                                            <span class="badge bg-soft-secondary text-secondary">Unknown</span>

                                            @endswitch
                                        </td>

                                        <td>
                                            @if(!$rs->issue)
                                            <span class="badge bg-soft-warning text-warning">Pending</span>
                                            @elseif($rs->issue->status === 'Partially Issued')
                                            <span class="badge bg-soft-warning text-warning">{{$rs->issue->status}}</span>
                                            @else($rs->issue->status === 'Issued')
                                            <span class="badge bg-soft-success text-success">{{$rs->issue->status}}</span>

                                            @endif
                                        </td>

                                        {{-- Actions --}}
                                        <td class="d-flex gap-2 justify-content-center">

                                            {{-- Edit for Supervisor if rejected by HOD --}}
                                            @if(
                                            auth()->user()->isAccount() &&
                                            $rs->status == 'rejected_hod' &&
                                            $rs->created_by == auth()->id()
                                            )
                                            <a href="{{ route('request-slip.edit', $rs->id) }}"
                                                class="btn btn-light btn-sm"
                                                title="Edit Request Slip">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            @endif

                                            {{-- Admin edit --}}
                                            @if(auth()->user()->isSuperAdmin())
                                            <a href="{{ route('request-slip.edit', $rs->id) }}"
                                                class="btn btn-light btn-sm"
                                                title="Edit Request Slip">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            @endif

                                            {{-- HR edit --}}
                                            @if(
                                                auth()->user()->isHR() &&
                                                in_array($rs->status, ['Hold', 'Pending'])
                                            )
                                                <a href="{{ route('request-slip.safety.edit', $rs->id) }}"
                                                    class="btn btn-light btn-sm"
                                                    title="Edit Request Slip">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                            @endif

                                            {{-- View Details --}}
                                            <a href="{{ route('request-slip.safety.show', $rs->id) }}"
                                                class="btn btn-light btn-sm"
                                                title="View Details">
                                                <i class="fa fa-eye"></i>
                                            </a>

                                            {{-- HR Delete --}}
                                            @if(
                                                auth()->user()->isHR() &&
                                                in_array($rs->status, ['Hold', 'Pending'])
                                            )
                                                <button type="button" class="btn btn-light btn-sm"
                                                    data-bs-toggle="offcanvas"
                                                    data-bs-target="#deleteInventory{{ $rs->id }}">
                                                    <i class="feather feather-trash-2"></i>
                                                </button>
                                            @endif                                            

                                            {{-- <a href="{{ route('request-slip.destroy', $rs->id) }}"
                                            class="btn btn-light btn-sm"
                                            title="Delete">
                                            <i class="feather feather-trash-2"></i>
                                            </a> --}}


                                            {{-- History Offcanvas (Admin only) --}}
                                            @if(auth()->user()->isSuperAdmin())
                                            <a href="javascript:void(0)"
                                                class="btn btn-light btn-sm"
                                                title="View History"
                                                data-bs-toggle="offcanvas"
                                                data-bs-target="#rsHistory{{ $rs->id }}">
                                                <i class="fa fa-history"></i>
                                            </a>

                                            {{-- OFFCANVAS: Request Slip History --}}
                                            <div class="offcanvas offcanvas-end"
                                                tabindex="-1"
                                                id="rsHistory{{ $rs->id }}">
                                                <div class="offcanvas-header ht-80 px-4 border-bottom border-gray-5">
                                                    <div>
                                                        <h2 class="fs-16 fw-bold mb-1">Request Slip History</h2>
                                                        <small class="fs-12 text-muted">
                                                            Track progress, comments and actions taken.
                                                        </small>
                                                    </div>
                                                    <button type="button"
                                                        class="btn-close"
                                                        data-bs-dismiss="offcanvas"></button>
                                                </div>

                                                {{-- Info Bar --}}
                                                <div class="py-3 px-4 d-flex justify-content-between align-items-center border-bottom border-bottom-dashed bg-gray-100">
                                                    <div>
                                                        <span class="fw-bold text-dark">RS ID:</span>
                                                        <span class="fs-12 fw-bold text-primary">
                                                            #{{ str_pad($rs->rs_id, 3, '0', STR_PAD_LEFT) }}
                                                        </span>
                                                    </div>
                                                    
                                                </div>

                                                {{-- Activity Feed --}}
                                                <div class="offcanvas-body overflow-auto py-3"
                                                    style="max-height: calc(100vh - 160px);">
                                                    @php
                                                    $histories = $rs->histories->sortBy('created_at');
                                                    @endphp

                                                    @if($histories->isEmpty())
                                                    <p class="text-muted mb-0">No history available.</p>
                                                    @else
                                                    <div class="card-body custom-card-action pb-3">
                                                        <ul class="list-unstyled activity-feed mb-0">
                                                            @foreach($histories as $history)
                                                            @php
                                                            // Normalize
                                                            $actionRaw = $history->action ?? '';
                                                            $actionKey = strtolower(trim($actionRaw));

                                                            $statusRaw = $history->status ?? '';
                                                            $statusKey = strtolower(trim($statusRaw));

                                                            // Action → label + class (case-insensitive)
                                                            switch ($actionKey) {
                                                            case 'created':
                                                            $feedClass = 'feed-item-success';
                                                            $label = 'Request Slip created';
                                                            break;
                                                            case 'updated':
                                                            $feedClass = 'feed-item-info';
                                                            $label = 'Request Slip updated';
                                                            break;
                                                            case 'pending_store':
                                                            $feedClass = 'feed-item-warning';
                                                            $label = 'Pending Store';
                                                            break;
                                                            case 'pending_hod':
                                                            case 'pending':
                                                            $feedClass = 'feed-item-warning';
                                                            $label = 'Pending';
                                                            break;
                                                            case 'rejected_hod':
                                                            case 'rejected':
                                                            $feedClass = 'feed-item-danger';
                                                            $label = 'Rejected';
                                                            break;
                                                            case 'approved':
                                                            $feedClass = 'feed-item-primary';
                                                            $label = 'Approved / Completed';
                                                            break;
                                                            default:
                                                            $feedClass = 'feed-item-secondary';
                                                            $label = ucfirst(str_replace('_', ' ', $actionRaw));
                                                            break;
                                                            }

                                                            $userName = $history->user?->name ?? 'System';
                                                            $dateOnly = $history->created_at?->format('d M Y');
                                                            $fullDateTime = $history->created_at?->format('d M Y, h:i A');

                                                            // Remarks (try JSON decode for changeset)
                                                            $remarksText = $history->remarks ?? '-';
                                                            $remarksJson = null;

                                                            if (is_string($remarksText) && strlen($remarksText) > 0) {
                                                            $tmp = json_decode($remarksText, true);
                                                            if (json_last_error() === JSON_ERROR_NONE && is_array($tmp)) {
                                                            $remarksJson = $tmp;
                                                            }
                                                            }

                                                            // If not JSON, show in chunks
                                                            $chunks = [];
                                                            if (!$remarksJson) {
                                                            $words = preg_split('/\s+/', strip_tags((string)$remarksText));
                                                            $chunks = array_chunk($words, 60); // 60 words per paragraph (cleaner)
                                                            }

                                                            // Status badge class (case-insensitive)
                                                            $statusBadge = 'bg-soft-secondary text-secondary';
                                                            if (in_array($statusKey, ['approved'], true)) $statusBadge = 'bg-soft-success text-success';
                                                            elseif (in_array($statusKey, ['rejected', 'rejected_hod'], true)) $statusBadge = 'bg-soft-danger text-danger';
                                                            elseif (in_array($statusKey, ['pending', 'pending_hod', 'pending_store'], true)) $statusBadge = 'bg-soft-warning text-warning';
                                                            @endphp

                                                            <li class="feed-item {{ $feedClass }} py-2">
                                                                <div class="d-flex justify-content-between align-items-stretch">

                                                                    <div class="flex-grow-1" style="word-break: break-word; white-space: normal;">
                                                                        <div class="card border-0 shadow-sm mb-0">
                                                                            <div class="card-body py-2 px-3">

                                                                                {{-- Header --}}
                                                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                                                    <div class="pe-2">
                                                                                        <span class="fw-semibold d-block">
                                                                                            {{ $label }} by {{ $userName }}
                                                                                        </span>
                                                                                        @if($dateOnly)
                                                                                        <span class="fs-12 text-muted">[{{ $dateOnly }}]</span>
                                                                                        @endif
                                                                                    </div>

                                                                                    <a href="javascript:void(0);"
                                                                                        class="avatar-text avatar-sm rounded-circle shadow-sm d-flex align-items-center justify-content-center"
                                                                                        style="background:#F1F1F1;"
                                                                                        data-bs-toggle="tooltip"
                                                                                        data-bs-trigger="hover"
                                                                                        title="{{ $fullDateTime }}">
                                                                                        <i class="feather feather-clock fs-12 text-dark"></i>
                                                                                    </a>
                                                                                </div>

                                                                                {{-- Status --}}
                                                                                <div class="fs-12 mt-1">
                                                                                    <strong>Status:</strong>
                                                                                    <span class="badge {{ $statusBadge }}">
                                                                                        {{ $history->status ?? 'N/A' }}
                                                                                    </span>
                                                                                </div>

                                                                                {{-- Remarks --}}
                                                                                <div class="fs-12 text-muted mt-2">

                                                                                    @if($remarksJson)
                                                                                    {{-- Pretty JSON changeset --}}
                                                                                    @if(!empty($remarksJson['slip_changes']))
                                                                                    <div class="mb-2">
                                                                                        <strong>Slip Changes:</strong>
                                                                                        <ul class="mb-0 ps-3">
                                                                                            @foreach($remarksJson['slip_changes'] as $field => $chg)
                                                                                            <li>
                                                                                                <span class="fw-semibold">{{ ucfirst(str_replace('_',' ',$field)) }}</span>:
                                                                                                <span class="text-danger">{{ $chg['from'] ?? '-' }}</span>
                                                                                                →
                                                                                                <span class="text-success">{{ $chg['to'] ?? '-' }}</span>
                                                                                            </li>
                                                                                            @endforeach
                                                                                        </ul>
                                                                                    </div>
                                                                                    @endif

                                                                                    @if(!empty($remarksJson['row_changes']))
                                                                                    <div>
                                                                                        <strong>Item Changes:</strong>

                                                                                        @if(!empty($remarksJson['row_changes']['added']))
                                                                                        <div class="mt-1">
                                                                                            <span class="fw-semibold">Added:</span>
                                                                                            <ul class="mb-0 ps-3">
                                                                                                @foreach($remarksJson['row_changes']['added'] as $r)
                                                                                                <li>{{ $r['item_name'] ?? ('Item #'.($r['item_id'] ?? '')) }} (Qty: {{ $r['quantity'] ?? 0 }})</li>
                                                                                                @endforeach
                                                                                            </ul>
                                                                                        </div>
                                                                                        @endif

                                                                                        @if(!empty($remarksJson['row_changes']['updated']))
                                                                                        <div class="mt-1">
                                                                                            <span class="fw-semibold">Updated:</span>
                                                                                            <ul class="mb-0 ps-3">
                                                                                                @foreach($remarksJson['row_changes']['updated'] as $u)
                                                                                                <li>
                                                                                                    {{ $u['item_name'] ?? ('Row #'.($u['id'] ?? '')) }}
                                                                                                    <ul class="mb-0 ps-3">
                                                                                                        @foreach($u['changes'] as $k => $v)
                                                                                                        <li>{{ ucfirst(str_replace('_',' ',$k)) }}: {{ $v['from'] ?? '-' }} → {{ $v['to'] ?? '-' }}</li>
                                                                                                        @endforeach
                                                                                                    </ul>
                                                                                                </li>
                                                                                                @endforeach
                                                                                            </ul>
                                                                                        </div>
                                                                                        @endif

                                                                                        @if(!empty($remarksJson['row_changes']['removed']))
                                                                                        <div class="mt-1">
                                                                                            <span class="fw-semibold">Removed:</span>
                                                                                            <ul class="mb-0 ps-3">
                                                                                                @foreach($remarksJson['row_changes']['removed'] as $r)
                                                                                                <li>{{ $r['item_name'] ?? ('Item #'.($r['item_id'] ?? '')) }} (Qty: {{ $r['quantity'] ?? 0 }})</li>
                                                                                                @endforeach
                                                                                            </ul>
                                                                                        </div>
                                                                                        @endif
                                                                                    </div>
                                                                                    @endif

                                                                                    @else
                                                                                    {{-- Normal text remarks --}}
                                                                                    @forelse($chunks as $chunk)
                                                                                    <p class="mb-1">{{ implode(' ', $chunk) }}</p>
                                                                                    @empty
                                                                                    <p class="mb-0">-</p>
                                                                                    @endforelse
                                                                                    @endif

                                                                                </div>

                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            @endforeach

                                                        </ul>
                                                    </div>
                                                    @endif
                                                </div>

                                                <div class="px-4 d-flex justify-content-end ht-80 border-top">
                                                    <button type="button"
                                                        class="btn btn-light"
                                                        data-bs-dismiss="offcanvas">
                                                        Close
                                                    </button>
                                                </div>
                                            </div>
                                            {{-- END OFFCANVAS --}}
                                            @endif
                                        </td>
                                    </tr>
                                    <div class="offcanvas offcanvas-end" tabindex="-1" id="deleteInventory{{ $rs->id }}">
                                        <form method="POST" action="{{ route('request-slip.safety.destroy', $rs->id) }}">
                                            @csrf
                                            @method('DELETE')

                                            <div class="offcanvas-header ht-80 px-4 border-bottom border-gray-5">
                                                <h2 class="fs-16 fw-bold mb-0">Delete Request Slip</h2>
                                                <button type="button" class="btn-close btn-close-gray ms-auto" data-bs-dismiss="offcanvas"></button>
                                            </div>

                                            <div class="offcanvas-body">
                                                <p class="fs-15">
                                                    Are you sure you want to delete
                                                    <strong>{{ $rs->name }}</strong>?
                                                </p>
                                            </div>

                                            <div class="px-4 gap-2 d-flex align-items-center justify-content-between ht-80 border-top border-gray-2">
                                                <button type="submit" class="btn btn-primary w-50">Yes, Delete</button>

                                                <button type="button" class="btn btn-danger w-50" data-bs-dismiss="offcanvas">
                                                    Cancel
                                                </button>
                                            </div>
                                        </form>
                                    </div>

                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">
                                            No Request Slips found
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>

                            {{-- Pagination --}}
                            <div class="mt-3">
                                {{ $requestSlips->links('pagination::bootstrap-5') }}
                            </div>
                        </div> {{-- table-responsive --}}

                    </div>
                </div>
            </div>
        </div>
    </div>


    <script>
        $('.openDelete').click(function() {
            let id = $(this).data('id');
            let name = $(this).data('name');

            // Show name in modal
            $('#deleteItemName').text(name);

            // Set form action dynamically
            $('#deleteForm').attr('action', '/request-slip/destroy/' + id);
        });
    </script>



@endsection