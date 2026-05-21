@extends('shared::layouts.app')

@section('content')
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">{{ $title ?? 'Dashboard' }}</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">Home</a>
                    </li>
                    <li class="breadcrumb-item">{{ $breadcrumb ?? 'Analytics' }}</li>
                </ul>
            </div>

            <div class="page-header-right ms-auto">
                <div class="page-header-right-items">

                    {{-- Mobile Back Button --}}
                    <div class="d-flex d-md-none">
                        <a href="javascript:void(0)" class="page-header-right-close-toggle">
                            <i class="feather-arrow-left me-2"></i>
                            <span>Back</span>
                        </a>
                    </div>

                    <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">

                        {{-- Date Range Picker --}}
                        <!-- <div id="reportrange" class="reportrange-picker d-flex align-items-center">
                            <span class="reportrange-picker-field">{{ $dateRange ?? '' }}</span>
                        </div> -->
                        {{-- Chart Toggle --}}
                        <a href="javascript:void(0);" class="btn btn-icon btn-light-brand" data-bs-toggle="collapse"
                            data-bs-target="#collapseOne">
                            <i class="feather-bar-chart"></i>
                        </a>
                    </div>
                </div>

                {{-- Mobile Filter Toggle --}}
                <div class="d-md-none d-flex align-items-center">
                    <a href="javascript:void(0)" class="page-header-right-open-toggle">
                        <i class="feather-align-right fs-20"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- Collapsible filter Stats --}}
        <div id="collapseOne" class="accordion-collapse collapse page-header-collapse {{ request('start') || request('end') ? 'show' : '' }}">
            <div class="accordion-body pb-2">

                <form method="GET" action="{{ route('dashboard') }}" class="row g-3 mb-4" id="date-filter-form">

                    <!-- Quick Presets -->
                    <div class="col-12 mb-3">
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm preset-btn" data-preset="today">Today</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm preset-btn" data-preset="yesterday">Yesterday</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm preset-btn" data-preset="7days">Last 7 Days</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm preset-btn" data-preset="30days">Last 30 Days</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm preset-btn" data-preset="this-month">This Month</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm preset-btn" data-preset="last-month">Last Month</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm preset-btn active" data-preset="custom">Custom</button>
                        </div>
                    </div>

                    <!-- Date From -->
                    <div class="col-md-3">
                        <label class="form-label">Start</label>
                        <input type="date" name="start" id="start-date" class="form-control" value="{{ request('start') }}">
                    </div>

                    <!-- Date To -->
                    <div class="col-md-3">
                        <label class="form-label">End</label>
                        <input type="date" name="end" id="end-date" class="form-control" value="{{ request('end') }}">
                    </div>

                    <!-- Buttons -->
                    <div class="col-12 d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary px-4">Filter</button>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-danger px-4">Reset</a>
                    </div>

                </form>

            </div>
        </div>

        <!-- JavaScript for quick presets -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                const startInput = document.getElementById('start-date');
                const endInput = document.getElementById('end-date');
                const form = document.getElementById('date-filter-form');

                document.querySelectorAll('.preset-btn').forEach(btn => {
                    btn.addEventListener('click', function() {

                        // Remove active class from all buttons
                        document.querySelectorAll('.preset-btn').forEach(b => b.classList.remove('active'));
                        // Add active to clicked button
                        this.classList.add('active');

                        const preset = this.dataset.preset;
                        const today = new Date();
                        let start = new Date();
                        let end = new Date();

                        switch (preset) {
                            case 'today':
                                start = today;
                                end = today;
                                break;

                            case 'yesterday':
                                start.setDate(today.getDate() - 1);
                                end = new Date(start);
                                break;

                            case '7days':
                                start.setDate(today.getDate() - 7);
                                break;

                            case '30days':
                                start.setDate(today.getDate() - 30);
                                break;

                            case 'this-month':
                                start = new Date(today.getFullYear(), today.getMonth(), 2);
                                break;

                            case 'last-month':
                                start = new Date(today.getFullYear(), today.getMonth() - 1, 2);
                                end = new Date(today.getFullYear(), today.getMonth(), 1);
                                break;

                            case 'custom':
                                // do nothing - let user pick manually
                                return;
                        }

                        // Format dates for input type="date"
                        startInput.value = start.toISOString().split('T')[0];
                        endInput.value = end.toISOString().split('T')[0];

                        // Optional: auto-submit on preset click
                        // form.submit();
                    });
                });

            });
        </script>

        <style>
            /* Optional: make active preset more visible */
            .preset-btn.active {
                background-color: #0d6efd;
                color: white;
                border-color: #0d6efd;
            }

            .preset-btn:hover {
                background-color: #e9ecef;
            }
        </style>
        <div class="main-content">
            <div class="row">

                <div class="col-12">
                    <div class="card stretch stretch-full shadow-sm">
                        <div class="card-body">

                            <div class="hstack justify-content-between mb-4">
                                <div>
                                    <h5 class="mb-1 fw-bold">Pipeline Overview</h5>
                                    <span class="fs-12 text-muted">
                                        Main Buckets Summary • Total: {{ $totalLeads }}
                                    </span>
                                </div>
                            </div>

                            <div class="row g-3">

                                @foreach($buckets as $bucket)

                                    @php
                                        $bucketName = strtolower(trim($bucket->name)); // Normalize for reliable matching

                                        // Default fallback
                                        $icon = 'bi-folder2-open';
                                        $color = 'text-primary';

                                        // Specific matches based on your exact bucket names
                                        if (str_contains($bucketName, 'new') || str_contains($bucketName, 'lead') || str_contains($bucketName, 'new lead')) {
                                            $icon = 'bi-person-plus-fill'; // adding new person / fresh lead
                                            $color = 'text-success'; // green = new & positive
                                        } elseif (str_contains($bucketName, 'not connected') || str_contains($bucketName, 'no connect')) {
                                            $icon = 'bi-telephone-x'; // call failed / no connection
                                            $color = 'text-danger'; // red = problem / needs attention
                                        } elseif (str_contains($bucketName, 'follow up') || str_contains($bucketName, 'follow-up')) {
                                            $icon = 'bi-arrow-repeat'; // repeat / follow-up action
                                            $color = 'text-warning'; // yellow = pending action
                                        } elseif (str_contains($bucketName, 'options shortlisting') || str_contains($bucketName, 'shortlist')) {
                                            $icon = 'bi-list-check'; // checklist / shortlisting
                                            $color = 'text-info'; // info blue = in progress / selection
                                        } elseif (str_contains($bucketName, 'application') || str_contains($bucketName, 'apply')) {
                                            $icon = 'bi-file-earmark-person'; // application form with person
                                            $color = 'text-primary'; // primary blue = core process step
                                        } elseif (str_contains($bucketName, 'offer letter') || str_contains($bucketName, 'offer')) {
                                            $icon = 'bi-envelope-check'; // envelope with check = offer sent/approved
                                            $color = 'text-success'; // green = positive milestone
                                        } elseif (str_contains($bucketName, 'payment')) {
                                            $icon = 'bi-currency-rupee'; // rupee / money (Bootstrap has bi-currency-rupee)
                                            $color = 'text-success'; // green = money received or due
                                        } elseif (str_contains($bucketName, 'cas')) {
                                            $icon = 'bi-shield-check'; // shield = compliance / CAS process
                                            $color = 'text-info'; // info = verification step
                                        } elseif (str_contains($bucketName, 'visa')) {
                                            $icon = 'bi-globe'; // globe = international / visa
                                            $color = 'text-primary'; // primary = important international step
                                        } elseif (str_contains($bucketName, 'enrollment') || str_contains($bucketName, 'enrol')) {
                                            $icon = 'bi-mortarboard-fill'; // graduation cap = enrollment / admission
                                            $color = 'text-success'; // green = final academic step
                                        } elseif (str_contains($bucketName, 'closed') || str_contains($bucketName, 'close')) {
                                            $icon = 'bi-check2-circle'; // double check = completed & closed
                                            $color = 'text-success'; // green = done
                                        } elseif (str_contains($bucketName, 'cold lead') || str_contains($bucketName, 'cold')) {
                                            $icon = 'bi-snow'; // snowflake = cold / inactive
                                            $color = 'text-muted'; // gray = low priority / dormant
                                        } elseif (str_contains($bucketName, 'next intake')) {
                                            $icon = 'bi-calendar-event'; // calendar = future intake date
                                            $color = 'text-warning'; // yellow = upcoming / pending
                                        }

                                        // Optional fallback if nothing matches
                                        if ($icon === 'bi-folder2-open' && str_contains($bucketName, 'lead')) {
                                            $icon = 'bi-person-lines-fill';
                                            $color = 'text-primary';
                                        }
                                    @endphp

                                    <div class="col-xxl-2 col-lg-3 col-md-6">
                                        <div class="card border border-dashed border-gray-5 h-100 hover-shadow transition-all">
                                            <div class="card-body text-center">

                                                <div class="mb-2">
                                                    <i class="bi {{ $icon }} fs-2 {{ $color }}"></i>
                                                </div>

                                                <div class="fs-3 fw-bold text-dark">
                                                    {{ $bucket->total_leads }}
                                                </div>

                                                <p class="fs-12 text-muted mb-0">
                                                    {{ $bucket->name }}
                                                </p>

                                            </div>
                                        </div>
                                    </div>

                                @endforeach

                            </div>

                        </div>
                    </div>
                </div>
                @if($firstBucket)
                    <div class="col-12 mt-2">
                        <div class="card stretch stretch-full shadow-sm">
                            <div class="card-body">

                                <div class="hstack justify-content-between mb-4">
                                    <div>
                                        <h5 class="mb-1 fw-bold">{{ $firstBucket->name }} Status Overview</h5>
                                        <span class="fs-12 text-muted">
                                            {{ auth()->user()->role_id == 1 ? 'All Leads View' : 'My Leads View' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="row g-3">

                                    @foreach($firstBucket->children as $child)
                                        @php
                                            $statusName = strtolower(trim($child->name)); // Normalize for matching

                                            // Default fallback
                                            $icon = 'bi-circle-fill';
                                            $color = 'text-secondary';

                                            // Specific matches based on your exact status names
                                            if (str_contains($statusName, 'sop under preparation') || str_contains($statusName, 'sop preparation')) {
                                                $icon = 'bi-file-earmark-plus'; // alternative: creating/preparing doc
                                                $color = 'text-primary';
                                            } elseif (str_contains($statusName, 'submitted')) {
                                                $icon = 'bi-check-circle';
                                                $color = 'text-success';
                                            } elseif (str_contains($statusName, 'processed') || str_contains($statusName, 'offer awaited')) {
                                                $icon = 'bi-clock-history';
                                                $color = 'text-warning';
                                            } elseif (str_contains($statusName, 'other') || str_contains($statusName, 'uncategorized')) {
                                                $icon = 'bi-question-circle';
                                                $color = 'text-secondary';
                                            }
                                        @endphp

                                        <div class="col-xxl-2 col-lg-3 col-md-6">
                                            <div class="card border border-dashed border-gray-5 h-100 hover-shadow transition-all">
                                                <div class="card-body text-center">
                                                    <div class="mb-2">
                                                        <i class="bi {{ $icon }} fs-2 {{ $color }}"></i>
                                                    </div>
                                                    <div class="fs-3 fw-bold text-dark">
                                                        {{ $statusCounts[$child->id] ?? 0 }}
                                                    </div>
                                                    <p class="fs-12 text-muted mb-0">
                                                        {{ $child->name }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                    {{-- Other (if handled separately in controller) --}}
                                    @if(isset($statusCounts['other']) && $statusCounts['other'] > 0)
                                        <div class="col-xxl-2 col-lg-3 col-md-6">
                                            <div
                                                class="card border border-dashed border-gray-5 h-100 hover-shadow transition-all bg-light">
                                                <div class="card-body text-center">
                                                    <div class="mb-2">
                                                        <i class="bi bi-question-circle fs-2 text-secondary"></i>
                                                    </div>
                                                    <div class="fs-3 fw-bold text-dark">
                                                        {{ $statusCounts['other'] }}
                                                    </div>
                                                    <p class="fs-12 text-muted mb-0">Other</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                </div>

                            </div>
                        </div>
                    </div>
                @endif

                <!-- [Goal Progress] start -->

                <div class="col-xxl-4">
                    <div class="card stretch stretch-full">
                        <div class="card-header">
                            <h5 class="card-title">Lead Engagement Progress</h5>
                            <div class="card-header-action">
                                <div class="card-header-btn">
                                    <div data-bs-toggle="tooltip" title="Delete">
                                        <a href="javascript:void(0);" class="avatar-text avatar-xs bg-danger"
                                            data-bs-toggle="remove"></a>
                                    </div>
                                    <div data-bs-toggle="tooltip" title="Refresh">
                                        <a href="javascript:void(0);" class="avatar-text avatar-xs bg-warning"
                                            data-bs-toggle="refresh"></a>
                                    </div>
                                    <div data-bs-toggle="tooltip" title="Maximize/Minimize">
                                        <a href="javascript:void(0);" class="avatar-text avatar-xs bg-success"
                                            data-bs-toggle="expand"></a>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <a href="javascript:void(0);" class="avatar-text avatar-sm" data-bs-toggle="dropdown"
                                        data-bs-offset="25, 25">
                                        <div data-bs-toggle="tooltip" title="Options">
                                            <i class="feather-more-vertical"></i>
                                        </div>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a href="javascript:void(0);" class="dropdown-item"><i
                                                class="feather-at-sign"></i>New</a>
                                        <a href="javascript:void(0);" class="dropdown-item"><i
                                                class="feather-calendar"></i>Event</a>
                                        <a href="javascript:void(0);" class="dropdown-item"><i
                                                class="feather-bell"></i>Snoozed</a>
                                        <a href="javascript:void(0);" class="dropdown-item"><i
                                                class="feather-trash-2"></i>Deleted</a>
                                        <div class="dropdown-divider"></div>
                                        <a href="javascript:void(0);" class="dropdown-item"><i
                                                class="feather-settings"></i>Settings</a>
                                        <a href="javascript:void(0);" class="dropdown-item"><i
                                                class="feather-life-buoy"></i>Tips & Tricks</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body custom-card-action">
                            <div class="row g-4">
                                @php
                                    $engagementItems = [
                                        [
                                            'key' => 'hot',
                                            'label' => 'Hot Leads',
                                            'color' => '#dc3545',
                                            'icon' => 'bi-fire',
                                            'iconClass' => 'text-danger',
                                        ],
                                        [
                                            'key' => 'warm',
                                            'label' => 'Warm Leads',
                                            'color' => '#fd7e14',
                                            'icon' => 'bi-thermometer-half',
                                            'iconClass' => 'text-warning',
                                        ],
                                        [
                                            'key' => 'cold',
                                            'label' => 'Cold Leads',
                                            'color' => '#0d6efd',
                                            'icon' => 'bi-snow',
                                            'iconClass' => 'text-primary',
                                        ],
                                        [
                                            'key' => 'dead',
                                            'label' => 'Dead Leads',
                                            'color' => '#6c757d',
                                            'icon' => 'bi-x-circle',
                                            'iconClass' => 'text-secondary',
                                        ],
                                    ];
                                @endphp

                                @foreach($engagementItems as $item)
                                    @php
                                        $percent = $engagementPercentages[$item['key']] ?? 0;
                                        $count = $engagementCounts[$item['key']] ?? 0;

                                        $knownEngagementTotal =
                                            ($engagementCounts['hot'] ?? 0) +
                                            ($engagementCounts['warm'] ?? 0) +
                                            ($engagementCounts['cold'] ?? 0) +
                                            ($engagementCounts['dead'] ?? 0);

                                        $remainingEngagement = $totalEngagement - $knownEngagementTotal;
                                    @endphp

                                    <div class="col-sm-6">
                                        <div class="px-4 py-3 text-center border border-dashed rounded-3">
                                            <div class="mx-auto mb-1 position-relative" style="width: 100px; height: 100px;">
                                                <!-- SVG Circular Progress -->
                                                <svg width="100" height="100" viewBox="0 0 100 100">
                                                    <!-- Background track -->
                                                    <circle cx="50" cy="50" r="42" fill="none" stroke="#e9ecef"
                                                        stroke-width="5" />
                                                    <!-- Colored progress arc -->
                                                    <circle cx="50" cy="50" r="42" fill="none" stroke="{{ $item['color'] }}"
                                                        stroke-width="5" stroke-linecap="round" stroke-dasharray="263.89"
                                                        stroke-dashoffset="{{ 263.89 * (1 - $percent / 100) }}"
                                                        transform="rotate(-90 50 50)" />
                                                </svg>

                                                <!-- Center percentage + count -->
                                                <div class="position-absolute top-50 start-50 translate-middle text-center">
                                                    <div class="fs-6 fw-bold">{{ $percent }}%</div>
                                                </div>

                                            </div>

                                            <!-- Icon on top of circle -->
                                            <div class="">
                                                <i class="bi {{ $item['icon'] }} fs-6 {{ $item['iconClass'] }}"></i>
                                            </div>
                                            <h2 class="fs-13 tx-spacing-1 mb-1">{{ $item['label'] }}</h2>
                                            <div class="fs-11 text-muted">
                                                {{ $count }} / {{ $totalEngagement }} leads
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                @if($remainingEngagement > 0)
                                    <div class="mb-2 text-muted small">
                                        Engagement status not available in
                                        <strong>{{ $remainingEngagement }}</strong> leads
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="card-footer text-center">
                            <a href="javascript:void(0);" class="btn btn-primary w-100">GENERATE REPORT</a>
                        </div>
                    </div>
                </div>
                <!-- [Goal Progress] end -->
                <!-- [Marketing Campaign] start -->

                <!-- [New Leads by Month - Duralux Style] -->
                <div class="col-xxl-8">
                    <div class="card stretch stretch-full">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">New Leads by Month</h5>

                            @if(auth()->user()->role_id === 1 && count($monthlyChartData) > 1)
                                <select id="monthlyUserSelect" class="form-select form-select-sm w-auto">
                                    @foreach($monthlyChartData as $index => $item)
                                        <option value="{{ $index }}" {{ $index === 0 ? 'selected' : '' }}>
                                            {{ $item['user_name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                        </div>

                        <div class="card-body custom-card-action p-0">
                            <div id="monthly-new-leads-chart" style="min-height: 340px; padding: 15px;"></div>
                        </div>

                        <div class="card-footer d-md-flex flex-wrap p-4 pt-5 border-top border-gray-5">
                            <div class="flex-fill mb-4 mb-md-0 pb-2 pb-md-0 text-center text-md-start">
                                <p class="fs-11 fw-semibold text-uppercase text-primary mb-1">Total in Period</p>
                                <h2 id="chartTotal" class="fs-22 fw-bold mb-0">0</h2>
                            </div>

                            <div class="vr mx-4 text-gray-600 d-none d-md-flex"></div>

                            <div class="flex-fill mb-4 mb-md-0 pb-2 pb-md-0 text-center text-md-start">
                                <p class="fs-11 fw-semibold text-uppercase text-primary mb-1">Current Month</p>
                                <h2 id="currentMonthTotal" class="fs-22 fw-bold mb-0">0</h2>
                                <span id="growthBadge" class="fs-12"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- new source wise chart -->
                <div class="col-xxl-8">
        <div class="card stretch stretch-full">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">New Leads by Source</h5>

                <!-- SOURCE DROPDOWN -->
                @if(count($sourceChartData) > 1)
                    <select id="sourceSelect" class="form-select form-select-sm w-auto">
                        @foreach($sourceChartData as $index => $item)
                            <option value="{{ $index }}" {{ $index === 0 ? 'selected' : '' }}>
                                {{ $item['source_name'] }}
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>

            <div class="card-body p-0">
                <div id="source-chart" style="min-height: 340px; padding: 15px;"></div>
            </div>

            <!-- Footer Stats -->
            <div class="card-footer d-md-flex flex-wrap p-4 pt-5 border-top border-gray-5">
                <div class="flex-fill text-center text-md-start">
                    <p class="fs-11 text-uppercase text-primary mb-1">Total in Period</p>
                    <h2 id="sourceTotal" class="fs-22 fw-bold mb-0">0</h2>
                </div>

                <div class="vr mx-4 d-none d-md-flex"></div>

                <div class="flex-fill text-center text-md-start">
                    <p class="fs-11 text-uppercase text-primary mb-1">Current Month</p>
                    <h2 id="sourceCurrent" class="fs-22 fw-bold mb-0">0</h2>
                    <span id="sourceGrowth" class="fs-12"></span>
                </div>
            </div>
        </div>
    </div>

         <!-- source wise js -->
          <script>
    document.addEventListener('DOMContentLoaded', function () {

        const sourceData = @json($sourceChartData ?? []);
        const categories = @json($chartCategories ?? []);

        if (!sourceData.length) return;

        let chart;
        let currentIndex = 0;

        function renderChart(index) {

            const data = sourceData[index];
            const seriesData = data.series;
            const total = data.total;

            // Growth Calculation
            const last = seriesData[seriesData.length - 1] || 0;
            const prev = seriesData.length >= 2 ? seriesData[seriesData.length - 2] : 0;

            const growth = prev > 0
                ? Math.round(((last - prev) / prev) * 100)
                : (last > 0 ? 100 : 0);

            // Update Footer
            document.getElementById('sourceTotal').textContent = total.toLocaleString();
            document.getElementById('sourceCurrent').textContent = last.toLocaleString();

            const badge = document.getElementById('sourceGrowth');
            badge.textContent = growth !== 0 ? `${growth >= 0 ? '+' : ''}${growth}% vs previous` : '';
            badge.className = `fs-12 ${growth >= 0 ? 'text-success' : 'text-danger'}`;

            // Chart Update / Create
            if (chart) {
                chart.updateOptions({
                    xaxis: { categories: categories }
                }, false, true);

                chart.updateSeries([{
                    name: data.source_name,
                    data: seriesData
                }]);

            } else {
                chart = new ApexCharts(document.querySelector("#source-chart"), {
                    chart: {
                        type: 'bar',
                        height: 340,
                        toolbar: { show: false }
                    },
                    plotOptions: {
                        bar: {
                            columnWidth: '48%',
                            borderRadius: 6
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: val => val > 0 ? val : '',
                        offsetY: -20
                    },
                    series: [{
                        name: data.source_name,
                        data: seriesData
                    }],
                    xaxis: {
                        categories: categories,
                        labels: {
                            rotate: -45
                        }
                    },
                    colors: ['#3454d1'],
                    yaxis: {
                        min: 0
                    },
                    grid: {
                        strokeDashArray: 4
                    },
                    tooltip: {
                        y: {
                            formatter: val => val + " leads"
                        }
                    }
                });

                chart.render();
            }
        }

        // Initial Load
        renderChart(currentIndex);

        // Dropdown Change
        document.getElementById("sourceSelect")?.addEventListener("change", function () {
            currentIndex = parseInt(this.value);
            renderChart(currentIndex);
        });

    });
    </script>

                <!-- JavaScript -->
                <script>
                    document.addEventListener('DOMContentLoaded', function() {

                        const usersData = @json($monthlyChartData ?? []);
                        const categories = @json($chartCategories ?? []);

                        if (!usersData.length) return;

                        let chart;
                        let currentIndex = 0; // starts with "All Users" (index 0)

                        function renderChart(index) {
                            const data = usersData[index];
                            const seriesData = data.series;
                            const total = data.total;

                            // Calculate growth
                            const last = seriesData[seriesData.length - 1] || 0;
                            const prev = seriesData.length >= 2 ? seriesData[seriesData.length - 2] : 0;
                            const growth = prev > 0 ? Math.round(((last - prev) / prev) * 100) : (last > 0 ? 100 : 0);

                            // Update footer
                            document.getElementById('chartTotal').textContent = total.toLocaleString();
                            document.getElementById('currentMonthTotal').textContent = last.toLocaleString();

                            const badge = document.getElementById('growthBadge');
                            badge.textContent = growth !== 0 ? `${growth >= 0 ? '+' : ''}${growth}% vs previous` : '';
                            badge.className = `fs-12 ${growth >= 0 ? 'text-success' : 'text-danger'}`;

                            // Chart update / create
                            if (chart) {
                                chart.updateOptions({
                                    xaxis: {
                                        categories: categories
                                    },
                                }, false, true);

                                chart.updateSeries([{
                                    name: 'New Leads',
                                    data: seriesData
                                }]);
                            } else {
                                chart = new ApexCharts(document.querySelector("#monthly-new-leads-chart"), {
                                    chart: {
                                        type: 'bar',
                                        height: 340,
                                        toolbar: {
                                            show: false
                                        },
                                        fontFamily: 'inherit'
                                    },
                                    plotOptions: {
                                        bar: {
                                            horizontal: false,
                                            columnWidth: '48%',
                                            borderRadius: 6,
                                            endingShape: 'rounded'
                                        }
                                    },
                                    dataLabels: {
                                        enabled: true,
                                        formatter: val => val > 0 ? val : '',
                                        offsetY: -22,
                                        style: {
                                            fontSize: '13px',
                                            fontWeight: 600,
                                            colors: ['#fff']
                                        }
                                    },
                                    series: [{
                                        name: 'New Leads',
                                        data: seriesData
                                    }],
                                    xaxis: {
                                        categories: categories,
                                        labels: {
                                            rotate: -45,
                                            rotateAlways: false,
                                            hideOverlappingLabels: true,
                                            style: {
                                                colors: '#64748b',
                                                fontSize: '12px'
                                            }
                                        },
                                        axisBorder: {
                                            show: false
                                        },
                                        axisTicks: {
                                            show: false
                                        }
                                    },
                                    colors: ['#3454d1'],
                                    yaxis: {
                                        min: 0,
                                        labels: {
                                            style: {
                                                colors: '#64748b',
                                                fontSize: '12px'
                                            }
                                        }
                                    },
                                    grid: {
                                        borderColor: '#e2e8f0',
                                        strokeDashArray: 4,
                                        yaxis: {
                                            lines: {
                                                show: true
                                            }
                                        },
                                        xaxis: {
                                            lines: {
                                                show: false
                                            }
                                        }
                                    },
                                    tooltip: {
                                        y: {
                                            formatter: val => val + " new leads"
                                        }
                                    },
                                    legend: {
                                        show: false
                                    }
                                });

                                chart.render();
                            }
                        }

                        // Initial render (All Users for admin, self for others)
                        renderChart(currentIndex);

                        // Dropdown change handler
                        document.getElementById("monthlyUserSelect")?.addEventListener("change", function() {
                            currentIndex = parseInt(this.value);
                            renderChart(currentIndex);
                        });
                    });
                </script>
                <!-- [Marketing Campaign] end -->

                <!-- [Projects Stats] start -->
                <style>
                    .stage-1 {
                        background-color: #ea4d4d !important;
                    }

                    /* very light blue */
                    .stage-2 {
                        background-color: #3454d1 !important;
                    }

                    .stage-3 {
                        background-color: #17c666 !important;
                    }

                    .stage-4 {
                        background-color: #3dc7be !important;
                    }

                    .stage-5 {
                        background-color: #ffa21d !important;
                    }

                    .stage-6 {
                        background-color: #ea4d4d !important;
                    }

                    .stage-7 {
                        background-color: #3454d1 !important;
                    }

                    .stage-8 {
                        background-color: #17c666 !important;
                    }

                    /* dark blue */

                    .stage-empty {
                        background-color: #e9ecef !important;
                    }
                </style>
                <div class="col-xxl-8">
                    <div class="card stretch stretch-full">
                        <div class="card-header">
                            <h5 class="card-title">Recent Leads Progress</h5>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive project-report-table">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Lead</th>
                                            <th style="width:45%">Stage</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($recentLeadsProgress as $lead)
                                                        @php
                                                            $stageColorClasses = [
                                                                1 => 'stage-1',
                                                                2 => 'stage-2',
                                                                3 => 'stage-3',
                                                                4 => 'stage-4',
                                                                5 => 'stage-5',
                                                                6 => 'stage-6',
                                                                7 => 'stage-7',
                                                                8 => 'stage-8',
                                                            ];

                                                            $currentStageClass = $stageColorClasses[$lead['stage_position']] ?? 'stage-1';
                                                        @endphp
                                                        <tr>
                                                            {{-- Lead Info --}}
                                                            <td>
                                                                <div>
                                                                    <div class="fw-bold text-dark">
                                                                        Lead #{{ $lead['lead_name'] }}
                                                                    </div>
                                                                    @if($lead['user'])
                                                                        <div class="fs-12 text-muted">
                                                                            {{ $lead['user']->name }}
                                                                        </div>
                                                                        <div class="fs-12 text-muted">
                                                                            {{ $lead['user']->contact_no }}
                                                                        </div>
                                                                        <div class="fs-12 text-muted">
                                                                            {{ $lead['user']->email }}
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </td>

                                                            {{-- Segmented Stage Progress --}}
                                                            <td>
                                                                <div class="d-flex align-items-center gap-2 justify-content-center">

                                                                    @for($i = 1; $i <= $lead['total_stages']; $i++)

                                                                                @php
                                                                                    $colorClass = ($i <= $lead['stage_position'])
                                                                                        ? ($stageColorClasses[$i] ?? 'stage-1')
                                                                                        : 'stage-empty';
                                                                                @endphp

                                                                                <div class="wd-20 ht-4 {{ $colorClass }} rounded-pill">
                                                                        </div>

                                                                    @endfor

                                            </div>
                                            </td>

                                            {{-- Status --}}
                                            <td>
                                                <span class="badge {{ $currentStageClass }} text-white px-3 py-2">
                                                    {{ $lead['bucket_name'] }}
                                                </span>
                                            </td>
                                            </tr>
                                        @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        No recent leads found
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                            </table>
                        </div>
                    </div>

                    <a href="{{ route('lead.index') }}"
                        class="card-footer fs-11 fw-bold text-uppercase text-center">
                        View All Leads
                    </a>
                </div>
            </div>
            <!-- [Projects Stats] end -->
            <!-- [Leads Overview] start -->
            <div class="col-xxl-4">
                <div class="card stretch stretch-full">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Sales Performance</h5>

                        @if(count($salesUserPerformance) > 0 && Auth()->user()->role_id === 1)
                            <select id="userSelect" class="form-select form-select-sm w-auto">
                                @foreach($salesUserPerformance as $index => $perf)
                                    <option value="{{ $index }}">
                                        {{ $perf['user']->name }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    <div class="card-body pt-1">

                        @if(count($salesUserPerformance) > 0)

                            <!-- Header -->
                            <div class="mb-3">
                                <strong id="userName" class="fs-6"></strong>
                                <div class="text-muted small" id="convertedText"></div>
                            </div>

                            <!-- Donut -->
                            <div class="d-flex justify-content-center">
                                <div id="salesDonutChart" style="height:240px;width:240px;"></div>
                            </div>

                            <!-- Status List -->
                            <div class="mt-3" id="statusList"></div>

                        @else
                            <div class="text-center py-5 text-muted">
                                No sales representatives found
                            </div>
                        @endif

                    </div>
                </div>
            </div>
            <script>
                document.addEventListener("DOMContentLoaded", function() {

                    let users = @json($salesUserPerformance);

                    if (!users.length) return;

                    let chart;
                    let currentIndex = 0;

                    function renderUser(index) {

                        let perf = users[index];

                        // Header
                        document.getElementById("userName").innerText = perf.user.name;
                        document.getElementById("convertedText").innerText =
                            `Converted: ${perf.converted} / ${perf.total_leads}`;

                        // Status list
                        let statusHtml = '';
                        perf.labels.forEach((label, i) => {
                            statusHtml += `
                                <div class="d-flex justify-content-between align-items-center mb-1 small">
                                    <div class="d-flex align-items-center">
                                        <span class="me-2 rounded-circle"
                                            style="width:10px;height:10px;background:${perf.colors[i]};">
                                        </span>
                                        ${label}
                                    </div>
                                    <strong>${perf.series[i]}</strong>
                                </div>
                            `;
                        });

                        document.getElementById("statusList").innerHTML = statusHtml;

                        // If chart already exists → update
                        if (chart) {

                            chart.updateSeries(perf.series);

                            chart.updateOptions({
                                labels: perf.labels,
                                colors: perf.colors,
                                plotOptions: {
                                    pie: {
                                        donut: {
                                            labels: {
                                                show: true,
                                                total: {
                                                    show: true,
                                                    label: "Conversion",
                                                    formatter: function() {
                                                        return perf.conversion_rate + "%";
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }, true, true);

                        } else {

                            // First time render
                            chart = new ApexCharts(document.querySelector("#salesDonutChart"), {
                                chart: {
                                    type: "donut",
                                    height: 240
                                },
                                series: perf.series,
                                labels: perf.labels,
                                colors: perf.colors,
                                legend: {
                                    show: false
                                },
                                dataLabels: {
                                    enabled: false
                                },
                                stroke: {
                                    width: 4,
                                    colors: ['#ffffff']
                                },
                                tooltip: {
                                    y: {
                                        formatter: function(val) {
                                            return val + " leads";
                                        }
                                    }
                                },
                                plotOptions: {
                                    pie: {
                                        expandOnClick: false, // IMPORTANT (no center change on hover)
                                        donut: {
                                            size: "75%",
                                            labels: {
                                                show: true,
                                                total: {
                                                    show: true,
                                                    label: "Conversion",
                                                    formatter: function() {
                                                        return perf.conversion_rate + "%";
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            });

                            chart.render();
                        }
                    }

                    // Initial render
                    renderUser(currentIndex);

                    // On dropdown change
                    document.getElementById("userSelect")?.addEventListener("change", function() {
                        currentIndex = this.value;
                        renderUser(currentIndex);
                    });

                });
            </script>
            <!-- [Leads Overview] end -->

            <!-- [Project Remainders] start -->
            <div class="col-xxl-12">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <div>
                            <h5 class="mb-0 fw-semibold">Recent Lead Activity</h5>
                            <small class="text-muted">Sorted by latest comment update</small>
                        </div>
                        <div class="card-header-action">
                            <div class="card-header-btn">
                                <div data-bs-toggle="tooltip" title="Delete">
                                    <a href="javascript:void(0);" class="avatar-text avatar-xs bg-danger"
                                        data-bs-toggle="remove"> </a>
                                </div>
                                <div data-bs-toggle="tooltip" title="Refresh">
                                    <a href="javascript:void(0);" class="avatar-text avatar-xs bg-warning"
                                        data-bs-toggle="refresh"> </a>
                                </div>
                                <div data-bs-toggle="tooltip" title="Maximize/Minimize">
                                    <a href="javascript:void(0);" class="avatar-text avatar-xs bg-success"
                                        data-bs-toggle="expand"> </a>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="card-body custom-card-action p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Lead Info</th>
                                        <th>Status</th>
                                        <th>Last Comment</th>
                                        <th>Updated By</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentLeads as $lead)
                                        <tr>

                                            <!-- Lead Info -->
                                            <td>
                                                <div>
                                                    <h6 class="mb-0 fw-semibold">
                                                        Lead #{{ $lead->id }}
                                                    </h6>

                                                    <small class="text-muted d-block">
                                                        {{ $lead->user->name ?? 'N/A' }}
                                                    </small>

                                                    <small class="text-muted d-block">
                                                        {{ $lead->user->contact_no ?? 'N/A' }}
                                                    </small>

                                                    <small class="text-muted">
                                                        {{ $lead->user->email ?? '' }}
                                                    </small>
                                                </div>
                                            </td>

                                            <!-- Status -->
                                            <td>
                                                <span class="badge bg-light text-dark border px-3 py-2">
                                                    {{ $lead->lead_status ?? 'N/A' }}
                                                </span>
                                            </td>

                                            <!-- Last Comment -->
                                            <td class="last-comment-col">
                                                @php
                                                    $message = $lead->latestMessage->message ?? '';
                                                    $isLong = strlen($message) > 120;
                                                @endphp

                                                <div>
                                                    <p class="mb-1 fw-medium text-dark comment-text"
                                                        id="comment-{{ $lead->id }}">
                                                        {{ $message }}
                                                    </p>

                                                    @if($isLong)
                                                        <span class="text-primary read-more-btn"
                                                            onclick="toggleComment({{ $lead->id }})" id="btn-{{ $lead->id }}">
                                                            Read More
                                                        </span>
                                                    @endif

                                                    <div>
                                                        <small class="text-muted">
                                                            {{ optional($lead->latestMessage->created_at)->diffForHumans() }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Updated By -->
                                            <td>
                                                <div class="d-flex align-items-center gap-2">

                                                    <div class="avatar-sm bg-light rounded-circle text-center">
                                                        <span class="fw-semibold">
                                                            {{ strtoupper(substr($lead->latestMessage->user->name ?? 'S', 0, 1)) }}
                                                        </span>
                                                    </div>

                                                    <div>
                                                        <div class="fw-semibold">
                                                            {{ $lead->latestMessage->user->name ?? 'System' }}
                                                        </div>

                                                        <small class="text-muted">
                                                            Owner: {{ $lead->owner->name ?? 'Unassigned' }}
                                                        </small>
                                                    </div>

                                                </div>
                                            </td>

                                            <!-- Action -->
                                            <td class="text-end">
                                                <a href="{{ route('lead.index') }}" class="btn btn-sm btn-outline-primary">
                                                    View
                                                </a>
                                            </td>

                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                No recent activity found
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer">
                            <div class="p-3">

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [Project Remainders] end -->
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


        </div>
        </div>
@endsection