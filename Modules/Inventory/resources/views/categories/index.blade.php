@extends('shared::layouts.app')
@section('content')

    <div class="nxl-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">Add Category</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                    <li class="breadcrumb-item active">Add Category</li>
                </ul>
            </div>
            <div class="page-header-right ms-auto d-flex align-items-center">
                <!-- Placeholder for additional actions -->
                <div class="d-md-none d-flex align-items-center">
                    <a href="javascript:void(0)" class="page-header-right-open-toggle">
                        <i class="feather-align-right fs-20"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="main-content">
            <div class="card card-primary card-outline">
                <div class="card-body">
                    <div class="row">
                        {{-- <div class="card-header">

                    @if($editCategory)
                        Edit Category
                    @else
                        Add Category
                    @endif

                </div> --}}

                        <div class="col-md-6">
                            @if($editCategory)
                            <form class="form-horizontal" method="POST" action="{{ route('categories.update', $editCategory->id) }}">
                                @csrf
                                @method('PUT')
                                @else
                                <form class="form-horizontal" method="POST" action="{{ route('categories.store') }}">
                                    @csrf
                                    @endif

                                    <div class="form-group">
                                        <label>Name</label>
                                        <input type="text" name="name" placeholder="Enter Category" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" value="{{ $editCategory->name ?? '' }}" required>
                                          @if ($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                                @endif
                                    </div>
                                    <div class="form-group mt-3">
                                        <button class="btn btn-primary btn-block">
                                            @if($editCategory) Update @else Save @endif
                                        </button>

                                        {{-- @if($editCategory)
                                <a href="{{ route('categories.index') }}" class="btn btn-secondary w-100 mt-2">
                                        Cancel Edit
                                        </a>
                                        @endif --}}
                                    </div>
                                </form>
                        </div>
                        <div class="col-md-6 bg-white">
                            <h5 style="background-color: white;">Category List</h5>
                            <div class="dataTables_wrapper dt-bootstrap5 no-footer shadow-sm p-3 mt-3 rounded">
                                {{-- <div class="card-header"></div> --}}
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered table-striped dataTable no-footer align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="background-color: white;width: 20%;">Sr No</th>
                                                <th style="background-color: white;">Name</th>
                                                <th style="background-color: white;width: 30%;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($categories as $index => $cat)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $cat->name }}</td>

                                                <td class="d-flex gap-2">
                                                    <a href="{{ route('categories.index', ['edit' => $cat->id]) }}"  class="btn btn-light btn-sm">
                                                         <i class="fa fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('categories.destroy', $cat->id) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button"
                                                            class="btn btn-light btn-sm"
                                                            title="Delete Request Slip"
                                                            data-bs-toggle="offcanvas"
                                                            data-bs-target="#deleteInventory{{ $cat->id }}">
                                                            <i class="fa fa-trash"></i>
                                                        </button>


                                                    </form>
                                                </td>

                                                <div class="offcanvas offcanvas-end" tabindex="-1" id="deleteInventory{{ $cat->id }}">
                                                    <form method="POST" action="{{ route('categories.destroy', $cat->id) }}">
                                                        @csrf
                                                        @method('DELETE')

                                                        <div class="offcanvas-header ht-80 px-4 border-bottom border-gray-5">
                                                            <h2 class="fs-16 fw-bold mb-0">Delete Request Slip</h2>
                                                            <button type="button" class="btn-close btn-close-gray ms-auto" data-bs-dismiss="offcanvas"></button>
                                                        </div>

                                                        <div class="offcanvas-body">
                                                            <p class="fs-15">
                                                                Are you sure you want to delete
                                                                <strong>#{{ $cat->name }}</strong>?

                                                            </p>
                                                        </div>

                                                        <div class="px-4 gap-2 d-flex align-items-center justify-content-between ht-80 border-top border-gray-2">
                                                            <button type="submit" class="btn btn-primary w-50">Yes, Delete</button>
                                                            <button type="button" class="btn btn-danger w-50" data-bs-dismiss="offcanvas">
                                                                Cancel
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>

                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection