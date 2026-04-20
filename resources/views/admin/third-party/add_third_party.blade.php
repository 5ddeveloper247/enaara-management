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
        <form id="addThirdPartyForm" data-store-url="{{ route('admin.third-party.store') }}">
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

            <div class="mb-3">
                <label for="third_party_name" class="form-label fw-semibold small text-white">
                    Third Party Name <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="third_party_name" name="third_party_name" placeholder="Enter third party company name" required>
            </div>

            <div class="mb-3">
                <label for="city" class="form-label fw-semibold small text-white">
                    City
                </label>
                <input type="text" class="form-control" id="city" name="city" placeholder="Enter city">
            </div>

            <div class="mb-3">
                <label for="address" class="form-label fw-semibold small text-white">
                    Address
                </label>
                <textarea class="form-control" id="address" name="address" rows="3" placeholder="Enter address"></textarea>
            </div>

            <div class="mb-3">
                <label for="latitude" class="form-label fw-semibold small text-white">
                    Latitude
                </label>
                <input type="number" step="0.00000001" class="form-control" id="latitude" name="latitude"
                    placeholder="e.g. 33.68442020">
            </div>

            <div class="mb-3">
                <label for="longitude" class="form-label fw-semibold small text-white">
                    Longitude
                </label>
                <input type="number" step="0.00000001" class="form-control" id="longitude" name="longitude"
                    placeholder="e.g. 73.04788480">
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
