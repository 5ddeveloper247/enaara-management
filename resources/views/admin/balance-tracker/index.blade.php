@extends('layouts.app')

@section('title', 'Balance Tracker - Admin Panel')

@section('page-title', 'Balance Tracker')

@push('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <!-- Balance Tracker Module CSS -->
    <link href="{{ asset('css/balance-tracker.css') }}" rel="stylesheet">

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

        .card .badge {
            font-weight: 500 !important;
            padding: .3rem .8rem !important;
            border-radius: 4px !important;
        }

        .table {
            --bs-table-bg: transparent !important;
        }

        th {
            padding: 0.75rem 1rem !important;
            color: var(--light-color) !important;
            white-space: nowrap !important;
            font-size: 0.85rem !important;
        }

        td {
            padding: 0.75rem 1rem !important;
        }

        .dt-control {
            padding-left: 5px !important;
            padding-right: 0 !important;
        }

        .dt-buttons {
            margin-top: 2px;
        }

        .progress {
            height: 8px;
            border-radius: 4px;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h5 class="mb-0">Leave Balance Tracker</h5>
                <small class="text-muted">Monitor and manage employee leave balances</small>
            </div>
            <div class="col-md-6 text-end">
                <button type="button" class="btn btn-outline-secondary me-2" id="exportBtn">
                    <i class="bi bi-download me-1"></i>Export All Balances
                </button>
            </div>
        </div>

        <!-- Counters -->
        @include('admin.balance-tracker.counters')

        <!-- Master Balance Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="card border-0 rounded-4 mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4 pb-4 border-bottom">
                            <h6 class="mb-0 fw-semibold">Master Balance Table</h6>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="exportTableBtn">
                                    <i class="bi bi-download me-1"></i>Export
                                </button>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle"
                                        data-bs-toggle="dropdown" aria-expanded="false" id="filterDropdownBtn">
                                        <i class="bi bi-funnel me-1"></i>Filter
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end p-3" style="min-width: 300px;">
                                        <li>
                                            <div class="mb-3">
                                                <label class="form-label small text-muted mb-1">Organization</label>
                                                <select class="form-select form-select-sm" id="filterOrganization">
                                                    <option value="">All Organizations</option>
                                                    @foreach($organizations as $org)
                                                        <option value="{{ $org->name }}" data-id="{{ $org->id }}">
                                                            {{ $org->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="mb-3">
                                                <label class="form-label small text-muted mb-1">Department</label>
                                                <select class="form-select form-select-sm" id="filterDepartment">
                                                    <option value="">All Departments</option>
                                                    @foreach($departments as $dept)
                                                        <option value="{{ $dept->name }}">{{ $dept->name }}</option>
                                                    @endforeach
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
                                                <button type="button"
                                                    class="btn btn-sm btn-primary bg-main border-0 flex-fill"
                                                    id="applyFiltersBtn">
                                                    <i class="bi bi-check-lg me-1"></i>Apply
                                                </button>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        @include('admin.balance-tracker.balance_table')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Manual Adjustment Canvas -->
    @include('admin.balance-tracker.adjustment_canvas')
@endsection

@push('scripts')
    <script>
        const balanceData = @json($balances);
    </script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <!-- DataTables Responsive Extension -->
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <!-- DataTables Buttons Extension -->
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <!-- Common Helper Functions -->
    <script src="{{ asset('js/helpers.js') }}"></script>

    <script>
        // Global variables
        let balanceTable;

        $(document).ready(function () {
            initializeBalanceTable();

            // Filter functionality
            $('#applyFiltersBtn').on('click', function () {
                const organization = $('#filterOrganization').val();
                const department = $('#filterDepartment').val();

                balanceTable.column(2).search(organization);
                balanceTable.column(3).search(department);

                balanceTable.draw();
                $('.dropdown-toggle').dropdown('hide');
            });

            $('#clearFiltersBtn').on('click', function () {
                $('#filterOrganization').val('');
                $('#filterDepartment').val('');

                balanceTable.columns().search('');
                balanceTable.draw();
            });

            // Manual adjustment button
            $(document).on('click', '.adjust-balance-btn', function (e) {
                e.stopPropagation();
                const employeeId = $(this).data('employee-id');
                const employee = balanceData.find(emp => emp.id === employeeId);
                if (employee) {
                    showAdjustmentCanvas(employee);
                }
            });

            // Dynamic Department Filtering
            $('#filterOrganization').on('change', function () {
                const orgId = $(this).find(':selected').data('id');
                const deptSelect = $('#filterDepartment');

                // Clear existing options except the first one
                deptSelect.html('<option value="">All Departments</option>');

                // Fetch departments via AJAX
                const url = "{{ route('admin.role.departmentsByOrganization') }}";
                $.ajax({
                    url: url,
                    data: { organization_id: orgId },
                    success: function (response) {
                        if (response.success) {
                            response.departments.forEach(function (dept) {
                                deptSelect.append(`<option value="${dept.name}">${dept.name}</option>`);
                            });
                        }
                    },
                    error: function () {
                        console.error('Failed to fetch departments');
                    }
                });
            });

            // Export functionality
            $('#exportBtn, #exportTableBtn').on('click', function () {
                const organization = $('#filterOrganization').val();
                const department = $('#filterDepartment').val();

                let url = "{{ route('admin.balance-tracker.export') }}";
                const params = new URLSearchParams();

                if (organization) params.append('organization', organization);
                if (department) params.append('department', department);

                if (params.toString()) {
                    url += '?' + params.toString();
                }

                window.location.href = url;
            });
        });

        function initializeBalanceTable() {
            const tbody = $('#balanceTableBody');
            tbody.empty();

            balanceData.forEach(employee => {
                const row = `
                <tr>
                    <td class="dt-control"></td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="user-avatar me-3">${getInitials(employee.employeeName)}</div>
                            <div>
                                <div class="fw-semibold">${employee.employeeName}</div>
                                <small class="text-muted">${employee.employeeId}</small>
                                <div class="small text-muted mt-1">Joined: ${formatDate(employee.joinDate)}</div>
                            </div>
                        </div>
                    </td>
                    <td>${employee.organization}</td>
                    <td>${employee.department}</td>
                    <td>
                        <div class="small mb-1">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Earned: <strong>${employee.annual.earned}</strong></span>
                                <span>Used: <strong>${employee.annual.used}</strong></span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Remaining: <strong class="text-success">${employee.annual.remaining}</strong></span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-main" role="progressbar" style="width: ${employee.annual.earned > 0 ? (employee.annual.remaining/employee.annual.earned)*100 : 0}%"></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="small mb-1">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Earned: <strong>${employee.sick.earned}</strong></span>
                                <span>Used: <strong>${employee.sick.used}</strong></span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Remaining: <strong class="text-success">${employee.sick.remaining}</strong></span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-danger" role="progressbar" style="width: ${employee.sick.earned > 0 ? (employee.sick.remaining/employee.sick.earned)*100 : 0}%"></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="small mb-1">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Earned: <strong>${employee.casual.earned}</strong></span>
                                <span>Used: <strong>${employee.casual.used}</strong></span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Remaining: <strong class="text-success">${employee.casual.remaining}</strong></span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-info" role="progressbar" style="width: ${employee.casual.earned > 0 ? (employee.casual.remaining/employee.casual.earned)*100 : 0}%"></div>
                            </div>
                        </div>
                    </td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-primary adjust-balance-btn" 
                                data-employee-id="${employee.id}" data-bs-toggle="tooltip" title="Adjust Balance">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                    </td>
                </tr>
                `;

                tbody.append(row);
            });

            balanceTable = initUserDataTable('#balanceTable', {
                pageLength: 25,
                lengthMenu: [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100]
                ],
                order: [
                    [1, 'asc']
                ],
                scrollX: true,
                responsive: {
                    details: {
                        type: 'column',
                        target: 0
                    }
                },
                columnDefs: [
                    {
                        targets: 0,
                        orderable: false,
                        className: 'dt-control',
                        responsivePriority: 0
                    },
                    {
                        targets: 7, // Actions column (control + name + org + dept + 3 leaves + actions)
                        orderable: false,
                        className: 'no-toggle',
                        responsivePriority: 1
                    }
                ],
                language: {
                    search: "",
                    searchPlaceholder: "Search by employee name or ID...",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ employees",
                    infoEmpty: "No employees available",
                    zeroRecords: "No matching employees found"
                },
                buttons: [{
                    extend: 'colvis',
                    text: 'Select Columns',
                    className: 'btn btn-sm border-0 bg-main text-white'
                }],
                drawCallback: function () {
                    $('[data-bs-toggle="tooltip"]').tooltip();
                }
            });
        }


        function getInitials(name) {
            return name.split(' ').map(n => n[0]).join('').toUpperCase();
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
        }
    </script>
@endpush