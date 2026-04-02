<div class="row">
    <div class="col-lg-9">
        <div class="row g-4" id="sbusGrid">
            @forelse($sbus ?? [] as $sbu)
            <div class="col-md-6 col-lg-4">
                <div class="card sbu-card border-1 rounded-3 h-100" data-sbu-status="{{ $sbu->is_active ? 'active' : 'inactive' }}">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3 bg-main text-white rounded-2 d-flex align-items-center justify-content-center fw-bold" style="width: 45px; height: 45px; font-size: 1.1rem;">
                                    {{ strtoupper(substr($sbu->name, 0, 2)) }}
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-semibold small">{{ $sbu->name }}</h6>
                                    <small class="text-muted small">{{ $sbu->city ?? '—' }}</small>
                                </div>
                            </div>
                            @if($sbu->is_active)
                            <span class="badge bg-success" style="font-size: 10px; padding: 4px 6px;">Active</span>
                            @else
                            <span class="badge bg-secondary" style="font-size: 10px; padding: 4px 6px;">Inactive</span>
                            @endif
                        </div>
                        @if($sbu->address)
                        <div class="mb-2">
                            <i class="bi bi-geo-alt me-1 text-main small"></i>
                            <small class="text-muted small">{{ Str::limit($sbu->address, 40) }}</small>
                        </div>
                        @endif
                        @if($sbu->organization)
                        <div class="mb-2">
                            <i class="bi bi-building me-1 text-main small"></i>
                            <small class="text-muted small">{{ $sbu->organization->name }}</small>
                        </div>
                        @endif
                        <div class="mt-3 pt-3 border-top d-flex gap-1">

                            <!-- Edit Button -->
                            <button type="button"
                                class="btn btn-sm btn-outline-primary flex-grow-1 edit-sbu-btn"
                                data-bs-toggle="offcanvas"
                                data-bs-target="#editSbuCanvas"
                                data-id="{{ $sbu->id }}"
                                data-edit-url="{{ route('admin.sbu.edit', $sbu->id) }}"
                                data-update-url="{{ route('admin.sbu.update', $sbu->id) }}"
                                data-delete-url="{{ route('admin.sbu.destroy', $sbu->id) }}">
                                <i class="bi bi-pencil me-1"></i>Edit
                            </button>

                            <!-- View Button -->
                            <button type="button"
                                class="btn btn-sm btn-outline-secondary view-sbu-btn"
                                data-bs-toggle="offcanvas"
                                data-bs-target="#sbuDetailCanvas"
                                data-sbu-id="{{ $sbu->id }}"
                                data-sbu-name="{{ e($sbu->name) }}"
                                data-sbu-city="{{ e($sbu->city ?? '') }}"
                                data-sbu-address="{{ e($sbu->address ?? '') }}"
                                data-sbu-latitude="{{ $sbu->latitude }}"
                                data-sbu-longitude="{{ $sbu->longitude }}"
                                data-sbu-active="{{ $sbu->is_active ? '1' : '0' }}"
                                data-organization-name="{{ $sbu->organization ? e($sbu->organization->name) : '' }}">
                                <i class="bi bi-eye"></i>
                            </button>

                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <p class="text-center text-muted">No SBUs found.</p>
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