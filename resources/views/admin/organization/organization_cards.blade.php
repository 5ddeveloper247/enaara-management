<div class="row">
    <!-- Organization Grid -->
    <div class="col-lg-9">
        <div class="row g-4" id="organizationsGrid">
            @forelse($organizations as $org)
                <div class="col-md-6 col-lg-4">
                    <div class="card organization-card border-1 rounded-3 h-100" data-org-status="{{ $org->is_active ? 'active' : 'inactive' }}">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="me-3 bg-main text-white rounded-2 d-flex align-items-center justify-content-center fw-bold" style="width: 45px; height: 45px; font-size: 1.1rem;">
                                        {{ strtoupper(substr($org->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-semibold small">{{ $org->name }}</h6>
                                        <small class="text-muted small">{{ $org->code ?? '—' }}</small>
                                    </div>
                                </div>
                                @if($org->is_active)
                                    <span class="badge bg-success" style="font-size: 10px; padding: 4px 6px;">Active</span>
                                @else
                                    <span class="badge bg-secondary" style="font-size: 10px; padding: 4px 6px;">Inactive</span>
                                @endif
                            </div>
                            @if($org->email)
                                <div class="mb-2">
                                    <i class="bi bi-envelope me-1 text-main small"></i>
                                    <small class="small">{{ $org->email }}</small>
                                </div>
                            @endif
                            @if($org->address)
                                <div class="mb-2">
                                    <i class="bi bi-geo-alt me-1 text-main small"></i>
                                    <small class="text-muted small">{{ Str::limit($org->address, 40) }}</small>
                                </div>
                            @endif
                            @if($org->parent)
                                <div class="mb-2">
                                    <i class="bi bi-building me-1 text-main small"></i>
                                    <small class="text-muted small">Parent: {{ $org->parent->name }}</small>
                                </div>
                            @endif
                            <div class="mt-3 pt-3 border-top">
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary w-100 view-organization-btn"
                                        data-bs-toggle="offcanvas"
                                        data-bs-target="#organizationDetailCanvas"
                                        data-org-id="{{ $org->id }}"
                                        data-org-name="{{ e($org->name) }}"
                                        data-org-code="{{ e($org->code ?? '') }}"
                                        data-org-email="{{ e($org->email ?? '') }}"
                                        data-org-address="{{ e($org->address ?? '') }}"
                                        data-org-description="{{ e($org->description ?? '') }}"
                                        data-org-tax-no="{{ e($org->tax_no ?? '') }}"
                                        data-org-active="{{ $org->is_active ? '1' : '0' }}"
                                        data-org-parent="{{ $org->parent ? e($org->parent->name) : '' }}">
                                    <i class="bi bi-eye me-1"></i>View Details
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <p class="text-center text-muted">No organizations found.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Filter Sidebar -->
    <div class="col-lg-3">
        <div class="card border-0 rounded-4">
            <div class="card-body p-4">
                <h6 class="fw-semibold mb-3">
                    <i class="bi bi-funnel me-2"></i>Filters
                </h6>

                <!-- Status Filter -->
                <div class="mb-4">
                    <label class="form-label small fw-semibold text-muted mb-2">Status</label>
                    <div class="bg-transparent">
                        <label class="list-group-item list-group-item-action border-0 px-0 py-2 cursor-pointer">
                            <input class="form-check-input me-2 filter-status" type="radio" name="filterStatus" value="all" id="filterStatusAll" checked>
                            All
                        </label>
                        <label class="list-group-item list-group-item-action border-0 px-0 py-2 cursor-pointer">
                            <input class="form-check-input me-2 filter-status" type="radio" name="filterStatus" value="active" id="filterStatusActive">
                            Active
                        </label>
                        <label class="list-group-item list-group-item-action border-0 px-0 py-2 cursor-pointer">
                            <input class="form-check-input me-2 filter-status" type="radio" name="filterStatus" value="inactive" id="filterStatusInactive">
                            Inactive
                        </label>
                    </div>
                </div>

                <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="clearFiltersBtn">
                    <i class="bi bi-x-circle me-1"></i>Clear Filters
                </button>
            </div>
        </div>
    </div>
</div>
