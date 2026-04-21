<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="outsourcedEmployeeDetailCanvas" aria-labelledby="outsourcedEmployeeDetailCanvasLabel">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="outsourcedEmployeeDetailCanvasLabel">
            <i class="bi bi-person-circle me-2"></i>Outsourced Employee Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-person-badge me-2"></i>Profile Information
            </h6>
            <div class="d-flex align-items-center">
                <img id="oeDetailPhoto" src="" alt="Employee Photo" class="rounded-circle border d-none me-3" style="width:45px;height:45px;object-fit:cover;border-color:#ffffff42 !important;">
                <div id="oeDetailPhotoPlaceholder" class="user-avatar me-3" style="width:45px;height:45px;font-size:1rem;">OE</div>
                <div>
                    <h6 class="fw-semibold small mb-0" id="oeDetailFullName">-</h6>
                    <small class="opacity-75 text-white small" id="oeDetailInfo">-</small>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-info-circle me-2"></i>Basic Information
            </h6>
            <div class="row g-3">
                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color:#ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">CNIC Number</small>
                        <div class="fw-semibold small" id="oeDetailCnic">-</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color:#ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Mobile Number</small>
                        <div class="fw-semibold small" id="oeDetailMobile">-</div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-building me-2"></i>Vendor Details
            </h6>
            <div class="row g-3">
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color:#ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Contractor Company</small>
                        <div class="fw-semibold small" id="oeDetailCompany">-</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color:#ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Supervisor Name</small>
                        <div class="fw-semibold small" id="oeDetailSupervisorName">-</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color:#ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Supervisor Contact</small>
                        <div class="fw-semibold small" id="oeDetailSupervisorContact">-</div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-briefcase me-2"></i>Work Details
            </h6>
            <div class="row g-3">
                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color:#ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Organization</small>
                        <div class="fw-semibold small" id="oeDetailOrganization">-</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color:#ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">SBU</small>
                        <div class="fw-semibold small" id="oeDetailSbu">-</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color:#ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Department</small>
                        <div class="fw-semibold small" id="oeDetailDepartment">-</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color:#ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Job Role / Trade</small>
                        <div class="fw-semibold small" id="oeDetailJobRole">-</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color:#ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Placement (Floor)</small>
                        <div class="fw-semibold small" id="oeDetailPlacementFloor">-</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color:#ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Date of Deployment</small>
                        <div class="fw-semibold small" id="oeDetailDeploymentDate">-</div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-fingerprint me-2"></i>Attendance
            </h6>
            <div class="row g-3">
                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color:#ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Biometric ID</small>
                        <div class="fw-semibold small" id="oeDetailBiometricId">-</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color:#ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Attendance Access</small>
                        <div class="fw-semibold small" id="oeDetailAttendanceAccess">-</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top" style="border-color: #ffffffab !important">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Close</button>
            <button type="button" class="btn btn-light text-dark border-0" id="oeDetailEditBtn">
                <i class="bi bi-pencil me-1"></i>Edit Employee
            </button>
        </div>
    </div>
</div>

