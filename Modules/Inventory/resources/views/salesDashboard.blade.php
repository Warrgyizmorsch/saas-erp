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
                <form action="" method="GET">
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
            <!-- [Payment Records] start -->
            <div class="col-xxl-8">
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
