@extends('layouts.app')

@section('title', 'Leave Requests - Admin Panel')

@section('page-title', 'Leave Management')

@push('styles')
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

    vi .card .badge {
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
            @if(validatePermissions('admin/leave-request/add'))
            <button type="button" class="btn btn-primary bg-main border-0" data-bs-toggle="offcanvas"
                data-bs-target="#addLeaveRequestCanvas">
                <i class="bi bi-plus-circle me-1"></i>Apply Leave for Employee
            </button>
            @endif
        </div>
    </div>

    <!-- Counters -->
    @include('admin.leave-requests.counters')
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
<!-- Common Helper Functions -->
<script src="{{ asset('js/helpers.js') }}"></script>

<script>
    // Global variables
    let leaveRequestsTable;
    let selectedRequests = new Set();

    // Dynamic leave requests data from backend
    const sampleLeaveRequests = @json($mappedLeaveRequests);

    // Away today data (currently empty or handled differently if needed)
    const awayToday = [];

    // Personal Quota Summary from backend
    const quotaWarnings = @json($personalQuota);

    $(document).ready(function() {
        $(document).on('click', '.action-leave-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const requestId = $(this).data('request-id');
            const action = $(this).data('action');

            if (!requestId || action === undefined) {
                window.showError('Request data is missing. Please refresh and try again.');
                return;
            }

            handleLeaveAction(requestId, action);
        });
        // Initialize DataTables on the server-rendered table
        leaveRequestsTable = initUserDataTable('#leaveRequestsTable', {
            pageLength: 25,
            lengthMenu: [
                [10, 25, 50, 100],
                [10, 25, 50, 100]
            ],
            order: [
                [4, 'desc']
            ],
            scrollX: false,
            responsive: {
                details: {
                    type: 'column',
                    target: 0
                }
            },
            columnDefs: [{
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
            e.preventDefault();
            e.stopPropagation();

            const requestId = $(this).data('request-id');
            const request = sampleLeaveRequests.find(r => Number(r.id) === Number(requestId));

            if (request) {
                showLeaveDetails(request);
            }
        });

        $(document).on('click', '#approveDetailBtn', function(e) {
            e.preventDefault();

            const requestId = $(this).data('request-id');
            const action = $(this).data('action');

            if (!requestId || action === undefined) {
                window.showError('Request data is missing. Please refresh and try again.');
                return;
            }

            handleLeaveAction(requestId, action);
        });

        $(document).on('click', '#rejectDetailBtn', function(e) {
            e.preventDefault();

            const requestId = $(this).data('request-id');
            const action = $(this).data('action');

            if (!requestId || action === undefined) {
                window.showError('Request data is missing. Please refresh and try again.');
                return;
            }

            handleLeaveAction(requestId, action);
        });

        // Prevent row expansion when clicking on actions column
        $(document).on('click', 'td:last-child, .dropdown-toggle, .dropdown-menu', function(e) {
            // Removed stopPropagation as it may interfere with button clicks
        });

        // Initialize tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();
    });



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

        if (quotaWarnings.length === 0) {
            container.append('<div class="text-muted small p-3 text-center">No quota data available</div>');
            return;
        }

        quotaWarnings.forEach(item => {
            const badgeClass = item.percentage >= 90 ? 'bg-danger text-white' : 'bg-warning text-dark';
            const html = `
                    <div class="alert ${badgeClass} mb-2 p-2" role="alert" style="border:none; border-radius: 8px;">
                        <div class="small fw-semibold">${item.type}</div>
                        <div class="small">Balance: ${item.remaining} / ${item.total} (Used: ${item.used})</div>
                    </div>
                `;
            container.append(html);
        });
    }

    function getStatusBadge(code) {
        const badges = {
            0: '<span class="badge bg-warning text-dark px-2 py-1 rounded-1">Pending</span>',
            1: '<span class="badge bg-info px-2 py-1 rounded-1">Recommended</span>',
            2: '<span class="badge bg-danger px-2 py-1 rounded-1">Not Recommended</span>',
            3: '<span class="badge bg-success px-2 py-1 rounded-1">Approved</span>',
            4: '<span class="badge bg-danger px-2 py-1 rounded-1">Rejected</span>',
            5: '<span class="badge bg-secondary px-2 py-1 rounded-1">Cancelled</span>'
        };
        return badges[code] || '<span class="badge bg-warning text-dark px-2 py-1 rounded-1">Pending</span>';
    }

    function getLeaveTypeBadge(type, label) {
        const badges = {
            'annual': 'bg-primary',
            'sick': 'bg-danger',
            'casual': 'bg-info text-dark',
            'comp-off': 'bg-warning text-dark'
        };
        const bgClass = badges[type] || 'bg-secondary';
        return `<span class="badge ${bgClass} px-2 py-1 rounded-1">${label || 'Other'}</span>`;
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

    function formatLeaveDays(request) {
        const days = request.days;
        if (request.isHalfDay && request.halfDaySession) {
            const session = request.halfDaySession.charAt(0).toUpperCase() + request.halfDaySession.slice(1);
            return `${days} (${session})`;
        }
        return days;
    }

    function renderOutstationDetail(request) {
        const section = $('#detailOutstationSection');
        const exemptRow = $('#detailOutstationExempt');
        const billableRow = $('#detailBillableDaysRow');

        if (!request.isOutstationLeave) {
            section.addClass('d-none');
            return;
        }

        section.removeClass('d-none');
        $('#detailOutstationDestination').text(
            request.outstationDestinationLabel || 'Outstation leave'
        );

        const exemptDays = parseFloat(request.exemptDays || 0);
        if (exemptDays > 0) {
            exemptRow.removeClass('d-none');
            $('#detailOutstationExemptText').text(
                `${exemptDays} travel day${exemptDays === 1 ? '' : 's'} exempt from balance deduction (destination outside Rawalpindi).`
            );
            billableRow.removeClass('d-none');
            $('#detailBillableDays').text(request.billableDays ?? Math.max(0, parseFloat(request.days || 0) - exemptDays));
        } else {
            exemptRow.addClass('d-none');
            billableRow.addClass('d-none');
        }
    }

    function getActionMeta(actionCode) {
        const action = Number(actionCode);
        const actionMap = {
            1: {
                label: 'Recommend',
                successMessage: 'Leave request recommended successfully.',
                requiresConfirmation: false
            },
            2: {
                label: 'Not Recommend',
                successMessage: 'Leave request marked as not recommended.',
                requiresConfirmation: false
            },
            3: {
                label: 'Approve',
                successMessage: 'Leave request approved successfully.',
                requiresConfirmation: true
            },
            4: {
                label: 'Reject',
                successMessage: 'Leave request rejected successfully.',
                requiresConfirmation: true
            },
            5: {
                label: 'Cancel',
                successMessage: 'Leave request cancelled successfully.',
                requiresConfirmation: true
            }
        };

        return actionMap[action] || null;
    }

    function extractApiErrorMessage(err, fallbackMessage) {
        if (err?.responseJSON?.message) {
            return err.responseJSON.message;
        }

        const validationErrors = err?.responseJSON?.errors;
        if (validationErrors && typeof validationErrors === 'object') {
            const firstKey = Object.keys(validationErrors)[0];
            if (firstKey && Array.isArray(validationErrors[firstKey]) && validationErrors[firstKey][0]) {
                return validationErrors[firstKey][0];
            }
        }

        if (err?.responseText) {
            return err.responseText;
        }

        return fallbackMessage;
    }

    function handleLeaveAction(requestId, actionCode) {
        const actionMeta = getActionMeta(actionCode);

        if (!requestId || !actionMeta) {
            window.showError('Invalid leave action request. Please refresh and try again.');
            return;
        }

        const performStatusUpdate = function() {
            $.ajax({
                url: `/admin/leave-request/${requestId}/status`,
                type: 'PATCH',
                data: {
                    status: Number(actionCode),
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (!response || response.success !== true) {
                        window.showError(response?.message || 'Unable to update leave status right now.');
                        return;
                    }

                    window.showSuccess(response.message || actionMeta.successMessage, 'Success')
                        .then(function() {
                            location.reload();
                        });
                },
                error: function(err) {
                    window.showError(
                        extractApiErrorMessage(err, `Failed to ${actionMeta.label.toLowerCase()} leave request.`)
                    );
                }
            });
        };

        if (actionMeta.requiresConfirmation) {
            Swal.fire({
                icon: 'warning',
                title: `${actionMeta.label} Leave Request?`,
                text: `Are you sure you want to ${actionMeta.label.toLowerCase()} this leave request?`,
                showCancelButton: true,
                confirmButtonText: `Yes, ${actionMeta.label}`,
                cancelButtonText: 'No',
                confirmButtonColor: '#1a237e',
                cancelButtonColor: '#6c757d'
            }).then(function(result) {
                if (result.isConfirmed) {
                    performStatusUpdate();
                }
            });
            return;
        }

        performStatusUpdate();
    }

    function showLeaveDetails(request) {
        $('#detailEmployeeName').text(request.employeeName);
        $('#detailEmployeeId').text(request.employeeId);
        $('#detailDepartment').text(request.department);
        $('#detailLeaveType').html(getLeaveTypeBadge(request.leaveType, request.leaveTypeLabel));
        $('#detailStartDate').text(formatDate(request.startDate));
        $('#detailEndDate').text(formatDate(request.endDate));
        $('#detailDays').text(formatLeaveDays(request));
        renderOutstationDetail(request);
        $('#detailReason').text(request.reason);
        $('#detailBalance').text(request.balance);
        $('#detailStatus').html(getStatusBadge(request.statusCode));
        $('#detailApprovalLevel').text(request.approvalLevel);

        // Dynamic Timeline Logic
        const timelineContainer = $('#approvalTimeline');
        timelineContainer.empty();

        // Step 1: Request Submitted
        timelineContainer.append(`
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-check-circle-fill text-success me-3 fs-5"></i>
                    <div>
                        <div class="fw-semibold small">Request Submitted</div>
                        <small class="opacity-75">By ${request.employeeName}</small>
                    </div>
                </div>
            `);

        // Step 2: Recommendations (Children)
        let step2Icon = 'bi-circle text-white-50';
        let step2Text = 'Waiting for Recommendation';
        if (request.statusCode == 1) {
            step2Icon = 'bi-check-circle-fill text-success';
            step2Text = 'Recommended';
        } else if (request.statusCode == 2) {
            step2Icon = 'bi-x-circle-fill text-danger';
            step2Text = 'Not Recommended';
        } else if (request.statusCode >= 3) {
            step2Icon = 'bi-check-circle-fill text-success';
            step2Text = 'Recommendation Step Passed';
        }

        timelineContainer.append(`
                <div class="d-flex align-items-center mb-3">
                    <i class="bi ${step2Icon} me-3 fs-5"></i>
                    <div>
                        <div class="fw-semibold small">Child Roles: ${step2Text}</div>
                        <small class="opacity-75">Team/Department Members</small>
                    </div>
                </div>
            `);

        // Step 3: Final Approval (Parent)
        let step3Icon = 'bi-circle text-white-50';
        let step3Text = 'Awaiting Final Decision';
        if (request.statusCode == 3) {
            step3Icon = 'bi-check-circle-fill text-success';
            step3Text = 'Approved';
        } else if (request.statusCode == 4) {
            step3Icon = 'bi-x-circle-fill text-danger';
            step3Text = 'Rejected';
        } else if (request.statusCode == 5) {
            step3Icon = 'bi-dash-circle-fill text-secondary';
            step3Text = 'Cancelled';
        }

        timelineContainer.append(`
                <div class="d-flex align-items-center">
                    <i class="bi ${step3Icon} me-3 fs-5"></i>
                    <div>
                        <div class="fw-semibold small">Parent Role: ${step3Text}</div>
                        <small class="opacity-75">Reporting Manager / Super Admin</small>
                    </div>
                </div>
            `);

        // Setup Buttons
        const approveBtn = $('#approveDetailBtn');
        const rejectBtn = $('#rejectDetailBtn');

        approveBtn.data('request-id', request.id);
        rejectBtn.data('request-id', request.id);

        if (request.canRecommend || request.canNotRecommend) {
            approveBtn.show().data('action', 1).html('<i class="bi bi-hand-thumbs-up me-1"></i>Recommend');
            rejectBtn.show().data('action', 2).html('<i class="bi bi-hand-thumbs-down me-1"></i>Not Recommend');
        } else if (request.canApprove || request.canReject) {
            approveBtn.show().data('action', 3).html('<i class="bi bi-check-circle me-1"></i>Approve');
            rejectBtn.show().data('action', 4).html('<i class="bi bi-x-circle me-1"></i>Reject');
        } else {
            approveBtn.hide();
            rejectBtn.hide();
        }

        const canvas = new bootstrap.Offcanvas(document.getElementById('leaveDetailCanvas'));
        canvas.show();
    }
</script>
@endpush
