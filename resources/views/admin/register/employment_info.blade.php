{{-- STEP 2: Employment Information --}}
@push('styles')
<style>
    .hybrid-day-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem 0.625rem;
        align-items: stretch;
    }
    .hybrid-day-chip {
        flex: 0 0 auto;
    }
    .hybrid-day-chip .hybrid-day-input {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }
    .hybrid-day-chip .hybrid-day-label {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 3.25rem;
        padding: 0.5rem 0.9rem;
        margin: 0;
        border-radius: 999px;
        border: 2px solid #012445;
        background: rgba(255, 255, 255, 0.95);
        color: #1a2b3c;
        font-size: 0.8125rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        cursor: pointer;
        user-select: none;
        transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
        box-shadow: 0 0 4px 2px rgba(90, 89, 89, 0.12);
    }
    .hybrid-day-chip .hybrid-day-input:focus-visible + .hybrid-day-label {
        outline: 2px solid #0d6efd;
        outline-offset: 3px;
    }
    .hybrid-day-chip .hybrid-day-input:checked + .hybrid-day-label {
        background: #012445;
        color: #fff;
        border-color: #012445;
        box-shadow: 0 0 7px 4px rgba(90, 89, 89, 0.18);
    }
    .hybrid-day-chip .hybrid-day-input:not(:checked) + .hybrid-day-label:hover {
        background: rgba(1, 36, 69, 0.06);
    }
    .hybrid-day-chips.is-invalid-step {
        outline: 2px solid #dc3545;
        outline-offset: 4px;
        border-radius: 0.75rem;
        padding: 0.35rem;
        margin: -0.35rem 0 0 -0.35rem;
    }
    .dept-multi-section.is-locked .form-label {
        opacity: 0.8;
    }
    .dept-multi-section.is-locked .dept-input-box {
        opacity: 0.72;
        cursor: not-allowed;
        background-color: rgba(1, 36, 69, 0.04) !important;
    }
    .dept-input-box {
        background: #fff;
        border: 1.5px solid #ced4da;
        border-radius: 10px;
        padding: 8px 40px 8px 8px;
        min-height: 46px;
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        align-items: center;
        cursor: text;
        position: relative;
        transition: border-color .15s, box-shadow .15s;
    }
    .dept-input-box:hover {
        border-color: #adb5bd;
    }
    .dept-input-box.open,
    .dept-input-box:focus-within {
        border-color: #86b7fe;
        box-shadow: 0 0 0 3px rgba(13,110,253,.12);
        outline: none;
    }
    .dept-input-box.is-invalid {
        border-color: #dc3545;
    }
    .dept-input-box.is-invalid.open {
        box-shadow: 0 0 0 3px rgba(220,53,69,.12);
    }
    .dept-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #e9f2ff;
        border: 1px solid #b6d4fe;
        color: #0a3060;
        font-size: 12px;
        font-weight: 500;
        padding: 3px 6px 3px 10px;
        border-radius: 999px;
        cursor: default;
        transition: background .12s;
    }
    .dept-chip:hover {
        background: #c8dffe;
    }
    .dept-chip-x {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: #185FA5;
        flex-shrink: 0;
    }
    .dept-chip-x:hover {
        background: #85B7EB;
        color: #042C53;
    }
    .dept-ph {
        font-size: 14px;
        color: #adb5bd;
        padding: 2px 4px;
        pointer-events: none;
    }
    .dept-chevron {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
        color: #adb5bd;
        transition: transform .18s;
    }
    .dept-input-box.open .dept-chevron {
        transform: translateY(-50%) rotate(180deg);
    }
    .dept-dropdown {
        background: #fff;
        border: 1px solid #ced4da;
        border-radius: 10px;
        margin-top: 6px;
        overflow: hidden;
        z-index: 1050;
        position: relative;
    }
    .dept-search-row {
        padding: 8px;
        border-bottom: 1px solid #f0f0f0;
    }
    .dept-search-row input {
        width: 100%;
        border: 1px solid #ced4da;
        border-radius: 8px;
        padding: 7px 12px;
        font-size: 13px;
        background: #f8f9fa;
        color: #212529;
        outline: none;
        transition: border-color .15s, box-shadow .15s;
    }
    .dept-search-row input:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 2px rgba(13,110,253,.1);
        background: #fff;
    }
    .dept-opt-list {
        max-height: 210px;
        overflow-y: auto;
        padding: 4px 0;
    }
    .dept-opt {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 14px;
        cursor: pointer;
        font-size: 14px;
        color: #212529;
        transition: background .1s;
        user-select: none;
    }
    .dept-opt:hover {
        background: #f8f9fa;
    }
    .dept-opt.picked {
        background: #e9f2ff;
    }
    .dept-opt.picked .dept-opt-name {
        color: #0a3060;
        font-weight: 500;
    }
    .dept-opt-cb {
        width: 17px;
        height: 17px;
        border-radius: 5px;
        border: 1.5px solid #adb5bd;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all .12s;
    }
    .dept-opt.picked .dept-opt-cb {
        background: #0d6efd;
        border-color: #0d6efd;
    }
    .dept-opt-ck {
        display: none;
        width: 10px;
        height: 10px;
    }
    .dept-opt.picked .dept-opt-ck {
        display: block;
    }
    .dept-no-result {
        padding: 16px;
        font-size: 13px;
        color: #adb5bd;
        text-align: center;
    }
    #standardScheduleSection .form-check-input[type="radio"] {
        appearance: radio;
        -webkit-appearance: radio;
        -moz-appearance: radio;
        width: 1.125rem;
        height: 1.125rem;
        margin-top: 0.25rem;
        flex-shrink: 0;
        background-color: #fff !important;
        border: 2px solid #012445 !important;
        box-shadow: none !important;
        accent-color: #012445;
    }
    #standardScheduleSection .form-check-input[type="checkbox"] {
        appearance: checkbox;
        -webkit-appearance: checkbox;
        -moz-appearance: checkbox;
        width: 1.125rem;
        height: 1.125rem;
        margin-top: 0.15rem;
        flex-shrink: 0;
        background-color: #fff !important;
        border: 2px solid #012445 !important;
        box-shadow: none !important;
        accent-color: #012445;
    }
    #standardScheduleSection .form-check {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
    }
    #standardScheduleSection .form-check-label {
        color: #1a2b3c;
        opacity: 1;
        font-weight: 500;
    }
