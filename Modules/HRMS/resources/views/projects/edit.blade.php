@extends('shared::layouts.app')

@section('title', 'Edit Project')

@section('content')
    <!-- [ page-header ] start -->
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Projects</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('hrms.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Edit Project</li>
            </ul>
        </div>
    </div>
    <!-- [ page-header ] end -->

    <div class="main-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="card border-top-0">
                    <div class="card-body p-0">
                        <form id="project-edit-form" action="{{ route('projects.update', $project) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div id="project-edit-wizard" style="visibility: hidden; height: 0; overflow: hidden;">

                                <!-- Step 1: Type -->
                                <h3>TYPE</h3>
                                <section>
                                    <div class="mb-5">
                                        <h2 class="fs-16 fw-bold">Project type</h2>
                                        <p class="text-muted">Select project type first.</p>
                                    </div>
                                    <fieldset>
                                        <label class="w-100" for="project_personal">
                                            <input class="card-input-element" type="radio" name="type" id="project_personal"
                                                value="Personal" {{ $project->type == 'Personal' ? 'checked' : '' }} required>
                                            <span
                                                class="card card-body d-flex flex-row justify-content-between align-items-center">
                                                <span class="hstack gap-3">
                                                    <span class="avatar-text text-primary"
                                                        style="background: rgba(52, 84, 209, 0.1); width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 8px;"><i
                                                            class="feather-user"></i></span>
                                                    <span>
                                                        <span class="d-block fs-13 fw-bold text-dark">Personal
                                                            Project</span>
                                                        <span class="d-block text-muted mb-0">Individual project
                                                            management</span>
                                                    </span>
                                                </span>
                                            </span>
                                        </label>
                                        <label class="w-100" for="project_team">
                                            <input class="card-input-element" type="radio" name="type" id="project_team"
                                                value="Team" {{ $project->type == 'Team' ? 'checked' : '' }}>
                                            <span
                                                class="card card-body d-flex flex-row justify-content-between align-items-center">
                                                <span class="hstack gap-3">
                                                    <span class="avatar-text text-primary"
                                                        style="background: rgba(52, 84, 209, 0.1); width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 8px;"><i
                                                            class="feather-users"></i></span>
                                                    <span>
                                                        <span class="d-block fs-13 fw-bold text-dark">Team Project</span>
                                                        <span class="d-block text-muted mb-0">Collaborative project for
                                                            multiple members</span>
                                                    </span>
                                                </span>
                                            </span>
                                        </label>
                                    </fieldset>
                                    <hr class="my-5">
                                    <div class="mb-5">
                                        <h2 class="fs-16 fw-bold">Project manage</h2>
                                        <p class="text-muted">Who can manage projects</p>
                                    </div>
                                    <fieldset>
                                        <label class="w-100" for="project_everyone">
                                            <input class="card-input-element" type="radio" name="manage"
                                                id="project_everyone" value="Everyone" {{ $project->manage == 'Everyone' || !$project->manage ? 'checked' : '' }} required>
                                            <span
                                                class="card card-body d-flex flex-row justify-content-between align-items-center">
                                                <span class="hstack gap-3">
                                                    <span class="avatar-text text-primary"
                                                        style="background: rgba(52, 84, 209, 0.1); width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 8px;"><i
                                                            class="feather-globe"></i></span>
                                                    <span>
                                                        <span class="d-block fs-13 fw-bold text-dark">Everyone</span>
                                                        <span class="d-block text-muted mb-0">Visible to all authenticated
                                                            users.</span>
                                                    </span>
                                                </span>
                                            </span>
                                        </label>
                                        <label class="w-100" for="project_admin">
                                            <input class="card-input-element" type="radio" name="manage" id="project_admin"
                                                value="Admin" {{ $project->manage == 'Admin' ? 'checked' : '' }}>
                                            <span
                                                class="card card-body d-flex flex-row justify-content-between align-items-center">
                                                <span class="hstack gap-3">
                                                    <span class="avatar-text text-primary"
                                                        style="background: rgba(52, 84, 209, 0.1); width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 8px;"><i
                                                            class="feather-shield"></i></span>
                                                    <span>
                                                        <span class="d-block fs-13 fw-bold text-dark">Only Admin's</span>
                                                        <span class="d-block text-muted mb-0">Only admins can manage.</span>
                                                    </span>
                                                </span>
                                            </span>
                                        </label>
                                    </fieldset>

                                </section>

                                <!-- Step 2: Details -->
                                <h3>DETAILS</h3>
                                <section>
                                    <div class="mb-5">
                                        <h2 class="fs-16 fw-bold">Project details</h2>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <label class="form-label fw-bold fs-12 text-muted text-uppercase">Project Name
                                                <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control premium-input" name="name"
                                                value="{{ $project->name }}" placeholder="Enter project name..." required>
                                        </div>
                                        <div class="col-md-6 mb-4">
                                            <label
                                                class="form-label fw-bold fs-12 text-muted text-uppercase">Technology</label>
                                            <input type="text" class="form-control premium-input" name="technology"
                                                value="{{ $project->technology }}" placeholder="e.g. PHP, Laravel, React">
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <label
                                            class="form-label fw-bold fs-12 text-muted text-uppercase">Description</label>
                                        <textarea name="description" id="projectDescriptionEditor"
                                            style="display:none;">{!! $project->description !!}</textarea>
                                        <textarea id="summernote-main" class="form-control"
                                            style="min-height: 250px;">{!! $project->description !!}</textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <label class="form-label fw-bold fs-12 text-muted text-uppercase">Start Date
                                                <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control premium-input" name="start_date"
                                                value="{{ $project->start_date ? $project->start_date->format('Y-m-d') : '' }}"
                                                required>
                                        </div>
                                        <div class="col-md-6 mb-4">
                                            <label class="form-label fw-bold fs-12 text-muted text-uppercase">End
                                                Date</label>
                                            <input type="date" class="form-control premium-input" name="end_date"
                                                value="{{ $project->end_date ? $project->end_date->format('Y-m-d') : '' }}"
                                                onclick="this.showPicker()">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <label class="form-label fw-bold fs-12 text-muted text-uppercase">Department
                                                <span class="text-danger">*</span></label>
                                            <select class="form-select premium-select" id="projectDepartment" name="department"
                                                data-placeholder="Select Department" required>
                                                <option value=""></option>
                                                @foreach($departments as $dept)
                                                    <option value="{{ $dept->name }}" {{ $project->department == $dept->name ? 'selected' : '' }}>{{ $dept->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-4">
                                            <label class="form-label fw-bold fs-12 text-muted text-uppercase">Status</label>
                                            <select class="form-select premium-select" name="status"
                                                data-placeholder="Select Status">
                                                <option value=""></option>
                                                @php
                                                    $statusMap = [
                                                        'Not Started' => 'Pending',
                                                        'In Progress' => 'In Process',
                                                        'Finished' => 'Completed',
                                                        'Declined' => 'Rework'
                                                    ];
                                                    $displayStatus = $statusMap[$project->status] ?? $project->status;
                                                @endphp
                                                <option value="Pending" {{ $displayStatus == 'Pending' ? 'selected' : '' }}>
                                                    Pending</option>
                                                <option value="In Process" {{ $displayStatus == 'In Process' ? 'selected' : '' }}>In Process</option>
                                                <option value="Completed" {{ $displayStatus == 'Completed' ? 'selected' : '' }}>Completed</option>
                                                <option value="On Hold" {{ $displayStatus == 'On Hold' ? 'selected' : '' }}>On
                                                    Hold</option>
                                                <option value="Review" {{ $displayStatus == 'Review' ? 'selected' : '' }}>
                                                    Review</option>
                                                <option value="Rework" {{ $displayStatus == 'Rework' ? 'selected' : '' }}>
                                                    Rework</option>
                                            </select>
                                        </div>
                                    </div>
                                </section>

                                <!-- Step 3: Members -->
                                <h3>MEMBERS</h3>
                                <section>
                                    <div class="mb-5">
                                        <h2 class="fs-16 fw-bold">Project Assignment</h2>
                                        <p class="text-muted">Select project leads and team members.</p>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <label class="form-label fw-bold fs-12 text-muted text-uppercase">Project Leads
                                                <span class="text-danger">*</span></label>
                                            <select class="form-select premium-select" id="projectLeaders" name="leaders[]" multiple="multiple"
                                                data-placeholder="Select Project Leads..." required>
                                                @foreach($employees as $emp)
                                                    <option value="{{ $emp->id }}" {{ is_array($project->leaders) && in_array($emp->id, $project->leaders) ? 'selected' : '' }}>
                                                        {{ $emp->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-4">
                                            <label class="form-label fw-bold fs-12 text-muted text-uppercase">Team
                                                Members</label>
                                            <select class="form-select premium-select" id="projectMembers" name="members[]" multiple="multiple"
                                                data-placeholder="Select Team Members...">
                                                @foreach($employees as $emp)
                                                    <option value="{{ $emp->id }}" {{ is_array($project->members) && in_array($emp->id, $project->members) ? 'selected' : '' }}>
                                                        {{ $emp->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </section>


                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Wizard Header Styling */
        #project-edit-wizard h3 {
            display: none !important;
        }

        .wizard>.content>section {
            display: none !important;
        }

        .wizard>.content>section.current {
            display: block !important;
        }

        .wizard>.steps {
            background: #fff !important;
            border-bottom: 1px solid #e2e8f0 !important;
        }

        .wizard>.steps ul {
            display: flex !important;
            padding: 0 !important;
            margin: 0 !important;
            list-style: none !important;
            width: 100% !important;
        }

        .wizard>.steps li {
            flex: 1 !important;
            text-align: center !important;
            border-right: 1px solid #e2e8f0 !important;
        }

        .wizard>.steps a {
            display: block !important;
            padding: 25px 15px !important;
            color: #64748b !important;
            font-weight: 700 !important;
            text-decoration: none !important;
            border-bottom: 4px solid transparent !important;
            font-size: 11px !important;
            text-transform: uppercase !important;
        }

        .wizard>.steps .current a {
            color: #3454d1 !important;
            border-bottom-color: #3454d1 !important;
        }

        .wizard>.content {
            padding: 50px !important;
            background: #fff !important;
            min-height: 450px;
        }

        .wizard>.actions {
            padding: 20px 50px !important;
            background: #fff !important;
            border-top: 1px solid #e2e8f0 !important;
        }

        .wizard>.actions ul {
            display: flex !important;
            justify-content: flex-end !important;
            gap: 10px !important;
            list-style: none !important;
        }

        .wizard>.actions a {
            display: block !important;
            padding: 10px 25px !important;
            background: #3454d1 !important;
            color: #fff !important;
            border-radius: 4px !important;
            text-decoration: none !important;
        }

        .card-input-element {
            display: none;
        }

        .card-input-element:checked+.card {
            border-color: #3454d1 !important;
            background-color: rgba(52, 84, 209, 0.05) !important;
            border-width: 2px !important;
        }

        /* Premium Input Styling */
        .premium-input,
        .premium-select {
            background-color: #f8fafc !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 12px !important;
            height: 48px !important;
            padding: 10px 16px !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            color: #1e293b !important;
            transition: all 0.2s ease !important;
        }

        .premium-input:focus {
            background-color: #fff !important;
            border-color: #3454d1 !important;
            box-shadow: 0 0 0 4px rgba(52, 84, 209, 0.1) !important;
        }

        /* Select2 Premium Styling */
        .select2-container--default .select2-selection--single,
        .select2-container--default .select2-selection--multiple {
            min-height: 48px !important;
            border-radius: 12px !important;
            border: 1px solid #ebf0f5 !important;
            display: flex !important;
            align-items: center !important;
            background-color: #fcfdfe !important;
            transition: all 0.2s ease;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered,
        .select2-container--default .select2-selection--multiple .select2-selection__rendered {
            color: #1a202c !important;
            font-weight: 600 !important;
            padding-left: 15px !important;
            font-size: 13px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 46px !important;
            right: 10px !important;
        }

        .select2-dropdown {
            border: 0 !important;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1) !important;
            border-radius: 15px !important;
            overflow: hidden !important;
            margin-top: 8px !important;
            padding: 8px 0 !important;
            z-index: 99999 !important;
        }

        .select2-search--dropdown {
            padding: 12px 15px !important;
        }

        .select2-search--dropdown .select2-search__field {
            border-radius: 10px !important;
            border: 1px solid #ebf0f5 !important;
            padding: 10px 15px !important;
            background-color: #f8fafc !important;
            font-size: 13px !important;
        }

        .select2-results__option {
            padding: 10px 15px !important;
            margin: 2px 10px !important;
            border-radius: 10px !important;
            font-weight: 600 !important;
            font-size: 13px !important;
            color: #4a5568 !important;
            transition: all 0.2s ease;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: rgba(56, 88, 249, 0.08) !important;
            color: #3858f9 !important;
            color: #3858f9 !important;
        }

        .select2-container--default .select2-results__option[aria-selected="true"] {
            background-color: rgba(56, 88, 249, 0.1) !important;
            color: #3858f9 !important;
        }

        /* Multiple selection chip styling */
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #3454d1 !important;
            border: none !important;
            color: #fff !important;
            border-radius: 6px !important;
            padding: 4px 10px !important;
            font-size: 11px !important;
            margin-top: 7px !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #fff !important;
            margin-right: 5px !important;
        }
    </style>
@endsection

@push('scripts')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-steps/1.1.0/jquery.steps.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-steps/1.1.0/jquery.steps.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function () {
            var form = $("#project-edit-form");
            const allEmployees = @json($employees);
            const initialLeaderIds = @json(is_array($project->leaders) ? array_map('strval', $project->leaders) : []);
            const initialMemberIds = @json(is_array($project->members) ? array_map('strval', $project->members) : []);

            function syncDepartmentEmployees() {
                const selectedDepartment = $('#projectDepartment').val();
                const currentLeaderIds = ($('#projectLeaders').val() || []).map(String);
                const currentMemberIds = ($('#projectMembers').val() || []).map(String);
                const leaderIdsToKeep = currentLeaderIds.length ? currentLeaderIds : initialLeaderIds;
                const memberIdsToKeep = currentMemberIds.length ? currentMemberIds : initialMemberIds;
                const filteredEmployees = selectedDepartment
                    ? allEmployees.filter(emp => emp.department === selectedDepartment)
                    : allEmployees;

                $('#projectLeaders').empty();
                $('#projectMembers').empty();

                filteredEmployees.forEach(function (emp) {
                    const empId = String(emp.id);
                    const leaderSelected = leaderIdsToKeep.includes(empId) ? 'selected' : '';
                    const memberSelected = memberIdsToKeep.includes(empId) ? 'selected' : '';

                    $('#projectLeaders').append(`<option value="${emp.id}" ${leaderSelected}>${emp.name}</option>`);
                    $('#projectMembers').append(`<option value="${emp.id}" ${memberSelected}>${emp.name}</option>`);
                });

                $('#projectLeaders').trigger('change.select2');
                $('#projectMembers').trigger('change.select2');
            }

            $("#project-edit-wizard").steps({
                headerTag: "h3",
                bodyTag: "section",
                transitionEffect: "fade",
                autoFocus: true,
                labels: { finish: "UPDATE", next: "NEXT", previous: "PREVIOUS" },
                onInit: function (event, currentIndex) {
                    $("#project-edit-wizard").css({ 'visibility': 'visible', 'height': 'auto', 'overflow': 'visible' });

                    // Initialize Summernote FIRST
                    $('#summernote-main').summernote({ height: 250 });

                    // Initialize Select2
                    if ($.fn.select2) {
                        $('.premium-select').each(function () {
                            $(this).select2({
                                width: '100%',
                                placeholder: $(this).data('placeholder'),
                                allowClear: true,
                                minimumResultsForSearch: 0
                            });
                        });
                    }

                    syncDepartmentEmployees();
                },
                onStepChanging: function (event, currentIndex, newIndex) {
                    if (currentIndex > newIndex) return true;

                    // Validation for Step 2: Details
                    if (currentIndex === 1) {
                        var name = $('input[name="name"]').val();
                        var dept = $('select[name="department"]').val();
                        var startDate = $('input[name="start_date"]').val();
                        var desc = $('#summernote-main').summernote('code');

                        if (!name || !dept || !startDate) {
                            if (typeof Toast !== 'undefined') {
                                Toast.fire({ icon: 'error', title: 'Please fill all mandatory fields including Name, Department and Start Date.' });
                            } else {
                                alert('Please fill all mandatory fields including Name, Department and Start Date.');
                            }
                            return false;
                        }
                    }

                    // Validation for Step 3: Members (Team Lead check)
                    if (currentIndex === 2) {
                        var leaders = $('select[name="leaders[]"]').val();
                        if (!leaders || leaders.length === 0) {
                            if (typeof Toast !== 'undefined') {
                                Toast.fire({ icon: 'error', title: 'Please select at least one Project Lead.' });
                            } else {
                                alert('Please select at least one Project Lead.');
                            }
                            return false;
                        }
                    }

                    $('#projectDescriptionEditor').val($('#summernote-main').summernote('code'));
                    return true;
                },
                onFinished: function (event, currentIndex) {
                    $('#projectDescriptionEditor').val($('#summernote-main').summernote('code'));
                    form.submit();
                }
            });
        });

        $(document).ready(function () {
            $('#projectDepartment').on('change', function () {
                const selectedDepartment = $(this).val();
                const allEmployees = @json($employees);
                const currentLeaderIds = ($('#projectLeaders').val() || []).map(String);
                const currentMemberIds = ($('#projectMembers').val() || []).map(String);
                const filteredEmployees = selectedDepartment
                    ? allEmployees.filter(emp => emp.department === selectedDepartment)
                    : allEmployees;

                $('#projectLeaders').empty();
                $('#projectMembers').empty();

                filteredEmployees.forEach(function (emp) {
                    const empId = String(emp.id);
                    const leaderSelected = currentLeaderIds.includes(empId) ? 'selected' : '';
                    const memberSelected = currentMemberIds.includes(empId) ? 'selected' : '';

                    $('#projectLeaders').append(`<option value="${emp.id}" ${leaderSelected}>${emp.name}</option>`);
                    $('#projectMembers').append(`<option value="${emp.id}" ${memberSelected}>${emp.name}</option>`);
                });

                $('#projectLeaders').trigger('change.select2');
                $('#projectMembers').trigger('change.select2');
            });
        });
    </script>
@endpush
