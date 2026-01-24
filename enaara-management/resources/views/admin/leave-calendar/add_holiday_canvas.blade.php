<!-- Add Holiday Canvas -->
<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="addHolidayCanvas" aria-labelledby="addHolidayCanvasLabel" style="width: 500px;">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="addHolidayCanvasLabel">
            <i class="bi bi-calendar-plus me-2"></i>Add Public Holiday
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="addHolidayForm">
            <!-- Holiday Name -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-info-circle me-2"></i>Holiday Information
                </h6>

                <div class="mb-3">
                    <label for="holidayName" class="form-label fw-semibold small text-white">Holiday Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="holidayName" placeholder="e.g., Independence Day" required>
                </div>

                <!-- Date Selection -->
                <div class="mb-3">
                    <label for="holidayDate" class="form-label fw-semibold small text-white">Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="holidayDate" required>
                </div>

                <!-- Recurring Option -->
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="isRecurring">
                    <label class="form-check-label text-white" for="isRecurring">
                        <strong>Recurring Holiday</strong>
                        <small class="d-block opacity-75">Repeat annually</small>
                    </label>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Organization Selection -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-building me-2"></i>Organization Scope
                </h6>

                <div class="mb-3">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="holidayScope" id="scopeAll" value="all" checked>
                        <label class="form-check-label text-white" for="scopeAll">
                            <strong>All Organizations</strong>
                            <small class="d-block opacity-75">Apply to entire group</small>
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="holidayScope" id="scopeSpecific" value="specific">
                        <label class="form-check-label text-white" for="scopeSpecific">
                            <strong>Specific Organization</strong>
                            <small class="d-block opacity-75">Select organization(s)</small>
                        </label>
                    </div>
                </div>

                <!-- Organization Select (shown when specific is selected) -->
                <div class="mb-3" id="organizationSelectSection" style="display: none;">
                    <label for="holidayOrganizations" class="form-label fw-semibold small text-white">Select Organizations</label>
                    <select class="form-select" id="holidayOrganizations" multiple>
                        <option value="1">Enaara Construction</option>
                        <option value="2">Enaara Properties</option>
                        <option value="3">Enaara Real Estate</option>
                    </select>
                    <small class="opacity-75 text-white">Hold Ctrl/Cmd to select multiple</small>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Blackout Date Option -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-x-circle me-2"></i>Blackout Date
                </h6>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="isBlackout">
                    <label class="form-check-label text-white" for="isBlackout">
                        <strong>Mark as Blackout Date</strong>
                        <small class="d-block opacity-75">No leave requests allowed on this date</small>
                    </label>
                </div>

                <div class="mb-3" id="blackoutReasonSection" style="display: none;">
                    <label for="blackoutReason" class="form-label fw-semibold small text-white">Reason</label>
                    <input type="text" class="form-control" id="blackoutReason" placeholder="e.g., Project Deadline, Quarter End">
                </div>
            </div>
        </form>
    </div>
    <div class="offcanvas-footer border-top p-3" style="border-color: #ffffffab !important">
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Cancel</button>
            <button type="button" class="btn btn-light text-dark border-0" id="saveHolidayBtn">
                <i class="bi bi-check-lg me-1"></i>Save Holiday
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const scopeAll = document.getElementById('scopeAll');
    const scopeSpecific = document.getElementById('scopeSpecific');
    const organizationSelectSection = document.getElementById('organizationSelectSection');
    const isBlackout = document.getElementById('isBlackout');
    const blackoutReasonSection = document.getElementById('blackoutReasonSection');
    const saveBtn = document.getElementById('saveHolidayBtn');

    // Show/hide organization select
    scopeAll.addEventListener('change', function() {
        if (this.checked) {
            organizationSelectSection.style.display = 'none';
        }
    });

    scopeSpecific.addEventListener('change', function() {
        if (this.checked) {
            organizationSelectSection.style.display = 'block';
        }
    });

    // Show/hide blackout reason
    isBlackout.addEventListener('change', function() {
        if (this.checked) {
            blackoutReasonSection.style.display = 'block';
        } else {
            blackoutReasonSection.style.display = 'none';
        }
    });

    // Reset form when offcanvas is hidden
    const addHolidayCanvas = document.getElementById('addHolidayCanvas');
    if (addHolidayCanvas) {
        addHolidayCanvas.addEventListener('hidden.bs.offcanvas', function() {
            document.getElementById('addHolidayForm').reset();
            organizationSelectSection.style.display = 'none';
            blackoutReasonSection.style.display = 'none';
        });
    }

    // Handle form submission
    if (saveBtn) {
        saveBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const form = document.getElementById('addHolidayForm');
            if (form && form.checkValidity()) {
                const formData = {
                    name: document.getElementById('holidayName').value,
                    date: document.getElementById('holidayDate').value,
                    isRecurring: document.getElementById('isRecurring').checked,
                    scope: document.querySelector('input[name="holidayScope"]:checked').value,
                    organizations: document.getElementById('holidayOrganizations').selectedOptions.length > 0 
                        ? Array.from(document.getElementById('holidayOrganizations').selectedOptions).map(opt => opt.value)
                        : [],
                    isBlackout: isBlackout.checked,
                    blackoutReason: document.getElementById('blackoutReason').value
                };
                
                console.log('Holiday data:', formData);
                // TODO: Implement API call to save holiday
                
                // Close offcanvas
                const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('addHolidayCanvas'));
                if (offcanvas) {
                    offcanvas.hide();
                }
            } else if (form) {
                form.reportValidity();
            }
        });
    }
});
</script>

