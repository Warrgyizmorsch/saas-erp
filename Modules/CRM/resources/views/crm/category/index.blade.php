
@extends('layouts.app')

@section('content')

<div class="nxl-content">

    <div class="page-header mb-4">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-5">Category Management</h5>
            </div>
        </div>

        <ul class="breadcrumb mb-0">
            <li class="breadcrumb-item">Home</li>
            <li class="breadcrumb-item active">Category</li>
        </ul>
    </div>

    <div class="main-content">
        <div class="row g-4">

            {{-- LEFT FORM --}}
            <div class="col-md-4">

                <div class="card shadow-sm">

                    <div class="card-header bg-white">
                        <h6 class="mb-0">
                            {{ isset($category) ? 'Edit Category' : 'Add Category' }}
                        </h6>
                    </div>

                    <div class="card-body">

                        <form method="POST"
                            action="{{ isset($category) ? route('category.update',$category->id) : route('category.store') }}">

                            @csrf

                            @if(isset($category))
                            @method('PUT')
                            @endif

                            <div class="mb-3">

                                <label class="form-label fw-semibold">
                                    Category Name
                                </label>

                                <input type="text"
                                    name="category_name"
                                    class="form-control"
                                    placeholder="Enter category name"
                                    value="{{ old('category_name', $category->category_name ?? '') }}">

                                @error('category_name')
                                <small class="text-danger">
                                    {{ $message }}
                                </small>
                                @enderror

                            </div>

                            <div class="d-grid">

                                <button class="btn btn-primary">

                                    <i class="fa fa-save me-1"></i>

                                    {{ isset($category) ? 'Update Category' : 'Save Category' }}

                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

            {{-- RIGHT TABLE --}}
            <div class="col-md-8">

                <div class="card shadow-sm">

                    <div class="card-header bg-white d-flex justify-content-between align-items-center">

                        <h6 class="mb-0">Category List</h6>

                    </div>

                    <div class="card-body p-3">

                        <table class="table table-bordered">

                            <thead class="table-light">

                                <tr>
                                    <th>ID</th>
                                    <th>Category Name</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>

                            </thead>

                            <tbody>

                                @forelse($categories as $index => $item)

                                <tr>

                                    <td>{{ $index+1 }}</td>

                                    <td>{{ $item->category_name }}</td>

                                    <td>

                                        @if($item->is_active)

                                        <span class="badge bg-soft-success text-success">
                                            Active
                                        </span>

                                        @else

                                        <span class="badge bg-soft-danger text-danger">
                                            Inactive
                                        </span>

                                        @endif

                                    </td>

                                    <td class="hstack gap-2 justify-content-center">

                                        <a href="{{ route('category.edit',$item->id) }}"
                                            class="avatar-text avatar-md">

                                            <i class="fa fa-edit"></i>

                                        </a>

                                        @if($item->is_active)

                                        <form action="{{ route('category.destroy',$item->id) }}"
                                            method="POST">

                                            @csrf
                                            @method('DELETE')

                                            <button class="avatar-text avatar-md border-0 bg-transparent">

                                                <i class="fa fa-trash"></i>

                                            </button>

                                        </form>

                                        @else

                                        <form action="{{ route('category.recover',$item->id) }}"
                                            method="POST">

                                            @csrf

                                            <button class="avatar-text avatar-md border-0 bg-transparent">

                                                <i class="fa fa-undo"></i>

                                            </button>

                                        </form>

                                        @endif

                                    </td>

                                </tr>

                                @empty

                                <tr>

                                    <td colspan="4" class="text-center">
                                        No Category Found
                                    </td>

                                </tr>

                                @endforelse

                            </tbody>

                        </table>

                        <div class="mt-3">
                            {{ $categories->links('pagination::bootstrap-5') }}
                        </div>

                    </div>

                </div>

            </div>

        </div>
    </div>

</div>

@endsection