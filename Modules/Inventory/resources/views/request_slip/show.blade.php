@extends('shared::layouts.app')
@section('content')

    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Requisition Slip Item Details</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('issue.index') }}">Requisition Slip Detail</a></li>
                <li class="breadcrumb-item active">View</li>
            </ul>
        </div>
    </div>

    @php
        $rows = $rs->rows ?? collect();

        // ✅ Controller should pass: $issues, $issueRowsGroupedByReqRowId, $issueRowsGroupedByItemId
        $issues = $issues ?? collect();

        $rsStatus = strtolower(trim($rs->status ?? ''));

        $issueHeaderBadge = function ($st) {
            $st = strtolower(trim($st ?? ''));
            return match ($st) {
                'issued', 'completed' => '<span class="badge bg-soft-success text-success">Issued</span>',
                'partial issue', 'partial_issued' => '<span class="badge bg-soft-warning text-warning">Partial Issue</span>',
                default => '<span class="badge bg-soft-secondary text-secondary">Not Issued</span>',
            };
        };

        $rowIssueBadge = function ($st) {
            $st = strtolower(trim($st ?? ''));
            return match ($st) {
                'full' => '<span class="badge bg-soft-success text-success">Full</span>',
                'partial_out_of_stock' => '<span class="badge bg-soft-warning text-warning">Out of Stock</span>',
                'partial_machining' => '<span class="badge bg-soft-info text-info">Machining</span>',
                default => '<span class="badge bg-soft-secondary text-secondary">N/A</span>',
            };
        };

        /**
         * ✅ Parse Description
         * - JSON array => jobcards
         * - else => plain text
         */
        $parseDesc = function ($desc) {
            $desc = trim((string)($desc ?? ''));
            if ($desc === '') return ['jobcards' => [], 'text' => ''];

            $decoded = json_decode($desc, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {

                // single object -> array
                if (isset($decoded['job_card_id']) || isset($decoded['job_card_no'])) {
                    $decoded = [$decoded];
                }

                $jobcards = [];
                foreach ($decoded as $it) {
                    if (!is_array($it)) continue;

                    $has = isset($it['job_card_id']) || isset($it['job_card_no']) || isset($it['job_type']);
                    if (!$has) continue;

                    $jobcards[] = [
                        'job_card_id'     => $it['job_card_id'] ?? null,
                        'job_card_no'     => $it['job_card_no'] ?? null,
                        'job_type'        => strtoupper((string)($it['job_type'] ?? '')),
                        'completion_date' => $it['completion_date'] ?? null,
                        'qty'             => $it['qty'] ?? null,
                    ];
                }

                if (count($jobcards) > 0) return ['jobcards' => $jobcards, 'text' => ''];
            }

            return ['jobcards' => [], 'text' => $desc];
        };

        // ✅ Job Card link builder
        $jobCardUrl = function ($jobCardId, $jobType) {
            $jobCardId = (int)($jobCardId ?? 0);
            if ($jobCardId <= 0) return null;

            $jobType = strtoupper(trim((string)$jobType));
            return url("job_card/{$jobCardId}/show") . '?type=' . urlencode($jobType);
        };

        // ✅ Latest issue (only for header display)
        $latestIssue = $issues->first();
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
                                    <strong>#{{ str_pad($rs->requisition_slip_no ?? 0, 3, '0', STR_PAD_LEFT) }}</strong>
                                    @if(!empty($rs->name))
                                        — {{ $rs->name }}
                                    @endif
                                </div>

                                {{-- ✅ Latest Issue info (header only) --}}
                                @if($latestIssue)
                                    <div class="fs-13 text-muted mt-1">
                                        <span class="badge bg-soft-primary text-primary me-2">Latest Issue</span>
                                        Issue Slip No:
                                        <strong>{{ $latestIssue->issue_slip_no ?? '-' }}</strong>
                                        |
                                        Issue Date:
                                        <strong>
                                            {{ !empty($latestIssue->transaction_date) ? \Carbon\Carbon::parse($latestIssue->transaction_date)->format('d-m-Y') : '-' }}
                                        </strong>
                                        |
                                        {!! $issueHeaderBadge($latestIssue->status ?? null) !!}
                                    </div>
                                @else
                                    <div class="fs-13 text-muted mt-1">
                                        <span class="badge bg-soft-secondary text-secondary">No Issue Found</span>
                                    </div>
                                @endif
                            </div>

                            {{-- ================= STATUS BADGES ================= --}}
                            <div class="d-flex align-items-center gap-2">

                                <a href="javascript:void(0)"
                                   class="btn btn-light btn-sm p-1"
                                   title="Requisition Slip History"
                                   data-bs-toggle="offcanvas"
                                   data-bs-target="#rsHistory{{ $rs->id }}">
                                    <i class="fa fa-history"></i>
                                </a>

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

                                {{-- BACK --}}
                                <a href="{{ route('request-slip.index') }}"
                                   class="avatar-text avatar-md bg-primary text-white"
                                   title="Back">
                                    <i class="feather feather-arrow-left"></i>
                                </a>

                                {{-- EDIT --}}
                                @if(auth()->user()->isAccount() || auth()->user()->isSuperAdmin())
                                    <a href="{{ route('request-slip.edit', $rs->id) }}"
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
                                        <div><strong>Project:</strong> {{ $rs->project?->name ?? 'Manual / Other' }}</div>
                                        <div><strong>Created On:</strong> {{ !empty($rs->created_on) ? \Carbon\Carbon::parse($rs->created_on)->format('d-m-Y H:i') : '-' }}</div>
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
                                        <div><strong>Issued Qty:</strong> {{ $rows->sum('issued_qty') }}</div>
                                        <div><strong>Out Of Stock Qty:</strong> {{ $rows->sum('order_qty') }}</div>
                                        <div><strong>Machining Qty:</strong> {{ $rows->sum('pending_qty') }}</div>
                                        <div><strong>Exceeded Qty:</strong> {{ $rows->sum('exited_qty') }}</div>
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
                                        <th>Machine</th>
                                        <th>Item</th>
                                        <th class="text-end">Requested</th>
                                        <th class="text-end">Issued</th>
                                        <th class="text-end">Out Of Stock</th>
                                        <th class="text-end">Machining</th>
                                        @if (Auth::user()->isAdmin())
                                            <th class="text-end">Exceed Qty</th>
                                        @endif
                                        <th>Description </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($rows as $index => $row)
                                        @php
                                            // ✅ Get ALL issue rows for same requisition_slip_row_id (same as $row->id)
                                            $issueRowsSameId = $issueRowsGroupedByReqRowId->get($row->id) ?? collect();

                                            // fallback by item id (optional)
                                            if ($issueRowsSameId->isEmpty()) {
                                                $issueRowsSameId = $issueRowsGroupedByItemId->get($row->item_id) ?? collect();
                                            }

                                            // ✅ Merge all descriptions
                                            $allJobCards = [];
                                            $allText = [];

                                            foreach ($issueRowsSameId as $ir) {
                                                $parsed = $parseDesc($ir->description ?? '');

                                                if (!empty($parsed['jobcards'])) {
                                                    foreach ($parsed['jobcards'] as $jc) {
                                                        $k = ($jc['job_card_id'] ?? 'na') . '|' . ($jc['job_type'] ?? 'NA');
                                                        $allJobCards[$k] = $jc;
                                                    }
                                                } else {
                                                    if (!empty($parsed['text'])) $allText[] = $parsed['text'];
                                                }
                                            }

                                            $allJobCards = array_values($allJobCards);
                                            $mergedText  = implode(' | ', array_unique(array_filter($allText)));

                                            $isExited = (float)($row->exited_qty ?? 0) > 0;
                                        @endphp

                                        <tr @if($isExited) style="background-color:#f8d7da !important;" @endif>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $row->machine?->name ?? 'N/A' }}</td>
                                            <td>{{ $row->inventory?->name ?? 'N/A' }}</td>

                                            <td class="text-end">{{ $row->quantity ?? 0 }}</td>
                                            <td class="text-end">{{ $row->issued_qty ?? 0 }}</td>
                                            <td class="text-end">{{ $row->order_qty ?? 0 }}</td>
                                            <td class="text-end">{{ $row->pending_qty ?? 0 }}</td>
                                            @if (Auth::user()->isAdmin())
                                             <td class="text-end fw-bold {{ $isExited ? 'text-danger' : '' }}">
                                                {{ $row->exited_qty ?? 0 }}
                                            </td>
                                            @endif
                                            {{-- ✅ Merged Description --}}
                                            <td class="text-muted" style="min-width:340px;">
                                                @if(empty($allJobCards) && empty($mergedText))
                                                    <span class="text-muted">N/A</span>
                                                @else
                                                    @if(!empty($allJobCards))
                                                        <small class="text-muted fw-bold d-block mb-1">Job Cards:</small>
                                                        <ul class="mb-2 ps-3" style="font-size:0.82rem;">
                                                            @foreach($allJobCards as $jc)
                                                                @php
                                                                    $jcNo   = $jc['job_card_no'] ?? ('JC-' . ($jc['job_card_id'] ?? ''));
                                                                    $jcType = strtoupper($jc['job_type'] ?? '');
                                                                    $jcDate = $jc['completion_date'] ?? null;
                                                                    $link   = $jobCardUrl($jc['job_card_id'] ?? null, $jcType);
                                                                @endphp
                                                                <li class="mb-1">
                                                                    <span class="badge bg-light text-dark border me-1">{{ $jcType ?: 'JOB' }}</span>

                                                                    @if($link)
                                                                        <a href="{{ $link }}" class="text-primary fw-semibold" target="_blank">{{ $jcNo }}</a>
                                                                    @else
                                                                        <span class="fw-semibold">{{ $jcNo }}</span>
                                                                    @endif

                                                                    @if($jcDate)
                                                                        <span class="text-muted">
                                                                             ({{ \Carbon\Carbon::parse($jcDate)->format('M-d') }})
                                                                        </span>
                                                                    @endif

                                                                   
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @endif

                                                    @if(!empty($mergedText))
                                                        <small class="text-muted fw-bold d-block">Note:</small>
                                                        <span>{{ $mergedText }}</span>
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted">No items found.</td>
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

    {{-- ================= OFFCANVAS HISTORY ================= --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="rsHistory{{ $rs->id }}">
        <div class="offcanvas-header ht-80 px-4 border-bottom border-gray-5">
            <h2 class="fs-16 fw-bold mb-0">Requisition Slip History</h2>
            <button type="button" class="btn-close btn-close-gray ms-auto" data-bs-dismiss="offcanvas"></button>
        </div>

        <div class="offcanvas-body">

            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">RS Information</h6>

                    <div class="row g-3">
                        <div class="col-6">
                            <small class="text-muted">RS Code</small>
                            <div class="fw-semibold">
                                #RS-{{ str_pad($rs->rs_id ?? 0, 3, '0', STR_PAD_LEFT) }}
                            </div>
                        </div>

                        <div class="col-6">
                            <small class="text-muted">Current Status</small><br>
                            <span class="badge bg-soft-primary text-primary">
                                {{ str_replace('_',' ', $rs->status ?? '-') }}
                            </span>
                        </div>

                        <div class="col-6">
                            <small class="text-muted">Project</small>
                            <div>{{ $rs->project?->name ?? 'N/A' }}</div>
                        </div>

                        <div class="col-6">
                            <small class="text-muted">Created By</small>
                            <div>{{ $rs->creator?->name ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Status History</h6>

                    @forelse($rs->histories as $history)
                        @php
                            $color = match($history->status) {
                                'Approved' => 'success',
                                'Pending' => 'warning',
                                'Rejected' => 'danger',
                                'Hold' => 'danger',
                                default => 'secondary',
                            };
                        @endphp

                        <div class="hstack gap-3 justify-content-between">
                            <div class="hstack gap-3">
                                <div class="wd-7 ht-7 bg-{{ $color }} rounded-circle"></div>

                                <div class="ps-3 border-start border-3 border-{{ $color }} rounded">
                                    <div class="fw-semibold text-uppercase">
                                        {{ str_replace('_',' ', $history->status ?? '-') }}
                                    </div>
                                    <div class="fs-12 fw-medium text-muted">
                                        {{ $history->user?->name ?? 'System' }}
                                    </div>
                                </div>
                            </div>

                            <div class="fw-bold fs-12 text-muted">
                                {{ !empty($history->created_at) ? \Carbon\Carbon::parse($history->created_at)->format('d-m-Y H:i') : '-' }}
                            </div>
                        </div>

                        <hr class="border-dashed my-3">
                    @empty
                        <div class="text-center text-muted py-3">No history found</div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

@endsection
