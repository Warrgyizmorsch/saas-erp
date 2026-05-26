@extends('shared::layouts.app')
@section('content')

    <!-- PAGE HEADER (WILL NOT PRINT) -->
    <div class="page-header d-flex justify-content-between align-items-center no-print">

        <!-- LEFT -->
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Product Details</h5>
            </div>

            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('product.index') }}">Products</a></li>
                <li class="breadcrumb-item active">View</li>
            </ul>
        </div>

        <!-- RIGHT -->
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print me-2"></i> Print
            </button>
        </div>

    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">

        <!-- PRINT AREA START -->
        <div id="print-area">

            <div class="card stretch stretch-full">
                <div class="card-body p-0">

                    <!-- CARD HEADER -->
                    <div class="d-flex justify-content-between align-items-center px-4 py-3 border-bottom border-gray-300">
                        <div>
                            <img src="{{ public_path('assets/logo.png') }}" width="70" alt="Logo">
                        </div>

                        <div class="text-end text-sm">
                            <strong>Your Company Name</strong><br>
                            Address Line 1<br>
                            Address Line 2<br>
                            Email | Website<br>
                            Contact No.
                        </div>
                    </div>

                    <!-- CONTENT -->
                    <div class="px-4 py-4">

                        <h5 class="text-center fw-bold mb-4">Product Details</h5>

                        <!-- BASIC DETAILS -->
                        <table class="table table-bordered mb-4">
                            <tr>
                                <th class="w-25">Product Name</th>
                                <td>{{ $product->name }}</td>
                            </tr>
                            <tr>
                                <th>Estimation Duration</th>
                                <td>{{ $product->estimation_duration }} Days</td>
                            </tr>
                        </table>

                        <!-- ITEMS TABLE -->
                        <table class="table table-bordered table-striped mb-4">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">Sr.</th>
                                    <th>Item Name</th>
                                    <th class="text-center">Unit</th>
                                    <th class="text-end">Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $productArr = $product->toArray();
                                @endphp

                                @if(!empty($productArr['product_items']) && count($productArr['product_items']) > 0)
                                    @foreach($productArr['product_items'] as $key => $row)
                                        <tr>
                                            <td class="text-center">{{ $key + 1 }}</td>
                                            <td>{{ $row['inventory']['name'] ?? '-' }}</td>
                                            <td class="text-center">{{ $row['inventory']['unit']['name'] ?? '-' }}</td>
                                            <td class="text-end">{{ $row['quantity'] }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            No items found
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>

                        <!-- DECLARATION -->
                        <div class="mb-4 text-sm">
                            <strong>Declaration :</strong>
                            We declare that this copy shows the actual information and all particulars are true and correct.
                        </div>

                        <!-- SIGN -->
                        <div class="text-end fw-bold">
                            ( Authorised Signatory )
                        </div>

                    </div>
                </div>
            </div>

        </div>
        <!-- PRINT AREA END -->

    </div>

@endsection

<!-- PRINT CSS -->
<style>
@media print {

    /* Hide everything */
    body * {
        visibility: hidden;
    }

    /* Show only print area */
    #print-area,
    #print-area * {
        visibility: visible;
    }

    /* Position print area */
    #print-area {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }

    /* Hide buttons and page header */
    .no-print,
    .btn {
        display: none !important;
    }

    /* Table styling for print */
    table {
        page-break-inside: auto;
    }

    tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }

    thead {
        display: table-header-group;
    }
}
</style>
