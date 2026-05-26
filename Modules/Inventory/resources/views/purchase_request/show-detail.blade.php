@extends('shared::layouts.app')
@section('content')

    {{-- PAGE HEADER --}}
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">purchase Request Slip Details</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('request-slip.index') }}">Purchase Request Slip</a></li>
                <li class="breadcrumb-item active">View</li>
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
                                <h5 class="mb-1">Purchase Request Slip</h5>
                                <div class="fs-13 text-muted">
                                    PR Number:
                                    <span class="fw-semibold">
                                        {{$pr->pr_no}}
                                    </span>
                                    @if($pr->name)
                                    <span class="text-muted"> — {{ $pr->name }}</span>
                                    @endif
                                </div>
                            </div>

                            {{-- RIGHT ACTIONS --}}
                            <div class="d-flex align-items-center gap-2">

                                {{-- STATUS BADGE --}}
                                <div class="me-2">
                                    @switch($pr->status)
                                    @case('DRAFT')
                                    <span class="badge bg-soft-warning text-warning">Draft</span>
                                    @break
                                    @case('REJECTED')
                                    <span class="badge bg-soft-danger text-danger">Rejected</span>
                                    @break
                                    @case('APPROVED')
                                    <span class="badge bg-soft-success text-success">Approved</span>
                                    @break
                                    @case('SUBMITTED')
                                    <span class="badge bg-soft-info text-info"> submited</span>
                                    @break
                                    @case('HOLD')
                                    <span class="badge bg-soft-danger text-danger">Hold</span>
                                    @break
                                    @default
                                    <span class="badge bg-soft-secondary text-secondary">Unknown</span>
                                    @endswitch
                                </div>

                                {{-- BACK --}}
                                <a href="{{ route('request-slip.index') }}" class="d-flex">
                                    <div class="avatar-text avatar-md bg-primary text-white"
                                        data-bs-toggle="tooltip" title="Back to List">
                                        <i class="feather feather-arrow-left"></i>
                                    </div>
                                </a>

                                {{-- EDIT BUTTON --}}


                                {{-- ADMIN + HOD ACTIONS WHEN PENDING --}}

                            </div>
                        </div>



                        {{-- SUMMARY --}}
                        <div class="row g-3 mb-3 mt-5">

                            <div class="col-md-12">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="fw-semibold mb-2">Requested By & Summary</h6>
                                    <div class="fs-13 text-muted">
                                        <div><strong>Requested By:</strong> {{ $pr->creator?->name ?? 'N/A' }}</div>
                                        <div><strong>Total Requested Qty:</strong> {{ $pr->total_qty }}</div>
                                        <div><strong>Request date:</strong> {{ $pr->request_date }}</div>
                                        <div><strong>Priority:</strong> {{ $pr->priority ?? 'N/A' }}</div>
                                        <div><strong>Remarks:</strong> {{ $pr->remarks ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>



                        {{-- ITEMS TABLE --}}
                        <h6 class="fw-semibold mt-3 mb-2">Items</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sr No</th>
                                        <th>Item</th>
                                        <th>Description</th>
                                        <th class="text-end">Requested Qty</th>
                                        <th class="text-end">approved_qty</th>
                                        <th class="text-end">order_qty</th>
                                        <th class="text-center">uom</th>
                                        <th class="text-center">reuired_date</th>
                                        <th class="text-center">status</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($items as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $item->inventory->name ?? 'Inventory #'.$item->item_id }}</td>
                                        <td class="text-muted">
                                            {{ $item->description ?? 'N/A' }}
                                        </td>
                                        <td class="text-end">
                                            {{ $item->requested_qty }}
                                        </td>
                                        <td class="text-end">
                                            {{ $item->approved_qty ?? 0 }}
                                        </td>
                                        {{-- Ordered Qty --}}
                                        <td class="text-end">
                                            {{ $item->ordered_qty ?? 0 }}
                                        </td>

                                        {{-- UOM --}}
                                        <td class="text-center">
                                            {{ $item->uom ?? '-' }}
                                        </td>

                                        {{-- Required Date --}}
                                        <td class="text-center">
                                            {{ $item->required_date ? \Carbon\Carbon::parse($item->required_date)->format('d-m-Y')  : '-' }}
                                        </td>

                                        {{-- Status --}}
                                        <td class="text-center">
                                            <span class="badge bg-secondary">
                                                {{ $item->status }}
                                            </span>
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