<!-- Organization Detail Canvas -->
<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="organizationDetailCanvas" aria-labelledby="organizationDetailCanvasLabel">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="organizationDetailCanvasLabel">
            <i class="bi bi-building me-2"></i>Organization Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <!-- Organization Identity -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-info-circle me-2"></i>Organization Identity
            </h6>
            <div class="d-flex align-items-center mb-3">
                <div class="me-3" id="detailOrgLogoContainer">
                    <img src="" alt="Logo" class="rounded-3" id="detailOrgLogo" style="width: 60px; height: 60px; object-fit: cover; display: none;">
                    <div class="bg-light text-dark rounded-3 d-flex align-items-center justify-content-center fw-bold" id="detailOrgLogoPlaceholder" style="width: 60px; height: 60px; font-size: 1.25rem;">EC</div>
                </div>
                <div class="flex-grow-1">
                    <h6 class="fw-semibold small mb-1" id="detailOrgName">Enaara Construction</h6>
                    <small class="opacity-75 text-white" id="detailOrgRegNumber">Reg. No: REG-2024-001</small>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Basic Information -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-file-text me-2"></i>Basic Information
            </h6>
            <div class="row g-3">
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Address</small>
                        <div class="fw-semibold small" id="detailOrgAddress">123 Business District, Dubai, UAE</div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Website</small>
                        <div class="fw-semibold small" id="detailOrgWebsite">
                            <a href="#" class="text-white text-decoration-underline" target="_blank" id="detailOrgWebsiteLink">www.enaara.com</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Statistics -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-bar-chart me-2"></i>Statistics
            </h6>
            <div class="row g-3">
                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Total Headcount</small>
                        <div class="fw-bold fs-5" id="detailOrgHeadcount">450</div>
                        <small class="opacity-50 text-white">Employees</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Departments</small>
                        <div class="fw-bold fs-5" id="detailOrgDepartments">12</div>
                        <small class="opacity-50 text-white">Active</small>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Admin Assigned -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-person-badge me-2"></i>Admin Assigned
            </h6>
            <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                <div class="d-flex align-items-center">
                    <div class="user-avatar me-3" id="detailAdminAvatar">AA</div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold small" id="detailAdminName">Ahmed Ali</div>
                        <small class="opacity-75 text-white" id="detailAdminEmail">ahmed.ali@enaara.com</small>
                    </div>
                    <span class="badge px-3 py-2 rounded-1 bg-success" id="detailAdminStatus">
                        <i class="bi bi-check-circle me-1"></i>Active
                    </span>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Configuration -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-gear me-2"></i>Configuration
            </h6>
            <div class="row g-3">
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Timezone</small>
                        <div class="fw-semibold small" id="detailOrgTimezone">Asia/Dubai (UTC+4)</div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Work Week</small>
                        <div class="fw-semibold small" id="detailOrgWorkWeek">Sunday - Thursday</div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Attendance Radius</small>
                        <div class="fw-semibold small" id="detailOrgAttendanceRadius">100 Meters</div>
                        <small class="opacity-50 text-white">Geofencing radius for attendance check-in/out</small>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Authentication Method -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-shield-lock me-2"></i>Authentication
            </h6>
            <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <small class="opacity-75 text-white d-block mb-2">Authentication Method</small>
                        <div class="fw-semibold small" id="detailOrgAuthMethod">Email/Password</div>
                    </div>
                    <div id="detailOrgAuthBadge">
                        <span class="badge px-3 py-2 rounded-1 bg-info">Standard</span>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-top" style="border-color: #ffffff1a !important;" id="detailSSOInfo" style="display: none;">
                    <small class="opacity-75 text-white d-block mb-2">SSO Provider</small>
                    <div class="fw-semibold small" id="detailSSOProvider">-</div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Hardware / Biometric Devices -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-fingerprint me-2"></i>Biometric Devices
            </h6>
            <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                <div class="mb-3">
                    <small class="opacity-75 text-white d-block mb-2">Total Devices</small>
                    <div class="fw-bold fs-5" id="detailOrgDevicesCount">8</div>
                </div>
                <div class="mt-3 pt-3 border-top" style="border-color: #ffffff1a !important;">
                    <small class="opacity-75 text-white d-block mb-2">Device Serial Numbers</small>
                    <div id="detailOrgDevicesList">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="small">BIO-001-2024</span>
                            <span class="badge bg-success small">Active</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="small">BIO-002-2024</span>
                            <span class="badge bg-success small">Active</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="small">BIO-003-2024</span>
                            <span class="badge bg-warning text-dark small">Maintenance</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Subscription Status -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-credit-card me-2"></i>Subscription Status
            </h6>
            <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                <div class="mb-3">
                    <small class="opacity-75 text-white d-block mb-2">Status</small>
                    <div id="detailOrgSubscriptionStatus">
                        <span class="badge px-3 py-2 rounded-1 bg-success">
                            <i class="bi bi-check-circle me-1"></i>Active
                        </span>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-top" style="border-color: #ffffff1a !important;">
                    <small class="opacity-75 text-white d-block mb-2">Plan</small>
                    <div class="fw-semibold small" id="detailOrgPlan">Enterprise</div>
                </div>
                <div class="mt-3 pt-3 border-top" style="border-color: #ffffff1a !important;">
                    <small class="opacity-75 text-white d-block mb-2">Expiry Date</small>
                    <div class="fw-semibold small" id="detailOrgExpiryDate">December 31, 2024</div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top" style="border-color: #ffffffab !important">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Close</button>
            <button type="button" class="btn btn-light text-dark border-0" id="editOrganizationBtn">
                <i class="bi bi-pencil me-1"></i>Edit Organization
            </button>
        </div>
    </div>
</div>
