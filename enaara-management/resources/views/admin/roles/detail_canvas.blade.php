<!-- Role Detail Canvas -->
<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="roleDetailCanvas" aria-labelledby="roleDetailCanvasLabel" style="width: 600px;">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="roleDetailCanvasLabel">
            <i class="bi bi-shield-check me-2"></i>Role Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <!-- Role Information -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-info-circle me-2"></i>Role Information
            </h6>
            <div class="p-3 rounded-3 border mb-3" style="border-color: #ffffff1a !important;">
                <div class="d-flex align-items-center mb-3">
                    <div class="role-icon-large me-3">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="fw-semibold mb-0" id="detailRoleName">Admin</h5>
                        <small class="opacity-75 text-white" id="detailRoleId">Role ID: ROL-001</small>
                    </div>
                    <div id="detailRoleStatus">
                        <span class="badge bg-success">Active</span>
                    </div>
                </div>
                <div class="mb-2">
                    <small class="opacity-75 text-white d-block mb-1">Type</small>
                    <div id="detailRoleType">
                        <span class="badge bg-primary">System</span>
                    </div>
                </div>
                <div>
                    <small class="opacity-75 text-white d-block mb-1">Description</small>
                    <div class="fw-semibold small" id="detailRoleDescription">Full administrative access to all system features and settings.</div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Statistics -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-bar-chart me-2"></i>Statistics
            </h6>
            <div class="row g-3">
                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-1">Users Assigned</small>
                        <div class="h4 mb-0 fw-bold" id="detailUsersCount">0</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-1">Total Permissions</small>
                        <div class="h4 mb-0 fw-bold" id="detailPermissionsCount">0</div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Permissions List -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-shield-lock me-2"></i>Assigned Permissions
            </h6>
            <div class="permissions-list-detail" style="max-height: 400px; overflow-y: auto;" id="detailPermissionsList">
                <!-- Permissions will be populated via JavaScript -->
                <div class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-white" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top" style="border-color: #ffffffab !important">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Close</button>
            <button type="button" class="btn btn-light text-dark border-0" id="editRoleFromDetailBtn">
                <i class="bi bi-pencil me-1"></i>Edit Role
            </button>
        </div>
    </div>
</div>

