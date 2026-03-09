<div class="row">
    <div class="col-lg-9">
        <div class="row g-4" id="departmentsGrid">
            @forelse($departments ?? [] as $dept)
                <div class="col-md-6 col-lg-4">
                    <div class="card department-card border-1 rounded-3 h-100" data-department-status="{{ $dept->is_active ? 'active' : 'inactive' }}">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-1 fw-semibold">{{ $dept->name }}</h6>
                                    <small class="text-muted small">{{ $dept->code ?? '—' }}</small>
                                </div>
                                @if($dept->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </div>
                            @if($dept->organization)
                                <div class="mb-2">
                                    <i class="bi bi-building me-1 text-main small"></i>
                                    <small class="text-muted small">{{ $dept->organization->name }}</small>
                                </div>
                            @endif
                            @if($dept->sbu)
                                <div class="mb-2">
                                    <i class="bi bi-geo-alt me-1 text-main small"></i>
                                    <small class="text-muted small">{{ $dept->sbu->name }}</small>
                                </div>
                            @endif
                            @if($dept->parent)
                                <div class="mb-2">
                                    <i class="bi bi-diagram-3 me-1 text-main small"></i>
                                    <small class="text-muted small">Parent: {{ $dept->parent->name }}</small>
                                </div>
                            @endif
                            <div class="mt-3 pt-3 border-top d-flex gap-1">
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary flex-grow-1 edit-department-btn"
                                        data-bs-toggle="offcanvas"
                                        data-bs-target="#departmentEditCanvas"
                                        data-department-id="{{ $dept->id }}">
                                    <i class="bi bi-pencil me-1"></i>Edit
                                </button>
                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary view-department-btn"
                                        data-bs-toggle="offcanvas"
                                        data-bs-target="#departmentDetailCanvas"
                                        data-department-id="{{ $dept->id }}"
                                        data-department-name="{{ e($dept->name) }}"
                                        data-department-code="{{ e($dept->code ?? '') }}"
                                        data-organization-name="{{ $dept->organization ? e($dept->organization->name) : '' }}"
                                        data-sbu-name="{{ $dept->sbu ? e($dept->sbu->name) : '' }}"
                                        data-parent-name="{{ $dept->parent ? e($dept->parent->name) : '' }}"
                                        data-description="{{ e($dept->description ?? '') }}"
                                        data-department-status="{{ $dept->is_active ? 'Active' : 'Inactive' }}">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <p class="text-center text-muted">No departments found.</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="col-lg-3">
        <div class="card border-0 rounded-4">
            <div class="card-body p-4">
                <h6 class="fw-semibold mb-3">
                    <i class="bi bi-funnel me-2"></i>Filters
                </h6>
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
