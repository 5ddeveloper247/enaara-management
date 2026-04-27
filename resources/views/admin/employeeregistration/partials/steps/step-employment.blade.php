                        <div class="wizard-pane px-3" id="stepPane2">
                            <div id="step-2">
                                <div class="card bg-light border-0 shadow-sm mb-3">
                                    <div class="card-body p-3">
                                        <div class="fw-bold text-dark mb-3">
                                            <span>Employment Information</span>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Employee Number</label>
                                                <input type="text" name="employee_number" id="employmentEmployeeNumberInput" class="form-control"
                                                    value="{{ $employee->employee_code ?? '' }}"
                                                    placeholder="e.g. EMP-CEO-VIUQ" disabled>
                                            </div>
                                            <div class="col-12">
                                                <div class="border rounded p-3" style="background-color: #01244518">
                                                    <label class="form-label fw-semibold d-block mb-2">Resource Type
                                                        <span class="text-danger">*</span></label>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <input type="radio" class="btn-check"
                                                            name="employment_category"
                                                            id="employmentDetailsCategoryEngagement" value="employee"
                                                            required
                                                            {{ ($employee->employment_category ?? '') == 'employee' ? 'checked' : '' }}>
                                                        <label class="btn btn-outline-secondary rounded-pill px-3 py-1"
                                                            for="employmentDetailsCategoryEngagement">Employee</label>

                                                        <input type="radio" class="btn-check"
                                                            name="employment_category"
                                                            id="employmentDetailsCategoryContractual"
                                                            value="consultant"
                                                            {{ in_array(($employee->employment_category ?? ''), ['consultant', 'contractual'], true) ? 'checked' : '' }}>
                                                        <label class="btn btn-outline-secondary rounded-pill px-3 py-1"
                                                            for="employmentDetailsCategoryContractual">Consultant /
                                                            Retainer</label>

                                                        <input type="radio" class="btn-check"
                                                            name="employment_category"
                                                            id="employmentDetailsCategoryIntern" value="intern"
                                                            {{ ($employee->employment_category ?? '') == 'intern' ? 'checked' : '' }}>
                                                        <label class="btn btn-outline-secondary rounded-pill px-3 py-1"
                                                            for="employmentDetailsCategoryIntern">Intern</label>
                                                    </div>

                                                    <div class="row g-3 {{ ($employee->employment_category ?? '') == 'intern' ? '' : 'd-none' }} mt-1"
                                                        id="employmentDetailsInternFields">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Intern Type <span
                                                                    class="text-danger">*</span></label>
                                                            <select name="intern_type" class="form-select"
                                                                id="employmentDetailsInternTypeInput">
                                                                <option value=""
                                                                    {{ !isset($employee->intern_type) ? 'selected' : '' }}
                                                                    disabled>Select intern type</option>
                                                                <option value="paid"
                                                                    {{ strtolower((string) ($employee->intern_type ?? '')) == 'paid' ? 'selected' : '' }}>
                                                                    Paid</option>
                                                                <option value="unpaid"
                                                                    {{ strtolower((string) ($employee->intern_type ?? '')) == 'unpaid' ? 'selected' : '' }}>
                                                                    Unpaid</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Intern Duration <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" name="intern_duration"
                                                                class="form-control"
                                                                id="employmentDetailsInternDurationInput"
                                                                value="{{ $employee->intern_duration ?? '' }}"
                                                                maxlength="10"
                                                                placeholder="e.g. 3 months, 6 months">
                                                        </div>
                                                    </div>

                                                    <div class="row g-3 {{ in_array(($employee->employment_category ?? ''), ['consultant', 'contractual'], true) ? '' : 'd-none' }} mt-1"
                                                        id="employmentDetailsContractualFields">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Start Date <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="date" name="contract_start_date"
                                                                class="form-control"
                                                                id="employmentDetailsContractStartDateInput"
                                                                value="{{ isset($employee->contract_start_date) && $employee->contract_start_date ? (is_string($employee->contract_start_date) ? date('Y-m-d', strtotime($employee->contract_start_date)) : $employee->contract_start_date->format('Y-m-d')) : '' }}"
                                                                placeholder="yyyy-mm-dd">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">End Date <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="date" name="contract_end_date"
                                                                class="form-control"
                                                                id="employmentDetailsContractEndDateInput"
                                                                value="{{ isset($employee->contract_end_date) && $employee->contract_end_date ? (is_string($employee->contract_end_date) ? date('Y-m-d', strtotime($employee->contract_end_date)) : $employee->contract_end_date->format('Y-m-d')) : '' }}"
                                                                placeholder="yyyy-mm-dd">
                                                        </div>
                                                    </div>

                                                    <div class="row g-3 {{ ($employee->employment_category ?? '') == 'employee' ? '' : 'd-none' }} mt-1"
                                                        id="employmentDetailsEngagementFields">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Probation Start Date <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="date" name="probation_start_date"
                                                                class="form-control"
                                                                id="employmentProbationStartDateInput"
                                                                value="{{ isset($employee->probation_start_date) && $employee->probation_start_date ? (is_string($employee->probation_start_date) ? date('Y-m-d', strtotime($employee->probation_start_date)) : $employee->probation_start_date->format('Y-m-d')) : '' }}"
                                                                placeholder="yyyy-mm-dd">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Probation End Date <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="date" name="probation_end_date"
                                                                class="form-control"
                                                                id="employmentProbationEndDateInput"
                                                                value="{{ isset($employee->probation_end_date) && $employee->probation_end_date ? (is_string($employee->probation_end_date) ? date('Y-m-d', strtotime($employee->probation_end_date)) : $employee->probation_end_date->format('Y-m-d')) : '' }}"
                                                                placeholder="yyyy-mm-dd">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Employment Type <span
                                                                    class="text-danger">*</span></label>
                                                            <select name="employment_type" class="form-select"
                                                                id="employmentDetailsEngagementModeInput">
                                                                <option value=""
                                                                    {{ !isset($employee->employment_type) ? 'selected' : '' }}
                                                                    disabled>Select employment type</option>
                                                                <option value="permanent"
                                                                    {{ ($employee->employment_type ?? '') == 'permanent' ? 'selected' : '' }}>
                                                                    Permanent</option>
                                                                <option value="contractual"
                                                                    {{ ($employee->employment_type ?? '') == 'contractual' ? 'selected' : '' }}>
                                                                    Contractual</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6 {{ ($employee->employment_type ?? '') == 'contractual' ? '' : 'd-none' }}"
                                                            id="employmentDetailsEmployeeContractTypeField">
                                                            <label class="form-label">Contract Type <span
                                                                    class="text-danger">*</span></label>
                                                            <select name="contractual_type" class="form-select"
                                                                id="employmentDetailsEmployeeContractTypeInput">
                                                                <option value=""
                                                                    {{ !isset($employee->contractual_type) ? 'selected' : '' }}
                                                                    disabled>Select contract type</option>
                                                                <option value="time_bound"
                                                                    {{ ($employee->contractual_type ?? '') == 'time_bound' ? 'selected' : '' }}>
                                                                    Time bound</option>
                                                                <option value="open_ended"
                                                                    {{ ($employee->contractual_type ?? '') == 'open_ended' ? 'selected' : '' }}>
                                                                    Open ended</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-12 {{ ($employee->contractual_type ?? '') == 'time_bound' ? '' : 'd-none' }}"
                                                            id="employmentDetailsEmployeeContractDatesField">
                                                            <div class="row g-3">
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Contract Start Date <span
                                                                            class="text-danger">*</span></label>
                                                                    <input type="date"
                                                                        name="employee_contract_start_date"
                                                                        class="form-control"
                                                                        id="employmentDetailsEmployeeContractStartDateInput"
                                                                        value="{{ isset($employee->contract_start_date) && $employee->contract_start_date ? (is_string($employee->contract_start_date) ? date('Y-m-d', strtotime($employee->contract_start_date)) : $employee->contract_start_date->format('Y-m-d')) : '' }}"
                                                                        placeholder="yyyy-mm-dd">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Contract End Date <span
                                                                            class="text-danger">*</span></label>
                                                                    <input type="date"
                                                                        name="employee_contract_end_date"
                                                                        class="form-control"
                                                                        id="employmentDetailsEmployeeContractEndDateInput"
                                                                        value="{{ isset($employee->contract_end_date) && $employee->contract_end_date ? (is_string($employee->contract_end_date) ? date('Y-m-d', strtotime($employee->contract_end_date)) : $employee->contract_end_date->format('Y-m-d')) : '' }}"
                                                                        placeholder="yyyy-mm-dd">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-12 col-xl-6">
                                        <div class="card border-0 bg-light">
                                            <div class="card-body p-3">
                                                <div class="fw-bold text-uppercase small mb-3">Organization and Role
                                                </div>
                                                <div class="row g-3">
                                                    <div class="col-12">
                                                        <label class="form-label">Organization <span
                                                                class="text-danger">*</span></label>
                                                        <select name="organization_id" class="form-select"
                                                            id="employmentOrganizationSelect">
                                                            <option value=""
                                                                {{ !isset($employee->organization_id) ? 'selected' : '' }}
                                                                disabled>Select organization</option>
                                                            @foreach ($organizations as $org)
                                                                <option value="{{ $org->id }}"
                                                                    {{ ($employee->organization_id ?? '') == $org->id ? 'selected' : '' }}>
                                                                    {{ $org->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">SBU <span
                                                                class="text-danger">*</span></label>
                                                        <select name="sbu_id" class="form-select"
                                                            id="employmentSbuSelect">
                                                            <option value=""
                                                                {{ !isset($employee->sbu_id) ? 'selected' : '' }}
                                                                disabled>Select SBU</option>
                                                            @if (isset($employee->organization_id))
                                                                @php
                                                                    $selectedOrg = $organizations
                                                                        ->where('id', $employee->organization_id)
                                                                        ->first();
                                                                    $currentSbus = $selectedOrg
                                                                        ? $selectedOrg->sbus
                                                                        : collect();
                                                                @endphp
                                                                @foreach ($currentSbus as $sbu)
                                                                    <option value="{{ $sbu->id }}"
                                                                        {{ ($employee->sbu_id ?? '') == $sbu->id ? 'selected' : '' }}>
                                                                        {{ $sbu->name }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Roles <span
                                                                class="text-danger">*</span></label>
                                                        <select name="role_id" class="form-select"
                                                            id="employmentRoleSelect">
                                                            <option value=""
                                                                {{ !isset($employee->role_id) ? 'selected' : '' }}
                                                                disabled>Select role</option>
                                                            @if (isset($rolesData))
                                                                @foreach ($rolesData as $role)
                                                                    <option value="{{ $role['id'] }}"
                                                                        {{ ($employee->role_id ?? '') == $role['id'] ? 'selected' : '' }}>
                                                                        {{ $role['name'] }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label" id="employmentDeptLabel">Departments
                                                            <span id="employmentDeptRequired"
                                                                class="text-muted fw-normal small">(optional)</span></label>
                                                        <select name="department_ids[]"
                                                            id="employmentDepartmentSelect" class="form-select d-none"
                                                            multiple>
                                                            @php
                                                                $savedDepts = collect(
                                                                    $editData['saved_departments'] ?? [],
                                                                );
                                                            @endphp
                                                            @if ($savedDepts->isNotEmpty())
                                                                @foreach ($savedDepts as $dept)
                                                                    <option value="{{ $dept['id'] }}" selected>
                                                                        {{ $dept['name'] }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                        <div class="emp-dept-input-box" id="employmentDeptBox">
                                                            <div id="employmentDeptChips" style="display:contents">
                                                            </div>
                                                            <span class="emp-dept-ph" id="employmentDeptPh">Select
                                                                Departments...</span>
                                                            <svg class="emp-dept-chevron" id="employmentDeptChevron"
                                                                width="16" height="16" viewBox="0 0 16 16"
                                                                fill="none">
                                                                <path d="M4 6l4 4 4-4" stroke="currentColor"
                                                                    stroke-width="1.5" stroke-linecap="round"
                                                                    stroke-linejoin="round" />
                                                            </svg>
                                                        </div>
                                                        <div class="emp-dept-dropdown" id="employmentDeptDd"
                                                            style="display:none">
                                                            <div class="emp-dept-search-row">
                                                                <input id="employmentDeptSearch"
                                                                    placeholder="Search Department..."
                                                                    autocomplete="off">
                                                            </div>
                                                            <div class="emp-dept-opt-list" id="employmentDeptList">
                                                            </div>
                                                        </div>
                                                        <small class="text-muted d-block mt-1"
                                                            id="employmentDeptHint">No departments are set up for this
                                                            SBU yet.</small>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Date of Joining <span
                                                                class="text-danger">*</span></label>
                                                        <input type="date" name="join_date" class="form-control"
                                                            id="employmentJoinDateInput"
                                                            value="{{ isset($employee->join_date) ? $employee->join_date->format('Y-m-d') : '' }}"
                                                            placeholder="yyyy-mm-dd">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Designation</label>
                                                        <input type="text" name="designation" class="form-control"
                                                            id="designation"
                                                            value="{{ $employee->designation ?? '' }}"
                                                            maxlength="50"
                                                            placeholder="Designation">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-xl-6">
                                        <div class="card border-0 bg-light">
                                            <div class="card-body p-3">
                                                <div class="fw-bold text-uppercase small mb-3">Placement and Grade
                                                </div>
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Grade</label>
                                                        <input type="text" name="grade" class="form-control"
                                                            id="grade"
                                                            value="{{ $employee->grade ?? '' }}" maxlength="10" placeholder="Grade">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Branch</label>
                                                        <input type="text" name="branch" class="form-control"
                                                            id="branch"
                                                            value="{{ $employee->branch ?? '' }}"
                                                            maxlength="30"
                                                            placeholder="Branch">
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Location</label>
                                                        <input type="text" name="location" class="form-control"
                                                            id="location"
                                                            value="{{ $employee->location ?? '' }}"
                                                            maxlength="100"
                                                            placeholder="Location">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @php
                                            $assignedFloorIdsRaw = $editData['assigned_floor_ids'] ?? [];
                                            if (!is_array($assignedFloorIdsRaw)) {
                                                $assignedFloorIdsRaw = $assignedFloorIdsRaw
                                                    ? explode(',', (string) $assignedFloorIdsRaw)
                                                    : [];
                                            }
                                            $assignedFloorIds = collect($assignedFloorIdsRaw)
                                                ->map(fn($id) => (int) $id)
                                                ->filter(fn($id) => $id > 0)
                                                ->values()
                                                ->all();
                                            $selectedEmployeeStatus = $employee->employee_status ?? 'Active';
                                        @endphp
                                        <div class="card border-0 bg-light mt-3">
                                            <div class="card-body p-3">
                                                <div class="fw-bold text-uppercase small mb-3">Status and Probation
                                                </div>
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Employee Status <span
                                                                class="text-danger">*</span></label>
                                                        <select name="employee_status" class="form-select"
                                                            id="employmentStatusInput" required>
                                                            <option value="Active"
                                                                {{ $selectedEmployeeStatus === 'Active' ? 'selected' : '' }}>
                                                                Active</option>
                                                            <option value="Suspend"
                                                                {{ $selectedEmployeeStatus === 'Suspend' ? 'selected' : '' }}>
                                                                Suspend</option>
                                                            <option value="Terminated"
                                                                {{ $selectedEmployeeStatus === 'Terminated' ? 'selected' : '' }}>
                                                                Terminated</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">TAS ID / Biometric ID</label>
                                                        <input type="text" name="biometric_id" id="biometric_id"
                                                            class="form-control"
                                                            value="{{ $employee->biometric_id ?? '' }}"
                                                            maxlength="20"
                                                            placeholder="Enter service or biometric ID">
                                                    </div>

                                                    <div class="col-12">
                                                        <label class="form-label">Assigned Floors</label>
                                                        <select name="assigned_floor_ids[]"
                                                            id="employmentAssignedFloorsSelect"
                                                            class="form-select d-none" multiple
                                                            data-selected-values='@json($assignedFloorIds)'>
                                                        </select>
                                                        <div class="emp-dept-input-box" id="employmentFloorBox">
                                                            <div id="employmentFloorChips" style="display:contents">
                                                            </div>
                                                            <span class="emp-dept-ph" id="employmentFloorPh">Select
                                                                Floors...</span>
                                                            <svg class="emp-dept-chevron" id="employmentFloorChevron"
                                                                width="16" height="16" viewBox="0 0 16 16"
                                                                fill="none">
                                                                <path d="M4 6l4 4 4-4" stroke="currentColor"
                                                                    stroke-width="1.5" stroke-linecap="round"
                                                                    stroke-linejoin="round" />
                                                            </svg>
                                                        </div>
                                                        <div class="emp-dept-dropdown" id="employmentFloorDd"
                                                            style="display:none">
                                                            <div class="emp-dept-search-row">
                                                                <input id="employmentFloorSearch"
                                                                    placeholder="Search Floor..." autocomplete="off">
                                                            </div>
                                                            <div class="emp-dept-opt-list" id="employmentFloorList">
                                                            </div>
                                                        </div>
                                                        <small class="text-muted d-block mt-1">Assign Floor
                                                            Access</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div class="card border-0 mt-3"
                                    style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);">
                                    <div class="card-body p-4">
                                        

                                            {{-- Section Header --}}
                                            <div class="d-flex align-items-center gap-2 mb-3">
                                                <div class="rounded-2 d-flex align-items-center justify-content-center"
                                                    style="width:30px;height:30px;background:rgba(1,45,90,0.1);">
                                                    <i class="bi bi-building-gear"
                                                        style="color:#012d5a;font-size:0.85rem;"></i>
                                                </div>
                                                <label class="form-label fw-semibold mb-0" style="color:#012d5a;">
                                                    Work Arrangement <span class="text-danger">*</span>
                                                </label>
                                            </div>

                                            {{-- Mode Pills --}}
                                            <div class="d-flex flex-wrap gap-2 mb-1"
                                                id="employmentWorkArrangementModeGroup">
                                                <input type="radio" class="btn-check" name="engagement_mode"
                                                    id="employmentWorkArrangementStandard" value="standard" required
                                                    {{ ($employee->engagement_mode ?? '') == 'standard' ? 'checked' : '' }}>
                                                <label
                                                    class="btn btn-outline-secondary rounded-pill px-4 py-1 d-flex align-items-center gap-1"
                                                    for="employmentWorkArrangementStandard">
                                                    <i class="bi bi-clock" style="font-size:0.75rem;"></i> Standard
                                                </label>

                                                <input type="radio" class="btn-check" name="engagement_mode"
                                                    id="employmentWorkArrangementShift" value="shifts"
                                                    {{ ($employee->engagement_mode ?? '') == 'shifts' ? 'checked' : '' }}>
                                                <label
                                                    class="btn btn-outline-secondary rounded-pill px-4 py-1 d-flex align-items-center gap-1"
                                                    for="employmentWorkArrangementShift">
                                                    <i class="bi bi-arrow-repeat" style="font-size:0.75rem;"></i>
                                                    Shift-Based
                                                </label>

                                                <input type="radio" class="btn-check" name="engagement_mode"
                                                    id="employmentWorkArrangementRemote" value="remote"
                                                    {{ ($employee->engagement_mode ?? '') == 'remote' ? 'checked' : '' }}>
                                                <label
                                                    class="btn btn-outline-secondary rounded-pill px-4 py-1 d-flex align-items-center gap-1"
                                                    for="employmentWorkArrangementRemote">
                                                    <i class="bi bi-house" style="font-size:0.75rem;"></i> Remote
                                                </label>

                                                <input type="radio" class="btn-check" name="engagement_mode"
                                                    id="employmentWorkArrangementHybrid" value="hybrid"
                                                    {{ ($employee->engagement_mode ?? '') == 'hybrid' ? 'checked' : '' }}>
                                                <label
                                                    class="btn btn-outline-secondary rounded-pill px-4 py-1 d-flex align-items-center gap-1"
                                                    for="employmentWorkArrangementHybrid">
                                                    <i class="bi bi-diagram-2" style="font-size:0.75rem;"></i> Hybrid
                                                </label>
                                            </div>

                                            {{-- Standard Fields --}}
                                            <div class="row g-3 {{ ($employee->engagement_mode ?? '') == 'standard' ? '' : 'd-none' }} mt-2"
                                                id="employmentWorkArrangementStandardFields">
                                                <div class="col-12">
                                                    <div class="rounded-3 p-3"
                                                        style="background:rgba(1,36,69,0.03); border:1px dashed rgba(1,36,69,0.15);">
                                                        <label
                                                            class="form-label fw-semibold small text-uppercase tracking-wide mb-2"
                                                            style="color:#012d5a; letter-spacing:0.04em;">
                                                            Schedule Type <span class="text-danger">*</span>
                                                        </label>
                                                        <div class="d-flex flex-wrap gap-2"
                                                            id="employmentWorkArrangementStandardTypeGroup">
                                                            <input type="radio" class="btn-check"
                                                                name="standard_schedule_mode"
                                                                id="employmentWorkArrangementStandardTypeDefault"
                                                                value="default"
                                                                {{ ($employee->standard_schedule_mode ?? '') == 'default' ? 'checked' : '' }}>
                                                            <label
                                                                class="btn btn-outline-secondary rounded-pill px-4 py-1 d-flex align-items-center gap-1"
                                                                for="employmentWorkArrangementStandardTypeDefault">
                                                                <i class="bi bi-sliders"
                                                                    style="font-size:0.75rem;"></i> Default
                                                            </label>

                                                            <input type="radio" class="btn-check"
                                                                name="standard_schedule_mode"
                                                                id="employmentWorkArrangementStandardTypeCustom"
                                                                value="custom"
                                                                {{ ($employee->standard_schedule_mode ?? '') == 'custom' ? 'checked' : '' }}>
                                                            <label
                                                                class="btn btn-outline-secondary rounded-pill px-4 py-1 d-flex align-items-center gap-1"
                                                                for="employmentWorkArrangementStandardTypeCustom">
                                                                <i class="bi bi-pencil-square"
                                                                    style="font-size:0.75rem;"></i> Custom
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Default Schedule Card --}}
                                            <div class="row g-3 {{ ($employee->standard_schedule_mode ?? '') == 'default' ? '' : 'd-none' }} mt-1"
                                                id="employmentWorkArrangementDefaultCardWrap">
                                                <div class="col-md-6">
                                                    <div class="card border-0 shadow-sm rounded-3"
                                                        style="border-left: 3px solid #012d5a !important;">
                                                        <div class="card-body p-3">
                                                            <div class="d-flex align-items-center gap-3 mb-3">
                                                                <div class="d-flex align-items-center justify-content-center text-white fw-bold rounded-2 flex-shrink-0"
                                                                    id="employmentWorkArrangementOrgInitial"
                                                                    style="width:40px;height:40px;background:#012d5a;font-size:16px;">
                                                                    -</div>
                                                                <div>
                                                                    <div class="text-muted"
                                                                        style="font-size:0.7rem; text-transform:uppercase; letter-spacing:0.05em;"
                                                                        title="Working days, hours, and grace periods below follow this organization's standard schedule (master defaults).">
                                                                        Default schedule source
                                                                    </div>
                                                                    <div class="fw-semibold text-dark small"
                                                                        id="employmentWorkArrangementOrgName">-</div>
                                                                </div>
                                                            </div>
                                                            <hr class="my-2 opacity-10">
                                                            <div class="row g-2">
                                                                <div class="col-6">
                                                                    <div class="small text-muted">Working days</div>
                                                                    <div class="fw-semibold small"
                                                                        id="employmentDefaultWorkingDays">- - -</div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <div class="small text-muted">Working time</div>
                                                                    <div class="fw-semibold small"
                                                                        id="employmentDefaultWorkingTime">- - -</div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <div class="small text-muted">Check-in grace</div>
                                                                    <div class="fw-semibold small"
                                                                        id="employmentDefaultCheckInGrace">-</div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <div class="small text-muted">Check-out grace</div>
                                                                    <div class="fw-semibold small"
                                                                        id="employmentDefaultCheckOutGrace">-</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Custom Schedule Fields --}}
                                            <div class="row g-3 {{ ($employee->standard_schedule_mode ?? '') == 'custom' ? '' : 'd-none' }} mt-1"
                                                id="employmentWorkArrangementCustomFields">
                                                <div class="col-12">
                                                    <div class="rounded-3 p-3"
                                                        style="background:rgba(1,36,69,0.03); border:1px dashed rgba(1,36,69,0.15);">
                                                        <label class="form-label fw-semibold small text-uppercase mb-2"
                                                            style="color:#012d5a; letter-spacing:0.04em;">
                                                            Working Days <span class="text-danger">*</span>
                                                        </label>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            @php
                                                                $workingDaysRaw = isset($employee->working_days)
                                                                    ? (is_array($employee->working_days)
                                                                        ? $employee->working_days
                                                                        : explode(',', $employee->working_days))
                                                                    : [];
                                                                $workingDays = collect($workingDaysRaw)
                                                                    ->map(fn($d) => strtolower(trim((string) $d)))
                                                                    ->values()
                                                                    ->all();
                                                                $workingDayOptions = [
                                                                    'monday' => 'Mon',
                                                                    'tuesday' => 'Tue',
                                                                    'wednesday' => 'Wed',
                                                                    'thursday' => 'Thu',
                                                                    'friday' => 'Fri',
                                                                    'saturday' => 'Sat',
                                                                    'sunday' => 'Sun',
                                                                ];
                                                            @endphp
                                                            @foreach ($workingDayOptions as $dayValue => $dayLabel)
                                                                <input type="checkbox" class="btn-check"
                                                                    id="employmentCustomDay{{ $dayLabel }}"
                                                                    name="working_days[]" value="{{ $dayValue }}"
                                                                    {{ in_array($dayValue, $workingDays, true) ? 'checked' : '' }}>
                                                                <label
                                                                    class="btn btn-outline-secondary rounded-pill px-3 py-1 fw-semibold"
                                                                    for="employmentCustomDay{{ $dayLabel }}">{{ $dayLabel }}</label>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="row g-3">
                                                        <div class="col-md-3">
                                                            <label class="form-label small fw-semibold">
                                                                <i
                                                                    class="bi bi-sunrise text-secondary me-1"></i>
                                                                Start Time<span class="text-danger">*</span>
                                                            </label>
                                                            <input type="time" name="working_start_time"
                                                                class="form-control"
                                                                id="employmentCustomWorkingStartInput"
                                                                value="{{ isset($employee->working_start_time) ? substr($employee->working_start_time, 0, 5) : '' }}">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label small fw-semibold">
                                                                <i class="bi bi-sunset text-secondary me-1"></i>
                                                                End Time<span class="text-danger">*</span>
                                                            </label>
                                                            <input type="time" name="working_end_time"
                                                                class="form-control"
                                                                id="employmentCustomWorkingEndInput"
                                                                value="{{ isset($employee->working_end_time) ? substr($employee->working_end_time, 0, 5) : '' }}">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label small fw-semibold">
                                                                <i
                                                                    class="bi bi-box-arrow-in-right text-secondary me-1"></i>Check-in
                                                                Grace <span class="text-muted fw-normal">(min)</span>
                                                            </label>
                                                            <input type="number" name="opening_grace_period"
                                                                min="0" max="600" class="form-control"
                                                                id="employmentCustomCheckInGraceInput"
                                                                placeholder="Optional"
                                                                value="{{ $employee->opening_grace_period ?? '' }}">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label small fw-semibold">
                                                                <i
                                                                    class="bi bi-box-arrow-right text-secondary me-1"></i>Check-out
                                                                Grace <span class="text-muted fw-normal">(min)</span>
                                                            </label>
                                                            <input type="number" name="closing_grace_period"
                                                                min="0" max="600" class="form-control"
                                                                id="employmentCustomCheckOutGraceInput"
                                                                placeholder="Optional"
                                                                value="{{ $employee->closing_grace_period ?? '' }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Hybrid Fields --}}
                                            <div class="row g-3 {{ ($employee->engagement_mode ?? '') == 'hybrid' ? '' : 'd-none' }} mt-2"
                                                id="employmentWorkArrangementHybridFields">
                                                <div class="col-12">
                                                    <div class="rounded-3 p-3"
                                                        style="background:rgba(1,36,69,0.03); border:1px dashed rgba(1,36,69,0.15);">
                                                        <label class="form-label fw-semibold small text-uppercase mb-2"
                                                            style="color:#012d5a; letter-spacing:0.04em;">
                                                            On-site Days <span class="text-danger">*</span>
                                                        </label>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            @php
                                                                $hybridDaysRaw = isset($employee->hybrid_days)
                                                                    ? (is_array($employee->hybrid_days)
                                                                        ? $employee->hybrid_days
                                                                        : explode(',', $employee->hybrid_days))
                                                                    : [];
                                                                $hybridDays = collect($hybridDaysRaw)
                                                                    ->map(fn($d) => strtolower(trim((string) $d)))
                                                                    ->values()
                                                                    ->all();
                                                                $hybridDayOptions = [
                                                                    'mon' => 'Mon',
                                                                    'tue' => 'Tue',
                                                                    'wed' => 'Wed',
                                                                    'thu' => 'Thu',
                                                                    'fri' => 'Fri',
                                                                    'sat' => 'Sat',
                                                                    'sun' => 'Sun',
                                                                ];
                                                            @endphp
                                                            @foreach ($hybridDayOptions as $dayValue => $dayLabel)
                                                                <input type="checkbox" class="btn-check"
                                                                    name="hybrid_days[]"
                                                                    id="employmentHybridDay{{ $dayLabel }}"
                                                                    value="{{ $dayValue }}"
                                                                    {{ in_array($dayValue, $hybridDays, true) ? 'checked' : '' }}>
                                                                <label
                                                                    class="btn btn-outline-secondary rounded-pill px-3 py-1 fw-semibold"
                                                                    for="employmentHybridDay{{ $dayLabel }}">{{ $dayLabel }}</label>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                    </div>
                                </div>
                            </div>
                        </div>
