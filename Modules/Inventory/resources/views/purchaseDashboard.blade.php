@extends('shared::layouts.app')
@section('content')
    <!-- [ page-header ] start -->
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Dashboard</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                <li class="breadcrumb-item">Dashboard</li>
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
                <form action="{{ route('employeeDashboard') }}" method="GET">
                    <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                        <div id="reportrange" class="reportrange-picker d-flex align-items-center">
                            <span class="reportrange-picker-field"></span>
                        </div>

                        {{-- ✅ Hidden inputs to send selected range --}}
                        <input type="hidden" name="from_date" id="from_date" value="{{ request('from_date') }}">
                        <input type="hidden" name="to_date" id="to_date" value="{{ request('to_date') }}">

                        <input class="btn btn-primary btn-sm px-3 py-2 fw-semibold" type="submit" value="Apply">
                    </div>
                </form>

            </div>
            <div class="d-md-none d-flex align-items-center">
                <a href="javascript:void(0)" class="page-header-right-open-toggle">
                    <i class="feather-align-right fs-20"></i>
                </a>
            </div>
        </div>
    </div>
    <!-- [ page-header ] end -->
    <!-- [ Main Content ] start -->
    <div class="main-content">
        <div class="row">
            <!-- [Invoices Awaiting Payment] start -->
          
            <!-- [Invoices Awaiting Payment] end -->
            <!-- [Converted Leads] start -->
        

            <div class="col-xxl-6 col-md-6">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between mb-4">
                            <div class="d-flex gap-4 align-items-center">
                                <div class="avatar-text avatar-lg bg-gray-200">
                                    <i class="feather-activity"></i>
                                </div>
                                <div>
                                    <div class="fs-4 fw-bold text-dark">{{ $purchase_submitted }}</div>
                                    <h3 class="fs-13 fw-semibold text-truncate-1-line">Purchase Request</h3>
                                </div>
                            </div>
                            <a href="javascript:void(0);" class="">
                                <i class="feather-more-vertical"></i>
                            </a>
                        </div>
                        <div class="text-start">
                        </div>
                        <div class="pt-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <a href="javascript:void(0);" class="fs-12 fw-medium text-muted text-truncate-1-line">
                                    Aprroved Pr </a>
                                <div class="w-100 text-end">
                                    <span class="fs-12 text-dark">{{ $purchase_apv }}</span>
                                    <span
                                        class="fs-11 text-muted">{{ $purchase_submitted > 0 ? round(($purchase_apv / $purchase_submitted) * 100, 2) : 0 }}%</span>
                                </div>
                            </div>
                            <div class="progress mt-2 ht-3">
                                <div class="progress-bar bg-danger" role="progressbar"
                                    style="width: {{ $purchase_submitted > 0 ? round(($purchase_apv / $purchase_submitted) * 100, 2) : 0 }}%">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-6 col-md-6">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between mb-4">
                            <div class="d-flex gap-4 align-items-center">
                                <div class="avatar-text avatar-lg bg-gray-200">
                                    <i class="feather-activity"></i>
                                </div>
                                <div>
                                    <div class="fs-4 fw-bold text-dark">{{ $total_po }}</div>
                                    <h3 class="fs-13 fw-semibold text-truncate-1-line">Purchase Order</h3>
                                </div>
                            </div>
                            <a href="javascript:void(0);" class="">
                                <i class="feather-more-vertical"></i>
                            </a>
                        </div>
                        <div class="text-start">
                            <a href="/purchase-order/view?from_date=&to_date=&po_no=&supplier_id=&status=Completed&ex_from_date=&ex_to_date="><span class="badge  bg-soft-success text-success">Received : {{ $completed_po }}</span></a>
                            <a href="/purchase-order/view?from_date=&to_date=&po_no=&supplier_id=&status=Partially+Received&ex_from_date=&ex_to_date="><span class="badge  bg-soft-warning text-warning">Partially Received : {{$po_pr}}</span></a>
                            <a href="/purchase-order/view?from_date=&to_date=&po_no=&supplier_id=&status=Submitted&ex_from_date=&ex_to_date="><span class="badge  bg-soft-info text-info">Submitted : {{$completed_submitted}}</span></a>
                            <a href="/purchase-order/view?from_date=&to_date=&po_no=&supplier_id=&status=Draft&ex_from_date=&ex_to_date="><span class="badge  bg-soft-warning text-warning">Draft : {{$completed_draft}}</span></a>
                            <a href="/purchase-order/view?from_date=&to_date=&po_no=&supplier_id=&status=Approved&ex_from_date=&ex_to_date="><span class="badge  bg-soft-success text-success">Approved : {{$completed_apv}}</span></a>
                        </div>
                        <div class="pt-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <a href="javascript:void(0);" class="fs-12 fw-medium text-muted text-truncate-1-line">
                                    Completed PO </a>
                                <div class="w-100 text-end">
                                    <span class="fs-12 text-dark">{{ $completed_po }}</span>
                                    <span
                                        class="fs-11 text-muted">{{ $total_po > 0 ? round(($completed_po / $total_po) * 100, 2) : 0 }}%</span>
                                </div>
                            </div>
                            <div class="progress mt-2 ht-3">
                                <div class="progress-bar bg-danger" role="progressbar"
                                    style="width: {{ $total_po > 0 ? round(($completed_po / $total_po) * 100, 2) : 0 }}%">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- [Projects In Progress] end -->
            <!-- [Conversion Rate] start -->

            <!-- [Conversion Rate] end -->


             <!-- grn -->
          
            <!-- recent pr -->

             <div class="col-xxl-6 col-lg-6">
                <div class="card widget-tickets-content">
                    <div class="card-header">
                        <h5 class="card-title">Latest Purchase Request</h5>
                        <div class="card-header-action">
                            <div class="card-header-action">
                                <a href="javascript:void(0);"
                                    class="avatar-text avatar-xs bg-gray-300 js-collapse-toggle"
                                    data-bs-toggle="collapse" data-bs-target="#existed_rs_table" aria-expanded="true"
                                    aria-controls="existed_rs_table">
                                    <span class="collapse-icon">−</span>
                                </a>
                            </div>

                            
                        </div>
                    </div>
                    <div id="existed_rs_table" class="collapse show">

                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive tickets-items-wrapper">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Pr Code</th>
                                            <th>Project / Created By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recent_pr as $pr)
                                                @php
                                                    $date = \Carbon\Carbon::parse($pr->created_on);
                                                @endphp
                                                <tr class="mb-1">
                                                    <td>
                                                        <a href="javascript:void(0);">{{ $pr->pr_no }}</a>
                                                    </td>
                                                    <td>
                                                        <a href="javascript:void(0);">Created By : {{ $pr->creator?->name ?? 'System / Unknown' }} </a>
                                                        <p class="fs-12 text-muted text-truncate-1-line tickets-sort-desc"><span
                                                                class="badge bg-soft-info text-info ms-1">Submitted</span>
                                                        </p>
                                                        <div class="tickets-list-action d-flex align-items-center gap-3">

                                                            <a href="{{ route('purchase_request.show-detail', $pr->id) }}">View</a>
                                                            <span>|</span>

                                                            <form action="{{ route('purchase_request.status-update', $pr->id) }}"
                                                                method="POST" class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="status" value="Approved">
                                                                <button type="submit"
                                                                    class="btn btn-link p-0 text-success fw-semibold">Approve</button>
                                                            </form>

                                                            <form action="{{ route('purchase_request.status-update', $pr->id) }}"
                                                                method="POST" class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="status" value="Rejected">
                                                                <button type="submit"
                                                                    class="btn btn-link p-0 text-danger fw-semibold">Reject</button>
                                                            </form>
                                                        </div>

                                                    </td>
                                                </tr>
                                            </tbody>
                                        @endforeach
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

             <div class=" col-lg-6">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h5 class="card-title">Latest Purchase Order</h5>

                        <div class="card-header-action">
                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-gray-300 js-collapse-toggle"
                                data-bs-toggle="collapse" data-bs-target="#latest_po_box" aria-expanded="true"
                                aria-controls="latest_po_box">
                                <span class="collapse-icon">−</span>
                            </a>

                          
                        </div>
                    </div>

                    <div id="latest_po_box" class="collapse show">
                        <div class="card-body">
                            @foreach($recent_po as $po)
                                @php
                                    $date = \Carbon\Carbon::parse($po->created_on);
                                @endphp

                                <div class="p-3 border border-dashed rounded-3 mb-3">
                                    <div class="d-flex justify-content-between">
                                        <div class="d-flex align-items-center gap-3">

                                            <div
                                                class="wd-50 ht-50 bg-soft-primary text-primary lh-1 d-flex align-items-center justify-content-center flex-column rounded-2 schedule-date">
                                                <span class="fs-18 fw-bold mb-1 d-block">
                                                    {{ $date->format('d') }}
                                                </span>
                                                <span class="fs-10 fw-semibold text-uppercase d-block">
                                                    {{ $date->format('M') }}
                                                </span>
                                            </div>

                                            <div class="text-dark">
                                                <a href="/purchase-order/{{ $po->id }}/show"
                                                    class="fw-bold mb-2 text-truncate-1-line">
                                                    PO No : <b>{{ $po->po_number }}</b>
                                                </a>
                                                <span class="fs-11 fw-normal text-muted text-truncate-1-line">
                                                    Created By <b>{{ $po->creator?->name ?? 'System / Unknown' }}</b>
                                                </span>
                                            </div>
                                        </div>

                                        <div
                                            class="img-group lh-0 ms-3 justify-content-start d-none d-sm-flex flex-column text-end">
                                            <span class="fw-semibold mb-1">
                                                {{ $po->satus }}
                                            </span>

                                            @php
                                                $statusText = '';
                                                $statusClass = '';

                                                if ($po->status == 'Approved' || $po->status == 'Completed') {
                                                    $statusText = $po->status;
                                                    $statusClass = 'bg-soft-success text-success';
                                                } elseif ($po->status == 'Partially Received' || $po->status == 'Draft') {
                                                    $statusText = $po->status;
                                                    $statusClass = 'bg-soft-warning text-warning';
                                                } elseif ($po->status == 'Submitted') {
                                                    $statusText = 'Submitted';
                                                    $statusClass = 'bg-soft-info text-info';
                                                } elseif ($po->status == 'Cancelled') {
                                                    $statusText = 'Cancelled';
                                                    $statusClass = 'bg-soft-danger text-danger';
                                                }
                                            @endphp

                                            <span class="badge {{ $statusClass }}">
                                                {{ $statusText }}
                                            </span>

                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <a href="/purchase-order/view"
                            class="card-footer fs-11 fw-bold text-uppercase text-center py-4">
                            View All PO
                        </a>
                    </div>
                </div>
            </div>

            <!-- [Payment Records] start -->
           
            <!-- [Payment Records] end -->
            <!-- [Total Sales] start -->
            



            

            <!--! END: [Team Progress] !-->
        </div>
    </div>
    <!-- [ Main Content ] end -->
    <script>
        window.poChartLabels = @json($po_chart_labels ?? []);
        window.poChartValues = @json($po_chart_values ?? []);
        window.poChartMeta = @json($po_chart_meta ?? []);
        window.payChartCategories = @json($payChartCategories ?? []);
        window.payChartSeries = @json($payChartSeries ?? []);
    </script>



@endsection
<script src="{{ asset('/assets/vendors/js/apexcharts.min.js') }}"></script>
<script src="{{ asset('/assets/vendors/js/circle-progress.min.js') }}"></script>

{{-- App Init JS --}}
<script src="{{ asset('/assets/js/common-init.min.js') }}"></script>
<script src="{{ asset('/assets/js/dashboard-init.min.js') }}"></script>