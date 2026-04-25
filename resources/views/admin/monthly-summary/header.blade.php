<!-- Monthly Summary Header -->
<div class="row align-items-center p-4">
    <div class="col-md-6">
        <h5 class="mb-0">Monthly Summary Report</h5>
        <small class="text-muted">Pre-Payroll Attendance & Leave Report</small>
    </div>
    <div class="col-md-6 text-end">
        <button type="button" class="btn btn-outline-secondary me-2" id="exportExcelBtn">
            <i class="bi bi-file-earmark-excel me-1"></i>Export to Excel
        </button>
        <button type="button" class="btn btn-outline-secondary me-2" id="exportPdfBtn">
            <i class="bi bi-file-earmark-pdf me-1"></i>Download PDF
        </button>
    </div>
</div>

<!-- Hierarchical Filter Bar -->
<div class="row g-3 px-4 pb-3">
    <div class="col-md-3">
        <label class="form-label small fw-semibold text-muted mb-2">Select Month</label>
        <input type="month" class="form-control form-control-sm" id="filterMonth" value="{{ $month ?? date('Y-m') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label small fw-semibold text-muted mb-2">SBU</label>
        <select class="form-select form-select-sm" id="filterSBU">
            <option value="">All SBUs</option>
            @foreach ($sbus as $sbu)
                <option value="{{ $sbu->id }}" {{ (string) request('sbu_id') === (string) $sbu->id ? 'selected' : '' }}>
                    {{ $sbu->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label small fw-semibold text-muted mb-2">Departments</label>
        <select class="form-select form-select-sm" id="filterBranch">
            <option value="">All Departments</option>
            @foreach ($departments as $dpt)
                <option value="{{ $dpt->id }}" data-sbu-id="{{ $dpt->sbu_id }}" {{ (string) request('department_id') === (string) $dpt->id ? 'selected' : '' }}>
                    {{ $dpt->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label small fw-semibold text-muted mb-2">Floor</label>
        <select class="form-select form-select-sm" id="filterFloor">
            <option value="">All Floors</option>
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

