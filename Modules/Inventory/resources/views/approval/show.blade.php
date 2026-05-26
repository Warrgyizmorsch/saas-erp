@extends('shared::layouts.app')
@section('content')

    {{-- PAGE HEADER --}}
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Request Slip Details</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('request-slip.index') }}">Request Slip</a></li>
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
                                <h5 class="mb-1">Request Slip</h5>
                                <div class="fs-13 text-muted">
                                    RS Code:
                                    <span class="fw-semibold">
                                        #{{ str_pad($rs->rs_id, 3, '0', STR_PAD_LEFT) }}
                                    </span>
                                    @if($rs->name)
                                    <span class="text-muted"> — {{ $rs->name }}</span>
                                    @endif
                                </div>
                            </div>

                            {{-- RIGHT ACTIONS --}}
                            <div class="d-flex align-items-center gap-2">

                                {{-- STATUS BADGE --}}
                                <div class="me-2">
                                    @switch($rs->status)
                                    @case('Pending')
                                        <span class="badge bg-soft-warning text-warning">Pending HOD</span>
                                        @break

                                    @case('rejected')
                                        <span class="badge bg-soft-danger text-danger">Rejected by HOD</span>
                                        @break

                                    @case('approved')
                                        <span class="badge bg-soft-success text-success">Completed</span>
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
                                @if(in_array(auth()->user()->role_id, [1, 3]))
                                <a href="{{ route('request-slip.edit', $rs->id) }}" class="d-flex">
                                    <div class="avatar-text avatar-md bg-success text-white"
                                        data-bs-toggle="tooltip" title="Edit Request Slip">
                                        <i class="feather feather-edit"></i>
                                    </div>
                                </a>
                                @endif

                                {{-- ADMIN + HOD ACTIONS WHEN PENDING --}}
                                @if(in_array(auth()->user()->role_id, [1, 4]) && $rs->status == 'Pending')

                                {{-- Approve --}}
                                <form action="{{ route('request-slip.approve', $rs->id) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="btn btn-success btn-sm d-flex justify-content-center align-items-center"
                                        data-bs-toggle="tooltip" title="Approve & Send to Store">
                                        <i class="feather feather-check"></i>
                                    </button>
                                </form>

                                {{-- Reject --}}
                                <button type="button"
                                    class="btn btn-danger btn-sm d-flex justify-content-center align-items-center"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#rejectSlide">
                                    <i class="feather feather-x"></i>
                                </button>

                                @endif
                            </div>
                        </div>

                        {{-- REJECT SLIDE FORM (TOP) --}}
                        @if(in_array(auth()->user()->role_id, [1, 4]) && $rs->status == 'Pending')
                        <div id="rejectSlide" class="collapse position-absolute top-0 start-0 w-100 p-3" style="z-index: 1050;">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="mb-3 text-danger"><i class="feather-x me-1"></i> Reject Request Slip</h5>

                                    <form action="{{ route('request-slip.reject', $rs->id) }}" method="POST">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Remarks <span class="text-danger">*</span></label>
                                            <textarea name="remarks" class="form-control" rows="3" required placeholder="Enter reason for rejection"></textarea>
                                        </div>

                                        <div class="d-flex justify-content-end gap-2">
                                            <button type="button" class="btn btn-light" data-bs-toggle="collapse" data-bs-target="#rejectSlide">Cancel</button>
                                            <button type="submit" class="btn btn-danger d-flex align-items-center gap-1">
                                                <i class="feather feather-x"></i> Confirm Reject
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- SUMMARY --}}
                        <div class="row g-3 mb-3 mt-5">
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="fw-semibold mb-2">Request Slip</h6>
                                    <div class="fs-13 text-muted">
                                        <div><strong>Project:</strong> {{ $rs->project->name ?? 'Manual / Other' }}</div>
                                        <div><strong>Created At:</strong> {{ $rs->created_at?->format('d-m-Y H:i') }}</div>
                                        <div><strong>Last Updated:</strong> {{ $rs->updated_at?->format('d-m-Y H:i') }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="fw-semibold mb-2">Requested By & Summary</h6>
                                    <div class="fs-13 text-muted">
                                        <div><strong>Requested By:</strong> {{ $rs->creator?->name ?? 'N/A' }}</div>
                                        <div><strong>Total Items:</strong> {{ $rs->items->count() }}</div>
                                        <div><strong>Requested Qty:</strong> {{ $rs->items->sum('quantity') }}</div>
                                        <div><strong>Issued Qty:</strong> {{ $rs->items->sum('issued_quantity') ?? 0 }}</div>
                                        <div><strong>Pending Qty:</strong> {{ $rs->items->sum('pending_quantity') ?? 0 }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- DESCRIPTION --}}
                        @if($rs->description)
                        <div class="mb-3">
                            <h6 class="fw-semibold mb-1">Description</h6>
                            <p class="mb-0 text-muted">{{ $rs->description }}</p>
                        </div>
                        @endif

                        {{-- HOD REMARKS --}}
                        @if($rs->remarks && $rs->status == 'rejected_hod')
                        <div class="mb-3">
                            <h6 class="fw-semibold text-danger mb-1">HOD Remarks</h6>
                            <p class="mb-0 text-danger">{{ $rs->remarks }}</p>
                        </div>
                        @endif

                        {{-- ITEMS TABLE --}}
                        <h6 class="fw-semibold mt-3 mb-2">Items</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Item</th>
                                        <th>Description</th>
                                        <th class="text-end">Requested Qty</th>
                                        <th class="text-end">Issued Qty</th>
                                        <th class="text-end">Pending Qty</th>
                                        <th class="text-center">Pending Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($rs->items as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $item->inventory->name ?? 'Inventory #'.$item->inventory_id }}</td>
                                        <td class="text-muted">{{ $item->comment->description ?? 'N/A' }}</td>
                                        <td class="text-end">{{ $item->quantity }}</td>
                                        <td class="text-end">{{ $item->issued_quantity ?? 0 }}</td>
                                        <td class="text-end">{{ $item->pending_quantity ?? 0 }}</td>
                                        <td class="text-center">
                                            {{ $item->pending_date ? \Carbon\Carbon::parse($item->pending_date)->format('d-m-Y') : '-' }}
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