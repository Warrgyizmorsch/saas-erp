@extends('shared::layouts.app')
@section('content')
    <style>
        :root{
            --primary:#4338ca;
            --bg:#f8fafc;
            --card:#ffffff;
            --text:#0f172a;
            --muted:#64748b;
            --border:#e2e8f0;
            --soft:#f1f5f9;
            --success:#16a34a;
            --danger:#dc2626;
            --warning:#f59e0b;
        }

        .grn-wrap{
            padding: 24px;
            background: var(--bg);
            min-height: 100vh;
        }

        /* Header */
        .grn-header{
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 18px 18px;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04);
        }
        .grn-title{
            font-weight: 900;
            color: var(--text);
            margin: 0;
            font-size: 1.25rem;
            letter-spacing: .2px;
        }
        .grn-sub{
            margin-top: 6px;
            color: var(--muted);
            font-weight: 600;
            font-size: .9rem;
        }

        .chip{
            display:inline-flex;
            align-items:center;
            gap:8px;
            background: var(--soft);
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: 6px 12px;
            font-weight: 800;
            color: var(--text);
            font-size: .82rem;
            white-space: nowrap;
        }
        .chip i{ color: var(--primary); }

        .btn-soft{
            background: var(--soft);
            border: 1px solid var(--border);
            color: var(--text);
            font-weight: 800;
            border-radius: 12px;
            padding: 10px 14px;
        }
        .btn-soft:hover{ background:#e9eef6; }
        .btn-primary2{
            background: var(--primary);
            border: 1px solid var(--primary);
            color:#fff;
            font-weight: 900;
            border-radius: 12px;
            padding: 10px 14px;
        }
        .btn-primary2:hover{ opacity:.95; }

        /* Cards */
        .grid{
            display:grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 14px;
            margin-top: 14px;
        }
        .cardx{
            grid-column: span 4;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 16px;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04);
        }
        .cardx.wide{ grid-column: span 12; }
        .cardx.half{ grid-column: span 6; }

        .cardx-title{
            display:flex;
            align-items:center;
            gap:10px;
            margin-bottom: 12px;
            font-weight: 900;
            color: var(--text);
        }
        .iconbox{
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: #eef2ff;
            border: 1px solid #e0e7ff;
            display:flex;
            align-items:center;
            justify-content:center;
            color: var(--primary);
        }

        .kv{
            display:grid;
            grid-template-columns: 120px 1fr;
            gap: 8px 12px;
            align-items: baseline;
        }
        .k{
            color: var(--muted);
            font-size: .78rem;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
        }
        .v{
            color: var(--text);
            font-weight: 900;
            font-size: .95rem;
            word-break: break-word;
        }
        .v.small{ font-weight: 700; color: var(--text); opacity:.9; }

        /* Table */
        .table-wrap{
            border-radius: 14px;
            overflow:hidden;
            border: 1px solid var(--border);
            background: var(--card);
        }
        .table thead th{
            background: var(--soft);
            color: #475569;
            font-weight: 900;
            font-size: .75rem;
            letter-spacing: .06em;
            text-transform: uppercase;
            padding: 14px 12px;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }
        .table tbody td{
            padding: 12px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            font-weight: 700;
            color: var(--text);
        }
        .table tbody tr:last-child td{ border-bottom: 0; }

        .muted2{
            color: var(--muted);
            font-weight: 700;
            font-size: .85rem;
        }
        .name{
            font-weight: 900;
            color: var(--text);
        }
        .model{
            font-size: .8rem;
            color: var(--muted);
            font-weight: 700;
        }

        .pill{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            min-width: 90px;
            padding: 6px 10px;
            border-radius: 999px;
            font-weight: 900;
            border: 1px solid var(--border);
            background: #fff;
        }
        .pill.green{ color: var(--success); border-color: rgba(22,163,74,.25); background: rgba(22,163,74,.06); }
        .pill.red{ color: var(--danger); border-color: rgba(220,38,38,.25); background: rgba(220,38,38,.06); }
        .pill.blue{ color: var(--primary); border-color: rgba(67,56,202,.25); background: rgba(67,56,202,.06); }

        /* Totals */
        .totals{
            display:grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 14px;
            margin-top: 14px;
        }
        .tcard{
            grid-column: span 4;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 16px;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04);
        }
        .tlabel{
            color: var(--muted);
            font-weight: 900;
            font-size: .78rem;
            letter-spacing: .06em;
            text-transform: uppercase;
        }
        .tvalue{
            margin-top: 8px;
            font-size: 1.35rem;
            font-weight: 1000;
            color: var(--text);
        }
        .tvalue.primary{ color: var(--primary); }
        .tvalue.success{ color: var(--success); }
        .tvalue.danger{ color: var(--danger); }

        /* Responsive */
        @media (max-width: 992px){
            .cardx{ grid-column: span 12; }
            .tcard{ grid-column: span 12; }
            .kv{ grid-template-columns: 120px 1fr; }
        }

        /* PRINT: keep same UI */
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print{ display:none !important; }
            .grn-wrap{
                padding: 0 !important;
                background: #fff !important;
                min-height: auto !important;
            }
            .grn-header, .cardx, .table-wrap, .tcard{
                box-shadow: none !important;  /* printer pe shadow sometimes muddy hota */
            }
            .table thead th{
                background: #f1f5f9 !important;
            }
            .chip, .pill{
                background: #f1f5f9 !important;
            }
        }
    </style>

    <div class="grn-wrap">
        {{-- Header --}}
        <div class="grn-header d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <h3 class="grn-title">GRN Details</h3>
                <div class="grn-sub">
                    Goods Receipt Note with item summary & totals
                </div>

                <div class="mt-2 d-flex flex-wrap gap-2">
                    <span class="chip"><i class="feather-hash"></i> {{ $grn->grn_number }}</span>
                    <span class="chip"><i class="feather-calendar"></i> {{ \Carbon\Carbon::parse($grn->grn_date)->format('d M Y') }}</span>
                    @if(!empty($grn->invoice_no))
                        <span class="chip"><i class="feather-file-text"></i> Invoice: {{ $grn->invoice_no }}</span>
                    @endif
                </div>
            </div>

            <div class="no-print d-flex gap-2">
                <a href="{{ route('grn.list') }}" class="btn btn-soft">
                    <i class="feather-arrow-left me-1"></i> Back
                </a>
                <button type="button" onclick="window.print()" class="btn btn-primary2">
                    <i class="feather-printer me-1"></i> Print
                </button>
            </div>
        </div>

        {{-- Cards Grid --}}
        <div class="grid">
            {{-- Vendor --}}
            <div class="cardx">
                <div class="cardx-title">
                    <div class="iconbox"><i class="feather-truck"></i></div>
                    <div>Supplier</div>
                </div>

                <div class="kv">
                    <div class="k">Name</div>
                    <div class="v">{{ $grn->purchaseOrder?->supplier?->supplier_name ?? 'N/A' }}</div>

                    <div class="k">Mobile</div>
                    <div class="v small">{{ $grn->purchaseOrder?->supplier?->mobile ?? 'N/A' }}</div>

                    <div class="k">GST</div>
                    <div class="v small">{{ $grn->purchaseOrder?->supplier?->gstin ?? 'N/A' }}</div>
         
                </div>
            </div>

            {{-- Purchase Order --}}
            <div class="cardx">
                <div class="cardx-title">
                    <div class="iconbox"><i class="feather-shopping-bag"></i></div>
                    <div>Purchase Order</div>
                </div>

                <div class="kv">
                    <div class="k">PO No</div>
                    <div class="v">#{{ $grn->purchaseOrder?->po_number ?? 'N/A' }}</div>

                    <div class="k">PO Date</div>
                    <div class="v small">
                        {{ $grn->purchaseOrder?->po_date ? \Carbon\Carbon::parse($grn->purchaseOrder->po_date)->format('d M Y') : 'N/A' }}
                    </div>

                    <div class="k">Status</div>
                    <div class="v small">{{ $grn->purchaseOrder?->status ?? 'N/A' }}</div>
                </div>
            </div>

            {{-- GRN Meta --}}
            <div class="cardx">
                <div class="cardx-title">
                    <div class="iconbox"><i class="feather-file"></i></div>
                    <div>GRN Meta</div>
                </div>

                <div class="kv">
                    <div class="k">Invoice</div>
                    <div class="v">{{ $grn->invoice_no ?? '-' }}</div>

                    <div class="k">Remarks</div>
                    <div class="v small">{{ $grn->remarks ?? '-' }}</div>
                </div>
            </div>

            {{-- Items Table --}}
            <div class="cardx wide">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="cardx-title mb-0">
                        <div class="iconbox"><i class="feather-list"></i></div>
                        <div>Items</div>
                    </div>

                    <div class="muted2">
                        <span class="chip"><i class="feather-check-circle"></i> Accepted Qty = Stock IN</span>
                    </div>
                </div>

                <div class="table-wrap">
                    <table class="table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th class="ps-4" style="width:70px;">Sr</th>
                                <th>Material</th>
                                <th class="text-center">PO Qty</th>
                                <th class="text-center">PO Received</th>
                                <th class="text-center">Received</th>
                                <th class="text-center">Rejected</th>
                                <th class="text-center">Accepted</th>
                                <th class="pe-4">Remark</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($grn->items as $idx => $it)
                                @php
                                    $poItem = $grn->purchaseOrder?->items?->firstWhere('inventory_id', $it->inventory_id);
                                    $poOrdered = (float)($poItem?->ordered_qty ?? 0);
                                    $poReceived = (float)($poItem?->received_qty ?? 0);
                                @endphp

                                <tr>
                                    <td class="ps-4">{{ $idx + 1 }}</td>

                                    <td>
                                        <div class="name">{{ $it->inventory?->name ?? 'N/A' }}</div>
                                        <div class="model">{{ $it->inventory?->model ?? '' }}</div>
                                    </td>

                                    <td class="text-center">
                                        <span class="pill">{{ number_format($poOrdered, 2) }}</span>
                                    </td>

                                    <td class="text-center">
                                        <span class="pill blue">{{ number_format($poReceived, 2) }}</span>
                                    </td>


                                    <td class="text-center">
                                        <span class="pill">{{ number_format((float)$it->received_qty, 2) }}</span>
                                    </td>

                                  

                                    <td class="text-center">
                                        <span class="pill red">{{ number_format((float)$it->rejected_qty, 2) }}</span>
                                    </td>

                                    <td class="text-center">
                                        <span class="pill green">{{ number_format((float)$it->accepted_qty, 2) }}</span>
                                    </td>

                                    <td class="pe-4">
                                        <span class="muted2">{{ $it->remarks ?? '-' }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted p-4">No items found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Totals --}}
        <div class="totals">
            <div class="tcard">
                <div class="tlabel">Total Received</div>
                <div class="tvalue primary">{{ number_format($totals['received'], 2) }}</div>
            </div>
            <div class="tcard">
                <div class="tlabel">Total Rejected</div>
                <div class="tvalue danger">{{ number_format($totals['rejected'], 2) }}</div>
            </div>
            <div class="tcard">
                <div class="tlabel">Total Accepted (Stock IN)</div>
                <div class="tvalue success">{{ number_format($totals['accepted'], 2) }}</div>
            </div>
        </div>
    </div>
@endsection
