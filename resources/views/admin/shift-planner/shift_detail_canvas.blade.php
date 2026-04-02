<!-- Shift Detail Canvas -->
<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="shiftDetailCanvas" aria-labelledby="shiftDetailCanvasLabel">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="shiftDetailCanvasLabel">
            <i class="bi bi-clock-history me-2"></i>Shift Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <!-- Shift Name -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-info-circle me-2"></i>Shift Information
            </h6>
            <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                <small class="opacity-75 text-white d-block mb-2">Shift Name</small>
                <div class="fw-semibold fs-5" id="detailShiftName">Morning Shift</div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Shift Timing -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-clock me-2"></i>Shift Timing
            </h6>
            <div class="row g-3">
                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Start Time</small>
                        <div class="fw-semibold" id="detailShiftStart">09:00</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">End Time</small>
                        <div class="fw-semibold" id="detailShiftEnd">18:00</div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Clock-in/Out Window -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-calendar-check me-2"></i>Clock-in/Out Window
            </h6>
            <div class="row g-3">
                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Clock-in Window</small>
                        <div class="fw-semibold" id="detailClockInWindow">08:30 - 09:00</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Clock-out Window</small>
                        <div class="fw-semibold" id="detailClockOutWindow">18:00 - 18:30</div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Grace Period & Break Time -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-hourglass-split me-2"></i>Grace Period & Break
            </h6>
            <div class="row g-3">
                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Grace Period</small>
                        <div class="fw-semibold" id="detailGracePeriod">15 mins</div>
                        <small class="opacity-50 text-white">Late tolerance before flag</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Break Time</small>
                        <div class="fw-semibold" id="detailBreakTime">60 mins</div>
                        <small class="opacity-50 text-white">Auto-deducted</small>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Overtime Settings -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-arrow-repeat me-2"></i>Overtime Settings
            </h6>
            <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <small class="opacity-75 text-white d-block mb-2">Overtime Allowed</small>
                        <div class="fw-semibold" id="detailOTAllowed">Yes</div>
                    </div>
                    <div id="detailOTBadge">
                        <span class="badge px-3 py-2 rounded-1 bg-success">
                            <i class="bi bi-check-circle me-1"></i>Enabled
                        </span>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-top" style="border-color: #ffffff1a !important;" id="detailOTTriggerSection">
                    <small class="opacity-75 text-white d-block mb-2">Overtime Trigger</small>
                    <div class="fw-semibold" id="detailOTTrigger">After 8 hours</div>
                    <small class="opacity-50 text-white">OT calculation starts after this duration</small>
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
                        <small class="opacity-75 text-white d-block mb-2">Assigned Employees</small>
                        <div class="fw-bold fs-5" id="detailAssignedEmployees">45</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Total Hours</small>
                        <div class="fw-bold fs-5" id="detailTotalHours">9h</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top" style="border-color: #ffffffab !important">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Close</button>
            <button type="button" class="btn btn-outline-danger delete-shift-btn" id="deleteShiftFromDetailBtn" data-shift-id="">
                <i class="bi bi-trash me-1"></i>Delete Shift
            </button>
            <button type="button" class="btn btn-light text-dark border-0" id="editShiftFromDetailBtn">
                <i class="bi bi-pencil me-1"></i>Edit Shift
            </button>
        </div>
    </div>
</div>

