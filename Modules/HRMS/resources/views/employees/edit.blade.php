@extends('shared::layouts.app')

@section('content')
    <!-- [ page-header ] start -->
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Edit Employee</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Employees</a></li>
                <li class="breadcrumb-item">Edit</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <a href="{{ route('employees.index') }}" class="btn btn-md btn-light-brand">
                    <i class="feather-arrow-left me-2"></i> Back to List
                </a>
            </div>
        </div>
    </div>
    <!-- [ page-header ] end -->

    <style>
        @media (max-width: 576px) {
            .nav-tabs .nav-link {
                text-align: left;
                padding-left: 20px !important;
                border-radius: 0 !important;
                border-bottom: 1px solid #eee !important;
            }

            .nav-tabs .nav-link.active {
                background: #fff !important;
                border-left: 4px solid #6366f1 !important;
            }

            .card.stretch {
                border-radius: 0 !important;
                margin: -15px !important;
            }

            .tab-content {
                padding: 15px !important;
            }

            .page-header {
                padding: 15px !important;
                flex-direction: column !important;
                align-items: flex-start !important;
            }

            .page-header-right {
                margin-top: 10px !important;
                margin-left: 0 !important;
                width: 100% !important;
            }

            .btn-lg {
                width: 100% !important;
                margin-bottom: 10px !important;
                margin-left: 0 !important;
            }
        }
    </style>

    <!-- [ Main Content ] start -->
    <div class="main-content">

        <!-- ERROR MESSAGES -->
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <strong><i class="feather-alert-circle me-2"></i> Validation Errors:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- SUCCESS MESSAGE -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                <i class="feather-check-circle me-2"></i> <strong>{{ session('success') }}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card stretch stretch-full">
            <form method="POST" action="{{ route('employees.update', $employee->id) }}" enctype="multipart/form-data" autocomplete="off" class="employee-premium-form">
                @csrf
                @method('PUT')
                <!-- Tab Navigation -->
                <ul class="nav nav-tabs nav-justified mb-0 flex-column flex-sm-row" id="employeeTab" role="tablist"
                    style="background: #f1f5f9; border-radius: 12px 12px 0 0; padding: 8px 8px 0 8px; border: none;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active w-100" id="personal-tab" data-bs-toggle="tab"
                            data-bs-target="#personal" type="button" role="tab" aria-controls="personal" aria-selected="true"
                            style="font-size: 15px; padding: 12px 0; border: none; border-radius: 10px 10px 0 0; transition: all 0.3s ease;">
                            <i class="bi bi-person-circle me-2"></i>Personal Details</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link w-100" id="bank-tab" type="button" data-bs-toggle="tab"
                            data-bs-target="#bank" role="tab" aria-controls="bank" aria-selected="false"
                            style="font-size: 15px; padding: 12px 0; border: none; border-radius: 10px 10px 0 0; transition: all 0.3s ease;">
                            <i class="bi bi-bank me-2"></i>Bank Details</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link w-100" id="salary-tab" data-bs-toggle="tab" data-bs-target="#salary"
                            type="button" role="tab" aria-controls="salary" aria-selected="false"
                            style="font-size: 15px; padding: 12px 0; border: none; border-radius: 10px 10px 0 0; transition: all 0.3s ease;">
                            <i class="bi bi-cash-coin me-2"></i>Salary Details</button>
                    </li>
                </ul>

                <style>
                    .employee-premium-form .tab-content {
                        background: #fff;
                        border-radius: 0 0 10px 10px;
                        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
                    }
                    .employee-premium-form .form-group {
                        margin-bottom: 20px;
                    }
                    .employee-premium-form label {
                        display: block;
                        font-weight: 700;
                        color: #475569;
                        font-size: 13px;
                        margin-bottom: 8px;
                        text-transform: none;
                    }
                    .employee-premium-form .input-group {
                        border-radius: 10px;
                        overflow: hidden;
                        border: 1px solid #e2e8f0;
                        transition: all 0.3s ease;
                    }
                    .employee-premium-form .input-group:focus-within {
                        border-color: #3858f9;
                        box-shadow: 0 0 0 4px rgba(56, 88, 249, 0.1);
                    }
                    .employee-premium-form .input-group-text {
                        background: #f1f5f9 !important;
                        border: none !important;
                        color: #64748b;
                        width: 48px;
                        display: flex;
                        justify-content: center;
                        font-size: 16px;
                    }
                    .employee-premium-form .form-control, 
                    .employee-premium-form .form-select {
                        border: none !important;
                        padding: 12px 15px !important;
                        font-size: 14px !important;
                        color: #1e293b !important;
                        height: 48px !important;
                    }
                    .employee-premium-form .form-control:focus, 
                    .employee-premium-form .form-select:focus {
                        box-shadow: none !important;
                    }
                    .employee-premium-form .form-control::placeholder {
                        color: #94a3b8 !important;
                    }
                    .employee-premium-form .form-check-input:checked {
                        background-color: #3858f9;
                        border-color: #3858f9;
                    }
                    .employee-premium-form .btn-primary {
                        background: #3858f9;
                        border: none;
                        padding: 12px 30px;
                        border-radius: 10px;
                        font-weight: 600;
                        box-shadow: 0 4px 12px rgba(56, 88, 249, 0.2);
                    }
                    .employee-premium-form .btn-primary:hover {
                        background: #2b46d1;
                        transform: translateY(-1px);
                    }
                    .employee-premium-form .btn-light {
                        background: #f1f5f9;
                        border: none;
                        color: #64748b;
                        padding: 12px 30px;
                        border-radius: 10px;
                        font-weight: 600;
                    }
                </style>

                <div class="tab-content p-4" id="employeeTabContent" style="background: #fff; border-radius: 0 0 10px 10px;">

                    <div class="tab-pane fade show active" id="personal" role="tabpanel" aria-labelledby="personal-tab">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label>Employee Code</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="bi bi-phone"></i></span>
                                    <input type="text" name="employee_code" class="form-control"
                                        placeholder="Enter employee code"
                                        value="{{ old('employee_code', $employee->employee_code) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label>Name <span class="text-danger">*</span></label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" name="name" class="form-control" placeholder="Enter employee name"
                                        value="{{ old('name', $employee->name) }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label>Email</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" class="form-control" placeholder="Enter email"
                                        value="{{ old('email', $employee->email) }}">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label>Role</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <select name="role" class="form-control" required>
                                        <option value="">Select Role</option>
                                        @foreach($roles as $rl)
                                            <option value="{{ $rl->slug }}" {{ old('role', $employee->role) == $rl->slug ? 'selected' : '' }}>{{ $rl->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label>Mobile Number <span class="text-danger">*</span></label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                    <input type="text" name="mobile_number" class="form-control"
                                        placeholder="Enter mobile number"
                                        value="{{ old('mobile_number', $employee->mobile_number) }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label>Department</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="bi bi-people"></i></span>
                                    <select name="department" class="form-control" required>
                                        <option value="">Select department...</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->name }}" {{ (strtolower(str_replace(' ', '_', $dept->name)) == strtolower(old('department', $employee->department)) || $dept->name == old('department', $employee->department)) ? 'selected' : '' }}>{{ $dept->name }} {{ $dept->short_name ? '('.$dept->short_name.')' : '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label>Designation <span class="text-danger">*</span></label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <select name="designation" class="form-control" required>
                                        <option value="">Select designation...</option>
                                        @foreach($designations as $desg)
                                            <option value="{{ $desg->name }}" {{ old('designation', $employee->designation) == $desg->name ? 'selected' : '' }}>{{ $desg->name }} {{ $desg->short_name ? '('.$desg->short_name.')' : '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label>Date of Joining</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                                    <input type="date" name="date_of_joining" class="form-control"
                                        value="{{ old('date_of_joining', $employee->date_of_joining) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label>Date of Birth</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                                    <input type="date" name="date_of_birth" class="form-control"
                                        value="{{ old('date_of_birth', $employee->date_of_birth) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label>Gender</label><br>
                                <div class="d-flex align-items-center mb-3 mt-2">
                                    <div class="form-check me-4">
                                        <input class="form-check-input" type="radio" name="gender" id="genderMale" value="male" {{ old('gender', $employee->gender) == 'male' ? 'checked' : '' }}>
                                        <label class="form-check-label ms-1" for="genderMale">Male</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="gender" id="genderFemale" value="female" {{ old('gender', $employee->gender) == 'female' ? 'checked' : '' }}>
                                        <label class="form-check-label ms-1" for="genderFemale">Female</label>
                                    </div>
                                </div>
                            </div>


                            <div class="col-md-4">
                                <label>Aadhaar Number</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                    <input type="text" name="aadhaar_number" class="form-control"
                                        placeholder="Enter Aadhaar Number"
                                        value="{{ old('aadhaar_number', $employee->aadhaar_number) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label>PAN Number</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                    <input type="text" name="pan_number" class="form-control" placeholder="E.G. ABCDE2548K"
                                        value="{{ old('pan_number', $employee->pan_number) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label>Address</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                    <textarea name="address" class="form-control" placeholder="Enter Address" style="height: 45px;">{{ old('address', $employee->address) }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label>Time In</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="bi bi-clock"></i></span>
                                    <input type="time" name="time_in" class="form-control"
                                        value="{{ old('time_in', $employee->time_in) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label>Time Out</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="bi bi-clock"></i></span>
                                    <input type="time" name="time_out" class="form-control"
                                        value="{{ old('time_out', $employee->time_out) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label>Leave</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                                    <input type="text" name="leave" class="form-control"
                                        value="{{ old('leave', $employee->leave) }}" placeholder="Enter Leave Allotment">
                                </div>
                            </div>

                            <!-- PF, ESI, Insurance Toggles and Fields -->
                            <div class="col-md-12 mt-4">
                                <div class="row g-3">
                                    <!-- PF Section -->
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" name="pf" id="pfToggle" {{ old('pf', $employee->pf) ? 'checked' : '' }} style="width: 40px; height: 20px;">
                                            <label class="form-check-label ms-2" for="pfToggle" style="margin-top: 2px;">Eligible For PF</label>
                                        </div>
                                        <div id="pfField" style="display: {{ old('pf', $employee->pf) ? 'block' : 'none' }};">
                                            <label class="small">PF No.</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-building"></i></span>
                                                <input type="text" name="pf_number" class="form-control" placeholder="PF No." value="{{ old('pf_number', $employee->pf_number) }}">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- ESI Section -->
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" name="esi" id="esiToggle" {{ old('esi', $employee->esi) ? 'checked' : '' }} style="width: 40px; height: 20px;">
                                            <label class="form-check-label ms-2" for="esiToggle" style="margin-top: 2px;">Eligible For ESI</label>
                                        </div>
                                        <div id="esiField" style="display: {{ old('esi', $employee->esi) ? 'block' : 'none' }};">
                                            <label class="small">ESI No.</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-shield"></i></span>
                                                <input type="text" name="esi_number" class="form-control" placeholder="ESI No." value="{{ old('esi_number', $employee->esi_number) }}">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Insurance Section -->
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" name="insurance" id="insuranceToggle" {{ old('insurance', $employee->insurance) ? 'checked' : '' }} style="width: 40px; height: 20px;">
                                            <label class="form-check-label ms-2" for="insuranceToggle" style="margin-top: 2px;">Insurance</label>
                                        </div>
                                        <div id="insuranceFields" style="display: {{ old('insurance', $employee->insurance) ? 'block' : 'none' }};">
                                            <div class="mb-2">
                                                <label class="small">Provider</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                                                    <input type="text" name="insurance_provider" class="form-control"
                                                        placeholder="Insurance Company" value="{{ old('insurance_provider', $employee->insurance_provider) }}">
                                                </div>
                                            </div>
                                            <div>
                                                <label class="small">Policy Number</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-hash"></i></span>
                                                    <input type="text" name="insurance_policy_number" class="form-control"
                                                        placeholder="Policy Number" value="{{ old('insurance_policy_number', $employee->insurance_policy_number) }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Photo Upload Section -->
                                    <div class="col-md-12 mt-4">
                                        <div class="p-3 border rounded-3 bg-light d-inline-flex align-items-center" style="border-style: dashed !important; border-width: 2px !important;">
                                            <!-- Hidden Input -->
                                            <input type="file" id="photoInput" name="photo" accept="image/*" hidden>

                                            <!-- Image Box -->
                                            <div onclick="document.getElementById('photoInput').click()"
                                                class="bg-white border d-flex align-items-center justify-content-center"
                                                style="width:100px; height:100px; cursor:pointer; overflow:hidden; border-radius: 12px; border: 1px solid #e2e8f0 !important;">

                                                <img id="previewImg" src="{{ $employee->photo ? asset('storage/' . $employee->photo) : '' }}"
                                                    style="width:100%; height:100%; object-fit:cover; display: {{ $employee->photo ? 'block' : 'none' }};">

                                                <div id="placeholderText" class="text-center" style="display: {{ $employee->photo ? 'none' : 'block' }};">
                                                    <i class="bi bi-camera text-muted" style="font-size: 24px;"></i>
                                                    <div style="color:#64748b; font-weight: 600; font-size: 10px; text-transform: uppercase; margin-top: 4px;">SELECT PHOTO</div>
                                                </div>
                                            </div>

                                            <!-- TEXT SIDE -->
                                            <div class="ms-4">
                                                <label class="d-block mb-1">Update Employee Photo</label>
                                                <p class="text-muted small mb-0">Max size 2MB. Allowed: PNG, JPG, JPEG, WEBP</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- JS for Toggle Logic -->
                            <script>
                                document.getElementById('pfToggle').addEventListener('change', function () {
                                    document.getElementById('pfField').style.display = this.checked ? 'block' : 'none';
                                });
                                document.getElementById('esiToggle').addEventListener('change', function () {
                                    document.getElementById('esiField').style.display = this.checked ? 'block' : 'none';
                                });
                                document.getElementById('insuranceToggle').addEventListener('change', function () {
                                    document.getElementById('insuranceFields').style.display = this.checked ? 'block' : 'none';
                                });

                                document.getElementById('photoInput').addEventListener('change', function (event) {
                                    const file = event.target.files[0];
                                    if (file) {
                                        const reader = new FileReader();
                                        reader.onload = function (e) {
                                            document.getElementById('previewImg').src = e.target.result;
                                            document.getElementById('previewImg').style.display = 'block';
                                            document.getElementById('placeholderText').style.display = 'none';
                                        }
                                        reader.readAsDataURL(file);
                                    }
                                });
                            </script>
                        </div>
                        <div class="text-end mt-4">
                            <button type="button" class="btn btn-primary" onclick="nextTab('bank')">
                                Next <i class="feather-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="bank" role="tabpanel" aria-labelledby="bank-tab">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label>Bank Name <span class="text-danger">*</span></label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="bi bi-bank"></i></span>
                                    <input type="text" name="bank_name" class="form-control" placeholder="Bank Name"
                                        value="{{ old('bank_name', $employee->bank_name) }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label>Account Number <span class="text-danger">*</span></label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="bi bi-hash"></i></span>
                                    <input type="text" name="account_number" class="form-control"
                                        placeholder="Account Number"
                                        value="{{ old('account_number', $employee->account_number) }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label>IFSC Code <span class="text-danger">*</span></label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="bi bi-key"></i></span>
                                    <input type="text" name="ifsc_code" class="form-control" placeholder="IFSC Code"
                                        value="{{ old('ifsc_code', $employee->ifsc_code) }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-light" onclick="prevTab('personal')">
                                <i class="feather-arrow-left"></i> Previous
                            </button>
                            <button type="button" class="btn btn-primary" onclick="nextTab('salary')">
                                Next <i class="feather-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="salary" role="tabpanel" aria-labelledby="salary-tab">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label>Basic Salary <span class="text-danger">*</span></label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                    <input type="number" name="basic_salary" id="basic_salary"
                                        class="form-control salary-input" placeholder="Basic Salary"
                                        value="{{ old('basic_salary', $employee->basic_salary) }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label>HRA</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="bi bi-house"></i></span>
                                    <input type="number" name="hra" id="hra" class="form-control salary-input"
                                        value="{{ old('hra', $employee->hra) }}" placeholder="HRA">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label>Conveyance Allowance</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="bi bi-truck"></i></span>
                                    <input type="number" name="conveyance_allowance" id="conveyance_allowance"
                                        class="form-control salary-input"
                                        value="{{ old('conveyance_allowance', $employee->conveyance_allowance) }}"
                                        placeholder="Conveyance Allowance">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label>Medical Allowance</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="bi bi-activity"></i></span>
                                    <input type="number" name="medical_allowance" id="medical_allowance"
                                        class="form-control salary-input"
                                        value="{{ old('medical_allowance', $employee->medical_allowance) }}"
                                        placeholder="Medical Allowance">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label>Other Allowance</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="bi bi-gift"></i></span>
                                    <input type="number" name="other_allowance" id="other_allowance"
                                        class="form-control salary-input"
                                        value="{{ old('other_allowance', $employee->other_allowance) }}"
                                        placeholder="Other Allowance">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label>Total Salary</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="bi bi-credit-card"></i></span>
                                    <input type="number" id="total_salary" class="form-control bg-light" value="0.00"
                                        readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="feather-check me-2"></i> UPDATE EMPLOYEE
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- [ Main Content ] end -->

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <script>
        function togglePassword(inputId, iconElement) {
            const passwordInput = document.getElementById(inputId);
            const icon = iconElement.querySelector('i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            }
        }

        // Tab Navigation with Validation
        function nextTab(targetTabId) {
            const currentTab = document.querySelector('.tab-pane.show.active');
            const requiredFields = currentTab.querySelectorAll('[required]');
            let missingFields = [];

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    let labelText = "a required field";
                    const container = field.closest('.col-md-4, .col-md-6, .col-md-12');
                    if (container) {
                        const label = container.querySelector('label');
                        if (label) labelText = label.innerText.replace('*', '').trim();
                    }
                    missingFields.push(labelText);
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            if (missingFields.length > 0) {
                alert("Please fill these required fields:\n- " + missingFields.join("\n- "));
                return;
            }

            // Standard Way to switch tabs
            const triggerEl = document.querySelector('#' + targetTabId + '-tab');
            if (triggerEl) {
                const tabInstance = bootstrap.Tab.getOrCreateInstance(triggerEl);
                tabInstance.show();
                window.scrollTo(0, 0); // Scroll to top of tab
            }
        }

        function prevTab(tabId) {
            const triggerEl = document.querySelector('#' + tabId + '-tab');
            if (triggerEl) {
                const tabInstance = bootstrap.Tab.getOrCreateInstance(triggerEl);
                tabInstance.show();
                window.scrollTo(0, 0);
            }
        }

        // Form Submit Validation
        document.querySelector('form').addEventListener('submit', function (e) {
            const requiredFields = this.querySelectorAll('[required]');
            let missingFields = [];

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    let labelText = "a required field";
                    const container = field.closest('.col-md-4, .col-md-6, .col-md-12');
                    if (container) {
                        const label = container.querySelector('label');
                        if (label) labelText = label.innerText.replace('*', '').trim();
                    }
                    missingFields.push(labelText);
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            if (missingFields.length > 0) {
                e.preventDefault();
                alert("Cannot submit. Please fill these required fields:\n- " + missingFields.join("\n- "));

                const firstInvalid = this.querySelector('.is-invalid');
                if (firstInvalid) {
                    const tabPane = firstInvalid.closest('.tab-pane');
                    const tabId = tabPane.id;
                    const tabLink = new bootstrap.Tab(document.querySelector('#' + tabId + '-tab'));
                    tabLink.show();
                }
            }
        });

        // Salary Calculation
        document.querySelectorAll('.salary-input').forEach(input => {
            input.addEventListener('input', calculateTotalSalary);
        });

        function calculateTotalSalary() {
            const basic = parseFloat(document.getElementById('basic_salary').value) || 0;
            const hra = parseFloat(document.getElementById('hra').value) || 0;
            const conveyance = parseFloat(document.getElementById('conveyance_allowance').value) || 0;
            const medical = parseFloat(document.getElementById('medical_allowance').value) || 0;
            const other = parseFloat(document.getElementById('other_allowance').value) || 0;

            const total = basic + hra + conveyance + medical + other;
            document.getElementById('total_salary').value = total.toFixed(2);
        }

        // Initialize total on load
        window.addEventListener('load', calculateTotalSalary);
    </script>
@endsection