{{-- resources/views/current-stock/current.blade.php --}}
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

    {{-- PAGE HEADER --}}
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Current Stock</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="/dashboard">Home</a>
                </li>
                <li class="breadcrumb-item active">Stock</li>
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
                    <button id="filterBtn" class="btn btn-icon btn-light-brand" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                        <i class="feather-filter"></i>
                    </button>
                    <a href="{{ route('current-stock.export', request()->query()) }}"
                        class="btn btn-success">
                        <i class="fa fa-download me-2"></i>
                        Export Excel
                    </a>
                </div>

            </div>

            <div class="d-md-none d-flex align-items-center">
                <a href="javascript:void(0)" class="page-header-right-open-toggle">
                    <i class="feather-align-right fs-20"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- MAIN CONTENT --}}
    <div class="main-content">

        {{-- FILTER --}}
        <div class="collapse mb-3 {{ request()->query() ? 'show' : '' }}" id="filterCollapse">
            <div class="card border shadow-sm">
                <div class="card-body">
                    <form method="GET" action="{{ route('current-stock.index') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-lg-4">
                                <label class="form-label">Item Name</label>
                                <select name="name" id="inventory-filter" class="form-control"></select>
                            </div>

                            <div class="col-lg-3">
                                <label class="form-label">Category</label>
                                <select name="category_id" data-select2-selector="status" class="form-control">
                                    <option value="">--Select Category--</option>
                                    @foreach($categories as $c)
                                    <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-lg-3">
                                <label class="form-label">Classification</label>
                                <select name="classification" data-select2-selector="status" class="form-control">
                                    <option value="">--Select Classification--</option>
                                    <option value="FINISH" {{ request('classification') == 'FINISH' ? 'selected' : '' }}>FINISH</option>
                                    <option value="SEMI_FINISH" {{ request('classification') == 'SEMI_FINISH' ? 'selected' : '' }}>SEMI_FINISH</option>
                                </select>
                            </div>

                            <div class="col-lg-2 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">Search</button>
                                <a href="{{ route('current-stock.index') }}" class="btn btn-light w-100">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full">
                    <div class="card-body p-0"> {{-- Removed padding for better table fit --}}
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 inventory-table" id="requestSlipList">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">Sr</th>
                                        <th>Inventory Details</th>
                                        <th>Placement</th>
                                        <th style="min-width: 250px;">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span>Stock Bifurcation</span>

                                                <div class="d-flex flex-column ms-2">

                                                    {{-- ASC --}}
                                                    <a href="{{ route('current-stock.index', array_merge(request()->query(), ['sort' => 'asc'])) }}"
                                                        style="line-height: 10px;">
                                                        <i class="fa fa-sort-up 
                    {{ request('sort') == 'asc' ? 'text-primary' : 'text-dark' }}">
                                                        </i>
                                                    </a>

                                                    {{-- DESC --}}
                                                    <a href="{{ route('current-stock.index', array_merge(request()->query(), ['sort' => 'desc'])) }}"
                                                        style="line-height: 10px; margin-top:-5px;">
                                                        <i class="fa fa-sort-down
                    {{ request('sort') == 'desc' ? 'text-primary' : 'text-dark' }}">
                                                        </i>
                                                    </a>

                                                </div>
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($currentStock as $index => $cs)
                                    <tr class="single-item">
                                        <td>{{ $currentStock->firstItem() + $index }}</td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $cs->name ?? 'N/A' }}</div>
                                            @if($cs->model)
                                            <small class="text-muted">Model: {{ $cs->model }}</small>
                                            @endif
                                        </td>
                                        <td><span class="badge bg-soft-secondary text-secondary">{{ $cs->placement ?? '-' }}</span></td>
                                        <td class="p-0">
                                            {{-- Clean Vertical List Style --}}
                                            <div class="d-flex flex-column">
                                                @if($cs->classification == 'SEMI_FINISH')
                                                <div class="stock-tag">
                                                    <span class="tag-label">MACHINING</span>
                                                    <span class="tag-value">{{ $cs->machining_stock ?? 0 }}</span>
                                                </div>
                                                <div class="stock-tag">
                                                    <span class="tag-label">FINISH</span>
                                                    <span class="tag-value">{{ $cs->finish_stock ?? 0 }}</span>
                                                </div>
                                                <div class="stock-tag">
                                                    <span class="tag-label">SEMI-FINISH</span>
                                                    <span class="tag-value">{{ $cs->semi_finish_stock ?? 0 }}</span>
                                                </div>
                                                @endif
                                                <div class="stock-tag total-row">
                                                    <span class="fw-bold text-primary" style="font-size: 10px;">AVAILABLE TOTAL</span>
                                                    <span class="fw-bold text-primary fs-13">{{ number_format($cs->total ?? 0, 2) }}</span>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            No stock data available.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($currentStock->hasPages())
                        <div class="p-3">
                            {{ $currentStock->links('pagination::bootstrap-5') }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
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
        });
    </script>
@endsection