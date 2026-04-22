<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="thirdPartyDetailCanvas" aria-labelledby="thirdPartyDetailCanvasLabel">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="thirdPartyDetailCanvasLabel">
            <i class="bi bi-building me-2"></i>Third Party Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-info-circle me-2"></i>Identity
            </h6>
            <div class="d-flex align-items-center mb-3">
                <div class="bg-light text-dark rounded-3 d-flex align-items-center justify-content-center fw-bold me-3" id="detailTpLogoPlaceholder" style="width: 60px; height: 60px; font-size: 1.25rem;">—</div>
                <div class="flex-grow-1">
                    <h6 class="fw-semibold small mb-1" id="detailTpName">—</h6>
                    <small class="opacity-75 text-white d-block" id="detailTpThirdPartyName">—</small>
                    <small class="opacity-75 text-white" id="detailTpCity">—</small>
                </div>
            </div>
        </div>
        <hr class="my-4" style="border-color: #ffffffab !important">
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-file-text me-2"></i>Basic Information
            </h6>
            <div class="row g-3">
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Organization</small>
                        <div class="fw-semibold small" id="detailTpOrganization">—</div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">SBUs</small>
                        <div class="fw-semibold small" id="detailTpSbus">—</div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Vendor ID</small>
                        <div class="fw-semibold small" id="detailTpVendorId">—</div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Service Type</small>
                        <div class="fw-semibold small" id="detailTpServiceType">—</div>
                    </div>
                </div>
                <div class="col-12 d-none" id="detailTpSpecifyServiceTypeRow">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Specify Service Type</small>
                        <div class="fw-semibold small" id="detailTpSpecifyServiceType">—</div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Vendor type</small>
                        <div class="fw-semibold small" id="detailTpVendorType">—</div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">NTN</small>
                        <div class="fw-semibold small" id="detailTpNtn">—</div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Contractor CNIC</small>
                        <div class="fw-semibold small" id="detailTpContractorCnic">—</div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Status</small>
                        <div class="fw-semibold small" id="detailTpStatus">—</div>
                    </div>
                </div>
            </div>
        </div>
        <hr class="my-4" style="border-color: #ffffffab !important">
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-person-lines-fill me-2"></i>Primary Contact
            </h6>
            <div class="row g-3">
                <div class="col-12"><div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;"><small class="opacity-75 text-white d-block mb-2">Contact Person Name</small><div class="fw-semibold small" id="detailTpContactPersonName">—</div></div></div>
                <div class="col-12"><div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;"><small class="opacity-75 text-white d-block mb-2">Mobile Number</small><div class="fw-semibold small" id="detailTpMobileNumber">—</div></div></div>
                <div class="col-12"><div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;"><small class="opacity-75 text-white d-block mb-2">Email Address</small><div class="fw-semibold small" id="detailTpEmail">—</div></div></div>
            </div>
        </div>
        <hr class="my-4" style="border-color: #ffffffab !important">
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-people me-2"></i>Operational Contact
            </h6>
            <div class="row g-3">
                <div class="col-12"><div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;"><small class="opacity-75 text-white d-block mb-2">Supervisor Name</small><div class="fw-semibold small" id="detailTpSupervisorName">—</div></div></div>
                <div class="col-12"><div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;"><small class="opacity-75 text-white d-block mb-2">Supervisor CNIC</small><div class="fw-semibold small" id="detailTpSupervisorCnic">—</div></div></div>
                <div class="col-12"><div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;"><small class="opacity-75 text-white d-block mb-2">Supervisor Mobile Number</small><div class="fw-semibold small" id="detailTpSupervisorMobile">—</div></div></div>
            </div>
        </div>
        <hr class="my-4" style="border-color: #ffffffab !important">
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-journal-text me-2"></i>Contract Details
            </h6>
            <div class="row g-3">
                <div class="col-12"><div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;"><small class="opacity-75 text-white d-block mb-2">Contract Start Date</small><div class="fw-semibold small" id="detailTpContractStartDate">—</div></div></div>
                <div class="col-12"><div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;"><small class="opacity-75 text-white d-block mb-2">Contract End Date</small><div class="fw-semibold small" id="detailTpContractEndDate">—</div></div></div>
                <div class="col-12"><div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;"><small class="opacity-75 text-white d-block mb-2">Scope of Work</small><div class="fw-semibold small" id="detailTpScopeOfWork">—</div></div></div>
                <div class="col-12"><div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;"><small class="opacity-75 text-white d-block mb-2">Estimated Staff Count</small><div class="fw-semibold small" id="detailTpEstimatedStaffCount">—</div></div></div>
            </div>
        </div>
        <hr class="my-4" style="border-color: #ffffffab !important">
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-shield-check me-2"></i>Compliance
            </h6>
            <div class="row g-3">
                <div class="col-12"><div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;"><small class="opacity-75 text-white d-block mb-2">Company Registration / ID Document</small><div class="fw-semibold small" id="detailTpCompanyDoc">—</div></div></div>
                <div class="col-12"><div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;"><small class="opacity-75 text-white d-block mb-2">Contract Copy</small><div class="fw-semibold small" id="detailTpContractDoc">—</div></div></div>
            </div>
        </div>
        <hr class="my-4" style="border-color: #ffffffab !important">
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-chat-square-text me-2"></i>Remarks
            </h6>
            <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                <div class="fw-semibold small" id="detailTpRemarks">—</div>
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top" style="border-color: #ffffffab !important">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Close</button>
        </div>
    </div>
</div>
