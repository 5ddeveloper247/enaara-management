<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="employeeDetailCanvas" aria-labelledby="employeeDetailCanvasLabel">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="employeeDetailCanvasLabel">
            <i class="bi bi-person-circle me-2"></i>Employee Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body">
        <!-- Profile Information -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-person-badge me-2"></i>Profile Information
            </h6>

            <div class="d-flex align-items-center">
                <div class="user-avatar me-3" id="detailEmployeeAvatar" style="width: 45px; height: 45px; font-size: 1rem;">JD</div>
                <div>
                    <h6 class="fw-semibold small mb-0" id="detailEmployeeName">Ahmed Ali</h6>
                    <small class="opacity-75 text-white small" id="detailEmployeeInfo">Sales - EMP-001</small>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Primary Employee Details -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-info-circle me-2"></i>Primary Employee Details
            </h6>

            <div class="row g-3">
                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">TAS ID</small>
                        <div class="fw-semibold small" id="detailTasId">-</div>
                    </div>
                </div>

                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Employee ID</small>
                        <div class="fw-semibold small" id="detailEmployeeId">-</div>
                    </div>
                </div>

                <!-- <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Employee No</small>
                        <div class="fw-semibold small" id="detailEmployeeNo">-</div>
                    </div>
                </div> -->

                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Verification Status</small>
                        <div class="fw-semibold small" id="detailVerificationStatus">-</div>
                    </div>
                </div>

                <!-- <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Summary</small>
                        <div class="fw-semibold small" id="detailSummary">-</div>
                    </div>
                </div> -->
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Organization Details -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-building me-2"></i>Organization Details
            </h6>

            <div class="row g-3">
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Organization</small>
                        <div class="fw-semibold small" id="detailOrganization">-</div>
                    </div>
                </div>

                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">SBU</small>
                        <div class="fw-semibold small" id="detailSbu">-</div>
                    </div>
                </div>

                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Department</small>
                        <div class="fw-semibold small" id="detailDepartment">-</div>
                    </div>
                </div>

                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Designation</small>
                        <div class="fw-semibold small" id="detailDesignation">-</div>
                    </div>
                </div>

                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Date of Joining</small>
                        <div class="fw-semibold small" id="detailDateOfJoining">-</div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Personal Details -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-person-lines-fill me-2"></i>Personal Details
            </h6>

            <div class="row g-3">
                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Category</small>
                        <div class="fw-semibold small" id="detailCategory">-</div>
                    </div>
                </div>

                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Gender</small>
                        <div class="fw-semibold small" id="detailGender">-</div>
                    </div>
                </div>

                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">CNIC</small>
                        <div class="fw-semibold small" id="detailCnic">-</div>
                    </div>
                </div>

                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Nationality</small>
                        <div class="fw-semibold small" id="detailNationality">-</div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Contact Information -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-envelope me-2"></i>Contact Information
            </h6>

            <div class="row g-3">
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Email</small>
                        <div class="fw-semibold small" id="detailEmail">-</div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Cell Number</small>
                        <div class="fw-semibold small" id="detailCellNumber">-</div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Employment / Assignment -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-briefcase me-2"></i>Employment / Assignment
            </h6>

            <div class="row g-3">
                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Category</small>
                        <div class="fw-semibold small" id="detailEmploymentType">-</div>
                    </div>
                </div>

                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Employee Type</small>
                        <div class="fw-semibold small" id="detailEmployeeType">-</div>
                    </div>
                </div>
<!-- 
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Site Assignment</small>
                        <div class="fw-semibold small" id="detailSiteAssignment">-</div>
                    </div>
                </div> -->

                <!-- <div class="col-12" id="detailVendorContainer" style="display: none;">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Vendor</small>
                        <div class="fw-semibold small" id="detailVendor">-</div>
                    </div>
                </div> -->

                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Floor Access</small>
                        <div class="fw-semibold small" id="detailFloorAccess">-</div>
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

            <div class="row g-3">
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Biometric ID</small>
                        <div class="fw-semibold small" id="detailBiometricId">-</div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Sync Status</small>
                        <div class="fw-semibold small" id="detailSyncStatusText">-</div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Sync Badge</small>
                        <div class="fw-semibold small" id="detailBiometricStatus">-</div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Current Status -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-activity me-2"></i>Status Information
            </h6>

            <div class="row g-3">
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Current Status</small>
                        <div class="fw-semibold small" id="detailCurrentStatus">-</div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Additional Information</small>
                        <ul class="list-unstyled mb-0 small opacity-75">
                            <li class="mb-1" id="detailStatusInfo1">-</li>
                            <li class="mb-1" id="detailStatusInfo2">-</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- User Account -->
        <!-- <div class="mb-4" id="userAccountSection">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-person-badge me-2"></i>User Account
            </h6>

            <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;" id="noUserAccountSection">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <small class="opacity-75 text-white d-block mb-2">Account Status</small>
                        <div class="fw-semibold">
                            <span class="badge px-3 py-2 rounded-1 bg-secondary">
                                <i class="bi bi-x-circle me-1"></i>No User Account
                            </span>
                        </div>
                    </div>
                </div>

                <div class="mt-3 pt-3 border-top" style="border-color: #ffffff1a !important;">
                    <small class="opacity-75 text-white d-block mb-2">This employee does not have a user account yet.</small>
                </div>
            </div>

            <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important; display: none;" id="hasUserAccountSection">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <small class="opacity-75 text-white d-block mb-2">Account Status</small>
                        <div class="fw-semibold">
                            <span class="badge px-3 py-2 rounded-1 bg-success">
                                <i class="bi bi-check-circle me-1"></i>User Account Active
                            </span>
                        </div>
                    </div>
                </div>

                <div class="mt-3 pt-3 border-top" style="border-color: #ffffff1a !important;">
                    <div class="row g-3">
                        <div class="col-12">
                            <small class="opacity-75 text-white d-block mb-2">Email</small>
                            <div class="fw-semibold small" id="userAccountEmail">-</div>
                        </div>

                        <div class="col-12">
                            <small class="opacity-75 text-white d-block mb-2">Role</small>
                            <div class="fw-semibold small" id="userAccountRole">-</div>
                        </div>

                        <div class="col-12">
                            <small class="opacity-75 text-white d-block mb-2">Last Login</small>
                            <div class="fw-semibold small" id="userAccountLastLogin">-</div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="button" class="btn btn-sm btn-outline-light me-2" id="editUserAccountBtn">
                            <i class="bi bi-pencil me-1"></i>Edit Account
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" id="deactivateUserAccountBtn">
                            <i class="bi bi-x-circle me-1"></i>Deactivate Account
                        </button>
                    </div>
                </div>
            </div>
        </div> -->

        <!-- Action Buttons -->
        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top" style="border-color: #ffffffab !important">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Close</button>
            <button type="button" class="btn btn-light text-dark border-0" id="editEmployeeBtn">
                <i class="bi bi-pencil me-1"></i>Edit Employee
            </button>
        </div>
    </div>
</div>
