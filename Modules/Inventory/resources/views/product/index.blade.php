@extends('shared::layouts.app')
@section('content')

    <!-- PAGE HEADER -->
    <div class="page-header d-flex justify-content-between align-items-center">

        <!-- LEFT SIDE -->
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">
                    {{ $product ? 'Edit Machine' : 'Add Machine' }}
                </h5>
            </div>

            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                <li class="breadcrumb-item active">
                    {{ $product ? 'Edit Machine' : 'Add Machine' }}
                </li>
            </ul>
        </div>

        <!-- BUTTONS -->
        @if(!auth()->user()->isHOD())
        <div class="d-flex gap-2">
            <button id="filterBtn" class="btn btn-icon btn-light-brand" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                <i class="feather-filter"></i>
            </button>

            <button class="btn btn-success" data-bs-toggle="collapse" data-bs-target="#importCollapse">
                <i class="feather-upload me-2"></i> Import Excel
            </button>

            @if(!isset($product))
            <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                <i class="feather-plus me-2"></i> Create Machine
            </button>
            @else
            <a href="{{ route('product.index') }}" class="btn btn-primary btn-sm">
                Add New
            </a>
            @endif

            <div class="dropdown d-flex align-items-center">
                <button class="btn btn-icon btn-light-brand" data-bs-toggle="dropdown">
                    <i class="feather-paperclip"></i>
                </button>

                <span id="import-spinner" class="spinner-border spinner-border-sm text-primary ms-2 d-none"></span>

                <div class="dropdown-menu dropdown-menu-end">
                    <!-- ✅ EXPORT -->


                    <div class="dropdown-divider"></div>

                    <a href="{{route('product.sample.download')}}" class="dropdown-item">Download Sample</a>
                    <div class="dropdown-divider"></div>
                </div>
            </div>

        </div>
        @endif
    </div>


    <!-- SLIDE FORM -->
    <div class="main-content">
        <div id="collapseOne" class="accordion-collapse collapse page-header-collapse {{ isset($product) ? 'show' : '' }}">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <h5 class="mb-3">Add / Edit Machine</h5>
                        <button type="button" class="btn btn-outline-primary mt-2" id="btn-add-row">
                            + Add Item
                        </button>
                    </div>
                    <form action="{{ $product ? route('product.update', $product->id) : route('product.store') }}" method="POST">
                        @csrf

                        @if($product)
                        @method('post')
                        @endif

                        <div class="row g-4">

                            <!-- NAME -->
                            <div class="col-lg-4">
                                <label class="form-label">Machine Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" placeholder="Enter Machine Name"
                                    value="{{ old('name', $product->name ?? '') }}">

                                @if ($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                                @endif
                            </div>

                            <!-- ESTIMATION BUDGET -->
                            <!-- <div class="col-lg-4">
                                <label class="form-label">Estimation Budget <span>(&#8377;)</span> <span class="text-danger">*</span></label>
                                <input type="number" name="estimation_budget" class="form-control {{ $errors->has('estimation_budget') ? 'is-invalid' : '' }}"
                                    value="{{ old('estimation_budget', $product->estimation_budget ?? '') }}"
                                    placeholder="Enter Estimated Budget">

                                @if ($errors->has('estimation_budget'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('estimation_budget') }}
                                </div>
                                @endif
                            </div> -->

                            <!-- ESTIMATION DURATION -->
                            <div class="col-lg-4">
                                <label class="form-label">Estimation Duration <span>(In-Day) </span> <span class="text-danger">*</span></label>
                                <input type="number" name="estimation_duration" class="form-control  {{ $errors->has('estimation_duration') ? 'is-invalid' : '' }}" placeholder="Enter Estimation Duration"
                                    value="{{ old('estimation_duration', $product->estimation_duration ?? '') }}">

                                @if ($errors->has('estimation_duration'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('estimation_duration') }}
                                </div>
                                @endif
                            </div>

                            <!-- ITEMS -->
                            <div class="col-lg-4">
                                <label class="form-label">Items (Inventory) <span class="text-danger">*</span> </label>

                                <div id="item-rows">

                                    @php
                                    $invList = old('inventory_id', $product ? $product->productItems->pluck('inventory_id')->toArray() : [null]);
                                    $qtyList = old('quantity', $product ? $product->productItems->pluck('quantity')->toArray() : [1]);
                                    @endphp

                                    @foreach($invList as $idx => $inv)
                                    <div class="row g-2 mb-2 item-row">
                                        <div class="col-7">
                                            <select name="inventory_id[]"
                                                class="form-control inventory-select"
                                                data-selected-id="{{ $inv ?? '' }}"
                                                data-selected-text="{{ 
        $inv ? ($inventories->firstWhere('id', $inv)?->name . ' ' . $inventories->firstWhere('id', $inv)?->model) : '' 
    }}">
                                            </select>
                                        </div>

                                        <div class="col-3">
                                            <input type="number" name="quantity[]" class="form-control" min="1" step="0.01"
                                                value="{{ $qtyList[$idx] ?? 1 }}">
                                        </div>

                                        <div class="col-2">
                                            <button type="button" class="btn btn-danger w-100 btn-remove-row">-</button>
                                        </div>
                                    </div>
                                    @endforeach

                                </div>

                                @if ($errors->has('inventory_id.0') || $errors->has('inventory_id.*'))
                                <div class="text-danger mt-1 small">
                                    {{ $errors->first('inventory_id.0') ?? $errors->first('inventory_id.*') }}
                                </div>
                                @endif

                            </div>
                        </div>

                        <div class="mt-3 d-flex justify-content-end">
                            <button class="btn btn-primary">
                                {{ $product ? 'Update Product' : 'Save Product' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- filterCollapse --}}
        <div class="collapse mb-3" id="filterCollapse">
            <div class="card border shadow-sm">
                <div class="card-body">
                    <form method="GET" action="{{ route('product.index') }}">
                        <div class="row g-3 align-items-end">

                            <!-- Filter: Display Name -->
                            <div class="col-md-4">
                                <label class="form-label">Name</label>
                                <select name="name" class="form-control" data-select2-selector="status">
                                    <option value="">-- Select Machine --</option>

                                    @foreach($allproducts as $product)
                                    <option value="{{ $product->name }}"
                                        {{ request('name') == $product->name ? 'selected' : '' }}>
                                        {{ $product->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Filter: Items -->
                            <!-- <div class="col-md-4">
                                <label class="form-label">Estimation Budget</label>
                                <input type="text" name="Estimation_Budget" class="form-control"
                                    value="{{ request('Estimation_Budget') }}" placeholder="Search by Estimation Budget">
                            </div> -->

                            <!-- Buttons -->
                            <div class="col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-50">
                                    <i class="feather-search"></i> Search
                                </button>

                                <a href="{{ route('product.index') }}" class="btn btn-light w-50">
                                    Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="collapse mb-3" id="importCollapse">
            <div class="card border shadow-sm">
                <div class="card-body">

                    <form action="{{ route('product.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row g-3 align-items-end">

                            <!-- File Upload -->
                            <div class="col-md-6">
                                <label class="form-label">Upload Excel</label>
                                <input type="file" name="file" class="form-control" required>
                            </div>

                            <!-- Info -->


                            <!-- Buttons -->
                            <div class="col-md-6 d-flex gap-2">
                                <button type="submit" class="btn btn-success w-50">
                                    <i class="feather-upload"></i> Import
                                </button>

                                <button type="button" class="btn btn-light w-50" data-bs-toggle="collapse" data-bs-target="#importCollapse">
                                    Cancel
                                </button>
                            </div>

                        </div>

                    </form>

                </div>
            </div>
        </div>

        <!-- TABLE -->
        <div class="card stretch stretch-full">
            <div class="card-body p-0">

                <div class="d-flex justify-content-between align-items-center px-4 py-3">
                    <h5 class="mb-0">Machine List</h5>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Sr</th>
                                <th style="width:420px; min-width:420px">Name</th>
                                <!-- <th>Items</th> -->
                                <!-- <th>Estimation Budget</th> -->
                                <th>Estimation Duration</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($products ?? [] as $index => $p)
                            <tr>
                                <td>{{ $index + 1 }}</td>

                                <td class="fw-bold text-dark fs-15" style="white-space: normal; word-break: break-word;">
                                    {{ $p->name }}

                                </td>

                                <!-- <td>
                                    @foreach($p->productItems as $i)
                                    <div>{{ $i->inventory->name ?? '-' }} ({{ $i->quantity }})</div>
                                    @endforeach
                                </td> -->

                                <!-- <td>
                                    <a href="javascript:void(0)" class="hstack gap-3">
                                        <div>{{ $p->estimation_budget }}</div>
                                    </a>
                                </td> -->

                                <td>
                                    <a href="javascript:void(0)" class="hstack gap-3">
                                        <div>{{ $p->estimation_duration ? $p->estimation_duration . ' Days' : '-' }}</div>
                                    </a>
                                </td>

                                <td>
                                    <span class="badge {{ !$p->is_deleted ? 'badge badge bg-soft-success text-success' : 'badge badge bg-soft-danger text-danger' }}">
                                        {{ !$p->is_deleted ? 'Active' : 'Deleted' }}
                                    </span>
                                </td>

                                <td class="text-end">
                                    <div class="hstack gap-2 justify-content-end">

                                        <a href="{{ route('product.product.pdf', $p->id) }}" class="avatar-text avatar-md">
                                            <i class="feather feather-download"></i>
                                        </a>


                                        <a href="{{ route('product.edit', $p->id) }}" class="avatar-text avatar-md">
                                            <i class="feather-edit-3"></i>
                                        </a>

                                        {{-- <form action="{{ $p->is_deleted ? route('product.restore', $p->id) : route('product.delete', $p->id) }}"
                                        method="GET"
                                        onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        @method('PUT')

                                        <button class="dropdown-item text-center p-2">
                                            @if($p->is_deleted)
                                            <i class="feather feather-refresh-ccw"></i> <!-- Recover Icon -->
                                            @else
                                            <i class="feather feather-trash-2"></i> <!-- Delete Icon -->
                                            @endif
                                        </button>
                                        </form> --}}
                                        <button class="avatar-text avatar-md" data-bs-toggle="offcanvas" data-bs-target="#deleteProduct{{  $p->id }}">
                                            @if($p->is_deleted)
                                            <i class="feather feather-refresh-ccw"></i>
                                            @else
                                            <i class="feather feather-trash-2"></i>
                                            @endif
                                        </button>

                                        <a href="{{ route('product.view', $p->id) }}" class="avatar-text avatar-md">
                                            <i class="fa fa-eye"></i>
                                        </a>

                                        <a href="{{ route('product.duplicate', $p->id) }}" class="avatar-text avatar-md" title="Duplicate">
                                            <i class="fa fa-copy"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <div class="offcanvas offcanvas-end" tabindex="-1" id="deleteProduct{{ $p->id }}">
                                <form method="get"
                                    action="{{$p->is_deleted 
                                                    ? route('product.restore', $p->id) 
                                                    : route('product.delete', $p->id) }}">
                                    @csrf

                                    <!-- Header -->
                                    <div class="offcanvas-header ht-80 px-4 border-bottom border-gray-5">
                                        <h2 class="fs-16 fw-bold mb-0">
                                            {{$p->is_deleted ? 'Restore Product' : 'Delete Product' }}
                                        </h2>
                                        <button type="button" class="btn-close btn-close-gray ms-auto" data-bs-dismiss="offcanvas"></button>
                                    </div>

                                    <!-- Body -->
                                    <div class="offcanvas-body">
                                        <p class="fs-15">
                                            {{$p->is_deleted ? 'Are you sure you want to restore' : 'Are you sure you want to delete' }}
                                            <strong>{{ $p->name }}</strong>?
                                        </p>
                                    </div>

                                    <!-- Footer -->
                                    <div class="px-4 gap-2 d-flex align-items-center justify-content-between ht-80 border-top border-gray-2">
                                        <button type="submit" class="btn btn-primary w-50">
                                            {{$p->is_deleted ? 'Restore' : 'Yes, Delete' }}
                                        </button>

                                        <button type="button" class="btn btn-danger w-50" data-bs-dismiss="offcanvas">
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                    {{ $products->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {

            const container = document.getElementById('item-rows');
            const btnAdd = document.getElementById('btn-add-row');

            // ✅ Initialize Select2 with AJAX
            function initSelect2(context = document) {
                $(context).find('.inventory-select').each(function() {
                    let $this = $(this);

                    // prevent duplicate init
                    if ($this.hasClass("select2-hidden-accessible")) return;

                    $this.select2({
                        theme: 'bootstrap-5',
                        placeholder: 'Select Item',
                        width: '100%',
                        ajax: {
                            url: "{{ route('inventory.search') }}",
                            dataType: 'json',
                            delay: 250,
                            data: function(params) {
                                return {
                                    q: params.term // search text
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

                    // ✅ Show old selected value (edit/validation case)
                    let selectedId = $this.data('selected-id');
                    let selectedText = $this.data('selected-text');

                    if (selectedId && selectedText) {
                        let option = new Option(selectedText, selectedId, true, true);
                        $this.append(option).trigger('change');
                    }
                });
            }

            // ✅ Remove row
            function attachRemoveButtons() {
                container.querySelectorAll('.btn-remove-row').forEach(btn => {
                    btn.onclick = function() {
                        const rows = container.querySelectorAll('.item-row');
                        if (rows.length > 1) {
                            this.closest('.item-row').remove();
                        }
                    };
                });
            }

            // ✅ Add new row
            btnAdd?.addEventListener('click', function() {

                const row = container.querySelector('.item-row');
                const clone = row.cloneNode(true);

                // reset inputs
                clone.querySelectorAll('select, input').forEach(el => {
                    if (el.tagName === 'SELECT') {
                        el.innerHTML = ''; // remove old options
                        el.classList.remove("select2-hidden-accessible");

                        // remove old selected data
                        el.removeAttribute('data-selected-id');
                        el.removeAttribute('data-selected-text');

                        // remove select2 container if exists
                        let next = el.nextElementSibling;
                        if (next && next.classList.contains('select2')) {
                            next.remove();
                        }
                    }

                    if (el.tagName === 'INPUT') {
                        el.value = 1;
                    }
                });

                container.appendChild(clone);

                // re-init only new row
                initSelect2(clone);
                attachRemoveButtons();
            });

            // ✅ Initial load
            initSelect2();
            attachRemoveButtons();

        });
    </script>
    @endpush
@endsection