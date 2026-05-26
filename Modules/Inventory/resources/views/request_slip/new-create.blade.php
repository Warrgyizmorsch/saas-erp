@extends('shared::layouts.app')
@section('content')

    @php
    $isEdit = isset($requestSlip);
    $pageTitle = $isEdit ? 'Edit Request Slip' : 'Create Request Slip';
    $subTitle = $isEdit ? 'Update Request Slip' : 'Add Request Slip';
    @endphp

    {{-- Error & Success --}}
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Error!</strong> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Success!</strong> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-1">{{ $pageTitle }}</h5>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('request-slip.index') }}">Request Slip</a></li>
                <li class="breadcrumb-item active">{{ $pageTitle }}</li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <div class="card shadow-sm border-0">
            <div class="card-body">

                <h5 class="mb-4 fw-semibold text-primary">{{ $subTitle }}</h5>

                <form id="rsForm"
                    action="{{ $isEdit ? route('request-slip.update', $requestSlip->id) : route('request-slip.store') }}"
                    method="POST">

                    @csrf
                    @if($isEdit) @method('PUT') @endif

                    <div class="row g-3 g-md-4">
                        <div class="col-lg-3 col-md-4">
                            <label class="form-label fw-medium">Requisition Slip No</label>
                            <input type="text" class="form-control bg-light" 
                                value="{{ old('requisition_slip_no', $isEdit ? $requestSlip->requisition_slip_no : $nextSlipNo) }}" readonly>
                        </div>

                        <div class="col-lg-3 col-md-4">
                            <label class="form-label fw-medium">Transaction Date <span class="text-danger">*</span></label>
                            <input type="date" name="transaction_date" class="form-control" 
                                value="{{ old('transaction_date', $isEdit ? $requestSlip->transaction_date : date('Y-m-d')) }}" readonly>
                        </div>

                        <div class="col-lg-6 col-md-4">
                            <label class="form-label fw-medium">Select Project <span class="text-danger">*</span></label>
                            <select id="project_id" name="project_id" class="form-select">
                                <option value="">-- Select Project --</option>
                                @foreach ($projects as $p)
                                <option value="{{ $p->id }}" 
                                    {{ old('project_id', $isEdit ? $requestSlip->project_id : '') == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('project_id')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <input type="hidden" name="employee_id" value="{{ Auth::user()->id }}">
                        <input type="hidden" name="department_id" value="{{ Auth::user()->department_id }}">

                        <div class="col-12">
                            <label class="form-label fw-medium">Comment 
                                <i class="feather-plus-circle text-primary comment-toggle d-md-none" style="cursor:pointer;"></i>
                            </label>
                            <textarea name="comment" class="form-control comment-box" rows="2" 
                                placeholder="Enter comment here...">{{ old('comment', $isEdit ? $requestSlip->comment : '') }}</textarea>
                        </div>
                    </div>

                    <!-- Items Grid Section -->
                    <div class="mt-5">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 fw-semibold">Items</h5>
                            <button type="button" id="add_inventory_btn" class="btn btn-primary btn-sm px-3">
                                <i class="feather-plus"></i> Add Item
                            </button>
                        </div>

                        <!-- Grid Container -->
                        <div id="items-grid" class="row g-3"></div>
                    </div>

                    <!-- Submit -->
                    <div class="sticky-save-bar mt-4">
                        <button type="submit" class="btn btn-primary px-5 py-2 mobile-full-btn">
                            {{ $isEdit ? 'Update Request Slip' : 'Save Request Slip' }}
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <style>
        .card {
            border-radius: 16px;
            box-shadow: 0 4px 25px rgba(0,0,0,0.06);
        }

        .item-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 18px;
            transition: all 0.3s ease;
        }

        .item-card:hover {
            border-color: #6366f1;
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.12);
        }

        .form-control, .form-select {
            border-radius: 10px;
        }

        .sticky-save-bar {
            position: sticky;
            bottom: 0;
            background: white;
            padding: 18px 0;
            border-top: 1px solid #eee;
            z-index: 1000;
            margin: 0 -20px;
            padding-left: 20px;
            padding-right: 20px;
        }

        .remove-row {
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .comment-box { display: none; }

        @media (min-width: 768px) {
            .comment-box { display: block; }
        }

        .mobile-full-btn {
            width: 100%;
        }
    </style>

    @section('scripts')
    <script>
        let machinesOptionsHtml = `<option value="">-- Select Machine --</option>`;
        let machinesLoadedForProject = null;
        window.RS_ID = {{ $isEdit ? (int)$requestSlip->id : 0 }};

        // ================== YOUR EXISTING JS (Only small changes) ==================
        // ... Keep all your existing functions (fetchMachines, onProjectChanged, etc.) same ...

        function addRow() {
            let rowHtml = `
            <div class="col-lg-6 col-xl-4 item-wrapper">
                <div class="item-card">
                    <input type="hidden" name="items[row_id][]" value="">

                    <div class="mb-3">
                        <label class="form-label small fw-medium">Machine</label>
                        <select name="items[machine_id][]" class="form-select machine-select">
                            ${machinesOptionsHtml}
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-medium">Inventory Item</label>
                        <select name="items[inventory_id][]" class="form-select item-select">
                            <option value="">-- Select Inventory --</option>
                        </select>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label small fw-medium">Quantity</label>
                            <input type="number" name="items[quantity][]" class="form-control qty-input" min="1" placeholder="Qty">
                            <input type="hidden" name="items[need_qty][]" class="inv-qty">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-medium">Description</label>
                            <input type="text" name="items[description][]" class="form-control" placeholder="Description">
                        </div>
                    </div>

                    <button type="button" class="btn btn-danger btn-sm remove-row position-absolute top-0 end-0 m-2">
                        <i class="feather-trash"></i>
                    </button>
                </div>
            </div>`;

            $('#items-grid').append(rowHtml);
            activateSelect2Scoped($('#items-grid .item-wrapper:last'));
        }

        $(document).on('click', '.remove-row', function() {
            $(this).closest('.item-wrapper').remove();
        });

        // Keep all your other existing scripts (project change, machine change, item change, etc.)
        // Just make sure you replace the old table logic with this grid version.

        $(document).ready(function() {
            // Initial row
            if ($('#items-grid .item-wrapper').length === 0) {
                addRow();
            }

            const projectId = $('#project_id').val();
            if (projectId) onProjectChanged(projectId);
        });

        document.querySelector("#add_inventory_btn").addEventListener("click", addRow);

        // Comment toggle
        $(document).on('click', '.comment-toggle', function() {
            $('.comment-box').slideToggle(200);
            $(this).toggleClass('feather-plus-circle feather-minus-circle');
        });
    </script>
    @endsection

@endsection