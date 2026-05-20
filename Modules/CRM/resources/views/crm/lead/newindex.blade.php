@extends('layouts.app')

@section('content')

<link href="{{ asset('crm-assets/assets/vendors/css/quill.min.css') }}" rel="stylesheet">
<style>
    /* Inactive (Normal) Tab ka Design */
    .lead-custom-tab {
        color: #6c757d !important;
        /* Muted Text */
        background: transparent !important;
        border: none !important;
        border-bottom: 3px solid transparent !important;
        padding: 0 0 0.5rem 0 !important;
        /* Niche se thodi jagah */
        font-weight: 500;
        transition: all 0.3s ease;
    }

    /* Hover karne par color change */
    .lead-custom-tab:hover {
        color: #f47b20 !important;
    }

    /* Active (Clicked) Tab ka Design */
    .lead-custom-tab.active {
        color: #212529 !important;
        /* Dark Text */
        font-weight: bold !important;
        /* Bold Text */
        border-bottom: 3px solid #f47b20 !important;
        /* Orange Border */
    }

    @media (min-width: 768px) {
        .card-width {
            width: 20%;
        }
    }

    @media (max-width: 767px) {
        .card-width {
            width: 100%;
        }
    }
</style>
<style>
    .duplicate-info-wrapper {
        position: relative;
        display: inline-flex;
        align-items: center;
    }

    /* hidden by default */
    .duplicate-popup {
        position: absolute;
        top: 28px;
        left: 0;
        min-width: 170px;
        background: #fff;
        padding: 8px 10px;
        border-radius: 8px;
        border: 1px solid #ddd;
        z-index: 99999;
        display: none;
    }

    /* hover on full wrapper */
    .duplicate-info-wrapper:hover .duplicate-popup {
        display: block;
    }
