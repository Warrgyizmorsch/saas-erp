@extends('shared::layouts.app')
@section('content')

 <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Vendor</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="/dashboard">Home</a>
                </li>
                <li class="breadcrumb-item active">Vendor List</li>
            </ul>
        </div>

        <div class="page-header-right ms-auto">
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

                    {{-- Create Request Slip --}}
                    <a href="{{ route('vendor.create')  }}" class="btn btn-primary">
                        <i class="feather-plus me-2"></i>
                        <span> Add Vendor</span>
                    </a>

                </div>

            </div>

            {{-- Mobile open toggle --}}
            <div class="d-md-none d-flex align-items-center">
                <a href="javascript:void(0)" class="page-header-right-open-toggle">
                    <i class="feather-align-right fs-20"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="main-content">

      {{-- filterCollapse --}}
        <div class="collapse mb-3" id="filterCollapse">
            <div class="card border shadow-sm">
                <div class="card-body">
                    <form method="GET" action="{{ route('vendor.index') }}">
                        <div class="row g-3 align-items-end">

                              <div class="col-md-3">
                                <label class="form-label">Name</label>
                                <select name="name" class="form-control" data-select2-selector="status">
                                    <option value="">-- Select Supplier --</option>

                                    @foreach($vendors as $vendor)
                                    <option value="{{$vendor->name }}"
                                        {{ request('name') == $vendor->name ? 'selected' : '' }}>
                                        {{ $vendor->name}}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control"
                                    value="{{ request('email') }}" placeholder="Search by email">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Mobile Number</label>
                                <input type="number" name="mobile" class="form-control"
                                    value="{{ request('mobile') }}" placeholder="Search by mobile">
                            </div>
                           
                            <!-- Buttons -->
                            <div class="col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-50">
                                    <i class="feather-search"></i> Search
                                </button>

                                <a href="{{ route('vendor.index') }}" class="btn btn-light w-50">
                                    Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card stretch stretch-full">
            <div class="card-body ">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Sr No</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Mobile</th>
                                <th>City</th>
                                <th>Address</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($vendors as $index=> $vendor)
                            <tr>
                                <td>{{ $index + 1 }}</td>

                                <td>{{$vendor->name}}</td>

                                <td>{{$vendor->email ?? '-'}} </td>

                                <td>{{$vendor->mobile_no ?? '-'  }}</td>

                                <td>{{ $vendor->city ?? '-' }}</td>

                                <td>{{$vendor->address ?? '-'}}</td>

                                <!-- Actions -->
                                <td class="text-end">
                                    <div class="hstack gap-2 justify-content-end">

                                        <!-- Edit -->
                                        <a href="{{ route('vendor.edit', $vendor->id) }}"
                                            class="avatar-text avatar-md">
                                            <i class="feather feather-edit-3"></i>
                                        </a>

                                        <!-- Delete -->
                                        <button class="avatar-text avatar-md"
                                            data-bs-toggle="offcanvas"
                                            data-bs-target="#deleteSupplier{{ $vendor->id }}">
                                            <i class="feather feather-trash-2"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- DELETE OFFCANVAS -->
                            <div class="offcanvas offcanvas-end"
                                tabindex="-1"
                                id="deleteSupplier{{ $vendor->id }}">
                                <form method="POST" action="{{ route('vendor.destroy', $vendor->id) }}">
                                    @csrf
                                    @method('DELETE')

                                    <div class="offcanvas-header border-bottom">
                                        <h5 class="mb-0">Delete Vendor</h5>
                                        <button type="button"
                                            class="btn-close"
                                            data-bs-dismiss="offcanvas"></button>
                                    </div>

                                    <div class="offcanvas-body">
                                        <p>
                                            Are you sure you want to delete
                                            <strong>{{ $vendor->name }}</strong>?
                                        </p>
                                    </div>

                                    <div class="border-top p-3 d-flex gap-2">
                                        <button type="submit" class="btn btn-primary w-50">
                                            Yes, Delete
                                        </button>
                                        <button type="button"
                                            class="btn btn-danger w-50"
                                            data-bs-dismiss="offcanvas">
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                  <div class="mt-3"> {{ $vendors->links('pagination::bootstrap-5') }} </div>
            </div>
        </div>

    </div>
@endsection