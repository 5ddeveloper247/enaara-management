<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="leaveTypeEditCanvas" aria-labelledby="leaveTypeEditCanvasLabel" style="width: 500px;">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="leaveTypeEditCanvasLabel">
            <i class="bi bi-pencil me-2" id="canvasEditIcon"></i>
            <span id="canvasTitleText">Edit Leave Type</span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="editLeaveTypeForm">
            <input type="hidden" id="editLeaveTypeId" name="id">
            <input type="hidden" id="editFormMode" name="mode" value="edit">
            
            <div class="mb-3">
                <label for="editOrganizationId" class="form-label text-white">Organization <span class="text-danger">*</span></label>
                <select name="organization_id" id="editOrganizationId" class="form-select" required>
                    <option value="">Select Organization</option>
                </select>
                <div class="invalid-feedback" id="editOrganizationIdError"></div>
            </div>

            <div class="mb-3">
                <label for="editDepartmentId" class="form-label text-white">Department</label>
                <select name="department_id" id="editDepartmentId" class="form-select">
                    <option value="">Select Department</option>
                </select>
                <div class="invalid-feedback" id="editDepartmentIdError"></div>
            </div>

            <div class="mb-3">
                <label for="editLeaveTypeName" class="form-label text-white">Name <span class="text-danger">*</span></label>
                <input type="text" name="name" id="editLeaveTypeName" class="form-control" required maxlength="255">
                <div class="invalid-feedback" id="editLeaveTypeNameError"></div>
            </div>

            <div class="mb-3">
                <label for="editLeaveTypeCode" class="form-label text-white">Code</label>
                <input type="text" name="code" id="editLeaveTypeCode" class="form-control" maxlength="64">
                <div class="invalid-feedback" id="editLeaveTypeCodeError"></div>
            </div>

            <div class="mb-3">
                <label for="editAnnualQuota" class="form-label text-white">Annual Quota <span class="text-danger">*</span></label>
                <input type="number" name="annual_quota" id="editAnnualQuota" class="form-control" step="0.25" min="0" max="999.99" required>
                <div class="invalid-feedback" id="editAnnualQuotaError"></div>
            </div>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="editLeaveTypeIsActive" class="form-check-input" value="1">
                    <label class="form-check-label text-white" for="editLeaveTypeIsActive">Active</label>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top" style="border-color: #ffffffab !important">
                <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" class="btn btn-light text-dark" id="submitLeaveTypeBtn">
                    <i class="bi bi-check-lg me-1"></i><span id="submitBtnText">Submit</span>
                </button>
            </div>
        </form>
    </div>
</div>
