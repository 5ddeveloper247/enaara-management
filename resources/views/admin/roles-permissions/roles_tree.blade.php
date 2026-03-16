<!-- Organizational Tree View -->
<div class="p-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h6 class="mb-0 fw-semibold">
            <i class="bi bi-diagram-3 me-2"></i>Organizational Hierarchy
        </h6>
    </div>

    <!-- Search Box -->
    <div class="mb-3">
        <input type="text" class="form-control form-control-sm" id="treeSearch" placeholder="Search roles...">
    </div>

    <!-- Role Tabs (replaces jsTree, same id kept) -->
    <div id="rolesTree" class="roles-tree-container d-flex flex-column gap-1">
        {{-- Populated by initializeRoleTabs() in roles-permissions.js --}}
    </div>

    <!-- Selected Role Info — shows users of selected role -->
    <div id="selectedRoleInfo" class="mt-3 p-3 rounded-3 bg-light border" style="display: none;">
        <h6 class="mb-2 fw-semibold small">Selected Role</h6>

        <!-- Role Label -->
        {{-- <div class="d-flex align-items-center mb-2 px-1">
            <div class="role-icon-circle bg-main text-white rounded-circle d-flex align-items-center justify-content-center me-2 flex-shrink-0" style="width: 28px; height: 28px; font-size: 0.7rem;">
                <i class="bi bi-person-badge"></i>
            </div>
            <div>
                <div class="fw-semibold small" id="selectedRoleName">-</div>
                <small class="text-muted" id="selectedRoleLevel">-</small>
            </div>
        </div> --}}

        <!-- Users List for this role -->
        <div id="roleUsersList" class="d-flex flex-column gap-1 ms-1">
            {{-- Populated dynamically when a role tab is clicked --}}
        </div>
    </div>
</div>

