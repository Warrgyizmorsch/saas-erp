@extends('shared::layouts.app')
@section('content')

    @php
    $isEdit = isset($requestSlip);
    $pageTitle = $isEdit ? 'Edit Request Slip' : 'Create Request Slip';
    $subTitle = $isEdit ? 'Update Request Slip' : 'Add Request Slip';
    @endphp
<!-- 
    {{-- ❌ Error Message --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ✅ Success Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif -->

    <div class="page-header d-flex justify-content-between align-items-center ">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">{{ $pageTitle }}</h5>
            </div>

            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('request-slip.safety.index') }}">Request Slip</a></li>
                <li class="breadcrumb-item active">{{ $pageTitle }}</li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <div class="card">
            <div class="card-body">

                <h5 class="mb-4">{{ $subTitle }}</h5>

                <form id="rsForm"
                    action="{{ $isEdit ? route('request-slip.safety.update', $requestSlip->id) : route('request-slip.safety.store') }}"
                    method="POST">

                    @csrf
                    @if($isEdit)
                    @method('PUT')
                    @endif
                    {{-- FORM SEPERATION --}}
                    <input type="hidden" name="rs_mode" value="inventory_only">

                    <!-- META STRIP -->
                    <div class="rs-meta-strip">
                        <div class="rs-meta-item">
                            <span class="label"><i class="fa fa-calendar" style="margin-right:4px;"></i> Date</span>
                            <span class="value">{{ old('transaction_date', $isEdit ? $requestSlip->transaction_date : date('Y-m-d')) }}</span>
                            <input type="hidden" name="transaction_date" value="{{ old('transaction_date', $isEdit ? $requestSlip->transaction_date : date('Y-m-d')) }}">
                        </div>
                        <div class="rs-meta-divider"></div>
                        <div class="rs-meta-item">
                            <span class="label"><i class="fa fa-hashtag" style="margin-right:4px;"></i> RS No.</span>
                            <span class="value mono">{{ old('requisition_slip_no', $isEdit ? $requestSlip->requisition_slip_no : $nextSlipNo) }}</span>
                            <input type="hidden" name="requisition_slip_no" value="{{ old('requisition_slip_no', $isEdit ? $requestSlip->requisition_slip_no : $nextSlipNo) }}">
                        </div>
                    </div>

                    <div class="row g-4">

                        {{-- EMPLOYEE --}}
                        <input type="hidden" name="employee_id" value="{{ Auth::user()->id }}">

                        {{-- HIDDEN DEPARTMENT --}}
                        <input type="hidden" name="department_id" value="{{ Auth::user()->department_id }}">

                        {{-- COMMENT --}}
                        <div class="col-lg-8">
                            <label class="form-label">Comment
                                <i class="feather-plus-circle bg-soft-primary text-primary d-md-none comment-toggle"
                                    style="cursor:pointer;"></i>
                            </label>
                            <textarea name="comment" class="form-control comment-box" rows="2"
                                placeholder="Enter Comment">{{ old('comment', $isEdit ? $requestSlip->comment : '') }}</textarea>
                        </div>
                    </div>

                    {{-- ITEMS TABLE --}}
                    <div class="mt-4">
                        <div class="card">
                            <div class="card-body p-0">
                                <div class="d-flex justify-content-between align-items-center px-4 py-3">
                                    <h5 class="mb-0">Selected Product Items</h5>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped mb-0 mobile-table">
                                        <thead>
                                            <tr>
                                                <!-- <th style="min-width:220px;">Machine</th> -->
                                                <th style="min-width:280px;">Inventory Item</th>
                                                <th style="min-width:140px;">Quantity</th>
                                                <th style="min-width:260px;">Description</th>
                                                <th style="width:60px;"></th>
                                            </tr>
                                        </thead>

                                        <tbody id="product_items_list">
                                            @php
                                            $oldItems = old('items', []);
                                            @endphp

                                            {{-- 1️⃣ Render old input (after validation fail) --}}
                                            @if(count($oldItems['quantity'] ?? []) > 0)
                                            @foreach($oldItems['quantity'] as $i => $qty)
                                            <tr>
                                                <input type="hidden" name="items[row_id][]" value="{{ $oldItems['row_id'][$i] ?? '' }}">

                                                {{-- INVENTORY --}}
                                                <td data-label="Inventory Item">
                                                    <select name="items[inventory_id][]" class="form-select item-select"
                                                        data-selected="{{ $oldItems['inventory_id'][$i] ?? '' }}">
                                                        <option value="">-- Select Inventory --</option>
                                                    </select>
                                                    @if(isset($errors->toArray()['items.inventory_id.'.$i]))
                                                    <div class="text-danger mt-1 small">
                                                        {{ $errors->first('items.inventory_id.'.$i) }}
                                                    </div>
                                                    @endif
                                                </td>

                                                {{-- QUANTITY --}}
                                                <td data-label="Quantity">
                                                    <input type="number" name="items[quantity][]" class="form-control"
                                                        value="{{ $qty }}" min="1" placeholder="Quantity">

                                                    @if(isset($errors->toArray()['items.quantity.'.$i]))
                                                    <div class="text-danger mt-1 small">
                                                        {{ $errors->first('items.quantity.'.$i) }}
                                                    </div>
                                                    @endif

                                                    {{-- ✅ TEMP: remaining/need_qty (auto) --}}
                                                   <input type="text" name="items[need_qty][]" class="form-control inv-qty mt-1" readonly
       placeholder="Need Qty (Auto)">

