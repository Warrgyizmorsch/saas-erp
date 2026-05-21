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
                    <h5 class="m-b-10">Activity Report</h5>
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

        {{-- Filters --}}
        {{-- Collapsible Lead Stats --}}
        <div id="collapseDailyReportFilter"
            class="accordion-collapse collapse page-header-collapse {{ request('from') || request('to') || request()->filled('name') || request()->filled('owner_id') ? 'show' : '' }}">

            <div class="accordion-body pb-2">

                <form method="GET" action="{{ route('lead.leadActivity') }}" class="row g-3 mb-4" id="lead-filter-form">

                    <!-- Quick Presets -->
                    <div class="col-12 mb-3">
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm preset-btn2"
                                data-preset="today">Today</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm preset-btn2"
                                data-preset="yesterday">Yesterday</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm preset-btn2"
                                data-preset="7days">Last 7 Days</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm preset-btn2"
                                data-preset="30days">Last 30 Days</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm preset-btn2"
                                data-preset="this-month">This Month</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm preset-btn2"
                                data-preset="last-month">Last Month</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm preset-btn2"
                                data-preset="custom">Custom</button>
                        </div>
                    </div>

                    <!-- From -->
                    <div class="col-md-3">
                        <label class="form-label">From Date</label>
                        <input type="date" name="from" id="from-date2" value="{{ request('from') }}" class="form-control">
                    </div>

                    <!-- To -->
                    <div class="col-md-3">
                        <label class="form-label">To Date</label>
                        <input type="date" name="to" id="to-date2" value="{{ request('to') }}" class="form-control">
                    </div>

                    <!-- Name -->
                    <div class="col-md-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" placeholder="Enter Lead Name" value="{{ request('name') }}"
                            class="form-control">
                    </div>

                    <!-- Owner -->
                    <div class="col-md-3">
                        <label class="form-label">Lead owner</label>
                        <select name="owner_id" class="form-control">
                            <option value="">All Owners</option>

                            <option value="null" {{ request('owner_id') == 'null' ? 'selected' : '' }}>
                                Unknown
                            </option>

                            @foreach($owners ?? [] as $owner)
                                <option value="{{ $owner->id }}" {{ request('owner_id') == $owner->id ? 'selected' : '' }}>
                                    {{ $owner->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Buttons -->
                    <div class="col-12 d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary px-4">Filter</button>
                        <a href="{{ route('lead.leadActivity') }}" class="btn btn-outline-danger px-4">Reset</a>
                    </div>

                </form>
            </div>
        </div>



        {{-- ===================== MAIN CONTENT ===================== --}}

        <div class="main-content mt-3">
            <div class="row">

                <div class="col-12">
                    <div class="card-body p-0">

                        <div class="d-flex overflow-auto border-bottom mb-2 pb-2 gap-3 align-items-center">

                            <div class="d-flex justify-content-start align-items-center gap-2 p-3 border-bottom">

                                <label class="mb-0">Show</label>

                                <form method="GET" class="d-flex align-items-center gap-2">
                                    @foreach(request()->except('per_page', 'page') as $key => $value)
                                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                    @endforeach

                                    <select name="per_page" class="form-select form-select-sm"
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
                            <a class=" text-nowrap">
                                All Activity {{$callbacks->total()}}
                            </a>

                        </div>


                        @forelse($callbacks as $cb)
                            <div class="card mb-3 shadow-sm border-0">
                                <div class="card-body">

                                    <div class="row">

                                        <!-- 🔹 1. Lead Details -->
                                        <div class="col-md-3 border-end">
                                            <h6 class="fw-bold text-primary mb-2">Lead Details</h6>

                                            <div><strong>ID:</strong> #{{ $cb->lead->id ?? '-' }}</div>
                                            <div><strong>Name:</strong> {{ $cb->lead->user->name ?? '-' }}</div>
                                            <div><strong>Email:</strong> {{ $cb->lead->user->email ?? '-' }}</div>
                                            <div><strong>Contact:</strong> {{ $cb->lead->user->contact_no ?? '-' }}</div>
                                        </div>


                                        <!-- 🔹 2. Activity History -->
                                        <div class="col-md-6 border-end">
                                            <h6 class="fw-bold text-success mb-2">Activity</h6>

                                            {{-- Followup Type --}}
                                            @if($cb->followup_type)
                                                <div><i class="fas fa-tag text-primary me-1"></i> <strong>Followup Type:</strong>
                                                    {{ $cb->followup_type }}</div>
                                            @endif

                                            {{-- Followup Status --}}
                                            @if($cb->followup_status)
                                                <div><i class="fas fa-chart-line text-info me-1"></i> <strong>Followup
                                                        Status:</strong> {{ $cb->followup_status }}</div>
                                            @endif

                                            {{-- Message --}}
                                            @if($cb->message)
                                                <div><i class="fas fa-comment-dots text-warning me-1"></i> <strong>Message:</strong>
                                                    {{ $cb->message }}</div>
                                            @endif

                                            {{-- Lead Engagement --}}
                                            @if($cb->lead_engagement_status)
                                                <div> <i class="fas fa-signal text-danger me-1"></i> <strong>Engagement:</strong>
                                                    {{ $cb->lead_engagement_status }}</div>
                                            @endif

                                            {{-- Bucket --}}
                                            @if($cb->bucket)
                                                <div><i class="fas fa-folder-open text-secondary me-1"></i> <strong>Bucket:</strong>
                                                    {{ $cb->bucket }}</div>
                                            @endif

                                            {{-- Sub Status --}}
                                            @if($cb->status)
                                                <div><i class="fas fa-bookmark text-dark me-1"></i> <strong>Sub Status:</strong>
                                                    {{ $cb->status }}</div>
                                            @endif

                                            {{-- Next Followup Date --}}
                                            @if(!empty($cb->next_followup_date))
                                                <div class="text-warning">
                                                    <i class="fas fa-clock me-1"></i>
                                                    <strong>Next Followup:</strong>
                                                    {{ \Carbon\Carbon::parse($cb->next_followup_date)->format('d M Y, h:i A') }}
                                                </div>
                                            @endif

                                            {{-- Done Status --}}
                                            @if((int) $cb->is_done === 1)
                                                <div class="text-success fw-semibold">
                                                    <i class="fas fa-check-circle me-1"></i>
                                                    Followup Done
                                                </div>
                                            @endif

                                            {{-- Date --}}
                                            <div class="text-muted mt-2 small">
                                                {{ \Carbon\Carbon::parse($cb->created_at)->format('d M Y, h:i A') }}
                                            </div>
                                        </div>


                                        <!-- 🔹 3. Owner -->
                                        <div class="col-md-3">
                                            <h6 class="fw-bold text-dark mb-2">Owner</h6>
                                            <div class="d-flex align-items-center gap-2 justify-content-start">
                                                <div class="avatar-image">
                                                    @if(optional($cb->lead->owner)->image)

                                                        <img src="{{ asset('storage/' . $cb->lead->owner->image) }}" alt=""
                                                            class="img-fluid rounded-circle">

                                                    @else

                                                        <div class="rounded-circle border d-flex align-items-center justify-content-center"
                                                            style="width:40px; height:40px;">
                                                        </div>

                                                    @endif
                                                </div>

                                                <div>
                                                    {{ $cb->lead->owner->name ?? 'Unknown' }}
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                </div>
                            </div>

                        @empty
                            <div class="text-center text-muted">No Activity Found</div>
                        @endforelse


                        <div class="m-4" style="display: flex; justify-content: center;">
                            {{ $callbacks->withQueryString()->links('pagination::bootstrap-4') }}
                        </div>


                    </div>
                </div>

            </div>
        </div>
        <style>
            .card {
                border-radius: 12px;
            }

            .card-body div {
                margin-bottom: 4px;
                font-size: 14px;
            }

            h6 {
                border-bottom: 1px solid #eee;
                padding-bottom: 5px;
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function () {

                const fromInput = document.getElementById('from-date2');
                const toInput = document.getElementById('to-date2');
                const buttons = document.querySelectorAll('.preset-btn2');

                // ===== Today Variables (GLOBAL) =====
                const today = new Date();

                const yesterday = new Date();
                yesterday.setDate(today.getDate() - 1);

                const days7 = new Date();
                days7.setDate(today.getDate() - 6);

                const days30 = new Date();
                days30.setDate(today.getDate() - 29);

                const thisMonth = new Date(today.getFullYear(), today.getMonth(), 1);

                const lastMonthStart = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                const lastMonthEnd = new Date(today.getFullYear(), today.getMonth(), 0);

                function formatDate(date) {
                    return date.toISOString().split('T')[0];
                }

                function setActive(type) {
                    buttons.forEach(btn => btn.classList.remove('active'));
                    document.querySelector(`.preset-btn2[data-preset="${type}"]`)?.classList.add('active');
                }

                // ===== Click Event =====
                buttons.forEach(btn => {
                    btn.addEventListener('click', function () {

                        let preset = this.dataset.preset;
                        let from = new Date();
                        let to = new Date();

                        switch (preset) {

                            case 'today':
                                break;

                            case 'yesterday':
                                from = new Date(yesterday);
                                to = new Date(yesterday);
                                break;

                            case '7days':
                                from = new Date(days7);
                                break;

                            case '30days':
                                from = new Date(days30);
                                break;

                            case 'this-month':
                                from = new Date(thisMonth);
                                break;

                            case 'last-month':
                                from = new Date(lastMonthStart);
                                to = new Date(lastMonthEnd);
                                break;

                            case 'custom':
                                setActive('custom');
                                return;
                        }

                        fromInput.value = formatDate(from);
                        toInput.value = formatDate(to);

                        setActive(preset);
                    });
                });

                // ===== Page Load Detect Active Button =====
                const from = fromInput.value;
                const to = toInput.value;

                if (from === formatDate(today) && to === formatDate(today)) {
                    setActive('today');

                } else if (from === formatDate(yesterday) && to === formatDate(yesterday)) {
                    setActive('yesterday');

                } else if (from === formatDate(days7) && to === formatDate(today)) {
                    setActive('7days');

                } else if (from === formatDate(days30) && to === formatDate(today)) {
                    setActive('30days');

                } else if (from === formatDate(thisMonth) && to === formatDate(today)) {
                    setActive('this-month');

                } else if (from === formatDate(lastMonthStart) && to === formatDate(lastMonthEnd)) {
                    setActive('last-month');

                } else {
                    setActive('custom');
                }

            });
        </script>
@endsection