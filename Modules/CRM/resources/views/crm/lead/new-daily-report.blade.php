@extends('layouts.app')

@section('content')

<style>
    td.active-status {
        background: #e8f0ff !important;
        border-radius: 8px;
        transition: 0.2s;
    }


    .table-responsive {
        overflow-x: auto;
        max-height: 70vh;
    }

    .table thead th {
        position: sticky;
        top: 0;
        background: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        z-index: 10;
        font-weight: 600;
        color: #333;
    }

    .main-wrapper {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    .main-content {
        flex: 1;
    }

    .report-section {
        background: #fff;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }

    .report-section h5 {
        color: #2c3e50;
        margin-bottom: 15px;
        border-bottom: 2px solid #007bff;
        padding-bottom: 10px;
    }

    .stat-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        background: #f0f2f5;
        color: #333;
    }

    .stat-value {
        color: #007bff;
        font-weight: 700;
        font-size: 18px;
    }

    .table-sm th,
    .table-sm td {
        padding: 10px;
        vertical-align: middle;
    }

    .user-filter-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }

    .empty-state {
        padding: 40px 20px;
        text-align: center;
        color: #999;
    }

    .transition-badge {
        font-size: 12px;
        padding: 4px 8px;
    }

    .record-row {
        border-left: 3px solid #007bff;
        background: #f8f9ff;
        margin-bottom: 8px;
        padding: 10px;
        border-radius: 4px;
    }

    .optional-cell {
        background: #fff3cd;
        font-style: italic;
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
                    <a href="javascript:void(0);" class="btn btn-icon btn-light-brand" data-bs-toggle="collapse"
                        data-bs-target="#collapseDailyReportFilter">
                        <i class="feather-filter"></i>Filter
                    </a>
                    <a href="{{ route('send.whatsapp.all') }}"
                        class="btn btn-success">
                        <i class="bi bi-whatsapp me-1"></i>
                        Send WhatsApp Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters Section --}}
    <div id="collapseDailyReportFilter" class="accordion-collapse show page-header-collapse">
        <div class="accordion-body pb-2">
            <form method="GET" action="{{ route('lead.newdailyReport') }}" class="row g-3 mb-4" id="date-filter-form">

                <div class="col-12 mb-3">
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm preset-btn"
                            data-preset="today">Today</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm preset-btn"
                            data-preset="yesterday">Yesterday</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm preset-btn"
                            data-preset="7days">Last 7 Days</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm preset-btn"
                            data-preset="30days">Last 30 Days</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm preset-btn"
                            data-preset="this-month">This Month</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm preset-btn"
                            data-preset="last-month">Last Month</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm preset-btn active"
                            data-preset="custom">Custom</button>
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Start</label>
                    <input type="date" name="from" id="start-date" class="form-control" value="{{ request('from') }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">End</label>
                    <input type="date" name="to" id="end-date" class="form-control" value="{{ request('to') }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">User</label>
                    <select name="user_id" id="user-filter" class="form-control">
                        <option value="">All Users</option>
                        @php
                        $allUserIds = array_keys($final);
                        @endphp
                        @foreach($allUserIds as $userId)
                        <option value="{{ $userId }}" {{ request('user_id') == $userId ? 'selected' : '' }}>
                            @if($userImages[$userId] ?? null)
                            📷 {{ $final[$userId]['name'] }}
                            @else
                            {{ $final[$userId]['name'] }}
                            @endif
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="feather-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('lead.newdailyReport') }}" class="btn btn-outline-danger px-4">Reset</a>
                </div>

            </form>
        </div>
    </div>

    {{-- ===================== MAIN CONTENT ===================== --}}
    <div class="main-content mt-4">

        @php
        $selectedUserId = request('user_id');
        $displayData = [];

        if ($selectedUserId && isset($final[$selectedUserId])) {
        $displayData[$selectedUserId] = $final[$selectedUserId];
        } else {
        $displayData = $final;
        }
        @endphp

        @forelse($displayData as $userId => $row)

        {{-- ===== DAILY REPORT SECTION ===== --}}
        <div class="report-section">
            <div class="d-flex align-items-center gap-3 mb-3">
                @if($userImages[$userId] ?? null)
                <img src="{{ asset('storage/' . $userImages[$userId]) }}" alt="{{ $row['name'] }}"
                    class="rounded-circle"
                    style="width: 50px; height: 50px; object-fit: cover; border: 2px solid #007bff;">
                @else
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                    style="width: 50px; height: 50px; font-weight: bold; font-size: 18px;">
                    {{ substr($row['name'], 0, 1) }}
                </div>
                @endif
                <div class="d-flex align-items-center gap-3 flex-wrap">

                    <h5 class="mb-0">
                        {{ $row['name'] }}'s Daily Report
                    </h5>

                    <span
                        id="totalLeadCount{{ $userId }}"
                        class="d-inline-flex align-items-center gap-2 px-3 py-2 bg-light border"
                        style="
        border-radius:10px;
        border-color:#dbe4ff !important;
              ">

                        <i class="bi bi-people-fill text-primary fs-5"></i>

                        <span class="text-muted fw-semibold">
                            Total Leads :
                        </span>

                        <span class="fw-bold text-primary fs-5">
                            {{ $row['total'] }}
                        </span>

                    </span>
                    <!-- TODAY / PAST TOGGLE -->
                    <div class="btn-group lead-toggle-group"
                        role="group"
                        data-user="{{ $userId }}"
                        style="
                                 background:#eef2ff;
                                 padding:5px;
                                 border-radius:50px;
                             ">

                        <button type="button"
                            class="btn btn-primary lead-date-btn active"
                            data-range="today"
                            data-user="{{ $userId }}"
                            style="
                                   border-radius:50px;
                                   padding:8px 18px;
                                   font-weight:600;
                                   border:none;
                                   box-shadow:none;
                               ">

                            <i class="bi bi-lightning-charge-fill me-1"></i>
                            Today

                        </button>

                        <button type="button"
                            class="btn btn-outline-primary lead-date-btn"
                            data-range="previous"
                            data-user="{{ $userId }}"
                            style="
                                border-radius:50px;
                                padding:8px 18px;
                                font-weight:600;
                                border:none;
                                background:transparent;
                            ">
                            Previous
                        </button>

                        <button type="button"
                            class="btn btn-outline-primary lead-date-btn"
                            data-range="past"
                            data-user="{{ $userId }}"
                            style="
                                border-radius:50px;
                                padding:8px 18px;
                                font-weight:600;
                                border:none;
                                background:transparent;
                            ">

                            <i class="bi bi-calendar2-week me-1"></i>
                            Past

                        </button>

                    </div>
                </div>
            </div>

            {{-- Summary Stats --}}
            <div class="table-responsive mb-4">
                <table class="table table-sm table-bordered align-middle">
                    <thead class="table-light">
                        <tr>

                            {{-- ENGAGEMENT STATUS --}}
                            <th class="text-center" style="width: 20%;">
                                <i class="bi bi-bar-chart-fill me-1 text-primary"></i>
                                Engagement Status
                            </th>

                            {{-- CIP --}}
                            <th class="text-center" style="width: 20%;">
                                <i class="bi bi-chat-dots-fill me-1 text-info"></i>
                                CIP
                            </th>

                            {{-- APPLICATION PROCESS --}}
                            <th class="text-center" style="width: 20%;">
                                <i class="bi bi-file-earmark-text-fill me-1 text-warning"></i>
                                Application Process
                            </th>

                            {{-- OFFER STAGE --}}
                            <th class="text-center" style="width: 20%;">
                                <i class="bi bi-award-fill me-1 text-success"></i>
                                Offer Stage
                            </th>

                            {{-- CONVERTED --}}
                            <th class="text-center" style="width: 20%;">
                                <i class="bi bi-check-circle-fill me-1 text-success"></i>
                                Converted
                            </th>

                            {{-- NOT CONNECTED --}}
                            <th class="text-center" style="width: 20%;">
                                <i class="bi bi-x-circle-fill me-1 text-danger"></i>
                                Not Connected
                            </th>

                        </tr>
                    </thead>

                    <tbody>
                        <tr>

                            {{-- ENGAGEMENT STATUS --}}
                            <td>
                                <div class="d-flex flex-column gap-2">

                                    <div
                                        class="d-flex justify-content-between align-items-center px-2 py-1 rounded bg-danger-subtle">
                                        <span class="fw-semibold text-danger">
                                            <i class="bi bi-fire me-1"></i> Hot
                                        </span>

                                        <span
                                            class="badge bg-danger cursor-pointer hot-count-btn"
                                            id="hotCount{{ $userId }}"
                                            data-user="{{ $userId }}"
                                            style="
                                                cursor:pointer;
                                                padding:7px 12px;
                                                border-radius:8px;
                                                transition:.2s;
                                            ">
                                            {{ $row['engagement']['hot'] ?? 0 }}
                                        </span>
                                    </div>

                                    <div
                                        class="d-flex justify-content-between align-items-center px-2 py-1 rounded bg-primary-subtle">

                                        <span class="fw-semibold text-primary">
                                            <i class="bi bi-arrow-repeat me-1"></i> Duplicate Hot
                                        </span>

                                        <span
                                            class="badge bg-primary cursor-pointer hot-count-btn"
                                            id="duplicateHotCount{{ $userId }}"
                                            data-user="{{ $userId }}"
                                            data-type="duplicate_hot"
                                            style="
                                                cursor:pointer;
                                                padding:7px 12px;
                                                border-radius:8px;
                                                transition:.2s;
                                            ">

                                            {{ $row['engagement']['duplicate_hot'] ?? 0 }}


                                        </span>

                                    </div>


                                    <div
                                        class="d-flex justify-content-between align-items-center px-2 py-1 rounded bg-warning-subtle">
                                        <span class="fw-semibold text-warning">
                                            <i class="bi bi-brightness-high me-1"></i> Warm
                                        </span>

                                        <span class="badge bg-warning text-dark" id="warmCount{{ $userId }}">
                                            {{ $row['engagement']['warm'] ?? 0 }}
                                        </span>
                                    </div>

                                    <div
                                        class="d-flex justify-content-between align-items-center px-2 py-1 rounded bg-info-subtle">
                                        <span class="fw-semibold text-info">
                                            <i class="bi bi-snow me-1"></i> Cold
                                        </span>

                                        <span class="badge bg-info" id="coldCount{{ $userId }}">
                                            {{ $row['engagement']['cold'] ?? 0 }}
                                        </span>
                                    </div>

                                </div>
                            </td>

                            {{-- CIP --}}
                            <td class="text-center">

                                <div class="status-box cursor-pointer lead-filter-btn "
                                    data-user="{{ $userId }}"
                                    data-type="15">

                                    <div class="stat-value text-info"
                                        id="count15_{{ $userId }}"
                                        style="font-size: 28px;">
                                        {{ $row['status_counts'][15] ?? 0 }}
                                    </div>

                                    <small class="text-muted fw-semibold">
                                        Counselling In Progress
                                    </small>
                                </div>

                            </td>

                            <td class="text-center">

                                <div class="status-box cursor-pointer lead-filter-btn"
                                    data-user="{{ $userId }}"
                                    data-type="23">

                                    <div class="stat-value text-warning"
                                        id="count23_{{ $userId }}"
                                        style="font-size: 28px;">
                                        {{ $row['status_counts'][23] ?? 0 }}
                                    </div>

                                    <small class="text-muted fw-semibold">
                                        Application Process
                                    </small>
                                </div>

                            </td>

                            <td class="text-center">

                                <div class="status-box cursor-pointer lead-filter-btn"
                                    data-user="{{ $userId }}"
                                    data-type="30">

                                    <div class="stat-value text-primary"
                                        id="count30_{{ $userId }}"
                                        style="font-size: 28px;">
                                        {{ $row['status_counts'][30] ?? 0 }}
                                    </div>

                                    <small class="text-muted fw-semibold">
                                        Offer Stage
                                    </small>
                                </div>

                            </td>

                            <td class="text-center">

                                <div class="status-box cursor-pointer lead-filter-btn"
                                    data-user="{{ $userId }}"
                                    data-type="48">

                                    <div class="stat-value text-success"
                                        id="count48_{{ $userId }}"
                                        style="font-size: 28px;">
                                        {{ $row['status_counts'][48] ?? 0 }}
                                    </div>

                                    <small class="text-muted fw-semibold">
                                        Converted
                                    </small>
                                </div>

                            </td>

                            <td class="text-center">

                                <div class="status-box cursor-pointer lead-filter-btn"
                                    data-user="{{ $userId }}"
                                    data-type="8">

                                    <div class="stat-value text-success"
                                        id="count8_{{ $userId }}"
                                        style="font-size: 28px;">
                                        {{ $row['status_counts'][8] ?? 0 }}
                                    </div>

                                    <small class="text-muted fw-semibold">
                                        Not Connected
                                    </small>
                                </div>

                            </td>

                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Lead Status Table --}}
            <!-- <h6 class="mb-3">Lead Status Breakdown</h6>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>Status Category</th>
                            <th class="text-center">Count</th>
                            <th>Sub-Status Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($row['statuses'] as $bucket => $bucketData)
                        @if($bucketData['count'] > 0)
                        <tr>
                            <td>
                                <strong>{{ $bucket }}</strong>
                            </td>
                            <td class="text-center">
                                <span class="badge
                                 @if($bucket == 'Converted') bg-success
                                 @elseif($bucket == 'Lost') bg-danger
                                 @elseif($bucket == 'Counselling in Progress') bg-primary
                                 @else bg-secondary
                                 @endif
                             ">{{ $bucketData['count'] }}</span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($bucketData['sub_status'] as $status => $count)
                                    @if($count > 0)
                                    <span class="badge bg-light text-dark">
                                        {{ $status }}: <strong>{{ $count }}</strong>
                                    </span>
                                    @endif
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                        @endif
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">No lead data available</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div> -->

            {{-- Follow-up Activities --}}
            <!-- <h6 class="mt-4 mb-3">Follow-up Activities</h6>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>Activity Type</th>
                            <th class="text-center">Total</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Call</strong></td>
                            <td class="text-center">
                                <span class="badge bg-danger">{{ $row['followups']['Call'] ?? 0 }}</span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    Connected: {{ $row['call_stats']['Call']['Connected'] ?? 0 }} |
                                    Not Connected: {{ $row['call_stats']['Call']['Not Connected'] ?? 0 }}
                                </small>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>WhatsApp Call</strong></td>
                            <td class="text-center">
                                <span class="badge bg-success">{{ $row['followups']['WhatsApp Call'] ?? 0 }}</span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    Connected: {{ $row['call_stats']['WhatsApp Call']['Connected'] ?? 0 }} |
                                    Not Connected: {{ $row['call_stats']['WhatsApp Call']['Not Connected'] ?? 0 }}
                                </small>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>WhatsApp Message</strong></td>
                            <td class="text-center">
                                <span class="badge bg-success">{{ $row['followups']['Whatsapp'] ?? 0 }}</span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    Discussion Start: {{ $row['whatsapp_stats']['Discussion Start'] ?? 0 }} |
                                    No Response: {{ $row['whatsapp_stats']['No Response'] ?? 0 }}
                                </small>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div> -->
        </div>

        {{-- ===== HOT LEADS SECTION ===== --}}
        <!-- @if(count($row['hot_leads']) > 0)
        <div class="report-section">
            <h5><i class="bi bi-fire me-2" style="color: #dc3545;"></i>Hot Leads ({{ count($row['hot_leads']) }})</h5>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Lead Name</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>Country</th>
                            <th>Course</th>
                            <th>Campaign</th>
                            <th>Date</th>
                            <th>Verified</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($row['hot_leads'] as $index => $lead)
                        <tr>
                            <td><strong>{{ $index + 1 }}</strong></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center flex-shrink-0"
                                        style="width: 32px; height: 32px; font-size: 12px; font-weight: bold;">
                                        {{ substr($lead['lead_name'], 0, 1) }}
                                    </div>
                                    <span>{{ $lead['lead_name'] }}</span>
                                </div>
                            </td>
                            <td>{{ $lead['email'] }}</td>
                            <td>{{ $lead['contact_no'] }}</td>
                            <td>{{ $lead['country'] }}</td>
                            <td>{{ $lead['course'] }}</td>
                            <td>{{ $lead['campaign_name'] }}</td>
                            <td>{{ $lead['date'] !== 'N/A' ? \Carbon\Carbon::parse($lead['date'])->format('M d, Y') : 'N/A' }}
                            </td>
                            <td>
                                @if($lead['verified_lead'])
                                <span class="badge bg-success">✓ Yes</span>
                                @else
                                <span class="badge bg-secondary">No</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif -->

        {{-- ===================== DYNAMIC LEADS TABLE ===================== --}}
        <div class="report-section mt-4">

            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 id="dynamicLeadTitle{{ $userId }}">
                    <i class="bi bi-fire me-2 text-danger"></i>
                    Hot Leads ({{ count($row['hot_leads']) }})
                </h5>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead id="dynamicLeadHead{{ $userId }}">
                        <tr>
                            <th>#</th>
                            <th class="text-start">Lead Details</th>
                            <!-- <th>Email</th>
                            <th>Contact</th>
                            <th>Country</th> -->
                            <th>Course</th>
                            <th>Status</th>
                            <th>Sub Status</th>
                            <th>Date</th>
                            <th>Verified</th>
                        </tr>
                    </thead>

                    <tbody id="dynamicLeadTable{{ $userId }}">

                        {{-- DEFAULT HOT LEADS --}}
                        @foreach($row['hot_leads'] as $index => $lead)

                        <tr>

                            <td><strong>{{ $index + 1 }}</strong></td>

                            <td>
                                <div class="d-flex flex-column align-items-start">
                                    <div class="fw-bold text-dark mb-1" style="font-size:14px;">
                                        {{ $lead['lead_name'] }}
                                    </div>

                                    <!-- CONTACT -->
                                    <div class="text-muted mb-1" style="font-size:12px;">
                                        {{ $lead['contact_no'] }}
                                    </div>

                                    <!-- EMAIL -->
                                    <div class="text-muted mb-1" style="font-size:12px;">
                                        {{ $lead['email'] }}
                                    </div>

                                    <!-- COUNTRY -->
                                    <div class="text-muted" style="font-size:12px;">
                                        <i class="fas fa-map-marker-alt  me-1"></i>
                                        {{ $lead['country'] }}
                                    </div>
                                </div>
                            </td>





                            <td>{{ $lead['course'] }}</td>

                            <td>{{ $lead['lead_bucket_name'] ?? 'N/A' }}</td>
                            <td>{{ ucfirst($lead['lead_status'] ?? 'N/A') }}</td>

                            <td>
                                {{ $lead['date'] !== 'N/A'
                            ? \Carbon\Carbon::parse($lead['date'])->format('M d, Y')
                            : 'N/A'
                        }}
                            </td>

                            <td>
                                @if($lead['verified_lead'])
                                <span class="badge bg-success">✓ Yes</span>
                                @else
                                <span class="badge bg-secondary">No</span>
                                @endif
                            </td>
                        </tr>

                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>

        {{-- ===== PREVIOUS LEADS - STATUS TRANSITIONS ===== --}}
        <div class="report-section">
            <h5><i class="bi bi-arrow-left-right me-2" style="color: #6c757d;"></i>Lead Status Transitions</h5>

            @if(count($row['warm_to_hot']) > 0 || count($row['hot_to_warm']) > 0)
            {{-- Warm to Hot --}}
            @if(count($row['warm_to_hot']) > 0)
            <div class="mb-4">
                <h6 class="mb-3">
                    <i class="bi bi-arrow-up-circle me-2" style="color: #28a745;"></i>
                    Warm → Hot Transitions ({{ count($row['warm_to_hot']) }})
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Lead Name</th>
                                <th>Email</th>
                                <th>Contact</th>
                                <th>Country</th>
                                <th>Course</th>
                                <th>Campaign</th>
                                <th>Date</th>
                                <th>Verified</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($row['warm_to_hot'] as $index => $lead)
                            <tr style="background: #f0fff4; border-left: 4px solid #28a745;">
                                <td><strong>{{ $index + 1 }}</strong></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center flex-shrink-0"
                                            style="width: 32px; height: 32px; font-size: 12px; font-weight: bold;">
                                            {{ substr($lead['lead_name'], 0, 1) }}
                                        </div>
                                        <span>{{ $lead['lead_name'] }}</span>
                                    </div>
                                </td>
                                <td>{{ $lead['email'] }}</td>
                                <td>{{ $lead['contact_no'] }}</td>
                                <td>{{ $lead['country'] }}</td>
                                <td>{{ $lead['course'] }}</td>
                                <td>{{ $lead['campaign_name'] }}</td>
                                <td>{{ $lead['date'] !== 'N/A' ? \Carbon\Carbon::parse($lead['date'])->format('M d, Y') : 'N/A' }}
                                </td>
                                <td>
                                    @if($lead['verified_lead'])
                                    <span class="badge bg-success">✓ Yes</span>
                                    @else
                                    <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Hot to Warm --}}
            @if(count($row['hot_to_warm']) > 0)
            <div class="mb-4">
                <h6 class="mb-3">
                    <i class="bi bi-arrow-down-circle me-2" style="color: #ffc107;"></i>
                    Hot → Warm Transitions ({{ count($row['hot_to_warm']) }})
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Lead Details</th>
                                <th>Email</th>
                                <th>Contact</th>
                                <th>Country</th>
                                <th>Course</th>
                                <th>Campaign</th>
                                <th>Date</th>
                                <th>Verified</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($row['hot_to_warm'] as $index => $lead)
                            <tr style="background: #fffbf0; border-left: 4px solid #ffc107;">
                                <td><strong>{{ $index + 1 }}</strong></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center flex-shrink-0"
                                            style="width: 32px; height: 32px; font-size: 12px; font-weight: bold;">
                                            {{ substr($lead['lead_name'], 0, 1) }}
                                        </div>
                                        <span>{{ $lead['lead_name'] }}</span>
                                    </div>
                                </td>
                                <td>{{ $lead['email'] }}</td>
                                <td>{{ $lead['contact_no'] }}</td>
                                <td>{{ $lead['country'] }}</td>
                                <td>{{ $lead['course'] }}</td>
                                <td>{{ $lead['campaign_name'] }}</td>
                                <td>{{ $lead['date'] !== 'N/A' ? \Carbon\Carbon::parse($lead['date'])->format('M d, Y') : 'N/A' }}
                                </td>
                                <td>
                                    @if($lead['verified_lead'])
                                    <span class="badge bg-success">✓ Yes</span>
                                    @else
                                    <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
            @else
            <div class="alert alert-info" role="alert">
                <i class="bi bi-info-circle me-2"></i>No status transitions found for this period.
            </div>
            @endif
        </div>

        @empty
        <div class="empty-state">
            <i class="bi bi-inbox" style="font-size: 48px; color: #ccc;"></i>
            <p class="mt-3">No data available for the selected criteria.</p>
        </div>
        @endforelse

    </div>

