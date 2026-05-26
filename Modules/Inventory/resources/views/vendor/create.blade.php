@extends('shared::layouts.app')
@section('content')

    <div class="main-content">

        <div>
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3"> {{ isset($vendor) ? 'Edit Vendor' : 'Add New Vendor' }}</h5>
                    @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form
                        action="{{ isset($vendor) ? route('vendor.update',$vendor->id) : route('vendor.store') }}"
                        method="POST">
                        @csrf
                        @if(isset($vendor))
                        @method('PUT')
                        @endif

                        <div class="row">

                            <div class="col-lg-4 mb-3">
                                <label class="form-label">Vendor Name</label>
                                <div class="input-group">
                                    <div class="input-group-text"><i class="feather-user"></i></div>
                                    <input type="text" placeholder="Enter Supplier Name" class="form-control" name="name" value="{{ old('name',$vendor->name ?? '' )}}" >
                                </div>

                                  @if ($errors->has('name'))
                            <div class="text-danger mt-1 small">
                                {{ $errors->first('name') }}
                            </div>
                            @endif
                            </div>

                            <div class="col-lg-4 mb-3">
                                <label class="form-label">Email</label>
                                <div class="input-group">
                                    <div class="input-group-text"><i class="feather-mail"></i></div>
                                    <input type="email" placeholder="Enter Email" name="email" class="form-control" value="{{  old('email',$vendor->email ?? '' )}}" >
                                </div>

                                  @if ($errors->has('email'))
                            <div class="text-danger mt-1 small">
                                {{ $errors->first('email') }}
                            </div>
                            @endif
                            </div>

                            <div class="col-lg-4 mb-3">
                                <label class="form-label">Mobile No</label>
                                <div class="input-group">
                                    <div class="input-group-text"><i class="feather-phone"></i></div>
                                    <input type="text" placeholder="Enter Mobile Numver" class="form-control"
                                        name="mobile_no"
                                        value="{{ old('mobile_no',$vendor->mobile_no ?? '') }}"
                                        minlength="10" maxlength="15"
                                        pattern="[0-9]+" >
                                </div>

                                  @if ($errors->has('mobile_no'))
                            <div class="text-danger mt-1 small">
                                {{ $errors->first('mobile_no') }}
                            </div>
                            @endif
                            </div>


                            <div class="col-lg-4 mb-3">
                                <label class="form-label">City</label>
                                <input type="text" placeholder="Enter City name" name="city" class="form-control"   value="{{ old('city',$vendor->city ?? '') }}" >

                                  @if ($errors->has('city'))
                            <div class="text-danger mt-1 small">
                                {{ $errors->first('city') }}
                            </div>
                            @endif
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" name="address" rows="2">{{ $vendor->address ?? '' }}</textarea>
                            </div>

                        </div>

                        <button class="btn btn-primary">
                            {{ isset($vendor) ? 'Update Vendor' : 'Save Vendor' }}
                        </button>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection