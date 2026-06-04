@extends('shared::layouts.app')

@section('content')

<!-- [ page-header ] start -->
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10" style="color: #3858f9; font-weight: 700;">Role Master</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('hrms.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Roles</li>
        </ul>
    </div>
</div>
<!-- [ page-header ] end -->

<!-- [ main-content ] start -->
<div class="main-content pt-4" style="margin-bottom: 100px;">
    <div class="row g-4">
        <!-- FORM (LEFT - 4 Cols) -->
        <div class="col-xl-4 col-lg-5">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; background: white;">
                <div class="card-header bg-white border-bottom py-3" style="border-radius: 12px 12px 0 0;">
                    <h6 class="fw-bold mb-0 text-uppercase" style="color: #64748b; font-size: 11px; letter-spacing: 0.5px;">Add Role</h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('master.role.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase mb-2">Role Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control border-0 bg-light shadow-none fw-bold" 
                                placeholder="e.g. HR Manager" required style="border-radius: 10px; height: 48px; font-size: 14px;">
                        </div>
                        
                        <div class="alert alert-info border-0 rounded mb-4" style="font-size: 12px;">
                            <i class="feather-info me-1"></i> Slug generated automatically.
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm" 
                            style="background: #3858f9; border: none; height: 52px; border-radius: 10px; font-size: 14px; letter-spacing: 0.5px;">
                            SAVE ROLE
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- LIST (RIGHT - 8 Cols) -->
        <div class="col-xl-8 col-lg-7">
            <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 12px; background: white;">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center"
                    style="border-radius: 12px 12px 0 0;">
                    
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="fw-bold mb-0 me-3" style="color: #334155; font-size: 16px;">Role List</h5>
                    </div>

                    <div class="wghrm-search-container d-flex align-items-center"
                        style="width: 250px; background: #f1f5f9; border-radius: 10px; border: 1px solid #e2e8f0; height: 40px; padding: 0 15px; transition: all 0.3s ease;">
                        <i class="feather-search text-muted" style="font-size: 14px;"></i>
                        <input type="text" id="roleSearch" onkeyup="filterRoles()" placeholder="Search..."
                            style="background: transparent !important; border: none !important; box-shadow: none !important; outline: none !important; width: 100%; height: 100%; padding-left: 10px; font-size: 13px; font-weight: 500; color: #334155;">
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover mb-0" id="roleTable">
                            <thead style="background: #3858f9; color: white;">
                                <tr style="height: 60px; vertical-align: middle;">
                                    <th class="ps-4" style="font-size: 12px; font-weight: 700; color: white; text-transform: uppercase;">Sr.No.</th>
                                    <th style="font-size: 12px; font-weight: 700; color: white; text-transform: uppercase;">Role Name</th>
                                    <th style="font-size: 12px; font-weight: 700; color: white; text-transform: uppercase;">System Slug</th>
                                    <th class="pe-4 text-center" style="font-size: 12px; font-weight: 700; color: white; text-transform: uppercase;">Action</th>
                                </tr>
                            </thead>
                            <tbody style="border-top: 1px solid #f1f5f9;">
                                @forelse($roles as $index => $role)
                                <tr class="role-row" style="height: 70px;">
                                    <td class="ps-4 fw-bold" style="font-size: 14px;">{{ $index + 1 }}</td>
                                    <td class="fw-bold" style="color: #3858f9; font-size: 14px;">{{ strtoupper($role->name) }}</td>
                                    <td><code class="text-secondary">{{ $role->slug }}</code></td>
                                    <td class="pe-4 text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="button" onclick='editRole(@json($role))' class="avatar-text avatar-md bg-soft-info text-info rounded border-0" title="Edit">
                                                <i class="feather-edit-3"></i>
                                            </button>
                                            <form action="{{ route('master.role.destroy', $role->id) }}" method="POST" onsubmit="return deleteData(event);">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="avatar-text avatar-md bg-soft-danger text-danger rounded border-0" title="Delete">
                                                    <i class="feather-trash-2"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">No roles recorded.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="px-4 py-4 border-top bg-white d-flex justify-content-center" style="border-radius: 0 0 12px 12px;">
                        {{ $roles->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- EDIT ROLE OFFCANVAS -->
<div class="offcanvas offcanvas-end shadow-lg" tabindex="-1" id="editRoleModal" style="border-left: none; width: 400px;">
    <div class="offcanvas-header bg-white border-bottom p-4">
        <h6 class="offcanvas-title fw-bold text-dark mb-0">Edit Role</h6>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <form id="editRoleForm" method="POST" class="d-flex flex-column h-100">
        @csrf @method('PUT')
        <div class="offcanvas-body p-4 bg-light flex-grow-1">
            <div class="mb-3">
                <label class="form-label fw-bold text-muted small text-uppercase mb-2">Role Name <span class="text-danger">*</span></label>
                <input type="text" name="name" id="editRoleName" class="form-control" required style="height: 48px; border-radius: 8px;">
            </div>
        </div>
        <div class="p-4 bg-white border-top d-flex gap-2">
            <button type="button" class="btn btn-light fw-bold flex-fill" data-bs-dismiss="offcanvas">Cancel</button>
            <button type="submit" class="btn btn-primary fw-bold flex-fill">Update Role</button>
        </div>
    </form>
</div>

<style>
    .bg-soft-info { background: rgba(13, 202, 240, 0.08) !important; color: #0dcaf0; }
    .bg-soft-danger { background: rgba(239, 68, 68, 0.08) !important; color: #ef4444; }
    .bg-soft-success { background: rgba(34, 197, 94, 0.08) !important; color: #22c55e; }
    .form-control:focus, .form-select:focus { 
        border: 1.5px solid #3858f9 !important; 
        box-shadow: 0 0 0 0.2rem rgba(56, 88, 249, 0.1) !important; 
    }
    .table thead th { border: none !important; }
    .avatar-md { width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; text-decoration: none; }

    .wghrm-search-container:focus-within {
        border-color: #3858f9 !important;
        background: #fff !important;
        box-shadow: 0 0 0 4px rgba(56, 88, 249, 0.1) !important;
    }
</style>

<script>
    function filterRoles() {
        const input = document.getElementById('roleSearch');
        const filter = input.value.toLowerCase();
        const rows = document.querySelectorAll('.role-row');

        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    }

    function editRole(data) {
        document.getElementById('editRoleForm').action = "/master/role/" + data.id;
        document.getElementById('editRoleName').value = data.name;
        new bootstrap.Offcanvas(document.getElementById('editRoleModal')).show();
    }
</script>

@endsection
