<!-- Overtime Detail Canvas -->
<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="overtimeDetailCanvas" aria-labelledby="overtimeDetailCanvasLabel" style="width: 700px;">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="overtimeDetailCanvasLabel">
            <i class="bi bi-hourglass-split me-2"></i>Overtime Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <!-- Employee Information -->
        <div class="mb-4">
            <div class="d-flex align-items-center mb-3">
                <div class="user-avatar me-3" id="detailEmployeeAvatar" style="width: 50px; height: 50px; font-size: 1.1rem;">AA</div>
                <div class="flex-grow-1">
                    <h5 class="fw-semibold mb-0" id="detailEmployeeName">Ahmed Ali</h5>
                    <small class="opacity-75 text-white" id="detailEmployeeInfo">EMP-0001 | Sales</small>
                    <div class="mt-1">
                        <span class="badge bg-info-subtle text-info px-2 py-1 rounded-1" id="detailEmployeeLocation" style="font-size: 0.7rem;">
                            <i class="bi bi-building me-1"></i>Rawalpindi - Floor 1
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Overtime Details -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-clock-history me-2"></i>Overtime Information
            </h6>
            <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                <div class="row g-3">
                    <div class="col-6">
                        <small class="opacity-75 text-white d-block mb-1">Date</small>
                        <div class="fw-semibold" id="detailOTDate">2024-01-15</div>
                    </div>
                    <div class="col-6">
                        <small class="opacity-75 text-white d-block mb-1">OT Hours</small>
                        <div class="fw-semibold text-primary" id="detailOTHours">2.5 hrs</div>
                    </div>
                    <div class="col-6">
                        <small class="opacity-75 text-white d-block mb-1">Shift End Time</small>
                        <div class="fw-semibold" id="detailShiftEnd">18:00</div>
                    </div>
                    <div class="col-6">
                        <small class="opacity-75 text-white d-block mb-1">Actual Punch Out</small>
                        <div class="fw-semibold text-primary" id="detailActualPunchOut">20:30</div>
                    </div>
                    <div class="col-12">
                        <small class="opacity-75 text-white d-block mb-1">OT Category</small>
                        <div id="detailOTCategory">
                            <span class="badge bg-primary px-2 py-1">In-Office OT</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Verification & Status -->
        <div class="mb-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <h6 class="mb-3 fw-semibold small">
                        <i class="bi bi-shield-check me-2"></i>Verification Status
                    </h6>
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <div id="detailVerificationStatus">
                            <span class="badge bg-info px-2 py-1">
                                <i class="bi bi-check-circle me-1"></i>Biometric Verified
                            </span>
                        </div>
                        <div class="mt-2" id="detailZone2Indicator">
                            <!-- Zone-2 indicator will be shown here if applicable -->
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <h6 class="mb-3 fw-semibold small">
                        <i class="bi bi-info-circle me-2"></i>Status
                    </h6>
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <div id="detailOTStatus">
                            <span class="badge bg-warning text-dark px-2 py-1">Pending</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Geofence Information (if applicable) -->
        <div class="mb-4" id="geofenceSection" style="display: none;">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-geo-alt-fill me-2"></i>Geofence Information
            </h6>
            <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                <div class="mb-2">
                    <small class="opacity-75 text-white d-block mb-1">Location Status</small>
                    <div id="detailGeofenceStatus">
                        <span class="badge bg-success px-2 py-1">
                            <i class="bi bi-geo-alt-fill me-1"></i>In Zone
                        </span>
                    </div>
                </div>
                <div id="geofenceWarning" style="display: none;">
                    <div class="alert alert-warning mt-2 mb-0 py-2" role="alert">
                        <small>
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            <strong>Location Mismatch:</strong> Employee was out of geofence zone during overtime.
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Evidence Section -->
        <div class="mb-4" id="evidenceSection" style="display: none;">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-file-earmark-image me-2"></i>Evidence
            </h6>
            <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                <div id="detailEvidence">
                    <small class="opacity-75 text-white d-block mb-2">Evidence files available</small>
                    <button type="button" class="btn btn-sm btn-outline-light" id="viewEvidenceBtn">
                        <i class="bi bi-eye me-1"></i>View Evidence
                    </button>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top" style="border-color: #ffffffab !important" id="actionButtonsSection">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Close</button>
            <button type="button" class="btn btn-danger" id="rejectOTBtn" style="display: none;">
                <i class="bi bi-x-circle me-1"></i>Reject
            </button>
            <button type="button" class="btn btn-success" id="approveOTBtn" style="display: none;">
                <i class="bi bi-check-circle me-1"></i>Approve
            </button>
        </div>
    </div>
</div>

