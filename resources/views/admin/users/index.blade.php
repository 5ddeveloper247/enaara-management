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

        th {
            padding: 1.3rem 2rem !important;
            color: var(--light-color) !important;
            white-space: nowrap !important;
        }

        td {
            padding: 1rem 2rem !important;
        }

        .dt-buttons {
            margin-top: 2px;
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

                <!-- Data Table -->
                @include('admin.users.user_table')
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
    <!-- Common Helper Functions -->
    <script src="{{ asset('js/helpers.js') }}"></script>

    <script>
        // Global variables
        let usersTable;
        let userToDelete = null;
        let userToDeleteRow = null;

        $(document).ready(function() {
            // Initialize DataTable
            usersTable = initUserDataTable('#usersTable', {
                columnDefs: [{
                        targets: [0, 1, 2, 3, 4, 5, 6], // User, Status, Actions - visible by default
                        visible: true,
                        className: 'default-col'
                    },
                    {
                        targets: [], // Last Login - hidden initially
                        visible: false
                    },
                    {
                        targets: 6, // Actions column
                        orderable: false,
                        className: 'no-toggle'
                    }
                ],
                buttons: [{
                    extend: 'colvis',
                    text: 'Select Columns',
                    className: 'btn btn-sm border-0 bg-main text-white',
                    columns: [0, 1, 2, 3, 4, 5] // All columns except Actions
                }]
            });

            // Note: User creation is now handled through Employee module

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

            // Confirm Delete
            $('#confirmDeleteBtn').on('click', function() {
                if (userToDelete && userToDeleteRow) {
                    console.log('Deleting user:', userToDelete);

                    if ($.fn.DataTable.isDataTable('#usersTable')) {
                        usersTable.row(userToDeleteRow).remove().draw();
                    } else {
                        userToDeleteRow.remove();
                    }

                    // Modal will close automatically via data-bs-dismiss attribute
                    userToDelete = null;
                    userToDeleteRow = null;
                }
            });

        });
    </script>
@endpush