</div>

<!-- HISTORY MODAL -->
<div class="modal fade" id="historyModal" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold">
                    <i class="feather-clock text-primary me-2"></i>
                    Lead History
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body bg-white">

                <ul class="list-unstyled mb-0 activity-feed-1" id="historyContent">
                </ul>

            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const presetButtons = document.querySelectorAll('.preset-btn');
        const startDateInput = document.getElementById('start-date');
        const endDateInput = document.getElementById('end-date');
        const form = document.getElementById('date-filter-form');

        function getDateRange(preset) {
            const today = new Date();
            let start, end;

            switch (preset) {
                case 'today':
                    start = new Date(today);
                    end = new Date(today);
                    break;
                case 'yesterday':
                    start = new Date(today);
                    start.setDate(start.getDate() - 1);
                    end = new Date(start);
                    break;
                case '7days':
                    start = new Date(today);
                    start.setDate(start.getDate() - 7);
                    end = new Date(today);
                    break;
                case '30days':
                    start = new Date(today);
                    start.setDate(start.getDate() - 30);
                    end = new Date(today);
                    break;
                case 'this-month':
                    start = new Date(today.getFullYear(), today.getMonth(), 1);
                    end = new Date(today);
                    break;
                case 'last-month':
                    start = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                    end = new Date(today.getFullYear(), today.getMonth(), 0);
                    break;
                default:
                    return null;
            }

            return {
                start: start.toISOString().split('T')[0],
                end: end.toISOString().split('T')[0]
            };
        }

        presetButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const preset = this.dataset.preset;

                // Update active button
                presetButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');

                if (preset === 'custom') {
                    startDateInput.focus();
                } else {
                    const range = getDateRange(preset);
                    if (range) {
                        startDateInput.value = range.start;
                        endDateInput.value = range.end;
                        form.submit();
                    }
                }
            });
        });

        // Set active button based on current filter
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;

        if (startDate || endDate) {
            // Check if it matches any preset, otherwise mark custom as active
            let isPreset = false;
            presetButtons.forEach(btn => {
                if (btn.dataset.preset !== 'custom') {
                    const range = getDateRange(btn.dataset.preset);
                    if (range && range.start === startDate && range.end === endDate) {
                        btn.classList.add('active');
                        isPreset = true;
                    } else {
                        btn.classList.remove('active');
                    }
                }
            });

            if (!isPreset) {
                document.querySelector('[data-preset="custom"]').classList.add('active');
            }
        }
    });
