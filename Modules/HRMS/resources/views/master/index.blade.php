@extends('shared::layouts.app')

@section('title', 'Master Settings')

@section('content')
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10" style="color: #3858f9; font-weight: 700;">Master Settings</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('hrms.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Master Module</li>
        </ul>
    </div>
</div>

<div class="main-content pt-4">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; background: white; overflow: hidden;">
                <div class="card-header bg-white border-bottom p-0">
                    <ul class="nav nav-tabs nav-fill border-0" id="masterTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold py-3 text-uppercase" id="dept-tab" data-bs-toggle="tab" data-bs-target="#dept-pane" type="button" role="tab" style="font-size: 12px; letter-spacing: 0.5px; border: none; border-bottom: 3px solid transparent;">
                                Departments
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold py-3 text-uppercase" id="desg-tab" data-bs-toggle="tab" data-bs-target="#desg-pane" type="button" role="tab" style="font-size: 12px; letter-spacing: 0.5px; border: none; border-bottom: 3px solid transparent;">
                                Designations
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold py-3 text-uppercase" id="role-tab" data-bs-toggle="tab" data-bs-target="#role-pane" type="button" role="tab" style="font-size: 12px; letter-spacing: 0.5px; border: none; border-bottom: 3px solid transparent;">
                                Roles
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-4">
                    <div class="tab-content" id="masterTabContent">
                        
                        <!-- DEPARTMENT PANE -->
                        <div class="tab-pane fade show active" id="dept-pane" role="tabpanel" aria-labelledby="dept-tab">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h6 class="fw-bold mb-0 text-dark">Department Master</h6>
                                <button type="button" class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#addDeptModal">
                                    <i class="feather-plus me-1"></i> Add Department
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead style="background: #f8fafc;">
                                        <tr>
                                            <th class="ps-4" style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Name</th>
                                            <th style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Short Name</th>
                                            <th style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Status</th>
                                            <th class="text-center pe-4" style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($departments as $dept)
                                        <tr>
                                            <td class="ps-4 fw-bold text-dark">{{ $dept->name }}</td>
                                            <td>{{ $dept->short_name ?? '-' }}</td>
                                            <td>
                                                @if($dept->is_active)
                                                    <span class="badge bg-soft-success text-success">Active</span>
                                                @else
                                                    <span class="badge bg-soft-danger text-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td class="text-center pe-4">
                                                <div class="d-flex justify-content-center gap-2">
                                                    <button class="avatar-text avatar-sm bg-soft-info text-info rounded border-0" onclick="editDept({{ $dept }})">
                                                        <i class="feather-edit-3"></i>
                                                    </button>
                                                    <form action="{{ route('master.department.destroy', $dept->id) }}" method="POST" onsubmit="return deleteData(event);">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="avatar-text avatar-sm bg-soft-danger text-danger rounded border-0">
                                                            <i class="feather-trash-2"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- DESIGNATION PANE -->
                        <div class="tab-pane fade" id="desg-pane" role="tabpanel" aria-labelledby="desg-tab">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h6 class="fw-bold mb-0 text-dark">Designation Master</h6>
                                <button type="button" class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#addDesgModal">
                                    <i class="feather-plus me-1"></i> Add Designation
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead style="background: #f8fafc;">
                                        <tr>
                                            <th class="ps-4" style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Name</th>
                                            <th style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Status</th>
                                            <th class="text-center pe-4" style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($designations as $desg)
                                        <tr>
                                            <td class="ps-4 fw-bold text-dark">{{ $desg->name }}</td>
                                            <td>
                                                @if($desg->is_active)
                                                    <span class="badge bg-soft-success text-success">Active</span>
                                                @else
                                                    <span class="badge bg-soft-danger text-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td class="text-center pe-4">
                                                <div class="d-flex justify-content-center gap-2">
                                                    <button class="avatar-text avatar-sm bg-soft-info text-info rounded border-0" onclick="editDesg({{ $desg }})">
                                                        <i class="feather-edit-3"></i>
                                                    </button>
                                                    <form action="{{ route('master.designation.destroy', $desg->id) }}" method="POST" onsubmit="return deleteData(event);">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="avatar-text avatar-sm bg-soft-danger text-danger rounded border-0">
                                                            <i class="feather-trash-2"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- ROLE PANE -->
                        <div class="tab-pane fade" id="role-pane" role="tabpanel" aria-labelledby="role-tab">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h6 class="fw-bold mb-0 text-dark">Role Master</h6>
                                <button type="button" class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                                    <i class="feather-plus me-1"></i> Add Role
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead style="background: #f8fafc;">
                                        <tr>
                                            <th class="ps-4" style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Name</th>
                                            <th style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Slug (System Key)</th>
                                            <th style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Status</th>
                                            <th class="text-center pe-4" style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($roles as $role)
                                        <tr>
                                            <td class="ps-4 fw-bold text-dark">{{ $role->name }}</td>
                                            <td><code class="text-primary">{{ $role->slug }}</code></td>
                                            <td>
                                                @if($role->is_active)
                                                    <span class="badge bg-soft-success text-success">Active</span>
                                                @else
                                                    <span class="badge bg-soft-danger text-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td class="text-center pe-4">
                                                <div class="d-flex justify-content-center gap-2">
                                                    <button class="avatar-text avatar-sm bg-soft-info text-info rounded border-0" onclick="editRole({{ $role }})">
                                                        <i class="feather-edit-3"></i>
                                                    </button>
                                                    <form action="{{ route('master.role.destroy', $role->id) }}" method="POST" onsubmit="return deleteData(event);">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="avatar-text avatar-sm bg-soft-danger text-danger rounded border-0">
                                                            <i class="feather-trash-2"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODALS -->
@include('hrms::master.modals')

@endsection

@push('scripts')
<style>
    .nav-tabs .nav-link.active {
        color: #3858f9 !important;
        background: transparent !important;
        border-bottom: 3px solid #3858f9 !important;
    }
    .nav-tabs .nav-link {
        color: #94a3b8;
    }
    .nav-tabs .nav-link:hover {
        color: #3858f9;
        border-color: #f1f5f9;
    }
    .bg-soft-success { background: rgba(34, 197, 94, 0.1) !important; color: #22c55e !important; }
    .bg-soft-danger { background: rgba(239, 68, 68, 0.1) !important; color: #ef4444 !important; }
    .bg-soft-info { background: rgba(13, 202, 240, 0.1) !important; color: #0dcaf0 !important; }
</style>

<script>
    function editDept(data) {
        document.getElementById('editDeptForm').action = "/master/department/" + data.id;
        document.getElementById('editDeptName').value = data.name;
        document.getElementById('editDeptShortName').value = data.short_name;
        document.getElementById('editDeptActive').checked = data.is_active;
        new bootstrap.Modal(document.getElementById('editDeptModal')).show();
    }
    function editDesg(data) {
        document.getElementById('editDesgForm').action = "/master/designation/" + data.id;
        document.getElementById('editDesgName').value = data.name;
        document.getElementById('editDesgActive').checked = data.is_active;
        new bootstrap.Modal(document.getElementById('editDesgModal')).show();
    }
    function editRole(data) {
        document.getElementById('editRoleForm').action = "/master/role/" + data.id;
        document.getElementById('editRoleName').value = data.name;
        document.getElementById('editRoleActive').checked = data.is_active;
        new bootstrap.Modal(document.getElementById('editRoleModal')).show();
    }
</script>
@endpush
