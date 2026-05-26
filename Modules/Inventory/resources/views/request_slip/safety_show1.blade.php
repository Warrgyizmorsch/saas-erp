@extends('shared::layouts.app')
@section('content')
    {{-- PAGE HEADER --}}
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Requisition Slip </h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                <!-- <li class="breadcrumb-item"><a href="{{ route('issue.index') }}">Requisition Slip Detail</a></li> -->
            </ul>
        </div>
    </div>

    @php
    $rows = $issue->rows ?? collect();
    $st = strtolower(trim($issue->status ?? ''));

    // Issue status helper function
    $getStatusBadge = function ($status) {
    return match (strtolower(trim($status ?? ''))) {
    'full', 'completed' => '<span class="badge bg-soft-success text-success border border-success">Completed</span>',
    'partial_out_of_stock' => '<span class="badge bg-soft-warning text-warning border border-warning">Partial (OOS)</span>',
    'partial_machining' => '<span class="badge bg-soft-info text-info border border-info">Partial (Machining)</span>',
    'pending' => '<span class="badge bg-soft-danger text-danger border border-danger">Pending</span>',
    default => '<span class="badge bg-soft-secondary text-secondary">N/A</span>',
    };
    };
    @endphp

    <div class="main-content container-fluid px-4 mt-4">
        <div class="row">
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">

                        {{-- ================= HEADER SECTION ================= --}}
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div>
                                <h3 class="fw-bold text-dark mb-1">{{ $issue->issue_slip_no }}</h3>
                                <div class="text-muted fs-13">
                                    <span class="me-3">Requisition Ref: <strong class="text-primary">#RS-{{ str_pad($issue->requisitionSlip->rs_id ?? 0, 3, '0', STR_PAD_LEFT) }}</strong></span>
                                    <span>Date: <strong>{{ \Carbon\Carbon::parse($issue->transaction_date)->format('d-m-Y') }}</strong></span>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="mb-2">{!! $getStatusBadge($st) !!}</div>
                                <div class="fs-12 text-muted text-uppercase fw-bold">Transaction ID: {{ $issue->id }}</div>
                            </div>
                        </div>

                        <hr class="my-4 opacity-50">

                        {{-- ================= SUMMARY BOXES ================= --}}
                        <div class="row g-4 mb-5">
                            
                            <div class="col-md-6 col-xl-3">
                                <div class="p-3 border rounded-3 bg-light">
                                    <label class="text-muted fs-11 text-uppercase fw-bold d-block mb-1">Requested By</label>
                                    <span class="fw-semibold text-dark">{{ $issue->requisitionSlip->creator->name ?? 'N/A' }}</span>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <div class="p-3 border rounded-3 bg-light border-start border-primary border-4">
                                    <label class="text-muted fs-11 text-uppercase fw-bold d-block mb-1">Total Issued Qty</label>
                                    <span class="fw-bold text-primary fs-5">{{ number_format($rows->sum('quantity'), 2) }}</span>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <div class="p-3 border rounded-3 bg-light border-start border-danger border-4">
                                    <label class="text-muted fs-11 text-uppercase fw-bold d-block mb-1">Pending Balance</label>
                                    <span class="fw-bold text-danger fs-5">{{ number_format($rows->sum('pending_qty'), 2) }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- ================= ITEMS TABLE ================= --}}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0"><i class="feather feather-package me-2 text-primary"></i>Material List</h6>
                            <span class="text-muted fs-13">Total Items: {{ $rows->count() }}</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle border">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-3" width="60">#</th>
                                        <th>Material / Inventory</th>
                                        <th class="text-end">Requested</th>
                                        <th class="text-end text-primary">Issued</th>
                                        <th class="text-end text-danger">Pending</th>
                                        <th class="text-center">Item Status</th>
                                        <th class="pe-3">Remarks</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white">
                                    @php
                                    $lastRowId = null;
                                    @endphp

                                    @forelse($rows as $index => $row)
                                    @php
                                    // Check if this row is a clone of the previous one
                                    $currentRowId = $row->requisition_slip_row_id;
                                    $isDuplicate = ($lastRowId === $currentRowId);
                                    $lastRowId = $currentRowId;
                                    @endphp

                                    <tr class="{{ $isDuplicate ? 'border-top-0' : '' }}">
                                        <td class="ps-3 text-muted text-center">
                                            {{-- Index sirf pehli baar dikhayenge --}}
                                            {{ !$isDuplicate ? ($index + 1) : '' }}
                                        </td>

                                        {{-- MATERIAL / INVENTORY --}}
                                        <td>
                                            @if(!$isDuplicate)
                                            <div class="fw-bold text-dark">{{ $row->inventory?->name ?? 'N/A' }}</div>
                                            <div class="fs-11 text-muted">{{ $row->inventory?->code ?? '' }}</div>
                                            @else
                                            <span class="text-light-muted" style="opacity: 0.3;">"</span>
                                            @endif
                                        </td>

                                        {{-- QUANTITIES (Ye hamesha dikhayenge kyunki ye har clone ka alag ho sakta hai) --}}
                                        <td class="text-end fw-medium">
                                            {{-- Order Qty sirf main row me dikhana sahi rehta hai --}}
                                            {{ !$isDuplicate ? number_format($row->order_qty, 2) : '' }}
                                        </td>

                                        <td class="text-end fw-bold text-primary">
                                            {{ number_format($row->quantity, 2) }}
                                        </td>

                                        <td class="text-end text-danger">
                                            {{ number_format($row->pending_qty, 2) }}
                                        </td>

                                        <td class="text-center">
                                            {!! $getStatusBadge($row->status) !!}
                                        </td>

                                        <td class="pe-3">
                                            <span class="text-muted fs-12 italic">{{ $row->description ?: '--' }}</span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="feather feather-info me-2"></i> No items found.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- ================= FOOTER NOTE ================= --}}
                        @if($issue->comment)
                        <div class="mt-4 p-3 bg-light rounded border">
                            <small class="text-uppercase fw-bold text-muted d-block mb-1">Store Comments:</small>
                            <p class="mb-0 fs-13">{{ $issue->comment }}</p>
                        </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection