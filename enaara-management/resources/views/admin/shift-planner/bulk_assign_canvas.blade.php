<!-- Bulk Assign Canvas -->
<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="bulkAssignCanvas" aria-labelledby="bulkAssignCanvasLabel" style="width: 600px;">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="bulkAssignCanvasLabel">
            <i class="bi bi-people-fill me-2"></i>Bulk Assign Shifts
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="bulkAssignForm">
            <!-- Employee Selection -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-people me-2"></i>Select Employees
                </h6>

                <!-- Quick Selection -->
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-white">Quick Selection</label>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-sm btn-outline-light" id="selectAllBtn">Select All</button>
                        <button type="button" class="btn btn-sm btn-outline-light" id="selectByDeptBtn">By Department</button>
                        <button type="button" class="btn btn-sm btn-outline-light" id="selectBySiteBtn">By Site</button>
                        <button type="button" class="btn btn-sm btn-outline-light" id="clearSelectionBtn">Clear</button>
                    </div>
                </div>

                <!-- Employee List -->
                <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto; border-color: #ffffff1a !important;">
                    <div id="employeeList">
                        <!-- Sample employees - replace with dynamic data -->
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" value="1" id="emp1" name="employees[]">
                            <label class="form-check-label text-white" for="emp1">
                                John Doe - Sales - Head Office
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" value="2" id="emp2" name="employees[]">
                            <label class="form-check-label text-white" for="emp2">
                                Sarah Miller - HR - Head Office
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" value="3" id="emp3" name="employees[]">
                            <label class="form-check-label text-white" for="emp3">
                                Robert Kim - IT - Site 1
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" value="4" id="emp4" name="employees[]">
                            <label class="form-check-label text-white" for="emp4">
                                Emma Wilson - Operations - Branch A
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" value="5" id="emp5" name="employees[]">
                            <label class="form-check-label text-white" for="emp5">
                                Michael Johnson - Finance - Head Office
                            </label>
                        </div>
                    </div>
                </div>
                <small class="opacity-75 text-white d-block mt-2">
                    <span id="selectedCount">0</span> employee(s) selected
                </small>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Shift Selection -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-clock-history me-2"></i>Assign Shift
                </h6>

                <div class="mb-3">
                    <label for="bulkShiftSelect" class="form-label fw-semibold small text-white">Shift <span class="text-danger">*</span></label>
                    <select class="form-select" id="bulkShiftSelect" required>
                        <option value="">Select Shift</option>
                        <option value="1">Morning Shift (09:00 - 18:00)</option>
                        <option value="2">Night Shift (18:00 - 06:00)</option>
                        <option value="3">Site Sales - Weekend (10:00 - 16:00)</option>
                    </select>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Date Range -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-calendar-range me-2"></i>Date Range
                </h6>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label for="bulkStartDate" class="form-label fw-semibold small text-white">Start Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="bulkStartDate" required>
                    </div>
                    <div class="col-6">
                        <label for="bulkEndDate" class="form-label fw-semibold small text-white">End Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="bulkEndDate" required>
                    </div>
                </div>

                <!-- Quick Date Selection -->
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-sm btn-outline-light" data-days="7">This Week</button>
                    <button type="button" class="btn btn-sm btn-outline-light" data-days="14">Next 2 Weeks</button>
                    <button type="button" class="btn btn-sm btn-outline-light" data-days="30">This Month</button>
                    <button type="button" class="btn btn-sm btn-outline-light" data-days="60">Next 2 Months</button>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Options -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-gear me-2"></i>Options
                </h6>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="checkConflicts" checked>
                    <label class="form-check-label text-white" for="checkConflicts">
                        Check for conflicts before assigning
                    </label>
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="overrideExisting">
                    <label class="form-check-label text-white" for="overrideExisting">
                        Override existing assignments
                    </label>
                </div>

                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="excludeWeekends">
                    <label class="form-check-label text-white" for="excludeWeekends">
                        Exclude weekends
                    </label>
                </div>
            </div>

            <!-- Conflict Warning -->
            <div id="bulkConflictWarning" class="alert alert-warning" style="display: none; background-color: rgba(255, 193, 7, 0.2); border-color: rgba(255, 193, 7, 0.3); color: white;">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Warning:</strong> Some employees have conflicting shifts. Review before proceeding.
            </div>
        </form>
    </div>
    <div class="offcanvas-footer border-top p-3" style="border-color: #ffffffab !important">
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Cancel</button>
            <button type="button" class="btn btn-light text-dark border-0" id="applyBulkAssignBtn">
                <i class="bi bi-check-lg me-1"></i>Apply Assignment
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update selected count
    function updateSelectedCount() {
        const selected = document.querySelectorAll('input[name="employees[]"]:checked').length;
        document.getElementById('selectedCount').textContent = selected;
    }

    // Employee selection handlers
    document.querySelectorAll('input[name="employees[]"]').forEach(function(checkbox) {
        checkbox.addEventListener('change', updateSelectedCount);
    });

    // Quick selection buttons
    document.getElementById('selectAllBtn')?.addEventListener('click', function() {
        document.querySelectorAll('input[name="employees[]"]').forEach(function(cb) {
            cb.checked = true;
        });
        updateSelectedCount();
    });

    document.getElementById('clearSelectionBtn')?.addEventListener('click', function() {
        document.querySelectorAll('input[name="employees[]"]').forEach(function(cb) {
            cb.checked = false;
        });
        updateSelectedCount();
    });

    // Quick date selection
    document.querySelectorAll('[data-days]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const days = parseInt(this.getAttribute('data-days'));
            const startDate = new Date();
            const endDate = new Date();
            endDate.setDate(startDate.getDate() + days - 1);
            
            document.getElementById('bulkStartDate').value = startDate.toISOString().split('T')[0];
            document.getElementById('bulkEndDate').value = endDate.toISOString().split('T')[0];
        });
    });

    // Set default dates (current month)
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    document.getElementById('bulkStartDate').value = firstDay.toISOString().split('T')[0];
    document.getElementById('bulkEndDate').value = lastDay.toISOString().split('T')[0];

            // Handle form submission
            document.getElementById('applyBulkAssignBtn')?.addEventListener('click', function() {
                const form = document.getElementById('bulkAssignForm');
                if (form.checkValidity()) {
                    const selectedEmployees = Array.from(document.querySelectorAll('input[name="employees[]"]:checked')).map(cb => {
                        const label = document.querySelector(`label[for="${cb.id}"]`).textContent.trim();
                        return {
                            id: cb.value,
                            name: label.split(' - ')[0] // Extract employee name
                        };
                    });
                    const shiftId = document.getElementById('bulkShiftSelect').value;
                    const shiftSelect = document.getElementById('bulkShiftSelect');
                    const shiftName = shiftSelect.options[shiftSelect.selectedIndex].text.split(' (')[0]; // Extract shift name
                    const startDate = document.getElementById('bulkStartDate').value;
                    const endDate = document.getElementById('bulkEndDate').value;
                    const checkConflicts = document.getElementById('checkConflicts').checked;
                    const overrideExisting = document.getElementById('overrideExisting').checked;
                    const excludeWeekends = document.getElementById('excludeWeekends').checked;

                    if (selectedEmployees.length === 0) {
                        return;
                    }

                    if (!shiftId) {
                        return;
                    }

                    const formData = {
                        employees: selectedEmployees,
                        shiftId: shiftId,
                        shiftName: shiftName,
                        startDate: startDate,
                        endDate: endDate,
                        checkConflicts: checkConflicts,
                        overrideExisting: overrideExisting,
                        excludeWeekends: excludeWeekends
                    };
                    
                    // Check conflicts if enabled
                    if (checkConflicts) {
                        // TODO: Implement conflict checking
                        // For now, just show warning
                        document.getElementById('bulkConflictWarning').style.display = 'block';
                    } else {
                        // Add events to calendar
                        const start = new Date(startDate);
                        const end = new Date(endDate);
                        
                        selectedEmployees.forEach(function(employee, index) {
                            const currentDate = new Date(start);
                            
                            while (currentDate <= end) {
                                // Skip weekends if option is enabled
                                if (excludeWeekends) {
                                    const dayOfWeek = currentDate.getDay();
                                    if (dayOfWeek === 0 || dayOfWeek === 6) {
                                        currentDate.setDate(currentDate.getDate() + 1);
                                        continue;
                                    }
                                }
                                
                                // Add event to calendar
                                if (typeof addRosterEvent === 'function') {
                                    addRosterEvent({
                                        id: 'bulk-' + employee.id + '-' + currentDate.toISOString().split('T')[0],
                                        employeeId: employee.id,
                                        employeeName: employee.name,
                                        shiftId: shiftId,
                                        shiftName: shiftName,
                                        start: currentDate.toISOString().split('T')[0],
                                        end: currentDate.toISOString().split('T')[0]
                                    });
                                }
                                
                                currentDate.setDate(currentDate.getDate() + 1);
                            }
                        });
                        
                        // TODO: Implement API call to bulk assign shifts
                        
                        // Close canvas after assignment
                        const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('bulkAssignCanvas'));
                        if (offcanvas) {
                            offcanvas.hide();
                        }
                    }
                } else {
                    form.reportValidity();
                }
            });

    // Reset form when canvas is hidden
    const bulkAssignCanvas = document.getElementById('bulkAssignCanvas');
    if (bulkAssignCanvas) {
        bulkAssignCanvas.addEventListener('hidden.bs.offcanvas', function() {
            document.getElementById('bulkAssignForm').reset();
            document.querySelectorAll('input[name="employees[]"]').forEach(function(cb) {
                cb.checked = false;
            });
            updateSelectedCount();
            document.getElementById('bulkConflictWarning').style.display = 'none';
        });
    }
});
</script>

