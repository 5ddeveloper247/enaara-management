<div class="row align-items-center p-4">
    <div class="col-md-6">
        <h5 class="mb-0">Manage Work Types</h5>
    </div>
    <div class="col-md-6 text-end">
        <button type="button" class="btn btn-outline-secondary me-2" id="exportBtn">
            <i class="bi bi-download me-1"></i>Export
        </button>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" id="filterDropdownBtn">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
            <ul class="dropdown-menu dropdown-menu-end p-3" style="min-width: 300px;">
                <li>
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Organization</label>
                        <select class="form-select form-select-sm" id="filterOrganization">
                            <option value="">All Organizations</option>
                            @foreach($organizations ?? [] as $org)
                                <option value="{{ $org->id }}">{{ $org->name }}</option>
                            @endforeach
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