</script>

<!-- <script>
    document.addEventListener('DOMContentLoaded', function() {

        const buttons = document.querySelectorAll('.lead-filter-btn');

        buttons.forEach(button => {

            button.addEventListener('click', function() {

                let bucketId = this.dataset.type;
                let userId = this.dataset.user;

                console.log(bucketId);

                let table = document.getElementById(`dynamicLeadTable${userId}`);

                let title = document.getElementById(`dynamicLeadTitle${userId}`);

                let from = document.getElementById('start-date').value;
                let to = document.getElementById('end-date').value;

                table.innerHTML = `
    <tr>
        <td colspan="9" class="text-center py-5">

            <div class="d-flex flex-column align-items-center justify-content-center">

                <div class="spinner-border text-primary mb-3"
                    role="status"
                    style="width:1.2rem;height:1.2rem;">
                </div>

                <span class="text-muted fw-semibold">
                    Loading leads...
                </span>

            </div>

        </td>
    </tr>
`;

                /*
                ==========================================
                YAHAN API / AJAX LAGANA HOGA
                ==========================================

                type values:
                cip
                application_process
                offer_stage
                converted

                backend se lead_bucket ke basis pr leads lao
                */

                fetch(`{{ route('get.leads.by.type') }}?bucket_id=${bucketId}&ower_id=${userId}&from=${from}&to=${to}`)

                    .then(response => response.json())

                    .then(data => {
                        table.innerHTML = '';

                        title.innerHTML = `
                        <i class="bi bi-list-check me-2 text-primary"></i>
                        ${data.title} (${data.leads.length})
                    `;

                        if (data.leads.length === 0) {

                            table.innerHTML = `
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    No Leads Found
                                </td>
                            </tr>
                        `;

                            return;
                        }

                        data.leads.forEach((lead, index) => {

                            table.innerHTML += `
                            <tr>

                                <td>
                                    <strong>${index + 1}</strong>
                                </td>

                                <td>
                                    <div class="d-flex align-items-center gap-2">

                                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center flex-shrink-0"
                                            style="width:32px;height:32px;font-size:12px;font-weight:bold;">

                                            ${lead.lead_name.charAt(0)}

                                        </div>

                                        <span>${lead.lead_name}</span>

                                    </div>
                                </td>

                                <td>${lead.email ?? ''}</td>

                                <td>${lead.contact_no ?? ''}</td>

                                <td>${lead.country ?? ''}</td>

                                <td>${lead.course ?? ''}</td>

                                <td>${lead.campaign_name ?? ''}</td>

                                <td>${lead.date ?? ''}</td>

                                <td>
                                    ${
                                        lead.verified_lead == 1
                                        ? '<span class="badge bg-success">✓ Yes</span>'
                                        : '<span class="badge bg-secondary">No</span>'
                                    }
                                </td>

                            </tr>
                        `;
                        });

                    });

            });

        });

    });
