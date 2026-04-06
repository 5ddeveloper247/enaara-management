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
                    <input type="number" class="form-control" id="adjustDays" min="0.5" step="0.5" placeholder="e.g., 1, 2.5" required>
                </div>

                <!-- Reason -->
                <div class="mb-3">
                    <label for="adjustReason" class="form-label fw-semibold small text-white">Reason <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="adjustReason" rows="3" placeholder="Enter reason for adjustment" required></textarea>
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
                const quota = employee.quotas[type.id] || { remaining: 0, earned: 0, used: 0 };
                
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

        function updatePreview() {
            if (!adjustLeaveType.value || !adjustDays.value || !currentEmployee) {
                previewText.textContent = 'Select adjustment type and leave type to see preview';
                return;
            }

            const type = adjustmentType.value;
            const selectedOption = $(adjustLeaveType).find(':selected');
            const leaveTypeId = selectedOption.data('id');
            const leaveTypeName = selectedOption.text();
            const days = parseFloat(adjustDays.value);
            
            if (!leaveTypeId || isNaN(days) || !currentEmployee) {
                previewText.textContent = 'Select adjustment type and leave type to see preview';
                return;
            }

            const currentBalance = currentEmployee.quotas[leaveTypeId] ? parseFloat(currentEmployee.quotas[leaveTypeId].remaining) : 0;
            const newBalance = type === 'add' ? currentBalance + days : currentBalance - days;
            const action = type === 'add' ? 'Adding to' : 'Subtracting from';

            previewText.innerHTML = `
                <div class="mb-1">${action} <strong>${leaveTypeName}</strong> quota: <strong>${days} days</strong></div>
                <div>New balance will be: <strong class="${newBalance < 0 ? 'text-danger' : 'text-success'}">${newBalance.toFixed(1)} days</strong></div>
            `;
        }

        adjustmentType.addEventListener('change', updatePreview);
        adjustLeaveType.addEventListener('change', updatePreview);
        adjustDays.addEventListener('input', updatePreview);

        if (saveBtn) {
            saveBtn.addEventListener('click', function() {
                const form = document.getElementById('adjustmentForm');
                if (form.checkValidity()) {
                    const formData = {
                        employee_id: $('#adjustEmployeeIdHidden').val(),
                        leave_type: $('#adjustLeaveType').val(),
                        increment_type: $('#adjustmentType').val(),
                        days: $('#adjustDays').val(),
                        reason: $('#adjustReason').val(),
                        _token: '{{ csrf_token() }}'
                    };

                    $.ajax({
                        url: "{{ route('admin.balance-tracker.adjust') }}",
                        method: "POST",
                        data: formData,
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Success', response.message, 'success').then(() => location.reload());
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error', 'Adjustment failed. Please try again.', 'error');
                        }
                    });
                } else {
                    form.reportValidity();
                }
            });
        }
    });
</script>
@endpush