</style>
<div class="container-fluid">

    <x-lead.tools :buckets="$mainbuckets" :filterBucket="$filterBucket" :totalLeadsCount="$totalLeadsCount" :filteredLeadCount="$filteredLeadCount" :owners="$owners" :sources="$sources" :categories="$categorys" />

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
        <strong>Whoops!</strong> There were some problems with your input:
        <ul class="mb-0 mt-1">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="d-flex overflow-auto border-bottom mb-2 mt-3 pb-2 gap-3 align-items-center">

        @php
        // All tab tabhi active hoga jab URL me koi bucket_id ya has_followups na ho
        $isAllActive = !request()->has('bucket_id') && !request()->has('has_followups');
        @endphp
        <a href="{{ route('modern.leads.index') }}"
            class="{{ $isAllActive ? 'btn btn-warning text-white fw-bold px-4 py-2' : 'text-muted fw-semibold px-2 text-decoration-none text-hover-primary' }} text-nowrap">
            All ({{ $totalLeadsCount }})
        </a>

        @php
        $isFollowupActive = request('has_followups') == 1;
        @endphp
        <a href="?has_followups=1"
            class="{{ $isFollowupActive ? 'btn btn-danger text-white fw-bold px-4 py-2' : 'btn btn-soft-danger text-danger fw-semibold px-3 py-1' }} text-nowrap d-flex align-items-center gap-2 text-decoration-none">
            <i class="fa-solid fa-clock"></i> Scheduled Activity ({{ $followupsCount}})
        </a>
        @if($buckets->count())
        @foreach($buckets as $bucket)

        @php
        $isActive = request('bucket_id') == $bucket->id;
        @endphp

        <a href="?bucket_id={{ $bucket->id }}&lead_status="
            class="{{ $isActive ? 'btn btn-warning text-white fw-bold px-4 py-2' : 'text-muted fw-semibold px-2 text-decoration-none text-hover-primary' }} text-nowrap">

            {{ $bucket->name }} ({{ $bucket->leads_count }})

        </a>

        @endforeach
        @endif

        <!-- @if($childBuckets->count())
        @foreach($childBuckets as $bucket) {{-- ✅ FIXED HERE --}}

        @php
        $isActive = request('lead_status') == $bucket->name;
        @endphp

        <a href="?bucket_id={{ request('bucket_id') }}&lead_status={{ urlencode($bucket->name) }}"
            class="{{ $isActive ? 'btn btn-warning text-white fw-bold px-4 py-2' : 'text-muted fw-semibold px-2 text-decoration-none text-hover-primary' }} text-nowrap">

            {{ $bucket->name }} ({{ $bucket->leads_count }})

        </a>

        @endforeach
        @endif -->
        @php
        $isDeletedActive = request('deleted_leads') == 1;
        @endphp

        <a href="?deleted_leads=1"
            class="{{ $isDeletedActive ? 'btn btn-dark text-white fw-bold px-4 py-2' : 'btn btn-soft-dark text-dark fw-semibold px-3 py-1' }} text-nowrap d-flex align-items-center gap-2 text-decoration-none">

            Old Leads ({{ $deletedLeadsCount }})
        </a>

    </div>

    <div class="d-flex overflow-auto border-bottom mb-2 mt-3 pb-2 gap-3 align-items-center">


        @if($childBuckets->count())
         @php
        $isAllActived = empty(request('lead_status'));
        @endphp
        <a href="?bucket_id={{ request('bucket_id') }}&lead_status="
            class="{{ $isAllActived ? 'btn btn-warning text-white fw-bold px-4 py-2' : 'text-muted fw-semibold px-2 text-decoration-none text-hover-primary' }} text-nowrap">
            All ({{ $childtotalLeadsCount}})
        </a>

        @foreach($childBuckets as $bucket) {{-- ✅ FIXED HERE --}}

        @php
        $isActive = request('lead_status') == $bucket->name;
        @endphp

        <a href="?bucket_id={{ request('bucket_id') }}&lead_status={{ urlencode($bucket->name) }}"
            class="{{ $isActive ? 'btn btn-warning text-white fw-bold px-4 py-2' : 'text-muted fw-semibold px-2 text-decoration-none text-hover-primary' }} text-nowrap">

            {{ $bucket->name }} ({{ $bucket->leads_count }})

        </a>

        @endforeach
        @endif


    </div>


    <div class="d-flex flex-wrap  align-items-center justify-content-between gap-3 mb-3 px-3 ">
        <div class="d-flex align-items-center">
            <div class="form-check">
                <input type="checkbox" id="selectAll" class="form-check-input">
            </div>

            <div class="d-flex align-items-center gap-2">

                <label class="mb-0">Show</label>

                <form method="GET">
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
        </div>
        @if(request('has_followups'))
        <form method="GET" id="followupTypeForm">
            <input type="hidden" name="has_followups" value="1">
            <input type="hidden" name="followup_type_filter" id="followupTypeInput" value="{{ request('followup_type_filter', 'upcoming') }}">

            <div class="d-flex gap-2">

                <!-- Upcoming Tab -->
                <button type="button"
                    class="btn btn-sm {{ request('followup_type_filter', 'upcoming') == 'upcoming' ? 'btn-primary' : 'btn-light' }}"
                    onclick="setFollowupType('upcoming')">
                    <i class="fas fa-clock me-1 fs-6"></i> Upcoming
                </button>

                <!-- Missed Tab -->
                <button type="button"
                    class="btn btn-sm {{ request('followup_type_filter') == 'missed' ? 'btn-primary' : 'btn-light' }}"
                    onclick="setFollowupType('missed')">
                    <i class="fas fa-times-circle me-1 fs-6"></i>Missed
                </button>

            </div>
        </form>
        @endif

        <div class="d-flex flex-wrap gap-2">

            <a href="{{ request()->fullUrlWithQuery(['lead_engagement_status' => '']) }}"
                class="btn btn-sm {{ request('lead_engagement_status') == '' ? 'btn-primary' : 'btn-light' }}">
                All
            </a>

            <a href="{{ request()->fullUrlWithQuery(['lead_engagement_status' => 'hot']) }}"
                class="btn btn-sm {{ request('lead_engagement_status') == 'hot' ? 'btn-danger' : 'btn-light' }}">
                Hot
            </a>

            <a href="{{ request()->fullUrlWithQuery(['lead_engagement_status' => 'warm']) }}"
                class="btn btn-sm {{ request('lead_engagement_status') == 'warm' ? 'btn-warning' : 'btn-light' }}">
                Warm
            </a>

            <a href="{{ request()->fullUrlWithQuery(['lead_engagement_status' => 'cold']) }}"
                class="btn btn-sm {{ request('lead_engagement_status') == 'cold' ? 'btn-info' : 'btn-light' }}">
                Cold
            </a>

            <a href="{{ request()->fullUrlWithQuery(['lead_engagement_status' => 'dead']) }}"
                class="btn btn-sm {{ request('lead_engagement_status') == 'dead' ? 'btn-dark' : 'btn-light' }}">
                Dead
            </a>

        </div>

        <div class="d-flex flex-wrap gap-3">
            <a href="javascript:void(0);" class="bulk-whatsapp text-warning">
                <i class="fab fa-whatsapp fs-5"></i>
            </a>

            <a href="javascript:void(0);" class="bulk-sms text-warning">
                <i class="fas fa-sms fs-5"></i>
            </a>

            <a href="javascript:void(0);" class="bulk-email text-warning">
                <i class="fas fa-envelope fs-5"></i>
            </a>
            <a href="javascript:void(0);"
                class="bulk-owner text-warning">

                <i class="fas fa-user-plus fs-5"></i>

            </a>
            <!-- <form id="bulkDeleteForm" method="POST" action="{{ route('leads.bulkDelete') }}">
                    @csrf

                    <input type="hidden" name="ids" id="deleteIds">

                    <button type="submit" class="text-warning border-0 bg-transparent p-0" id="bulkDeleteBtn">
                        <i class="fas fa-trash fs-5"></i>
                    </button>
                </form> -->

        </div>
    </div>

    <div class="row">
        <div class="col-12">
            @forelse($leads as $lead)
            <div class="card shadow-sm mb-3 border-0">
                <div class="card-body d-flex flex-wrap flex-xxl-nowrap align-items-center justify-content-between gap-3 p-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input lead-checkbox" value="{{ $lead->id }}" data-email="{{ optional($lead->user)->email }}">
                    </div>

                    <div class="d-flex justify-content-center align-items-center mx-3" title="Status Progress">
                        @php
                        $bucketProgress = [
                        'Counselling in Progress' => 20,
                        'Application Process' => 40,
                        'Offer Stage' => 60,
                        'Visa Process' => 80,
                        'Converted' => 100,
                        ];
                        $bucketColors = [
                        'Counselling in Progress' => '#ffc107', // yellow
                        'Application Process' => '#020203', // blue
                        'Offer Stage' => '#6f42c1', // purple
                        'Visa Process' => '#fd7e14', // orange
                        'Converted' => '#28a745', // green
                        ];
                        // Parent bucket logic
                        $bucket = $lead->bucket;
                        if ($bucket && $bucket->parent_id) {
                        $bucket = \App\Models\Bucket::find($bucket->parent_id);
                        }

                        $currentBucket = $bucket->name ?? '';

                        $percentage = $bucketProgress[$currentBucket] ?? 0;
                        $color = $bucketColors[$currentBucket] ?? '#6c757d'; // default gray
                        @endphp
                        <div
                            class="rounded-circle d-flex justify-content-center align-items-center shadow-sm"
                            style="
                                        width: 45px;
                                        height: 45px;
                                        background: conic-gradient(
                                            {{ $color }} {{ $percentage }}%, 
                                            #e9ecef {{ $percentage }}%
                                        );
                                    ">
                            <div class="rounded-circle bg-white d-flex justify-content-center align-items-center"
                                style="width: 35px; height: 35px;">
                                <span class="fs-12 fw-bold text-dark">{{ $percentage }}%</span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-start">
                        <!-- <div class="form-check mt-1">
                                <input type="checkbox" class="form-check-input" value="{{ $lead->id }}" id="checkLead{{ $lead->id }}">
                            </div> -->
                        @php
                        $engStatus = strtolower($lead->lead_engagement_status ?? 'n/a');
                        $badgeClass = 'bg-soft-secondary text-secondary';
                        if ($engStatus == 'hot') {
                        $badgeClass = 'bg-soft-danger text-danger';
                        } elseif ($engStatus == 'warm') {
                        $badgeClass = 'bg-soft-warning text-warning';
                        } elseif ($engStatus == 'cold') {
                        $badgeClass = 'bg-soft-info text-info';
                        } elseif ($engStatus == 'dead') {
                        $badgeClass = 'bg-soft-dark text-dark';
                        }
                        @endphp
                        <div style="width: 210px;">
                            <div class="d-flex align-items-center gap-2">

                                <p class="mb-0 fw-bold text-dark">
                                    <a data-bs-toggle="collapse" href="#details-{{ $lead->id }}" class="text-dark text-decoration-none hover-orange">
                                        {{ optional($lead->user)->name ?? 'Unknown User' }}
                                    </a>

                                </p>
                                <span class="badge bg-light text-secondary rounded-pill border">#{{ $lead->id }}</span>
                                @if($lead->duplicate_count > 0)
                                <span class="duplicate-info-wrapper">

                                    <a href="{{ request()->fullUrlWithQuery(['duplicate_of' => $lead->id]) }}"
                                        class="btn btn-sm btn-light rounded-circle p-0 duplicate-btn d-flex align-items-center justify-content-center"
                                        style="width:22px;height:22px;font-size:12px;"
                                        title="click to View Duplicates">
                                        <i class="fa-solid fa-circle-info"></i>
                                    </a>

                                    <div class="duplicate-popup shadow">
                                        <strong>{{ $lead->duplicate_count }}</strong> Duplicate Leads <br>
                                        IDs: {{ $lead->duplicate_ids->implode(', ') }}
                                    </div>

                                </span>
                                @endif
                                @if($lead->verified_lead)
                                <span class="badge bg-light text-success rounded-pill border">Verified</span>
                                @endif
                            </div>
                            <small class="text-muted d-block mt-1">{{ optional($lead->user)->contact_no ?? 'N/A' }}</small>
                            <div class="badge {{ $badgeClass }}  fw-semibold px-2 py-1 text-capitalize" style="font-size: 14px;">
                                {{ $engStatus }}
                            </div>
                            <div>
                                <small class="" style="font-size: .815em; line-height: 0.8;">Create On</small>
                                <span class="text-muted  fw-semibold" style="font-size:.815em; line-height: 0.8;">{{ \Carbon\Carbon::parse($lead->created_at)->format('d M Y h:i A') }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- EDIT STATUS AND ADD FOLLOW UP --}}
                    <div class="flex" style="min-width: 150px;">
                        <div class="d-inline-flex align-items-center justify-content-between bg-dark text-white rounded px-2 py-1 w-100" style="max-width: 160px; cursor:pointer;" data-bs-toggle="offcanvas" data-bs-target="#editStatusOffcanvas-{{ $lead->id }}">
                            <span class="fs-12 text-truncate">{{ $lead->bucket->name ?? 'No Bucket' }}</span>
                            <i class="fa-solid fa-pen-to-square text-secondary ms-2"></i>
                        </div>
                        <small class="text-muted d-block mt-1 text-truncate" style="max-width: 160px;">
                            {{ $lead->lead_status ?? 'No Status' }}
                        </small>
                    </div>
                    @php
                    $message = $lead->latestMessage->message ?? '';
                    $created_at = $lead->latestMessage->created_at ?? null;
                    $followup = $lead->latestMessage->next_followup_date ?? null;

                    $isLong = strlen($message) > 80;
                    @endphp

                    <div class="p-2 rounded-3 d-flex flex-column justify-content-between card-width" style="{{$message ? 'background:#f3f4f6;' : ''}} ">

                        @if($message || $followup)
                        <div class="d-flex justify-content-between align-items-center">
                            <strong class="fs-13">
                                {{ $lead->lastMessage->user->name ?? 'Unknown' }}
                            </strong>

                            @if($created_at)
                            <span class="fs-10 text-muted">
                                <span class="fs-10 text-dark">{{ ($created_at) ? $created_at->diffForHumans() : 'N/A' }}</span>
                                <!-- {{ \Carbon\Carbon::parse($created_at)->format('d M, h:i A') }} -->
                            </span>
                            @endif
                        </div>


                        @if($message)
                        <p class="fs-12 text-dark mb-1 mt-1">
                            {{ Str::limit($message, 80) }}

                            @if($isLong)
                            <a href="javascript:void(0);"
                                class="open-callback text-warning"
                                data-bs-toggle="offcanvas"
                                data-bs-target="#proposalSent{{ $lead->id }}">
                                Read More
                            </a>
                            @endif
                        </p>
                        @endif


                        @if($followup)
                        <div class="fs-11 text-primary fw-semibold mb-1">
                            📅 {{ \Carbon\Carbon::parse($followup)->format('d M Y, h:i A') }}
                        </div>
                        @endif


                        @if($lead->latestMessage->call_recording)
                        <div class="mt-2 p-1 rounded d-flex align-items-center gap-2"
                            style="background:#e9ecef;">

                            <!-- Hidden Audio -->
                            @if($lead->latestMessage->call_recording)
                            <audio controls style="width:100%; height:30px;">
                                <source src="{{ asset('storage/' . $lead->latestMessage->call_recording) }}" type="audio/mpeg">
                                Your browser does not support the audio element.
                            </audio>
                            @endif

                        </div>
                        @endif
                        @else
                        <div class="d-none d-md-block" style="height:60px;"></div>
                        @endif
                    </div>

                    {{-- EDIT COUNTRY OR INTAKE --}}
                    <div class="flex" style="min-width: 150px;">
                        <div class="d-inline-flex align-items-center justify-content-between bg-dark text-white rounded px-2 py-1 w-100"
                            style="max-width: 160px; cursor:pointer;"
                            data-lead="{{ json_encode($lead ?? []) }}"
                            data-user="{{ json_encode($lead->user ?? []) }}"
                            onclick="openEditModal(this)">

                            <span class="fs-12 text-truncate">{{ $lead->applying_country_for_a_visa ?? 'N/A' }}</span>
                            <i class="fa-solid fa-pen-to-square text-secondary ms-2"></i>
                        </div>

                        <small class="text-muted d-block mt-1 text-truncate" style="max-width: 160px;">
                            {{ $lead->whats_your_preferred_intake ?? 'N/A' }}
                        </small>
                    </div>
                    <div style="margin: 0 auto;">
                        <div class="d-flex flex-column justify-content-center align-items-center gap-2 text-center" style="min-width:100px;">
                            @if($lead->owner)
                            <div class="d-flex align-items-center gap-2 justify-content-center">
                                <div class="avatar-image">
                                    <img src="{{ asset('storage/' . $lead->owner->image) }}" alt="" class="img-fluid">
                                </div>

                                <div>
                                    <a href="javascript:void(0);">{{ $lead->owner->name ?? 'Unknown' }}</a>
                                    <div class="fs-11 text-muted">{{ $lead->owner->role->name ?? 'Unknown' }}</div>
                                </div>
                            </div>
                            @if(optional($lead->latestAssignHistory)->created_at)
                            <div class="">
                                <small class="lh-1 " style="font-size: .675em; line-height: 0.2;">Assign:</small>
                                <span class="text-muted fw-semibold" style="font-size: .705em; line-height: 0.8;"> {{ optional($lead->latestAssignHistory)->created_at
                ? \Carbon\Carbon::parse($lead->latestAssignHistory->created_at)->format('d M Y h:i A')
                : '-' }}</span>
                            </div>
                            @endif
                            @else
                            <div class="d-flex align-items-center gap-2 justify-content-center">
                                <div class="avatar-image">
                                    <img src="assets/images/avatar/1.png" alt="" class="img-fluid">
                                </div>
                                <div>
                                    <a href="javascript:void(0);">Unknown</a>
                                    <div class="fs-11 text-muted">Unknown</div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="d-flex flex-wrap align-items-center  ms-auto text-warning fs-5">
                            <div class="text-muted fs-14 me-2" title="Missed Calls">
                                <i class="fas fa-phone-slash text-warning "></i> {{$lead->call_followup_count ?? 0}}
                            </div>
                            <div class="text-muted fs-14 me-2" title="Messages">
                                <i class="far fa-comment-alt text-warning "></i> {{ $lead->messages ? $lead->messages->count() : 0 }}
                            </div>
                            <a href="tel:{{ optional($lead->user)->contact_no }}" class="text-warning me-2"><i class="fas fa-phone-alt"></i></a>
                            <a href="javascript:void(0);" class="open-callback text-warning me-2" data-bs-toggle="offcanvas" data-bs-target="#proposalSent{{ $lead->id }}"><i class="fas fa-comment-dots"></i></a>
                            <a href="javascript:void(0);" class="open-whatsapp text-warning me-2" data-bs-toggle="offcanvas" data-bs-target="#whatsappSent{{ $lead->id }}"><i class="fab fa-whatsapp fs-5"></i></a>
                            <a href="javascript:void(0);" class="open-SMS text-warning me-2" data-bs-toggle="offcanvas" data-bs-target="#SMSSent{{ $lead->id }}"><i class="fa-solid fa-message"></i></a>

                            <a href="javascript:void(0);" class="text-warning me-2" data-bs-toggle="modal" data-bs-target="#composeMail">
                                <i class="fas fa-envelope" onclick="openSingleEmail('{{ optional($lead->user)->email }}')"></i>
                            </a>


                            {{-- <a href="{{ route('lead.history', $lead->id) }}" class="text-secondary me-2"><i class="fas fa-history"></i></a> --}}
                            <a href="javascript:void(0);" class="text-secondary"
                                data-lead="{{ json_encode($lead) }}"
                                data-user="{{ json_encode($lead->user) }}"
                                onclick="openEditModal(this)">
                                <i class="fas fa-edit"></i>
                            </a>
                            <!-- <a href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#todoOffcanvas-{{ $lead->id }}" class="text-secondary"><i class="fas fa-tasks"></i></a>  -->

                            <a class="text-dark p-1 collapsed me-2" data-bs-toggle="collapse" href="#details-{{ $lead->id }}" role="button">
                                <i class="fas fa-chevron-down fs-6 p-2 rounded-circle border bg-warning text-white"></i>
                            </a>
                        </div>
                        <div class="d-flex justify-content-between ">
                            <div>
                                <small class="lh-1 " style="font-size: .815em; line-height: 0.8;">Modify On</small>
                                <span class="text-muted fw-semibold" style="font-size: .815em; line-height: 0.8;">{{ \Carbon\Carbon::parse($lead->updated_at)->format('d M Y h:i A') }}</span>
                            </div>


                        </div>
                    </div>

                    @include('crm.lead.call-back')
                </div>
                {{-- whatsapp offcanvace --}}

                <div class="content-area offcanvas offcanvas-end" data-scrollbar-target="#psScrollbarInit" style="width:400px" tabindex="-1" id="whatsappSent{{ $lead->id }}" aria-labelledby="whatsappOffcanvasLabel{{ $lead->id }}">
                    <div class="content-area-header sticky-top" style="background-color:#ffffff;">
                        <div class="offcanvas-header  gap-4">

                            <a href="javascript:void(0);" class="d-flex align-items-center justify-content-center gap-3" data-bs-toggle="offcanvas" data-bs-target="#userProfileDetails">
                                <div class="avatar-image">
                                    <img src="{{ $lead->user->image ? asset('storage/' . $lead->user->image) : '/images/blank.jpeg' }}"
                                        class="img-fluid"
                                        alt="image">
                                </div>
                                <div class="d-none d-sm-block">
                                    <div class="fw-bold d-flex align-items-center">{{ optional($lead->user)->name ?? 'User' }}</div>
                                    <div class="d-flex align-items-center mt-1">
                                        <span class="wd-7 ht-7 rounded-circle opacity-75 me-2 bg-success"></span>
                                        <span class="fs-9 text-uppercase fw-bold text-success">{{ optional($lead->user)->contact_no ?? '-' }}</span>
                                    </div>
                                </div>
                            </a>
                            <button type="button" class="btn-close text-reset cancel-offcanvas" data-id="{{ $lead->id }}" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>

                    </div>
                    <div class="content-area-body h-100 p-4" style="background-color:#efeae2;">
                        <!--! BEGIN: Single Message [start] !-->
                        <div class="d-flex mb-3">
                            <div class="p-2 px-3 bg-white rounded-3 shadow-sm" style="max-width: 60%;">
                                Hi,
                                <div class="text-muted fs-10 text-end mt-1">10:30 AM</div>
                            </div>
                        </div>

                        <!-- RIGHT (Your Message) -->
                        <div class="d-flex justify-content-end mb-3">
                            <div class="p-2 px-3 rounded-3 shadow-sm" style="background-color:#d9fdd3; max-width: 60%;">
                                hy
                                <div class="text-muted fs-10 text-end mt-1">10:31 AM</div>
                            </div>
                        </div>

                        <!-- LEFT -->
                        <div class="d-flex mb-3">
                            <div class="p-2 px-3 bg-white rounded-3 shadow-sm" style="max-width: 60%;">
                                Hello
                                <div class="text-muted fs-10 text-end mt-1">10:32 AM</div>
                            </div>
                        </div>

                        <!-- RIGHT -->
                        <div class="d-flex justify-content-end mb-3">
                            <div class="p-2 px-3 rounded-3 shadow-sm" style="background-color:#d9fdd3; max-width: 60%;">
                                hello
                                <div class="text-muted fs-10 text-end mt-1">10:33 AM</div>
                            </div>
                        </div>

                    </div>
                    <!--! BEGIN: Message Editor !-->
                    <div class="d-flex align-items-center justify-content-between border-top border-gray-5 bg-white  sticky-bottom">
                        <div class="d-flex align-center">
                            <div class="dropdown border-end border-gray-5">
                                <a href="javascript:void(0)" data-bs-toggle="dropdown">
                                    <div class="wd-60 d-flex align-items-center justify-content-center" data-bs-toggle="tooltip" data-bs-trigger="hover" title="Pick Template" style="height: 59px"><i class="feather-hash"></i></div>
                                </a>
                                <ul class="dropdown-menu wd-300">
                                    <li>
                                        <a href="javascript:void(0)" class="dropdown-item"><i class="feather-file-text me-3"></i>Welcome you message</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" class="dropdown-item"><i class="feather-file-text me-3"></i>Your issues solved</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" class="dropdown-item"><i class="feather-file-text me-3"></i>Thank you message</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" class="dropdown-item"><i class="feather-file-text me-3"></i>Make a offer message</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" class="dropdown-item"><i class="feather-file-text me-3"></i>Add the Unsubscribe option</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" class="dropdown-item"><i class="feather-file-text me-3"></i>Thank your customer for joining</a>
                                    </li>
                                    <li class="dropdown-divider"></li>
                                    <li>
                                        <a href="javascript:void(0)" class="dropdown-item"><i class="feather-save me-3"></i>Save as Template</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" class="dropdown-item"><i class="feather-sun me-3"></i>Manage Template</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="dropdown border-end border-gray-5">
                                <a href="javascript:void(0)" data-bs-toggle="dropdown">
                                    <div class="wd-60 d-flex align-items-center justify-content-center" data-bs-toggle="tooltip" data-bs-trigger="hover" title="Upload Attachments" style="height: 59px"><i class="feather-link"></i></div>
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a href="javascript:void(0)" class="dropdown-item"><i class="feather-image me-3"></i>Upload Images</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" class="dropdown-item"><i class="feather-mic me-3"></i>Upload Audios</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" class="dropdown-item"><i class="feather-video me-3"></i>Upload Videos</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" class="dropdown-item"><i class="feather-file me-3"></i>Upload Documents</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="dropdown border-end border-gray-5 d-none d-sm-block">
                                <a href="javascript:void(0)" data-bs-toggle="dropdown">
                                    <div class="wd-60 d-flex align-items-center justify-content-center" data-bs-toggle="tooltip" data-bs-trigger="hover" title="Calling Options" style="height: 59px"><i class="feather-phone-call"></i></div>
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a href="javascript:void(0)" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#voiceCallingModalScreen"><i class="feather-phone-call me-3"></i>Audio Call</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#videoCallingModalScreen"><i class="feather-video me-3"></i>Video Call</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <input class="form-control border-0 emoji-picker" placeholder="Type your message here...">
                        <div class="border-start border-gray-5 send-message">
                            <a href="javascript:void(0)" class="wd-60 d-flex align-items-center justify-content-center" data-bs-toggle="tooltip" data-bs-trigger="hover" title="Send Message" style="height: 59px"><i class="feather-send"></i></a>
                        </div>
                    </div>
                    <!--! END: Message Editor !-->
                </div>

                {{-- SMS Offcanvas --}}
                <div class="offcanvas offcanvas-end" tabindex="-1" id="SMSSent{{ $lead->id }}" aria-labelledby="SMSSentOffcanvasLabel{{ $lead->id }}" style="width: 400px;">

                    <div class="offcanvas-header border-bottom bg-light py-3">
                        <h6 class="offcanvas-title d-flex align-items-center gap-2 fw-bold text-dark" id="SMSSentOffcanvasLabel{{ $lead->id }}">
                            <i class="fa-regular fa-comment-dots text-secondary"></i>
                            Send SMS to <span class="text-capitalize">{{ optional($lead->user)->name ?? 'User' }}</span>
                        </h6>
                        <button type="button" class="btn-close text-reset cancel-offcanvas" data-id="{{ $lead->id }}" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>

                    <div class="offcanvas-body p-3" style="background-color: #f4f6f8;">

                        <hr class="my-2">

                        <!-- Options -->
                        <div class="mb-3">

                            <div class="form-check">
                                <input class="form-check-input number-checkbox" type="checkbox" value="+916265455843">
                                <label class="form-check-label" for="mobileNo">Mobile No</label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="fatherNo">
                                <label class="form-check-label" for="fatherNo">Father's Number</label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="motherNo">
                                <label class="form-check-label" for="motherNo">Mother's Number</label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="whatsappNo">
                                <label class="form-check-label" for="whatsappNo">Whatsapp No</label>
                            </div>
                        </div>

                        <!-- Template Select -->
                        <select class="form-control template-dropdown">
                            <option selected disabled>Select Template</option>
                        </select>

                        <!-- Message Box -->
                        <div class="mb-3 flex-grow-1 d-flex flex-column">
                            <label class="form-label fw-semibold">Message</label>
                            <textarea class="form-control flex-grow-1" rows="12" placeholder="Type your message...">

                                </textarea>
                            <small class="text-muted mt-1 text-end">0/160</small>
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex justify-content-end gap-2 mt-auto">
                            <button class=" btn btn-light border text-reset cancel-offcanvas" data-id="{{ $lead->id }}" data-bs-dismiss="offcanvas" aria-label="Close">Cancel</button>
                            <button class="btn btn-warning text-white send-sms-btn">Send SMS</button>
                        </div>


                    </div>
                </div>

                {{-- CHANGE STATUS AND FOLLOW UP --}}
                <div class="offcanvas offcanvas-end" tabindex="-1" id="editStatusOffcanvas-{{ $lead->id }}" aria-labelledby="editStatusOffcanvasLabel-{{ $lead->id }}" style="width: 400px;">
                    <div class="offcanvas-header border-bottom bg-light py-3">
                        <h6 class="offcanvas-title d-flex align-items-center gap-2 fw-bold text-dark" id="editStatusOffcanvasLabel-{{ $lead->id }}">
                            <i class="fa-solid fa-clipboard-list text-secondary"></i>
                            Edit Followup for <span class="text-capitalize">{{ optional($lead->user)->name ?? 'User' }}</span>
                        </h6>
                        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>

                    <div class="offcanvas-body p-4 bg-white">
                        <form id="quickUpdateForm-{{ $lead->id }}" action="{{ route('lead.updateQuick', $lead->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="mb-4">
                                <label class="form-label text-muted small mb-1" style="font-size: 12px;">Engagement Status </label>
                                <select class="form-select bg-light border-0 shadow-sm" name="lead_engagement_status" style="font-size: 14px;">
                                    <option value="">Select Engagement</option>
                                    <option value="hot" {{ strtolower($lead->lead_engagement_status) == 'hot' ? 'selected' : '' }}>Hot</option>
                                    <option value="warm" {{ strtolower($lead->lead_engagement_status) == 'warm' ? 'selected' : '' }}>Warm</option>
                                    <option value="cold" {{ strtolower($lead->lead_engagement_status) == 'cold' ? 'selected' : '' }}>Cold</option>
                                    <option value="dead" {{ strtolower($lead->lead_engagement_status) == 'dead' ? 'selected' : '' }}>Dead</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label text-muted small mb-1" style="font-size: 12px;">Status <span class="text-danger">*</span></label>
                                <select name="lead_bucket_id" class="form-select bg-light border-0 shadow-sm bucket-select required-field" style="font-size: 14px;">
                                    <option value="" disabled>Select Status</option>
                                    @foreach($mainbuckets as $bucket)
                                    <option value="{{ $bucket->id }}" {{ $lead->lead_bucket_id == $bucket->id ? 'selected' : '' }}>
                                        {{ $bucket->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('lead_bucket_id')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label text-muted small mb-1" style="font-size: 12px;">Sub-Status <span class="text-danger">*</span></label>
                                <select name="lead_status" class="form-select bg-light border-0 shadow-sm status-select required-field" style="font-size: 14px;">
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
                                @error('lead_status')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label text-muted small mb-1" style="font-size: 12px;">Follow Up Type</label>
                                <select class="form-select bg-light border-0 shadow-sm" name="followup_type" style="font-size: 14px;">
                                    <option value="">--pls select Follow Up type --</option>
                                    <option value="WhatsApp Call">WhatsApp Call</option>
                                    <option value="Call">Call</option>
                                    <option value="Whatsapp">Whatsapp</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label text-muted small mb-1" style="font-size: 12px;">Follow Up Status</label>
                                <select class="form-select bg-light border-0 shadow-sm" name="followup_status" style="font-size: 14px;">
                                    <option value="">--select Follow Up status --</option>
                                    <option value="Connected">Connected</option>
                                    <option value="Not Connected">Not Connected</option>
                                    <option value="Discussion Start">Discussion Start</option>
                                    <option value="No Response">No Response</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label text-muted small mb-1" style="font-size: 12px;">Next Follow-up Date</label>
                                <input type="datetime-local" class="form-control bg-light border-0 shadow-sm" name="next_followup_date" value="" style="font-size: 14px;">
                            </div>

                            <div class="mb-4">
                                <label class="form-label text-muted small mb-1" style="font-size: 12px;">Add Comment / Message</label>
                                <textarea class="form-control bg-light border-0 shadow-sm" name="message" rows="3" placeholder="Write a comment..." style="font-size: 14px; resize: none;"></textarea>
                            </div>
                            <div class="mb-4">
                                <label class="form-label text-muted small mb-1" style="font-size: 12px;">
                                    Upload Call Recording
                                </label>
                                <input type="file"
                                    name="call_recording"
                                    class="form-control bg-light border-0 shadow-sm"
                                    accept="audio/*"
                                    style="font-size: 14px;">
                            </div>

                            <div class="d-flex justify-content-end gap-3 pt-3 mt-4 border-top">
                                <button type="button" class="btn btn-white text-secondary fw-bold border px-4" data-bs-dismiss="offcanvas" style="font-size: 13px;">CLOSE</button>
                                <button type="submit" class="btn text-white fw-bold px-4" style="background-color: #f47b20; font-size: 13px; border-radius: 4px;">UPDATE DETAILS</button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- ADD TO-DO TASK  --}}
                <div class="offcanvas offcanvas-end" tabindex="-1" id="todoOffcanvas-{{ $lead->id }}" style="width: 420px;">

                    <div class="offcanvas-header border-bottom">
                        <h5 class="offcanvas-title fw-bold text-dark" style="font-size: 18px;">To-Do Task</h5>
                        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>

                    <div class="offcanvas-body p-0">

                        <div class="p-4" style="background-color: #f8fafc;">
                            <h6 class="fw-bold mb-3 text-dark" style="font-size: 15px;">Add New To-Do Task:</h6>

                            <form action="{{ route('lead.storeTodo', $lead->id) }}" method="POST">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label text-muted mb-1" style="font-size: 13px;">Summary:</label>
                                    <textarea class="form-control" name="summary" rows="3" placeholder="Write Your Summary" required style="font-size: 14px; border-color: #cbd5e1;"></textarea>
                                </div>

                                @if(auth()->check() && auth()->user()->role_id == 1)
                                <div class="mb-3">
                                    <label class="form-label text-muted mb-1" style="font-size: 13px;">Assign To</label>
                                    <select class="form-select" name="assign_to" required style="font-size: 14px; border-color: #cbd5e1;">
                                        <option value="" disabled selected>Select User</option>
                                        @foreach($owners as $owner)
                                        <option value="{{ $owner->id }}">{{ $owner->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif

                                <div class="mb-3">
                                    <label class="form-label text-muted mb-1" style="font-size: 13px;">Due Date</label>
                                    <input type="datetime-local" class="form-control" name="due_date" required style="font-size: 14px; border-color: #cbd5e1;">
                                </div>

                                <div class="text-end mt-2">
                                    <button type="submit" class="btn text-white fw-bold px-4 py-2" style="background-color: #3b5bdb; font-size: 13px;">SAVE TO-DO</button>
                                </div>
                            </form>
                        </div>

                        <hr class="m-0" style="border-color: #e2e8f0;">

                        <div class="p-4 bg-white">
                            @forelse($lead->todoTasks->sortByDesc('created_at') as $task)
                            <div class="card mb-3 shadow-none" style="border: 1px dashed #cbd5e1; border-radius: 8px;">
                                <div class="card-body p-3 d-flex align-items-center">

                                    <div class="rounded text-center p-2 me-3 d-flex flex-column justify-content-center" style="background-color: #fef0db; color: #f47b20; min-width: 55px; height: 55px;">
                                        <span class="fw-bold" style="font-size: 18px; line-height: 1;">{{ \Carbon\Carbon::parse($task->due_date)->format('d') }}</span>
                                        <span class="fw-bold text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">{{ \Carbon\Carbon::parse($task->due_date)->format('M') }}</span>
                                    </div>

                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center mb-1 gap-2">
                                            <span class="fw-bold text-dark" style="font-size: 14px;">To-Do Task</span>
                                            <span class="badge" style="background-color: #fef0db; color: #f47b20; font-size: 10px;">{{ $task->status }}</span>
                                        </div>
                                        <div class="text-muted text-uppercase fw-semibold" style="font-size: 11px;">
                                            {{ optional($task->assignee)->name ?? 'Unassigned' }}
                                        </div>
                                        <div class="text-muted" style="font-size: 11px;">
                                            {{ \Carbon\Carbon::parse($task->due_date)->format('h:i A') }}
                                        </div>
                                    </div>

                                    <div>
                                        <button class="btn btn-light rounded-circle border d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background-color: #f8fafc;">
                                            <i class="fa-solid fa-arrow-right text-muted" style="font-size: 12px;"></i>
                                        </button>
                                    </div>

                                </div>
                            </div>
                            @empty
                            <div class="text-center py-4">
                                <p class="text-muted small">No To-Do tasks found for this lead.</p>
                            </div>
                            @endforelse
                        </div>

                    </div>
                </div>

                {{-- LEAD DETAILS  --}}
                <div class="collapse w-100" id="details-{{ $lead->id }}">
                    <div class="border-top p-4 bg-white" style="border-left: 4px solid #f47b20; border-bottom-left-radius: 0.375rem; border-bottom-right-radius: 0.375rem;">

                        <ul class="nav nav-tabs border-bottom-0 mb-4 gap-3" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link lead-custom-tab active" id="personal-tab-{{ $lead->id }}" data-bs-toggle="tab" data-bs-target="#personal-{{ $lead->id }}" type="button" role="tab">Personal Details</button>
                            </li>

                            <li class="nav-item" role="presentation">
                                <button class="nav-link lead-custom-tab" id="source-tab-{{ $lead->id }}" data-bs-toggle="tab" data-bs-target="#source-{{ $lead->id }}" type="button" role="tab">Source Details</button>
                            </li>

                            <li class="nav-item" role="presentation">
                                <button class="nav-link lead-custom-tab" id="followup-tab-{{ $lead->id }}" data-bs-toggle="tab" data-bs-target="#followup-{{ $lead->id }}" type="button" role="tab">Followup Details</button>
                            </li>
                        </ul>

                        <div class="tab-content">

                            <div class="tab-pane fade show active" id="personal-{{ $lead->id }}" role="tabpanel">
                                <div class="row g-4">
                                    @if(!empty($lead->category))
                                    <div class="col-md-3 col-sm-6">
                                        <small class="text-muted text-uppercase d-block mb-1"
                                            style="font-size: 11px; letter-spacing: 0.5px;">
                                            Category Name
                                        </small>

                                        <span class="fs-15 text-dark">
                                            {{ $lead->category->category_name }}
                                        </span>
                                    </div>
                                    @endif
                                    <div class="col-md-3 col-sm-6">
                                        <small class="text-muted text-uppercase d-block mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Name</small>
                                        <span class="fs-15 text-dark">{{ optional($lead->user)->name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <small class="text-muted text-uppercase d-block mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Email</small>
                                        <span class="fs-15 text-dark">{{ optional($lead->user)->email ?? 'N/A' }}</span>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <small class="text-muted text-uppercase d-block mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Mobile No.</small>
                                        <span class="fs-15 text-dark">{{ optional($lead->user)->contact_no ?? 'N/A' }}</span>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <small class="text-muted text-uppercase d-block mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Country</small>
                                        <span class="fs-15 text-dark">{{ $lead->applying_country_for_a_visa ?? 'N/A' }}</span>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <small class="text-muted text-uppercase d-block mb-1" style="font-size: 11px; letter-spacing: 0.5px;">City</small>
                                        <span class="fs-15 text-dark">{{ optional($lead->user)->city ?? 'N/A' }}</span>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <small class="text-muted text-uppercase d-block mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Course</small>
                                        <span class="fs-15 text-dark">{{ $lead->what_course_are_you_planning_to_study ?? 'N/A' }}</span>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <small class="text-muted text-uppercase d-block mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Lead Added On</small>
                                        <span class="fs-15 text-dark">{{ $lead->created_at ? $lead->created_at->format('M d, Y h:i A') : 'N/A' }}</span>
                                    </div>

                                </div>
                            </div>

                            <div class="tab-pane fade" id="source-{{ $lead->id }}" role="tabpanel">
                                <div class="row g-4">
                                    <div class="col-md-3 col-sm-6">
                                        <small class="text-muted text-uppercase d-block mb-1" style="font-size: 11px;">Source</small>
                                        <span class="fs-15 text-dark">{{ $lead->platform ?? 'N/A' }}</span>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <small class="text-muted text-uppercase d-block mb-1" style="font-size: 11px;">Page URL</small>
                                        @if(!empty($lead->page_url))
                                        <a href="{{ $lead->page_url }}" target="_blank" class="fs-15 text-primary text-decoration-none d-inline-flex align-items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                                <polyline points="15 3 21 3 21 9"></polyline>
                                                <line x1="10" y1="14" x2="21" y2="3"></line>
                                            </svg>
                                            Click Me
                                        </a>
                                        @else
                                        <span class="fs-15 text-dark">N/A</span>
                                        @endif
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <small class="text-muted text-uppercase d-block mb-1" style="font-size: 11px;">Campaign Name</small>
                                        <span class="fs-15 text-dark">{{ $lead->campaign_name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <small class="text-muted text-uppercase d-block mb-1" style="font-size: 11px;">Adset Name</small>
                                        <span class="fs-15 text-dark">{{ $lead->adset_name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <small class="text-muted text-uppercase d-block mb-1" style="font-size: 11px;">Form Name</small>
                                        <span class="fs-15 text-dark">{{ $lead->form_name ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="followup-{{ $lead->id }}" role="tabpanel">
                                @php
                                $today = \Carbon\Carbon::today();

                                $Followups = $lead->messages->filter(function ($item) use ($today) {
                                return $item->next_followup_date &&
                                \Carbon\Carbon::parse($item->next_followup_date)->startOfDay()->gte($today) &&
                                $item->is_done == 0;
                                });

                                $todayActivities = $lead->messages->filter(function ($item) {
                                return (
                                ($item->created_at && \Carbon\Carbon::parse($item->created_at)->isToday())
                                ||
                                ($item->updated_at && \Carbon\Carbon::parse($item->updated_at)->isToday())
                                );
                                });

                                $previousActivities = $lead->messages->filter(function ($item) {
                                return $item->created_at &&
                                \Carbon\Carbon::parse($item->created_at)->lt(\Carbon\Carbon::today());
                                })->sortByDesc('created_at');

                                $overdueFollowups = $lead->messages->filter(
                                fn($item) =>
                                $item->next_followup_date &&
                                (
                                \Carbon\Carbon::parse($item->next_followup_date)->startOfDay()->lt($today)
                                || (
                                \Carbon\Carbon::parse($item->next_followup_date)->startOfDay()->gte($today)
                                && $item->is_done == 1
                                )
                                )
                                );

                                $doneFollowups = $lead->messages->filter(function ($item) {
                                return $item->is_done == 1;
                                });
                                @endphp


                                <div class="container-fluid mt-3">
                                    <div class="followup-main-scroll">
                                        <div class="row g-3">

                                            <!-- TODAY -->
                                            <div class="col-lg-3 col-md-6 col-12">
                                                <div class="p-2 border-end h-100">
                                                    <h6 class="text-warning fw-semibold mb-3">Planned Activities</h6>
                                                    @forelse($Followups as $followup)
                                                    @php
                                                    $date = \Carbon\Carbon::parse($followup->next_followup_date)->startOfDay();
                                                    $today = \Carbon\Carbon::today();
                                                    if ($date->eq($today)) {
                                                    $label = 'Today';
                                                    $class = 'text-warning';
                                                    } else {
                                                    $days = $today->diffInDays($date);
                                                    $label = 'Due in ' . $days . ' day' . ($days > 1 ? 's' : '');
                                                    $class = 'text-success';
                                                    }
                                                    @endphp

                                                    <div class="activity-item mb-3">
                                                        <div class="fw-semibold d-flex gap-1 position-relative">

                                                            <span class="{{ $class }}"> {{ $label }} : </span> for <span class="text_muted fw-bold">{{ $followup?->user->name ?? 'N/A' }}</span>
                                                            <button class="btn p-0 border-0 bg-transparent"
                                                                type="button"
                                                                data-bs-toggle="collapse"
                                                                data-bs-target="#info{{ $followup->id }}">
                                                                <i class="feather feather-info text-muted"></i>
                                                            </button>
                                                        </div>
                                                        <div class="collapse mt-2" id="info{{ $followup->id }}">
                                                            <div class="border rounded p-2 bg-light" style="font-size: 12px; max-width: 350px;">

                                                                <div><strong>Message:</strong> <span class="text-muted">{{ $followup->message ?? '-' }}</span></div>
                                                                <div><strong>Date:</strong> <span class="text-muted">{{ \Carbon\Carbon::parse($followup->next_followup_date)->format('d M Y h:i A') }}</span></div>
                                                                <div><strong>Status:</strong> <span class="text-muted">{{ $followup->bucket ?? '-' }}</span></div>
                                                                <div><strong>Sub Status:</strong> <span class="text-muted">{{ $followup->status ?? '-' }}</span></div>
                                                                <div><strong>Created By:</strong> <span class="text-muted">{{ $followup?->user->name ?? '-' }}</span></div>
                                                                <div><strong>Followup Type:</strong> <span class="text-muted">{{ $followup->followup_type ?? '-' }}</span></div>

                                                            </div>
                                                        </div>
                                                        <div class="d-flex gap-3 mt-1">

                                                            @if($followup->is_done == 0)
                                                            <button type="button"
                                                                onclick="openDoneModal(this)"
                                                                data-id="{{ $followup->id }}"
                                                                class="d-flex align-items-center gap-1 p-0 border-0 bg-transparent">

                                                                <i class="feather feather-check-circle text-success"></i>
                                                                <span class="text-muted" style="font-size: 12px;">Done</span>
                                                            </button>
                                                            @endif

                                                            <button type="button"
                                                                class="d-flex align-items-center gap-1 p-0 border-0 bg-transparent"
                                                                data-bs-toggle="offcanvas"
                                                                data-bs-target="#snoozeOffcanvas-{{ $followup->id }}">

                                                                <i class="feather feather-clock text-warning"></i>
                                                                <span class="text-muted" style="font-size: 12px;">Reschedule</span>
                                                            </button>

                                                        </div>

                                                    </div>
                                                    <div class="offcanvas offcanvas-end" tabindex="-1" id="snoozeOffcanvas-{{ $followup->id }}" aria-labelledby="snoozeOffcanvasLabel-{{ $followup->id }}" style="width: 400px;">
                                                        <div class="offcanvas-header border-bottom bg-light py-3">
                                                            <h6 class="offcanvas-title d-flex align-items-center gap-2 fw-bold text-dark" id="editStatusOffcanvasLabel-{{ $followup->id }}">
                                                                <i class="fa-solid fa-clipboard-list text-secondary"></i>
                                                                Edit Followup for <span class="text-capitalize">{{ optional($followup->lead->user)->name ?? 'User' }}</span>
                                                            </h6>
                                                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                                        </div>

                                                        <div class="offcanvas-body p-4 bg-white">
                                                            <form action="{{ route('lead.callbackUpdate', $followup->id) }}" method="POST" enctype="multipart/form-data">
                                                                @csrf
                                                                <div class="mb-4">
                                                                    <label class="form-label text-muted small mb-1" style="font-size: 12px;">Follow Up Type</label>
                                                                    <select class="form-select bg-light border-0 shadow-sm" name="followup_type" style="font-size: 14px;">
                                                                        <option value="" disabled selected>WhatsApp Follow Up</option>
                                                                        <option value="WhatsApp Call" {{ $followup->followup_type == 'WhatsApp Call' ? 'selected' : '' }}>WhatsApp Call</option>
                                                                        <option value="Call" {{ $followup->followup_type == 'Call' ? 'selected' : '' }}>Call</option>
                                                                        <option value="Whatsapp" {{ $followup->followup_type == 'Whatsapp' ? 'selected' : '' }}>Whatsapp</option>
                                                                    </select>
                                                                </div>

                                                                <div class="mb-4">
                                                                    <label class="form-label text-muted small mb-1" style="font-size: 12px;">Next Follow-up Date</label>
                                                                    <input type="datetime-local" class="form-control bg-light border-0 shadow-sm" name="next_followup_date" value="" style="font-size: 14px;">
                                                                </div>

                                                                <div class="mb-4">
                                                                    <label class="form-label text-muted small mb-1" style="font-size: 12px;">Add Comment / Message</label>
                                                                    <textarea class="form-control bg-light border-0 shadow-sm" name="message" rows="3" placeholder="Write a comment..." style="font-size: 14px; resize: none;"></textarea>
                                                                </div>

                                                                <div class="d-flex justify-content-end gap-3 pt-3 mt-4 border-top">
                                                                    <button type="button" class="btn btn-white text-secondary fw-bold border px-4" data-bs-dismiss="offcanvas" style="font-size: 13px;">CLOSE</button>
                                                                    <button type="submit" class="btn text-white fw-bold px-4" style="background-color: #f47b20; font-size: 13px; border-radius: 4px;">UPDATE DETAILS</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>

                                                    @empty
                                                    <small class="text-muted">No followups</small>
                                                    @endforelse
                                                </div>
                                            </div>

                                            <!-- Today Activity -->
                                            <div class="col-lg-3 col-md-6 col-12">
                                                <div class="p-1 border-end h-100">
                                                    <h6 class="text-success fw-semibold mb-3">Today Activity</h6>

                                                    @forelse($todayActivities as $followup)

                                                    <div class="activity-item mb-3">

                                                        <!-- HEADER -->
                                                        <div class="fw-semibold d-flex align-items-center gap-1 position-relative">
                                                            <span class="text_muted fw-bold">{{ $followup?->user->name ?? 'N/A' }} </span>
                                                            <span class="text-muted" style="font-size: 11px;">
                                                                {{ \Carbon\Carbon::parse($followup->created_at)->format('g:i A') }}
                                                            </span>

                                                        </div>

                                                        <!-- CONTENT (NEW) -->
                                                        <div class="mt-1">
                                                            <div class="fw-semibold" style="font-size: 13px;">
                                                                <strong>Followup Type : </strong>{{ $followup->followup_type ?? 'N/A' }}
                                                            </div>
                                                            <div class="text-muted" style="font-size: 12px;">
                                                                {{ $followup->message ?? '-' }}
                                                            </div>
                                                        </div>

                                                    </div>

                                                    @empty
                                                    <small class="text-muted">No activity today</small>
                                                    @endforelse

                                                </div>
                                            </div>


                                            <div class="col-lg-3 col-md-6 col-12">
                                                <div class="p-1 border-end h-100">
                                                    <h6 class="text-primary fw-semibold mb-3">Past Activity</h6>

                                                    @forelse($previousActivities as $followup)

                                                    <div class="activity-item mb-3">

                                                        <div class="fw-semibold d-flex align-items-center gap-1">
                                                            <span class="fw-bold text_muted">{{ $followup?->user->name ?? 'N/A' }}</span>

                                                            <span class="text-muted" style="font-size:11px;">
                                                                {{ \Carbon\Carbon::parse($followup->created_at)->format('d M Y g:i A') }}
                                                            </span>
                                                        </div>

                                                        <div class="mt-1">
                                                            <div style="font-size:13px;">
                                                                <strong>Followup Type :</strong>
                                                                {{ $followup->followup_type ?? 'N/A' }}
                                                            </div>

                                                            <div class="text-muted" style="font-size:12px;">
                                                                {{ $followup->message ?? '-' }}
                                                            </div>
                                                        </div>

                                                    </div>

                                                    @empty
                                                    <small class="text-muted">No previous activity</small>
                                                    @endforelse
                                                </div>
                                            </div>

                                            <!-- DONE -->
                                            <div class="col-lg-3 col-md-6 col-12">
                                                <div class="p-1 h-100">
                                                    <h6 class="text-danger fw-semibold mb-3">Overdue / Done</h6>

                                                    @forelse($overdueFollowups as $followup)

                                                    <div class="activity-item mb-3">

                                                        @if($followup->is_done == 0)

                                                        @php
                                                        $date = \Carbon\Carbon::parse($followup->next_followup_date)->startOfDay();
                                                        $days = $date->diffInDays(\Carbon\Carbon::today());
                                                        @endphp

                                                        <div class="fw-semibold d-flex gap-1">
                                                            <span class="text-danger">
                                                                Overdue by {{ $days }} day{{ $days > 1 ? 's' : '' }} :
                                                            </span>
                                                            for <span class="fw-bold text_muted">{{ $followup?->user->name ?? 'N/A' }}</span>

                                                            <button class="btn p-0 border-0 bg-transparent"
                                                                type="button"
                                                                data-bs-toggle="collapse"
                                                                data-bs-target="#info{{ $followup->id }}">
                                                                <i class="feather feather-info text-muted"></i>
                                                            </button>
                                                        </div>
                                                        <div class="collapse mt-2" id="info{{ $followup->id }}">
                                                            <div class="border rounded p-2 bg-light" style="font-size: 12px; max-width: 350px;">

                                                                <div><strong>Message:</strong> <span class="text-muted">{{ $followup->message ?? '-' }}</span></div>
                                                                <div><strong>Date:</strong> <span class="text-muted">{{ \Carbon\Carbon::parse($followup->next_followup_date)->format('d M Y h:i A') }}</span></div>
                                                                <div><strong>Status:</strong> <span class="text-muted">{{ $followup->bucket ?? '-' }}</span></div>
                                                                <div><strong>Sub Status:</strong> <span class="text-muted">{{ $followup->status ?? '-' }}</span></div>
                                                                <div><strong>Created By:</strong> <span class="text-muted">{{ $followup?->user->name ?? '-' }}</span></div>
                                                                <div><strong>Followup Type:</strong> <span class="text-muted">{{ $followup->followup_type ?? '-' }}</span></div>

                                                            </div>
                                                        </div>
                                                        <div class="d-flex gap-3 mt-1">

                                                            @if($followup->is_done == 0)
                                                            <button type="button"
                                                                onclick="openDoneModal(this)"
                                                                data-id="{{ $followup->id }}"
                                                                class="d-flex align-items-center gap-1 p-0 border-0 bg-transparent">

                                                                <i class="feather feather-check-circle text-success"></i>
                                                                <span class="text-muted" style="font-size: 12px;">Done</span>
                                                            </button>
                                                            @endif

                                                            <button type="button"
                                                                class="d-flex align-items-center gap-1 p-0 border-0 bg-transparent"
                                                                data-bs-toggle="offcanvas"
                                                                data-bs-target="#snoozeOffcanvas-{{ $followup->id }}">

                                                                <i class="feather feather-clock text-warning"></i>
                                                                <span class="text-muted" style="font-size: 12px;">Reschedule</span>
                                                            </button>

                                                        </div>
                                                        <div class="offcanvas offcanvas-end" tabindex="-1" id="snoozeOffcanvas-{{ $followup->id }}" aria-labelledby="snoozeOffcanvasLabel-{{ $followup->id }}" style="width: 400px;">
                                                            <div class="offcanvas-header border-bottom bg-light py-3">
                                                                <h6 class="offcanvas-title d-flex align-items-center gap-2 fw-bold text-dark" id="editStatusOffcanvasLabel-{{ $followup->id }}">
                                                                    <i class="fa-solid fa-clipboard-list text-secondary"></i>
                                                                    Edit Followup for <span class="text-capitalize">{{ optional($followup->lead->user)->name ?? 'User' }}</span>
                                                                </h6>
                                                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                                            </div>

                                                            <div class="offcanvas-body p-4 bg-white">
                                                                <form action="{{ route('lead.callbackUpdate', $followup->id) }}" method="POST" enctype="multipart/form-data">
                                                                    @csrf
                                                                    <div class="mb-4">
                                                                        <label class="form-label text-muted small mb-1" style="font-size: 12px;">Follow Up Type</label>
                                                                        <select class="form-select bg-light border-0 shadow-sm" name="followup_type" style="font-size: 14px;">
                                                                            <option value="" disabled selected>WhatsApp Follow Up</option>
                                                                            <option value="WhatsApp Call" {{ $followup->followup_type == 'WhatsApp Call' ? 'selected' : '' }}>WhatsApp Call</option>
                                                                            <option value="Call" {{ $followup->followup_type == 'Call' ? 'selected' : '' }}>Call</option>
                                                                            <option value="Whatsapp" {{ $followup->followup_type == 'Whatsapp' ? 'selected' : '' }}>Whatsapp</option>
                                                                        </select>
                                                                    </div>

                                                                    <div class="mb-4">
                                                                        <label class="form-label text-muted small mb-1" style="font-size: 12px;">Next Follow-up Date</label>
                                                                        <input type="datetime-local" class="form-control bg-light border-0 shadow-sm" name="next_followup_date" value="" style="font-size: 14px;">
                                                                    </div>

                                                                    <div class="mb-4">
                                                                        <label class="form-label text-muted small mb-1" style="font-size: 12px;">Add Comment / Message</label>
                                                                        <textarea class="form-control bg-light border-0 shadow-sm" name="message" rows="3" placeholder="Write a comment..." style="font-size: 14px; resize: none;"></textarea>
                                                                    </div>

                                                                    <div class="d-flex justify-content-end gap-3 pt-3 mt-4 border-top">
                                                                        <button type="button" class="btn btn-white text-secondary fw-bold border px-4" data-bs-dismiss="offcanvas" style="font-size: 13px;">CLOSE</button>
                                                                        <button type="submit" class="btn text-white fw-bold px-4" style="background-color: #f47b20; font-size: 13px; border-radius: 4px;">UPDATE DETAILS</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>


                                                        @else

                                                        <!-- ✅ DONE STYLE -->
                                                        <div class="fw-semibold d-flex gap-1">
                                                            <span class="text-success">
                                                                Done :
                                                            </span>
                                                            by <span class="fw-bold text_muted">{{ $followup?->user->name ?? 'N/A' }}</span>
                                                            <span class="text-muted" style="font-size: 11px;">
                                                                {{\Carbon\Carbon::parse($followup->created_at)->format('d M Y')}} {{ \Carbon\Carbon::parse($followup->created_at)->format('g:i A') }}
                                                            </span>
                                                        </div>
                                                        <small class="text-muted">
                                                            {{ $followup->message ?? '-' }}
                                                        </small>

                                                        @endif

                                                    </div>

                                                    @empty
                                                    <small class="text-muted">No overdue</small>
                                                    @endforelse

                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <hr>

                            </div>

                        </div>
                    </div>
                </div>

                <div class="modal fade" id="DoneModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-md modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg">

                            <!-- Header -->
                            <div class="modal-header bg-light border-bottom">
                                <h5 class="modal-title fw-bold text-dark">
                                    <i class="feather-check-circle text-success me-2"></i>
                                    <span>Mark as Done</span>
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <!-- Body -->
                            <form method="POST" action="{{ route('lead.callbackDone', $lead->id) }}">
                                @csrf
                                <input type="hidden" name="lead_id" id="done_lead_id">
                                <div class="modal-body">
                                    <div class="mb-4">
                                        <label class="form-label text-muted small mb-1" style="font-size: 12px;">Follow Up Status</label>
                                        <select class="form-select bg-light border-0 shadow-sm" name="followup_status" style="font-size: 14px;">
                                            <option value="">--select Follow Up status --</option>
                                            <option value="Connected">Connected</option>
                                            <option value="Not Connected">Not Connected</option>
                                            <option value="Discussion Start">Discussion Start</option>
                                            <option value="No Response">No Response</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Feedback</label>
                                        <textarea
                                            class="form-control"
                                            name="feedback"
                                            rows="4"
                                            placeholder="Enter your feedback here..."></textarea>
                                    </div>
                                </div>

                                <!-- Footer -->
                                <div class="modal-footer">
                                    <button
                                        type="button"
                                        class="btn btn-light"
                                        data-bs-dismiss="modal">
                                        Cancel
                                    </button>

                                    <button
                                        type="submit"
                                        class="btn btn-success">
                                        Save
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center p-5 bg-white rounded border shadow-sm mt-3">
                <h5 class="text-muted fw-bold">No Lead Found</h5>
            </div>
            @endforelse
        </div>
    </div>

    <div class="d-flex justify-content-center mt-4 mb-5">
        {{ $leads->withQueryString()->links('pagination::bootstrap-5') }}
    </div>

</div>

{{-- ADD/EDIT LEAD FORM  --}}
<div class="modal fade" id="leadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header bg-light border-bottom">
                <h5 class="modal-title fw-bold text-dark" id="leadModalTitle">
                    <i class="feather-user text-primary me-2"></i> <span>Create New Lead</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="leadForm" method="POST" action="{{ route('lead.store') }}">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="modal-body p-4 bg-white">
                    <h6 class="fw-bold mb-3 text-primary border-bottom pb-2">Student Details</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label small text-muted">Mobile <span class="text-danger">*</span></label>
                            <input type="tel" name="mobile" id="inp_mobile" class="form-control phone-input" required>
                            <input type="hidden" name="country_code" id="inp_country_code" class="country-code-input">
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label small text-muted">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="inp_name" class="form-control auto-name" required>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label small text-muted">Email</label>
                            <input type="email" name="email" id="inp_email" class="form-control auto-email">
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label small text-muted">City</label>
                            <input type="text" name="city" id="inp_city" class="form-control auto-city">
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3 text-primary border-bottom pb-2">Lead Details</h6>
                    <div class="row g-3">
                        <div class="col-lg-4 mb-4">
                            <label class="form-label small text-muted">Lead Source</label>
                            <select name="platform" id="inp_platform" class="form-select">
                                <option value="">Select Source</option>
                                @foreach($sources ?? [] as $source)
                                <option value="{{ $source }}">{{ $source }}</option>
                                @endforeach
                            </select>
                        </div>
                        {{-- <div class="col-lg-4 mb-4">
                            <label class="form-label small text-muted">Campaign Name</label>
                            <input type="text" name="campaign_name" id="inp_campaign" class="form-control">
                        </div>
                        <div class="col-lg-4 mb-4">
                            <label class="form-label small text-muted">Adset Name</label>
                            <input type="text" name="adset_name" id="inp_adset" class="form-control">
                        </div>
                        <div class="col-lg-4 mb-4">
                            <label class="form-label small text-muted">Ad Name</label>
                            <input type="text" name="ad_name" id="inp_ad" class="form-control">
                        </div>
                        <div class="col-lg-4 mb-4">
                            <label class="form-label small text-muted">Form Name</label>
                            <input type="text" name="form_name" id="inp_form" class="form-control">
                        </div> --}}
                        <div class="col-lg-4 mb-4">
                            <label class="form-label small text-muted">Lead Owner</label>
                            <select name="lead_owner" id="inp_owner" class="form-select">
                                <option value="">Select Owner</option>
                                @foreach($owners ?? [] as $owner)
                                <option value="{{ $owner->id }}">{{ $owner->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3 mb-4">
                            <label class="form-label small text-muted">Budget</label>
                            <input type="text" name="budget" id="inp_budget" class="form-control">
                        </div>
                        <div class="col-lg-3 mb-4">
                            <label class="form-label small text-muted">Applying Country</label>
                            <select name="applying_country_for_a_visa" id="inp_country" class="form-select">
                                <option value="">Select Country</option>
                                <option value="UK">UK</option>
                                <option value="Canada">Canada</option>
                                <option value="Australia">Australia</option>
                                <option value="USA">USA</option>
                                <option value="Europe">Europe</option>
                            </select>
                        </div>
                        <div class="col-lg-3 mb-4">
                            <label class="form-label small text-muted">Planned Course</label>
                            <input type="text" name="what_course_are_you_planning_to_study" id="inp_course" class="form-control">
                        </div>
                        <div class="col-lg-3 mb-4">
                            <label class="form-label small text-muted">Preferred Intake</label>
                            <input type="text" name="whats_your_preferred_intake" id="inp_intake" class="form-control">
                        </div>
                        <div class="col-lg-3 mb-4">
                            <label class="form-label small text-muted">Category</label>
                            <select name="category_id" id="inp_category" class="form-select">
                                <option value="">Select Category</option>
                                @foreach($categorys as $category)
                                <option value="{{ $category->id }}">
                                    {{ $category->category_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        {{-- <div class="col-lg-6 mb-4">
                            <label class="form-label small text-muted">Academic Gap</label>
                            <input type="text" name="any_academic_gap" id="inp_gap" class="form-control">
                        </div>
                        <div class="col-lg-6 mb-4">
                            <label class="form-label small text-muted">Highest Completed</label>
                            <input type="text" name="highest_completed" id="inp_highest" class="form-control">
                        </div>
                        <div class="col-12 mt-2">
                            <label class="form-label small text-muted">Description</label>
                            <textarea name="description" id="inp_desc" class="form-control" rows="3"></textarea>
                        </div> --}}
                    </div>
                </div>

                <div class="modal-footer bg-light mx-n4 mb-n4 px-4 py-3 mt-4 border-top">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="btnSubmit" class="btn text-white px-4 fw-medium" style="background-color: #f47b20;">Create Lead</button>
                </div>
            </form>
        </div>
    </div>
</div>


{{-- Email model --}}
<div class="modal fade-scale" id="composeMail" tabindex="-1" aria-labelledby="composeMail" aria-hidden="true" data-bs-dismiss="ou">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content position-relative">
            <div class="mail-loader" id="mailLoader"></div>
            <!--! BEGIN: [modal-header] !-->
            <div class="modal-header">
                <h2 class="d-flex flex-column mb-0">
                    <span class="fs-18 fw-bold mb-1">Compose Mail</span>
                    <small class="d-block fs-11 fw-normal text-muted">Compose Your Message</small>
                </h2>
                <a href="javascript:void(0)" class="avatar-text avatar-md bg-soft-danger close-icon" data-bs-dismiss="modal">
                    <i class="feather-x text-danger"></i>
                </a>
            </div>
            <!--! BEGIN: [modal-body] !-->
            <div class="modal-body p-0">
                <div class="position-relative border-bottom">
                    <div class="px-2 d-flex align-items-center">
                        <div class="p-0 w-100">

                            <select class="form-control border-0 email-template-dropdown">
                                <option selected disabled>Select Template</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="position-relative border-bottom">
                    <div class="px-2 d-flex align-items-center">
                        <div class="p-0 w-100">
                            <input class="form-control border-0 text-dark" name="tomailmodal" placeholder="TO">
                        </div>
                    </div>
                    <a href="javascript:void(0)" class="position-absolute top-50 end-0 translate-middle badge bg-gray-100 border border-gray-3 fs-10 fw-semibold text-uppercase text-dark rounded-pill c-pointer z-index-100" id="ccbccToggleModal"><span data-bs-toggle="tooltip" data-bs-trigger="hover" title="CC / BCC" style="font-size: 9px !important">CC / BCC</span></a>
                </div>
                <div class="border-bottom mail-cc-bcc-fields" id="ccbccToggleModalFileds" style="display: none">
                    <div class="px-2 w-100 d-flex align-items-center border-bottom">
                        <input class="form-control border-0 text-dark" id="cc" name="ccmailmodal" placeholder="CC">
                    </div>
                    <div class="px-2 w-100 d-flex align-items-center">
                        <input class="form-control border-0 text-dark" id="bcc" name="bccmailmodal" placeholder="BCC">
                    </div>
                </div>
                <div class="px-3 w-100 d-flex align-items-center">
                    <input class="form-control border-0 my-1 w-100 shadow-none" name="subject" type="email" placeholder="Subject">
                </div>
                <div class="editor w-100 m-0">
                    <div class="ht-300 border-bottom-0" id="mailEditorModal"></div>
                </div>
            </div>
            <!--! BEGIN: [modal-footer] !-->
            <div class="modal-footer d-flex align-items-center justify-content-between">
                <!--! BEGIN: [mail-editor-action-left] !-->
                <div class="d-flex align-items-center">
                    <div class="dropdown me-2">
                        <a href="javascript:void(0)" data-bs-toggle="dropdown" data-bs-offset="0, 0">
                            <span class="btn btn-primary dropdown-toggle" data-bs-toggle="tooltip" data-bs-trigger="hover" title="Send Message"> Send </span>
                        </a>
                        <div class="dropdown-menu">
                            <a href="javascript:void(0)" class="dropdown-item" data-action-target="#mailActionMessage">
                                <i class="feather-send me-3"></i>
                                <span>Instant Send</span>
                            </a>
                            <a href="javascript:void(0);" class="dropdown-item successAlertMessage">
                                <i class="feather-clock me-3"></i>
                                <span>Schedule Send</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="javascript:void(0)" class="dropdown-item successAlertMessage">
                                <i class="feather-x me-3"></i>
                                <span>Discard Now</span>
                            </a>
                            <a href="javascript:void(0)" class="dropdown-item successAlertMessage">
                                <i class="feather-edit-3 me-3"></i>
                                <span>Save as Draft</span>
                            </a>
                        </div>
                    </div>
                    <div class="dropdown me-2 d-none d-sm-block">
                        <a href="javascript:void(0)" data-bs-toggle="dropdown" data-bs-offset="0, 0">
                            <span class="btn btn-icon" data-bs-toggle="tooltip" data-bs-trigger="hover" title="Pick Template">
                                <i class="feather-hash"></i>
                            </span>
                        </a>
                        <div class="dropdown-menu wd-300">
                            <a href="javascript:void(0)" class="dropdown-item">
                                <i class="feather-file-text me-3"></i>
                                <span>Welcome you message</span>
                            </a>
                            <a href="javascript:void(0)" class="dropdown-item">
                                <i class="feather-file-text me-3"></i>
                                <span>Your issues solved</span>
                            </a>
                            <a href="javascript:void(0)" class="dropdown-item">
                                <i class="feather-file-text me-3"></i>
                                <span>Thank you message</span>
                            </a>
                            <a href="javascript:void(0)" class="dropdown-item">
                                <i class="feather-file-text me-3"></i>
                                <span>Make a offer message</span>
                            </a>
                            <a href="javascript:void(0)" class="dropdown-item">
                                <i class="feather-file-text me-3"></i>
                                <span>Add the Unsubscribe option</span>
                            </a>
                            <a href="javascript:void(0)" class="dropdown-item">
                                <i class="feather-file-text me-3"></i>
                                <span>Thank your customer for joining</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="javascript:void(0)" class="dropdown-item">
                                <i class="feather-save me-3"></i>
                                <span>Save as Template</span>
                            </a>
                            <a href="javascript:void(0)" class="dropdown-item">
                                <i class="feather-sun me-3"></i>
                                <span>Manage Template</span>
                            </a>
                        </div>
                    </div>
                    <div class="dropdown">
                        <a href="javascript:void(0)" data-bs-toggle="dropdown" data-bs-offset="0, 0">
                            <span class="btn btn-icon" data-bs-toggle="tooltip" data-bs-trigger="hover" title="Upload Attachments">
                                <i class="feather-upload"></i>
                            </span>
                        </a>
                        <div class="dropdown-menu">
                            <a href="javascript:void(0)" class="dropdown-item">
                                <i class="feather-image me-3"></i>
                                <span>Upload Images</span>
                            </a>
                            <a href="javascript:void(0)" class="dropdown-item">
                                <i class="feather-video me-3"></i>
                                <span>Upload Videos</span>
                            </a>
                            <a href="javascript:void(0)" class="dropdown-item">
                                <i class="feather-mic me-3"></i>
                                <span>Upload Musics</span>
                            </a>
                            <a href="javascript:void(0)" class="dropdown-item">
                                <i class="feather-file-text me-3"></i>
                                <span>Upload Documents</span>
                            </a>
                        </div>
                    </div>
                </div>
                <!--! BEGIN: [mail-editor-action-right] !-->
                <div class="d-flex align-items-center">
                    <div class="dropdown me-2">
                        <a href="javascript:void(0)" data-bs-toggle="dropdown" data-bs-offset="0, 0">
                            <span class="btn btn-icon" data-bs-toggle="tooltip" data-bs-trigger="hover" title="Editing Actions">
                                <i class="feather-more-horizontal"></i>
                            </span>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="javascript:void(0)" class="dropdown-item">
                                    <i class="feather-type me-3"></i>
                                    <span>Plain Text Mode</span>
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)" class="dropdown-item">
                                    <i class="feather-check me-3"></i>
                                    <span>Check Spelling</span>
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)" class="dropdown-item">
                                    <i class="feather-compass me-3"></i>
                                    <span>Smart Compose</span>
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)" class="dropdown-item">
                                    <i class="feather-feather me-3"></i>
                                    <span>Manage Signature</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <a href="javascript:void(0);" data-bs-dismiss="modal">
                        <span class="btn btn-icon" data-bs-toggle="tooltip" data-bs-trigger="hover" title="Delete Message">
                            <i class="feather-x"></i>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ADD LEAD OWNER MODAL -->
<div class="modal fade" id="leadOwnerModal" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header bg-light">

                <h5 class="modal-title fw-bold">

                    <i class="feather-user-plus text-primary me-2"></i>

                    Add Lead Owner

                </h5>

                <button type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>

            </div>

            <form action="{{ route('lead.bulkOwnerUpdate') }}" method="POST">

                @csrf

                <div class="modal-body">

                    <input type="hidden" name="lead_ids">


                    <div class="mb-3">

                        <label class="form-label small text-muted">Lead Owner</label>

                        <select name="lead_owner" id="" class="form-select">
                            <option value="">Select Owner</option>
                            @foreach($owners ?? [] as $owner)
                            <option value="{{ $owner->id }}">{{ $owner->name }}</option>
                            @endforeach
                        </select>

                    </div>
                </div>

                <div class="modal-footer">

                    <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                        Cancel

                    </button>

                    <button type="submit"
                        class="btn btn-primary">

                        Save Owner

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<script src="{{ asset('crm-assets/assets/vendors/js/quill.min.js') }}"></script>
@push('scripts')
<style>
    .iti {
        width: 100%;
        display: block;
    }

    .iti__country-list {
        z-index: 9999 !important;
        width: 250px !important;
        max-height: 200px;
        overflow-y: auto;
    }

    .followup-main-scroll {
        max-height: 500px;
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 6px;
    }

    textarea.form-control {
        resize: none;
        border-radius: 8px;
    }

    .form-check-input:checked {
        background-color: #ff9800;
        border-color: #ff9800;
    }

    .mail-loader {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.7);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    /* spinner */
    .mail-loader::after {
        content: "";
        width: 40px;
        height: 40px;
        border: 4px solid #ccc;
        border-top-color: #007bff;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    /* blur effect */
    .mail-blur {
        filter: blur(3px);
        pointer-events: none;
    }
</style>
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script> -->
<script>
    document.addEventListener("DOMContentLoaded", function() {

        document.querySelectorAll("form[id^='quickUpdateForm-']").forEach(form => {

            const fileInput = form.querySelector('input[name="call_recording"]');
            const followupType = form.querySelector('select[name="followup_type"]');
            const followupStatus = form.querySelector('select[name="followup_status"]');

            // ✅ REAL-TIME FILE VALIDATION
            if (fileInput) {
                fileInput.addEventListener("change", function() {

                    let oldError = form.querySelector(".file-error");
                    if (oldError) oldError.remove();

                    const file = this.files[0];

                    if (file) {
                        const maxSize = 50 * 1024 * 1024; // 50MB

                        if (file.size > maxSize) {
                            this.value = "";
                            this.classList.add("border-danger");

                            let error = document.createElement("small");
                            error.classList.add("text-danger", "file-error");
                            error.innerText = "File size must be less than 50MB";

                            this.closest(".mb-4").appendChild(error);
                        } else {
                            this.classList.remove("border-danger");
                        }
                    }

                });
            }

            // ✅ FORM SUBMIT VALIDATION
            form.addEventListener("submit", function(e) {

                let isValid = true;

                // remove old errors
                form.querySelectorAll(".error-text, .file-error").forEach(el => el.remove());

                // required fields check
                form.querySelectorAll(".required-field").forEach(field => {

                    if (!field.value) {
                        isValid = false;

                        field.classList.add("border-danger");

                        let error = document.createElement("small");
                        error.classList.add("text-danger", "error-text");
                        error.innerText = "This field is required";

                        field.closest(".mb-4").appendChild(error);
                    } else {
                        field.classList.remove("border-danger");
                    }

                });

                if (followupType && followupType.value.trim() !== "") {

                    if (!followupStatus.value.trim()) {

                        isValid = false;

                        followupStatus.classList.add("border-danger");

                        let error = document.createElement("small");
                        error.classList.add("text-danger", "error-text");
                        error.innerText = "Follow Up Status is required";

                        followupStatus.closest(".mb-4").appendChild(error);

                    } else {
                        followupStatus.classList.remove("border-danger");
                    }
                }

                // ✅ FILE CHECK ON SUBMIT
                if (fileInput && fileInput.files[0]) {
                    const maxSize = 50 * 1024 * 1024;

                    if (fileInput.files[0].size > maxSize) {
                        isValid = false;

                        fileInput.classList.add("border-danger");

                        let error = document.createElement("small");
                        error.classList.add("text-danger", "file-error");
                        error.innerText = "File size must be less than 50MB";

                        fileInput.closest(".mb-4").appendChild(error);
                    }
                }

                if (!isValid) {
                    e.preventDefault();
                }

            });

        });

    });
</script>
<script>
    $('#composeMail').on('shown.bs.modal', function() {

        if (!window.quillInitialized) {
            window.quill = new Quill('#mailEditorModal', {
                theme: 'snow',
                placeholder: 'Write your email...'
            });

            window.quillInitialized = true;
        }

    });

    $(document).ready(function() {
        $("#ccbccToggleModal").click(function() {
            $("#ccbccToggleModalFileds").slideToggle(200);
        });
    });
</script>
<script>
    $(document).ready(function() {

        // Bucket change -> AJAX update + Status dropdown update
        $(document).on("change", ".bucket-select", function() {

            let bucketId = $(this).val();
            let form = $(this).closest("form"); // current form
            let statusSelect = form.find(".status-select"); //  same form ka dropdown

            $.ajax({
                url: "{{ route('lead.getSubStatus') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    lead_bucket_id: bucketId
                },
                success: function(res) {

                    statusSelect.empty();
                    statusSelect.append('<option value="">Select Status</option>');

                    res.children.forEach(function(child) {
                        statusSelect.append(
                            `<option value="${child.name}" data-bg="${child.color}">
                        ${child.name}
                    </option>`
                        );
                    });

                    // ✅ If using select2
                    statusSelect.trigger('change');

                },
                error: function(xhr) {
                    toastr.error("Sub-status load failed!");
                }
            });

        });
    });
</script>
<script>
    // Create Mode
    function openCreateModal() {
        console.log('store method clicked');

        // Form ko reset karein (taaki purana edit data hat jaye)
        document.getElementById('leadForm').reset();

        // Action aur Method theek karein
        document.getElementById('leadForm').action = "{{ route('lead.store') }}";
        document.getElementById('formMethod').value = "POST";

        // UI Text change karein
        document.querySelector('#leadModalTitle span').innerText = "Create New Lead";
        document.getElementById('btnSubmit').innerText = "Create Lead";

        // Modal Open karein
        var myModal = new bootstrap.Modal(document.getElementById('leadModal'));
        myModal.show();
    }

    // Edit Mode
    function openEditModal(button) {
        console.log('Edit method clicked');
        console.log(document.getElementById('leadForm').action);
        // Button se data extract karein
        let lead = JSON.parse(button.getAttribute('data-lead') || '{}');
        let user = JSON.parse(button.getAttribute('data-user') || '{}');

        // Form ko update mode me laayein
        let updateUrl = "{{ url('/lead/update') }}/" + lead.id; // Ya jo aapka update route format ho
        document.getElementById('leadForm').action = updateUrl;
        document.getElementById('formMethod').value = "PUT"; // Laravel spoofing

        // UI Text change karein
        document.querySelector('#leadModalTitle span').innerText = "Edit Lead: " + (user.name || 'Unknown');
        document.getElementById('btnSubmit').innerText = "Update Lead";

        // Form mein purana data bharein
        document.getElementById('inp_mobile').value = user.contact_no || '';
        document.getElementById('inp_country_code').value = user.country_code || '';
        document.getElementById('inp_name').value = user.name || '';
        document.getElementById('inp_email').value = user.email || '';
        document.getElementById('inp_city').value = lead.city || user.city || '';

        document.getElementById('inp_platform').value = lead.platform || '';
        // document.getElementById('inp_campaign').value = lead.campaign_name || '';
        // document.getElementById('inp_adset').value = lead.adset_name || '';
        // document.getElementById('inp_ad').value = lead.ad_name || '';
        // document.getElementById('inp_form').value = lead.form_name || '';
        document.getElementById('inp_owner').value = lead.lead_owner || '';
        document.getElementById('inp_category').value = lead.category_id || '';
        document.getElementById('inp_budget').value = lead.budget || '';
        document.getElementById('inp_country').value = lead.applying_country_for_a_visa || '';
        document.getElementById('inp_course').value = lead.what_course_are_you_planning_to_study || '';
        document.getElementById('inp_intake').value = lead.whats_your_preferred_intake || '';
        // document.getElementById('inp_gap').value = lead.any_academic_gap || '';
        // document.getElementById('inp_highest').value = lead.highest_completed || '';
        // document.getElementById('inp_desc').value = lead.description || '';

        // Modal Open karein
        var myModal = new bootstrap.Modal(document.getElementById('leadModal'));
        myModal.show();
    }

    function openDoneModal(button) {
        let leadId = button.getAttribute('data-id');

        document.getElementById('done_lead_id').value = leadId;
        var myModal = new bootstrap.Modal(document.getElementById('DoneModal'));
        myModal.show();
    }
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {

        // 1. Initialize IntlTelInput for ALL inputs with class '.phone-input'
        document.querySelectorAll('.phone-input').forEach(function(input) {

            var iti = window.intlTelInput(input, {
                initialCountry: "in",
                separateDialCode: true,
                preferredCountries: ["in", "us", "gb", "au", "ca"],
                utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/utils.js"
            });

            // Function to update hidden country code input
            function updateCountryCode() {
                var countryData = iti.getSelectedCountryData();
                // Find the hidden input within the same form
                var hiddenInput = input.closest('form').querySelector('.country-code-input');
                if (hiddenInput) {
                    hiddenInput.value = "+" + countryData.dialCode;
                }
            }

            // Set initial code and listen for changes
            updateCountryCode();
            input.addEventListener("countrychange", updateCountryCode);

            // 2. Auto-fetch User Details on Mobile Blur
            input.addEventListener("blur", function() {
                let mobile = input.value.trim();
                if (!mobile) return;

                let modal = input.closest('.modal'); // Find the current modal

                fetch("{{ route('user.search.byMobile') }}?mobile=" + encodeURIComponent(mobile))
                    .then(res => res.json())
                    .then(data => {
                        let nameInput = modal.querySelector(".auto-name");
                        let emailInput = modal.querySelector(".auto-email");
                        let cityInput = modal.querySelector(".auto-city");

                        if (data.exists) {
                            nameInput.value = data.user.name;
                            emailInput.value = data.user.email;
                            cityInput.value = data.user.city;

                            nameInput.readOnly = true;
                            emailInput.readOnly = true;
                            cityInput.readOnly = true;
                        } else {
                            nameInput.value = "";
                            emailInput.value = "";
                            cityInput.value = "";

                            nameInput.readOnly = false;
                            emailInput.readOnly = false;
                            cityInput.readOnly = false;
                        }
                    });
            });
        });

    });
</script>
<script>
    let allTemplates = {};

    document.querySelectorAll('.offcanvas').forEach(function(offcanvas) {

        offcanvas.addEventListener('show.bs.offcanvas', function() {

            let leadId = this.id.replace('SMSSent', '');

            if (allTemplates[leadId]) return;

            fetch(`/fetch-templates`)
                .then(res => res.json())
                .then(data => {
                    allTemplates[leadId] = data.templates;

                    let dropdown = this.querySelector('.template-dropdown');

                    dropdown.innerHTML = `<option selected disabled>Select Template</option>`;

                    data.templates.forEach(template => {
                        let option = document.createElement("option");
                        option.value = template.id;
                        option.textContent = template.name;

                        dropdown.appendChild(option);
                    });

                });

        });

    });
</script>
<script>
    document.addEventListener("change", function(e) {

        if (e.target.classList.contains("template-dropdown")) {

            let offcanvas = e.target.closest('.offcanvas');
            let leadId = offcanvas.id.replace('SMSSent', '');
            let templateId = e.target.value;

            let template = allTemplates[leadId].find(t => t.id == templateId);

            if (template) {
                let textarea = offcanvas.querySelector("textarea");
                textarea.value = template.message;
            }
        }

    });
</script>

<script>
    let emailTemplates = {};

    document.getElementById('composeMail').addEventListener('show.bs.modal', function() {

        let modal = this;

        // prevent multiple API calls
        if (emailTemplates.loaded) return;

        fetch('/fetch-templates')
            .then(res => res.json())
            .then(data => {

                emailTemplates.data = data.templates;
                emailTemplates.loaded = true;

                let dropdown = modal.querySelector('.email-template-dropdown');

                dropdown.innerHTML = `<option selected disabled>Select Template</option>`;

                data.templates.forEach(template => {
                    let option = document.createElement("option");
                    option.value = template.id;
                    option.textContent = template.name;
                    dropdown.appendChild(option);
                });

            })
            .catch(err => console.log(err));
    });
</script>
<script>
    document.addEventListener("change", function(e) {

        if (e.target.classList.contains("email-template-dropdown")) {

            let modal = e.target.closest('#composeMail');
            let templateId = e.target.value;

            let template = emailTemplates.data.find(t => t.id == templateId);

            if (template) {
                if (window.quill) {
                    window.quill.setText(template.message);
                }
            }
        }

    });
</script>

<script>
    document.addEventListener("click", function(e) {

        if (e.target.classList.contains("send-sms-btn")) {

            let offcanvas = e.target.closest('.offcanvas');

            // 📱 Selected Numbers
            let numbers = [];
            offcanvas.querySelectorAll(".number-checkbox:checked").forEach(cb => {
                if (cb.value) numbers.push(cb.value);
            });

            // 📝 Message
            let message = offcanvas.querySelector("textarea").value;

            if (numbers.length === 0) {
                alert("Please select at least one number");
                return;
            }

            if (!message.trim()) {
                alert("Message cannot be empty");
                return;
            }

            //  API Call
            fetch(`/send-sms`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        numbers: numbers,
                        message: message
                    })
                })
                .then(res => res.json())
                .then(data => {
                    console.log(data);
                    alert("SMS Sent Successfully");
                })
                .catch(err => {
                    console.error(err);
                    alert("Failed to send SMS");
                });

        }

    });
</script>
<script src="https://cdn.jsdelivr.net/npm/emailjs-com@3/dist/email.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {

        const selectAll = document.getElementById("selectAll");
        const checkboxes = document.querySelectorAll(".lead-checkbox");

        // ✅ Select All click
        if (selectAll) {
            selectAll.addEventListener("change", function() {

                checkboxes.forEach(cb => {
                    cb.checked = selectAll.checked;
                });

            });
        }

        // ✅ Individual checkbox change → SelectAll update
        checkboxes.forEach(cb => {
            cb.addEventListener("change", function() {

                let allChecked = document.querySelectorAll(".lead-checkbox:checked").length === checkboxes.length;

                selectAll.checked = allChecked;

            });
        });

    });
</script>
<script>
    document.querySelector(".bulk-owner").addEventListener("click", function() {

        let leadIds = [];

        document.querySelectorAll(".lead-checkbox:checked").forEach(cb => {

            let id = cb.value;

            if (id) {
                leadIds.push(id);
            }

        });

        // ✅ check selected or not
        if (leadIds.length === 0) {

            alert("Please select at least one lead");

            return;
        }

        // ✅ hidden input me ids bhejo
        document.querySelector('input[name="lead_ids"]').value = leadIds.join(",");

        // ✅ modal open
        let modal = new bootstrap.Modal(
            document.getElementById('leadOwnerModal')
        );

        modal.show();

    });

    document.querySelector(".bulk-email").addEventListener("click", function() {

        let emails = [];

        document.querySelectorAll(".lead-checkbox:checked").forEach(cb => {
            let email = cb.getAttribute("data-email");
            if (email) emails.push(email);
        });

        if (emails.length === 0) {
            alert("Please select at least one lead");
            return;
        }

        // 👉 TO field me sab emails daal do (comma separated)
        document.querySelector('input[name="tomailmodal"]').value = emails.join(",");

        // 👉 modal open
        let modal = new bootstrap.Modal(document.getElementById('composeMail'));
        modal.show();
    });

    (function(email) {
        emailjs.init("7C4A3PjvrSEwKHu2n");
    })();

    document.addEventListener("DOMContentLoaded", function() {

        const sendBtn = document.querySelector('[data-action-target="#mailActionMessage"]');
        const loader = document.getElementById('mailLoader');
        const modalContent = document.querySelector('#composeMail .modal-content');

        if (sendBtn) {
            sendBtn.addEventListener("click", function() {

                let to = document.querySelector('input[name="tomailmodal"]').value;
                let subject = document.querySelector('input[name="subject"]').value;
                let message = document.getElementById('mailEditorModal').innerText;
                let cc = document.getElementById('cc').value;
                let bcc = document.getElementById('bcc').value;

                message = message.replace(/\n+/g, '\n').trim();

                if (!to || !subject) {
                    alert("To and Subject fields are required.");
                    return;
                }

                // ✅ START LOADER
                loader.style.display = "flex";
                modalContent.classList.add("mail-blur");
                sendBtn.style.pointerEvents = "none";

                let params = {
                    to: to,
                    cc: cc,
                    bcc: bcc,
                    subject: subject,
                    message: message
                };

                emailjs.send("service_q245cck", "template_2lq452u", params)
                    .then(function(response) {

                        alert("Email Sent Successfully");

                        // ✅ STOP LOADER
                        loader.style.display = "none";
                        modalContent.classList.remove("mail-blur");

                        // ✅ CLOSE MODAL
                        let modal = document.getElementById('composeMail');
                        let modalInstance = bootstrap.Modal.getInstance(modal);
                        if (modalInstance) modalInstance.hide();

                    })
                    .catch(function(error) {

                        console.log(error);
                        alert("Failed ");

                        // ✅ STOP LOADER ON ERROR
                        loader.style.display = "none";
                        modalContent.classList.remove("mail-blur");
                        sendBtn.style.pointerEvents = "auto";

                    });

            });
        }

    });
</script>
<script>
    document.getElementById('bulkDeleteForm').addEventListener('submit', function(e) {

        let ids = [];

        document.querySelectorAll('.lead-checkbox:checked').forEach(cb => {
            ids.push(cb.value);
        });

        if (ids.length === 0) {
            e.preventDefault();
            alert('Please select at least one lead');
            return;
        }

        if (!confirm('Are you sure you want to delete selected leads?')) {
            e.preventDefault();
            return;
        }

        document.getElementById('deleteIds').value = ids.join(',');
    });
</script>

<script id="setFollowupTypeScript">
    function setFollowupType(type) {
        document.getElementById('followupTypeInput').value = type;
        document.getElementById('followupTypeForm').submit();
    }
</script>

@endpush
@endsection