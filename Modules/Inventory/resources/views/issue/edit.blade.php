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

        .muted-dash {
            color: #94a3b8;
            font-size: .85rem;
        }
    </style>

    <div class="main-content">
        {{-- ✅ EDIT MODE --}}
        <form action="{{ route('issue.update', $issue->id) }}" method="POST" id="issueForm">
            @csrf
            @method('PUT')

            <input type="hidden" name="requisition_id" value="{{ $requisition->id }}">
            <input type="hidden" name="project_id" value="{{ $requisition->project_id }}">
            <input type="hidden" name="issue_id" value="{{ $issue->id }}">
            <input type="hidden" name="issue_date" value="{{ $issue->transaction_date ? \Carbon\Carbon::parse($issue->transaction_date)->format('Y-m-d') : now()->format('Y-m-d') }}">
            <input type="hidden" name="issue_slip_no" value="{{ $issue->issue_slip_no }}">

            <div class="card shadow-sm border-0 rounded-lg">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-file-invoice me-2 text-primary"></i>Issue Slip Update
                    </h5>
                    <span class="badge rounded-pill bg-light text-primary border border-primary px-3 py-2">
                        Req #{{ $requisition->requisition_slip_no }}
                    </span>
                </div>

                <div class="card-body p-4">
                    <div class="table-container shadow-sm">
                        <table class="table table-hover align-middle mb-0" id="itemsTable">
                            <thead>
                                <tr>
                                    <th class="ps-3" width="70">Split</th>
                                    <th width="270">Material</th>
                                    <th class="text-end" width="180">Stock</th>
                                    <th class="text-end" width="140">Pending</th>
                                    <th width="240">Supplier</th>
                                    <th class="text-end" width="130">Qty</th>
                                    <th width="200">Status</th>
                                </tr>
                            </thead>

                            <tbody>
                                @php $globalIndex = 0; $lastInv = null; @endphp
                                @foreach($issue->rows as $row)
                                @if($row->status != 'full')
                                @php
                                $inv = $row->inventory;
                                if(!$inv) continue;

                                $reqRow = $requisition->rows->where('id', $row->requisition_slip_row_id)->first();
                                if(!$reqRow) continue;

                                // ✅ pending shown by current status
                                if ($row->status === 'partial_out_of_stock') {
                                $pendingQty = (float)($row->order_qty ?? 0);
                                } elseif ($row->status === 'partial_machining') {
                                $pendingQty = (float)($row->pending_qty ?? 0);
                                $sfStock = (float)($row->sf_stock ?? 0);
                                $totalRequested = (float)($row->quantity ?? 0);
                                $alreadyMachining = (float)($row->machining_total ?? 0);
                                $remainingRequestQty = max($totalRequested - $alreadyMachining, 0);
                                $availableSfForMachining = max($sfStock - $alreadyMachining, 0);
                                $maxMachining = min($remainingRequestQty, $availableSfForMachining);
                                } else {
                                $orderQty = (float)($reqRow->quantity ?? 0);
                                $issued = (float)($reqRow->issued_qty ?? 0);
                                $pendingQty = max($orderQty - $issued, 0);
                                }
                                if($pendingQty <= 0) continue;

                                    $classif=strtoupper(trim((string)($inv->classification ?? 'FINISH')));
                                    $classif = $classif ?: 'FINISH';

                                    $sf = (float)($row->sf_stock ?? 0);
                                    $fn = (float)($row->fn_stock ?? 0);
                                    $mc = (float)($row->mc_stock ?? 0);
                                    $total = ($classif === 'SEMI_FINISH') ? ($sf + $mc + $fn) : (float)($row->total_stock ?? $fn);

                                    $isDup = ($lastInv == $inv->id);
                                    $lastInv = $inv->id;
                                    @endphp

                                    <tr class="item-row"
                                        data-row-id="{{ $row->requisition_slip_row_id }}"
                                        data-max-pending="{{ $pendingQty }}"
                                        data-max-machining="{{$row->max_machining ?? 0}}"
                                        data-sf="{{ $sf }}"
                                        data-prev-mc="{{ $row->pending_qty ?? 0 }}"
                                        data-classif="{{ $classif }}"
                                        data-fn="{{ $fn }}"
                                        data-total="{{ $total }}">

                                        <td class="ps-3 text-center split-cell">
                                            <button type="button" class="btn btn-sm btn-outline-primary clone-row rounded-circle">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </td>

                                        <td class="material-cell">
                                            @if($isDup)
                                            <span class="muted-dash">—</span>
                                            @else
                                            <div class="fw-bold small text-dark">{{ $inv->name }}</div>
                                            <div class="text-muted" style="font-size:0.65rem">{{ $classif }}</div>
                                            @endif
                                        </td>

                                        <td class="text-end stock-cell">
                                            @if($isDup)
                                            <span class="muted-dash">—</span>
                                            @else
                                            @if($classif === 'SEMI_FINISH')
                                            @php $ts = $sf + $mc + $fn; @endphp
                                            <div class="stock-badge {{ $ts > 0 ? 'stock-green' : 'stock-red' }}">Total: {{ number_format($ts,2) }}</div>
                                            <div class="stock-badge {{ $sf > 0 ? 'stock-green' : 'stock-red' }}">SF: {{ number_format($sf,2) }}</div>
                                            <div class="stock-badge {{ $mc > 0 ? 'stock-green' : 'stock-red' }}">MC: {{ number_format($mc,2) }}</div>
                                            <div class="stock-badge {{ $fn > 0 ? 'stock-green' : 'stock-red' }}">FN: {{ number_format($fn,2) }}</div>
                                            @else
                                            <span class="{{ $total > 0 ? 'stock-green' : 'stock-red' }} small fw-bold">{{ number_format($total,2) }}</span>
                                            @endif
                                            @endif
                                        </td>

                                        <td class="text-end pending-cell small fw-bold text-primary">
                                            {{ number_format($pendingQty, 2) }}
                                        </td>

                                        {{-- ✅ Supplier select2 --}}
                                        <!-- <td>
                                            <select name="items[{{ $globalIndex }}][supplier_id]"
                                                data-select2-selector="status"
                                                class="form-control supplierSelect" {{$row->status == 'partial_machining' ? 'disabled' : ''}}>
                                                <option value="">-- Select Supplier --</option>
                                                @foreach($suppliers as $supplier)
                                                <option value="{{ $supplier->id }}" {{ $row->supplier_id == $supplier->id ? 'selected' : '' }}>
                                                    {{ $supplier->supplier_code }}
                                                </option>
                                                @endforeach
                                            </select>
                                            <div class="error-msg d-none supplier-error"></div>
                                        </td> -->

                                        <td>
                                            <select name="items[{{ $globalIndex }}][supplier_id]"  class="form-select form-select-sm border-0 bg-light supplierSelect">
                                                <option value="">-- Select Supplier --</option>

                                                @if($row->supplier_id && $row->supplier)
                                                <option value="{{ $row->supplier_id }}" selected>
                                                    {{ $row->supplier->supplier_code }}
                                                </option>
                                                @endif
                                            </select>
                                            <div class="error-msg d-none supplier-error"></div>
                                        </td>

                                        <td>
                                            <input type="number" name="items[{{ $globalIndex }}][issue_qty]"
                                                class="form-control form-control-sm text-end issueQty" {{$row->status == 'partial_machining' ? 'disabled' : ''}}
                                                value="0" step="0.01" min="0">
                                            <div class="error-msg d-none qty-error"></div>
                                        </td>

                                        <td>
                                            <select name="items[{{ $globalIndex }}][status]"
                                                class="form-select form-select-sm statusSelect border-0 bg-light" {{$row->status == 'partial_machining' ? 'disabled' : ''}}>
                                                <option value="">Select status</option>
                                                <option value="full" {{ $row->status=='full'?'selected':'' }}>Full</option>
                                                <option value="partial_out_of_stock" {{ $row->status=='partial_out_of_stock'?'selected':'' }}>Out of Stock</option>
                                                <option value="partial_machining" {{ $row->status=='partial_machining'?'selected':'' }}>Machining</option>
                                            </select>
                                            <div class="error-msg d-none status-error"></div>
                                        </td>

                                        {{-- hidden --}}
                                        <input type="hidden" name="items[{{ $globalIndex }}][inventory_id]" value="{{ $inv->id }}">
                                        <input type="hidden" name="items[{{ $globalIndex }}][rs_row_id]" value="{{ $row->requisition_slip_row_id }}">
                                        <input type="hidden" name="items[{{ $globalIndex }}][issue_row_id]" value="{{ $row->id }}">
                                        <input type="hidden" name="items[{{ $globalIndex }}][is_clone]" value="0">
                                        <input type="hidden" name="items[{{ $globalIndex }}][clone_of_issue_row_id]" value="">
                                    </tr>

                                    @php $globalIndex++; @endphp
                                    @endif
                                    @endforeach

                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted small">
                            <i class="fas fa-info-circle me-1"></i>
                            Stock check only for <b>Full</b>. Supplier not required only for <b>Out of Stock</b>.
                        </div>
                        <div class="text-end">
                            <div class="mb-2">Total Issue: <span class="fw-bold text-primary" id="display_total_issue">0.00</span></div>
                            <button type="submit" id="submitBtn" class="btn btn-primary px-5 shadow-sm rounded-pill">
                                Update Issue Slip
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let globalIndex = {{$globalIndex ?? 0}};

            const tableBody = document.querySelector('#itemsTable tbody');

            // ✅ SELECT2 init (IMPORTANT)
            function initSelect2(context = document) {
                if (typeof $ === 'undefined' || !$.fn || !$.fn.select2) return;

                $(context).find('[data-select2-selector="status"]').each(function() {

                    // destroy if already
                    if ($(this).hasClass('select2-hidden-accessible')) {
                        $(this).select2('destroy');
                    }

                    $(this).select2({
                        width: '100%',

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
                refresh();
            });

            // ✅ Clean select2 artifacts on clone
            function resetSelect2Artifacts(clone) {
                clone.querySelectorAll('.select2-container').forEach(el => el.remove());
                clone.querySelectorAll('select').forEach(sel => {
                    sel.classList.remove('select2-hidden-accessible');
                    sel.removeAttribute('data-select2-id');
                    sel.querySelectorAll('option').forEach(opt => opt.removeAttribute('data-select2-id'));
                });
            }

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

            function applyStatusOptions(row) {
                const classif = (row.dataset.classif || '').toUpperCase();
                const statusEl = row.querySelector('.statusSelect');
                if (!statusEl) return;

                const machiningOpt = statusEl.querySelector('option[value="partial_machining"]');
                if (machiningOpt) {
                    const hideMachining = (classif === 'FINISH');
                    machiningOpt.hidden = hideMachining;
                    machiningOpt.disabled = hideMachining;

                    if (hideMachining && statusEl.value === 'partial_machining') statusEl.value = '';
                }
            }

            let totalPendingOutOfStockInitial = 0;

            document.querySelectorAll('.item-row').forEach(row => {
                const pending = parseFloat(row.dataset.maxPending || 0);
                const status = row.querySelector('.statusSelect')?.value || '';

                if (status === 'partial_out_of_stock') {
                    totalPendingOutOfStockInitial += pending;
                }
            });


            function refresh() {
                let totalIssue = 0;
                let hasError = false;
                const itemFullUsage = {};

                // ===== SINGLE OUT OF STOCK PER ITEM (EDIT MODE) =====
                const outOfStockCount = {};

                // 1️⃣ count how many times out_of_stock selected per row_id
                document.querySelectorAll('.item-row').forEach(row => {
                    const rowId = row.dataset.rowId;
                    const status = row.querySelector('.statusSelect')?.value;

                    if (status === 'partial_out_of_stock') {
                        outOfStockCount[rowId] = (outOfStockCount[rowId] || 0) + 1;
                    }
                });

                // 2️⃣ disable Out of Stock option where already used
                document.querySelectorAll('.item-row').forEach(row => {
                    const rowId = row.dataset.rowId;
                    const statusEl = row.querySelector('.statusSelect');
                    if (!statusEl) return;

                    const outOpt = statusEl.querySelector('option[value="partial_out_of_stock"]');
                    if (!outOpt) return;

                    const used = outOfStockCount[rowId] || 0;

                    if (used >= 1) {
                        // agar current row khud out_of_stock nahi hai → option disable
                        if (statusEl.value !== 'partial_out_of_stock') {
                            outOpt.disabled = true;
                        }
                    } else {
                        outOpt.disabled = false;
                    }

                    //  safety: agar kisi tarah 2nd out_of_stock aa gaya
                    if (statusEl.value === 'partial_out_of_stock' && used > 1) {
                        hasError = true;

                    }
                });


                document.querySelectorAll('.item-row').forEach(row => {
                    applyStatusOptions(row);
                    const qty = parseFloat(row.querySelector('.issueQty')?.value) || 0;
                    const status = row.querySelector('.statusSelect')?.value || '';
                    const invId = row.querySelector('input[name*="inventory_id"]')?.value;

                    totalIssue += qty;

                    if (status === 'full' && invId) {
                        itemFullUsage[invId] = (itemFullUsage[invId] || 0) + qty;
                    }
                });

                document.querySelectorAll('.item-row').forEach(row => {
                    const qtyEl = row.querySelector('.issueQty');
                    const qtyErr = row.querySelector('.qty-error');

                    const statusEl = row.querySelector('.statusSelect');
                    const statusErr = row.querySelector('.status-error');

                    const supplierEl = row.querySelector('.supplierSelect');
                    const supplierErr = row.querySelector('.supplier-error');

                    const status = statusEl?.value || '';
                    const rowId = row.dataset.rowId;
                    const classif = (row.dataset.classif || '').toUpperCase();

                    clearInvalid(qtyEl, qtyErr);
                    clearInvalid(statusEl, statusErr);
                    clearInvalid(supplierEl, supplierErr);



                    // ✅ status required
                    if (!status) {
                        setInvalid(statusEl, "Status required", statusErr);
                        hasError = true;
                    }

                    // ✅ supplier required except out_of_stock
                    const supplierRequired = (status && status !== 'partial_out_of_stock');
                    if (supplierRequired && !supplierEl.value) {
                        setInvalid(supplierEl, "Supplier required", supplierErr);
                        hasError = true;
                    }

                    let machiningTotal = 0;
                    let outStockTotal = 0;
                    let fullQty = 0;



                    document.querySelectorAll(`.item-row[data-row-id="${rowId}"]`).forEach(r => {
                        const st = r.querySelector('.statusSelect')?.value;
                        const q = parseFloat(r.querySelector('.issueQty')?.value) || 0;

                        if (st === 'partial_machining') machiningTotal += q;
                        if (st === 'partial_out_of_stock') outStockTotal += q;
                        if (st === 'full') fullQty += q;
                    });

                    // allowed quantities


                    // ----- MACHINING CHECK -----
                    if (status === 'partial_machining') {

                        // ✅ PHP se aaya hua FINAL value
                        const maxMachining = parseFloat(row.dataset.maxMachining || 0);

                        const dbMaxMachining = parseFloat(row.dataset.maxMachining || 0);

                        // current row ka qty hata do (warna double count hoga)
                        const currentRowQty =
                            status === 'partial_machining' ?
                            (parseFloat(qtyEl?.value) || 0) :
                            0;

                        const remainingMachining = Math.max(
                            dbMaxMachining - (machiningTotal - currentRowQty),
                            0
                        );


                        const qty = parseFloat(qtyEl?.value) || 0;

                        if (qty > remainingMachining + 0.001) {
                            setInvalid(
                                qtyEl,
                                `Exeed Quantity `,
                                qtyErr
                            );
                            hasError = true;
                        }
                    }

                    // ----- OUT OF STOCK CHECK -----
                    if (status === 'partial_out_of_stock') {
                        const qty = parseFloat(qtyEl?.value) || 0;
                        const sfStock = parseFloat(row.dataset.sf || 0);
                        const currentPending = parseFloat(row.dataset.maxPending || 0);


                        // pehle se machining me gaya hua
                        const alreadyMachining = parseFloat(row.dataset.prevMc || 0);
                        const allowedOutStock = Math.max(0, currentPending - machiningTotal - fullQty);


                        if (qty > allowedOutStock + 0.001) {
                            setInvalid(
                                qtyEl,
                                `Only ${allowedOutStock} allowed for out of stock`,
                                qtyErr
                            );
                            hasError = true;
                        }
                    }

                    // ✅ stock check only for FULL
                    if (status === 'full') {
                        const invId = row.querySelector('input[name*="inventory_id"]')?.value;
                        const limitFN = parseFloat(row.dataset.fn) || 0;
                        const limitTotal = parseFloat(row.dataset.total) || 0;

                        const stockToCheck = (classif === 'SEMI_FINISH') ? limitFN : limitTotal;
                        const used = itemFullUsage[invId] || 0;

                        if (used > stockToCheck + 0.001) {
                            setInvalid(qtyEl, "Exeed Quantity", qtyErr);
                            hasError = true;
                        }
                    }

                });

                let totalIssueOutOfStock = 0;

                document.querySelectorAll('.item-row').forEach(row => {
                    const status = row.querySelector('.statusSelect')?.value || '';
                    const pending = parseFloat(row.dataset.maxPending || 0);
                    const qty = parseFloat(row.querySelector('.issueQty')?.value) || 0;

                    totalIssueOutOfStock += qty;
                });


                if (totalPendingOutOfStockInitial > 0) {
                    if (Math.abs(totalIssueOutOfStock - totalPendingOutOfStockInitial) > 0.001) {
                        hasError = true;
                    }
                }

                document.getElementById('display_total_issue').innerText = totalIssue;
                document.getElementById('submitBtn').disabled = hasError;
            }

            function replaceIndexNames(clone, newIndex) {
                clone.querySelectorAll('[name]').forEach(el => {
                    const old = el.getAttribute('name');
                    if (!old) return;
                    el.setAttribute('name', old.replace(/\[\d+\]/, `[${newIndex}]`));
                });
            }

            function upsertHidden(clone, name, value) {
                let input = clone.querySelector(`input[name="${name}"]`);
                if (!input) {
                    input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = name;
                    clone.appendChild(input);
                }
                input.value = value;
            }

            // ✅ init select2 on initial page
            initSelect2(document);
            initSupplierSelect2(document);

            // ✅ clone / remove
            tableBody.addEventListener('click', function(e) {
                const btn = e.target.closest('button');
                if (!btn) return;

                if (btn.classList.contains('clone-row')) {
                    const row = btn.closest('tr');
                    const clone = row.cloneNode(true);
                    clone.classList.add('cloned-row');
                    clone.dataset.cloned = "1";

                    // parent issue_row_id
                    const parentIssueRowId = row.querySelector('input[name*="[issue_row_id]"]')?.value || "";

                    // update names to new index
                    replaceIndexNames(clone, globalIndex);

                    // ✅ reset select2 wrappers and attributes
                    resetSelect2Artifacts(clone);

                    // reset values
                    const qtyInput = clone.querySelector('.issueQty');
                    if (qtyInput) qtyInput.value = 0;

                    // keep status selected (as you wanted) OR reset (if you want reset then set '')
                    // clone.querySelector('.statusSelect').value = '';
                    const statusSelect = clone.querySelector('.statusSelect');
                    if (statusSelect) {
                        statusSelect.value = 'partial_machining';
                    }


                    const supplierSelect = clone.querySelector('.supplierSelect');
                    if (supplierSelect) supplierSelect.value = '';

                    // new row create => issue_row_id null
                    const issueRowIdInput = clone.querySelector('input[name*="[issue_row_id]"]');
                    if (issueRowIdInput) issueRowIdInput.value = "";

                    // clone flags
                    upsertHidden(clone, `items[${globalIndex}][is_clone]`, "1");
                    upsertHidden(clone, `items[${globalIndex}][clone_of_issue_row_id]`, parentIssueRowId);

                    // clone me only "-" button
                    const splitCell = clone.querySelector('.split-cell');
                    splitCell.innerHTML = `
                        <button type="button" class="btn btn-sm btn-outline-danger remove-row rounded-circle">
                            <i class="fas fa-minus"></i>
                        </button>
                    `;

                    // hide name/stock in clone
                    const materialCell = clone.querySelector('.material-cell');
                    if (materialCell) materialCell.innerHTML = '<span class="muted-dash">—</span>';

                    const stockCell = clone.querySelector('.stock-cell');
                    if (stockCell) stockCell.innerHTML = '<span class="muted-dash">—</span>';

                    row.after(clone);

                    // ✅ IMPORTANT: re-init select2 on clone
                    initSelect2(clone);
                    initSupplierSelect2(clone);


                    globalIndex++;
                    refresh();
                    return;
                }

                if (btn.classList.contains('remove-row')) {
                    const tr = btn.closest('tr');
                    if (tr && tr.dataset.cloned === "1") {
                        // destroy select2 before remove (safe)
                        if (typeof $ !== 'undefined' && $.fn && $.fn.select2) {
                            $(tr).find('[data-select2-selector="status"]').each(function() {
                                if ($(this).hasClass('select2-hidden-accessible')) {
                                    $(this).select2('destroy');
                                }
                            });
                        }
                        tr.remove();
                        refresh();
                    }
                }
            });

            tableBody.addEventListener('input', refresh);
            tableBody.addEventListener('change', refresh);
            refresh();
        });
    </script>
@endsection