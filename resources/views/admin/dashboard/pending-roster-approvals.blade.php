<div class="col-12">
    <div class="card overflow-hidden rounded-5 border-0">
        <div class="card-header px-4 pt-4 pb-3 border-0 px-0 pb-2 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-main">Pending Roster Approvals</h5>
            <span class="badge bg-main" id="pendingRosterApprovalsBadge">0</span>
        </div>

        <div class="card-body p-0">
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

    </div>
</div>
