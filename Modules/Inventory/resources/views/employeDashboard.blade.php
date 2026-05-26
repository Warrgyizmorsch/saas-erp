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
            <div class="col-xxl-6 col-md-6">
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
            <div class="col-xxl-6 col-md-6">
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
           
           
         
            <div class=" col-lg-8">
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
                                                } elseif ($rs->status == 'Rejected') {
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
                                        <a href="javascript:void(0);">{{ $p['name'] }}</a>
                                        <div class="fs-11 text-muted">
                                            Created By: {{ $p['user']['name'] }}
                                        </div>
                                    </div>
                                </div>

                                {{-- 👇 UI SAME, only data-value added --}}
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
    
    


@endsection
  <script src="{{ asset('/assets/vendors/js/apexcharts.min.js') }}"></script>
    <script src="{{ asset('/assets/vendors/js/circle-progress.min.js') }}"></script>

    {{-- App Init JS --}}
    <script src="{{ asset('/assets/js/common-init.min.js') }}"></script>
    <script src="{{ asset('/assets/js/dashboard-init.min.js') }}"></script>