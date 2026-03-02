<div class="row g-3 px-4 pb-3">
    <div class="col-md-4">
        <div class="card bg-main border-0 rounded-3 shadow h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="opacity-75 text-white mb-1 small fw-normal text-uppercase">
                            <i class="bi bi-briefcase me-1"></i>Total Work Types
                        </h6>
                        <div class="h4 mb-0 fw-bold text-white" id="totalWorkTypes">{{ $total ?? 0 }}</div>
                    </div>
                    <div class="text-white opacity-25">
                        <i class="bi bi-briefcase fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 rounded-3 shadow h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1 small fw-normal text-uppercase">
                            <i class="bi bi-check-circle me-1"></i>Active
                        </h6>
                        <div class="h4 mb-0 fw-bold text-success" id="totalActive">{{ $active ?? 0 }}</div>
                    </div>
                    <div class="text-success opacity-25">
                        <i class="bi bi-check-circle fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 rounded-3 shadow h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1 small fw-normal text-uppercase">
                            <i class="bi bi-x-circle me-1"></i>Inactive
                        </h6>
                        <div class="h4 mb-0 fw-bold text-secondary" id="totalInactive">{{ $inactive ?? 0 }}</div>
                    </div>
                    <div class="text-secondary opacity-25">
                        <i class="bi bi-x-circle fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
