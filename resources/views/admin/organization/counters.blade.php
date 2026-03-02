<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card bg-main border-0 rounded-3 shadow h-100 department-metric-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <h6 class="opacity-75 text-white mb-2 small fw-normal text-uppercase">
                            <i class="bi bi-building me-1"></i>Total Organizations
                        </h6>
                        <div class="fs-4 mb-2 fw-bold text-white" id="totalOrganizations">{{ $totalOrganizations ?? 0 }}</div>
                    </div>
                    <div class="text-white opacity-50">
                        <i class="bi bi-building fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 rounded-3 shadow h-100 department-metric-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-2 small fw-normal text-uppercase">
                            <i class="bi bi-people me-1"></i>Active organizations
                        </h6>
                        <div class="fs-4 mb-2 fw-bold" id="globalHeadcount">{{ $activeOrganizations ?? 0 }}</div>
                        <small class="text-muted">Active organizations</small>
                    </div>
                    <div class="text-main opacity-50">
                        <i class="bi bi-people fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 rounded-3 shadow h-100 department-metric-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-2 small fw-normal text-uppercase">
                            <i class="bi bi-fingerprint me-1"></i>In-Active Organizations
                        </h6>
                        <div class="fs-4 mb-2 fw-bold text-main" id="biometricStatus">{{ $totalOrganizations - $activeOrganizations ?? 0 }}</div>
                        <small class="text-muted">In-Active Organizations</small>
                    </div>
                    <div class="text-main opacity-50">
                        <i class="bi bi-fingerprint fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 rounded-3 shadow h-100 department-metric-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-2 small fw-normal text-uppercase">
                            <i class="bi bi-activity me-1"></i>Active rate
                        </h6>
                        <div class="fs-4 mb-2 fw-bold text-main" id="attendencePulse">{{ $activePercentage ?? 0 }}%</div>
                        <small class="text-muted">Active rate</small>
                    </div>
                    <div class="text-main opacity-50">
                        <i class="bi bi-activity fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>