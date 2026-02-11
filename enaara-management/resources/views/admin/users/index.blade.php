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
        <!-- Main Card -->
        <div class="card border-0 rounded-4 mb-4">
            <div class="card-body p-0">
                <!-- Header with Actions and Filters -->
                @include('admin.users.header')

                <!-- Counters -->
                @include('admin.users.counters')

                <!-- Data Table (Yajra server-side) -->
                {!! $dataTable->table(['class' => 'display table table-striped', 'style' => 'width:100%']) !!}
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    @include('admin.users.delete-modal')
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

        });
    </script>
@endpush
