@extends('shared::layouts.app')
@section('content')
    <div class="main-content">
        <div id="issueSlipCollapse" class="accordion-collapse collapse show">
            <div class="card">
                <div class="card-body">

                    <h5 class="mb-3">
                        {{ $item ? 'Edit Supplier' : 'Add New Supplier' }}
                    </h5>

                    <form
                        action="{{ $item ? route('suppliers.update', $item->id) : route('suppliers.store') }}"
                        method="POST">
                        @csrf
                        @if($item)
                        @method('PUT')
                        @endif

                        <div class="row">

                            {{-- Category --}}
                            <div class="col-lg-4 mb-3">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <select data-select2-selector="status" name="category" class="form-control @error('category') is-invalid @enderror">
                                    <option value="">--Select Category--</option>
                                    @foreach($categories as $c)
                                    <option value="{{ $c->id }}"
                                        {{ old('category', $item->category ?? '') == $c->id ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Supplier Name --}}
                            <div class="col-lg-4 mb-3">
                                <label>Supplier Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-text"><i class="feather-user"></i></div>
                                    <input
                                        type="text"
                                        name="supplier_name"
                                        placeholder="Enter Supplier Name"
                                        value="{{ old('supplier_name', $item->supplier_name ?? '') }}"
                                        class="form-control @error('supplier_name') is-invalid @enderror">
                                    @error('supplier_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Supplier Code (READONLY) --}}
                            <div class="col-lg-4 mb-3">
                                <label>Supplier Code</label>
                                <div class="input-group">
                                    <div class="input-group-text"><i class="feather-code"></i></div>
                                    <input
                                        type="text"
                                        name="supplier_code"
                                        value="{{ old('supplier_code', $supplierCode ?? '') }}"
                                        class="form-control @error('supplier_code') is-invalid @enderror"
                                        readonly>
                                    @error('supplier_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Email --}}
                            <div class="col-lg-4 mb-3">
                                <label>Email </label>
                                <div class="input-group">
                                    <div class="input-group-text"><i class="feather-mail"></i></div>
                                    <input
                                        type="email"
                                        name="email"
                                        placeholder="Enter Email"
                                        value="{{ old('email', $item->email ?? '') }}"
                                        class="form-control">
                                    @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- State --}}
                            <div class="col-lg-4 mb-3">
                                <label>State </label>
                                <select data-select2-selector="status" name="state" class="form-control ">
                                    <option value=""> -- pls select state -- </option>

                                    {{-- States --}}
                                    <option value="Andhra Pradesh" {{ old('state', $item->state ?? '') == 'Andhra Pradesh' ? 'selected' : '' }}>Andhra Pradesh</option>
                                    <option value="Arunachal Pradesh" {{ old('state', $item->state ?? '') == 'Arunachal Pradesh' ? 'selected' : '' }}>Arunachal Pradesh</option>
                                    <option value="Assam" {{ old('state', $item->state ?? '') == 'Assam' ? 'selected' : '' }}>Assam</option>
                                    <option value="Bihar" {{ old('state', $item->state ?? '') == 'Bihar' ? 'selected' : '' }}>Bihar</option>
                                    <option value="Chhattisgarh" {{ old('state', $item->state ?? '') == 'Chhattisgarh' ? 'selected' : '' }}>Chhattisgarh</option>
                                    <option value="Goa" {{ old('state', $item->state ?? '') == 'Goa' ? 'selected' : '' }}>Goa</option>
                                    <option value="Gujarat" {{ old('state', $item->state ?? '') == 'Gujarat' ? 'selected' : '' }}>Gujarat</option>
                                    <option value="Haryana" {{ old('state', $item->state ?? '') == 'Haryana' ? 'selected' : '' }}>Haryana</option>
                                    <option value="Himachal Pradesh" {{ old('state', $item->state ?? '') == 'Himachal Pradesh' ? 'selected' : '' }}>Himachal Pradesh</option>
                                    <option value="Jharkhand" {{ old('state', $item->state ?? '') == 'Jharkhand' ? 'selected' : '' }}>Jharkhand</option>
                                    <option value="Karnataka" {{ old('state', $item->state ?? '') == 'Karnataka' ? 'selected' : '' }}>Karnataka</option>
                                    <option value="Kerala" {{ old('state', $item->state ?? '') == 'Kerala' ? 'selected' : '' }}>Kerala</option>
                                    <option value="Madhya Pradesh" {{ old('state', $item->state ?? '') == 'Madhya Pradesh' ? 'selected' : '' }}>Madhya Pradesh</option>
                                    <option value="Maharashtra" {{ old('state', $item->state ?? '') == 'Maharashtra' ? 'selected' : '' }}>Maharashtra</option>
                                    <option value="Manipur" {{ old('state', $item->state ?? '') == 'Manipur' ? 'selected' : '' }}>Manipur</option>
                                    <option value="Meghalaya" {{ old('state', $item->state ?? '') == 'Meghalaya' ? 'selected' : '' }}>Meghalaya</option>
                                    <option value="Mizoram" {{ old('state', $item->state ?? '') == 'Mizoram' ? 'selected' : '' }}>Mizoram</option>
                                    <option value="Nagaland" {{ old('state', $item->state ?? '') == 'Nagaland' ? 'selected' : '' }}>Nagaland</option>
                                    <option value="Odisha" {{ old('state', $item->state ?? '') == 'Odisha' ? 'selected' : '' }}>Odisha</option>
                                    <option value="Punjab" {{ old('state', $item->state ?? '') == 'Punjab' ? 'selected' : '' }}>Punjab</option>
                                    <option value="Rajasthan" {{ old('state', $item->state ?? '') == 'Rajasthan' ? 'selected' : '' }}>Rajasthan</option>
                                    <option value="Sikkim" {{ old('state', $item->state ?? '') == 'Sikkim' ? 'selected' : '' }}>Sikkim</option>
                                    <option value="Tamil Nadu" {{ old('state', $item->state ?? '') == 'Tamil Nadu' ? 'selected' : '' }}>Tamil Nadu</option>
                                    <option value="Telangana" {{ old('state', $item->state ?? '') == 'Telangana' ? 'selected' : '' }}>Telangana</option>
                                    <option value="Tripura" {{ old('state', $item->state ?? '') == 'Tripura' ? 'selected' : '' }}>Tripura</option>
                                    <option value="Uttar Pradesh" {{ old('state', $item->state ?? '') == 'Uttar Pradesh' ? 'selected' : '' }}>Uttar Pradesh</option>
                                    <option value="Uttarakhand" {{ old('state', $item->state ?? '') == 'Uttarakhand' ? 'selected' : '' }}>Uttarakhand</option>
                                    <option value="West Bengal" {{ old('state', $item->state ?? '') == 'West Bengal' ? 'selected' : '' }}>West Bengal</option>

                                    {{-- Union Territories --}}
                                    <option value="Andaman and Nicobar Islands" {{ old('state', $item->state ?? '') == 'Andaman and Nicobar Islands' ? 'selected' : '' }}>Andaman and Nicobar Islands</option>
                                    <option value="Chandigarh" {{ old('state', $item->state ?? '') == 'Chandigarh' ? 'selected' : '' }}>Chandigarh</option>
                                    <option value="Dadra and Nagar Haveli and Daman and Diu" {{ old('state', $item->state ?? '') == 'Dadra and Nagar Haveli and Daman and Diu' ? 'selected' : '' }}>Dadra and Nagar Haveli and Daman and Diu</option>
                                    <option value="Delhi" {{ old('state', $item->state ?? '') == 'Delhi' ? 'selected' : '' }}>Delhi</option>
                                    <option value="Jammu and Kashmir" {{ old('state', $item->state ?? '') == 'Jammu and Kashmir' ? 'selected' : '' }}>Jammu and Kashmir</option>
                                    <option value="Ladakh" {{ old('state', $item->state ?? '') == 'Ladakh' ? 'selected' : '' }}>Ladakh</option>
                                    <option value="Lakshadweep" {{ old('state', $item->state ?? '') == 'Lakshadweep' ? 'selected' : '' }}>Lakshadweep</option>
                                    <option value="Puducherry" {{ old('state', $item->state ?? '') == 'Puducherry' ? 'selected' : '' }}>Puducherry</option>

                                </select>

                                @error('state')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- City --}}
                            <div class="col-lg-4 mb-3">
                                <label>City </label>
                                <input
                                    type="text"
                                    name="city"
                                    placeholder="Enter city"
                                    value="{{ old('city', $item->city ?? '') }}"
                                    class="form-control ">
                                @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Mobile --}}
                            <div class="col-lg-4 mb-3">
                                <label>Mobile </label>
                                <div class="input-group">
                                    <div class="input-group-text"><i class="feather-phone"></i></div>
                                    <input
                                        type="tel"
                                        name="mobile"
                                        placeholder="Enter Mobile Number"
                                        value="{{ old('mobile', $item->mobile ?? '') }}"
                                        class="form-control @"
                                        minlength="10" maxlength="15"
                                        pattern="[0-9]+">
                                    @error('mobile')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- GSTIN --}}
                            <div class="col-lg-4 mb-3">
                                <label>GSTIN </label>
                                <div class="input-group">
                                    <div class="input-group-text"><i class="feather-file-text"></i></div>
                                    <input
                                        type="text"
                                        name="gstin"
                                        placeholder="Enter GSTIN Number"
                                        value="{{ old('gstin', $item->gstin ?? '') }}"
                                        class="form-control ">
                                    @error('gstin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- PAN --}}
                            <div class="col-lg-4 mb-3">
                                <label>PAN </label>
                                <div class="input-group">
                                    <div class="input-group-text"><i class="feather-hash"></i></div>
                                    <input
                                        type="text"
                                        name="pan"
                                        placeholder="Enter PAN Number"
                                        value="{{ old('pan', $item->pan ?? '') }}"
                                        class="form-control ">
                                    @error('pan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Address --}}
                            <div class="col-lg-4 mb-3">
                                <label>Supplier Address </label>
                                <div class="input-group">
                                    <div class="input-group-text"><i class="feather-map-pin"></i></div>
                                    <textarea
                                        name="supplier_address"
                                        class="form-control "
                                        placeholder="Enter Address">{{ old('supplier_address', $item->supplier_address ?? '') }}</textarea>
                                    @error('supplier_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Bank Name --}}
                            <div class="col-lg-4 mb-3">
                                <label>Bank Name </label>
                                <div class="input-group">
                                    <div class="input-group-text"><i class="feather-briefcase"></i></div>
                                    <input
                                        type="text"
                                        name="bank_name"
                                        placeholder="Enter Bank Name"
                                        value="{{ old('bank_name', $item->bank_name ?? '') }}"
                                        class="form-control ">
                                    @error('bank_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Account Number --}}
                            <div class="col-lg-4 mb-3">
                                <label>Account Number </label>
                                <div class="input-group">
                                    <div class="input-group-text"><i class="feather-key"></i></div>
                                    <input
                                        type="text"
                                        name="account_number"
                                        placeholder="Enter Account Number"
                                        value="{{ old('account_number', $item->account_number ?? '') }}"
                                        class="form-control ">
                                    @error('account_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- IFSC --}}
                            <div class="col-lg-4 mb-3">
                                <label>IFSC </label>
                                <div class="input-group">
                                    <div class="input-group-text"><i class="feather-lock"></i></div>
                                    <input
                                        type="text"
                                        name="ifsc"
                                        placeholder="Enter IFSC Code"
                                        value="{{ old('ifsc', $item->ifsc ?? '') }}"
                                        class="form-control ">
                                    @error('ifsc')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Submit --}}
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    {{ $item ? 'Update' : 'Save' }}
                                </button>
                            </div>

                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection