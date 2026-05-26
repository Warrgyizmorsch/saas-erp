@extends('shared::layouts.app')
@section('content')
    <style>
        :root {
            --primary: #4338ca;
            --bg: #f8fafc;
            --card: #ffffff;
            --text: #0f172a;
            --muted: #64748b;
            --border: #e2e8f0;
            --soft: #f1f5f9;
            --danger: #dc2626;
            --success: #16a34a;
        }

        .main-content {
            padding: 24px;
            background: var(--bg);
            min-height: 100vh;
        }

        .form-label-bold {
            font-weight: 800;
            font-size: 0.72rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 6px;
        }

        .info-section {
            background: var(--card);
            border-radius: 14px;
            padding: 18px;
            border: 1px solid var(--border);
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.04);
            height: 100%;
        }

        .vendor-icon {
            width: 44px;
            height: 44px;
            background: #eef2ff;
            color: var(--primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            border: 1px solid #e0e7ff;
        }

        .meta-input {
            border: 1px solid var(--border) !important;
            border-radius: 12px !important;
            padding: 10px 12px !important;
            font-weight: 800;
        }

        .meta-input:focus {
            border-color: var(--primary) !important;
            box-shadow: none !important;
        }

        .table-container {
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid var(--border);
            background: var(--card);
        }

        .table thead th {
            background: var(--soft);
            color: #475569;
            font-weight: 900;
            font-size: 0.73rem;
            padding: 14px 12px;
            text-transform: uppercase;
            letter-spacing: .06em;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        .table tbody td {
            padding: 12px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        .table tbody tr:last-child td {
            border-bottom: 0;
        }

        .badge-soft {
            background: #f8fafc;
            border: 1px solid var(--border);
            color: var(--text);
            padding: 6px 10px;
            border-radius: 999px;
            font-weight: 900;
            font-size: .78rem;
        }

        .qty-input {
            max-width: 140px;
            text-align: center;
            font-weight: 900;
            border-radius: 12px;
            border: 2px solid var(--border);
            padding: 10px 10px;
            font-size: .92rem;
        }

        .qty-input.received {
            color: var(--primary);
        }

        .qty-input.accepted {
            background: #f8fafc;
            color: var(--text);
        }

        .qty-input:focus {
            border-color: var(--primary);
            box-shadow: none;
        }

        .error-msg {
            font-size: .72rem;
            font-weight: 900;
            color: var(--danger);
            margin-top: 6px;
        }

        .footer-box {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 14px 16px;
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.04);
            display: inline-block;
        }

        .total-val {
            font-size: 1.6rem;
            font-weight: 1000;
            color: var(--primary);
            margin: 0;
        }

        .btn-save {
            border-radius: 14px;
            font-weight: 1000;
            padding: 12px 22px;
        }

        /* ✅ Responsive = Horizontal scroll on small screens */
        .grn-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* ✅ table width so columns don't squeeze */
        .grn-table {
            min-width: 1100px; /* adjust if you add more cols */
        }

        /* ✅ nice scrollbar (optional) */
        .grn-responsive::-webkit-scrollbar {
            height: 8px;
        }

        .grn-responsive::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 999px;
        }

        .grn-responsive::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        /* ✅ On very small screens padding reduce */
        @media (max-width: 576px) {
            .main-content { padding: 14px; }
            .qty-input { max-width: 120px; }
        }
    </style>

    @php
        // ✅ Show only pending items (remaining > 0)
        $pendingItems = $purchaseOrder->items->filter(function($it){
            $already = (float)($it->received_qty ?? 0);
            $remaining = max((float)$it->ordered_qty - $already, 0);
            return $remaining > 0;
        });
    @endphp

    <div class="main-content">
        <form action="{{ route('grn.store') }}" method="POST" id="grnForm">
            @csrf
            <input type="hidden" name="po_id" value="{{ $purchaseOrder->id }}">

            {{-- TOP HEADER --}}
            <div class="row g-4 mb-4">
                <div class="col-lg-7">
                    <div class="info-section">
                        <div class="d-flex align-items-center mb-3">
                            <div class="vendor-icon me-3">
                                <i class="fas fa-truck-loading"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold text-dark">Supplier Information</h6>
                                <p class="text-muted small mb-0">Supplier details for this receipt</p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-5">
                                <label class="form-label-bold">Name</label>
                                <div class="fw-bold text-dark fs-5">{{ $purchaseOrder->supplier?->supplier_name ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label-bold">Mobile / Contact</label>
                                <div class="text-dark">{{ $purchaseOrder->supplier?->mobile ?? 'Not Provided' }}</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label-bold">GST</label>
                                <div class="badge bg-soft-info text-info border border-info-subtle">
                                    {{ $purchaseOrder->supplier?->gstin ?? 'UNREGISTERED' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="info-section border-primary-subtle">
                        <h6 class="fw-bold text-dark">
                            <i class="fas fa-file-invoice me-2 text-primary"></i>GRN Meta Data
                        </h6>

                        <div class="row g-3 mt-1">
                            <div class="col-6">
                                <label class="form-label-bold">GRN Number</label>
                                <input type="text" name="grn_no" value="{{ $grnNo }}"
                                    class="form-control meta-input bg-light border-0" readonly>
                            </div>

                            <div class="col-6">
                                <label class="form-label-bold">GRN Date</label>
                                <input type="date" name="grn_date" value="{{ $today }}"
                                    class="form-control meta-input" required>
                            </div>

                            <div class="col-6">
                                <label class="form-label-bold">Challan No</label>
                                <input type="text" name="invoice_no"
                                    class="form-control meta-input"
                                    placeholder="Optional">
                            </div>

                            <div class="col-6">
                                <label class="form-label-bold">PO Number</label>
                                <input type="text" value="{{ $purchaseOrder->po_number ?? '' }}"
                                    class="form-control meta-input bg-light border-0" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- If no pending items --}}
            @if($pendingItems->count() === 0)
                <div class="alert alert-info fw-bold">
                    All items are already received. No pending quantity left for this PO.
                </div>
            @endif

            {{-- ITEM TABLE --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">Pending Inventory Items</h6>
                        <span class="badge-soft">Accepted Qty will be added to Stock (IN)</span>
                    </div>
                </div>

                <div class="table-container">
                    <div class="grn-responsive">
                        <table class="table table-hover align-middle mb-0 grn-table">
                            <thead>
                                <tr>
                                    <th class="ps-4" width="60">Sr.</th>
                                    <th>Material / Description</th>
                                    <th class="text-center">PO Qty</th>
                                    <th class="text-center">Remaining</th>
                                    <th class="text-center" width="170">Received</th>
                                    <th class="text-center" width="170">Rejected</th>
                                    <th class="text-center" width="170">Accepted</th>
                                    <th class="pe-4" width="180">Remark</th>
                                </tr>
                            </thead>

                            <tbody>
                                @php $i = 0; @endphp
                                @forelse($pendingItems as $item)
                                    @php
                                        $already = (float)($item->received_qty ?? 0);
                                        $remaining = max((float)$item->ordered_qty - $already, 0);
                                    @endphp

                                    <tr>
                                        <td class="ps-4 text-muted">{{ $i + 1 }}</td>

                                        <td>
                                            <div class="fw-bold text-dark">{{ $item->inventory?->name }}</div>
                                            <div class="small text-muted">{{ $item->inventory?->model }}</div>
                                        </td>

                                        <td class="text-center">
                                            <span class="fw-bold">{{ number_format((float)$item->ordered_qty, 2) }}</span>
                                        </td>

                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border">
                                                {{ number_format($remaining, 2) }}
                                            </span>
                                        </td>

                                        <td>
                                            <div class="d-flex flex-column align-items-center">
                                                <input type="number"
                                                    name="items[{{ $i }}][received_qty]"
                                                    class="form-control qty-input received received-qty-input"
                                                    value="{{ old('items.'.$i.'.received_qty', 0) }}"
                                                    step="0.01"
                                                    min="0"
                                                    data-max="{{ $remaining }}"
                                                    required>
                                                <div class="error-msg"></div>
                                            </div>
                                        </td>

                                        <td class="text-center">
                                            <input type="number"
                                                name="items[{{ $i }}][rejected_qty]"
                                                class="form-control qty-input rejected-qty-input mx-auto"
                                                value="{{ old('items.'.$i.'.rejected_qty', 0) }}"
                                                step="0.01"
                                                min="0"
                                                data-max="{{ $remaining }}">
                                        </td>

                                        <td class="text-center">
                                            <input type="number"
                                                name="items[{{ $i }}][accepted_qty]"
                                                class="form-control qty-input accepted accepted-qty-input mx-auto"
                                                value="{{ old('items.'.$i.'.accepted_qty', 0) }}"
                                                step="0.01"
                                                min="0"
                                                readonly>
                                        </td>

                                        
                                        <td class="pe-4">
                                            <input type="text"
                                                name="items[{{ $i }}][remark]"
                                                class="form-control meta-input bg-light border-0"
                                                placeholder="Optional...">

                                            <input type="hidden" name="items[{{ $i }}][po_item_id]" value="{{ $item->id }}">
                                            <input type="hidden" name="items[{{ $i }}][inventory_id]" value="{{ $item->inventory?->id }}">
                                        </td>
                                    </tr>
                                    @php $i++; @endphp
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted p-4">
                                            No pending items found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- FOOTER --}}
            <div class="row align-items-center">
                <div class="col-md-6">
                    <label class="form-label-bold">Overall Delivery Notes</label>
                    <textarea name="remarks" class="form-control meta-input" rows="2"
                        placeholder="Enter any general notes regarding this delivery..."></textarea>
                </div>

                <div class="col-md-6 text-end">
                    <div class="footer-box me-3">
                        <div class="text-muted small fw-bold text-uppercase">Total Accepted (Stock In)</div>
                        <h3 class="total-val" id="display_total_accepted">0.00</h3>
                    </div>

                    <button type="submit" id="submitBtn"
                        class="btn btn-primary btn-save btn-lg px-5">
                        <i class="fas fa-check-circle me-2"></i>SAVE GRN
                    </button>
                </div>
            </div>

        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const rows = document.querySelectorAll('tbody tr');
            const totalAcceptedEl = document.getElementById('display_total_accepted');
            const submitBtn = document.getElementById('submitBtn');

            function recalc() {
                let totalAccepted = 0;
                let hasError = false;

                rows.forEach(row => {
                    const receivedInput = row.querySelector('.received-qty-input');
                    const rejectedInput = row.querySelector('.rejected-qty-input');
                    const acceptedInput = row.querySelector('.accepted-qty-input');
                    const errorContainer = row.querySelector('.error-msg');

                    if (!receivedInput || !rejectedInput || !acceptedInput || !errorContainer) return;

                    const max = parseFloat(receivedInput.dataset.max) || 0;
                    const received = parseFloat(receivedInput.value) || 0;
                    const rejected = parseFloat(rejectedInput.value) || 0;

                    receivedInput.style.borderColor = "#e2e8f0";
                    rejectedInput.style.borderColor = "#e2e8f0";
                    errorContainer.innerText = "";

                    if (received < 0 || received > max) {
                        hasError = true;
                        receivedInput.style.borderColor = "#dc2626";
                        errorContainer.innerText = "Received cannot exceed Remaining: " + max.toFixed(2);
                    }

                    if (rejected < 0 || rejected > received) {
                        hasError = true;
                        rejectedInput.style.borderColor = "#dc2626";
                        errorContainer.innerText = "Rejected cannot exceed Received.";
                    }

                    const accepted = Math.max(received - rejected, 0);
                    acceptedInput.value = accepted.toFixed(2);

                    totalAccepted += accepted;
                });

                totalAcceptedEl.innerText = totalAccepted.toFixed(2);

                const hasAnyRow = document.querySelectorAll('.received-qty-input').length > 0;
                submitBtn.disabled = hasError || !hasAnyRow;
                submitBtn.style.opacity = (hasError || !hasAnyRow) ? '0.5' : '1';
            }

            document.querySelectorAll('.received-qty-input, .rejected-qty-input')
                .forEach(i => i.addEventListener('input', recalc));

            recalc();
        });
    </script>
@endsection
