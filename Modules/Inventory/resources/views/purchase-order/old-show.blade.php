    @extends('shared::layouts.app')
@section('content')

        @php
        $taxType = $po->items->first()->tax_type ?? null;

        $remaining_amount = $po->subtotal_discount_amount - $po->final_discount;
        $grandTotal = $po->subtotal_discount_amount - $po->final_discount + $po->tax_amount;

        @endphp

        <style>
            .container-lg {
                padding: 0px !important;
            }
        </style>

        <div class="main-content py-4">
            <div class="container-lg">

                {{-- ACTION BAR - Yeh print mein nahi aayega --}}
                <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
                    <a href="{{ route('purchase-order.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                        <i class="feather feather-arrow-left"></i> Back
                    </a>
                    <div class="d-flex gap-2">
                        @if(in_array($po->status, ['Approved', 'Submitted']))
                        <button class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                            <i class="feather feather-file-text me-1"></i> Create Bill
                        </button>
                        <button class="btn btn-success btn-sm rounded-pill px-3 shadow-sm">
                            <i class="feather feather-package me-1"></i> Receive Goods
                        </button>
                        @endif
                        <button onclick="window.print()" class="btn btn-dark btn-sm rounded-pill px-3 shadow-sm">
                            <i class="feather feather-printer me-1"></i> Print / Download
                        </button>
                    </div>
                </div>

                {{-- THE ACTUAL BILL --}}
                <div id="printable-bill" class="bill-container shadow-lg mx-auto">
                    <div class="container-fluid p-0">

                        <div class="row g-0 align-items-center print-bg "
                            style="background:#c62828; color:#fff; padding:18px 20px;">

                            <!-- Logo -->
                            <div class="col-md-6 d-flex align-items-center">

                                <img src="{{ asset($po->firmData->logo) }}"
                                    class="img-fluid me-3"
                                    style="max-height:60px;">

                                <div>
                                    <h4 class="fw-bold mb-1 text-white"
                                        style="letter-spacing:2px;">
                                        {{ strtoupper($po->firmData->name) }}
                                    </h4>
                                </div>

                            </div>

                        </div>

                    </div>
                    {{-- TOP HEADER --}}
                    <div class="bill-header border rounded mb-3 px-md-3">


                        <div class="row g-0">

                            <!-- COMPANY BOX -->
                            <div class="col-md-6 p-3  border-end">

                                <div class="d-flex flex-column flex-md-row pt-md-3">


                                    <div>

                                        <h5 class="fw-bold mb-1 ">
                                            {{ $po->firmData->name }}
                                        </h5>

                                        <div style="font-size:14px; line-height:22px;">

                                            <div>
                                                <strong>Regd.Office :</strong>
                                                {!! preg_replace('/,/', ',<br>', $po->firmData->address, 1) !!}
                                            </div>

                                            <div>
                                                <strong>Tel :</strong> {{ $po->firmData->phone }}
                                            </div>

                                            <div>
                                                <strong>Website :</strong> {{ $po->firmData->website }}
                                            </div>

                                            <div>
                                                <strong>Email :</strong> {{ $po->firmData->email }}
                                            </div>

                                            <div class="fw-bold">
                                                GST : {{ $po->firmData->gst_no }}
                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>


                            <!-- SUPPLIER BOX -->
                            <div class="col-md-6 p-3 supplier-box">

                                <div class="text-start text-md-start">

                                    <span class="badge bg-primary mb-2">
                                        SUPPLIER
                                    </span>

                                    <h5 class="fw-bold mb-1">
                                        {{ $po->supplier?->supplier_name }}
                                    </h5>

                                    <div style="font-size:14px; line-height:22px;">

                                        @if(!empty($po->supplier?->supplier_address))
                                        {{ $po->supplier->supplier_address }} <br>
                                        @endif

                                        @if(!empty($po->supplier?->mobile))
                                        <strong>Contact :</strong>
                                        {{ $po->supplier->mobile }} <br>
                                        @endif

                                        @if(!empty($po->supplier?->email))
                                        <strong>Email :</strong>
                                        {{ $po->supplier->email }} <br>
                                        @endif

                                        @if(!empty($po->supplier?->account_number))
                                        <strong>Account No :</strong>
                                        {{ $po->supplier->account_number }} <br>
                                        @endif

                                        @if(!empty($po->supplier?->gstin))
                                        <strong>GSTIN :</strong>
                                        {{ $po->supplier->gstin }} <br>
                                        @endif

                                        @if(!empty($po->supplier?->pan))
                                        <strong>PAN :</strong>
                                        {{ $po->supplier->pan }}
                                        @endif

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="p-2 pt-md-3 p-md-5">
                        {{-- TABLE --}}
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr class="text-muted small text-uppercase bg-light">
                                        <th class="py-3 ps-3 border-0 " style="width: 320px; min-width:260px">Item Description</th>
                                        <th class="text-center py-3 border-0" style="width: 220px; min-width:200px">HSN No.</th>
                                        <th class="text-center py-3 border-0">Qty</th>
                                        <th class="text-end py-3 border-0">Unit Price</th>
                                        <th class="text-end py-3 border-0">Taxable</th>
                                        <th class="text-end py-3 border-0">Discount </th>
                                        <th class="text-center py-3 border-0">Tax %</th>
                                        <th class="text-end py-3 pe-3 border-0">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($po->items as $item)
                                    <tr class="align-middle">
                                        <td class="py-4 ps-3">
                                            <span class="fw-bold text-dark fs-15" style="white-space: normal; word-break: break-word;">{{ $item->inventory->name }}</span></br>
                                            <span class="fw-bold text-dark fs-12">Item Not:</span>
                                            <span class="text-muted" style="font-size: 12px; white-space: normal; word-break: break-word;">{{ $item->item_not }}</span>
                                        </td>
                                        <td class="text-center  py-4" style="white-space: normal; word-break: break-word;">{{ $item->hsn }}</td>
                                        <td class="text-center py-4">{{ $item->ordered_qty }}</td>
                                        <td class="text-end py-4">{{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-end py-4">{{ number_format($item->taxable_total, 2) }}</td>
                                        <td class="text-end py-4">
                                            <div> <span class="fs-12">({{ (float)$item->discount}}%) </span> <span class="fs-12">{{$item->discount_amount}}</span> </div>
                                        </td>
                                        @if($item->tax_type !== 'Other')
                                        <td class="text-center py-4 text-muted small">({{(float) $item->tax_percent }}%) <span>{{$item->tax_amount}}</span></td>
                                        @else
                                        <td class="text-center py-4">
                                            <div> <span class="fs-12">SGST: ({{ (float)$item->tax_percent / 2 }}%)</span> <span class="fs-12">{{$item->tax_amount/2}}</span></div>
                                            <div> <span class="fs-12">CGST: ({{ (float)$item->tax_percent / 2 }}%) </span> <span class="fs-12">{{$item->tax_amount/2}}</span> </div>
                                        </td>
                                        @endif
                                        <td class="text-end py-4 pe-3 fw-bold text-dark">{{ number_format($item->line_total, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- TOTALS SECTION --}}
                        <!-- <div class="row mt-4 pt-3">
                            <div class="col-md-7">
                                <div class="p-4 rounded bg-light border-start border-4 border-primary">
                                    <h6 class="fw-bold small text-uppercase">Terms & Instructions</h6>
                                    <p class="small text-muted mb-0">Please mention the PO number on all shipping documents. Goods must be delivered in original packaging.</p>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="list-group list-group-flush">
                                    <div class="list-group-item d-flex justify-content-between border-0 px-0 py-1">
                                        <span class="text-muted">Taxable Subtotal</span>
                                        <span class="fw-bold">{{ number_format($po->subtotal_discount_amount, 2) }}</span>
                                    </div>

                                    <div class="list-group-item d-flex justify-content-between border-0 px-0 py-1">
                                        <span class="text-muted">Discount</span>
                                        <span class="fw-bold">{{ number_format($po->final_discount, 2) }}</span>
                                    </div>

                                    <div class="list-group-item d-flex justify-content-between border-0 px-0 py-1">
                                        <span class="text-muted">Remaining Amount</span>
                                        <span id="remaining_amount" class="text-success fw-bold">
                                            {{ number_format($remaining_amount, 2) }}
                                        </span>
                                    </div>
                                    @if($taxType !== 'Other')
                                    <div class="list-group-item d-flex justify-content-between border-0 px-0 py-1">
                                        <span class="text-muted">IGST Amount</span>
                                        <span class="text-success fw-bold">+{{ number_format($po->tax_amount, 2) }}</span>
                                    </div>
                                    @else
                                    <div class="list-group-item d-flex justify-content-between border-0 px-0 py-1">
                                        <span class="text-muted">CGST Amount</span>
                                        <span class="text-success fw-bold">+{{ number_format($po->tax_amount/2, 2) }}</span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between border-0 px-0 py-1">
                                        <span class="text-muted">SGST Amount</span>
                                        <span class="text-success fw-bold">+{{ number_format($po->tax_amount/2, 2) }}</span>
                                    </div>
                                    @endif


                                    <div class="list-group-item d-flex justify-content-between border-0 px-0 py-3 mt-2 border-top">
                                        <span class="h5 fw-bolder mb-0">Grand Total</span>
                                        <span id="grand_total" class="h5 fw-bolder mb-0 text-primary">{{ number_format($grandTotal, 2) }}</span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between border-0 px-0 py-1 mb-2">
                                        <span class="h5 fw-bolder mb-0">Advance Amount</span>
                                        <span id="advance_amount" class="h5 fw-bolder mb-0 text-success fw-bold">
                                            -{{ number_format($po->advance_amount, 2) }}
                                        </span>
                                    </div>

                                    <div class="list-group-item d-flex justify-content-between border-0 px-0 py-2 bg-dark text-white rounded px-3">
                                        <span class="fw-bold">Balance Due</span>
                                        <span id="balance_due" class="fw-bold">{{ number_format($po->balance_amount, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div> -->

                        <div class="row mt-4 pt-3 d-none d-md-flex align-items-stretch">
                            <!-- LEFT SIDE -->
                            <div class="col-md-7 d-flex">
                                <div class="card-body w-100 d-flex flex-column">

                                    <h6 class="fw-bold text-primary mb-3">
                                        Terms & Conditions
                                    </h6>

                                    @if($po->terms_and_conditions)
                                    <div class="terms-content border rounded p-3 bg-light flex-grow-1">
                                        {!! $po->terms_and_conditions !!}
                                    </div>
                                    @endif

                                </div>
                            </div>

                            <!-- RIGHT SIDE (UNCHANGED TOTAL SECTION) -->
                            <div class="col-md-5 d-flex flex-column">
                                <div class="list-group list-group-flush">
                                    <div class="list-group-item d-flex justify-content-between border-0 px-0 py-1">
                                        <span class="text-muted">Taxable Subtotal</span>
                                        <span class="fw-bold">{{ number_format($po->subtotal_discount_amount, 2) }}</span>
                                    </div>

                                    <div class="list-group-item d-flex justify-content-between border-0 px-0 py-1">
                                        <span class="text-muted">Discount</span>
                                        <span class="fw-bold">{{ number_format($po->final_discount, 2) }}</span>
                                    </div>

                                    <div class="list-group-item d-flex justify-content-between border-0 px-0 py-1">
                                        <span class="text-muted">Remaining Amount</span>
                                        <span id="remaining_amount" class="text-success fw-bold">
                                            {{ number_format($remaining_amount, 2) }}
                                        </span>
                                    </div>
                                    @if($taxType !== 'Other')
                                    <div class="list-group-item d-flex justify-content-between border-0 px-0 py-1">
                                        <span class="text-muted">IGST Amount</span>
                                        <span class="text-success fw-bold">+{{ number_format($po->tax_amount, 2) }}</span>
                                    </div>
                                    @else
                                    <div class="list-group-item d-flex justify-content-between border-0 px-0 py-1">
                                        <span class="text-muted">CGST Amount</span>
                                        <span class="text-success fw-bold">+{{ number_format($po->tax_amount/2, 2) }}</span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between border-0 px-0 py-1">
                                        <span class="text-muted">SGST Amount</span>
                                        <span class="text-success fw-bold">+{{ number_format($po->tax_amount/2, 2) }}</span>
                                    </div>
                                    @endif


                                    <div class="list-group-item d-flex justify-content-between border-0 px-0 py-3 mt-2 border-top">
                                        <span class="h5 fw-bolder mb-0">Grand Total</span>
                                        <span id="grand_total" class="h5 fw-bolder mb-0 text-primary">{{ number_format($grandTotal, 2) }}</span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between border-0 px-0 py-1 mb-2">
                                        <span class="h5 fw-bolder mb-0">Advance Amount</span>
                                        <span id="advance_amount" class="h5 fw-bolder mb-0 text-success fw-bold">
                                            -{{ number_format($po->advance_amount, 2) }}
                                        </span>
                                    </div>

                                    <div class="list-group-item d-flex justify-content-between border-0 px-0 py-2 bg-dark text-white rounded px-3">
                                        <span class="fw-bold">Balance Due</span>
                                        <span id="balance_due" class="fw-bold">{{ number_format($po->balance_amount, 2) }}</span>
                                    </div>
                                </div>

                                <!-- CONTACT / REMARKS -->
                                <div class="p-3 border rounded mt-4">
                                    <p class="mb-1"><b>Dealing Person :</b> AUC</p>
                                    <p class="mb-2"><b>Contact No :</b> 8829920844</p>
                                    <hr>

                                    <p class="text-center mt-2 mb-5 fw-bold">
                                        For {{$po->firmData->name}}
                                    </p>

                                    <div style="height:90px;"></div>

                                    <p class="text-end">
                                        <b>AUTHORISED SIGNATORY</b>
                                    </p>
                                </div>
                            </div>

                        </div>

                        <!-- mobile version -->
                        <div class="d-block d-md-none mt-4 pt-3">

                            <!-- TOTAL FIRST -->
                            <div class="mb-4">

                                <div class="list-group list-group-flush">
                                    <div class="list-group-item d-flex justify-content-between border-0 px-0 py-1">
                                        <span class="text-muted">Taxable Subtotal</span>
                                        <span class="fw-bold">{{ number_format($po->subtotal_discount_amount, 2) }}</span>
                                    </div>

                                    <div class="list-group-item d-flex justify-content-between border-0 px-0 py-1">
                                        <span class="text-muted">Discount</span>
                                        <span class="fw-bold">{{ number_format($po->final_discount, 2) }}</span>
                                    </div>

                                    <div class="list-group-item d-flex justify-content-between border-0 px-0 py-1">
                                        <span class="text-muted">Remaining Amount</span>
                                        <span class="text-success fw-bold">
                                            {{ number_format($remaining_amount, 2) }}
                                        </span>
                                    </div>

                                    @if($taxType !== 'Other')
                                    <div class="list-group-item d-flex justify-content-between border-0 px-0 py-1">
                                        <span class="text-muted">IGST Amount</span>
                                        <span class="text-success fw-bold">+{{ number_format($po->tax_amount, 2) }}</span>
                                    </div>
                                    @else
                                    <div class="list-group-item d-flex justify-content-between border-0 px-0 py-1">
                                        <span class="text-muted">CGST Amount</span>
                                        <span class="text-success fw-bold">+{{ number_format($po->tax_amount/2, 2) }}</span>
                                    </div>

                                    <div class="list-group-item d-flex justify-content-between border-0 px-0 py-1">
                                        <span class="text-muted">SGST Amount</span>
                                        <span class="text-success fw-bold">+{{ number_format($po->tax_amount/2, 2) }}</span>
                                    </div>
                                    @endif

                                    <div class="list-group-item d-flex justify-content-between border-0 px-0 py-3 mt-2 border-top">
                                        <span class="h5 fw-bolder mb-0">Grand Total</span>
                                        <span class="h5 fw-bolder mb-0 text-primary">{{ number_format($grandTotal, 2) }}</span>
                                    </div>

                                    <div class="list-group-item d-flex justify-content-between border-0 px-0 py-1 mb-2">
                                        <span class="h5 fw-bolder mb-0">Advance Amount</span>
                                        <span class="h5 fw-bolder mb-0 text-success fw-bold">
                                            -{{ number_format($po->advance_amount, 2) }}
                                        </span>
                                    </div>

                                    <div class="list-group-item d-flex justify-content-between border-0 px-0 py-2 bg-dark text-white rounded px-3">
                                        <span class="fw-bold">Balance Due</span>
                                        <span class="fw-bold">{{ number_format($po->balance_amount, 2) }}</span>
                                    </div>
                                </div>

                            </div>


                            <!-- TERMS SECOND -->
                            <div class="card-body">

                                <h6 class="fw-bold text-primary mb-3">
                                    Terms & Conditions
                                </h6>
                                @if($po->terms_and_conditions)
                                <div class="terms-content border rounded p-3 bg-light">
                                    {!! $po->terms_and_conditions !!}
                                </div>
                                @endif
                            </div>


                            <!-- SIGN BOX LAST -->
                            <div class="p-3 border rounded">

                                <p class="mb-1"><b>Dealing Person :</b> AUC</p>
                                <p class="mb-2"><b>Contact No :</b> 8829920844</p>

                                <hr>

                                <p class="text-center mt-2 mb-5 fw-bold">
                                    For {{$po->firmData->name}}
                                </p>

                                <div style="height:90px;"></div>

                                <p class="text-end">
                                    <b>AUTHORISED SIGNATORY</b>
                                </p>

                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </div>

        <style>
            .bill-container {
                background: #fff;
                border-radius: 12px;
                border: 1px solid #dee2e6;
                overflow: hidden;
            }

            .text-primary {
                color: #2563eb !important;
            }

            .bg-light {
                background-color: #f8fafc !important;
            }

            .status-label {
                padding: 6px 16px;
                border-radius: 50px;
                font-size: 11px;
                font-weight: 800;
                border: 1px solid;
            }

            .status-draft {
                background: #fef3c7;
                color: #92400e;
                border-color: #fde68a;
            }

            .status-submitted {
                background: #e0f2fe;
                color: #075985;
                border-color: #bae6fd;
            }

            .status-approved {
                background: #dcfce7;
                color: #166534;
                border-color: #bbf7d0;
            }

            .tracking-widest {
                letter-spacing: 0.1em;
            }

            .terms-content {
                font-size: 14px;
                line-height: 1.6;
            }

            .terms-content p {
                margin-bottom: 8px;
            }

            .terms-content ul {
                padding-left: 18px;
                margin-bottom: 10px;
            }

            .terms-content li {
                margin-bottom: 5px;
            }

            .terms-content strong {
                color: #333;
            }

            .terms-content h1,
            .terms-content h2,
            .terms-content h3 {
                font-size: 16px;
                margin-top: 10px;
            }

            @media print {

                /* Poori screen ko hide karo */
                html,
                body * {
                    visibility: hidden;
                    margin: 0 !important;
                    padding: 0 !important;
                }

                .print-bg {
                    background: #c62828 !important;
                    color: #fff !important;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                /* Sirf bill container aur uske bachon ko dikhao */
                #printable-bill,
                #printable-bill * {
                    visibility: visible;
                }

                th:first-child,
                td:first-child {
                    width: 250px !important;
                    max-width: 250px !important;
                }

                #printable-bill {
                    position: absolute;
                    left: 0;
                    top: 0;
                    width: 100% !important;
                    border: none !important;
                    box-shadow: none !important;
                }

                /* Extra space htao */
                .d-print-none,
                .main-content {
                    padding: 0 !important;
                    margin: 0 !important;
                }

                .bill-header .row {
                    display: flex !important;
                    flex-wrap: nowrap !important;
                }

                .bill-header .col-md-6 {
                    width: 50% !important;
                    max-width: 50% !important;
                    flex: 0 0 50% !important;
                }

                .bill-header .border-end {
                    border-right: 1px solid #dee2e6 !important;
                }

                .bill-header .text-md-end {
                    text-align: right !important;
                }

                .supplier-box {
                    display: flex;
                    justify-content: start;
                    align-items: center;
                }


                @page {
                    size: A4;
                    margin: 0mm;
                }

                .bg-dark {
                    background-color: #1e293b !important;
                    color: white !important;
                    -webkit-print-color-adjust: exact;
                }

                .bg-light {
                    background-color: #f8fafc !important;
                    -webkit-print-color-adjust: exact;
                }
            }
        </style>

        <script>
        </script>
    @endsection