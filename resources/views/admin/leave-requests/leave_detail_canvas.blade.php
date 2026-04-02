<!-- Leave Detail Canvas -->
<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="leaveDetailCanvas" aria-labelledby="leaveDetailCanvasLabel" style="width: 600px;">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="leaveDetailCanvasLabel">
            <i class="bi bi-calendar-event me-2"></i>Leave Request Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <!-- Employee Information -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-person me-2"></i>Employee Information
            </h6>
            <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                <div class="mb-2">
                    <small class="opacity-75 text-white d-block mb-1">Employee Name</small>
                    <div class="fw-semibold small" id="detailEmployeeName">Ahmed Ali</div>
                </div>
                <div class="mb-2">
                    <small class="opacity-75 text-white d-block mb-1">Employee ID</small>
                    <div class="small" id="detailEmployeeId">EMP-001</div>
                </div>
                <div>
                    <small class="opacity-75 text-white d-block mb-1">Department</small>
                    <div class="small" id="detailDepartment">Sales</div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Leave Details -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-calendar me-2"></i>Leave Details
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
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Leave Balance & Status -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-info-circle me-2"></i>Balance & Status
            </h6>
            <div class="row g-3">
                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Remaining Balance</small>
                        <div class="fw-bold fs-5" id="detailBalance">25</div>
                        <small class="opacity-50 text-white">days</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Status</small>
                        <div id="detailStatus">
                            <span class="badge bg-warning text-dark">Pending</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Approval Workflow -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-diagram-3 me-2"></i>Approval Workflow
            </h6>
            <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                <div class="mb-2">
                    <small class="opacity-75 text-white d-block mb-1">Current Level</small>
                    <div class="fw-semibold small" id="detailApprovalLevel">Supervisor</div>
                </div>
                <div class="mt-3 pt-3 border-top" style="border-color: #ffffff1a !important;">
                    <small class="opacity-75 text-white d-block mb-3">Approval Timeline</small>
                    <div id="approvalTimeline">
                        <!-- Populated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="offcanvas-footer border-top p-3" style="border-color: #ffffffab !important">
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Close</button>
            <button type="button" class="btn btn-light text-dark border-0" id="approveDetailBtn">
                <i class="bi bi-check-circle me-1"></i>Approve
            </button>
            <button type="button" class="btn btn-outline-danger" id="rejectDetailBtn">
                <i class="bi bi-x-circle me-1"></i>Reject
            </button>
        </div>
    </div>
</div>



