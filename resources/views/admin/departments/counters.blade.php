<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card bg-main border-0 rounded-3 shadow h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="opacity-75 text-white mb-2 small fw-normal text-uppercase">
                            <i class="bi bi-building me-1"></i>Total Departments
                        </h6>
                        <div class="fs-4 mb-0 fw-bold text-white" id="totalDepartments">{{ $totalDepartments ?? 0 }}</div>
                    </div>
                    <div class="text-white opacity-50">
                        <i class="bi bi-building fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 rounded-3 shadow h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted mb-2 small fw-normal text-uppercase">
                            <i class="bi bi-check-circle me-1"></i>Active
                        </h6>
                        <div class="fs-4 mb-0 fw-bold" id="activeDepartments">{{ $activeDepartments ?? 0 }}</div>
                    </div>
                    <div class="text-main opacity-50">
                        <i class="bi bi-check-circle fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 rounded-3 shadow h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted mb-2 small fw-normal text-uppercase">
                            <i class="bi bi-activity me-1"></i>Active Rate
                        </h6>
                        <div class="fs-4 mb-0 fw-bold text-main" id="activePercentage">{{ $activePercentage ?? 0 }}%</div>
                    </div>
                    <div class="text-main opacity-50">
                        <i class="bi bi-activity fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
