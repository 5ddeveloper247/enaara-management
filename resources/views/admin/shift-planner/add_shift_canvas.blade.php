<!-- Add/Edit Shift Canvas -->
<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="addShiftCanvas" aria-labelledby="addShiftCanvasLabel" style="width: 600px;">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="addShiftCanvasLabel">
            <i class="bi bi-plus-circle me-2"></i>Add New Shift
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="addShiftForm">
            <!-- Shift Name -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-info-circle me-2"></i>Basic Information
                </h6>
                
                <div class="mb-3">
                    <label for="shiftName" class="form-label fw-semibold small text-white">Shift Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="shiftName" placeholder="e.g., Morning Shift, Night Shift" required>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Shift Timing -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-clock me-2"></i>Shift Timing
                </h6>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label for="shiftStartTime" class="form-label fw-semibold small text-white">Start Time <span class="text-danger">*</span></label>
                        <input type="time" class="form-control" id="shiftStartTime" value="09:00" required>
                    </div>
                    <div class="col-6">
                        <label for="shiftEndTime" class="form-label fw-semibold small text-white">End Time <span class="text-danger">*</span></label>
                        <input type="time" class="form-control" id="shiftEndTime" value="18:00" required>
                    </div>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Clock-in/Out Window -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-calendar-check me-2"></i>Clock-in/Out Window
                </h6>

                <div class="mb-3">
                    <label for="clockInWindow" class="form-label fw-semibold small text-white">Clock-in Window (minutes before start) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="clockInWindow" min="0" max="120" value="30" required>
                    <small class="opacity-75 text-white">Employees can clock-in this many minutes before shift starts</small>
                </div>

                <div class="mb-3">
                    <label for="clockOutWindow" class="form-label fw-semibold small text-white">Clock-out Window (minutes after end) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="clockOutWindow" min="0" max="120" value="30" required>
                    <small class="opacity-75 text-white">Employees can clock-out this many minutes after shift ends</small>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Grace Period & Break Time -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-hourglass-split me-2"></i>Grace Period & Break
                </h6>

                <div class="mb-3">
                    <label for="gracePeriod" class="form-label fw-semibold small text-white">Grace Period (minutes) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="gracePeriod" min="0" max="60" value="15" required>
                    <small class="opacity-75 text-white">How many minutes late is acceptable before flagging in Daily Logs</small>
                </div>

                <div class="mb-3">
                    <label for="breakTime" class="form-label fw-semibold small text-white">Break Time (minutes) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="breakTime" min="0" max="180" value="60" required>
                    <small class="opacity-75 text-white">Automatically deducted from total working hours</small>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Overtime Settings -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-arrow-repeat me-2"></i>Overtime Settings
                </h6>

                <div class="mb-3">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="overtimeAllowed" checked>
                        <label class="form-check-label text-white" for="overtimeAllowed">
                            Allow Overtime
                        </label>
                    </div>
                </div>

                <div id="overtimeTriggerSection">
                    <div class="mb-3">
                        <label for="overtimeTrigger" class="form-label fw-semibold small text-white">Overtime Trigger (hours) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="overtimeTrigger" min="1" max="12" value="8" step="0.5" required>
                        <small class="opacity-75 text-white">OT calculation starts after this many hours</small>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="offcanvas-footer border-top p-3" style="border-color: #ffffffab !important">
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Cancel</button>
            <button type="button" class="btn btn-light text-dark border-0" id="saveShiftBtn">
                <i class="bi bi-check-lg me-1"></i>Save Shift
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle overtime toggle
    const overtimeAllowed = document.getElementById('overtimeAllowed');
    const overtimeTriggerSection = document.getElementById('overtimeTriggerSection');
    const overtimeTrigger = document.getElementById('overtimeTrigger');
    
    if (overtimeAllowed && overtimeTriggerSection) {
        overtimeAllowed.addEventListener('change', function() {
            if (this.checked) {
                overtimeTriggerSection.style.display = 'block';
                overtimeTrigger.required = true;
            } else {
                overtimeTriggerSection.style.display = 'none';
                overtimeTrigger.required = false;
            }
        });
    }

    // Handle canvas show event for edit mode
    const addShiftCanvas = document.getElementById('addShiftCanvas');
    if (addShiftCanvas) {
        addShiftCanvas.addEventListener('show.bs.offcanvas', function(event) {
            const button = event.relatedTarget;
            if (button && button.classList.contains('edit-shift-btn')) {
                const mode = button.getAttribute('data-mode');
                const shiftId = button.getAttribute('data-shift-id');
                
                if (mode === 'edit') {
                    document.getElementById('addShiftCanvasLabel').innerHTML = '<i class="bi bi-pencil me-2"></i>Edit Shift';
                    document.getElementById('saveShiftBtn').innerHTML = '<i class="bi bi-check-lg me-1"></i>Update Shift';
                    
                    // Load shift data (this would come from API)
                    // For now, we'll just set the mode
                    document.getElementById('addShiftForm').setAttribute('data-shift-id', shiftId);
                } else {
                    document.getElementById('addShiftCanvasLabel').innerHTML = '<i class="bi bi-plus-circle me-2"></i>Add New Shift';
                    document.getElementById('saveShiftBtn').innerHTML = '<i class="bi bi-check-lg me-1"></i>Save Shift';
                    document.getElementById('addShiftForm').reset();
                }
            } else {
                // New shift mode
                document.getElementById('addShiftCanvasLabel').innerHTML = '<i class="bi bi-plus-circle me-2"></i>Add New Shift';
                document.getElementById('saveShiftBtn').innerHTML = '<i class="bi bi-check-lg me-1"></i>Save Shift';
                document.getElementById('addShiftForm').reset();
            }
        });

        // Reset form when canvas is hidden
        addShiftCanvas.addEventListener('hidden.bs.offcanvas', function() {
            document.getElementById('addShiftForm').reset();
            overtimeTriggerSection.style.display = 'block';
            overtimeTrigger.required = true;
        });
    }

    // Handle form submission
    const saveBtn = document.getElementById('saveShiftBtn');
    if (saveBtn) {
        saveBtn.addEventListener('click', function() {
            const form = document.getElementById('addShiftForm');
            if (form.checkValidity()) {
                const formData = {
                    name: document.getElementById('shiftName').value,
                    startTime: document.getElementById('shiftStartTime').value,
                    endTime: document.getElementById('shiftEndTime').value,
                    clockInWindow: document.getElementById('clockInWindow').value,
                    clockOutWindow: document.getElementById('clockOutWindow').value,
                    gracePeriod: document.getElementById('gracePeriod').value,
                    breakTime: document.getElementById('breakTime').value,
                    overtimeAllowed: document.getElementById('overtimeAllowed').checked,
                    overtimeTrigger: document.getElementById('overtimeTrigger').value
                };
                
                const shiftId = form.getAttribute('data-shift-id');
                if (shiftId) {
                    formData.id = shiftId;
                }
                
                // Frontend view only - no API call
                
                // Close canvas after save
                // const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('addShiftCanvas'));
                // if (offcanvas) {
                //     offcanvas.hide();
                // }
            } else {
                form.reportValidity();
            }
        });
    }
});
</script>

