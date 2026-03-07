/**
 * Roles & Permissions Module
 * Manage organizational hierarchy and access controls
 */

(function () {
    'use strict';

    // ============================================
    // GLOBAL VARIABLES
    // ============================================
    let currentRoleId = null;
    let currentRoleData = null;
    let currentUserId = null;
    let usersTable = null;

    // ============================================
    // INITIALIZATION
    // ============================================
    $(document).ready(function () {
        initializeRoleTabs();
        initializeEventHandlers();
    });

    // ============================================
    // ROLE TABS
    // ============================================
    function initializeRoleTabs() {
        if (typeof ProjectData === 'undefined' || !ProjectData.rolesPermissions) {
            console.error('ProjectData.rolesPermissions not found');
            return;
        }

        const hierarchy = ProjectData.rolesPermissions.getRolesHierarchy();
        const flatRoles = flattenHierarchy(hierarchy);
        const container = document.getElementById('rolesTree');

        container.innerHTML = flatRoles.map((role, index) => `
            <button
                type="button"
                class="btn btn-sm text-start role-tab-btn px-3 py-2 rounded-2 ${index === 0 ? 'btn-dark active' : 'btn-light text-muted'}"
                data-role-id="${role.id}"
                onclick="handleRoleTabClick('${role.id}')"
            >
                <i class="bi bi-person-badge me-2"></i>
                ${role.text}
            </button>
        `).join('');

        // Auto-select first role tab
        if (flatRoles.length > 0) {
            handleRoleTabClick(flatRoles[0].id);
        }
    }

    // ============================================
    // FLATTEN nested tree → flat ordered array
    // ============================================
    function flattenHierarchy(node, result = []) {
        if (!node) return result;
        if (Array.isArray(node)) {
            node.forEach(n => flattenHierarchy(n, result));
        } else {
            result.push({ id: node.id, text: node.text, level: node.level });
            if (node.children && node.children.length > 0) {
                node.children.forEach(child => flattenHierarchy(child, result));
            }
        }
        return result;
    }

    // ============================================
    // ROLE TAB CLICK
    // → highlights tab, loads users list below
    // ============================================
    function handleRoleTabClick(roleId) {
        currentRoleId = roleId;
        currentUserId = null;

        // Update tab active styling
        document.querySelectorAll('.role-tab-btn').forEach(btn => {
            const isActive = btn.dataset.roleId === roleId;
            btn.classList.toggle('active', isActive);
            btn.classList.toggle('btn-dark', isActive);
            btn.classList.toggle('btn-light', !isActive);
            btn.classList.toggle('text-muted', !isActive);
        });

        if (typeof ProjectData === 'undefined' || !ProjectData.rolesPermissions) {
            console.error('ProjectData.rolesPermissions not found');
            return;
        }

        const roleData = ProjectData.rolesPermissions.getRolePermissions(roleId);
        if (!roleData) { showEmptyState(); return; }

        currentRoleData = roleData;

        // Show role label + users list in sidebar
        updateSelectedRoleInfo(roleData);

        // Hide right panel until a user is picked
        showEmptyState();
    }

    // ============================================
    // UPDATE SELECTED ROLE INFO + USERS LIST
    // ============================================
    function updateSelectedRoleInfo(roleData) {
        $('#selectedRoleName').text(roleData.name);
        $('#selectedRoleLevel').text(`Level ${roleData.level}`);
        $('#selectedRoleInfo').show();

        const list = document.getElementById('roleUsersList');

        if (!roleData.users || roleData.users.length === 0) {
            list.innerHTML = `<div class="text-muted small px-2 py-1">No users in this role</div>`;
            return;
        }

        list.innerHTML = roleData.users.map(user => `
            <button
                type="button"
                class="btn btn-sm text-start user-tab-btn px-3 py-2 rounded-2 btn-light text-muted"
                data-user-id="${user.id}"
                onclick="handleUserTabClick('${user.id}')"
            >
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar-circle bg-main text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 24px; height: 24px; font-size: 0.6rem;">
                        ${user.avatar}
                    </div>
                    <div class="text-truncate">
                        <div class="fw-semibold" style="font-size: 0.78rem;">${user.name}</div>
                        <div class="text-muted" style="font-size: 0.68rem;">${user.department}</div>
                    </div>
                </div>
            </button>
        `).join('');
    }

    // ============================================
    // USER TAB CLICK
    // → highlights user, loads right panel for them
    // ============================================
    function handleUserTabClick(userId) {
        currentUserId = userId;

        // Update user button active styling
        document.querySelectorAll('.user-tab-btn').forEach(btn => {
            const isActive = btn.dataset.userId == userId;
            btn.classList.toggle('active', isActive);
            btn.classList.toggle('btn-dark', isActive);
            btn.classList.toggle('btn-light', !isActive);
            btn.classList.toggle('text-muted', !isActive);
        });

        // Find user in current role's users list
        const user = currentRoleData.users.find(u => u.id == userId);
        if (!user) return;

        // Build a user-specific roleData view:
        // permissions & dataScope come from the role, but header shows the user
        const userRoleData = Object.assign({}, currentRoleData, {
            name: user.name,
            subtitle: `${currentRoleData.name} · Level ${currentRoleData.level}`,
            _user: user
        });

        loadPermissions(userRoleData);
        loadDataScope(userRoleData);
        loadUsers(userRoleData);
    }

    // ============================================
    // LOAD PERMISSIONS
    // ============================================
    function loadPermissions(roleData) {
        $('#permissionsLoading').show();
        $('#permissionsEmpty').hide();
        $('#permissionsContent').hide();

        setTimeout(() => {
            $('#permissionsLoading').hide();
            $('#permissionsContent').show();

            // Update role header
            $('#permissionRoleName').text(roleData.name);
            $('#permissionRoleLevel').text(
                roleData.subtitle || `Level ${roleData.level} - Organizational Hierarchy`
            );

            // Build permissions accordion
            const accordion = $('#permissionsAccordion');
            accordion.empty();

            const permissionGroups = {
                'Core Management': ['Dashboard', 'Employees', 'Departments', 'Organizations'],
                'Attendance & Time': ['Daily Logs', 'Leave Requests', 'Overtime', 'Regularization', 'Shift Planner'],
                'Location & Security': ['Geofencing'],
                'Analytics & Reports': ['Reports'],
                'System Administration': ['Settings', 'Roles & Permissions']
            };

            let accordionItemIndex = 0;

            Object.keys(permissionGroups).forEach(groupName => {
                const groupPermissions = permissionGroups[groupName];
                const hasAnyPermission = groupPermissions.some(perm => roleData.permissions[perm]);

                if (!hasAnyPermission) return;

                const accordionItemId = `accordion-${accordionItemIndex}`;
                const accordionItem = `
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading-${accordionItemIndex}">
                            <button class="accordion-button ${accordionItemIndex === 0 ? '' : 'collapsed'}" type="button" data-bs-toggle="collapse" data-bs-target="#${accordionItemId}" aria-expanded="${accordionItemIndex === 0 ? 'true' : 'false'}">
                                <i class="bi bi-folder me-2"></i>${groupName}
                            </button>
                        </h2>
                        <div id="${accordionItemId}" class="accordion-collapse collapse ${accordionItemIndex === 0 ? 'show' : ''}" data-bs-parent="#permissionsAccordion">
                            <div class="accordion-body">
                                <div class="row g-3">
                                    ${groupPermissions.map(perm => {
                    const permData = roleData.permissions[perm];
                    if (!permData) return '';
                    return buildPermissionRow(perm, permData);
                }).join('')}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                accordion.append(accordionItem);
                accordionItemIndex++;
            });

            // Initialize Bootstrap 5 Toggle switches
            $('[data-switch]').each(function () {
                const isInherited = $(this).data('inherited') === true;
                $(this).bootstrapToggle({
                    on: 'ON',
                    off: 'OFF',
                    size: 'small',
                    onstyle: 'success',
                    offstyle: 'secondary'
                });

                // Disable inherited permissions
                if (isInherited) {
                    $(this).bootstrapToggle('disable');
                }
            });
        }, 300);
    }

    // ============================================
    // BUILD PERMISSION ROW
    // ============================================
    function buildPermissionRow(permissionName, permData) {
        const isInherited = permData.inherited;
        const inheritedClass = isInherited ? 'inherited-permission' : '';
        const inheritedBadge = isInherited ? '<span class="badge bg-info ms-2">Inherited</span>' : '';

        return `
            <div class="col-md-6">
                <div class="permission-item p-3 border rounded-3 ${inheritedClass}">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="mb-0 fw-semibold small">${permissionName} ${inheritedBadge}</h6>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small text-muted mb-1">View</label>
                            <input type="checkbox" data-switch data-permission="${permissionName}" data-action="view" data-inherited="${isInherited}" ${permData.view ? 'checked' : ''}>
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted mb-1">Edit</label>
                            <input type="checkbox" data-switch data-permission="${permissionName}" data-action="edit" data-inherited="${isInherited}" ${permData.edit ? 'checked' : ''}>
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted mb-1">Delete</label>
                            <input type="checkbox" data-switch data-permission="${permissionName}" data-action="delete" data-inherited="${isInherited}" ${permData.delete ? 'checked' : ''}>
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted mb-1">Approve</label>
                            <input type="checkbox" data-switch data-permission="${permissionName}" data-action="approve" data-inherited="${isInherited}" ${permData.approve ? 'checked' : ''}>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // ============================================
    // LOAD DATA SCOPE
    // ============================================
    function loadDataScope(roleData) {
        if (!roleData.dataScope) return;

        $('#dataScopeOrganization').val(roleData.dataScope.organization || 'all');
        $('#dataScopeDepartment').val(roleData.dataScope.department || 'all');
        $('#dataScopeFloor').val(roleData.dataScope.floor || 'all');
        $('#dataScopeEmployee').val(roleData.dataScope.employee || 'all');

        // Show/hide specific selections
        updateSpecificSelections();
    }

    // ============================================
    // LOAD USERS
    // ============================================
    function loadUsers(roleData) {
        if (!roleData.users || roleData.users.length === 0) {
            $('#usersTableBody').html('<tr><td colspan="5" class="text-center text-muted">No users assigned to this role</td></tr>');
            return;
        }

        const tbody = $('#usersTableBody');
        tbody.empty();

        roleData.users.forEach(user => {
            const statusBadge = user.status === 'Active'
                ? '<span class="badge bg-success">Active</span>'
                : '<span class="badge bg-secondary">Inactive</span>';

            const isCurrentUser = roleData._user && roleData._user.id == user.id;
            const highlightClass = isCurrentUser ? 'table-active fw-bold' : '';

            const row = `
                <tr class="${highlightClass}">
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle bg-main text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                ${user.avatar}
                            </div>
                            <div>
                                <div class="fw-semibold small">${user.name}</div>
                            </div>
                        </div>
                    </td>
                    <td><small>${user.email}</small></td>
                    <td><small>${user.department}</small></td>
                    <td>${statusBadge}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="viewUser('${user.id}')" title="View User">
                            <i class="bi bi-eye"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });

        // Initialize DataTable if not already initialized
        if (!usersTable) {
            usersTable = $('#usersTable').DataTable({
                paging: true,
                pageLength: 10,
                searching: true,
                info: false,
                lengthChange: false,
                order: [[0, 'asc']],
                language: {
                    search: "",
                    searchPlaceholder: "Search users...",
                    zeroRecords: "No matching users found"
                }
            });

            // Custom search
            $('#usersSearch').on('keyup', function () {
                usersTable.search(this.value).draw();
            });
        } else {
            usersTable.clear();
            usersTable.rows.add($('#usersTableBody tr'));
            usersTable.draw();
        }
    }

    // ============================================
    // SHOW EMPTY STATE
    // ============================================
    function showEmptyState() {
        $('#permissionsLoading').hide();
        $('#permissionsEmpty').show();
        $('#permissionsContent').hide();
    }

    // ============================================
    // EVENT HANDLERS
    // ============================================
    function initializeEventHandlers() {
        // Search filters both role tabs and user buttons
        $('#treeSearch').on('keyup', function () {
            const query = $(this).val().toLowerCase();
            $('.role-tab-btn, .user-tab-btn').each(function () {
                const text = $(this).text().toLowerCase();
                $(this).toggle(text.includes(query));
            });
        });

        // Save permissions
        $('#savePermissionsBtn').on('click', function () {
            savePermissions();
        });

        // Reset permissions
        $('#resetPermissionsBtn').on('click', function () {
            if (currentRoleData) {
                loadPermissions(currentRoleData);
                loadDataScope(currentRoleData);
            }
        });

        // Data scope change handlers
        $('#dataScopeOrganization, #dataScopeDepartment, #dataScopeFloor, #dataScopeEmployee').on('change', function () {
            updateSpecificSelections();
        });

        // Export button
        $('#exportBtn').on('click', function () {
            alert('Export functionality will be implemented with backend integration.');
        });

        // Refresh tabs
        $('#refreshTreeBtn').on('click', function () {
            initializeRoleTabs();
        });
    }

    // ============================================
    // UPDATE SPECIFIC SELECTIONS
    // ============================================
    function updateSpecificSelections() {
        const orgScope = $('#dataScopeOrganization').val();
        const deptScope = $('#dataScopeDepartment').val();

        if (orgScope === 'specific' || deptScope === 'specific') {
            $('#specificSelections').show();
        } else {
            $('#specificSelections').hide();
        }
    }

    // ============================================
    // SAVE PERMISSIONS
    // ============================================
    function savePermissions() {
        if (!currentRoleId || !currentRoleData) {
            alert('No role selected');
            return;
        }

        // Collect permission changes
        const permissions = {};
        $('[data-permission]').each(function () {
            const permissionName = $(this).data('permission');
            const action = $(this).data('action');
            const checkbox = $(this);
            const isChecked = checkbox.is(':checked') || (checkbox.data('toggle') && checkbox.prop('checked'));

            if (!permissions[permissionName]) {
                permissions[permissionName] = {};
            }
            permissions[permissionName][action] = isChecked;
        });

        // Collect data scope
        const dataScope = {
            organization: $('#dataScopeOrganization').val(),
            department: $('#dataScopeDepartment').val(),
            floor: $('#dataScopeFloor').val(),
            employee: $('#dataScopeEmployee').val()
        };

        console.log('Saving permissions for role:', currentRoleId, '/ user:', currentUserId);
        console.log('Permissions:', permissions);
        console.log('Data Scope:', dataScope);

        alert('Permissions saved successfully! (This is a prototype - changes are not persisted)');
    }

    // ============================================
    // GLOBAL FUNCTIONS
    // ============================================
    window.viewUser = function (userId) {
        alert(`View user details for user ID: ${userId} (This will open user detail view in a real application)`);
    };

    window.handleRoleTabClick = handleRoleTabClick;
    window.handleUserTabClick = handleUserTabClick;

})();