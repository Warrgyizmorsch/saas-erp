@extends('shared::layouts.app')
@section('content')
    @php
        $items = $oldItems ?? null;
        $isDraft = $PurchaseOrder->status === 'Draft';
    @endphp

    <style>
        :root {
            --po-blue:       #2563eb;
            --po-blue-light: #eff6ff;
            --po-blue-dark:  #1d4ed8;
            --po-border:     #e2e8f0;
            --po-bg:         #f8fafc;
            --po-surface:    #ffffff;
            --po-text:       #1e293b;
            --po-muted:      #64748b;
            --po-danger:     #ef4444;
            --po-badge-bg:   #f1f5f9;
            --radius-sm:     6px;
            --radius-md:     10px;
            --radius-lg:     14px;
            --shadow-sm:     0 1px 3px rgba(0,0,0,.08);
        }

        .po-page-header {
            display: flex; align-items: center; gap: 12px;
            padding: 18px 0 12px;
            border-bottom: 1px solid var(--po-border);
            margin-bottom: 20px;
        }
        .po-page-header .po-icon {
            width: 40px; height: 40px; background: var(--po-blue);
            border-radius: var(--radius-md); display: grid;
            place-items: center; color: #fff; font-size: 18px; flex-shrink: 0;
        }
        .po-page-header h5 { margin: 0; font-size: 17px; font-weight: 700; color: var(--po-text); }
        .po-breadcrumb { margin: 0; padding: 0; list-style: none; display: flex; gap: 6px; font-size: 12px; color: var(--po-muted); }
        .po-breadcrumb li + li::before { content: '/'; margin-right: 6px; }
        .po-breadcrumb a { color: var(--po-blue); text-decoration: none; }

        .po-status-badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 10px; border-radius: 20px;
            font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px;
        }
        .po-status-badge.draft     { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
        .po-status-badge.submitted { background: #dcfce7; color: #166534; border: 1px solid #86efac; }

        .po-card { background: var(--po-surface); border: 1px solid var(--po-border); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); overflow: hidden; }
        .po-card-body { padding: 24px; }

        .po-meta-strip {
            display: flex; align-items: center; gap: 10px;
            background: var(--po-badge-bg); border: 1px solid var(--po-border);
            border-radius: var(--radius-md); padding: 10px 16px;
            margin-bottom: 22px; flex-wrap: wrap;
        }
        .po-meta-item { display: flex; align-items: center; gap: 6px; font-size: 13px; }
        .po-meta-item .label { color: var(--po-muted); font-weight: 500; white-space: nowrap; }
        .po-meta-item .value {
            background: var(--po-surface); border: 1px solid var(--po-border);
            border-radius: var(--radius-sm); padding: 4px 12px;
            font-weight: 700; font-size: 13px; color: var(--po-text); white-space: nowrap;
        }
        .po-meta-item .value.mono { /*font-family: 'Courier New', monospace;*/ color: var(--po-blue); letter-spacing: .5px; }
        .po-meta-divider { width: 1px; height: 28px; background: var(--po-border); flex-shrink: 0; }

        .po-fields-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px 20px; margin-bottom: 24px; }
        @media (max-width: 1200px) { .po-fields-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 640px)  { .po-fields-grid { grid-template-columns: 1fr; } }

        .po-field label { display: block; font-size: 12px; font-weight: 600; color: var(--po-muted); text-transform: uppercase; letter-spacing: .5px; margin-bottom: 6px; }
        .po-field .form-control,
        .po-field .form-select {
            border: 1.5px solid var(--po-border); border-radius: var(--radius-sm);
            font-size: 13.5px; padding: 8px 12px; color: var(--po-text);
            width: 100%; transition: border-color .15s; background: var(--po-surface);
        }
        .po-field .form-control:focus, .po-field .form-select:focus { border-color: var(--po-blue); outline: none; box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
        .po-field .form-control[readonly], .po-field .form-control:disabled, .po-field .form-select:disabled { background: var(--po-badge-bg); color: var(--po-muted); cursor: default; }
        .po-field .error-msg { font-size: 11px; color: var(--po-danger); margin-top: 4px; }

        .po-items-section { border: 1px solid var(--po-border); border-radius: var(--radius-md); overflow: hidden; margin-bottom: 24px; }
        .po-items-header { background: var(--po-badge-bg); border-bottom: 1px solid var(--po-border); padding: 10px 16px; display: flex; justify-content: space-between; align-items: center; }
        .po-items-header span { font-size: 13px; font-weight: 700; color: var(--po-text); text-transform: uppercase; letter-spacing: .5px; }
        .btn-add-row { background: var(--po-blue-light); color: var(--po-blue); border: 1.5px solid var(--po-blue); border-radius: var(--radius-sm); padding: 5px 14px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all .15s; }
        .btn-add-row:hover { background: var(--po-blue); color: #fff; }
        .btn-add-row:disabled { opacity: .45; cursor: not-allowed; pointer-events: none; }

        .po-table-wrap { overflow-x: auto; }
        .po-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
        .po-table thead th { background: #f8fafc; color: var(--po-muted); font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; padding: 10px 8px; border-bottom: 2px solid var(--po-border); white-space: nowrap; text-align: left; }
        .po-table tbody tr { border-bottom: 1px solid var(--po-border); transition: background .1s; }
        .po-table tbody tr:hover { background: #fafcff; }
        .po-table tbody td { padding: 8px 6px; vertical-align: top; }
        .po-table tfoot td { padding: 8px 6px; background: var(--po-badge-bg); border-top: 2px solid var(--po-border); }

        .po-table .form-control, .po-table .form-select { border: 1.5px solid var(--po-border); border-radius: var(--radius-sm); font-size: 12.5px; padding: 5px 8px; width: 100%; background: var(--po-surface); color: var(--po-text); transition: border-color .15s; }
        .po-table .form-control:focus, .po-table .form-select:focus { border-color: var(--po-blue); outline: none; box-shadow: 0 0 0 2px rgba(37,99,235,.10); }
        .po-table .form-control[readonly], .po-table .form-control:disabled, .po-table .form-select:disabled { background: var(--po-badge-bg); color: var(--po-muted); cursor: default; }
        .po-table textarea.form-control { resize: vertical; min-height: 38px; }

        .col-item { min-width: 200px; width: 200px; } .col-hsn { min-width: 110px; width: 110px; }
        .col-qty  { min-width: 90px;  width: 90px;  } .col-price { min-width: 100px; width: 100px; }
        .col-taxsub { min-width: 110px; width: 110px; } .col-disc { min-width: 80px; width: 80px; }
        .col-amt { min-width: 110px; width: 110px; } .col-taxtype { min-width: 90px; width: 90px; }
        .col-tax { min-width: 75px; width: 75px; } .col-taxamt { min-width: 100px; width: 100px; }
        .col-total { min-width: 110px; width: 110px; } .col-del { min-width: 40px; width: 40px; text-align: center; }

        .btn-remove { background: #fee2e2; color: var(--po-danger); border: 1px solid #fca5a5; border-radius: var(--radius-sm); width: 28px; height: 28px; display: grid; place-items: center; cursor: pointer; font-size: 16px; font-weight: 700; transition: all .15s; }
        .btn-remove:hover { background: var(--po-danger); color: #fff; }

        .po-table tfoot .foot-label { font-size: 11px; font-weight: 700; color: var(--po-muted); text-transform: uppercase; text-align: right; white-space: nowrap; padding-right: 8px; }

        .item-note-toggle { display: inline-flex; align-items: center; gap: 4px; margin-top: 5px; font-size: 11px; font-weight: 600; color: var(--po-blue); cursor: pointer; user-select: none; background: var(--po-blue-light); border: 1px solid #bfdbfe; border-radius: 20px; padding: 2px 8px 2px 6px; transition: background .15s; width: fit-content; }
        .item-note-toggle:hover { background: #dbeafe; }
        .item-note-toggle .plus-icon { font-size: 14px; font-weight: 700; line-height: 1; color: var(--po-blue); transition: transform .2s; }
        .item-note-toggle.open .plus-icon { transform: rotate(45deg); }
        .item-note-wrap { display: none; margin-top: 5px; }
        .item-note-wrap.open { display: block; }

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
        .po-terms label { font-size: 12px; font-weight: 700; color: var(--po-muted); text-transform: uppercase; letter-spacing: .5px; margin-bottom: 8px; display: block; }

        .po-totals-card { border: 1.5px solid var(--po-border); border-radius: var(--radius-md); overflow: hidden; }
        .po-totals-card .tc-head { background: var(--po-blue); color: #fff; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; padding: 10px 16px; }
        .po-totals-card .tc-body { padding: 16px; }
        .tc-row { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 7px 0; border-bottom: 1px solid var(--po-border); }
        .tc-row:last-child { border-bottom: none; }
        .tc-row .tc-label { font-size: 12.5px; color: var(--po-muted); font-weight: 500; white-space: nowrap; }
        .tc-row .tc-input { width: 160px; flex-shrink: 0; }
        .tc-row .tc-input input { width: 100%; border: 1.5px solid var(--po-border); border-radius: var(--radius-sm); padding: 6px 10px; font-size: 13px; text-align: right; background: var(--po-badge-bg); color: var(--po-text); font-weight: 600; }
        .tc-row .tc-input input:not([readonly]) { background: var(--po-surface); }
        .tc-row .tc-input input:not([readonly]):focus { border-color: var(--po-blue); outline: none; box-shadow: 0 0 0 2px rgba(37,99,235,.10); }
        .tc-divider { border: none; border-top: 2px solid var(--po-border); margin: 8px 0; }
        .tc-row.old-advance .tc-label { color: #92400e; }
        .tc-row.old-advance .tc-input input { background: #fef3c7; color: #92400e; border-color: #fcd34d; }
        .tc-row.balance .tc-label { font-weight: 800; font-size: 14px; color: var(--po-blue); }
        .tc-row.balance .tc-input input { background: var(--po-blue-light); border-color: var(--po-blue); color: var(--po-blue); font-size: 15px; font-weight: 800; }

        .po-save-bar { display: flex; align-items: center; justify-content: flex-end; gap: 12px; padding: 16px 24px; border-top: 1px solid var(--po-border); background: var(--po-badge-bg); }
        .po-save-bar .status-wrap { display: flex; align-items: center; gap: 8px; }
        .po-save-bar .status-wrap label { font-size: 12px; font-weight: 700; color: var(--po-muted); text-transform: uppercase; letter-spacing: .5px; white-space: nowrap; }
        .po-save-bar .status-wrap select { border: 1.5px solid var(--po-border); border-radius: var(--radius-sm); padding: 7px 12px; font-size: 13px; font-weight: 600; color: var(--po-text); background: var(--po-surface); min-width: 140px; }
        .po-save-bar .status-wrap select:disabled { background: var(--po-badge-bg); color: var(--po-muted); }
        .btn-save-po { background: var(--po-blue); color: #fff; border: none; border-radius: var(--radius-sm); padding: 9px 28px; font-size: 14px; font-weight: 700; cursor: pointer; transition: background .15s; }
        .btn-save-po:hover { background: var(--po-blue-dark); }

        .select2-container { z-index: 1; }
         .ck.ck-editor{
            height: 100%;
        }
        .ck-editor__main{
         height: 80%;
        }
        .ck-editor__editable_inline {
            height: 100%;
                }
    </style>

    <!-- PAGE HEADER -->
    <div class="page-header d-flex justify-content-between align-items-center">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">purchase order </h5>
            </div>

            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                <li class="breadcrumb-item active">purchase order edit</li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <div class="po-card">

            <form method="POST" action="{{ route('purchase-order.update', $PurchaseOrder->id) }}">
                @csrf     

                <div class="po-card-body">

                    <!-- META STRIP -->
                    <div class="po-meta-strip">
                        <div class="po-meta-item">
                            <span class="label"><i class="fa fa-calendar" style="margin-right:4px;"></i>Date</span>
                            <span class="value">{{ $today }}</span>
                            <input type="hidden" name="purchase_order_date" value="{{ $today }}">
                        </div>
                        <div class="po-meta-divider"></div>
                        <div class="po-meta-item">
                            <span class="label"><i class="fa fa-hashtag" style="margin-right:4px;"></i>PO No.</span>
                            <span class="value mono" id="poNumberText">{{ $PurchaseOrder->po_number }}</span>
                            <input type="hidden" name="purchase_order_no" id="poNumberInput" value="{{ $PurchaseOrder->po_number }}">

                            <input type="hidden" id="mhelPo" value="{{ $MHELNextPO }}">
                            <input type="hidden" id="mtplPo" value="{{ $MTPLNextPO }}">
                        </div>
                    </div>

                    <!-- FORM FIELDS -->
                    <div class="po-fields-grid">

                        <div class="po-field">
                            <label>Select Supplier</label>
                            <select name="supplier" id="supplier-select" class="form-control" {{ !$isDraft ? 'disabled' : '' }}></select>
                            @if($errors->has('supplier'))
                                <div class="error-msg">{{ $errors->first('supplier') }}</div>
                            @endif
                        </div>

                        <div class="po-field">
                            <label>Select Firm</label>
                            <select name="firm" id="firmSelect" class="form-select" data-select2-selector="status" {{ !$isDraft ? 'disabled' : '' }}>
                                <option value="">-- Select Firm --</option>
                                @foreach($Firms as $f)
                                    <option value="{{ $f->id }}" {{ old('firm', $PurchaseOrder->firm) == $f->id ? 'selected' : '' }}>{{ $f->name }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('firm'))
                                <div class="error-msg">{{ $errors->first('firm') }}</div>
                            @endif
                        </div>

                        <div class="po-field">
                            <label>Expected Delivery</label>
                            <input type="date" name="expected_delivery_date" class="form-control"
                                value="{{ old('expected_delivery_date', $PurchaseOrder->expected_delivery) }}"
                                {{ !$isDraft ? 'readonly' : '' }}>
                            @if($errors->has('expected_delivery_date'))
                                <div class="error-msg">{{ $errors->first('expected_delivery_date') }}</div>
                            @endif
                        </div>

                        <div class="po-field">
                            <label>Quotation Number</label>
                            <input type="text" name="quotation_number" class="form-control"
                                value="{{ old('quotation_number', $PurchaseOrder->remarks) }}"
                                placeholder="e.g. QT-2024-001"
                                {{ !$isDraft ? 'readonly' : '' }}>
                            @if($errors->has('quotation_number'))
                                <div class="error-msg">{{ $errors->first('quotation_number') }}</div>
                            @endif
                        </div>

                    </div>

                    <!-- ITEMS TABLE -->
                    <div class="po-items-section">
                        <div class="po-items-header">
                            <span><i class="fa fa-list" style="margin-right:6px;"></i>Order Items</span>
                            <button type="button" class="btn-add-row" id="addRow" {{ !$isDraft ? 'disabled' : '' }}>+ Add Item</button>
                        </div>

                        <div class="po-table-wrap">
                            <table class="po-table">
                                <thead>
                                    <tr>
                                        <th class="col-item">Inventory Item</th>
                                        <th class="col-hsn">HSN Code</th>
                                        <th class="col-qty">Qty</th>
                                        <th class="col-price">Price</th>
                                        <th class="col-taxsub">Taxable Total</th>
                                        <th class="col-disc">Disc %</th>
                                        <th class="col-amt">Amount</th>
                                        <th class="col-taxtype">Tax Type</th>
                                        <th class="col-tax">Tax</th>
                                        <th class="col-taxamt">Tax Amt</th>
                                        <th class="col-total">Total</th>
                                        <th class="col-del"></th>
                                    </tr>
                                </thead>

                                <tbody id="item_rows">

                                    @if(is_array($items))
                                        @foreach($items as $index => $item)
                                        <tr>
                                            <input type="hidden" name="items[{{ $index }}][row_id]" value="{{ $item['row_id'] ?? '' }}">
                                            <td class="col-item">
                                                <select name="items[{{ $index }}][item_id]" class="form-select item-id itemSelect"
                                                    data-id="{{ $item['item_id'] ?? '' }}"
                                                    data-text="{{ $item['item_name'] ?? '' }}"
                                                    {{ !empty($item['row_id']) ? 'disabled' : '' }}></select>
                                                @if(isset($item['row_id']) && $item['row_id'] != '')
                                                    <input type="hidden" name="items[{{ $index }}][item_id]" value="{{ $item['item_id'] }}">
                                                @endif
                                                @if($errors->has("items.$index.item_id"))
                                                    <div class="error-msg">{{ $errors->first("items.$index.item_id") }}</div>
                                                @endif
                                                <div class="item-note-toggle {{ !empty($item['note']) ? 'open' : '' }}">
                                                    <span class="plus-icon">+</span> Item Note
                                                </div>
                                                <div class="item-note-wrap {{ !empty($item['note']) ? 'open' : '' }}">
                                                    <textarea name="items[{{ $index }}][note]" class="form-control" rows="2" placeholder="Item note...">{{ $item['note'] ?? '' }}</textarea>
                                                </div>
                                            </td>
                                            <td class="col-hsn"><input type="text" name="items[{{ $index }}][hsn]" class="form-control" placeholder="HSN" value="{{ $item['hsn'] ?? '' }}"></td>
                                            <td class="col-qty">
                                                <input type="text" name="items[{{ $index }}][requested_qty]" class="form-control requested-qty"
                                                    value="{{ $item['requested_qty'] ?? 0 }}" {{ !$isDraft ? 'readonly' : '' }}
                                                    oninput="this.value=this.value.replace(/[^0-9.]/g,'').replace(/(\..*?)\..*/g,'$1')">
                                                @if($errors->has("items.$index.requested_qty"))
                                                    <div class="error-msg">{{ $errors->first("items.$index.requested_qty") }}</div>
                                                @endif
                                            </td>
                                            <td class="col-price">
                                                <input type="text" name="items[{{ $index }}][price]" class="form-control price" value="{{ $item['price'] ?? 0 }}" {{ !$isDraft ? 'readonly' : '' }} step="0.01" >
                                                @if($errors->has("items.$index.price"))
                                                    <div class="error-msg">{{ $errors->first("items.$index.price") }}</div>
                                                @endif
                                            </td>
                                            <td class="col-taxsub"><input type="number" name="items[{{ $index }}][total_amount]" class="form-control total_amount" value="{{ old("items.$index.total_amount", $item['total_amount'] ?? 0) }}" step="0.01" readonly></td>
                                            <td class="col-disc"><input type="number" name="items[{{ $index }}][discount]" class="form-control discount" value="{{ old("items.$index.discount", $item['discount'] ?? 0) }}" min="0" max="100" {{ !$isDraft ? 'readonly' : '' }} step="0.01"></td>
                                            <td class="col-amt"><input type="number" name="items[{{ $index }}][discount_amount]" class="form-control discount_amount" value="{{ old("items.$index.discount_amount", $item['discount_amount'] ?? 0) }}" step="0.01" readonly></td>
                                            <td class="col-taxtype">
                                                <select name="items[{{ $index }}][tax_type]" class="form-select" data-select2-selector="status" {{ !$isDraft ? 'disabled' : '' }}>
                                                    <option value="IGST"  {{ ($item['tax_type'] ?? '') == 'IGST'  ? 'selected' : '' }}>IGST</option>
                                                    <option value="Other" {{ ($item['tax_type'] ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
                                                </select>
                                            </td>
                                            <td class="col-tax">
                                                <select name="items[{{ $index }}][tax]" class="form-select item-status" data-select2-selector="status" {{ !$isDraft ? 'disabled' : '' }}>
                                                    <option value="5"  {{ ($item['tax'] ?? '') == 5  ? 'selected' : '' }}>5%</option>
                                                    <option value="18" {{ ($item['tax'] ?? '') == 18 ? 'selected' : '' }}>18%</option>
                                                    <option value="28" {{ ($item['tax'] ?? '') == 28 ? 'selected' : '' }}>28%</option>
                                                </select>
                                                @if($errors->has("items.$index.tax"))
                                                    <div class="error-msg">{{ $errors->first("items.$index.tax") }}</div>
                                                @endif
                                            </td>
                                            <td class="col-taxamt"><input type="number" name="items[{{ $index }}][tax_amount]" class="form-control tax_amount" value="{{ $item['tax_amount'] ?? 0 }}"  step="0.01"  readonly></td>
                                            <td class="col-total"><input type="number" name="items[{{ $index }}][taxable_total]" class="form-control taxable_total" value="{{ $item['taxable_total'] ?? 0 }}" step="0.01" readonly></td>
                                            <td class="col-del">@if($isDraft)<button type="button" class="btn-remove remove-row">×</button>@endif</td>
                                        </tr>
                                        @endforeach

                                    @else
                                        @foreach($PurchaseOrder->items as $index => $row)
                                        <tr>
                                            <input type="hidden" name="items[{{ $index }}][row_id]" value="{{ $row->id ?? '' }}">
                                            <td class="col-item">
                                                <select class="form-select" disabled>
                                                    <option value="">Select Item</option>
                                                    @foreach($inventories as $inv)
                                                        <option value="{{ $inv->id }}" {{ $row->inventory_id == $inv->id ? 'selected' : '' }}>{{ $inv->name }}</option>
                                                    @endforeach
                                                </select>
                                                <input type="hidden" name="items[{{ $index }}][item_id]" value="{{ $row->inventory_id }}">
                                                <div class="item-note-toggle {{ !empty($row->item_not) ? 'open' : '' }}">
                                                    <span class="plus-icon">+</span> Item Note
                                                </div>
                                                <div class="item-note-wrap {{ !empty($row->item_not) ? 'open' : '' }}">
                                                    <textarea name="items[{{ $index }}][note]" class="form-control" rows="2" placeholder="Item note...">{{ $row->item_not ?? '' }}</textarea>
                                                </div>
                                            </td>
                                            <td class="col-hsn"><input type="text" name="items[{{ $index }}][hsn]" class="form-control" placeholder="HSN" value="{{ $row->hsn ?? '' }}"></td>
                                            <td class="col-qty"><input type="text" name="items[{{ $index }}][requested_qty]" class="form-control requested-qty" value="{{ $row->ordered_qty }}" min="0" {{ !$isDraft ? 'readonly' : '' }} oninput="this.value=this.value.replace(/[^0-9.]/g,'').replace(/(\..*?)\..*/g,'$1')"></td>
                                            <td class="col-price"><input type="number" name="items[{{ $index }}][price]" class="form-control price" value="{{ $row->unit_price }}" {{ !$isDraft ? 'readonly' : '' }} step="0.01"></td>
                                            <td class="col-taxsub"><input type="number" name="items[{{ $index }}][total_amount]" class="form-control total_amount" value="{{ $row->taxable_total }}" step="0.01" readonly></td>
                                            <td class="col-disc"><input type="number" name="items[{{ $index }}][discount]" class="form-control discount" value="{{ $row->discount ?? 0 }}" min="0" max="100" {{ !$isDraft ? 'readonly' : '' }} step="0.01"></td>
                                            <td class="col-amt"><input type="number" name="items[{{ $index }}][discount_amount]" class="form-control discount_amount" value="{{ $row->discount_amount ?? 0 }}" step="0.01" readonly></td>
                                            <td class="col-taxtype">
                                                <select name="items[{{ $index }}][tax_type]" class="form-select" data-select2-selector="status" {{ !$isDraft ? 'disabled' : '' }}>
                                                    <option value="IGST"  {{ $row->tax_typ == 'IGST'  ? 'selected' : '' }}>IGST</option>
                                                    <option value="Other" {{ $row->tax_typ == 'Other' ? 'selected' : '' }}>Other</option>
                                                </select>
                                            </td>
                                            <td class="col-tax">
                                                <select name="items[{{ $index }}][tax]" class="form-select item-status" data-select2-selector="status" {{ !$isDraft ? 'disabled' : '' }}>
                                                    <option value="5"  {{ $row->tax_percent == '5'  ? 'selected' : '' }}>5%</option>
                                                    <option value="18" {{ $row->tax_percent == '18' ? 'selected' : '' }}>18%</option>
                                                    <option value="28" {{ $row->tax_percent == '28' ? 'selected' : '' }}>28%</option>
                                                </select>
                                            </td>
                                            <td class="col-taxamt"><input type="number" name="items[{{ $index }}][tax_amount]" class="form-control tax_amount" value="{{ $row->tax_amount }}" step="0.01" readonly></td>
                                            <td class="col-total"><input type="number" name="items[{{ $index }}][taxable_total]" class="form-control taxable_total" value="{{ $row->line_total }}" step="0.01" readonly></td>
                                            <td class="col-del">@if($isDraft)<button type="button" class="btn-remove remove-row">×</button>@endif</td>
                                        </tr>
                                        @endforeach
                                    @endif

                                </tbody>

                                <tfoot>
                                    <tr>
                                        <td colspan="2" class="foot-label">Totals →</td>
                                        <td class="col-qty"><input type="number" id="total_requested_qty" name="total_qty" class="form-control" value="0" step="0.01" readonly></td>
                                        <td></td>
                                        <td class="col-taxsub"><input type="number" id="total" name="total" class="form-control" value="0" step="0.01" readonly></td>
                                        <td></td>
                                        <td class="col-amt"><input type="number" id="total_discount_amount" name="total_discount_amount" class="form-control" value="0" step="0.01" readonly></td>
                                        <td></td><td></td>
                                        <td class="col-taxamt"><input type="number" id="total_tax" name="total_tax" class="form-control" value="0" step="0.01" readonly></td>
                                        <td class="col-total"><input type="number" id="final_total" name="final_total" class="form-control" value="0" step="0.01" readonly></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- BOTTOM PANEL -->
                    <div class="po-bottom" style="width: 100%;">

                        <div class="po-terms"style="width:100%; min-height:300px;  height:100%;">
                            <label>Terms &amp; Conditions</label>
                            <textarea name="terms_and_conditions" id="terms_and_conditions"
                                class="form-control editor-box" rows="7"
                                placeholder="Enter purchase order terms & conditions...">{{ old('terms_and_conditions', $PurchaseOrder->terms_and_conditions ?? '') }}</textarea>
                        </div>

                        <div class="po-totals-card">
                            <div class="tc-head">Order Summary</div>
                            <div class="tc-body">

                                <div class="tc-row">
                                    <span class="tc-label">Taxable Subtotal</span>
                                    <div class="tc-input"><input type="number" id="taxable_subtotal" name="taxable_subtotal" value="{{ old('taxable_subtotal', $PurchaseOrder->subtotal_discount_amount ?? 0) }}" step="0.01" readonly></div>
                                </div>
                                <div class="tc-row">
                                    <span class="tc-label">Loading / Cutting</span>
                                    <div class="tc-input"><input type="text" id="final_loading_cutting_charges" name="final_loading_cutting_charges" min="0" step="0.01" value="{{ old('final_loading_cutting_charges', $PurchaseOrder->loading_cutting_charges ?? 0) }}" oninput="this.value=this.value.replace(/[^0-9.]/g,'').replace(/(\..*?)\..*/g,'$1')" {{ !$isDraft ? 'readonly' : '' }}></div>
                                </div>
                                <div class="tc-row">
                                    <span class="tc-label">Freight Charges</span>
                                    <div class="tc-input"><input type="text" id="final_freight_charges" name="final_freight_charges" min="0" step="0.01" value="{{ old('final_freight_charges', $PurchaseOrder->freight_charges ?? 0) }}" oninput="this.value=this.value.replace(/[^0-9.]/g,'').replace(/(\..*?)\..*/g,'$1')" {{ !$isDraft ? 'readonly' : '' }}></div>
                                </div>
                                <div class="tc-row">
                                    <span class="tc-label">Tax Amount</span>
                                    <div class="tc-input"><input type="number" id="final_tax_amount" name="final_tax_amount" value="{{ old('final_tax_amount', $PurchaseOrder->tax_amount ?? 0) }}" step="0.01" readonly></div>
                                </div>
                                <div class="tc-row">
                                    <span class="tc-label">Grand Total</span>
                                    <div class="tc-input"><input type="number" id="final_grand_total" name="final_grand_total" value="{{ old('final_grand_total', $PurchaseOrder->total_amount ?? 0) }}" step="0.01" readonly></div>
                                </div>
                                
                                <div class="tc-row" style="display: none;">
                                    <span class="tc-label">Remaining Amount</span>
                                    <div class="tc-input"><input type="number" id="remaining_amount" name="remaining_amount" value="{{ old('remaining_amount', $PurchaseOrder->remaining_amount ?? 0) }}" step="0.01" readonly></div>
                                </div>

                                <hr class="tc-divider">

                                <div class="tc-row">
                                    <span class="tc-label">Discount Amount</span>
                                    <div class="tc-input"><input type="number" id="final_discount_amount" name="final_discount_amount" min="0" value="{{ old('final_discount_amount', $PurchaseOrder->final_discount ?? 0) }}" step="0.01" {{ !$isDraft ? 'readonly' : '' }}></div>
                                </div>

                                <div class="tc-row old-advance">
                                    <span class="tc-label">Old Advance Amount</span>
                                    <div class="tc-input"><input type="number" id="old_advance_amount" name="old_advance_amount" value="{{ old('old_advance_amount', $PurchaseOrder->advance_amount ?? 0) }}" step="0.01" readonly></div>
                                </div>

                                <input type="hidden" id="db_advance_amount" value="{{ $PurchaseOrder->advance_amount ?? 0 }}">

                                <div class="tc-row">
                                    <span class="tc-label">New Advance Amount</span>
                                    <div class="tc-input"><input type="number" id="advance_amount" name="advance_amount" min="0" step="0.01" value="{{ old('advance_amount', 0) }}" step="0.01" {{ !$isDraft ? 'readonly' : '' }}></div>
                                </div>

                                <div class="tc-row balance">
                                    <span class="tc-label">Balance Amount</span>
                                    <div class="tc-input"><input type="number" id="balance_amount" name="balance_amount" value="{{ old('balance_amount', $PurchaseOrder->balance_amount ?? 0) }}" step="0.01" readonly></div>
                                </div>

                            </div>
                        </div>

                    </div>

                </div><!-- end card-body -->

                <!-- SAVE BAR -->
                <div class="po-save-bar">
                    <div class="status-wrap">
                        <label>Status</label>
                        <select name="status" {{ !$isDraft ? 'disabled' : '' }}>
                            <option value="Draft"     {{ old('status', $PurchaseOrder->status) == 'Draft'     ? 'selected' : '' }}>Draft</option>
                            <option value="Submitted" {{ old('status', $PurchaseOrder->status) == 'Submitted' ? 'selected' : '' }}>Submitted</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-save-po">
                        <i class="fa fa-save" style="margin-right:6px;"></i>Update Purchase Order
                    </button>
                </div>

            </form>
        </div>
    </div>

    <!-- ROW TEMPLATE -->
    <template id="item_row_template">
        <tr>
            <td class="col-item">
                <select class="form-select item-id itemSelect"></select>
                <div class="item-note-toggle"><span class="plus-icon">+</span> Item Note</div>
                <div class="item-note-wrap">
                    <textarea class="form-control item-note mt-1" rows="2" placeholder="Item note..."></textarea>
                </div>
            </td>
            <td class="col-hsn"><input type="text" class="form-control item-hsn" placeholder="HSN"></td>
            <td class="col-qty"><input type="text" class="form-control requested-qty" value="0" min="0" oninput="this.value=this.value.replace(/[^0-9.]/g,'').replace(/(\..*?)\..*/g,'$1')"></td>
            <td class="col-price"><input type="text" class="form-control price" value=""></td>
            <td class="col-taxsub"><input type="number" class="form-control total_amount" value="" readonly step="0.01"></td>
            <td class="col-disc"><input type="number" class="form-control discount" value="0" min="0" max="100" step="0.01"></td>
            <td class="col-amt"><input type="number" class="form-control discount_amount" value="0" readonly step="0.01"></td>
            <td class="col-taxtype">
                <select class="form-select tax-type">
                    <option value="IGST">IGST</option>
                    <option value="Other">Other</option>
                </select>
            </td>
            <td class="col-tax">
                <select class="form-select item-status">
                    <option value="5">5%</option>
                    <option value="18">18%</option>
                    <option value="28">28%</option>
                </select>
            </td>
            <td class="col-taxamt"><input type="number" class="form-control tax_amount" value="0" step="0.01" readonly></td>
            <td class="col-total"><input type="number" class="form-control taxable_total" value="0" step="0.01"readonly></td>
            <td class="col-del"><button type="button" class="btn-remove remove-row">×</button></td>
        </tr>
    </template>

    @section('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {

        let rowIndex = @json(count($PurchaseOrder->items));

        ClassicEditor.create(document.querySelector('#terms_and_conditions')).catch(e => console.error(e));

        $('#supplier-select').select2({
            placeholder: 'Search Supplier', width: '100%', minimumInputLength: 0,
            ajax: {
                url: "{{ route('suppliers.search') }}", dataType: 'json', delay: 250,
                data: p => ({ q: p.term }),
                processResults: d => ({ results: d.results }),
                cache: true
            }
        });

        let supplierId   = "{{ old('supplier', $PurchaseOrder->supplier_id) }}";
        let supplierText = "{{ $PurchaseOrder->supplier->supplier_name ?? '' }}";
        if (supplierId && supplierText) {
            $('#supplier-select').append(new Option(supplierText, supplierId, true, true)).trigger('change');
        }

        function initItemSelect2(context = document) {
            if (typeof $ === 'undefined' || !$.fn.select2) return;
            $(context).find('.itemSelect').each(function () {
                let $s = $(this);
                if ($s.hasClass('select2-hidden-accessible')) $s.select2('destroy');
                $s.select2({
                    placeholder: 'Search Item name or model', width: '100%',
                    minimumInputLength: 0, allowClear: false,
                    ajax: {
                        url: "{{ route('inventory.search') }}", dataType: 'json', delay: 250,
                        data: p => ({ q: p.term }),
                        processResults: d => ({ results: [{ id: '', text: 'Select Item' }, ...(d.results || [])] })
                    }
                });
            });
        }

        initItemSelect2(document);
        $('.itemSelect').each(function () {
            let id = $(this).data('id'), text = $(this).data('text');
            if (id && text) $(this).append(new Option(text, id, true, true)).trigger('change');
        });

        function calculateRow(row) {
            if (!row) return;
            let qty      = parseFloat(row.querySelector('.requested-qty')?.value) || 0;
            let price    = parseFloat(row.querySelector('.price')?.value)         || 0;
            let discount = parseFloat(row.querySelector('.discount')?.value)      || 0;
            let tax      = parseFloat(row.querySelector('.item-status')?.value)   || 0;

            discount = Math.min(Math.max(discount, 0), 100);
            if (row.querySelector('.discount')) row.querySelector('.discount').value = discount;

            let subTotal      = qty * price;
            let discountAmt   = subTotal * discount / 100;
            let afterDiscount = subTotal - discountAmt;
            let taxAmount     = afterDiscount * tax / 100;
            let taxableTotal  = afterDiscount + taxAmount;

            if (row.querySelector('.total_amount'))    row.querySelector('.total_amount').value    = subTotal.toFixed(2);
            if (row.querySelector('.discount_amount')) row.querySelector('.discount_amount').value = afterDiscount.toFixed(2);
            if (row.querySelector('.tax_amount'))      row.querySelector('.tax_amount').value      = taxAmount.toFixed(2);
            if (row.querySelector('.taxable_total'))   row.querySelector('.taxable_total').value   = taxableTotal.toFixed(2);
        }

        function calculateFooter() {
            let totalQty = 0, subTotal = 0, totalDiscount = 0, taxTotal = 0, taxableGrand = 0;
            let firstRowTax = 0; // first row tax %


            document.querySelectorAll('#item_rows tr').forEach(row => {
                totalQty      += parseFloat(row.querySelector('.requested-qty')?.value)   || 0;
                subTotal      += parseFloat(row.querySelector('.total_amount')?.value)    || 0;
                totalDiscount += parseFloat(row.querySelector('.discount_amount')?.value) || 0;
                taxTotal      += parseFloat(row.querySelector('.tax_amount')?.value)      || 0;
                taxableGrand  += parseFloat(row.querySelector('.taxable_total')?.value)   || 0;
                 // first row tax %
                firstRowTax = parseFloat(document.querySelector('#item_rows tr .item-status')?.value) || 0;
            });

            let lc        = parseFloat(document.getElementById('final_loading_cutting_charges').value) || 0;
            let fc        = parseFloat(document.getElementById('final_freight_charges').value)         || 0;
            totalDiscount += lc + fc;
                taxTotal = (totalDiscount * firstRowTax) / 100;
                taxableGrand = totalDiscount + taxTotal;

            let dbAdv     = parseFloat(document.getElementById('db_advance_amount').value)             || 0;
            let discEl    = document.getElementById('final_discount_amount');
            let disc      = parseFloat(discEl.value) || 0;
            let advEl     = document.getElementById('advance_amount');
            let adv       = parseFloat(advEl.value)  || 0;

            document.getElementById('remaining_amount').value = taxableGrand.toFixed(2);

            let payable = Math.max(taxableGrand - disc - dbAdv, 0);
            if (adv > payable) { adv = payable; advEl.value = payable.toFixed(2); }

            let base = taxableGrand - dbAdv - adv;
            if (disc > base) { disc = base; discEl.value = base.toFixed(2); }

            let balance = base - disc;

            document.getElementById('total_requested_qty').value  = totalQty.toFixed(2);
            document.getElementById('total').value                = subTotal.toFixed(2);
            document.getElementById('total_discount_amount').value = totalDiscount.toFixed(2);
            document.getElementById('total_tax').value            = taxTotal.toFixed(2);
            document.getElementById('final_total').value          = taxableGrand.toFixed(2);
            document.getElementById('final_grand_total').value    = taxableGrand.toFixed(2);
            document.getElementById('taxable_subtotal').value     = totalDiscount.toFixed(2);
            document.getElementById('final_tax_amount').value     = taxTotal.toFixed(2);
            document.getElementById('balance_amount').value       = balance.toFixed(2);
        }

        document.addEventListener('input', function (e) {
            if (['requested-qty','price','discount'].some(c => e.target.classList.contains(c))) {
                if (e.target.classList.contains('requested-qty')) {
                    let max = parseFloat(e.target.getAttribute('max'));
                    let val = parseFloat(e.target.value) || 0;
                    if (!isNaN(max) && val > max) e.target.value = max;
                }
                calculateRow(e.target.closest('tr'));
                calculateFooter();
            }
            if (['final_loading_cutting_charges','final_freight_charges','advance_amount','final_discount_amount'].includes(e.target.id)) {
                calculateFooter();
            }
        });

        document.addEventListener('change', function (e) {
            if (e.target.classList.contains('item-status')) {
                calculateRow(e.target.closest('tr'));
                calculateFooter();
            }
        });

        if (window.jQuery) {
            $(document).on('change', '.item-status', function () {
                calculateRow(this.closest('tr'));
                calculateFooter();
            });
        }

        document.addEventListener('click', function (e) {
            const toggle = e.target.closest('.item-note-toggle');
            if (toggle) {
                toggle.classList.toggle('open');
                toggle.nextElementSibling.classList.toggle('open');
            }
        });

        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-row')) {
                let tbody = document.getElementById('item_rows');
                    let rows = tbody.querySelectorAll('tr');
                     if (rows.length <= 1) {
                      return; 
                        }
                e.target.closest('tr').remove();
                calculateFooter();
            }
        });

        document.getElementById('addRow')?.addEventListener('click', function () {
            let tmpl = document.getElementById('item_row_template').content.cloneNode(true);
            let i    = rowIndex;

            tmpl.querySelector('.item-id').name        = `items[${i}][item_id]`;
            tmpl.querySelector('.item-hsn').name        = `items[${i}][hsn]`;
            tmpl.querySelector('.requested-qty').name   = `items[${i}][requested_qty]`;
            tmpl.querySelector('.price').name           = `items[${i}][price]`;
            tmpl.querySelector('.total_amount').name    = `items[${i}][total_amount]`;
            tmpl.querySelector('.discount').name        = `items[${i}][discount]`;
            tmpl.querySelector('.discount_amount').name = `items[${i}][discount_amount]`;
            tmpl.querySelector('.item-status').name     = `items[${i}][tax]`;
            tmpl.querySelector('.tax-type').name        = `items[${i}][tax_type]`;
            tmpl.querySelector('.tax_amount').name      = `items[${i}][tax_amount]`;
            tmpl.querySelector('.taxable_total').name   = `items[${i}][taxable_total]`;
            tmpl.querySelector('.item-note').name       = `items[${i}][note]`;

            let tbody = document.getElementById('item_rows');
            tbody.appendChild(tmpl);
            let last = tbody.lastElementChild;
            initItemSelect2(last);

            if (window.jQuery) {
                $(last).find('select[data-select2-selector="status"]').select2({ width: '100%' });
            }
            rowIndex++;
        });

        document.querySelectorAll('#item_rows tr').forEach(row => calculateRow(row));
        calculateFooter();
    });
    </script>

    <script>
        $('#firmSelect').select2({
    width: '100%'
});

$(document).ready(function () {

    $('#firmSelect').on('change', function () {

        let firmId = $(this).val(); // ✅ correct way

        console.log('Selected Firm ID:', firmId);

        let newPo = '';

        if (firmId == 1) {
            newPo = $('#mhelPo').val();
        } 
        else if (firmId == 2) {
            newPo = $('#mtplPo').val();
        }

        if (newPo) {
            $('#poNumberText').text(newPo);
            $('#poNumberInput').val(newPo);
        }

    });

});
</script>
    @endsection

@endsection
