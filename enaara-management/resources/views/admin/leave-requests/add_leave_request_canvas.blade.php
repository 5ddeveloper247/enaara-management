<!-- Add Leave Request Canvas -->
<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="addLeaveRequestCanvas" aria-labelledby="addLeaveRequestCanvasLabel" style="width: 600px;">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="addLeaveRequestCanvasLabel">
            <i class="bi bi-plus-circle me-2"></i>New Leave Request
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="addLeaveRequestForm">
            <!-- Employee Selection -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-person me-2"></i>Employee
                </h6>
                
                <div class="mb-3">
                    <label for="leaveEmployee" class="form-label fw-semibold small text-white">Select Employee <span class="text-danger">*</span></label>
                    <select class="form-select" id="leaveEmployee" required>
                        <option value="">Select Employee</option>
                        <option value="1">Ahmed Ali (EMP-001) - Sales</option>
                        <option value="2">Zainab Malik (EMP-002) - HR</option>
                        <option value="3">Bilal Ahmed (EMP-003) - IT</option>
                        <option value="4">Hira Ali (EMP-004) - Operations</option>
                    </select>
                </div>

                <!-- Leave Balance Display -->
                <div class="p-3 rounded-3 border mb-3" style="border-color: #ffffff1a !important;">
                    <small class="opacity-75 text-white d-block mb-2">Current Leave Balance</small>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="small">Annual: <strong id="balanceAnnual">25</strong> days</div>
                        </div>
                        <div class="col-6">
                            <div class="small">Sick: <strong id="balanceSick">13</strong> days</div>
                        </div>
                        <div class="col-6">
                            <div class="small">Casual: <strong id="balanceCasual">8</strong> days</div>
                        </div>
                        <div class="col-6">
                            <div class="small">Comp-Off: <strong id="balanceCompOff">2</strong> days</div>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Leave Details -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-calendar me-2"></i>Leave Details
                </h6>

                <!-- Leave Type -->
                <div class="mb-3">
                    <label for="leaveType" class="form-label fw-semibold small text-white">Leave Type <span class="text-danger">*</span></label>
                    <select class="form-select" id="leaveType" required>
                        <option value="">Select Leave Type</option>
                        <option value="annual">Annual Leave</option>
                        <option value="sick">Sick Leave</option>
                        <option value="casual">Casual Leave</option>
                        <option value="comp-off">Compensatory Off</option>
                    </select>
                </div>

                <!-- Date Range -->
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label for="leaveStartDate" class="form-label fw-semibold small text-white">Start Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="leaveStartDate" required>
                    </div>
                    <div class="col-6">
                        <label for="leaveEndDate" class="form-label fw-semibold small text-white">End Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="leaveEndDate" required>
                    </div>
                </div>

                <!-- Calculated Days -->
                <div class="mb-3 p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small opacity-75 text-white">Total Days:</span>
                        <strong class="fs-5" id="calculatedDays">0</strong>
                    </div>
                </div>

                <!-- Reason -->
                <div class="mb-3">
                    <label for="leaveReason" class="form-label fw-semibold small text-white">Reason <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="leaveReason" rows="3" placeholder="Enter reason for leave" required></textarea>
                </div>

                <!-- Medical Certificate (for Sick Leave) -->
                <div class="mb-3" id="medicalCertSection" style="display: none;">
                    <label for="medicalCertificate" class="form-label fw-semibold small text-white">Medical Certificate</label>
                    <input type="file" class="form-control" id="medicalCertificate" accept=".pdf,.jpg,.jpeg,.png">
                    <small class="opacity-75 text-white">Required for sick leave exceeding 2 days</small>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Approval Level -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-diagram-3 me-2"></i>Approval Workflow
                </h6>

                <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                    <div class="small opacity-75 text-white mb-2">This request will be routed through:</div>
                    <div class="small">
                        <div class="mb-1">1. Supervisor → Team workload check</div>
                        <div class="mb-1">2. HR/Dept Head → Leave balance verification</div>
                        <div>3. Super Admin → Final approval (if required)</div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="offcanvas-footer border-top p-3" style="border-color: #ffffffab !important">
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Cancel</button>
            <button type="button" class="btn btn-light text-dark border-0" id="submitLeaveRequestBtn">
                <i class="bi bi-check-lg me-1"></i>Submit Request
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.getElementById('leaveStartDate');
    const endDateInput = document.getElementById('leaveEndDate');
    const leaveTypeSelect = document.getElementById('leaveType');
    const calculatedDaysEl = document.getElementById('calculatedDays');
    const medicalCertSection = document.getElementById('medicalCertSection');

    // Calculate days when dates change
    function calculateDays() {
        if (startDateInput.value && endDateInput.value) {
            const start = new Date(startDateInput.value);
            const end = new Date(endDateInput.value);
            if (end >= start) {
                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1; // +1 to include both start and end
                calculatedDaysEl.textContent = diffDays;
            } else {
                calculatedDaysEl.textContent = '0';
            }
        } else {
            calculatedDaysEl.textContent = '0';
        }
    }

    startDateInput.addEventListener('change', calculateDays);
    endDateInput.addEventListener('change', calculateDays);

    // Show/hide medical certificate section
    leaveTypeSelect.addEventListener('change', function() {
        if (this.value === 'sick') {
            medicalCertSection.style.display = 'block';
        } else {
            medicalCertSection.style.display = 'none';
        }
    });

    // Reset form when offcanvas is hidden
    const addLeaveCanvas = document.getElementById('addLeaveRequestCanvas');
    if (addLeaveCanvas) {
        addLeaveCanvas.addEventListener('hidden.bs.offcanvas', function() {
            document.getElementById('addLeaveRequestForm').reset();
            calculatedDaysEl.textContent = '0';
            medicalCertSection.style.display = 'none';
        });
    }

    // Handle form submission
    const submitBtn = document.getElementById('submitLeaveRequestBtn');
    if (submitBtn) {
        submitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const form = document.getElementById('addLeaveRequestForm');
            if (form && form.checkValidity()) {
                const formData = {
                    employee: document.getElementById('leaveEmployee').value,
                    leaveType: document.getElementById('leaveType').value,
                    startDate: document.getElementById('leaveStartDate').value,
                    endDate: document.getElementById('leaveEndDate').value,
                    days: calculatedDaysEl.textContent,
                    reason: document.getElementById('leaveReason').value,
                    medicalCertificate: document.getElementById('medicalCertificate').files[0]
                };
                
                console.log('Leave Request data:', formData);
                // TODO: Implement API call to submit leave request
                
                // Close offcanvas
                const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('addLeaveRequestCanvas'));
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



