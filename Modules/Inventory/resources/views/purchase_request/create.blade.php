    @extends('shared::layouts.app')
@section('content')
        @php
        $items = $oldItems ?? null;
        @endphp

        <!-- PAGE HEADER -->
        <div class="page-header d-flex justify-content-between align-items-center">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">Create Purchase Request</h5>
                </div>

                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                    <li class="breadcrumb-item active">Purchase Request</li>
                </ul>
            </div>
        </div>

        <!-- MAIN CONTENT -->
        <div class="main-content">
            <div class="card">
                <div class="card-body">

                    <form method="POST" action="{{ route('purchase_request.store') }}">
                        @csrf

                        <div class="po-meta-strip">

                            <div class="po-meta-item">
                                <span class="label">
                                    <i class="fa fa-calendar" style="margin-right:4px;"></i>
                                    Date
                                </span>

                                <span class="value">{{ $today }}</span>

                                <input type="hidden"
                                    name="request_date"
                                    value="{{ $today }}">
                            </div>

                            <div class="po-meta-divider"></div>

                            <div class="po-meta-item">
                                <span class="label">
                                    <i class="fa fa-hashtag" style="margin-right:4px;"></i>
                                    PR No.
                                </span>

                                <span class="value mono">
                                    {{ $nextPrNo }}
                                </span>

                                <input type="hidden"
                                    name="purchase_request_no"
                                    value="{{ $nextPrNo }}">
                            </div>

                        </div>

                        <!-- TOP FORM -->
                        <div class="row g-4">

                            <!-- <div class="col-lg-3">
                                <label class="form-label">Request Date</label>
                                <input type="date" name="request_date"
                                    class="form-control"
                                    value="{{ $today }}" required readonly>
                            </div>

                            <div class="col-lg-3">
                                <label class="form-label">Purchase Request No</label>
                                <input type="text" name="purchase_request_no"
                                    class="form-control"
                                    value="{{ $nextPrNo }}" required readonly>
                            </div> -->

                            <div class="col-lg-3">
                                <label class="form-label">Priority</label>
                                <select name="priority" class="form-control" data-select2-selector="status">
                                    <option value="NORMAL">NORMAL</option>
                                    <option value="LOW">LOW</option>
                                    <option value="HIGH">HIGH</option>
                                    <option value="URGENT">URGENT</option>
                                </select>

                                @if ($errors->has('priority'))
                                <div class="text-danger mt-1 small">
                                    {{ $errors->first('priority') }}
                                </div>
                                @endif
                            </div>

                            <div class="col-lg-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control" data-select2-selector="status">
                                    <option value="DRAFT">DRAFT</option>
                                    <option value="SUBMITTED">SUBMITTED</option>

                                </select>
                            </div>

                            <div class="col-lg-6">
                                <label class="form-label">Comment</label>
                                <textarea name="comment" class="form-control" rows="1"></textarea>
                            </div>

                        </div>

                        <!-- ITEMS TABLE -->
                        <div class="mt-4">
                            <div class="card">
                                <div class="card-body p-0">
                                    <div class="d-flex justify-content-between align-items-center mb-2">

                                        <button type="button" class="btn btn-outline-primary btn-sm" id="addRow">
                                            + Add Item
                                        </button>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-striped mb-0">
                                            <thead>
                                                <tr>
                                                    <th style="min-width:280px;">Inventory Item</th>
                                                    <th style="min-width:140px;">Request Qty</th>
                                                    <th>Description</th>
                                                    <!-- <th>Required date</th> -->
                                                    <th style="width:60px;"></th>
                                                </tr>
                                            </thead>

                                            <tbody id="item_rows">

                                                {{-- 1️⃣ OLD INPUT (validation fail ke baad) --}}

                                                @if(!empty($selectedItems))

                                                @foreach($selectedItems as $index => $item)

                                                <tr>

                                                    <input type="hidden"
                                                        name="items[{{ $index }}][item_id]"
                                                        value="{{ $item['item_id'] }}">

                                                    <td>
                                                        <select name="items[{{ $index }}][item_id]"
                                                            class="form-select item-id itemSelect"
                                                            data-id="{{ $item['item_id'] }}"
                                                            data-text="{{ $item['item_name'] }} {{ $item['inventory_model'] }}">
                                                        </select>
                                                    </td>

                                                    <td>
                                                        <input type="number"
                                                            name="items[{{ $index }}][request_qty]"
                                                            class="form-control request-qty"
                                                            value="{{ $item['qty'] }}">
                                                    </td>

                                                    <td>
                                                        <input type="text"
                                                            name="items[{{ $index }}][description]"
                                                            class="form-control">
                                                    </td>

                                                    <td>
                                                        <button type="button"
                                                            class="btn btn-sm btn-danger remove-row">
                                                            -
                                                        </button>
                                                    </td>

                                                </tr>

                                                @endforeach
                                                @endif

                                               

                                                @if(is_array($items))

                                                @foreach($items as $index => $item)
                                                <tr>
                                                    {{-- ITEM --}}
                                                    <input type="hidden"
                                                        name="items[{{ $index }}][issue_slip_row_id]"
                                                        value="{{ $item['issue_slip_row_id'] ?? ''}}">

                                                    <input type="hidden"
                                                        name="items[{{ $index }}][item_id]"
                                                        value="{{ $item['item_id'] }}">
                                                    <td>
                                                        <select name="items[{{ $index }}][item_id]"
                                                            class="form-select item-id itemSelect"
                                                            data-id="{{ $item['item_id'] ?? '' }}"
                                                            data-text="{{ $item['item_name'] ?? '' }}"
                                                            {{ !empty($item['row_id']) ? 'disabled' : '' }}>
                                                        </select>
                                                    </td>
                                                    {{-- QTY --}}
                                                    <td>
                                                        <input type="number"
                                                            name="items[{{ $index }}][request_qty]"
                                                            class="form-control request-qty"
                                                            value="{{ $item['request_qty'] ?? 0 }}">

                                                        @if ($errors->has("items.$index.request_qty"))
                                                        <div class="text-danger small mt-1">
                                                            {{ $errors->first("items.$index.request_qty") }}
                                                        </div>
                                                        @endif
                                                    </td>

                                                    {{-- DESCRIPTION --}}
                                                    <td>
                                                        <input type="text"
                                                            name="items[{{ $index }}][description]"
                                                            class="form-control"
                                                            value="{{ $item['description'] ?? '' }}">
                                                    </td>

                                                    {{-- REQUIRED DATE --}}
                                                    <!-- <td>
                                                        <input type="date"
                                                            name="items[{{ $index }}][required_date]"
                                                            class="form-control"
                                                            value="{{ $item['required_date'] ?? '' }}">

                                                        @if ($errors->has("items.$index.required_date"))
                                                        <div class="text-danger small mt-1">
                                                            {{ $errors->first("items.$index.required_date") }}
                                                        </div>
                                                        @endif
                                                    </td> -->

                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-danger remove-row">-</button>
                                                    </td>
                                                </tr>
                                                @endforeach



                                                @else

                                                {{-- EXISTING ITEMS --}}
                                                @foreach($Request_items as $index => $row)
                                                <tr>
                                                    <input type="hidden"
                                                        name="items[{{ $index }}][issue_slip_row_id]"
                                                        value="{{ $row->issue_slip_row_id ?? ''}}">

                                                    <input type="hidden"
                                                        name="items[{{ $index }}][row_id]"
                                                        value="{{$row->id}}">
                                                    <td>
                                                        <select class="form-select item-id itemSelect" data-select2-selector="status" disabled>
                                                            <option value="">Select Item</option>

                                                            @if($row->item_id && $row->inventory)
                                                            <option value="{{ $row->item_id }}" selected>
                                                                {{ $row->inventory->name }} {{ $row->inventory->model }}
                                                            </option>
                                                            @endif
                                                        </select>
                                                    </td>

                                                    <input type="hidden" name="items[{{ $index }}][item_id]" value="{{ $row->item_id }}">

                                                    <td>
                                                        <input type="number"
                                                            name="items[{{ $index }}][request_qty]"
                                                            class="form-control request-qty"
                                                            value="{{ $row->order_qty }}"
                                                            min="0">

                                                        <input type="hidden" name="items[{{ $index }}][order_qty]" value="{{ $row->order_qty}}">
                                                    </td>

                                                    <td>
                                                        <input type="text"
                                                            name="items[{{ $index }}][description]"
                                                            class="form-control description"
                                                            value="">
                                                    </td>

                                                    <!-- <td>
                                                        <input type="date" name="items[{{ $index }}][required_date]" class="form-control required-date">
                                                    </td> -->

                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-danger remove-row">-</button>
                                                    </td>
                                                </tr>
                                                @endforeach
                                                @endif

                                            </tbody>

                                            <tfoot>
                                                <tr>
                                                    <td class="text-end"><strong>Total Request qty:</strong></td>
                                                    <td>
                                                        <input type="number" id="total_request_qty" name="total_qty" class="form-control" value="0" readonly>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>



                                </div>
                            </div>
                        </div>

                        <!-- SAVE -->
                        <div class="mt-3 d-flex justify-content-end">
                            <button class="btn btn-primary">Save Purchase Request</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <!-- TEMPLATE FOR NEW ROW -->
        <template id="item_row_template">
            <tr>
                <td>
                    <select class="form-select item-id itemSelect">
                        <option value="">Select Item</option>
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control request-qty" value="0" min="0">
                </td>

                <td>
                    <input type="text" class="form-control description" value="">
                </td>

                <!-- <td>
                    <input type="date" class="form-control required-date">
                </td> -->

                <td>
                    <button type="button" class="btn btn-sm btn-danger remove-row">-</button>
                </td>
            </tr>
        </template>

        <style>
            :root {
                --po-blue: #2563eb;
                --po-blue-light: #eff6ff;
                --po-blue-dark: #1d4ed8;
                --po-border: #e2e8f0;
                --po-surface: #ffffff;
                --po-text: #1e293b;
                --po-muted: #64748b;
                --po-danger: #ef4444;
                --po-badge-bg: #f1f5f9;
                --radius-sm: 6px;
                --radius-md: 10px;
                --radius-lg: 14px;
                --shadow-sm: 0 1px 3px rgba(0, 0, 0, .08);
            }

            .po-page-header {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 18px 0 12px;
                border-bottom: 1px solid var(--po-border);
                margin-bottom: 20px;
            }

            .po-page-header .po-icon {
                width: 40px;
                height: 40px;
                background: var(--po-blue);
                border-radius: var(--radius-md);
                display: grid;
                place-items: center;
                color: #fff;
                font-size: 18px;
                flex-shrink: 0;
            }

            .po-page-header h5 {
                margin: 0;
                font-size: 17px;
                font-weight: 700;
                color: var(--po-text);
                letter-spacing: -.3px;
            }

            .po-breadcrumb {
                margin: 0;
                padding: 0;
                list-style: none;
                display: flex;
                gap: 6px;
                font-size: 12px;
                color: var(--po-muted);
            }

            .po-breadcrumb li+li::before {
                content: '/';
                margin-right: 6px;
            }

            .po-breadcrumb a {
                color: var(--po-blue);
                text-decoration: none;
            }

            .po-card {
                background: var(--po-surface);
                border: 1px solid var(--po-border);
                border-radius: var(--radius-lg);
                box-shadow: var(--shadow-sm);
                overflow: hidden;
            }

            .po-card-body {
                padding: 24px;
            }

            .po-meta-strip {
                display: flex;
                align-items: center;
                gap: 10px;
                background: var(--po-badge-bg);
                border: 1px solid var(--po-border);
                border-radius: var(--radius-md);
                padding: 10px 16px;
                margin-bottom: 22px;
                flex-wrap: wrap;
                width: fit-content;
            }

            .po-meta-item {
                display: flex;
                align-items: center;
                gap: 6px;
                font-size: 13px;
            }

            .po-meta-item .label {
                color: var(--po-muted);
                font-weight: 500;
                white-space: nowrap;
            }

            .po-meta-item .value {
                background: var(--po-surface);
                border: 1px solid var(--po-border);
                border-radius: var(--radius-sm);
                padding: 4px 12px;
                font-weight: 700;
                font-size: 13px;
                color: var(--po-text);
                white-space: nowrap;
            }

            .po-meta-item .value.mono {
                color: var(--po-blue);
                letter-spacing: .5px;
            }

            .po-meta-divider {
                width: 1px;
                height: 28px;
                background: var(--po-border);
                flex-shrink: 0;
            }

            .po-fields-grid {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 16px 20px;
                margin-bottom: 24px;
            }

            @media(max-width:1200px) {
                .po-fields-grid {
                    grid-template-columns: repeat(2, 1fr);
                }
            }

            @media(max-width:640px) {
                .po-fields-grid {
                    grid-template-columns: 1fr;
                }
            }

            .po-field label {
                display: block;
                font-size: 12px;
                font-weight: 600;
                color: var(--po-muted);
                text-transform: uppercase;
                letter-spacing: .5px;
                margin-bottom: 6px;
            }

            .po-field .form-control,
            .po-field .form-select {
                border: 1.5px solid var(--po-border);
                border-radius: var(--radius-sm);
                font-size: 13.5px;
                padding: 8px 12px;
                color: var(--po-text);
                width: 100%;
                transition: border-color .15s;
                background: var(--po-surface);
            }

            .po-field .form-control:focus,
            .po-field .form-select:focus {
                border-color: var(--po-blue);
                outline: none;
                box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
            }

            .po-field .supplier-row {
                display: flex;
                gap: 8px;
            }

            .po-field .supplier-row .select-wrap {
                flex: 1;
                min-width: 0;
            }

            .po-field .btn-add-supplier {
                background: var(--po-blue);
                color: #fff;
                border: none;
                border-radius: var(--radius-sm);
                padding: 0 14px;
                cursor: pointer;
                font-size: 18px;
                line-height: 1;
                flex-shrink: 0;
                transition: background .15s;
            }

            .po-field .btn-add-supplier:hover {
                background: var(--po-blue-dark);
            }

            .po-field .error-msg {
                font-size: 11px;
                color: #dc3545;
                margin-top: 4px;
            }

            .po-items-section {
                border: 1px solid var(--po-border);
                border-radius: var(--radius-md);
                overflow: hidden;
                margin-bottom: 24px;
            }

            .po-items-header {
                background: var(--po-badge-bg);
                border-bottom: 1px solid var(--po-border);
                padding: 10px 16px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 8px;
            }

            .po-items-header span {
                font-size: 13px;
                font-weight: 700;
                color: var(--po-text);
                text-transform: uppercase;
                letter-spacing: .5px;
            }

            .po-items-header-btns {
                display: flex;
                gap: 8px;
                align-items: center;
            }

            .btn-add-row {
                background: var(--po-blue-light);
                color: var(--po-blue);
                border: 1.5px solid var(--po-blue);
                border-radius: var(--radius-sm);
                padding: 5px 14px;
                font-size: 13px;
                font-weight: 600;
                cursor: pointer;
                transition: all .15s;
            }

            .btn-add-row:hover {
                background: var(--po-blue);
                color: #fff;
            }

            .btn-add-inv-hdr {
                background: #f0fdf4;
                color: #16a34a;
                border: 1.5px solid #86efac;
                border-radius: var(--radius-sm);
                padding: 5px 14px;
                font-size: 13px;
                font-weight: 600;
                cursor: pointer;
                transition: all .15s;
                display: flex;
                align-items: center;
                gap: 5px;
            }

            .btn-add-inv-hdr:hover {
                background: #16a34a;
                color: #fff;
            }

            .po-table-wrap {
                overflow-x: auto;
            }

            .po-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 12.5px;
            }

            .po-table thead th {
                background: #f8fafc;
                color: var(--po-muted);
                font-size: 11px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .5px;
                padding: 10px 8px;
                border-bottom: 2px solid var(--po-border);
                white-space: nowrap;
                text-align: left;
            }

            .po-table tbody tr {
                border-bottom: 1px solid var(--po-border);
                transition: background .1s;
            }

            .po-table tbody tr:hover {
                background: #fafcff;
            }

            .po-table tbody td {
                padding: 8px 6px;
                vertical-align: top;
            }

            .po-table tfoot td {
                padding: 8px 6px;
                background: var(--po-badge-bg);
                border-top: 2px solid var(--po-border);
            }

            .po-table .form-control,
            .po-table .form-select {
                border: 1.5px solid var(--po-border);
                border-radius: var(--radius-sm);
                font-size: 12.5px;
                padding: 5px 8px;
                width: 100%;
                background: var(--po-surface);
                color: var(--po-text);
                transition: border-color .15s;
            }

            .po-table .form-control:focus,
            .po-table .form-select:focus {
                border-color: var(--po-blue);
                outline: none;
                box-shadow: 0 0 0 2px rgba(37, 99, 235, .10);
            }


            .po-table .form-control[readonly] {
                background: var(--po-badge-bg);
                color: var(--po-muted);
                cursor: default;
            }

            .po-table .form-control.is-invalid {
                border-color: #dc3545 !important;
            }

            .field-error {
                font-size: 11px;
                color: #dc3545;
                margin-top: 3px;
                display: none;
            }

            .col-item {
                min-width: 220px;
                width: 220px;
            }

            .col-hsn {
                min-width: 110px;
                width: 110px;
            }

            .col-qty {
                min-width: 90px;
                width: 90px;
            }

            .col-price {
                min-width: 100px;
                width: 100px;
            }

            .col-taxsub {
                min-width: 110px;
                width: 110px;
            }

            .col-disc {
                min-width: 80px;
                width: 80px;
            }

            .col-amt {
                min-width: 110px;
                width: 110px;
            }

            .col-taxtype {
                min-width: 90px;
                width: 90px;
            }

            .col-tax {
                min-width: 75px;
                width: 75px;
            }

            .col-taxamt {
                min-width: 100px;
                width: 100px;
            }

            .col-total {
                min-width: 110px;
                width: 110px;
            }

            .col-del {
                min-width: 40px;
                width: 40px;
                text-align: center;
            }

            .btn-remove {
                background: #fee2e2;
                color: var(--po-danger);
                border: 1px solid #fca5a5;
                border-radius: var(--radius-sm);
                width: 28px;
                height: 28px;
                display: grid;
                place-items: center;
                cursor: pointer;
                font-size: 16px;
                font-weight: 700;
                transition: all .15s;
            }

            .btn-remove:hover {
                background: var(--po-danger);
                color: #fff;
            }

            .po-table tfoot .foot-label {
                font-size: 11px;
                font-weight: 700;
                color: var(--po-muted);
                text-transform: uppercase;
                text-align: right;
                white-space: nowrap;
                padding-right: 8px;
            }

            .po-bottom {
                display: grid;
                grid-template-columns: minmax(0, 1fr) minmax(280px, 420px);
                gap: 24px;
                align-items: start;
            }

            @media(max-width:992px) {
                .po-bottom {
                    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                }
            }

            .po-terms label {
                font-size: 12px;
                font-weight: 700;
                color: var(--po-muted);
                text-transform: uppercase;
                letter-spacing: .5px;
                margin-bottom: 8px;
                display: block;
            }

            .po-totals-card {
                border: 1.5px solid var(--po-border);
                border-radius: var(--radius-md);
                overflow: hidden;
            }

            .po-totals-card .tc-head {
                background: var(--po-blue);
                color: #fff;
                font-size: 12px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .5px;
                padding: 10px 16px;
            }

            .po-totals-card .tc-body {
                padding: 16px;
            }

            .tc-row {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 12px;
                padding: 7px 0;
                border-bottom: 1px solid var(--po-border);
            }

            .tc-row:last-child {
                border-bottom: none;
            }

            .tc-row .tc-label {
                font-size: 12.5px;
                color: var(--po-muted);
                font-weight: 500;
                white-space: nowrap;
            }

            .tc-row .tc-input {
                width: 160px;
                flex-shrink: 0;
            }

            .tc-row .tc-input input {
                width: 100%;
                border: 1.5px solid var(--po-border);
                border-radius: var(--radius-sm);
                padding: 6px 10px;
                font-size: 13px;
                text-align: right;
                background: var(--po-badge-bg);
                color: var(--po-text);
                font-weight: 600;
            }

            .tc-row .tc-input input:not([readonly]) {
                background: var(--po-surface);
            }

            .tc-row .tc-input input:not([readonly]):focus {
                border-color: var(--po-blue);
                outline: none;
                box-shadow: 0 0 0 2px rgba(37, 99, 235, .10);
            }

            .tc-divider {
                border: none;
                border-top: 2px solid var(--po-border);
                margin: 8px 0;
            }

            .tc-row.balance .tc-label {
                font-weight: 800;
                font-size: 14px;
                color: var(--po-blue);
            }

            .tc-row.balance .tc-input input {
                background: var(--po-blue-light);
                border-color: var(--po-blue);
                color: var(--po-blue);
                font-size: 15px;
                font-weight: 800;
            }

            .po-save-bar {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                gap: 12px;
                padding: 16px 24px;
                border-top: 1px solid var(--po-border);
                background: var(--po-badge-bg);
            }

            .po-save-bar .status-wrap {
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .po-save-bar .status-wrap label {
                font-size: 12px;
                font-weight: 700;
                color: var(--po-muted);
                text-transform: uppercase;
                letter-spacing: .5px;
                white-space: nowrap;
            }

            .po-save-bar .status-wrap select {
                border: 1.5px solid var(--po-border);
                border-radius: var(--radius-sm);
                padding: 7px 12px;
                font-size: 13px;
                font-weight: 600;
                color: var(--po-text);
                background: var(--po-surface);
                min-width: 140px;
            }

            .btn-save-po {
                background: var(--po-blue);
                color: #fff;
                border: none;
                border-radius: var(--radius-sm);
                padding: 9px 28px;
                font-size: 14px;
                font-weight: 700;
                cursor: pointer;
                transition: background .15s;
                letter-spacing: .2px;
            }

            .btn-save-po:hover {
                background: var(--po-blue-dark);
            }

            .item-note-toggle {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                margin-top: 5px;
                font-size: 11px;
                font-weight: 600;
                color: var(--po-blue);
                cursor: pointer;
                user-select: none;
                background: var(--po-blue-light);
                border: 1px solid #bfdbfe;
                border-radius: 20px;
                padding: 2px 8px 2px 6px;
                transition: background .15s;
                width: fit-content;
            }

            .item-note-toggle:hover {
                background: #dbeafe;
            }

            .item-note-toggle .plus-icon {
                font-size: 14px;
                font-weight: 700;
                line-height: 1;
                color: var(--po-blue);
                transition: transform .2s;
            }

            .item-note-toggle.open .plus-icon {
                transform: rotate(45deg);
            }

            .item-note-wrap {
                display: none;
                margin-top: 5px;
            }

            .item-note-wrap.open {
                display: block;
            }

            .select2-container {
                z-index: 1;
            }

            .ck.ck-editor {
                height: 100%;
            }

            .ck-editor__main {
                height: 80%;
            }

            .ck-editor__editable_inline {
                height: 100%;
            }


            /* ============================================================
           CUSTOM MODAL — completely separate from Bootstrap modal
           Fixed to viewport via JS-injected overlay in <body>
           No blur, no z-index conflict with Duralux theme
        ============================================================ */
            #po-inv-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, 0.55);
                z-index: 999999;
                align-items: center;
                justify-content: center;
                padding: 16px;
            }

            #po-inv-overlay.active {
                display: flex;
            }

            #po-inv-box {
                background: #ffffff;
                border-radius: 14px;
                width: 100%;
                max-width: 860px;
                max-height: 90vh;
                display: flex;
                flex-direction: column;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
                overflow: hidden;
                position: relative;
                z-index: 1000000;
            }

            #po-inv-box .inv-modal-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 16px 20px;
                border-bottom: 1px solid #e2e8f0;
                background: #f8fafc;
                flex-shrink: 0;
            }

            #po-inv-box .inv-modal-head h5 {
                margin: 0;
                font-size: 15px;
                font-weight: 700;
                color: #1e293b;
            }

            #po-inv-box .inv-modal-close {
                width: 30px;
                height: 30px;
                border: none;
                background: #fee2e2;
                border-radius: 6px;
                cursor: pointer;
                font-size: 18px;
                font-weight: 700;
                color: #ef4444;
                display: grid;
                place-items: center;
                line-height: 1;
                transition: background .15s;
            }

            #po-inv-box .inv-modal-close:hover {
                background: #ef4444;
                color: #fff;
            }

            #po-inv-box .inv-modal-body {
                padding: 20px;
                overflow-y: auto;
                flex: 1;
            }

            #po-inv-box .inv-modal-foot {
                padding: 14px 20px;
                border-top: 1px solid #e2e8f0;
                background: #f8fafc;
                display: flex;
                justify-content: flex-end;
                gap: 10px;
                flex-shrink: 0;
            }

            /* Inventory form grid */
            .inv-form-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 14px 18px;
            }

            @media(max-width:680px) {
                .inv-form-grid {
                    grid-template-columns: repeat(2, 1fr);
                }
            }

            @media(max-width:440px) {
                .inv-form-grid {
                    grid-template-columns: 1fr;
                }
            }

            .inv-field label {
                display: block;
                font-size: 11px;
                font-weight: 700;
                color: #64748b;
                text-transform: uppercase;
                letter-spacing: .5px;
                margin-bottom: 5px;
            }

            .inv-req {
                color: #dc3545;
            }

            .inv-input-group {
                display: flex;
                align-items: center;
                border: 1.5px solid #e2e8f0;
                border-radius: 6px;
                overflow: hidden;
                background: #fff;
            }

            .inv-input-group:focus-within {
                border-color: #2563eb;
                box-shadow: 0 0 0 2px rgba(37, 99, 235, .10);
            }

            .inv-input-icon {
                display: flex;
                align-items: center;
                padding: 0 10px;
                background: #f8fafc;
                border-right: 1px solid #e2e8f0;
                color: #64748b;
                font-size: 14px;
                height: 36px;
                flex-shrink: 0;
            }

            .inv-input-group .inv-control {
                border: none !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                flex: 1;
            }

            .inv-control {
                width: 100%;
                border: 1.5px solid #e2e8f0;
                border-radius: 6px;
                font-size: 13px;
                padding: 7px 10px;
                color: #1e293b;
                background: #fff;
                transition: border-color .15s;
            }

            .inv-control:focus {
                border-color: #2563eb;
                outline: none;
                box-shadow: 0 0 0 2px rgba(37, 99, 235, .10);
            }

            .inv-select {
                appearance: auto;
            }

            .btn-inv-save {
                background: #16a34a;
                color: #fff;
                border: none;
                border-radius: 6px;
                padding: 9px 24px;
                font-size: 14px;
                font-weight: 700;
                cursor: pointer;
                transition: background .15s;
            }

            .btn-inv-save:hover {
                background: #15803d;
            }

            .btn-inv-cancel {
                background: #f1f5f9;
                color: #1e293b;
                border: 1px solid #e2e8f0;
                border-radius: 6px;
                padding: 9px 20px;
                font-size: 14px;
                font-weight: 600;
                cursor: pointer;
                transition: background .15s;
            }

            .btn-inv-cancel:hover {
                background: #e2e8f0;
            }
        </style>

        <!-- JS -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                let rowIndex = {{request()->has('item_id') ? 1 : (is_array(old('items')) ? count(old('items')) : count($Request_items))}};
                let tbody = document.getElementById('item_rows');

                // ✅ SELECT2 INIT
                function initItemSelect2(context = document) {
                    if (typeof $ === 'undefined' || !$.fn.select2) return;

                    $(context).find('.itemSelect').each(function() {
                        let $this = $(this);

                        if ($this.hasClass('select2-hidden-accessible')) return;

                        $this.select2({
                            placeholder: 'Search Item name or model',
                            width: '100%',
                            ajax: {
                                url: "{{ route('inventory.search') }}",
                                dataType: 'json',
                                delay: 250,
                                data: function(params) {
                                    return {
                                        q: params.term
                                    };
                                },
                                processResults: function(data) {
                                    return {
                                        results: data.results
                                    };
                                }
                            }
                        });

                        // ✅ OLD VALUE SET
                        let id = $this.data('id');
                        let text = $this.data('text');

                        if (id && text) {
                            let option = new Option(text, id, true, true);
                            $this.append(option).trigger('change');
                        }
                    });
                }

                // ✅ TOTAL CALCULATION
                function calculateTotal() {
                    let total = 0;

                    document.querySelectorAll('.request-qty').forEach(input => {
                        let val = parseFloat(input.value);
                        if (!isNaN(val)) total += val;
                    });

                    document.getElementById('total_request_qty').value = total;
                }

                // ✅ ADD ROW
                document.getElementById('addRow').addEventListener('click', function() {

                    let template = document.getElementById('item_row_template').content.cloneNode(true);

                    template.querySelector('.item-id').name = `items[${rowIndex}][item_id]`;
                    template.querySelector('.request-qty').name = `items[${rowIndex}][request_qty]`;
                    template.querySelector('.description').name = `items[${rowIndex}][description]`;
                    // template.querySelector('.required-date').name = `items[${rowIndex}][required_date]`;

                    tbody.appendChild(template);

                    let lastRow = tbody.lastElementChild;

                    initItemSelect2(lastRow); // select2 for new row
                    rowIndex++;

                    calculateTotal(); // update total
                });

                // ✅ REMOVE ROW
                document.addEventListener('click', function(e) {
                    if (e.target.classList.contains('remove-row')) {
                        e.target.closest('tr').remove();
                        calculateTotal();
                    }
                });

                // ✅ INPUT CHANGE (LIVE CALC)
                document.addEventListener('input', function(e) {
                    if (e.target.classList.contains('request-qty')) {
                        calculateTotal();
                    }
                });

                // ✅ DEFAULT ROW (if empty)
                if (tbody.children.length === 0) {
                    document.getElementById('addRow').click();
                }

                // ✅ INITIAL LOAD
                initItemSelect2(document);
                calculateTotal();

            });
        </script>

    @endsection