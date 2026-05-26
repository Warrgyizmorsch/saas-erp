@extends('shared::layouts.app')
@section('content')
    {{-- PAGE HEADER --}}
    <div class="page-header px-4 py-3 bg-white border-bottom shadow-sm">
        <div class="page-header-left d-flex align-items-center justify-content-between w-100">
            <div>
                <h5 class="m-b-10 fw-bold">Issue Slip Details</h5>
                <ul class="breadcrumb mb-0" style="font-size: 0.8rem;">
                    <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('issue.index') }}">Issue Management</a></li>
                    <li class="breadcrumb-item active">View Details</li>
                </ul>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('issue.index') }}" class="btn btn-outline-primary btn-sm rounded-pill">
                    <i class="feather feather-arrow-left me-1"></i> Back
                </a>

                {{-- EDIT BUTTON --}}
                @if(isset($issue) && $issue->id && $issue->status != 'Issued')
                    <a href="{{ route('issue.create') }}?req_id={{ $issue->requisitionSlip->id }}" class="btn btn-success btn-sm">
                        <i class="feather feather-edit"></i> Edit
                    </a>
                @endif
            </div>
        </div>
    </div>

    @php
        $rows = $issue->rows ?? collect();
        $st = strtolower(trim($issue->status ?? ''));

        // badge helper
        $getStatusBadge = function ($status) {
            return match (strtolower(trim($status ?? ''))) {
                'full', 'completed' => '<span class="badge bg-soft-success text-success border border-success">Completed</span>',
                'partial_out_of_stock' => '<span class="badge bg-soft-warning text-warning border border-warning">Partial (OOS)</span>',
                'partial_machining' => '<span class="badge bg-soft-info text-info border border-info">Partial (Machining)</span>',
                'pending' => '<span class="badge bg-soft-danger text-danger border border-danger">Pending</span>',
                default => '<span class="badge bg-soft-secondary text-secondary">N/A</span>',
            };
        };

        /**
         * ✅ DESCRIPTION JSON PARSER
         * description may be:
         * 1) JSON array: [{"job_card_id":14,"job_card_no":"JC-113","job_type":"OUT_SOURCE","completion_date":"2026-01-27",...}]
         * 2) plain text
         */
        $parseDescription = function ($desc) {
            $desc = trim((string)($desc ?? ''));
            if ($desc === '') return ['jobcards' => [], 'text' => ''];

            // try decode json
            $decoded = json_decode($desc, true);

            // If JSON array of jobcards
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                // Sometimes it may decode as assoc single object
                if (isset($decoded['job_card_id']) || isset($decoded['job_card_no'])) {
                    $decoded = [$decoded];
                }

                // Normalize only rows that look like job-card data
                $jobcards = [];
                foreach ($decoded as $it) {
                    if (!is_array($it)) continue;

                    $has = isset($it['job_card_id']) || isset($it['job_card_no']) || isset($it['job_type']);
                    if (!$has) continue;

                    $jobcards[] = [
                        'job_card_id'      => $it['job_card_id'] ?? null,
                        'job_card_no'      => $it['job_card_no'] ?? null,
                        'job_type'         => strtoupper((string)($it['job_type'] ?? '')),
                        'completion_date'  => $it['completion_date'] ?? null,
                        'qty'              => $it['qty'] ?? null,
                        'vendor_id'        => $it['vendor_id'] ?? null,
                        'employee_id'      => $it['employee_id'] ?? null,
                    ];
                }

                if (count($jobcards) > 0) {
                    return ['jobcards' => $jobcards, 'text' => ''];
                }
            }

            // fallback plain text
            return ['jobcards' => [], 'text' => $desc];
        };

        /**
         * ✅ GROUP same item into single line
         * key = requisition_slip_row_id
         */
        $grouped = $rows->groupBy('requisition_slip_row_id')->map(function ($g) use ($parseDescription) {

            $first = $g->first();

            $orderQty = (float)($first->quantity ?? 0);                                   // total requested
            $issuedQty = (float)$g->sum('issue_qty');                                     // total issued
            $oosQty = (float)$g->where('status','partial_out_of_stock')->sum('order_qty'); // OOS qty stored in order_qty
            $machQty = (float)$g->where('status','partial_machining')->sum('pending_qty'); // machining qty stored in pending_qty

            $pendingTotal = max($oosQty + $machQty, 0);

            // ✅ Merge ALL descriptions from split rows
            $allJobCards = [];
            $allText = [];

            foreach ($g as $row) {
                $parsed = $parseDescription($row->description ?? '');
                if (!empty($parsed['jobcards'])) {
                    foreach ($parsed['jobcards'] as $jc) {
                        // avoid duplicates by job_card_id+type
                        $key = ($jc['job_card_id'] ?? 'na') . '|' . ($jc['job_type'] ?? 'NA');
                        $allJobCards[$key] = $jc;
                    }
                } else {
                    if (!empty($parsed['text'])) $allText[] = $parsed['text'];
                }
            }

            return (object)[
                'row_id'         => $first->requisition_slip_row_id,
                'machine'        => $first->machine,
                'machine_id'     => $first->machine_id,
                'inventory'      => $first->inventory,
                'inventory_id'   => $first->inventory_id,

                'quantity'       => $orderQty,
                'issue_qty'      => $issuedQty,
                'oos_qty'        => $oosQty,
                'mach_qty'       => $machQty,
                'pending_total'  => $pendingTotal,

                // ✅ merged remarks
                'jobcards'       => array_values($allJobCards),
                'remarks_text'   => implode(' | ', array_unique(array_filter($allText))),
            ];
        })->values();

        // Summary for top boxes
        $sumIssued  = (float)$grouped->sum('quantity');
        $sumPending = (float)$grouped->sum('pending_total');

        // ✅ Job card link builder (OUT_SOURCE / IN_HOUSE)
        $jobCardUrl = function ($jobCardId, $jobType) {
            $jobCardId = (int)($jobCardId ?? 0);
            if ($jobCardId <= 0) return null;

            $jobType = strtoupper(trim((string)$jobType));

            // As you said: job_card/14/show (OUT_SOURCE / IN_HOUSE both supported)
            // If you have named route, replace with route('job_card.show', $jobCardId)
            $base = url("job_card/{$jobCardId}/show");

            // Optional: add type query (safe if controller uses it)
            // OUT_SOURCE / IN_HOUSE
            return $base . '?type=' . urlencode($jobType);
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
                                    <span class="me-3">
                                        Requisition Ref:
                                        <strong class="text-primary">#RS-{{ str_pad($issue->requisitionSlip->rs_id ?? 0, 3, '0', STR_PAD_LEFT) }}</strong>
                                    </span>
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
                                    <label class="text-muted fs-11 text-uppercase fw-bold d-block mb-1">Project Name</label>
                                    <span class="fw-semibold text-dark">{{ $issue->project?->name ?? 'Manual Entry' }}</span>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <div class="p-3 border rounded-3 bg-light">
                                    <label class="text-muted fs-11 text-uppercase fw-bold d-block mb-1">Requested By</label>
                                    <span class="fw-semibold text-dark">{{ $issue->requisitionSlip->creator->name ?? 'N/A' }}</span>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <div class="p-3 border rounded-3 bg-light border-start border-primary border-4">
                                    <label class="text-muted fs-11 text-uppercase fw-bold d-block mb-1">Total Issued Qty</label>
                                    <span class="fw-bold text-primary fs-5">{{ number_format($sumIssued, 2) }}</span>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <div class="p-3 border rounded-3 bg-light border-start border-danger border-4">
                                    <label class="text-muted fs-11 text-uppercase fw-bold d-block mb-1">Pending Balance (OOS + Machining)</label>
                                    <span class="fw-bold text-danger fs-5">{{ number_format($sumPending, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- ================= ITEMS TABLE ================= --}}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0"><i class="feather feather-package me-2 text-primary"></i>Material List</h6>
                            <span class="text-muted fs-13">Total Items: {{ $grouped->count() }}</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle border">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-3" width="60">#</th>
                                        <th>Machine Details</th>
                                        <th>Material / Inventory</th>
                                        <th class="text-end">Order Qty</th>
                                        <th class="text-end ">Issued Qty</th>
                                        <th class="text-end ">Out of Stock</th>
                                        <th class="text-end ">Machining</th>
                                        <th class="text-end ">Pending Total</th>
                                        <th class="text-center">Item Status</th>
                                        <th class="pe-3">Remarks (Merged)</th>
                                    </tr>
                                </thead>

                                <tbody class="bg-white">
                                    @forelse($grouped as $index => $r)
                                        <tr>
                                            <td class="ps-3 text-muted text-center">{{ $index + 1 }}</td>

                                            <td>
                                                <div class="fw-semibold text-dark">{{ $r->machine?->name ?? 'N/A' }}</div>
                                                <div class="fs-11 text-muted">ID: {{ $r->machine_id }}</div>
                                            </td>

                                            <td>
                                                <div class="fw-bold text-dark">{{ $r->inventory?->name ?? 'N/A' }}</div>
                                                <div class="fs-11 text-muted">{{ $r->inventory?->code ?? '' }}</div>
                                            </td>

                                            <td class="text-end fw-medium">{{ number_format($r->quantity, 2) }}</td>
                                            <td class="text-end fw-bold ">{{ number_format($r->issue_qty, 2) }}</td>
                                            <td class="text-end ">{{ number_format($r->oos_qty, 2) }}</td>
                                            <td class="text-end ">{{ number_format($r->mach_qty, 2) }}</td>
                                            <td class="text-end text-danger fw-bold">{{ number_format($r->pending_total, 2) }}</td>

                                            <td class="text-center">
                                                @if($r->pending_total <= 0.0001)
                                                    {!! $getStatusBadge('completed') !!}
                                                @else
                                                    @if($r->oos_qty > 0 && $r->mach_qty > 0)
                                                        <span class="badge bg-soft-danger text-danger border border-danger">Partial (OOS + Machining)</span>
                                                    @elseif($r->oos_qty > 0)
                                                        {!! $getStatusBadge('partial_out_of_stock') !!}
                                                    @else
                                                        {!! $getStatusBadge('partial_machining') !!}
                                                    @endif
                                                @endif
                                            </td>

                                            {{-- ✅ MERGED REMARKS --}}
                                            <td class="pe-3">
                                                {{-- Job card list --}}
                                                @if(!empty($r->jobcards))
                                                    <div class="mb-1">
                                                        <small class="text-muted fw-bold d-block">Job Cards:</small>
                                                        <ul class="mb-1 ps-3" style="font-size: 0.82rem;">
                                                            @foreach($r->jobcards as $jc)
                                                                @php
                                                                    $jcNo   = $jc['job_card_no'] ?? ('JC-' . ($jc['job_card_id'] ?? ''));
                                                                    $jcType = strtoupper($jc['job_type'] ?? '');
                                                                    $jcDate = $jc['completion_date'] ?? null;
                                                                    $link   = $jobCardUrl($jc['job_card_id'] ?? null, $jcType);
                                                                @endphp

                                                                <li class="mb-1">
                                                                    <span class="badge bg-light text-dark border me-1">{{ $jcType ?: 'JOB' }}</span>

                                                                    @if($link)
                                                                        <a href="{{ $link }}" class="text-primary fw-semibold" target="_blank">
                                                                            {{ $jcNo }}
                                                                        </a>
                                                                    @else
                                                                        <span class="fw-semibold">{{ $jcNo }}</span>
                                                                    @endif

                                                                    @if($jcDate)
                                                                        <span class="text-muted"> • Completion: {{ \Carbon\Carbon::parse($jcDate)->format('d-m-Y') }}</span>
                                                                    @endif
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endif

                                                {{-- Plain text remarks merged --}}
                                                @if(!empty($r->remarks_text))
                                                    <div class="text-muted" style="font-size: 0.82rem;">
                                                        <small class="fw-bold d-block">Note:</small>
                                                        <span>{{ $r->remarks_text }}</span>
                                                    </div>
                                                @endif

                                                @if(empty($r->jobcards) && empty($r->remarks_text))
                                                    <span class="text-muted fs-12">--</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center py-5 text-muted">
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