</script> -->

<script>
    document.addEventListener('DOMContentLoaded', function() {

        let selectedRanges = {};

        // DEFAULT TODAY
        document.querySelectorAll('.lead-toggle-group').forEach(group => {

            let userId = group.dataset.user;

            selectedRanges[userId] = 'today';
        });

        // TOGGLE CLICK
        document.querySelectorAll('.lead-date-btn').forEach(btn => {

            btn.addEventListener('click', function() {

                let userId = this.dataset.user;
                let range = this.dataset.range;

                selectedRanges[userId] = range;

                // BUTTON ACTIVE UI
                let group = this.closest('.lead-toggle-group');

                group.querySelectorAll('.lead-date-btn').forEach(b => {

                    b.classList.remove('btn-primary', 'active');
                    b.classList.add('btn-outline-primary');
                });

                this.classList.remove('btn-outline-primary');
                this.classList.add('btn-primary', 'active');

                let from = '';
                let to = '';

                // ONLY for today
                if (range === 'today') {

                    from = document.getElementById('start-date')?.value || '';
                    to = document.getElementById('end-date')?.value || '';
                }

                loadUserReport(userId, range, from, to);
            });

        });

        // STATUS CLICK
        document.querySelectorAll('.lead-filter-btn').forEach(button => {

            button.addEventListener('click', function() {

                let userId = this.dataset.user;
                let bucketId = this.dataset.type;

                // 🔥 remove active from all TDs of this user
                document.querySelectorAll(`#count${bucketId}_${userId}`).forEach(() => {});

                let section = this.closest('.report-section');

                section.querySelectorAll('.active-status')
                    .forEach(el => el.classList.remove('active-status'));

                // 🔥 add active to clicked TD
                let td = this.closest('td');
                if (td) {
                    td.classList.add('active-status');
                }

                let range = selectedRanges[userId] || 'today';

                loadLeadTable(userId, bucketId, range);
            });

        });

        // HOT COUNT CLICK
        document.querySelectorAll('.hot-count-btn').forEach(btn => {

            btn.addEventListener('click', function(e) {

                e.stopPropagation();

                let userId = this.dataset.user;
                let range = selectedRanges[userId] || 'today';
                let type = this.dataset.type || 'hot';

                let section = this.closest('.report-section');

                // 🔥 REMOVE ACTIVE FROM ALL STATUS BOXES (CIP / APPLICATION / etc.)
                section.querySelectorAll('.lead-filter-btn').forEach(el => {
                    el.classList.remove('active-status');
                });

                // optional: also remove direct TD highlight if any
                section.querySelectorAll('td.active-status').forEach(td => {
                    td.classList.remove('active-status');
                });

                // 🔥 NOW LOAD HOT / DUPLICATE HOT DATA
                loadLeadTable(userId, type, range);

            });

        });

    });


    // LOAD COUNTS
    function loadUserReport(userId, range, from = '', to = '') {
        let url = `{{ route('get.user.report.data') }}?user_id=${userId}&range=${range}`;

        if (range === 'today' && from && to) {
            url += `&from=${from}&to=${to}`;
        }

        fetch(url)

            .then(res => res.json())

            .then(data => {

                document.getElementById(`totalLeadCount${userId}`).innerHTML = `
    
                        <i class="bi bi-people-fill text-primary fs-5"></i>
                    
                        <span class="text-muted fw-semibold">
                            Total Leads :
                        </span>
                    
                        <span class="fw-bold text-primary fs-5">
                            ${data.total}
                        </span>
                    
                    `;

                document.getElementById(`count15_${userId}`).innerHTML =
                    data.status_counts[15] ?? 0;

                document.getElementById(`count23_${userId}`).innerHTML =
                    data.status_counts[23] ?? 0;

                document.getElementById(`count30_${userId}`).innerHTML =
                    data.status_counts[30] ?? 0;

                document.getElementById(`count48_${userId}`).innerHTML =
                    data.status_counts[48] ?? 0;

                document.getElementById(`count8_${userId}`).innerHTML =
                    data.status_counts[8] ?? 0;

                document.getElementById(`hotCount${userId}`).innerHTML =
                    data.engagement.hot ?? 0;

                document.getElementById(`warmCount${userId}`).innerHTML =
                    data.engagement.warm ?? 0;

                document.getElementById(`coldCount${userId}`).innerHTML =
                    data.engagement.cold ?? 0;

                document.getElementById(`duplicateHotCount${userId}`).innerHTML =
                    data.engagement.duplicate_hot ?? 0;

                // DEFAULT HOT TABLE LOAD
                loadLeadTable(userId, 'hot', range);

            });

    }


    // LOAD TABLE
    function loadLeadTable(userId, bucketId, range) {

        let table = document.getElementById(`dynamicLeadTable${userId}`);
        let title = document.getElementById(`dynamicLeadTitle${userId}`);

        table.innerHTML = `
        <tr>
            <td colspan="9" class="text-center py-4">
                Loading...
            </td>
        </tr>
    `;

        fetch(`{{ route('get.leads.by.type') }}?bucket_id=${bucketId}&ower_id=${userId}&range=${range}`)

            .then(response => response.json())

            .then(data => {

                table.innerHTML = '';

                if (data.is_history) {

                    setHistoryHeader(userId);

                } else {

                    setNormalHeader(userId);
                }

                title.innerHTML = `
                <i class="bi bi-list-check me-2 text-primary"></i>
                ${data.title} (${data.leads.length})
            `;

                if (data.leads.length == 0) {

                    table.innerHTML = `
                    <tr>
                        <td colspan="9" class="text-center">
                            No Data Found
                        </td>
                    </tr>
                `;
                    return;
                }

                // 🔥 CHECK IF HISTORY MODE
                if (data.is_history) {

                    // ===============================
                    //  CALLBACK HISTORY TABLE VIEW
                    // ===============================

                    data.leads.forEach((lead, index) => {

                        table.innerHTML += `
                         <tr>

                            <td><strong>${index + 1}</strong></td>

                            <td>
                                <div class="fw-bold">${lead.lead_name}</div>
                                <small>${lead.email}</small>
                            </td>

                            <td>${lead.course}</td>

                            <td>${lead.lead_bucket_name}</td>

                            <td>${lead.lead_status}</td>

                            <td>
                                ${lead.date ? new Date(lead.date).toDateString() : ''}
                            </td>

                            <td>
                                ${
                                    lead.verified_lead == 1
                                    ? '<span class="badge bg-success">Yes</span>'
                                    : '<span class="badge bg-secondary">No</span>'
                                }
                          <td class="text-end">
                              <button 
                                  class="btn btn-sm btn-outline-secondary rounded-circle"
                                  onclick='showHistoryModal(${JSON.stringify(data.dublicate_hots)})'
                                  title="History"
                              >
                                  <i class="feather-clock"></i>
                              </button>
                          </td>

                        </tr>
                    `;
                    });

                } else {

                    // ===============================
                    // 📌 NORMAL LEADS TABLE VIEW
                    // ===============================

                    data.leads.forEach((lead, index) => {

                        table.innerHTML += `
                        <tr>

                            <td><strong>${index + 1}</strong></td>

                            <td>
                                <div class="fw-bold">${lead.lead_name}</div>
                                <small>${lead.email}</small>
                            </td>

                            <td>${lead.course}</td>

                            <td>${lead.lead_bucket_name}</td>

                            <td>${lead.lead_status}</td>

                            <td>
                                ${lead.date ? new Date(lead.date).toDateString() : ''}
                            </td>

                            <td>
                                ${
                                    lead.verified_lead == 1
                                    ? '<span class="badge bg-success">Yes</span>'
                                    : '<span class="badge bg-secondary">No</span>'
                                }
                            </td>

                        </tr>
                    `;
                    });
                }

            });

    }

    function setNormalHeader(userId) {

        document.getElementById(`dynamicLeadHead${userId}`).innerHTML = `
        <tr>
            <th>#</th>
            <th>Lead Details</th>
            <th>Course</th>
            <th>Bucket</th>
            <th>Status</th>
            <th>Date</th>
            <th>Verified</th>
        </tr>
    `;
    }

    function setHistoryHeader(userId) {

        document.getElementById(`dynamicLeadHead${userId}`).innerHTML = `
        <tr>
             <th>#</th>
            <th>Lead Details</th>
            <th>Course</th>
            <th>Bucket</th>
            <th>Status</th>
            <th>Date</th>
            <th>Verified</th>
            <th>History</th>
        </tr>
    `;
    }
