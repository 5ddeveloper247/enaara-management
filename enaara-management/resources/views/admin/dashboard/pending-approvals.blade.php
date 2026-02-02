<div class="col-lg-12">
    <div class="card overflow-hidden rounded-5 border-0">
        <div class="card-header px-4 pt-4 pb-3 border-0 px-0 pb-2 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-main">Pending Approvals</h5>
            <span class="badge bg-main">5</span>
        </div>

        <div class="card-body p-0">
            <!-- Bulk Approve Checkbox -->
            <div class="bulk-approve-header py-3 px-4">
                <label class="bulk-approve-checkbox mb-0 d-flex align-items-center">
                    <input type="checkbox" id="bulkApproveAll" class="form-check-input mb-0">
                    <span class="small fw-semibold mt-1">Select All / Bulk Approve</span>
                </label>
                <button id="bulkApproveBtn"
                    class="btn btn-sm btn-primary bg-main rounded-3 border-0 mt-2 d-none" disabled>
                    <i class="bi bi-check-lg me-1"></i>Approve Selected (<span id="selectedCount">0</span>)
                </button>
            </div>

            <div class="approval-item" data-approval-id="1">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center flex-grow-1">
                        <input type="checkbox"
                            class="form-check-input approval-checkbox approval-item-checkbox" value="1">
                        <div class="employee-avatar me-2">AA</div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0 small">Ahmed Ali</h6>
                            <small class="text-muted">Sick Leave</small>
                            <div class="small text-muted">Requested: Jan 15, 2024</div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-secondary rounded-3 border" title="View Reason"
                            onclick="viewLeaveReason(1, 'Ahmed Ali', 'AA', 'Sick Leave', 'Jan 15, 2024', 'Jan 16, 2024', 'Jan 17, 2024', 'I need to take sick leave due to a severe migraine and need to rest.')">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-success bg-main rounded-3 border-0 approve-btn"
                            data-id="1" title="Approve">
                            <i class="bi bi-check-lg"></i>
                        </button>
                        <button class="btn btn-sm btn-danger border-0 bg-main rounded-3 reject-btn"
                            data-id="1" title="Reject">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="approval-item" data-approval-id="2">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center flex-grow-1">
                        <input type="checkbox"
                            class="form-check-input approval-checkbox approval-item-checkbox" value="2">
                        <div class="employee-avatar me-2">FK</div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0 small">Fatima Khan</h6>
                            <small class="text-muted">Annual Leave</small>
                            <div class="small text-muted">Requested: Jan 14, 2024</div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-secondary rounded-3 border" title="View Reason"
                            onclick="viewLeaveReason(2, 'Fatima Khan', 'FK', 'Annual Leave', 'Jan 14, 2024', 'Jan 20, 2024', 'Jan 26, 2024', 'I would like to take my annual leave to spend time with family during the holidays.')">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-success bg-main rounded-3 border-0 approve-btn"
                            data-id="2" title="Approve">
                            <i class="bi bi-check-lg"></i>
                        </button>
                        <button class="btn btn-sm btn-danger border-0 bg-main rounded-3 reject-btn"
                            data-id="2" title="Reject">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="approval-item" data-approval-id="3">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center flex-grow-1">
                        <input type="checkbox"
                            class="form-check-input approval-checkbox approval-item-checkbox" value="3">
                        <div class="employee-avatar me-2">HM</div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0 small">Hassan Malik</h6>
                            <small class="text-muted">Emergency Leave</small>
                            <div class="small text-muted">Requested: Jan 15, 2024</div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-secondary rounded-3 border" title="View Reason"
                            onclick="viewLeaveReason(3, 'Hassan Malik', 'HM', 'Emergency Leave', 'Jan 15, 2024', 'Jan 15, 2024', 'Jan 15, 2024', 'Family emergency - need to attend to urgent family matter.')">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-success bg-main rounded-3 border-0 approve-btn"
                            data-id="3" title="Approve">
                            <i class="bi bi-check-lg"></i>
                        </button>
                        <button class="btn btn-sm btn-danger border-0 bg-main rounded-3 reject-btn"
                            data-id="3" title="Reject">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="approval-item" data-approval-id="4">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center flex-grow-1">
                        <input type="checkbox"
                            class="form-check-input approval-checkbox approval-item-checkbox" value="4">
                        <div class="employee-avatar me-2">AS</div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0 small">Ayesha Sheikh</h6>
                            <small class="text-muted">Sick Leave</small>
                            <div class="small text-muted">Requested: Jan 13, 2024</div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-secondary rounded-3 border" title="View Reason"
                            onclick="viewLeaveReason(4, 'Ayesha Sheikh', 'AS', 'Sick Leave', 'Jan 13, 2024', 'Jan 13, 2024', 'Jan 14, 2024', 'Feeling unwell with flu symptoms. Need rest to recover.')">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-success bg-main rounded-3 border-0 approve-btn"
                            data-id="4" title="Approve">
                            <i class="bi bi-check-lg"></i>
                        </button>
                        <button class="btn btn-sm btn-danger border-0 bg-main rounded-3 reject-btn"
                            data-id="4" title="Reject">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="approval-item" data-approval-id="5">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center flex-grow-1">
                        <input type="checkbox"
                            class="form-check-input approval-checkbox approval-item-checkbox" value="5">
                        <div class="employee-avatar me-2">UR</div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0 small">Usman Raza</h6>
                            <small class="text-muted">Personal Leave</small>
                            <div class="small text-muted">Requested: Jan 12, 2024</div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-secondary rounded-3 border" title="View Reason"
                            onclick="viewLeaveReason(5, 'Usman Raza', 'UR', 'Personal Leave', 'Jan 12, 2024', 'Jan 18, 2024', 'Jan 18, 2024', 'Personal appointment that cannot be rescheduled.')">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-success bg-main rounded-3 border-0 approve-btn"
                            data-id="5" title="Approve">
                            <i class="bi bi-check-lg"></i>
                        </button>
                        <button class="btn btn-sm btn-danger border-0 bg-main rounded-3 reject-btn"
                            data-id="5" title="Reject">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty State (Hidden by default) -->
            <div id="pendingApprovalsEmpty" class="empty-state d-none">
                <div class="empty-state-icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div class="empty-state-title">All Caught Up!</div>
                <div class="empty-state-text">No pending leave requests at the moment.</div>
            </div>
        </div>
        <div class="card-footer bg-transparent border-top">
            <a href="#" class="btn btn-link text-decoration-none text-main p-0">View All Approvals <i
                    class="bi bi-arrow-right ms-1"></i></a>
        </div>
    </div>
</div>

