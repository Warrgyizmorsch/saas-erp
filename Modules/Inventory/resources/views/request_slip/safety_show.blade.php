@extends('shared::layouts.app')
@section('content')

    {{-- PAGE HEADER --}}
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Requisition Slip Item Details</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                <!-- <li class="breadcrumb-item"><a href="{{ route('issue.index') }}">Requisition Slip Detail</a></li> -->
                <li class="breadcrumb-item active">View</li>
            </ul>
        </div>
    </div>

    @php
        $rows = $rs->rows ?? collect();

        // RS status
        $rsStatus = strtolower(trim($rs->status ?? ''));

        // Issue status
        $issueStatus = strtolower(trim($issue->status ?? ''));
        
        // Issue badge helper
        $issueBadge = function ($st) {
            return match (strtolower(trim($st ?? ''))) {
                'full' =>
                    '<span class="badge bg-soft-success text-success">Fully Issued</span>',
                'partial_out_of_stock' =>
                    '<span class="badge bg-soft-warning text-warning">Partial (Out of Stock)</span>',
                'partial_machining' =>
                    '<span class="badge bg-soft-info text-info">Partial (Machining)</span>',
                default =>
                    '<span class="badge bg-soft-secondary text-secondary">N/A</span>',
            };
        };
    @endphp

    <div class="main-content container-lg">
        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full position-relative">
                    <div class="card-body pb-3">

                        {{-- ================= TOP HEADER ================= --}}
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="mb-1">Request Slip</h5>

                                <div class="fs-13 text-muted">
                                    RS Code:
                                    <strong>#{{ str_pad($rs->rs_id ?? 0, 3, '0', STR_PAD_LEFT) }}</strong>
                                    @if(!empty($rs->name))
                                        — {{ $rs->name }}
                                    @endif
                                </div>

                                @if($issue)
                                    <div class="fs-13 text-muted mt-1">
                                        Issue Slip No:
                                        <strong>{{ $issue->issue_slip_no }}</strong>
                                        |
                                        Issue Date:
                                        <strong>{{ \Carbon\Carbon::parse($issue->transaction_date)->format('d-m-Y') }}</strong>
                                    </div>
                                @endif
                            </div>

                            {{-- ================= STATUS BADGES ================= --}}
                            <div class="d-flex align-items-center gap-2">

                                {{-- RS STATUS --}}
                                @if(in_array($rsStatus, ['pending','pending_hod']))
                                    <span class="badge bg-soft-warning text-warning">RS Pending</span>
                                @elseif(in_array($rsStatus, ['rejected','rejected_hod']))
                                    <span class="badge bg-soft-danger text-danger">RS Rejected</span>
                                @elseif(in_array($rsStatus, ['approved','completed']))
                                    <span class="badge bg-soft-success text-success">RS Approved</span>
                                @else
                                    <span class="badge bg-soft-secondary text-secondary">RS Unknown</span>
                                @endif

                                {{-- ISSUE STATUS --}}
                                @if($issue)
                                    @if($issueStatus === 'completed')
                                        <span class="badge bg-soft-success text-success">
                                            Issue Completed
                                        </span>
                                    @else
                                        <span class="badge bg-soft-warning text-warning">
                                            Issue Pending
                                        </span>
                                    @endif
                                @else
                                    <span class="badge bg-soft-secondary text-secondary">
                                        Not Issued
                                    </span>
                                @endif

                                {{-- BACK --}}
                                <a href="{{ route('request-slip.safety.index') }}" class="avatar-text avatar-md bg-primary text-white"
                                   title="Back">
                                    <i class="feather feather-arrow-left"></i>
                                </a>

                                {{-- EDIT --}}
                                @if(auth()->user()->isAdmin() || auth()->user()->isAccount())
                                    <a href="{{ route('request-slip.safety.edit', $rs->id) }}"
                                       class="avatar-text avatar-md bg-success text-white"
                                       title="Edit">
                                        <i class="feather feather-edit"></i>
                                    </a>
                                @endif

                            </div>
                        </div>

                        {{-- ================= SUMMARY ================= --}}
                        <div class="row g-3 mb-4 mt-4">
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="fw-semibold mb-2">Request Slip</h6>
                                    <div class="fs-13 text-muted">
                                        <!-- <div><strong>Project:</strong> {{ $rs->project?->name ?? 'Manual / Other' }}</div> -->
                                        <div><strong>Created On:</strong> {{ \Carbon\Carbon::parse($rs->created_on)->format('d-m-Y H:i') ?? '-' }}</div>
                                        <div><strong>Created By:</strong> {{ $rs->creator?->name ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="fw-semibold mb-2">Summary</h6>
                                    <div class="fs-13 text-muted">
                                        <div><strong>Requested By:</strong> {{ $rs->creator?->name ?? 'N/A' }}</div>
                                        <div><strong>Total Items:</strong> {{ $rows->count() }}</div>
                                        <div><strong>Requested Qty:</strong> {{ $rows->sum('quantity') }}</div>
                                        <div><strong>Issued Qty:</strong> {{ $rows->sum('issue_qty') }}</div>
                                        <div><strong>Exited Qty:</strong> {{ $rows->sum('exited_qty') }}</div>
                                        <div><strong>Pending Qty:</strong> {{ $rows->sum('pending_qty') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ================= ITEMS TABLE ================= --}}
                        <h6 class="fw-semibold mt-3 mb-2">Items</h6>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Item</th>
                                        <th class="text-end">Requested</th>
                                        <th class="text-end">Issued</th>
                                        <th class="text-end">Exited Qty</th> {{-- Naya Column --}}
                                        <th class="text-end">Pending</th>
                                        <th>Issue Status</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($rows as $index => $row)
                                        @php
                                            $ir = $issueRowsByReqRowId[$row->id]
                                                  ?? $issueRowsByItemId[$row->item_id]
                                                  ?? null;
                                            
                                            // Red Row Condition: agar exited_qty 0 se zyada hai
                                            $isExited = (float)($row->exited_qty ?? 0) > 0;
                                        @endphp

                                        <tr class="{{ $isExited ? 'table-danger' : '' }}" 
                                            @if($isExited) style="background-color: #f8d7da !important;" @endif>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $row->inventory?->name ?? 'N/A' }}</td>
                                            <td class="text-end">{{ $row->quantity }}</td>
                                            <td class="text-end">{{ $row->issue_qty }}</td>
                                            <td class="text-end fw-bold {{ $isExited ? 'text-danger' : '' }}">
                                                {{ $row->exited_qty ?? 0 }}
                                            </td>
                                            <td class="text-end">{{ $row->pending_qty }}</td>
                                            <td>{!! $issueBadge($ir->status ?? null) !!}</td>
                                            <td class="text-muted">{{ $ir->description ?? 'N/A' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted">
                                                No items found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection