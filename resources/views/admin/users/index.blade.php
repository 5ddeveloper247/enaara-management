@extends('layouts.app')

@section('title', 'Users - Admin Panel')
@section('page-title', 'Users')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <link href="{{ asset('css/users.css') }}" rel="stylesheet">
    <style>
        .btn { font-size: 13px; }
        .table { --bs-table-bg: transparent !important; }
        th { padding: 1.3rem 2rem !important; color: var(--light-color) !important; white-space: nowrap !important; }
        td { padding: 1rem 2rem !important; }
        .dt-buttons { margin-top: 2px; }
        .offcanvas select option { background: #012445; color: #fff; }
        #userCanvas input::placeholder, #userCanvas select { color: rgba(255,255,255,0.7) !important; }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="card border-0 rounded-4 mb-4">
            <div class="card-body p-0">
                @include('admin.users.header')
                @include('admin.users.counters')
                @include('admin.users.user_table')
            </div>
        </div>
    </div>

    @include('admin.users.side_canvas')
    @include('admin.users.delete-modal')
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
    <script src="{{ asset('js/helpers.js') }}"></script>

    <script>
    (function () {
        'use strict';

        let usersTable;
        let deleteUserId = null;

        // =============================================
        // INIT
        // =============================================
        $(document).ready(function () {
            initDataTable();
            loadStats();
            initCanvasEvents();
            initDeleteModal();
            initStatusToggles();
            initResetPassword();
            initFiltersAndExport();
        });

        // =============================================
        // DATATABLE
        // =============================================
        function initDataTable() {
            usersTable = initUserDataTable('#usersTable', {
                ajax: {
                    url: window.usersDataUrl,
                    type: 'GET',
                    dataSrc: 'data',
                },
                columns: [
                    { data: null,             render: renderUser,       orderable: true  },
                    { data: 'employee_code',  render: renderEmpCode,    orderable: true  },
                    { data: 'sbu_name',       render: renderSbu,        orderable: true  },
                    { data: 'department',     render: renderDept,       orderable: true  },
                    { data: 'role',           render: renderRole,       orderable: true  },
                    { data: 'last_login',     render: renderLastLogin,  orderable: false },
                    { data: 'is_active',      render: renderStatus,     orderable: true  },
                    { data: null,             render: renderActions,    orderable: false, className: 'text-end no-toggle' },
                ],
                columnDefs: [
                    { targets: 7, orderable: false, className: 'no-toggle' },
                ],
                buttons: [{
                    extend: 'colvis',
                    text: 'Select Columns',
                    className: 'btn btn-sm border-0 bg-main text-white',
                    columns: [0, 1, 2, 3, 4, 5, 6],
                }],
                language: {
                    search: '',
                    searchPlaceholder: 'Search users...',
                    processing: '<div class="spinner-border spinner-border-sm text-secondary" role="status"></div> Loading...',
                },
            });
        }

        // =============================================
        // COLUMN RENDERERS
        // =============================================
        function renderUser(row) {
            var initials = esc(row.initials);
            var name     = esc(row.name);
            var email    = esc(row.email);
            var avatar = row.avatar_url 
                ? '<img src="' + escAttr(row.avatar_url) + '" alt="Avatar" class="user-avatar" style="object-fit:cover;">' 
                : '<div class="user-avatar">' + initials + '</div>';
                
            return '<div class="d-flex align-items-center">' +
                '<div class="me-3">' + avatar + '</div>' +
                '<div><div class="fw-semibold">' + name + '</div>' +
                '<small class="text-muted">' + email + '</small></div>' +
                '</div>';
        }

        function renderEmpCode(data) {
            if (!data || data === '-') return '<span class="text-muted">-</span>';
            return '<span class="badge px-3 rounded-1 bg-light text-dark">' + esc(data) + '</span>';
        }

        function renderSbu(data) {
            if (!data || data === '-') return '<span class="text-muted">-</span>';
            return '<span class="text-dark fw-medium">' + esc(data) + '</span>';
        }

        function renderDept(data) {
            if (!data || data === '-') return '<span class="text-muted">-</span>';
            return '<span class="badge px-3 rounded-1 bg-primary">' + esc(data) + '</span>';
        }

        function renderRole(data) {
            if (!data || data === '-') return '<span class="text-muted">-</span>';
            return '<span class="text-dark fw-semibold">' + esc(data) + '</span>';
        }

        function renderLastLogin(data) {
            return '<small class="text-muted">' + esc(data) + '</small>';
        }

        function renderStatus(data, type, row) {
            var checked = data ? 'checked' : '';
            return '<div class="form-check form-switch">' +
                '<input class="form-check-input status-toggle" type="checkbox" ' + checked +
                ' data-user-id="' + row.id + '" title="Toggle status">' +
                '</div>';
        }

        function renderActions(row) {
            var resetBtn = '';
            if (window.canResetUserPassword) {
                resetBtn = '<button type="button" class="action-btn border-0 text-dark btn-warning reset-password-btn"' +
                    ' data-user-id="' + row.id + '"' +
                    ' title="Email new temporary password"><i class="bi bi-key"></i></button>';
            }
            return '<div class="btn-group d-flex align-items-center gap-1">' +
                '<button type="button" class="action-btn border-0 text-white btn-primary edit-user-btn"' +
                ' data-bs-toggle="offcanvas" data-bs-target="#userCanvas"' +
                ' data-id="'          + row.id                        + '"' +
                ' data-name="'        + escAttr(row.name)             + '"' +
                ' data-email="'       + escAttr(row.email)            + '"' +
                ' data-employee-id="'   + (row.employee_id   || '')     + '"' +
                ' data-employee-code="'+ escAttr(row.employee_code || '') + '"' +
                ' data-employee-name="'+ escAttr(row.employee_name || '') + '"' +
                ' data-sbu-name="'    + escAttr(row.sbu_name || '')   + '"' +
                ' data-avatar-url="'  + escAttr(row.avatar_url || '') + '"' +
                ' data-role-name="'    + escAttr(row.role || '')          + '"' +
                ' data-role-id="'     + (row.role_id || '')           + '"' +
                ' title="Edit"><i class="bi bi-pencil"></i></button>' +
                resetBtn +
                '<button type="button" class="action-btn border-0 text-danger bg-danger-subtle delete-user-btn"' +
                ' data-bs-toggle="modal" data-bs-target="#deleteConfirmModal"' +
                ' data-id="'    + row.id            + '"' +
                ' data-name="'  + escAttr(row.name) + '"' +
                ' data-email="' + escAttr(row.email)+ '"' +
                ' title="Delete"><i class="bi bi-trash"></i></button>' +
                '</div>';
        }

        // =============================================
        // STATS
        // =============================================
        function loadStats() {
            $.get(window.usersStatsUrl, function (res) {
                if (!res.success) return;
                var s = res.stats;
                $('#totalAdmins').text(s.admins   || 0);
                $('#totalManagers').text(s.managers || 0);
                $('#totalEmployees').text(s.employees || 0);
                $('#totalActive').text(s.active   || 0);
            });
        }

        // =============================================
        // OFFCANVAS — ADD / EDIT
        // =============================================
        function initCanvasEvents() {
            var canvas = document.getElementById('userCanvas');
            if (!canvas) return;

            var userForm = document.getElementById('userForm');
            if (userForm) {
                userForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    submitForm();
                });
            }

            canvas.addEventListener('show.bs.offcanvas', function (e) {
                var btn = e.relatedTarget;
                if (btn && btn.classList.contains('edit-user-btn')) {
                    openEditMode(btn);
                } else {
                    openAddMode();
                }
            });

            canvas.addEventListener('hidden.bs.offcanvas', function () {
                resetForm();
            });

            document.getElementById('userEmployeeSelect').addEventListener('change', function () {
                var opt = this.options[this.selectedIndex];
                var hint = document.getElementById('emailManualHint');
                var avatarHolder = document.getElementById('userCanvasAvatar');
                if (opt.value) {
                    var name  = opt.getAttribute('data-name')  || '';
                    var email = opt.getAttribute('data-email') || '';
                    var role  = opt.getAttribute('data-role')  || '';
                    var sbu   = opt.getAttribute('data-sbu')   || '';
                    var avatarUrl = opt.getAttribute('data-avatar') || '';

                    if (name) document.getElementById('userName').value = name;
                    document.getElementById('userEmail').value = email;
                    document.getElementById('userAssignedRole').value = role || '';
                    document.getElementById('userAssignedSbu').value = sbu || '';

                    if (avatarHolder) {
                        avatarHolder.src = avatarUrl ? avatarUrl : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(name || 'User') + '&background=e6c673&color=000&size=80';
                    }

                    if (hint) {
                        hint.classList.toggle('d-none', !!email);
                    }
                } else {
                    document.getElementById('userAssignedRole').value = '';
                    document.getElementById('userAssignedSbu').value = '';
                    document.getElementById('userEmail').value = '';
                    if (avatarHolder) {
                        avatarHolder.src = 'https://ui-avatars.com/api/?name=User&background=e6c673&color=000&size=80';
                    }
                    if (hint) hint.classList.add('d-none');
                }
            });
        }

        function openAddMode() {
            document.getElementById('userCanvasLabel').textContent = 'Add New User';
            document.getElementById('userSubmitBtn').innerHTML     = '<i class="bi bi-person-check me-1"></i>Create User';
            document.getElementById('editUserId').value            = '';
            document.getElementById('userAssignedRole').value      = '';
            document.getElementById('userAssignedSbu').value       = '';
            var avatarHolder = document.getElementById('userCanvasAvatar');
            if (avatarHolder) {
                avatarHolder.src = 'https://ui-avatars.com/api/?name=User&background=e6c673&color=000&size=80';
            }
            var note = document.getElementById('createUserPasswordNote');
            if (note) note.classList.remove('d-none');
        }

        function openEditMode(btn) {
            document.getElementById('userCanvasLabel').textContent = 'Edit User';
            document.getElementById('userSubmitBtn').innerHTML     = '<i class="bi bi-check-lg me-1"></i>Update User';
            document.getElementById('editUserId').value            = btn.dataset.id;
            document.getElementById('userName').value              = btn.dataset.name  || '';
            document.getElementById('userEmail').value             = btn.dataset.email || '';
            var note = document.getElementById('createUserPasswordNote');
            if (note) note.classList.add('d-none');

            var empId    = btn.dataset.employeeId   || '';
            var empCode  = btn.dataset.employeeCode || '';
            var empName  = btn.dataset.employeeName || '';
            var roleName = btn.dataset.roleName || '';
            var sbuName  = btn.dataset.sbuName || '';
            var avatarUrl = btn.dataset.avatarUrl || '';

            var empSel   = document.getElementById('userEmployeeSelect');
            document.getElementById('userAssignedRole').value = roleName === '-' ? '' : roleName;
            document.getElementById('userAssignedSbu').value = sbuName === '-' ? '' : sbuName;

            var avatarHolder = document.getElementById('userCanvasAvatar');
            if (avatarHolder) {
                avatarHolder.src = avatarUrl ? avatarUrl : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(document.getElementById('userName').value || 'User') + '&background=e6c673&color=000&size=80';
            }

            if (empSel && empId) {
                var existing = empSel.querySelector('option[value="' + empId + '"]');
                if (!existing) {
                    var opt = document.createElement('option');
                    opt.value                       = empId;
                    opt.setAttribute('data-name',   empName);
                    opt.setAttribute('data-email',  btn.dataset.email || '');
                    opt.setAttribute('data-role',   roleName === '-' ? '' : roleName);
                    opt.setAttribute('data-sbu',    sbuName === '-' ? '' : sbuName);
                    opt.setAttribute('data-avatar', avatarUrl);
                    opt.textContent                 = empCode + ' — ' + empName;
                    empSel.insertAdjacentElement('afterend', opt);
                    empSel.appendChild(opt);
                }
                empSel.value = empId;
            }

        }

        function resetForm() {
            document.getElementById('userForm').reset();
            document.getElementById('editUserId').value = '';
            document.getElementById('userAssignedRole').value = '';
            document.getElementById('userAssignedSbu').value = '';
            var avatarHolder = document.getElementById('userCanvasAvatar');
            if (avatarHolder) {
                avatarHolder.src = 'https://ui-avatars.com/api/?name=User&background=e6c673&color=000&size=80';
            }
            var emailHint = document.getElementById('emailManualHint');
            if (emailHint) emailHint.classList.add('d-none');
            clearErrors();
            document.getElementById('userFormAlert').classList.add('d-none');
        }

        // =============================================
        // FORM SUBMIT
        // =============================================
        function submitForm() {
            clearErrors();

            var userId = document.getElementById('editUserId').value;
            var isEdit = !!userId;

            var payload = {
                name:                 document.getElementById('userName').value.trim(),
                email:                document.getElementById('userEmail').value.trim(),
                employee_id:          document.getElementById('userEmployeeSelect').value || null,
                _token:               window.csrfToken,
            };

            var url = isEdit
                ? window.usersUpdateUrl + '/' + userId + '/update'
                : window.usersStoreUrl;

            var btn = document.getElementById('userSubmitBtn');
            btn.disabled    = true;
            btn.textContent = 'Saving…';

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': window.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(payload),
            })
            .then(function (r) {
                return r.json().then(function (data) {
                    return { ok: r.ok, status: r.status, data: data };
                });
            })
            .then(function (res) {
                btn.disabled = false;
                btn.innerHTML = isEdit
                    ? '<i class="bi bi-check-lg me-1"></i>Update User'
                    : '<i class="bi bi-person-check me-1"></i>Create User';

                var data = res.data;
                if (res.ok && data.success) {
                    showAlert('success', data.message);
                    usersTable.ajax.reload(null, false);
                    loadStats();
                    setTimeout(function () {
                        bootstrap.Offcanvas.getInstance(document.getElementById('userCanvas'))?.hide();
                    }, 1400);
                } else if (data.errors) {
                    showFieldErrors(data.errors);
                    showAlert('danger', data.message || 'Please fix the highlighted fields.');
                } else {
                    showAlert('danger', data.message || 'Something went wrong.');
                }
            })
            .catch(function () {
                btn.disabled = false;
                btn.innerHTML = isEdit
                    ? '<i class="bi bi-check-lg me-1"></i>Update User'
                    : '<i class="bi bi-person-check me-1"></i>Create User';
                showAlert('danger', 'Network error. Please try again.');
            });
        }

        // =============================================
        // STATUS TOGGLE
        // =============================================
        function initStatusToggles() {
            $('#usersTable').on('change', '.status-toggle', function () {
                var userId   = this.dataset.userId;
                var isActive = this.checked ? 1 : 0;
                var toggle   = this;

                // Revert optimistically until confirmed
                toggle.checked = !toggle.checked;

                Swal.fire({
                    title: isActive ? 'Activate User?' : 'Deactivate User?',
                    text: isActive
                        ? 'This user will be able to log in again.'
                        : 'This user will be blocked from logging in.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: isActive ? '#012445' : '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: isActive ? 'Yes, activate!' : 'Yes, deactivate!',
                    cancelButtonText: 'Cancel',
                }).then(function (result) {
                    if (!result.isConfirmed) return;

                    // User confirmed — flip it back and make the call
                    toggle.checked = !!isActive;

                    fetch(window.usersStatusUrl + '/' + userId + '/status', {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept':       'application/json',
                            'X-CSRF-TOKEN': window.csrfToken,
                        },
                        body: JSON.stringify({ is_active: isActive }),
                    })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.success) {
                            Swal.fire({
                                title: isActive ? 'Activated!' : 'Deactivated!',
                                text: isActive ? 'User has been activated.' : 'User has been deactivated.',
                                icon: 'success',
                                confirmButtonColor: '#012445',
                                timer: 2000,
                                timerProgressBar: true,
                            });
                        } else {
                            toggle.checked = !toggle.checked;
                            Swal.fire('Error', data.message || 'Status update failed.', 'error');
                        }
                        loadStats();
                    })
                    .catch(function () {
                        toggle.checked = !toggle.checked;
                        Swal.fire('Error', 'Network error. Please try again.', 'error');
                    });
                });
            });
        }

        // =============================================
        // DELETE & RESET PASSWORD
        // =============================================
        function initResetPassword() {
            $('#usersTable').on('click', '.reset-password-btn', function () {
                var userId = this.getAttribute('data-user-id');
                if (!userId || !window.usersResetPasswordUrl) return;

                Swal.fire({
                    title: 'Reset Password',
                    text: 'Send a new temporary password to this user by email? They must sign in and choose a new password.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#012445',
                    cancelButtonColor: '#dc3545',
                    confirmButtonText: 'Yes, reset it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(window.usersResetPasswordUrl + '/' + userId + '/reset-password', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept':       'application/json',
                                'X-CSRF-TOKEN': window.csrfToken,
                            },
                            body: JSON.stringify({}),
                        })
                        .then(function (r) { return r.json(); })
                        .then(function (data) {
                            if (data.success) {
                                Swal.fire({
                                    title: 'Done!',
                                    text: data.message || 'Done.',
                                    icon: 'success',
                                    confirmButtonColor: '#012445'
                                });
                            } else {
                                Swal.fire('Error', data.message || 'Request failed.', 'error');
                            }
                        })
                        .catch(function () {
                            Swal.fire('Error', 'Network error.', 'error');
                        });
                    }
                });
            });
        }

        function initDeleteModal() {
            var modal = document.getElementById('deleteConfirmModal');
            if (!modal) return;

            modal.addEventListener('show.bs.modal', function (e) {
                var btn = e.relatedTarget;
                deleteUserId = btn.dataset.id;
                $('#deleteUserName').text(btn.dataset.name + ' (' + btn.dataset.email + ')');
            });

            document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
                if (!deleteUserId) return;

                var self = this;
                self.disabled    = true;
                self.textContent = 'Deleting…';

                fetch(window.usersDeleteUrl + '/' + deleteUserId + '/delete', {
                    method: 'DELETE',
                    headers: {
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': window.csrfToken,
                    },
                })
                .then(r => r.json())
                .then(function (data) {
                    self.disabled    = false;
                    self.textContent = 'Delete';
                    if (data.success) {
                        bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'))?.hide();
                        usersTable.ajax.reload(null, false);
                        loadStats();
                    }
                    deleteUserId = null;
                })
                .catch(function () {
                    self.disabled    = false;
                    self.textContent = 'Delete';
                });
            });
        }

        // =============================================
        // FILTER & EXPORT
        // =============================================

        // Custom DataTables search filter for role & status
        var _filterRole   = '';
        var _filterStatus = '';

        $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            if (settings.nTable.id !== 'usersTable') return true;
            var row     = usersTable ? usersTable.row(dataIndex).data() : null;
            if (!row) return true;

            if (_filterRole && row.role !== _filterRole) return false;
            if (_filterStatus !== '' && String(row.is_active ? '1' : '0') !== _filterStatus) return false;
            return true;
        });

        function initFiltersAndExport() {
            // Populate role dropdown after first AJAX data load completes
            if (usersTable) {
                usersTable.one('init.dt', function () {
                    populateFilterRoles();
                });
            }

            // Apply filters
            document.getElementById('applyFiltersBtn').addEventListener('click', function () {
                _filterRole   = document.getElementById('filterRole').value;
                _filterStatus = document.getElementById('filterStatus').value;
                usersTable.draw();
                // Close dropdown
                var dd = document.querySelector('[data-bs-toggle="dropdown"].dropdown-toggle');
                if (dd) bootstrap.Dropdown.getInstance(dd.closest('.btn-group').querySelector('.dropdown-toggle'))?.hide();
            });

            // Clear filters
            document.getElementById('clearFiltersBtn').addEventListener('click', function () {
                _filterRole   = '';
                _filterStatus = '';
                document.getElementById('filterRole').value   = '';
                document.getElementById('filterStatus').value = '';
                usersTable.draw();
            });

            // Export to Excel (CSV)
            document.getElementById('exportBtn').addEventListener('click', function () {
                var rows = usersTable.rows({ search: 'applied' }).data();
                var BOM  = '\uFEFF'; // UTF-8 BOM for Excel
                var headers = ['Name', 'Email', 'Employee Code', 'SBU', 'Department', 'Role', 'Status', 'Last Login'];
                var lines   = [headers.join(',')];

                rows.each(function (row) {
                    var line = [
                        csvCell(row.name),
                        csvCell(row.email),
                        csvCell(row.employee_code),
                        csvCell(row.sbu_name),
                        csvCell(row.department),
                        csvCell(row.role),
                        row.is_active ? 'Active' : 'Inactive',
                        csvCell(row.last_login),
                    ];
                    lines.push(line.join(','));
                });

                var csv  = BOM + lines.join('\n');
                var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                var link = document.createElement('a');
                link.href     = URL.createObjectURL(blob);
                link.download = 'users_' + new Date().toISOString().split('T')[0] + '.csv';
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        }

        function csvCell(val) {
            var s = (val === null || val === undefined || val === '-') ? '' : String(val);
            s = s.replace(/"/g, '""');
            return '"' + s + '"';
        }

        function populateFilterRoles() {
            var sel  = document.getElementById('filterRole');
            if (!sel) return;
            var seen = new Set();
            usersTable.rows().data().each(function (row) {
                if (row.role && row.role !== '-' && !seen.has(row.role)) {
                    seen.add(row.role);
                    var opt = document.createElement('option');
                    opt.value       = row.role;
                    opt.textContent = row.role;
                    sel.appendChild(opt);
                }
            });
        }

        // =============================================
        // HELPERS
        // =============================================
        function showAlert(type, msg) {
            var el = document.getElementById('userFormAlert');
            el.className = 'alert alert-' + type + ' small fw-semibold';
            el.textContent = msg;
            el.classList.remove('d-none');
        }

        function showFieldErrors(errors) {
            Object.entries(errors).forEach(function ([field, messages]) {
                var errEl = document.getElementById('err_' + field);
                if (errEl) {
                    errEl.textContent = messages[0];
                    errEl.classList.remove('d-none');
                }
                var input = document.querySelector('[name="' + field + '"]');
                if (input) input.classList.add('is-invalid');
            });
        }

        function clearErrors() {
            document.querySelectorAll('.field-error').forEach(function (el) {
                el.textContent = '';
                el.classList.add('d-none');
            });
            document.querySelectorAll('.is-invalid').forEach(function (el) {
                el.classList.remove('is-invalid');
            });
        }

        function esc(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function escAttr(str) {
            if (str === null || str === undefined) return '';
            return String(str).replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        }

    })();
    </script>
@endpush
