@extends('shared::layouts.app')
@section('content')

    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Stock Transaction Ledger</h4>
            <small class="text-muted">
                Inventory Stock In / Out History
            </small>
        </div>
    </div>

    <div class="main-content">

        <!-- FILTER CARD -->
        <form method="GET" action="{{ route('stockLedger') }}">

            <!-- FILTER CARD -->
            <div class="card shadow-sm border mb-4">
                <div class="card-body">

                    <div class="row g-3">

                        <!-- Inventory -->
                        <div class="col-lg-3">
                            <label class="form-label">Inventory Name </label>

                            <select name="inventory_id"
                                id="inventory-filter"
                                class="form-control">
                            </select>
                        </div>

                        <!-- Transaction Type -->
                        <div class="col-lg-2">
                            <label class="form-label">Transaction Type</label>

                            <select class="form-control" name="transaction_type">

                                <option value="">All</option>

                                <option value="In"
                                    {{ request('transaction_type') == 'In' ? 'selected' : '' }}>
                                    IN
                                </option>

                                <option value="Out"
                                    {{ request('transaction_type') == 'Out' ? 'selected' : '' }}>
                                    OUT
                                </option>

                            </select>
                        </div>

                        <!-- From Date -->
                        <div class="col-lg-2">
                            <label class="form-label">From Date</label>

                            <input type="date"
                                name="from_date"
                                value="{{ request('from_date') }}"
                                class="form-control">
                        </div>

                        <!-- To Date -->
                        <div class="col-lg-2">
                            <label class="form-label">To Date</label>

                            <input type="date"
                                name="to_date"
                                value="{{ request('to_date') }}"
                                class="form-control">
                        </div>

                        <!-- Buttons -->
                        <div class="col-lg-3 d-flex align-items-end gap-2">

                            <button type="submit" class="btn btn-primary w-100">
                                Search
                            </button>

                            <a href="{{ route('stockLedger') }}"
                                class="btn btn-light w-100">
                                Reset
                            </a>

                        </div>

                    </div>

                </div>
            </div>

        </form>


        <!-- LEDGER TABLE -->
        <div class="card border shadow-sm">

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-bordered align-middle mb-0">

                        <thead class="table-light">

                            <tr>

                                <th>#</th>

                                <th>Date</th>

                                <th>Reference No</th>
                                <th>Reference Type</th>

                                <th style="width:220px; min-width:220px;">Inventory Item</th>


                                <th class="text-end">IN Qty</th>

                                <th class="text-end">OUT Qty</th>

                                <th>Remarks</th>

                            </tr>

                        </thead>

                        <tbody>

                            @forelse($transactions as $index => $txn)

                            <tr>

                                <td>
                                    {{ $index + 1 }}
                                </td>

                                <td>
                                    {{ \Carbon\Carbon::parse($txn->txn_date)->format('d-m-Y') }}
                                </td>

                                <td>

                                    @if($txn->txn_type == 'IN')

                                    <span class="badge bg-soft-primary text-primary">
                                        @if(!empty($txn->ref_no))
                                        <a href="{{ route('stock.redirect', $txn->id) }}" class=" text-decoration-none">
                                            {{ $txn->ref_no }}
                                        </a>
                                        @else
                                        {{ '-' }}
                                        @endif
                                    </span>

                                    @else

                                    <span class="badge bg-soft-danger text-danger">
                                        @if(!empty($txn->ref_no))
                                        <a href="{{ route('stock.redirect', $txn->id) }}" class=" text-decoration-none">
                                            {{ $txn->ref_no }}
                                        </a>
                                        @else
                                        {{ '-' }}
                                        @endif
                                    </span>

                                    @endif

                                </td>

                                <td>
                                    {{$txn->ref_type ?? 'N/A'}}
                                </td>

                                <td>

                                    <div style="width:220px; min-width:220px; white-space: normal; word-break: break-word;" class="fw-bold">
                                        {{ $txn->inventory->name ?? '-' }}
                                    </div>

                                    <small class="text-muted">
                                        Model :
                                        {{ $txn->inventory->model ?? '-' }}
                                    </small>

                                </td>



                                {{-- IN QTY --}}
                                <td class="text-end fw-bold text-success">

                                    @if($txn->txn_type == 'In')
                                    {{ number_format($txn->quantity, 2) }}
                                    @else
                                    -
                                    @endif

                                </td>

                                {{-- OUT QTY --}}
                                <td class="text-end fw-bold text-danger">

                                    @if($txn->txn_type == 'Out')
                                    {{ number_format($txn->quantity, 2) }}
                                    @else
                                    -
                                    @endif

                                </td>

                                <td>
                                    {{ $txn->remarks ?? '-' }}
                                </td>

                            </tr>

                            @empty

                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    No Stock Transactions Found
                                </td>
                            </tr>

                            @endforelse

                            <tr class="table-dark">

                                <td colspan="5" class="text-end fw-bold">
                                    TOTAL
                                </td>

                                <td class="text-end fw-bold text-success">
                                    {{ number_format($totalIn, 2) }}
                                </td>

                                <td class="text-end fw-bold text-danger">
                                    {{ number_format($totalOut, 2) }}
                                </td>

                                <td class="fw-bold">
                                    Available :
                                    {{ number_format($availableStock, 2) }}
                                </td>

                            </tr>
                            <tr>
                                <td colspan="8" class="text-end">
                                    <small class="text-muted">
                                        Finish & Machining excluded from totals.
                                    </small>
                                </td>
                            </tr>


                        </tbody>

                    </table>

                </div>
                 {{-- Pagination --}}
                                <div class="mt-3 px-3">
                                    {{ $transactions->links('pagination::bootstrap-5') }}
                                </div>

            </div>

        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('#inventory-filter').select2({
                placeholder: 'Search Inventory',
                allowClear: false,
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
                    },
                    cache: true
                }
            });

            // SELECTED VALUE SET
            let selectedInventoryId = "{{ request('inventory_id') }}";
            let selectedInventoryText = "{{ $inventoryName ?? '' }}";

            if (selectedInventoryId && selectedInventoryText) {

                let option = new Option(
                    selectedInventoryText,
                    selectedInventoryId,
                    true,
                    true
                );

                $('#inventory-filter')
                    .append(option)
                    .trigger('change');
            }

        });
    </script>

@endsection