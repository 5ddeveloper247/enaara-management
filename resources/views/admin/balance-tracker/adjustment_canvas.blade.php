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
                <div class="row g-2" id="currentBalancesContainer">
                    <!-- Dynamic balances will be populated here -->
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
                    <select class="form-select" id="adjustLeaveType" required name="leave_type">
                        <option value="">Select Leave Type</option>
                    </select>
                </div>

                <!-- Number of Days -->
                <div class="mb-3">
                    <label for="adjustDays" class="form-label fw-semibold small text-white">Number of Days <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="adjustDays" inputmode="decimal" autocomplete="off" placeholder="e.g., 1, 2.5, 5" required>
                    <small class="opacity-75 text-white">Minimum 0.5 day. Half-day increments (0.5) are supported.</small>
                </div>

                <!-- Reason -->
                <div class="mb-3">
                    <label for="adjustReason" class="form-label fw-semibold small text-white">Reason <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="adjustReason" rows="3" minlength="5" maxlength="255" placeholder="Enter reason for adjustment (min. 5 characters)" required></textarea>
                    <small class="opacity-75 text-white">Minimum 5 characters required for audit purposes.</small>
                </div>
            </div>

            <!-- Preview -->
            <div class="mb-4">
                <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                    <div id="previewText" class="small opacity-75 text-white text-center">
                        Select adjustment type and leave type to see preview
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const adjustmentType = document.getElementById('adjustmentType');
        const adjustLeaveType = document.getElementById('adjustLeaveType');
        const adjustDays = document.getElementById('adjustDays');
        const previewText = document.getElementById('previewText');
        const saveBtn = document.getElementById('saveAdjustmentBtn');
        let currentEmployee = null;

        window.showAdjustmentCanvas = function(employee) {
            currentEmployee = employee;
            $('#adjustEmployeeName').text(employee.employeeName);
            $('#adjustEmployeeId').text(employee.employeeId);
            $('#adjustEmployeeIdHidden').val(employee.id);

            // Populate current balances dynamically
            const container = $('#currentBalancesContainer');
            container.empty();
            
            // Reset and populate dropdown
            const typeSelect = $('#adjustLeaveType');
            typeSelect.html('<option value="">Select Leave Type</option>');

            leaveTypes.forEach(type => {
                const quota = employee.quotas[type.id] || { eligible: true, remaining: 0, earned: 0, used: 0 };

                if (quota.eligible === false) {
                    return;
                }

                if (quota.earned > 0 || quota.used > 0) {
                    container.append(`
                        <div class="col-4">
                            <div class="p-2 rounded-3 border text-center" style="border-color: #ffffff1a !important;">
                                <small class="opacity-75 text-white d-block mb-1" style="font-size: 10px;">${type.name}</small>
                                <div class="fw-bold" style="font-size: 14px;">${quota.remaining}</div>
                            </div>
                        </div>
                    `);
                    
                    typeSelect.append(`<option value="${type.name}" data-id="${type.id}">${type.name}</option>`);
                }
            });

            $('#adjustmentForm')[0].reset();
            updatePreview();

            const canvas = new bootstrap.Offcanvas(document.getElementById('adjustmentCanvas'));
            canvas.show();
        }

        function parseAdjustmentDaysInput(rawValue) {
            const cleaned = String(rawValue ?? '').trim().replace(',', '.');

            if (cleaned === '' || !/^\d+(\.\d+)?$/.test(cleaned)) {
                return null;
            }

            const days = Number(cleaned);

            return Number.isFinite(days) ? days : null;
        }

        function normalizeAdjustmentDays(rawValue) {
            const days = parseAdjustmentDaysInput(rawValue);

            if (days === null || days < 0.5) {
                return null;
            }

            return Math.round((days + Number.EPSILON) * 2) / 2;
        }

        function formatAdjustmentDays(days) {
            const normalized = Math.round((days + Number.EPSILON) * 2) / 2;

            if (Math.abs(normalized - Math.round(normalized)) < 0.001) {
                return String(Math.round(normalized));
            }

            return normalized.toFixed(1);
        }

        function isHalfDayIncrement(days) {
            return Math.abs((days * 2) - Math.round(days * 2)) < 0.001;
        }

        function updatePreview() {
            if (!adjustLeaveType.value || !adjustDays.value || !currentEmployee) {
                previewText.textContent = 'Select adjustment type and leave type to see preview';
                return;
            }

            const type = adjustmentType.value;
            const selectedOption = $(adjustLeaveType).find(':selected');
            const leaveTypeId = selectedOption.data('id');
            const leaveTypeName = selectedOption.text();
            const days = normalizeAdjustmentDays(adjustDays.value);
            
            if (!leaveTypeId || days === null || days < 0.5 || !currentEmployee) {
                previewText.textContent = 'Select adjustment type and leave type to see preview';
                return;
            }

            const quota = currentEmployee.quotas[String(leaveTypeId)] || currentEmployee.quotas[leaveTypeId];
            const currentBalance = quota ? (parseFloat(quota.remaining) || 0) : 0;
            const newBalance = type === 'add' ? currentBalance + days : currentBalance - days;
            const action = type === 'add' ? 'Adding to' : 'Subtracting from';

            previewText.innerHTML = `
                <div class="mb-1">${action} <strong>${leaveTypeName}</strong> quota: <strong>${formatAdjustmentDays(days)} day${days === 1 ? '' : 's'}</strong></div>
                <div>New balance will be: <strong class="${newBalance < 0 ? 'text-danger' : 'text-success'}">${newBalance.toFixed(1)} days</strong></div>
            `;
        }

        adjustDays.addEventListener('blur', function() {
            const normalized = normalizeAdjustmentDays(adjustDays.value);

            if (normalized !== null) {
                adjustDays.value = formatAdjustmentDays(normalized);
            }
        });

        adjustDays.addEventListener('input', function() {
            adjustDays.value = adjustDays.value.replace(/[^\d.,]/g, '').replace(',', '.');
            updatePreview();
        });

        adjustmentType.addEventListener('change', updatePreview);
        adjustLeaveType.addEventListener('change', updatePreview);

        if (saveBtn) {
            saveBtn.addEventListener('click', function() {
                const form = document.getElementById('adjustmentForm');
                const reason = $('#adjustReason').val().trim();

                if (!$('#adjustLeaveType').val()) {
                    showError('Please select a leave type.');
                    return;
                }

                const normalizedDays = normalizeAdjustmentDays($('#adjustDays').val());

                if (normalizedDays === null || normalizedDays < 0.5) {
                    showError('Please enter a valid number of days (minimum 0.5).');
                    return;
                }

                if (!isHalfDayIncrement(normalizedDays)) {
                    showError('Days must be in 0.5 increments (e.g., 1, 1.5, 2, 5).');
                    return;
                }

                if (reason.length < 5) {
                    showError('Reason must be at least 5 characters.');
                    return;
                }

                $('#adjustDays').val(formatAdjustmentDays(normalizedDays));

                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                    const formData = {
                        employee_id: $('#adjustEmployeeIdHidden').val(),
                        leave_type: $('#adjustLeaveType').val(),
                        increment_type: $('#adjustmentType').val(),
                        days: formatAdjustmentDays(normalizedDays),
                        reason: $('#adjustReason').val(),
                        _token: '{{ csrf_token() }}'
                    };

                    $.ajax({
                        url: "{{ route('admin.balance-tracker.adjust') }}",
                        method: "POST",
                        data: formData,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        success: function(response) {
                            if (response.success) {
                                showSuccess(response.message).then(() => location.reload());
                            } else {
                                showError(response.message || 'Adjustment failed. Please try again.');
                            }
                        },
                        error: function(xhr) {
                            showError(getAjaxErrorMessage(xhr, 'Adjustment failed. Please try again.'));
                        }
                    });
            });
        }
    });
</script>
@endpush
