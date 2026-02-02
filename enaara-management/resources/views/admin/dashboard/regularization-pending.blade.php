<!-- Regularization Pending (Super Admin Final Approval) -->
<div class="col-lg-6">
    <div class="card rounded-5 border-0 overflow-hidden">
        <div class="card-header px-4 pt-4 pb-3 border-0 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 text-main">Regularization Pending</h5>
                <small class="text-muted">Final Approval / Exceptions</small>
            </div>
            <span class="badge bg-warning">12</span>
        </div>
        <div class="card-body p-0">
            <div class="regularization-item" data-regularization-id="1">
                <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
                    <div class="d-flex align-items-center flex-grow-1">
                        <div class="employee-avatar me-3">AA</div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0 small">Ahmed Ali</h6>
                            <small class="text-muted d-block">EMP-001</small>
                            <div class="conflict-info mt-1">
                                <span class="badge bg-danger-subtle text-danger small">System: Absent</span>
                                <i class="bi bi-arrow-right mx-1 text-muted"></i>
                                <span class="badge bg-success-subtle text-success small">Employee: 9 hours</span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-success rounded-3 border-0" style="padding: 4px 7px" title="Approve">
                            <i class="bi bi-check-lg"></i>
                        </button>
                        <button class="btn btn-sm btn-primary rounded-3 border-0" style="padding: 4px 7px" title="Reject">
                            <i class="bi bi-x-lg"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-primary rounded-3 border-0" style="padding: 4px 7px" title="View Details">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="regularization-item" data-regularization-id="2">
                <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
                    <div class="d-flex align-items-center flex-grow-1">
                        <div class="employee-avatar me-3">FK</div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0 small">Fatima Khan</h6>
                            <small class="text-muted d-block">EMP-002</small>
                            <div class="conflict-info mt-1">
                                <span class="badge bg-danger-subtle text-danger small">Missed Punch</span>
                                <span class="badge bg-info-subtle text-info small ms-2">
                                    <i class="bi bi-paperclip"></i> Evidence
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-success rounded-3 border-0" style="padding: 4px 7px" title="Approve">
                            <i class="bi bi-check-lg"></i>
                        </button>
                        <button class="btn btn-sm btn-primary rounded-3 border-0" style="padding: 4px 7px" title="Reject">
                            <i class="bi bi-x-lg"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-primary rounded-3 border-0" style="padding: 4px 7px" title="View Details">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="regularization-item" data-regularization-id="3">
                <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
                    <div class="d-flex align-items-center flex-grow-1">
                        <div class="employee-avatar me-3">HM</div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0 small">Hassan Malik</h6>
                            <small class="text-muted d-block">EMP-003</small>
                            <div class="conflict-info mt-1">
                                <span class="badge bg-warning-subtle text-warning small">Technical Error</span>
                                <span class="badge bg-info-subtle text-info small ms-2">
                                    <i class="bi bi-paperclip"></i> Evidence
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-success rounded-3 border-0" style="padding: 4px 7px" title="Approve">
                            <i class="bi bi-check-lg"></i>
                        </button>
                        <button class="btn btn-sm btn-primary rounded-3 border-0" style="padding: 4px 7px" title="Reject">
                            <i class="bi bi-x-lg"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-primary rounded-3 border-0" style="padding: 4px 7px" title="View Details">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="regularization-item" data-regularization-id="4">
                <div class="d-flex align-items-center justify-content-between p-3">
                    <div class="d-flex align-items-center flex-grow-1">
                        <div class="employee-avatar me-3">AS</div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0 small">Ayesha Sheikh</h6>
                            <small class="text-muted d-block">EMP-004</small>
                            <div class="conflict-info mt-1">
                                <span class="badge bg-danger-subtle text-danger small">On-Duty (Outside)</span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-success rounded-3 border-0" style="padding: 4px 7px" title="Approve">
                            <i class="bi bi-check-lg"></i>
                        </button>
                        <button class="btn btn-sm btn-primary rounded-3 border-0" style="padding: 4px 7px" title="Reject">
                            <i class="bi bi-x-lg"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-primary rounded-3 border-0" style="padding: 4px 7px" title="View Details">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer bg-transparent border-top">
            <a href="{{ url('/admin/regularization') }}" class="btn btn-link text-decoration-none text-main p-0">
                View All Regularizations <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</div>

