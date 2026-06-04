@extends('shared::layouts.app')

@section('content')
    <!-- [ page-header ] start -->
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Job Requirement</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('hrms.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Job Requirement</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex d-md-none">
                    <a href="javascript:void(0)" class="page-header-right-close-toggle">
                        <i class="feather-arrow-left me-2"></i>
                        <span>Back</span>
                    </a>
                </div>
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <div id="bulk-action-wrapper" style="display: none;">
                        <a href="javascript:void(0);" id="btn-bulk-delete" class="btn btn-icon btn-soft-danger"
                            style="width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; 
                            justify-content: center;">
                            <i class="feather-trash-2 fs-18"></i>
                        </a>
                    </div>
                    <div class="filter-toggle-wrapper">
                        <!-- <a href="javascript:void(0);" class="btn btn-icon btn-light-brand" id="toggleFilter"
                            style="cursor: pointer;">
                            <i class="feather-filter"></i>
                        </a> -->
                    </div>
                    <button type="button" class="btn btn-primary" id="showAddRequirement">
                        <i class="feather-plus me-2"></i>
                        <span>Add Requirement</span>
                    </button>
                </div>
            </div>
            <div class="d-md-none d-flex align-items-center">
                <a href="javascript:void(0)" class="page-header-right-open-toggle">
                    <i class="feather-align-right fs-20"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="container mt-4 d-none" id="requirementFormContainer">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white">
                    <h4 class="text-white">Job Requirement</h4>
                </div>

                <div class="card-body">
                    <form action="{{route('requirement.store')}}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Role</label>
                                <select name="role_id" class="form-select" required>
                                    <option value="">Select Role</option>
                                    @foreach($roles as $role)
                                        <option value="{{$role->id}}">{{$role->name}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Priority</label>
                                <select name="priority" class="form-select">
                                    <option value="">Select</option>
                                    <option>Urgent</option>
                                    <option>High</option>
                                    <option>Medium</option>
                                    <option>Low</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Required Date</label>
                                <input type="date" name="date" class="form-control">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Fresher / Experience</label>
                                <select name="candidate_type" id="candidateType" class="form-select">
                                    <option value="">Select</option>
                                    <option>Fresher</option>
                                    <option>Experience</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3 d-none" id="experienceDiv">
                                <label>Minimum Experience</label>
                                <input type="number" name="minimum_experience" class="form-control" placeholder="Years">
                            </div>

                            <div class="col-md-12 mb-3">
                                <label>Skills</label>
                                <input type="text" name="skills" class="form-control" placeholder="HTML,CSS,Javascript,React">
                                <small>Use comma separated skills</small>
                            </div>

                            <div class="text-end">
                                <button class="btn btn-primary">Save</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="card mt-4 shadow">
            <div class="card-header">
                <h5>Requirement List</h5>
            </div>

            <div class="card-body">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Role Details</th>
                            <th>Skills</th>
                            <th>Status</th>
                            <th>Action</th>
                            <th>Interview Count</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($requirements as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                
                                <td>
                                    <strong>{{ $item->role_name }}</strong><br>
                                    <small>
                                        Priority: {{ $item->priority }} | 
                                        Required Date: {{ $item->date }} | 
                                        @if(strtolower($item->candidate_type) == 'experience')
                                            Experience: {{ $item->minimum_experience ?? '0' }} Yrs
                                        @else
                                            Fresher
                                        @endif
                                    </small>
                                </td>

                                <td>
                                    @php
                                        $skills = is_array($item->skills)
                                            ? $item->skills
                                            : json_decode($item->skills, true);

                                        if (!is_array($skills)) {
                                            $skills = explode(',', $item->skills);
                                        }
                                    @endphp

                                    @foreach($skills as $skill)
                                        <span class="badge bg-primary text-white">{{ trim($skill) }}</span>
                                    @endforeach
                                </td>

                                <td>
                                    <select class="form-select form-select-sm status-updater" name="status" data-id="{{ $item->id }}">
                                        <option value="hold" {{ ($item->status ?? '') == 'hold' ? 'selected' : '' }}>Hold</option>
                                        <option value="hiring" {{ ($item->status ?? '') == 'hiring' ? 'selected' : '' }}>Hiring</option>
                                        <option value="hired" {{ ($item->status ?? '') == 'hired' ? 'selected' : '' }}>Hired</option>
                                    </select>
                                </td>

                                <td>
                                     <a
                                        href="javascript:void(0)"
                                        class="btn btn-sm btn-primary"
                                        data-bs-toggle="offcanvas"
                                        data-bs-target="#projectOffcanvas"
                                        data-designation="{{ $item->role_name }}"
                                    >
                                        Schedule Interview
                                    </a>
                                </td>
                                <td>
                                    @if(($item->applications_count ?? 0) > 0)
                                        <a href="{{ route('vacancy.show', ['role' => $item->role_name]) }}" 
                                        class="badge bg-info text-white text-decoration-none">
                                            <i class="feather-users me-1"></i> {{ $item->applications_count }}
                                        </a>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="projectOffcanvas" style="width:600px;">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title d-flex align-items-center gap-2">
                <i class="feather-user-plus text-primary"></i>
                Candidate Information
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>

        <div class="offcanvas-body">
            <form action="{{ url('/hrms/job-vacancy/store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <!-- Basic Information -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3 text-primary">
                            Basic Information
                        </h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name </label>
                                <input type="text" class="form-control" name="name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mobile</label>
                                <input type="text" class="form-control" name="phone">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Qualification</label>
                                <select class="form-select" name="qualification" required>
                                    <option value="">Select Qualification</option>

                                    <option value="B.Tech">B.Tech</option>
                                    <option value="BCA">BCA</option>
                                    <option value="MCA">MCA</option>
                                    <option value="M.Tech">M.Tech</option>
                                    <option value="B.Sc IT">B.Sc IT</option>
                                    <option value="M.Sc IT">M.Sc IT</option>
                                    <option value="B.Sc Computer Science">B.Sc Computer Science</option>
                                    <option value="M.Sc Computer Science">M.Sc Computer Science</option>
                                    <option value="BE Computer Engineering">BE Computer Engineering</option>
                                    <option value="Diploma in Computer Engineering">
                                        Diploma in Computer Engineering
                                    </option>
                                    <option value="PGDCA">PGDCA</option>
                                    <option value="MBA IT">MBA IT</option>
                                    <option value="Full Stack Development">Full Stack Development</option>
                                    <option value="Cyber Security">Cyber Security</option>
                                    <option value="Data Science">Data Science</option>
                                    <option value="Artificial Intelligence">Artificial Intelligence</option>
                                    <option value="Machine Learning">Machine Learning</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Department</label>

                                <select class="form-select" name="department_id" required>
                                    <option value="">Select Department</option>

                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}">
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3 d-none">
                                <label class="form-label">Designation</label>
                                <input type="text" class="form-control" name="designation" id="requirementDesignation" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Experience</label>
                                <input type="text" class="form-control" placeholder="1 years" name="experience">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Interview -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3 text-primary">Interview Information</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Interview Date</label>
                                <input type="date" value="{{ date('Y-m-d') }}" class="form-control" name="interview_date">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Interview Time</label>
                                <input type="time" class="form-control" name="interview_time">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select"name="status">
                                    <option>Pending</option>
                                    <option>Selected</option>
                                    <option>Awaited</option>
                                    <option>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Interviewer</label>
                                <select class="form-select" name="interviewer_id">
                                    <option value="">Select Interviewer</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">
                                            {{ $employee->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md">
                                <label class="form-label">Interview Description / Link</label>
                                <textarea class="form-control" name="interview_details" rows="3"
                                    placeholder="Enter interview description or meeting link"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3 text-primary">Profile Upload</h6>
                        <div class="mb-3">
                            <label class="form-label">Upload Resume/Profile</label>
                            <input type="file" class="form-control" name="resume" id="resume" accept=".pdf,.doc,.docx">
                            <div id="resumeError" class="text-danger small mt-1 d-none">
                                Resume size should not exceed 2 MB.
                            </div>
                            <small class="text-muted"> Accepted formats: PDF, DOC, DOCX | Maximum file size: 2 MB</small>
                        </div>
                    </div>
                </div>
                <button class="btn btn-primary w-100">
                    <i class="feather-save me-2"></i>
                    Save Candidate
                </button>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const candidateType = document.getElementById('candidateType');
            const experienceDiv = document.getElementById('experienceDiv');
            const projectOffcanvas = document.getElementById('projectOffcanvas');
            const requirementDesignation = document.getElementById('requirementDesignation');
            
            // Handle change event
            candidateType.addEventListener('change', function () {
                if (this.value === 'Experience') {
                    experienceDiv.classList.remove('d-none');
                } else {
                    experienceDiv.classList.add('d-none');
                }
            });

            if (projectOffcanvas && requirementDesignation) {
                projectOffcanvas.addEventListener('show.bs.offcanvas', function (event) {
                    const trigger = event.relatedTarget;
                    requirementDesignation.value = trigger?.getAttribute('data-designation') || '';
                });
            }
        });

        $(document).ready(function() {
            $('.status-updater').on('change', function() {
                let status = $(this).val();
                let requirementId = $(this).data('id');

                $.ajax({
                    url: "{{ route('requirements.update-status') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: requirementId,
                        status: status
                    },
                    success: function(response) {
                        if(response.success) {
                            alert('Status updated successfully!');
                        } else {
                            alert('Something went wrong.');
                        }
                    },
                    error: function() {
                        alert('Server error. Failed to update status.');
                    }
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            const showBtn = document.getElementById('showAddRequirement');
            const formContainer = document.getElementById('requirementFormContainer');

            if (showBtn && formContainer) {
                showBtn.addEventListener('click', function () {
                    // Check if the container is hidden, then show it; otherwise hide it
                    if (formContainer.classList.contains('d-none')) {
                        formContainer.classList.remove('d-none');
                    } else {
                        formContainer.classList.add('d-none');
                    }
                });
            }
        });
    </script>
@endpush
