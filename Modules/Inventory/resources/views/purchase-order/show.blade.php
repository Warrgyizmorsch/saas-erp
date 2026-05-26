 @extends('shared::layouts.app')
@section('content')
     @php
     $taxType = $po->items->first()->tax_type ?? null;

     $remaining_amount = $po->subtotal_discount_amount - $po->final_discount;
     $grandTotal = $po->subtotal_discount_amount - $po->final_discount + $po->tax_amount;
     $hasDiscount = $po->items->sum('discount') > 0;
     @endphp

     <div class="main-content container-lg">
         <div class="row">
             <div class="col-lg-12">
                 <div class="card invoice-container" id="printableInvoice">
                     <div class="card-header" style="background-color: {{ strtolower($po->firm) == '1' ? '#861b1b' : '#3454d1' }};">
                         <div class="d-flex align-items-center">
                             <img src="{{ asset($po->firmData->logo) }}"
                                 class="img-fluid me-3"
                                 style="height:80px;">
                             <div class="fs-24 fw-bolder text-white  text-uppercase firm-name">

                                 {{ $po->firmData->name }}
                             </div>
                         </div>

                         <div class="d-flex flex-column align-items-center justify-content-center">
                             <div class="d-flex justify-content-end mb-2 w-100 download-hide">
                                 <a href="javascript:void(0)" class="d-flex me-1" id="downloadBTN">
                                     <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover" title="Download PDF">
                                         <i class="feather feather-download"></i>
                                     </div>
                                 </a>
                                 <a href="javascript:void(0)" class="d-flex me-1 printBTN">
                                     <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover" title="Print Invoice"><i class="feather feather-printer"></i></div>
                                 </a>
                             </div>
                             <div>
                                 <span class="fw-bold text-white">Po No. :</span>
                                 <span class=" text-white">{{ $po->po_number }} </span>
                             </div>
                             <div class="text-start w-100">
                                 <span class="fw-bold text-white">Date :</span>
                                 <span class=" text-white">{{ \Carbon\Carbon::parse($po->po_date)->format('d F Y') }}</span>
                             </div>
                         </div>
                     </div>
                     <div class="card-body p-0">
                         <div class="px-4 pt-4">
                             <div class="d-flex justify-content-center fs-16 fw-bold">
                                 <h4> Purchase Order </h4>
                             </div>
                             <div class="d-sm-flex align-items-start justify-content-between">
                                 <div>
                                     <h2 class="fs-6 fw-bold ">{{ $po->firmData->name }}</h2>

                                     <address class="text-muted">
                                         {!! preg_replace('/,/', ',<br>', $po->firmData->address, 1) !!}</br>
                                         <strong>Tel :</strong> {{ $po->firmData->phone }}</br>
                                         <strong>Website :</strong> {{ $po->firmData->website }}</br>
                                         <strong>Email :</strong> {{ $po->firmData->email }}</br>
                                         <div class="fw-bold">
                                             GST : {{ $po->firmData->gst_no }}
                                         </div>
                                     </address>

                                 </div>
                                 <div class="lh-lg pt-3 pt-sm-0">
                                     <h2 class="fs-4 fw-bold text-primary">Supplier</h2>
                                     <h2 class="fs-6 fw-bold ">{{ $po->supplier?->supplier_name }}</h2>
                                     <div class="text-muted lh-sm">
                                         @if(!empty($po->supplier?->supplier_address))
                                         @php
                                         $words = explode(' ', $po->supplier->supplier_address);
                                         $chunks = array_chunk($words, 6);
                                         @endphp

                                         @foreach($chunks as $chunk)
                                         {{ implode(' ', $chunk) }} <br>
                                         @endforeach
                                         @endif
                                     </div>
                                     <div class="lh-base">
                                         @if(!empty($po->supplier?->supplier_code))
                                         <span class="fw-bold text-dark">Vendor Code :</span>
                                         <span class="text-muted">{{ $po->supplier->supplier_code }}</span>
                                         @endif
                                     </div>
                                     <div class="lh-base">
                                         @if(!empty($po->supplier?->mobile))
                                         <span class="fw-bold text-dark">Contact :</span>
                                         <span class="text-muted">{{ $po->supplier->mobile }}</span>
                                         @endif
                                     </div>
                                     <div class="lh-base">
                                         @if(!empty($po->supplier?->email))
                                         <span class="fw-bold text-dark">Email :</span>
                                         <span class="text-muted">{{ $po->supplier->email }} <br></span>
                                         @endif
                                     </div>
                                     <div class="lh-base">
                                         @if(!empty($po->supplier?->account_number))
                                         <span class="fw-bold text-dark">Account No :</span>
                                         <span class="text-muted">{{ $po->supplier->account_number }} <br></span>
                                         @endif
                                     </div>
                                     <div class="lh-base">
                                         @if(!empty($po->supplier?->gstin))
                                         <span class="fw-bold text-dark">GSTIN :</span>
                                         <span class="text-muted">{{ $po->supplier->gstin }} <br></span>
                                         @endif
                                     </div>
                                     <div class="lh-base">
                                         @if(!empty($po->supplier?->pan))
                                         <span class="fw-bold text-dark">PAN :</span>
                                         <span class="text-muted">{{ $po->supplier->pan }}</span>
                                         @endif
                                     </div>
                                 </div>
                             </div>
                         </div>

                         <hr class="border-dashed mb-0">
                         <div class="table-responsive">
                             <table class="table">
                                 <thead>
                                     <tr>
                                         <th class="text-start" style="max-width:120px">Item Description</th>
                                         <th class="text-start">Qty</th>
                                         <th class="text-start">Unit</th>
                                         <th class="text-start">Price</th>
                                         <th class="text-start">Taxable</th>
                                         @if($hasDiscount)<th>Sub Total</th>@endif
                                         <th>Tax %</th>
                                         <th>Total</th>

                                     </tr>
                                 </thead>
                                 <tbody>
                                     @foreach($po->items as $item)
                                     <tr>
                                         <td style="white-space: normal; word-break: break-word;" style="font-size:12px;"> {{ $item->inventory->name }}</br>
                                             @if(!empty($item->hsn))
                                             <span class="fw-bold text-dark " style="font-size:12px;">HSN No:</span>
                                             <span style="font-size:12px;">{{ $item->hsn }}</span></br>
                                             @endif
                                             @if(!empty($item->item_not))
                                             <span class="fw-bold text-dark " style="font-size:12px;">Not:</span>
                                             <span class="text-muted" style="font-size: 12px; white-space: normal; word-break: break-word;">{{ $item->item_not }}</span>
                                             @endif
                                         </td>
                                         <td style="font-size:12px;">{{ $item->ordered_qty }}</td>
                                         <td style="font-size:12px;">{{ $item->inventory->unit }}</td>
                                         <td style="font-size:12px;">{{ number_format($item->unit_price, 2) }}</td>
                                         <td class="text-dark fw-semibold" style="font-size:12px;">{{ number_format($item->taxable_total, 2) }}</td>
                                         @if($hasDiscount)
                                         <td>
                                             @if((float)$item->discount > 0)
                                             <span class="fs-12">({{ (float)$item->discount }}%) </span>
                                             @endif
                                             <span class="fs-12">{{ number_format($item->discount_amount, 2) }}</span>
                                         </td>
                                         @endif
                                         @if($item->tax_type !== 'Other')
                                         <td style="font-size:12px;">({{(float) $item->tax_percent }}%) <span style="font-size:12px;">{{number_format($item->tax_amount, 2)}}</span></td>
                                         @else
                                         <td>
                                             <div> <span style="font-size:12px;">SGST: ({{ (float)$item->tax_percent / 2 }}%)</span> <span style="font-size:12px;">{{number_format($item->tax_amount/2, 2)}}</span></div>
                                             <div> <span style="font-size:12px;">CGST: ({{ (float)$item->tax_percent / 2 }}%) </span> <span style="font-size:12px;">{{number_format($item->tax_amount/2, 2)}}</span> </div>
                                         </td>
                                         @endif
                                         <td style="font-size:12px;">{{ number_format($item->line_total, 2) }}</td>
                                     </tr>
                                     @endforeach

                                 </tbody>
                             </table>
                         </div>
                         <div class="row px-4 pt-4">

                             <!-- LEFT SIDE: TERMS -->
                             <div class="col-6">
                                 <h6 class="fs-13 fw-bold mb-3">Terms & Condition :</h6>
                                 @if($po->terms_and_conditions)
                                 <div class="list-unstyled lh-lg fs-12 term">
                                     {!! $po->terms_and_conditions !!}
                                 </div>
                                 @endif
                             </div>

                             <!-- RIGHT SIDE: TOTALS -->
                             <div class="col-6">
                                 <table class="table table-borderless">

                                     <tr>
                                         <td class="text-end fw-semibold" style="padding: 1px;">Taxable Subtotal</td>
                                         <td class="text-end fw-bold" style="padding: 1px;">{{ number_format($po->subtotal_discount_amount, 2) }}</td>
                                     </tr>
                                     <tr>
                                         <td class="text-end fw-semibold" style="padding: 1px;">Loading Cutting Charges</td>
                                         <td class="text-end fw-bold" style="padding: 1px;">{{ number_format($po->loading_cutting_charges, 2) }}</td>
                                     </tr>
                                     <tr>
                                         <td class="text-end fw-semibold" style="padding: 1px;">Freight Charges</td>
                                         <td class="text-end fw-bold" style="padding: 1px;">{{ number_format($po->freight_charges, 2) }}</td>
                                     </tr>

                                     @if($taxType !== 'Other')
                                     <tr>
                                         <td class="text-end fw-semibold" style="padding: 1px;">IGST</td>
                                         <td class="text-end fw-bold" style="padding: 1px;">
                                             + {{ number_format($po->tax_amount, 2) }}
                                         </td>
                                     </tr>
                                     @else
                                     <tr>
                                         <td class="text-end fw-semibold" style="padding: 1px;">CGST</td>
                                         <td class="text-end fw-bold " style="padding: 1px;">
                                             + {{ number_format($po->tax_amount / 2, 2) }}
                                         </td>
                                     </tr>

                                     <tr>
                                         <td class="text-end fw-semibold" style="padding: 1px;">SGST</td>
                                         <td class="text-end fw-bold " style="padding: 1px;">
                                             + {{ number_format($po->tax_amount / 2, 2) }}
                                         </td>
                                     </tr>
                                     @endif

                                     <tr>
                                         <td class="text-end fw-semibold" style="padding: 1px;">Grand Total</td>
                                         <td class="text-end fw-bold " style="padding: 1px;">
                                             {{ number_format($po->remaining_amount, 2) }}
                                         </td>
                                     </tr>

                                     <tr>
                                         <td class="text-end fw-semibold" style="padding: 1px;">Discount</td>
                                         <td class="text-end fw-bold " style="padding: 1px;">
                                             -{{ number_format($po->final_discount, 2) }}
                                         </td>
                                     </tr>

                                     <tr>
                                         <td class="text-end fw-semibold" style="padding: 1px;">Advance</td>
                                         <td class="text-end fw-bold" style="padding: 1px;">
                                             -{{ number_format($po->advance_amount, 2) }}
                                         </td>
                                     </tr>

                                     <tr>
                                         <td class="text-end fw-semibold" style="padding: 1px;">Balance Due</td>
                                         <td class="text-end fw-bold" style="padding: 1px;">
                                             {{ number_format($po->balance_amount, 2) }}
                                         </td>
                                     </tr>

                                 </table>
                             </div>

                         </div>
                         <hr class="border-dashed mt-0">

                         <div class="px-4  d-sm-flex  justify-content-between " style="padding-top: 80px;">
                             <div>
                                 <p class="mb-1"><b class="text-muted">Dealing Person :</b> AUC</p>
                                 <p class="mb-2"><b>Contact No :</b> 8829920844</p>
                             </div>
                             <div class="d-flex  align-items-end fs-13 fw-bold text-black mb-1">
                                 AUTHORISED SIGNATORY
                             </div>
                         </div>
                     </div>
                 </div>
             </div>
         </div>
     </div>
     <style>
         .term p {
             margin-bottom: 1px;
         }

         .table-responsive .table tr td {
             padding: 7px 7px;
         }

         .table-responsive .table tfoot th,
         .table-responsive .table thead th {
             padding: 8px 3px;
         }

         @media (max-width: 768px) {
             .firm-name {
                 font-size: 14px;
             }
         }

         @media print {
             body * {
                 visibility: hidden;
             }

             .invoice-container,
             .invoice-container * {
                 visibility: visible;
             }

             .invoice-container {
                 position: static !important;
                 left: 0;
                 top: 0;
                 width: 100%;
             }

             .card-header {
                 color: #fff !important;
                 -webkit-print-color-adjust: exact;
                 print-color-adjust: exact;
             }

             .firm-name {
                 font-size: 28px;
             }

             .no-pdf {
                 display: none !important;
             }

             #printableInvoice table td,
             #printableInvoice table th {
                 font-size: 12px !important;
             }

             .firm-name {
                 -webkit-print-color-adjust: exact;
             }
         }

         @media print {

             .printBTN,
             #downloadBTN {
                 display: none !important;
             }

             #printableInvoice table th:nth-child(1),
             #printableInvoice table td:nth-child(1) {
                 width: 24%;
             }
         }

         #printableInvoice {
             width: 100%;
             max-width: 800px;
             /* A4 ke andar fit */
             margin: auto;
         }

         table {
             width: 100% !important;
             /* table-layout: fixed; */
         }

         td,
         th {
             white-space: normal !important;
             word-break: break-word;
             font-size: 11px;
         }

         #printableInvoice table th:nth-child(1),
         #printableInvoice table td:nth-child(1) {
             width: 24%;
         }

         #printableInvoice table th:nth-child(2),
         #printableInvoice table td:nth-child(2) {
             width: 8%;
         }

         #printableInvoice table th:nth-child(3),
         #printableInvoice table td:nth-child(3) {
             width: 8%;
         }

         #printableInvoice table th:nth-child(4),
         #printableInvoice table td:nth-child(4) {
             width: 10%;
         }

         #printableInvoice table th:nth-child(5),
         #printableInvoice table td:nth-child(5),
         #printableInvoice table th:nth-child(6),
         #printableInvoice table td:nth-child(6),
         #printableInvoice table th:nth-child(7),
         #printableInvoice table td:nth-child(7),
         #printableInvoice table th:nth-child(8),
         #printableInvoice table td:nth-child(8) {
             width: 11%;
         }

         #printableInvoice th {
             white-space: normal !important;
             word-break: keep-all;
             /* 🔥 words ko beech me todne se rokega */
             text-align: center;
             font-size: 11px;
         }
     </style>
     <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
     <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
     <script src="{{ asset('assets/vendors/js/jquery.print.min.js') }}"></script>
     <script>
         $(document).ready(function() {
             $('.printBTN').click(function() {
                 window.print();
             });

             $('#downloadBTN').click(function() {
                 const element = document.getElementById('printableInvoice');

                 const opt = {
                     margin: [0.3, 0.3, 0.3, 0.3],
                     filename: 'PO_{{ $po->po_number }}.pdf',
                     image: {
                         type: 'jpeg',
                         quality: 0.98
                     },
                     html2canvas: {
                         scale: 1.5,
                         useCORS: true,
                         // windowWidth: 1200,
                         ignoreElements: (el) => el.classList.contains('download-hide')
                     },
                     jsPDF: {
                         unit: 'in',
                         format: 'a4',
                         orientation: 'portrait'
                     }
                 };

                 html2pdf().set(opt).from(element).save();
             });
         });
     </script>
 @endsection