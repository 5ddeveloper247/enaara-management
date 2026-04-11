{{-- STEP 2: Employment Information --}}
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
                placeholder="— Select Organization &amp; Role —"
                style="opacity:1;background:rgba(255,255,255,.06)!important;cursor:not-allowed;">
        </div>

        <div class="col-12">
            <div class="p-3 rounded-3 border bg-light bg-opacity-25">
                <label class="form-label fw-semibold mb-2 d-block">Category <span class="text-danger">*</span></label>
                <div id="categoryRadioGroup" class="d-flex flex-wrap gap-2 mb-2">
                    <div class="form-check d-flex align-items-center gap-2 px-3 py-2 rounded-pill border bg-white">
                        <input class="check-input" type="radio" name="employment_category" id="catIntern" value="intern">
                        <label class="form-check-label" for="catIntern">Intern</label>
                    </div>
                    <div class="form-check d-flex align-items-center gap-2 px-3 py-2 rounded-pill border bg-white">
                        <input class="check-input" type="radio" name="employment_category" id="catContractual" value="contractual">
                        <label class="form-check-label" for="catContractual">Contractual</label>
                    </div>
                    <div class="form-check d-flex align-items-center gap-2 px-3 py-2 rounded-pill border bg-white">
                        <input class="check-input" type="radio" name="employment_category" id="catEngagement" value="engagement">
                        <label class="form-check-label" for="catEngagement">Engagement</label>
                    </div>
                </div>

                <div class="row g-2" id="internFields" style="display:none;">
                    <div class="col-md-3">
                        <label class="form-label">Intern Type</label>
                        <select name="intern_type" class="form-select">
                            <option value="">Select</option>
                            <option value="paid">Paid</option>
                            <option value="unpaid">Unpaid</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Intern Duration</label>
                        <input type="text" name="intern_duration" class="form-control" placeholder="e.g. 3 Months">
                    </div>
                </div>

                <div class="row g-2" id="contractualFields" style="display:none;">
                    <div class="col-md-3">
                        <label class="form-label">Contract Type</label>
                        <select name="contractual_type" class="form-select">
                            <option value="">Select</option>
                            <option value="time_bound">Time Bound</option>
                            <option value="open">Open</option>
                            <option value="project_based">Project-Based Consultants</option>
                        </select>
                    </div>
                </div>

                <div class="row g-2" id="engagementFields" style="display:none;">
                    <div class="col-md-3">
                        <label class="form-label">Engagement Mode</label>
                        <select name="engagement_mode" id="engagementMode" class="form-select">
                            <option value="">Select</option>
                            <option value="on_site">On-site</option>
                            <option value="remote">Remote</option>
                            <option value="shifts">Shifts</option>
                            <option value="hybrid">Hybrid</option>
                        </select>
                    </div>
                    <div class="col-md-9" id="hybridDaysWrapper" style="display:none;">
                        <label class="form-label">Hybrid Days</label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach (['mon' => 'M', 'tue' => 'T', 'wed' => 'W', 'thu' => 'T', 'fri' => 'F', 'sat' => 'S', 'sun' => 'S'] as $dayKey => $dayLabel)
                                <div class="form-check d-flex align-items-center gap-1 px-2 py-1 rounded-pill border bg-white">
                                    <input class="form-check-input" type="checkbox" name="hybrid_days[]" id="hybrid_{{ $dayKey }}" value="{{ $dayKey }}">
                                    <label class="form-check-label" for="hybrid_{{ $dayKey }}">{{ $dayLabel }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <label class="form-label">Organization <span class="text-danger">*</span></label>
            <select name="organization_id" id="org_select" class="form-select"
                style="background-color:transparent !important; border:1px solid #012445; box-shadow:0 0 4px 2px #5a59593d; appearance:none; -webkit-appearance:none;"
                onchange="onOrgChange(this.value)">
                <option value="">— Select Organization —</option>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Role <span class="text-danger">*</span></label>
            <select name="role_id" id="role_select" class="form-select" disabled
                style="background-color:transparent !important; border:1px solid #012445; box-shadow:0 0 4px 2px #5a59593d; appearance:none; -webkit-appearance:none;">
                <option value="">— Select Organization first —</option>
            </select>
        </div>

        <div class="col-12 mt-3" id="sbuDeptSection" style="display:none;">
            <div class="row g-2 p-2 rounded-3 border bg-light bg-opacity-25">
                <div class="col-12">
                    <div class="small fw-semibold text-uppercase text-muted">Role Placement</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">SBU <span class="text-danger sbu-dept-req">*</span></label>
                    <select name="sbu_id" id="sbu_select" class="form-select"
                        style="background-color:transparent !important; border:1px solid #012445; box-shadow:0 0 4px 2px #5a59593d; appearance:none; -webkit-appearance:none;"
                        onchange="onSbuChange(this.value)">
                        <option value="">— Select SBU —</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Department <span class="text-danger sbu-dept-req">*</span></label>
                    <select name="department_id" id="dept_select" class="form-select"
                        style="background-color:transparent !important; border:1px solid #012445; box-shadow:0 0 4px 2px #5a59593d; appearance:none; -webkit-appearance:none;">
                        <option value="">— Select Department —</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <label class="form-label">Date of Joining <span class="text-danger">*</span></label>
            <input type="date" name="join_date" class="form-control">
        </div>

        <div class="col-md-3">
            <label class="form-label">Designation</label>
            <input type="text" name="designation" class="form-control" placeholder="e.g. Software Engineer">
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

    </div>
</div>

@include('admin.register.employment_info_scripts')