<input type="hidden" class="form-control exited-qty mt-1" readonly
       placeholder="Exited Qty (Auto)">
                                                </td>

                                                {{-- DESCRIPTION --}}
                                                <td data-label="Description">
                                                    <input type="text" name="items[description][]" class="form-control"
                                                        value="{{ $oldItems['description'][$i] ?? '' }}" placeholder="Description">
                                                </td>

                                                {{-- REMOVE BUTTON --}}
                                                <td>
                                                    <button type="button" class="btn btn-danger btn-sm remove-row">
                                                        <i class="feather-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach

                                            {{-- 2️⃣ Render existing rows (edit mode, no validation errors) --}}
                                            @elseif($isEdit && $requestSlip->rows->count() > 0)
                                            @foreach ($requestSlip->rows as $i => $row)
                                            <tr>
                                                <input type="hidden" name="items[row_id][]" value="{{ $row->id }}">

                                                {{-- INVENTORY --}}
                                                <td data-label="Inventory Item">
                                                    <select name="items[inventory_id][]" class="form-select item-select">
                                                        <option value="">-- Select Inventory --</option>

                                                        @foreach ($inventory as $inv)
                                                            <option value="{{ $inv->id }}"
                                                                {{ $inv->id == $row->item_id ? 'selected' : '' }}>
                                                                {{ $inv->item_name ?? $inv->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>


                                                {{-- QUANTITY --}}
                                                <td data-label="Quantity">
                                                    <input type="number" name="items[quantity][]" class="form-control"
                                                        value="{{ $row->quantity }}" min="1" placeholder="Quantity">

                                                    {{-- ✅ TEMP: remaining/need_qty (auto) --}}
                                                    <input type="hidden" name="items[need_qty][]" class="form-control inv-qty mt-1" readonly placeholder="Remaining (Auto)">
                                                </td>

                                                {{-- DESCRIPTION --}}
                                                <td data-label="Description">
                                                    <input type="text" name="items[description][]" class="form-control"
                                                        value="{{ $row->description }}" placeholder="Description">
                                                </td>

                                                {{-- REMOVE BUTTON --}}
                                                <td>
                                                    <button type="button" class="btn btn-danger btn-sm remove-row">
                                                        <i class="feather-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>

                                <div class="p-3">
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="add_inventory_btn">
                                        <i class="feather-plus"></i> Add Inventory
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SUBMIT --}}
                    <div class="sticky-save-bar d-flex justify-content-end">
                        <button class="btn btn-primary mobile-full-btn">
                            {{ $isEdit ? 'Update Request Slip' : 'Save Request Slip' }}
                        </button>
                    </div>                    


                </form>

            </div>
        </div>
    </div>

    @push('scripts')
   <script>
    window.RS_ID = {{ $isEdit ? (int)$requestSlip->id : 0 }};
    window.INVENTORIES = @json($inventory);

    function activateSelect2Scoped($root) {
        if (!(window.jQuery && $.fn.select2)) return;
        $root.find('.machine-select').not('.select2-hidden-accessible').select2({ width: '100%' });
        $root.find('.item-select').not('.select2-hidden-accessible').select2({ width: '100%' });
    }

    function buildMachinesOptions(machines) {
        let html = `<option value="">-- Select Machine --</option>`;
        machines.forEach(m => { html += `<option value="${m.id}">${m.name}</option>`; });
        return html;
    }
    
    function buildInventoryOptions(selectedId = '') {
        let html = `<option value="">-- Select Inventory --</option>`;

        window.INVENTORIES.forEach(item => {
            const selected = (selectedId == item.id) ? 'selected' : '';
            html += `
                <option value="${item.id}"
                        data-need="${item.quantity ?? 0}"
                        ${selected}>
                    ${item.item_name ?? item.name}
                </option>`;
        });

        return html;
    }

    function calcExitedForRow($row) {
        const need = parseFloat($row.find('.inv-qty').val() || 0);
        const qty  = parseFloat($row.find('.qty-input').val() || 0);

        const exited = qty > need ? (qty - need) : 0;
        $row.find('.exited-qty').val(exited);
    }
    

    function addRow() {
        let rowHtml = `
        <tr>
            <input type="hidden" name="items[row_id][]" value="">            

            <td data-label="Inventory Item">
                <select name="items[inventory_id][]" class="form-select item-select">
                    ${buildInventoryOptions()}
                </select>
            </td>

            <td data-label="Quantity">
                <input type="number" name="items[quantity][]" class="form-control qty-input" placeholder="Quantity" min="1">

                <input type="hidden" name="items[need_qty][]" class="form-control inv-qty mt-1" readonly
                       placeholder="Need Qty (Auto)">

                <input type="hidden" class="form-control exited-qty mt-1" readonly
                       placeholder="Exited Qty (Auto)">
            </td>

            <td data-label="Description">
                <input type="text" name="items[description][]" class="form-control" placeholder="Description">
            </td>

            <td>
                <button type="button" class="btn btn-danger btn-sm remove-row">
                    <i class="feather-trash"></i>
                </button>
            </td>
        </tr>
        `;

        $('#product_items_list').append(rowHtml);
        activateSelect2Scoped($('#product_items_list tr:last'));
    }

    $(document).on('click', '.remove-row', function() {
        $(this).closest('tr').remove();
    });    

    // ✅ Inventory change -> need_qty set + exited recalc
    $(document).on('change', '.item-select', function() {
        const $row = $(this).closest('tr');
        const $opt = $(this).find('option:selected');

        if (!$(this).val()) {
            $row.find('.inv-qty').val('');
            $row.find('.exited-qty').val('');
            return;
        }

        const need = parseFloat($opt.data('need') || 0);
        $row.find('.inv-qty').val(need);

        calcExitedForRow($row);
    });

    // ✅ Quantity type -> exited live update
    $(document).on('input', '.qty-input', function() {
        const $row = $(this).closest('tr');
        calcExitedForRow($row);
    });

    // Toggle comments visibility in mobile
    $(document).on('click', '.comment-toggle', function() {
        $('.comment-box').slideToggle(200);
        $(this).toggleClass('feather-plus-circle feather-minus-circle');
    });

    $(document).ready(function() {
        if ($('#product_items_list tr').length === 0) {
            addRow();
        } else {
            activateSelect2Scoped($('#product_items_list'));
        }

        $(document).on('click', '#add_inventory_btn', function() {
            addRow();
        });
    });
