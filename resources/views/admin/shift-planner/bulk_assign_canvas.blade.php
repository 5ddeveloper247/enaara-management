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
                    <div class="d-flex flex-wrap gap-2" id="dayCheckboxesContainer">
                        @php $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']; @endphp
                        @foreach($days as $day)
                            <div class="form-check form-check-inline">
                                <input class="form-check-input day-checkbox" type="checkbox" id="day{{ $day }}" name="days[]" value="{{ strtolower($day) }}" checked>
                                <label class="form-check-label text-white small" for="day{{ $day }}">{{ substr($day, 0, 3) }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
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
                    <select class="form-select bg-dark text-white border-secondary" id="bulkShiftSelect" name="shift_planner_id" required>
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

            <!-- Employee Selection -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-people me-2"></i>3. Select Employees
                </h6>

                <!-- Quick Selection -->
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-white">Bulk Actions</label>
                    <div class="d-flex gap-2 flex-wrap mb-2">
                        <button type="button" class="btn btn-sm btn-outline-light" id="selectAllBtn">Select All</button>
                        <button type="button" class="btn btn-sm btn-outline-light" id="selectByDeptBtn">By Department</button>
                        <button type="button" class="btn btn-sm btn-outline-light" id="selectBySiteBtn">By Site</button>
                        <button type="button" class="btn btn-sm btn-outline-light" id="clearSelectionBtn">Clear</button>
                    </div>

                    <div id="deptSelectionWrapper" style="display: none; transition: all 0.3s ease;" class="mb-2 animate__animated animate__fadeIn">
                        <select class="form-select form-select-sm bg-dark text-white border-secondary" id="deptFilterSelect">
                            <option value="">-- Select Department --</option>
                            @php 
                                // Fallback for departments if not passed correctly
                                $depts = $departments ?? \App\Models\Department::orderBy('name')->get();
                            @endphp
                            @foreach($depts as $dept)
                                <option value="{{ strtolower($dept->name) }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Employee List -->
                <div class="border rounded p-3 bg-dark" style="max-height: 250px; overflow-y: auto; border-color: #ffffff1a !important;">
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
                                <label class="form-check-label text-white small" for="emp{{ $employee->id }}">
                                    {{ $employee->full_name }}
                                    @if(!empty($employee->department->name ?? null))
                                        <span class="opacity-50 ms-2">[{{ $employee->department->name }}]</span>
                                    @endif
                                </label>
                            </div>
                        @empty
                            <div class="text-white-50 small text-center py-4">No active employees found.</div>
                        @endforelse
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-2 px-1">
                    <small class="opacity-75 text-white">
                        <span id="selectedCount" class="fw-bold text-info">0</span> employee(s) selected
                    </small>
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
                end = new Date(start.getFullYear(), start.getMonth() + 3, 0);
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

    function toggleAssignMode() {
        var isDefault = $('#modeDefault').is(':checked');
        console.log("Mode Change: isDefault =", isDefault);
        
        $('#defaultModeContainer').toggle(isDefault);
        
        if (isDefault) {
            $startDateInput.prop('readonly', true).css('background-color', '#e9ecef');
            $endDateInput.prop('readonly', true).css('background-color', '#e9ecef');
            
            // Auto-trigger "This Week" if no dates are set yet
            if (!$startDateInput.val()) {
                console.log("Initial load: Triggering This Week");
                window.setBulkDates('this_week');
            }
        } else {
            // In Custom mode, dates are NOT readonly
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

    $('#selectAllBtn').on('click', function() {
        $('input[name="employee_ids[]"]').prop('checked', true);
        $selectedCount.text($('input[name="employee_ids[]"]:checked').length);
    });

    $('#clearSelectionBtn').on('click', function() {
        $('input[name="employee_ids[]"]').prop('checked', false);
        $selectedCount.text(0);
    });

    $('#deptFilterSelect').on('change', function() {
        var dept = $(this).val();
        if(!dept) return;
        $('.employee-item').each(function() {
            var $item = $(this);
            var isMatch = (String($item.data('department')).toLowerCase().trim() === dept.toLowerCase().trim());
            $item.find('input').prop('checked', isMatch);
            if(isMatch) $('#employeeList').prepend($item);
        });
        $selectedCount.text($('input[name="employee_ids[]"]:checked').length);
    });

    $(document).on('change', 'input[name="employee_ids[]"]', function() {
        $selectedCount.text($('input[name="employee_ids[]"]:checked').length);
    });

    $('#applyBulkAssignBtn').on('click', function() {
        if (!$form[0].checkValidity()) {
            $form[0].reportValidity();
            return;
        }

        var ids = $('input[name="employee_ids[]"]:checked').map(function() { return this.value; }).get();
        if (ids.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Wait!', text: 'Select at least one employee.' });
            return;
        }

        $applyBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Applying...');

        var payload = {
            employee_ids: ids,
            shift_planner_id: $('#bulkShiftSelect').val(),
            start_date: $startDateInput.val(),
            end_date: $endDateInput.val(),
            assign_mode: $('#modeDefault').is(':checked') ? 'default' : 'custom',
            days: $('.day-checkbox:checked').map(function() { return this.value; }).get(),
            check_conflicts: $('#checkConflicts').is(':checked') ? 1 : 0,
            override_existing: $('#overrideExisting').is(':checked') ? 1 : 0,
            exclude_weekends: $('#excludeWeekends').is(':checked') ? 1 : 0
        };

        $.ajax({
            url: @json(route('admin.shift-roster.bulk-assign')),
            method: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': '@json(csrf_token())' },
            success: function(res) {
                if(res.success) {
                    Swal.fire({ icon: 'success', title: 'Assigned', text: res.message });
                    bootstrap.Offcanvas.getInstance($canvas[0]).hide();
                    location.reload(); 
                } else {
                    Swal.fire({ icon: 'error', title: 'Conflict', text: res.message });
                }
            },
            error: function(xhr) {
                var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error processing request';
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
            },
            complete: function() {
                $applyBtn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>Apply Assignment');
            }
        });
    });

    $('#selectByDeptBtn').on('click', function() { $('#deptSelectionWrapper').slideToggle(); });

    // Initial Trigger
    toggleAssignMode();
});
</script>
@endpush