<script>
window.populateShiftDetail = function(card) {
    if (!card) return;

    // Extract data from card data attributes
    const shiftData = {
        shiftId: card.getAttribute('data-shift-id') || '-',
        shiftName: card.getAttribute('data-shift-name') || '-',
        shiftStart: card.getAttribute('data-shift-start') || '-',
        shiftEnd: card.getAttribute('data-shift-end') || '-',
        clockInWindow: card.getAttribute('data-clock-in-window') || '-',
        clockOutWindow: card.getAttribute('data-clock-out-window') || '-',
        gracePeriod: card.getAttribute('data-grace-period') || '0',
        breakTime: card.getAttribute('data-break-time') || '0',
        overtimeAllowed: card.getAttribute('data-overtime-allowed') === 'true',
        overtimeTrigger: card.getAttribute('data-overtime-trigger') || '0'
    };

    // Populate shift information
    const detailShiftName = document.getElementById('detailShiftName');
    const detailShiftStart = document.getElementById('detailShiftStart');
    const detailShiftEnd = document.getElementById('detailShiftEnd');
    const detailClockInWindow = document.getElementById('detailClockInWindow');
    const detailClockOutWindow = document.getElementById('detailClockOutWindow');
    const detailGracePeriod = document.getElementById('detailGracePeriod');
    const detailBreakTime = document.getElementById('detailBreakTime');
    const detailOTAllowed = document.getElementById('detailOTAllowed');
    const detailOTBadge = document.getElementById('detailOTBadge');
    const detailOTTriggerSection = document.getElementById('detailOTTriggerSection');
    const detailOTTrigger = document.getElementById('detailOTTrigger');
    const detailTotalHours = document.getElementById('detailTotalHours');
    const editShiftFromDetailBtn = document.getElementById('editShiftFromDetailBtn');
    const deleteShiftFromDetailBtn = document.getElementById('deleteShiftFromDetailBtn');

    if (detailShiftName) detailShiftName.textContent = shiftData.shiftName;
    if (detailShiftStart) detailShiftStart.textContent = shiftData.shiftStart;
    if (detailShiftEnd) detailShiftEnd.textContent = shiftData.shiftEnd;
    if (detailClockInWindow) detailClockInWindow.textContent = shiftData.clockInWindow + ' - ' + shiftData.shiftStart;
    if (detailClockOutWindow) detailClockOutWindow.textContent = shiftData.shiftEnd + ' - ' + shiftData.clockOutWindow;
    if (detailGracePeriod) detailGracePeriod.textContent = shiftData.gracePeriod + ' mins';
    if (detailBreakTime) detailBreakTime.textContent = shiftData.breakTime + ' mins';

    // Overtime settings
    if (shiftData.overtimeAllowed) {
        if (detailOTAllowed) detailOTAllowed.textContent = 'Yes';
        if (detailOTBadge) detailOTBadge.innerHTML = '<span class="badge px-3 py-2 rounded-1 bg-success"><i class="bi bi-check-circle me-1"></i>Enabled</span>';
        if (detailOTTriggerSection) detailOTTriggerSection.style.display = 'block';
        if (detailOTTrigger) detailOTTrigger.textContent = 'After ' + shiftData.overtimeTrigger + ' hours';
    } else {
        if (detailOTAllowed) detailOTAllowed.textContent = 'No';
        if (detailOTBadge) detailOTBadge.innerHTML = '<span class="badge px-3 py-2 rounded-1 bg-secondary"><i class="bi bi-x-circle me-1"></i>Disabled</span>';
        if (detailOTTriggerSection) detailOTTriggerSection.style.display = 'none';
    }

    // Calculate total hours
    const start = shiftData.shiftStart.split(':');
    const end = shiftData.shiftEnd.split(':');
    let startHour = parseInt(start[0]);
    let endHour = parseInt(end[0]);
    
    // Handle overnight shifts
    if (endHour < startHour) {
        endHour += 24;
    }
    
    const totalHours = endHour - startHour;
    if (detailTotalHours) detailTotalHours.textContent = totalHours + 'h';

    // Store shift ID for edit and delete buttons
    if (editShiftFromDetailBtn) editShiftFromDetailBtn.setAttribute('data-shift-id', shiftData.shiftId);
    if (deleteShiftFromDetailBtn) deleteShiftFromDetailBtn.setAttribute('data-shift-id', shiftData.shiftId);
};

document.addEventListener('DOMContentLoaded', function() {
    // Shift Detail Canvas Handler
    const shiftDetailCanvas = document.getElementById('shiftDetailCanvas');
    if (shiftDetailCanvas) {
        shiftDetailCanvas.addEventListener('show.bs.offcanvas', function(event) {
            let card = event.relatedTarget;
            
            if (card && !card.classList.contains('shift-card')) {
                card = card.closest('.shift-card');
            }
            
            if (card) {
                window.populateShiftDetail(card);
            }
        });
    }

    // Handle edit from detail canvas
    const editShiftFromDetailBtn = document.getElementById('editShiftFromDetailBtn');
    if (editShiftFromDetailBtn) {
        editShiftFromDetailBtn.addEventListener('click', function() {
            const shiftId = this.getAttribute('data-shift-id');
            // Close detail canvas and open edit canvas
            const detailCanvas = bootstrap.Offcanvas.getInstance(document.getElementById('shiftDetailCanvas'));
            if (detailCanvas) {
                detailCanvas.hide();
            }
            // Trigger edit canvas with shift data
            setTimeout(function() {
                // This will be handled by the edit button click handler
            }, 300);
        });
    }
});
</script>

