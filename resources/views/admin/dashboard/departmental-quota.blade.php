<!-- Departmental Quota Warnings -->
<div class="col-12">
    <div class="card rounded-5 border-0 overflow-hidden">
        <div class="card-header px-4 pt-4 pb-3 border-0 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 text-main">Departmental Quota Warnings</h5>
                <small class="text-muted">High Leave Concentration</small>
            </div>
            <span class="badge bg-warning">2</span>
        </div>
        <div class="card-body p-0">
            <div class="quota-warning-item border-bottom p-3">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                            <h6 class="mb-0 small">Maintenance Department</h6>
                        </div>
                        <p class="mb-1 small text-muted">30% of staff will be on leave next Monday (Feb 19)</p>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: 30%"></div>
                        </div>
                        <small class="text-muted">6 out of 20 employees</small>
                    </div>
                    <button class="btn btn-sm btn-outline-warning rounded-3" title="View Details">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
            <div class="quota-warning-item p-3">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
                            <h6 class="mb-0 small">Sales Department</h6>
                        </div>
                        <p class="mb-1 small text-muted">25% of staff will be on leave next Friday (Feb 23)</p>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-danger" role="progressbar" style="width: 25%"></div>
                        </div>
                        <small class="text-muted">5 out of 20 employees</small>
                    </div>
                    <button class="btn btn-sm btn-outline-danger rounded-3" title="View Details">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-footer bg-transparent border-top">
            <a href="{{ url('/admin/leave-requests') }}" class="btn btn-link text-decoration-none text-main p-0">
                View All Warnings <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</div>

