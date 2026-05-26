@extends('shared::layouts.app')
@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- PAGE HEADER -->
    <div class="page-header d-flex justify-content-between align-items-center ">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">
                    {{ isset($department) ? 'Edit Department' : 'Add Department' }}
                </h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                <li class="breadcrumb-item active">
                    {{ isset($department) ? 'Edit Department' : 'Add Department' }}
                </li>
            </ul>
        </div>

        <div class="d-flex gap-2">
            @if(!isset($department))
            <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                <i class="feather-plus me-2"></i> Add Department
            </button>
            @else
            <a href="{{ route('departments.index') }}" class="btn btn-primary btn-sm">
                Add New
            </a>
            @endif
        </div>
    </div>

    <!-- SLIDE FORM -->
    <div class="main-content">
        <div id="collapseOne" class="accordion-collapse collapse page-header-collapse {{ isset($department) ? 'show' : '' }}">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3">{{ isset($department) ? 'Edit Department' : 'Add Department' }}</h5>

                    <form action="{{ isset($department) ? route('departments.update', $department->id) : route('departments.store') }}" method="POST">
                        @csrf
                        @if(isset($department))
                        @method('PUT')
                        @endif

                        <div class="row g-4">

                            <div class="col-lg-6">
                                <label class="form-label">
                                    Department Name <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                    name="department_name"
                                    value="{{ old('department_name', $department->department_name ?? '') }}"
                                    class="form-control {{ $errors->has('department_name') ? 'is-invalid' : '' }}"
                                    placeholder="Enter department name">

                                @if ($errors->has('department_name'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('department_name') }}
                                    </div>
                                @endif
                            </div>


                            <div class="col-lg-6">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control">
                                    <option value="1" {{ isset($department) && $department->status == 1 ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ isset($department) && $department->status == 0 ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                        </div>

                        <div class="mt-3 d-flex justify-content-end">
                            <button class="btn btn-primary">{{ isset($department) ? 'Update Department' : 'Save Department' }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- DEPARTMENTS LIST TABLE -->
        <div class="card stretch stretch-full mt-4">
            <div class="card-body p-0">
                <div class="d-flex justify-content-between align-items-center px-4 py-3">
                    <h5 class="mb-0">Departments List</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped" id="departmentList">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Department Name</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($departments as $index => $d)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $d->department_name }}</td>
                                <td>
                                    <span class="badge {{ $d->status ? 'bg-success' : 'bg-danger' }}">
                                        {{ $d->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="hstack gap-2 justify-content-end">
                                        <a href="{{ route('departments.edit', $d->id) }}" class="btn btn-sm btn-light">
                                            <i class="feather-edit"></i>
                                        </a>

                                        <form action="{{ route('departments.destroy', $d->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light">
                                                <i class="feather-trash-2"></i>
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
