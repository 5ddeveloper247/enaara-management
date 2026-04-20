<div class="row">
    <div class="col-lg-9">
        <div class="row g-4" id="thirdPartiesGrid">
            @forelse($thirdParties ?? [] as $tp)
            <div class="col-md-6 col-lg-4">
                <div class="card third-party-card border-1 rounded-3 h-100" data-tp-status="{{ $tp->is_active ? 'active' : 'inactive' }}">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3 bg-main text-white rounded-2 d-flex align-items-center justify-content-center fw-bold" style="width: 45px; height: 45px; font-size: 1.1rem;">
                                    {{ strtoupper(substr($tp->third_party_name, 0, 2)) }}
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-semibold small">{{ $tp->third_party_name }}</h6>
                                    <small class="text-muted small d-block">
                                        {{ ($tp->sbus ?? collect())->pluck('name')->take(2)->implode(', ') ?: '—' }}
                                    </small>
                                    <small class="text-muted small">{{ $tp->city ?? '—' }}</small>
                                </div>
                            </div>
                            @if($tp->is_active)
                            <span class="badge bg-success" style="font-size: 10px; padding: 4px 6px;">Active</span>
                            @else
                            <span class="badge bg-secondary" style="font-size: 10px; padding: 4px 6px;">Inactive</span>
                            @endif
                        </div>
                        @if($tp->address)
                        <div class="mb-2">
                            <i class="bi bi-geo-alt me-1 text-main small"></i>
                            <small class="text-muted small">{{ Str::limit($tp->address, 40) }}</small>
                        </div>
                        @endif
                        @if($tp->organization)
                        <div class="mb-2">
                            <i class="bi bi-building me-1 text-main small"></i>
                            <small class="text-muted small">
                                {{ ($tp->organizations ?? collect())->pluck('name')->take(2)->implode(', ') ?: $tp->organization->name }}
                            </small>
                        </div>
                        @endif
                        <div class="mt-3 pt-3 border-top d-flex gap-1">

                            <button type="button"
                                class="btn btn-sm btn-outline-primary flex-grow-1 edit-tp-btn"
                                data-bs-toggle="offcanvas"
                                data-bs-target="#editThirdPartyCanvas"
                                data-id="{{ $tp->id }}"
                                data-edit-url="{{ route('admin.third-party.edit', $tp->id) }}"
                                data-update-url="{{ route('admin.third-party.update', $tp->id) }}"
                                data-delete-url="{{ route('admin.third-party.destroy', $tp->id) }}">
                                <i class="bi bi-pencil me-1"></i>Edit
                            </button>

                            <button type="button" class="btn btn-sm btn-outline-danger delete-tp-btn" data-delete-url="{{ route('admin.third-party.destroy', $tp->id) }}">
                                <i class="bi bi-trash"></i>
                            </button>

                            <button type="button"
                                class="btn btn-sm btn-outline-secondary view-tp-btn"
                                data-bs-toggle="offcanvas"
                                data-bs-target="#thirdPartyDetailCanvas"
                                data-tp-name="{{ e($tp->third_party_name) }}"
                                data-tp-third-party-name="{{ e($tp->third_party_name) }}"
                                data-tp-sbu-names="{{ e(($tp->sbus ?? collect())->pluck('name')->implode(', ')) }}"
                                data-tp-city="{{ e($tp->city ?? '') }}"
                                data-tp-address="{{ e($tp->address ?? '') }}"
                                data-tp-latitude="{{ $tp->latitude }}"
                                data-tp-longitude="{{ $tp->longitude }}"
                                data-tp-active="{{ $tp->is_active ? '1' : '0' }}"
                                data-organization-name="{{ e(($tp->organizations ?? collect())->pluck('name')->implode(', ')) }}">
                                <i class="bi bi-eye"></i>
                            </button>

                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <p class="text-center text-muted">No third parties found.</p>
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
                            <input class="form-check-input me-2 filter-tp-status" type="radio" name="filterTpStatus" value="all" id="filterTpStatusAll" checked>
                            All
                        </label>
                        <label class="list-group-item list-group-item-action border-0 px-0 py-2 cursor-pointer">
                            <input class="form-check-input me-2 filter-tp-status" type="radio" name="filterTpStatus" value="active" id="filterTpStatusActive">
                            Active
                        </label>
                        <label class="list-group-item list-group-item-action border-0 px-0 py-2 cursor-pointer">
                            <input class="form-check-input me-2 filter-tp-status" type="radio" name="filterTpStatus" value="inactive" id="filterTpStatusInactive">
                            Inactive
                        </label>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="clearTpFiltersBtn">
                    <i class="bi bi-x-circle me-1"></i>Clear Filters
                </button>
            </div>
        </div>
    </div>
</div>
