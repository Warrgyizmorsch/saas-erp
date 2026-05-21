@extends('shared::layouts.app')

@section('content')
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">User Profile</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                <li class="breadcrumb-item active">User Profile</li>
            </ul>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show m-x-30" role="alert">
            <i class="feather-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show m-x-30" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li><i class="feather-alert-triangle me-2"></i> {{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- [ Main Content ] start -->
    <div class="main-content crm-page-container pt-0">
        <div class="row">
            <!-- Left Column (Avatar & Quick Info Card) -->
            <div class="col-lg-4 col-md-5 mb-4">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 glass-card">
                    <div class="card-header bg-transparent border-0 text-center pt-4 pb-0">
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1.5 fs-12 fw-bold text-uppercase tracking-wider">
                            {{ $user->role?->name ?? 'Staff Member' }}
                        </span>
                    </div>
                    <div class="card-body text-center d-flex flex-column justify-content-center align-items-center py-5">
                        <!-- Profile Image Upload Form -->
                        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" id="avatarForm" class="w-100">
                            @csrf
                            <div class="position-relative d-inline-block mb-4">
                                <div class="avatar-container shadow-lg rounded-circle border border-4 border-white overflow-hidden mx-auto position-relative" style="width: 160px; height: 160px;">
                                    @if($user->image)
                                        <img id="avatarPreview" src="{{ asset('storage/' . $user->image) }}" alt="Avatar" class="w-100 h-100 object-fit-cover">
                                    @else
                                        <img id="avatarPreview" src="/images/blank.jpeg" alt="Avatar" class="w-100 h-100 object-fit-cover">
                                    @endif
                                    
                                    <!-- Upload Overlay on Hover -->
                                    <label for="avatarInput" class="avatar-upload-overlay d-flex flex-column align-items-center justify-content-center position-absolute top-0 start-0 w-100 h-100 text-white cursor-pointer m-0">
                                        <i class="feather-camera fs-22 mb-1"></i>
                                        <span class="fs-10 text-uppercase fw-bold">Update</span>
                                        <input type="file" name="image" id="avatarInput" class="d-none" accept="image/*" onchange="submitAvatar(event)">
                                    </label>
                                </div>
                            </div>
                            <input type="hidden" name="name" value="{{ $user->name }}">
                            <input type="hidden" name="mobile" value="{{ $user->contact_no }}">
                        </form>

                        <h4 class="fw-bold mb-1 text-dark">{{ $user->name }}</h4>
                        <p class="text-muted fs-14 mb-3 d-flex align-items-center justify-content-center gap-1.5">
                            <i class="feather-mail text-primary"></i> {{ $user->email }}
                        </p>

                        <hr class="w-75 my-4 opacity-10">

                        <!-- System Details Grid -->
                        <div class="row g-3 w-100 text-start px-2">
                            <div class="col-6">
                                <div class="p-3 bg-light rounded-3 text-center border border-light-subtle">
                                    <h6 class="text-muted fs-11 text-uppercase fw-semibold mb-1">Joined</h6>
                                    <p class="fw-bold text-dark mb-0 fs-13">{{ $user->created_at?->format('d M Y') ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-light rounded-3 text-center border border-light-subtle">
                                    <h6 class="text-muted fs-11 text-uppercase fw-semibold mb-1">Status</h6>
                                    <p class="mb-0"><span class="badge bg-success-subtle text-success border border-success-subtle fs-12 px-2.5 rounded-pill">Active</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column (Profile details & Password Update Forms) -->
            <div class="col-lg-8 col-md-7">
                <!-- Profile details form -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 glass-card">
                    <div class="card-header bg-transparent border-bottom border-light-subtle py-4 px-4">
                        <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="feather-user text-primary"></i> Personal Details
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('profile.update') }}" method="POST">
                            @csrf
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-muted mb-2 fs-13">Full Name <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-light-subtle text-muted"><i class="feather-user"></i></span>
                                        <input type="text" name="name" class="form-control border-light-subtle rounded-end" value="{{ old('name', $user->name) }}" required placeholder="Enter full name">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-muted mb-2 fs-13">Mobile / Contact No</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-light-subtle text-muted"><i class="feather-phone"></i></span>
                                        <input type="text" name="mobile" class="form-control border-light-subtle rounded-end" value="{{ old('mobile', $user->contact_no) }}" placeholder="Enter mobile number">
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-semibold text-muted mb-2 fs-13">Email Address (Read-only)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-light-subtle text-muted"><i class="feather-mail"></i></span>
                                        <input type="email" class="form-control border-light-subtle rounded-end bg-light-subtle text-muted" value="{{ $user->email }}" readonly>
                                    </div>
                                    <small class="text-muted mt-2 d-block fs-12"><i class="feather-info me-1"></i> Contact systems administrator to update registered email address.</small>
                                </div>

                                <div class="col-md-12 text-end mt-4">
                                    <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-semibold">
                                        Save Changes
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Password change form -->
                <div class="card border-0 shadow-sm rounded-4 glass-card">
                    <div class="card-header bg-transparent border-bottom border-light-subtle py-4 px-4">
                        <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="feather-lock text-primary"></i> Change Password
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('profile.password.update') }}" method="POST">
                            @csrf
                            <div class="row g-4">
                                @if($user->role_id === 1)
                                    <!-- Searchable Select2 user picker for Admin -->
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold text-muted mb-2 fs-13">Target Team Member <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-light-subtle text-muted"><i class="feather-users"></i></span>
                                            <select name="user_id" id="userIdSelect" class="form-control border-light-subtle select2-dropdown rounded-end" style="width: calc(100% - 40px);" required>
                                                <option value="{{ $user->id }}" selected>My Account ({{ $user->name }})</option>
                                                @foreach($users as $staff)
                                                    @if($staff->id !== $user->id)
                                                        <option value="{{ $staff->id }}">{{ $staff->name }} ({{ $staff->email }})</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                        <small class="text-muted mt-2 d-block fs-12"><i class="feather-shield me-1"></i> Admin Privileges: You can change the password of any active staff member.</small>
                                    </div>
                                @else
                                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                                @endif

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-muted mb-2 fs-13">New Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-light-subtle text-muted"><i class="feather-lock"></i></span>
                                        <input type="password" name="password" class="form-control border-light-subtle rounded-end" placeholder="Enter new password" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-muted mb-2 fs-13">Confirm New Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-light-subtle text-muted"><i class="feather-lock"></i></span>
                                        <input type="password" name="password_confirmation" class="form-control border-light-subtle rounded-end" placeholder="Confirm new password" required>
                                    </div>
                                </div>

                                <div class="col-md-12 text-end mt-4">
                                    <button type="submit" class="btn btn-warning px-4 py-2 rounded-3 fw-semibold text-dark">
                                        Update Password
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->

    <!-- Profile Specific CSS -->
    <style>
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.25) !important;
            transition: all 0.3s ease;
        }

        .avatar-container {
            transition: all 0.3s ease;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08) !important;
        }

        .avatar-upload-overlay {
            background: rgba(0, 0, 0, 0.6);
            opacity: 0;
            transition: all 0.3s ease;
            border-radius: 50%;
        }

        .avatar-container:hover .avatar-upload-overlay {
            opacity: 1;
        }

        .cursor-pointer {
            cursor: pointer;
        }
        
        .gap-1\.5 {
            gap: 6px !important;
        }
    </style>

    <!-- Profile Specific Scripts -->
    <script>
        function submitAvatar(event) {
            const input = event.target;
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatarPreview').src = e.target.result;
                    // Submit avatar automatically
                    document.getElementById('avatarForm').submit();
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            // Initialize Select2 if available
            if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
                jQuery('#userIdSelect').select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Search staff member...'
                });
            }
        });
    </script>
@endsection
