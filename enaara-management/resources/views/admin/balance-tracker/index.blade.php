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
            padding: 1.3rem 2rem !important;
            color: var(--light-color) !important;
            white-space: nowrap !important;
        }

        td {
            padding: 1rem 2rem !important;
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
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-4 border-bottom">
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
                                                    <option value="Enaara Construction">Enaara Construction</option>
                                                    <option value="Enaara Properties">Enaara Properties</option>
                                                    <option value="Enaara Real Estate">Enaara Real Estate</option>
                                                </select>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="mb-3">
                                                <label class="form-label small text-muted mb-1">Department</label>
                                                <select class="form-select form-select-sm" id="filterDepartment">
                                                    <option value="">All Departments</option>
                                                    <option value="Sales">Sales</option>
                                                    <option value="HR">HR</option>
                                                    <option value="IT">IT</option>
                                                    <option value="Operations">Operations</option>
                                                    <option value="Finance">Finance</option>
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

        // Sample balance data
        const sampleBalances = [
            {
                id: 1,
                employeeName: 'John Doe',
                employeeId: 'EMP-001',
                joinDate: '2020-01-15',
                organization: 'Enaara Construction',
                department: 'Sales',
                annual: { earned: 30, used: 5, remaining: 25 },
                sick: { earned: 15, used: 2, remaining: 13 },
                casual: { earned: 10, used: 2, remaining: 8 }
            },
            {
                id: 2,
                employeeName: 'Sarah Miller',
                employeeId: 'EMP-002',
                joinDate: '2019-03-20',
                organization: 'Enaara Construction',
                department: 'HR',
                annual: { earned: 30, used: 8, remaining: 22 },
                sick: { earned: 15, used: 3, remaining: 12 },
                casual: { earned: 10, used: 1, remaining: 9 }
            },
            {
                id: 3,
                employeeName: 'Robert Kim',
                employeeId: 'EMP-003',
                joinDate: '2021-06-10',
                organization: 'Enaara Properties',
                department: 'IT',
                annual: { earned: 30, used: 12, remaining: 18 },
                sick: { earned: 15, used: 1, remaining: 14 },
                casual: { earned: 10, used: 4, remaining: 6 }
            },
            {
                id: 4,
                employeeName: 'Emma Wilson',
                employeeId: 'EMP-004',
                joinDate: '2020-11-05',
                organization: 'Enaara Real Estate',
                department: 'Operations',
                annual: { earned: 30, used: 15, remaining: 15 },
                sick: { earned: 15, used: 5, remaining: 10 },
                casual: { earned: 10, used: 3, remaining: 7 }
            },
            {
                id: 5,
                employeeName: 'Michael Johnson',
                employeeId: 'EMP-005',
                joinDate: '2018-09-12',
                organization: 'Enaara Construction',
                department: 'Sales',
                annual: { earned: 30, used: 3, remaining: 27 },
                sick: { earned: 15, used: 0, remaining: 15 },
                casual: { earned: 10, used: 1, remaining: 9 }
            },
            {
                id: 6,
                employeeName: 'Lisa Anderson',
                employeeId: 'EMP-006',
                joinDate: '2022-02-28',
                organization: 'Enaara Properties',
                department: 'Finance',
                annual: { earned: 30, used: 20, remaining: 10 },
                sick: { earned: 15, used: 8, remaining: 7 },
                casual: { earned: 10, used: 5, remaining: 5 }
            }
        ];

        $(document).ready(function() {
            initializeBalanceTable();

            // Filter functionality
            $('#applyFiltersBtn').on('click', function() {
                const organization = $('#filterOrganization').val();
                const department = $('#filterDepartment').val();
                
                balanceTable.column(1).search(organization);
                balanceTable.column(2).search(department);
                
                balanceTable.draw();
                $('.dropdown-toggle').dropdown('hide');
            });

            $('#clearFiltersBtn').on('click', function() {
                $('#filterOrganization').val('');
                $('#filterDepartment').val('');
                
                balanceTable.columns().search('');
                balanceTable.draw();
            });

            // Manual adjustment button
            $(document).on('click', '.adjust-balance-btn', function(e) {
                e.stopPropagation();
                const employeeId = $(this).data('employee-id');
                const employee = sampleBalances.find(emp => emp.id === employeeId);
                if (employee) {
                    showAdjustmentCanvas(employee);
                }
            });

            // Export functionality
            $('#exportBtn, #exportTableBtn').on('click', function() {
                // TODO: Implement export to Excel
                alert('Export functionality will be implemented');
            });
        });

        function initializeBalanceTable() {
            const tbody = $('#balanceTableBody');
            tbody.empty();

            sampleBalances.forEach(employee => {
                const annualPercent = (employee.annual.used / employee.annual.earned) * 100;
                const sickPercent = (employee.sick.used / employee.sick.earned) * 100;
                const casualPercent = (employee.casual.used / employee.casual.earned) * 100;

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
                                    <div class="progress-bar bg-main" role="progressbar" style="width: ${annualPercent}%"></div>
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
                                    <div class="progress-bar bg-danger" role="progressbar" style="width: ${sickPercent}%"></div>
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
                                    <div class="progress-bar bg-info" role="progressbar" style="width: ${casualPercent}%"></div>
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
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                order: [[1, 'asc']],
                scrollX: false,
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
                        targets: [1, 2, 3, 4, 5, 6, 7],
                        visible: true
                    },
                    {
                        targets: 7,
                        orderable: false,
                        className: 'no-toggle',
                        responsivePriority: 1
                    },
                    {
                        targets: 1,
                        responsivePriority: 2
                    },
                    {
                        targets: [4, 5, 6],
                        responsivePriority: 4
                    },
                    {
                        targets: [2, 3],
                        responsivePriority: 5
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
                    className: 'btn btn-sm border-0 bg-main text-white',
                    columns: [1, 2, 3, 4, 5, 6]
                }],
                drawCallback: function() {
                    $('[data-bs-toggle="tooltip"]').tooltip();
                }
            });
        }

        function showAdjustmentCanvas(employee) {
            // Populate adjustment canvas
            $('#adjustEmployeeName').text(employee.employeeName);
            $('#adjustEmployeeId').text(employee.employeeId);
            $('#adjustEmployeeIdHidden').val(employee.id);
            
            // Set current balances
            $('#currentAnnualBalance').text(employee.annual.remaining);
            $('#currentSickBalance').text(employee.sick.remaining);
            $('#currentCasualBalance').text(employee.casual.remaining);

            // Reset form
            $('#adjustmentForm')[0].reset();
            $('#adjustmentType').val('add').trigger('change');

            // Show canvas
            const canvas = new bootstrap.Offcanvas(document.getElementById('adjustmentCanvas'));
            canvas.show();
        }

        function getInitials(name) {
            return name.split(' ').map(n => n[0]).join('').toUpperCase();
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        }
    </script>
@endpush

