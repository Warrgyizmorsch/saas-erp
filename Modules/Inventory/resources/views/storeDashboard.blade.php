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
            <div class="col-xxl-4 col-md-6">
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
                            <a href="/request-slip/view-all?status=Pending&rs_code=&user=&project="><span class="badge  bg-soft-warning text-warning">Pending : {{$pending_rs}}</span></a>
                            <a href="/request-slip/view-all?status=Rejected&rs_code=&user=&project="><span class="badge  bg-soft-danger text-danger">Rejected : {{$rejected_rs}}</span></a>
                            <a href="/request-slip/view-all?status=Hold&rs_code=&user=&project="><span class="badge  bg-soft-danger text-danger">Hold : {{$hold_rs}}</span></a>
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
            <div class="col-xxl-4 col-md-6">
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
                            <a href="/issue/view-list?from_date=&to_date=&issue_no=&project_id=&req_no=&status=Issued"><span class="badge  bg-soft-success text-success">Issued : {{$completed_issued}}</span></a>
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

            <div class="col-xxl-4 col-md-6">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between mb-4">
                            <div class="d-flex gap-4 align-items-center">
                                <div class="avatar-text avatar-lg bg-gray-200">
                                    <i class="feather-activity"></i>
                                </div>
                                <div>
                                    <div class="fs-4 fw-bold text-dark">{{ $total_grn }}</div>
                                    <h3 class="fs-13 fw-semibold text-truncate-1-line">Grn</h3>
                                </div>
                            </div>
                            <a href="javascript:void(0);" class="">
                                <i class="feather-more-vertical"></i>
                            </a>
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

            <div class="col-xxl-6 col-md-6">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between mb-4">
                            <div class="d-flex gap-4 align-items-center">
                                <div class="avatar-text avatar-lg bg-gray-200">
                                    <i class="feather-activity"></i>
                                </div>
                                <div>
                                    <div class="fs-4 fw-bold text-dark">{{ $total_pr }}</div>
                                    <h3 class="fs-13 fw-semibold text-truncate-1-line">Purchase Request</h3>
                                </div>
                            </div>
                            <a href="javascript:void(0);" class="">
                                <i class="feather-more-vertical"></i>
                            </a>
                        </div>
                        <div class="text-start">
                            <a href="/purchase_request/list-view?from_date=&to_date=&pr_no=&priority=&status=ORDERED"><span class="badge  bg-soft-success text-success">Ordered : {{ $purchase_order }}</span></a>
                            <a href="/purchase_request/list-view?from_date=&to_date=&pr_no=&priority=&status=PARTIALLY_ORDERED"><span class="badge  bg-soft-warning text-warning">Partially Ordered : {{$purchase_par_order}}</span></a>
                            <a href="/purchase_request/list-view?from_date=&to_date=&pr_no=&priority=&status=SUBMITTED"><span class="badge  bg-soft-info text-info">Submitted : {{$purchase_submitted}}</span></a>
                            <a href="/purchase_request/list-view?from_date=&to_date=&pr_no=&priority=&status=DRAFT"><span class="badge  bg-soft-warning text-warning">Draft : {{$purchase_draft}}</span></a>
                            <a href="/purchase_request/list-view?from_date=&to_date=&pr_no=&priority=&status=APPROVED"><span class="badge  bg-soft-success text-success">Approved : {{$purchase_apv}}</span></a>
                            <a href="/purchase_request/list-view?from_date=&to_date=&pr_no=&priority=&status=HOLD"><span class="badge  bg-soft-danger text-danger">Hold : {{$purchase_hold}}</span></a>
                            <a href="/purchase_request/list-view?from_date=&to_date=&pr_no=&priority=&status=REJECTED"><span class="badge  bg-soft-danger text-danger">Rejected : {{$purchase_rejected}}</span></a>
                        </div>
                        <div class="pt-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <a href="javascript:void(0);" class="fs-12 fw-medium text-muted text-truncate-1-line">
                                    Completed Pr </a>
                                <div class="w-100 text-end">
                                    <span class="fs-12 text-dark">{{ $purchase_order }}</span>
                                    <span
                                        class="fs-11 text-muted">{{ $total_pr > 0 ? round(($purchase_order / $total_pr) * 100, 2) : 0 }}%</span>
                                </div>
                            </div>
                            <div class="progress mt-2 ht-3">
                                <div class="progress-bar bg-danger" role="progressbar"
                                    style="width: {{ $total_pr > 0 ? round(($purchase_order / $total_pr) * 100, 2) : 0 }}%">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            <!-- [Projects In Progress] end -->
            <!-- [Conversion Rate] start -->

            <!-- [Conversion Rate] end -->


             <div class="col-xxl-6 col-lg-6">
                <div class="card widget-tickets-content">
                    <div class="card-header">
                        <h5 class="card-title">Pending For Approval Requisition Slip</h5>
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
                                            <th>Rs Code</th>
                                            <th>Created By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recent_pending_rs as $rs)
                                        @php
                                        $date = \Carbon\Carbon::parse($rs->created_at);
                                        @endphp

                                        <tr class="mb-1">
                                            <td>
                                                <a href="javascript:void(0);">
                                                    {{ $rs->requisition_slip_no ?? 'N/A' }}
                                                </a>
                                            </td>

                                            <td>
                                                <a href="javascript:void(0);">
                                                    Rs Code :
                                                    <b>{{ $rs->requisition_slip_no ?? 'N/A' }}</b>
                                                    <br>

                                                    <span class="fs-12 fw-normal text-muted">
                                                        Created By :
                                                        <b>{{ $rs->creator?->name ?? 'System / Unknown' }}</b>
                                                    </span>
                                                </a>

                                                <p class="fs-12 text-muted text-truncate-1-line tickets-sort-desc">
                                                    <span class="badge bg-soft-warning text-warning ms-1">
                                                        {{$rs->status}}
                                                    </span>
                                                </p>

                                                <div class="tickets-list-action d-flex align-items-center gap-3">
                                                    <a href="{{ route('request-slip.show' , $rs->id ) }}">View</a>
                                                    <span>|</span>

                                                    <form action="{{ route('requisition.update-status', $rs->id) }}"
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


                                                    <form action="{{ route('requisition.update-status', $rs->id) }}"
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
                        <h5 class="card-title">Latest Job Carts</h5>

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
                            @foreach($recent_job_carts as $jc)
                            @php
                            $date = \Carbon\Carbon::parse($jc->created_at);
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
                                            <a href="{{route('job_card.show', $jc->id)}}"
                                                class="fw-bold mb-2 text-truncate-1-line">
                                                job Cart No : <b>{{ $jc->job_card_no  }}</b>
                                            </a>
                                        </div>
                                    </div>

                                    <div
                                        class="img-group lh-0 ms-3 justify-content-start d-none d-sm-flex flex-column text-end">
                                        <span class="fw-semibold mb-1">
                                            {{ $jc->satus }}
                                        </span>

                                        @php
                                        $statusText = '';
                                        $statusClass = '';

                                        if ($jc->status == 'COMPLETED') {
                                        $statusText = $jc->status;
                                        $statusClass = 'bg-soft-success text-success';
                                        } elseif ($jc->status == 'PENDING') {
                                        $statusText = $jc->status;
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

                        <a href="{{route('job_card.view')}}"
                            class="card-footer fs-11 fw-bold text-uppercase text-center py-4">
                            View All job cart
                        </a>
                    </div>
                </div>
            </div>

             <!-- grn -->
            <div class=" col-lg-6">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h5 class="card-title">Latest Pending Grn</h5>

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
                            @foreach($recent_pending_grn as $grn)
                            @php
                            $date = \Carbon\Carbon::parse($grn->created_at);
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
                                            <a href="{{route('grn.create', ['po_id' => $grn->id])}}"
                                                class="fw-bold mb-2 text-truncate-1-line">
                                                Po Number : <b>{{ $grn->po_number  }}</b>
                                            </a>
                                            <span class="fs-11 fw-normal text-muted text-truncate-1-line">
                                                Created By <b>{{ $grn->creator?->name ?? 'System / Unknown' }}</b>
                                            </span>
                                        </div>
                                    </div>

                                    <div
                                        class="img-group lh-0 ms-3 justify-content-start d-none d-sm-flex flex-column text-end">
                                        <span class="fw-semibold mb-1">
                                        </span>
                                        <span class="badge bg-soft-warning text-warning">
                                             Pending
                                        </span>

                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <a href="/grn"
                            class="card-footer fs-11 fw-bold text-uppercase text-center py-4">
                            View All pending grn 
                        </a>
                    </div>
                </div>
            </div>

            <!-- recent pr -->

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

                                                if ($pr->status == 'APPROVED' || $pr->status == 'CLOSED'|| $pr->status == 'ORDERED') {
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