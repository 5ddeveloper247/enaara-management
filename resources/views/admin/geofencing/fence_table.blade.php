<div class="row">
    <div class="col-md-12">
        <div class="card border-0 rounded-4 mb-4">
            <div class="card-body p-0">
                <div class="d-flex justify-content-between align-items-center p-4 border-bottom">
                    <h6 class="mb-0 fw-semibold">Active Geofences</h6>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="exportFencesBtn">
                            <i class="bi bi-download me-1"></i>Export
                        </button>
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle"
                                data-bs-toggle="dropdown" aria-expanded="false" id="filterDropdownBtn">
                                <i class="bi bi-funnel me-1"></i>Filter
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end p-3" style="min-width: 300px;">
                                <li>
                                    <div class="mb-3">
                                        <label class="form-label small text-muted mb-1">Fence Type</label>
                                        <select class="form-select form-select-sm" id="filterType">
                                            <option value="">All Types</option>
                                            <option value="hard-lock">Hard Lock</option>
                                            <option value="soft-lock">Soft Lock</option>
                                        </select>
                                    </div>
                                </li>
                                <li>
                                    <div class="mb-3">
                                        <label class="form-label small text-muted mb-1">Status</label>
                                        <select class="form-select form-select-sm" id="filterStatus">
                                            <option value="">All Status</option>
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
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
                <table id="fencesTable" class="display nowrap table table-striped" style="width:100%">
                    <thead class="bg-main">
                        <tr>
                            <th class="dt-control"></th>
                            <th>Site Name</th>
                            <th>Address</th>
                            <th>Radius</th>
                            <th>Type</th>
                            <th>Assigned Groups</th>
                            <th>Inside/Outside</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-transparent" id="fencesTableBody">
                        <!-- Sample data will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>