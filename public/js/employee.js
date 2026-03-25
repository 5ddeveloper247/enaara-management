(function() {
    'use strict';

    // ============================================
    // GLOBAL VARIABLES
    // ============================================
    let employeeTable;

    // ============================================
    // INITIALIZATION
    // ============================================
    $(document).ready(function() {
        initializeDataTable();
        initializeEventHandlers();
        updateEmployeeStats();
    });

    // ============================================
    // DATA TABLE INITIALIZATION
    // ============================================
    function initializeDataTable() {
        employeeTable = initUserDataTable('#employeeTable', {
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, 200], [10, 25, 50, 100, 200]],
            order: [[0, 'asc']],
            scrollX: false,
            responsive: {
                details: {
                    type: 'column',
                    target: 'tr'
                }
            },
            columnDefs: [
                {
                    targets: [0, 1, 2, 3, 4, 5, 6, 7],
                    visible: true
                },
                {
                    targets: 7, // Actions column
                    orderable: false,
                    className: 'no-toggle',
                    responsivePriority: 1
                },
                {
                    targets: 0, // Profile column
                    responsivePriority: 2
                },
                {
                    targets: [2, 3, 4], // Employment Type, Site, Vendor
                    responsivePriority: 4
                },
                {
                    targets: [1, 5, 6], // Biometric ID, Sync Status, Floor Access
                    responsivePriority: 5
                }
            ],
            language: {
                search: "",
                searchPlaceholder: "Search employees...",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ employees",
                infoEmpty: "No employees available",
                zeroRecords: "No matching employees found"
            },
            buttons: [{
                extend: 'colvis',
                text: 'Select Columns',
                className: 'btn btn-sm border-0 bg-main text-black',
                columns: [0, 1, 2, 3, 4, 5, 6]
            }]
        });
    }

    // ============================================
    // EVENT HANDLERS
    // ============================================
    function initializeEventHandlers() {

        // Employee Detail Canvas
        const employeeDetailCanvas = document.getElementById('employeeDetailCanvas');
        if (employeeDetailCanvas) {
            employeeDetailCanvas.addEventListener('show.bs.offcanvas', handleEmployeeDetailShow);
        }

        // Create User Account Button
        $('#createUserAccountBtn').on('click', handleCreateUserAccount);

        // Edit User Account Button
        $('#editUserAccountBtn').on('click', handleEditUserAccount);

        // Deactivate User Account Button
        $('#deactivateUserAccountBtn').on('click', handleDeactivateUserAccount);

        // Edit Employee Button
        $('#editEmployeeBtn').on('click', handleEditEmployee);

        // Create User Account Canvas
        const createUserAccountCanvas = document.getElementById('createUserAccountCanvas');
        if (createUserAccountCanvas) {
            createUserAccountCanvas.addEventListener('show.bs.offcanvas', handleCreateUserAccountShow);
            createUserAccountCanvas.addEventListener('hidden.bs.offcanvas', handleCreateUserAccountHide);
        }

        // Create User Account Form
        const createUserAccountForm = document.getElementById('createUserAccountForm');
        if (createUserAccountForm) {
            createUserAccountForm.addEventListener('submit', handleCreateUserAccountSubmit);
        }

        // Add Employee Canvas
        initializeAddEmployeeCanvas();
    }

    // ============================================
    // EMPLOYEE DETAIL HANDLERS
    // ============================================
    function handleEmployeeDetailShow(event) {
        const button = event.relatedTarget;
        if (!button || !button.classList.contains('view-employee-btn')) return;

        const employeeData = extractEmployeeData(button);
        populateEmployeeDetail(employeeData);
    }

    // ============================================
    // USER ACCOUNT HANDLERS
    // ============================================
    function handleCreateUserAccount() {
        const createUserCanvas = new bootstrap.Offcanvas(document.getElementById('createUserAccountCanvas'));
        createUserCanvas.show();
    }

    function handleEditUserAccount() {
        const employeeId = $('#detailEmployeeId').text();
        console.log('Edit user account for employee:', employeeId);
        // TODO: Implement edit user account functionality
    }

    function handleDeactivateUserAccount() {
        const employeeId = $('#detailEmployeeId').text();
        const employeeName = $('#detailEmployeeName').text();
        
        if (confirm(`Are you sure you want to deactivate the user account for ${employeeName}?`)) {
            console.log('Deactivating user account for employee:', employeeId);
            // TODO: Implement deactivate user account API call
        }
    }

    // ============================================
    // EMPLOYEE HANDLERS
    // ============================================
    function handleEditEmployee() {
        const employeeId = $('#editEmployeeBtn').attr('data-employee-id');
        console.log('Edit employee:', employeeId);
        // TODO: Implement edit employee functionality
    }

    // ============================================
    // CREATE USER ACCOUNT HANDLERS
    // ============================================
    function handleCreateUserAccountShow(event) {
        const employeeName = $('#detailEmployeeName').text();
        const employeeId = $('#detailEmployeeId').text();
        const department = $('#detailDepartment').text();

        $('#createUserEmployeeName').text(employeeName);
        $('#createUserEmployeeId').text(employeeId);
        $('#createUserDepartment').text(department);
    }

    // function handleCreateUserAccountHide() {
    //     const form = document.getElementById('createUserAccountForm');
    //     if (form) {
    //         form.reset();
    //     }
    // }

    // function handleCreateUserAccountSubmit(e) {
    //     e.preventDefault();
    //     const formData = {
    //         employeeId: $('#createUserEmployeeId').text(),
    //         email: $('#userAccountEmail').val(),
    //         role: $('#userAccountRole').val(),
    //         passwordOption: $('input[name="passwordOption"]:checked').val(),
    //         password: $('#userTempPassword').val()
    //     };
    //     console.log('Creating user account:', formData);

    //     // TODO: Implement API call to create user account

    //     // Close canvas
    //     const canvas = bootstrap.Offcanvas.getInstance(document.getElementById('createUserAccountCanvas'));
    //     if (canvas) {
    //         canvas.hide();
    //     }

    //     // Reset form
    //     this.reset();
    // }

    // ============================================
    // ADD EMPLOYEE CANVAS
    // ============================================
    function initializeAddEmployeeCanvas() {
        const addEmployeeCanvas = document.getElementById('addEmployeeCanvas');
        const employeeTypeSelect = document.getElementById('employeeType');
        const createUserCheckbox = document.getElementById('createUserAccount');
        const userAccountSection = document.getElementById('userAccountSection');
        const vendorSection = document.getElementById('vendorSection');

        // Show/hide vendor section based on employee type
        if (employeeTypeSelect) {
            employeeTypeSelect.addEventListener('change', function() {
                if (this.value === 'Third-party') {
                    vendorSection.style.display = 'block';
                    document.getElementById('employeeVendor').setAttribute('required', 'required');
                } else {
                    vendorSection.style.display = 'none';
                    document.getElementById('employeeVendor').removeAttribute('required');
                }
            });
        }

        // Show/hide user account section
        if (createUserCheckbox) {
            createUserCheckbox.addEventListener('change', function() {
                userAccountSection.style.display = this.checked ? 'block' : 'none';
            });
        }

        // Handle form submission via AJAX
        const addEmployeeForm = document.getElementById('addEmployeeForm');
        if (addEmployeeForm) {
            addEmployeeForm.addEventListener('submit', function(e) {
                e.preventDefault();

                // Clear previous errors
                addEmployeeForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                addEmployeeForm.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

                const formData = new FormData(this);

                fetch('/admin/employee/add', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: formData,
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Close canvas
                        const canvas = bootstrap.Offcanvas.getInstance(addEmployeeCanvas);
                        if (canvas) canvas.hide();

                        // Reset form
                        addEmployeeForm.reset();
                        if (vendorSection) vendorSection.style.display = 'none';
                        if (userAccountSection) userAccountSection.style.display = 'none';

                        // Reset SBU/Dept dropdowns
                        const sbuSelect = document.getElementById('employeeSbu');
                        const deptSelect = document.getElementById('employeeDepartment');
                        if (sbuSelect) { sbuSelect.innerHTML = '<option value="">Select SBU</option>'; sbuSelect.disabled = true; }
                        if (deptSelect) { deptSelect.innerHTML = '<option value="">Select Department</option>'; deptSelect.disabled = true; }

                        // Show success toast / reload table
                        alert('Employee created successfully!');
                        location.reload();
                    } else if (data.errors) {
                        // Show validation errors inline
                        Object.entries(data.errors).forEach(([field, messages]) => {
                            const input = addEmployeeForm.querySelector(`[name="${field}"]`);
                            if (input) {
                                input.classList.add('is-invalid');
                                const feedback = document.createElement('div');
                                feedback.className = 'invalid-feedback';
                                feedback.textContent = messages[0];
                                input.insertAdjacentElement('afterend', feedback);
                            }
                        });
                    } else {
                        alert(data.message || 'Something went wrong.');
                    }
                })
                .catch(() => alert('Network error. Please try again.'));
            });
        }

        // Reset form when canvas is closed
        if (addEmployeeCanvas) {
            addEmployeeCanvas.addEventListener('hidden.bs.offcanvas', function() {
                if (addEmployeeForm) addEmployeeForm.reset();
                if (vendorSection) vendorSection.style.display = 'none';
                if (userAccountSection) userAccountSection.style.display = 'none';
                addEmployeeForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                addEmployeeForm.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

                const sbuSelect = document.getElementById('employeeSbu');
                const deptSelect = document.getElementById('employeeDepartment');
                if (sbuSelect) { sbuSelect.innerHTML = '<option value="">Select SBU</option>'; sbuSelect.disabled = true; }
                if (deptSelect) { deptSelect.innerHTML = '<option value="">Select Department</option>'; deptSelect.disabled = true; }
            });
        }
    }

    // ============================================
    // STATS UPDATE
    // ============================================
    function updateEmployeeStats() {
        if (employeeTable) {
            $('#totalWorkforceBadge').text(employeeTable.rows().count());
        }
    }

})();

