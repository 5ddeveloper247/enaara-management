<!-- Leave Policy Canvas -->
<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="leavePolicyCanvas" aria-labelledby="leavePolicyCanvasLabel" style="width: 700px;">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="leavePolicyCanvasLabel">
            <i class="bi bi-gear me-2"></i>Leave Policy Configuration
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="leavePolicyForm">
            <!-- Leave Types Configuration -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-list-check me-2"></i>Leave Types
                </h6>
                
                <!-- Annual Leave -->
                <div class="mb-3 p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <label class="form-label fw-semibold small text-white mb-1">Annual Leave</label>
                            <div class="input-group" style="max-width: 200px;">
                                <input type="number" class="form-control" id="annualLeaveDays" value="30" min="0" max="365">
                                <span class="input-group-text bg-transparent text-white border-0">days/year</span>
                            </div>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="annualLeaveEnabled" checked>
                            <label class="form-check-label text-white" for="annualLeaveEnabled">Enabled</label>
                        </div>
                    </div>
                </div>

                <!-- Sick Leave -->
                <div class="mb-3 p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <label class="form-label fw-semibold small text-white mb-1">Sick Leave</label>
                            <div class="input-group" style="max-width: 200px;">
                                <input type="number" class="form-control" id="sickLeaveDays" value="15" min="0" max="365">
                                <span class="input-group-text bg-transparent text-white border-0">days/year</span>
                            </div>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="sickLeaveEnabled" checked>
                            <label class="form-check-label text-white" for="sickLeaveEnabled">Enabled</label>
                        </div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="sickLeaveMedicalCert" checked>
                        <label class="form-check-label text-white small" for="sickLeaveMedicalCert">
                            Require medical certificate
                        </label>
                    </div>
                </div>

                <!-- Casual Leave -->
                <div class="mb-3 p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <label class="form-label fw-semibold small text-white mb-1">Casual Leave</label>
                            <div class="input-group" style="max-width: 200px;">
                                <input type="number" class="form-control" id="casualLeaveDays" value="10" min="0" max="365">
                                <span class="input-group-text bg-transparent text-white border-0">days/year</span>
                            </div>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="casualLeaveEnabled" checked>
                            <label class="form-check-label text-white" for="casualLeaveEnabled">Enabled</label>
                        </div>
                    </div>
                </div>

                <!-- Compensatory Off -->
                <div class="mb-3 p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <label class="form-label fw-semibold small text-white mb-1">Compensatory Off</label>
                            <small class="d-block opacity-75 text-white">Earned when working on weekends/holidays</small>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="compOffEnabled" checked>
                            <label class="form-check-label text-white" for="compOffEnabled">Enabled</label>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Accrual Logic -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-calendar-event me-2"></i>Accrual Logic
                </h6>

                <div class="mb-3">
                    <label for="accrualMethod" class="form-label fw-semibold small text-white">Accrual Method <span class="text-danger">*</span></label>
                    <select class="form-select" id="accrualMethod" required>
                        <option value="calendar-year">Calendar Year (Jan 1st)</option>
                        <option value="anniversary">Joining Anniversary</option>
                        <option value="monthly">Monthly Accrual</option>
                    </select>
                    <small class="opacity-75 text-white">When does the leave balance refresh?</small>
                </div>

                <div class="mb-3">
                    <label for="accrualFrequency" class="form-label fw-semibold small text-white">Accrual Frequency</label>
                    <select class="form-select" id="accrualFrequency">
                        <option value="yearly">Yearly (Full balance at start)</option>
                        <option value="monthly">Monthly (Pro-rated)</option>
                        <option value="quarterly">Quarterly</option>
                    </select>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Carry-Forward Policy -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-arrow-repeat me-2"></i>Carry-Forward Policy
                </h6>

                <div class="mb-3">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="carryForwardPolicy" id="carryForwardAllowed" value="allowed" checked>
                        <label class="form-check-label text-white" for="carryForwardAllowed">
                            <strong>Allow Carry-Forward</strong>
                        </label>
                    </div>
                    <div class="ms-4 mb-3">
                        <label for="carryForwardDays" class="form-label small text-white">Maximum Days</label>
                        <div class="input-group" style="max-width: 150px;">
                            <input type="number" class="form-control" id="carryForwardDays" value="10" min="0" max="30">
                            <span class="input-group-text bg-transparent text-white border-0">days</span>
                        </div>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="carryForwardPolicy" id="carryForwardNotAllowed" value="not-allowed">
                        <label class="form-check-label text-white" for="carryForwardNotAllowed">
                            <strong>Use it or Lose it</strong>
                            <small class="d-block opacity-75">Unused leave expires at year-end</small>
                        </label>
                    </div>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Approval Workflow -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-diagram-3 me-2"></i>Approval Workflow
                </h6>

                <div class="mb-3">
                    <label class="form-label fw-semibold small text-white">Approval Levels</label>
                    <div class="border rounded p-3" style="border-color: #ffffff1a !important;">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="levelSupervisor" checked>
                            <label class="form-check-label text-white" for="levelSupervisor">
                                <strong>Level 1: Supervisor</strong>
                                <small class="d-block opacity-75">Checks team workload</small>
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="levelHR" checked>
                            <label class="form-check-label text-white" for="levelHR">
                                <strong>Level 2: HR/Dept Head</strong>
                                <small class="d-block opacity-75">Checks leave balance</small>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="levelSuperAdmin">
                            <label class="form-check-label text-white" for="levelSuperAdmin">
                                <strong>Level 3: Super Admin</strong>
                                <small class="d-block opacity-75">Final oversight (Special cases only)</small>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Integration Settings -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-link-45deg me-2"></i>Integration Settings
                </h6>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="autoMarkAttendance" checked>
                    <label class="form-check-label text-white" for="autoMarkAttendance">
                        <strong>Auto-Mark Attendance</strong>
                        <small class="d-block opacity-75">Automatically mark "On Leave" in Daily Logs when approved</small>
                    </label>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="enableLWP" checked>
                    <label class="form-check-label text-white" for="enableLWP">
                        <strong>Leave Without Pay (LWP)</strong>
                        <small class="d-block opacity-75">Flag for Payroll when employee has 0 balance</small>
                    </label>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="notifyPayroll">
                    <label class="form-check-label text-white" for="notifyPayroll">
                        <strong>Auto-Notify Payroll</strong>
                        <small class="d-block opacity-75">Send LWP notifications to Payroll module</small>
                    </label>
                </div>
            </div>
        </form>
    </div>
    <div class="offcanvas-footer border-top p-3" style="border-color: #ffffffab !important">
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Cancel</button>
            <button type="button" class="btn btn-light text-dark border-0" id="savePolicyBtn">
                <i class="bi bi-check-lg me-1"></i>Save Policy
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Reset form when offcanvas is hidden
    const leavePolicyCanvas = document.getElementById('leavePolicyCanvas');
    if (leavePolicyCanvas) {
        leavePolicyCanvas.addEventListener('hidden.bs.offcanvas', function() {
            // Form will be reset if needed
        });
    }

    // Handle form submission
    const saveBtn = document.getElementById('savePolicyBtn');
    if (saveBtn) {
        saveBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const form = document.getElementById('leavePolicyForm');
            if (form && form.checkValidity()) {
                const policyData = {
                    annualLeave: {
                        days: document.getElementById('annualLeaveDays').value,
                        enabled: document.getElementById('annualLeaveEnabled').checked
                    },
                    sickLeave: {
                        days: document.getElementById('sickLeaveDays').value,
                        enabled: document.getElementById('sickLeaveEnabled').checked,
                        requireMedicalCert: document.getElementById('sickLeaveMedicalCert').checked
                    },
                    casualLeave: {
                        days: document.getElementById('casualLeaveDays').value,
                        enabled: document.getElementById('casualLeaveEnabled').checked
                    },
                    compOff: {
                        enabled: document.getElementById('compOffEnabled').checked
                    },
                    accrualMethod: document.getElementById('accrualMethod').value,
                    accrualFrequency: document.getElementById('accrualFrequency').value,
                    carryForwardPolicy: document.querySelector('input[name="carryForwardPolicy"]:checked').value,
                    carryForwardDays: document.getElementById('carryForwardDays').value,
                    approvalLevels: {
                        supervisor: document.getElementById('levelSupervisor').checked,
                        hr: document.getElementById('levelHR').checked,
                        superAdmin: document.getElementById('levelSuperAdmin').checked
                    },
                    autoMarkAttendance: document.getElementById('autoMarkAttendance').checked,
                    enableLWP: document.getElementById('enableLWP').checked,
                    notifyPayroll: document.getElementById('notifyPayroll').checked
                };
                
                console.log('Leave Policy data:', policyData);
                // TODO: Implement API call to save policy
                
                // Close offcanvas
                const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('leavePolicyCanvas'));
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



