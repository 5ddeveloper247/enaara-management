<div class="row">
    <div class="col-lg-9">
        <div class="row g-4" id="roleLevelsGrid">
            @forelse($roleLevels ?? [] as $roleLevel)
            <div class="col-md-6 col-lg-4">
                <div class="card sbu-card border-1 rounded-3 h-100" data-rolelevel-status="{{ $roleLevel->is_active ? 'active' : 'inactive' }}">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3 bg-main text-white rounded-2 d-flex align-items-center justify-content-center fw-bold" style="width: 45px; height: 45px; font-size: 1.1rem;">
                                    {{ strtoupper(substr($roleLevel->name, 0, 2)) }}
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-semibold small">{{ $roleLevel->name }}</h6>
                                    <small class="text-muted small">Level {{ $roleLevel->level }}</small>
                                </div>
                            </div>
                            @if($roleLevel->is_active)
                            <span class="badge bg-success" style="font-size: 10px; padding: 4px 6px;">Active</span>
                            @else
                            <span class="badge bg-secondary" style="font-size: 10px; padding: 4px 6px;">Inactive</span>
                            @endif
                        </div>
                        @if($roleLevel->description)
                        <div class="mb-2">
                            <i class="bi bi-file-text me-1 text-main small"></i>
                            <small class="text-muted small">{{ Str::limit($roleLevel->description, 60) }}</small>
                        </div>
                        @endif
                        <div class="mb-2">
                            <i class="bi bi-sort-numeric-up me-1 text-main small"></i>
                            <small class="text-muted small">Priority Level: {{ $roleLevel->level }}</small>
                        </div>
                        <div class="mt-3 pt-3 border-top d-flex gap-1">

                            <!-- Edit Button -->
                            <button type="button"
                                class="btn btn-sm btn-outline-primary flex-grow-1 edit-rolelevel-btn"
                                data-bs-toggle="offcanvas"
                                data-bs-target="#editRoleLevelCanvas"
                                data-id="{{ $roleLevel->id }}"
                                data-edit-url="{{ route('admin.role-levels.edit', $roleLevel->id) }}"
                                data-update-url="{{ route('admin.role-levels.update', $roleLevel->id) }}">
                                <i class="bi bi-pencil me-1"></i>Edit
                            </button>

                            <!-- Delete Button -->
                            <button type="button" class="btn btn-sm btn-outline-danger delete-rolelevel-btn" data-delete-url="{{ route('admin.role-levels.destroy', $roleLevel->id) }}">
                                <i class="bi bi-trash"></i>
                            </button>

                            <!-- View Button -->
                            <button type="button"
                                class="btn btn-sm btn-outline-secondary view-rolelevel-btn"
                                data-bs-toggle="offcanvas"
                                data-bs-target="#roleLevelDetailCanvas"
                                data-rolelevel-id="{{ $roleLevel->id }}"
                                data-rolelevel-name="{{ e($roleLevel->name) }}"
                                data-rolelevel-description="{{ e($roleLevel->description ?? '') }}"
                                data-rolelevel-level="{{ $roleLevel->level }}"
                                data-rolelevel-active="{{ $roleLevel->is_active ? '1' : '0' }}">
                                <i class="bi bi-eye"></i>
                            </button>

                            
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <p class="text-center text-muted">No Role Levels found.</p>
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
