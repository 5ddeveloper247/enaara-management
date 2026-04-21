<div class="row">
    <div class="col-lg-9">
        <div class="row g-4" id="biometricDevicesGrid">
            @forelse($devices ?? [] as $d)
            <div class="col-md-6 col-lg-4">
                <div class="card biometric-device-card border-1 rounded-3 h-100" data-bd-status="{{ $d->device_status }}">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3 bg-main text-white rounded-2 d-flex align-items-center justify-content-center fw-bold" style="width: 45px; height: 45px; font-size: 1.1rem;">
                                    <i class="bi bi-fingerprint"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-semibold small">{{ $d->device_name }}</h6>
                                    <small class="text-muted small d-block">{{ $d->serial_number }}</small>
                                    <small class="text-muted small">{{ $d->brand_model }}</small>
                                </div>
                            </div>
                            @if($d->device_status === 'active')
                            <span class="badge bg-success" style="font-size: 10px; padding: 4px 6px;">Active</span>
                            @elseif($d->device_status === 'faulty')
                            <span class="badge bg-danger" style="font-size: 10px; padding: 4px 6px;">Faulty</span>
                            @else
                            <span class="badge bg-secondary" style="font-size: 10px; padding: 4px 6px;">Inactive</span>
                            @endif
                        </div>
                        <div class="mb-2">
                            <i class="bi bi-building me-1 text-main small"></i>
                            <small class="text-muted small">{{ $d->organization?->name ?? '—' }}</small>
                        </div>
                        <div class="mb-2">
                            <i class="bi bi-diagram-3 me-1 text-main small"></i>
                            <small class="text-muted small">{{ $d->sbu?->name ?? '—' }} · {{ $d->floor?->name ?? '—' }}</small>
                        </div>
                        <div class="mb-2">
                            <i class="bi bi-hdd-network me-1 text-main small"></i>
                            <small class="text-muted small">{{ $d->ip_address }}:{{ $d->port }} · {{ strtoupper($d->connection_type) }}</small>
                        </div>
                        <div class="mt-3 pt-3 border-top d-flex gap-1">
                            @if(validatePermissions('admin/biometric-device/edit'))
                            <button type="button"
                                class="btn btn-sm btn-outline-primary flex-grow-1 edit-bd-btn"
                                data-bs-toggle="offcanvas"
                                data-bs-target="#editBiometricDeviceCanvas"
                                data-id="{{ $d->id }}"
                                data-edit-url="{{ route('admin.biometric-device.edit', $d->id) }}"
                                data-update-url="{{ route('admin.biometric-device.update', $d->id) }}">
                                <i class="bi bi-pencil me-1"></i>Edit
                            </button>
                            @endif
                            @if(validatePermissions('admin/biometric-device/delete'))
                            <button type="button" class="btn btn-sm btn-outline-danger delete-bd-btn" data-delete-url="{{ route('admin.biometric-device.destroy', $d->id) }}">
                                <i class="bi bi-trash"></i>
                            </button>
                            @endif
                            <button type="button"
                                class="btn btn-sm btn-outline-secondary view-bd-btn"
                                data-bs-toggle="offcanvas"
                                data-bs-target="#biometricDeviceDetailCanvas"
                                data-bd-device-name="{{ e($d->device_name) }}"
                                data-bd-serial="{{ e($d->serial_number) }}"
                                data-bd-type="{{ e($d->device_type) }}"
                                data-bd-brand="{{ e($d->brand_model) }}"
                                data-bd-org="{{ e($d->organization?->name ?? '') }}"
                                data-bd-sbu="{{ e($d->sbu?->name ?? '') }}"
                                data-bd-floor="{{ e($d->floor?->name ?? '') }}"
                                data-bd-ip="{{ e($d->ip_address) }}"
                                data-bd-port="{{ $d->port }}"
                                data-bd-conn="{{ e($d->connection_type) }}"
                                data-bd-device-status="{{ e($d->device_status) }}"
                                data-bd-online="{{ e($d->online_status) }}"
                                data-bd-last-sync="{{ $d->last_sync_time ? $d->last_sync_time->format('Y-m-d H:i') : '' }}"
                                data-bd-install="{{ $d->installation_date ? $d->installation_date->format('Y-m-d') : '' }}"
                                data-bd-created-by="{{ e($d->creator?->name ?? '—') }}"
                                data-bd-created-at="{{ $d->created_at?->format('Y-m-d H:i') ?? '' }}"
                                data-bd-updated-at="{{ $d->updated_at?->format('Y-m-d H:i') ?? '' }}">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <p class="text-center text-muted">No biometric devices registered.</p>
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
                    <label class="form-label small fw-semibold text-muted mb-2">Device status</label>
                    <div class="bg-transparent">
                        <label class="list-group-item list-group-item-action border-0 px-0 py-2 cursor-pointer">
                            <input class="form-check-input me-2 filter-bd-status" type="radio" name="filterBdStatus" value="all" id="filterBdStatusAll" checked>
                            All
                        </label>
                        <label class="list-group-item list-group-item-action border-0 px-0 py-2 cursor-pointer">
                            <input class="form-check-input me-2 filter-bd-status" type="radio" name="filterBdStatus" value="active" id="filterBdStatusActive">
                            Active
                        </label>
                        <label class="list-group-item list-group-item-action border-0 px-0 py-2 cursor-pointer">
                            <input class="form-check-input me-2 filter-bd-status" type="radio" name="filterBdStatus" value="inactive" id="filterBdStatusInactive">
                            Inactive
                        </label>
                        <label class="list-group-item list-group-item-action border-0 px-0 py-2 cursor-pointer">
                            <input class="form-check-input me-2 filter-bd-status" type="radio" name="filterBdStatus" value="faulty" id="filterBdStatusFaulty">
                            Faulty
                        </label>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="clearBdFiltersBtn">
                    <i class="bi bi-x-circle me-1"></i>Clear Filters
                </button>
            </div>
        </div>
    </div>
</div>
