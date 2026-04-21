<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card bg-main border-0 rounded-3 shadow h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <h6 class="opacity-75 text-white mb-2 small fw-normal text-uppercase">
                            <i class="bi bi-fingerprint me-1"></i>Total Devices
                        </h6>
                        <div class="fs-4 mb-2 fw-bold text-white" id="totalBiometricDevices">{{ $totalDevices ?? 0 }}</div>
                    </div>
                    <div class="text-white opacity-50">
                        <i class="bi bi-fingerprint fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 rounded-3 shadow h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-2 small fw-normal text-uppercase">
                            <i class="bi bi-check-circle me-1"></i>Active
                        </h6>
                        <div class="fs-4 mb-2 fw-bold" id="activeBiometricDevices">{{ $activeDevices ?? 0 }}</div>
                        <small class="text-muted">Status: Active</small>
                    </div>
                    <div class="text-success opacity-50">
                        <i class="bi bi-check-circle fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 rounded-3 shadow h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-2 small fw-normal text-uppercase">
                            <i class="bi bi-pause-circle me-1"></i>Inactive
                        </h6>
                        <div class="fs-4 mb-2 fw-bold text-main" id="inactiveBiometricDevices">{{ $inactiveDevices ?? 0 }}</div>
                        <small class="text-muted">Status: Inactive</small>
                    </div>
                    <div class="text-main opacity-50">
                        <i class="bi bi-pause-circle fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 rounded-3 shadow h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-2 small fw-normal text-uppercase">
                            <i class="bi bi-exclamation-triangle me-1"></i>Faulty
                        </h6>
                        <div class="fs-4 mb-2 fw-bold text-danger" id="faultyBiometricDevices">{{ $faultyDevices ?? 0 }}</div>
                        <small class="text-muted">Status: Faulty</small>
                    </div>
                    <div class="text-danger opacity-50">
                        <i class="bi bi-exclamation-triangle fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
