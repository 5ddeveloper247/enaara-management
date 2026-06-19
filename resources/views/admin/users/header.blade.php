<div class="row align-items-center p-4">
    <div class="col-md-6">
        <h5 class="mb-0">Manage Users</h5>
    </div>
    <div class="col-md-6 text-end d-flex align-items-center justify-content-end gap-2">
        @if(validatePermissions('admin/users/store'))
        <button type="button" class="btn btn-primary bg-main border-0 px-3" id="addUserBtn"
            data-bs-toggle="offcanvas" data-bs-target="#userCanvas">
            <i class="bi bi-person-plus me-1"></i>Add User
        </button>
        @endif
        <button type="button" class="btn btn-outline-secondary" id="exportBtn">
            <i class="bi bi-download me-1"></i>Export
        </button>

        <div class="btn-group">
            <button type="button" class="btn btn-outline-secondary dropdown-toggle"
                data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
            <ul class="dropdown-menu dropdown-menu-end p-3" style="min-width:300px;">
                <li>
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Role</label>
                        <select class="form-select form-select-sm" id="filterRole">
                            <option value="">All Roles</option>
                        </select>
                    </div>
                </li>
                <li>
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Department</label>
                        <select class="form-select form-select-sm" id="filterDepartment">
                            <option value="">All Departments</option>
                        </select>
                    </div>
                </li>
                <li>
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Status</label>
                        <select class="form-select form-select-sm" id="filterStatus">
                            <option value="">All Status</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary flex-fill" id="clearFiltersBtn">
                            <i class="bi bi-x-circle me-1"></i>Clear
                        </button>
                        <button type="button" class="btn btn-sm btn-primary bg-main border-0 flex-fill" id="applyFiltersBtn">
                            <i class="bi bi-check-lg me-1"></i>Apply
                        </button>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>
