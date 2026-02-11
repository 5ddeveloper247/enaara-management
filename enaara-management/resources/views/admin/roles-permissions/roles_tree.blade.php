<!-- Organizational Tree View -->
<div class="p-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h6 class="mb-0 fw-semibold">
            <i class="bi bi-diagram-3 me-2"></i>Organizational Hierarchy
        </h6>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="expandAllBtn" title="Expand All">
            <i class="bi bi-arrows-angle-expand"></i>
        </button>
    </div>
    
    <!-- Search Box -->
    <div class="mb-3">
        <input type="text" class="form-control form-control-sm" id="treeSearch" placeholder="Search roles...">
    </div>

    <!-- jsTree Container -->
    <div id="rolesTree" class="roles-tree-container"></div>

    <!-- Selected Role Info -->
    <div id="selectedRoleInfo" class="mt-3 p-3 rounded-3 bg-light border" style="display: none;">
        <h6 class="mb-2 fw-semibold small">Selected Role</h6>
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
                <div class="role-icon-circle bg-main text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="bi bi-person-badge"></i>
                </div>
            </div>
            <div class="flex-grow-1 ms-3">
                <div class="fw-semibold" id="selectedRoleName">-</div>
                <small class="text-muted" id="selectedRoleLevel">-</small>
            </div>
        </div>
    </div>
</div>

