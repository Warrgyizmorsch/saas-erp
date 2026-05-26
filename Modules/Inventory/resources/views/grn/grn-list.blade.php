@extends('shared::layouts.app')
@section('content')
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">GRN List</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('grn.index') }}">Approval GRN</a></li>
                <li class="breadcrumb-item active">GRN List</li>
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
                    <a href="{{ route('grn.index') }}" class="btn btn-light">
                        <i class="feather-arrow-left me-2"></i>
                        <span>Back to Create GRN</span>
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
                <form method="GET" action="{{ route('grn.list') }}" class="mb-3">
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
                            <label class="form-label fw-semibold">PO NO</label>
                            <input type="text" name="po_code" class="form-control"
                                placeholder="Enter PO Code"
                                value="{{ request('po_code') }}">
                        </div>

                        <div class="col-lg-3 mb-2">
                            <label class="form-label fw-semibold">GRN NO</label>
                            <input type="text" name="grn_no" class="form-control"
                                placeholder="Enter GRN NO"
                                value="{{ request('grn_no') }}">
                        </div>

                        {{-- Supplier --}}
                        <div class="col-lg-3 mb-2">
                            <label class="form-label fw-semibold">Supplier</label>
                            <select name="supplier_id" id="supplier-filter" class="form-control"></select>

                        </div>


                        {{-- Buttons --}}
                        <div class="col-12 mt-2">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('grn.list') }}" class="btn btn-light">Reset</a>
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

                        @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-striped align-middle" id="grnList">
                                <thead>
                                    <tr>
                                        <th style="width:70px">Sr</th>
                                        <th>GRN No</th>
                                        <th>PO No</th>
                                        <th>Supplier</th>
                                        <th>GRN Date</th>
                                        <th>Invoice</th>
                                        <th class="text-end">Total Accepted</th>
                                        <th class="text-center" style="width:140px">Actions</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($grns as $index => $g)
                                    @php
                                    $totalAccepted = (float) ($g->items_sum_accepted_qty ?? 0);
                                    $totalRejected = (int) ($g->items_sum_rejected_qty ?? 0);

                                    // GRN Status optional (agar column nahi hai to N/A)
                                    $grnStatusRaw = $g->status ?? 'Saved';
                                    $grnStatus = strtolower(trim((string)$grnStatusRaw));

                                    $badgeClass = 'bg-soft-success text-success';
                                    $label = $grnStatusRaw;

                                    if (in_array($grnStatus, ['pending','draft'])) {
                                    $badgeClass = 'bg-soft-warning text-warning';
                                    $label = 'Pending';
                                    } elseif (in_array($grnStatus, ['rejected'])) {
                                    $badgeClass = 'bg-soft-danger text-danger';
                                    $label = 'Rejected';
                                    } elseif (in_array($grnStatus, ['approved'])) {
                                    $badgeClass = 'bg-soft-success text-success';
                                    $label = 'Approved';
                                    } else {
                                    $badgeClass = 'bg-soft-primary text-primary';
                                    $label = 'Saved';
                                    }
                                    @endphp

                                    <tr>
                                        <td>{{ $grns->firstItem() + $index }}</td>

                                        <td class="fw-bold">{{ $g->grn_number }}</td>

                                        <td>#{{ $g->purchaseOrder?->po_number ?? 'N/A' }}</td>

                                        <td>{{ $g->purchaseOrder?->supplier?->supplier_name ?? 'N/A' }}</td>

                                        <td>{{ \Carbon\Carbon::parse($g->grn_date)->format('d M Y') }}</td>

                                        <td>{{ $g->invoice_no ?? '-' }}</td>

                                        <td class="text-end fw-bold">{{ number_format($totalAccepted, 2) }}</td>



                                        <td class="text-center">
                                            <div class="d-flex gap-2 justify-content-center">
                                                {{-- View GRN (agar show route banaya ho) --}}
                                                <a href="{{ route('grn.show', $g->id) }}"
                                                    class="btn btn-light btn-sm"
                                                    title="View GRN">
                                                    <i class="fa fa-eye"></i>
                                                </a>

                                                @if($totalRejected > 0 )
                                                <button type="button"
                                                    class="btn btn-primary btn-sm"
                                                    title="Update Status"
                                                    data-bs-toggle="offcanvas"
                                                    data-bs-target="#statusUpdate{{ $g->id }}">
                                                    <i class="feather-check-circle me-1"></i> Update Status
                                                </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>

                                    <div class="offcanvas offcanvas-end" tabindex="-1" id="statusUpdate{{ $g->id }}">
                                        <form method="POST" action="{{ route('grn.updateStatus', $g->purchase_order_id) }}">
                                            @csrf
                                            <div class="offcanvas-header ht-80 px-4 border-bottom border-gray-5">
                                                <div>
                                                    <h2 class="fs-16 fw-bold mb-0">Update Status</h2>
                                                    <small class="fs-12 text-muted">Choose status and submit.</small>
                                                </div>
                                                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                                            </div>

                                            <div class="offcanvas-body px-4">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">RS Code</label>
                                                    <input type="text" class="form-control" value="#{{ $g->grn_number }}" disabled>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Select Status</label>
                                                    <select name="status" class="form-control" data-select2-selector="status" required>
                                                        <option value="">-- Select --</option>
                                                        <option value="Completed">Completed</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Remarks (optional)</label>
                                                    <textarea name="remarks" rows="4" class="form-control" placeholder="Reason / note..."></textarea>
                                                </div>
                                            </div>

                                            <div class="px-4 gap-2 d-flex align-items-center justify-content-between ht-80 border-top border-gray-2">
                                                <button type="submit" class="btn btn-primary w-50">Submit</button>
                                                <button type="button" class="btn btn-light w-50" data-bs-dismiss="offcanvas">Cancel</button>
                                            </div>
                                        </form>
                                    </div>

                                    @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">
                                            No GRN found
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $grns->links('pagination::bootstrap-5') }}
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