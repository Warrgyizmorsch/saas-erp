@extends('shared::layouts.app')
@section('content')


    {{-- PAGE HEADER --}}
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Purchase Order </h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="/dashboard">Home</a>
                </li>
                <li class="breadcrumb-item active">Purchase Order</li>
            </ul>
        </div>

        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">

                {{-- Mobile back (when right panel open) --}}
                <div class="d-flex d-md-none">
                    <a href="javascript:void(0)" class="page-header-right-close-toggle">
                        <i class="feather-arrow-left me-2"></i>
                        <span>Back</span>
                    </a>
                </div>

                {{-- Right side buttons --}}
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <button id="filterBtn" class="btn btn-icon btn-light-brand" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                        <i class="feather-filter"></i>
                    </button>
                </div>

            </div>

            {{-- Mobile open toggle --}}
            <div class="d-md-none d-flex align-items-center">
                <a href="javascript:void(0)" class="page-header-right-open-toggle">
                    <i class="feather-align-right fs-20"></i>
                </a>
            </div>
        </div>
    </div>


    {{-- MAIN CONTENT --}}
    <div class="main-content">
        {{-- filterCollapse --}}
        <div class="collapse mb-3" id="filterCollapse">
            <div class="card border shadow-sm">
                <div class="card-body">
                    <form method="GET" action="{{ route('purchase-order.approval') }}">
                        <div class="row g-3 align-items-end">

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

                            <div class="col-md-3">
                                <label class="form-label">Purchase Order No</label>
                                <input type="text" name="po_no" class="form-control"
                                    value="{{ request('po_no') }}" placeholder="Search by Purchase Order No ">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Supplier</label>
                                <select name="supplier_id" id="supplier-filter" class="form-control"></select>
                            </div>

                            <div class="col-lg-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control" data-select2-selector="status">
                                    <option value="">-- Select --</option>
                                    <option value="Draft" {{ request('status') == 'Draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="Submitted" {{ request('status') == 'Submitted' ? 'selected' : '' }}>Submitted</option>
                                    <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="Partially Received" {{ request('status') == 'Partially Received' ? 'selected' : '' }}>Partially Received</option>
                                    <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="Cancelled" {{ request('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">From Date(expected_date)</label>
                                <input type="date" name="ex_from_date" class="form-control"
                                    value="{{ request('from_date') }}">

                                @error('ex_from_date')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">To Date(expected_date)</label>
                                <input type="date" name="ex_to_date" class="form-control"
                                    value="{{ request('to_date') }}">

                                @error('ex_to_date')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>


                            <!-- Buttons -->
                            <div class="col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-50">
                                    <i class="feather-search"></i> Search
                                </button>

                                <a href="{{ route('purchase-order.approval') }}" class="btn btn-light w-50">
                                    Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full">
                    <div class="card-body">

                        <div class="table-responsive">
                            <table class="table table-striped" id="requestSlipList">
                                <thead>
                                    <tr>
                                        <th>Sr</th>
                                        <th>Purchase Order Date</th>
                                        <th>Purchase Order No</th>
                                        <th>Expected Delivery </th>
                                        <th>Supplier</th>
                                        <th>Total Request Qunatity</th>
                                        <th>SubTotal</th>
                                        <th>Tax Amount</th>
                                        <th>Total Amount</th>
                                        <th>Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($PurchaseOrders as $index => $po)
                                    <tr class="single-item">

                                        <td>{{ $index+1 }}</td>

                                        <td>
                                            <span>
                                                {{ $po->po_date ?? 'N/A' }}
                                            </span>
                                        </td>

                                        <td>{{ $po->po_number ?? 'N/A' }}</td>

                                        <td>{{$po->expected_delivery ?? 'N/A'}}</td>
                                        <td>{{$po->supplier->supplier_name ?? 'N/A'}}</td>
                                        <td>{{$po->total_qty}}</td>

                                        <td>{{ $po->subtotal ?? 'N/A' }}</td>

                                        <td>{{ $po->tax_amount ?? 'N/A' }}</td>

                                        <td>{{$po->total_amount ?? 'N/A'}}</td>

                                        <td>
                                            @switch($po->status)
                                            @case('Draft')
                                            <span class="badge bg-soft-warning text-warning">Draft</span>
                                            @break
                                            @case('Submitted')
                                            <span class="badge bg-soft-info text-info">Submitted</span>
                                            @break

                                            @case('Approved')
                                            <span class="badge bg-soft-success text-success">Approved</span>
                                            @break
                                            @case('Partially Received')
                                            <span class="badge bg-soft-warning text-warning">Partially Received</span>
                                            @break
                                            @case('Completed')
                                            <span class="badge bg-soft-info text-info">Completed</span>
                                            @break
                                            @case('Cancelled')
                                            <span class="badge bg-soft-danger text-danger">Cancelled</span>
                                            @break
                                            @default
                                            <span class="badge bg-soft-secondary text-secondary">Unknown</span>

                                            @endswitch
                                        </td>

                                        {{-- Actions --}}
                                        <td class="d-flex gap-2 justify-content-center">

                                            {{-- View --}}
                                            <a href="{{ route('purchase-order.show', $po->id) }}"
                                                class="btn btn-light btn-sm" title="View Details">
                                                <i class="fa fa-eye"></i>
                                            </a>

                                            <button type="button"
                                                class="btn btn-primary btn-sm"
                                                title="Update Status"
                                                data-bs-toggle="offcanvas"
                                                data-bs-target="#statusUpdate{{ $po->id }}">
                                                <i class="feather-check-circle me-1"></i> Update Status
                                            </button>


                                        </td>
                                    </tr>

                                    {{-- ✅ OFFCANVAS: STATUS UPDATE --}}
                                    <div class="offcanvas offcanvas-end"
                                        tabindex="-1"
                                        id="statusUpdate{{ $po->id }}">
                                        <form method="POST" action="{{ route('purchase-order.status-update', $po->id) }}">
                                            @csrf

                                            <div class="offcanvas-header ht-80 px-4 border-bottom border-gray-5">
                                                <div>
                                                    <h2 class="fs-16 fw-bold mb-0">Update Purchase order Status</h2>
                                                    <small class="fs-12 text-muted">Choose Approved / Rejected .</small>
                                                </div>
                                                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                                            </div>

                                            <div class="offcanvas-body px-4">

                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">purchase Order no</label>
                                                    <input type="text" class="form-control" value="#{{ $po->po_number }}" disabled>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">total qty</label>
                                                    <input type="text" class="form-control" value="{{ $po->total_qty ?? 'N/A' }}" disabled>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Select Status</label>
                                                    <select name="status" class="form-control" data-select2-selector="status" required>
                                                        <option value="">-- Select --</option>
                                                        <option value="Approved">Approved</option>
                                                        <option value="Cancelled">Rejected</option>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Remarks (optional)</label>
                                                    <textarea name="remarks" rows="4" class="form-control"
                                                        placeholder="Reason / note..."></textarea>
                                                </div>

                                            </div>

                                            <div class="px-4 gap-2 d-flex align-items-center justify-content-between ht-80 border-top border-gray-2">
                                                <button type="submit" class="btn btn-primary w-50">Submit</button>
                                                <button type="button" class="btn btn-light w-50" data-bs-dismiss="offcanvas">
                                                    Cancel
                                                </button>
                                            </div>
                                        </form>
                                    </div>


                                    @empty
                                    <tr>
                                        <td colspan="12" class="text-center text-muted">
                                            No Request Slips found
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>

                            {{-- Pagination --}}
                            <div class="mt-3">
                                {{ $PurchaseOrders->links('pagination::bootstrap-5') }}
                            </div>
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