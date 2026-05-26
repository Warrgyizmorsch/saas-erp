@extends('shared::layouts.app')
@section('content')

    {{-- ================= PAGE HEADER ================= --}}

    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Issue Slip List</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="/dashboard">Home</a>
                </li>
                <li class="breadcrumb-item active">Issue Slips Records</li>
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
                    <button id="filterBtn" class="btn btn-icon btn-light-brand" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                        <i class="feather-filter"></i>
                    </button>

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

    {{-- filterCollapse --}}
    <div class="collapse mb-3" id="filterCollapse">
        <div class="card border shadow-sm">
            <div class="card-body">
                <form method="GET" action="{{ route('issue.view-list') }}">
                    <div class="row">

                     <div class="col-md-3">
                            <label class="form-label">From Date</label>
                            <input type="date" name="from_date" class="form-control"
                                value="{{ request('from_date') }}">

                            @error('from_date')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">To Date</label>
                            <input type="date" name="to_date" class="form-control"
                                value="{{ request('to_date') }}">

                            @error('to_date')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>



                        {{-- Issue Slip No --}}
                        <div class="col-lg-3 mb-3">
                            <label class="form-label fw-semibold mb-1">Issue Slip No</label>
                            <input type="text" name="issue_no" class="form-control"
                                placeholder="Enter Issue Slip No"
                                value="{{ request('issue_no') }}">
                        </div>

                        {{-- Project Name --}}
                        <div class="col-lg-3 mb-3">
                            <label class="form-label fw-semibold mb-1">Project</label>
                            <select name="project_id" class="form-control" data-select2-selector="status">
                                <option value="">All Projects</option>
                                @foreach($projects as $project)
                                <option value="{{ $project->id }}"
                                    {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Requisition No --}}
                        <div class="col-lg-3 mb-3">
                            <label class="form-label fw-semibold mb-1">Requisition No</label>
                            <input type="text" name="req_no" class="form-control"
                                placeholder="Enter Requisition No"
                                value="{{ request('req_no') }}">
                        </div>

                        {{-- Status --}}
                        <div class="col-lg-3 mb-3">
                            <label class="form-label fw-semibold mb-1">Status</label>
                            <select name="status" class="form-control" data-select2-selector="status">
                                <option value="">All Status</option>
                                <option value="Issued" {{ request('status') == 'Issued' ? 'selected' : '' }}>Issued</option>
                                <option value="Partially Issued" {{ request('status') == 'Partially Issued' ? 'selected' : '' }}>Partially Issued</option>
                            </select>
                        </div>

                        {{-- Buttons --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2 mt-2">
                                <a href="{{ route('issue.view-list') }}" class="btn btn-light">Reset</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="feather-search me-1"></i> Apply Filters
                                </button>
                            </div>
                        </div>

                    </div>
                </form>

            </div>
        </div>
    </div>
    {{-- ================= MAIN CONTENT ================= --}}

    <div class="main-content">
        <div class="card stretch stretch-full">
            <div class="card-body p-0">

                <div class="d-flex justify-content-between align-items-center px-4 py-3">
                    <h5 class="mb-0">Recent Issue Records</h5>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Issue No & Date</th>
                                <th>Project Name</th>
                                <th>Req. No</th>

                                {{-- ✅ Proper Calculation Columns --}}
                                <th class="text-center">Total Required</th>
                                <th class="text-center">Total Issued</th>
                                <th class="text-center">Total Pending</th>

                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($issueSlipData as $index => $is)
                            @php
                            $req = $is->requisitionSlip;

                            // ✅ Total Required (RS rows quantity sum)
                            $totalRequired = $req ? (float) $req->rows->sum('quantity') : 0;

                            // ✅ Total Issued (Issue table total_issue_qty already maintained)
                            $totalIssued = (float) ($is->total_issue_qty ?? 0);

                            // ✅ Total Pending
                            $totalPending = $totalRequired - $totalIssued;
                            if ($totalPending < 0) $totalPending=0;

                                // ✅ Status (calculated)
                                $computedStatus=($totalPending <=0.000001) ? 'Issued' : 'Partially Issued' ;
                                @endphp

                                <tr>
                                <td>{{ $issueSlipData->firstItem() + $index }}</td>

                                <td>
                                    <div class="fw-bold text-dark">{{ $is->issue_slip_no }}</div>
                                    <div class="small text-muted">
                                        {{ $is->transaction_date
                                                ? \Carbon\Carbon::parse($is->transaction_date)->format('d M, Y')
                                                : 'N/A' }}
                                    </div>
                                </td>

                                <td>
                                    <span class="text-primary fw-semibold">
                                        <i class="feather-map-pin me-1 small"></i>
                                       <a href="{{route('project.show', $req->project->id)}}"> {{ $req?->project?->name ?? 'N/A' }} </a>
                                    </span>
                                </td>

                                <td>
                                    <span class="badge bg-soft-secondary text-secondary">
                                        #{{ $req?->requisition_slip_no ?? 'N/A' }}
                                    </span>
                                </td>

                                {{-- ✅ Total Required --}}
                                <td class="text-center fw-bold text-muted">
                                    {{ number_format($totalRequired, 2) }}
                                </td>

                                {{-- ✅ Total Issued --}}
                                <td class="text-center fw-bold text-primary">
                                    {{ number_format($totalIssued, 2) }}
                                </td>

                                {{-- ✅ Total Pending --}}
                                <td class="text-center fw-bold {{ $totalPending > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($totalPending, 2) }}
                                </td>

                                {{-- ✅ Status --}}
                                <td>
                                    @if($computedStatus === 'Issued')
                                    <span class="badge bg-soft-success text-success">Issued</span>
                                    @else
                                    <span class="badge bg-soft-warning text-warning">Partially Issued</span>
                                    @endif
                                </td>

                                {{-- Actions --}}
                                <td class="text-end pe-4">
                                    <div class="hstack gap-2 justify-content-end">

                                    <a href="{{route('issue.edit', $is->id)}}" 
                                    class="btn btn-sm btn-icon btn-light-primary"
                                            title="edit issue slip">
                                            <i class="fa fa-edit"></i>

                                    </a>

                                        {{-- VIEW --}}
                                        <a href="{{ route('issue.show', $is->requisition_slip_id) }}"
                                            class="btn btn-sm btn-icon btn-light-primary"
                                            title="View Full Details">
                                            <i class="fa fa-eye"></i>
                                        </a>

                                    </div>
                                </td>
                                </tr>

                                {{-- DELETE CONFIRMATION --}}
                                <div class="offcanvas offcanvas-end" tabindex="-1" id="deleteIssue{{ $is->id }}">
                                    <form method="POST" action="{{ route('issue.destroy', $is->id) }}">
                                        @csrf
                                        @method('DELETE')

                                        <div class="offcanvas-header border-bottom">
                                            <h5 class="mb-0">Confirm Delete</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                                        </div>

                                        <div class="offcanvas-body">
                                            Are you sure you want to delete Issue Slip
                                            <strong>#{{ $is->issue_slip_no }}</strong>?
                                            <p class="text-danger small mt-2">
                                                <i class="feather-alert-triangle me-1"></i>
                                                This action cannot be undone.
                                            </p>
                                        </div>

                                        <div class="border-top d-flex gap-2 p-3">
                                            <button type="submit" class="btn btn-danger w-50">Delete Now</button>
                                            <button type="button" class="btn btn-light w-50" data-bs-dismiss="offcanvas">
                                                Cancel
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-5">
                                        <img src="{{ asset('assets/images/no-data.png') }}"
                                            alt="" style="width: 50px; opacity: 0.5;">
                                        <p class="mt-2">No issue slips found.</p>
                                    </td>
                                </tr>
                                @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-3">
                    {{ $issueSlipData->links('pagination::bootstrap-5') }}
                </div>

            </div>
        </div>
    </div>

@endsection