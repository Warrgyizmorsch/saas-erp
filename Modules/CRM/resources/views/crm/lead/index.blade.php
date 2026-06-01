@extends('shared::layouts.app')

@section('content')

    <style>
        .page-url-col {
            max-width: 260px;
            width: 260px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .page-url-link {
            display: inline-block;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Sticky header */
        #leadList thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        /* Sticky first column (Actions column) */
        #leadList tbody td:first-child,
        #leadList thead th:first-child {
            position: sticky;
            left: 0;
            z-index: 20;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        #leadList tbody td:nth-child(2),
        #leadList thead th:nth-child(2) {
            position: sticky;
            left: 182px;
            z-index: 19;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }


        /* Ensure horizontal scroll on overflow */
        .table-responsive {
            overflow-x: auto;
        }

        /* Extra attribute columns (both th & td) */
        .highlight-column {
            background-color: #fafafaf5 !important;
            /* soft light background */

        }
    </style>

    <style>
        /* Base transition */
        .select2-container--bootstrap-5 .select2-selection {
            transition: all 0.3s ease;
            font-weight: 600;
        }

        /* HOT */
        .select2-selection.hot {
            background-color: #ffeaea !important;
            border: 1px solid #ff4d4f !important;
            color: #c0392b !important;
        }

        /* WARM */
        .select2-selection.warm {
            background-color: #fff6e5 !important;
            border: 1px solid #ffa940 !important;
            color: #d35400 !important;
        }

        /* COLD */
        .select2-selection.cold {
            background-color: #e6f4ff !important;
            border: 1px solid #4096ff !important;
            color: #1677ff !important;
        }

        /* DEAD */
        .select2-selection.dead {
            background-color: #f5f5f5 !important;
            border: 1px solid #bfbfbf !important;
            color: #8c8c8c !important;
        }
    </style>
    <style>
        /* Fix comment column width */
        .last-comment-col {
            width: 350px;
        }

        /* 3 line clamp */
        .comment-text {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            white-space: normal;
        }

        /* Expanded */
        .comment-text.expanded {
            display: block;
            -webkit-line-clamp: unset;
            overflow: visible;
            white-space: normal;
        }

        .read-more-btn {
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
        }
    </style>
    <script>
        function toggleComment(id) {
            const text = document.getElementById('comment-' + id);
            const btn = document.getElementById('btn-' + id);

            text.classList.toggle('expanded');

            if (text.classList.contains('expanded')) {
                btn.innerText = "Show Less";
            } else {
                btn.innerText = "Read More";
            }
        }
    </script>
    {{-- Page Header --}}
    <x-crm::lead.tools :filterBucket="$filterBucket" :buckets="$buckets" :totalLeadsCount="$totalLeadsCount"
        :filteredLeadCount="$filteredLeadCount" />

    {{-- [ Page Header ] end --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ⚠️ Error / Validation Warnings --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
            <strong>Whoops!</strong> There were some problems with your input:
            <ul class="mb-0 mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Collapsible Lead Stats --}}
    <div id="collapseOne"
        class="accordion-collapse collapsed page-header-collapse {{ request('search') || request('from') || request('to') || request('source') || request('status') || request('lead_owner') || request('country') || request('course') || request('campaign_name') || request('adset_name') || request('ad_name') ? 'show' : '' }}">
        <div class="accordion-body pb-2">
            <form method="GET" action="{{ route('lead.index') }}" class="row g-3 mb-4">

                <!-- 👇 Preserve bucket_id -->
                @if(request('bucket_id'))
                    <input type="hidden" name="bucket_id" value="{{ request('bucket_id') }}">
                @endif

                <!-- Search -->
                <div class="col-md-3">
                    <label class="form-label">Search (Name/Email/Contact)</label>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}">
                </div>

                <!-- Date From -->
                <div class="col-md-3">
                    <label class="form-label">From</label>
                    <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                </div>

                <!-- Date To -->
                <div class="col-md-3">
                    <label class="form-label">To</label>
                    <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                </div>

                <!-- Source -->
                <div class="col-md-3">
                    <label class="form-label">Source</label>
                    <select name="source" id="source" class="form-control" data-select2-selector="tag">
                        <option value="">Select or Type Source</option>
                        @foreach($sources as $source)
                            <option value="{{ $source }}" {{ request('source') == $source ? 'selected' : '' }}>
                                {{ $source }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status (children of bucket) -->
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" data-select2-selector="tag">
                        <option value="">All Status</option>
                        @foreach($buckets as $bucket)
                            @if($bucket->children)
                                <optgroup label="{{ $bucket->name }}">
                                    @foreach($bucket->children as $child)
                                        <option value="{{ $child->name }}" {{ request('status') == $child->name ? 'selected' : '' }}>
                                            {{ $child->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        @endforeach
                    </select>
                </div>

                <!-- Lead Owner -->
                <div class="col-md-3">
                    <label class="form-label">Lead Owner</label>
                    <select name="owner_id" class="form-select" data-select2-selector="tag">
                        <option value="">All Owners</option>
                        @foreach($owners as $owner)
                            <option value="{{ $owner->id }}" {{ request('lead_owner') == $owner->id ? 'selected' : '' }}>
                                {{ $owner->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Country -->
                <div class="col-md-3">
                    <label class="form-label">Applied Country</label>
                    <input type="text" name="country" class="form-control" value="{{ request('country') }}">
                </div>

                <!-- Course -->
                <div class="col-md-3">
                    <label class="form-label">Course</label>
                    <input type="text" name="course" class="form-control" value="{{ request('course') }}">
                </div>

                <!-- Campaign Name -->
                <div class="col-md-3">
                    <label class="form-label">Campaign Name</label>
                    <input type="text" name="campaign_name" class="form-control" value="{{ request('campaign_name') }}">
                </div>

                <!-- Adset Name -->
                <div class="col-md-3">
                    <label class="form-label">Adset Name</label>
                    <input type="text" name="adset_name" class="form-control" value="{{ request('adset_name') }}">
                </div>

                <!-- Ad Name -->
                <div class="col-md-3">
                    <label class="form-label">Ad Name</label>
                    <input type="text" name="ad_name" class="form-control" value="{{ request('ad_name') }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">old bucket</label>
                    <select name="old_bucket_id" class="form-select" data-select2-selector="tag">
                        <option value="">All Old bucket</option>
                        @foreach($oldbuckets as $oldbucket)
                            <option value="{{ $oldbucket->id }}" {{ request('old_bucket_id') == $oldbucket->id ? 'selected' : '' }}>
                                {{ $oldbucket->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">old SubStatus</label>
                    <select name="old_sub_status" class="form-select" data-select2-selector="tag">
                        <option value="">All Old SubStatus</option>
                        @foreach($oldSubStatus as $oldstatus)
                            <option value="{{ $oldstatus}}" {{ request('old_sub_status') == $oldstatus ? 'selected' : '' }}>
                                {{ $oldstatus}}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Buttons -->
                <div class="col-12 d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ request('bucket_id') ? route('lead.index', ['bucket_id' => request('bucket_id')]) : route('lead.index') }}"
                        class="btn btn-danger">
                        Reset
                    </a>
                    @php
                        // Check if any filter is active
                        $filtersApplied = request('search') || request('from') || request('to') || request('source') || request('status') || request('lead_owner') || request('country') || request('course') || request('campaign_name') || request('adset_name') || request('ad_name');
                    @endphp
                    <!-- Export Toggle Button (only show if filter applied) -->
                    @if($filtersApplied)
                        <button type="button" class="btn btn-success" id="exportToggleBtn">Export Filter Data</button>
                    @endif
                </div>
            </form>
            <!-- Export Columns Section (Hidden initially) -->
            <div class="card p-3 mb-4 border-light shadow-sm mt-2" id="exportColumnsSection" style="display: none;">
                <form action="{{ route('lead.export') }}" method="GET" id="exportForm" class="row g-2 align-items-center">

                    @foreach(request()->query() as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach

                    <!-- Column Selection -->
                    <div class="col-12 col-md-8 d-flex flex-wrap gap-2">
                        @php
                            // Columns that should be selected by default
                            $defaultColumns = ['lead_id', 'lead_name', 'email', 'contact_no'];

                            // All available columns for export
                            $exportColumns = [
                                'lead_id' => 'Lead ID',
                                'lead_name' => 'Name',
                                'email' => 'Email',
                                'contact_no' => 'Contact',
                                'date' => 'Date',
                                'bucket' => 'Bucket',
                                'status' => 'Status',
                                'owner' => 'Owner',
                                'source' => 'Source',
                                'country' => 'Country',
                                'course' => 'Course'
                            ];
                        @endphp

                        <div class="col-12 col-md-8 d-flex flex-wrap gap-2">
                            @foreach($exportColumns as $value => $label)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="columns[]" value="{{ $value }}" {{ in_array($value, $defaultColumns) ? 'checked' : '' }}>
                                    <label class="form-check-label">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>

                    </div>

                    <!-- Export Button -->
                    <div class="col-12 col-md-4 text-md-end mt-2 mt-md-0">
                        <button type="submit" class="btn btn-success" id="downloadBtn">
                            <i class="bi bi-download me-1"></i> Download Excel
                        </button>
                    </div>
                </form>
            </div>

            <!-- JS to toggle export section -->
            <script>
                document.getElementById('exportToggleBtn').addEventListener('click', function () {
                    const section = document.getElementById('exportColumnsSection');
                    section.style.display = section.style.display === 'none' ? 'block' : 'none';
                });
            </script>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const btn = document.getElementById('downloadBtn');
                    const form = document.getElementById('exportForm');

                    form.addEventListener('submit', () => {
                        btn.disabled = true;
                        btn.innerHTML = `
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Preparing file...
                            `;
                        // Button will restore automatically when the download popup appears
                        setTimeout(() => {
                            btn.disabled = false;
                            btn.innerHTML = `<i class="bi bi-download me-1"></i> Download Excel`;
                        }, 4000);
                    });
                });
            </script>
        </div>
    </div>

    <!-- [ page-header ] end -->
    <!-- [ Main Content ] start -->
    <div class="main-content">
        <!-- hide and show column functionality start -->
        <div class="mb-2">
            <button class="btn btn-light-primary" id="columnToggleBtn">
                <i class="fas fa-columns"></i> Columns
            </button>

            <div id="columnTogglePanel" class="column-toggle-panel shadow">
                <label><input type="checkbox" data-column="4"> City</label>
                <label><input type="checkbox" data-column="5"> Date</label>
                <label><input type="checkbox" data-column="6"> Source</label>

                <label><input type="checkbox" data-column="9"> Owner</label>
                <label><input type="checkbox" data-column="10"> Country</label>
                <label><input type="checkbox" data-column="11"> Course</label>
                <label><input type="checkbox" data-column="12"> E.T.S.</label>

                {{-- Only admin should see social ads columns --}}
                @if(Auth::user()->role_id == 1)
                    <label><input type="checkbox" data-column="13" checked> Campaign</label>
                    <label><input type="checkbox" data-column="14" checked> Adset</label>
                    <label><input type="checkbox" data-column="15" checked> Ad</label>
                    <label><input type="checkbox" data-column="16" checked> Form</label>
                    <label><input type="checkbox" data-column="17" checked> Url</label>
                @endif

                {{-- Only admin should see social ads columns --}}
                @if(Auth::user()->isAccount())
                    <label><input type="checkbox" data-column="13"> Url</label>
                @endif

            </div>
        </div>
        <style>
            .column-toggle-panel {
                position: absolute;
                background: #fff;
                border: 1px solid #e5e7eb;
                padding: 15px;
                border-radius: 8px;
                display: none;
                z-index: 1000;
                width: 200px;
            }

            .column-toggle-panel label {
                display: block;
                font-size: 13px;
                margin-bottom: 6px;
                cursor: pointer;
            }
        </style>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            $(document).ready(function () {

                const storageKey = "leadTableColumns";

                // columns hidden by default
                const defaultHidden = [4, 5, 6, 9, 10, 11, 12, 13, 14, 15, 16];

                // toggle column panel
                $("#columnToggleBtn").click(function () {
                    $("#columnTogglePanel").toggle();
                });


                function toggleColumn(index, show) {

                    if (show) {
                        $("#leadList th:nth-child(" + (index + 1) + "), #leadList td:nth-child(" + (index + 1) + ")").show();
                    } else {
                        $("#leadList th:nth-child(" + (index + 1) + "), #leadList td:nth-child(" + (index + 1) + ")").hide();
                    }

                }


                function saveColumns() {

                    let settings = {};

                    $("#columnTogglePanel input").each(function () {

                        const col = $(this).data("column");

                        settings[col] = $(this).is(":checked");

                    });

                    localStorage.setItem(storageKey, JSON.stringify(settings));

                }


                // LOAD SETTINGS
                let saved = JSON.parse(localStorage.getItem(storageKey));

                $("#columnTogglePanel input").each(function () {

                    const col = $(this).data("column");

                    if (saved) {

                        // apply saved state
                        if (saved[col] === false) {

                            $(this).prop("checked", false);
                            toggleColumn(col, false);

                        } else {

                            $(this).prop("checked", true);
                            toggleColumn(col, true);

                        }

                    } else {

                        // apply default hidden
                        if (defaultHidden.includes(col)) {

                            $(this).prop("checked", false);
                            toggleColumn(col, false);

                        } else {

                            $(this).prop("checked", true);
                            toggleColumn(col, true);

                        }

                    }

                });


                // checkbox change
                $("#columnTogglePanel input").change(function () {

                    const column = $(this).data("column");

                    const visible = $(this).is(":checked");

                    toggleColumn(column, visible);

                    saveColumns();

                });


                // close panel when clicking outside
                $(document).click(function (e) {

                    if (!$(e.target).closest("#columnToggleBtn, #columnTogglePanel").length) {
                        $("#columnTogglePanel").hide();
                    }

                });

            });
        </script>
        <!-- hide and show column functionality end -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover" id="leadList">
                                <thead>
                                    <tr>
                                        <th class="bg-white">Actions</th>
                                        <!-- <th class="wd-30">
                                                <div class="btn-group mb-1">
                                                    <div class="custom-control custom-checkbox ms-1">
                                                        <input type="checkbox" class="custom-control-input"
                                                            id="checkAllLead">
                                                        <label class="custom-control-label" for="checkAllLead"></label>
                                                    </div>
                                                </div>
                                            </th> -->
                                        <th class="bg-white">Detail</th>
                                        <th style="text-align: left;">Last Comment</th>
                                        <th>Engagement</th>
                                        <!-- <th>Name</th>
                                            <th>Contact</th> -->
                                        <th>City</th>
                                        <th>Date</th>
                                        <th>Source</th>
                                        <th>Status</th>
                                        <th>Sub Status</th>
                                        <th>Owner</th>
                                        <th>Country</th>
                                        <th>Course</th>
                                        <th>E.T.S.</th>
                                        <!-- <th>English Test Status</th> -->
                                        @if (Auth::user()->role_id == 1)
                                            <th>Campaign Name</th>
                                            <th>Adset Name</th>
                                            <th>Ad Name</th>
                                            <th>Form Name</th>
                                        @endif
                                        <!-- <th>Extra Fields</th> -->
                                        {{-- Dynamically add extra fields as table headers --}}
                                        @foreach($extraFieldNames as $field)
                                            <th class="highlight-column bg-white">{{ ucwords(str_replace('_', ' ', $field)) }}
                                            </th>
                                        @endforeach
                                        <th class="page-url-col">Page Url</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($leads as $lead)
                                        <tr>
                                            <!-- Actions -->
                                            <td class="bg-white">
                                                <div class="action-links">
                                                    <a href="{{ route('lead.edit', $lead->id) }}" class="btn-edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <!-- History Button -->
                                                    <a href="{{ route('lead.history', $lead->id) }}" class="btn-history">
                                                        <i class="fa fa-history"></i>
                                                    </a>
                                                    <a href="javascript:void(0);" class="btn btn-light-brand open-callback"
                                                        data-bs-toggle="offcanvas" data-bs-target="#proposalSent{{ $lead->id }}"
                                                        data-id="{{ $lead->id }}">
                                                        <i class="feather-phone me-2"></i>
                                                        <span>Call</span>
                                                    </a>

                                                    @include('crm::crm.lead.call-back')

                                                </div>
                                                @if($lead->messages->isNotEmpty())
                                                    <div class="mt-1">
                                                        <span class="badge bg-secondary" title="Last Callback Time">
                                                            <i class="fas fa-clock me-1"></i>
                                                            {{ $lead->messages->last()->updated_at->format('d M Y, h:i A') }}
                                                        </span>
                                                    </div>
                                                @endif
                                            </td>

                                            <!-- Checkbox -->
                                            <!-- <td>
                                                        <div class="custom-control custom-checkbox ms-1">
                                                            <input type="checkbox" class="custom-control-input"
                                                                id="lead{{ $lead->id }}">
                                                            <label class="custom-control-label" for="lead{{ $lead->id }}"></label>
                                                        </div>
                                                    </td> -->

                                            <!-- Lead Id -->
                                            <td class="bg-white">
                                                <!-- {{ $lead->id ?? 'N/A' }} -->
                                                <div>
                                                    <h6 class="mb-0 fw-semibold">
                                                        Lead #{{ $lead->id }}
                                                    </h6>

                                                    <small class="text-muted d-block">
                                                        {{ $lead->user->name ?? 'N/A' }}
                                                    </small>

                                                    <small class="text-muted">
                                                        {{ $lead->user->email ?? '' }}
                                                    </small>
                                                    <small class="text-muted d-block">
                                                        {{ $lead->user->contact_no ?? 'N/A' }}
                                                        @if($lead->verified_lead)
                                                            <span
                                                                style="color:#059669;font-size:11px;font-weight:600;margin-left:4px;">Verified</span>
                                                        @endif
                                                    </small>
                                                </div>
                                            </td>

                                            <td style="text-align: left;" class="last-comment-col">
                                                @php
                                                    $message = $lead->latestMessage->message ?? '';
                                                    $isLong = strlen($message) > 120;
                                                @endphp
                                                @if($lead->lastMessage)
                                                    <strong>{{ $lead->lastMessage->user->name ?? 'Unknown' }}</strong>
                                                    <small class="text-muted">:
                                                        {{ $lead->lastMessage->created_at->format('d M Y, h:i A') }}</small><br>
                                                    <!-- <span style="text-wrap: auto; font-size: 0.7rem;">{{ Str::limit($lead->lastMessage->message ?? '', 80) }}</span> -->
                                                    <p class="mb-1 fw-medium text-dark comment-text" id="comment-{{ $lead->id }}">
                                                        {{ $message }}
                                                    </p>

                                                    @if($isLong)
                                                        <span class="text-primary read-more-btn"
                                                            onclick="toggleComment({{ $lead->id }})" id="btn-{{ $lead->id }}">
                                                            Read More
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">No comments yet</span>
                                                @endif
                                            </td>

                                            <td style="max-width: 200px !important;">
                                                <form action="{{ route('lead.updateEngagementStatus', $lead->id) }}"
                                                    method="POST" class="engagement-form">
                                                    @csrf
                                                    @method('PUT')

                                                    <select name="lead_engagement_status"
                                                        class="form-control engagement-status-select"
                                                        data-select2-selector="tag">
                                                        <option value="">Select</option>
                                                        <option value="hot" {{ $lead->lead_engagement_status === 'hot' ? 'selected' : '' }}>Hot</option>
                                                        <option value="warm" {{ $lead->lead_engagement_status === 'warm' ? 'selected' : '' }}>Warm</option>
                                                        <option value="cold" {{ $lead->lead_engagement_status === 'cold' ? 'selected' : '' }}>Cold</option>
                                                        <option value="dead" {{ $lead->lead_engagement_status === 'dead' ? 'selected' : '' }}>Dead</option>
                                                    </select>
                                                </form>
                                            </td>

                                            <!-- Name -->
                                            <!-- <td>{{ $lead->user->name ?? 'N/A' }}</td> -->

                                            <!-- Contact -->
                                            <!-- <td>
                                                        {{ $lead->user->contact_no ?? 'N/A' }} 
                                                        @if($lead->verified_lead)
                                                            <span style="color:#059669;font-size:11px;font-weight:600;margin-left:4px;">Verified</span>
                                                        @endif
                                                        <br>
                                                        {{ $lead->user->email ?? '' }}
                                                    </td> -->

                                            <!-- City -->
                                            <td>{{ $lead->city ?? ($lead->user->city ?? 'N/A') }}</td>

                                            <!-- Date -->
                                            <td>{{ $lead->date ? $lead->date->format('d M Y') : 'N/A' }}</td>

                                            <!-- Source -->
                                            <td>
                                                @php
                                                    if (!empty($lead->platform)) {

                                                        // remove query params
                                                        $cleanUrl = strtok(trim($lead->platform), '?');

                                                        // if it is a URL show Website
                                                        if (filter_var($cleanUrl, FILTER_VALIDATE_URL)) {
                                                            $source = "Website";
                                                        } else {
                                                            $source = $cleanUrl;
                                                        }

                                                    } else {
                                                        $source = "Website";
                                                    }

                                                    // convert to short form (first letter of each word)
                                                    $words = preg_split('/[\s\-_]+/', $source);
                                                    $shortForm = '';

                                                    foreach ($words as $word) {
                                                        $shortForm .= strtoupper(substr($word, 0, 1));
                                                    }
                                                @endphp

                                                <span class="badge bg-soft-dark text-dark">
                                                    {{ $shortForm }}
                                                </span>
                                            </td>

                                            <!-- Bucket (relationship or ID) -->
                                            <td style="max-width: 200px !important;">
                                                <form action="{{ route('lead.updateBucket', $lead->id) }}" method="POST"
                                                    class="bucket-form">
                                                    @csrf
                                                    @method('PUT')

                                                    <select name="lead_bucket_id" class="form-control bucket-select"
                                                        data-select2-selector="tag">
                                                        <option value="">Select Bucket</option>
                                                        @foreach($buckets as $bucket)
                                                            <option data-bg="{{ $bucket->bucket_color }}" value="{{ $bucket->id }}"
                                                                {{ $lead->lead_bucket_id == $bucket->id ? 'selected' : '' }}>
                                                                {{ $bucket->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </form>
                                            </td>

                                            <!-- Status -->
                                            <td style="max-width: 200px !important;">
                                                <form action="{{ route('lead.updateStatus', $lead->id) }}" method="POST"
                                                    class="status-form">
                                                    @csrf
                                                    @method('PUT')
                                                    <select name="lead_status" class="form-control status-select"
                                                        data-select2-selector="tag">
                                                        <option value="">Select Status</option>
                                                        @if($lead->bucket && $lead->bucket->children)
                                                            @foreach($lead->bucket->children as $child)
                                                                <option data-bg="{{ $child->bucket_color }}" value="{{ $child->name }}"
                                                                    {{ $lead->lead_status == $child->name ? 'selected' : '' }}>
                                                                    {{ $child->name }}
                                                                </option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </form>
                                            </td>

                                            <!-- Lead Owner -->
                                            <td>{{ $lead->owner->name ?? 'N/A' }}</td>

                                            <!-- Applied Country -->
                                            <td>{{ $lead->applying_country_for_a_visa ?? 'N/A' }}</td>

                                            <!-- Planned Course -->
                                            <td>{{ $lead->what_course_are_you_planning_to_study ?? 'N/A' }}</td>

                                            <!-- english_test_status -->
                                            <td>{{ $lead->english_test_status ?? 'N/A' }}</td>

                                            @if (Auth::user()->role_id == 1)
                                                <!-- campaign_name -->
                                                <td>{{ $lead->campaign_name ?? 'N/A' }}</td>

                                                <!-- adset_name -->
                                                <td>{{ $lead->adset_name ?? 'N/A' }}</td>

                                                <!-- ad_name -->
                                                <td>{{ $lead->ad_name ?? 'N/A' }}</td>

                                                <!-- form_name -->
                                                <td>{{ $lead->form_name ?? 'N/A' }}</td>
                                            @endif

                                            <!-- extra fields -->
                                            <!-- Inside the foreach row -->
                                            <!-- <td>
                                                        @if($lead->attributes->isNotEmpty())
                                                            <ol class="mb-0 text-start">
                                                            @foreach($lead->attributes as $attr)
                                                                <li><strong>{{ ucwords(str_replace('_',' ',$attr->field_name)) }}:</strong> {{ $attr->field_value }}</li>
                                                            @endforeach
                                                            </ol>
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td> -->
                                            {{-- Loop through extra fields and align with headers --}}
                                            @foreach($extraFieldNames as $field)
                                                @php
                                                    $attr = $lead->attributes->firstWhere('field_name', $field);
                                                @endphp
                                                <td class="highlight-column bg-white">{{ $attr->field_value ?? 'N/A' }}</td>
                                            @endforeach

                                            <!-- url -->
                                            <td class="page-url-col">
                                                @if(!empty($lead->page_url))
                                                    @php
                                                        // Always strip everything after ?
                                                        $cleanUrl = strtok(trim($lead->page_url), '?');
                                                    @endphp

                                                    @if(filter_var($cleanUrl, FILTER_VALIDATE_URL))
                                                        <a href="{{ $cleanUrl }}" target="_blank" title="{{ $cleanUrl }}"
                                                            class="page-url-link">
                                                            {{ $cleanUrl }}
                                                        </a>
                                                    @else
                                                        {{ $cleanUrl }}
                                                    @endif
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="12" class="text-center text-muted">No Lead Found</td>
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


    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        $(document).ready(function () {

            // Bucket change -> AJAX update + Status dropdown update
            $(document).on("change", ".bucket-select", function () {
                let form = $(this).closest("form");
                let url = form.attr("action");
                let data = form.serialize();

                $.ajax({
                    url: url,
                    type: "POST",
                    data: data,
                    success: function (res) {
                        toastr.success(res.message);

                        // Update corresponding status dropdown
                        let statusSelect = form.closest('td').siblings().find('.status-select');
                        statusSelect.empty(); // Remove old options
                        statusSelect.append('<option value="">Select Status</option>');

                        res.children.forEach(function (child) {
                            statusSelect.append(
                                `<option value="${child.name}" data-bg="${child.color}">${child.name}</option>`
                            );
                        });
                    },
                    error: function (xhr) {
                        toastr.error(xhr.responseJSON?.message || "Status update failed!");
                    }
                });
            });

            // Status change -> AJAX update
            $(document).on("change", ".status-select", function () {
                let form = $(this).closest("form");
                let url = form.attr("action");
                let data = form.serialize();

                $.ajax({
                    url: url,
                    type: "POST",
                    data: data,
                    success: function (res) {
                        toastr.success(res.message);
                    },
                    error: function (xhr) {
                        toastr.error(xhr.responseJSON?.message || "Sub Status update failed!");
                    }
                });
            });

            // Status change -> AJAX update
            $(document).on("change", ".engagement-status-select", function () {
                let form = $(this).closest("form");
                let url = form.attr("action");
                let data = form.serialize();

                $.ajax({
                    url: url,
                    type: "POST",
                    data: data,
                    success: function (res) {
                        toastr.success(res.message);
                    },
                    error: function (xhr) {
                        toastr.error(xhr.responseJSON?.message || "Sub Status update failed!");
                    }
                });
            });
        });
    </script>
    <!-- [ Main Content ] end -->

    <script>
        $(document).ready(function () {

            function applyStatusColor(select) {

                let value = $(select).val();

                let selection = $(select)
                    .next('.select2-container')
                    .find('.select2-selection');

                selection.removeClass('hot warm cold dead');

                if (value) {
                    selection.addClass(value);
                }
            }

            $('.engagement-status-select').each(function () {

                // Apply on page load
                applyStatusColor(this);

                // Apply on change
                $(this).on('change', function () {
                    applyStatusColor(this);
                });

            });

        });
    </script>
@endsection