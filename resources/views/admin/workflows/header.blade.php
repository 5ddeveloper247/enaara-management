<!-- Workflows Header -->
<div class="row align-items-center p-4">
    <div class="col-md-6">
        <h5 class="mb-0">Workflow Management</h5>
        <small class="text-muted">Manage approval chains and request routing</small>
    </div>
    <div class="col-md-6 text-end">
        <button type="button" class="btn btn-outline-secondary me-2" id="exportBtn">
            <i class="bi bi-download me-1"></i>Export
        </button>
        <button type="button" class="btn btn-primary bg-main border-0" data-bs-toggle="offcanvas" data-bs-target="#createWorkflowCanvas" id="createWorkflowBtn">
            <i class="bi bi-plus-circle me-1"></i>Create Workflow
        </button>
    </div>
</div>

<!-- Filter Bar -->
<div class="row g-3 px-4 pb-4">
    <div class="col-md-3">
        <label class="form-label small fw-semibold text-muted mb-2">Request Type</label>
        <select class="form-select form-select-sm" id="filterRequestType">
            <option value="">All Types</option>
            <option value="leave">Leave</option>
            <option value="overtime">Overtime</option>
            <option value="regularization">Regularization</option>
            <option value="shift">Shift</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label small fw-semibold text-muted mb-2">Status</label>
        <select class="form-select form-select-sm" id="filterStatus">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label small fw-semibold text-muted mb-2">Organization</label>
        <select class="form-select form-select-sm" id="filterOrganization">
            <option value="">All Organizations</option>
            <option value="enaara">Enaara Developers</option>
            <option value="msr-rawalpindi">Madison Square Mall Rawalpindi</option>
            <option value="msr-lahore">Madison Square Mall Lahore</option>
            <option value="royal-swiss">Royal Swiss Lahore</option>
            <option value="global">Global</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label small fw-semibold text-muted mb-2">Branch</label>
        <select class="form-select form-select-sm" id="filterBranch">
            <option value="">All Branches</option>
            <option value="rawalpindi">Rawalpindi</option>
            <option value="lahore">Lahore</option>
            <option value="karachi">Karachi</option>
        </select>
    </div>
    <div class="col-12">
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="clearFiltersBtn">
                <i class="bi bi-x-circle me-1"></i>Clear Filters
            </button>
            <button type="button" class="btn btn-sm btn-primary bg-main border-0" id="applyFiltersBtn">
                <i class="bi bi-funnel me-1"></i>Apply Filters
            </button>
        </div>
    </div>
</div>

