@extends('shared::layouts.app')
@section('content')
    <style>
        .main-content { padding: 20px; }
        .form-label-bold { font-weight: 600; font-size: 0.85rem; color: #4a5568; margin-bottom: 4px; }
        .table thead th { background-color: #f8f9fa; text-transform: uppercase; font-size: 0.75rem; color: #718096; border-bottom: 2px solid #edf2f7; }
        .is-invalid-custom { border: 2px solid #e53e3e !important; background-color: #fff5f5 !important; }
        .error-msg { color: #e53e3e; font-size: 0.7rem; font-weight: bold; margin-top: 2px; }
        .table-container { border: 1px solid #edf2f7; border-radius: 8px; background: #fff; overflow: hidden; }
        .cloned-row { background-color: #f9fbff; border-left: 4px solid #4299e1; }
        .split-badge { display: inline-block; font-size: 0.70rem; font-weight: 700; padding: 3px 10px; border-radius: 999px; border: 1px solid #cfe3ff; background: #eef6ff; color: #1d4ed8; }
        .ghost-cell { color:#94a3b8; font-size: 0.75rem; }
        .text-finish { color: #059669; font-weight: 700; } /* Green for Finish */
    </style>

    <div class="main-content">
        <div class="card shadow-sm border-0 rounded-lg">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="feather feather-file-text me-2 text-primary"></i>
                    Update Issue Slip
                </h5>
                <span class="badge rounded-pill bg-soft-primary text-primary px-3 py-2 border border-primary">
                    Requisition: #{{ $issue->requisitionSlip->requisition_slip_no }}
                </span>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('issue.update', $issue->id) }}" method="POST" id="issueForm">
                    @csrf
                    @method('PUT')

                    {{-- HEADER --}}
                    <div class="row g-4 mb-5">
                        <div class="col-md-3">
                            <label class="form-label-bold">Issue Slip No</label>
                            <input type="text" class="form-control bg-light fw-bold" value="{{ $issue->issue_slip_no }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-bold">Transaction Date</label>
                            <input type="date" name="issue_date" class="form-control" value="{{ $today }}" required readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-bold">Project / Site</label>
                            <div class="py-2 fw-semibold text-primary">
                                <i class="feather feather-map-pin me-1"></i>
                                {{ $issue->project->name ?? 'N/A' }}
                            </div>
                        </div>
                    </div>

                    @php
                        $rowCounts = [];
                        foreach ($issue->rows as $r) {
                            if ($r->status === 'full') continue;
                            $rid = (int)($r->requisition_slip_row_id ?? 0);
                            if ($rid > 0) $rowCounts[$rid] = ($rowCounts[$rid] ?? 0) + 1;
                        }
                        $seenFirst = [];
                    @endphp

                    {{-- ITEMS TABLE --}}
                    <div class="table-container mb-4 shadow-sm">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4 text-center" width="70">Split</th>
                                    <th>Material / Model</th>
                                    <th class="text-end" width="150">In Stock</th>
                                    <th class="text-end" width="120">Pending</th>
                                    <th class="text-end" width="150">Today Issue</th>
                                    <th width="180">Status</th>
                                    <th class="pe-4">Remarks</th>
                                </tr>
                            </thead>

                            <tbody>
                                @php $globalIndex = 0; @endphp
                                @foreach($issue->rows as $row)
                                @php 
                                        $pending = (float)($row->pending_qty ?? 0); 
                                    @endphp
                                   @if($row->status !== 'full' || $pending > 0)
                                        @php
                                            $pending = (float)($row->pending_qty ?? 0);
                                            $totalStock = (float)($row->inventory->available_stock ?? 0);
                                            $usableFinish = (float)($row->usable_finish_stock ?? 0);
                                            $classification = strtoupper(trim((string)($row->inventory->classification ?? '')));
                                            
                                            // Agar Semi-Finish hai toh Usable Stock, warna Total Stock ko limit maane
                                            $limitStock = ($classification === 'SEMI_FINISH') ? $usableFinish : $totalStock;

                                            $reqRowId = (int)($row->requisition_slip_row_id ?? 0);
                                            $isSplitGroup = $reqRowId > 0 && (($rowCounts[$reqRowId] ?? 0) > 1);
                                            $isFirstInGroup = true;
                                            if ($isSplitGroup) {
                                                if (!empty($seenFirst[$reqRowId])) $isFirstInGroup = false;
                                                else $seenFirst[$reqRowId] = true;
                                            }
                                        @endphp

                                        <tr class="item-row {{ (!$isFirstInGroup && $isSplitGroup) ? 'cloned-row' : '' }}"
                                            data-pending="{{ $pending }}"
                                            data-stock-limit="{{ $limitStock }}">
                                            
                                            <td class="ps-4 text-center">
                                                @if($isSplitGroup) <span class="split-badge">Split</span> @endif
                                            </td>

                                            <td>
                                                @if(!$isSplitGroup || $isFirstInGroup)
                                                    <div class="fw-bold">{{ $row->inventory->name }}</div>
                                                    <div class="small text-muted">{{ $row->inventory->model }}</div>
                                                @else
                                                    <span class="ghost-cell">↳ Same item split entry</span>
                                                @endif
                                            </td>

                                            <td class="text-end">
                                                @if(!$isSplitGroup || $isFirstInGroup)
                                                    <div class="fw-bold text-dark">{{ number_format($totalStock, 2) }}</div>
                                                    @if($classification === 'SEMI_FINISH')
                                                        <div class="small text-finish">Finish: {{ number_format($usableFinish, 2) }}</div>
                                                    @endif
                                                @endif
                                            </td>

                                            <td class="text-end fw-semibold text-muted">
                                                {{ number_format($pending, 0) }}
                                            </td>

                                            <td>
                                                <input type="number"
                                                    name="items[{{ $globalIndex }}][issue_qty]"
                                                    class="form-control form-control-sm text-end fw-bold issueQty"
                                                    step="0.01" min="0"
                                                    value="0">
                                                <div class="error-msg d-none"></div>
                                            </td>

                                            <td>
                                                <select name="items[{{ $globalIndex }}][status]" class="form-select form-select-sm">
                                                    <option value="full" {{ $row->status=='full'?'selected':'' }}>Full</option>
                                                    <option value="partial_out_of_stock" {{ $row->status=='partial_out_of_stock'?'selected':'' }}>Out of Stock</option>
                                                    <option value="partial_machining" {{ $row->status=='partial_machining'?'selected':'' }}>Machining</option>
                                                </select>
                                            </td>

                                            <td class="pe-4">
                                                <input type="text" name="items[{{ $globalIndex }}][description]" class="form-control form-control-sm" value="{{ $row->description }}">
                                                <input type="hidden" name="items[{{ $globalIndex }}][row_id]" value="{{ $row->requisition_slip_row_id }}">
                                                <input type="hidden" name="items[{{ $globalIndex }}][issue_row_id]" value="{{ $row->id }}">
                                                <input type="hidden" name="items[{{ $globalIndex }}][inventory_id]" value="{{ $row->inventory->id }}">
                                            </td>
                                        </tr>
                                        @php $globalIndex++; @endphp
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- FOOTER --}}
                    <div class="row g-4 mt-2">
                        <div class="col-lg-7">
                            <label class="form-label-bold">Notes</label>
                            <textarea name="comment" class="form-control" rows="2">{{ $issue->comment }}</textarea>
                        </div>
                        <div class="col-lg-5 text-end">
                            <span class="text-muted small">Total Issue Quantity</span>
                            <h3 class="fw-bold text-primary" id="display_total_issue">0.00</h3>
                            <button type="submit" id="submitBtn" class="btn btn-primary btn-lg px-5 rounded-pill mt-2">Update Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const submitBtn = document.getElementById('submitBtn');
        const toNum = (v) => { const n = parseFloat(v); return Number.isFinite(n) ? n : 0; };

        function validateAndUpdate() {
            let hasError = false;
            let total = 0;

            document.querySelectorAll('tr.item-row').forEach(row => {
                const input = row.querySelector('.issueQty');
                const errorBox = input.nextElementSibling;
                
                const pending = toNum(row.dataset.pending);
                const limit = toNum(row.dataset.stockLimit); // Yeh limit SEMI_FINISH ke liye 'usable' hai
                let val = toNum(input.value);

                // Validation Rule: Min of Pending & Stock Limit
                let finalAllowed = Math.min(pending, limit);

                input.classList.remove('is-invalid-custom');
                errorBox.classList.add('d-none');

                if (val > finalAllowed + 0.000001) {
                    hasError = true;
                    input.classList.add('is-invalid-custom');
                    errorBox.classList.remove('d-none');
                    errorBox.innerText = `Limit: ${finalAllowed.toFixed(2)}`;
                }
                total += val;
            });

            document.getElementById('display_total_issue').innerText = total.toFixed(2);
            submitBtn.disabled = hasError;
        }

        document.querySelectorAll('.issueQty').forEach(input => {
            input.addEventListener('input', validateAndUpdate);
            input.addEventListener('blur', function() {
                if(this.value === "" || this.value < 0) this.value = "0.00";
                validateAndUpdate();
            });
        });

        document.getElementById('issueForm').addEventListener('submit', function (e) {
            validateAndUpdate();
            if (submitBtn.disabled) {
                e.preventDefault();
                alert('Quantity limit check karein.');
            }
        });

        validateAndUpdate();
    </script>
@endsection