<!-- Add/Edit Role Canvas -->
<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="addRoleCanvas" aria-labelledby="addRoleCanvasLabel" style="width: 700px;">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="addRoleCanvasLabel">
            <i class="bi bi-plus-circle me-2"></i><span id="canvasTitle">Add New Role</span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="addRoleForm">
            <input type="hidden" id="roleId" name="role_id">
            
            <!-- Role Information -->
            <div class="mb-4">
                <h6 class="mb-3 fw-semibold small">
                    <i class="bi bi-info-circle me-2"></i>Role Information
                </h6>
                <div class="mb-3">
                    <label for="roleName" class="form-label fw-semibold small text-white">Role Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="roleName" name="role_name" required placeholder="e.g., HR Manager">
                    <small class="opacity-75 text-white">Enter a descriptive name for this role</small>
                </div>
                <div class="mb-3">
                    <label for="roleDescription" class="form-label fw-semibold small text-white">Description</label>
                    <textarea class="form-control" id="roleDescription" name="role_description" rows="3" placeholder="Describe the purpose and responsibilities of this role"></textarea>
                </div>
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="roleStatus" name="role_status" checked>
                        <label class="form-check-label fw-semibold small text-white" for="roleStatus">
                            Active Role
                        </label>
                    </div>
                    <small class="opacity-75 text-white">Inactive roles cannot be assigned to users</small>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Permissions Management -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 fw-semibold small">
                        <i class="bi bi-shield-lock me-2"></i>Permissions
                    </h6>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-light" id="selectAllPermissions">
                            <i class="bi bi-check-all me-1"></i>Select All
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-light" id="deselectAllPermissions">
                            <i class="bi bi-x-lg me-1"></i>Deselect All
                        </button>
                    </div>
                </div>

                <!-- Permission Categories -->
                <div class="permissions-container" style="max-height: 500px; overflow-y: auto;">
                    @php
                        $permissionCategories = [
                            'Dashboard' => ['view_dashboard', 'view_analytics', 'export_reports'],
                            'Employee Management' => ['view_employees', 'create_employee', 'edit_employee', 'delete_employee', 'view_employee_details'],
                            'User Management' => ['view_users', 'create_user', 'edit_user', 'delete_user', 'activate_user', 'deactivate_user'],
                            'SBU Management' => ['view_organizations', 'create_organization', 'edit_organization', 'delete_organization', 'assign_admin'],
                            'Department Management' => ['view_departments', 'create_department', 'edit_department', 'delete_department', 'transfer_employees'],
                            'Leave Management' => ['view_leaves', 'create_leave', 'approve_leave', 'reject_leave', 'view_leave_balance', 'manage_leave_policies'],
                            'Attendance Management' => ['view_attendance', 'mark_attendance', 'view_daily_logs', 'approve_regularization', 'manage_shifts'],
                            'Geofencing' => ['view_geofences', 'create_geofence', 'edit_geofence', 'delete_geofence', 'view_violations'],
                            'Shift Management' => ['view_shifts', 'create_shift', 'edit_shift', 'delete_shift', 'assign_shifts', 'bulk_assign'],
                            'Regularization' => ['view_regularizations', 'approve_regularization', 'reject_regularization', 'view_audit_trail'],
                            'Roles & Permissions' => ['view_roles', 'create_role', 'edit_role', 'delete_role', 'assign_permissions'],
                            'Settings' => ['view_settings', 'edit_settings', 'manage_holidays', 'manage_policies']
                        ];
                    @endphp

                    @foreach($permissionCategories as $category => $permissions)
                    <div class="permission-category mb-4">
                        <div class="d-flex align-items-center mb-2 p-2 rounded-2" style="background-color: rgba(255, 255, 255, 0.1);">
                            <i class="bi bi-folder me-2"></i>
                            <h6 class="mb-0 fw-semibold small">{{ $category }}</h6>
                            <button type="button" class="btn btn-sm btn-link text-white ms-auto p-0 category-toggle" data-category="{{ str_replace(' ', '_', strtolower($category)) }}">
                                <i class="bi bi-chevron-down"></i>
                            </button>
                        </div>
                        <div class="permission-list ps-3" id="category_{{ str_replace(' ', '_', strtolower($category)) }}">
                            @foreach($permissions as $permission)
                            <div class="form-check mb-2">
                                <input class="form-check-input permission-checkbox" type="checkbox" 
                                       id="perm_{{ $permission }}" 
                                       name="permissions[]" 
                                       value="{{ $permission }}">
                                <label class="form-check-label opacity-75 text-white small" for="perm_{{ $permission }}">
                                    {{ ucwords(str_replace('_', ' ', $permission)) }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Form Buttons -->
            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top" style="border-color: #ffffffab !important">
                <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" class="btn btn-light text-dark border-0" id="saveRoleBtn">
                    <i class="bi bi-check-lg me-1"></i>Save Role
                </button>
            </div>
        </form>
    </div>
</div>

