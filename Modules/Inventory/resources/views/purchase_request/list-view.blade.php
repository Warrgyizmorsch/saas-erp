    @extends('shared::layouts.app')
@section('content')

        @php
        $isFilterActive = request()->filled('rs_code')
        || request()->filled('user')
        || request()->filled('status')
        || request()->filled('project');
        @endphp

        {{-- PAGE HEADER --}}
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">Purchase Request Slip</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/dashboard">Home</a>
                    </li>
                    <li class="breadcrumb-item active">Purchase Request Slip</li>
                </ul>
            </div>

            <div class="page-header-right ms-auto">
                <div class="page-header-right-items">

                    {{-- Mobile back (when right panel open) --}}
                    <div class="d-flex d-md-none">
                        <a href="javascript:void(0)" class="page-header-right-close-toggle">
                            <i class="feather-arrow-left me-2"></i>
                            <span>Back</span>
                        </a>
                    </div>

                    {{-- Right side buttons --}}
                    <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">

                        <button id="filterBtn" class="btn btn-icon btn-light-brand" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                            <i class="feather-filter"></i>
                        </button>

                        {{-- Create Request Slip --}}
                        <a href="{{ route('purchase_request.create') }}" class="btn btn-primary">
                            <i class="feather-plus me-2"></i>
                            <span>Create Purchase</span>
                        </a>

                    </div>

                </div>

                {{-- Mobile open toggle --}}
                <div class="d-md-none d-flex align-items-center">
                    <a href="javascript:void(0)" class="page-header-right-open-toggle">
                        <i class="feather-align-right fs-20"></i>
                    </a>
                </div>
            </div>
        </div>


        {{-- MAIN CONTENT --}}
        <div class="main-content">

            {{-- filterCollapse --}}
            <div class="collapse mb-3" id="filterCollapse">
                <div class="card border shadow-sm">
                    <div class="card-body">
                        <form method="GET" action="{{ route('purchase_request.list-view') }}">
                            <div class="row g-3 align-items-end">

                                <div class="col-md-3">
                                    <label class="form-label">From Date</label>
                                    <input type="date" name="from_date" class="form-control"
                                        value="{{ request('from_date') }}">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">To Date</label>
                                    <input type="date" name="to_date" class="form-control"
                                        value="{{ request('to_date') }}">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Pr No</label>
                                    <input type="text" name="pr_no" class="form-control"
                                        value="{{ request('pr_no') }}" placeholder="Search by Pr No ">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Priority</label>
                                    <select name="priority" class="form-control" data-select2-selector="status">
                                        <option value="">Select Priority</option>
                                        <option value="LOW" {{ request('priority') == 'LOW' ? 'selected' : '' }}>LOW</option>
                                        <option value="NORMAL" {{ request('priority') == 'NORMAL' ? 'selected' : '' }}>NORMAL</option>
                                        <option value="HIGH" {{ request('priority') == 'HIGH' ? 'selected' : '' }}>HIGH</option>
                                        <option value="URGENT" {{ request('priority') == 'URGENT' ? 'selected' : '' }}>URGENT</option>
                                    </select>
                                </div>

                                <div class="col-lg-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-control" data-select2-selector="status">
                                        <option value="">-- Select --</option>
                                        <option value="DRAFT" {{ request('priority') == 'DRAFT' ? 'selected' : '' }}>DRAFT</option>
                                        <option value="SUBMITTED" {{ request('priority') == 'SUBMITTED' ? 'selected' : '' }}>SUBMITTED</option>
                                        <option value="APPROVED" {{ request('priority') == 'APPROVED' ? 'selected' : '' }}>APPROVED</option>
                                        <option value="REJECTED" {{ request('priority') == 'REJECTED' ? 'selected' : '' }}>REJECTED</option>
                                        <option value="HOLD" {{ request('priority') == 'HOLD' ? 'selected' : '' }}>HOLD</option>
                                        <option value="ORDERED" {{ request('priority') == 'ORDERED' ? 'selected' : '' }}>ORDERED</option>
                                        <option value="PARTIALLY_ORDERED" {{ request('priority') == 'PARTIALLY_ORDERED' ? 'selected' : '' }}>PARTIALLY ORDERED</option>

                                    </select>
                                </div>


                                <!-- Buttons -->
                                <div class="col-md-4 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary w-50">
                                        <i class="feather-search"></i> Search
                                    </button>

                                    <a href="{{ route('purchase_request.list-view') }}" class="btn btn-light w-50">
                                        Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card stretch stretch-full">
                        <div class="card-body">

                            <div class="table-responsive">
                                <table class="table table-striped" id="requestSlipList">
                                    <thead>
                                        <tr>
                                            <th>Sr</th>
                                            <th>Pr No </th>
                                            <th>Request Date</th>
                                            <th>Requested By</th>
                                            <th>total_qty</th>
                                            <th>Priority</th>
                                            <th>Status</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($purchaseRequests as $index => $pr)
                                        <tr class="single-item">

                                            <td>{{ $index+1 }}</td>
                                            <td>{{ $pr->pr_no }}</td>

                                            <td>
                                                <span class="text-truncate-1-line">
                                                    {{ $pr->request_date ?? 'N/A' }}
                                                </span>
                                            </td>

                                            <td>{{ $pr->creator?->name ?? 'N/A' }}</td>

                                            <td>{{ $pr->total_qty ?? 'N/A' }}</td>

                                            <td>{{ $pr->priority ?? 'N/A' }}</td>

                                            <td>
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
                                                  @case('PARTIALLY_ORDERED')
                                                <span class="badge bg-soft-warning text-warning"> Partially_ordered</span>
                                                @break
                                                @break
                                                  @case('ORDERED')
                                                <span class="badge bg-soft-success text-success">Ordered</span>
                                                @break
                                                @case('HOLD')
                                                <span class="badge bg-soft-danger text-danger">Hold</span>
                                                @break
                                                @default
                                                <span class="badge bg-soft-secondary text-secondary">Unknown</span>
                                                @endswitch
                                            </td>

                                            {{-- Actions --}}
                                            <td class="d-flex gap-2 justify-content-center">

                                                {{-- Edit --}}
                                              
                                                @if($pr->status == 'DRAFT')
                                                <a href="{{ route('purchase_request.list-edit',$pr->id) }}"
                                                    class="btn btn-light btn-sm" title="Edit Request Slip">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                 @endif

                                                {{-- View --}}
                                                <a href="{{ route('purchase_request.show-detail', $pr->id) }}"
                                                    class="btn btn-light btn-sm" title="View Details">
                                                    <i class="fa fa-eye"></i>
                                                </a>


                                                {{-- ✅ Delete --}}
                                                 @if($pr->status == 'DRAFT')
                                                <button type="button"
                                                    class="btn btn-light btn-sm"
                                                    title="Delete Request Slip"
                                                    data-bs-toggle="offcanvas"
                                                    data-bs-target="#deleteInventory{{ $pr->id }}">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                                 @endif
                                            </td>
                                        </tr>

                                        {{-- ✅ OFFCANVAS: STATUS UPDATE --}}
                                        <div class="offcanvas offcanvas-end"
                                            tabindex="-1"
                                            id="statusUpdate{{ $pr->id }}">
                                            <form method="POST" action="{{ route('purchase_request.status-update', $pr->id) }}">
                                                @csrf

                                                <div class="offcanvas-header ht-80 px-4 border-bottom border-gray-5">
                                                    <div>
                                                        <h2 class="fs-16 fw-bold mb-0">Update Purchase Slip Status</h2>
                                                        <small class="fs-12 text-muted">Choose Approved / Rejected and submit.</small>
                                                    </div>
                                                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                                                </div>

                                                <div class="offcanvas-body px-4">

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">pr no</label>
                                                        <input type="text" class="form-control" value="#{{ $pr->pr_no }}" disabled>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">total qty</label>
                                                        <input type="text" class="form-control" value="{{ $pr->total_qty ?? 'N/A' }}" disabled>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Select Status</label>
                                                        <select name="status" class="form-control" data-select2-selector="status" required>
                                                            <option value="">-- Select --</option>
                                                            <option value="APPROVED">Approved</option>
                                                            <option value="REJECTED">Rejected</option>
                                                            <option value="HOLD">Hold</option>
                                                        </select>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Remarks (optional)</label>
                                                        <textarea name="remarks" rows="4" class="form-control"
                                                            placeholder="Reason / note..."></textarea>
                                                    </div>

                                                </div>

                                                <div class="px-4 gap-2 d-flex align-items-center justify-content-between ht-80 border-top border-gray-2">
                                                    <button type="submit" class="btn btn-primary w-50">Submit</button>
                                                    <button type="button" class="btn btn-light w-50" data-bs-dismiss="offcanvas">
                                                        Cancel
                                                    </button>
                                                </div>
                                            </form>
                                        </div>

                                        {{-- OFFCANVAS: DELETE --}}
                                        <div class="offcanvas offcanvas-end" tabindex="-1" id="deleteInventory{{ $pr->id }}">
                                            <form method="POST" action="{{ route('purchase_request.destroy', $pr->id) }}">
                                                @csrf
                                                @method('DELETE')

                                                <div class="offcanvas-header ht-80 px-4 border-bottom border-gray-5">
                                                    <h2 class="fs-16 fw-bold mb-0">Delete Request Slip</h2>
                                                    <button type="button" class="btn-close btn-close-gray ms-auto" data-bs-dismiss="offcanvas"></button>
                                                </div>

                                                <div class="offcanvas-body">
                                                    <p class="fs-15">
                                                        Are you sure you want to delete
                                                        <strong>#{{ $pr->pr_no }}</strong>?
                                                    </p>
                                                </div>

                                                <div class="px-4 gap-2 d-flex align-items-center justify-content-between ht-80 border-top border-gray-2">
                                                    <button type="submit" class="btn btn-primary w-50">Yes, Delete</button>
                                                    <button type="button" class="btn btn-danger w-50" data-bs-dismiss="offcanvas">
                                                        Cancel
                                                    </button>
                                                </div>
                                            </form>
                                        </div>



                                        @empty
                                        <tr>
                                            <td colspan="12" class="text-center text-muted">
                                                No Request Slips found
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>

                                {{-- Pagination --}}
                                <div class="mt-3">
                                    {{ $purchaseRequests->links('pagination::bootstrap-5') }}
                                </div>

                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

    @endsection