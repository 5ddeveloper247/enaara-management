<!-- Bulk Assign Canvas -->
<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="bulkAssignCanvas" aria-labelledby="bulkAssignCanvasLabel" style="width: 600px;">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="bulkAssignCanvasLabel">
            <i class="bi bi-people-fill me-2"></i>Bulk Assign Shifts
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body">
        <form id="bulkAssignForm" method="POST" action="{{ route('admin.shift-roster.bulk-assign') }}">
            @csrf

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
                        @forelse($employees ?? [] as $employee)
                            <div class="form-check mb-2 employee-item"
                                 data-department="{{ strtolower($employee->department->name ?? '') }}"
                                 data-site="{{ strtolower($employee->site ?? '') }}">
                                <input class="form-check-input"
                                       type="checkbox"
                                       value="{{ $employee->id }}"
                                       id="emp{{ $employee->id }}"
                                       name="employee_ids[]">
                                <label class="form-check-label text-white" for="emp{{ $employee->id }}">
                                    {{ $employee->full_name }}
                                    @if(!empty($employee->department->name ?? null))
                                        - {{ $employee->department->name }}
                                    @endif
                                    @if(!empty($employee->site ?? null))
                                        - {{ $employee->site }}
                                    @endif
                                </label>
                            </div>
                        @empty
                            <div class="text-white-50 small">No employees available.</div>
                        @endforelse
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
                    <label for="bulkShiftSelect" class="form-label fw-semibold small text-white">
                        Shift <span class="text-danger">*</span>
                    </label>
                    <select class="form-select" id="bulkShiftSelect" name="shift_planner_id" required>
                        <option value="">Select Shift</option>
                        @forelse($shifts ?? [] as $shift)
                            <option value="{{ $shift->id }}">
                                {{ $shift->name }} ({{ $shift->start_time }} - {{ $shift->end_time }})
                            </option>
                        @empty
                            <option value="">No shifts available</option>
                        @endforelse
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
                        <label for="bulkStartDate" class="form-label fw-semibold small text-white">
                            Start Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" class="form-control" id="bulkStartDate" name="start_date" required>
                    </div>
                    <div class="col-6">
                        <label for="bulkEndDate" class="form-label fw-semibold small text-white">
                            End Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" class="form-control" id="bulkEndDate" name="end_date" required>
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
                    <input class="form-check-input" type="checkbox" id="checkConflicts" name="check_conflicts" value="1" checked>
                    <label class="form-check-label text-white" for="checkConflicts">
                        Check for conflicts before assigning
                    </label>
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="overrideExisting" name="override_existing" value="1">
                    <label class="form-check-label text-white" for="overrideExisting">
                        Override existing assignments
                    </label>
                </div>

                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="excludeWeekends" name="exclude_weekends" value="1">
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
    const form = document.getElementById('bulkAssignForm');
    const applyBtn = document.getElementById('applyBulkAssignBtn');
    const bulkAssignCanvas = document.getElementById('bulkAssignCanvas');
    const employeeCheckboxesSelector = 'input[name="employee_ids[]"]';
    const bulkConflictWarning = document.getElementById('bulkConflictWarning');

    const bulkAssignUrl = @json(route('admin.shift-roster.bulk-assign'));
    const csrfToken = @json(csrf_token());

    function updateSelectedCount() {
        const selected = document.querySelectorAll(`${employeeCheckboxesSelector}:checked`).length;
        document.getElementById('selectedCount').textContent = selected;
    }

    function resetDefaultDates() {
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);

        document.getElementById('bulkStartDate').value = firstDay.toISOString().split('T')[0];
        document.getElementById('bulkEndDate').value = lastDay.toISOString().split('T')[0];
    }

    function getSelectedEmployeesForCalendar() {
        return Array.from(document.querySelectorAll(`${employeeCheckboxesSelector}:checked`)).map(cb => {
            const label = document.querySelector(`label[for="${cb.id}"]`);
            const labelText = label ? label.textContent.trim() : '';
            return {
                id: cb.value,
                name: labelText.split(' - ')[0] || labelText
            };
        });
    }

    function addEventsToCalendar(selectedEmployees, shiftId, shiftName, startDate, endDate, excludeWeekends) {
        const start = new Date(startDate);
        const end = new Date(endDate);

        selectedEmployees.forEach(function(employee) {
            const currentDate = new Date(start);

            while (currentDate <= end) {
                if (excludeWeekends) {
                    const dayOfWeek = currentDate.getDay();
                    if (dayOfWeek === 0 || dayOfWeek === 6) {
                        currentDate.setDate(currentDate.getDate() + 1);
                        continue;
                    }
                }

                if (typeof addRosterEvent === 'function') {
                    const dateStr = currentDate.toISOString().split('T')[0];

                    addRosterEvent({
                        id: 'bulk-' + employee.id + '-' + dateStr,
                        employeeId: employee.id,
                        employeeName: employee.name,
                        shiftId: shiftId,
                        shiftName: shiftName,
                        start: dateStr,
                        end: dateStr
                    });
                }

                currentDate.setDate(currentDate.getDate() + 1);
            }
        });
    }

    function resetBulkAssignForm() {
        form.reset();

        document.querySelectorAll(employeeCheckboxesSelector).forEach(function(cb) {
            cb.checked = false;
        });

        updateSelectedCount();
        resetDefaultDates();

        if (bulkConflictWarning) {
            bulkConflictWarning.style.display = 'none';
            bulkConflictWarning.innerHTML = `
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Warning:</strong> Some employees have conflicting shifts. Review before proceeding.
            `;
        }

        const checkConflicts = document.getElementById('checkConflicts');
        if (checkConflicts) {
            checkConflicts.checked = true;
        }
    }

    document.querySelectorAll(employeeCheckboxesSelector).forEach(function(checkbox) {
        checkbox.addEventListener('change', updateSelectedCount);
    });

    document.getElementById('selectAllBtn')?.addEventListener('click', function() {
        document.querySelectorAll(employeeCheckboxesSelector).forEach(function(cb) {
            cb.checked = true;
        });
        updateSelectedCount();
    });

    document.getElementById('clearSelectionBtn')?.addEventListener('click', function() {
        document.querySelectorAll(employeeCheckboxesSelector).forEach(function(cb) {
            cb.checked = false;
        });
        updateSelectedCount();
    });

    document.getElementById('selectByDeptBtn')?.addEventListener('click', function() {
        const dept = prompt('Enter department name');
        if (!dept) return;

        const target = dept.trim().toLowerCase();

        document.querySelectorAll('.employee-item').forEach(function(item) {
            const checkbox = item.querySelector(employeeCheckboxesSelector);
            checkbox.checked = item.dataset.department.includes(target);
        });

        updateSelectedCount();
    });

    document.getElementById('selectBySiteBtn')?.addEventListener('click', function() {
        const site = prompt('Enter site name');
        if (!site) return;

        const target = site.trim().toLowerCase();

        document.querySelectorAll('.employee-item').forEach(function(item) {
            const checkbox = item.querySelector(employeeCheckboxesSelector);
            checkbox.checked = item.dataset.site.includes(target);
        });

        updateSelectedCount();
    });

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

    resetDefaultDates();
    updateSelectedCount();

    applyBtn?.addEventListener('click', async function() {
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const selectedEmployees = getSelectedEmployeesForCalendar();
        const shiftSelect = document.getElementById('bulkShiftSelect');
        const shiftId = shiftSelect.value;
        const shiftName = shiftSelect.options[shiftSelect.selectedIndex]?.text.split(' (')[0] || '';
        const startDate = document.getElementById('bulkStartDate').value;
        const endDate = document.getElementById('bulkEndDate').value;
        const checkConflicts = document.getElementById('checkConflicts').checked;
        const overrideExisting = document.getElementById('overrideExisting').checked;
        const excludeWeekends = document.getElementById('excludeWeekends').checked;

        if (selectedEmployees.length === 0) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning',
                    text: 'Please select at least one employee.'
                });
            }
            return;
        }

        if (!shiftId) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning',
                    text: 'Please select a shift.'
                });
            }
            return;
        }

        applyBtn.disabled = true;

        const originalBtnHtml = applyBtn.innerHTML;
        applyBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Applying...';

        const payload = {
            employee_ids: selectedEmployees.map(e => e.id),
            shift_planner_id: shiftId,
            start_date: startDate,
            end_date: endDate,
            check_conflicts: checkConflicts ? 1 : 0,
            override_existing: overrideExisting ? 1 : 0,
            exclude_weekends: excludeWeekends ? 1 : 0
        };

        try {
            const response = await fetch(bulkAssignUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const result = await response.json();

            if (!response.ok) {
                if (response.status === 422 && result.errors) {
                    const firstError = Object.values(result.errors)[0];
                    const message = Array.isArray(firstError) ? firstError[0] : 'Validation failed.';

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text: message
                        });
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: result.message || 'Failed to assign shift roster.'
                        });
                    }
                }
                return;
            }

                if (result.success) {
                if (typeof window.reloadRosterGrid === 'function') {
                    window.reloadRosterGrid();
                }
                if (bulkConflictWarning && result.data?.conflicts?.length > 0) {
                    bulkConflictWarning.style.display = 'block';
                    bulkConflictWarning.innerHTML = `
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> ${result.data.conflicts.length} conflict(s) found. Some entries were skipped.
                    `;
                } else if (bulkConflictWarning) {
                    bulkConflictWarning.style.display = 'none';
                }

                addEventsToCalendar(
                    selectedEmployees,
                    shiftId,
                    shiftName,
                    startDate,
                    endDate,
                    excludeWeekends
                );

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: result.message || 'Shift roster assigned successfully.'
                    });
                }

                const offcanvas = bootstrap.Offcanvas.getInstance(bulkAssignCanvas);
                if (offcanvas) {
                    offcanvas.hide();
                }
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: result.message || 'Failed to assign shift roster.'
                    });
                }
            }
        } catch (error) {
            console.error('Bulk assign error:', error);

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Something went wrong while assigning shifts.'
                });
            }
        } finally {
            applyBtn.disabled = false;
            applyBtn.innerHTML = originalBtnHtml;
        }
    });

    if (bulkAssignCanvas) {
        bulkAssignCanvas.addEventListener('hidden.bs.offcanvas', function() {
            resetBulkAssignForm();
        });
    }
});
</script>