<!-- Policy Summary Cards -->
<div class="row g-3 px-4 pb-3">
    <div class="col-md-3">
        <div class="card bg-main border-0 rounded-4 shadow h-100 summary-counter-card">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="opacity-75 text-white mb-1 small fw-normal text-uppercase">
                            <i class="bi bi-file-text me-1"></i>Total Policies
                        </h6>
                        <div class="h4 mb-0 fw-bold text-white" id="totalPolicies">0</div>
                        <small class="opacity-75 text-white">All policies</small>
                    </div>
                    <div class="text-white opacity-25">
                        <i class="bi bi-file-text fs-1"></i>
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
                        <h6 class="text-muted mb-1 small fw-normal text-uppercase">
                            <i class="bi bi-check-circle me-1"></i>Active Policies
                        </h6>
                        <div class="h4 mb-0 fw-bold text-success" id="activePolicies">0</div>
                        <small class="text-muted">Currently enforced</small>
                    </div>
                    <div class="text-success opacity-25">
                        <i class="bi bi-check-circle fs-1"></i>
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
                        <h6 class="text-muted mb-1 small fw-normal text-uppercase">
                            <i class="bi bi-file-earmark me-1"></i>Draft Policies
                        </h6>
                        <div class="h4 mb-0 fw-bold text-warning" id="draftPolicies">0</div>
                        <small class="text-muted">Under review</small>
                    </div>
                    <div class="text-warning opacity-25">
                        <i class="bi bi-file-earmark fs-1"></i>
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
                        <h6 class="text-muted mb-1 small fw-normal text-uppercase">
                            <i class="bi bi-archive me-1"></i>Archived Policies
                        </h6>
                        <div class="h4 mb-0 fw-bold text-secondary" id="archivedPolicies">0</div>
                        <small class="text-muted">No longer active</small>
                    </div>
                    <div class="text-secondary opacity-25">
                        <i class="bi bi-archive fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

