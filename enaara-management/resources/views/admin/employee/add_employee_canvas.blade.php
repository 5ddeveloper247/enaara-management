<!-- Add Employee Canvas -->
<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="addEmployeeCanvas" aria-labelledby="addEmployeeCanvasLabel" style="width: 600px;">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="addEmployeeCanvasLabel">
            <i class="bi bi-person-plus me-2"></i>Add New Employee
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="addEmployeeForm">
            <!-- Personal Information -->
            <div class="mb-4">
                <h6 class="mb-3 fw-semibold small">
                    <i class="bi bi-person me-2"></i>Personal Information
                </h6>
                <div class="mb-3">
                    <label for="employeeFullName" class="form-label fw-semibold small text-white">Full Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="employeeFullName" name="full_name" required>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label for="employeeEmail" class="form-label fw-semibold small text-white">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="employeeEmail" name="email" required>
                    </div>
                    <div class="col-6">
                        <label for="employeePhone" class="form-label fw-semibold small text-white">Phone Number</label>
                        <input type="tel" class="form-control" id="employeePhone" name="phone" placeholder="+92-300-1234567">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="employeeId" class="form-label fw-semibold small text-white">Employee ID <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="employeeId" name="employee_id" required placeholder="EMP-001">
                    <small class="opacity-75 text-white">Unique internal ID for payroll syncing</small>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Employment Information -->
            <div class="mb-4">
                <h6 class="mb-3 fw-semibold small">
                    <i class="bi bi-briefcase me-2"></i>Employment Information
                </h6>
                <div class="mb-3">
                    <label for="employeeOrganization" class="form-label fw-semibold small text-white">SBU <span class="text-danger">*</span></label>
                    <select class="form-select" id="employeeOrganization" name="organization" required>
                        <option value="">Select SBU</option>
                        <option value="Enaara Developers">Enaara Developers</option>
                        <option value="Madison Square Mall Rawalpindi">Madison Square Mall Rawalpindi</option>
                        <option value="Madison Square Mall Lahore">Madison Square Mall Lahore</option>
                        <option value="Royal Swiss Lahore">Royal Swiss Lahore</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="employeeDepartment" class="form-label fw-semibold small text-white">Department <span class="text-danger">*</span></label>
                    <select class="form-select" id="employeeDepartment" name="department" required>
                        <option value="">Select Department</option>
                        <option value="Sales">Sales</option>
                        <option value="IT">IT</option>
                        <option value="HR">HR</option>
                        <option value="Operations">Operations</option>
                        <option value="Finance">Finance</option>
                        <option value="Legal">Legal</option>
                    </select>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label for="employeeType" class="form-label fw-semibold small text-white">Employee Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="employeeType" name="employee_type" required>
                            <option value="">Select Type</option>
                            <option value="Internal">Internal</option>
                            <option value="Third-party">Third-party</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label for="employmentType" class="form-label fw-semibold small text-white">Employment Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="employmentType" name="employment_type" required>
                            <option value="">Select Type</option>
                            <option value="Permanent">Permanent</option>
                            <option value="Contract">Contract</option>
                            <option value="Third-party">Third-party</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3" id="vendorSection" style="display: none;">
                    <label for="employeeVendor" class="form-label fw-semibold small text-white">Vendor</label>
                    <select class="form-select" id="employeeVendor" name="vendor">
                        <option value="">Select Vendor</option>
                        <option value="TechStaff Solutions">TechStaff Solutions</option>
                        <option value="Global Workforce Inc">Global Workforce Inc</option>
                        <option value="StaffPro Services">StaffPro Services</option>
                        <option value="Manpower Group">Manpower Group</option>
                        <option value="Adecco">Adecco</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="employeeSite" class="form-label fw-semibold small text-white">Site Assignment</label>
                    <select class="form-select" id="employeeSite" name="site_assignment">
                        <option value="">Select Site</option>
                        <option value="Head Office">Head Office</option>
                        <option value="Branch A">Branch A</option>
                        <option value="Branch B">Branch B</option>
                        <option value="Site 1">Site 1</option>
                        <option value="Site 2">Site 2</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="employeeJoinDate" class="form-label fw-semibold small text-white">Join Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="employeeJoinDate" name="join_date" required>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Floor Access Information -->
            <div class="mb-4">
                <h6 class="mb-3 fw-semibold small">
                    <i class="bi bi-layers me-2"></i>Floor Access
                </h6>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="floorAccess10" name="floor_access_10" value="1">
                    <label class="form-check-label opacity-75 text-white" for="floorAccess10">
                        <i class="bi bi-building me-1"></i>Access to 10th Floor / Corporate Office
                    </label>
                    <small class="d-block opacity-50 text-white ms-4 mt-1">Grant employee access to corporate office floor</small>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Biometric Information -->
            <div class="mb-4">
                <h6 class="mb-3 fw-semibold small">
                    <i class="bi bi-fingerprint me-2"></i>Biometric Information
                </h6>
                <div class="mb-3">
                    <label for="biometricId" class="form-label fw-semibold small text-white">Biometric ID</label>
                    <input type="text" class="form-control" id="biometricId" name="biometric_id" placeholder="BIO-000001">
                    <small class="opacity-75 text-white">Leave empty if not linked yet</small>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="syncBiometric" name="sync_biometric" value="1">
                    <label class="form-check-label opacity-75 text-white" for="syncBiometric">
                        Sync with biometric system immediately
                    </label>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- User Account Creation (Optional) -->
            <div class="mb-4">
                <h6 class="mb-3 fw-semibold small">
                    <i class="bi bi-person-badge me-2"></i>User Account (Optional)
                </h6>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="createUserAccount" name="create_user_account" value="1">
                    <label class="form-check-label opacity-75 text-white" for="createUserAccount">
                        Create user account for this employee
                    </label>
                </div>
                <div id="userAccountSection" style="display: none;">
                    <div class="mb-3">
                        <label for="userRole" class="form-label fw-semibold small text-white">Role</label>
                        <select class="form-select" id="userRole" name="user_role">
                            <option value="">Select Role</option>
                            <option value="Admin">Admin</option>
                            <option value="Manager">Manager</option>
                            <option value="Agent">Agent</option>
                            <option value="Employee">Employee</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="passwordOption" id="setPassword" value="set" checked>
                            <label class="form-check-label opacity-75 text-white" for="setPassword">
                                Set Temporary Password
                            </label>
                        </div>
                        <div class="password-input-group ms-4">
                            <label for="tempPassword" class="form-label fw-semibold small text-white">Temporary Password</label>
                            <input type="password" class="form-control" id="tempPassword" name="password">
                            <small class="opacity-50 text-white">User will be required to change on first login</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="passwordOption" id="sendInvite" value="invite">
                            <label class="form-check-label opacity-75 text-white" for="sendInvite">
                                Send Invite Email
                            </label>
                        </div>
                        <small class="opacity-50 text-white ms-4">User will receive an email with password setup instructions</small>
                    </div>
                </div>
            </div>

            <!-- Form Buttons -->
            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top" style="border-color: #ffffffab !important">
                <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" class="btn btn-light text-dark border-0">Create Employee</button>
            </div>
        </form>
    </div>
</div>

