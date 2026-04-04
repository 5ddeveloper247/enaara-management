<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card bg-main border-0 rounded-3 shadow h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <h6 class="opacity-75 text-white mb-2 small fw-normal text-uppercase">
                            <i class="bi bi-building me-1"></i>Total Third Parties
                        </h6>
                        <div class="fs-4 mb-2 fw-bold text-white" id="totalThirdParties">{{ $totalThirdParties ?? 0 }}</div>
                    </div>
                    <div class="text-white opacity-50">
                        <i class="bi bi-building fs-1"></i>
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
                        <div class="fs-4 mb-2 fw-bold" id="activeThirdParties">{{ $activeThirdParties ?? 0 }}</div>
                        <small class="text-muted">Active</small>
                    </div>
                    <div class="text-main opacity-50">
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
                            <i class="bi bi-x-circle me-1"></i>Inactive
                        </h6>
                        <div class="fs-4 mb-2 fw-bold text-main" id="inactiveThirdParties">{{ ($totalThirdParties ?? 0) - ($activeThirdParties ?? 0) }}</div>
                        <small class="text-muted">Inactive</small>
                    </div>
                    <div class="text-main opacity-50">
                        <i class="bi bi-x-circle fs-1"></i>
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
                            <i class="bi bi-activity me-1"></i>Active Rate
                        </h6>
                        <div class="fs-4 mb-2 fw-bold text-main" id="activeTpPercentage">{{ $activePercentage ?? 0 }}%</div>
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
