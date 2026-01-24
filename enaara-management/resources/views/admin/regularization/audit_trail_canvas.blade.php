<!-- Audit Trail Canvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="auditTrailCanvas" aria-labelledby="auditTrailCanvasLabel" style="width: 500px;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="auditTrailCanvasLabel">
            <i class="bi bi-clock-history me-2"></i>Audit Trail
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="mb-3">
            <h6 class="fw-semibold mb-3">Request Details</h6>
            <div class="p-3 border rounded-3 mb-3">
                <div class="mb-2">
                    <small class="opacity-50 text-white d-block">Employee</small>
                    <strong id="auditEmployeeName">-</strong>
                </div>
                <div class="mb-2">
                    <small class="opacity-50 text-white d-block">Date</small>
                    <strong id="auditDate">-</strong>
                </div>
                <div>
                    <small class="opacity-50 text-white d-block">Request ID</small>
                    <strong id="auditRequestId">-</strong>
                </div>
            </div>
        </div>

        <h6 class="fw-semibold mb-3">Activity Log</h6>
        <div id="auditTrailList">
            <!-- Audit trail items will be populated here -->
            <div class="timeline-item mb-3 pb-3 border-bottom">
                <div class="d-flex align-items-start">
                    <div class="me-3">
                        <i class="bi bi-check-circle text-success"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <strong class="small">Request Approved</strong>
                            <small class="opacity-50 text-white">2 hours ago</small>
                        </div>
                        <small class="opacity-50 text-white d-block">
                            Admin <strong>John Admin</strong> approved this request on <strong>Jan 15, 2024</strong> at <strong>10:30 AM</strong>
                        </small>
                    </div>
                </div>
            </div>

            <div class="timeline-item mb-3 pb-3 border-bottom">
                <div class="d-flex align-items-start">
                    <div class="me-3">
                        <i class="bi bi-person text-info"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <strong class="small">Request Submitted</strong>
                            <small class="opacity-50 text-white">1 day ago</small>
                        </div>
                        <small class="opacity-50 text-white d-block">
                            Employee <strong>John Doe</strong> submitted this request on <strong>Jan 14, 2024</strong> at <strong>6:00 PM</strong>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

