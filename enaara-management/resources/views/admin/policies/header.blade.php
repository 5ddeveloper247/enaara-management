<!-- Policy Management Header -->
<div class="row align-items-center p-4">
    <div class="col-md-6">
        <h5 class="mb-0">Policy Management</h5>
        <small class="text-muted">Manage organization-wide rules and documentation</small>
    </div>
    <div class="col-md-6 text-end">
        <button type="button" class="btn btn-outline-secondary me-2" id="exportBtn">
            <i class="bi bi-download me-1"></i>Export
        </button>
        <button type="button" class="btn btn-primary bg-main border-0" data-bs-toggle="modal" data-bs-target="#createPolicyModal" id="createPolicyBtn">
            <i class="bi bi-plus-circle me-1"></i>Create Policy
        </button>
    </div>
</div>

<!-- Filter Bar -->
<div class="row g-3 px-4 pb-4">
    <div class="col-md-3">
        <label class="form-label small fw-semibold text-muted mb-2">Policy Category</label>
        <select class="form-select form-select-sm" id="filterCategory">
            <option value="">All Categories</option>
            <option value="leave">Leave Policy</option>
            <option value="attendance">Attendance Grace Period</option>
            <option value="geofence">Geofencing Rules</option>
            <option value="shift">Shift Rota Protocols</option>
            <option value="security">Security Policy</option>
            <option value="hr">HR Policy</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label small fw-semibold text-muted mb-2">Status</label>
        <select class="form-select form-select-sm" id="filterStatus">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="draft">Draft</option>
            <option value="archived">Archived</option>
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
        <label class="form-label small fw-semibold text-muted mb-2">Applicable To</label>
        <select class="form-select form-select-sm" id="filterApplicableTo">
            <option value="">All Types</option>
            <option value="global">Global</option>
            <option value="organization">Organization</option>
            <option value="branch">Branch</option>
            <option value="floor">Floor</option>
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

