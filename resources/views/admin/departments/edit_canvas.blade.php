<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="departmentEditCanvas" aria-labelledby="departmentEditCanvasLabel" style="width: 500px;">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="departmentEditCanvasLabel">
            <i class="bi bi-pencil me-2" id="canvasEditIcon"></i>
            <span id="canvasTitleText">Edit Department</span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="editDepartmentForm" novalidate>
            <input type="hidden" id="editDepartmentId" name="id">
            <input type="hidden" id="editFormMode" name="mode" value="edit">
            
            <div class="mb-3">
                <label for="editOrganizationId" class="form-label text-white">Organization <span class="text-danger">*</span></label>
                <select name="organization_id" id="editOrganizationId" class="form-select" required>
                    <option value="">Select Organization</option>
                </select>
                <div class="invalid-feedback" id="editOrganizationIdError"></div>
            </div>

            <div class="mb-3">
                <label for="editSbuId" class="form-label text-white">SBU <span class="text-danger">*</span></label>
                <select name="sbu_id" id="editSbuId" class="form-select" required>
                    <option value="">Please select Organization first...</option>
                </select>
                <div class="invalid-feedback" id="editSbuIdError"></div>
            </div>

            <div class="mb-3">
                <label for="editDepartmentName" class="form-label text-white">Name <span class="text-danger">*</span></label>
                <input type="text" name="name" id="editDepartmentName" class="form-control" required maxlength="50" placeholder="Enter department name">
                <div class="invalid-feedback" id="editDepartmentNameError"></div>
            </div>

            <div class="mb-3">
                <label for="editDepartmentCode" class="form-label text-white">Code</label>
                <input type="text" name="code" id="editDepartmentCode" class="form-control" maxlength="10" placeholder="Enter department code (e.g. DEPT-001)">
                <div class="invalid-feedback" id="editDepartmentCodeError"></div>
            </div>

            <div class="mb-3">
                <label for="editParentDepartmentId" class="form-label text-white">Parent Department</label>
                <select name="parent_department_id" id="editParentDepartmentId" class="form-select">
                    <option value="">Please select SBU first...</option>
                </select>
                <div class="invalid-feedback" id="editParentDepartmentIdError"></div>
            </div>

            <div class="mb-3">
                <label for="editDepartmentDescription" class="form-label text-white">Description</label>
                <textarea name="description" id="editDepartmentDescription" class="form-control" rows="3" placeholder="Enter department description"></textarea>
                <div class="invalid-feedback" id="editDepartmentDescriptionError"></div>
            </div>

            <div class="mb-3 d-none" id="deptScheduleModeSection">
                <label class="form-label text-white">Selection Mode</label>
                <div class="btn-group w-100" role="group" aria-label="Department Selection Mode">
                    <input type="radio" class="btn-check" name="schedule_mode" id="deptScheduleModeStandard" value="standard" checked>
                    <label class="btn btn-outline-light" for="deptScheduleModeStandard">Standard</label>
                    <input type="radio" class="btn-check" name="schedule_mode" id="deptScheduleModeCustom" value="custom">
                    <label class="btn btn-outline-light" for="deptScheduleModeCustom">Custom</label>
                </div>
            </div>

            <div id="deptWorkingScheduleFields">
                <div class="mb-3">
                    <label class="form-label text-white">Working Days</label>
                    <div class="d-flex flex-wrap gap-3">
                        @php($days = ['monday' => 'Mon', 'tuesday' => 'Tue', 'wednesday' => 'Wed', 'thursday' => 'Thu', 'friday' => 'Fri', 'saturday' => 'Sat', 'sunday' => 'Sun'])
                        @foreach($days as $dayValue => $dayLabel)
                            <div class="form-check">
                                <input class="form-check-input dept-working-day" type="checkbox" id="deptWorkingDay_{{ $dayValue }}" name="working_days[]" value="{{ $dayValue }}">
                                <label class="form-check-label text-white" for="deptWorkingDay_{{ $dayValue }}">{{ $dayLabel }}</label>
                            </div>
                        @endforeach
                    </div>
                    <div class="invalid-feedback d-block" id="editWorkingDaysError"></div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label for="editWorkingStartTime" class="form-label text-white">Working Start Time</label>
                        <input type="time" name="working_start_time" id="editWorkingStartTime" class="form-control">
                        <div class="invalid-feedback" id="editWorkingStartTimeError"></div>
                    </div>
                    <div class="col-6">
                        <label for="editWorkingEndTime" class="form-label text-white">Working End Time</label>
                        <input type="time" name="working_end_time" id="editWorkingEndTime" class="form-control">
                        <div class="invalid-feedback" id="editWorkingEndTimeError"></div>
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label for="editOpeningGracePeriod" class="form-label text-white">Opening Grace Period (min)</label>
                        <input type="number" name="opening_grace_period" id="editOpeningGracePeriod" class="form-control" min="0" max="600">
                        <div class="invalid-feedback" id="editOpeningGracePeriodError"></div>
                    </div>
                    <div class="col-6">
                        <label for="editClosingGracePeriod" class="form-label text-white">Closing Grace Period (min)</label>
                        <input type="number" name="closing_grace_period" id="editClosingGracePeriod" class="form-control" min="0" max="600">
                        <div class="invalid-feedback" id="editClosingGracePeriodError"></div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="editDepartmentIsActive" class="form-check-input" value="1">
                    <label class="form-check-label text-white" for="editDepartmentIsActive">Active</label>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top" style="border-color: #ffffffab !important">
                <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" class="btn btn-light text-dark" id="submitDepartmentBtn">
                    <i class="bi bi-check-lg me-1"></i><span id="submitBtnText">Submit</span>
                </button>
            </div>
        </form>
    </div>
</div>
