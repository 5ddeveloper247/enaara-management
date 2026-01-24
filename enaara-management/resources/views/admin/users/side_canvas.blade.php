<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="userCanvas" aria-labelledby="userCanvasLabel">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="userCanvasLabel">Add New User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="userForm">
            <!-- Account Information -->
            <div class="mb-4">
                <h6 class="mb-3">Account Information</h6>
                <div class="mb-3">
                    <label for="userName" class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="userName" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="userEmail" class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="userEmail" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="employeeId" class="form-label">Employee ID <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="employeeId" name="employee_id" required>
                    <small class="opacity-50 text-white">Unique internal ID for payroll syncing</small>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">
            
            <!-- Access Level -->
            <div class="mb-4">
                <h6 class="mb-3">Access Level</h6>
                <div class="mb-3">
                    <label for="userDepartment" class="form-label">Department <span class="text-danger">*</span></label>
                    <select class="form-select" id="userDepartment" name="department" required>
                        <option value="">Select Department</option>
                        <option value="Sales">Sales</option>
                        <option value="IT">IT</option>
                        <option value="HR">HR</option>
                        <option value="Operations">Operations</option>
                        <option value="Finance">Finance</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="userRole" class="form-label">Role <span class="text-danger">*</span></label>
                    <select class="form-select" id="userRole" name="role" required>
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
                <h6 class="mb-3">Security Settings</h6>
                <div class="mb-3">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="passwordOption" id="setPassword"
                            value="set" checked>
                        <label class="form-check-label" for="setPassword">
                            Set Temporary Password
                        </label>
                    </div>
                    <div class="password-input-group ms-4">
                        <label for="tempPassword" class="form-label">Temporary Password</label>
                        <input type="password" class="form-control" id="tempPassword" name="password">
                        <small class="opacity-50 text-white">User will be required to change on first login</small>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="passwordOption" id="sendInvite"
                            value="invite">
                        <label class="form-check-label" for="sendInvite">
                            Send Invite Email
                        </label>
                    </div>
                    <small class="opacity-50 text-white ms-4">User will receive an email with password setup instructions</small>
                </div>
            </div>

            <!-- Form Buttons -->
            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" class="btn btn-primary border-0 bg-primary-custom">Create User</button>
            </div>
        </form>
    </div>
</div>
