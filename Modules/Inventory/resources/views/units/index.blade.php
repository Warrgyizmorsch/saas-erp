@extends('shared::layouts.app')
@section('content')

    <!-- PAGE HEADER -->
    <div class="page-header d-flex justify-content-between align-items-center ">

        <!-- LEFT SIDE (DYNAMIC) -->
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">
                    {{ isset($unit) ? 'Edit Unit' : 'Add Unit' }}
                </h5>
            </div>

            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                <li class="breadcrumb-item active">
                    {{ isset($unit) ? 'Edit Unit' : 'Add Unit' }}
                </li>
            </ul>
        </div>

        <!-- RIGHT SIDE BUTTONS -->
        <div class="d-flex gap-2">
            <button class="btn btn-icon btn-light-brand" data-bs-toggle="collapse" data-bs-target="#collapse">
                <i class="feather-filter"></i>
            </button>

            @if(!isset($unit))
            <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                <i class="feather-plus me-2"></i> Create Unit
            </button>
            @else
            <!-- When editing, show Add New -->
            <a href="{{ route('units.index') }}" class="btn btn-primary btn-sm">
                Add New
            </a>
            @endif
        </div>

    </div>

    <!-- SLIDE FORM -->
    <div class="main-content">
        <div id="collapseOne" class="accordion-collapse collapse page-header-collapse {{ isset($unit) ? 'show' : '' }}">
            <div class="card">
                <div class="card-body">

                    <h5 class="mb-3">Add / Edit Unit</h5>

                    <form action="{{ isset($unit) ? route('units.update', $unit->id) : route('units.store') }}" method="POST">
                        @csrf
                        @if(isset($unit)) @method('PUT') @endif

                        <div class="row g-4">

                            <!-- Unit Name -->
                            <div class="col-lg-6">
                                <label class="form-label">Unit Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-text"><i class="feather-flag"></i></div>
                                    <input type="text" name="name"
                                        value="{{ old('name', $unit->name ?? '') }}"
                                        class="form-control @error('name') is-invalid @enderror" placeholder="Enter unit name" required>
                                </div>
                                @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>

                        <div class="mt-3 d-flex justify-content-end">
                            <button class="btn btn-primary">
                                {{ isset($unit) ? 'Update Unit' : 'Save Unit' }}
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>

        <!-- FILTER PANEL -->
        <div id="collapse" class="accordion-collapse collapse mb-3">
            <div class="card">
                <div class="card-body">

                    <form method="GET" action="{{ route('units.index') }}">
                        <div class="row g-3 align-items-end">

                            <!-- Filter by Name -->
                            <div class="col-md-4">
                                <label class="form-label">Unit Name</label>
                                 <select name="name" class="form-select" data-select2-selector="status">
                                <option value="">-- Select Project -- </option>
                                @foreach ($units as $unit)
                                <option value="{{ $unit->name }}" {{ request('name')==$unit->name ? 'selected' : '' }}>{{ $unit->name }}</option>
                                @endforeach
                            </select>
                               
                            </div>

                            <!-- Filter by Status -->
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control" data-select2-selector="status">
                                    <option value="">-- All --</option>
                                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Active</option>
                                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Deleted</option>
                                </select>
                            </div>

                            <!-- Buttons -->
                            <div class="col-md-3 d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="feather-search me-1"></i> Filter
                                </button>

                                <a href="{{ route('units.index') }}" class="btn btn-light">
                                    Reset
                                </a>
                            </div>

                        </div>
                    </form>

                </div>
            </div>
        </div>


        <!-- UNITS TABLE -->
        <div class="card stretch stretch-full">
            <div class="card-body p-0">

                <div class="d-flex justify-content-between align-items-center px-4 py-3">
                    <h5 class="mb-0">Units List</h5>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped" id="leadList">
                        <thead>
                            <tr>
                                <th>Sr</th>
                                <th>Name</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($units as $index => $u)
                            <tr>

                                <!-- Sr -->
                                <td>{{ $index + 1 }}</td>

                                <!-- Name -->
                                <td>
                                    <div>
                                        <span class="text-truncate-1-line">{{ $u->name }}</span>
                                    </div>
                                </td>

                                <!-- Status -->
                                <td>
                                    <span class="badge {{ !$u->is_deleted ? 'badge badge bg-soft-success text-success' : 'badge badge bg-soft-danger text-danger' }}">
                                        {{ !$u->is_deleted ? 'Active' : 'Deleted' }}
                                    </span>
                                </td>

                                <!-- ACTIONS -->
                                <td class="text-end">
                                    <div class="hstack gap-2 justify-content-end">

                                        <!-- EDIT -->
                                        <a href="{{ route('units.edit', $u->id) }}" class="avatar-text avatar-md">
                                            <i class="feather-edit-3"></i>
                                        </a>

                                        <!-- DELETE / RECOVER BUTTON -->
                                        <form action="{{ route('units.toggle', $u->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="dropdown-item text-center p-2">
                                                @if($u->is_deleted)
                                                <i class="feather-refresh-ccw"></i> <!-- Recover -->
                                                @else
                                                <i class="feather-trash-2"></i> <!-- Delete -->
                                                @endif
                                            </button>
                                        </form>

                                    </div>
                                </td>

                            </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>

            </div>
        </div>
    </div>

@endsection