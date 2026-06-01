<!-- Leave Detail Canvas -->
<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="leaveDetailCanvas" aria-labelledby="leaveDetailCanvasLabel" style="width: 500px;">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="leaveDetailCanvasLabel">
            <i class="bi bi-calendar-event me-2"></i>Leave Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <!-- Leave Details -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-calendar me-2"></i>Leave Information
            </h6>
            <div class="row g-3">
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Leave Type</small>
                        <div id="detailLeaveType">
                            <span class="badge bg-primary">Annual Leave</span>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Start Date</small>
                        <div class="fw-semibold small" id="detailStartDate">Feb 1, 2024</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">End Date</small>
                        <div class="fw-semibold small" id="detailEndDate">Feb 5, 2024</div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Total Days</small>
                        <div class="fw-bold fs-5" id="detailDays">5</div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Reason</small>
                        <div class="small" id="detailReason">Family vacation</div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Status</small>
                        <div id="detailStatus">
                            <span class="badge bg-success">Approved</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="offcanvas-footer border-top p-3" style="border-color: #ffffffab !important">
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Close</button>
            <button type="button" class="btn btn-danger d-none" id="cancelLeaveBtn">Cancel Leave</button>
        </div>
    </div>
</div>

