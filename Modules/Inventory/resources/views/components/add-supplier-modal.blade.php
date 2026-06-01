@php
    $categories = $categories ?? \Modules\Inventory\App\Models\Category::orderBy('name')->get();
@endphp
<div class="modal fade-scale" id="addSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h2 class="d-flex flex-column mb-0">
                    <span class="fs-18 fw-bold mb-1">Add Supplier</span>
                    <small class="d-block fs-11 fw-normal text-muted">Create New Supplier</small>
                </h2>

                <a href="javascript:void(0)" class="avatar-text avatar-md bg-soft-danger close-icon" data-bs-dismiss="modal">
                    <i class="feather-x text-danger"></i>
                </a>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">

                <form action="{{ route('suppliers.store') }}" method="POST">
                    @csrf

                    <div class="row">

                        <!-- Category -->
                        <div class="col-lg-4 mb-3">
                            <label class="form-label">Category *</label>
                            <select name="category" class="form-control" data-select2-selector="status">
                                <option value="">Select Category</option>

                                @foreach($categories as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach

                            </select>
                        </div>


                        <!-- Supplier Name -->
                        <div class="col-lg-4 mb-3">
                            <label>Supplier Name *</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="feather-user"></i>
                                </span>

                                <input type="text"
                                    name="supplier_name"
                                    class="form-control"
                                    placeholder="Enter Supplier Name">
                            </div>
                        </div>


                        <!-- Supplier Code -->
                        <div class="col-lg-4 mb-3">
                            <label>Supplier Code</label>
                            <div class="input-group">

                                <span class="input-group-text">
                                    <i class="feather-code"></i>
                                </span>

                                <input type="text"
                                    name="supplier_code"
                                    value="{{ $supplierCode ?? '' }}"
                                    class="form-control"
                                    readonly>
                            </div>
                        </div>


                        <!-- Email -->
                        <div class="col-lg-4 mb-3">
                            <label>Email </label>

                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="feather-mail"></i>
                                </span>

                                <input type="email"
                                    name="email"
                                    class="form-control"
                                    placeholder="Enter Email">
                            </div>
                        </div>


                        <!-- State -->
                        <div class="col-lg-4 mb-3">
                            <label>State </label>

                            <select name="state" class="form-control select2" data-select2-selector="status">
                                <option value="">--Select State--</option>

                                <option value="Andhra Pradesh">Andhra Pradesh</option>
                                <option value="Arunachal Pradesh">Arunachal Pradesh</option>
                                <option value="Assam">Assam</option>
                                <option value="Bihar">Bihar</option>
                                <option value="Chhattisgarh">Chhattisgarh</option>
                                <option value="Goa">Goa</option>
                                <option value="Gujarat">Gujarat</option>
                                <option value="Haryana">Haryana</option>
                                <option value="Himachal Pradesh">Himachal Pradesh</option>
                                <option value="Jharkhand">Jharkhand</option>
                                <option value="Karnataka">Karnataka</option>
                                <option value="Kerala">Kerala</option>
                                <option value="Madhya Pradesh">Madhya Pradesh</option>
                                <option value="Maharashtra">Maharashtra</option>
                                <option value="Manipur">Manipur</option>
                                <option value="Meghalaya">Meghalaya</option>
                                <option value="Mizoram">Mizoram</option>
                                <option value="Nagaland">Nagaland</option>
                                <option value="Odisha">Odisha</option>
                                <option value="Punjab">Punjab</option>
                                <option value="Rajasthan">Rajasthan</option>
                                <option value="Sikkim">Sikkim</option>
                                <option value="Tamil Nadu">Tamil Nadu</option>
                                <option value="Telangana">Telangana</option>
                                <option value="Tripura">Tripura</option>
                                <option value="Uttar Pradesh">Uttar Pradesh</option>
                                <option value="Uttarakhand">Uttarakhand</option>
                                <option value="West Bengal">West Bengal</option>

                                <!-- Union Territories -->
                                <option value="Andaman and Nicobar Islands">Andaman and Nicobar Islands</option>
                                <option value="Chandigarh">Chandigarh</option>
                                <option value="Dadra and Nagar Haveli and Daman and Diu">Dadra and Nagar Haveli and Daman and Daman and Diu</option>
                                <option value="Delhi">Delhi</option>
                                <option value="Jammu and Kashmir">Jammu and Kashmir</option>
                                <option value="Ladakh">Ladakh</option>
                                <option value="Lakshadweep">Lakshadweep</option>
                                <option value="Puducherry">Puducherry</option>

                            </select>
                        </div>


                        <!-- City -->
                        <div class="col-lg-4 mb-3">
                            <label>City </label>

                            <input type="text"
                                name="city"
                                class="form-control"
                                placeholder="Enter City">
                        </div>


                        <!-- Mobile -->
                        <div class="col-lg-4 mb-3">
                            <label>Mobile </label>

                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="feather-phone"></i>
                                </span>

                                <input type="text"
                                    name="mobile"
                                    class="form-control"
                                    placeholder="Enter Mobile">
                            </div>
                        </div>


                        <!-- GST -->
                        <div class="col-lg-4 mb-3">
                            <label>GSTIN </label>

                            <input type="text"
                                name="gstin"
                                class="form-control"
                                placeholder="Enter GSTIN">
                        </div>


                        <!-- PAN -->
                        <div class="col-lg-4 mb-3">
                            <label>PAN </label>

                            <input type="text"
                                name="pan"
                                class="form-control"
                                placeholder="Enter PAN">
                        </div>


                        <!-- Address -->
                        <div class="col-lg-6 mb-3">
                            <label>Supplier Address </label>

                            <textarea
                                name="supplier_address"
                                class="form-control"
                                placeholder="Enter Address"></textarea>
                        </div>


                        <!-- Bank -->
                        <div class="col-lg-4 mb-3">
                            <label>Bank Name </label>

                            <input type="text"
                                name="bank_name"
                                class="form-control"
                                placeholder="Enter Bank Name">
                        </div>


                        <!-- Account -->
                        <div class="col-lg-4 mb-3">
                            <label>Account Number </label>

                            <input type="text"
                                name="account_number"
                                class="form-control"
                                placeholder="Enter Account Number">
                        </div>


                        <!-- IFSC -->
                        <div class="col-lg-4 mb-3">
                            <label>IFSC </label>

                            <input type="text"
                                name="ifsc"
                                class="form-control"
                                placeholder="Enter IFSC">
                        </div>

                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer mt-3">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            Cancel
                        </button>

                        <button type="submit" class="btn btn-primary">
                            Save Supplier
                        </button>
                    </div>

                </form>

            </div>

    </div>
</div>

<style>
    .select2-container--open {
        z-index: 99999 !important;
    }
</style>