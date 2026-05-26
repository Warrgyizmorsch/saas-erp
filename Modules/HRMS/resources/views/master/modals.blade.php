<!-- ADD DEPARTMENT MODAL -->
<div class="modal fade" id="addDeptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-white border-bottom p-4">
                <h6 class="modal-title fw-bold text-dark mb-0">Add Department</h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('master.department.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4 bg-light">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase mb-2">Department Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control border-0 shadow-none fw-bold" placeholder="e.g. Finance" required style="height: 48px; border-radius: 8px;">
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold text-muted small text-uppercase mb-2">Short Name (Optional)</label>
                        <input type="text" name="short_name" class="form-control border-0 shadow-none fw-bold" placeholder="e.g. FIN" style="height: 48px; border-radius: 8px;">
                    </div>
                </div>
                <div class="modal-footer bg-white border-top p-4">
                    <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Save Department</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- EDIT DEPARTMENT MODAL -->
<div class="modal fade" id="editDeptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-white border-bottom p-4">
                <h6 class="modal-title fw-bold text-dark mb-0">Edit Department</h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editDeptForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4 bg-light">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase mb-2">Department Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editDeptName" class="form-control border-0 shadow-none fw-bold" required style="height: 48px; border-radius: 8px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase mb-2">Short Name (Optional)</label>
                        <input type="text" name="short_name" id="editDeptShortName" class="form-control border-0 shadow-none fw-bold" style="height: 48px; border-radius: 8px;">
                    </div>
                    </div>
                </div>
                </div>
                <div class="modal-footer bg-white border-top p-4">
                    <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ADD DESIGNATION MODAL -->
<div class="modal fade" id="addDesgModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-white border-bottom p-4">
                <h6 class="modal-title fw-bold text-dark mb-0">Add Designation</h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('master.designation.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4 bg-light">
                    <div class="mb-0">
                        <label class="form-label fw-bold text-muted small text-uppercase mb-2">Designation Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control border-0 shadow-none fw-bold" placeholder="e.g. Software Engineer" required style="height: 48px; border-radius: 8px;">
                    </div>
                </div>
                <div class="modal-footer bg-white border-top p-4">
                    <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Save Designation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- EDIT DESIGNATION MODAL -->
<div class="modal fade" id="editDesgModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-white border-bottom p-4">
                <h6 class="modal-title fw-bold text-dark mb-0">Edit Designation</h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editDesgForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4 bg-light">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase mb-2">Designation Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editDesgName" class="form-control border-0 shadow-none fw-bold" required style="height: 48px; border-radius: 8px;">
                    </div>
                    </div>
                </div>
                </div>
                <div class="modal-footer bg-white border-top p-4">
                    <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ADD ROLE MODAL -->
<div class="modal fade" id="addRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-white border-bottom p-4">
                <h6 class="modal-title fw-bold text-dark mb-0">Add Role</h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('master.role.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4 bg-light">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase mb-2">Role Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control border-0 shadow-none fw-bold" placeholder="e.g. HR Manager" required style="height: 48px; border-radius: 8px;">
                    </div>
                    <div class="alert alert-info border-0 rounded" style="font-size: 13px;">
                        <i class="feather-info me-2"></i> Slug will be automatically generated (e.g. hr_manager).
                    </div>
                </div>
                <div class="modal-footer bg-white border-top p-4">
                    <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Save Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- EDIT ROLE MODAL -->
<div class="modal fade" id="editRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-white border-bottom p-4">
                <h6 class="modal-title fw-bold text-dark mb-0">Edit Role</h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editRoleForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4 bg-light">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase mb-2">Role Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editRoleName" class="form-control border-0 shadow-none fw-bold" required style="height: 48px; border-radius: 8px;">
                    </div>
                    <div class="alert alert-info border-0 rounded" style="font-size: 13px;">
                        <i class="feather-info me-2"></i> Slug will be automatically re-generated on save.
                    </div>
                    </div>
                </div>
                </div>
                <div class="modal-footer bg-white border-top p-4">
                    <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
