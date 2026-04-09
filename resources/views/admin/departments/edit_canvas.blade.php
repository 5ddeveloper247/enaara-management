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
                <input type="text" name="name" id="editDepartmentName" class="form-control" required maxlength="255" placeholder="Enter department name">
                <div class="invalid-feedback" id="editDepartmentNameError"></div>
            </div>

            <div class="mb-3">
                <label for="editDepartmentCode" class="form-label text-white">Code</label>
                <input type="text" name="code" id="editDepartmentCode" class="form-control" maxlength="32" placeholder="Enter department code (e.g. DEPT-001)">
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
