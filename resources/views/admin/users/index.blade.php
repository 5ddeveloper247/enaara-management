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
            populateFilterRoles();
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
                    { data: null,        render: renderUser,       orderable: true  },
                    { data: 'employee_code', render: renderEmpCode, orderable: true  },
                    { data: 'department', render: renderDept,      orderable: true  },
                    { data: 'role',       render: renderRole,      orderable: true  },
                    { data: 'last_login', render: renderLastLogin, orderable: false },
                    { data: 'is_active',  render: renderStatus,    orderable: true  },
                    { data: null,         render: renderActions,   orderable: false, className: 'text-end no-toggle' },
                ],
                columnDefs: [
                    { targets: 6, orderable: false, className: 'no-toggle' },
                ],
                buttons: [{
                    extend: 'colvis',
                    text: 'Select Columns',
                    className: 'btn btn-sm border-0 bg-main text-white',
                    columns: [0, 1, 2, 3, 4, 5],
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
            return '<div class="d-flex align-items-center">' +
                '<div class="user-avatar me-3">' + initials + '</div>' +
                '<div><div class="fw-semibold">' + name + '</div>' +
                '<small class="text-muted">' + email + '</small></div>' +
                '</div>';
        }

        function renderEmpCode(data) {
            if (!data || data === '-') return '<span class="text-muted">-</span>';
            return '<span class="badge px-3 rounded-1 bg-light text-dark">' + esc(data) + '</span>';
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
            return '<div class="btn-group d-flex align-items-center gap-1">' +
                '<button type="button" class="action-btn border-0 text-white btn-primary edit-user-btn"' +
                ' data-bs-toggle="offcanvas" data-bs-target="#userCanvas"' +
                ' data-id="'          + row.id                        + '"' +
                ' data-name="'        + escAttr(row.name)             + '"' +
                ' data-email="'       + escAttr(row.email)            + '"' +
                ' data-employee-id="'   + (row.employee_id   || '')     + '"' +
                ' data-employee-code="'+ escAttr(row.employee_code || '') + '"' +
                ' data-employee-name="'+ escAttr(row.employee_name || '') + '"' +
                ' data-role-id="'     + (row.role_id || '')           + '"' +
                ' title="Edit"><i class="bi bi-pencil"></i></button>' +
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
                if (opt.value) {
                    var name  = opt.getAttribute('data-name')  || '';
                    var email = opt.getAttribute('data-email') || '';
                    if (name)  document.getElementById('userName').value  = name;
                    if (email) document.getElementById('userEmail').value = email;
                }
            });

            document.getElementById('userForm').addEventListener('submit', function (e) {
                e.preventDefault();
                submitForm();
            });
        }

        function openAddMode() {
            document.getElementById('userCanvasLabel').textContent = 'Add New User';
            document.getElementById('userSubmitBtn').innerHTML     = '<i class="bi bi-person-check me-1"></i>Create User';
            document.getElementById('editUserId').value            = '';
            document.getElementById('passwordRequired').style.display = 'inline';
            document.getElementById('passwordHint').textContent   = 'Required. Min. 8 chars with uppercase, lowercase and numbers.';
        }

        function openEditMode(btn) {
            document.getElementById('userCanvasLabel').textContent = 'Edit User';
            document.getElementById('userSubmitBtn').innerHTML     = '<i class="bi bi-check-lg me-1"></i>Update User';
            document.getElementById('editUserId').value            = btn.dataset.id;
            document.getElementById('userName').value              = btn.dataset.name  || '';
            document.getElementById('userEmail').value             = btn.dataset.email || '';
            document.getElementById('passwordRequired').style.display = 'none';
            document.getElementById('passwordHint').textContent   = 'Leave blank to keep the current password.';

            var empId   = btn.dataset.employeeId   || '';
            var empCode = btn.dataset.employeeCode || '';
            var empName = btn.dataset.employeeName || '';
            var roleId  = btn.dataset.roleId       || '';
            var empSel  = document.getElementById('userEmployeeSelect');
            var rolSel  = document.getElementById('userRole');

            if (empSel && empId) {
                var existing = empSel.querySelector('option[value="' + empId + '"]');
                if (!existing) {
                    var opt = document.createElement('option');
                    opt.value                       = empId;
                    opt.setAttribute('data-name',  empName);
                    opt.setAttribute('data-email', btn.dataset.email || '');
                    opt.textContent                 = empCode + ' — ' + empName;
                    empSel.insertAdjacentElement('afterend', opt);
                    empSel.appendChild(opt);
                }
                empSel.value = empId;
            }

            if (rolSel) rolSel.value = roleId;
        }

        function resetForm() {
            document.getElementById('userForm').reset();
            document.getElementById('editUserId').value = '';
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
                role_id:              document.getElementById('userRole').value,
                password:             document.getElementById('userPassword').value,
                password_confirmation:document.getElementById('userPasswordConfirm').value,
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
                },
                body: JSON.stringify(payload),
            })
            .then(r => r.json())
            .then(function (data) {
                btn.disabled = false;
                btn.innerHTML = isEdit
                    ? '<i class="bi bi-check-lg me-1"></i>Update User'
                    : '<i class="bi bi-person-check me-1"></i>Create User';

                if (data.success) {
                    showAlert('success', data.message);
                    usersTable.ajax.reload(null, false);
                    loadStats();
                    setTimeout(function () {
                        bootstrap.Offcanvas.getInstance(document.getElementById('userCanvas'))?.hide();
                    }, 1400);
                } else if (data.errors) {
                    showFieldErrors(data.errors);
                } else {
                    showAlert('danger', data.message || 'Something went wrong.');
                }
            })
            .catch(function () {
                btn.disabled = false;
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

                fetch(window.usersStatusUrl + '/' + userId + '/status', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': window.csrfToken,
                    },
                    body: JSON.stringify({ is_active: isActive }),
                })
                .then(r => r.json())
                .then(function (data) {
                    if (!data.success) {
                        toggle.checked = !toggle.checked;
                    }
                    loadStats();
                })
                .catch(function () {
                    toggle.checked = !toggle.checked;
                });
            });
        }

        // =============================================
        // DELETE
        // =============================================
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
        // FILTER ROLES (populate filter dropdown)
        // =============================================
        function populateFilterRoles() {
            var sel = document.getElementById('filterRole');
            if (!sel) return;
            document.querySelectorAll('#userRole option').forEach(function (o) {
                if (!o.value) return;
                sel.insertAdjacentHTML('beforeend',
                    '<option value="' + esc(o.value) + '">' + esc(o.textContent) + '</option>');
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
