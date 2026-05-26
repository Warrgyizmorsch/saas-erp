@extends('shared::layouts.app')
@section('content')
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Approval GRN</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="/dashboard">Home</a>
                </li>
                <li class="breadcrumb-item active">Approval GRN</li>
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
                    {{-- GRN LIST --}}
                    <a href="{{ route('grn.list') }}" class="btn btn-light">
                        <i class="feather-list me-2"></i>
                        <span>GRN List</span>
                    </a>

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

    {{-- filterCollapse --}}
    <div class="collapse mb-3" id="filterCollapse">
        <div class="card border shadow-sm">
            <div class="card-body">
                <form method="GET" action="{{ route('grn.index') }}" class="mb-3">
                    <div class="row">

                        {{-- PO Code --}}

                        <div class="col-md-3">
                            <label class="form-label">From Date</label>
                            <input type="date" name="from_date" class="form-control"
                                value="{{ request('from_date') }}">

                            @error('from_date')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">To Date</label>
                            <input type="date" name="to_date" class="form-control"
                                value="{{ request('to_date') }}">

                            @error('to_date')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>


                        <div class="col-lg-3 mb-2">
                            <label class="form-label fw-semibold">PO Code</label>
                            <input type="text" name="po_code" class="form-control"
                                placeholder="Enter PO Code"
                                value="{{ request('po_code') }}">
                        </div>

                        {{-- Supplier --}}
                        <div class="col-lg-3 mb-2">
                            <label class="form-label fw-semibold">Supplier</label>
                            <select name="supplier_id" id="supplier-filter" class="form-control"></select>
                        </div>

                        <div class="col-lg-3 mb-2">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-control" data-select2-selector="status">
                                <option value="">All Status</option>
                                <option value="Approved"
                                    {{ request('status') == 'Approved' ? 'selected' : '' }}>
                                    Approved
                                </option>
                                <option value="Partially Received"
                                    {{ request('status') == 'Partially Received' ? 'selected' : '' }}>
                                   Partially Received
                                </option>
                            </select>
                        </div>


                        {{-- Buttons --}}
                        <div class="col-12 mt-2">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('grn.index') }}" class="btn btn-light">Reset</a>
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
        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full">
                    <div class="card-body">



                        <div class="table-responsive">
                            <table class="table table-striped align-middle" id="poList">
                                <thead>
                                    <tr>
                                        <th style="width:70px">Sr</th>
                                        <th>PO Code</th>
                                        <th>Supplier</th>
                                        <th>Created By</th>
                                        <th>Created At</th>
                                        <th>Status</th>
                                        <th>Remark</th>
                                        <th class="text-center" style="width:140px">Actions</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($po as $index => $row)
                                    @php
                                    $statusRaw = trim((string)($row->status ?? ''));
                                    $status = strtolower($statusRaw);

                                    // Badge mapping for PO status
                                    $badgeClass = 'bg-soft-secondary text-secondary';
                                    $label = $statusRaw ?: 'N/A';

                                    if (in_array($status, ['approved'])) {
                                    $badgeClass = 'bg-soft-success text-success';
                                    $label = 'Approved';
                                    } elseif (in_array($status, ['partially received', 'partial', 'partially_received'])) {
                                    $badgeClass = 'bg-soft-warning text-warning';
                                    $label = 'Partially Received';
                                    } elseif (in_array($status, ['completed', 'closed', 'received'])) {
                                    $badgeClass = 'bg-soft-primary text-primary';
                                    $label = 'Completed';
                                    } elseif (in_array($status, ['draft'])) {
                                    $badgeClass = 'bg-soft-info text-info';
                                    $label = 'Draft';
                                    } elseif (in_array($status, ['rejected'])) {
                                    $badgeClass = 'bg-soft-danger text-danger';
                                    $label = 'Rejected';
                                    }
                                    @endphp

                                    <tr>
                                        <td>{{ $po->firstItem() + $index }}</td>

                                        <td class="fw-bold">#{{ $row->po_number ?? 'N/A' }}</td>

                                        <td>
                                            <span class="text-truncate-1-line">
                                                {{ $row->supplier?->supplier_name ?? 'N/A' }}
                                            </span>
                                        </td>

                                        <td>{{ $row->creator?->name ?? 'N/A' }}</td>

                                        <td>
                                            {{ \Carbon\Carbon::parse($row->created_at)->format('d M Y, h:i A') }}
                                        </td>

                                        <td>
                                            <span class="badge {{ $badgeClass }}">
                                                {{ $label }}
                                            </span>
                                        </td>

                                        <td>
                                            <span class="text-truncate-1-line">
                                                {{ $row->remarks ?? '-' }}
                                            </span>
                                        </td>

                                        <td class="text-center">
                                            <div class="d-flex gap-2 justify-content-center">

                                                {{-- View PO (optional route) --}}
                                                {{-- <a href="{{ route('purchase-orders.show', $row->id) }}" class="btn btn-light btn-sm" title="View PO">
                                                <i class="fa fa-eye"></i>
                                                </a> --}}

                                                {{-- Create GRN from this PO --}}
                                                <a href="{{ route('grn.create', ['po_id' => $row->id]) }}"
                                                    class="btn btn-primary btn-sm"
                                                    title="Create GRN">
                                                    <i class="feather feather-refresh-ccw"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                   
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">
                                            No Purchase Orders found
                                        </td>
                                    </tr>


                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $po->links('pagination::bootstrap-5') }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
         document.addEventListener("DOMContentLoaded", function() {
         $('#supplier-filter').select2({
                placeholder: 'All Suppliers',
                allowClear: false,
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
                    },
                    cache: true
                }
            });

            // ✅ SELECTED VALUE SET KARNA (MOST IMPORTANT)
            let selectedSupplierId = "{{ request('supplier_id') }}";
            let selectedSupplierText = "{{ $selectedSupplierName ?? '' }}";

            if (selectedSupplierId && selectedSupplierText) {
                let option = new Option(selectedSupplierText, selectedSupplierId, true, true);
                $('#supplier-filter').append(option).trigger('change');
            }
});
    </script>
@endsection