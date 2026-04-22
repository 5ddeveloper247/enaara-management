<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="editThirdPartyCanvas"
    aria-labelledby="editThirdPartyCanvasLabel" style="width: 600px;">

    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="editThirdPartyCanvasLabel">
            <i class="bi bi-pencil-square me-2"></i>Edit Third Party
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
            aria-label="Close"></button>
    </div>

    <div class="offcanvas-body">
        <form id="editThirdPartyForm" method="POST" action="javascript:void(0);" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="edit_id" name="id">

            <div class="mb-3">
                <label class="form-label fw-semibold small text-white">
                    Organizations <span class="text-danger">*</span>
                </label>
                <div id="editOrganizationHiddenInputs"></div>
                <div class="tp-ms-box" id="editOrganizationBox" data-field-box="organization_ids">
                    <div class="tp-ms-chips" id="editOrganizationChips"></div>
                    <span class="tp-ms-ph" id="editOrganizationPh">Select organizations...</span>
                    <svg class="tp-ms-chevron" width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="tp-ms-dropdown" id="editOrganizationDropdown">
                    <div class="tp-ms-search-row">
                        <input id="editOrganizationSearch" placeholder="Search organizations..." autocomplete="off">
                    </div>
                    <div class="tp-ms-opt-list" id="editOrganizationList"></div>
                </div>
                <small class="text-white-50">Select one or more organizations.</small>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small text-white">
                    SBUs <span class="text-danger">*</span>
                </label>
                <div id="editSbuHiddenInputs"></div>
                <div class="tp-ms-box" id="editSbuBox" data-field-box="sbu_ids">
                    <div class="tp-ms-chips" id="editSbuChips"></div>
                    <span class="tp-ms-ph" id="editSbuPh">First select the organization</span>
                    <svg class="tp-ms-chevron" width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="tp-ms-dropdown" id="editSbuDropdown">
                    <div class="tp-ms-search-row">
                        <input id="editSbuSearch" placeholder="Search SBUs..." autocomplete="off">
                    </div>
                    <div class="tp-ms-opt-list" id="editSbuList"></div>
                </div>
                <small class="text-white-50">SBUs are shown based on selected organizations.</small>
            </div>

            <hr class="my-4" style="border-color: #ffffff42 !important;">
            <h6 class="fw-semibold small text-white mb-3">1. Basic Information</h6>
            <div class="mb-3">
                <label for="edit_third_party_name" class="form-label fw-semibold small text-white">
                    Company Name <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="edit_third_party_name" name="third_party_name" placeholder="Enter company name" required>
            </div>
            <div class="mb-3">
                <label for="edit_service_type" class="form-label fw-semibold small text-white">
                    Service Type <span class="text-danger">*</span>
                </label>
                <select class="form-select" id="edit_service_type" name="service_type" required>
                    <option value="">Select service type</option>
                    <option value="Security">Security</option>
                    <option value="Housekeeping">Housekeeping</option>
                    <option value="Construction">Construction</option>
                    <option value="MEP">MEP</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="mb-3 d-none" id="editSpecifyServiceTypeWrap">
                <label for="edit_specify_service_type" class="form-label fw-semibold small text-white">
                    Specify Service Type <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="edit_specify_service_type" name="specify_service_type" maxlength="150" placeholder="Describe the service type" disabled>
            </div>
            <div class="mb-3">
                <label for="edit_is_individual_contractor" class="form-label fw-semibold small text-white">
                    Vendor type <span class="text-danger">*</span>
                </label>
                <select class="form-select" id="edit_is_individual_contractor" name="is_individual_contractor" required>
                    <option value="0">Registered company (NTN)</option>
                    <option value="1">Individual contractor (CNIC)</option>
                </select>
            </div>
            <div class="mb-3" id="edit_ntnWrap">
                <label for="edit_ntn" class="form-label fw-semibold small text-white">
                    NTN number <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control tp-ntn-field" id="edit_ntn" name="ntn" inputmode="numeric" autocomplete="off" maxlength="13" placeholder="5–13 digits">
            </div>
            <div class="mb-3 d-none" id="edit_contractorCnicWrap">
                <label for="edit_contractor_cnic" class="form-label fw-semibold small text-white">
                    Contractor CNIC <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control tp-cnic-field" id="edit_contractor_cnic" name="contractor_cnic" inputmode="numeric" autocomplete="off" maxlength="17" placeholder="00000-0000000-0">
            </div>

            <hr class="my-4" style="border-color: #ffffff42 !important;">
            <h6 class="fw-semibold small text-white mb-3">2. Primary Contact</h6>
            <div class="mb-3">
                <label for="edit_contact_person_name" class="form-label fw-semibold small text-white">
                    Contact Person Name <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="edit_contact_person_name" name="contact_person_name" placeholder="Enter contact person name" required>
            </div>
            <div class="mb-3">
                <label for="edit_mobile_number" class="form-label fw-semibold small text-white">
                    Mobile Number <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="edit_mobile_number" name="mobile_number" placeholder="03XXXXXXXXX" required>
            </div>
            <div class="mb-3">
                <label for="edit_email" class="form-label fw-semibold small text-white">
                    Email Address <span class="text-danger">*</span>
                </label>
                <input type="email" class="form-control" id="edit_email" name="email" placeholder="Enter email address" required>
            </div>

            <hr class="my-4" style="border-color: #ffffff42 !important;">
            <h6 class="fw-semibold small text-white mb-3">3. Operational Contact</h6>
            <div class="mb-3">
                <label for="edit_supervisor_name" class="form-label fw-semibold small text-white">
                    Supervisor Name <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="edit_supervisor_name" name="supervisor_name" placeholder="Enter supervisor name" required>
            </div>
            <div class="mb-3">
                <label for="edit_supervisor_cnic" class="form-label fw-semibold small text-white">
                    Supervisor CNIC <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control tp-cnic-field" id="edit_supervisor_cnic" name="supervisor_cnic" placeholder="00000-0000000-0" maxlength="17" required>
            </div>
            <div class="mb-3">
                <label for="edit_supervisor_mobile_number" class="form-label fw-semibold small text-white">
                    Supervisor Mobile Number <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="edit_supervisor_mobile_number" name="supervisor_mobile_number" placeholder="03XXXXXXXXX" required>
            </div>

            <hr class="my-4" style="border-color: #ffffff42 !important;">
            <h6 class="fw-semibold small text-white mb-3">4. Contract Details</h6>
            <div class="mb-3">
                <label for="edit_contract_start_date" class="form-label fw-semibold small text-white">
                    Contract Start Date <span class="text-danger">*</span>
                </label>
                <input type="date" class="form-control" id="edit_contract_start_date" name="contract_start_date" required>
            </div>
            <div class="mb-3">
                <label for="edit_contract_end_date" class="form-label fw-semibold small text-white">
                    Contract End Date <span class="text-danger">*</span>
                </label>
                <input type="date" class="form-control" id="edit_contract_end_date" name="contract_end_date" required>
            </div>
            <div class="mb-3">
                <label for="edit_scope_of_work" class="form-label fw-semibold small text-white">
                    Scope of Work (Short Description) <span class="text-danger">*</span>
                </label>
                <textarea class="form-control" id="edit_scope_of_work" name="scope_of_work" rows="3" placeholder="Enter scope of work" required></textarea>
            </div>
            <div class="mb-3">
                <label for="edit_estimated_staff_count" class="form-label fw-semibold small text-white">
                    Estimated Staff Count <span class="text-danger">*</span>
                </label>
                <input type="number" class="form-control" id="edit_estimated_staff_count" name="estimated_staff_count" min="1" step="1" placeholder="Enter estimated staff count" required>
            </div>

            <hr class="my-4" style="border-color: #ffffff42 !important;">
            <h6 class="fw-semibold small text-white mb-3">6. Compliance (Basic Only)</h6>
            <div class="mb-3">
                <label for="edit_company_registration_document" class="form-label fw-semibold small text-white">
                    Company Registration / ID Document
                </label>
                <input type="file" class="form-control" id="edit_company_registration_document" name="company_registration_document" accept=".pdf,.jpg,.jpeg,.png,.webp">
                <small class="text-white-50 d-block mt-1" id="edit_company_registration_document_link"></small>
            </div>
            <div class="mb-3">
                <label for="edit_contract_copy" class="form-label fw-semibold small text-white">
                    Contract Copy
                </label>
                <input type="file" class="form-control" id="edit_contract_copy" name="contract_copy" accept=".pdf,.jpg,.jpeg,.png,.webp">
                <small class="text-white-50 d-block mt-1" id="edit_contract_copy_link"></small>
            </div>

            <hr class="my-4" style="border-color: #ffffff42 !important;">
            <h6 class="fw-semibold small text-white mb-3">7. System Control</h6>
            <div class="mb-3">
                <label for="edit_vendor_id" class="form-label fw-semibold small text-white">
                    Vendor ID (Auto)
                </label>
                <input type="text" class="form-control" id="edit_vendor_id" value="" readonly>
            </div>

            <div class="mb-3">
                <label for="edit_is_active" class="form-label fw-semibold small text-white">
                    Status <span class="text-danger">*</span>
                </label>
                <select class="form-select" id="edit_is_active" name="is_active" required>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="edit_remarks" class="form-label fw-semibold small text-white">
                    Remarks (Optional)
                </label>
                <textarea class="form-control" id="edit_remarks" name="remarks" rows="2" placeholder="Enter remarks"></textarea>
            </div>
        </form>
    </div>

    <div class="offcanvas-footer border-top p-3" style="border-color: #ffffffab !important">
        <div class="d-flex justify-content-end align-items-center gap-2">
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="button" class="btn btn-light text-dark border-0" id="updateThirdPartyBtn">
                    <i class="bi bi-check-lg me-1"></i>Update Third Party
                </button>
            </div>
        </div>
    </div>
</div>
