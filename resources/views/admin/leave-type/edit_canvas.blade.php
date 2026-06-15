<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="leaveTypeEditCanvas" aria-labelledby="leaveTypeEditCanvasLabel" style="width: 500px;">
    <style>
        .lt-dept-input-box {
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
        .lt-dept-input-box:hover {
            border-color: #adb5bd;
        }
        .lt-dept-input-box.open,
        .lt-dept-input-box:focus-within {
            border-color: #86b7fe;
            box-shadow: 0 0 0 3px rgba(13,110,253,.12);
            outline: none;
        }
        .lt-dept-input-box.is-invalid {
            border-color: #dc3545;
        }
        .lt-dept-input-box.is-invalid.open {
            box-shadow: 0 0 0 3px rgba(220,53,69,.12);
        }
        .lt-dept-chip {
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
        }
        .lt-dept-chip-x {
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
        .lt-dept-chip-x:hover {
            background: #85B7EB;
            color: #042C53;
        }
        .lt-dept-ph {
            font-size: 14px;
            color: #adb5bd;
            padding: 2px 4px;
            pointer-events: none;
        }
        .lt-dept-chevron {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: #adb5bd;
            transition: transform .18s;
        }
        .lt-dept-input-box.open .lt-dept-chevron {
            transform: translateY(-50%) rotate(180deg);
        }
        .lt-dept-dropdown {
            background: #fff;
            border: 1px solid #ced4da;
            border-radius: 10px;
            margin-top: 6px;
            overflow: hidden;
            z-index: 1050;
            position: relative;
        }
        .lt-dept-search-row {
            padding: 8px;
            border-bottom: 1px solid #f0f0f0;
        }
        .lt-dept-search-row input {
            width: 100%;
            border: 1px solid #ced4da;
            border-radius: 8px;
            padding: 7px 12px;
            font-size: 13px;
            background: #f8f9fa;
            color: #212529;
            outline: none;
        }
        .lt-dept-opt-list {
            max-height: 210px;
            overflow-y: auto;
            padding: 4px 0;
        }
        .lt-dept-opt {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 14px;
            cursor: pointer;
            font-size: 14px;
            color: #212529;
            user-select: none;
        }
        .lt-dept-opt:hover {
            background: #f8f9fa;
        }
        .lt-dept-opt.picked {
            background: #e9f2ff;
        }
        .lt-dept-opt-cb {
            width: 17px;
            height: 17px;
            border-radius: 5px;
            border: 1.5px solid #adb5bd;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .lt-dept-opt.picked .lt-dept-opt-cb {
            background: #0d6efd;
            border-color: #0d6efd;
        }
        .lt-dept-opt-ck {
            display: none;
            width: 10px;
            height: 10px;
        }
        .lt-dept-opt.picked .lt-dept-opt-ck {
            display: block;
        }
        .lt-dept-no-result {
            padding: 16px;
            font-size: 13px;
            color: #adb5bd;
            text-align: center;
        }
    </style>
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
                <label for="editSbuId" class="form-label text-white">SBU <span class="text-danger">*</span></label>
                <select name="sbu_id" id="editSbuId" class="form-select" required>
                    <option value="">Please select Organization first...</option>
                </select>
                <div class="invalid-feedback" id="editSbuIdError"></div>
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label text-white mb-0">Departments</label>
                    <div class="form-check form-check-inline small">
                        <input class="form-check-input" type="checkbox" id="selectAllDepartments">
                        <label class="form-check-label text-white-50" for="selectAllDepartments" style="font-size: 0.8rem;">Select All</label>
                    </div>
                </div>
                <div id="departmentHiddenInputs"></div>
                <div id="leaveDeptBox" class="lt-dept-input-box" onclick="leaveDeptBoxClick(event)">
                    <div id="leaveDeptChips" style="display:contents"></div>
                    <span class="lt-dept-ph" id="leaveDeptPlaceholder">Select Departments...</span>
                    <svg class="lt-dept-chevron" width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div id="leaveDeptDropdown" class="lt-dept-dropdown" style="display:none">
                    <div class="lt-dept-search-row">
                        <input id="leaveDeptSearch" placeholder="Search Department..." oninput="leaveDeptRenderList()" onclick="event.stopPropagation()" autocomplete="off">
                    </div>
                    <div id="leaveDeptList" class="lt-dept-opt-list"></div>
                </div>
                <div class="invalid-feedback d-block" id="editDepartmentIdError"></div>
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
                <input type="number" name="annual_quota" id="editAnnualQuota" class="form-control" step="any" min="0" max="999.99" required>
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
                @if(validatePermissions('admin/leave-type/edit'))
                <button type="submit" class="btn btn-light text-dark" id="submitLeaveTypeBtn">
                    <i class="bi bi-check-lg me-1"></i><span id="submitBtnText">Submit</span>
                </button>
                @endif
            </div>
        </form>
    </div>
</div>
