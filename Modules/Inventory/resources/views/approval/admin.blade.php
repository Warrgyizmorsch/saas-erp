@extends('shared::layouts.app')
@section('content')

    @php
        $isFilterActive = request()->filled('rs_code')
            || request()->filled('user')
            || request()->filled('status')
            || request()->filled('project');
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
                    <!-- <a href="{{ route('request-slip.create') }}" class="btn btn-primary">
                        <i class="feather-plus me-2"></i>
                        <span>Create Request Slip</span>
                    </a> -->

                </div>

            </div>

            {{-- Mobile open toggle --}}
            <div class="d-md-none d-flex align-items-center">
                <a href="javascript:void(0)" class="page-header-right-open-toggle">
                    <i class="feather-align-right fs-20"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- FILTER COLLAPSE --}}
    <div id="collapseOne"
         class="accordion-collapse collapse page-header-collapse {{ $isFilterActive ? 'show' : '' }}">
        <div class="accordion-body pb-2">
            <form method="GET" action="{{ route('request-slip.index') }}">
                <div class="row">

                    {{-- Status --}}
                    <div class="col-lg-3 mb-3">
                        <label class="form-label fw-semibold mb-1">Search by Status</label>
                        <select name="status" class="form-control" data-select2-selector="status">
                            <option value="" data-bg="bg-indigo" {{ request('status') == '' ? 'selected' : '' }}>
                                All Status
                            </option>
                            <option value="Pending" data-bg="bg-warning" {{ request('status') == 'Pending' ? 'selected' : '' }}>
                                Pending
                            </option>
                            <option value="Rejected" data-bg="bg-danger" {{ request('status') == 'Rejected' ? 'selected' : '' }}>
                                Rejected
                            </option>
                            <option value="pending_store" data-bg="bg-indigo" {{ request('status') == 'pending_store' ? 'selected' : '' }}>
                                Pending Store
                            </option>
                            <option value="Approved" data-bg="bg-success" {{ request('status') == 'Approved' ? 'selected' : '' }}>
                                Completed
                            </option>
                        </select>
                    </div>

                    {{-- RS Code --}}
                    <div class="col-lg-3 mb-3">
                        <label class="form-label fw-semibold mb-1">Search by RS Code</label>
                        <div class="input-group">
                            <input type="text" name="rs_code" class="form-control"
                                   placeholder="e.g. 005 or #005" value="{{ request('rs_code') }}">
                        </div>
                    </div>

                    {{-- User --}}
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

                    {{-- Project --}}
                    <div class="col-lg-3 mb-3">
                        <label class="form-label fw-semibold mb-1">Search by Project</label>
                        <select name="project" class="form-control" data-select2-selector="project">
                            <option value="">All Projects</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}"
                                    {{ (string)request('project') === (string)$project->id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Buttons --}}
                    <div class="col-12">
                        <div class="d-flex justify-content-end gap-2 mt-1">
                            <a href="{{ route('request-slip.index') }}" class="btn btn-light">Reset</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="feather-search me-1"></i>
                                Apply Filters
                            </button>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- MAIN CONTENT --}}
    <div class="main-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full">
                    <div class="card-body">

                        <div class="table-responsive">
                            <table class="table" id="requestSlipList">
                                <thead>
                                    <tr>
                                        <th>Sr</th>
                                        <th>RS Code</th>
                                        <th>Project</th>
                                        <th>Created By</th>
                                        <th>Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($requestSlips as $index => $rs)
                                        {{-- Yahan check ho raha hai is_exited --}}
                                        <tr style="{{ ( $rs->is_exited) ? 'background-color: #f8d7da !important;' : '' }}">

                                            <td>{{ $requestSlips->firstItem() + $index }}</td>
                                            <td>#{{ $rs->requisition_slip_no }}</td>

                                            <td>
                                                <span class="text-truncate-1-line">
                                                    {{ $rs->project->name ?? 'N/A' }}
                                                </span>
                                            </td>

                                            <td>{{ $rs->creator?->name ?? 'N/A' }}</td>

                                            <td>
                                                @switch($rs->status)
                                                    @case('Pending')
                                                        <span class="badge bg-soft-warning text-warning">Pending</span>
                                                        @break
                                                    @case('Rejected')
                                                    @case('rejected_hod')
                                                        <span class="badge bg-soft-danger text-danger">Rejected</span>
                                                        @break
                                                    @case('Approved')
                                                        <span class="badge bg-soft-success text-success">Approved</span>
                                                        @break
                                                    @case('pending_store')
                                                        <span class="badge bg-soft-info text-info">Pending Store</span>
                                                        @break
                                                    @case('Hold')
                                                        <span class="badge bg-soft-danger text-danger">Hold</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-soft-secondary text-secondary">Unknown</span>
                                                @endswitch
                                            </td>

                                            {{-- Actions --}}
                                            <td class="d-flex gap-2 justify-content-center">


                                                {{-- View --}}
                                                <a href="{{ route('request-slip.show', $rs->id) }}"
                                                   class="btn btn-light btn-sm" title="View Details">
                                                    <i class="fa fa-eye"></i>
                                                </a>

                                                {{-- ✅ SINGLE STATUS BUTTON (Approve/Reject inside) --}}
                                                @if($rs->status == 'Pending')

                                                    @if(auth()->user()->isAdmin() || auth()->user()->isAccount())
                                                        <button type="button"
                                                                class="btn btn-primary btn-sm"
                                                                title="Update Status"
                                                                data-bs-toggle="offcanvas"
                                                                data-bs-target="#statusUpdate{{ $rs->id }}">
                                                            <i class="feather-check-circle me-1"></i> Update Status
                                                        </button>
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>

                                        {{-- ✅ OFFCANVAS: STATUS UPDATE --}}
                                        <div class="offcanvas offcanvas-end"
                                             tabindex="-1"
                                             id="statusUpdate{{ $rs->id }}">
                                            <form method="POST" action="{{ route('requisition.update-status', $rs->id) }}">
                                                @csrf

                                                <div class="offcanvas-header ht-80 px-4 border-bottom border-gray-5">
                                                    <div>
                                                        <h2 class="fs-16 fw-bold mb-0">Update Request Slip Status</h2>
                                                        <small class="fs-12 text-muted">Choose Approved / Rejected and submit.</small>
                                                    </div>
                                                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                                                </div>

                                                <div class="offcanvas-body px-4">

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">RS Code</label>
                                                        <input type="text" class="form-control" value="#{{ $rs->requisition_slip_no }}" disabled>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Project</label>
                                                        <input type="text" class="form-control" value="{{ $rs->project?->name ?? 'N/A' }}" disabled>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Select Status</label>
                                                        <select name="status" class="form-control" data-select2-selector="status" required>
                                                            <option value="">-- Select --</option>
                                                            <option value="Approved">Approved</option>
                                                            <option value="Rejected">Rejected</option>
                                                            <option value="Hold">Hold</option>
                                                        </select>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Remarks (optional)</label>
                                                        <textarea name="remarks" rows="4" class="form-control"
                                                                  placeholder="Reason / note..."></textarea>
                                                    </div>

                                                </div>

                                                <div class="px-4 gap-2 d-flex align-items-center justify-content-between ht-80 border-top border-gray-2">
                                                    <button type="submit" class="btn btn-primary w-50">Submit</button>
                                                    <button type="button" class="btn btn-light w-50" data-bs-dismiss="offcanvas">
                                                        Cancel
                                                    </button>
                                                </div>
                                            </form>
                                        </div>

                                        {{-- OFFCANVAS: DELETE --}}
                                        <div class="offcanvas offcanvas-end" tabindex="-1" id="deleteInventory{{ $rs->id }}">
                                            <form method="POST" action="{{ route('request-slip.destroy', $rs->id) }}">
                                                @csrf
                                                @method('DELETE')

                                                <div class="offcanvas-header ht-80 px-4 border-bottom border-gray-5">
                                                    <h2 class="fs-16 fw-bold mb-0">Delete Request Slip</h2>
                                                    <button type="button" class="btn-close btn-close-gray ms-auto" data-bs-dismiss="offcanvas"></button>
                                                </div>

                                                <div class="offcanvas-body">
                                                    <p class="fs-15">
                                                        Are you sure you want to delete
                                                        <strong>#{{ $rs->requisition_slip_no }}</strong>?
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
                                            <td colspan="6" class="text-center text-muted">
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

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection