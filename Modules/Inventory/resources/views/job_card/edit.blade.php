@extends('shared::layouts.app')
@section('content')

    @php
    $items = old('items');
    $rowIndexCount = $jobCard->rows->count();
    @endphp

    <!-- PAGE HEADER -->
    <div class="page-header d-flex justify-content-between align-items-center">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Job card</h5>
            </div>

            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                <li class="breadcrumb-item active">Job card</li>
            </ul>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="card">
            <div class="card-body">

                @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
                @endif


                <form method="POST" action="{{ route('job_card.update', $jobCard->id) }}">
                    @csrf

                    <!-- META STRIP -->
                    <div class="po-meta-strip">
                        <div class="po-meta-item">
                            <span class="label"><i class="fa fa-calendar" style="margin-right:4px;"></i> Date</span>
                            <span class="value">{{ old('transaction_date', $jobCard->transaction_date) }}</span>
                            <input type="hidden" name="transaction_date" value="{{ old('transaction_date', $jobCard->transaction_date) }}">
                        </div>
                        <div class="po-meta-divider"></div>
                        <div class="po-meta-item">
                            <span class="label"><i class="fa fa-hashtag" style="margin-right:4px;"></i> JC No.</span>
                            <span class="value mono">{{ old('job_card_no', $jobCard->job_card_no) }}</span>
                            <input type="hidden" name="job_card_no" value="{{ old('job_card_no', $jobCard->job_card_no) }}">
                        </div>
                    </div>

                    <!-- TOP FORM -->
                    <div class="row g-4">

                        <div class="col-lg-3">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-control" data-select2-selector="status" disabled>
                                <option value="">Select Priority</option>
                                <option value="LOW" {{  old('priority',$jobCard->priority == 'LOW') ? 'selected' : '' }}>LOW</option>
                                <option value="NORMAL" {{  old('priority',$jobCard->priority == 'NORMAL') ? 'selected' : '' }}>NORMAL</option>
                                <option value="HIGH" {{ old('priority',$jobCard->priority == 'HIGH') ? 'selected' : '' }}>HIGH</option>
                                <option value="URGENT" {{ old('priority',$jobCard->priority == 'URGENT') ? 'selected' : '' }}>URGENT</option>
                            </select>
                            @if ($errors->has('priority'))
                            <div class="text-danger mt-1 small">
                                {{ $errors->first('priority') }}
                            </div>
                            @endif
                        </div>
                        <!-- Hidden field sends the value -->
                        <input type="hidden" name="priority" value="{{ $jobCard->priority }}">

                        <div class="col-lg-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control" data-select2-selector="status">
                                <option value="PENDING" {{ $jobCard->status == 'PENDING' ? 'selected' : '' }}>PENDING</option>
                            </select>
                        </div>

                        @if($jobCard->vendor_id)
                        <div class="col-lg-3">
                            <label class="form-label">Select Vendor</label>
                            <select name="vendor_id" class="form-control" data-select2-selector="status" disabled>
                                <option value="">-- Select Vendor --</option>
                                @foreach($vendors as $v)
                                <option value="{{ $v->id }}"
                                    {{ old('vendor_id', $jobCard->vendor_id) == $v->id ? 'selected' : '' }}>
                                    {{ $v->name }}
                                </option>
                                @endforeach
                            </select>

                            @if ($errors->has('vendor_id'))
                            <div class="text-danger mt-1 small">
                                {{ $errors->first('vendor_id') }}
                            </div>
                            @endif
                        </div>
                        <!-- Hidden field sends the value -->
                        <input type="hidden" name="vendor_id" value="{{ $jobCard->vendor_id }}">

                        @else

                        <div class="col-lg-3">
                            <label class="form-label">Select Employee</label>
                            <select name="employee" class="form-control" data-select2-selector="status" disabled>
                                <option value="">--select employee--</option>
                                @foreach($users as $user)
                                <option value="{{$user->id}}" {{ old('employee', $jobCard->employee_id) == $user->id ? 'selected' : '' }}>{{$user->name}}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('employee'))
                            <div class="text-danger mt-1 small">
                                {{ $errors->first('employee') }}
                            </div>
                            @endif
                        </div>
                        <!-- Hidden field sends the value -->
                        <input type="hidden" name="employee" value="{{ $jobCard->employee_id}}">
                        @endif

                        <div class="col-lg-3">
                            <label class="form-label">Completion Date</label>
                            <input type="date"
                                name="completion_date"
                                class="form-control completion_date"
                                value="{{old('completion_date',$jobCard->completion_date)}}" readonly>

                            @if ($errors->has('completion_date'))
                            <div class="text-danger mt-1 small">
                                {{ $errors->first('completion_date') }}
                            </div>
                            @endif
                        </div>

                    </div>

                    <!-- ITEMS TABLE -->
                    <div class="mt-4">
                        <div class="card">
                            <div class="card-body p-0">

                                <div class="table-responsive">
                                    <table class="table table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th style="min-width:280px;">Inventory Item</th>
                                                <th style="min-width:180px;">Quantity</th>
                                                <th style="min-width:180px;">pending Qty</th>
                                                <th style="min-width:180px;">Received Qty</th>
                                                <th style="min-width:200px;">Description</th>
                                                <th>Status</th>
                                                <th style="width:60px;"></th>
                                            </tr>
                                        </thead>

                                        <tbody id="item_rows">

                                            {{-- 1️⃣ OLD INPUT (validation error ke baad) --}}
                                            @if(is_array($items))
                                            @foreach($items as $index => $item)
                                            <tr>
                                                <input type="hidden"
                                                    name="items[{{ $index }}][row_id]"
                                                    value="{{ $item['row_id'] ?? '' }}">

                                                {{-- INVENTORY --}}
                                                <td>
                                                    <select class="form-select" name="items[{{ $index }}][item_id]" data-select2-selector="status" {{isset($item['row_id']) ? 'disabled' : ""}}>
                                                        <option value="">Select Item</option>
                                                        @foreach($inventories as $inv)
                                                        <option value="{{ $inv->id }}"
                                                            {{ ($item['item_id'] ?? '') == $inv->id ? 'selected' : '' }}>
                                                            {{ $inv->name }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                    @if(isset($item['row_id']) && $item['row_id'] != '')
                                                    <input type="hidden"
                                                        name="items[{{ $index }}][item_id]"
                                                        value="{{ $item['item_id'] ?? '' }}">
                                                    @endif

                                                    @if ($errors->has("items.$index.item_id"))
                                                    <div class="text-danger mt-1 small">
                                                        {{ $errors->first("items.$index.item_id") }}
                                                    </div>
                                                    @endif
                                                </td>

                                                {{-- REQUEST QTY --}}
                                                <td>
                                                    <input type="number"
                                                        name="items[{{ $index }}][request_qty]"
                                                        class="form-control request-qty"
                                                        value="{{ $item['request_qty'] ?? 0 }}"
                                                        {{ isset($item['row_id']) ? 'readonly' : '' }}>


                                                    @if ($errors->has("items.$index.request_qty"))
                                                    <div class="text-danger mt-1 small">
                                                        {{ $errors->first("items.$index.request_qty") }}
                                                    </div>
                                                    @endif
                                                </td>

                                                {{-- PENDING QTY --}}
                                                <td>
                                                    <input type="number"
                                                        name="items[{{ $index }}][item_pending_qty]"
                                                        class="form-control"
                                                        value="{{ $item['item_pending_qty'] ?? 0 }}"
                                                        readonly>
                                                </td>

                                                {{-- RECEIVED QTY --}}
                                                <td>
                                                    <input type="number"
                                                        name="items[{{ $index }}][received_qty]"
                                                        class="form-control received_qty"
                                                        value="{{ $item['received_qty'] ?? 0 }}"
                                                        data-max="{{ $item['item_pending_qty'] ?? 0 }}">
                                                    @error("items.$index.received_qty")
                                                    <div class="text-danger small">{{ $message }}</div>
                                                    @enderror
                                                </td>

                                                {{-- DESCRIPTION --}}
                                                <td>
                                                    <input type="text"
                                                        name="items[{{ $index }}][description]"
                                                        class="form-control"
                                                        value="{{ $item['description'] ?? '' }}">
                                                </td>

                                                {{-- STATUS --}}
                                                <td>
                                                    <select name="items[{{ $index }}][item_status]" data-select2-selector="status" class="form-select">
                                                        <option value="MACHINING"
                                                            {{ ($item['item_status'] ?? '') == 'MACHINING' ? 'selected' : '' }}>
                                                            MACHINING
                                                        </option>
                                                        <option value="COMPLETED"
                                                            {{ ($item['item_status'] ?? '') == 'COMPLETED' ? 'selected' : '' }}>
                                                            COMPLETED
                                                        </option>
                                                    </select>
                                                </td>

                                                <td>
                                                    <button type="button" class="btn btn-sm btn-danger remove-row">-</button>
                                                </td>
                                            </tr>
                                            @endforeach

                                            {{-- 2️⃣ NORMAL EDIT MODE (NO ERROR) --}}
                                            @else

                                            @foreach($jobCard->rows as $index => $row)
                                            <tr>

                                                <input type="hidden"
                                                    name="items[{{ $index }}][row_id]"
                                                    value="{{$row->id}}">
                                                <td>
                                                    <select class="form-select" data-select2-selector="status" disabled>
                                                        <option value="">Select Item</option>
                                                        @foreach($inventories as $inv)
                                                        <option value="{{ $inv->id }}"
                                                            {{ $row->item_id == $inv->id ? 'selected' : '' }}>
                                                            {{ $inv->name }}
                                                        </option>
                                                        @endforeach
                                                    </select>

                                                    <input type="hidden"
                                                        name="items[{{ $index }}][item_id]"
                                                        value="{{ $row->item_id }}">
                                                </td>

                                                <td>
                                                    <input type="number"
                                                        name="items[{{ $index }}][request_qty]"
                                                        class="form-control request-qty"
                                                        value="{{ $row->qty }}"
                                                        min="0"
                                                        readonly>
                                                </td>

                                                <td>
                                                    <input type="number"
                                                        name="items[{{ $index }}][item_pending_qty]"
                                                        class="form-control item_pending_qty"
                                                        value="{{ $row->item_pending_qty }}"
                                                        min="0"
                                                        readonly>
                                                </td>

                                                <td>
                                                    <input type="number"
                                                        name="items[{{ $index }}][received_qty]"
                                                        class="form-control received_qty"
                                                        min="0"
                                                        value="{{$row->item_pending_qty}}"
                                                        data-max="{{$row->item_pending_qty}}"
                                                        max="{{$row->item_pending_qty}}">
                                                </td>

                                                <td>
                                                    <input type="text"
                                                        name="items[{{ $index }}][description]"
                                                        class="form-control description"
                                                        value="{{ $row->description }}">
                                                </td>

                                                <td>
                                                    <select name="items[{{ $index }}][item_status]"
                                                        class="form-select item-status"
                                                        data-select2-selector="status">
                                                        <option value="MACHINING" {{ $row->status == 'MACHINING' ? 'selected' : '' }}>
                                                            MACHINING
                                                        </option>
                                                        <option value="COMPLETED" {{ $row->status == 'COMPLETE' ? 'selected' : '' }}>
                                                            COMPLETED
                                                        </option>
                                                    </select>
                                                </td>



                                                <td>
                                                    <button type="button" class="btn btn-sm btn-danger remove-row">-</button>
                                                </td>
                                            </tr>
                                            @endforeach
                                            @endif

                                        </tbody>

                                        <tfoot>
                                            <tr>
                                                <td class="text-end"><strong>Total Quantity:</strong></td>
                                                <td>
                                                    <input type="number" id="total_request_qty" name="total_qty" class="form-control" value="{{$jobCard->total_qty }}" readonly>
                                                </td>
                                                <td>
                                                    <input type="number" id="pending_qty" name="pending_qty" class="form-control" value="{{$jobCard->pending_qty }}" readonly>
                                                </td>
                                                <td>
                                                    <input type="number" id="total_received_qty" name="total_received_qty" class="form-control" value="{{ $jobCard->received_qty }}" readonly>
                                                </td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                @if($jobCard->status == "PENDING")
                                <div class="d-flex justify-content-between align-items-center px-4 py-3">
                                    <h5 class="mb-0">job Card Items</h5>
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="addRow">
                                        + Add Item
                                    </button>
                                </div>
                                @endif

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
                <select class="form-select item-id" data-select2-selector="status">
                    <option value="">Select Item</option>
                    @foreach($inventories as $inv)
                    <option value="{{ $inv->id }}">{{ $inv->name }}</option>
                    @endforeach
                </select>
            </td>

            <td>
                <input type="number" class="form-control request-qty" value="0" min="0">
            </td>
            <td>
                <input type="number" class="form-control item_pending_qty" value="0" min="0" readonly>
            </td>

            <td>
                <input type="number" class="form-control received_qty" value="0" min="0">
            </td>


            <td>
                <input type="text" class="form-control description" value="">
            </td>

            <td>
                <select class="form-select item-status" data-select2-selector="status">
                    <option value=" MACHINING"> MACHINING</option>
                    <option value="COMPLETED">COMPLETED</option>
                </select>
            </td>


            <td>
                <button type="button" class="btn btn-sm btn-danger remove-row">-</button>
            </td>
        </tr>
    </template>

    <!-- JS -->
    <script>
        let rowIndex = {{$rowIndexCount}};



        function calculateTotal() {
            let total = 0;
            document.querySelectorAll('.request-qty').forEach(input => {
                total += parseFloat(input.value) || 0;
            });
            document.getElementById('total_request_qty').value = total;
        }

        // Initial calculation
        calculateTotal();


        function calculateTotalReceived() {
            let total = 0;
            document.querySelectorAll('.received_qty').forEach(input => {
                let max = parseFloat(input.dataset.max) || Infinity;
                let value = parseFloat(input.value) || 0;
                total += Math.min(value, max);
            });
            document.getElementById('total_received_qty').value = total;
        }

        // Initial calculation
        calculateTotalReceived();


        // ADD ROW
        document.getElementById('addRow').addEventListener('click', function() {
            let template = document.getElementById('item_row_template').content.cloneNode(true);

            template.querySelector('.item-id').name = `items[${rowIndex}][item_id]`;
            template.querySelector('.request-qty').name = `items[${rowIndex}][request_qty]`;
            template.querySelector('.item_pending_qty').name = `items[${rowIndex}][item_pending_qty]`;
            template.querySelector('.received_qty').name = `items[${rowIndex}][received_qty]`;
            template.querySelector('.description').name = `items[${rowIndex}][description]`;
            template.querySelector('.item-status').name = `items[${rowIndex}][item_status]`;

            let tbody = document.getElementById('item_rows');
            tbody.appendChild(template);

            let lastRow = tbody.lastElementChild;

            // select2 init for new row
            $(lastRow).find('select[data-select2-selector="status"]').select2({
                width: '100%'
            });

            rowIndex++;
        });

        // REMOVE ROW
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-row')) {
                e.target.closest('tr').remove();
                calculateTotal();
            }
        });

        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('received_qty')) {
                let max = parseFloat(e.target.dataset.max);
                let value = parseFloat(e.target.value);

                if (value > max) {
                    e.target.value = max;
                }
            }
        });

        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('request-qty')) {
                calculateTotal();
            }
            if (e.target.classList.contains('received_qty')) {
                calculateTotalReceived();
            }
        });
    </script>

    <style>
        .po-meta-strip {
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

        .po-meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
        }

        .po-meta-item .label {
            color: #64748b;
            font-weight: 500;
            white-space: nowrap;
        }

        .po-meta-item .value {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 4px 12px;
            font-weight: 700;
            font-size: 13px;
            color: #1e293b;
            white-space: nowrap;
        }

        .po-meta-item .value.mono {
            color: #2563eb;
            letter-spacing: .5px;
        }

        .po-meta-divider {
            width: 1px;
            height: 28px;
            background: #e2e8f0;
            flex-shrink: 0;
        }
    </style>
@endsection