</script>
<script>
    function showHistoryModal(histories) {
        let html = '';

        if (histories.length > 0) {
            histories.forEach((item) => {

                html += `

            <li class="feed-item feed-item-primary mb-4">

                <div class="d-flex gap-4 justify-content-between">

                    <div>

                        <div class="mb-2">
                            <a href="javascript:void(0)" class="fw-semibold text-dark">

                                ${item.lead?.user?.name ?? 'N/A'} <span>${item.lead_id ?? 'N/A'}</span>
                            </a>

                        </div>

                        <p class="fs-12 text-muted mb-2">
                            ${item.message ?? 'No Message'}
                        </p>

                        <div class="d-flex flex-wrap gap-2">

                            <span class="badge text-success border border-dashed border-gray-500">
                                ${item.lead_engagement_status ?? 'N/A'}
                            </span>

                        </div>

                    </div>

                    <div class="fs-10 fw-medium text-uppercase text-muted text-nowrap">

                         ${item.created_at ? new Date(item.created_at).toLocaleDateString() : ''}</br>
                         ${item?.user?.name ?? 'N/A'}

                    </div>

                </div>

            </li>

            `;
            });
        } else {
            html = `
            <div class="text-center text-muted py-5">
                No History Found
            </div>
        `;
        }

        document.getElementById('historyContent').innerHTML = html;

        let modal = new bootstrap.Modal(document.getElementById('historyModal'));

        modal.show();
    }
</script>

@endsection