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

        <!-- LEFT SIDE -->
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Request Slip</h5>
            </div>

            <ul class="breadcrumb d-none d-md-flex ">
                <li class="breadcrumb-item">
                    <a href="/dashboard">Home</a>
                </li>
                <li class="breadcrumb-item active">Request Slip</li>
            </ul>
        </div>

        <!-- RIGHT SIDE -->
        <div class="page-header-right ms-auto d-flex align-items-center">

            <div class="d-flex align-items-center gap-2 flex-nowrap">

                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">

                    <a href="javascript:void(0)"
                        class="btn btn-icon btn-light-brand"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseOne">
                        <i class="feather-filter"></i>
                    </a>

                    <a href="javascript:void(0)"
                        class="btn btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#addRSModal">
                        Create Request Slip
                    </a>

                </div>

            </div>

            <!-- <div class="d-md-none d-flex align-items-center">
                <a href="javascript:void(0)" class="page-header-right-open-toggle">
                    <i class="feather-align-right fs-20"></i>
                </a>
            </div> -->

        </div>
    </div>

    {{-- FILTER COLLAPSE --}}
    <div id="collapseOne"
        class="accordion-collapse collapse page-header-collapse fillter-col {{ $isFilterActive ? 'show' : '' }}">
        <div class="accordion-body pb-2">
            <form method="GET" action="{{ route('request-slip.index') }}">
                <div class="row">

                    <div class="col-lg-3 mb-3">
                        <label class="form-label">From Date</label>
                        <input type="date" name="from_date" class="form-control"
                            value="{{ request('from_date') }}">

                        @error('from_date')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-lg-3 mb-3">
                        <label class="form-label">To Date</label>
                        <input type="date" name="to_date" class="form-control"
                            value="{{ request('to_date') }}">

                        @error('to_date')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

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
                            <input type="text" name="rs_code" class="form-control" placeholder="e.g. 005 or #005"
                                value="{{ request('rs_code') }}">
                        </div>
                    </div>



                    {{-- Project dropdown (FILTER) --}}
                    <div class="col-lg-3 mb-3">
                        <label class="form-label fw-semibold mb-1">Search by Project</label>
                        <select id="filter_project_id" name="project" class="form-control" data-select2-selector="status">
                            <option value="">All Projects</option>
                            @foreach($projects as $project)
                            <option value="{{ $project->id }}"
                                {{ (string)request('project') === (string)$project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Machine (FILTER) --}}
                    <div class="col-lg-3 mb-3">
                        <label class="form-label fw-semibold mb-1">Search by Machine</label>
                        <select id="filter_machine_id" name="machine" class="form-control">
                            <option value="">All Machines</option>
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

                        {{-- ✅ Offcanvas 80% width on PC + compact form --}}
                        <style>
                            @media (min-width: 992px) {
                                .offcanvas.offcanvas-end.offcanvas-80 {
                                    width: 80vw !important;
                                }
                            }

                            .cons-compact .form-label {
                                font-size: 12px;
                                margin-bottom: 4px;
                            }

                            .cons-compact .form-control-sm {
                                padding: .25rem .5rem;
                                font-size: .82rem;
                            }

                            .cons-compact textarea.form-control-sm {
                                resize: vertical;
                            }

                            .text-truncate-1-line {
                                display: -webkit-box;
                                -webkit-line-clamp: 1;
                                -webkit-box-orient: vertical;
                                overflow: hidden;
                            }

                            .mobile-rs-card {
                                display: none;
                            }

                            /* Mobile View */
                            @media (max-width:768px) {

                                #requestSlipList {
                                    display: none;
                                }

                                .mobile-rs-card {
                                    display: block;
                                }

                                .mobile-consumption-canvas {
                                    height: 95vh !important;
                                }

                                .mobile-consumption-canvas .offcanvas-body {
                                    overflow-y: auto;
                                }

                                .rs-card {
                                    border: 1px solid #e5e7eb;
                                    border-radius: 12px;
                                    padding: 14px;
                                    margin-bottom: 14px;
                                    background: #fff;
                                    box-shadow: 0 2px 8px rgba(0, 0, 0, .05);
                                }

                                .rs-top {
                                    display: flex;
                                    justify-content: space-between;
                                    gap: 10px;
                                    margin-bottom: 10px;
                                    align-items: start;
                                }

                                .rs-code {
                                    font-weight: 700;
                                    font-size: 15px;
                                }

                                .rs-grid {
                                    display: grid;
                                    grid-template-columns: 1fr 1fr;
                                    gap: 10px;
                                    margin-bottom: 12px;
                                }

                                .rs-label {
                                    font-size: 11px;
                                    color: #6b7280;
                                    margin-bottom: 2px;
                                    text-transform: uppercase;
                                    font-weight: 600;
                                }

                                .rs-value {
                                    font-size: 13px;
                                    font-weight: 500;
                                }

                                .rs-actions {
                                    display: flex;
                                    gap: 8px;
                                    flex-wrap: wrap;
                                }

                                .rs-actions .btn {
                                    flex: 1;
                                    min-width: 90px;
                                }

                                .machine-badge {
                                    display: inline-block;
                                    background: #f1f5f9;
                                    padding: 4px 8px;
                                    border-radius: 20px;
                                    font-size: 12px;
                                    margin: 2px;
                                }
                            }
                        </style>

                        <div class="table-responsive">
                            <table class="table table-striped" id="requestSlipList">
                                <thead>
                                    <tr>
                                        <th>Sr</th>
                                        <th>RS Code</th>
                                        <th>Project</th>
                                        <th>Machine</th>
                                        <th>Created Date</th>
                                        <th>Status</th>
                                        <th>Issue Status </th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($requestSlips as $index => $rs)

                                    <tr class="single-item">
                                        <td>{{ $requestSlips->firstItem() + $index }}</td>
                                        <td>#{{ $rs->requisition_slip_no}}</td>

                                        <td>
                                            <span class="text-truncate-1-line">
                                               <a href="{{route('project.show', $rs->project->id)}}"> {{ $rs->project->name ?? 'N/A' }}</a>
                                            </span>
                                        </td>

                                        <td>
                                            @php
                                            $uniqueMachines = $rs->rows
                                            ->pluck('machine.name')
                                            ->filter()
                                            ->unique()
                                            ->values();
                                            @endphp

                                            @if($uniqueMachines->count())

                                            <div class="d-flex flex-column gap-1">
                                                @foreach($uniqueMachines as $machineName)
                                                <span class="badge bg-light text-dark border"
                                                    style="font-size: 13px; width: fit-content;">
                                                    {{ $machineName }}
                                                </span>
                                                @endforeach
                                            </div>

                                            @else
                                            <span class="text-muted">N/A</span>
                                            @endif
                                        </td>

                                        <td>{{ \Carbon\Carbon::parse($rs->created_on)->format('d-m-Y') }}</td>

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
                                            @case('Approved HOD')
                                            <span class="badge bg-soft-success text-success">Approved HOD</span>
                                            @break
                                            @endswitch
                                        </td>

                                        <td>
                                            @if(!$rs->issue)
                                            <span class="badge bg-soft-warning text-warning">Pending</span>
                                            @elseif($rs->issue->status === 'Partially Issued')
                                            <span class="badge bg-soft-warning text-warning">{{$rs->issue->status}}</span>
                                            @else
                                            <span class="badge bg-soft-success text-success">{{$rs->issue->status}}</span>
                                            @endif
                                        </td>

                                        <td class="text-center">

                                            <div class="d-flex gap-2 justify-content-center">
                                                <a href="{{ route('request-slip.show', $rs->id) }}"
                                                    class="btn btn-light btn-sm" title="View Details">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                @if((auth()->user()->role_id == '1' || auth()->user()->role_id == '2') && $rs->status === 'Pending' )
                                                <button type="button" class="btn btn-light btn-sm"
                                                    data-bs-toggle="offcanvas"
                                                    data-bs-target="#deleteInventory{{ $rs->id }}">
                                                    <i class="feather feather-trash-2"></i>
                                                </button>
                                                @endif

                                                {{-- ✅ Consumption + History Offcanvas (Admin only) --}}
                                                @if($rs->issue && $rs->issue->status === 'Issued')
                                                <a href="javascript:void(0)"
                                                    class="btn btn-light btn-sm"
                                                    title="Consumption + History"
                                                    data-bs-toggle="offcanvas"
                                                    data-bs-target="#rsHistory{{ $rs->id }}">
                                                    Consumption
                                                </a>
                                                @endif
                                            </div>


                                            <div class="offcanvas offcanvas-end offcanvas-80"
                                                tabindex="-1"
                                                id="rsHistory{{ $rs->id }}">

                                                <div class="offcanvas-header ht-80 px-4 border-bottom border-gray-5">
                                                    <div>
                                                        <h2 class="fs-16 fw-bold mb-1">Create Consumption</h2>
                                                        <small class="fs-12 text-muted">
                                                            RS: #{{ $rs->requisition_slip_no }} • Project: {{ $rs->project?->name ?? 'N/A' }}
                                                        </small>
                                                    </div>
                                                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                                                </div>

                                                @php
                                                // ✅ Issue rows

                                                $projectID = $rs->project_id;

                                                // ✅ Maps from controller
                                                $consumedQtyMap = $rs->consumedQtyMap ?? [];
                                                $consumedHMap = $rs->consumedHMap ?? [];
                                                $consumedWMap = $rs->consumedWMap ?? [];
                                                $lastMachineMap = $rs->lastMachineMap ?? [];
                                                $lastProjectMap = $rs->lastProjectMap ?? [];
                                                $consHistory = $rs->consHistory ?? [];

                                                // ✅ FIRST TIME default: machine from RS row table, project from RS main
                                                // rs->rows relation is already eager loaded in controller
                                                $rsRowMachineMap = collect($rs->rows ?? [])->mapWithKeys(function($r){
                                                return [$r->id => ($r->machine_id ?? null)];
                                                });

                                                // for history item name resolve
                                                $issueRowsForHistory = (isset($rs->issue) && isset($rs->issue->rows)) ? $rs->issue->rows : collect([]);
                                                @endphp

                                                <div class="offcanvas-body overflow-auto py-3 cons-compact">

                                                    @if(!$rs->issue)
                                                    <div class="alert alert-danger">Issue entry not found for this Request Slip.</div>
                                                    @else

                                                    <form id="consForm{{ $rs->id }}" action="{{ route('consumption.store', $rs->id) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="transaction_date" value="{{ date('Y-m-d') }}">

                                                        <div class="table-responsive">
                                                            <table class="table table-striped mb-0">
                                                                <thead>
                                                                    <tr>
                                                                        <th style="min-width:360px;">Item Details</th>
                                                                        <th style="min-width:260px;">New Entry</th>
                                                                        <th style="min-width:360px;">Machine / Project / Remark</th>
                                                                    </tr>
                                                                </thead>

                                                                <tbody>
                                                                    @if($rs->rows)

                                                                    @foreach($rs->rows as $i => $row)
                                                                    @php
                                                                    $issuedQty = $row->issued_qty;
                                                                    $machineID = $row->machine_id;


                                                                    @endphp
                                                                    @foreach($row->pieces as $p)
                                                                    @php



                                                                    $inv = $p->inventory;


                                                                    $itemName = optional($inv)->name ?? optional($inv)->item_name ?? '—';
                                                                    $unit = strtoupper(trim(optional($inv)->unit ?? 'NOS'));
                                                                    $itemh= $inv->height ?? null;
                                                                    $itemW = $inv->width ?? null;
                                                                    $itemTH = $inv->thikness ?? null;

                                                                    $remainingH = $itemh - ($p->consumed_height ?? 0);
                                                                    $remainingW = $itemW - ($p->consumed_width ?? 0);

                                                                    $remainingQty = $issuedQty - ($p->consumed_qty ?? 0);

                                                                    $isLocked= $p->is_completed == 1 ? true : false;

                                                                    @endphp

                                                                    <tr class="cons-row" data-unit="{{ $unit }}">
                                                                        <input type="hidden" name="items[{{ $p->id }}][rs_row_id]" value="{{ $p->id}}">
                                                                        <input type="hidden" name="items[{{ $p->id }}][unit]" value="{{ $unit }}">
                                                                        <input type="hidden" name="items[{{ $p->id }}][issued_height]" value="{{ $itemh}}">
                                                                        <input type="hidden" name="items[{{ $p->id }}][issued_width]" value="{{ $itemW  }}">
                                                                        <input type="hidden" name="items[{{ $p->id }}][issued_qty]" value="{{ $issuedQty }}">

                                                                        {{-- ITEM DETAILS --}}
                                                                        <td>
                                                                            <div class="fw-semibold text-truncate-1-line">{{ $itemName }}</div>

                                                                            @if($unit === 'KG')
                                                                            <div class="small text-muted">
                                                                                Issued: <span class="fw-semibold text-dark"> {{ $itemh }} X {{ $itemW }} X {{ $itemTH }}</span>
                                                                                &nbsp;|&nbsp;
                                                                                Remain: <span class="fw-semibold text-dark">{{ $remainingH }} X {{ $remainingW }}</span>
                                                                            </div>
                                                                            @else
                                                                            <div class="small text-muted">
                                                                                Issued: <span class="fw-semibold text-dark">{{ $issuedQty }}</span> ({{ $unit }})
                                                                                &nbsp;|&nbsp;
                                                                                Remain: <span class="fw-semibold text-dark">{{ $remainingQty }}</span> ({{ $unit }})
                                                                            </div>
                                                                            @endif

                                                                            @if($isLocked)
                                                                            <div class="mt-1">
                                                                                <span class="badge bg-success">Completed</span>
                                                                            </div>
                                                                            @endif
                                                                        </td>

                                                                        {{-- ENTRY --}}
                                                                        <td>
                                                                            <input type="hidden" name="items[{{ $p->id }}][inv_id]" value="{{$inv->id}}">

                                                                            @if($unit === 'KG')
                                                                            <input type="hidden" name="items[{{ $p->id }}][consume_qty]" value="0">

                                                                            <div class="d-flex gap-2">
                                                                                <input type="number"
                                                                                    name="items[{{ $p->id }}][height]"
                                                                                    class="form-control form-control-sm"
                                                                                    value="{{ old('items.height.'.$i) }}"
                                                                                    min="0" step="1"
                                                                                    placeholder="H"
                                                                                    {{ $isLocked ? 'readonly disabled' : '' }}>
                                                                                <input type="number"
                                                                                    name="items[{{ $p->id }}][width]"
                                                                                    class="form-control form-control-sm"
                                                                                    value="{{ old('items.width.'.$i) }}"
                                                                                    min="0" step="1"
                                                                                    placeholder="W"
                                                                                    {{ $isLocked ? 'readonly disabled' : '' }}>
                                                                            </div>
                                                                            <div class="mt-2 text-center">
                                                                                <label class="form-label fw-semibold small mb-1">
                                                                                    Item Shape
                                                                                </label>
                                                                                <select name="items[{{ $p->id }}][shape]"
                                                                                    class="form-control"
                                                                                    {{ $isLocked ? 'disabled' : '' }} data-select2-selector="status">

                                                                                    <option value="">Select Shape</option>
                                                                                    <option value="RECT" {{ old("items.$p->id.shape" , $p->shape) == 'RECT' ? 'selected' : '' }}>Rectangle</option>
                                                                                    <option value="L" {{ old("items.$p->id.shape" , $p->shape) == 'L' ? 'selected' : '' }}>L Shape</option>
                                                                                    <option value="U" {{ old("items.$p->id.shape" , $p->shape) == 'U' ? 'selected' : '' }}>U Shape</option>
                                                                                    <option value="T" {{ old("items.$p->id.shape" , $p->shape) == 'T' ? 'selected' : '' }}>T Shape</option>
                                                                                    <option value="TRIANGLE" {{ old("items.$p->id.shape" , $p->shape) == 'TRIANGLE' ? 'selected' : '' }}>Triangle</option>
                                                                                </select>
                                                                            </div>
                                                                            @else
                                                                            <input type="number"
                                                                                name="items[{{ $p->id }}][consume_qty]"
                                                                                class="form-control form-control-sm"
                                                                                value="{{ old('items.consume_qty.'.$i) }}"
                                                                                min="0" step="1"
                                                                                placeholder="{{ $isLocked ? 'Done' : 'Qty' }}"
                                                                                {{ $isLocked ? 'readonly disabled' : '' }}>

                                                                            <input type="hidden" name="items[{{ $p->id }}][height]" value="">
                                                                            <input type="hidden" name="items[{{ $p->id }}][width]" value="">

                                                                            <!-- <div class=" text-center">
                                                                                <label class="form-label fw-semibold small mb-1">
                                                                                    Item Shape
                                                                                </label>
                                                                                <select name="items[{{ $p->id }}][shape]"
                                                                                    class="form-control "
                                                                                    {{ $isLocked ? 'disabled' : '' }} data-select2-selector="status">

                                                                                    <option value="">Select Shape</option>
                                                                                    <option value="RECT" {{ old("items.$p->id.shape", $p->shape) == 'RECT' ? 'selected' : '' }}>Rectangle</option>
                                                                                    <option value="L" {{ old("items.$p->id.shape", $p->shape) == 'L' ? 'selected' : '' }}>L Shape</option>
                                                                                    <option value="U" {{ old("items.$p->id.shape", $p->shape) == 'U' ? 'selected' : '' }}>U Shape</option>
                                                                                    <option value="T" {{ old("items.$p->id.shape", $p->shape) == 'T' ? 'selected' : '' }}>T Shape</option>
                                                                                    <option value="TRIANGLE" {{ old("items.$p->id.shape", $p->shape) == 'TRIANGLE' ? 'selected' : '' }}>Triangle</option>
                                                                                </select>
                                                                            </div> -->

                                                                            @endif

                                                                            @error("items.$p->id.consume_qty")
                                                                            <div class="text-danger mt-1 small">{{ $message }}</div>
                                                                            @enderror

                                                                            @error("items.$p->id.height")
                                                                            <div class="text-danger mt-1 small">{{ $message }}</div>
                                                                            @enderror

                                                                            @error("items.$p->id.width")
                                                                            <div class="text-danger mt-1 small">{{ $message }}</div>
                                                                            @enderror
                                                                        </td>

                                                                        {{-- MACHINE / PROJECT / REMARK (compact, low height) --}}
                                                                        <td>
                                                                            <div class="d-flex gap-2">
                                                                                <div class="flex-grow-1">
                                                                                    <label class="form-label">Machine</label>
                                                                                    <select name="items[{{ $p->id }}][machine_id]" class="form-control form-control-sm" {{ $isLocked ? 'disabled' : '' }} data-select2-selector="status">
                                                                                        <option value="">Select</option>
                                                                                        @foreach($machines as $m)
                                                                                        <option value="{{ $m->id }}" {{ $machineID === $m->id ? 'selected' : '' }}>
                                                                                            {{ $m->name ?? $m->machine_name ?? ('Machine #'.$m->id) }}
                                                                                        </option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>

                                                                                <div class="flex-grow-1">
                                                                                    <label class="form-label">Project</label>
                                                                                    <select name="items[{{ $p->id }}][project_id]" class="form-control form-control-sm" {{ $isLocked ? 'disabled' : '' }} data-select2-selector="status">
                                                                                        <option value="">Select</option>
                                                                                        @foreach($projects as $project)
                                                                                        <option value="{{ $project->id }}" {{  $projectID === $project->id ? 'selected' : '' }}>
                                                                                            {{ $project->name }}
                                                                                        </option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>
                                                                            </div>

                                                                            <div class="mt-2">
                                                                                <label class="form-label">Remark</label>
                                                                                <textarea name="items[{{ $p->id }}][remark]"
                                                                                    class="form-control form-control-sm"
                                                                                    rows="1"
                                                                                    placeholder="Optional"
                                                                                    {{ $isLocked ? 'readonly disabled' : '' }}>{{ old('items.remark.'.$i) }}</textarea>
                                                                            </div>
                                                                            <div class="mt-3 d-flex justify-content-end">
                                                                                <button type="submit"
                                                                                    name="send_to_hod_item"
                                                                                    value="{{ $p->id }}"
                                                                                    class="btn btn-primary btn-sm">
                                                                                    Send To HOD
                                                                                </button>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                    @endforeach
                                                                    @endforeach

                                                                    @else
                                                                    <tr>
                                                                        <td colspan="3" class="text-center p-4">
                                                                            <strong>No issued items found.</strong>
                                                                        </td>
                                                                    </tr>
                                                                    @endif
                                                                </tbody>

                                                            </table>
                                                        </div>

                                                        <div class="mt-3 d-flex justify-content-end">
                                                            <button class="btn btn-primary">Save Consumption</button>
                                                        </div>

                                                    </form>
                                                    @endif

                                                    {{-- ✅ CONSUMPTION HISTORY (machine/project separate columns) --}}
                                                    <div class="mt-4">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <h6 class="fw-bold mb-0">Consumption History</h6>
                                                            <span class="text-muted small">Latest entries</span>
                                                        </div>

                                                        @if(!empty($consHistory) && count($consHistory) > 0)
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-bordered">
                                                                <thead>
                                                                    <tr>
                                                                        <th style="white-space:nowrap;">Date</th>
                                                                        <th>Item</th>
                                                                        <th class="text-end">Qty</th>
                                                                        <th class="text-end">H</th>
                                                                        <th class="text-end">W</th>
                                                                        <th>Machine</th>
                                                                        <th>Project</th>
                                                                        <th>Remark</th>
                                                                        @if(auth()->user()->role_id == 1)
                                                                        <th>By</th>
                                                                        @endif
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($consHistory as $c)
                                                                    @php
                                                                    // item resolve without Consumption relations
                                                                    $rowMatch = $issueRowsForHistory->first(function($ir) use ($c){
                                                                    $rid = $ir->rs_row_id ?? $ir->requisition_slip_row_id ?? $ir->request_slip_row_id ?? null;
                                                                    return (string)$rid === (string)($c->rs_row_id ?? null);
                                                                    });

                                                                    $invH = $rowMatch?->inventory;

                                                                    $cItem = $c->inventory->name
                                                                    ?? ('Row #'.$c->rs_row_id);

                                                                    $mName = '-';
                                                                    if (!empty($c->machine_id)) {
                                                                    $mObj = $machines->firstWhere('id', $c->machine_id);
                                                                    $mName = $mObj?->name ?? $mObj?->machine_name ?? $c->machine_id;
                                                                    }

                                                                    $pName = '-';
                                                                    if (!empty($c->project_id)) {
                                                                    $pObj = $projects->firstWhere('id', $c->project_id);
                                                                    $pName = $pObj?->name ?? $c->project_id;
                                                                    }

                                                                    $cBy = $c->user->name ?? '—';
                                                                    $cDate = $c->created_at ? $c->created_at->format('d M Y, h:i A') : '—';
                                                                    @endphp
                                                                    <tr>
                                                                        <td style="white-space:nowrap;">{{ $cDate }}</td>
                                                                        <td class="text-truncate-1-line" style="max-width:280px;">{{ $cItem }}</td>
                                                                        <td class="text-end">{{ (float)($c->quantity ?? 0) }}</td>
                                                                        <td class="text-end">{{ (float)($c->height ?? 0) }}</td>
                                                                        <td class="text-end">{{ (float)($c->width ?? 0) }}</td>
                                                                        <td>{{ $mName }}</td>
                                                                        <td>{{ $pName }}</td>
                                                                        <td class="text-truncate-1-line" style="max-width:260px;">{{ $c->remark ?? '-' }}</td>
                                                                        @if(auth()->user()->role_id == 1)
                                                                        <td>{{ $cBy }}</td>
                                                                        @endif
                                                                    </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                        @else
                                                        <div class="text-muted small">No consumption history found.</div>
                                                        @endif
                                                    </div>

                                                </div>

                                                <div class="px-4 d-flex justify-content-end ht-80 border-top">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Close</button>
                                                </div>

                                            </div>

                                        </td>
                                    </tr>

                                    {{-- DELETE OFFCANVAS --}}
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
                                                    <strong>{{ $rs->name }}</strong>?
                                                </p>
                                            </div>

                                            <div class="px-4 gap-2 d-flex align-items-center justify-content-between ht-80 border-top border-gray-2">
                                                <button type="submit" class="btn btn-primary w-50">Yes, Delete</button>
                                                <button type="button" class="btn btn-danger w-50" data-bs-dismiss="offcanvas">Cancel</button>
                                            </div>
                                        </form>
                                    </div>

                                    @empty
                                    <tr>
                                        <td colspan="12" class="text-center text-muted">
                                            No Request Slips found
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>

                            {{-- MOBILE CARD VIEW --}}
                            <div class="mobile-rs-card">

                                @forelse($requestSlips as $index => $rs)

                                <div class="rs-card">

                                    <div class="rs-top">
                                        <div>
                                            <div class="rs-code">#{{ $rs->requisition_slip_no }}</div>
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::parse($rs->created_on)->format('d-m-Y') }}
                                            </small>
                                        </div>

                                        <div>
                                            <span class="badge  @if($rs->status == 'Pending')
                                                     bg-soft-warning text-warning
                                                 @elseif($rs->status == 'Rejected')
                                                     bg-soft-danger text-danger
                                                 @elseif($rs->status == 'Approved')
                                                     bg-soft-success text-success
                                                 @elseif($rs->status == 'Hold')
                                                     bg-soft-danger text-danger
                                                 @elseif($rs->status == 'Approved HOD')
                                                     bg-soft-success text-success
                                                 @else
                                                     bg-soft-secondary text-secondary
                                                 @endif
                                             ">
                                                {{ $rs->status }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="rs-grid">

                                        <div>
                                            <div class="rs-label">Project</div>
                                            <div class="rs-value">{{ $rs->project->name ?? 'N/A' }}</div>
                                        </div>

                                        <div>
                                            <div class="rs-label">Issue</div>
                                            <span class="badge
                                                     @if(!$rs->issue)
                                                         bg-soft-warning text-warning
                                                     @elseif($rs->issue->status == 'Partially Issued')
                                                         bg-soft-warning text-warning
                                                     @elseif($rs->issue->status == 'Issued')
                                                         bg-soft-success text-success
                                                     @else
                                                         bg-soft-success text-success
                                                     @endif
                                                 ">
                                                {{ $rs->issue->status ?? 'Pending' }}
                                            </span>
                                        </div>

                                        <div style="grid-column:1 / -1;">
                                            <div class="rs-label">Machines</div>

                                            @php
                                            $uniqueMachines = $rs->rows
                                            ->pluck('machine.name')
                                            ->filter()
                                            ->unique()
                                            ->values();
                                            @endphp

                                            @foreach($uniqueMachines as $machine)
                                            <span class="machine-badge">{{ $machine }}</span>
                                            @endforeach
                                        </div>

                                    </div>

                                    <div class="rs-actions">

                                        <button class="btn btn-light btn-sm"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#mobileDetail{{ $rs->id }}">
                                            View
                                        </button>

                                        @if((auth()->user()->role_id == '1' || auth()->user()->role_id == '2') && $rs->status === 'Pending')
                                        <button class="btn btn-danger btn-sm"
                                            data-bs-toggle="offcanvas"
                                            data-bs-target="#deleteInventory{{ $rs->id }}">
                                            Delete
                                        </button>
                                        @endif

                                        @if($rs->issue && $rs->issue->status === 'Issued')
                                        <button class="btn btn-primary btn-sm"
                                            data-bs-toggle="offcanvas"
                                            data-bs-target="#mobileRsHistory{{ $rs->id }}">
                                            Consumption
                                        </button>

                                        {{-- MOBILE OFFCANVAS : functionality same rahegi, sirf UI mobile friendly --}}
                                        <div class="offcanvas offcanvas-bottom mobile-consumption-canvas"
                                            tabindex="-1"
                                            id="mobileRsHistory{{ $rs->id }}"
                                            style="height:95vh;">

                                            <div class="offcanvas-header border-bottom">
                                                <div>
                                                    <h5 class="mb-0">Create Consumption</h5>
                                                    <small class="text-muted">
                                                        RS #{{ $rs->requisition_slip_no }} • {{ $rs->project?->name ?? 'N/A' }}
                                                    </small>
                                                </div>

                                                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                                            </div>

                                            <div class="offcanvas-body p-2">

                                                @if(!$rs->issue)
                                                <div class="alert alert-danger">Issue entry not found</div>
                                                @else

                                                {{-- SAME FORM ID --}}
                                                <form id="consForm{{ $rs->id }}"
                                                    action="{{ route('consumption.store', $rs->id) }}"
                                                    method="POST">

                                                    @csrf
                                                    <input type="hidden" name="transaction_date" value="{{ date('Y-m-d') }}">

                                                    @foreach($rs->rows as $row)

                                                    @php
                                                    $issuedQty = $row->issued_qty;
                                                    $machineID = $row->machine_id;
                                                    @endphp

                                                    @foreach($row->pieces as $p)

                                                    @php
                                                    $inv = $p->inventory;

                                                    $itemName = optional($inv)->name ?? '---';
                                                    $unit = strtoupper(trim(optional($inv)->unit ?? 'NOS'));

                                                    $itemh = $inv->height ?? 0;
                                                    $itemW = $inv->width ?? 0;
                                                    $itemTH = $inv->thikness ?? 0;

                                                    $remainingH = $itemh - ($p->consumed_height ?? 0);
                                                    $remainingW = $itemW - ($p->consumed_width ?? 0);

                                                    $remainingQty = $issuedQty - ($p->consumed_qty ?? 0);

                                                    $isLocked = $p->is_completed == 1;
                                                    @endphp

                                                    {{-- SAME INPUT NAMES --}}
                                                    <div class="card mb-3 shadow-sm">

                                                        <div class="card-body p-3">

                                                            <h6 class="mb-1">{{ $itemName }}</h6>

                                                            @if($unit == 'KG')
                                                            <small class="text-muted d-block">
                                                                Issued: {{ $itemh }} x {{ $itemW }} x {{ $itemTH }}
                                                            </small>

                                                            <small class="text-success d-block mb-2">
                                                                Remain: {{ $remainingH }} x {{ $remainingW }}
                                                            </small>
                                                            @else
                                                            <small class="text-muted d-block">
                                                                Issued: {{ $issuedQty }} {{ $unit }}
                                                            </small>

                                                            <small class="text-success d-block mb-2">
                                                                Remain: {{ $remainingQty }} {{ $unit }}
                                                            </small>
                                                            @endif


                                                            <input type="hidden" name="items[{{ $p->id }}][rs_row_id]" value="{{ $p->id }}">
                                                            <input type="hidden" name="items[{{ $p->id }}][inv_id]" value="{{ $inv->id }}">
                                                            <input type="hidden" name="items[{{ $p->id }}][unit]" value="{{ $unit }}">
                                                            <input type="hidden" name="items[{{ $p->id }}][issued_qty]" value="{{ $issuedQty }}">
                                                            <input type="hidden" name="items[{{ $p->id }}][issued_height]" value="{{ $itemh }}">
                                                            <input type="hidden" name="items[{{ $p->id }}][issued_width]" value="{{ $itemW }}">


                                                            {{-- QTY / H-W --}}
                                                            @if($unit == 'KG')

                                                            <div class="row g-2 mb-2">

                                                                <div class="col-6">
                                                                    <input type="number"
                                                                        name="items[{{ $p->id }}][height]"
                                                                        class="form-control"
                                                                        placeholder="Height"
                                                                        {{ $isLocked ? 'readonly disabled' : '' }}>
                                                                </div>

                                                                <div class="col-6">
                                                                    <input type="number"
                                                                        name="items[{{ $p->id }}][width]"
                                                                        class="form-control"
                                                                        placeholder="Width"
                                                                        {{ $isLocked ? 'readonly disabled' : '' }}>
                                                                </div>

                                                            </div>

                                                            <input type="hidden"
                                                                name="items[{{ $p->id }}][consume_qty]"
                                                                value="0">

                                                            @else

                                                            <input type="number"
                                                                name="items[{{ $p->id }}][consume_qty]"
                                                                class="form-control mb-2"
                                                                placeholder="Enter Qty"
                                                                {{ $isLocked ? 'readonly disabled' : '' }}>

                                                            <input type="hidden" name="items[{{ $p->id }}][height]" value="">
                                                            <input type="hidden" name="items[{{ $p->id }}][width]" value="">

                                                            @endif


                                                            {{-- MACHINE --}}
                                                            <select name="items[{{ $p->id }}][machine_id]"
                                                                class="form-control mb-2"
                                                                {{ $isLocked ? 'disabled' : '' }}>

                                                                <option value="">Select Machine</option>

                                                                @foreach($machines as $m)
                                                                <option value="{{ $m->id }}"
                                                                    {{ $machineID == $m->id ? 'selected' : '' }}>
                                                                    {{ $m->name }}
                                                                </option>
                                                                @endforeach

                                                            </select>


                                                            {{-- PROJECT --}}
                                                            <select name="items[{{ $p->id }}][project_id]"
                                                                class="form-control mb-2"
                                                                {{ $isLocked ? 'disabled' : '' }}>

                                                                <option value="">Select Project</option>

                                                                @foreach($projects as $project)
                                                                <option value="{{ $project->id }}"
                                                                    {{ $projectID == $project->id ? 'selected' : '' }}>
                                                                    {{ $project->name }}
                                                                </option>
                                                                @endforeach

                                                            </select>


                                                            {{-- REMARK --}}
                                                            <textarea
                                                                name="items[{{ $p->id }}][remark]"
                                                                class="form-control mb-2"
                                                                rows="2"
                                                                placeholder="Remark"
                                                                {{ $isLocked ? 'readonly disabled' : '' }}></textarea>


                                                            {{-- SAME BUTTON --}}
                                                            <button type="submit"
                                                                name="send_to_hod_item"
                                                                value="{{ $p->id }}"
                                                                class="btn btn-warning w-100 mb-2">
                                                                Send To HOD
                                                            </button>

                                                        </div>
                                                    </div>

                                                    @endforeach
                                                    @endforeach


                                                    <button class="btn btn-primary w-100">
                                                        Save Consumption
                                                    </button>

                                                </form>

                                                {{-- ================= CONSUMPTION HISTORY ================= --}}
                                                @php
                                                $consHistory = $rs->consHistory ?? [];
                                                @endphp

                                                <div class="mt-3">
                                                    <div class="card border-0 shadow-sm">
                                                        <div class="card-header bg-light fw-semibold">
                                                            Consumption History
                                                        </div>

                                                        <div class="card-body p-2">

                                                            @if(!empty($consHistory) && count($consHistory) > 0)

                                                            @foreach($consHistory as $c)

                                                            @php
                                                            $mName = '-';
                                                            if (!empty($c->machine_id)) {
                                                            $mObj = $machines->firstWhere('id', $c->machine_id);
                                                            $mName = $mObj?->name ?? '-';
                                                            }

                                                            $pName = '-';
                                                            if (!empty($c->project_id)) {
                                                            $pObj = $projects->firstWhere('id', $c->project_id);
                                                            $pName = $pObj?->name ?? '-';
                                                            }

                                                            $cDate = $c->created_at
                                                            ? $c->created_at->format('d M Y, h:i A')
                                                            : '-';
                                                            @endphp

                                                            <div class="border rounded p-2 mb-2 bg-white">

                                                                <div class="fw-semibold">
                                                                    {{ $c->inventory->name ?? ('Row #'.$c->rs_row_id) }}
                                                                </div>

                                                                <small class="text-muted d-block">
                                                                    {{ $cDate }}
                                                                </small>

                                                                <small class="d-block">
                                                                    Qty: {{ $c->quantity ?? 0 }}
                                                                    | H: {{ $c->height ?? 0 }}
                                                                    | W: {{ $c->width ?? 0 }}
                                                                </small>

                                                                <small class="d-block">
                                                                    Machine: {{ $mName }}
                                                                </small>

                                                                <small class="d-block">
                                                                    Project: {{ $pName }}
                                                                </small>

                                                                @if($c->remark)
                                                                <small class="text-muted d-block">
                                                                    Remark: {{ $c->remark }}
                                                                </small>
                                                                @endif

                                                            </div>

                                                            @endforeach

                                                            @else
                                                            <div class="text-muted small">
                                                                No consumption history found.
                                                            </div>
                                                            @endif

                                                        </div>
                                                    </div>
                                                </div>
                                                @endif

                                            </div>
                                        </div>

                                        @endif

                                    </div>

                                    <div class="collapse mt-2" id="mobileDetail{{ $rs->id }}">

                                        <div class="card border-0 shadow-sm">
                                            <div class="card-header bg-light fw-semibold py-2">
                                                Request Details
                                            </div>

                                            <div class="card-body p-2">

                                                @forelse($rs->rows as $row)

                                                @php
                                                $isExited = (float)($row->exited_qty ?? 0) > 0;
                                                @endphp

                                                <div class="border rounded p-2 mb-2 {{ $isExited ? 'bg-danger-subtle' : '' }}">

                                                    <div class="fw-semibold">
                                                        {{ $row->inventory?->name ?? 'N/A' }}
                                                    </div>

                                                    <small class="text-muted d-block mb-1">
                                                        Machine: {{ $row->machine?->name ?? 'N/A' }}
                                                    </small>

                                                    <div class="row g-1 small">
                                                        <div class="col-6">
                                                            Req: {{ $row->quantity ?? 0 }}
                                                        </div>

                                                        <div class="col-6 text-end">
                                                            Issued: {{ $row->issued_qty ?? 0 }}
                                                        </div>

                                                        <div class="col-6">
                                                            Out Stock: {{ $row->order_qty ?? 0 }}
                                                        </div>

                                                        <div class="col-6 text-end">
                                                            Machining: {{ $row->pending_qty ?? 0 }}
                                                        </div>

                                                        @if (in_array(Auth::user()->role_id,[1,2]))
                                                        <div class="col-12 text-danger fw-bold">
                                                            Exceed Qty: {{ $row->exited_qty ?? 0 }}
                                                        </div>
                                                        @endif
                                                    </div>

                                                    @if($row->description)
                                                    <div class="mt-2 pt-2 border-top">
                                                        <small class="fw-semibold d-block">Description</small>
                                                        <small>{{ $row->description }}</small>
                                                    </div>
                                                    @endif

                                                </div>

                                                @empty
                                                <div class="text-muted text-center">
                                                    No Items Found
                                                </div>
                                                @endforelse

                                            </div>
                                        </div>

                                    </div>

                                </div>

                                @empty
                                <div class="text-center text-muted">No Request Slips Found</div>
                                @endforelse

                            </div>

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

    <div class="modal fade" id="addRSModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rs-box">

                <!-- Header -->
                <div class="rs-head">
                    <h5><i class="feather-file-text me-2 text-primary"></i>Request Slip <span style="font-size: 11px;">
                            : {{ old('requisition_slip_no', $nextSlipNo) }}
                        </span></h5>
                    <button type="button" class="rs-close" data-bs-dismiss="modal">&times;</button>
                </div>

                <!-- Body -->
                <div class="rs-body">

                    <form id="rsForm" method="POST" action="{{ route('request-slip.store') }}">
                        @csrf

                        <input type="hidden" name="employee_id" value="{{ Auth::id() }}">
                        <input type="hidden" name="department_id" value="{{ Auth::user()->department_id }}">

                        <div class="rs-grid">

                            <div class="rs-field d-none">
                                <label>RS No.</label>
                                <input type="text" name="requisition_slip_no" class="rs-control" value="{{ old('requisition_slip_no', $nextSlipNo) }}" style="background-color: #f1f5f9;" readonly>

                            </div>

                            <div class="rs-field">
                                <label>Date <span class="rs-req">*</span></label>
                                <input type="date" name="transaction_date" class="rs-control" value="{{ old('transaction_date', date('Y-m-d')) }}" style="background-color: #f1f5f9;" readonly>
                                <small class="text-danger error-msg"></small>
                            </div>

                            <div class="rs-field">
                                <label>Project *</label>
                                <select id="project_id" name="project_id" class="rs-control" data-select2-selector="status">
                                    <option value="">Select Project</option>
                                    @foreach($modelprojects as $p)
                                    <option value="{{ $p->id }}" {{ old('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-danger error-msg"></small>
                            </div>

                            <div class="rs-field">
                                <label>Comment</label>
                                <input type="text" name="comment" class="rs-control" value="{{ old('comment') }}">
                            </div>

                        </div>

                        <!-- Items -->
                        <div id="rs_items"></div>

                        <button type="button" class="rs-add-btn" id="addRowBtn">+ Add Item</button>

                        <div class="rs-foot">
                            <button type="button" class="rs-cancel" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="rs-save">Save</button>
                        </div>

                    </form>

                </div>
            </div>
        </div>

        <style>
            .fillter-col .select2-container {
                z-index: 1;
            }

            .select2-container .select2-selection--single {
                padding: 2px;
            }

            .select2-container .select2-selection--single .select2-selection__rendered {
                padding: 2px;
            }

            .select2-container .select2-selection {
                min-height: calc(1.5em + .95rem + 1px);
            }

            /* Header */
            .rs-head {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 14px 18px;
                border-bottom: 1px solid #e2e8f0;
                background: #f8fafc;
            }

            .rs-head h5 {
                font-size: 15px;
                font-weight: 700;
                margin: 0;
            }

            .rs-close {
                width: 28px;
                height: 28px;
                border: none;
                background: #fee2e2;
                color: #ef4444;
                border-radius: 6px;
                font-size: 18px;
            }

            /* Body */
            .rs-body {
                padding: 16px;
            }

            /* Grid */
            .rs-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
                gap: 12px 14px;
                margin-bottom: 10px;
            }

            .rs-grids {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
                gap: 12px 14px;
                margin-bottom: 10px;
            }

            @media(max-width:600px) {
                .rs-grid {
                    display: grid;
                    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                }

                .rs-grids .rs-field.full {
                    grid-column: span 1 !important;
                }

                .rs-grids {
                    display: grid;
                    grid-template-columns: 1fr !important;
                }




            }

            @media(max-width:400px) {
                .rs-grid {
                    display: grid;
                    grid-template-columns: repeat(1, minmax(0, 1fr)) !important;
                }


            }

            .rs-field label {
                font-size: 11px;
                font-weight: 700;
                color: #64748b;
                margin-bottom: 4px;
                display: block;
                text-transform: uppercase;
            }

            .rs-req {
                color: red;
            }

            .rs-field.full {
                grid-column: span 3;
            }



            /* Input */
            .rs-control {
                width: 100%;
                border: 1.5px solid #e2e8f0;
                border-radius: 6px;
                font-size: 13px;
                padding: 6px 10px;
                height: 34px;
            }

            .rs-control:focus {
                border-color: #2563eb;
                outline: none;
                box-shadow: 0 0 0 2px rgba(37, 99, 235, .1);
            }

            /* Items Card */
            .rs-item {
                border: 1px solid #e2e8f0;
                border-radius: 10px;
                padding: 10px;
                margin-bottom: 8px;
            }

            /* Add Btn */
            .rs-add-btn {
                width: 100%;
                border: 1px dashed #2563eb;
                background: #f1f5ff;
                color: #2563eb;
                padding: 7px;
                border-radius: 6px;
                font-size: 13px;
            }

            /* Footer */
            .rs-foot {
                display: flex;
                justify-content: flex-end;
                gap: 10px;
                padding: 12px 16px;
                border-top: 1px solid #e2e8f0;
                background: #f8fafc;
            }

            .rs-save {
                background: #16a34a;
                color: #fff;
                border: none;
                padding: 8px 18px;
                border-radius: 6px;
            }

            .rs-cancel {
                background: #f1f5f9;
                border: 1px solid #e2e8f0;
                padding: 8px 16px;
                border-radius: 6px;
            }

            .nxl-container {
                filter: none !important;
                overflow-y: auto;
            }
        </style>
        @push('scripts')
        <script>
            const supModal = document.getElementById('addRSModal');
            if (supModal) document.body.appendChild(supModal);
            let filterMachineOptions = `<option value="">All Machines</option>`;

            function buildFilterMachineOptions(machines) {
                let html = `<option value="">All Machines</option>`;
                machines.forEach(m => {
                    const label = m.name ?? m.machine_name ?? ('Machine #' + m.id);
                    html += `<option value="${m.id}">${label}</option>`;
                });
                return html;
            }

            function fetchFilterMachines(projectId) {

                if (!projectId) {
                    $('#filter_machine_id')
                        .html(`<option value="">All Machines</option>`)
                        .val('')
                        .trigger('change.select2');
                    return;
                }

                $.ajax({
                    url: "/request-slip/products/" + projectId,
                    type: "GET",
                    success: function(machines) {

                        filterMachineOptions = buildFilterMachineOptions(machines);

                        $('#filter_machine_id').html(filterMachineOptions);

                        // select2 re-init
                        if ($('#filter_machine_id').hasClass('select2-hidden-accessible')) {
                            $('#filter_machine_id').select2('destroy');
                        }

                        $('#filter_machine_id').select2({
                            width: '100%'
                        });

                        // restore selected machine (after page reload)
                        const selectedMachine = "{{ request('machine') }}";
                        if (selectedMachine) {
                            $('#filter_machine_id')
                                .val(String(selectedMachine))
                                .trigger('change.select2');
                        }
                    },
                    error: function() {
                        $('#filter_machine_id')
                            .html(`<option value="">All Machines</option>`)
                            .val('')
                            .trigger('change.select2');
                    }
                });
            }

            // Project change (Select2 safe)
            $(document).on('change select2:select', '#filter_project_id', function() {
                fetchFilterMachines($(this).val());
            });

            // Page load (filters already applied)
            document.addEventListener("DOMContentLoaded", function() {

                // init select2
                $('#filter_project_id').select2({
                    width: '100%'
                });
                $('#filter_machine_id').select2({
                    width: '100%'
                });

                const projectId = $('#filter_project_id').val();
                if (projectId) {
                    fetchFilterMachines(projectId);
                }
            });

            $('.openDelete').click(function() {
                let id = $(this).data('id');
                let name = $(this).data('name');
                $('#deleteItemName').text(name);
                $('#deleteForm').attr('action', '/request-slip/destroy/' + id);
            });
        </script>
        <script>
            let machinesOptionsHtml = `<option value="">Select Machine</option>`;
            let machinesLoadedForProject = null;

            /* ================================
               SELECT2 INIT
            ================================ */
            function initSelect2($el) {
                if ($.fn.select2) {

                    // destroy if already initialized
                    if ($el.hasClass("select2-hidden-accessible")) {
                        $el.select2('destroy');
                    }

                    $el.select2({
                        width: '100%',
                        dropdownParent: $('#addRSModal')
                    });
                }
            }

            /* ================================
               BUILD MACHINE OPTIONS
            ================================ */
            function buildMachinesOptions(machines) {
                let html = `<option value="">Select Machine</option>`;
                machines.forEach(m => {
                    html += `<option value="${m.id}">${m.name}</option>`;
                });
                return html;
            }

            /* ================================
               FETCH MACHINES
            ================================ */
            function fetchMachines(projectId, cb) {
                $.get("/request-slip/products/" + projectId, function(machines) {
                    machinesOptionsHtml = buildMachinesOptions(machines);
                    machinesLoadedForProject = projectId;
                    cb && cb();
                });
            }

            /* ================================
               ADD ROW
            ================================ */
            function addRow() {

                let html = `
    <div class="rs-item">
        <div class="rs-grids">
            <div class="rs-field">
                <label>Machine</label>
                <select name="items[machine_id][]" 
                        class="rs-control machine-select" 
                        data-select2-selector="status">
                    ${machinesOptionsHtml}
                </select>
                <small class="text-danger error-msg"></small>

            </div>

            <div class="rs-field">
                <label>Inventory</label>
                <select name="items[inventory_id][]" 
                        class="rs-control item-select" 
                        data-select2-selector="status">
                    <option value="">Select</option>
                </select>
                <small class="text-danger error-msg"></small>

            </div>

            <div class="rs-field qty">
                <label>Qty</label>
                <input type="number" name="items[quantity][]" 
                       class="rs-control qty-input" min="1">

                <input type="hidden" name="items[need_qty][]" class="inv-qty">
                <small class="text-danger error-msg"></small>

            </div>
            
            <div class="rs-field full">
                <label>Description</label>
                <input type="text" name="items[description][]" class="rs-control">
            </div>

            <div class="rs-field full">
                <button type="button" class="rs-remove">Remove</button>
            </div>

        </div>
    </div>
    `;

                let $row = $(html);
                $('#rs_items').append($row);

                // ✅ init select2
                initSelect2($row.find('.machine-select'));
                initSelect2($row.find('.item-select'));
            }

            /* ================================
               REMOVE ROW
            ================================ */
            $(document).on('click', '.rs-remove', function() {
                $(this).closest('.rs-item').remove();
            });

            /* ================================
               PROJECT CHANGE → MACHINES LOAD + AUTO ROW
            ================================ */
            $('#project_id').on('change', function() {

                let projectId = $(this).val();

                if (!projectId) return;

                fetchMachines(projectId, function() {

                    $('.machine-select').each(function() {
                        $(this).html(machinesOptionsHtml);
                        initSelect2($(this));
                    });

                    // ✅ AUTO ADD FIRST ROW
                    if ($('#rs_items .rs-item').length === 0) {
                        addRow();
                    }

                });
            });

            /* ================================
               MACHINE CHANGE → INVENTORY LOAD
            ================================ */
            $(document).on('change', '.machine-select', function() {

                let machineId = $(this).val();
                let row = $(this).closest('.rs-item');
                let invSelect = row.find('.item-select');

                invSelect.html('<option>Loading...</option>');

                if (!machineId) {
                    invSelect.html('<option value="">Select</option>');
                    initSelect2(invSelect);
                    return;
                }

                let projectId = $('#project_id').val();

                $.get(`/request-slip/product-items/${machineId}?project_id=${projectId}`, function(resp) {

                    let rows = resp.data ?? resp;

                    let options = `<option value="">Select</option>`;

                    rows.forEach(r => {
                        if (!r.inventory) return;

                        let inv = r.inventory;
                        let need = r.need_qty ?? 0;

                        options += `
                <option value="${inv.id}" data-need="${need}">
                    ${inv.name} ${inv.model ? '['+inv.model+']' : ''}
                </option>
            `;
                    });

                    invSelect.html(options);

                    // ✅ re-init select2
                    initSelect2(invSelect);
                });
            });

            /* ================================
               INVENTORY CHANGE → NEED QTY SET
            ================================ */
            $(document).on('change', '.item-select', function() {

                let row = $(this).closest('.rs-item');
                let need = $(this).find(':selected').data('need') || 0;

                row.find('.inv-qty').val(need);
            });

            /* ================================
               ADD BUTTON
            ================================ */
            $('#addRowBtn').on('click', addRow);

            /* ================================
               INIT
            ================================ */
            $(document).ready(function() {

                // agar modal open hote hi project selected ho
                let projectId = $('#project_id').val();

                if (projectId) {
                    fetchMachines(projectId, function() {
                        if ($('#rs_items .rs-item').length === 0) {
                            addRow();
                        }
                    });
                }

            });

            function showError(el, message) {
                let errorEl = $(el).closest('.rs-field').find('.error-msg');

                if (errorEl.length === 0) {
                    $(el).after(`<small class="text-danger error-msg">${message}</small>`);
                } else {
                    errorEl.text(message);
                }
            }

            function clearErrors() {
                $('.error-msg').text('');
            }

            function isEmpty(value) {
                return value === null || value === undefined || value.trim() === '';
            }

            $('#rsForm').on('submit', function(e) {
                e.preventDefault();

                clearErrors();

                let isValid = true;

                // ======================
                // MAIN FIELDS
                // ======================
                let project = $('#project_id').val();
                let date = $('input[name="transaction_date"]').val();

                if (isEmpty(project)) {
                    showError('#project_id', 'Project is required');
                    isValid = false;
                }

                if (isEmpty(date)) {
                    showError('input[name="transaction_date"]', 'Date is required');
                    isValid = false;
                }

                // ======================
                // ITEMS VALIDATION
                // ======================
                $('.rs-item').each(function(index) {

                    let machine = $(this).find('.machine-select').val();
                    let inventory = $(this).find('.item-select').val();
                    let qty = $(this).find('.qty-input').val();

                    if (isEmpty(machine)) {
                        showError($(this).find('.machine-select'), 'Machine required');
                        isValid = false;
                    }

                    if (isEmpty(inventory)) {
                        showError($(this).find('.item-select'), 'Inventory required');
                        isValid = false;
                    }

                    if (isEmpty(qty) || qty <= 0) {
                        showError($(this).find('.qty-input'), 'Valid quantity required');
                        isValid = false;
                    }
                });

                // ======================
                // FINAL SUBMIT
                // ======================
                if (isValid) {
                    this.submit(); // real submit
                }
            });
        </script>



        @if(session('show_add_form'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                let modal = new bootstrap.Modal(document.getElementById('addRSModal'));
                modal.show();
            });
        </script>
        @endif
        @endpush

@endsection