</script>

    <style>
        .rs-meta-strip {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 16px;
            margin-bottom: 22px;
            flex-wrap: wrap;
            width: fit-content;
        }

        .rs-meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
        }

        .rs-meta-item .label {
            color: #64748b;
            font-weight: 500;
            white-space: nowrap;
        }

        .rs-meta-item .value {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 4px 12px;
            font-weight: 700;
            font-size: 13px;
            color: #1e293b;
            white-space: nowrap;
        }

        .rs-meta-item .value.mono {
            color: #2563eb;
            letter-spacing: .5px;
        }

        .rs-meta-divider {
            width: 1px;
            height: 28px;
            background: #e2e8f0;
            flex-shrink: 0;
        }

        .sticky-save-bar {
            position: sticky;
            bottom: 0;
            background: #fff;
            padding: 10px 16px;
            border-top: 1px solid #eee;
            z-index: 1000;
        }

        .select2-container {
            z-index: 1;
        }

        .card,
        .card-body,
        .main-content {
            overflow: visible !important;
        }

        @media (max-width: 768px) {
            .sticky-save-bar {
                position: fixed;
                left: 0;
                right: 0;
            }

            .comment-box {
                display: none;
            }

            .mobile-full-btn {
                width: 100%;
            }

            .mobile-table thead {
                display: none;
            }

            .mobile-table tbody tr {
                display: block;
                border: 1px solid #eee;
                border-radius: 10px;
                margin-bottom: 12px;
                padding: 12px;
                background: #fff;
            }

            .mobile-table tbody td {
                display: block;
                width: 100%;
                border: none;
                padding: 6px 0;
            }

            .table-responsive .table tr td {
                padding: 8px 8px;
            }

            .mobile-table tbody td::before {
                content: attr(data-label);
                font-weight: 600;
                font-size: 12px;
                color: #666;
                display: block;
                margin-bottom: 3px;
            }

            .mobile-table tbody td:last-child {
                text-align: right;
            }
        }
    </style>
    @endpush

@endsection
