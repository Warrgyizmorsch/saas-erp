@extends('shared::layouts.app')
@section('content')

    {{-- PAGE HEADER --}}
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Consumption List</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="/dashboard">Home</a>
                </li>
                <li class="breadcrumb-item active">Consumption</li>
            </ul>
        </div>
    </div>

    {{-- MAIN CONTENT --}}
    <div class="main-content">

        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full">
                    <div class="card-body">

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sr</th>
                                        <th>Rs No</th>
                                        <th>Item Name</th>
                                        <th>Requested Qty</th>
                                        <th>Requested Height</th>
                                        <th>Requested Width</th>
                                        <th>Consumed Height</th>
                                        <th>Consumed Width</th>
                                        <th>consumed_qty</th>
                                        <th>Remaining </th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @php
                                    $index = 0;
                                    @endphp
                                    @forelse($rs as  $r)
                                    @foreach($r->rows as $row)
                                    @foreach($row->pieces as  $piece)

                                    @php

                                    $index++;

                                    $isKg = strtolower(optional($piece->inventory)->unit) === 'kg';
                                    if ($isKg) {
                                    $height = $piece->issued_height ?? 0;
                                    $width = $piece->issued_width ?? 0;
                                    $qty = $piece->issued_qty ?? 0;

                                    
                                    

                                    
                                    $remainingHeight =  $height - ($piece->consumed_height ?? 0);
                                    $remainingWidth = $width - ($piece->consumed_width ?? 0);
                                    }

                                    @endphp

                                    <tr>
                                        <td>{{ $index }}</td>

                                        <td>{{$r->requisition_slip_no ?? 'N/A'}}</td>

                                        <td>
                                            <strong>
                                                {{ $piece->inventory->name ?? 'N/A' }}
                                            </strong>
                                        </td>


                                        <td>
                                            {{$piece->issued_qty ?? 0 }}
                                        </td>

                                        <td>
                                            {{ $isKg ? $height : '-' }}
                                        </td>

                                        <td>
                                            {{ $isKg ? $width : '-' }}
                                        </td>

                                        <td>
                                            {{ $isKg ? ($piece->consumed_height ?? 0) : '-' }}
                                        </td>

                                        <td>
                                            {{ $isKg ? ($piece->consumed_width ?? 0) : '-' }}
                                        </td>

                                        <td>{{ $isKg ? '-' : ($piece->consumed_qty ?? 0) }}</td>

                                        <td>
                                            {{ $isKg ? ($remainingHeight . ' x ' . $remainingWidth) : ($piece->issued_qty - $piece->consumed_qty) }}
                                        </td>

                                        <td>
                                            <a href="javascript:void(0)"
                                                class="btn btn-light btn-sm"
                                                title="View History"
                                                data-bs-toggle="offcanvas"
                                                data-bs-target="#consumptionHistory{{ $piece->id }}">
                                                <i class="fa fa-history"></i>
                                            </a>


                                        </td>
                                    </tr>

                                    <div class="offcanvas offcanvas-end offcanvas-80"
                                        tabindex="-1"
                                        id="consumptionHistory{{ $piece->id }}">

                                        @php
                                        $historyData = \Modules\Inventory\App\Models\Consumption::with('inventory','requestslip')
                                        ->where('rs_row_id', $piece->id)
                                        ->orderBy('created_at')
                                        ->get();
                                        @endphp

                                        <div class="offcanvas-header ht-80 px-4 border-bottom border-gray-5">
                                            <div>
                                                <h2 class="fs-16 fw-bold mb-1"> Consumption Items History</h2>
                                                <small class="fs-12 text-muted">

                                                </small>
                                            </div>
                                            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                                        </div>

                                        <div class="offcanvas-body overflow-auto py-3 cons-compact">
                                            @if($historyData->isEmpty())
                                            <div class="text-center text-muted py-4">
                                                No history available
                                            </div>
                                            @else

                                            <div class="card border-0 shadow-sm">
                                                <div class="card-body">

                                                    <h6 class="fw-bold mb-3"> History</h6>

                                                    @foreach($historyData as $history)

                                                    <div class="hstack gap-3 justify-content-between">

                                                        <div class="hstack gap-3">

                                                            {{-- Timeline Dot --}}

                                                            {{-- Content --}}
                                                            <div class="">
                                                                <div class="fs-11 text-primary fw-bold mb-1">
                                                                    Slip No: {{ $history->requestslip->requisition_slip_no ?? 'N/A' }}
                                                                </div>

                                                                <div class="fw-semibold">
                                                                    {{ $history->inventory->name ?? 'N/A' }}
                                                                </div>
                                                                @if($history->height)
                                                                <div class="fs-12 text-muted">
                                                                    Height: <strong>{{ $history->height ?? 0 }}</strong> |
                                                                    Width: <strong>{{ $history->width ?? 0 }}</strong>
                                                                </div>
                                                                @else
                                                                <div>
                                                                    Quantity:{{$history->quantity ?? 0 }}
                                                                </div>
                                                                @endif

                                                            </div>

                                                        </div>

                                                        {{-- Date --}}
                                                        <div class="fw-bold fs-12 text-muted">
                                                            {{ $history->created_at->format('d-m-Y H:i') }}
                                                        </div>

                                                    </div>

                                                    <hr class="border-dashed my-3">

                                                    @endforeach

                                                </div>
                                            </div>

                                            @endif


                                        </div>

                                        <div class="px-4 d-flex justify-content-end ht-80 border-top">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Close</button>
                                        </div>

                                    </div>
                                    @endforeach
                                    @endforeach
                                    @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">
                                            No KG Consumption Found
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>



                            {{-- Pagination --}}


                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection