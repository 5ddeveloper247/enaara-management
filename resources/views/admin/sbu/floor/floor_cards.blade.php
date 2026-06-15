<div class="row">
    <div class="col-lg-9">
        <div class="row g-4" id="sbuFloorsGrid">
            @forelse($sbuFloors ?? [] as $floor)
            <div class="col-md-6 col-lg-4">
                <div class="card sbu-floor-card border-1 rounded-3 h-100" data-floor-status="{{ $floor->is_active ? 'active' : 'inactive' }}">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center">
                            <div class="me-3 bg-main text-white rounded-2 d-flex align-items-center justify-content-center fw-bold floor-number-badge">
    {{ $floor->floor_number !== null ? $floor->floor_number : 'F' }}
</div>
                                <div>
                                    <h6 class="mb-0 fw-semibold small">{{ $floor->name }}</h6>
                                    <small class="text-muted small">{{ ucfirst($floor->floor_type) }}</small>
                                </div>
                            </div>
                            @if($floor->is_active)
                            <span class="badge bg-success" style="font-size: 10px; padding: 4px 6px;">Active</span>
                            @else
                            <span class="badge bg-secondary" style="font-size: 10px; padding: 4px 6px;">Inactive</span>
                            @endif
                        </div>
                        @if($floor->sbu)
                        <div class="mb-2">
                            <i class="bi bi-building me-1 text-main small"></i>
                            <small class="text-muted small">{{ $floor->sbu->name }}</small>
                        </div>
                        @endif
                        @if($floor->sbu && $floor->sbu->organization)
                        <div class="mb-2">
                            <i class="bi bi-diagram-3 me-1 text-main small"></i>
                            <small class="text-muted small">{{ $floor->sbu->organization->name }}</small>
                        </div>
                        @endif
                        @if($floor->is_restricted)
                        <div class="mb-2">
                            <span class="badge bg-warning text-dark">Restricted</span>
                        </div>
                        @endif
                            <!-- <a href="{{ route('admin.sbu.floor.show', $floor->id) }}" class="btn btn-sm btn-outline-secondary me-1">Open</a> -->
                            <div class="mt-3 pt-3 border-top d-flex gap-1">

                                @if(validatePermissions('admin/sbu-floor/edit'))
                                <!-- Edit Button -->
                                <button type="button"
                                    class="btn btn-sm btn-outline-primary flex-grow-1 edit-floor-btn"
                                    data-bs-toggle="offcanvas"
                                    data-bs-target="#editSbuFloorCanvas"
                                    data-id="{{ $floor->id }}"
                                    data-edit-url="{{ route('admin.sbu.floor.edit', $floor->id) }}"
                                    data-update-url="{{ route('admin.sbu.floor.update', $floor->id) }}"
                                    data-delete-url="{{ route('admin.sbu.floor.destroy', $floor->id) }}">
                                    <i class="bi bi-pencil me-1"></i>Edit
                                </button>
                                @endif

                                @if(validatePermissions('admin/sbu-floor/delete'))
                                <!-- Delete Button -->
                                <button type="button" class="btn btn-sm btn-outline-danger flex-grow-1 delete-floor-btn" data-delete-url="{{ route('admin.sbu.floor.destroy', $floor->id) }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                                @endif

                                <!-- View Button -->
                                <button type="button"
                                    class="btn btn-sm btn-outline-secondary view-floor-btn"
                                    data-bs-toggle="offcanvas"
                                    data-bs-target="#sbuFloorDetailCanvas"
                                    data-floor-id="{{ $floor->id }}"
                                    data-detail-url="{{ route('admin.sbu.floor.detail-json', $floor->id) }}">
                                    <i class="bi bi-eye"></i>
                                </button>


                            </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <p class="text-center text-muted">No SBU floors found.</p>
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
