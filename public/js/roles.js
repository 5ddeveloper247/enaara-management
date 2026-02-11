/**
 * Roles & Permissions Module
 * Complete roles functionality in a single file
 */

(function() {
    'use strict';

    // ============================================
    // GLOBAL VARIABLES
    // ============================================
    let rolesTable;
    let roleToDelete = null;
    let roleToDeleteRow = null;

    // ============================================
    // INITIALIZATION
    // ============================================
    $(document).ready(function() {
        initializeDataTable();
        initializeEventHandlers();
        updateRoleCounters();
    });

    // ============================================
    // DATA TABLE INITIALIZATION
    // ============================================
    function initializeDataTable() {
        rolesTable = initUserDataTable('#rolesTable', {
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            order: [[0, 'asc']],
            scrollX: false,
            columnDefs: [
                {
                    targets: [0, 1, 2, 3, 4, 5, 6],
                    visible: true
                },
                {
                    targets: 6, // Actions column
                    orderable: false,
                    className: 'no-toggle',
                    responsivePriority: 1
                },
                {
                    targets: 0, // Role Name column
                    responsivePriority: 2
                },
                {
                    targets: [1, 2, 3], // Type, Users, Permissions
                    responsivePriority: 4
                },
                {
                    targets: [4, 5], // Status, Last Updated
                    responsivePriority: 5
                }
            ],
            language: {
                search: "",
                searchPlaceholder: "Search roles...",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ roles",
                infoEmpty: "No roles available",
                zeroRecords: "No matching roles found"
            },
            buttons: [{
                extend: 'colvis',
                text: 'Select Columns',
                className: 'btn btn-sm border-0 bg-main text-black',
                columns: [0, 1, 2, 3, 4, 5]
            }]
        });
    }

    // ============================================
    // EVENT HANDLERS
    // ============================================
    function initializeEventHandlers() {
        // Export functionality
        $('#exportBtn').on('click', handleExport);

        // Add Role Canvas
        const addRoleCanvas = document.getElementById('addRoleCanvas');
        if (addRoleCanvas) {
            addRoleCanvas.addEventListener('show.bs.offcanvas', handleAddRoleCanvasShow);
            addRoleCanvas.addEventListener('hidden.bs.offcanvas', handleAddRoleCanvasHide);
        }

        // Role Detail Canvas
        const roleDetailCanvas = document.getElementById('roleDetailCanvas');
        if (roleDetailCanvas) {
            roleDetailCanvas.addEventListener('show.bs.offcanvas', handleRoleDetailShow);
        }

        // Add Role Form
        const addRoleForm = document.getElementById('addRoleForm');
        if (addRoleForm) {
            addRoleForm.addEventListener('submit', handleAddRoleSubmit);
        }

        // Permission Management
        $('#selectAllPermissions').on('click', selectAllPermissions);
        $('#deselectAllPermissions').on('click', deselectAllPermissions);
        
        // Category Toggle
        $(document).on('click', '.category-toggle', toggleCategory);

        // Edit Role from Detail
        $('#editRoleFromDetailBtn').on('click', handleEditFromDetail);

        // Delete Role Modal
        const deleteModal = document.getElementById('deleteRoleModal');
        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', handleDeleteModalShow);
        }

        $('#confirmDeleteRoleBtn').on('click', handleConfirmDelete);
    }

    // ============================================
    // EXPORT FUNCTIONALITY
    // ============================================
    function handleExport() {
        const data = rolesTable.rows({search: 'applied'}).data();
        let csvContent = "Role Name,Type,Users,Permissions,Status,Last Updated\n";
        
        data.each(function(row) {
            const roleName = $(row[0]).text().trim().replace(/,/g, ';');
            const type = $(row[1]).text().trim().replace(/,/g, ';');
            const users = $(row[2]).text().trim().replace(/,/g, ';');
            const permissions = $(row[3]).text().trim().replace(/,/g, ';');
            const status = $(row[4]).text().trim().replace(/,/g, ';');
            const updated = $(row[5]).text().trim().replace(/,/g, ';');
            csvContent += `"${roleName}","${type}","${users}","${permissions}","${status}","${updated}"\n`;
        });

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', `roles-${new Date().toISOString().split('T')[0]}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // ============================================
    // ADD ROLE CANVAS HANDLERS
    // ============================================
    function handleAddRoleCanvasShow(event) {
        const button = event.relatedTarget;
        const mode = button ? button.getAttribute('data-mode') : 'add';
        const roleId = button ? button.getAttribute('data-role-id') : null;
        const roleName = button ? button.getAttribute('data-role-name') : null;

        if (mode === 'edit' && roleId) {
            $('#canvasTitle').text('Edit Role');
            $('#roleId').val(roleId);
            $('#roleName').val(roleName || '');
            // TODO: Load role data from backend
        } else {
            $('#canvasTitle').text('Add New Role');
            $('#roleId').val('');
            resetRoleForm();
        }
    }

    function handleAddRoleCanvasHide() {
        resetRoleForm();
    }

    function resetRoleForm() {
        document.getElementById('addRoleForm').reset();
        $('#roleId').val('');
        $('.permission-checkbox').prop('checked', false);
        $('.permission-list').removeClass('show');
        $('.category-toggle').removeClass('rotated');
    }

    function handleAddRoleSubmit(e) {
        e.preventDefault();
        const formData = {
            role_id: $('#roleId').val(),
            role_name: $('#roleName').val(),
            role_description: $('#roleDescription').val(),
            role_status: $('#roleStatus').is(':checked'),
            permissions: $('.permission-checkbox:checked').map(function() {
                return $(this).val();
            }).get()
        };
        console.log('Saving role:', formData);

        // TODO: Implement API call to save role

        // Close canvas
        const canvas = bootstrap.Offcanvas.getInstance(document.getElementById('addRoleCanvas'));
        if (canvas) {
            canvas.hide();
        }

        // Show success message
        alert('Role saved successfully!');
    }

    // ============================================
    // ROLE DETAIL HANDLERS
    // ============================================
    function handleRoleDetailShow(event) {
        const button = event.relatedTarget;
        if (!button || !button.classList.contains('view-role-btn')) return;

        const roleData = {
            roleId: button.getAttribute('data-role-id') || '-',
            roleName: button.getAttribute('data-role-name') || '-',
            roleType: button.getAttribute('data-role-type') || '-',
            usersCount: button.getAttribute('data-role-users') || '0',
            permissions: button.getAttribute('data-role-permissions') || '0',
            status: button.getAttribute('data-role-status') || '-',
            updated: button.getAttribute('data-role-updated') || '-'
        };

        populateRoleDetail(roleData);
    }

    function populateRoleDetail(data) {
        $('#detailRoleName').text(data.roleName);
        $('#detailRoleId').text(`Role ID: ${data.roleId}`);
        
        // Status Badge
        if (data.status === 'Active') {
            $('#detailRoleStatus').html('<span class="badge bg-success">Active</span>');
        } else {
            $('#detailRoleStatus').html('<span class="badge bg-secondary">Inactive</span>');
        }

        // Type Badge
        if (data.roleType === 'System') {
            $('#detailRoleType').html('<span class="badge bg-primary"><i class="bi bi-gear me-1"></i>System</span>');
        } else {
            $('#detailRoleType').html('<span class="badge bg-info"><i class="bi bi-pencil-square me-1"></i>Custom</span>');
        }

        // Description (placeholder - should come from backend)
        $('#detailRoleDescription').text('Full administrative access to all system features and settings.');

        // Statistics
        $('#detailUsersCount').text(data.usersCount);
        $('#detailPermissionsCount').text(data.permissions === 'All' ? 'All' : data.permissions);

        // Permissions List (placeholder - should come from backend)
        populatePermissionsList(data.permissions);
    }

    function populatePermissionsList(permissions) {
        const listContainer = $('#detailPermissionsList');
        listContainer.empty();

        if (permissions === 'All') {
            listContainer.html('<div class="badge bg-success p-2"><i class="bi bi-check-all me-1"></i>All Permissions Granted</div>');
        } else {
            // TODO: Load actual permissions from backend
            const samplePermissions = [
                'View Dashboard', 'View Employees', 'Create Employee', 'Edit Employee',
                'View Users', 'Create User', 'Approve Leave', 'View Attendance'
            ];
            
            samplePermissions.forEach(perm => {
                listContainer.append(`
                    <div class="d-flex align-items-center mb-2 p-2 rounded-2" style="background-color: rgba(255, 255, 255, 0.1);">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        <span class="small">${perm}</span>
                    </div>
                `);
            });
        }
    }

    // ============================================
    // PERMISSION MANAGEMENT
    // ============================================
    function selectAllPermissions() {
        $('.permission-checkbox').prop('checked', true);
    }

    function deselectAllPermissions() {
        $('.permission-checkbox').prop('checked', false);
    }

    function toggleCategory() {
        const toggle = $(this);
        const categoryId = toggle.attr('data-category');
        const categoryList = $(`#category_${categoryId}`);
        
        categoryList.toggleClass('show');
        toggle.toggleClass('rotated');
        
        const icon = toggle.find('i');
        if (categoryList.hasClass('show')) {
            icon.removeClass('bi-chevron-down').addClass('bi-chevron-up');
        } else {
            icon.removeClass('bi-chevron-up').addClass('bi-chevron-down');
        }
    }

    // ============================================
    // EDIT ROLE HANDLERS
    // ============================================
    function handleEditFromDetail() {
        const roleId = $('#detailRoleId').text().replace('Role ID: ', '');
        const roleName = $('#detailRoleName').text();
        
        // Close detail canvas
        const detailCanvas = bootstrap.Offcanvas.getInstance(document.getElementById('roleDetailCanvas'));
        if (detailCanvas) {
            detailCanvas.hide();
        }

        // Open edit canvas
        setTimeout(() => {
            const editButton = $(`.edit-role-btn[data-role-id="${roleId}"]`);
            if (editButton.length) {
                editButton[0].click();
            }
        }, 300);
    }

    // ============================================
    // DELETE ROLE HANDLERS
    // ============================================
    function handleDeleteModalShow(event) {
        const button = event.relatedTarget;
        const roleId = button.getAttribute('data-role-id');
        const roleName = button.getAttribute('data-role-name');

        $('#deleteRoleName').text(roleName);
        roleToDelete = roleId;
        roleToDeleteRow = $(button).closest('tr');
    }

    function handleConfirmDelete() {
        if (roleToDelete && roleToDeleteRow) {
            console.log('Deleting role:', roleToDelete);

            // TODO: Implement API call to delete role

            if ($.fn.DataTable.isDataTable('#rolesTable')) {
                rolesTable.row(roleToDeleteRow).remove().draw();
            } else {
                roleToDeleteRow.remove();
            }

            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('deleteRoleModal'));
            if (modal) {
                modal.hide();
            }

            roleToDelete = null;
            roleToDeleteRow = null;

            // Update counters
            updateRoleCounters();
        }
    }

    // ============================================
    // COUNTERS UPDATE
    // ============================================
    function updateRoleCounters() {
        if (!rolesTable) return;

        const totalRoles = rolesTable.rows().count();
        const systemRoles = rolesTable.column(1).data().toArray().filter(type => $(type).text().includes('System')).length;
        const customRoles = totalRoles - systemRoles;
        const activeRoles = rolesTable.column(4).data().toArray().filter(status => $(status).text().includes('Active')).length;

        $('#totalRoles').text(totalRoles);
        $('#systemRoles').text(systemRoles);
        $('#customRoles').text(customRoles);
        $('#activeRoles').text(activeRoles);
    }

})();

