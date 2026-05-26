@extends('shared::layouts.app')
@section('content')
    <style>
        .main-content {
            padding: 20px;
        }

        .form-label-bold {
            font-weight: 600;
            font-size: 0.85rem;
            color: #4a5568;
            margin-bottom: 4px;
        }

        .table thead th {
            background-color: #f8f9fa;
            text-transform: uppercase;
            font-size: 0.72rem;
            color: #718096;
            border-bottom: 2px solid #edf2f7;
        }

        .is-invalid-custom {
            border: 2px solid #e53e3e !important;
            background-color: #fff5f5 !important;
        }

        .error-msg {
            color: #e53e3e;
            font-size: 0.7rem;
            font-weight: bold;
            margin-top: 2px;
        }

        .table-container {
            border: 1px solid #edf2f7;
            border-radius: 8px;
            background: #fff;
            overflow: hidden;
        }

        .cloned-row {
            background-color: #fdfdfd;
            border-left: 3px solid #4299e1;
        }

        .stock-badge {
            font-size: 0.65rem;
            padding: 2px 5px;
            border-radius: 4px;
            display: block;
            margin-bottom: 2px;
            text-align: right;
        }

        .stock-red {
            color: #e53e3e;
            font-weight: bold;
        }

        .stock-green {
            color: #38a169;
            font-weight: bold;
        }

        /* ✅ select2 clear (x) hide */
        .select2-selection__clear {
            display: none !important;
        }

        .po-meta-strip {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 16px;
            margin-bottom: 22px;
            flex-wrap: wrap;
            width: fit-content;
        }

        .po-meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
        }

        .po-meta-item .label {
            color: #64748b;
            font-weight: 500;
            white-space: nowrap;
        }

        .po-meta-item .value {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 4px 12px;
            font-weight: 700;
            font-size: 13px;
            color: #1e293b;
            white-space: nowrap;
        }

        .po-meta-item .value.mono {
            color: #2563eb;
            letter-spacing: .5px;
        }

        .po-meta-divider {
            width: 1px;
            height: 28px;
            background: #e2e8f0;
            flex-shrink: 0;
        }
    </style>

    <div class="main-content">
        <form action="{{ route('issue.store') }}" method="POST" id="issueForm">
            @csrf
            <div class="card shadow-sm border-0 rounded-lg">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-file-invoice me-2 text-primary"></i>Issue Slip Generation</h5>
                    <span class="badge rounded-pill bg-light text-primary border border-primary px-3 py-2">Req #{{ $requisition->requisition_slip_no }}</span>
                </div>

                <div class="card-body p-4">
                    <input type="hidden" name="requisition_id" value="{{ $requisition->id }}">
                    <input type="hidden" name="project_id" value="{{ $requisition->project_id }}">

                    <!-- META STRIP -->
                    <div class="po-meta-strip">
                        <div class="po-meta-item">
                            <span class="label"><i class="fa fa-calendar" style="margin-right:4px;"></i> Date</span>
                            <span class="value">{{ $today }}</span>
                            <input type="hidden" name="issue_date" value="{{ $today }}">
                        </div>
                        <div class="po-meta-divider"></div>
                        <div class="po-meta-item">
                            <span class="label"><i class="fa fa-hashtag" style="margin-right:4px;"></i> Issue No.</span>
                            <span class="value mono">{{ $issueSlipNo }}</span>
                            <input type="hidden" name="issue_slip_no" value="{{ $issueSlipNo }}">
                        </div>
                    </div>

                    <div class="table-container shadow-sm">
                        <table class="table table-hover align-middle mb-0" id="itemsTable">
                            <thead>
                                <tr>
                                    <th class="ps-3" width="50">Split</th>
                                    <th width="200">Material Details</th>
                                    <th class="text-end" width="130">Available Stock</th>
                                    <th class="text-end" width="90">Pending</th>
                                    <th width="180">Supplier / Vendor</th>
                                    <th class="text-end" width="120">Issue Qty</th>
                                    <th width="150">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $globalIndex = 0; @endphp
                                @foreach($requisition->rows as $row)
                                @php
                                $inventory = $row->inventory;
                                if(!$inventory) continue;

                                $pendingQty = (float)($row->quantity ?? 0) - (float)($row->issue_qty ?? 0);
                                $classif = strtoupper(trim((string)($inventory->classification ?? '')));

                                // ✅ IMPORTANT: FINISH validation ke liye total_stock aapka "Available Stock" hai (same as display)
                                // SEMI_FINISH me FULL ke liye FN bucket (fn_stock) use hota hai (aapka purana logic)
                                @endphp
                                <tr class="item-row"
                                    data-row-id="{{ $row->id }}"
                                    data-max-pending="{{ $pendingQty }}"
                                    data-classif="{{ $classif }}"
                                    data-sf="{{ $row->sf_stock }}"
                                    data-fn="{{ $row->fn_stock }}"
                                    data-total="{{ $row->total_stock }}">

                                    <td class="ps-3 text-center split-cell">
                                        <button type="button" class="btn btn-sm btn-outline-primary clone-row rounded-circle">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </td>

                                    <td class="material-cell">
                                        <div class="fw-bold small text-dark">{{ $inventory->name }}</div>
                                        <div class="text-muted" style="font-size: 0.65rem;">{{ $classif ?: 'FINISH' }}</div>
                                    </td>

                                    <td class="text-end stock-cell">
                                        @if($classif === 'SEMI_FINISH')
                                        @php
                                        $totalStock = ($row->sf_stock ?? 0) + ($row->mc_stock ?? 0) + ($row->fn_stock ?? 0);
                                        @endphp

                                        <div class="stock-badge {{ $totalStock > 0 ? 'stock-green' : 'stock-red' }}">
                                            Total: {{ number_format($totalStock, 2) }}
                                        </div>
                                        <div class="stock-badge {{ $row->sf_stock > 0 ? 'stock-green' : 'stock-red' }}">Semi Finish: {{ number_format($row->sf_stock, 2) }}</div>
                                        <div class="stock-badge {{ $row->mc_stock > 0 ? 'stock-green' : 'stock-red' }}">Machining: {{ number_format($row->mc_stock, 2) }}</div>
                                        <div class="stock-badge {{ $row->fn_stock > 0 ? 'stock-green' : 'stock-red' }}">Finish: {{ number_format($row->fn_stock, 2) }}</div>
                                        @else
                                        <span class="{{ $row->total_stock > 0 ? 'stock-green' : 'stock-red' }} small fw-bold">
                                            {{ number_format($row->total_stock, 2) }}
                                        </span>
                                        @endif
                                    </td>

                                    <td class="text-end pending-cell small fw-bold text-primary">{{ number_format($pendingQty, 2) }}</td>

                                    <!-- <td>
                                        <select name="items[{{ $globalIndex }}][supplier_id]"
                                            class="form-select form-select-sm border-0 bg-light supplierSelect"
                                            data-select2-selector="supplier">
                                            <option value="">-- No Supplier --</option>
                                            @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->supplier_code }}</option>
                                            @endforeach
                                        </select>
                                        <div class="error-msg d-none supplier-error"></div>
                                    </td> -->

                                    <td>
                                        <select name="items[{{ $globalIndex }}][supplier_id]" class="form-select form-select-sm border-0 bg-light supplierSelect"></select>
                                        <div class="error-msg d-none supplier-error"></div>
                                    </td>

                                    <td>
                                        <input type="number" name="items[{{ $globalIndex }}][issue_qty]" class="form-control form-control-sm text-end issueQty" value="" step="0.01">
                                        <div class="error-msg d-none qty-error"></div>
                                    </td>

                                    <td>
                                        <select name="items[{{ $globalIndex }}][status]" class="form-select form-select-sm statusSelect border-0 bg-light  @error('items.'.$globalIndex.'.status') is-invalid-custom @enderror">
                                            <option value="">Select status</option>
                                            <option value="full">Full</option>
                                            <option value="partial_out_of_stock">Out of Stock</option>
                                            @if($classif === 'SEMI_FINISH')
                                            <option value="partial_machining">Machining</option>
                                            @endif
                                        </select>
                                        @error('items.'.$globalIndex.'.status')
                                        <div class="error-msg">{{ $message }}</div>
                                        @enderror
                                    </td>


                                    <input type="hidden" name="items[{{ $globalIndex }}][inventory_id]" value="{{ $inventory->id }}">
                                    <input type="hidden" name="items[{{ $globalIndex }}][row_id]" value="{{ $row->id }}">

                                </tr>
                                @php $globalIndex++; @endphp
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted small">
                            <i class="fas fa-info-circle me-1"></i>
                            Stock is validated for <b>Full</b> status only.
                            Supplier is <b>required</b> for <b>Full</b> & <b>Machining</b>, and <b>NOT required</b> for <b>Out of Stock</b>.
                        </div>
                        <div class="text-end">
                            <div class="mb-2">Total Issue: <span class="fw-bold text-primary" id="display_total_issue">0.00</span></div>
                            <button type="submit" id="submitBtn" class="btn btn-primary px-5 shadow-sm rounded-pill">Process Issue Slip</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let globalIndex = {{$globalIndex}};
            const tableBody = document.querySelector('#itemsTable tbody');

            // ✅ Select2 init (NO X button)
            function initSelect2(context = document) {
                if (typeof $ === 'undefined' || !$.fn || !$.fn.select2) return;

                $(context).find('[data-select2-selector="supplier"]').each(function() {
                    if ($(this).hasClass('select2-hidden-accessible')) {
                        $(this).select2('destroy');
                    }
                    $(this).select2({
                        width: '100%',
                        allowClear: true
                    });
                });
            }

            function initSupplierSelect2(context = document) {

                if (typeof $ === 'undefined' || !$.fn.select2) return;

                $(context).find('.supplierSelect').each(function() {

                    let $this = $(this);

                    // destroy if already initialized
                    if ($this.hasClass('select2-hidden-accessible')) {
                        $this.select2('destroy');
                    }

                    $this.select2({
                        placeholder: 'Search Supplier Code like 100',
                        width: '100%',
                        minimumInputLength: 0,
                        ajax: {
                            url: "{{ route('suppliers.searchCode') }}",
                            dataType: 'json',
                            delay: 250,
                            data: function(params) {
                                return {
                                    q: params.term
                                };
                            },
                            processResults: function(data) {
                                return {
                                    results: data.results
                                };
                            },
                            cache: true
                        }
                    });

                });
            }

            initSupplierSelect2(document);

            $(document).on('change', '.supplierSelect', function() {
                const supplierEl = this;
                const row = supplierEl.closest('.item-row');
                const supplierErr = row.querySelector('.supplier-error');

                supplierEl.classList.remove('is-invalid-custom');
                if (supplierErr) {
                    supplierErr.textContent = '';
                    supplierErr.classList.add('d-none');
                }
            });

            function resetSelect2Artifacts(clone) {
                clone.querySelectorAll('.select2-container').forEach(el => el.remove());
                clone.querySelectorAll('select').forEach(sel => {
                    sel.classList.remove('select2-hidden-accessible');
                    sel.removeAttribute('data-select2-id');
                    sel.querySelectorAll('option').forEach(opt => opt.removeAttribute('data-select2-id'));
                });
            }

            initSelect2(document);

            function setInvalid(el, msg, errEl) {
                el.classList.add('is-invalid-custom');
                if (errEl) {
                    errEl.textContent = msg;
                    errEl.classList.remove('d-none');
                }
            }

            function clearInvalid(el, errEl) {
                el.classList.remove('is-invalid-custom');
                if (errEl) {
                    errEl.textContent = "";
                    errEl.classList.add('d-none');
                }
            }

            function refresh() {
                let totalIssue = 0;
                let hasError = false;
                const itemFullUsage = {};
                const outOfStockCount = {};


                // Track how much is being issued as "Full" per Inventory
                document.querySelectorAll('.item-row').forEach(row => {
                    const qty = parseFloat(row.querySelector('.issueQty').value) || 0;
                    const status = row.querySelector('.statusSelect').value;
                    const invId = row.querySelector('input[name*="inventory_id"]').value;
                    const rowId = row.dataset.rowId;

                    totalIssue += qty;
                    if (status === 'full') {
                        itemFullUsage[invId] = (itemFullUsage[invId] || 0) + qty;
                    }
                    if (status === 'partial_out_of_stock') {
                        outOfStockCount[rowId] = (outOfStockCount[rowId] || 0) + 1;
                    }
                });

                document.querySelectorAll('.item-row').forEach(row => {
                    const input = row.querySelector('.issueQty');
                    const qtyErr = row.querySelector('.qty-error') || row.querySelector('.error-msg');

                    const statusEl = row.querySelector('.statusSelect');
                    const statusErr = row.querySelector('.status-error');

                    const supplierEl = row.querySelector('.supplierSelect');
                    const supplierErr = row.querySelector('.supplier-error');

                    const status = statusEl.value;
                    const invId = row.querySelector('input[name*="inventory_id"]').value;
                    const rowId = row.dataset.rowId;
                    const classif = row.dataset.classif;

                    // Stocks
                    const limitFN = parseFloat(row.dataset.fn) || 0;
                    const limitTotal = parseFloat(row.dataset.total) || 0;
                    const maxPend = parseFloat(row.dataset.maxPending) || 0;

                    // reset errors
                    clearInvalid(input, qtyErr);
                    clearInvalid(statusEl, statusErr);
                    clearInvalid(supplierEl, supplierErr);



                    //
                    const outStockUsed = outOfStockCount[rowId] || 0;

                    const outStockOption = statusEl.querySelector('option[value="partial_out_of_stock"]');

                    // Agar already kisi clone me Out of Stock select hai
                    if (outStockUsed > 0) {

                        // current row khud Out of Stock nahi hai → option disable
                        if (status !== 'partial_out_of_stock') {
                            outStockOption.disabled = true;
                        }

                        // ❌ agar galti se 2nd select ho gaya
                        if (status === 'partial_out_of_stock' && outStockUsed > 1) {
                            setInvalid(statusEl, "Only one Out of Stock allowed", statusErr);
                            hasError = true;
                        }

                    } else {
                        // koi bhi Out of Stock nahi hai → option enable
                        outStockOption.disabled = false;
                    }

                    // ✅ supplier mandatory for FULL & MACHINING, NOT required for OUT OF STOCK
                    const supplierRequired = (status === 'full' || status === 'partial_machining');
                    if (supplierRequired && !supplierEl.value) {
                        setInvalid(supplierEl, "Supplier required", supplierErr);
                        hasError = true;
                    }

                    // Pending Group Check (same as your old logic)
                    let currentGroupQty = 0;
                    document.querySelectorAll(`.item-row[data-row-id="${rowId}"]`).forEach(r => {
                        currentGroupQty += parseFloat(r.querySelector('.issueQty').value) || 0;
                    });

                    let msg = "";

                    // 1. All statuses must follow Pending Limit
                    if (currentGroupQty > maxPend + 0.001) {
                        msg = "Exceeds Pending";
                    }
                    // 2. Only "Full" status validates against Stock
                    else if (status === 'full') {
                        // ✅ FIX: FINISH me stockToCheck = MIN(pending, available)
                        // - SEMI_FINISH: FULL ke liye FN bucket use (same old)
                        // - FINISH: total_stock aapka available hai, aur issue cannot exceed pending bhi
                        const stockToCheck = (classif === 'SEMI_FINISH') ? limitFN : limitTotal;
                        const used = itemFullUsage[invId] || 0;

                        // ✅ full issue max = min(stock, pending group maxPend)
                        const maxAllowedByStockAndPending = Math.max(0, Math.min(stockToCheck, maxPend));

                        if (used > maxAllowedByStockAndPending + 0.001) {
                            msg = "Low Stock";
                        }
                    } else if (status === 'partial_machining') {

                        // Sirf SEMI_FINISH ke liye rule
                        if (classif === 'SEMI_FINISH') {

                            const sfLimit = parseFloat(row.dataset.sf) || 0;

                            // same row_id ke sab machining qty ka sum
                            let machiningUsed = 0;
                            document.querySelectorAll(`.item-row[data-row-id="${rowId}"]`).forEach(r => {
                                const st = r.querySelector('.statusSelect').value;
                                if (st === 'partial_machining') {
                                    machiningUsed += parseFloat(r.querySelector('.issueQty').value) || 0;
                                }
                            });

                            if (machiningUsed > sfLimit + 0.001) {
                                msg = "Exceeds Semi-Finish Stock";
                            }
                        }
                    }

                    if (msg) {
                        setInvalid(input, msg, qtyErr);
                        hasError = true;
                    }
                });

                document.getElementById('display_total_issue').innerText = totalIssue.toFixed(2);
                document.getElementById('submitBtn').disabled = hasError;

                //btn disable
                let allPendingMatched = true;
                const rowGroups = {};

                // 1️⃣ Group quantities by row_id
                document.querySelectorAll('.item-row').forEach(row => {
                    const rowId = row.dataset.rowId;
                    const qty = parseFloat(row.querySelector('.issueQty').value) || 0;
                    const maxPend = parseFloat(row.dataset.maxPending) || 0;

                    if (!rowGroups[rowId]) {
                        rowGroups[rowId] = {
                            sum: 0,
                            max: maxPend
                        };
                    }
                    rowGroups[rowId].sum += qty;
                });

                // 2️⃣ Check if sum === pending for ALL items
                Object.values(rowGroups).forEach(group => {
                    if (Math.abs(group.sum - group.max) > 0.001) {
                        allPendingMatched = false;
                    }
                });

                // 3️⃣ FINAL BUTTON CONTROL
                document.getElementById('submitBtn').disabled = hasError || !allPendingMatched;
            }

            tableBody.addEventListener('click', function(e) {
                const btn = e.target.closest('button');
                if (!btn) return;

                if (btn.classList.contains('clone-row')) {
                    const row = btn.closest('tr');
                    const clone = row.cloneNode(true);
                    clone.classList.add('cloned-row');

                    resetSelect2Artifacts(clone);

                    clone.querySelectorAll('[name]').forEach(el => {
                        el.setAttribute('name', el.getAttribute('name').replace(/\[\d+\]/, `[${globalIndex}]`));
                    });

                    clone.querySelector('.issueQty').value = 0;
                    clone.querySelector('.material-cell').innerHTML = '<span class="text-muted" style="font-size:0.65rem">↳ Split Line</span>';
                    clone.querySelector('.stock-cell').innerHTML = '';
                    clone.querySelector('.pending-cell').innerText = '';
                    clone.querySelector('.split-cell').innerHTML = `<button type="button" class="btn btn-sm btn-outline-danger remove-row rounded-circle"><i class="fas fa-times"></i></button>`;

                    // reset supplier in clone
                    const supplier = clone.querySelector('.supplierSelect');
                    if (supplier) supplier.value = '';

                    row.after(clone);

                    initSelect2(clone);
                    initSupplierSelect2(clone);

                    globalIndex++;
                    refresh();
                } else if (btn.classList.contains('remove-row')) {

                    if (typeof $ !== 'undefined' && $.fn && $.fn.select2) {
                        $(btn.closest('tr')).find('[data-select2-selector="supplier"]').each(function() {
                            if ($(this).hasClass('select2-hidden-accessible')) $(this).select2('destroy');
                        });
                    }

                    btn.closest('tr').remove();
                    refresh();
                }
            });

            tableBody.addEventListener('input', refresh);
            tableBody.addEventListener('change', refresh);
            refresh();

            document.getElementById('issueForm').addEventListener('submit', function(e) {

                let hasStatusError = false;

                document.querySelectorAll('.item-row').forEach(row => {

                    const statusEl = row.querySelector('.statusSelect');

                    // status ke niche error-msg dhundo ya banao
                    let err = row.querySelector('.status-js-error');
                    if (!err) {
                        err = document.createElement('div');
                        err.className = 'error-msg status-js-error';
                        statusEl.after(err);
                    }

                    // reset
                    statusEl.classList.remove('is-invalid-custom');
                    err.textContent = '';

                    //  status empty
                    if (!statusEl.value) {
                        statusEl.classList.add('is-invalid-custom');
                        err.textContent = 'Status is required';
                        hasStatusError = true;
                    }
                });

                //  agar koi bhi status missing hai → submit stop
                if (hasStatusError) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>
@endsection