@extends('shared::layouts.app')
@section('content')

    <!-- PAGE HEADER -->
    <div class="page-header d-flex justify-content-between align-items-center ">

        <!-- LEFT SIDE -->
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">
                    {{ $project ? 'Edit Project' : 'Add Project' }}
                </h5>
            </div>

            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                <li class="breadcrumb-item active">
                    {{ $project ? 'Edit Project' : 'Add Project' }}
                </li>
            </ul>
        </div>

        <!-- RIGHT SIDE BUTTONS -->
        <div class="d-flex gap-2">
            <button id="filterBtn" class="btn btn-icon btn-light-brand" data-bs-toggle="collapse"
                data-bs-target="#filterCollapse">
                <i class="feather-filter"></i>
            </button>

            @if(!isset($project))
            @if(auth()->user()->role_id != 6)
            <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                <i class="feather-plus me-2"></i> Create Project
            </button>
            @endif
            @endif
        </div>

    </div>

    <!-- SLIDE FORM -->
    <div class="main-content">
        <div id="collapseOne" class="accordion-collapse collapse page-header-collapse {{ isset($project) ? 'show' : '' }}">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <h5 class="mb-3">Add / Edit Project</h5>
                    </div>

                    <form action="{{ $project ? route('project.update', $project->id) : route('project.store') }}"
                        method="POST" id="projectForm">
                        @csrf
                        @if($project)
                        @method('Post')
                        @endif

                        <div class="row g-4">

                            <!-- Project Name -->
                            <div class="col-lg-4">
                                <label class="form-label">Project Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-text"><i class="feather-file-text"></i></div>
                                    <input type="text" name="name"
                                        value="{{ old('name', $project->name ?? '') }}"
                                        class="form-control @error('name') is-invalid @enderror"
                                        placeholder="Enter project name">

                                    @error('name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="col-lg-4">
                                <label class="form-label">Project Status</label>
                                @php
                                $currentStatus = old('status', $project->status ?? 'new');
                                @endphp
                                <select name="status" class="form-control @error('status') is-invalid @enderror" data-select2-selector="status" required>
                                    <option value="new" {{ $currentStatus == 'new' ? 'selected' : '' }}>New</option>
                                    <option value="in_progress" {{ $currentStatus == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="completed" {{ $currentStatus == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ $currentStatus == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    <option value="hold" {{ $currentStatus == 'hold' ? 'selected' : '' }}>Hold</option>
                                </select>
                                @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-lg-4">
                                <label class="form-label">Project Priority</label>
                                <select name="priority" class="form-control" data-select2-selector="status">
                                    <option value="NORMAL" {{ (old('priority', $project->priority ?? 'NORMAL') === 'NORMAL') ? 'selected' : '' }}>NORMAL</option>
                                    <option value="LOW" {{ (old('priority', $project->priority ?? 'NORMAL') === 'LOW') ? 'selected' : '' }}>LOW</option>
                                    <option value="HIGH" {{ (old('priority', $project->priority ?? 'NORMAL') === 'HIGH') ? 'selected' : '' }}>HIGH</option>
                                    <option value="URGENT" {{ (old('priority', $project->priority ?? 'NORMAL') === 'URGENT') ? 'selected' : '' }}>URGENT</option>
                                </select>
                            </div>

                            <!-- start date -->
                            <div class="col-lg-4">
                                <label class="form-label">Start Date</label>
                                <div class="input-group">
                                    <div class="input-group-text"><i class="feather-calendar"></i></div>
                                    <input type="date" name="start_date"
                                        value="{{ old('start_date', $project->start_date ?? '') }}"
                                        class="form-control">
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <label class="form-label">End date</label>
                                <div class="input-group">
                                    <div class="input-group-text"><i class="feather-calendar"></i></div>
                                    <input type="date" name="end_date" value="{{ old('end_date', $project->end_date ?? '') }}" class="form-control">
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <label class="form-label">
                                    Budget <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <div class="input-group-text"><i class="feather-dollar-sign"></i></div>
                                    <input type="number" name="budget" placeholder="Enter Project Budget"
                                        class="form-control @error('budget') is-invalid @enderror"
                                        value="{{ old('budget', $project->budget ?? '') }}"
                                        required>

                                    @error('budget')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>


                            <!-- Products -->
                            <div class="col-lg-6">
                                <div class="d-flex gap-2 align-items-center">

                                    <label class="form-label mb-2">
                                        Machines for this Project <span class="text-danger">*</span>
                                    </label>

                                    <button type="button" class="btn btn-sm btn-outline-primary mb-2" id="btn-add-row">
                                        + Add Machine
                                    </button>

                                </div>

                                <div id="product-rows">
                                    @php
                                    $oldProducts = old(
                                    'product_id',
                                    $project ? $project->projectProducts->pluck('product_id')->toArray() : [null]
                                    );

                                    $oldQty = old(
                                    'quantity',
                                    $project ? $project->projectProducts->pluck('quantity')->toArray() : [1]
                                    );

                                    $oldStatus = old(
                                    'product_status',
                                    $project ? $project->projectProducts->pluck('status')->toArray() : [null]
                                    );
                                    @endphp

                                    @foreach($oldProducts as $idx => $pid)
                                    <div class="row g-2 mb-2 project-product-row">

                                        {{-- PRODUCT --}}
                                        <div class="col-5">
                                            <select name="product_id[]" class="form-control product-select" data-select2-selector="status">
                                                <option value="">Select Product</option>
                                                @foreach($products as $prod)
                                                <option value="{{ $prod->id }}"
                                                    {{ (int)$pid === (int)$prod->id ? 'selected' : '' }}>
                                                    {{ $prod->code }} - {{ $prod->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- STATUS --}}
                                        <div class="col-3">
                                            <select name="product_status[]" class="form-control" data-select2-selector="status">
                                                <option value="">Select Status</option>
                                                @foreach(['Fabrication','Assembly','Machining','Completed'] as $st)
                                                <option value="{{ $st }}"
                                                    {{ ($oldStatus[$idx] ?? null) == $st ? 'selected' : '' }}>
                                                    {{ $st }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- {{-- QUANTITY --}}
                                        <div class="col-2">
                                            <input type="number"
                                                name="quantity[]"
                                                min="1"
                                                class="form-control"
                                                value="{{ $oldQty[$idx] ?? 1 }}">
                                        </div> -->

                                        {{-- REMOVE --}}
                                        <div class="col-1">
                                            <button type="button" class="btn btn-danger w-100 btn-remove-row">-</button>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                {{-- ✅ client-side error (minimum 1 product mandatory) --}}
                                <div class="text-danger small mt-1 d-none" id="productError">
                                    Please select at least one Product.
                                </div>
                            </div>

                            <div class="col-lg-6">

                                <div class="d-flex gap-2 align-items-center">

                                    <label class="form-label">Inventory Items (Optional)</label>

                                    <button type="button" class="btn btn-outline-primary mb-2" id="add-inventory-row">
                                        + Add Item
                                    </button>

                                </div>




                                <div id="inventory-rows">
                                    {{-- dd($project->ProductItem); --}}
                                    @php
                                    /**
                                    * Inventory OPTIONAL:
                                    * direct Project ke projectItems (project_items table) se load hoga.
                                    * Assumption: Project model: projectItems() relation exists.
                                    */
                                    $invList = old(
                                    'inventory_id',
                                    $project ? $project->projectItems->pluck('inventory_id')->toArray() : []
                                    );

                                    $qtyList = old(
                                    'inventory_qty',
                                    $project ? $project->projectItems->pluck('quantity')->toArray() : []
                                    );

                                    $lengthList = old(
                                    'length',
                                    $project ? $project->projectItems->pluck('length')->toArray() : []
                                    );

                                    // ✅ optional hai, but UI ke liye ek empty row show
                                    if (empty($invList)) {
                                    $invList = [null];
                                    $qtyList = [1];
                                    $lengthList = [''];
                                    }
                                    @endphp
                                    @foreach($invList as $idx => $inv)
                                    <div class="row g-2 mb-2 inventory-row">
                                        <div class="col-4">
                                            <select name="inventory_id[]" class="form-control @error('inventory_id.*') is-invalid @enderror">
                                                <option value="">Select Item</option>
                                                @foreach($inventories as $it)
                                                <option value="{{ $it->id }}"
                                                    {{ (int)$inv === (int)$it->id ? 'selected' : '' }}>
                                                    {{ $it->name }}{!! $it->model ? ' ['.$it->model.']' : '' !!}
                                                </option>
                                                @endforeach
                                            </select>
                                            @error('inventory_id.*')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-4">
                                            <input type="text"
                                                name="length[]"
                                                class="form-control  @error('length.*') is-invalid @enderror"
                                                placeholder="Enter length/dimension"
                                                value="{{ $lengthList[$idx] ?? '' }}">
                                        </div>

                                        <div class="col-2">
                                            <input type="number"
                                                name="inventory_qty[]"
                                                class="form-control  @error('inventory_qty.*') is-invalid @enderror"
                                                min="1"
                                                value="{{ $qtyList[$idx] ?? 1 }}">
                                        </div>

                                        <div class="col-1">
                                            <button type="button"
                                                class="btn btn-danger w-100 btn-remove-row">-</button>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="col-lg-4">
                                @php
                                $refurbishVal = old(
                                'refurbish',
                                isset($project) ? (int)($project->refurbish ?? 0) : 0
                                );
                                @endphp

                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body py-3">
                                        <div class="d-flex align-items-start justify-content-between">
                                            <div class="me-3">
                                                <label class="form-label fw-semibold mb-1">
                                                    Old / Refurbished
                                                </label>
                                                <div class="text-muted small">
                                                    This project is marked as refurbished
                                                </div>
                                            </div>

                                            <div class="form-check form-switch m-0">
                                                {{-- OFF ke liye hidden --}}
                                                @if(!$refurbishVal)
                                                <input type="hidden" name="refurbish" value="0">
                                                @endif

                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    id="refurbishToggle"
                                                    name="refurbish"
                                                    value="1"
                                                    {{ $refurbishVal ? 'checked disabled' : '' }}>
                                            </div>
                                        </div>

                                        {{-- disabled checkbox ka value preserve --}}
                                        @if($refurbishVal)
                                        <input type="hidden" name="refurbish" value="1">
                                        @endif
                                    </div>
                                </div>
                            </div>


                            <div class="col-lg-8">
                                <label class="form-label">Note</label>
                                <div class="input-group">
                                    <textarea
                                        name="not"
                                        class="form-control @error('not') is-invalid @enderror"
                                        placeholder="Enter Comment"
                                        rows="4">{{ old('not', $project->comment ?? '') }}</textarea>

                                    @error('not')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            {{-- ================= PROJECT STAGES ================= --}}
                            <div class="col-12">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-white border-bottom">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-1">Project Stages</h5>
                                                <small class="text-muted">
                                                    Select section, parent stages and sub stages status
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-body">

                                        <div id="stage-wrapper">

                                            {{-- ================= ROW ================= --}}
                                            <div class="stage-row border rounded-3 p-3 mb-3 bg-light-subtle">

                                                <div class="row g-3">

                                                    {{-- SECTION --}}
                                                    <div class="col-lg-4">
                                                        <label class="form-label fw-semibold">
                                                            Work Flow
                                                        </label>

                                                        <select name="work_flow"
                                                            class="form-control section-select"
                                                            data-select2-selector="status">

                                                            <option value="">Select Work Flow</option>

                                                            @foreach($sections as $section)
                                                            <option value="{{ $section }}" {{ old('work_flow', $project->work_flow ?? '') == $section ? 'selected' : '' }}>
                                                                {{ $section }}
                                                            </option>
                                                            @endforeach

                                                        </select>
                                                    </div>




                                                </div>


                                            </div>

                                        </div>

                                    </div>
                                </div>
                            </div>

                            {{-- <div class="col-lg-4">
                                <label class="form-label">End date</label>
                                <input type="date" name="end_date" value="{{ old('end_date', $project->end_date ?? '') }}">
                        </div> --}}
                </div>

                <div class="mt-3 d-flex justify-content-end">
                    <button class="btn btn-primary">
                        {{ $project ? 'Update Project' : 'Save Project' }}
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
                <form method="GET" action="{{ route('project.index') }}">
                    <div class="row g-3">

                        <!-- Project Name -->
                        <div class="col-md-3">
                            <label class="form-label">Project Name</label>
                            <select name="name" class="form-select" data-select2-selector="status">
                                <option value="">-- Select Project -- </option>
                                @foreach ($productsfilter as $project)
                                <option value="{{ $project->name }}" {{ request('name')==$project->name ? 'selected' : '' }}>{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" data-select2-selector="status">
                                <option value="">All</option>
                                <option value="new" {{ request('status')=='new' ? 'selected' : '' }}>New</option>
                                <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in_progress" {{ request('status')=='in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ request('status')=='completed' ? 'selected' : '' }}>Completed</option>
                                <option value="Hold" {{ request('status')=='Hold' ? 'selected' : '' }}>Hold</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Project Priority</label>
                            <select name="priority" class="form-select" data-select2-selector="status">
                                <option value="">All</option>
                                <option value="NORMAL" {{ request('status')=='NORMAL' ? 'selected' : '' }}>NORMAL</option>
                                <option value="LOW" {{ request('status')=='LOW' ? 'selected' : '' }}>LOW</option>
                                <option value="HIGH" {{ request('status')=='HIGH' ? 'selected' : '' }}>HIGH</option>
                                <option value="URGENT" {{ request('status')=='URGENT' ? 'selected' : '' }}>URGENT</option>
                            </select>
                        </div>

                        <!-- Machine -->
                        <div class="col-md-3">
                            <label class="form-label">Machine</label>
                            <select name="machine_id" class="form-select" data-select2-selector="status">
                                <option value="">-- All Machines --</option>
                                @foreach($products as $m)
                                <option value="{{ $m->id }}" {{ request('machine_id') == $m->id ? 'selected' : '' }}>
                                    {{ $m->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>


                        <!-- Buttons -->
                        <div class="col-md-12 d-flex justify-content-end gap-2 mt-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="feather-search"></i> Search
                            </button>

                            <a href="{{ route('project.index') }}" class="btn btn-light">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- TABLE -->
    <div class="card stretch stretch-full">
        <div class="card-body p-0">

            <div class="table-responsive">
                <table class="table table-hover" id="leadList">
                    <thead>
                        <tr>
                            <th>Sr</th>
                            <th>Progress</th>
                            <th>Name</th>
                            <th>Project Date</th>
                            <th scope="col">Remaining</th>
                            <!-- <th style="min-width: 200px; max-width: 300px;">Comment</th> -->
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($projects as $index => $p)
                        <tr style="{{  $p->is_late ? 'background-color: #f8d7da !important;' : '' }}">

                            <td>{{ $projects->firstItem() + $index }}</td>
                            <td style="width:90px; min-width:90px;">

                                <div class="stage-circle">

                                    <div class="team-progress-{{ $index + 1 }}"
                                        data-progress="{{ $p->progress }}">
                                    </div>

                                </div>

                            </td>

                            <td>
                                @php
                                $statusClasses = [
                                'new' => 'bg-soft-primary text-primary',
                                'in_progress' => 'bg-soft-warning text-warning',
                                'completed' => 'bg-soft-success text-success',
                                'cancelled' => 'bg-soft-danger text-danger',
                                'hold' => 'bg-soft-danger text-danger',
                                ];

                                $priorityClasses = [
                                'HIGH' => 'bg-soft-primary text-primary',
                                'LOW' => 'bg-soft-warning text-warning',
                                'NORMAL' => 'bg-soft-success text-success',
                                'URGENT' => 'bg-soft-danger text-danger',
                                ];
                                @endphp

                                <a href="javascript:void(0)" class="hstack gap-3">
                                    <div>
                                        <span class="fw-semibold d-block">{{ $p->name }}</span>

                                        <div class="d-flex gap-1 flex-wrap mt-1">

                                            {{-- Status --}}
                                            <span class="badge {{ $statusClasses[$p->status] ?? 'bg-secondary' }}">
                                                {{ ucfirst(str_replace('_', ' ', $p->status)) }}
                                            </span>

                                            {{-- Priority --}}
                                            <span class="badge {{ $priorityClasses[$p->priority] ?? 'bg-secondary' }}">
                                                {{ $p->priority ?? 'N/A' }}
                                            </span>

                                            {{-- Refurbished --}}
                                            @if((int)($p->refurbish ?? 0) === 1)
                                            <span class="badge bg-warning-subtle text-warning border">
                                                Refurbished
                                            </span>
                                            @endif

                                            @if($p->comment)
                                            <button type="button"
                                                class="btn btn-sm btn-outline-info read-comment-btn d-inline-flex align-items-center gap-1 px-2 py-1"
                                                data-comment="{{ htmlspecialchars($p->comment, ENT_QUOTES) }}"
                                                data-user="{{ $p->user->name ?? 'System' }}"
                                                data-bs-toggle="modal"
                                                data-bs-target="#commentModal">

                                                <i class="fa fa-comment-dots"></i>
                                                Comment

                                            </button>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            </td>

                            <td>
                                <div class="small">
                                    <div>
                                        <strong>Start Date:</strong>
                                        {{$p->start_date ?? '-'}}
                                    </div>
                                    <div>
                                        <strong>End Date:</strong>
                                        {{ $p->end_date ? \Carbon\Carbon::parse($p->end_date)->format('d-m-Y') : 'N/A' }}
                                        @if($p->is_near_deadline && strtolower($p->status) !== 'completed') <span class="deadline-indicator">
                                            <span class="dot"></span>
                                            <span class="pulse"></span>
                                        </span>
                                        @endif
                                    </div>
                                    @if($p->completion_date)
                                    <div>
                                        <strong>Completed:</strong>
                                        {{ $p->completion_date ? \Carbon\Carbon::parse($p->completion_date)->format('d-m-Y') : 'N/A' }}
                                    </div>
                                    @endif

                                    @if($p->is_late && is_array($p->is_late) && $p->is_late['late'])
                                    <div>
                                        <strong class="text-danger">Delay:</strong>
                                        <span class="text-danger">{{ $p->is_late['delay_days'] }} day(s)</span>
                                    </div>
                                    @endif
                                </div>
                            </td>

                            @php
                            $endTime = \Carbon\Carbon::parse($p->end_date)->timezone('Asia/Kolkata')->addDay()->startOfDay()->timestamp;
                            @endphp
                            <td>
                                @if($p->status === 'completed')
                                <span class="fw-bold text-success">
                                    00 : 00 : 00 : 00
                                </span>
                                @else
                                <div class="project-countdown"
                                    data-end="{{ $endTime }}">
                                </div>
                                @endif
                            </td>


                            <!-- <td>
                                <div class="text-muted small text-wrap" style="width: 250px; word-break: break-word;">
                                    {{ \Illuminate\Support\Str::limit($p->comment, 80) ?? '-' }}
                                </div>

                                @if(strlen($p->comment) > 80)
                                <button type="button"
                                    class="btn btn-link p-0 text-primary small read-comment-btn"
                                    data-comment="{{ htmlspecialchars($p->comment, ENT_QUOTES) }}"
                                    data-user="{{ $p->user->name ?? 'System' }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#commentModal">
                                    Read More
                                </button>
                                @endif
                            </td> -->

                            <td class="text-end">
                                <div class="hstack gap-2 justify-content-end">

                                    <!-- Edit -->
                                    @if(auth()->user()->role_id !== 6)
                                    <a href="{{ route('project.edit', $p->id) }}" class="avatar-text avatar-md">
                                        <i class="feather feather-edit-3"></i>
                                    </a>
                                    @endif

                                    <a href="{{ route('project.show', $p->id) }}" class="avatar-text avatar-md">
                                        <i class="feather feather-eye"></i>
                                    </a>

                                   

                                    @if(auth()->user()->role_id !== 6)
                                    <button class="btn btn-sm d-inline-flex align-items-center gap-1 custome"
                                        data-bs-toggle="offcanvas"
                                        data-bs-target="#deleteProject{{ $p->id }}">

                                        @if($p->is_deleted)
                                        <i class="feather feather-refresh-ccw"></i>
                                        Recover
                                        @else
                                        <i class="feather feather-trash-2"></i>
                                        Delete
                                        @endif

                                    </button>
                                    @endif

                                     <!-- Project Stage -->
                                    <a href="{{ route('project.stage', $p->id) }}"
                                        class="btn btn-sm d-inline-flex align-items-center gap-1 btn-primary">

                                        <i class="feather feather-layers"></i>
                                        Stages
                                    </a>


                                </div>
                            </td>
                        </tr>

                        {{-- View Project --}}
                        <div class="offcanvas offcanvas-end w-50" tabindex="-1" id="viewProject{{ $p->id }}">
                            <!-- Header -->
                            <div class="offcanvas-header ht-80 px-4 border-bottom border-gray-5">
                                <h2 class="fs-16 fw-bold mb-0">
                                    Project Details
                                </h2>
                                <!-- Right side -->
                                <div class="d-flex align-items-center gap-2">

                                    @php
                                    $statusClasses = [
                                    'new' => 'bg-soft-primary text-primary',
                                    'in_progress' => 'bg-soft-warning text-warning',
                                    'completed' => 'bg-soft-success text-success',
                                    'cancelled' => 'bg-soft-danger text-danger',
                                    ];
                                    @endphp

                                    <span class="badge text-uppercase {{ $statusClasses[$p->status] ?? 'bg-secondary' }}">
                                        {{ str_replace('_',' ', $p->status) }}
                                    </span>

                                    <button type="button" class="btn-close btn-close-gray ms-auto" data-bs-dismiss="offcanvas"></button>

                                </div>
                            </div>

                            <div class="offcanvas-body">
                                {{-- ================= PROJECT INFO ================= --}}
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h6 class="fw-bold mb-3 text-bold">Project Information</h6>

                                        <div class="row g-3">
                                            <div class="col-6">
                                                <small class="text-muted">Project Name</small>
                                                <div class="fw-semibold">{{ $p->name }}</div>
                                            </div>

                                            <div class="col-6">
                                                <small class="text-muted">Start Date</small>
                                                <div>{{ $p->start_date ?? '-' }}</div>
                                            </div>

                                            <div class="col-6">
                                                <small class="text-muted">End Date</small>
                                                <div>{{ $p->end_date ?? '-' }}</div>
                                            </div>
                                            @if(auth()->user()->role_id !== 6)
                                            <div class="col-6">
                                                <small class="text-muted">Budget</small>
                                                <div class="fw-bold text-success">₹ {{ number_format($p->budget) }}</div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                {{-- ================= MACHINES ================= --}}
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h6 class="fw-bold mb-3 text-bold">Machines & Inventory Items</h6>

                                        <div class="list-group list-group-flush">

                                            @forelse($p->projectProducts as $k => $pp)
                                            {{-- MACHINE --}}
                                            <div class="list-group-item rounded mb-2 shadow-sm"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#machine-{{ $pp->id }}"
                                                style="cursor:pointer;">

                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <div class="fw-semibold">
                                                            {{ $pp->product->code ?? '' }} - {{ $pp->product->name ?? '-' }}
                                                        </div>
                                                        <small class="text-muted">Quantity: {{ $pp->quantity }}</small><br>
                                                        <span class="badge bg-secondary mt-1">{{ $pp->status ?? 'N/A' }}</span>
                                                    </div>

                                                    <i class="fa fa-chevron-down toggle-icon"></i>
                                                </div>
                                            </div>

                                            {{-- INVENTORY ITEMS --}}
                                            <div class="collapse  mb-3" id="machine-{{ $pp->id }}">
                                                @php
                                                $inventoryList = $pp->product->productItems
                                                ->map(function($item){
                                                return [
                                                'name' => $item->inventory->name ?? null,
                                                'qty' => $item->quantity ?? null
                                                ];
                                                })
                                                ->filter(fn($i) => $i['name'])
                                                ->values();
                                                @endphp

                                                @if($inventoryList->count())
                                                <div class="card border-0 bg-light shadow-sm ms-3">
                                                    <div class="card-body py-2">
                                                        <h6 class="fw-semibold text-muted mb-2">
                                                            Inventory Items
                                                        </h6>

                                                        <ul class="list-group list-group-flush">
                                                            @foreach($inventoryList as $i => $inv)
                                                            <li class="list-group-item bg-transparent px-0 d-flex justify-content-between align-items-center">
                                                                <div class="d-flex align-items-center gap-2">
                                                                    <span class="badge bg-primary-subtle text-primary">
                                                                        {{ $i + 1 }}
                                                                    </span>
                                                                    <span class="fw-normal">
                                                                        {{ $inv['name'] }}
                                                                    </span>
                                                                </div>

                                                                @if($inv['qty'])
                                                                <span class="badge bg-secondary-subtle text-secondary">
                                                                    Qty: {{ $inv['qty'] }}
                                                                </span>
                                                                @endif
                                                            </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                                @else
                                                <div class="ms-4 p-3 rounded bg-light text-muted small">
                                                    No inventory items assigned to this machine
                                                </div>
                                                @endif
                                            </div>

                                            @empty
                                            <div class="text-center text-muted">No machines added</div>
                                            @endforelse

                                        </div>
                                    </div>
                                </div>
                                {{-- ================= INVENTORY ITEMS ================= --}}
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="fw-bold mb-3 text-bold">Project Inventory Items</h6>

                                        <div class="row g-2">
                                            @php $sr = 1; @endphp

                                            @forelse($p->projectItems as $pi)
                                            <div class="col-6">
                                                <div class="border rounded p-3 d-flex justify-content-between">
                                                    <div>
                                                        <div class="fw-semibold">
                                                            {{ $sr++ }}. {{ $pi->inventory->name ?? '-' }}
                                                        </div>
                                                        <small class="text-muted">
                                                            Quantity: {{ $pi->quantity }}
                                                        </small><br>
                                                        <small class="text-muted fw-semibold">
                                                            Item Length/Dimension: {{ $pi->length }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                            @empty
                                            <div class="col-12 text-center text-muted">
                                                No inventory items
                                            </div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>

                                {{-- ================= PROJECT COMMENT ================= --}}
                                @if($p->comment)
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h6 class="fw-bold mb-3">Project Comment</h6>

                                        <div class="bg-light rounded p-3">
                                            {{ $p->comment }}
                                        </div>

                                        <div class="text-muted small mt-2">
                                            Added by: {{ $p->user->name ?? 'System' }}
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="offcanvas offcanvas-end" tabindex="-1" id="deleteProject{{ $p->id }}">
                            <form method="post"
                                action="{{ $p->is_deleted
                                                    ? route('project.restore', $p->id)
                                                    : route('project.delete', $p->id) }}">
                                @csrf

                                <!-- Header -->
                                <div class="offcanvas-header ht-80 px-4 border-bottom border-gray-5">
                                    <h2 class="fs-16 fw-bold mb-0">
                                        {{ $p->is_deleted ? 'Restore Project' : 'Delete Project' }}
                                    </h2>
                                    <button type="button" class="btn-close btn-close-gray ms-auto" data-bs-dismiss="offcanvas"></button>
                                </div>

                                <!-- Body -->
                                <div class="offcanvas-body">
                                    <p class="fs-15">
                                        {{ $p->is_deleted ? 'Are you sure you want to restore' : 'Are you sure you want to delete' }}
                                        <strong>{{ $p->name }}</strong>?
                                    </p>
                                </div>

                                <!-- Footer -->
                                <div class="px-4 gap-2 d-flex align-items-center justify-content-between ht-80 border-top border-gray-2">
                                    <button type="submit" class="btn btn-primary w-50">
                                        {{ $p->is_deleted ? 'Restore' : 'Yes, Delete' }}
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
            {{-- Pagination --}}
            <div class="mt-3">
                {{ $projects->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <style>
        .nxl-container {
            filter: none !important;
        }

        .btn.custome {
            border: 1px solid #e9ecef;
        }

        .btn.custome:hover,
        .btn.custome:focus,
        .btn.custome:active,
        .btn.custome:focus:hover,
        .btn.custome:hover:focus {
            background-color: inherit !important;
            border-color: grey !important;
            color: inherit !important;
            box-shadow: none !important;
            outline: none !important;
            transition: none !important;
        }

        .deadline-indicator {
            position: relative;
            display: inline-flex;
            width: 12px;
            height: 12px;
            margin-left: 6px;
        }

        .stage-circle svg {
            width: 50px !important;
            height: 50px !important;
        }

        .stage-circle {
            width: 50px !important;
            height: 50px !important;
            min-width: 50px !important;
            min-height: 50px !important;

            display: flex;
            align-items: center;
            justify-content: center;

            overflow: hidden;
        }

        .circle-progress-text {
            font-size: 24px !important;
        }

        .deadline-indicator .dot {
            width: 10px;
            height: 10px;
            background: #ff3b3b;
            border-radius: 50%;
            position: relative;
            z-index: 2;
        }

        .deadline-indicator .pulse {
            position: absolute;
            width: 10px;
            height: 10px;
            background: rgba(255, 59, 59, 0.5);
            border-radius: 50%;
            animation: pulseRing 1.5s infinite;
        }

        @keyframes pulseRing {
            0% {
                transform: scale(1);
                opacity: 1;
            }

            70% {
                transform: scale(2.5);
                opacity: 0.4;
            }

            100% {
                transform: scale(1);
                opacity: 0;
            }
        }

        .toggle-icon {
            transition: transform 0.3s ease;
            font-size: 14px;
        }

        /* collapse open hone par */
        .list-group-item[aria-expanded="true"] .toggle-icon {
            transform: rotate(180deg);
        }
    </style>




    <!-- JS ADD/REMOVE PRODUCT ROW -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const container = document.getElementById('product-rows');
            const btnAdd = document.getElementById('btn-add-row');

            const modal = document.getElementById('commentModal');
            if (modal) {
                document.body.appendChild(modal);
            }

            // 🔥 Dynamic data fill
            document.querySelectorAll('.read-comment-btn').forEach(function(btn) {

                btn.addEventListener('click', function() {

                    let comment = this.getAttribute('data-comment');
                    let user = this.getAttribute('data-user');

                    document.getElementById('modalComment').innerHTML = comment;
                });

            });


            function initSelect2In(scope) {
                // product + status dono selects
                $(scope).find('select').select2({
                    theme: 'bootstrap-5',
                    width: '100%'
                });
            }

            // ✅ initial init (page load par)
            initSelect2In(container);

            btnAdd.addEventListener('click', function() {
                const firstRow = container.querySelector('.project-product-row');
                if (!firstRow) return;

                // ✅ destroy select2 on first row before cloning (VERY IMPORTANT)
                $(firstRow).find('select').select2('destroy');

                const clone = firstRow.cloneNode(true);

                // reset values
                clone.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
                clone.querySelectorAll('input').forEach(i => i.value = 1);

                container.appendChild(clone);

                // ✅ re-init select2 on both original row + new clone row
                initSelect2In(firstRow);
                initSelect2In(clone);
            });

            // remove row (same as your code)
            container.addEventListener('click', function(e) {
                if (e.target.classList.contains('btn-remove-row')) {
                    if (container.querySelectorAll('.project-product-row').length > 1) {
                        e.target.closest('.project-product-row').remove();
                    }
                }
            });

        });
    </script>



    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const container = document.getElementById('inventory-rows');
            const btnAdd = document.getElementById('add-inventory-row');

            // init select2 for existing rows
            $('#inventory-rows select').select2({
                theme: 'bootstrap-5'
            });

            function attachRemoveButtons() {
                container.querySelectorAll('.btn-remove-row').forEach(btn => {
                    btn.onclick = function() {
                        // ✅ inventory optional, last row remove nahi karenge (UI ke liye 1 empty row rahe)
                        if (container.querySelectorAll('.inventory-row').length > 1) {
                            btn.closest('.inventory-row').remove();
                        } else {
                            // last row ko empty kar do
                            const row = btn.closest('.inventory-row');
                            $(row).find('select').val('').trigger('change');
                            row.querySelectorAll('input').forEach(i => i.value = 1);
                        }
                    };
                });
            }

            btnAdd?.addEventListener('click', function() {
                const row = container.querySelector('.inventory-row');
                if (!row) return;

                // destroy select2 before cloning
                $(row).find('select').select2('destroy');

                const clone = row.cloneNode(true);

                // reset values
                clone.querySelectorAll('select').forEach(s => {
                    s.selectedIndex = 0;
                });

                clone.querySelectorAll('input').forEach(i => {
                    if (i.name.includes('inventory_qty')) {
                        i.value = 1;
                    } else {
                        i.value = '';
                    }
                });

                container.appendChild(clone);

                // re-init select2
                $('#inventory-rows select').select2({
                    theme: 'bootstrap-5'
                });

                attachRemoveButtons();
            });

            attachRemoveButtons();
        });

        function toggleComment(id) {
            let comment = document.getElementById('comment-' + id);
            let btn = document.getElementById('btn-' + id);

            comment.classList.toggle('expanded');

            btn.innerText = comment.classList.contains('expanded') ?
                'Read Less' :
                'Read More';
        }
    </script>

    @push('scripts')
    <script>
        $(document).ready(function() {

            $('.project-countdown').each(function() {

                var endTime = $(this).data('end'); // future timestamp
                var now = Math.floor(Date.now() / 1000);

                var seconds = endTime - now;

                if (seconds <= 0) {
                    $(this).html("00 : 00 : 00 : 00").addClass('text-success fw-bold');
                    return;
                }

                let days = Math.floor(seconds / (24 * 60 * 60));

                let hours = Math.floor(
                    (seconds % (24 * 60 * 60)) / (60 * 60)
                );

                $(this).html(`
    <span class="fw-bold text-dark">
        ${days} Days ${hours} Hours
    </span>
`);

            });

        });
    </script>
    @endpush

@endsection
<script src="{{ asset('/assets/vendors/js/circle-progress.min.js') }}"></script>

{{-- App Init JS --}}
<script src="{{ asset('/assets/js/dashboard-init.min.js') }}"></script>

<x-inventory::comment-modal />