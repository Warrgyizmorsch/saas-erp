{{-- resources/views/consumption/create.blade.php --}}
@extends('shared::layouts.app')
@section('content')

@php
    $pageTitle = 'Create Consumption';

    $allIssueRows = (isset($rs->issue) && isset($rs->issue->rows)) ? $rs->issue->rows : collect([]);

    // ✅ only issued > 0
    $rows = $allIssueRows->filter(function ($row) {
        $issued = (float)($row->issue_nqty ?? $row->quantity ?? 0);
        return $issued > 0;
    })->values();
@endphp

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

<div class="page-header d-flex justify-content-between align-items-center ">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">{{ $pageTitle }}</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('request-slip.index') }}">Request Slip</a></li>
            <li class="breadcrumb-item active">{{ $pageTitle }}</li>
        </ul>
    </div>
</div>

<div class="main-content">
    <div class="card">
        <div class="card-body">

            <form id="consForm" action="{{ route('consumption.store', $rs->id) }}" method="POST">
                @csrf
                <input type="hidden" name="transaction_date" value="{{ date('Y-m-d') }}">

                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th style="min-width:560px;">Item Details</th>
                                <th style="min-width:420px;">New Consumption Entry</th>
                                <th style="min-width:420px;">Purpose / Remark</th>
                            </tr>
                        </thead>

                        <tbody>
                            @if($rows->count() > 0)

                                @foreach($rows as $i => $r)
                                    @php
                                        $rsRowId = $r->rs_row_id ?? $r->requisition_slip_row_id ?? $r->request_slip_row_id ?? null;

                                        $inv = $r->inventory;
                                        $itemName = optional($inv)->name ?? optional($inv)->item_name ?? '—';

                                        $unit = strtoupper(trim(optional($inv)->unit ?? 'NOS'));

                                        // issued
                                        $issuedQty = (float)($r->issue_nqty ?? $r->quantity ?? 0);

                                        // already consumed (for this RS row)
                                        $alreadyQty = (float)($consumedQtyMap[$rsRowId] ?? 0);
                                        $alreadyH   = (float)($consumedHMap[$rsRowId] ?? 0);
                                        $alreadyW   = (float)($consumedWMap[$rsRowId] ?? 0);

                                        // remark mandatory only if already consumed exists
                                        $remarkRequired = false;
                                        if ($unit === 'KG') {
                                            $remarkRequired = ($alreadyH > 0 || $alreadyW > 0);
                                        } else {
                                            $remarkRequired = ($alreadyQty > 0);
                                        }

                                        // remaining
                                        $invH = (float)(optional($inv)->height ?? 0);
                                        $invW = (float)(optional($inv)->width ?? 0);

                                        if ($unit === 'KG') {
                                            $remainH = max(0, $invH - $alreadyH);
                                            $remainW = max(0, $invW - $alreadyW);
                                            $isLocked = ($remainH <= 0 && $remainW <= 0);
                                        } else {
                                            $remainQty = max(0, $issuedQty - $alreadyQty);
                                            $isLocked = ($remainQty <= 0);
                                        }
                                    @endphp

                                    <tr class="cons-row" data-unit="{{ $unit }}" data-locked="{{ $isLocked ? 1 : 0 }}">
                                        <input type="hidden" name="items[rs_row_id][]" value="{{ $rsRowId }}">

                                        {{-- ✅ ITEM DETAILS (show remaining) --}}
                                        <td>
                                            <div class="fw-semibold">{{ $itemName }}</div>

                                            @if($unit === 'KG')
                                                <div class="mt-1 small text-muted">
                                                    Issued Size: <span class="fw-semibold text-dark">{{ $invH }} × {{ $invW }}</span>
                                                </div>
                                                <div class="mt-1 small text-muted">
                                                    Remaining: <span class="fw-semibold text-dark">{{ $remainH }} × {{ $remainW }}</span> (KG)
                                                </div>
                                            @else
                                                <div class="mt-1 small text-muted">
                                                    Issued Qty: <span class="fw-semibold text-dark">{{ $issuedQty }}</span> ({{ $unit }})
                                                </div>
                                                <div class="mt-1 small text-muted">
                                                    Remaining: <span class="fw-semibold text-dark">{{ $remainQty }}</span> ({{ $unit }})
                                                </div>
                                            @endif

                                            @if($isLocked)
                                                <div class="mt-2">
                                                    <span class="badge bg-success">Completed</span>
                                                </div>
                                            @endif
                                        </td>

                                        {{-- ✅ ENTRY --}}
                                        <td>
                                            @if($unit === 'KG')
                                                {{-- KG => NO qty input --}}
                                                <input type="hidden" name="items[consume_qty][]" value="0">

                                                <div class="d-flex gap-2">
                                                    <input type="number"
                                                           name="items[height][]"
                                                           class="form-control"
                                                           value="{{ old('items.height.'.$i) }}"
                                                           min="0" step="1"
                                                           placeholder="Height"
                                                           {{ $isLocked ? 'readonly disabled' : '' }}>
                                                    <input type="number"
                                                           name="items[width][]"
                                                           class="form-control"
                                                           value="{{ old('items.width.'.$i) }}"
                                                           min="0" step="1"
                                                           placeholder="Width"
                                                           {{ $isLocked ? 'readonly disabled' : '' }}>
                                                </div>

                                                @if(isset($errors->toArray()['items.height.'.$i]))
                                                    <div class="text-danger mt-1 small">{{ $errors->first('items.height.'.$i) }}</div>
                                                @endif
                                                @if(isset($errors->toArray()['items.width.'.$i]))
                                                    <div class="text-danger mt-1 small">{{ $errors->first('items.width.'.$i) }}</div>
                                                @endif
                                            @else
                                                {{-- non-KG => qty input --}}
                                                <input type="number"
                                                       name="items[consume_qty][]"
                                                       class="form-control"
                                                       value="{{ old('items.consume_qty.'.$i) }}"
                                                       min="0" step="1"
                                                       placeholder="{{ $isLocked ? 'Completed' : 'Enter Qty' }}"
                                                       {{ $isLocked ? 'readonly disabled' : '' }}>

                                                @if(isset($errors->toArray()['items.consume_qty.'.$i]))
                                                    <div class="text-danger mt-1 small">{{ $errors->first('items.consume_qty.'.$i) }}</div>
                                                @endif

                                                {{-- keep arrays aligned --}}
                                                <input type="hidden" name="items[height][]" value="">
                                                <input type="hidden" name="items[width][]" value="">
                                            @endif
                                        </td>

                                        {{-- ✅ REMARK --}}
                                        <td>
                                            <label class="form-label mb-1">
                                                Remark @if($remarkRequired)<span class="text-danger">*</span>@endif
                                            </label>

                                            <textarea name="items[remark][]"
                                                      class="form-control"
                                                      rows="2"
                                                      placeholder="{{ $remarkRequired ? 'Remark required (already consumed earlier)' : 'Optional remark' }}"
                                                      {{ $isLocked ? 'readonly disabled' : '' }}
                                            >{{ old('items.remark.'.$i) }}</textarea>

                                            @if(isset($errors->toArray()['items.remark.'.$i]))
                                                <div class="text-danger mt-1 small">{{ $errors->first('items.remark.'.$i) }}</div>
                                            @endif

                                            <input type="hidden" name="items[remark_required][]" value="{{ $remarkRequired ? 1 : 0 }}">
                                        </td>
                                    </tr>
                                @endforeach

                            @else
                                <tr>
                                    <td colspan="3" class="text-center p-4">
                                        <strong>No issued items found.</strong>
                                    </td>
                                </tr>
                            @endif
                        </tbody>

                    </table>
                </div>

                <div class="mt-3 d-flex justify-content-end">
                    <button class="btn btn-primary">Save Consumption</button>
                </div>

            </form>

        </div>
    </div>
</div>

@endsection
