<!-- Overtime Tracker Header -->
<div class="row align-items-center p-4">
    <div class="col-md-6">
        <h5 class="mb-0">Overtime Tracker</h5>
        <small class="text-muted">Monitor and manage employee overtime requests</small>
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
        <div class="input-group input-group-sm rounded-2 border-0">
            <input type="date" class="form-control" id="filterDateFrom" value="{{ date('Y-m-d', strtotime('-7 days')) }}">
            <small class="input-group-text">to</small>
            <input type="date" class="form-control" id="filterDateTo" value="{{ date('Y-m-d') }}">
        </div>
    </div>
    <div class="col-md-3">
        <label class="form-label small fw-semibold text-muted mb-2">Organization</label>
        <select class="form-select form-select-sm" id="filterOrganization">
            <option value="">All Organizations</option>
            <option value="enaara">Enaara Developers</option>
            <option value="msr-rawalpindi">Madison Square Mall Rawalpindi</option>
            <option value="msr-lahore">Madison Square Mall Lahore</option>
            <option value="royal-swiss">Royal Swiss Lahore</option>
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
    <div class="col-md-3">
        <label class="form-label small fw-semibold text-muted mb-2">Floor</label>
        <select class="form-select form-select-sm" id="filterFloor">
            <option value="">All Floors</option>
            <option value="ground">Ground Floor</option>
            <option value="1">Floor 1</option>
            <option value="2">Floor 2</option>
            <option value="3">Floor 3</option>
            <option value="4">Floor 4</option>
            <option value="5">Floor 5</option>
            <option value="6">Floor 6</option>
            <option value="7">Floor 7</option>
            <option value="8">Floor 8</option>
            <option value="9" data-restricted="true">Floor 9 (Corporate/HR Zone)</option>
            <option value="10">Floor 10</option>
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

