@extends('layouts.app')

@section('title', 'Leave Requests - Admin Panel')

@section('page-title', 'Leave Management')

@push('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <!-- Leave Requests Module CSS -->
    <link href="{{ asset('css/leave-requests.css') }}" rel="stylesheet">

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
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h5 class="mb-0">Global Leave Dashboard</h5>
                <small class="text-muted">Monitor and manage employee leave requests</small>
            </div>
            <div class="col-md-6 text-end">
                <button type="button" class="btn btn-outline-secondary me-2" id="exportBtn">
                    <i class="bi bi-download me-1"></i>Export
                </button>
                <button type="button" class="btn btn-outline-secondary me-2" data-bs-toggle="offcanvas"
                    data-bs-target="#leavePolicyCanvas">
                    <i class="bi bi-gear me-1"></i>Leave Policy
                </button>
                <button type="button" class="btn btn-primary bg-main border-0" data-bs-toggle="offcanvas"
                    data-bs-target="#addLeaveRequestCanvas">
                    <i class="bi bi-plus-circle me-1"></i>New Leave Request
                </button>
            </div>
        </div>

        <!-- Counters -->
        @include('admin.leave-requests.counters')

        <!-- Who's Away Today -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card border-0 rounded-4">
                    <div class="card-header bg-transparent border-0 p-4">
                        <h6 class="mb-0 fw-semibold">Who's Away Today</h6>
                    </div>
                    <div class="card-body">
                        <div id="awayTodayList">
                            <!-- Sample data will be populated by JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 rounded-4">
                    <div class="card-header bg-transparent border-0 p-4">
                        <h6 class="mb-0 fw-semibold">Departmental Quota Warnings</h6>
                    </div>
                    <div class="card-body">
                        <div id="quotaWarnings">
                            <!-- Sample warnings will be populated by JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Requests -->
        <div class="row">
            <div class="col-md-12">
                <div class="card border-0 rounded-4 mb-4">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-4 border-bottom">
                            <h6 class="mb-0 fw-semibold">Pending Leave Requests</h6>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="exportRequestsBtn">
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
                                                <label class="form-label small text-muted mb-1">Leave Type</label>
                                                <select class="form-select form-select-sm" id="filterLeaveType">
                                                    <option value="">All Types</option>
                                                    <option value="annual">Annual Leave</option>
                                                    <option value="sick">Sick Leave</option>
                                                    <option value="casual">Casual Leave</option>
                                                    <option value="comp-off">Compensatory Off</option>
                                                </select>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="mb-3">
                                                <label class="form-label small text-muted mb-1">Status</label>
                                                <select class="form-select form-select-sm" id="filterStatus">
                                                    <option value="">All Status</option>
                                                    <option value="pending">Pending</option>
                                                    <option value="approved">Approved</option>
                                                    <option value="rejected">Rejected</option>
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
                        @include('admin.leave-requests.leave_requests_table')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leave Policy Canvas -->
    @include('admin.leave-requests.leave_policy_canvas')

    <!-- Add Leave Request Canvas -->
    @include('admin.leave-requests.add_leave_request_canvas')

    <!-- Leave Detail Canvas -->
    @include('admin.leave-requests.leave_detail_canvas')
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
    <!-- Common Helper Functions -->
    <script src="{{ asset('js/helpers.js') }}"></script>

    <script>
        // Global variables
        let leaveRequestsTable;
        let selectedRequests = new Set();

        // Sample leave requests data
        const sampleLeaveRequests = [
            {
                id: 1,
                employeeName: 'Ahmed Ali',
                employeeId: 'EMP-001',
                department: 'Sales',
                leaveType: 'annual',
                leaveTypeLabel: 'Annual Leave',
                startDate: '2024-02-01',
                endDate: '2024-02-05',
                days: 5,
                reason: 'Family vacation',
                status: 'pending',
                approvalLevel: 'supervisor',
                pendingSince: '2 days ago',
                balance: 25
            },
            {
                id: 2,
                employeeName: 'Zainab Malik',
                employeeId: 'EMP-002',
                department: 'HR',
                leaveType: 'sick',
                leaveTypeLabel: 'Sick Leave',
                startDate: '2024-01-25',
                endDate: '2024-01-26',
                days: 2,
                reason: 'Medical appointment',
                status: 'pending',
                approvalLevel: 'hr',
                pendingSince: '1 day ago',
                balance: 13
            },
            {
                id: 3,
                employeeName: 'Bilal Ahmed',
                employeeId: 'EMP-003',
                department: 'IT',
                leaveType: 'casual',
                leaveTypeLabel: 'Casual Leave',
                startDate: '2024-01-30',
                endDate: '2024-01-30',
                days: 1,
                reason: 'Personal work',
                status: 'approved',
                approvalLevel: 'super-admin',
                pendingSince: '-',
                balance: 8
            },
            {
                id: 4,
                employeeName: 'Hira Ali',
                employeeId: 'EMP-004',
                department: 'Operations',
                leaveType: 'comp-off',
                leaveTypeLabel: 'Compensatory Off',
                startDate: '2024-02-10',
                endDate: '2024-02-10',
                days: 1,
                reason: 'Worked on weekend',
                status: 'pending',
                approvalLevel: 'supervisor',
                pendingSince: '3 days ago',
                balance: 3
            },
            {
                id: 5,
                employeeName: 'Hamza Khan',
                employeeId: 'EMP-005',
                department: 'Sales',
                leaveType: 'annual',
                leaveTypeLabel: 'Annual Leave',
                startDate: '2024-02-15',
                endDate: '2024-02-20',
                days: 6,
                reason: 'Holiday trip',
                status: 'rejected',
                approvalLevel: 'hr',
                pendingSince: '-',
                balance: 10
            }
        ];

        // Sample away today data
        const awayToday = [
            { name: 'Ahmed Ali', department: 'Sales', leaveType: 'Annual Leave', days: 3 },
            { name: 'Zainab Malik', department: 'HR', leaveType: 'Sick Leave', days: 1 },
            { name: 'Faisal Raza', department: 'IT', leaveType: 'Casual Leave', days: 1 }
        ];

        // Sample quota warnings
        const quotaWarnings = [
            { department: 'Maintenance Team', date: 'Next Monday', percentage: 30, status: 'warning' },
            { department: 'Sales Team', date: 'Feb 15', percentage: 45, status: 'critical' }
        ];

        $(document).ready(function() {
            initializeLeaveRequestsDataTable();
            populateAwayToday();
            populateQuotaWarnings();

            // Filter functionality
            $('#applyFiltersBtn').on('click', function() {
                const leaveType = $('#filterLeaveType').val();
                const status = $('#filterStatus').val();
                const department = $('#filterDepartment').val();
                
                leaveRequestsTable.column(3).search(leaveType);
                leaveRequestsTable.column(8).search(status);
                leaveRequestsTable.column(2).search(department);
                
                leaveRequestsTable.draw();
                $('.dropdown-toggle').dropdown('hide');
            });

            $('#clearFiltersBtn').on('click', function() {
                $('#filterLeaveType').val('');
                $('#filterStatus').val('');
                $('#filterDepartment').val('');
                
                leaveRequestsTable.columns().search('');
                leaveRequestsTable.draw();
            });

            // View leave details
            $(document).on('click', '.view-leave-btn', function(e) {
                e.stopPropagation();
                const requestId = $(this).data('request-id');
                const request = sampleLeaveRequests.find(r => r.id === requestId);
                if (request) {
                    showLeaveDetails(request);
                }
            });

            // Approve/Reject actions
            $(document).on('click', '.approve-leave-btn', function(e) {
                e.stopPropagation();
                const requestId = $(this).data('request-id');
                handleLeaveAction(requestId, 'approved');
            });

            $(document).on('click', '.reject-leave-btn', function(e) {
                e.stopPropagation();
                const requestId = $(this).data('request-id');
                handleLeaveAction(requestId, 'rejected');
            });

            // Prevent row expansion when clicking on actions column
            $(document).on('click', 'td:last-child, .dropdown-toggle, .dropdown-menu', function(e) {
                e.stopPropagation();
            });

            // Initialize tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();
        });

        function initializeLeaveRequestsDataTable() {
            const tbody = $('#leaveRequestsTableBody');
            tbody.empty();

            sampleLeaveRequests.forEach(request => {
                const statusBadge = getStatusBadge(request.status);
                const leaveTypeBadge = getLeaveTypeBadge(request.leaveType);

                const row = `
                    <tr>
                        <td class="dt-control"></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="user-avatar me-3">${getInitials(request.employeeName)}</div>
                                <div>
                                    <div class="fw-semibold">${request.employeeName}</div>
                                    <small class="text-muted">${request.employeeId} • ${request.department}</small>
                                </div>
                            </div>
                        </td>
                        <td>${request.department}</td>
                        <td>${leaveTypeBadge}</td>
                        <td>
                            <div class="fw-semibold small">${formatDate(request.startDate)}</div>
                            <small class="text-muted">to ${formatDate(request.endDate)}</small>
                        </td>
                        <td>
                            <div class="fw-semibold">${request.days} day${request.days > 1 ? 's' : ''}</div>
                        </td>
                        <td>
                            <div class="small">${request.reason}</div>
                        </td>
                        <td>
                            <div class="small">
                                <div>Balance: <strong>${request.balance}</strong></div>
                                <div class="text-muted">${request.approvalLevel}</div>
                            </div>
                        </td>
                        <td>${statusBadge}</td>
                        <td>
                            <div class="small text-muted">${request.pendingSince}</div>
                        </td>
                        <td class="text-end">
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item view-leave-btn" href="#" data-request-id="${request.id}">
                                            <i class="bi bi-eye text-secondary me-2"></i>View Details
                                        </a>
                                    </li>
                                    ${request.status === 'pending' ? `
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item approve-leave-btn" href="#" data-request-id="${request.id}">
                                            <i class="bi bi-check-circle text-success me-2"></i>Approve
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item reject-leave-btn" href="#" data-request-id="${request.id}">
                                            <i class="bi bi-x-circle text-danger me-2"></i>Reject
                                        </a>
                                    </li>
                                    ` : ''}
                                </ul>
                            </div>
                        </td>
                    </tr>
                `;
                tbody.append(row);
            });

            leaveRequestsTable = initUserDataTable('#leaveRequestsTable', {
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                order: [[4, 'desc']],
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
                        targets: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                        visible: true
                    },
                    {
                        targets: 10,
                        orderable: false,
                        className: 'no-toggle',
                        responsivePriority: 1
                    },
                    {
                        targets: 1,
                        responsivePriority: 2
                    },
                    {
                        targets: [5, 6, 7],
                        responsivePriority: 4
                    },
                    {
                        targets: [2, 3, 8, 9],
                        responsivePriority: 5
                    }
                ],
                language: {
                    search: "",
                    searchPlaceholder: "Search leave requests...",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ requests",
                    infoEmpty: "No requests available",
                    zeroRecords: "No matching requests found"
                },
                buttons: [{
                    extend: 'colvis',
                    text: 'Select Columns',
                    className: 'btn btn-sm border-0 bg-main text-white',
                    columns: [1, 2, 3, 4, 5, 6, 7, 8, 9]
                }],
                drawCallback: function() {
                    $('[data-bs-toggle="tooltip"]').tooltip();
                }
            });
        }

        function populateAwayToday() {
            const container = $('#awayTodayList');
            container.empty();

            awayToday.forEach(employee => {
                const item = `
                    <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                        <div class="d-flex align-items-center">
                            <div class="user-avatar me-3">${getInitials(employee.name)}</div>
                            <div>
                                <div class="fw-semibold">${employee.name}</div>
                                <small class="text-muted">${employee.department}</small>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="small fw-semibold">${employee.leaveType}</div>
                            <small class="text-muted">${employee.days} day${employee.days > 1 ? 's' : ''}</small>
                        </div>
                    </div>
                `;
                container.append(item);
            });
        }

        function populateQuotaWarnings() {
            const container = $('#quotaWarnings');
            container.empty();

            quotaWarnings.forEach(warning => {
                const badgeClass = warning.status === 'critical' ? 'bg-danger' : 'bg-warning text-dark';
                const item = `
                    <div class="alert ${badgeClass} mb-2" role="alert">
                        <div class="small fw-semibold">${warning.department}</div>
                        <div class="small">${warning.percentage}% on leave - ${warning.date}</div>
                    </div>
                `;
                container.append(item);
            });
        }

        function getStatusBadge(status) {
            const badges = {
                'pending': '<span class="badge bg-warning text-dark px-2 py-1 rounded-1">Pending</span>',
                'approved': '<span class="badge bg-success px-2 py-1 rounded-1">Approved</span>',
                'rejected': '<span class="badge bg-danger px-2 py-1 rounded-1">Rejected</span>'
            };
            return badges[status] || badges['pending'];
        }

        function getLeaveTypeBadge(type) {
            const badges = {
                'annual': '<span class="badge bg-primary px-2 py-1 rounded-1">Annual Leave</span>',
                'sick': '<span class="badge bg-danger px-2 py-1 rounded-1">Sick Leave</span>',
                'casual': '<span class="badge bg-info text-dark px-2 py-1 rounded-1">Casual Leave</span>',
                'comp-off': '<span class="badge bg-warning text-dark px-2 py-1 rounded-1">Comp-Off</span>'
            };
            return badges[type] || '<span class="badge bg-secondary px-2 py-1 rounded-1">Other</span>';
        }

        function getInitials(name) {
            return name.split(' ').map(n => n[0]).join('').toUpperCase();
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        }

        function handleLeaveAction(requestId, action) {
            const row = $(`tr:has(.approve-leave-btn[data-request-id="${requestId}"])`);
            if (row.length) {
                const statusCell = row.find('td').eq(8);
                const badge = action === 'approved' 
                    ? '<span class="badge bg-success px-2 py-1 rounded-1">Approved</span>'
                    : '<span class="badge bg-danger px-2 py-1 rounded-1">Rejected</span>';
                statusCell.html(badge);
                
                // Update the request in sample data
                const request = sampleLeaveRequests.find(r => r.id === requestId);
                if (request) {
                    request.status = action;
                }
            }
        }

        function showLeaveDetails(request) {
            $('#detailEmployeeName').text(request.employeeName);
            $('#detailEmployeeId').text(request.employeeId);
            $('#detailDepartment').text(request.department);
            $('#detailLeaveType').html(getLeaveTypeBadge(request.leaveType));
            $('#detailStartDate').text(formatDate(request.startDate));
            $('#detailEndDate').text(formatDate(request.endDate));
            $('#detailDays').text(request.days);
            $('#detailReason').text(request.reason);
            $('#detailBalance').text(request.balance);
            $('#detailStatus').html(getStatusBadge(request.status));
            $('#detailApprovalLevel').text(request.approvalLevel);
            
            const canvas = new bootstrap.Offcanvas(document.getElementById('leaveDetailCanvas'));
            canvas.show();
        }
    </script>
@endpush

