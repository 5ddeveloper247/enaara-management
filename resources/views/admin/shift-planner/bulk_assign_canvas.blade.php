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

            <!-- Assignment Mode & Schedule -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-calendar-check me-2"></i>1. Schedule Assignment
                </h6>

                <!-- Mode Selection -->
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-white d-block mb-2">Selection Mode</label>
                    <div class="btn-group w-100 shadow-sm" role="group" aria-label="Assignment Mode">
                        <input type="radio" class="btn-check" name="assign_mode" id="modeDefault" value="default" checked autocomplete="off">
                        <label class="btn btn-outline-light py-2" for="modeDefault"><i class="bi bi-calendar-week me-1"></i>Default (Weeks)</label>

                        <input type="radio" class="btn-check" name="assign_mode" id="modeCustom" value="custom" autocomplete="off">
                        <label class="btn btn-outline-light py-2" for="modeCustom"><i class="bi bi-calendar-plus me-1"></i>Custom Range</label>
                    </div>
                </div>

                <!-- Default Mode Specifics (Weeks Selection) -->
                <div id="defaultModeContainer" class="mb-3 animate__animated animate__fadeIn">
                    <label class="form-label fw-semibold small text-white">Quick Date Selection</label>
                    <div class="d-flex gap-2 flex-wrap mb-2">
                        <button type="button" class="btn btn-sm btn-outline-light quick-date-btn" data-mode="this_week">This Week</button>
                        <button type="button" class="btn btn-sm btn-outline-light quick-date-btn" data-mode="next_week">Next Week</button>
                        <button type="button" class="btn btn-sm btn-outline-light quick-date-btn" data-mode="next_2_weeks">Next 2 Weeks</button>
                        <button type="button" class="btn btn-sm btn-outline-light quick-date-btn" data-mode="this_month">This Month</button>
                        <button type="button" class="btn btn-sm btn-outline-light quick-date-btn" data-mode="next_2_months">Next 2 Months</button>
                    </div>
                </div>

                <!-- Shared Date Range -->
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label for="bulkStartDate" class="form-label fw-semibold small text-white">Start Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="bulkStartDate" name="start_date" required style="color: #000 !important; background-color: #fff !important;">
                    </div>
                    <div class="col-6">
                        <label for="bulkEndDate" class="form-label fw-semibold small text-white">End Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="bulkEndDate" name="end_date" required style="color: #000 !important; background-color: #fff !important;">
                    </div>
                </div>

                <!-- Day Selection -->
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-white d-block mb-2">Repeat Days</label>
                    <p class="small text-white-50 mb-2">Select at least 1 working day (up to all 7). Checked days receive the selected shift; unchecked days are saved as OFF for the full date range.</p>
                    <div class="d-flex flex-wrap gap-2" id="dayCheckboxesContainer">
                        @php $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']; @endphp
                        @foreach($days as $day)
                            <div class="form-check form-check-inline">
                                <input class="form-check-input day-checkbox" type="checkbox" id="day{{ $day }}" name="days[]" value="{{ strtolower($day) }}">
                                <label class="form-check-label text-white small" for="day{{ $day }}">{{ substr($day, 0, 3) }}</label>
                            </div>
                        @endforeach
                    </div>
                    <div id="bulkRepeatDaysError" class="invalid-feedback d-block d-none mt-1"></div>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Shift Selection -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-clock-history me-2"></i>2. Assign Shift
                </h6>

                <p class="small text-white-50 mb-3">Select a shift, or enable custom time below.</p>

                <div class="mb-3">
                    <label for="bulkShiftSelect" class="form-label fw-semibold small text-white">
                        Shift <span id="bulkShiftRequiredMark" class="text-danger">*</span>
                    </label>
                    <select class="form-select bg-dark text-white border-secondary" id="bulkShiftSelect" name="shift_planner_id" required>
                        <option value="">Select Shift</option>
                        @forelse($shifts ?? [] as $shift)
                            <option value="{{ $shift->id }}"
                                    data-start="{{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }}"
                                    data-end="{{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}">
                                {{ $shift->name }} ({{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }})
                            </option>
                        @empty
                            <option value="">No shifts available</option>
                        @endforelse
                    </select>
                    <div id="bulkShiftSelectError" class="invalid-feedback d-block d-none"></div>
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="bulkUseCustomTime" value="1">
                    <label class="form-check-label text-white small" for="bulkUseCustomTime">Use custom start and end time</label>
                </div>
                <div id="bulkShiftTimeFields" style="display: none;">
                    <div class="row g-3 mb-0">
                        <div class="col-6">
                            <label for="bulkCustomStartTime" class="form-label fw-semibold small text-white">
                                Start Time <span class="text-danger">*</span>
                            </label>
                            <input type="time" class="form-control bulk-shift-time-input" id="bulkCustomStartTime" name="start_time" style="color: #000 !important; background-color: #fff !important;">
                            <div id="bulkCustomStartTimeError" class="invalid-feedback d-block d-none"></div>
                        </div>
                        <div class="col-6">
                            <label for="bulkCustomEndTime" class="form-label fw-semibold small text-white">
                                End Time <span class="text-danger">*</span>
                            </label>
                            <input type="time" class="form-control bulk-shift-time-input" id="bulkCustomEndTime" name="end_time" style="color: #000 !important; background-color: #fff !important;">
                            <div id="bulkCustomEndTimeError" class="invalid-feedback d-block d-none"></div>
                        </div>
                    </div>
                </div>


            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Employee Selection -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-people me-2"></i>3. Select Employees
                </h6>

                <!-- Tabs -->
                <div class="btn-group w-100 shadow-sm mb-3" role="group" aria-label="Employee Tabs">
                    <input type="radio" class="btn-check employee-tab-radio" name="employee_tab" id="tabInternal" value="internal" checked autocomplete="off">
                    <label class="btn btn-outline-light py-2" for="tabInternal"><i class="bi bi-person-badge me-1"></i>Internal Employees</label>

                    <input type="radio" class="btn-check employee-tab-radio" name="employee_tab" id="tabExternal" value="external" autocomplete="off">
                    <label class="btn btn-outline-light py-2" for="tabExternal"><i class="bi bi-person-lines-fill me-1"></i>External Employees</label>
                </div>

                <!-- Tab Content -->
                <div class="tab-content" id="bulkEmployeeTabsContent">
                    <!-- Internal Tab -->
                    <div class="tab-pane fade show active" id="internal-list" role="tabpanel">
                        <!-- Quick Selection Internal -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-white">Bulk Actions (Internal)</label>
                            <div class="d-flex gap-2 flex-wrap mb-2">
                                <button type="button" class="btn btn-sm btn-outline-light" id="selectAllInternalBtn">Select All</button>
                                <button type="button" class="btn btn-sm btn-outline-light" id="selectByDeptBtn">By Department</button>
                                <button type="button" class="btn btn-sm btn-outline-light" id="clearInternalBtn">Clear</button>
                            </div>

                            <div id="deptSelectionWrapper" style="display: none; transition: all 0.3s ease;" class="mb-2 animate__animated animate__fadeIn">
                                <select class="form-select form-select-sm bg-dark text-white border-secondary" id="deptFilterSelect">
                                    <option value="">-- Select Department --</option>
                                    @php 
                                        $depts = $departments ?? \App\Models\Department::orderBy('name')->get();
                                    @endphp
                                    @foreach($depts as $dept)
                                        <option value="{{ strtolower($dept->name) }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="border rounded p-3 bg-dark" style="max-height: 250px; overflow-y: auto; border-color: #ffffff1a !important;">
                            <div id="internalEmployeeList">
                                @forelse($employees ?? [] as $employee)
                                    <div class="form-check mb-2 internal-item"
                                         data-department="{{ strtolower($employee->department->name ?? '') }}">
                                        <input class="form-check-input employee-checkbox"
                                               type="checkbox"
                                               value="employee:{{ $employee->id }}"
                                               id="emp_employee_{{ $employee->id }}"
                                               name="employee_ids[]">
                                        <label class="form-check-label text-white small" for="emp_employee_{{ $employee->id }}">
                                            {{ $employee->rosterDisplayName() }}
                                            @if(!empty($employee->department->name ?? null))
                                                <span class="opacity-50 ms-2">[{{ $employee->department->name }}]</span>
                                            @endif
                                        </label>
                                    </div>
                                @empty
                                @endforelse
                            </div>
                            <div id="noInternalRecord" class="text-white-50 small text-center py-4" style="display: none;">No records found.</div>
                        </div>
                    </div>

                    <!-- External Tab -->
                    <div class="tab-pane fade" id="external-list" role="tabpanel">
                        <!-- Quick Selection External -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-white">Bulk Actions (External)</label>
                            <div class="d-flex gap-2 flex-wrap mb-2">
                                <button type="button" class="btn btn-sm btn-outline-light" id="selectAllExternalBtn">Select All</button>
                                <button type="button" class="btn btn-sm btn-outline-light" id="selectByVendorBtn">By Vendor</button>
                                <button type="button" class="btn btn-sm btn-outline-light" id="clearExternalBtn">Clear</button>
                            </div>

                            <div id="vendorSelectionWrapper" style="display: none; transition: all 0.3s ease;" class="mb-2 animate__animated animate__fadeIn">
                                <select class="form-select form-select-sm bg-dark text-white border-secondary" id="vendorFilterSelect">
                                    <option value="">-- Select Vendor --</option>
                                    @php 
                                        $vendors = \App\Models\ThirdParty::orderBy('third_party_name')->get();
                                    @endphp
                                    @foreach($vendors as $vendor)
                                        <option value="{{ strtolower($vendor->third_party_name) }}">{{ $vendor->third_party_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="border rounded p-3 bg-dark" style="max-height: 250px; overflow-y: auto; border-color: #ffffff1a !important;">
                            <div id="externalEmployeeList">
                                @forelse($outsourcedEmployees ?? [] as $employee)
                                    <div class="form-check mb-2 external-item"
                                         data-vendor="{{ strtolower($employee->contractorCompany->third_party_name ?? '') }}">
                                        <input class="form-check-input employee-checkbox"
                                               type="checkbox"
                                               value="outsourced:{{ $employee->id }}"
                                               id="emp_outsourced_{{ $employee->id }}"
                                               name="employee_ids[]">
                                        <label class="form-check-label text-white small" for="emp_outsourced_{{ $employee->id }}">
                                            {{ trim($employee->full_name ?? '') }}
                                            @if(!empty($employee->contractorCompany->third_party_name ?? null))
                                                <span class="opacity-50 ms-2">[{{ $employee->contractorCompany->third_party_name }}]</span>
                                            @endif
                                        </label>
                                    </div>
                                @empty
                                @endforelse
                            </div>
                            <div id="noExternalRecord" class="text-white-50 small text-center py-4" style="display: none;">No records found.</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-2 px-1">
                    <small class="opacity-75 text-white">
                        <span id="selectedCount" class="fw-bold text-info">0</span> employee(s) selected
                    </small>
                </div>

                <div class="mt-4 pt-3 border-top" style="border-color: #ffffff1a !important;">
                    <div class="mb-3">
                        <label for="bulkFloorSelect" class="form-label fw-semibold small text-white">
                            Floor <span class="opacity-75 fw-normal">(optional)</span>
                        </label>
                        <select class="form-select bg-dark text-white border-secondary" id="bulkFloorSelect" name="sbu_floor_id" disabled>
                            <option value="">Select employees first</option>
                        </select>
                        <div id="bulkFloorSelectError" class="invalid-feedback d-block d-none"></div>
                    </div>

                    <div class="mb-3">
                        <label for="bulkLocationText" class="form-label fw-semibold small text-white">Location</label>
                        <input type="text" class="form-control" id="bulkLocationText" name="location_text" maxlength="15" placeholder="Optional (3-15 characters)" autocomplete="off" style="color: #000 !important; background-color: #fff !important;">
                        <div id="bulkLocationTextError" class="invalid-feedback d-block d-none"></div>
                    </div>

                    <div class="mb-0">
                        <label for="bulkShiftNotes" class="form-label fw-semibold small text-white">Notes</label>
                        <textarea class="form-control" id="bulkShiftNotes" name="notes" rows="3" placeholder="Optional" style="color: #000 !important; background-color: #fff !important;"></textarea>
                        <div id="bulkShiftNotesError" class="invalid-feedback d-block d-none"></div>
                    </div>
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

@push('scripts')
<script>
$(document).ready(function() {
    console.log("Roster Canvas JS Init");

    var $startDateInput = $('#bulkStartDate');
    var $endDateInput = $('#bulkEndDate');
    var $form = $('#bulkAssignForm');
    var $applyBtn = $('#applyBulkAssignBtn');
    var $canvas = $('#bulkAssignCanvas');
    var $selectedCount = $('#selectedCount');
    var $repeatDaysError = $('#bulkRepeatDaysError');
    var MIN_REPEAT_DAYS = 1;
    var MAX_REPEAT_DAYS = 7;

    function getCheckedRepeatDayCount() {
        return $('.day-checkbox:checked').length;
    }

    function clearRepeatDaysError() {
        $repeatDaysError.addClass('d-none').text('');
        $('#dayCheckboxesContainer .day-checkbox').removeClass('is-invalid');
    }

    function showRepeatDaysError(message) {
        $repeatDaysError.removeClass('d-none').text(message);
        $('#dayCheckboxesContainer .day-checkbox').addClass('is-invalid');
    }

    function resetRepeatDays() {
        $('.day-checkbox').prop('checked', false);
        clearRepeatDaysError();
    }

    function validateRepeatDays() {
        clearRepeatDaysError();
        var count = getCheckedRepeatDayCount();

        if (count < MIN_REPEAT_DAYS) {
            showRepeatDaysError('Select at least ' + MIN_REPEAT_DAYS + ' working days.');
            return false;
        }

        if (count > MAX_REPEAT_DAYS) {
            showRepeatDaysError('You can select at most ' + MAX_REPEAT_DAYS + ' working days.');
            return false;
        }

        return true;
    }

    $(document).on('change', '.day-checkbox', function() {
        var $checkbox = $(this);
        if ($checkbox.is(':checked') && getCheckedRepeatDayCount() > MAX_REPEAT_DAYS) {
            $checkbox.prop('checked', false);
            showRepeatDaysError('You can select at most ' + MAX_REPEAT_DAYS + ' working days.');
            return;
        }

        clearRepeatDaysError();
    });

    // Utility: Format Date to YYYY-MM-DD
    function formatDate(date) {
        if (!(date instanceof Date)) return '';
        var year = date.getFullYear();
        var month = ('0' + (date.getMonth() + 1)).slice(-2);
        var day = ('0' + date.getDate()).slice(-2);
        return year + '-' + month + '-' + day;
    }

    // Utility: Get Monday of a week
    function getMonday(d) {
        d = new Date(d);
        var day = d.getDay();
        var diff = d.getDate() - day + (day == 0 ? -6 : 1);
        return new Date(d.setDate(diff));
    }

    // Function to calculate and SET dates
    window.setBulkDates = function(mode) {
        console.log("setBulkDates triggered with mode:", mode);
        var start = new Date();
        var end = new Date();
        start.setHours(0,0,0,0);
        end.setHours(0,0,0,0);

        switch(mode) {
            case 'this_week':
                start = new Date();
                end = getMonday(new Date());
                end.setDate(end.getDate() + 6);
                break;
            case 'next_week':
                start = getMonday(new Date());
                start.setDate(start.getDate() + 7);
                end = new Date(start);
                end.setDate(start.getDate() + 6);
                break;
            case 'next_2_weeks':
                start = getMonday(new Date());
                start.setDate(start.getDate() + 7);
                end = new Date(start);
                end.setDate(start.getDate() + 13);
                break;
            case 'this_month':
                start = new Date();
                end = new Date(start.getFullYear(), start.getMonth() + 1, 0);
                break;
            case 'next_2_months':
                start = new Date(start.getFullYear(), start.getMonth() + 1, 1);
                end = new Date(start.getFullYear(), start.getMonth() + 2, 0);
                break;
        }

        var startStr = formatDate(start);
        var endStr = formatDate(end);
        
        console.log("Calculated Dates:", startStr, "to", endStr);
        
        $startDateInput.val(startStr);
        $endDateInput.val(endStr);
        
        $('.quick-date-btn').removeClass('btn-light').addClass('btn-outline-light');
        $('.quick-date-btn[data-mode="' + mode + '"]').removeClass('btn-outline-light').addClass('btn-light');
    };

    function clearBulkShiftFieldErrors() {
        $('#bulkShiftSelect, #bulkCustomStartTime, #bulkCustomEndTime, #bulkFloorSelect, #bulkLocationText, #bulkShiftNotes').removeClass('is-invalid');
        $('#bulkShiftSelectError, #bulkCustomStartTimeError, #bulkCustomEndTimeError, #bulkFloorSelectError, #bulkLocationTextError, #bulkShiftNotesError').addClass('d-none').text('');
    }

    var bulkFloorLoadTimer = null;

    function getSelectedEmployeeIds() {
        return $('input[name="employee_ids[]"]:checked').map(function() { return this.value; }).get();
    }

    function populateBulkFloorOptions(options) {
        var $floor = $('#bulkFloorSelect');
        var html = '<option value="">Select floor</option>';
        (options || []).forEach(function(option) {
            html += '<option value="' + String(option.id) + '">' + option.label + '</option>';
        });
        $floor.html(html);
        $floor.prop('disabled', (options || []).length === 0);
    }

    function loadBulkFloorOptions() {
        var ids = getSelectedEmployeeIds();
        var url = window.rosterBulkFloorOptionsUrl || '';

        if (!ids.length || !url) {
            $('#bulkFloorSelect').html('<option value="">Select employees first</option>').prop('disabled', true);
            return;
        }

        $.ajax({
            url: url,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ employee_ids: ids }),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: function(res) {
                var options = (res && res.success && Array.isArray(res.data)) ? res.data : [];
                populateBulkFloorOptions(options);
                if (!options.length) {
                    showBulkFieldError('#bulkFloorSelect', '#bulkFloorSelectError', 'No floors found for selected employees.');
                }
            },
            error: function() {
                populateBulkFloorOptions([]);
                showBulkFieldError('#bulkFloorSelect', '#bulkFloorSelectError', 'Could not load floor options.');
            }
        });
    }

    function scheduleBulkFloorOptionsReload() {
        if (bulkFloorLoadTimer) {
            clearTimeout(bulkFloorLoadTimer);
        }
        bulkFloorLoadTimer = setTimeout(loadBulkFloorOptions, 200);
    }

    function validateBulkLocationField() {
        var value = ($('#bulkLocationText').val() || '').trim();
        $('#bulkLocationText').removeClass('is-invalid');
        $('#bulkLocationTextError').addClass('d-none').text('');

        if (value === '') {
            return true;
        }
        if (value.length < 3) {
            showBulkFieldError('#bulkLocationText', '#bulkLocationTextError', 'Location must be at least 3 characters.');
            return false;
        }
        if (value.length > 15) {
            showBulkFieldError('#bulkLocationText', '#bulkLocationTextError', 'Location may not be greater than 15 characters.');
            return false;
        }
        if (/^\d+$/.test(value)) {
            showBulkFieldError('#bulkLocationText', '#bulkLocationTextError', 'Location cannot contain only digits.');
            return false;
        }
        if (!/[A-Za-z]/.test(value)) {
            showBulkFieldError('#bulkLocationText', '#bulkLocationTextError', 'Location must contain at least one letter.');
            return false;
        }
        if (!/^[A-Za-z0-9\s\-'.]+$/.test(value)) {
            showBulkFieldError('#bulkLocationText', '#bulkLocationTextError', 'Location may only contain letters, numbers, spaces, hyphens, apostrophes, or periods.');
            return false;
        }
        return true;
    }

    function validateBulkPlacementFields() {
        var valid = true;

        if (!validateBulkLocationField()) {
            valid = false;
        }

        var notes = ($('#bulkShiftNotes').val() || '').trim();
        if (notes.length > 1000) {
            showBulkFieldError('#bulkShiftNotes', '#bulkShiftNotesError', 'Notes must not exceed 1000 characters.');
            valid = false;
        }

        return valid;
    }

    function resetBulkPlacementFields() {
        $('#bulkFloorSelect').html('<option value="">Select employees first</option>').prop('disabled', true).val('');
        $('#bulkLocationText, #bulkShiftNotes').val('');
        $('#bulkFloorSelect, #bulkLocationText, #bulkShiftNotes').removeClass('is-invalid');
        $('#bulkFloorSelectError, #bulkLocationTextError, #bulkShiftNotesError').addClass('d-none').text('');
    }

    function showBulkFieldError(fieldId, errorId, message) {
        $(fieldId).addClass('is-invalid');
        $(errorId).removeClass('d-none').text(message);
    }

    function isCustomAssignMode() {
        return $('#modeCustom').is(':checked');
    }

    function bulkUsesCustomTime() {
        return $('#bulkUseCustomTime').is(':checked');
    }

    function toggleBulkCustomTimeFields() {
        var useCustom = bulkUsesCustomTime();

        $('#bulkShiftTimeFields').toggle(useCustom);
        $('#bulkShiftRequiredMark').toggle(!useCustom);
        $('#bulkShiftSelect').prop('required', !useCustom);
        if (!useCustom) {
            $('#bulkCustomStartTime, #bulkCustomEndTime').val('');
        }
        clearBulkShiftFieldErrors();
    }

    function resetBulkShiftCustomTime() {
        $('#bulkUseCustomTime').prop('checked', false);
        $('#bulkCustomStartTime, #bulkCustomEndTime').val('');
        $('#bulkShiftTimeFields').hide();
        toggleBulkCustomTimeFields();
    }

    function toggleShiftAssignmentUi() {
        clearBulkShiftFieldErrors();
        resetBulkShiftCustomTime();
    }

    $('#bulkUseCustomTime').on('change', toggleBulkCustomTimeFields);

    $('#bulkShiftSelect').on('change', function() {
        if (!bulkUsesCustomTime()) {
            return;
        }
        var opt = this.options[this.selectedIndex];
        if (!opt || !opt.value) {
            return;
        }
        var start = opt.getAttribute('data-start');
        var end = opt.getAttribute('data-end');
        if (start) {
            $('#bulkCustomStartTime').val(start);
        }
        if (end) {
            $('#bulkCustomEndTime').val(end);
        }
    });

    function toggleAssignMode() {
        var isDefault = $('#modeDefault').is(':checked');

        $('#defaultModeContainer').toggle(isDefault);
        toggleShiftAssignmentUi();

        if (isDefault) {
            $startDateInput.prop('readonly', true).css('background-color', '#e9ecef');
            $endDateInput.prop('readonly', true).css('background-color', '#e9ecef');

            if (!$startDateInput.val()) {
                window.setBulkDates('this_week');
            }
        } else {
            $startDateInput.prop('readonly', false).css('background-color', '#fff');
            $endDateInput.prop('readonly', false).css('background-color', '#fff');
        }
    }

    // Event Handlers
    $('input[name="assign_mode"]').on('change', toggleAssignMode);

    $(document).on('click', '.quick-date-btn', function(e) {
        e.preventDefault();
        var mode = $(this).data('mode');
        window.setBulkDates(mode);
    });

    // Employee Tabs logic
    $('.employee-tab-radio').on('change', function() {
        if ($('#tabInternal').is(':checked')) {
            $('#internal-list').addClass('show active');
            $('#external-list').removeClass('show active');
        } else {
            $('#external-list').addClass('show active');
            $('#internal-list').removeClass('show active');
        }
    });

    // Tab specific Select All / Clear
    $('#selectAllInternalBtn').on('click', function() {
        $('#internalEmployeeList .internal-item:visible input').prop('checked', true);
        updateSelectedCount();
    });

    $('#selectAllExternalBtn').on('click', function() {
        $('#externalEmployeeList .external-item:visible input').prop('checked', true);
        updateSelectedCount();
    });

    $('#clearInternalBtn').on('click', function() {
        $('#internalEmployeeList input').prop('checked', false);
        $('#deptFilterSelect').val('');
        $('.internal-item').show();
        $('#noInternalRecord').hide();
        updateSelectedCount();
    });

    $('#clearExternalBtn').on('click', function() {
        $('#externalEmployeeList input').prop('checked', false);
        $('#vendorFilterSelect').val('');
        $('.external-item').show();
        $('#noExternalRecord').hide();
        updateSelectedCount();
    });

    // Filtering Logic
    $('#deptFilterSelect').on('change', function() {
        var dept = $(this).val();
        if(!dept) {
            $('.internal-item').show();
            $('#noInternalRecord').hide();
            return;
        }
        var visibleCount = 0;
        $('.internal-item').each(function() {
            var $item = $(this);
            var isMatch = (String($item.data('department')).toLowerCase().trim() === dept.toLowerCase().trim());
            $item.toggle(isMatch);
            if(isMatch) {
                $item.find('input').prop('checked', true);
                visibleCount++;
            }
        });
        $('#noInternalRecord').toggle(visibleCount === 0);
        updateSelectedCount();
    });

    $('#vendorFilterSelect').on('change', function() {
        var vendor = $(this).val();
        if(!vendor) {
            $('.external-item').show();
            $('#noExternalRecord').hide();
            return;
        }
        var visibleCount = 0;
        $('.external-item').each(function() {
            var $item = $(this);
            var isMatch = (String($item.data('vendor')).toLowerCase().trim() === vendor.toLowerCase().trim());
            $item.toggle(isMatch);
            if(isMatch) {
                $item.find('input').prop('checked', true);
                visibleCount++;
            }
        });
        $('#noExternalRecord').toggle(visibleCount === 0);
        updateSelectedCount();
    });

    function updateSelectedCount() {
        $selectedCount.text($('input[name="employee_ids[]"]:checked').length);
        scheduleBulkFloorOptionsReload();
    }

    $(document).on('change', 'input[name="employee_ids[]"]', function() {
        updateSelectedCount();
    });

    function validateBulkShiftFields() {
        clearBulkShiftFieldErrors();
        var valid = true;

        if (bulkUsesCustomTime()) {
            var startTime = $('#bulkCustomStartTime').val();
            var endTime = $('#bulkCustomEndTime').val();
            if (!startTime) {
                showBulkFieldError('#bulkCustomStartTime', '#bulkCustomStartTimeError', 'Start time is required.');
                valid = false;
            }
            if (!endTime) {
                showBulkFieldError('#bulkCustomEndTime', '#bulkCustomEndTimeError', 'End time is required.');
                valid = false;
            }
            if (startTime && endTime && startTime === endTime) {
                showBulkFieldError('#bulkCustomEndTime', '#bulkCustomEndTimeError', 'End time must be different from start time.');
                valid = false;
            }
        } else if (!$('#bulkShiftSelect').val()) {
            showBulkFieldError('#bulkShiftSelect', '#bulkShiftSelectError', 'Please select a shift or enable custom time.');
            valid = false;
        }

        return valid;
    }

    $('#applyBulkAssignBtn').on('click', function() {
        if (!$form[0].checkValidity()) {
            $form[0].reportValidity();
            return;
        }

        if (!validateBulkShiftFields()) {
            return;
        }

        if (!validateRepeatDays()) {
            return;
        }

        var ids = getSelectedEmployeeIds();
        if (ids.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Wait!',
                text: 'Select at least one employee.',
                confirmButtonColor: '#1a237e'
            });
            return;
        }

        if (!validateBulkPlacementFields()) {
            return;
        }

        $applyBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Applying...');

        var isCustom = isCustomAssignMode();
        var payload = {
            employee_ids: ids,
            start_date: $startDateInput.val(),
            end_date: $endDateInput.val(),
            assign_mode: isCustom ? 'custom' : 'default',
            days: $('.day-checkbox:checked').map(function() { return this.value; }).get(),
            off_days: $('.day-checkbox:not(:checked)').map(function() { return this.value; }).get(),
            check_conflicts: $('#checkConflicts').is(':checked') ? 1 : 0,
            override_existing: $('#overrideExisting').is(':checked') ? 1 : 0,
            exclude_weekends: $('#excludeWeekends').is(':checked') ? 1 : 0
        };

        var useBulkCustom = bulkUsesCustomTime();
        payload.is_custom_time = useBulkCustom ? 1 : 0;
        if (!useBulkCustom) {
            var bulkShiftId = $('#bulkShiftSelect').val();
            if (bulkShiftId) {
                payload.shift_planner_id = bulkShiftId;
            }
        }
        if (useBulkCustom) {
            payload.start_time = $('#bulkCustomStartTime').val();
            payload.end_time = $('#bulkCustomEndTime').val();
        }

        var bulkFloorVal = $('#bulkFloorSelect').val();
        var bulkFloorId = bulkFloorVal ? parseInt(bulkFloorVal, 10) : null;
        payload.sbu_floor_id = Number.isFinite(bulkFloorId) && bulkFloorId > 0 ? bulkFloorId : null;
        var bulkLocation = ($('#bulkLocationText').val() || '').trim();
        payload.location_text = bulkLocation === '' ? null : bulkLocation;
        var bulkNotes = ($('#bulkShiftNotes').val() || '').trim();
        payload.notes = bulkNotes === '' ? null : bulkNotes;

        $.ajax({
            url: @json(route('admin.shift-roster.bulk-assign')),
            method: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(res) {
                if (res.success) {
                    showSuccess(res.message, 'Assigned').then(function() {
                        location.reload();
                    });
                } else {
                    showError(res.message, 'Conflict');
                }
            },
            error: function(xhr) {
                var data = xhr.responseJSON || {};
                var msg = data.message || 'Error processing request';
                if (xhr.status === 422 && data.errors) {
                    if (data.errors.shift_planner_id) {
                        showBulkFieldError('#bulkShiftSelect', '#bulkShiftSelectError', data.errors.shift_planner_id[0]);
                    }
                    if (data.errors.start_time) {
                        showBulkFieldError('#bulkCustomStartTime', '#bulkCustomStartTimeError', data.errors.start_time[0]);
                    }
                    if (data.errors.end_time) {
                        showBulkFieldError('#bulkCustomEndTime', '#bulkCustomEndTimeError', data.errors.end_time[0]);
                    }
                    if (data.errors.days) {
                        showRepeatDaysError(data.errors.days[0]);
                    }
                    if (data.errors.sbu_floor_id) {
                        showBulkFieldError('#bulkFloorSelect', '#bulkFloorSelectError', data.errors.sbu_floor_id[0]);
                    }
                    if (data.errors.location_text) {
                        showBulkFieldError('#bulkLocationText', '#bulkLocationTextError', data.errors.location_text[0]);
                    }
                    if (data.errors.notes) {
                        showBulkFieldError('#bulkShiftNotes', '#bulkShiftNotesError', data.errors.notes[0]);
                    }
                    msg = Object.values(data.errors).flat().join('<br>');
                }
                showError(msg);
            },
            complete: function() {
                $applyBtn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>Apply Assignment');
            }
        });
    });

    $('#selectByDeptBtn').on('click', function() { $('#deptSelectionWrapper').slideToggle(); });
    $('#selectByVendorBtn').on('click', function() { $('#vendorSelectionWrapper').slideToggle(); });

    var bulkCanvasEl = document.getElementById('bulkAssignCanvas');
    if (bulkCanvasEl) {
        bulkCanvasEl.addEventListener('shown.bs.offcanvas', function() {
            resetRepeatDays();
            resetBulkPlacementFields();
            toggleShiftAssignmentUi();
        });
    }

    resetRepeatDays();
    resetBulkPlacementFields();
    toggleAssignMode();
});
</script>
@endpush
