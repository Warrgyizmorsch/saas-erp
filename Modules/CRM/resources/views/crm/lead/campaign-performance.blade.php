@extends('layouts.app')

@section('content')

<style>
    .table-responsive {
        overflow-x: auto;
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

    /* Table Base */
    #leadList {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        font-size: 14px;
    }

    /* Header */
    #leadList thead th {
        background: #f8f9fb;
        color: #344054;
        font-weight: 600;
        border-bottom: 1px solid #e5e7eb;
    }

    /* Dark Mode Support */
    body.dark-mode #leadList thead th {
        background: #1f2937;
        color: #e5e7eb;
    }

    /* Rows */
    #leadList tbody tr {
        transition: all 0.2s ease;
    }

    /* Zebra striping */
    #leadList tbody tr:nth-child(even) {
        background-color: #fafafa;
    }

    body.dark-mode #leadList tbody tr:nth-child(even) {
        background-color: #111827;
    }

    /* Hover Effect */
    #leadList tbody tr:hover {
        background-color: #e8f0fe !important;
        cursor: pointer;
    }

    body.dark-mode #leadList tbody tr:hover {
        background-color: #1e3a8a !important;
    }

    /* Cell padding */
    #leadList th,
    #leadList td {
        padding: 12px 14px;
        border-bottom: 1px solid #eee;
    }

    body.dark-mode #leadList th,
    body.dark-mode #leadList td {
        border-bottom: 1px solid #374151;
    }

    /* First column highlight */
    #leadList td:first-child {
        font-weight: 600;
        color: #111827;
    }

    body.dark-mode #leadList td:first-child {
        color: #f9fafb;
    }

    /* Numbers styling */
    #leadList td:not(:first-child) {
        text-align: center;
        font-weight: 500;
    }

    /* ================= TABLE SORTING ================= */

    #leadList th.sortable {
        cursor: pointer;
        user-select: none;
        white-space: nowrap;
    }

    #leadList th.sortable .sort-icons {
        display: inline-flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        margin-left: 6px;
        font-size: 7px;
        line-height: 7px;
        opacity: 0.5;
        vertical-align: middle;
    }

    .sort-icons {
        font-size: 10px !important;
        font-weight: 600 !important;
        line-height: 1 !important;
    }
</style>

