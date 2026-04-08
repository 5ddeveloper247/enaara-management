<!-- Audit Trails Header -->
<div class="row align-items-center p-4">
    <div class="col-md-6">
        <h5 class="mb-0">Audit Trails</h5>
        <small class="text-muted">System activity log and change tracking</small>
    </div>
    <div class="col-md-6 text-end">
        <button type="button" class="btn btn-outline-secondary me-2" id="exportBtn">
            <i class="bi bi-download me-1"></i>Export
        </button>
    </div>
</div>

<!-- Hierarchical Filter Bar -->
<div class="row g-3 px-4 pb-4">
    <div class="col-md-3">
        <label class="form-label small fw-semibold text-muted mb-2">Date Range</label>
        <div class="input-group input-group-sm rounded-2">
            <input type="date" class="form-control" id="filterDateFrom" value="{{ date('Y-m-d', strtotime('-30 days')) }}">
            <small class="input-group-text">to</small>
            <input type="date" class="form-control" id="filterDateTo" value="{{ date('Y-m-d') }}">
        </div>
    </div>
    <div class="col-md-3">
        <label class="form-label small fw-semibold text-muted mb-2">Organization</label>
        <select class="form-select form-select-sm" id="filterOrganization">
            <option value="">All Organizations</option>
            @isset($organizations)
                @foreach ($organizations as $organization)
                    <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                @endforeach
            @endisset
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label small fw-semibold text-muted mb-2">Action Category</label>
        <select class="form-select form-select-sm" id="filterCategory">
            <option value="">All Categories</option>
            <option value="leave">Leave</option>
            <option value="geofence">Geofence</option>
            <option value="shift">Shift</option>
            <option value="security">Security</option>
            <option value="employee">Employee</option>
            <option value="system">System</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label small fw-semibold text-muted mb-2">Severity</label>
        <select class="form-select form-select-sm" id="filterSeverity">
            <option value="">All Severities</option>
            <option value="critical">Critical</option>
            <option value="warning">Warning</option>
            <option value="info">Info</option>
            <option value="success">Success</option>
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

