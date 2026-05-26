@extends('shared::layouts.app')
@section('content')
    {{-- PAGE HEADER --}}
    <div class="page-header d-flex justify-content-between align-items-center">

    {{-- LEFT SIDE (Title + Breadcrumb) --}}
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Purchase Request slip </h5>
        </div>
        <ul class="breadcrumb mb-0">
            <li class="breadcrumb-item">
                <a href="/dashboard">Home</a>
            </li>
            <li class="breadcrumb-item active">Purchase Request</li>
        </ul>
    </div>

    <div class="page-header-right ms-auto d-flex align-items-center gap-2">

            <button class="btn btn-primary" id="convertSelected" disabled>
            Convert Selected
        </button>

            <div class="page-header-right-items">

                {{-- Mobile back (when right panel open) --}}
                <div class="d-flex d-md-none">
                    <a href="javascript:void(0)" class="page-header-right-close-toggle">
                        <i class="feather-arrow-left me-2"></i>
                        <span>Back</span>
                    </a>
                </div>

                {{-- Right side buttons --}}
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">

                    <button id="filterBtn" class="btn btn-icon btn-light-brand" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                        <i class="feather-filter"></i>
                    </button>

                    <a href="{{ route('purchase_request.create') }}" class="btn btn-primary ">
                        <i class="feather-plus me-2"></i> Create Purchase 
                    </a>

                </div>

            </div>

            {{-- Mobile open toggle (hamburger right panel) --}}
            <div class="d-md-none d-flex align-items-center">
                <a href="javascript:void(0)" class="page-header-right-open-toggle">
                    <i class="feather-align-right fs-20"></i>
                </a>
            </div>
        </div>

</div>


    {{-- MAIN CONTENT --}}
    <div class="main-content">

     {{-- filterCollapse --}}
        <div class="collapse mb-3" id="filterCollapse">
            <div class="card border shadow-sm">
                <div class="card-body">
                    <form method="GET" action="{{ route('purchase_request.add') }}">
                        <div class="row g-3 align-items-end">

                            <!-- Filter: Display Name -->
                            <div class="col-md-4">
                                <label class="form-label">Name</label>
                                <input type="text" name="name" class="form-control"
                                    value="{{ request('name') }}" placeholder="Search by Item Name">
                            </div>

                
                            <!-- Buttons -->
                            <div class="col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-50">
                                    <i class="feather-search"></i> Search
                                </button>

                                <a href="{{ route('purchase_request.add') }}" class="btn btn-light w-50">
                                    Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="requestSlipList">
                                <thead>
                                    <tr>
                                        <th class="wd-30">
                                            <div class="btn-group mb-1">
                                                <div class="custom-control custom-checkbox ms-1">
                                                    <input type="checkbox" id="checkAll" class="custom-control-input">
                                                    <label class="custom-control-label" for="checkAll"></label>
                                                </div>
                                            </div>
                                        </th>
                                        <th>Sr</th>
                                        <th>item Name </th>
                                        <th>Quantity </th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($Request_items as $index => $item)                                    
                               <tr class="single-item">
                                        <td>
                                            <div class=" ms-1">
                                                <div class="custom-control custom-checkbox">

                                                    <input type="checkbox"
                                                        class="item-checkbox custom-control-input checkbox"
                                                        value="{{ $item->id }}"
                                                        id="{{ $item->id }}">
                                                    <label class="custom-control-label" for="{{ $item->id }}"></label>
                                                </div>
                                            </div>
                                        </td>

                                        <td>{{ $index + 1}}</td>

                                        <td>{{ $item->inventory->name}}</td>

                                        <td>
                                            {{ $item->order_qty ?? 'N/A' }}
                                        </td>

                                        {{-- Actions --}}
                                        <td class="d-flex gap-2 justify-content-center">
                                            <a href="{{ route('purchase_request.create') }}?req_id={{ $item->id }}"
                                                class="btn btn-light btn-sm"
                                                title="convert">
                                                <i class="feather feather-refresh-ccw"></i>
                                            </a>

                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">
                                            No Request Slips found
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>


                        </div> {{-- table-responsive --}}

                       

                    </div>
                </div>
            </div>
        </div>
    </div>

   <script>
    const checkAll = document.getElementById('checkAll');
    const convertBtn = document.getElementById('convertSelected');

    function toggleConvertButton() {
        const checkedCount = document.querySelectorAll('.item-checkbox:checked').length;
        convertBtn.disabled = checkedCount === 0;
    }

    // Select All checkbox
    checkAll.addEventListener('change', function () {
        document.querySelectorAll('.item-checkbox').forEach(cb => {
            cb.checked = this.checked;
        });
        toggleConvertButton();
    });

    // Individual checkbox change
    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('item-checkbox')) {
            toggleConvertButton();
        }
    });

    // Convert Selected click
    convertBtn.addEventListener('click', function () {
        let selected = [];

        document.querySelectorAll('.item-checkbox:checked').forEach(cb => {
            selected.push(cb.value);
        });

        let url = "{{ route('purchase_request.create') }}" + "?req_ids=" + selected.join(',');
        window.location.href = url;
    });
</script>



@endsection