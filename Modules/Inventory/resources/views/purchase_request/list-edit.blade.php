@extends('shared::layouts.app')
@section('content')
    @php
    $olditems =  $oldItems ?? null;
    @endphp
    <!-- PAGE HEADER -->
    <div class="page-header d-flex justify-content-between align-items-center">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Edit Purchase Request</h5>
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

                <form method="POST" action="{{ route('purchase_request.update', $purchaseRequest->id) }}">
                    @csrf

                    <!-- TOP FORM -->
                    <div class="row g-4">

                        <div class="col-lg-3">
                            <label class="form-label">Request Date</label>
                            <input type="date" name="request_date"
                                class="form-control"
                                value="{{ $purchaseRequest->request_date}}" required>
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label">Purchase Request No</label>
                            <input type="text" name="purchase_request_no"
                                class="form-control"
                                value="{{ $purchaseRequest->pr_no }}" required readonly>
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-control" data-select2-selector="status">
                                <option value="">Select Priority</option>
                                <option value="LOW" {{ $purchaseRequest->priority == 'LOW' ? 'selected' : '' }}>LOW</option>
                                <option value="NORMAL" {{ $purchaseRequest->priority == 'NORMAL' ? 'selected' : '' }}>NORMAL</option>
                                <option value="HIGH" {{ $purchaseRequest->priority == 'HIGH' ? 'selected' : '' }}>HIGH</option>
                                <option value="URGENT" {{ $purchaseRequest->priority == 'URGENT' ? 'selected' : '' }}>URGENT</option>
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
                                <option value="DRAFT" {{ $purchaseRequest->status == 'DRAFT' ? 'selected' : '' }}>DRAFT</option>
                                <option value="SUBMITTED" {{ $purchaseRequest->status == 'SUBMITTED' ? 'selected' : '' }}>SUBMITTED</option>

                            </select>
                        </div>

                        <div class="col-lg-6">
                            <label class="form-label">Comment</label>
                            <textarea name="comment" class="form-control" rows="1">{{ $purchaseRequest->remarks }}</textarea>
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
                                                <th>Required date</th>
                                                <th style="width:60px;"></th>
                                            </tr>
                                        </thead>

                                        <tbody id="item_rows">


                                            {{-- 1️⃣ OLD INPUT (validation fail ke baad) --}}
                                            @if(is_array($olditems))

                                            @foreach($olditems as $index => $item)
                                            <tr>
                                                {{-- ITEM --}}
                                                <input type="hidden"
                                                    name="items[{{ $index }}][issue_slip_row_id]"
                                                    value="{{ $item['issue_slip_row_id'] ?? '' }}">

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

                                                    @if ($errors->has("items.$index.item_id"))
                                                    <div class="text-danger small mt-1">
                                                        {{ $errors->first("items.$index.item_id") }}
                                                    </div>
                                                    @endif
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
                                                <td>
                                                    <input type="date"
                                                        name="items[{{ $index }}][required_date]"
                                                        class="form-control"
                                                        value="{{ $item['required_date'] ?? '' }}">

                                                    @if ($errors->has("items.$index.required_date"))
                                                    <div class="text-danger small mt-1">
                                                        {{ $errors->first("items.$index.required_date") }}
                                                    </div>
                                                    @endif
                                                </td>

                                                <td>
                                                    <button type="button" class="btn btn-sm btn-danger remove-row">-</button>
                                                </td>
                                            </tr>
                                            @endforeach

                                            @else

                                            {{-- EXISTING ITEMS --}}
                                            @foreach($items as $index => $row)
                                            <tr>
                                                <input type="hidden"
                                                    name="items[{{ $index }}][issue_slip_row_id]"
                                                    value="{{ $row->issue_slip_row_id }}">

                                                <input type="hidden"
                                                    name="items[{{ $index }}][row_id]"
                                                    value="{{$row->id}}">
                                                <td>
                                                    <select class="form-select" data-select2-selector="status" disabled>
                                                        <option value="">Select Item</option>
                                                        <option value="{{ $row->inventory->id }}" selected>
                                                           {{ $row->inventory->name }} {{ $row->inventory->model }}
                                                        </option>
                                                    </select>
                                                    <!-- Hidden input to submit value -->
                                                    <input type="hidden" name="items[{{ $index }}][item_id]" value="{{ $row->inventory->id}}">
                                                </td>

                                                <td>
                                                    <input type="number"
                                                        name="items[{{ $index }}][request_qty]"
                                                        class="form-control request-qty"
                                                        value="{{ $row->requested_qty }}"
                                                        min="0">
                                                </td>

                                                <input type="hidden" name="items[{{ $index }}][order_qty]" value="{{ $row->requested_qty}}">


                                                <td>
                                                    <input type="text"
                                                        name="items[{{ $index }}][description]"
                                                        class="form-control description"
                                                        value="{{$row->description}}">
                                                </td>

                                                <td>
                                                    <input type="date" name="items[{{ $index }}][required_date]" value="{{$row->required_date}}" class="form-control required-date">
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

            <td>
                <input type="date" class="form-control required-date">
            </td>

            <td>
                <button type="button" class="btn btn-sm btn-danger remove-row">-</button>
            </td>
        </tr>
    </template>

    <!-- JS -->
     
     <script>
     document.addEventListener('DOMContentLoaded', function () {

    let rowIndex = {{ count($items) }};
    let tbody = document.getElementById('item_rows');

    // ✅ SELECT2 INIT
    function initItemSelect2(context = document) {
        if (typeof $ === 'undefined' || !$.fn.select2) return;

        $(context).find('.itemSelect').each(function () {
            let $this = $(this);

            if ($this.hasClass('select2-hidden-accessible')) return;

            $this.select2({
                placeholder: 'Search Item name or model',
                width: '100%',
                ajax: {
                    url: "{{ route('inventory.search') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { q: params.term };
                    },
                    processResults: function (data) {
                        return { results: data.results };
                    }
                }
            });

            // ✅ OLD VALUE SET (validation case)
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
    document.getElementById('addRow').addEventListener('click', function () {

        let template = document.getElementById('item_row_template').content.cloneNode(true);

        template.querySelector('.item-id').name = `items[${rowIndex}][item_id]`;
        template.querySelector('.request-qty').name = `items[${rowIndex}][request_qty]`;
        template.querySelector('.description').name = `items[${rowIndex}][description]`;
        template.querySelector('.required-date').name = `items[${rowIndex}][required_date]`;

        tbody.appendChild(template);

        let lastRow = tbody.lastElementChild;
        initItemSelect2(lastRow);

        rowIndex++;

        calculateTotal(); // ✅ important
    });

    // ✅ REMOVE ROW
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-row')) {
            e.target.closest('tr').remove();
            calculateTotal(); // ✅ update after delete
        }
    });

    // ✅ INPUT CHANGE (LIVE UPDATE)
    document.addEventListener('input', function (e) {
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
    calculateTotal(); // 🔥 VERY IMPORTANT

});
    </script>

@endsection