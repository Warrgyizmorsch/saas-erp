@extends('shared::layouts.app')
@section('content')


    <div class="container py-4">

        <div class="card shadow border-0">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-bold">Product Details</h4>

                    <!-- PDF DOWNLOAD BUTTON -->
                    {{-- <a href="{{ route('product.pdf', $product->id) }}"
                    class="btn btn-primary"
                    target="_blank">
                    Download
                    </a> --}}
                </div>

                <hr>

                <!-- BASIC DETAILS -->
                {{-- <h5 class="fw-bold mb-3">Basic Information</h5> --}}

                <table class="table table-bordered">
                    <tr>
                        <th width="30%">Product Name</th>
                        <td>{{ $product->name }}</td>
                    </tr>
                    <tr>
                        <th>Estimation Duration</th>
                        <td>{{ $product->estimation_duration }} Days</td>
                    </tr>

                    {{-- <tr>
                    <th>Status</th>
                    <td>
                        @if($product->is_deleted)
                            <span class="badge bg-danger">Deleted</span>
                        @else
                            <span class="badge bg-success">Active</span>
                        @endif
                    </td>
                </tr> --}}
                </table>


                <!-- ITEMS DETAILS -->
                <h5 class="fw-bold mb-3 mt-4">Items Used in Product</h5>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th width="10%">Sr.</th>
                            <th>Item Name</th>
                            <th width="20%">Unit</th>
                            <th width="20%">Quantity</th>
                        </tr>
                    </thead>

                    <tbody>

                        @php
                        $productArr = $product->toArray();
                        @endphp

                        @foreach($productArr['product_items'] ?? [] as $key => $row)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $row['inventory']['name'] ?? '-' }}</td>
                            <td>{{ $row['inventory']['unit'] ?? '-' }}</td>
                            <td>{{ $row['quantity'] ?? '-' }}</td>
                        </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection