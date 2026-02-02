<!-- Quick Request Canvas -->
<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="quickRequestCanvas" aria-labelledby="quickRequestCanvasLabel" style="width: 500px;">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="quickRequestCanvasLabel">
            <i class="bi bi-lightning-charge me-2"></i>Quick Leave Request
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="quickRequestForm">
            <!-- Leave Type -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-calendar me-2"></i>Leave Details
                </h6>

                <div class="mb-3">
                    <label for="quickLeaveType" class="form-label fw-semibold small text-white">Leave Type <span class="text-danger">*</span></label>
                    <select class="form-select" id="quickLeaveType" required>
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
                        <label for="quickStartDate" class="form-label fw-semibold small text-white">Start Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="quickStartDate" required>
                    </div>
                    <div class="col-6">
                        <label for="quickEndDate" class="form-label fw-semibold small text-white">End Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="quickEndDate" required>
                    </div>
                </div>

                <!-- Calculated Days -->
                <div class="mb-3 p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small opacity-75 text-white">Total Days:</span>
                        <strong class="fs-5" id="quickCalculatedDays">0</strong>
                    </div>
                </div>

                <!-- Reason -->
                <div class="mb-3">
                    <label for="quickReason" class="form-label fw-semibold small text-white">Reason <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="quickReason" rows="3" placeholder="Brief reason for leave" required></textarea>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Approval Method -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-shield-check me-2"></i>Approval Method
                </h6>

                <div class="mb-3">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="approvalMethod" id="autoApproval" value="auto" checked>
                        <label class="form-check-label text-white" for="autoApproval">
                            <strong>Auto-Approval</strong>
                            <small class="d-block opacity-75">Record immediately (Super Admin privilege)</small>
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="approvalMethod" id="boardApproval" value="board">
                        <label class="form-check-label text-white" for="boardApproval">
                            <strong>Board/Director Approval</strong>
                            <small class="d-block opacity-75">Send to another Super Admin for formal recording</small>
                        </label>
                    </div>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Proxy Assignment (if leaving) -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-person-check me-2"></i>Proxy Assignment
                </h6>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="assignProxyOnLeave">
                    <label class="form-check-label text-white" for="assignProxyOnLeave">
                        <strong>Assign Proxy Admin</strong>
                        <small class="d-block opacity-75">Forward approval tasks while I'm away</small>
                    </label>
                </div>

                <div class="mb-3" id="proxySelectSection" style="display: none;">
                    <label for="quickProxySelect" class="form-label fw-semibold small text-white">Select Proxy</label>
                    <select class="form-select" id="quickProxySelect">
                        <option value="">Select a proxy admin...</option>
                        <option value="1">Zainab Malik (HR Manager)</option>
                        <option value="2">Bilal Ahmed (IT Director)</option>
                        <option value="3">Hira Ali (Operations Head)</option>
                    </select>
                </div>
            </div>
        </form>
    </div>
    <div class="offcanvas-footer border-top p-3" style="border-color: #ffffffab !important">
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Cancel</button>
            <button type="button" class="btn btn-light text-dark border-0" id="submitQuickRequestBtn">
                <i class="bi bi-check-lg me-1"></i>Submit Request
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.getElementById('quickStartDate');
    const endDateInput = document.getElementById('quickEndDate');
    const calculatedDaysEl = document.getElementById('quickCalculatedDays');
    const assignProxyCheckbox = document.getElementById('assignProxyOnLeave');
    const proxySelectSection = document.getElementById('proxySelectSection');

    // Calculate days when dates change
    function calculateDays() {
        if (startDateInput.value && endDateInput.value) {
            const start = new Date(startDateInput.value);
            const end = new Date(endDateInput.value);
            if (end >= start) {
                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
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

    // Show/hide proxy select
    assignProxyCheckbox.addEventListener('change', function() {
        if (this.checked) {
            proxySelectSection.style.display = 'block';
        } else {
            proxySelectSection.style.display = 'none';
        }
    });

    // Reset form when offcanvas is hidden
    const quickRequestCanvas = document.getElementById('quickRequestCanvas');
    if (quickRequestCanvas) {
        quickRequestCanvas.addEventListener('hidden.bs.offcanvas', function() {
            document.getElementById('quickRequestForm').reset();
            calculatedDaysEl.textContent = '0';
            proxySelectSection.style.display = 'none';
        });
    }

    // Handle form submission
    const submitBtn = document.getElementById('submitQuickRequestBtn');
    if (submitBtn) {
        submitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const form = document.getElementById('quickRequestForm');
            if (form && form.checkValidity()) {
                const formData = {
                    leaveType: document.getElementById('quickLeaveType').value,
                    startDate: document.getElementById('quickStartDate').value,
                    endDate: document.getElementById('quickEndDate').value,
                    days: calculatedDaysEl.textContent,
                    reason: document.getElementById('quickReason').value,
                    approvalMethod: document.querySelector('input[name="approvalMethod"]:checked').value,
                    assignProxy: assignProxyCheckbox.checked,
                    proxyId: document.getElementById('quickProxySelect').value
                };
                
                console.log('Quick Leave Request data:', formData);
                // TODO: Implement API call to submit leave request
                
                // Close offcanvas
                const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('quickRequestCanvas'));
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

