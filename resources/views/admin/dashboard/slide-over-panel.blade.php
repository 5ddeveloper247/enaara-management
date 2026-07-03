<!-- Slide-over Panel for Leave Reason -->
<div class="slide-over-backdrop" id="slideOverBackdrop" onclick="closeSlideOver()"></div>
<div class="slide-over-panel" id="slideOverPanel">
    <div class="slide-over-header">
        <h5 class="mb-0">Leave Request Details</h5>
        <button class="slide-over-close" onclick="closeSlideOver()" title="Close">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <div class="slide-over-body">
        <div class="leave-detail-item">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="employee-avatar me-2" id="slideEmployeeAvatar">JD</div>
                <div>
                    <h5 class="mb-0" id="slideEmployeeName">Ahmed Ali</h5>
                    <small class="text-muted" id="slideLeaveType">Sick Leave</small>
                </div>
            </div>
        </div>

        <div class="leave-detail-item">
            <div class="leave-detail-label">Requested by</div>
            <div class="leave-detail-value" id="slideRequestedBy">-</div>
        </div>

        <div class="leave-detail-item">
            <div class="leave-detail-label">Request Date</div>
            <div class="leave-detail-value" id="slideRequestDate">Jan 15, 2024</div>
        </div>

        <div class="leave-detail-item">
            <div class="leave-detail-label">Leave Start Date</div>
            <div class="leave-detail-value" id="slideStartDate">Jan 16, 2024</div>
        </div>

        <div class="leave-detail-item">
            <div class="leave-detail-label">Leave End Date</div>
            <div class="leave-detail-value" id="slideEndDate">Jan 17, 2024</div>
        </div>

        <div class="leave-detail-item">
            <div class="leave-detail-label">Reason for Leave</div>
            <div class="leave-reason-box" id="slideReason">
                I need to take sick leave due to a severe migraine and need to rest.
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button class="btn btn-success bg-main rounded-3 border-0 flex-fill" id="slideApproveBtn"
                onclick="approveFromSlide()">
                <i class="bi bi-check-lg me-1"></i>Approve
            </button>
            <button class="btn btn-danger bg-main rounded-3 border-0 flex-fill" id="slideRejectBtn"
                onclick="rejectFromSlide()">
                <i class="bi bi-x-lg me-1"></i>Reject
            </button>
        </div>
    </div>
</div>

