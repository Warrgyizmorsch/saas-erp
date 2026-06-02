@extends('shared::layouts.app')
@section('content')

    <style>
        /* Strong Black Borders for the whole table */
        .inventory-table,
        .inventory-table th,
        .inventory-table td {
            border: 1px solid #000 !important;
        }

        .inventory-table thead th {
            background-color: #f8f9fa;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
        }

        /* Compact Styling for Bifurcation Tags */
        .stock-tag {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 2px 8px;
            border-bottom: 1px solid #eee;
            font-size: 11px;
        }

        .stock-tag:last-child {
            border-bottom: none;
        }

        .tag-label {
            color: #666;
            font-weight: 600;
        }

        .tag-value {
            font-weight: 700;
            color: #000;
        }

        .total-row {
            background-color: #eef2ff;
            border-top: 1px solid #000 !important;
        }
    </style>

    <div class="page-header d-flex justify-content-between align-items-center mb-3">
        <div class="page-header-left">
            <h5 class="mb-1">Inventory Required vs Available</h5>
            <small class="text-muted">Live Stock Management</small>
        </div>
        <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
            <button id="filterBtn" class="btn btn-icon btn-light-brand" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                <i class="feather-filter"></i>
            </button>
            <button type="button"
                class="btn btn-primary"
                id="createSelectedPR">
                <i class="fa fa-plus me-2"></i>
                Create Selected PR
            </button>
            <a href="{{ route('require-vs-available.export') }}"
                class="btn btn-success">
                <i class="fa fa-download me-2"></i>
                <span>Export Excel</span>
            </a>

        </div>
    </div>

    <div class="main-content">
        {{-- FILTER --}}
        <div class="collapse mb-3 {{ request()->query() ? 'show' : '' }}" id="filterCollapse">
            <div class="card border shadow-sm">
                <div class="card-body">
                    <form method="GET" action="{{ route('required-vs-available.index') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-lg-4">
                                <label class="form-label">Inventory Name & Details</label>
                                <select name="name" id="inventory-filter" class="form-control"></select>
                            </div>

                            <div class="col-lg-2 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">Search</button>
                                <a href="{{ route('required-vs-available.index') }}" class="btn btn-light w-100">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card stretch stretch-full border shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    @php
                        $sortIcon = function ($column) use ($sortBy, $sortDirection) {
                            if (($sortBy ?? '') !== $column) {
                                return 'feather-chevrons-up';
                            }
                            return ($sortDirection ?? 'desc') === 'asc' ? 'feather-arrow-up' : 'feather-arrow-down';
                        };

                        $sortUrl = function ($column) use ($sortBy, $sortDirection) {
                            $direction = (($sortBy ?? '') === $column && ($sortDirection ?? 'desc') === 'asc') ? 'desc' : 'asc';
                            return request()->fullUrlWithQuery([
                                'sort_by' => $column,
                                'sort_direction' => $direction,
                            ]);
                        };
                    @endphp
                    <table class="table table-bordered inventory-table mb-0 align-middle table-sm" style="font-size: 13px;">
                        <thead>
                            <tr>
                                <th style="width:40px;" class="text-center">
                                    <input type="checkbox" id="selectAll">
                                </th>

                                <th class="text-center" style="width: 40px;">#</th>
                                <th style="width: 30%;">Inventory Name & Details</th>
                                <th class="text-center" style="width: 130px;">
                                    <a href="{{ $sortUrl('required') }}" class="text-dark d-inline-flex align-items-center gap-1">
                                        Required Qty <i class="{{ $sortIcon('required') }}"></i>
                                    </a>
                                </th>
                                <th class="text-center" style="width: 280px;">
                                    <a href="{{ $sortUrl('available') }}" class="text-dark d-inline-flex align-items-center gap-1">
                                        Stock Bifurcation <i class="{{ $sortIcon('available') }}"></i>
                                    </a>
                                </th>
                                <th class="text-center" style="width: 160px;">
                                    <a href="{{ $sortUrl('short_extra') }}" class="text-dark d-inline-flex align-items-center gap-1">
                                        Short / Extra <i class="{{ $sortIcon('short_extra') }}"></i>
                                    </a>
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($data as $index => $row)
                            @php
                            $required = (float) ($row['required'] ?? 0);
                            $available = (float) ($row['available'] ?? 0);
                            $shortQty = (float) ($row['short_qty'] ?? 0);
                            $extraQty = (float) ($row['extra_qty'] ?? 0);
                            @endphp

                            <tr>
                                <td class="text-center">
                                    @if($shortQty > 0)
                                    <input type="checkbox"
                                        class="pr-item-checkbox"
                                        data-item-id="{{ $row['inventory_id'] }}"
                                        data-item-name="{{ $row['inventory_name'] }}"
                                        data-item-model="{{ $row['inventory_model'] }}"
                                        data-qty="{{ $shortQty }}">
                                    @endif
                                </td>
                                <td class="text-center fw-bold">{{ $index + 1 }}</td>

                                <td class="ps-3 py-1">
                                    <div class="fw-bold text-dark">{{ $row['inventory_name'] }}</div>
                                    @if($row['inventory_model'])
                                    <div class="text-muted" style="font-size: 10px;">
                                        <i class="feather-tag me-1"></i>{{ $row['inventory_model'] }}
                                    </div>
                                    @endif
                                </td>

                                <td class="text-center fw-bold text-secondary fs-13">
                                    {{ number_format($required, 2) }}
                                </td>

                                <td class="p-0">
                                    {{-- Clean Vertical List Style --}}
                                    <div class="d-flex flex-column">
                                        @if ($row['classification'] == 'SEMI_FINISH')
                                        <div class="stock-tag">
                                            <span class="tag-label">MACHINING</span>
                                            <span class="tag-value">{{ number_format($row['machining'] ?? 0, 2) }}</span>
                                        </div>
                                        <div class="stock-tag">
                                            <span class="tag-label">FINISH</span>
                                            <span class="tag-value">{{ number_format($row['finish'] ?? 0, 2) }}</span>
                                        </div>
                                        <div class="stock-tag">
                                            <span class="tag-label">SEMI-FINISH</span>
                                            <span class="tag-value">{{ number_format($row['machine_available'] ?? 0, 2) }}</span>
                                        </div>
                                        @endif
                                        <div class="stock-tag total-row">
                                            <span class="fw-bold text-primary" style="font-size: 10px;">AVAILABLE TOTAL</span>
                                            <span class="fw-bold text-primary fs-13">{{ number_format($available, 2) }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td class="text-center fw-bold">
                                    @if($shortQty > 0)
                                    <div class="py-1 px-2 rounded bg-soft-danger mx-2">
                                        <span class="text-danger">Short: {{ number_format($shortQty, 2) }}</span>
                                    </div>

                                    @elseif($extraQty > 0)
                                        <div class="py-1 px-2 rounded bg-soft-secondary mx-2">
                                        <span class="text-dark">Extra: {{ number_format($extraQty, 2) }}</span>
                                    </div>
                                    @else
                                    <span class="text-success small"><i class="feather-check-circle me-1"></i>OK</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No inventory data found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if(!empty($data))
                        <tfoot>
                            <tr class="total-row fw-bold">
                                <td colspan="3" class="text-end">TOTAL</td>
                                <td class="text-center">{{ number_format($totals['required'] ?? 0, 2) }}</td>
                                <td class="text-center">
                                    <div>Available: {{ number_format($totals['available'] ?? 0, 2) }}</div>
                                </td>
                                <td class="text-center">
                                    <div class="text-danger">Short: {{ number_format($totals['short_qty'] ?? 0, 2) }}</div>
                                    <div class="text-dark">Extra: {{ number_format($totals['extra_qty'] ?? 0, 2) }}</div>
                                </td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
        </div>
    </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('#inventory-filter').select2({
                placeholder: 'Search Inventory',
                allowClear: false,
                width: '100%',
                ajax: {
                    url: "{{ route('inventory.search') }}",
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

            // ✅ SELECTED VALUE SET KARNA (MOST IMPORTANT)
            let selectedinventoryname = "{{ request('name') }}";
            let selectedinventoryText = "{{ $inventoryName ?? '' }}";

            if (selectedinventoryname && selectedinventoryText) {
                let option = new Option(selectedinventoryText, selectedinventoryname, true, true);
                $('#inventory-filter').append(option).trigger('change');
            }

            // Select All
            document.getElementById('selectAll')?.addEventListener('change', function() {

                document.querySelectorAll('.pr-item-checkbox').forEach(cb => {
                    cb.checked = this.checked;
                });

            });

            // Create PR
            // Create PR
            document.getElementById('createSelectedPR')?.addEventListener('click', function() {

                let selected = [];

                document.querySelectorAll('.pr-item-checkbox:checked').forEach(cb => {

                    selected.push({
                        item_id: cb.dataset.itemId,
                        qty: cb.dataset.qty
                    });

                });

                if (selected.length === 0) {
                    alert('Please select at least one item');
                    return;
                }

                // Hidden Form
                let form = document.createElement('form');

                form.method = 'POST';

                form.action = "{{ route('purchase_request.create') }}";

                // CSRF
                let csrf = document.createElement('input');

                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = "{{ csrf_token() }}";

                form.appendChild(csrf);

                // Selected Items
                let input = document.createElement('input');

                input.type = 'hidden';
                input.name = 'selected_items';

                input.value = JSON.stringify(selected);

                form.appendChild(input);

                document.body.appendChild(form);

                form.submit();

            });

        });
    </script>
@endsection