</style>
@endpush
<div class="step" id="step-2">
    <div class="section-title d-flex align-items-center justify-content-between">
        <span>Section B — Employment Information</span>
        <small class="text-muted">Job mapping, category and placement details</small>
    </div>
    <div class="row g-2">

        <div class="col-md-3">
            <label class="form-label">TAS ID</label>
            <input type="text" name="biometric_id" class="form-control" placeholder="Biometric ID">
        </div>

        <div class="col-md-3">
            <label class="form-label">Employee Number</label>
            <input type="text" id="employee_number_display" class="form-control" disabled readonly
                placeholder="— Select Organization, SBU &amp; Role —"
                style="opacity:1;background:rgba(255,255,255,.06)!important;cursor:not-allowed;">
        </div>

        <div class="col-12">
            <div class="p-3 rounded-3 border bg-light bg-opacity-25">
                <label class="form-label fw-semibold mb-2 d-block">Resource Type <span class="text-danger">*</span></label>
                <div id="categoryRadioGroup" class="d-flex flex-wrap gap-2 mb-2">
                    <div class="form-check d-flex align-items-center gap-2 px-3 py-2 rounded-pill border bg-white">
                        <input class="check-input" type="radio" name="employment_category" id="catIntern" value="intern">
                        <label class="form-check-label" for="catIntern">Intern</label>
                    </div>
                    <div class="form-check d-flex align-items-center gap-2 px-3 py-2 rounded-pill border bg-white">
                        <input class="check-input" type="radio" name="employment_category" id="catConsultant" value="consultant">
                        <label class="form-check-label" for="catConsultant">Consultant</label>
                    </div>
                    <div class="form-check d-flex align-items-center gap-2 px-3 py-2 rounded-pill border bg-white">
                        <input class="check-input" type="radio" name="employment_category" id="catEmployee" value="employee">
                        <label class="form-check-label" for="catEmployee">Employment type</label>
                    </div>
                </div>

                <div class="row g-2" id="internFields" style="display:none;">
                    <div class="col-md-3">
                        <label class="form-label">Intern Type <span class="text-danger">*</span></label>
                        <select name="intern_type" class="form-select">
                            <option value="">Select</option>
                            <option value="paid">Paid</option>
                            <option value="unpaid">Unpaid</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Intern Duration <span class="text-danger">*</span></label>
                        <input type="text" name="intern_duration" class="form-control" placeholder="e.g. 3 Months">
                    </div>
                </div>

                <div class="row g-2" id="employeeResourceFields" style="display:none;">
                    <div class="col-md-3">
                        <label class="form-label">Permanent / Contractual <span class="text-danger">*</span></label>
                        <select name="employment_type" id="resourceEmploymentType" class="form-select">
                            <option value="">— Select —</option>
                            <option value="permanent">Permanent</option>
                            <option value="contractual">Contractual</option>
                        </select>
                    </div>
                    <div class="col-md-3" id="employeeContractualSub" style="display:none;">
                        <label class="form-label">Contract type <span class="text-danger">*</span></label>
                        <select name="contractual_type" id="resourceContractualType" class="form-select">
                            <option value="">— Select —</option>
                            <option value="open">Open Ended</option>
                            <option value="time_bound">Time Bound</option>
                        </select>
                    </div>
                    <div class="col-md-6" id="timeBoundContractDates" style="display:none;">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Contract start date <span class="text-danger">*</span></label>
                                <input type="date" name="contract_start_date" id="contract_start_date" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contract end date <span class="text-danger">*</span></label>
                                <input type="date" name="contract_end_date" id="contract_end_date" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="row g-2 align-items-start">
                <div class="col-12 col-md-4">
                    <label class="form-label">Organization <span class="text-danger">*</span></label>
                    <select name="organization_id" id="org_select" class="form-select"
                        style="background-color:transparent !important; border:1px solid #012445; box-shadow:0 0 4px 2px #5a59593d; appearance:none; -webkit-appearance:none;">
                        <option value="">— Select Organization —</option>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">SBU <span class="text-danger">*</span></label>
                    <select name="sbu_id" id="sbu_select" class="form-select" disabled
                        style="background-color:transparent !important; border:1px solid #012445; box-shadow:0 0 4px 2px #5a59593d; appearance:none; -webkit-appearance:none;">
                        <option value="">— Select Organization first —</option>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Role <span class="text-danger">*</span></label>
                    <select name="role_id" id="role_select" class="form-select" disabled
                        style="background-color:transparent !important; border:1px solid #012445; box-shadow:0 0 4px 2px #5a59593d; appearance:none; -webkit-appearance:none;">
                        <option value="">— Select SBU first —</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="row g-2 align-items-start">
                <div class="col-12 col-md-4 dept-multi-section is-locked" id="deptMultiSection">
                    <label class="form-label">Departments <span class="text-muted fw-normal small">(optional)</span></label>
                    <select name="department_ids[]" id="dept_select" class="form-select dept-multi-select d-none" multiple size="4" disabled></select>
                    <div class="dept-input-box" id="dept-box" onclick="deptBoxClick(event)">
                        <div id="dept-chips" style="display:contents"></div>
                        <span class="dept-ph" id="dept-ph">Select Departments...</span>
                        <svg class="dept-chevron" id="dept-chevron" width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="dept-dropdown" id="dept-dd" style="display:none">
                        <div class="dept-search-row">
                            <input id="dept-search" placeholder="Search Department..." oninput="deptRenderList()" onclick="event.stopPropagation()" autocomplete="off">
                        </div>
                        <div class="dept-opt-list" id="dept-list"></div>
                    </div>
                    <small class="text-muted d-block mt-1 dept-multi-hint">Select Organization and SBU to enable this list. Optional.</small>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Date of Joining <span class="text-danger">*</span></label>
                    <input type="date" name="join_date" class="form-control">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Designation</label>
                    <input type="text" name="designation" class="form-control" placeholder="e.g. Software Engineer">
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <label class="form-label">Grade</label>
            <input type="text" name="grade" class="form-control" placeholder="e.g. G1">
        </div>

        <div class="col-md-3">
            <label class="form-label">Branch</label>
            <input type="text" name="branch" class="form-control" placeholder="e.g. Islamabad">
        </div>

        <div class="col-md-3">
            <label class="form-label">Location</label>
            <input type="text" name="location" class="form-control" placeholder="e.g. Ground Floor">
        </div>

        <div class="col-12">
            <div class="p-3 rounded-3 border bg-light bg-opacity-25">
                <label class="form-label fw-semibold mb-2 d-block">Work Arrangement <span class="text-danger">*</span></label>
                <div class="row g-3 align-items-start">
                    <div class="col-12 col-lg-4 col-md-5">
                        <select name="engagement_mode" id="engagementMode" class="form-select" aria-label="Work arrangement">
                            <option value="">— Select —</option>
                            <option value="shifts">Shift-Based</option>
                            <option value="standard">Standard</option>
                            <option value="remote">Remote (Work From Home)</option>
                            <option value="hybrid">Hybrid</option>
                        </select>
                    </div>
                    <div class="col-12 col-lg-8 col-md-7" id="hybridDaysWrapper" style="display:none;">
                        <label class="form-label mb-2" id="hybridDaysLegend">Hybrid — on-site days <span class="text-danger">*</span></label>
                        <div class="hybrid-day-chips" role="group" aria-labelledby="hybridDaysLegend">
                            @foreach (['mon' => 'Mon', 'tue' => 'Tue', 'wed' => 'Wed', 'thu' => 'Thu', 'fri' => 'Fri', 'sat' => 'Sat', 'sun' => 'Sun'] as $dayKey => $dayLabel)
                                <div class="hybrid-day-chip position-relative">
                                    <input class="hybrid-day-input" type="checkbox" name="hybrid_days[]" id="hybrid_{{ $dayKey }}" value="{{ $dayKey }}">
                                    <label class="hybrid-day-label" for="hybrid_{{ $dayKey }}">{{ $dayLabel }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-12" id="standardScheduleSection" style="display:none;">
                        <div class="border rounded-3 p-3 bg-white bg-opacity-50">
                            <label class="form-label fw-semibold mb-2 d-block">Standard office hours <span class="text-danger">*</span></label>
                            <div class="d-flex flex-wrap gap-3 mb-3" role="group" aria-label="Default or custom schedule">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="standard_schedule_mode" id="standardScheduleDefault" value="default" checked>
                                    <label class="form-check-label" for="standardScheduleDefault">Default</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="standard_schedule_mode" id="standardScheduleCustom" value="custom">
                                    <label class="form-check-label" for="standardScheduleCustom">Custom</label>
                                </div>
                            </div>
                            <div id="standardScheduleDefaultReadonly" class="mb-0">
                                <p class="small text-muted mb-2" id="standardScheduleSourceLabel"></p>
                                <div id="standardScheduleReadonlyBody" class="small"></div>
                            </div>
                            <div id="standardScheduleCustomFields" class="d-none">
                                <div class="mb-3">
                                    <label class="form-label">Working days <span class="text-danger">*</span></label>
                                    <div class="d-flex flex-wrap gap-2 gap-md-3">
                                        @foreach (['monday' => 'Mon', 'tuesday' => 'Tue', 'wednesday' => 'Wed', 'thursday' => 'Thu', 'friday' => 'Fri', 'saturday' => 'Sat', 'sunday' => 'Sun'] as $dayValue => $dayLabel)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="empWorkingDay_{{ $dayValue }}" name="working_days[]" value="{{ $dayValue }}">
                                                <label class="form-check-label" for="empWorkingDay_{{ $dayValue }}">{{ $dayLabel }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-md-3">
                                        <label class="form-label" for="empWorkingStartTime">Working start <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control" id="empWorkingStartTime" name="working_start_time" step="60">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label" for="empWorkingEndTime">Working end <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control" id="empWorkingEndTime" name="working_end_time" step="60">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label" for="empOpeningGrace">Check-in grace (min)</label>
                                        <input type="number" class="form-control" id="empOpeningGrace" name="opening_grace_period" min="0" max="600" placeholder="Optional">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label" for="empClosingGrace">Check-out grace (min)</label>
                                        <input type="number" class="form-control" id="empClosingGrace" name="closing_grace_period" min="0" max="600" placeholder="Optional">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
    window._orgsData = @json($orgsData ?? []);
    window._rolesData = @json($rolesData ?? []);
    window.__employeeEditMode = @json(isset($employee));
    window.__previewEmployeeCodeUrl = @json(route('admin.employee.preview_code'));
</script>
<script src="{{ asset('js/employee-register-employment.js') }}" defer></script>
@endpush
