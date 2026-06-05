<div class="col-lg-12">
    <div class="card overflow-hidden rounded-5 border-0">
        <div class="card-header px-4 pt-4 pb-3 border-0 px-0 pb-2 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-main">Pending Approvals</h5>
            <span class="badge bg-main" id="pendingApprovalsBadge">0</span>
        </div>

        <div class="card-body p-0">
            <div class="px-4 pt-3 pb-2 border-bottom">
                <p class="small fw-semibold text-main mb-2">Leave requests</p>
            </div>

            <div class="bulk-approve-header py-3 px-4" id="bulkApproveHeader">
                <label class="bulk-approve-checkbox mb-0 d-flex align-items-center">
                    <input type="checkbox" id="bulkApproveAll" class="form-check-input mb-0">
                    <span class="small fw-semibold mt-1">Select All / Bulk Approve</span>
                </label>
                <button id="bulkApproveBtn"
                    class="btn btn-sm btn-primary bg-main rounded-3 border-0 mt-2 d-none" disabled>
                    <i class="bi bi-check-lg me-1"></i>Approve Selected (<span id="selectedCount">0</span>)
                </button>
            </div>

            <div id="approvalsList">
                <div class="text-center py-4 text-muted" id="approvalsLoader">
                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                    Loading...
                </div>
            </div>

            <div id="pendingApprovalsEmpty" class="empty-state d-none">
                <div class="empty-state-icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div class="empty-state-title">All Caught Up!</div>
                <div class="empty-state-text">No pending leave requests at the moment.</div>
            </div>

            <div class="px-4 pt-4 pb-2 border-top border-bottom mt-2 d-flex justify-content-between align-items-center">
                <p class="small fw-semibold text-main mb-0">Pending roster approvals</p>
                <span class="badge bg-secondary" id="pendingRosterApprovalsBadge">0</span>
            </div>

            <div id="rosterApprovalsList">
                <div class="text-center py-4 text-muted" id="rosterApprovalsLoader">
                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                    Loading...
                </div>
            </div>

            <div id="pendingRosterApprovalsEmpty" class="empty-state d-none">
                <div class="empty-state-icon">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div class="empty-state-title">No Pending Rosters</div>
                <div class="empty-state-text">No shift roster requests waiting for your approval.</div>
            </div>
        </div>

        <div class="card-footer bg-transparent border-top">
            <a href="{{ route('admin.leave.request.index') }}" class="btn btn-link text-decoration-none text-main p-0">
                View All Leave Approvals <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</div>
