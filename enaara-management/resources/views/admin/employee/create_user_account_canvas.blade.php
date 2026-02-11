<!-- Create User Account Canvas -->
<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="createUserAccountCanvas" aria-labelledby="createUserAccountCanvasLabel" style="width: 500px;">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="createUserAccountCanvasLabel">
            <i class="bi bi-person-plus me-2"></i>Create User Account
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="createUserAccountForm">
            <!-- Employee Information (Read-only) -->
            <div class="mb-4">
                <h6 class="mb-3 fw-semibold small">
                    <i class="bi bi-person me-2"></i>Employee Information
                </h6>
                <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                    <div class="mb-2">
                        <small class="opacity-75 text-white d-block mb-1">Employee Name</small>
                        <div class="fw-semibold small" id="createUserEmployeeName">-</div>
                    </div>
                    <div class="mb-2">
                        <small class="opacity-75 text-white d-block mb-1">Employee ID</small>
                        <div class="fw-semibold small" id="createUserEmployeeId">-</div>
                    </div>
                    <div>
                        <small class="opacity-75 text-white d-block mb-1">Department</small>
                        <div class="fw-semibold small" id="createUserDepartment">-</div>
                    </div>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- User Account Information -->
            <div class="mb-4">
                <h6 class="mb-3 fw-semibold small">
                    <i class="bi bi-person-badge me-2"></i>User Account Information
                </h6>
                <div class="mb-3">
                    <label for="userAccountEmail" class="form-label fw-semibold small text-white">Email Address <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="userAccountEmail" name="email" required>
                    <small class="opacity-75 text-white">This will be used for login</small>
                </div>
                <div class="mb-3">
                    <label for="userAccountRole" class="form-label fw-semibold small text-white">Role <span class="text-danger">*</span></label>
                    <select class="form-select" id="userAccountRole" name="role" required>
                        <option value="">Select Role</option>
                        <option value="Admin">Admin</option>
                        <option value="Manager">Manager</option>
                        <option value="Agent">Agent</option>
                        <option value="Employee">Employee</option>
                    </select>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Security Settings -->
            <div class="mb-4">
                <h6 class="mb-3 fw-semibold small">
                    <i class="bi bi-shield-lock me-2"></i>Security Settings
                </h6>
                <div class="mb-3">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="passwordOption" id="setPasswordOption" value="set" checked>
                        <label class="form-check-label opacity-75 text-white" for="setPasswordOption">
                            Set Temporary Password
                        </label>
                    </div>
                    <div class="password-input-group ms-4">
                        <label for="userTempPassword" class="form-label fw-semibold small text-white">Temporary Password</label>
                        <input type="password" class="form-control" id="userTempPassword" name="password">
                        <small class="opacity-50 text-white">User will be required to change on first login</small>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="passwordOption" id="sendInviteOption" value="invite">
                        <label class="form-check-label opacity-75 text-white" for="sendInviteOption">
                            Send Invite Email
                        </label>
                    </div>
                    <small class="opacity-50 text-white ms-4">User will receive an email with password setup instructions</small>
                </div>
            </div>

            <!-- Form Buttons -->
            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top" style="border-color: #ffffffab !important">
                <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" class="btn btn-light text-dark border-0">Create User Account</button>
            </div>
        </form>
    </div>
</div>

