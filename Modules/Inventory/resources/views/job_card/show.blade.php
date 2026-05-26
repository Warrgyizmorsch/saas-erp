@extends('shared::layouts.app')
@section('content')

    {{-- PAGE HEADER --}}
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">job card Details</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('job_card.add') }}">job card </a></li>
            </ul>
        </div>
    </div>

    <div class="main-content container-lg">
        <div class="row">

            {{-- MAIN CARD --}}
            <div class="col-lg-12">
                <div class="card stretch stretch-full position-relative">
                    <div class="card-body pb-3">

                        {{-- TOP HEADER --}}
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="mb-1">Job Card Detail</h5>
                                <div class="fs-13 text-muted">
                                    Job Card Number:
                                    <span class="fw-semibold">
                                        {{$jobCard->job_card_no}}
                                    </span>
                                </div>
                            </div>

                            {{-- RIGHT ACTIONS --}}
                            <div class="d-flex align-items-center gap-2">

                                {{-- STATUS BADGE --}}
                                <div class="me-2">
                                    @switch($jobCard->status)
                                    @case('PENDING')
                                    <span class="badge bg-soft-warning text-warning">PENDING</span>
                                    @break
                                    @case('COMPLETED')
                                    <span class="badge bg-soft-success text-success">COMPLETE</span>
                                    @break
                                    @default
                                    <span class="badge bg-soft-secondary text-secondary">Unknown</span>
                                    @endswitch
                                </div>

                                {{-- BACK --}}
                                <a href="{{ route('job_card.view') }}" class="d-flex">
                                    <div class="avatar-text avatar-md bg-primary text-white"
                                        data-bs-toggle="tooltip" title="Back to List">
                                        <i class="feather feather-arrow-left"></i>
                                    </div>
                                </a>

                            </div>
                        </div>



                        {{-- SUMMARY --}}
                        <div class="row g-3 mb-3 mt-5">
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="fw-semibold mb-2">Requested By & Summary</h6>
                                    <div class="fs-13 text-muted">
                                        <div><strong>Job Card no </strong> {{ $jobCard->job_card_no ?? 'N/A' }}</div>
                                        <div><strong>Priority</strong> {{ $jobCard->priority ?? 'N/A' }}</div>
                                        <div><strong>Total Qunatity</strong> {{ $jobCard->total_qty ?? 'N/A' }}</div>
                                        <div><strong>Pending Quantity</strong> {{ $jobCard->pending_qty ?? 'N/A' }}</div>
                                        <div><strong>Total Received Quantity</strong> {{ $jobCard->total_received_qty ?? 'N/A' }}</div>
                                        <div><strong>Completion Date</strong> {{ $jobCard->completion_date ?? 'N/A' }}</div>
                                        <div><strong>Completed At</strong> {{ $jobCard->completed_at ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>

                            @if($jobCard->vendor)
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="fw-semibold mb-2">Vendor Details</h6>
                                    <div class="fs-13 text-muted">
                                        <div><strong>Vendor name:</strong> {{ $jobCard->vendor?->name ?? 'N/A' }}</div>
                                        <div><strong>Vendor Mobile No:</strong> {{ $jobCard->vendor?->mobile_no ?? 'N/A' }}</div>
                                        <div><strong>Email:</strong> {{ $jobCard->vendor?->email ?? 'N/A' }}</div>
                                        <div><strong>City:</strong> {{ $jobCard->vendor?->city?? 'N/A' }}</div>
                                        <div><strong>Vendor Address:</strong> {{ $jobCard->vendor?->address ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="fw-semibold mb-2">Employee Details</h6>
                                    <div class="fs-13 text-muted">
                                        <div><strong>Employee name:</strong> {{ $jobCard->employee?->name ?? 'N/A' }}</div>
                                        <div><strong>Employee Mobile No:</strong> {{ $jobCard->employee?->mobile ?? 'N/A' }}</div>
                                        <div><strong>Email:</strong> {{ $jobCard->employee?->email ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>
                            @endif

                        </div>



                        {{-- ITEMS TABLE --}}
                        <h6 class="fw-semibold mt-3 mb-2">Items</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                    <tr>
                                         <th>sr</th>
                                        <th style="min-width:280px;">Inventory Item</th>
                                                <th style="min-width:180px;">Quantity</th>
                                                <th style="min-width:180px;">pending Qty</th>
                                                <th style="min-width:180px;">Received Qty</th>
                                                <th style="min-width:200px;">Description</th>
                                                <th>Status</th>
                                    </tr>

                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($jobCard->rows as $index => $p)
                                    <tr>

                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $p->item->name ?? 'N/A' }}</td>
                                        <td class="text-muted">
                                            {{ $p->qty ?? 'N/A' }}
                                        </td>
                                        <td>
                                            {{$p->item_pending_qty ?? 'N/A'}}
                                        </td>
                                        <td class="text-end">
                                            {{ $p->received_qty ?? 'N/A' }}
                                        </td>
                                        <td class="text-end">
                                            {{ $p->description ?? 'N/A' }}
                                        </td>
                                        <td class="text-end">
                                            {{ $p->status ?? 'N/A' }}
                                        </td>
                                       
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No items found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection