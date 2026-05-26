@extends('shared::layouts.app')
@section('content')
    @php
    $items = old('items') ;
    @endphp
    <!-- PAGE HEADER -->
    <div class="page-header d-flex justify-content-between align-items-center">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Job cart</h5>
            </div>

            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                <li class="breadcrumb-item active">Job cart</li>
            </ul>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="card">
            <div class="card-body">

                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error!</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
                @endif


                <form method="POST" action="{{ route('job_card.store') }}">
                    @csrf
                    <!-- META STRIP -->
                    <div class="po-meta-strip">
                        <div class="po-meta-item">
                            <span class="label"><i class="fa fa-calendar" style="margin-right:4px;"></i> Date</span>
                            <span class="value">{{ old('transaction_date', $today) }}</span>
                            <input type="hidden" name="transaction_date" value="{{ old('transaction_date', $today) }}">
                        </div>
                        <div class="po-meta-divider"></div>
                        <div class="po-meta-item">
                            <span class="label"><i class="fa fa-hashtag" style="margin-right:4px;"></i> JC No.</span>
                            <span class="value mono">{{ old('job_card_no', $nextPrNo) }}</span>
                            <input type="hidden" name="job_card_no" value="{{ old('job_card_no', $nextPrNo) }}">
                        </div>
                    </div>

                    <!-- TOP FORM -->
                    <div class="row g-4">

                        <div class="col-lg-3">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-control" data-select2-selector="status">
                                <option value="NORMAL" {{ old('priority') == 'NORMAL' ? 'selected' : '' }}>NORMAL</option>
                                <option value="LOW" {{ old('priority') == 'LOW' ? 'selected' : '' }}>LOW</option>
                                <option value="HIGH" {{ old('priority') == 'HIGH' ? 'selected' : '' }}>HIGH</option>
                                <option value="URGENT" {{ old('priority') == 'URGENT' ? 'selected' : '' }}>URGENT</option>
                            </select>
                            @if ($errors->has('priority'))
                            <div class="text-danger mt-1 small">
                                {{ $errors->first('priority') }}
                            </div>
                            @endif
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label">Job Type</label>
                            <select name="job_type" id="job_type" class="form-control">
                                <option value="out_source" {{ old('job_type') == 'out_source' ? 'selected' : '' }}>Out-Source</option>
                                <option value="in_house" {{ old('job_type') == 'in_house' ? 'selected' : '' }}>In-House</option>
                            </select>
                            @if ($errors->has('job_type'))
                            <div class="text-danger mt-1 small">
                                {{ $errors->first('job_type') }}
                            </div>
                            @endif
                        </div>

                        <div class="col-lg-3" id="vendor_div" style="display:none;">
                            <label class="form-label">Select Vendor</label>
                            <select name="vendor" class="form-control" data-select2-selector="status">
                                <option value="">--select vendor --</option>
                                @foreach($vendor as $v)
                                <option value="{{$v->id}}" {{ old('vendor') == $v->id ? 'selected' : '' }}>{{$v->name}}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('vendor'))
                            <div class="text-danger mt-1 small">
                                {{ $errors->first('vendor') }}
                            </div>
                            @endif
                        </div>

                        <div class="col-lg-3" id="employee_div" style="display:none;">
                            <label class="form-label">Select Employee</label>
                            <select name="employee" class="form-control" data-select2-selector="status">
                                <option value="">--select employee--</option>
                                @foreach($users as $user)
                                <option value="{{$user->id}}" {{ old('employee',$emp_id) == $user->id ? 'selected' : '' }}>{{$user->name}}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('employee'))
                            <div class="text-danger mt-1 small">
                                {{ $errors->first('employee') }}
                            </div>
                            @endif
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label">Expected Date</label>
                            <input type="date" name="completion_date" class="form-control completion_date" value="{{old('completion_date')}}">

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
                                                <th style="min-width:140px;">Quantity</th>
                                                <th style="min-width:140px;">Available Stock</th>

                                                <th>Description</th>
                                                <th>Supplier</th>
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
                                                        <option value="{{$inv->id }}"
                                                            {{ ($item['item_id']) == $inv->id ? 'selected' : '' }}>
                                                            {{ $inv->name }}
                                                        </option>
                                                        @endforeach
                                                    </select>

                                                    @if(isset($item['row_id']) && $item['row_id'] != '')
                                                    <input type="hidden"
                                                        name="items[{{ $index }}][item_id]"
                                                        value="{{ $item['item_id'] }}">
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
                                                        value="1"
                                                        max="{{$item['available_stock'] ?? 0}}"
                                                        min="0" readonly>


                                                    @if ($errors->has("items.$index.request_qty"))
                                                    <div class="text-danger mt-1 small">
                                                        {{ $errors->first("items.$index.request_qty") }}
                                                    </div>
                                                    @endif
                                                </td>

                                                <td>
                                                    <input type="number"
                                                        name="items[{{$index}}][available_stock]"
                                                        class="form-control"
                                                        value="{{$item['available_stock'] ?? 0}}" readonly>
                                                </td>



                                                {{-- DESCRIPTION --}}
                                                <td>
                                                    <input type="text"
                                                        name="items[{{ $index }}][description]"
                                                        class="form-control"
                                                        value="{{ $item['description'] ?? '' }}">
                                                </td>

                                                <td>
                                                    <select name="items[{{ $index }}][supplier]"
                                                        class="form-select"
                                                        data-select2-selector="status" {{ $item['supplier']  ? 'disabled' : '' }}>
                                                        <option value="">Select Supplier</option>
                                                        @foreach($suppliers as $s)
                                                        <option value="{{ $s->id }}"
                                                            {{ ($item['supplier'] ?? '') == $s->id ? 'selected' : '' }}>
                                                            {{ $s->supplier_code }}
                                                        </option>
                                                        @endforeach
                                                    </select>

                                                    @if(!empty($item['supplier']))
                                                    <input type="hidden"
                                                        name="items[{{ $index }}][supplier]"
                                                        value="{{ $item['supplier'] }}">
                                                    @endif


                                                    @if ($errors->has("items.$index.supplier"))
                                                    <div class="text-danger mt-1 small">
                                                        {{ $errors->first("items.$index.supplier") }}
                                                    </div>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach

                                            {{-- 2️⃣ NORMAL EDIT MODE (NO ERROR) --}}
                                            @else


                                            {{-- EXISTING ITEMS --}}
                                            @foreach($Request_items as $index => $row)
                                            <tr>

                                                <input type="hidden"
                                                    name="items[{{ $index }}][row_id]"
                                                    value="{{ $row->id ?? '' }}">

                                                <td>
                                                    <select class="form-select" data-select2-selector="status" disabled>
                                                        <option value="">Select Item</option>
                                                        @foreach($inventories as $inv)
                                                        <option value="{{ $inv->id }}" {{ $row->item_id == $inv->id ? 'selected' : '' }}> {{ $inv->name }} </option>
                                                        @endforeach
                                                    </select>
                                                    <input type="hidden" name="items[{{ $index }}][item_id]" value="{{ $row->item_id }}">

                                                </td>
                                                <td>
                                                    <input type="number" name="items[{{ $index }}][request_qty]" class="form-control request-qty" value="1" max="{{$row->available_stock }}" min="0" readonly>
                                                </td>
                                                <td>
                                                    <input type="number"
                                                        name="items[{{$index}}][available_stock]"
                                                        class="form-control"
                                                        value="{{ $row->available_stock }}"
                                                        readonly>
                                                </td>
                                                <td>
                                                    <input type="text" name="items[{{ $index }}][description]" class="form-control description" value="">
                                                </td>
                                                <td>
                                                    <select name="items[{{ $index }}][supplier]" class="form-select" data-select2-selector="status" {{ $row->supplier_id  ? 'disabled' : '' }}>
                                                        <option value="">Select Supplier</option>
                                                        @foreach($suppliers as $s)
                                                        <option value="{{ $s->id }}" {{ $row->supplier_id == $s->id ? 'selected' : '' }}> {{ $s->supplier_code }} </option>
                                                        @endforeach

                                                        @if($row->supplier_id)
                                                        <input type="hidden"
                                                            name="items[{{ $index }}][supplier]"
                                                            value="{{ $row->supplier_id }}">
                                                        @endif

                                                        @if ($errors->has("items.$index.supplier"))
                                                        <div class="text-danger mt-1 small">
                                                            {{ $errors->first("items.$index.supplier") }}
                                                        </div>
                                                        @endif
                                                    </select>
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
                        <button class="btn btn-primary">Save Job Cart</button>
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
                <input type="number" class="form-control request-qty" value="1" min="0" readonly>
            </td>

            <td>
                <input type="number" class="form-control available-stock" name="available_stock" value="0" readonly>
            </td>

            <td>
                <input type="text" class="form-control description" value="">
            </td>


            <td>
                <select class="form-select item-status" data-select2-selector="status">
                    <option value="">--Select Supplier--</option>
                    @foreach($suppliers as $s)
                    <option value="{{ $s->id}}">{{ $s->supplier_name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger remove-row">-</button>
            </td>
        </tr>
    </template>

    <!-- JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let rowIndex = @json(count($Request_items));

            function calculateTotal() {
                let total = 0;
                document.querySelectorAll('.request-qty').forEach(input => {
                    total += parseFloat(input.value) || 0;
                });
                document.getElementById('total_request_qty').value = total;
            }

            function toggleAddButton() {
                let rowCount = document.querySelectorAll('#item_rows tr').length;
                let addBtn = document.getElementById('addRow');

                if (rowCount === 0 && rowIndex === 0) {
                    addItemRow() // hide when at least 1 item
                }
            }


            // Initial calculation
            calculateTotal();
            toggleAddButton();


            // ADD ROW
            function addItemRow() {

                let template = document
                    .getElementById('item_row_template')
                    .content.cloneNode(true);

                template.querySelector('.item-id').name = `items[${rowIndex}][item_id]`;
                template.querySelector('.request-qty').name = `items[${rowIndex}][request_qty]`;
                template.querySelector('.available-stock').name = `items[${rowIndex}][available_stock]`;
                template.querySelector('.description').name = `items[${rowIndex}][description]`;
                template.querySelector('.item-status').name = `items[${rowIndex}][item_status]`;

                let tbody = document.getElementById('item_rows');
                tbody.appendChild(template);

                let row = tbody.lastElementChild;

                $(row).find('select[data-select2-selector="status"]').select2({
                    width: '100%'
                });

                $(row).find('.item-id').on('select2:select', function(e) {

                    let inventoryId = this.value;

                    let stockInput = row.querySelector('.available-stock');
                    let qtyInput = row.querySelector('.request-qty');

                    if (!inventoryId) {
                        stockInput.value = 0;
                        qtyInput.max = 0;
                        return;
                    }

                    fetch(`/inventory/available-stock/${inventoryId}`)
                        .then(res => res.json())
                        .then(data => {
                            stockInput.value = data.available_stock;
                            qtyInput.max = data.available_stock;

                            if (parseFloat(qtyInput.value) > data.available_stock) {
                                qtyInput.value = data.available_stock;
                            }

                            calculateTotal();
                        })
                        .catch(() => {
                            stockInput.value = 0;
                            qtyInput.max = 0;
                        });
                });

                rowIndex++;
                toggleAddButton();

            };

            // REMOVE ROW
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-row')) {
                    e.target.closest('tr').remove();
                    calculateTotal();
                    toggleAddButton();

                }
            });


            // REQUEST QTY CHANGE
            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('request-qty')) {

                    let max = parseFloat(e.target.max) || 0;
                    let val = parseFloat(e.target.value) || 0;

                    if (val > max) {
                        e.target.value = max;
                    }

                    calculateTotal();
                }
            });

            function toggleJobTypeDropdown() {
                const jobType = document.getElementById('job_type').value;
                const vendorDiv = document.getElementById('vendor_div');
                const employeeDiv = document.getElementById('employee_div');

                if (jobType === 'in_house') {
                    // In-House → Employee
                    vendorDiv.style.display = 'none';
                    employeeDiv.style.display = 'block';
                } else if (jobType === 'out_source') {
                    // Out-Source → Vendor
                    vendorDiv.style.display = 'block';
                    employeeDiv.style.display = 'none';
                } else {
                    vendorDiv.style.display = 'none';
                    employeeDiv.style.display = 'none';
                }
            }

            // Initial check on page load
            toggleJobTypeDropdown();

            // Trigger toggle when job type changes
            document.getElementById('job_type').addEventListener('change', toggleJobTypeDropdown);

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