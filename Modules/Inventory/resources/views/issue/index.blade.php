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
                <h5 class="m-b-10">Issue Slip</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="/dashboard">Home</a>
                </li>
                <li class="breadcrumb-item active">Issue Slip</li>
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
                        data-bs-toggle="collapse" data-bs-target="#collapseOne"
                        aria-expanded="{{ $isFilterActive ? 'true' : 'false' }}" aria-controls="collapseOne">
                        <i class="feather-filter"></i>
                    </a>

                    {{-- Create Request Slip --}}

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
    <div id="collapseOne" class="accordion-collapse collapse page-header-collapse {{ $isFilterActive ? 'show' : '' }}">
        <div class="accordion-body pb-2">
            <form method="GET" action="{{ route('issue.index') }}">
                <div class="row">

                    {{-- Status --}}
                    <div class="col-lg-3 mb-3">
                        <label class="form-label fw-semibold mb-1">Search by Status</label>
                        <select name="status" class="form-control" data-select2-selector="status">
                            <option value="" data-bg="bg-indigo" {{ request('status') == '' ? 'selected' : '' }}>
                                All Status
                            </option>
                            <option value="Partially Issued" data-bg="bg-warning" {{ request('status') == 'Partially Issued' ? 'selected' : '' }}>
                                Partially Issued
                            </option>
                            <option value="Issued" data-bg="bg-danger" {{ request('status') == 'Issued' ? 'selected' : '' }}>
                                Issued
                            </option>
                        </select>
                    </div>

                    {{-- RS Code --}}
                    <div class="col-lg-3 mb-3">
                        <label class="form-label fw-semibold mb-1">Search by RS Code</label>
                        <div class="input-group">
                            <input type="text" name="rs_code" class="form-control" placeholder="e.g. 005 or 005"
                                value="{{ request('rs_code') }}">
                        </div>
                    </div>

                    {{-- Created By (User dropdown) --}}
                    <div class="col-lg-3 mb-3">
                        <label class="form-label fw-semibold mb-1">Search by User</label>
                        <select name="user" class="form-control" data-select2-selector="user">
                            <option value="">All Users</option>
                            @foreach($users as $userOption)
                            <option value="{{ $userOption->id }}" {{ (string) request('user') === (string) $userOption->id ? 'selected' : '' }}>
                                {{ $userOption->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Project dropdown --}}
                    <div class="col-lg-3 mb-3">
                        <label class="form-label fw-semibold mb-1">Search by Project</label>
                        <select name="project" class="form-control" data-select2-selector="status">
                            <option value="">All Projects</option>
                            @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ (string) request('project') === (string) $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Buttons --}}
                    <div class="col-12">
                        <div class="d-flex justify-content-end gap-2 mt-1">
                            <a href="{{ route('issue.index') }}" class="btn btn-light">
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
                                        <th> Requisition No </th>
                                        <th>RS Name</th>
                                        <th> Requisition Date </th>
                                        <th>Created By</th>
                                        <th>Status</th>
                                        <th>Approved RS </th>
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

                                        {{-- RS Name --}}
                                        <td>
                                            <span class="text-truncate-1-line">
                                                {{ $rs->project->name ?? 'N/A' }}
                                            </span>
                                        </td>

                                        <td>
                                            {{date('Y-m-d', strtotime($rs->created_on))}}
                                        </td>



                                        {{-- Created By --}}
                                        <td>{{ $rs->creator?->name ?? 'N/A' }}</td>

                                        {{-- Status Badge --}}
                                        <td>
                                            @php
                                            $issueStatus = $rs->issue->status ?? null; // ✅ Issue status (if exists)
                                            @endphp

                                            @if($issueStatus)
                                            @if($issueStatus === 'Issued')
                                            {{-- Waise to query me hide ho rahi hai, safety ke liye --}}
                                            <span class="badge bg-soft-success text-success">Issued</span>
                                            @elseif($issueStatus === 'Partially Issued')
                                            <span class="badge bg-soft-warning text-warning">Partially Issued</span>
                                            @else
                                            <span
                                                class="badge bg-soft-secondary text-secondary">{{ $issueStatus }}</span>
                                            @endif
                                            @elseif($rs->status == 'Approved HOD')
                                            <span class="badge bg-soft-success text-success">Approved HOD</span>
                                            @else
                                            <span class="badge bg-soft-success text-success">Approved</span>
                                            @endif
                                        </td>

                                        <td>
                                            @if($rs->status !== 'Approved')
                                            <button type="button"
                                                                class="btn btn-primary btn-sm"
                                                                title="Update Status"
                                                                data-bs-toggle="offcanvas"
                                                                data-bs-target="#statusUpdate{{ $rs->id }}">
                                                            <i class="feather-check-circle me-1"></i> Update Status
                                                        </button>
                                              @endif          
                                        </td>


                                        {{-- Actions --}}
                                        <td class="d-flex gap-2 justify-content-center">



                                            {{-- View Details --}}
                                            <!-- <a href="{{ route('issue.show', $rs->id) }}"
                                                    class="btn btn-light btn-sm"
                                                    title="View Details">
                                                    <i class="fa fa-eye"></i>
                                                </a> -->
                                            @if($rs->status !== 'Approved HOD')
                                            <a href="{{ route('issue.create') }}?req_id={{ $rs->id }}"
                                                class="btn btn-light btn-sm" title="convert">
                                                <i class="feather feather-refresh-ccw"></i>
                                            </a>
                                            @else
                                            <span class="btn btn-light btn-sm invisible">
                                                <i class="feather feather-refresh-ccw"></i>
                                            </span>
                                            @endif



                                        </td>
                                    </tr>

                                    {{-- ✅ OFFCANVAS: STATUS UPDATE --}}
                                            <div class="offcanvas offcanvas-end" tabindex="-1" id="statusUpdate{{ $rs->id }}">
                                                <form method="POST" action="{{ route('requisition.update-status', $rs->id) }}">
                                                    @csrf
                                                    <div class="offcanvas-header ht-80 px-4 border-bottom border-gray-5">
                                                        <div>
                                                            <h2 class="fs-16 fw-bold mb-0">Update Request Slip Status</h2>
                                                            <small class="fs-12 text-muted">Choose status and submit.</small>
                                                        </div>
                                                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                                                    </div>

                                                    <div class="offcanvas-body px-4">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">RS Code</label>
                                                            <input type="text" class="form-control" value="#{{ $rs->requisition_slip_no }}" disabled>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Select Status</label>
                                                            <select name="status" class="form-control" data-select2-selector="status" required>
                                                                <option value="">-- Select --</option>
                                                                @if(auth()->user()->isHOD())
                                                                    <option value="Approved HOD">Approved HOD</option>
                                                                @else
                                                                <option value="Approved">Approved</option>
                                                                @endif
                                                                <option value="Rejected">Rejected</option>
                                                                <option value="Hold">Hold</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Remarks (optional)</label>
                                                            <textarea name="remarks" rows="4" class="form-control" placeholder="Reason / note..."></textarea>
                                                        </div>
                                                    </div>

                                                    <div class="px-4 gap-2 d-flex align-items-center justify-content-between ht-80 border-top border-gray-2">
                                                        <button type="submit" class="btn btn-primary w-50">Submit</button>
                                                        <button type="button" class="btn btn-light w-50" data-bs-dismiss="offcanvas">Cancel</button>
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

@endsection