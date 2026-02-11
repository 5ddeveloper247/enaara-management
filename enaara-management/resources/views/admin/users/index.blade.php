@extends('layouts.app')

@section('title', 'Users - Admin Panel')

@section('page-title', 'Users')

@push('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <!-- Users Module CSS -->
    <link href="{{ asset('css/users.css') }}" rel="stylesheet">



    <style>
        .btn {
            font-size: 13px;
        }

        .input-group {
            border: 1px solid var(--main-color) !important;
        }

        input:focus {
            box-shadow: none !important;
            border: 1px solid var(--main-color) !important;
        }

        .badge {
            font-weight: 500 !important;
        }

        .table {
            --bs-table-bg: transparent !important;
        }

        /* Table header: match Employee Directory (dark blue bar, white uppercase) */
        #usersTable thead th {
            background-color: var(--main-color) !important;
            color: white !important;
            padding: 1rem 1.5rem !important;
            white-space: nowrap !important;
            text-transform: uppercase;
            font-size: 0.8rem;
            font-weight: 600;
            border: none;
        }

        td {
            padding: 1rem 2rem !important;
        }

        /* Toolbar row: match Employee Directory - buttons to the left of search, same row */
        .row:first-child {
            padding: 0.75rem 1.5rem;
            align-items: center;
        }
        .row:first-child > [class*="col-"]:last-child {
            display: flex;
            flex-wrap: nowrap;
            align-items: center;
            justify-content: flex-end;
            gap: 0.5rem;
        }
        /* Buttons container: above / immediately left of search */
        .dt-buttons {
            order: 1;
        }
        .dataTables_filter {
            order: 2;
            margin: 0;
        }
        .row:first-child .form-select {
            border-radius: 0.375rem;
            border: 1px solid #dee2e6;
            font-size: 13px;
            padding: 0.35rem 2rem;
        }
        /* Button group: match Employee (Select Columns + Export/PDF in one row, rounded, same height) */
        .dt-buttons {
            margin-top: 0;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
        }
        .dt-buttons .btn {
            border-radius: 0.375rem;
            font-size: 13px;
            padding: 0.35rem 0.75rem;
            font-weight: 500;
        }
        /* Select Columns: dark blue, white text (match Employee) */
        .dt-buttons .btn.bg-main,
        .dt-buttons .btn.bg-main {
            background-color: var(--main-color) !important;
            color: white !important;
            border: none !important;
        }
        .dt-buttons .btn.bg-main::after {
            display: inline-block;
            margin-left: 0.5em;
            vertical-align: 0.255em;
            content: "";
            border-top: 0.3em solid currentColor;
            border-right: 0.3em solid transparent;
            border-bottom: 0;
            border-left: 0.3em solid transparent;
        }
        /* Excel, CSV, PDF: outline style like Employee Export/Filter (light grey border, dark text) */
        .dt-buttons .btn-outline-secondary,
        .dt-buttons .btn:not(.bg-main) {
            border: 1px solid #dee2e6 !important;
            color: #495057 !important;
            background-color: #fff !important;
        }
        .dt-buttons .btn-outline-secondary:hover,
        .dt-buttons .btn:not(.bg-main):hover {
            background-color: #f8f9fa !important;
            border-color: #dee2e6 !important;
            color: #495057 !important;
        }
        /* Search input: match Employee (white, rounded, placeholder) */
        .dataTables_filter input {
            border-radius: 0.375rem;
            border: 1px solid #dee2e6;
            font-size: 13px;
            padding: 0.35rem 0.75rem;
            margin-left: 0.5rem;
        }
        .dataTables_filter input:focus {
            border-color: var(--main-color);
            outline: 0;
            box-shadow: none;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Top Header -->
        <div class="card border-0 rounded-4 mb-4">
            <div class="card-body p-0">
                <div class="row align-items-center p-4">
                    <div class="col-md-6">
                        <h5 class="mb-0">Manage Users</h5>
                    </div>
                    <div class="col-md-6 text-end">
                        <button type="button" class="btn btn-primary bg-main border-0 me-2" data-bs-toggle="offcanvas"
                            data-bs-target="#userCanvas" data-mode="add">
                            <i class="bi bi-person-plus me-1"></i>Add New User
                        </button>

                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-secondary dropdown-toggle"
                                data-bs-toggle="dropdown" aria-expanded="false" id="filterDropdownBtn">
                                <i class="bi bi-funnel me-1"></i>Filter
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end p-3" style="min-width: 300px;">
                                <li>
                                    <div class="mb-3">
                                        <label class="form-label small text-muted mb-1">Department</label>
                                        <select class="form-select form-select-sm" id="filterDepartment">
                                            <option value="">All Departments</option>
                                            <option value="Sales">Sales</option>
                                            <option value="IT">IT</option>
                                            <option value="HR">HR</option>
                                            <option value="Operations">Operations</option>
                                            <option value="Finance">Finance</option>
                                        </select>
                                    </div>
                                </li>
                                <li>
                                    <div class="mb-3">
                                        <label class="form-label small text-muted mb-1">Role</label>
                                        <select class="form-select form-select-sm" id="filterRole">
                                            <option value="">All Roles</option>
                                            <option value="Admin">Admin</option>
                                            <option value="Manager">Manager</option>
                                            <option value="Agent">Agent</option>
                                            <option value="Employee">Employee</option>
                                        </select>
                                    </div>
                                </li>
                                <li>
                                    <div class="mb-3">
                                        <label class="form-label small text-muted mb-1">Status</label>
                                        <select class="form-select form-select-sm" id="filterStatus">
                                            <option value="">All Status</option>
                                            <option value="Active">Active</option>
                                            <option value="Inactive">Inactive</option>
                                        </select>
                                    </div>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-secondary flex-fill"
                                            id="clearFiltersBtn">
                                            <i class="bi bi-x-circle me-1"></i>Clear
                                        </button>
                                        <button type="button" class="btn btn-sm btn-primary bg-main border-0 flex-fill"
                                            id="applyFiltersBtn">
                                            <i class="bi bi-check-lg me-1"></i>Apply
                                        </button>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="row g-3 px-4 pb-3">
                    <div class="col-md-3">
                        <div class="card bg-main border-0 rounded-3 shadow h-100 user-counter-card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="opacity-75 text-white mb-1 small fw-normal text-uppercase">
                                            <i class="bi bi-shield-check me-1"></i>Total Admins
                                        </h6>
                                        <div class="h4 mb-0 fw-bold text-white" id="totalAdmins">0</div>
                                    </div>
                                    <div class="text-white opacity-25">
                                        <i class="bi bi-shield-check fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 rounded-3 shadow h-100 user-counter-card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="text-muted mb-1 small fw-normal text-uppercase">
                                            <i class="bi bi-person-badge me-1"></i>Total Managers
                                        </h6>
                                        <div class="h4 mb-0 fw-bold text-main" id="totalManagers">0</div>
                                    </div>
                                    <div class="text-main opacity-25">
                                        <i class="bi bi-person-badge fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 rounded-3 shadow h-100 user-counter-card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="text-muted mb-1 small fw-normal text-uppercase">
                                            <i class="bi bi-people me-1"></i>Total Employees
                                        </h6>
                                        <div class="h4 mb-0 fw-bold text-main" id="totalEmployees">0</div>
                                    </div>
                                    <div class="text-main opacity-25">
                                        <i class="bi bi-people fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 rounded-3 shadow h-100 user-counter-card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="text-muted mb-1 small fw-normal text-uppercase">
                                            <i class="bi bi-check-circle me-1"></i>Active
                                        </h6>
                                        <div class="h4 mb-0 fw-bold text-success" id="totalActive">0</div>
                                    </div>
                                    <div class="text-success opacity-25">
                                        <i class="bi bi-check-circle fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Table (Yajra server-side) -->
                {!! $dataTable->table(['class' => 'display table table-striped', 'style' => 'width:100%']) !!}
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    @include('admin.users.delete-modal')

    <!-- User Slide-over Canvas (Bootstrap Offcanvas) -->
    @include('admin.users.side_canvas')
@endsection

@push('scripts')
    <!-- jQuery (required for DataTables) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <!-- DataTables Buttons Extension -->
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    {!! $dataTable->scripts(attributes: ['type' => 'text/javascript']) !!}
    <script>
        let usersTable;
        let userToDelete = null;
        let userToDeleteRow = null;

        $(document).ready(function() {
            usersTable = $('#usersTable').DataTable();

            // Handle Offcanvas show event (for add/edit/view)
            const userCanvas = document.getElementById('userCanvas');
            userCanvas.addEventListener('show.bs.offcanvas', function(event) {
                const button = event.relatedTarget;
                const mode = button ? button.getAttribute('data-mode') : 'add';

                const title = document.getElementById('userCanvasLabel');
                const form = document.getElementById('userForm');
                const submitBtn = form.querySelector('button[type="submit"]');

                // Reset form
                form.reset();
                $('#userForm input, #userForm select').prop('disabled', false);

                if (mode === 'add') {
                    title.textContent = 'Add New User';
                    submitBtn.textContent = 'Create User';
                    submitBtn.style.display = 'block';
                } else if (mode === 'edit') {
                    title.textContent = 'Edit User';
                    submitBtn.textContent = 'Update User';
                    submitBtn.style.display = 'block';

                    // Populate form with button data
                    $('#userName').val(button.getAttribute('data-user-name') || '');
                    $('#userEmail').val(button.getAttribute('data-user-email') || '');
                    $('#employeeId').val(button.getAttribute('data-employee-id') || '');
                    $('#userDepartment').val(button.getAttribute('data-department') || '');
                    $('#userRole').val(button.getAttribute('data-role') || '');
                } else if (mode === 'view') {
                    title.textContent = 'View User';
                    submitBtn.style.display = 'none';

                    // Populate form with button data (read-only)
                    $('#userName').val(button.getAttribute('data-user-name') || '');
                    $('#userEmail').val(button.getAttribute('data-user-email') || '');
                    $('#employeeId').val(button.getAttribute('data-employee-id') || '');
                    $('#userDepartment').val(button.getAttribute('data-department') || '');
                    $('#userRole').val(button.getAttribute('data-role') || '');
                    $('#userForm input, #userForm select').prop('disabled', true);
                }
            });

            // Handle Modal show event (for delete)
            const deleteModal = document.getElementById('deleteConfirmModal');
            deleteModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const userName = button.getAttribute('data-user-name');
                const userEmail = button.getAttribute('data-user-email');
                const userId = button.getAttribute('data-user-id');

                $('#deleteUserName').text(`${userName} (${userEmail})`);

                // Store user info for deletion
                userToDelete = userId;
                userToDeleteRow = $(button).closest('tr');
            });

            // Confirm Delete (reload table after row remove for server-side DataTables)
            $('#confirmDeleteBtn').on('click', function() {
                if (userToDelete && userToDeleteRow) {
                    if ($.fn.DataTable.isDataTable('#usersTable')) {
                        usersTable.row(userToDeleteRow).remove().draw(false);
                    }
                    userToDelete = null;
                    userToDeleteRow = null;
                }
            });


            // Form Submit (no validation)
            $('#userForm').on('submit', function(e) {
                e.preventDefault();
                const formData = {
                    name: $('#userName').val(),
                    email: $('#userEmail').val(),
                    employee_id: $('#employeeId').val(),
                    department: $('#userDepartment').val(),
                    role: $('#userRole').val(),
                    passwordOption: $('input[name="passwordOption"]:checked').val(),
                    password: $('#tempPassword').val()
                };
                console.log('Submitting user form:', formData);

                // Close offcanvas using Bootstrap
                const canvas = bootstrap.Offcanvas.getInstance(document.getElementById('userCanvas'));
                if (canvas) {
                    canvas.hide();
                }

                // Reset form
                $(this)[0].reset();
                $('#userForm input, #userForm select').prop('disabled', false);
            });
        });
    </script>
@endpush
