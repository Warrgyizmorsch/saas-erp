@extends('shared::layouts.app')

@section('content')
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">{{ isset($user) ? 'Edit User' : 'Add User' }}</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                <li class="breadcrumb-item active">{{ isset($user) ? 'Edit User' : 'Add User' }}</li>
            </ul>
        </div>
    </div>

    <!-- [ Main Content ] start -->
    <div class="main-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full">
                    <form id="userForm"
                        action="{{ isset($user) ? route('users.update', $user->id) : route('users.store') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @if(isset($user)) @method('PUT') @endif

                        <div class="card-body">
                            <div class="row g-4">
                                <!-- LEFT SIDE (Profile Image) -->
                                <div class="col-lg-4 text-center border-end">
                                    <div class="image-preview mb-3 mx-auto">
                                        @if(isset($user) && $user->image)
                                            <img id="preview" src="{{ asset('storage/' . $user->image) }}" alt="Profile Image">
                                        @else
                                            <img id="preview" src="/images/blank.jpeg" alt="Profile Image">
                                        @endif
                                    </div>
                                    <label class="btn btn-outline-primary">
                                        <i class="feather-upload me-2"></i> Upload Picture
                                        <input type="file" name="image" id="imageInput" class="d-none" accept="image/*"
                                            onchange="previewImage(event)">
                                    </label>
                                    @error('image') <small class="text-danger d-block">{{ $message }}</small> @enderror
                                </div>

                                <!-- RIGHT SIDE (Form Fields) -->
                                <div class="col-lg-8">
                                    <div class="row g-3">
                                        <!-- Name -->
                                        <div class="col-lg-6">
                                            <label class="form-label">Name <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-user"></i></div>
                                                <input type="text" name="name" class="form-control"
                                                    value="{{ old('name', $user->name ?? '') }}" placeholder="Enter name">
                                            </div>
                                            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                                        </div>

                                        <!-- Email -->
                                        <div class="col-lg-6">
                                            <label class="form-label">Email <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-mail"></i></div>
                                                <input type="email" name="email" class="form-control"
                                                    value="{{ old('email', $user->email ?? '') }}"
                                                    placeholder="Enter email">
                                            </div>
                                            @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                                        </div>

                                        <!-- Phone (Country Code + Number in one field) -->
                                        <div class="col-lg-6">
                                            <label class="form-label">Contact No <span class="text-danger">*</span></label>
                                            <div>
                                                <input id="phone" type="tel" name="contact_no" class="form-control"
                                                    value="{{ old('contact_no', $user->contact_no ?? '') }}"
                                                    placeholder="Contact Number">
                                                <input type="hidden" name="country_code" id="country_code"
                                                    value="{{ old('country_code', $user->country_code ?? '') }}">
                                            </div>
                                            @error('contact_no') <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                            @error('country_code') <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        <!-- Role -->
                                        <div class="col-lg-6">
                                            <label class="form-label">Role <span class="text-danger">*</span></label>
                                            <select name="role_id" class="form-control" data-select2-selector="tag">
                                                <option value="">Select Role</option>
                                                @foreach($roles as $role)
                                                    <option value="{{ $role->id }}" {{ old('role_id', $user->role_id ?? '') == $role->id ? 'selected' : '' }}>
                                                        {{ $role->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('role_id') <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        <!-- City -->
                                        <div class="col-lg-6">
                                            <label class="form-label">City <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-text"><i class="feather-map-pin"></i></div>
                                                <input type="text" name="city" class="form-control"
                                                    value="{{ old('city', $user->city ?? '') }}" placeholder="Enter City">
                                            </div>
                                            @error('city') <small class="text-danger">{{ $message }}</small> @enderror
                                        </div>
                                    </div>

                                    @auth
                                        @if(isset($user) && auth()->user()->role_id === 1)
                                            <div class="row g-4 mt-1">
                                                <!-- Password -->
                                                <div class="col-lg-6">
                                                    <label class="form-label">New Password</label>
                                                    <div class="input-group">
                                                        <div class="input-group-text">
                                                            <i class="feather-lock"></i>
                                                        </div>
                                                        <input type="password" name="password" class="form-control"
                                                            placeholder="Enter new password">
                                                    </div>
                                                    @error('password')
                                                        <small class="text-danger">{{ $message }}</small>
                                                    @enderror
                                                </div>

                                                <!-- Confirm Password -->
                                                <div class="col-lg-6">
                                                    <label class="form-label">Confirm Password</label>
                                                    <div class="input-group">
                                                        <div class="input-group-text">
                                                            <i class="feather-lock"></i>
                                                        </div>
                                                        <input type="password" name="password_confirmation" class="form-control"
                                                            placeholder="Confirm new password">
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endauth
                                </div>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="m-4 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                {{ isset($user) ? 'Update User' : 'Save User' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->

    <!-- Styles -->
    <style>
        .image-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid #ddd;
            background: #fff;
        }

        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .iti__country-list {
            width: 250px !important;
            max-height: 250px;
            overflow-y: auto;
        }
    </style>

    <!-- Scripts -->
    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function () {
                document.getElementById('preview').src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }

        document.addEventListener("DOMContentLoaded", function () {
            var input = document.querySelector("#phone");
            if (!input) return;

            var iti = window.intlTelInput(input, {
                initialCountry: "in",
                separateDialCode: true,
                preferredCountries: ["in", "us", "gb"],
                utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/utils.js"
            });

            @if(isset($user) && isset($user->country_code))
                var savedDialCode = "{{ str_replace('+', '', $user->country_code) }}";
                var country = window.intlTelInputGlobals.getCountryData()
                    .find(c => c.dialCode === savedDialCode);
                if (country) {
                    iti.setCountry(country.iso2);
                }
            @endif

            function updateCountryCode() {
                var countryData = iti.getSelectedCountryData();
                document.querySelector("#country_code").value = "+" + countryData.dialCode;
            }

            updateCountryCode();
            input.addEventListener("countrychange", updateCountryCode);
            input.form && input.form.addEventListener("submit", updateCountryCode);
        });
    </script>
@endsection