@extends('shared::layouts.app')
@section('content')

    {{-- PAGE HEADER --}}
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Supplier</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="/dashboard">Home</a>
                </li>
                <li class="breadcrumb-item active">Supplier List</li>
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
                    <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
                        <i class="feather-plus me-2"></i>
                        <span>Add Supplier</span>
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
                    <form method="GET" action="{{ route('suppliers.index') }}">
                        <div class="row g-3 align-items-end">

                            <div class="col-md-3">
                                <label class="form-label">Supplier Name</label>
                            <select name="supplier_id" id="supplier-filter" class="form-control"></select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control"
                                    value="{{ request('email') }}" placeholder="Search by email">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Mobile Number</label>
                                <input type="number" name="mobile" class="form-control"
                                    value="{{ request('mobile') }}" placeholder="Search by mobile">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Supplier Code</label>
                                <input type="text" name="supplier_code" class="form-control"
                                    value="{{ request('supplier_code') }}" placeholder="Search by supplier code ">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-control" data-select2-selector="status">
                                    <option value="">All Category</option>
                                    @foreach($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Buttons -->
                            <div class="col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-50">
                                    <i class="feather-search"></i> Search
                                </button>

                                <a href="{{ route('suppliers.index') }}" class="btn btn-light w-50">
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
                                <th>Category</th>
                                <th>Supplier Details</th>
                                <th>account Details</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($suppliers as $s)
                            @php

                            $supplier = $s->toArray();
                            @endphp
                            <tr>

                                <!-- SR -->
                                <td>{{ $loop->iteration }}</td>
                                <!-- CATEGORY NAME (FIXED) -->
                                <td>{{ $supplier['category']['name'] ?? '-' }}</td>

                                </td>

                                <!-- Supplier Name + Code -->
                                <!-- <td>
                                    <div>
                                        <strong>{{ $s->supplier_name ?? 'N/A' }}</strong><br>
                                        <small class="text-muted">{{ $s->supplier_code ?? '' }}</small>
                                    </div>
                                </td> -->

                                <td>
                                    <div class="small">
                                        <div>
                                            <strong>Name:</strong>
                                            {{$s->supplier_name ?? '-'}} </br>
                                            <small class="text-muted">{{ $s->supplier_code ?? '' }}</small>

                                        </div>
                                        
                                        <div>
                                            <strong>Email:</strong>
                                            {{ $s->email ?? 'N/A' }}
                                        </div>
                                        <div>
                                            <strong>State:</strong>
                                            {{ $s->state ?? 'N/A' }}
                                        </div>

                                        <div>
                                            <strong >City:</strong>
                                            <span >{{ $s->city ?? 'N/A'}}</span>
                                        </div>

                                        <div>
                                            <strong >Mobile:</strong>
                                            <span >{{ $s->mobile ?? 'N/A'}}</span>
                                        </div>
                                        <div>
                                            <strong >GST No:</strong>
                                            <span >{{ $s->gstin ?? 'N/A'}}</span>
                                        </div>
                                        <div>
                                            <strong >PAN No:</strong>
                                            <span >{{ $s->pan ?? 'N/A'}}</span>
                                        </div>
                                        <div>
                                            <strong >Address:</strong>
                                            <span >{{ $s->address ?? 'N/A'}}</span>
                                        </div>
                                        
                                    </div>
                                </td>

                                <!-- supplier bank details -->
                                 <td>
                                    <div class="small">
                                        <div>
                                            <strong>Bank:</strong>
                                            {{$s->bank_name ?? '-'}} </br>
                                        </div>
                                        
                                        <div>
                                            <strong>Branch:</strong>
                                            {{ $s->branch_address ?? 'N/A' }}
                                        </div>
                                        <div>
                                            <strong>IFSC:</strong>
                                            {{ $s->ifsc ?? 'N/A' }}
                                        </div>

                                        <div>
                                            <strong >Account No:</strong>
                                            <span >{{ $s->account_number ?? 'N/A'}}</span>
                                        </div>

                                    </div>
                                </td>
                                <!-- Actions -->
                                <td class="text-end">
                                    <div class="hstack gap-2 justify-content-end">

                                        <!-- Edit -->
                                        <a href="{{ route('suppliers.edit', $s->id) }}"
                                            class="avatar-text avatar-md">
                                            <i class="feather feather-edit-3"></i>
                                        </a>

                                        <!-- Delete -->
                                        <button class="avatar-text avatar-md"
                                            data-bs-toggle="offcanvas"
                                            data-bs-target="#deleteSupplier{{ $s->id }}">
                                            <i class="feather feather-trash-2"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- DELETE OFFCANVAS -->
                            <div class="offcanvas offcanvas-end"
                                tabindex="-1"
                                id="deleteSupplier{{ $s->id }}">
                                <form method="POST" action="{{ route('suppliers.destroy', $s->id) }}">
                                    @csrf
                                    @method('DELETE')

                                    <div class="offcanvas-header border-bottom">
                                        <h5 class="mb-0">Delete Supplier</h5>
                                        <button type="button"
                                            class="btn-close"
                                            data-bs-dismiss="offcanvas"></button>
                                    </div>

                                    <div class="offcanvas-body">
                                        <p>
                                            Are you sure you want to delete
                                            <strong>{{ $s->supplier_name }}</strong>?
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

                            @empty
                            <tr>
                                <td colspan="6"
                                    class="text-center text-muted py-4">
                                    No suppliers found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- Pagination --}}
                    <div class="mt-3">
                        {{ $suppliers->links('pagination::bootstrap-5') }}
                    </div>

                </div>

            </div>
        </div>

    </div>
    @push('scripts')
    <script>
         document.addEventListener("DOMContentLoaded", function() {
            $('#supplier-filter').select2({
                placeholder: 'All Suppliers',
                allowClear: false,
                width: '100%',
                ajax: {
                    url: "{{ route('suppliers.search') }}",
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
            let selectedSupplierId = "{{ request('supplier_id') }}";
            let selectedSupplierText = "{{ $selectedSupplierName ?? '' }}";

            if (selectedSupplierId && selectedSupplierText) {
                let option = new Option(selectedSupplierText, selectedSupplierId, true, true);
                $('#supplier-filter').append(option).trigger('change');
            }
        });
    </script>
    @endpush
@endsection