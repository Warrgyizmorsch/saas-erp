@extends('shared::layouts.app')

@section('content')

    <style>
        .table-responsive {
            overflow-x: auto;
            max-height: 70vh;
            /* adjust height if needed */
        }

        #leadList thead th {
            position: sticky;
            top: 0;
            background: #ffffff;
            /* important so it doesn't turn transparent */
            z-index: 10;
            box-shadow: 0 2px 2px rgba(0, 0, 0, 0.05);
        }

        .highlight-column {
            background-color: #fafafaf5 !important;
        }

        .table-responsive {
            overflow-x: auto;
        }

        /* Make content take full height so footer stays at bottom */
        .main-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .main-content {
            flex: 1;
        }
    </style>

    <div class="main-wrapper">

        {{-- ===================== HEADER AREA ===================== --}}
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">Daily Lead Report</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item">Daily Report</li>
                </ul>


            </div>

            <div class="page-header-right ms-auto">
                <div class="page-header-right-items">
                    <div class="d-flex d-md-none">
                        <a href="javascript:void(0)" class="page-header-right-close-toggle">
                            <i class="feather-arrow-left me-2"></i> <span>Back</span>
                        </a>
                    </div>

                    <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">

                        {{-- Chart Toggle --}}
                        <a href="javascript:void(0);" class="btn btn-icon btn-light-brand" data-bs-toggle="collapse"
                            data-bs-target="#collapseDailyReportFilter">
                            <i class="feather-bar-chart"></i>Filter
                        </a>
                    </div>
                </div>
            </div>

        </div>

        {{-- Filters --}}
        {{-- Collapsible Lead Stats --}}
        <div id="collapseDailyReportFilter"
            class="accordion-collapse collapse page-header-collapse {{ request('search') || request('from') || request('to') || request('source') || request('status') || request('lead_owner') || request('country') || request('course') || request('campaign_name') || request('adset_name') || request('ad_name') ? 'show' : '' }}">
            <div class="accordion-body pb-2">
                <form method="GET" action="{{ route('lead.dailyReport') }}" class="row g-3 mb-4">

                    <div class="col-md-3">
                        <label class="form-label">From Date</label>
                        <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">To Date</label>
                        <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                    </div>

                    <!-- Buttons -->
                    <div class="col-12 d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="feather-filter me-1"></i> Filter
                        </button>

                        <a href="{{ route('lead.dailyReport') }}" class="btn btn-danger">
                            Reset
                        </a>
                    </div>

                </form>
            </div>
        </div>


        {{-- ===================== MAIN CONTENT ===================== --}}
        <div class="main-content mt-3">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card stretch stretch-full">
                        <div class="card-body p-0">
                            <div class="table-responsive">

                                <table class="table table-hover" id="leadList">

                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Total Leads</th>
                                            <th class="highlight-column">{{ $unassignedColumn }}</th>
                                            <th class="highlight-column">Convert</th>
                                            @foreach($statusColumns as $status)
                                                <th>{{ $status }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($paginated as $date => $row)
                                            <tr>
                                                <td>
                                                    <strong>{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</strong>
                                                </td>

                                                <td>
                                                    <strong>{{ $row['total_leads'] ?? 0 }}</strong>
                                                </td>

                                                {{-- No Bucket / Unassigned Column --}}
                                                <td class="highlight-column">
                                                    @php
                                                        $noBucket = $row[$unassignedColumn] ?? 0;
                                                    @endphp

                                                    @if($noBucket > 0)
                                                        <span class="badge bg-danger">
                                                            {{ $noBucket }}
                                                        </span>
                                                    @else
                                                        <span>
                                                            {{ $noBucket }}
                                                        </span>
                                                    @endif
                                                </td>

                                                {{-- Convert Column --}}
                                                <td class="highlight-column">
                                                    @php
                                                        $convert = $row['convert'] ?? 0;
                                                    @endphp

                                                    @if($convert > 0)
                                                        <span class="badge bg-success">
                                                            {{ $convert }}
                                                        </span>
                                                    @else
                                                        <span>
                                                            {{ $convert }}
                                                        </span>
                                                    @endif
                                                </td>

                                                {{-- Other Status Buckets --}}
                                                @foreach($statusColumns as $status)
                                                    <td>{{ $row[$status] ?? 0 }}</td>
                                                @endforeach
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ count($statusColumns) + 3 }}"
                                                    class="text-center p-5 text-muted">
                                                    No Records Found
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>

                                </table>

                            </div>

                            <div class="m-4" style="display: flex; justify-content: center;">
                                {{ $paginated->withQueryString()->links('pagination::bootstrap-4') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection