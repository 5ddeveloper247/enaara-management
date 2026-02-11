<!-- Audit Summary Cards -->
<div class="row g-3 px-4 pb-4">
    <div class="col-md-3">
        <div class="card bg-main border-0 rounded-4 shadow h-100 summary-counter-card">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="opacity-75 text-white mb-3 small fw-normal text-capitalize">
                            <i class="bi bi-list-check me-1"></i>Total Activities
                        </h6>
                        <div class="h4 mb-0 fw-bold text-white" id="totalActivities">0</div>
                        <small class="opacity-75 text-white">Last 30 days</small>
                    </div>
                    <div class="text-white opacity-25">
                        <i class="bi bi-list-check fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 rounded-4 shadow h-100 summary-counter-card">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted mb-3 small fw-normal text-capitalize">
                            <i class="bi bi-exclamation-triangle me-1"></i>Critical Events
                        </h6>
                        <div class="h4 mb-0 fw-bold text-danger" id="criticalEvents">0</div>
                        <small class="text-muted">Security alerts</small>
                    </div>
                    <div class="text-danger opacity-25">
                        <i class="bi bi-exclamation-triangle fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 rounded-4 shadow h-100 summary-counter-card">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted mb-3 small fw-normal text-capitalize">
                            <i class="bi bi-shield-lock me-1"></i>Security Actions
                        </h6>
                        <div class="h4 mb-0 fw-bold text-warning" id="securityActions">0</div>
                        <small class="text-muted">Access changes</small>
                    </div>
                    <div class="text-warning opacity-25">
                        <i class="bi bi-shield-lock fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 rounded-4 shadow h-100 summary-counter-card">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted mb-3 small fw-normal text-capitalize">
                            <i class="bi bi-people me-1"></i>Active Users
                        </h6>
                        <div class="h4 mb-0 fw-bold text-info" id="activeUsers">0</div>
                        <small class="text-muted">Unique admins</small>
                    </div>
                    <div class="text-info opacity-25">
                        <i class="bi bi-people fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

