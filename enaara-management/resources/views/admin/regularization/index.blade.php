@extends('layouts.app')

@section('title', 'Regularization - Admin Panel')

@section('page-title', 'Regularization')

@push('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <!-- Regularization Module CSS -->
    <link href="{{ asset('css/regularization.css') }}" rel="stylesheet">

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

        .dt-buttons {
            margin-top: 2px;
        }

        /* Tooltip font size */
        .tooltip {
            font-size: 12px !important;
        }

        .tooltip-inner {
            font-size: 12px !important;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h5 class="mb-0">Regularization Inbox</h5>
                <small class="text-muted">Review and approve attendance correction requests</small>
            </div>
            <div class="col-md-6 text-end">
                <button type="button" class="btn btn-outline-secondary me-2" id="exportBtn">
                    <i class="bi bi-download me-1"></i>Export
                </button>
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown"
                        aria-expanded="false" id="filterDropdownBtn">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end p-3" style="min-width: 350px;">
                        <!-- Category Filter -->
                        <li>
                            <div class="mb-3">
                                <label class="form-label small text-muted mb-1">Category</label>
                                <select class="form-select form-select-sm" id="filterCategory">
                                    <option value="">All Categories</option>
                                    <option value="missed-punch">Missed Punch</option>
                                    <option value="on-duty">On-Duty (Outside)</option>
                                    <option value="technical-error">Technical Error</option>
                                    <option value="late-regularization">Late Regularization</option>
                                </select>
                            </div>
                        </li>
                        <!-- Status Filter -->
                        <li>
                            <div class="mb-3">
                                <label class="form-label small text-muted mb-1">Status</label>
                                <select class="form-select form-select-sm" id="filterStatus">
                                    <option value="">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="clarification">Clarification Requested</option>
                                </select>
                            </div>
                        </li>
                        <!-- Date Range Filter -->
                        <li>
                            <div class="mb-3">
                                <label class="form-label small text-muted mb-1">Date From</label>
                                <input type="date" class="form-control form-control-sm" id="filterDateFrom">
                            </div>
                        </li>
                        <li>
                            <div class="mb-3">
                                <label class="form-label small text-muted mb-1">Date To</label>
                                <input type="date" class="form-control form-control-sm" id="filterDateTo">
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

        <!-- Counters -->
        @include('admin.regularization.counters')

        <!-- Bulk Actions Bar -->
        <div class="card border-0 rounded-4 mb-4" id="bulkActionsBar" style="display: none;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-semibold" id="selectedCount">0</span> request(s) selected
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-success" id="bulkApproveBtn">
                            <i class="bi bi-check-circle me-1"></i>Bulk Approve
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" id="bulkRejectBtn">
                            <i class="bi bi-x-circle me-1"></i>Bulk Reject
                        </button>
                        <button type="button" class="btn btn-sm btn-warning" id="bulkClarificationBtn">
                            <i class="bi bi-question-circle me-1"></i>Request Clarification
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="clearSelectionBtn">
                            <i class="bi bi-x me-1"></i>Clear Selection
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card border-0 rounded-4 mb-4">
            <div class="card-body p-0">
                @include('admin.regularization.regularization_table')
            </div>
        </div>
    </div>

    <!-- Audit Trail Canvas -->
    @include('admin.regularization.audit_trail_canvas')
@endsection

@push('scripts')
    <!-- jQuery (required for DataTables) -->
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
        let regularizationTable;
        let selectedRequests = new Set();

        $(document).ready(function() {
            // Initialize DataTable
            regularizationTable = initUserDataTable('#regularizationTable', {
                pageLength: 25,
                lengthMenu: [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100]
                ],
                order: [
                    [3, 'desc']
                ], // Sort by date descending
                scrollX: false,

                responsive: {
                    details: {
                        type: 'column',
                        target: 0 // 👈 MUST be the dt-control column, not 'tr'
                    }
                },

                columnDefs: [{
                        targets: 0, // Expand / collapse control
                        className: 'dt-control',
                        orderable: false,
                        responsivePriority: 0
                    },
                    {
                        targets: 1, // Checkbox column
                        orderable: false,
                        responsivePriority: 1,
                        className: 'no-toggle'
                    },
                    {
                        targets: 9, // Actions column
                        orderable: false,
                        responsivePriority: 1,
                        className: 'no-toggle'
                    },
                    {
                        targets: 2, // Employee
                        responsivePriority: 2
                    },
                    {
                        targets: [3, 8], // Date, Status
                        responsivePriority: 3
                    },
                    {
                        targets: [4, 5, 6, 7], // Conflict, Reason, Category, Evidence
                        responsivePriority: 4
                    }
                ],

                language: {
                    search: "",
                    searchPlaceholder: "Search requests...",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ requests",
                    infoEmpty: "No requests available",
                    zeroRecords: "No matching requests found"
                },

                buttons: [{
                    extend: 'colvis',
                    text: 'Select Columns',
                    className: 'btn btn-sm border-0 bg-main text-white',
                    columns: [2, 3, 4, 5, 6, 7, 8] // Excludes control, checkbox, actions
                }],

                drawCallback: function() {
                    $('[data-bs-toggle="tooltip"]').tooltip();
                    updateBulkActions();
                }
            });


            // Filter functionality
            $('#applyFiltersBtn').on('click', function() {
                const category = $('#filterCategory').val();
                const status = $('#filterStatus').val();
                const dateFrom = $('#filterDateFrom').val();
                const dateTo = $('#filterDateTo').val();

                // Apply filters to DataTable
                regularizationTable.column(6).search(category); // Category column
                regularizationTable.column(8).search(status); // Status column

                // Date filtering would need custom filtering logic
                regularizationTable.draw();

                // Close dropdown
                $('.dropdown-toggle').dropdown('hide');
            });

            $('#clearFiltersBtn').on('click', function() {
                $('#filterCategory').val('');
                $('#filterStatus').val('');
                $('#filterDateFrom').val('');
                $('#filterDateTo').val('');

                // Clear filters from DataTable
                regularizationTable.columns().search('');
                regularizationTable.draw();
            });

            // Select all checkbox
            $('#selectAllCheckbox').on('change', function() {
                const isChecked = this.checked;
                $('.request-checkbox:not(:disabled)').each(function() {
                    this.checked = isChecked;
                    if (isChecked) {
                        selectedRequests.add(this.value);
                    } else {
                        selectedRequests.delete(this.value);
                    }
                });
                updateBulkActions();
            });

            // Individual checkbox change
            $(document).on('change', '.request-checkbox', function() {
                const requestId = this.value;
                if (this.checked) {
                    selectedRequests.add(requestId);
                } else {
                    selectedRequests.delete(requestId);
                }
                updateSelectAllState();
                updateBulkActions();
            });

            // Individual approval actions
            $(document).on('click', '.approve-btn', function() {
                const requestId = $(this).data('request-id');
                handleApproval(requestId, 'approved');
            });

            $(document).on('click', '.reject-btn', function() {
                const requestId = $(this).data('request-id');
                handleApproval(requestId, 'rejected');
            });

            $(document).on('click', '.clarification-btn', function() {
                const requestId = $(this).data('request-id');
                handleApproval(requestId, 'clarification');
            });

            // Bulk actions
            $('#bulkApproveBtn').on('click', function() {
                handleBulkAction(Array.from(selectedRequests), 'approved');
            });

            $('#bulkRejectBtn').on('click', function() {
                handleBulkAction(Array.from(selectedRequests), 'rejected');
            });

            $('#bulkClarificationBtn').on('click', function() {
                handleBulkAction(Array.from(selectedRequests), 'clarification');
            });

            // Clear selection
            $('#clearSelectionBtn').on('click', function() {
                $('.request-checkbox').prop('checked', false);
                selectedRequests.clear();
                $('#selectAllCheckbox').prop('checked', false);
                updateBulkActions();
            });

            // View audit trail
            $(document).on('click', '.view-audit-btn', function() {
                const requestId = $(this).data('request-id');
                const employeeName = $(this).data('employee-name');
                const date = $(this).data('date');
                showAuditTrail(requestId, employeeName, date);
            });

            // Initialize tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();
        });

        function updateSelectAllState() {
            const totalCheckboxes = $('.request-checkbox:not(:disabled)').length;
            const checkedCheckboxes = $('.request-checkbox:not(:disabled):checked').length;
            $('#selectAllCheckbox').prop('checked', totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes);
        }

        function updateBulkActions() {
            const count = selectedRequests.size;
            if (count > 0) {
                $('#bulkActionsBar').show();
                $('#selectedCount').text(count);
            } else {
                $('#bulkActionsBar').hide();
            }
        }

        function handleApproval(requestId, action) {
            const row = $(`tr[data-request-id="${requestId}"]`);
            if (row.length) {
                // Update status badge
                const statusCell = row.find('td').eq(8);
                let badgeClass = 'bg-warning';
                let badgeText = 'Pending';

                if (action === 'approved') {
                    badgeClass = 'bg-success';
                    badgeText = 'Approved';
                } else if (action === 'rejected') {
                    badgeClass = 'bg-danger';
                    badgeText = 'Rejected';
                } else {
                    badgeClass = 'bg-info';
                    badgeText = 'Clarification';
                }

                statusCell.html(`<span class="badge ${badgeClass}">${badgeText}</span>`);

                // Disable action buttons and checkbox
                const actionCell = row.find('td').eq(8);
                actionCell.find('.approve-btn, .reject-btn, .clarification-btn').prop('disabled', true).addClass(
                    'opacity-50');
                row.find('.request-checkbox').prop('disabled', true);

                // Remove from selection
                selectedRequests.delete(requestId);
                updateBulkActions();
                updateSelectAllState();

                // Redraw table to reflect changes
                regularizationTable.draw(false);
            }
        }

        function handleBulkAction(requestIds, action) {
            requestIds.forEach(function(requestId) {
                handleApproval(requestId, action);
            });

            // Clear selection
            $('.request-checkbox').prop('checked', false);
            $('#selectAllCheckbox').prop('checked', false);
            selectedRequests.clear();
            updateBulkActions();
        }

        function showAuditTrail(requestId, employeeName, date) {
            $('#auditEmployeeName').text(employeeName || '-');
            $('#auditDate').text(date || '-');
            $('#auditRequestId').text('REQ-' + requestId);

            const auditCanvas = new bootstrap.Offcanvas(document.getElementById('auditTrailCanvas'));
            auditCanvas.show();
        }
    </script>
@endpush
