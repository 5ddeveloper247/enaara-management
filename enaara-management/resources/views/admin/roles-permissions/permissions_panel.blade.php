<!-- Permission Matrix Panel -->
<div class="p-4">
    <!-- Loading State -->
    <div id="permissionsLoading" class="text-center py-5" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3 text-muted">Loading permissions...</p>
    </div>

    <!-- Empty State -->
    <div id="permissionsEmpty" class="text-center py-5">
        <i class="bi bi-info-circle text-muted" style="font-size: 3rem;"></i>
        <p class="mt-3 text-muted">Select a role from the tree to view and manage permissions</p>
    </div>

    <!-- Permissions Content -->
    <div id="permissionsContent" style="display: none;">
        <!-- Role Header -->
        <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
            <div>
                <h5 class="mb-1" id="permissionRoleName">-</h5>
                <small class="text-muted" id="permissionRoleLevel">-</small>
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-outline-primary me-2" id="savePermissionsBtn">
                    <i class="bi bi-save me-1"></i>Save Changes
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="resetPermissionsBtn">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                </button>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs mb-4" id="permissionsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="permissions-tab" data-bs-toggle="tab" data-bs-target="#permissions" type="button" role="tab">
                    <i class="bi bi-shield-check me-1"></i>Permissions
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="data-scope-tab" data-bs-toggle="tab" data-bs-target="#data-scope" type="button" role="tab">
                    <i class="bi bi-eye me-1"></i>Data Scope
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab">
                    <i class="bi bi-people me-1"></i>Assigned Users
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="permissionsTabContent">
            <!-- Permissions Tab -->
            <div class="tab-pane fade show active" id="permissions" role="tabpanel">
                <div class="accordion" id="permissionsAccordion">
                    <!-- Permissions will be dynamically generated here -->
                </div>
            </div>

            <!-- Data Scope Tab -->
            <div class="tab-pane fade" id="data-scope" role="tabpanel">
                <div class="card border-0 bg-light">
                    <div class="card-body">
                        <h6 class="mb-3 fw-semibold">
                            <i class="bi bi-diagram-3 me-2"></i>Visibility Boundary
                        </h6>
                        <p class="text-muted small mb-4">Define the data visibility scope for this role within the organizational hierarchy.</p>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Organization Scope</label>
                                <select class="form-select form-select-sm" id="dataScopeOrganization">
                                    <option value="all">All Organizations</option>
                                    <option value="specific">Specific Organizations</option>
                                    <option value="own">Own Organization Only</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Department/Branch Scope</label>
                                <select class="form-select form-select-sm" id="dataScopeDepartment">
                                    <option value="all">All Departments/Branches</option>
                                    <option value="specific">Specific Departments/Branches</option>
                                    <option value="own">Own Department/Branch Only</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Floor/Team Scope</label>
                                <select class="form-select form-select-sm" id="dataScopeFloor">
                                    <option value="all">All Floors/Teams</option>
                                    <option value="specific">Specific Floors/Teams</option>
                                    <option value="own">Own Floor/Team Only</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Employee Scope</label>
                                <select class="form-select form-select-sm" id="dataScopeEmployee">
                                    <option value="all">All Employees</option>
                                    <option value="direct">Direct Reports Only</option>
                                    <option value="team">Team Members Only</option>
                                </select>
                            </div>
                        </div>

                        <!-- Specific Selections (shown when "Specific" is selected) -->
                        <div id="specificSelections" class="mt-4" style="display: none;">
                            <h6 class="mb-3 fw-semibold small">Specific Selections</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small">Organizations</label>
                                    <div class="border rounded p-2" style="max-height: 150px; overflow-y: auto;">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="org-enaara" value="enaara">
                                            <label class="form-check-label small" for="org-enaara">Enaara Developers</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="org-msr-rawalpindi" value="msr-rawalpindi">
                                            <label class="form-check-label small" for="org-msr-rawalpindi">Madison Square Mall Rawalpindi</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="org-msr-lahore" value="msr-lahore">
                                            <label class="form-check-label small" for="org-msr-lahore">Madison Square Mall Lahore</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="org-royal-swiss" value="royal-swiss">
                                            <label class="form-check-label small" for="org-royal-swiss">Royal Swiss Lahore</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Departments/Branches</label>
                                    <div class="border rounded p-2" style="max-height: 150px; overflow-y: auto;">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="dept-it" value="it">
                                            <label class="form-check-label small" for="dept-it">IT</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="dept-hr" value="hr">
                                            <label class="form-check-label small" for="dept-hr">HR</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="dept-sales" value="sales">
                                            <label class="form-check-label small" for="dept-sales">Sales</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="dept-operations" value="operations">
                                            <label class="form-check-label small" for="dept-operations">Operations</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users Tab -->
            <div class="tab-pane fade" id="users" role="tabpanel">
                <div class="card border-0 bg-light">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="mb-0 fw-semibold">
                                <i class="bi bi-people me-2"></i>Assigned Users
                            </h6>
                            <div class="input-group" style="max-width: 300px;">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control form-control-sm" id="usersSearch" placeholder="Search users...">
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm" id="usersTable">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Email</th>
                                        <th>Department</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="usersTableBody">
                                    <!-- Users will be dynamically populated here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