<div class="main-wrapper">

    {{-- ===================== HEADER AREA ===================== --}}
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Campaign Report</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Campaign Report</li>
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
    <div id="collapseDailyReportFilter" class="accordion-collapse collapse page-header-collapse ">
        <div class="accordion-body pb-2">
            <form method="GET" action="{{ route('lead.campaignPerformance') }}" class="row g-3 mb-4">
                <input type="hidden" name="group_by" value="{{ request('group_by') }}">
                <div class="col-md-3">
                    <label for="campaign_name" class="form-label">Campaign Name</label>
                    <select name="campaign_name" class="form-control" data-select2-selector="status">
                        <option value="">All Campaign</option>
                        @foreach($campaigns as $campaign)
                        <option value="{{ $campaign }}" {{ request('campaign_name') == $campaign ? 'selected' : '' }}>
                            {{ $campaign }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="adset_name" class="form-label">Adset Name</label>
                    <select name="adset_name" class="form-control" data-select2-selector="status">
                        <option value="">All Adset</option>
                        @foreach($adsets as $adset)
                        <option value="{{ $adset }}" {{ request('adset_name') == $adset ? 'selected' : '' }}>
                            {{ $adset }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="form_name" class="form-label">Form Name</label>
                    <select name="adset_name" class="form-control" data-select2-selector="status">
                        <option value="">All Adset</option>
                        @foreach($adsets as $adset)
                        <option value="{{ $adset }}" {{ request('adset_name') == $adset ? 'selected' : '' }}>
                            {{ $adset }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                </div>
                <div class="col-12 d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="feather-filter me-1"></i> Filter
                    </button>

                    <a href="{{ route('lead.campaignPerformance') }}" class="btn btn-danger">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>



    {{-- ===================== MAIN CONTENT ===================== --}}
    <div class="main-content mt-3">
        <div class="row">

            {{-- 🔥 TOP SECTION (Dropdown + Chart) --}}
            <div class="col-12">
                <div class="card mb-3">
                    <div class="card-body">

                        <div class="d-flex flex-column flex-lg-row gap-4 align-items-start">

                            {{-- LEFT: DROPDOWN --}}
                            <div style="min-width: 250px;">
                                <form method="GET" action="{{ route('lead.campaignPerformance') }}">
                                    <input type="hidden" name="campaign_name" value="{{ request('campaign_name') }}">
                                    <input type="hidden" name="from" value="{{ request('from') }}">
                                    <input type="hidden" name="to" value="{{ request('to') }}">

                                    <label class="form-label fw-semibold">Select Data Type</label>

                                    <select name="group_by" class="form-control" onchange="this.form.submit()">

                                        <option value="campaign" {{ request('group_by') == 'campaign' ? 'selected' : '' }}>
                                            Campaign
                                        </option>

                                        <option value="ad-name" {{ request('group_by') == 'ad-name' ? 'selected' : '' }}>
                                            Ad Name
                                        </option>

                                        <option value="adset-name" {{ request('group_by') == 'adset-name' ? 'selected' : '' }}>
                                            Adset Name
                                        </option>

                                        <option value="form-name" {{ request('group_by') == 'form-name' ? 'selected' : '' }}>
                                            Form Name
                                        </option>

                                    </select>
                                </form>
                            </div>

                            {{-- RIGHT: FUNNEL CHART --}}
                            <div class="d-flex align-items-center " style="width:60%;">

                                <!-- LEFT: Funnel -->
                                <div id="funnelChart" style="height:300px; width:60%;"></div>

                                <!-- RIGHT: Custom Legend -->
                                <div id="funnelLegend" style="width:40%; padding-left:20px;"></div>

                            </div>

                        </div>

                    </div>
                </div>
            </div>

            {{-- 🔥 TABLE SECTION --}}
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-0">

                        <div class="d-flex justify-content-start align-items-center gap-2 p-3 border-bottom">

                            <label class="mb-0">Show</label>

                            <form method="GET" class="d-flex align-items-center gap-2">
                                @foreach(request()->except('per_page', 'page') as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endforeach

                                <select name="per_page"
                                    class="form-select form-select-sm"
                                    onchange="this.form.submit()">

                                    <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                    <option value="150" {{ request('per_page') == 150 ? 'selected' : '' }}>150</option>
                                    <option value="250" {{ request('per_page') == 250 ? 'selected' : '' }}>250</option>
                                    <option value="500" {{ request('per_page') == 500 ? 'selected' : '' }}>500</option>

                                </select>
                            </form>

                            <span>Entries</span>

                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="leadList">

                                <thead>
                                    <tr>

                                        <th class="text-start" style="min-width:200px; max-width:250px;">

                                            <div class="d-flex align-items-center gap-1">

                                                <span>Name</span>

                                                <span class="sort-icons d-flex flex-column">

                                                    @if(request('sort_by') == 'name' && request('sort_order') == 'asc')

                                                    <!-- ASC active → show only DESC -->
                                                    <a href="{{ request()->fullUrlWithQuery([
                                                            'sort_by' => 'name',
                                                            'sort_order' => 'desc'
                                                        ]) }}"
                                                        class="text-decoration-none lh-1 text-dark">

                                                        ▼
                                                    </a>

                                                    @elseif(request('sort_by') == 'name' && request('sort_order') == 'desc')

                                                    <!-- DESC active → show only ASC -->
                                                    <a href="{{ request()->fullUrlWithQuery([
                                                              'sort_by' => 'name',
                                                              'sort_order' => 'asc'
                                                          ]) }}"
                                                        class="text-decoration-none lh-1 text-dark">

                                                        ▲
                                                    </a>

                                                    @else

                                                    <!-- First time दोनों -->
                                                    <a href="{{ request()->fullUrlWithQuery([
                                                             'sort_by' => 'name',
                                                             'sort_order' => 'asc'
                                                         ]) }}"
                                                        class="text-decoration-none lh-1 text-muted">

                                                        ▲
                                                    </a>

                                                    <a href="{{ request()->fullUrlWithQuery([
                                                          'sort_by' => 'name',
                                                          'sort_order' => 'desc'
                                                      ]) }}"
                                                        class="text-decoration-none lh-1 text-muted">

                                                        ▼
                                                    </a>

                                                    @endif

                                                </span>

                                            </div>

                                        </th>

                                        <th class="text-start">

                                            <div class="d-flex align-items-center gap-0.5">

                                                <span>Total Leads</span>

                                                <span class="sort-icons d-flex flex-column">

                                                    @if(request('sort_by') == 'total_leads' && request('sort_order') == 'asc')

                                                    <!-- ASC active → show only DESC -->
                                                    <a href="{{ request()->fullUrlWithQuery([
                                                           'sort_by' => 'total_leads',
                                                           'sort_order' => 'desc'
                                                       ]) }}"
                                                        class="text-decoration-none lh-1 text-dark">

                                                        ▼
                                                    </a>

                                                    @elseif(request('sort_by') == 'total_leads' && request('sort_order') == 'desc')


                                                    <a href="{{ request()->fullUrlWithQuery([
                                                            'sort_by' => 'total_leads',
                                                            'sort_order' => 'asc'
                                                        ]) }}"
                                                        class="text-decoration-none lh-1 text-dark">

                                                        ▲
                                                    </a>

                                                    @else


                                                    <a href="{{ request()->fullUrlWithQuery([
                                                              'sort_by' => 'total_leads',
                                                              'sort_order' => 'asc'
                                                          ]) }}"
                                                        class="text-decoration-none lh-1 text-muted">

                                                        ▲
                                                    </a>

                                                    <a href="{{ request()->fullUrlWithQuery([
                                                              'sort_by' => 'total_leads',
                                                              'sort_order' => 'desc'
                                                          ]) }}"
                                                        class="text-decoration-none lh-1 text-muted">

                                                        ▼
                                                    </a>

                                                    @endif

                                                </span>

                                            </div>

                                        </th>

                                        @foreach($buckets as $bucket)

                                        <th>

                                            <div class="d-flex align-items-center gap-1">

                                                <span>{{ $bucket->name }}</span>

                                                <span class="sort-icons d-flex flex-column">

                                                    @if(request('sort_by') == 'bucket_'.$bucket->id && request('sort_order') == 'asc')

                                                    <!-- ASC active -->
                                                    <a href="{{ request()->fullUrlWithQuery([
                                                           'sort_by' => 'bucket_'.$bucket->id,
                                                           'sort_order' => 'desc'
                                                       ]) }}"
                                                        class="text-decoration-none lh-1 text-dark">

                                                        ▼
                                                    </a>

                                                    @elseif(request('sort_by') == 'bucket_'.$bucket->id && request('sort_order') == 'desc')

                                                    <!-- DESC active -->
                                                    <a href="{{ request()->fullUrlWithQuery([
                                                         'sort_by' => 'bucket_'.$bucket->id,
                                                         'sort_order' => 'asc'
                                                     ]) }}"
                                                        class="text-decoration-none lh-1 text-dark">

                                                        ▲
                                                    </a>

                                                    @else

                                                    <!-- Default -->
                                                    <a href="{{ request()->fullUrlWithQuery([
                                                            'sort_by' => 'bucket_'.$bucket->id,
                                                            'sort_order' => 'asc'
                                                        ]) }}"
                                                        class="text-decoration-none lh-1 text-muted">

                                                        ▲
                                                    </a>

                                                    <a href="{{ request()->fullUrlWithQuery([
                                                            'sort_by' => 'bucket_'.$bucket->id,
                                                            'sort_order' => 'desc'
                                                        ]) }}"
                                                        class="text-decoration-none lh-1 text-muted">

                                                        ▼
                                                    </a>

                                                    @endif

                                                </span>

                                            </div>

                                        </th>

                                        @endforeach

                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($data as $row)
                                    <tr>
                                        <td class="text-start" style="word-break: break-word; white-space: normal;">
                                            <strong>{{ $row->name }}</strong>
                                        </td>

                                        <td><strong>{{ $row->total_leads ?? 0 }}</strong></td>
                                        @foreach($buckets as $bucket)

                                        <td>

                                            {{ $row->{'bucket_'.$bucket->id} ?? 0 }}

                                        </td>

                                        @endforeach
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="10" class="text-center p-5 text-muted">
                                            No Records Found
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>

                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-4 mb-5">
                            {{ $data->withQueryString()->links('pagination::bootstrap-5') }}
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    // window.onload = function () {

    //     let data = [
    //         { name: 'Total Leads', real: {{ $totals->sum('total_leads') ?? 0 }} },
    //         { name: 'Lost', real: {{ $totals->sum('lost') ?? 0 }} },
    //         { name: ' Not Connected', real: {{ $totals->sum('not_connected') ?? 0 }} },
    //         { name: 'Untouched', real: {{ $totals->sum('untouched') ?? 0 }} },
    //         { name: 'Counselling', real: {{ $totals->sum('counselling') ?? 0 }} },
    //         { name: 'Application', real: {{ $totals->sum('application') ?? 0 }} },
    //         { name: 'Offer Stage', real: {{ $totals->sum('offer_stage') ?? 0 }} },
    //         { name: 'Visa', real: {{ $totals->sum('visa_process') ?? 0 }} },
    //         { name: 'Converted', real: {{ $totals->sum('converted') ?? 0 }} }
    //     ];

    //     // 🔥 SORT: highest first
    //     data.sort((a, b) => b.real - a.real);

    //     // 🔥 FIXED SHAPE (same size)
    //     let finalData = data.map(item => ({
    //         name: item.name,
    //         y: 100,        // shape same rahega
    //         real: item.real
    //     }));

    //     Highcharts.chart('funnelChart', {
    //         chart: {
    //             type: 'funnel'
    //         },

    //         title: {
    //             text: ''
    //         },
    //          tooltip: {
    //         formatter: function () {
    //             return '<b>' + this.point.name + '</b>: ' + this.point.real;
    //         }
    //     },

    //         plotOptions: {
    //             series: {
    //                 dataLabels: {
    //                     enabled: true,
    //                     formatter: function () {
    //                         return this.point.name + ': ' + this.point.real;
    //                     }
    //                 },
    //                 neckWidth: '0%',
    //                 neckHeight: '0%',
    //                 width: '60%'
    //             }
    //         },

    //         series: [{
    //             name: 'Leads',
    //             data: finalData,
    //             colors: [
    //         '#E67E3F',
    //         '#E5D46A',
    //         '#76B7B2',
    //         '#8FD18B',
    //         '#3B6FB6',
    //         '#E76F51',
    //         '#F4E7A1'
    //     ]
    //         }]
    //     });

    // };

    window.onload = function() {

        let colors = [
            '#E67E3F',
            '#E5D46A',
            '#76B7B2',
            '#8FD18B',
            '#3B6FB6',
            '#E76F51',
            '#F4E7A1',
            '#A29BFE',
            '#FF9FF3'
        ];

        let data = [

            {
                name: 'Total Leads',
                real: {{$totals-> sum('total_leads') ?? 0}}
            },

            @foreach($buckets as $bucket)

            {
                name: '{{ $bucket->name }}',
                real: {{$totals-> sum('bucket_'.$bucket-> id) ?? 0}}
            },

            @endforeach

        ];
        // 🔥 Sort descending
        data.sort((a, b) => b.real - a.real);

        let finalData = data.map((item, index) => ({
            name: item.name,
            y: 100, // shape same
            real: item.real,
            color: colors[index % colors.length]
        }));

        // ================= FUNNEL CHART =================
        Highcharts.chart('funnelChart', {
            chart: {
                type: 'funnel'
            },

            title: {
                text: ''
            },

            tooltip: {
                formatter: function() {
                    return '<b>' + this.point.name + '</b>: ' + this.point.real;
                }
            },

            plotOptions: {
                series: {
                    dataLabels: {
                        enabled: true,
                        inside: true, // 🔥 center me
                        style: {
                            color: '#000',
                            fontSize: '13px',
                            fontWeight: '600',
                            textOutline: 'none'
                        },
                        formatter: function() {
                            return this.point.real; // 🔥 sirf count
                        }
                    },
                    neckWidth: '0%',
                    neckHeight: '0%',
                    width: '80%'
                }
            },

            series: [{
                name: 'Leads',
                data: finalData
            }]
        });

        // ================= RIGHT SIDE LEGEND =================
        let legendHTML = '';

        finalData.forEach(item => {
            legendHTML += `
                        <div style="display:flex; align-items:center; margin-bottom:10px;">

                            <div style="
                                width:14px;
                                height:14px;
                                background:${item.color};
                                margin-right:10px;
                                border-radius:3px;">
                            </div>

                            <div style="flex:1; font-size:14px;">
                                ${item.name}
                            </div>

                            <div style="font-weight:600;">
                                ${item.real}
                            </div>

                        </div>
                    `;
        });

        document.getElementById('funnelLegend').innerHTML = legendHTML;

    };
</script>
@endsection