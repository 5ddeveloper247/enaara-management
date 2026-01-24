<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="employeeDetailCanvas" aria-labelledby="employeeDetailCanvasLabel">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="employeeDetailCanvasLabel">
            <i class="bi bi-person-circle me-2"></i>Employee Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <!-- Employee Profile Information -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-person-badge me-2"></i>Profile Information
            </h6>
            <div class="d-flex align-items-center">
                <div class="user-avatar me-3" id="detailEmployeeAvatar" style="width: 45px; height: 45px; font-size: 1rem;">JD</div>
                <div>
                    <h6 class="fw-semibold small mb-0" id="detailEmployeeName">John Doe</h6>
                    <small class="opacity-75 text-white small" id="detailEmployeeInfo">Sales - EMP-001</small>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Employee Details -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-info-circle me-2"></i>Employee Details
            </h6>
            <div class="row g-3">
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Employee ID</small>
                        <div class="fw-semibold small" id="detailEmployeeId">EMP-001</div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Department</small>
                        <div class="fw-semibold small" id="detailDepartment">Sales</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Employment Type</small>
                        <div class="fw-semibold small" id="detailEmploymentType">
                            <span class="badge bg-success">Permanent</span>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Employee Type</small>
                        <div class="fw-semibold small" id="detailEmployeeType">
                            <span class="badge bg-primary">Internal</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Biometric Information -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-fingerprint me-2"></i>Biometric Information
            </h6>
            <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <small class="opacity-75 text-white d-block mb-2">Biometric ID</small>
                        <div class="fw-semibold" id="detailBiometricId">BIO-000001</div>
                    </div>
                    <div id="detailBiometricStatus">
                        <span class="badge px-3 py-2 rounded-1 bg-success">
                            <i class="bi bi-check-circle me-1"></i>Synced
                        </span>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-top" style="border-color: #ffffff1a !important;">
                    <small class="opacity-75 text-white d-block mb-2">Sync Status</small>
                    <div class="fw-semibold" id="detailSyncStatusText">Successfully synced with biometric system</div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Assignment Information -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-building me-2"></i>Assignment & Organization
            </h6>
            <div class="row g-3">
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Site Assignment</small>
                        <div class="fw-semibold" id="detailSiteAssignment">Head Office</div>
                    </div>
                </div>
                <div class="col-12" id="detailVendorContainer" style="display: none;">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Vendor</small>
                        <div class="fw-semibold" id="detailVendor">-</div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Status Information -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-activity me-2"></i>Status Information
            </h6>
            <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                <div class="mb-3">
                    <small class="opacity-75 text-white d-block mb-2">Current Status</small>
                    <div id="detailCurrentStatus">
                        <span class="badge px-3 py-2 rounded-1 bg-success">
                            <i class="bi bi-check-circle me-1"></i>Active
                        </span>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-top" style="border-color: #ffffff1a !important;">
                    <small class="opacity-75 text-white d-block mb-2">Additional Information</small>
                    <ul class="list-unstyled mb-0 small opacity-75">
                        <li class="mb-1">
                            <i class="bi bi-check-circle-fill text-success me-1"></i>
                            <span id="detailStatusInfo1">Employee is active and working</span>
                        </li>
                        <li class="mb-1">
                            <span id="detailStatusInfo2">Biometric device linked</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top" style="border-color: #ffffffab !important">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Close</button>
            <button type="button" class="btn btn-light text-dark border-0" id="editEmployeeBtn">
                <i class="bi bi-pencil me-1"></i>Edit Employee
            </button>
        </div>
    </div>
</div>

