                        <div class="wizard-pane" id="stepPane2">
                            <div>
                                <div class="card bg-light border-0 shadow-sm mb-3">
                                    <div class="card-body p-3">
                                        <div class="fw-bold text-dark mb-3">
                                            <span>Employment Information</span>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">TAS ID</label>
                                                <input type="text" class="form-control" placeholder="Enter service or biometric ID">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Employee Number</label>
                                                <input type="text" class="form-control" placeholder="e.g. EMP-CEO-VIUQ">
                                            </div>
                                            <div class="col-12">
                                                <div class="border rounded p-3" style="background-color: #01244518">
                                                    <label class="form-label fw-semibold d-block mb-2">Resource Type <span
                                                            class="text-danger">*</span></label>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <input type="radio" class="btn-check" name="employmentCategory"
                                                            id="employmentDetailsCategoryEngagement" value="Engagement" required>
                                                        <label class="btn btn-outline-secondary rounded-pill px-3 py-1"
                                                            for="employmentDetailsCategoryEngagement">Employee</label>

                                                        <input type="radio" class="btn-check" name="employmentCategory"
                                                            id="employmentDetailsCategoryContractual" value="Contractual">
                                                        <label class="btn btn-outline-secondary rounded-pill px-3 py-1"
                                                            for="employmentDetailsCategoryContractual">Consultant / Retainer</label>

                                                        <input type="radio" class="btn-check" name="employmentCategory"
                                                            id="employmentDetailsCategoryIntern" value="Intern">
                                                        <label class="btn btn-outline-secondary rounded-pill px-3 py-1"
                                                            for="employmentDetailsCategoryIntern">Intern</label>
                                                    </div>

                                                    <div class="row g-3 d-none mt-1" id="employmentDetailsInternFields">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Intern Type <span class="text-danger">*</span></label>
                                                            <select class="form-select" id="employmentDetailsInternTypeInput">
                                                                <option value="" selected disabled>Select intern type</option>
                                                                <option value="Paid">Paid</option>
                                                                <option value="Unpaid">Unpaid</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Intern Duration <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" id="employmentDetailsEmployeeNumberInput"
                                                                placeholder="e.g. 3 months, 6 months">
                                                        </div>
                                                    </div>

                                                    <div class="row g-3 d-none mt-1" id="employmentDetailsContractualFields">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                                            <input type="date" class="form-control" id="employmentDetailsContractStartDateInput" placeholder="yyyy-mm-dd">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">End Date <span class="text-danger">*</span></label>
                                                            <input type="date" class="form-control" id="employmentDetailsContractEndDateInput" placeholder="yyyy-mm-dd">
                                                        </div>
                                                    </div>

                                                    <div class="row g-3 d-none mt-1" id="employmentDetailsEngagementFields">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Employment Type <span class="text-danger">*</span></label>
                                                            <select class="form-select" id="employmentDetailsEngagementModeInput">
                                                                <option value="" selected disabled>Select employment type</option>
                                                                <option value="Permanent">Permanent</option>
                                                                <option value="Contractual">Contractual</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6 d-none" id="employmentDetailsEmployeeContractTypeField">
                                                            <label class="form-label">Contract Type <span class="text-danger">*</span></label>
                                                            <select class="form-select" id="employmentDetailsEmployeeContractTypeInput">
                                                                <option value="" selected disabled>Select contract type</option>
                                                                <option value="Time bound">Time bound</option>
                                                                <option value="Open ended">Open ended</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-12 d-none" id="employmentDetailsEmployeeContractDatesField">
                                                            <div class="row g-3">
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Contract Start Date <span class="text-danger">*</span></label>
                                                                    <input type="date" class="form-control" id="employmentDetailsEmployeeContractStartDateInput" placeholder="yyyy-mm-dd">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Contract End Date <span class="text-danger">*</span></label>
                                                                    <input type="date" class="form-control" id="employmentDetailsEmployeeContractEndDateInput" placeholder="yyyy-mm-dd">
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
                                        <div class="card border-0 bg-light h-100">
                                            <div class="card-body p-3">
                                                <div class="fw-bold text-uppercase small mb-3">Organization and Role</div>
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Organization <span
                                                                class="text-danger">*</span></label>
                                                        <select class="form-select" id="employmentOrganizationSelect">
                                                            <option value="" selected disabled>Select organization</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">SBU <span class="text-danger">*</span></label>
                                                        <select class="form-select" id="employmentSbuSelect">
                                                            <option value="" selected disabled>Select SBU</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Roles <span class="text-danger">*</span></label>
                                                        <select class="form-select" id="employmentRoleSelect">
                                                            <option value="" selected disabled>Select role</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Departments <span class="text-muted fw-normal small">(optional)</span></label>
                                                        <select name="department_ids[]" id="employmentDepartmentSelect" class="form-select d-none" multiple size="4">
                                                            <option value="" selected disabled>No departments under this SBU</option>
                                                        </select>
                                                        <div class="emp-dept-input-box" id="employmentDeptBox" onclick="employmentDeptBoxClick(event)">
                                                            <div id="employmentDeptChips" style="display:contents"></div>
                                                            <span class="emp-dept-ph" id="employmentDeptPh">Select Departments...</span>
                                                            <svg class="emp-dept-chevron" id="employmentDeptChevron" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                                <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>
                                                        </div>
                                                        <div class="emp-dept-dropdown" id="employmentDeptDd" style="display:none">
                                                            <div class="emp-dept-search-row">
                                                                <input id="employmentDeptSearch" placeholder="Search Department..." oninput="employmentDeptRenderList()" onclick="event.stopPropagation()" autocomplete="off">
                                                            </div>
                                                            <div class="emp-dept-opt-list" id="employmentDeptList"></div>
                                                        </div>
                                                        <small class="text-muted d-block mt-1" id="employmentDeptHint">No departments are set up for this SBU yet.</small>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Date of Joining <span
                                                                class="text-danger">*</span></label>
                                                        <input type="date" class="form-control" placeholder="yyyy-mm-dd">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Designation</label>
                                                        <input type="text" class="form-control" placeholder="Designation">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-xl-6">
                                        <div class="card border-0 bg-light h-100">
                                            <div class="card-body p-3">
                                                <div class="fw-bold text-uppercase small mb-3">Placement and Grade</div>
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Grade</label>
                                                        <input type="text" class="form-control" placeholder="Grade">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Branch</label>
                                                        <input type="text" class="form-control" placeholder="Branch">
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Location</label>
                                                        <input type="text" class="form-control" placeholder="Location">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border-0 bg-light mt-3">
                                    <div class="card-body p-3">
                                        <div class="border rounded p-3" style="background-color: #01244518">
                                            <label class="form-label fw-semibold d-block mb-2">Work Arrangement <span
                                                    class="text-danger">*</span></label>
                                            <div class="d-flex flex-wrap gap-2">
                                                <input type="radio" class="btn-check" name="employmentWorkArrangement"
                                                    id="employmentWorkArrangementStandard" value="Standard" required>
                                                <label class="btn btn-outline-secondary rounded-pill px-3 py-1"
                                                    for="employmentWorkArrangementStandard">Standard</label>

                                                <input type="radio" class="btn-check" name="employmentWorkArrangement"
                                                    id="employmentWorkArrangementShift" value="Shift-Based">
                                                <label class="btn btn-outline-secondary rounded-pill px-3 py-1"
                                                    for="employmentWorkArrangementShift">Shift-Based</label>

                                                <input type="radio" class="btn-check" name="employmentWorkArrangement"
                                                    id="employmentWorkArrangementRemote" value="Remote">
                                                <label class="btn btn-outline-secondary rounded-pill px-3 py-1"
                                                    for="employmentWorkArrangementRemote">Remote</label>

                                                <input type="radio" class="btn-check" name="employmentWorkArrangement"
                                                    id="employmentWorkArrangementHybrid" value="Hybrid">
                                                <label class="btn btn-outline-secondary rounded-pill px-3 py-1"
                                                    for="employmentWorkArrangementHybrid">Hybrid</label>
                                            </div>

                                            <div class="row g-3 d-none mt-1" id="employmentWorkArrangementStandardFields">
                                                <div class="col-12">
                                                    <label class="form-label fw-semibold d-block mb-2">Standard Type <span class="text-danger">*</span></label>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <input type="radio" class="btn-check" name="employmentWorkArrangementStandardType"
                                                            id="employmentWorkArrangementStandardTypeDefault" value="Default">
                                                        <label class="btn btn-outline-secondary rounded-pill px-3 py-1"
                                                            for="employmentWorkArrangementStandardTypeDefault">Default</label>

                                                        <input type="radio" class="btn-check" name="employmentWorkArrangementStandardType"
                                                            id="employmentWorkArrangementStandardTypeCustom" value="Custom">
                                                        <label class="btn btn-outline-secondary rounded-pill px-3 py-1"
                                                            for="employmentWorkArrangementStandardTypeCustom">Custom</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row g-3 d-none mt-1" id="employmentWorkArrangementDefaultCardWrap">
                                                <div class="col-md-6">
                                                    <div class="card border shadow-sm">
                                                        <div class="card-body p-2">
                                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                                <div class="d-flex align-items-center justify-content-center text-white fw-bold rounded"
                                                                    id="employmentWorkArrangementOrgInitial"
                                                                    style="width:44px;height:44px;background:#012d5a;font-size:18px;">-</div>
                                                                <div>
                                                                    <div class="text-muted small">Source: Organization - <span class="fw-semibold text-dark"
                                                                            id="employmentWorkArrangementOrgName">-</span></div>
                                                                </div>
                                                            </div>
                                                            <div class="fw-semibold small">Working days: <span class="fw-normal">- - -</span></div>
                                                            <div class="fw-semibold small">Working time: <span class="fw-normal">- - -</span></div>
                                                            <div class="fw-semibold small">Check-in grace: <span class="fw-normal">-</span></div>
                                                            <div class="fw-semibold small">Check-out grace: <span class="fw-normal">-</span></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row g-3 d-none mt-1" id="employmentWorkArrangementCustomFields">
                                                <div class="col-12">
                                                    <label class="form-label fw-semibold">Working days <span class="text-danger">*</span></label>
                                                    <div class="d-flex flex-wrap gap-3">
                                                        <div class="form-check mb-0">
                                                            <input class="form-check-input" type="checkbox" id="employmentCustomDayMon" name="employment_custom_days[]" value="Mon">
                                                            <label class="form-check-label" for="employmentCustomDayMon">Mon</label>
                                                        </div>
                                                        <div class="form-check mb-0">
                                                            <input class="form-check-input" type="checkbox" id="employmentCustomDayTue" name="employment_custom_days[]" value="Tue">
                                                            <label class="form-check-label" for="employmentCustomDayTue">Tue</label>
                                                        </div>
                                                        <div class="form-check mb-0">
                                                            <input class="form-check-input" type="checkbox" id="employmentCustomDayWed" name="employment_custom_days[]" value="Wed">
                                                            <label class="form-check-label" for="employmentCustomDayWed">Wed</label>
                                                        </div>
                                                        <div class="form-check mb-0">
                                                            <input class="form-check-input" type="checkbox" id="employmentCustomDayThu" name="employment_custom_days[]" value="Thu">
                                                            <label class="form-check-label" for="employmentCustomDayThu">Thu</label>
                                                        </div>
                                                        <div class="form-check mb-0">
                                                            <input class="form-check-input" type="checkbox" id="employmentCustomDayFri" name="employment_custom_days[]" value="Fri">
                                                            <label class="form-check-label" for="employmentCustomDayFri">Fri</label>
                                                        </div>
                                                        <div class="form-check mb-0">
                                                            <input class="form-check-input" type="checkbox" id="employmentCustomDaySat" name="employment_custom_days[]" value="Sat">
                                                            <label class="form-check-label" for="employmentCustomDaySat">Sat</label>
                                                        </div>
                                                        <div class="form-check mb-0">
                                                            <input class="form-check-input" type="checkbox" id="employmentCustomDaySun" name="employment_custom_days[]" value="Sun">
                                                            <label class="form-check-label" for="employmentCustomDaySun">Sun</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Working start <span class="text-danger">*</span></label>
                                                    <input type="time" class="form-control" id="employmentCustomWorkingStartInput">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Working end <span class="text-danger">*</span></label>
                                                    <input type="time" class="form-control" id="employmentCustomWorkingEndInput">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Check-in grace (min)</label>
                                                    <input type="number" min="0" class="form-control" id="employmentCustomCheckInGraceInput" placeholder="Optional">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Check-out grace (min)</label>
                                                    <input type="number" min="0" class="form-control" id="employmentCustomCheckOutGraceInput" placeholder="Optional">
                                                </div>
                                            </div>

                                            <div class="row g-3 d-none mt-1" id="employmentWorkArrangementHybridFields">
                                                <div class="col-12">
                                                    <label class="form-label fw-semibold d-block mb-2">Hybrid - on-site days <span class="text-danger">*</span></label>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <input type="checkbox" class="btn-check" name="employment_hybrid_days[]" id="employmentHybridDayMon" value="Mon">
                                                        <label class="btn btn-outline-secondary rounded-pill px-3 py-1 fw-semibold" for="employmentHybridDayMon">Mon</label>

                                                        <input type="checkbox" class="btn-check" name="employment_hybrid_days[]" id="employmentHybridDayTue" value="Tue">
                                                        <label class="btn btn-outline-secondary rounded-pill px-3 py-1 fw-semibold" for="employmentHybridDayTue">Tue</label>

                                                        <input type="checkbox" class="btn-check" name="employment_hybrid_days[]" id="employmentHybridDayWed" value="Wed">
                                                        <label class="btn btn-outline-secondary rounded-pill px-3 py-1 fw-semibold" for="employmentHybridDayWed">Wed</label>

                                                        <input type="checkbox" class="btn-check" name="employment_hybrid_days[]" id="employmentHybridDayThu" value="Thu">
                                                        <label class="btn btn-outline-secondary rounded-pill px-3 py-1 fw-semibold" for="employmentHybridDayThu">Thu</label>

                                                        <input type="checkbox" class="btn-check" name="employment_hybrid_days[]" id="employmentHybridDayFri" value="Fri">
                                                        <label class="btn btn-outline-secondary rounded-pill px-3 py-1 fw-semibold" for="employmentHybridDayFri">Fri</label>

                                                        <input type="checkbox" class="btn-check" name="employment_hybrid_days[]" id="employmentHybridDaySat" value="Sat">
                                                        <label class="btn btn-outline-secondary rounded-pill px-3 py-1 fw-semibold" for="employmentHybridDaySat">Sat</label>

                                                        <input type="checkbox" class="btn-check" name="employment_hybrid_days[]" id="employmentHybridDaySun" value="Sun">
                                                        <label class="btn btn-outline-secondary rounded-pill px-3 py-1 fw-semibold" for="employmentHybridDaySun">Sun</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


