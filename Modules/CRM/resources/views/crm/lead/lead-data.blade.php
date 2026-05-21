@extends('shared::layouts.app')

@section('content')

    <style>
        .sort-icons {
            font-size: 10px !important;
            font-weight: 600 !important;
            line-height: 1 !important;
        }

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
    </style>

    <div class="main-wrapper">

        {{-- ===================== HEADER AREA ===================== --}}
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">Follow Up</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>

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

        <div id="collapseDailyReportFilter"
            class="accordion-collapse collapse page-header-collapse {{ request('search') || request('from') || request('to') || request('source') || request('status') || request('lead_owner') || request('country') || request('course') || request('campaign_name') || request('adset_name') || request('ad_name') ? 'show' : '' }}">
            <div class="accordion-body pb-2">
                <form method="GET" action="{{ route('lead.followUpData') }}" class="row g-3 mb-4">

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

                        <a href="{{ route('lead.followUpData') }}" class="btn btn-danger">
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

                        <div class="table-responsive">

                            <table class="table table-hover" id="leadList">

                                <thead>

                                    {{-- ================= FIRST ROW ================= --}}
                                    <tr>

                                        {{-- SHOW ENTRIES HEADER --}}
                                        <th class="text-start" style="min-width:320px;">

                                            <div class="d-flex justify-content-start align-items-center gap-2">

                                                <label class="mb-0 fw-semibold">Show</label>

                                                <form method="GET" class="d-flex align-items-center gap-2">

                                                    @foreach(request()->except('per_page', 'page') as $key => $value)
                                                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                                    @endforeach

                                                    <select name="per_page" class="form-select form-select-sm"
                                                        onchange="this.form.submit()">

                                                        <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>

                                                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>
                                                            50</option>

                                                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>

                                                        <option value="150" {{ request('per_page') == 150 ? 'selected' : '' }}>150</option>

                                                        <option value="250" {{ request('per_page') == 250 ? 'selected' : '' }}>250</option>

                                                        <option value="500" {{ request('per_page') == 500 ? 'selected' : '' }}>500</option>

                                                    </select>

                                                </form>

                                                <span class="fw-semibold">Entries</span>

                                            </div>

                                        </th>

                                        {{-- DONE FOLLOWUPS --}}
                                        <th rowspan="2">

                                            <div class="d-flex align-items-center gap-1">

                                                <span>Done Followups</span>

                                                <span class="sort-icons d-flex flex-column">

                                                    @if(request('sort_by') == 'done_followups' && request('sort_order') == 'asc')

                                                                                                    <a href="{{ request()->fullUrlWithQuery([
                                                            'sort_by' => 'done_followups',
                                                            'sort_order' => 'desc'
                                                        ]) }}" class="text-decoration-none lh-1 text-dark">
                                                                                                        ▼
                                                                                                    </a>

                                                    @elseif(request('sort_by') == 'done_followups' && request('sort_order') == 'desc')

                                                                                                    <a href="{{ request()->fullUrlWithQuery([
                                                            'sort_by' => 'done_followups',
                                                            'sort_order' => 'asc'
                                                        ]) }}" class="text-decoration-none lh-1 text-dark">
                                                                                                        ▲
                                                                                                    </a>

                                                    @else

                                                                                                    <a href="{{ request()->fullUrlWithQuery([
                                                            'sort_by' => 'done_followups',
                                                            'sort_order' => 'asc'
                                                        ]) }}" class="text-decoration-none lh-1 text-muted">
                                                                                                        ▲
                                                                                                    </a>

                                                                                                    <a href="{{ request()->fullUrlWithQuery([
                                                            'sort_by' => 'done_followups',
                                                            'sort_order' => 'desc'
                                                        ]) }}" class="text-decoration-none lh-1 text-muted">
                                                                                                        ▼
                                                                                                    </a>

                                                    @endif

                                                </span>

                                            </div>

                                        </th>

                                        {{-- GROUP HEADERS --}}
                                        <th colspan="2" class="text-center">Call</th>

                                        <th colspan="2" class="text-center">Whatsapp Call</th>

                                        <th colspan="2" class="text-center">Whatsapp</th>

                                    </tr>

                                    {{-- ================= SECOND ROW ================= --}}
                                    <tr>

                                        {{-- COUNSELOR NAME --}}
                                        <th class="text-start" style="min-width:320px;">

                                            <div class="d-flex align-items-center gap-1">

                                                <span>Counselor Name</span>

                                                <span class="sort-icons d-flex flex-column">

                                                    @if(request('sort_by') == 'name' && request('sort_order') == 'asc')

                                                                                                    <a href="{{ request()->fullUrlWithQuery([
                                                            'sort_by' => 'name',
                                                            'sort_order' => 'desc'
                                                        ]) }}" class="text-decoration-none lh-1 text-dark">
                                                                                                        ▼
                                                                                                    </a>

                                                    @elseif(request('sort_by') == 'name' && request('sort_order') == 'desc')

                                                                                                    <a href="{{ request()->fullUrlWithQuery([
                                                            'sort_by' => 'name',
                                                            'sort_order' => 'asc'
                                                        ]) }}" class="text-decoration-none lh-1 text-dark">
                                                                                                        ▲
                                                                                                    </a>

                                                    @else

                                                                                                    <a href="{{ request()->fullUrlWithQuery([
                                                            'sort_by' => 'name',
                                                            'sort_order' => 'asc'
                                                        ]) }}" class="text-decoration-none lh-1 text-muted">
                                                                                                        ▲
                                                                                                    </a>

                                                                                                    <a href="{{ request()->fullUrlWithQuery([
                                                            'sort_by' => 'name',
                                                            'sort_order' => 'desc'
                                                        ]) }}" class="text-decoration-none lh-1 text-muted">
                                                                                                        ▼
                                                                                                    </a>

                                                    @endif

                                                </span>

                                            </div>

                                        </th>

                                        {{-- CALL CONNECTED --}}
                                        <th>

                                            <div class="d-flex align-items-center gap-1">

                                                <span>Connected</span>

                                                <span class="sort-icons d-flex flex-column">

                                                    @if(request('sort_by') == 'call_connected' && request('sort_order') == 'asc')

                                                                                                    <a href="{{ request()->fullUrlWithQuery([
                                                            'sort_by' => 'call_connected',
                                                            'sort_order' => 'desc'
                                                        ]) }}" class="text-decoration-none lh-1 text-dark">
                                                                                                        ▼
                                                                                                    </a>

                                                    @elseif(request('sort_by') == 'call_connected' && request('sort_order') == 'desc')

                                                                                                    <a href="{{ request()->fullUrlWithQuery([
                                                            'sort_by' => 'call_connected',
                                                            'sort_order' => 'asc'
                                                        ]) }}" class="text-decoration-none lh-1 text-dark">
                                                                                                        ▲
                                                                                                    </a>

                                                    @else

                                                                                                    <a href="{{ request()->fullUrlWithQuery([
                                                            'sort_by' => 'call_connected',
                                                            'sort_order' => 'asc'
                                                        ]) }}" class="text-decoration-none lh-1 text-muted">
                                                                                                        ▲
                                                                                                    </a>

                                                                                                    <a href="{{ request()->fullUrlWithQuery([
                                                            'sort_by' => 'call_connected',
                                                            'sort_order' => 'desc'
                                                        ]) }}" class="text-decoration-none lh-1 text-muted">
                                                                                                        ▼
                                                                                                    </a>

                                                    @endif

                                                </span>

                                            </div>

                                        </th>

                                        {{-- CALL NOT CONNECTED --}}
                                        <th>

                                            <div class="d-flex align-items-center gap-1">

                                                <span>Not Connected</span>

                                                <span class="sort-icons d-flex flex-column">

                                                    @if(request('sort_by') == 'call_not_connected' && request('sort_order') == 'asc')

                                                                                                    <a href="{{ request()->fullUrlWithQuery([
                                                            'sort_by' => 'call_not_connected',
                                                            'sort_order' => 'desc'
                                                        ]) }}" class="text-decoration-none lh-1 text-dark">
                                                                                                        ▼
                                                                                                    </a>

                                                    @elseif(request('sort_by') == 'call_not_connected' && request('sort_order') == 'desc')

                                                                                                    <a href="{{ request()->fullUrlWithQuery([
                                                            'sort_by' => 'call_not_connected',
                                                            'sort_order' => 'asc'
                                                        ]) }}" class="text-decoration-none lh-1 text-dark">
                                                                                                        ▲
                                                                                                    </a>

                                                    @else

                                                                                                    <a href="{{ request()->fullUrlWithQuery([
                                                            'sort_by' => 'call_not_connected',
                                                            'sort_order' => 'asc'
                                                        ]) }}" class="text-decoration-none lh-1 text-muted">
                                                                                                        ▲
                                                                                                    </a>

                                                                                                    <a href="{{ request()->fullUrlWithQuery([
                                                            'sort_by' => 'call_not_connected',
                                                            'sort_order' => 'desc'
                                                        ]) }}" class="text-decoration-none lh-1 text-muted">
                                                                                                        ▼
                                                                                                    </a>

                                                    @endif

                                                </span>

                                            </div>

                                        </th>

                                        {{-- WHATSAPP CALL CONNECTED --}}
                                        <th>

                                            <div class="d-flex align-items-center gap-1">

                                                <span>Connected</span>

                                                <span class="sort-icons d-flex flex-column">

                                                    @if(request('sort_by') == 'whatsapp_call_connected' && request('sort_order') == 'asc')

                                                                                                    <a href="{{ request()->fullUrlWithQuery([
                                                            'sort_by' => 'whatsapp_call_connected',
                                                            'sort_order' => 'desc'
                                                        ]) }}" class="text-decoration-none lh-1 text-dark">
                                                                                                        ▼
                                                                                                    </a>

                                                    @elseif(request('sort_by') == 'whatsapp_call_connected' && request('sort_order') == 'desc')

                                                                                                    <a href="{{ request()->fullUrlWithQuery([
                                                            'sort_by' => 'whatsapp_call_connected',
                                                            'sort_order' => 'asc'
                                                        ]) }}" class="text-decoration-none lh-1 text-dark">
                                                                                                        ▲
                                                                                                    </a>

                                                    @else

                                                                                                    <a href="{{ request()->fullUrlWithQuery([
                                                            'sort_by' => 'whatsapp_call_connected',
                                                            'sort_order' => 'asc'
                                                        ]) }}" class="text-decoration-none lh-1 text-muted">
                                                                                                        ▲
                                                                                                    </a>

                                                                                                    <a href="{{ request()->fullUrlWithQuery([
                                                            'sort_by' => 'whatsapp_call_connected',
                                                            'sort_order' => 'desc'
                                                        ]) }}" class="text-decoration-none lh-1 text-muted">
                                                                                                        ▼
                                                                                                    </a>

                                                    @endif

                                                </span>

                                            </div>

                                        </th>

                                        {{-- WHATSAPP CALL NOT CONNECTED --}}
                                        <th>

                                            <div class="d-flex align-items-center gap-1">

                                                <span>Not Connected</span>

                                                <span class="sort-icons d-flex flex-column">

                                                    @if(request('sort_by') == 'whatsapp_call_not_connected' && request('sort_order') == 'asc')

                                                                                                    <a href="{{ request()->fullUrlWithQuery([
                                                            'sort_by' => 'whatsapp_call_not_connected',
                                                            'sort_order' => 'desc'
                                                        ]) }}" class="text-decoration-none lh-1 text-dark">
                                                                                                        ▼
                                                                                                    </a>

                                                    @elseif(request('sort_by') == 'whatsapp_call_not_connected' && request('sort_order') == 'desc')

                                                                                                    <a href="{{ request()->fullUrlWithQuery([
                                                            'sort_by' => 'whatsapp_call_not_connected',
                                                            'sort_order' => 'asc'
                                                        ]) }}" class="text-decoration-none lh-1 text-dark">
                                                                                                        ▲
                                                                                                    </a>

                                                    @else

                                                                                                    <a href="{{ request()->fullUrlWithQuery([
                                                            'sort_by' => 'whatsapp_call_not_connected',
                                                            'sort_order' => 'asc'
                                                        ]) }}" class="text-decoration-none lh-1 text-muted">
                                                                                                        ▲
                                                                                                    </a>

                                                                                                    <a href="{{ request()->fullUrlWithQuery([
                                                            'sort_by' => 'whatsapp_call_not_connected',
                                                            'sort_order' => 'desc'
                                                        ]) }}" class="text-decoration-none lh-1 text-muted">
                                                                                                        ▼
                                                                                                    </a>

                                                    @endif

                                                </span>

                                            </div>

                                        </th>

                                        {{-- DISCUSSION START --}}
                                        <th>

                                            <div class="d-flex align-items-center gap-1">

                                                <span>Discussion Start</span>

                                                <span class="sort-icons d-flex flex-column">

                                                    @if(request('sort_by') == 'discussion_start' && request('sort_order') == 'asc')

                                                                                                    <a href="{{ request()->fullUrlWithQuery([
                                                            'sort_by' => 'discussion_start',
                                                            'sort_order' => 'desc'
                                                        ]) }}" class="text-decoration-none lh-1 text-dark">
                                                                                                        ▼
                                                                                                    </a>

                                                    @elseif(request('sort_by') == 'discussion_start' && request('sort_order') == 'desc')

                                                                                                    <a href="{{ request()->fullUrlWithQuery([
                                                            'sort_by' => 'discussion_start',
                                                            'sort_order' => 'asc'
                                                        ]) }}" class="text-decoration-none lh-1 text-dark">
                                                                                                        ▲
                                                                                                    </a>

                                                    @else

                                                                                                    <a href="{{ request()->fullUrlWithQuery([
                                                            'sort_by' => 'discussion_start',
                                                            'sort_order' => 'asc'
                                                        ]) }}" class="text-decoration-none lh-1 text-muted">
                                                                                                        ▲
                                                                                                    </a>

                                                                                                    <a href="{{ request()->fullUrlWithQuery([
                                                            'sort_by' => 'discussion_start',
                                                            'sort_order' => 'desc'
                                                        ]) }}" class="text-decoration-none lh-1 text-muted">
                                                                                                        ▼
                                                                                                    </a>

                                                    @endif

                                                </span>

                                            </div>

                                        </th>

                                        {{-- NO RESPONSE --}}
                                        <th>

                                            <div class="d-flex align-items-center gap-1">

                                                <span>No Response</span>

                                                <span class="sort-icons d-flex flex-column">

                                                    @if(request('sort_by') == 'no_response' && request('sort_order') == 'asc')

                                                                                                    <a href="{{ request()->fullUrlWithQuery([
                                                            'sort_by' => 'no_response',
                                                            'sort_order' => 'desc'
                                                        ]) }}" class="text-decoration-none lh-1 text-dark">
                                                                                                        ▼
                                                                                                    </a>

                                                    @elseif(request('sort_by') == 'no_response' && request('sort_order') == 'desc')

                                                                                                    <a href="{{ request()->fullUrlWithQuery([
                                                            'sort_by' => 'no_response',
                                                            'sort_order' => 'asc'
                                                        ]) }}" class="text-decoration-none lh-1 text-dark">
                                                                                                        ▲
                                                                                                    </a>

                                                    @else

                                                                                                    <a href="{{ request()->fullUrlWithQuery([
                                                            'sort_by' => 'no_response',
                                                            'sort_order' => 'asc'
                                                        ]) }}" class="text-decoration-none lh-1 text-muted">
                                                                                                        ▲
                                                                                                    </a>

                                                                                                    <a href="{{ request()->fullUrlWithQuery([
                                                            'sort_by' => 'no_response',
                                                            'sort_order' => 'desc'
                                                        ]) }}" class="text-decoration-none lh-1 text-muted">
                                                                                                        ▼
                                                                                                    </a>

                                                    @endif

                                                </span>

                                            </div>

                                        </th>

                                    </tr>

                                </thead>

                                <tbody>
                                    @forelse($leads as $row)
                                        <tr>
                                            <td class="text-start">
                                                <strong>{{ $row->user->name ?? 'user' }}</strong>
                                            </td>

                                            <td>{{ $row->done_followups }}</td>

                                            <!-- Call -->
                                            <td>{{ $row->call_connected }}</td>
                                            <td>{{ $row->call_not_connected }}</td>

                                            <!-- Whatsapp Call -->
                                            <td>{{ $row->whatsapp_call_connected }}</td>
                                            <td>{{ $row->whatsapp_call_not_connected }}</td>

                                            <td>{{ $row->discussion_start }}</td>
                                            <td>{{ $row->no_response }}</td>

                                            <!-- Others -->


                                            <!-- <td>{{ $row->planned_followups }}</td>
                                                                                        <td>{{ $row->missed_followups }}</td> -->


                                            <!-- <td style="cursor:pointer; position:relative;">

                                                                                            {{-- Done Toggle --}}
                                                                                            <div class="done-toggle d-flex align-items-center justify-content-center gap-1 fw-bold" data-id="{{ $row->created_by }}">
                                                                                                <span>{{ $row->done_followups }}</span>
                                                                                                <i class="bi bi-chevron-right toggle-icon"></i>
                                                                                            </div>

                                                                                            {{-- Level 1 Container --}}
                                                                                            <div class="done-content d-none mt-2" id="done-{{ $row->created_by }}">

                                                                                                <div class="d-flex justify-content-between gap-2">

                                                                                                    {{-- CALL --}}
                                                                                                    <div class="flex-fill border rounded p-1 text-start">
                                                                                                        <div class="type-toggle d-flex justify-content-between align-items-center" data-target="call-{{ $row->created_by }}">
                                                                                                            <span> Call ({{ $row->phone_call }})</span>
                                                                                                            <i class="bi bi-chevron-right type-icon"></i>
                                                                                                        </div>

                                                                                                        <div class="type-content d-none mt-2 small text-muted" id="call-{{ $row->created_by }}">
                                                                                                            <div> Connected: {{ $row->call_connected }}</div>
                                                                                                            <div> Not Connected: {{ $row->call_not_connected }}</div>
                                                                                                        </div>
                                                                                                    </div>

                                                                                                    {{-- WHATSAPP CALL --}}
                                                                                                    <div class="flex-fill border rounded p-1 text-start ">
                                                                                                        <div class="type-toggle d-flex justify-content-between align-items-center" data-target="wacall-{{ $row->created_by }}">
                                                                                                            <span> WA Call ({{ $row->whatsapp_call }})</span>
                                                                                                            <i class="bi bi-chevron-right type-icon"></i>
                                                                                                        </div>

                                                                                                        <div class="type-content d-none mt-2 small text-muted text-start" id="wacall-{{ $row->created_by }}">
                                                                                                            <div class="text-start"> Connected: {{ $row->whatsapp_call_connected }}</div>
                                                                                                            <div> Not Connected: {{ $row->whatsapp_call_not_connected }}</div>
                                                                                                        </div>
                                                                                                    </div>

                                                                                                    {{-- WHATSAPP --}}
                                                                                                    <div class="flex-fill border rounded p-1 text-start">
                                                                                                        <div class="type-toggle d-flex justify-content-between align-items-center" data-target="wa-{{ $row->created_by }}">
                                                                                                            <span> WA ({{ $row->whatsapp }})</span>
                                                                                                            <i class="bi bi-chevron-right type-icon"></i>
                                                                                                        </div>

                                                                                                        <div class="type-content d-none mt-2 small text-muted" id="wa-{{ $row->created_by }}">
                                                                                                            <div> Discussion Start: {{ $row->discussion_start }}</div>
                                                                                                            <div> No Response: {{ $row->no_response }}</div>
                                                                                                        </div>
                                                                                                    </div>

                                                                                                </div>

                                                                                            </div>

                                                                                        </td> -->


                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="text-center p-5 text-muted">
                                                No Records Found
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>

                            </table>

                        </div>

                        <div class="m-4" style="display: flex; justify-content: center;">
                            {{ $leads->withQueryString()->links('pagination::bootstrap-4') }}
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // 👉 Done toggle
            document.querySelectorAll('.done-toggle').forEach(el => {
                el.addEventListener('click', function (e) {
                    e.stopPropagation();

                    let id = this.dataset.id;
                    let box = document.getElementById('done-' + id);
                    let icon = this.querySelector('.toggle-icon');

                    box.classList.toggle('d-none');

                    icon.classList.toggle('bi-chevron-right');
                    icon.classList.toggle('bi-chevron-down');
                });
            });

            // 👉 Type toggle (nested)
            document.querySelectorAll('.type-toggle').forEach(el => {
                el.addEventListener('click', function (e) {
                    e.stopPropagation();

                    let target = this.dataset.target;
                    let box = document.getElementById(target);
                    let icon = this.querySelector('.type-icon');

                    box.classList.toggle('d-none');

                    icon.classList.toggle('bi-chevron-right');
                    icon.classList.toggle('bi-chevron-down');
                });
            });



        });
    </script>
@endsection