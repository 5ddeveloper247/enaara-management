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
        <form id="editThirdPartyForm" method="POST" action="javascript:void(0);">
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

            <div class="mb-3">
                <label for="edit_third_party_name" class="form-label fw-semibold small text-white">
                    Third Party Name <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="edit_third_party_name" name="third_party_name" placeholder="Enter third party company name" required>
            </div>

            <div class="mb-3">
                <label for="edit_city" class="form-label fw-semibold small text-white">
                    City
                </label>
                <input type="text" class="form-control" id="edit_city" name="city" placeholder="Enter city">
            </div>

            <div class="mb-3">
                <label for="edit_address" class="form-label fw-semibold small text-white">
                    Address
                </label>
                <textarea class="form-control" id="edit_address" name="address" rows="3" placeholder="Enter address"></textarea>
            </div>

            <div class="mb-3">
                <label for="edit_latitude" class="form-label fw-semibold small text-white">
                    Latitude
                </label>
                <input type="number" step="0.00000001" class="form-control" id="edit_latitude" name="latitude"
                    placeholder="e.g. 33.68442020">
            </div>

            <div class="mb-3">
                <label for="edit_longitude" class="form-label fw-semibold small text-white">
                    Longitude
                </label>
                <input type="number" step="0.00000001" class="form-control" id="edit_longitude" name="longitude"
                    placeholder="e.g. 73.04788480">
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
