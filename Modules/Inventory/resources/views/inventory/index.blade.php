@extends('shared::layouts.app')
@section('content')

    <!-- PAGE HEADER -->
    <div class="page-header d-flex justify-content-between align-items-center ">

        <!-- LEFT SIDE (DYNAMIC) -->
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">
                    {{ isset($item) ? 'Edit Item' : 'Add Item' }}
                </h5>
            </div>

            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                <li class="breadcrumb-item active">
                    {{ isset($item) ? 'Edit Item' : 'Add Item' }}
                </li>
            </ul>
        </div>

        <!-- RIGHT SIDE BUTTONS -->
        <div class="d-flex gap-2">
            <button id="filterBtn" class="btn btn-icon btn-light-brand" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                <i class="feather-filter"></i>
            </button>

            @if(!isset($item))
            <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                <i class="feather-plus me-2"></i> Create Inventory
            </button>
            @else
            <!-- Show Add New link when no menu is being edited -->
            <a href="{{ route('inventory.index') }}" class="btn btn-primary btn-sm">
                Add New
            </a>
            @endif
        </div>

    </div>

    <!-- SLIDE FORM -->
    <div class="main-content">
        <div id="collapseOne" class="accordion-collapse collapse page-header-collapse {{ isset($item) ? 'show' : '' }}">
            <div class="card ">
                <div class="card-body">
                    {{-- <h5 class="mb-3">Add / Edit Inventory</h5>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>Please fix the errors below</strong>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                    @endforeach
                    </ul>
                </div>
                @endif --}}

                <form action="{{ isset($item) ? route('inventory.update', $item->id) : route('inventory.store') }}" method="POST">
                    @csrf
                    @if(isset($item))
                    @method('PUT')
                    @endif

                    <div class="row g-4">

                        <div class="col-lg-4">
                            <label class="form-label">Item Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-text"><i class="feather-box"></i></div>
                                <input type="text" name="name"
                                    value="{{ old('name', $item->name ?? '') }}"
                                    class="form-control @error('name') is-invalid @enderror"
                                    placeholder="Enter Name">

                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label">Model <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-text"><i class="feather-box"></i></div>
                                <input type="text" name="model"
                                    value="{{ old('model', $item->model ?? '') }}"
                                    class="form-control @error('model') is-invalid @enderror"
                                    placeholder="Enter Model Name">

                                @error('model')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label">Min. Quantity <span class="text-danger">*</span></label>
                            {{-- <input type="number" step="0.01" name="min_quantity"
                                    value="{{ $item->min_quantity ?? '' }}"
                            class="form-control" placeholder="Enter Quantity" required> --}}
                            <input type="number" name="min_quantity"
                                value="{{ old('min_quantity', $item->min_quantity ?? '') }}"
                                class="form-control @error('min_quantity') is-invalid @enderror">

                            @error('min_quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- <div class="col-lg-4">
                                <label class="form-label">Category</label>
                                <input type="text" name="manufacturer"
                                    value="{{ old('manufacturer', $item->manufacturer ?? '') }}"
                        class="form-control">
                    </div> --}}

                    <div class="col-lg-4">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select name="category_id" data-select2-selector="status"
                            class="form-control @error('category_id') is-invalid @enderror">

                            <option value="">--Select Category--</option>
                            @foreach($categories as $c)
                            <option value="{{ $c->id }}"
                                {{ old('category_id', $item->category_id ?? '') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                            @endforeach
                        </select>

                        @error('category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">Classification <span class="text-danger">*</span></label>
                        <select name="classification" data-select2-selector="status" class="form-control  @error('classification') is-invalid @enderror ">
                            <option value="">--please select classification--</option>
                            <option value="FINISH"
                                {{ old('classification', $item->classification ?? '') == 'FINISH' ? 'selected' : '' }}>
                                FINISH
                            </option>
                            <option value="SEMI_FINISH"
                                {{ old('classification', $item->classification ?? '') == 'SEMI_FINISH' ? 'selected' : '' }}>
                                SEMI_FINISH
                            </option>
                        </select>
                        @error('classification')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">Placement <span class="text-danger">*</span></label>
                        <select name="placement" data-select2-selector="status" class="form-control  @error('placement') is-invalid @enderror ">
                            <option value="">--please select placement--</option>
                            @foreach($placements as $p)
                            <option value="{{ $p->name }}"
                                {{ old('placement', $item->placement ?? '') == $p->name ? 'selected' : '' }}>
                                {{ $p->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('placement')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">Grade</label>
                        {{-- <select name="grade" class="form-control" data-select2-selector="status"  required >
                                    <option value="">--Select Grade--</option>
                                    <option value="A (150kg)"{{ old('grade', $item->grade ?? '') == 'A (150kg)' ? 'selected' : '' }}>

                        A (150kg)
                        </option>
                        <option value="B (50-150kg)"
                            {{ old('grade', $item->grade ?? '') == 'B (50-150kg)' ? 'selected' : '' }}>
                            B (50-150kg)
                        </option>
                        <option value="C (Less Then 50kg)"
                            {{ old('grade', $item->grade ?? '') == 'C (Less Then 50kg)' ? 'selected' : '' }}>
                            C (Less Then 50kg)
                        </option>
                        </select> --}}
                        <select name="grade" data-select2-selector="status"
                            class="form-control @error('grade') is-invalid @enderror">

                            <option value="">--Select Grade--</option>
                            <option value="A (150kg)" {{ old('grade', $item->grade ?? '') == 'A (150kg)' ? 'selected' : '' }}>A (150kg)</option>
                            <option value="B (50-150kg)" {{ old('grade', $item->grade ?? '') == 'B (50-150kg)' ? 'selected' : '' }}>B (50-150kg)</option>
                            <option value="C (Less Then 50kg)" {{ old('grade', $item->grade ?? '') == 'C (Less Then 50kg)' ? 'selected' : '' }}>C (Less Then 50kg)</option>
                        </select>

                        @error('grade')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>


                    <div class="col-lg-4">
                        <label class="form-label">Unit <span class="text-danger">*</span></label>
                        <select name="unit" data-select2-selector="status"
                            class="form-control @error('unit') is-invalid @enderror">

                            <option value="">--Select Unit--</option>
                            @foreach($units as $u)
                            <option value="{{ $u->name }}"
                                {{ old('unit', $item->unit ?? '') == $u->name ? 'selected' : '' }}>
                                {{ $u->name }}
                            </option>
                            @endforeach
                        </select>


                        @error('unit')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">Height <span class="text-danger"></span></label>
                        {{-- <input type="number" step="0.01" name="height"
                                    value="{{ $item->min_quantity ?? '' }}"
                        class="form-control" placeholder="Enter Height" required> --}}
                        {{-- <input type="number" name="height" value="{{ $item->height ?? '' }}" class="form-control" placeholder="Enter Height"> --}}
                        <input type="number" name="height"
                            value="{{ old('height', $item->height ?? '') }}"
                            class="form-control @error('height') is-invalid @enderror">

                        @error('height')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">Width <span class="text-danger"></span></label>
                        <input type="number" name="width"
                            value="{{ $item->width ?? ''}}"
                            class="form-control" placeholder="Enter Width" @error('width') is-invalid @enderror>
                        @error('width')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-lg-4">
                        <label class="form-label">Length <span class="text-danger"></span></label>
                        <input type="number" name="length"
                            value="{{ old('length', $item->length ?? '')}}"
                            step="0.01"
                            class="form-control" placeholder="Enter Length" @error('length') is-invalid @enderror>
                        @error('length')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">Thikness <span class="text-danger"></span></label>
                        <input type="number" name="thikness"
                            value="{{ $item->thikness ?? ''}}"
                            class="form-control" placeholder="Enter Thikness" @error('thikness') is-invalid @enderror>
                        @error('thikness')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">Composition <span class="text-danger"></span></label>
                        <input type="text" name="composition"
                            value="{{ $item->composition ?? ''}}"
                            class="form-control" placeholder="Enter composition" @error('composition') is-invalid @enderror>
                        @error('composition')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">outer_diameter <span class="text-danger"></span></label>
                        <input type="text" name="outer_diameter"
                            value="{{ $item->outer_diameter ?? ''}}"
                            class="form-control" placeholder="Enter outer_diameter" @error('outer_diameter') is-invalid @enderror>
                        @error('outer_diameter')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">inner_diameter <span class="text-danger"></span></label>
                        <input type="text" name="inner_diameter"
                            value="{{ $item->inner_diameter ?? ''}}"
                            class="form-control" placeholder="Enter inner_diameter" @error('inner_diameter') is-invalid @enderror>
                        @error('inner_diameter')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

            </div>

            <div class="mt-3 d-flex justify-content-end">
                <button class="btn btn-primary">
                    {{ isset($item) ? 'Update Item' : 'Save Item' }}
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
                <form method="GET" action="{{ route('inventory.index') }}">
                    <div class="row g-3 align-items-end">

                        <!-- Filter: Display Name -->
                        <div class="col-md-3">
                            <label class="form-label">Name</label>
                            <select name="name" id="inventory-filter" class="form-control"></select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Category</label>
                            <select name="Category"
                                class="form-control " data-select2-selector="status">
                                <option value="">--Select Category--</option>
                                @foreach($categories as $c)
                                <option value="{{ $c->id }}"
                                    {{ request('Category') == $c->name ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Classification</label>
                            <select name="classification" class="form-control" data-select2-selector="status">
                                <option value="">-- Select classification --</option>

                                <option value="FINISH" {{ request('classification') == 'FINISH' ? 'selected' : '' }}>
                                    FINISH
                                </option>

                                <option value="SEMI_FINISH" {{ request('classification') == 'SEMI_FINISH' ? 'selected' : '' }}>
                                    SEMI_FINISH
                                </option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">placement</label>
                            <select name="placement" class="form-control" data-select2-selector="status">
                                <option value="">-- Select Placement --</option>

                                @foreach($placements as $item)
                                <option value="{{ $item->name }}"
                                    {{ request('placement') == $item->name ? 'selected' : '' }}>
                                    {{ $item->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- <div class="col-md-4">
                                <label class="form-label">Dymantion</label>
                                <input type="text" name="Dymantion" class="form-control"
                                    value="{{ request('Dymantion') }}" placeholder="Search by Display Name">
                            </div> -->

                        <!-- Filter: Unit -->
                        <!-- <div class="col-md-4">
                                <label class="form-label">Unit</label>
                                <input type="text" name="Unit" class="form-control"
                                    value="{{ request('Unit') }}" placeholder="Search by Unit">
                            </div> -->

                        <!-- Buttons -->
                        <div class="col-md-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-50">
                                <i class="feather-search"></i> Search
                            </button>

                            <a href="{{ route('inventory.index') }}" class="btn btn-light w-50">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card  stretch stretch-full">
        <div class="card-body ">
            <div class="d-flex justify-content-between align-items-center px-4 py-3">
                <h5 class="mb-0">Inventory List</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-striped" id="leadList">
                    <thead>
                        <tr>
                            <th>
                                Sr
                            </th>
                            <th>Name</th>
                            <th>Model</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Dimension</th>
                            <th>Length</th>
                            <th>classification</th>
                            <th>Placement</th>
                            <th>Composition</th>
                            <th>diameter</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($items as $index => $i)
                        <tr class="single-item">

                            <!-- Checkbox -->
                            <td>
                                {{ $items->firstItem() + $loop->index }}

                            </td>

                            <!-- Name -->
                            <td>
                                <a href="javascript:void(0)" class="hstack gap-3">
                                    <div>
                                        <span class="text-truncate-1-line">{{ $i->name }}</span>
                                    </div>
                                </a>
                            </td>

                            <td>{{ $i->model ?? '-' }}</td>

                            <td>{{ $i->category->name ?? '-' }}</td>

                            <!-- Qty -->
                            <td>{{ $i->min_quantity.' ('.$i->unit.')' }}</td>
                            <td>{{ $i->height }}×{{ $i->width }}</td>
                             <td>{{ $i->length ?? 'N/A' }}</td>
                            <td>{{$i->classification ?? 'N/A'}}</td>
                            <td>{{$i->placement ?? 'N/A'}}</td>
                            <td>{{$i->composition ?? 'N/A'}}</td>
                            <td>
                                <div><strong>OD:</strong> {{ $i->outer_diameter ?? 'N/A' }}</div>
                                <div><strong>ID:</strong> {{ $i->inner_diameter ?? 'N/A' }}</div>
                            </td>

                            <!-- Status With Select2 -->
                            {{-- <td>
                                    <span class="badge {{ !$i->is_deleted ? 'badge badge bg-soft-success text-success' : 'badge badge bg-soft-danger text-danger' }}">
                            {{ !$i->is_deleted ? 'Active' : 'Deleted' }}
                            </span>
                            </td> --}}

                            <!-- Actions -->
                            <td class="text-end">
                                <div class="hstack gap-2 justify-content-end">

                                    <!-- Edit -->
                                    <a href="{{ route('inventory.edit', $i->id) }}" class="avatar-text avatar-md">
                                        <i class="feather feather-edit-3"></i>
                                    </a>

                                    {{-- <form action="{{ route('inventory.toggle', $i->id) }}" method="POST"
                                    onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="dropdown-item text-center p-2">
                                        @if($i->is_deleted)
                                        <i class="feather feather-refresh-ccw"></i> <!-- Recover icon -->
                                        @else
                                        <i class="feather feather-trash-2"></i> <!-- Delete icon -->
                                        @endif
                                    </button>
                                    </form> --}}
                                    <button class="avatar-text avatar-md" data-bs-toggle="offcanvas" data-bs-target="#deleteInventory{{ $i->id }}">
                                        @if($i->is_deleted)
                                        <i class="feather feather-refresh-ccw"></i> <!-- Recover icon -->
                                        @else
                                        <i class="feather feather-trash-2"></i> <!-- Delete icon -->
                                        @endif
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <div class="offcanvas offcanvas-end" tabindex="-1" id="deleteInventory{{ $i->id }}">
                            <form method="POST"
                                action="{{ $i->is_deleted 
                                                    ? route('inventory.toggle', $i->id) 
                                                    : route('inventory.destroy', $i->id) }}">
                                @csrf

                                <!-- Header -->
                                <div class="offcanvas-header ht-80 px-4 border-bottom border-gray-5">
                                    <h2 class="fs-16 fw-bold mb-0">
                                        {{ $i->is_deleted ? 'Restore Inventory' : 'Delete Inventory' }}
                                    </h2>
                                    <button type="button" class="btn-close btn-close-gray ms-auto" data-bs-dismiss="offcanvas"></button>
                                </div>

                                <!-- Body -->
                                <div class="offcanvas-body">
                                    <p class="fs-15">
                                        {{ $i->is_deleted ? 'Are you sure you want to restore' : 'Are you sure you want to delete' }}
                                        <strong>{{ $i->name }}</strong>?
                                    </p>
                                </div>

                                <!-- Footer -->
                                <div class="px-4 gap-2 d-flex align-items-center justify-content-between ht-80 border-top border-gray-2">
                                    <button type="submit" class="btn btn-primary w-50">
                                        {{ $i->is_deleted ? 'Restore' : 'Yes, Delete' }}
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
                {{-- Pagination --}}
                <div class="mt-3">
                    {{ $items->links('pagination::bootstrap-5') }}
                </div>

            </div>
        </div>
    </div>
    </div>

    @push('scripts')
    <style>
        .select2-container {
            z-index: 9999 !important;
        }
        #filterCollapse,
        .card,
        .card-body {
            overflow: visible !important;
        }
    </style>
    <script>
        $(document).ready(function() {
            $('#inventory-filter').select2({
                placeholder: 'Inventory',
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

            // ✅ SELECTED VALUE SET KARNA (MOST IMPORTANT)
            let selectedinventoryname = "{{ request('name') }}";
            let selectedinventoryText = "{{ $inventoryName ?? '' }}";

            if (selectedinventoryname && selectedinventoryText) {
                let option = new Option(selectedinventoryText, selectedinventoryname, true, true);
                $('#inventory-filter').append(option).trigger('change');
            }
        });
    </script>  
    @endpush
@endsection