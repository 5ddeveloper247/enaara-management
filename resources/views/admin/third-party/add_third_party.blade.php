<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="addThirdPartyCanvas"
    aria-labelledby="addThirdPartyCanvasLabel" style="width: 600px;">

    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="addThirdPartyCanvasLabel">
            <i class="bi bi-building-add me-2"></i>Add New Third Party
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
            aria-label="Close"></button>
    </div>

    <div class="offcanvas-body">
        <form id="addThirdPartyForm" data-store-url="{{ route('admin.third-party.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-semibold small text-white">
                    Organizations <span class="text-danger">*</span>
                </label>
                <div id="addOrganizationHiddenInputs"></div>
                <div class="tp-ms-box" id="addOrganizationBox" data-field-box="organization_ids">
                    <div class="tp-ms-chips" id="addOrganizationChips"></div>
                    <span class="tp-ms-ph" id="addOrganizationPh">Select organizations...</span>
                    <svg class="tp-ms-chevron" width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="tp-ms-dropdown" id="addOrganizationDropdown">
                    <div class="tp-ms-search-row">
                        <input id="addOrganizationSearch" placeholder="Search organizations..." autocomplete="off">
                    </div>
                    <div class="tp-ms-opt-list" id="addOrganizationList"></div>
                </div>
                <small class="text-white-50">Select one or more organizations.</small>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small text-white">
                    SBUs <span class="text-danger">*</span>
                </label>
                <div id="addSbuHiddenInputs"></div>
                <div class="tp-ms-box" id="addSbuBox" data-field-box="sbu_ids">
                    <div class="tp-ms-chips" id="addSbuChips"></div>
                    <span class="tp-ms-ph" id="addSbuPh">First select the organization</span>
                    <svg class="tp-ms-chevron" width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="tp-ms-dropdown" id="addSbuDropdown">
                    <div class="tp-ms-search-row">
                        <input id="addSbuSearch" placeholder="Search SBUs..." autocomplete="off">
                    </div>
                    <div class="tp-ms-opt-list" id="addSbuList"></div>
                </div>
                <small class="text-white-50">SBUs are shown based on selected organizations.</small>
            </div>

            <hr class="my-4" style="border-color: #ffffff42 !important;">
            <h6 class="fw-semibold small text-white mb-3">1. Basic Information</h6>
            <div class="mb-3">
                <label for="third_party_name" class="form-label fw-semibold small text-white">
                    Company Name <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="third_party_name" name="third_party_name" placeholder="Enter company name" required>
            </div>
            <div class="mb-3">
                <label for="service_type" class="form-label fw-semibold small text-white">
                    Service Type <span class="text-danger">*</span>
                </label>
                <select class="form-select" id="service_type" name="service_type" required>
                    <option value="">Select service type</option>
                    <option value="Security">Security</option>
                    <option value="Housekeeping">Housekeeping</option>
                    <option value="Construction">Construction</option>
                    <option value="MEP">MEP</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="mb-3 d-none" id="specifyServiceTypeWrap">
                <label for="specify_service_type" class="form-label fw-semibold small text-white">
                    Specify Service Type <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="specify_service_type" name="specify_service_type" maxlength="150" placeholder="Describe the service type" disabled>
            </div>
            <div class="mb-3">
                <label for="is_individual_contractor" class="form-label fw-semibold small text-white">
                    Vendor type <span class="text-danger">*</span>
                </label>
                <select class="form-select" id="is_individual_contractor" name="is_individual_contractor" required>
                    <option value="0" selected>Registered company (NTN)</option>
                    <option value="1">Individual contractor (CNIC)</option>
                </select>
            </div>
            <div class="mb-3" id="ntnWrap">
                <label for="ntn" class="form-label fw-semibold small text-white">
                    NTN number <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control tp-ntn-field" id="ntn" name="ntn" inputmode="numeric" autocomplete="off" maxlength="13" placeholder="5–13 digits">
            </div>
            <div class="mb-3 d-none" id="contractorCnicWrap">
                <label for="contractor_cnic" class="form-label fw-semibold small text-white">
                    Contractor CNIC <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control tp-cnic-field" id="contractor_cnic" name="contractor_cnic" inputmode="numeric" autocomplete="off" maxlength="17" placeholder="00000-0000000-0">
            </div>

            <hr class="my-4" style="border-color: #ffffff42 !important;">
            <h6 class="fw-semibold small text-white mb-3">2. Primary Contact</h6>
            <div class="mb-3">
                <label for="contact_person_name" class="form-label fw-semibold small text-white">
                    Contact Person Name <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="contact_person_name" name="contact_person_name" placeholder="Enter contact person name" required>
            </div>
            <div class="mb-3">
                <label for="mobile_number" class="form-label fw-semibold small text-white">
                    Mobile Number <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="mobile_number" name="mobile_number" placeholder="03XXXXXXXXX" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label fw-semibold small text-white">
                    Email Address <span class="text-danger">*</span>
                </label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter email address" required>
            </div>

            <hr class="my-4" style="border-color: #ffffff42 !important;">
            <h6 class="fw-semibold small text-white mb-3">3. Operational Contact</h6>
            <div class="mb-3">
                <label for="supervisor_name" class="form-label fw-semibold small text-white">
                    Supervisor Name <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="supervisor_name" name="supervisor_name" placeholder="Enter supervisor name" required>
            </div>
            <div class="mb-3">
                <label for="supervisor_cnic" class="form-label fw-semibold small text-white">
                    Supervisor CNIC <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control tp-cnic-field" id="supervisor_cnic" name="supervisor_cnic" placeholder="00000-0000000-0" maxlength="17" required>
            </div>
            <div class="mb-3">
                <label for="supervisor_mobile_number" class="form-label fw-semibold small text-white">
                    Supervisor Mobile Number <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="supervisor_mobile_number" name="supervisor_mobile_number" placeholder="03XXXXXXXXX" required>
            </div>

            <hr class="my-4" style="border-color: #ffffff42 !important;">
            <h6 class="fw-semibold small text-white mb-3">4. Contract Details</h6>
            <div class="mb-3">
                <label for="contract_start_date" class="form-label fw-semibold small text-white">
                    Contract Start Date <span class="text-danger">*</span>
                </label>
                <input type="date" class="form-control" id="contract_start_date" name="contract_start_date" required>
            </div>
            <div class="mb-3">
                <label for="contract_end_date" class="form-label fw-semibold small text-white">
                    Contract End Date <span class="text-danger">*</span>
                </label>
                <input type="date" class="form-control" id="contract_end_date" name="contract_end_date" required>
            </div>
            <div class="mb-3">
                <label for="scope_of_work" class="form-label fw-semibold small text-white">
                    Scope of Work (Short Description) <span class="text-danger">*</span>
                </label>
                <textarea class="form-control" id="scope_of_work" name="scope_of_work" rows="3" placeholder="Enter scope of work" required></textarea>
            </div>
            <div class="mb-3">
                <label for="estimated_staff_count" class="form-label fw-semibold small text-white">
                    Estimated Staff Count <span class="text-danger">*</span>
                </label>
                <input type="number" class="form-control" id="estimated_staff_count" name="estimated_staff_count" min="1" step="1" placeholder="Enter estimated staff count" required>
            </div>

            <hr class="my-4" style="border-color: #ffffff42 !important;">
            <h6 class="fw-semibold small text-white mb-3">6. Compliance (Basic Only)</h6>
            <div class="mb-3">
                <label for="company_registration_document" class="form-label fw-semibold small text-white">
                    Company Registration / ID Document
                </label>
                <input type="file" class="form-control" id="company_registration_document" name="company_registration_document" accept=".pdf,.jpg,.jpeg,.png,.webp">
            </div>
            <div class="mb-3">
                <label for="contract_copy" class="form-label fw-semibold small text-white">
                    Contract Copy
                </label>
                <input type="file" class="form-control" id="contract_copy" name="contract_copy" accept=".pdf,.jpg,.jpeg,.png,.webp">
            </div>

            <hr class="my-4" style="border-color: #ffffff42 !important;">
            <h6 class="fw-semibold small text-white mb-3">7. System Control</h6>
            <div class="mb-3">
                <label for="vendor_id_display" class="form-label fw-semibold small text-white">
                    Vendor ID (Auto)
                </label>
                <input type="text" class="form-control" id="vendor_id_display" value="Auto-generated after save" readonly>
            </div>

            <div class="mb-3">
                <label for="is_active" class="form-label fw-semibold small text-white">
                    Status <span class="text-danger">*</span>
                </label>
                <select class="form-select" id="is_active" name="is_active" required>
                    <option value="1" selected>Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="remarks" class="form-label fw-semibold small text-white">
                    Remarks (Optional)
                </label>
                <textarea class="form-control" id="remarks" name="remarks" rows="2" placeholder="Enter remarks"></textarea>
            </div>
        </form>
    </div>

    <div class="offcanvas-footer border-top p-3" style="border-color: #ffffffab !important">
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Cancel</button>
            <button type="button" class="btn btn-light text-dark border-0" id="saveThirdPartyBtn">
                <i class="bi bi-check-lg me-1"></i>Create Third Party
            </button>
        </div>
    </div>
</div>
