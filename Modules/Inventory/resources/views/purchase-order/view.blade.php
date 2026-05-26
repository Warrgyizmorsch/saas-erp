@extends('shared::layouts.app')
@section('content')

    {{-- PAGE HEADER --}}
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Purchase Order</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="/dashboard">Home</a>
                </li>
                <li class="breadcrumb-item active">Purchase Order List</li>
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

                    {{-- Create Request Slip --}}
                    <a href="{{ route('purchase-order.create') }}" class="btn btn-primary">
                        <i class="feather-plus me-2"></i>
                        <span>Create PO</span>
                    </a>

                    <a href="{{ route('purchase-order.export', request()->query()) }}"
                        class="btn btn-success">
                        <i class="fa fa-download me-2"></i>
                        <span>Export Excel</span>
                    </a>

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
                    <form method="GET" action="{{ route('purchase-order.view') }}">
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
                                <label class="form-label">Firm</label>
                                <select name="firm_id" class="form-control" data-select2-selector="firm_id">
                                    <option value="">-- Select Firm--</option>
                                    @foreach($firms as $firm)
                                    <option value="{{ $firm->id }}" {{ request('firm_id') == $firm->id ? 'selected' : '' }}>
                                        {{ $firm->name }}
                                    </option>
                                    @endforeach
                                </select>
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
                                    <option value="Cancelled" {{ request('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>


                            <div class="col-md-3">
                                <label class="form-label">From Date(expected_date)</label>
                                <input type="date" name="ex_from_date" class="form-control"
                                    value="{{ request('ex_from_date') }}">

                                @error('ex_from_date')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">To Date(expected_date)</label>
                                <input type="date" name="ex_to_date" class="form-control"
                                    value="{{ request('ex_to_date') }}">

                                @error('ex_to_date')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>


                            <!-- Buttons -->
                            <div class="col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-50">
                                    <i class="feather-search"></i> Search
                                </button>

                                <a href="{{ route('purchase-order.view') }}" class="btn btn-light w-50">
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
                        <!-- SUMMARY SECTION -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="p-3 rounded-3 d-flex justify-content-between align-items-center" style="background: #eff6ff; border-left: 4px solid #2563eb;">
                                    <div>
                                        <span class="text-muted small uppercase fw-bold d-block mb-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Total Amount</span>
                                        <h5 class="text-primary fw-bold mb-0">₹{{ number_format($summaryTotal ?? 0, 2) }}</h5>
                                    </div>
                                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: rgba(37, 99, 235, 0.1);">
                                        <i class="fa fa-calculator text-primary" style="font-size: 14px;"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 rounded-3 d-flex justify-content-between align-items-center" style="background: #f0fdf4; border-left: 4px solid #16a34a;">
                                    <div>
                                        <span class="text-muted small uppercase fw-bold d-block mb-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Advance Amount</span>
                                        <h5 class="text-success fw-bold mb-0">₹{{ number_format($summaryAdvance ?? 0, 2) }}</h5>
                                    </div>
                                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: rgba(22, 163, 74, 0.1);">
                                        <i class="fa fa-credit-card text-success" style="font-size: 14px;"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 rounded-3 d-flex justify-content-between align-items-center" style="background: #fef2f2; border-left: 4px solid #ef4444;">
                                    <div>
                                        <span class="text-muted small uppercase fw-bold d-block mb-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Due Amount</span>
                                        <h5 class="text-danger fw-bold mb-0">₹{{ number_format($summaryDue ?? 0, 2) }}</h5>
                                    </div>
                                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: rgba(239, 68, 68, 0.1);">
                                        <i class="fa fa-exclamation-circle text-danger" style="font-size: 14px;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table" id="requestSlipList">
                                <thead>
                                    <tr>
                                        <th>Sr</th>
                                        <th>PO Details</th>
                                        <th>Supplier/Firm</th>
                                        <th>Amount Details</th>
                                        <th>Delivery Info</th>
                                        <th>Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>



                                <tbody>
                                    @forelse($PurchaseOrders as $index => $po)

                                    <tr style="{{ (in_array(Auth::user()->role_id, [1,2]) && $po->is_late) ? 'background-color: #f8d7da !important;' : '' }}">

                                        <td>{{ $index+1 }}</td>

                                        <td>
                                            <div class="small" style="min-width: 120px;">
                                                <div class="mb-1">
                                                    <span class="text-muted d-block" style="font-size: 10px;">PO NO</span>
                                                    <span class="fw-bold text-primary">{{ $po->po_number ?? 'N/A' }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-muted d-block" style="font-size: 10px;">PO DATE</span>
                                                    <span class="fw-semibold text-dark">{{ $po->po_date ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <div class="small" style="min-width: 140px;">
                                                <div class="mb-1">
                                                    <span class="text-muted d-block" style="font-size: 10px;">SUPPLIER</span>
                                                    <span class="fw-semibold text-dark">{{$po->supplier->supplier_name ?? 'N/A'}}</span>
                                                </div>
                                                <div>
                                                    <span class="text-muted d-block" style="font-size: 10px;">FIRM</span>
                                                    <span class="fw-semibold text-dark">{{$po->firmData->name ?? 'N/A'}}</span>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <div class="small" style="min-width: 150px;">
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span class="text-muted me-2">Total:</span>
                                                    <span class="fw-bold text-dark">{{ number_format($po->total_amount ?? 0, 2) }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-1 pb-1 border-bottom">
                                                    <span class="text-muted me-2">Advance:</span>
                                                    <span class="fw-semibold text-success">{{ number_format($po->advance_amount ?? 0, 2) }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between pt-1">
                                                    <span class="text-muted me-2">Due Amount:</span>
                                                    <span class="fw-bold text-danger">{{ number_format($po->balance_amount ?? 0, 2) }}</span>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <div class="small">
                                                <div>
                                                    <strong>Deliver Status:</strong>
                                                    @if($po->delivery_status == 'Shipped')
                                                    <span class="badge bg-soft-primary text-primary"> {{ $po->delivery_status ?? 'N/A' }}</span>
                                                    @elseif($po->delivery_status == 'Transit')
                                                    <span class="badge bg-soft-info text-info"> {{ $po->delivery_status ?? 'N/A' }}</span>
                                                    @elseif($po->delivery_status == 'Received')
                                                    <span class="badge bg-soft-success text-success"> {{ $po->delivery_status ?? 'N/A' }}</span>
                                                    @else
                                                    <span class="badge bg-soft-warning text-warning">Pending</span>
                                                    @endif
                                                </div>
                                                <div>
                                                    <strong>Expected Date:</strong>
                                                    {{ $po->expected_delivery ? \Carbon\Carbon::parse($po->expected_delivery)->format('d-m-Y') : 'N/A' }}
                                                </div>
                                                @if($po->completed_at)
                                                <div>
                                                    <strong>Completed:</strong>
                                                    {{ $po->completed_at ? \Carbon\Carbon::parse($po->completed_at)->format('d-m-Y') : 'N/A' }}
                                                </div>
                                                @endif

                                                @if($po->is_late && is_array($po->is_late) && $po->is_late['late'])
                                                <div>
                                                    <strong class="text-danger">Delay:</strong>
                                                    <span class="text-danger">{{ $po->is_late['delay_days'] }} day(s)</span>
                                                </div>
                                                @endif
                                            </div>
                                        </td>


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
                                        <td class="align-middle">
                                            <div class="d-flex flex-column gap-2 mx-auto" style="width: 120px;">
                                                <div class="d-grid gap-1" style="grid-template-columns: repeat(2, 1fr);">
                                                    @if($po->status == 'Draft')
                                                    <a href="{{ route('purchase-order.edit', $po->id) }}"
                                                        class="btn btn-light btn-sm w-100" title="Edit Purchase Order">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                    @endif

                                                    <a href="{{ route('purchase-order.show', $po->id) }}"
                                                        class="btn btn-light btn-sm w-100" title="View Details">
                                                        <i class="fa fa-eye"></i>
                                                    </a>

                                                    @if($po->status == 'Draft')
                                                    <button type="button"
                                                        class="btn btn-light btn-sm w-100"
                                                        title="Delete Purchase Order"
                                                        data-bs-toggle="offcanvas"
                                                        data-bs-target="#deleteInventory{{$po->id }}">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                    @endif

                                                    <button type="button"
                                                        class="btn btn-light btn-sm w-100"
                                                        title="Update Advance Amount"
                                                        data-bs-toggle="offcanvas"
                                                        data-bs-target="#advanceAmount{{$po->id }}">
                                                        <i class="fa fa-inr"></i>
                                                    </button>
                                                </div>

                                                <a href="javascript:void(0)"
                                                    class="btn btn-light btn-sm w-100"
                                                    title="add Expected date"
                                                    data-bs-toggle="offcanvas"
                                                    data-bs-target="#expactedDelivery{{$po->id }}">
                                                    + Expected Date
                                                </a>
                                            </div>
                                        </td>
                                    </tr>

                                    <div class="offcanvas offcanvas-end" tabindex="-1" id="expactedDelivery{{$po->id}}">

                                        <!-- Header -->
                                        <div class="offcanvas-header ht-80 px-4 border-bottom border-gray-5">
                                            <h5 class="mb-0">Expected Delivery</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                                        </div>

                                        <div class="offcanvas-body px-4">

                                            <!-- Add Expected Date -->
                                            <div class="mb-4">
                                                <h6 class="mb-3 text-muted">Update Expected Date</h6>

                                                <form method="POST" action="{{route('purchase.updateDeliveryStatus', $po->id)}}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="mb-3">
                                                        <label class="form-label">Expected Delivery Date</label>
                                                        <input type="date"
                                                            name="expected_date"
                                                            class="form-control"
                                                            value="{{$po->expected_delivery}}">
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label">Delivery Status</label>

                                                        <select name="delivery_status"
                                                            class="form-control"
                                                            data-select2-selector="status">
                                                            <option value="Pending"
                                                                data-bg="bg-warning"
                                                                {{ $po->delivery_status === 'Pending' ? 'selected' : '' }}>
                                                                Pending
                                                            </option>

                                                            <option value="Shipped"
                                                                data-bg="bg-primary"
                                                                {{ $po->delivery_status === 'Shipped' ? 'selected' : '' }}>
                                                                Shipped
                                                            </option>

                                                            <option value="Transit"
                                                                data-bg="bg-info"
                                                                {{ $po->delivery_status === 'Transit' ? 'selected' : '' }}>
                                                                Transit
                                                            </option>

                                                            <option value="Received"
                                                                data-bg="bg-success"
                                                                {{ $po->delivery_status === 'Received' ? 'selected' : '' }}>
                                                                Received
                                                            </option>

                                                        </select>
                                                    </div>

                                                    <button type="submit" class="btn btn-primary w-100">
                                                        Update Delivery
                                                    </button>

                                                </form>
                                            </div>


                                            <!-- Divider -->
                                            <hr class="my-4">


                                            <!-- Tracking History -->
                                            <div class="card-body custom-card-action">
                                                <ul class="list-unstyled mb-0 activity-feed-1">

                                                    @foreach($po->grns as $grn)

                                                    <li class="feed-item feed-item-success">
                                                        <div class="d-flex gap-4 justify-content-between">

                                                            <div>
                                                                <div class="mb-2">
                                                                    <a href="javascript:void(0)" class="fw-semibold text-dark">
                                                                        GRN Generated : {{ $grn->grn_number }}
                                                                    </a>
                                                                </div>

                                                                <p class="fs-12 text-muted mb-2">
                                                                    GRN Date : <strong>{{ $grn->grn_date }}</strong><br>
                                                                </p>

                                                                {{-- Item wise quantity --}}
                                                                @foreach($grn->items as $item)

                                                                <p class="fs-12 text-muted mb-1">
                                                                    Item : {{ $item->inventory->name }} <br>
                                                                    Received Qty : <strong>{{ $item->accepted_qty}}</strong></br>
                                                                </p>

                                                                @endforeach

                                                            </div>

                                                            <div class="fs-10 fw-medium text-uppercase text-muted text-nowrap">
                                                                {{ \Carbon\Carbon::parse($grn->created_at)->format('d M Y') }}
                                                            </div>

                                                        </div>
                                                    </li>

                                                    @endforeach

                                                </ul>
                                            </div>

                                        </div>
                                    </div>

                                    {{-- OFFCANVAS: DELETE --}}
                                    <div class="offcanvas offcanvas-end" tabindex="-1" id="deleteInventory{{ $po->id }}">
                                        <form method="POST" action="{{ route('purchase-order.destroy', $po->id) }}">
                                            @csrf
                                            @method('DELETE')

                                            <div class="offcanvas-header ht-80 px-4 border-bottom border-gray-5">
                                                <h2 class="fs-16 fw-bold mb-0">Delete Request Slip</h2>
                                                <button type="button" class="btn-close btn-close-gray ms-auto" data-bs-dismiss="offcanvas"></button>
                                            </div>

                                            <div class="offcanvas-body">
                                                <p class="fs-15">
                                                    Are you sure you want to delete
                                                    <strong>#{{ $po->po_number }}</strong>?
                                                </p>
                                            </div>

                                            <div class="px-4 gap-2 d-flex align-items-center justify-content-between ht-80 border-top border-gray-2">
                                                <button type="submit" class="btn btn-primary w-50">Yes, Delete</button>
                                                <button type="button" class="btn btn-danger w-50" data-bs-dismiss="offcanvas">
                                                    Cancel
                                                </button>
                                            </div>
                                        </form>
                                    </div>


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

                        {{-- OFFCANVAS: ADVANCE AMOUNT --}}
                        @foreach($PurchaseOrders as $po)
                        <div class="offcanvas offcanvas-end" tabindex="-1" id="advanceAmount{{ $po->id }}">

                            {{-- Header --}}
                            <div class="offcanvas-header ht-80 px-4 border-bottom border-gray-5">
                                <h5 class="mb-0">pay Amount</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                            </div>

                            {{-- Form --}}
                            <form method="POST" action="{{ route('purchase-order.updateAdvance', $po->id) }}">
                                @csrf

                                <div class="offcanvas-body px-4">

                                    {{-- Old Balance Amount --}}
                                    <div class="mb-3">
                                        <label class="form-label">Old Balance Amount</label>
                                        <input type="text" class="form-control"
                                            value="{{ number_format($po->balance_amount ?? 0, 2) }}"
                                            readonly>
                                    </div>

                                    {{-- Pay Amount + Pay Button --}}
                                    <div class="mb-2">
                                        <label class="form-label">Pay Amount</label>

                                        <div class="input-group">
                                            <input type="number"
                                                name="pay_amount"
                                                class="form-control pay_amount"
                                                data-balance="{{ $po->balance_amount ?? 0 }}"
                                                min="0"
                                                max="{{ $po->balance_amount ?? 0 }}"
                                                step="0.01"
                                                placeholder="Enter amount"
                                                required>

                                            <button type="submit"
                                                class="btn btn-primary save-advance-btn">
                                                <i class="feather-check me-1"></i> Pay
                                            </button>
                                        </div>

                                        <small class="text-danger d-none advance-error">
                                            New advance amount cannot be greater than balance amount.
                                        </small>

                                        @error('pay_amount')
                                        <div class="text-danger mt-1 small">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Divider --}}
                                    <hr class="my-4">

                                    {{-- Purchase Order History --}}
                                    <div class="mb-2">
                                        <h6 class="mb-3 text-muted">Payment History</h6>

                                        {{-- History Item --}}
                                        @foreach($po->paymentRecord as $p)
                                        <div class="d-flex justify-content-between align-items-start p-3 mb-2 border rounded">
                                            <div>
                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                    <span class="badge bg-success">Paid</span>
                                                    <strong>{{$p->pay_amount}}</strong>
                                                </div>
                                            </div>

                                            <div class="text-end">
                                                <div class="fs-12 text-muted">
                                                    {{ \Carbon\Carbon::parse($p->transaction_date)->format('d M Y') }}
                                                </div>
                                                <button type="button"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="deleteTransaction({{ $p->id }})">

                                                    <i class="fa fa-trash"></i>

                                                </button>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>

                                </div>
                            </form>

                        </div>
                        @endforeach

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

            document.querySelectorAll(".pay_amount").forEach(input => {

                input.addEventListener("input", function() {
                    let balance = parseFloat(this.dataset.balance) || 0;
                    let entered = parseFloat(this.value) || 0;

                    if (entered > balance) {
                        this.value = balance; //  auto max set
                    }

                    if (entered < 0) {
                        this.value = 0;
                    }
                });

            });

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

    <script>
        async function deleteTransaction(id) {
            if (!confirm('Are you sure?')) {
                return;
            }

            try {

                let response = await fetch(
                    `/purchase-order/transaction/${id}`, {
                        method: 'DELETE',

                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    }
                );

                let data = await response.json();

                if (data.status) {

                    alert(data.message);

                    location.reload();

                } else {

                    alert(data.message || 'Something went wrong');
                }

            } catch (error) {

                console.log(error);

                alert('Server Error');
            }
        }
    </script>

@endsection