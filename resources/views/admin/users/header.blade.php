<!-- Users Module Header -->
<div class="row align-items-center p-4">
            <div class="col-md-6">
                <h5 class="mb-0">Manage Users</h5>
            </div>
            <div class="col-md-6 text-end">
                <button type="button" class="btn btn-outline-secondary me-2" id="exportBtn">
                    <i class="bi bi-download me-1"></i>Export
                </button>

                <div class="btn-group">
                    <button type="button" class="btn btn-outline-secondary dropdown-toggle"
                        data-bs-toggle="dropdown" aria-expanded="false" id="filterDropdownBtn">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end p-3" style="min-width: 300px;">
                        <li>
                            <div class="mb-3">
                                <label class="form-label small text-muted mb-1">Department</label>
                                <select class="form-select form-select-sm" id="filterDepartment">
                                    <option value="">All Departments</option>
                                    <option value="Sales">Sales</option>
                                    <option value="IT">IT</option>
                                    <option value="HR">HR</option>
                                    <option value="Operations">Operations</option>
                                    <option value="Finance">Finance</option>
                                </select>
                            </div>
                        </li>
                        <li>
                            <div class="mb-3">
                                <label class="form-label small text-muted mb-1">Role</label>
                                <select class="form-select form-select-sm" id="filterRole">
                                    <option value="">All Roles</option>
                                    <option value="Admin">Admin</option>
                                    <option value="Manager">Manager</option>
                                    <option value="Agent">Agent</option>
                                    <option value="Employee">Employee</option>
                                </select>
                            </div>
                        </li>
                        <li>
                            <div class="mb-3">
                                <label class="form-label small text-muted mb-1">Status</label>
                                <select class="form-select form-select-sm" id="filterStatus">
                                    <option value="">All Status</option>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary flex-fill"
                                    id="clearFiltersBtn">
                                    <i class="bi bi-x-circle me-1"></i>Clear
                                </button>
                                <button type="button" class="btn btn-sm btn-primary bg-main border-0 flex-fill"
                                    id="applyFiltersBtn">
                                    <i class="bi bi-check-lg me-1"></i>Apply
                                </button>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

