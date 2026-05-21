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
                    <h5 class="m-b-10">Lead Performance</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item">Application</li>
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
                <form method="GET" action="{{ route('lead.application') }}" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Applicant Name</label>
                        <input type="text" name="applicant_name" value="{{ request('applicant_name') }}"
                            class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Mobile No</label>
                        <input type="text" name="mobile_no" value="{{ request('mobile_no') }}" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control" data-select2-selector="status">
                            <option value="">All Status</option>
                            <option value="23">Application Process</option>
                            <option value="30">Offer Stage</option>
                            <option value="37">Visa Process</option>
                            <option value="48">Converted</option>
                        </select>
                    </div>
                    <div class="col-12 d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="feather-filter me-1"></i> Filter
                        </button>

                        <a href="{{ route('lead.application') }}" class="btn btn-danger">
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
                                            <th class="text-start">Lead ID</th>
                                            <th>Applicant Name</th>
                                            <th>Email</th>
                                            <th>Mobile No</th>
                                            <th>Creation Date</th>
                                            <th>Modification Date</th>
                                            <th>Status</th>
                                            <th>Sub Status</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($applicationData as $row)
                                            <tr>
                                                <td class="text-start">
                                                    <strong>{{ $row->id }}</strong>
                                                </td>

                                                <td>
                                                    <strong>{{ $row->user->name ?? 'N/A' }}</strong>
                                                </td>

                                                <td>
                                                    <strong>{{ $row->user->email ?? 'N/A' }}</strong>
                                                </td>
                                                <td>{{ $row->user->contact_no ?? 'N/A' }}</td>
                                                <td>{{ \Carbon\Carbon::parse($row->created_at)->format('M d, Y h:i A') }}</td>
                                                <td>{{ \Carbon\Carbon::parse($row->updated_at)->format('M d, Y h:i A') }}</td>
                                                <td>{{ $row->bucket->name ?? 'N/A' }}</td>
                                                <td>{{ $row->lead_status }}</td>
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
                                {{ $applicationData->withQueryString()->links('pagination::bootstrap-4') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection