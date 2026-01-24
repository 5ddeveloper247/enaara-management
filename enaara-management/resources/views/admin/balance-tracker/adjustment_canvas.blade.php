<!-- Manual Adjustment Canvas -->
<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="adjustmentCanvas" aria-labelledby="adjustmentCanvasLabel" style="width: 500px;">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="adjustmentCanvasLabel">
            <i class="bi bi-pencil-square me-2"></i>Manual Balance Adjustment
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="adjustmentForm">
            <input type="hidden" id="adjustEmployeeIdHidden" name="employee_id">

            <!-- Employee Information -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-person me-2"></i>Employee Information
                </h6>
                <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                    <div class="mb-2">
                        <small class="opacity-75 text-white d-block mb-1">Employee Name</small>
                        <div class="fw-semibold small" id="adjustEmployeeName">-</div>
                    </div>
                    <div>
                        <small class="opacity-75 text-white d-block mb-1">Employee ID</small>
                        <div class="small" id="adjustEmployeeId">-</div>
                    </div>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Current Balances -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-wallet2 me-2"></i>Current Balances
                </h6>
                <div class="row g-3">
                    <div class="col-4">
                        <div class="p-3 rounded-3 border text-center" style="border-color: #ffffff1a !important;">
                            <small class="opacity-75 text-white d-block mb-2">Annual</small>
                            <div class="fw-bold fs-5" id="currentAnnualBalance">-</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 rounded-3 border text-center" style="border-color: #ffffff1a !important;">
                            <small class="opacity-75 text-white d-block mb-2">Sick</small>
                            <div class="fw-bold fs-5" id="currentSickBalance">-</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 rounded-3 border text-center" style="border-color: #ffffff1a !important;">
                            <small class="opacity-75 text-white d-block mb-2">Casual</small>
                            <div class="fw-bold fs-5" id="currentCasualBalance">-</div>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Adjustment Details -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-sliders me-2"></i>Adjustment Details
                </h6>

                <!-- Adjustment Type -->
                <div class="mb-3">
                    <label for="adjustmentType" class="form-label fw-semibold small text-white">Adjustment Type <span class="text-danger">*</span></label>
                    <select class="form-select" id="adjustmentType" required>
                        <option value="add">Add Days</option>
                        <option value="subtract">Subtract Days</option>
                    </select>
                </div>

                <!-- Leave Type -->
                <div class="mb-3">
                    <label for="adjustLeaveType" class="form-label fw-semibold small text-white">Leave Type <span class="text-danger">*</span></label>
                    <select class="form-select" id="adjustLeaveType" required>
                        <option value="">Select Leave Type</option>
                        <option value="annual">Annual Leave</option>
                        <option value="sick">Sick Leave</option>
                        <option value="casual">Casual Leave</option>
                    </select>
                </div>

                <!-- Number of Days -->
                <div class="mb-3">
                    <label for="adjustDays" class="form-label fw-semibold small text-white">Number of Days <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="adjustDays" min="0.5" step="0.5" placeholder="e.g., 1, 2.5" required>
                    <small class="opacity-75 text-white">Half days allowed (0.5, 1.5, etc.)</small>
                </div>

                <!-- Reason -->
                <div class="mb-3">
                    <label for="adjustReason" class="form-label fw-semibold small text-white">Reason <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="adjustReason" rows="3" placeholder="Enter reason for adjustment (mandatory)" required></textarea>
                    <small class="opacity-75 text-white">This reason will be logged in the audit trail</small>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Preview -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-eye me-2"></i>Preview
                </h6>
                <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                    <div class="small">
                        <div id="previewText" class="opacity-75 text-white">
                            Select adjustment type and leave type to see preview
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="offcanvas-footer border-top p-3" style="border-color: #ffffffab !important">
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Cancel</button>
            <button type="button" class="btn btn-light text-dark border-0" id="saveAdjustmentBtn">
                <i class="bi bi-check-lg me-1"></i>Apply Adjustment
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const adjustmentType = document.getElementById('adjustmentType');
    const adjustLeaveType = document.getElementById('adjustLeaveType');
    const adjustDays = document.getElementById('adjustDays');
    const previewText = document.getElementById('previewText');
    const currentAnnualBalance = document.getElementById('currentAnnualBalance');
    const currentSickBalance = document.getElementById('currentSickBalance');
    const currentCasualBalance = document.getElementById('currentCasualBalance');
    const saveBtn = document.getElementById('saveAdjustmentBtn');

    function updatePreview() {
        if (!adjustLeaveType.value || !adjustDays.value) {
            previewText.textContent = 'Select adjustment type and leave type to see preview';
            return;
        }

        const type = adjustmentType.value;
        const leaveType = adjustLeaveType.value;
        const days = parseFloat(adjustDays.value);
        const currentBalance = parseFloat(
            leaveType === 'annual' ? currentAnnualBalance.textContent :
            leaveType === 'sick' ? currentSickBalance.textContent :
            currentCasualBalance.textContent
        );

        const newBalance = type === 'add' ? currentBalance + days : currentBalance - days;
        const action = type === 'add' ? 'Adding' : 'Subtracting';
        const leaveTypeLabel = leaveType === 'annual' ? 'Annual Leave' : leaveType === 'sick' ? 'Sick Leave' : 'Casual Leave';

        previewText.innerHTML = `
            <div class="mb-2"><strong>${action}</strong> ${days} day(s) to/from <strong>${leaveTypeLabel}</strong></div>
            <div class="mb-1">Current Balance: <strong>${currentBalance}</strong></div>
            <div>New Balance: <strong class="${newBalance < 0 ? 'text-danger' : 'text-success'}">${newBalance.toFixed(1)}</strong></div>
        `;
    }

    adjustmentType.addEventListener('change', updatePreview);
    adjustLeaveType.addEventListener('change', updatePreview);
    adjustDays.addEventListener('input', updatePreview);

    // Reset form when offcanvas is hidden
    const adjustmentCanvas = document.getElementById('adjustmentCanvas');
    if (adjustmentCanvas) {
        adjustmentCanvas.addEventListener('hidden.bs.offcanvas', function() {
            document.getElementById('adjustmentForm').reset();
            previewText.textContent = 'Select adjustment type and leave type to see preview';
        });
    }

    // Handle form submission
    if (saveBtn) {
        saveBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const form = document.getElementById('adjustmentForm');
            if (form && form.checkValidity()) {
                const formData = {
                    employeeId: document.getElementById('adjustEmployeeIdHidden').value,
                    adjustmentType: adjustmentType.value,
                    leaveType: adjustLeaveType.value,
                    days: parseFloat(adjustDays.value),
                    reason: document.getElementById('adjustReason').value
                };
                
                console.log('Balance Adjustment data:', formData);
                // TODO: Implement API call to save adjustment
                
                // Close offcanvas
                const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('adjustmentCanvas'));
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

