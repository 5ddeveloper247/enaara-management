/**
 * Roles & Permissions Module
 * Manage organizational hierarchy and access controls
 */

(function () {
    'use strict';

    // ============================================
    // GLOBAL VARIABLES
    // ============================================
    let rolesTree;
    let currentRoleId = null;
    let currentRoleData = null;
    let usersTable = null;

    // ============================================
    // INITIALIZATION
    // ============================================
    $(document).ready(function () {
        initializeRolesTree();
        initializeEventHandlers();
    });

    // ============================================
    // ROLES TREE INITIALIZATION
    // ============================================
    function initializeRolesTree() {
        if (typeof ProjectData === 'undefined' || !ProjectData.rolesPermissions) {
            console.error('ProjectData.rolesPermissions not found');
            return;
        }

        const hierarchy = ProjectData.rolesPermissions.getRolesHierarchy();
        const treeData = convertToJsTreeFormat(hierarchy);

        $('#rolesTree').jstree({
            'core': {
                'data': treeData,
                'themes': {
                    'name': 'default',
                    'responsive': true
                },
                'check_callback': true
            },
            'plugins': ['search', 'types'],
            'types': {
                'default': {
                    'icon': 'bi bi-person-badge'
                }
            }
        }).on('select_node.jstree', function (e, data) {
            handleRoleSelection(data.node.id);
        });

        rolesTree = $('#rolesTree').jstree(true);
    }

    // ============================================
    // CONVERT HIERARCHY TO JSTREE FORMAT
    // ============================================
    function convertToJsTreeFormat(node) {
        const result = {
            id: node.id,
            text: node.text,
            icon: node.icon || 'bi bi-person-badge',
            data: {
                level: node.level
            }
        };

        if (node.children && node.children.length > 0) {
            result.children = node.children.map(child => convertToJsTreeFormat(child));
        }

        return result;
    }

    // ============================================
    // ROLE SELECTION HANDLER
    // ============================================
    function handleRoleSelection(roleId) {
        currentRoleId = roleId;
        
        if (typeof ProjectData === 'undefined' || !ProjectData.rolesPermissions) {
            console.error('ProjectData.rolesPermissions not found');
            return;
        }

        const roleData = ProjectData.rolesPermissions.getRolePermissions(roleId);
        
        if (!roleData) {
            showEmptyState();
            return;
        }

        currentRoleData = roleData;
        updateSelectedRoleInfo(roleData);
        loadPermissions(roleData);
        loadDataScope(roleData);
        loadUsers(roleData);
    }

    // ============================================
    // UPDATE SELECTED ROLE INFO
    // ============================================
    function updateSelectedRoleInfo(roleData) {
        $('#selectedRoleName').text(roleData.name);
        $('#selectedRoleLevel').text(`Level ${roleData.level}`);
        $('#selectedRoleInfo').show();
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
            $('#permissionRoleLevel').text(`Level ${roleData.level} - Organizational Hierarchy`);

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
            $('[data-switch]').each(function() {
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

            const row = `
                <tr>
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
            $('#usersSearch').on('keyup', function() {
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
        // Tree search
        $('#treeSearch').on('keyup', function() {
            rolesTree.search($(this).val());
        });

        // Expand all
        $('#expandAllBtn').on('click', function() {
            rolesTree.open_all();
        });

        // Save permissions
        $('#savePermissionsBtn').on('click', function() {
            savePermissions();
        });

        // Reset permissions
        $('#resetPermissionsBtn').on('click', function() {
            if (currentRoleData) {
                loadPermissions(currentRoleData);
                loadDataScope(currentRoleData);
            }
        });

        // Data scope change handlers
        $('#dataScopeOrganization, #dataScopeDepartment, #dataScopeFloor, #dataScopeEmployee').on('change', function() {
            updateSpecificSelections();
        });

        // Export button
        $('#exportBtn').on('click', function() {
            alert('Export functionality will be implemented with backend integration.');
        });

        // Refresh tree
        $('#refreshTreeBtn').on('click', function() {
            rolesTree.refresh();
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
        $('[data-permission]').each(function() {
            const permissionName = $(this).data('permission');
            const action = $(this).data('action');
            // For bootstrap5-toggle, get the actual checkbox state
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

        // In a real application, this would send data to the backend
        console.log('Saving permissions for role:', currentRoleId);
        console.log('Permissions:', permissions);
        console.log('Data Scope:', dataScope);

        alert('Permissions saved successfully! (This is a prototype - changes are not persisted)');
    }

    // ============================================
    // GLOBAL FUNCTIONS
    // ============================================
    window.viewUser = function(userId) {
        alert(`View user details for user ID: ${userId} (This will open user detail view in a real application)`);
    };
})();

