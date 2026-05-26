@extends('shared::layouts.app')
@section('content')
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Opening Stock</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                <li class="breadcrumb-item active">Opening Stock</li>
            </ul>
        </div>

        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex d-md-none">
                    <a href="javascript:void(0)" class="page-header-right-close-toggle">
                        <i class="feather-arrow-left me-2"></i>
                        <span>Back</span>
                    </a>
                </div>

                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    {{-- Filter / Collapse trigger --}}
                    <button id="filterBtn" class="btn btn-icon btn-light-brand" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                        <i class="feather-filter"></i>
                    </button>

                </div>
            </div>

            <div class="d-md-none d-flex align-items-center">
                <a href="javascript:void(0)" class="page-header-right-open-toggle">
                    <i class="feather-align-right fs-20"></i>
                </a>
            </div>
        </div>
    </div>
    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif


    <div class="collapse mb-3 show" id="filterCollapse">
        <div class="card border shadow-sm">
            <div class="card-body">
                <form method="GET" action="{{ route('inventory.opening-stock.form') }}" class="mb-3">
                    <div class="row">

                        <div class="col-lg-4">
                            <label class="form-label">Item Name </label>
                            <select name="name" id="inventory-filter" class="form-control"></select>
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label">Category </label>
                            <select name="category" data-select2-selector="status"
                                class="form-control">
                                <option value="">--Select Item--</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id}}"
                                    {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Buttons --}}
                        <div class="col-12 mt-2">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('inventory.opening-stock.form') }}" class="btn btn-light">Reset</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="feather-search me-1"></i> Apply Filters
                                </button>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="card stretch stretch-full">
            <div class="card-body ">

                {{-- ✅ BULK SAVE FORM --}}
                <form action="{{ route('opening-stock.bulk.update') }}" method="POST" id="bulkOpeningStockForm">
                    @csrf

                    {{-- ✅ Header --}}
                    <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">Inventory List</h5>
                            <small class="text-muted">Add opening stock and click Save All (button will always stay on screen).</small>
                        </div>

                        {{-- Optional tools (top) --}}
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light" onclick="fillEmptyWithZero()">
                                Fill empty = 0
                            </button>
                            <button type="button" class="btn btn-outline-danger" onclick="clearAllInputs()">
                                Clear
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">
                                Reset
                            </button>
                        </div>
                    </div>

                    {{-- ✅ Table --}}
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0" id="leadList">
                            <thead>
                                <tr>
                                    <th>Sr</th>
                                    <th>Name</th>
                                    <th class="text-center">Supplier</th>
                                    <th class="text-center">Placement</th>
                                    <th class="text-end" style="width: 280px;">Opening Stock</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($items as $index => $i)
                                <tr class="single-item">
                                    <td>{{ $index + 1 }}</td>

                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold">{{ $i->name }}</span>
                                            <small class="text-muted">ID: {{ $i->model   }}</small>
                                        </div>
                                    </td>

                                    <td>
                                        <select
                                            name="supplier_id[{{ $i->id }}]"
                                            class="form-control supplierSelect"
                                            data-id="{{ request('supplier_id')[$i->id] ?? '' }}"
                                            data-text="{{ $selectedSupplierNames[$i->id] ?? '' }}">
                                        </select>
                                    </td>
                                    <td>
                                        <select name="placement[{{ $i->id }}]"
                                            class="form-control select2"
                                            data-select2-selector="status">

                                            <option value="">-- Select Placement --</option>

                                            @foreach($placements as $id => $name)
                                            <option value="{{ $name }}"
                                                {{ (isset($i->placement) && $i->placement == $name) ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                            @endforeach

                                        </select>
                                    </td>


                                    <td class="text-end">
                                        <input
                                            type="number"
                                            name="opening_stock[{{ $i->id }}]"
                                            value="0"
                                            step="0.01"
                                            min="0"
                                            class="form-control text-end opening-stock-input"
                                            placeholder="">

                                        @error('opening_stock.' . $i->id)
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        No items found.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-3">
                        {{ $items->links('pagination::bootstrap-5') }}
                    </div>

                    {{-- ✅ FLOATING STICKY SAVE BUTTON (always visible) --}}
                    <div class="opening-stock-float">
                        <button type="submit" class="btn btn-primary shadow-lg px-4 py-2">
                            💾 Save All Opening Stock
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <style>
        .opening-stock-float {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 9999;
        }

        .opening-stock-float button {
            border-radius: 30px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        /* Mobile friendly */
        @media (max-width: 576px) {
            .opening-stock-float {
                left: 16px;
                right: 16px;
                bottom: 16px;
            }

            .opening-stock-float button {
                width: 100%;
                border-radius: 14px;
            }
        }
    </style>

    <script>
        function fillEmptyWithZero() {
            document.querySelectorAll('.opening-stock-input').forEach(el => {
                if (el.value === '' || el.value === null) el.value = 0;
            });
        }

        function clearAllInputs() {
            document.querySelectorAll('.opening-stock-input').forEach(el => el.value = '');
        }
        document.addEventListener("DOMContentLoaded", function() {
            $('#inventory-filter').select2({
                placeholder: 'Inventory',
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

            function initSupplierSelect(context = document) {
                if (typeof $ === 'undefined' || !$.fn.select2) return;

                $(context).find('.supplierSelect').each(function() {
                    let $this = $(this);

                    if ($this.hasClass('select2-hidden-accessible')) return;

                    $this.select2({
                        placeholder: 'Search Supplier',
                        width: '100%',
                        ajax: {
                            url: "{{ route('suppliers.search') }}",
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
                            }
                        }
                    });

                    // ✅ OLD VALUE SET (important)
                    let id = $this.data('id');
                    let text = $this.data('text');

                    if (id && text) {
                        let option = new Option(text, id, true, true);
                        $this.append(option).trigger('change');
                    }
                });
            }

            initSupplierSelect(document);
        });
    </script>
@endsection
``