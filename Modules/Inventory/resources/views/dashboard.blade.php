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
                <form action="{{ route('dashboard') }}" method="GET">
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
            <div class="col-xxl-3 col-md-6">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between mb-4">
                            <div class="d-flex gap-4 align-items-center">
                                <div class="avatar-text avatar-lg bg-gray-200">
                                    <i class="feather-file-text"></i>
                                </div>
                                <div>
                                    <div class="fs-4 fw-bold text-dark text-center"><span
                                            class="counter">{{ $rs }}</span></div>
                                    <h3 class="fs-13 fw-semibold text-truncate-1-line">Total Requisition</h3>
                                </div>
                            </div>
                            
                        </div>
                        <div class="text-center">
                            <a href="/request-slip/view-all?status=Approved&rs_code=&user=&project="><span class="badge  bg-soft-info text-info">Approved : {{$approved_rs}}</span></a>
                            <a href="/request-slip/view-all?status=Approved&rs_code=&user=&project="><span class="badge  bg-soft-success text-success">Completed : {{$completed_issued}}</span></a>
                            <a href="/request-slip/view-all?status=Pending&rs_code=&user=&project="><span class="badge  bg-soft-warning text-warning">Pending : {{$pending_rs}}</span></a>
                            <a href="/request-slip/view-all?status=Rejected&rs_code=&user=&project="><span class="badge  bg-soft-danger text-danger">Rejected : {{$rejected_rs}}</span></a>
                            <a href="/request-slip/view-all?status=Hold&rs_code=&user=&project="><span class="badge  bg-soft-danger text-danger">Hold : {{$hold_rs}}</span></a>
                            <a href="/approval/admin"><span class="badge  bg-soft-warning text-warning">Exceed : {{$exceed_rs}}</span></a>
                        </div>
                        <div class="pt-4">
                            
                            <div class="d-flex align-items-center justify-content-between">
                                <a href="javascript:void(0);"
                                    class="fs-12 fw-medium text-muted text-truncate-1-line">Completed </a>
                                <div class="w-100 text-end">
                                    <span class="fs-12 text-dark">{{ $completed_issued }}</span>
                                    <span
                                        class="fs-11 text-muted">{{ $rs > 0 ? round(($completed_issued / $rs) * 100, 2) : 0 }}%</span>
                                </div>
                            </div>
                            <div class="progress mt-2 ht-3">
                                <div class="progress-bar bg-primary" role="progressbar"
                                    style="width: {{ $rs > 0 ? round(($completed_issued / $rs) * 100, 2) : 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [Invoices Awaiting Payment] end -->
            <!-- [Converted Leads] start -->
            <div class="col-xxl-3 col-md-6">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between mb-4">
                            <div class="d-flex gap-4 align-items-center">
                                <div class="avatar-text avatar-lg bg-gray-200">
                                    <i class="feather-cast"></i>
                                </div>
                                <div>
                                    <div class="fs-4 fw-bold text-dark"><span
                                            class="counter">{{$issue}}</span></div>
                                    <h3 class="fs-13 fw-semibold text-truncate-1-line">Issue Slips</h3>
                                </div>
                            </div>
                            <a href="javascript:void(0);" class="">
                                <i class="feather-more-vertical"></i>
                            </a>
                        </div>
                         <div class="text-start">
                            <a href="/issue?status=Issued&rs_code=&user=&project="><span class="badge  bg-soft-success text-success">Issued : {{$completed_issued}}</span></a>
                            <a href="/issue?status=Partially+Issued&rs_code=&user=&project="><span class="badge  bg-soft-warning text-warning">Partially Issued : {{$partially_issue}}</span></a>
                        </div>
                        <div class="pt-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <a href="javascript:void(0);"
                                    class="fs-12 fw-medium text-muted text-truncate-1-line">Completed Issue </a>
                                <div class="w-100 text-end">
                                    <span class="fs-12 text-dark">{{$completed_issued}}</span>
                                    <span
                                        class="fs-11 text-muted">{{ $issue > 0 ? round(($completed_issued / $issue) * 100, 2) : 0 }}%</span>
                                </div>
                            </div>
                            <div class="progress mt-2 ht-3">
                                <div class="progress-bar bg-warning" role="progressbar"
                                    style="width: {{ $issue > 0 ? round(($completed_issued / $issue) * 100, 2) : 0 }}%">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [Converted Leads] end -->
            <!-- [Projects In Progress] start -->
            <div class="col-xxl-3 col-md-6">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between mb-4">
                            <div class="d-flex gap-4 align-items-center">
                                <div class="avatar-text avatar-lg bg-gray-200">
                                    <i class="feather-briefcase"></i>
                                </div>
                                <div>
                                    <div class="fs-4 fw-bold text-dark"><span
                                            class="counter">{{$total_job_cart}}</span></div>
                                    <h3 class="fs-13 fw-semibold text-truncate-1-line">Job Cart</h3>
                                </div>
                            </div>
                            <a href="javascript:void(0);" class="">
                                <i class="feather-more-vertical"></i>
                            </a>
                        </div>
                        <div class="text-start">
                            <a href="/job_card/view?from_date=&to_date=&job_card_no=&vendor_id=&priority=&status=COMPLETED"><span class="badge  bg-soft-success text-success">Completed : {{$completed_job_cart}}</span></a>
                            <a href="/job_card/view?from_date=&to_date=&job_card_no=&vendor_id=&priority=&status=PENDING"><span class="badge  bg-soft-warning text-warning">Pending : {{$Pending_job_card}}</span></a>
                        </div>
                        <div class="pt-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <a href="javascript:void(0);"
                                    class="fs-12 fw-medium text-muted text-truncate-1-line">Job Cart </a>
                                <div class="w-100 text-end">
                                    <span class="fs-12 text-dark">{{$completed_job_cart}}</span>
                                    <span
                                        class="fs-11 text-muted">{{ $total_job_cart > 0 ? round(($completed_job_cart / $total_job_cart) * 100, 2) : 0 }}%</span>
                                </div>
                            </div>
                            <div class="progress mt-2 ht-3">
                                <div class="progress-bar bg-success" role="progressbar"
                                    style="width: {{ $total_job_cart > 0 ? round(($completed_job_cart / $total_job_cart) * 100, 2) : 0 }}%">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [Projects In Progress] end -->
            <!-- [Conversion Rate] start -->
            <div class="col-xxl-3 col-md-6">
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
            <!-- [Conversion Rate] end -->
            <div class="col-xxl-6 col-lg-6">
                <div class="card widget-tickets-content">
                    <div class="card-header">
                        <h5 class="card-title">Pending For Approval Purchase Order</h5>
                        <div class="card-header-action">
                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-gray-300 js-collapse-toggle"
                                data-bs-toggle="collapse" data-bs-target="#CARD_BODY_ID" aria-expanded="true"
                                aria-controls="CARD_BODY_ID">
                                <span class="collapse-icon">−</span>
                            </a>
                        </div>
                    </div>
                    <div id="CARD_BODY_ID" class="collapse show">
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive tickets-items-wrapper">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>PO Code</th>
                                            <th>Created By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recent_pending_po as $po)
                                            @php
                                                $date = \Carbon\Carbon::parse($po->created_at);
                                            @endphp

                                            <tr class="mb-1">
                                                <td>
                                                    <a href="javascript:void(0);">
                                                        {{ $po->po_number ?? 'N/A' }}
                                                    </a>
                                                </td>

                                                <td>
                                                    <a href="javascript:void(0);">
                                                        Po Code :
                                                        <b>{{ $po->po_number ?? 'N/A' }}</b>
                                                        <br>

                                                        <span class="fs-12 fw-normal text-muted">
                                                            Created By :
                                                            <b>{{ $po->creator?->name ?? 'System / Unknown' }}</b>
                                                        </span>
                                                    </a>

                                                    <p class="fs-12 text-muted text-truncate-1-line tickets-sort-desc">
                                                        <span class="badge bg-soft-warning text-warning ms-1">
                                                            Pending
                                                        </span>
                                                    </p>

                                                    <div class="tickets-list-action d-flex align-items-center gap-3">
                                                        <a href="{{ url('/purchase-order/' . $po->id . '/show') }}">View</a>
                                                        <span>|</span>

                                                        <form action="{{ route('purchase-order.status-update', $po->id) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            <input type="hidden" name="status" value="Approved">
                                                            <input type="hidden" name="remarks"
                                                                value="Approved from dashboard">
                                                            <button type="submit"
                                                                class="btn btn-link p-0 text-success fw-semibold">
                                                                Approve
                                                            </button>
                                                        </form>


                                                        <form action="{{ route('purchase-order.status-update', $po->id) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            <input type="hidden" name="status" value="Rejected">
                                                            <input type="hidden" name="remarks"
                                                                value="Rejected from dashboard">
                                                            <button type="submit"
                                                                class="btn btn-link p-0 text-danger fw-semibold">
                                                                Reject
                                                            </button>
                                                        </form>

                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>

                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-6 col-lg-6">
                <div class="card widget-tickets-content">
                    <div class="card-header">
                        <h5 class="card-title">Exceed Request Slips</h5>
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
                                            <th>Rs Code</th>
                                            <th>Project / Created By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recent_rs_existed as $rs)
                                                @php
                                                    $date = \Carbon\Carbon::parse($rs->created_on);
                                                @endphp
                                                <tr class="mb-1">
                                                    <td>
                                                        <a href="javascript:void(0);">{{ $rs->requisition_slip_no }}</a>
                                                    </td>
                                                    <td>
                                                        <a href="javascript:void(0);">Created By : {{ $rs->creator?->name ?? 'System / Unknown' }} <br>
                                                            <span class="fs-12 fw-normal text-muted"> Project :
                                                                <b>{{ $rs->project?->name ?? 'N/A' }}</b> </span> </a>
                                                        <p class="fs-12 text-muted text-truncate-1-line tickets-sort-desc"><span
                                                                class="badge bg-soft-danger text-danger ms-1">RS Exists</span>
                                                        </p>
                                                        <div class="tickets-list-action d-flex align-items-center gap-3">

                                                            <a href="/request-slip/{{ $rs->id }}">View</a>
                                                            <span>|</span>

                                                            <form action="{{ route('requisition.update-status', $rs->id) }}"
                                                                method="POST" class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="status" value="Approved">
                                                                <button type="submit"
                                                                    class="btn btn-link p-0 text-success fw-semibold">Approve</button>
                                                            </form>

                                                            <form action="{{ route('requisition.update-status', $rs->id) }}"
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
                        <h5 class="card-title">Latest Requisition Slip</h5>

                        <div class="card-header-action">
                            {{-- ✅ ONLY + / - toggle --}}
                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-gray-300 js-collapse-toggle"
                                data-bs-toggle="collapse" data-bs-target="#latest_rs_box" aria-expanded="true"
                                aria-controls="latest_rs_box">
                                <span class="collapse-icon">−</span>
                            </a>

                            {{-- ✅ dropdown same --}}
                           
                        </div>
                    </div>

                    {{-- ✅ collapse wrapper (body + footer inside) --}}
                    <div id="latest_rs_box" class="collapse show">
                        <div class="card-body">
                            @foreach($recent_rs as $rs)
                                @php
                                    $date = \Carbon\Carbon::parse($rs->created_on);
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
                                                <a href="request-slip/{{ $rs->id }}"
                                                    class="fw-bold mb-2 text-truncate-1-line">
                                                    Project <b>{{ $rs->project?->name ?? 'N/A' }}</b>
                                                </a>
                                                <span class="fs-11 fw-normal text-muted text-truncate-1-line">
                                                    Created By <b>{{ $rs->creator?->name ?? 'System / Unknown' }}</b>
                                                </span>
                                            </div>
                                        </div>

                                        <div
                                            class="img-group lh-0 ms-3 justify-content-start d-none d-sm-flex flex-column text-end">
                                            <span class="fw-semibold mb-1">
                                                {{ $rs->requisition_slip_no }}
                                            </span>

                                            @php
                                                $statusText = '';
                                                $statusClass = '';

                                                if ($rs->status == 'Approved') {
                                                    $statusText = 'Approved';
                                                    $statusClass = 'bg-soft-success text-success';
                                                }elseif($rs->status == 'Approved HOD'){
                                                    $statusText = 'Approved HOD';
                                                     $statusClass = 'bg-soft-success text-success';
                                                }
                                                 elseif ($rs->status == 'Rejected') {
                                                    $statusText = 'Rejected';
                                                    $statusClass = 'bg-soft-danger text-danger';
                                                } elseif ($rs->status == 'Hold') {
                                                    $statusText = 'On Hold';
                                                    $statusClass = 'bg-soft-warning text-warning';
                                                } elseif ($rs->status == 'Pending') {
                                                    $statusText = 'Pending';
                                                    $statusClass = 'bg-soft-warning text-warning';
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

                        <a href="{{route('request-slip.index')}}"
                            class="card-footer fs-11 fw-bold text-uppercase text-center py-4">
                            View All Requisition
                        </a>
                    </div>
                </div>
            </div>

            <div class=" col-lg-6">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h5 class="card-title">Latest Issue Slip</h5>

                        <div class="card-header-action">
                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-gray-300 js-collapse-toggle"
                                data-bs-toggle="collapse" data-bs-target="#latest_issue_box" aria-expanded="true"
                                aria-controls="latest_issue_box">
                                <span class="collapse-icon">−</span>
                            </a>

                           
                        </div>
                    </div>

                    <div id="latest_issue_box" class="collapse show">
                        <div class="card-body">
                            @foreach($recent_issues as $issue)
                                @php
                                    $date = \Carbon\Carbon::parse($issue->created_on);
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
                                                <a href="request-slip/{{ $issue->id }}"
                                                    class="fw-bold mb-2 text-truncate-1-line">
                                                    Project <b>{{ $issue->project?->name ?? 'N/A' }}</b>
                                                </a>
                                                <span class="fs-11 fw-normal text-muted text-truncate-1-line">
                                                    Created By <b>{{ $issue->creator?->name ?? 'System / Unknown' }}</b>
                                                </span>
                                            </div>
                                        </div>

                                        <div
                                            class="img-group lh-0 ms-3 justify-content-start d-none d-sm-flex flex-column text-end">
                                            <span class="fw-semibold mb-1">
                                                {{ $issue->issue_slip_no }}
                                            </span>

                                            @php
                                                $statusText = '';
                                                $statusClass = '';

                                                if ($issue->status == 'Issued') {
                                                    $statusText = 'Issued';
                                                    $statusClass = 'bg-soft-success text-success';
                                                } elseif ($issue->status == 'Partially Issued') {
                                                    $statusText = 'Partially Issued';
                                                    $statusClass = 'bg-soft-danger text-danger';
                                                } elseif ($issue->status == 'Hold') {
                                                    $statusText = 'On Hold';
                                                    $statusClass = 'bg-soft-warning text-warning';
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

                        <a href="{{route('issue.view-list')}}"
                            class="card-footer fs-11 fw-bold text-uppercase text-center py-4">
                            View All Issue
                        </a>
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

                                                if ($po->status == 'Approved') {
                                                    $statusText = 'Approved';
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

            <div class=" col-lg-6">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h5 class="card-title">Latest Purchase Request</h5>

                        <div class="card-header-action">
                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-gray-300 js-collapse-toggle"
                                data-bs-toggle="collapse" data-bs-target="#latest_pr_box" aria-expanded="true"
                                aria-controls="latest_pr_box">
                                <span class="collapse-icon">−</span>
                            </a>

                         
                        </div>
                    </div>

                    <div id="latest_pr_box" class="collapse show">
                        <div class="card-body">
                            @foreach($recent_pr as $pr)
                                @php
                                    $date = \Carbon\Carbon::parse($pr->created_on);
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
                                                <a href="purchase_request/{{ $pr->id }}/show-detail"
                                                    class="fw-bold mb-2 text-truncate-1-line">
                                                    Pr No : <b>{{ $pr->pr_no  }}</b>
                                                </a>
                                                <span class="fs-11 fw-normal text-muted text-truncate-1-line">
                                                    Created By <b>{{ $pr->creator?->name ?? 'System / Unknown' }}</b>
                                                </span>
                                            </div>
                                        </div>

                                        <div
                                            class="img-group lh-0 ms-3 justify-content-start d-none d-sm-flex flex-column text-end">
                                            <span class="fw-semibold mb-1">
                                                {{ $pr->satus }}
                                            </span>

                                            @php
                                                $statusText = '';
                                                $statusClass = '';

                                                if ($pr->status == 'APPROVED' || $pr->status == 'CLOSED') {
                                                    $statusText = $pr->status;
                                                    $statusClass = 'bg-soft-success text-success';
                                                } elseif ($pr->status == 'PARTIALLY_ORDEREDS' || $pr->status == 'DRAFT') {
                                                    $statusText = $pr->status;
                                                    $statusClass = 'bg-soft-warning text-warning';
                                                } elseif ($pr->status == 'SUBMITTED') {
                                                    $statusText = 'Submitted';
                                                    $statusClass = 'bg-soft-info text-info';
                                                } elseif ($pr->status == 'CANCELLED' || $pr->status == 'HOLD') {
                                                    $statusText = $pr->status;
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

                        <a href="/purchase_request/list-view"
                            class="card-footer fs-11 fw-bold text-uppercase text-center py-4">
                            View All PR
                        </a>
                    </div>
                </div>
            </div>


            <!-- [Payment Records] start -->
            <div class="col-xxl-4">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h5 class="card-title">Project Record</h5>
                        
                    </div>
                    <div class="card-body custom-card-action p-0">
                        <div id="payment-records-chart"></div>
                    </div>
                    <div class="card-footer">
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <div class="p-3 border border-dashed rounded">
                                    <div class="fs-12 text-muted mb-1">Created </div>
                                    <h6 class="fw-bold text-dark">{{ $projects_created_last6 }}</h6>
                                    <div class="progress mt-2 ht-3">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: 81%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="p-3 border border-dashed rounded">
                                    <div class="fs-12 text-muted mb-1">Completed </div>
                                    <h6 class="fw-bold text-dark">{{ $projects_completed_last6 }}</h6>
                                    <div class="progress mt-2 ht-3">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 82%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                          
                        </div>
                    </div>
                </div>
            </div>
            <!-- [Payment Records] end -->
            <!-- [Total Sales] start -->
            <div class="col-xxl-4">
                <div class="card stretch stretch-full overflow-hidden">
                    <div class="bg-primary text-white">
                        <div class="p-4">
                            <div class="text-start">
                                <h4 class="text-reset mb-1">
                                    ₹ {{ number_format($total_purchase_amount ?? 0, 2) }}
                                </h4>
                                <p class="text-reset m-0">Total Purchase (Approved)</p>
                            </div>

                            <div class="d-flex gap-3 mt-3 flex-wrap">
                                <div>
                                    <div class="fs-12 text-white-50">Paid</div>
                                    <div class="fw-bold">
                                        ₹ {{ number_format($paid_amount ?? 0, 2) }}
                                    </div>
                                </div>

                                <div class="ms-auto">
                                    <div class="fs-12 text-white-50 text-end">Balance / Pending</div>
                                    <div class="fw-bold text-end">
                                        ₹ {{ number_format($pending_amount ?? 0, 2) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="total-sales-color-graph"></div>
                    </div>

                    <div class="card-body">
                        <a
                            href="/purchase-order/view?from_date=&to_date=&po_no=&supplier_id=&status=Draft&ex_from_date=&ex_to_date=">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="fw-semibold text-dark">Draft POs</div>
                                    <div class="fs-12 text-muted">Not submitted</div>
                                </div>
                                <div class="fw-bold">{{ $po_draft_count ?? 0 }}</div>
                            </div>
                        </a>

                        <hr class="border-dashed my-3" />
                        <a
                            href="http://127.0.0.1:8000/purchase-order/view?from_date=&to_date=&po_no=&supplier_id=&status=Submitted&ex_from_date=&ex_to_date=">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="fw-semibold text-dark">Submitted POs</div>
                                    <div class="fs-12 text-muted">Pending approval</div>
                                </div>
                                <div class="fw-bold">{{ $po_submitted_count ?? 0 }}</div>
                            </div>
                        </a>
                        <hr class="border-dashed my-3" />

                        <a
                            href="http://127.0.0.1:8000/purchase-order/view?from_date=&to_date=&po_no=&supplier_id=&status=Submitted&ex_from_date=&ex_to_date=">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="fw-semibold text-dark">Approved POs</div>
                                    <div class="fs-12 text-muted">Finalized</div>
                                </div>
                                <div class="fw-bold">{{ $po_approved_count ?? 0 }}</div>
                            </div>
                        </a>
                    </div>

                    <a href="{{ url('/purchase-order') }}"
                        class="card-footer fs-11 fw-bold text-uppercase text-center py-4">
                        Full Details
                    </a>
                </div>
            </div>




            <div class="col-xxl-4">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h5 class="card-title">Project Progress</h5>
                    </div>

                    <div class="card-body custom-card-action">

                        @foreach($project_progress as $index => $p)
                            <div class="hstack justify-content-between border border-dashed rounded-3 p-3 mb-3">
                                <div class="hstack gap-3">
                                    <div>
                                        <a href="{{route('project.show', $p['id'])}}">{{ $p['name'] }}</a>
                                        <div class="fs-11 text-muted">
                                            Created By: {{ $p['user']['name'] }}
                                        </div>
                                    </div>
                                </div>

                                <div class="team-progress-{{ $index + 1 }}" data-progress="{{ $p['progress'] }}">
                                </div>
                            </div>
                        @endforeach

                    </div>

                    <a href="/project" class="card-footer fs-11 fw-bold text-uppercase text-center">
                        All Project
                    </a>
                </div>
            </div>

            <!--! END: [Team Progress] !-->
        </div>
    </div>
    <!-- [ Main Content ] end -->
    <script>
        window.poChartLabels = @json($po_chart_labels ?? []);
        window.poChartValues = @json($po_chart_values ?? []);
        window.poChartMeta = @json($po_chart_meta ?? []);
       window.payChartCategories = @json($payChartCategories ?? []);
    window.payChartSeries     = @json($payChartSeries ?? []);
    </script>
    


@endsection
  <script src="{{ asset('/assets/vendors/js/apexcharts.min.js') }}"></script>
    <script src="{{ asset('/assets/vendors/js/circle-progress.min.js') }}"></script>

    {{-- App Init JS --}}
    <script src="{{ asset('/assets/js/common-init.min.js') }}"></script>
    <script src="{{ asset('/assets/js/dashboard-init.min.js') }}"></script>
