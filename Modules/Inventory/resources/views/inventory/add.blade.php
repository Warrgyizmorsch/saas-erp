@extends('shared::layouts.app')
@section('content')
    {{-- PAGE HEADER --}}
    <div class="page-header d-flex justify-content-between align-items-center ">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-5 mb-0">Add Inventory</h5>
            </div>
            <ul class="breadcrumb mb-0 ms-3">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active">Add Inventory</li>
            </ul>
        </div>
    </div>
    <div class="main-content container-lg">
        <div class="row">
            <div class="col-xl-12">
                <div class="card invoice-container">

                    {{-- CARD HEADER --}}
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">Add Inventory</h5>
                            <small class="text-muted">
                                Select supplier and add all items being added into stock.
                            </small>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <form action="{{ route('supplier_inventory.store') }}" method="POST">
                            @csrf

                            {{-- SUPPLIER SECTION --}}
                            <hr class="border-dashed mb-0">
                            <div class="px-4 py-3 row g-3">
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label for="sup_id" class="form-label fw-semibold">
                                            Supplier Name <span class="text-danger">*</span>
                                        </label>
                                        <select name="sup_id"
                                            id="sup_id"
                                            class="form-control select2-basic @error('sup_id') is-invalid @enderror"
                                            required>
                                            <option value="">--- Select Supplier ---</option>
                                            @foreach ($suppliers as $sup)
                                            <option value="{{ $sup->id }}"
                                                {{ old('sup_id') == $sup->id ? 'selected' : '' }}>
                                                {{ $sup->name }} / {{ $sup->email }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('sup_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- ITEMS SECTION --}}
                            <hr class="border-dashed mt-0">
                            <div class="px-4 pb-4 clearfix">

                                {{-- Section Header --}}
                                <div class="mb-3 d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="fw-bold mb-1">Inventory Items</h6>
                                        <span class="fs-12 text-muted">
                                            Add item name and quantity to be added in stock.
                                        </span>
                                    </div>
                                    <div class="avatar-text avatar-sm"
                                        data-bs-toggle="tooltip"
                                        data-bs-trigger="hover"
                                        title="Add all items received from this supplier">
                                        <i class="feather feather-info"></i>
                                    </div>
                                </div>

                                {{-- ITEMS TABLE --}}
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle mb-0" id="tab_logic">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center" style="width: 60px;">#</th>
                                                <th style="min-width: 260px;">Goods Name</th>
                                                <th class="text-center" style="width: 140px;">Quantity</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {{-- FIRST ROW (TEMPLATE BASE) --}}
                                            <tr class="item-row">
                                                <td class="text-center align-middle row-index">1</td>
                                                <td>
                                                    <select name="item_id[]"
                                                        class="form-control select2-basic item-select"
                                                        required>
                                                        <option value="">-- Select Item --</option>
                                                        @foreach($items as $item)
                                                        <option value="{{ $item->id }}">
                                                            {{ $item->name }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number"
                                                        name="item_qty[]"
                                                        class="form-control qty-input"
                                                        placeholder="0"
                                                        step="1"
                                                        min="1"
                                                        required>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                {{-- TABLE ACTIONS --}}
                                <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                                    <div class="text-muted fs-12">
                                        Tip: Click <strong>Add Items</strong> to insert new rows.
                                        <strong>Delete</strong> will remove the last row.
                                    </div>
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="button"
                                            id="delete_row"
                                            class="btn btn-sm bg-soft-danger text-danger">
                                            <i class="feather feather-minus me-1"></i> Delete
                                        </button>
                                        <button type="button"
                                            id="add_row"
                                            class="btn btn-sm btn-primary">
                                            <i class="feather feather-plus me-1"></i> Add Items
                                        </button>
                                    </div>
                                </div>

                                {{-- SUBMIT --}}
                                <div class="mt-4 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-success">
                                        Save Inventory
                                    </button>
                                </div>

                            </div>

                        </form>
                    </div>

                </div>
            </div>
        </div>

        {{-- SUPPLIER INVENTORY LIST --}}
        <div class="card stretch stretch-full">
            <div class="card-body p-0">

                <div class="d-flex justify-content-between align-items-center px-4 py-3">
                    <h5 class="mb-0">Supplier Inventory Stock</h5>
                    <small class="text-muted">All received stock from suppliers</small>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped  mb-0">
                        <thead>
                            <tr>
                                <th style="width: 60px">#</th>
                                <th>Supplier</th>
                                <th>Item Name</th>
                                <th class="text-center" style="width: 160px">Quantity Added</th>
                                <th class="text-center" style="width: 200px">Date</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($supplierInventory as $index => $inv)
                            <tr>
                                <td>{{ $index + 1 }}</td>

                                <td>
                                    {{ $inv->supplier->name ?? 'N/A' }} <br>
                                    <small class="text-muted">{{ $inv->supplier->email ?? '' }}</small>
                                </td>

                                <td>{{ $inv->inventory->name ?? 'N/A' }}</td>

                                <td class="text-center fw-bold">
                                    {{ $inv->quantity }}
                                </td>

                                <td class="text-center">
                                    {{ $inv->created_at->format('d M, Y h:i A') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">
                                    No stock entries found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>




    {{-- SCRIPTS --}}
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addRowBtn = document.getElementById('add_row');
            const deleteRowBtn = document.getElementById('delete_row');
            const tableBody = document.querySelector('#tab_logic tbody');

            // Save clean template BEFORE any plugin changes it
            const templateRowHtml = tableBody.querySelector('.item-row').outerHTML;

            function initSelect2(container) {
                // Agar Select2 use kar rahe ho to yeh run hoga
                if (window.jQuery && jQuery.fn.select2) {
                    jQuery(container).find('.select2-basic').select2({
                        width: '100%'
                    });
                }
            }

            function reindexRows() {
                const rows = tableBody.querySelectorAll('.item-row');
                rows.forEach((row, idx) => {
                    const indexCell = row.querySelector('.row-index');
                    if (indexCell) {
                        indexCell.textContent = idx + 1;
                    }
                });
            }

            // Init first row select
            initSelect2(tableBody);

            // ADD ROW
            addRowBtn.addEventListener('click', function() {
                // Insert a fresh copy of template
                tableBody.insertAdjacentHTML('beforeend', templateRowHtml);

                const newRow = tableBody.querySelector('.item-row:last-of-type');

                // Reset values in new row
                const selectEl = newRow.querySelector('.item-select');
                const qtyEl = newRow.querySelector('.qty-input');

                if (selectEl) {
                    selectEl.value = '';
                }
                if (qtyEl) {
                    qtyEl.value = '';
                }

                // Re-index serial no.
                reindexRows();

                // Init Select2 for the new row (so dropdown opens properly)
                initSelect2(newRow);
            });

            // DELETE ROW (min 1)
            deleteRowBtn.addEventListener('click', function() {
                const rows = tableBody.querySelectorAll('.item-row');
                if (rows.length > 1) {
                    rows[rows.length - 1].remove();
                    reindexRows();
                }
            });
        });
    </script>
    @endpush
@endsection