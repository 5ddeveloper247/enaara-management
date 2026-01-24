@extends('layouts.app')

@section('title', 'Employee Management - Admin Panel')

@section('page-title', 'Employee Management')

@push('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <!-- Employee Module CSS -->
    <link href="{{ asset('css/employee.css') }}" rel="stylesheet">

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

        .nav-pills .nav-link {
            border-radius: 0.375rem;
            color: var(--dark-color);
            font-size: 13px;
            padding: 0.5rem 1rem;
        }

        .nav-pills .nav-link.active {
            background-color: var(--main-color) !important;
            color: white !important;
        }

        /* .stat-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
        } */
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Header Stats with Nav Pills -->
        <div class="card border-0 rounded-4 mb-4">
            <div class="card-body p-4">
                <ul class="nav nav-pills mb-4" id="employeeStatsTabs" role="tablist">
                    <li class="nav-item me-2" role="presentation">
                        <button class="nav-link active" id="total-workforce-tab" data-bs-toggle="pill" 
                                data-bs-target="#total-workforce" type="button" role="tab">
                            <i class="bi bi-people-fill me-2"></i>Total Workforce
                            <span class="badge bg-light text-dark ms-2" id="totalWorkforceBadge">0</span>
                        </button>
                    </li>
                    <li class="nav-item me-2" role="presentation">
                        <button class="nav-link" id="internal-staff-tab" data-bs-toggle="pill" 
                                data-bs-target="#internal-staff" type="button" role="tab">
                            <i class="bi bi-person-badge me-2"></i>Internal Staff
                            <span class="badge bg-light text-dark ms-2" id="internalStaffBadge">0</span>
                        </button>
                    </li>
                    <li class="nav-item me-2" role="presentation">
                        <button class="nav-link" id="outsourced-staff-tab" data-bs-toggle="pill" 
                                data-bs-target="#outsourced-staff" type="button" role="tab">
                            <i class="bi bi-building me-2"></i>Outsourced Staff
                            <span class="badge bg-light text-dark ms-2" id="outsourcedStaffBadge">0</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="biometric-sync-tab" data-bs-toggle="pill" 
                                data-bs-target="#biometric-sync" type="button" role="tab">
                            <i class="bi bi-fingerprint me-2"></i>Biometric Sync Status
                            <span class="badge bg-light text-dark ms-2" id="biometricSyncBadge">0</span>
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="employeeStatsTabsContent">
                    <!-- Total Workforce Tab -->
                    <div class="tab-pane fade show active" id="total-workforce" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="card stat-card bg-main border-0 rounded-3 shadow h-100">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <h6 class="opacity-75 text-white mb-1 small fw-normal text-uppercase">
                                                    Total Employees
                                                </h6>
                                                <div class="h4 mb-0 fw-bold text-white" id="statTotalEmployees">0</div>
                                            </div>
                                            <div class="text-white opacity-25">
                                                <i class="bi bi-people fs-1"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card stat-card border-0 rounded-3 shadow h-100">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <h6 class="opacity-75 text-black mb-1 small fw-normal text-uppercase">
                                                    Active
                                                </h6>
                                                <div class="h4 mb-0 fw-bold text-black" id="statActive">0</div>
                                            </div>
                                            <div class="text-black opacity-25">
                                                <i class="bi bi-check-circle fs-1"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="card stat-card border-0 rounded-3 shadow h-100">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <h6 class="opacity-75 text-black mb-1 small fw-normal text-uppercase">
                                                    Biometric Linked
                                                </h6>
                                                <div class="h4 mb-0 fw-bold text-black" id="statBiometricLinked">0</div>
                                            </div>
                                            <div class="text-black opacity-25">
                                                <i class="bi bi-fingerprint fs-1"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card stat-card border-0 rounded-3 shadow h-100">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <h6 class="opacity-75 text-black mb-1 small fw-normal text-uppercase">
                                                    Pending Sync
                                                </h6>
                                                <div class="h4 mb-0 fw-bold text-black" id="statPendingSync">0</div>
                                            </div>
                                            <div class="text-black opacity-25">
                                                <i class="bi bi-clock-history fs-1"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Internal Staff Tab -->
                    <div class="tab-pane fade" id="internal-staff" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="card stat-card bg-primary border-0 rounded-3 shadow h-100">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <h6 class="opacity-75 text-white mb-1 small fw-normal text-uppercase">
                                                    Internal Employees
                                                </h6>
                                                <div class="h4 mb-0 fw-bold text-white" id="statInternal">0</div>
                                            </div>
                                            <div class="text-white opacity-25">
                                                <i class="bi bi-person-badge fs-1"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card stat-card border-0 rounded-3 shadow h-100">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <h6 class="opacity-75 text-black mb-1 small fw-normal text-uppercase">
                                                    Permanent
                                                </h6>
                                                <div class="h4 mb-0 fw-bold text-black" id="statPermanent">0</div>
                                            </div>
                                            <div class="text-black opacity-25">
                                                <i class="bi bi-shield-check fs-1"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card stat-card border-0 rounded-3 shadow h-100">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <h6 class="opacity-75 text-black mb-1 small fw-normal text-uppercase">
                                                    Contract
                                                </h6>
                                                <div class="h4 mb-0 fw-bold text-black" id="statContract">0</div>
                                            </div>
                                            <div class="text-black opacity-25">
                                                <i class="bi bi-file-earmark-text fs-1"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Outsourced Staff Tab -->
                    <div class="tab-pane fade" id="outsourced-staff" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="card stat-card border-0 rounded-3 shadow h-100">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <h6 class="opacity-75 text-black mb-1 small fw-normal text-uppercase">
                                                    Third-Party Workers
                                                </h6>
                                                <div class="h4 mb-0 fw-bold text-black" id="statOutsourced">0</div>
                                            </div>
                                            <div class="text-black opacity-25">
                                                <i class="bi bi-building fs-1"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card stat-card border-0 rounded-3 shadow h-100">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <h6 class="opacity-75 text-black mb-1 small fw-normal text-uppercase">
                                                    Active Vendors
                                                </h6>
                                                <div class="h4 mb-0 fw-bold text-black" id="statVendors">0</div>
                                            </div>
                                            <div class="text-black opacity-25">
                                                <i class="bi bi-shop fs-1"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Biometric Sync Status Tab -->
                    <div class="tab-pane fade" id="biometric-sync" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="card stat-card border-0 rounded-3 shadow h-100">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <h6 class="opacity-75 text-black mb-1 small fw-normal text-uppercase">
                                                    Synced
                                                </h6>
                                                <div class="h4 mb-0 fw-bold text-black" id="statSynced">0</div>
                                            </div>
                                            <div class="text-black opacity-25">
                                                <i class="bi bi-check-circle fs-1"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card stat-card border-0 rounded-3 shadow h-100">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <h6 class="opacity-75 text-black mb-1 small fw-normal text-uppercase">
                                                    Pending
                                                </h6>
                                                <div class="h4 mb-0 fw-bold text-black" id="statPending">0</div>
                                            </div>
                                            <div class="text-black opacity-25">
                                                <i class="bi bi-clock-history fs-1"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card stat-card border-0 rounded-3 shadow h-100">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <h6 class="opacity-75 text-black mb-1 small fw-normal text-uppercase">
                                                    Failed
                                                </h6>
                                                <div class="h4 mb-0 fw-bold text-black" id="statFailed">0</div>
                                            </div>
                                            <div class="text-black opacity-25">
                                                <i class="bi bi-x-circle fs-1"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Card -->
        <div class="card border-0 rounded-4 mb-4">
            <div class="card-body p-0">
                <div class="row align-items-center p-4">
                    <div class="col-md-6">
                        <h5 class="mb-0">Employee Directory</h5>
                    </div>
                    <div class="col-md-6 text-end">
                        <button type="button" class="btn btn-outline-secondary me-2" id="exportBtn">
                            <i class="bi bi-download me-1"></i>Export
                        </button>
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-secondary dropdown-toggle"
                                data-bs-toggle="dropdown" aria-expanded="false" id="filterDropdownBtn">
                                <i class="bi bi-funnel me-1"></i>Filter
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end p-3" style="min-width: 320px;">
                                <!-- Employee Type Filter -->
                                <li>
                                    <div class="mb-3">
                                        <label class="form-label small text-muted mb-1">Employee Type</label>
                                        <select class="form-select form-select-sm" id="filterEmployeeType">
                                            <option value="">All Types</option>
                                            <option value="Internal">Internal</option>
                                            <option value="Third-party">Third-party</option>
                                        </select>
                                    </div>
                                </li>
                                <!-- Department Filter -->
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
                                            <option value="Legal">Legal</option>
                                        </select>
                                    </div>
                                </li>
                                <!-- Vendor Filter -->
                                <li>
                                    <div class="mb-3">
                                        <label class="form-label small text-muted mb-1">Vendor</label>
                                        <select class="form-select form-select-sm" id="filterVendor">
                                            <option value="">All Vendors</option>
                                            <option value="TechStaff Solutions">TechStaff Solutions</option>
                                            <option value="Global Workforce Inc">Global Workforce Inc</option>
                                            <option value="StaffPro Services">StaffPro Services</option>
                                            <option value="Manpower Group">Manpower Group</option>
                                            <option value="Adecco">Adecco</option>
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

                <!-- Data Table -->
                @include('admin.employee.employee_table')
            </div>
        </div>
    </div>

    <!-- Employee Detail Canvas -->
    @include('admin.employee.detail_canvas')
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
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <!-- Common Helper Functions -->
    <script src="{{ asset('js/helpers.js') }}"></script>

    <script>
        // Global variables
        let employeeTable;

        $(document).ready(function() {
            // Initialize DataTable with Responsive
            employeeTable = initUserDataTable('#employeeTable', {
                pageLength: 50,
                lengthMenu: [[25, 50, 100, 200], [25, 50, 100, 200]],
                order: [[0, 'asc']],
                scrollX: false,
                responsive: {
                    details: {
                        type: 'column',
                        target: 'tr'
                    }
                },
                columnDefs: [
                    {
                        targets: [0, 1, 2, 3, 4, 5, 6], // All columns visible by default
                        visible: true
                    },
                    {
                        targets: 6, // Actions column
                        orderable: false,
                        className: 'no-toggle',
                        responsivePriority: 1
                    },
                    {
                        targets: 0, // Profile column
                        responsivePriority: 2
                    },
                    {
                        targets: [2, 3, 4], // Employment Type, Site, Vendor - lower priority
                        responsivePriority: 4
                    },
                    {
                        targets: [1, 5], // Biometric ID, Status - can hide on small screens
                        responsivePriority: 5
                    }
                ],
                language: {
                    search: "",
                    searchPlaceholder: "Search employees...",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ employees",
                    infoEmpty: "No employees available",
                    zeroRecords: "No matching employees found"
                },
                buttons: [{
                    extend: 'colvis',
                    text: 'Select Columns',
                    className: 'btn btn-sm border-0 bg-main text-black',
                    columns: [0, 1, 2, 3, 4, 5]
                }],
                // drawCallback: function() {
                //     updateEmployeeStats();
                // }
            });


           

            // Export functionality
            $('#exportBtn').on('click', function() {
                const data = employeeTable.rows({search: 'applied'}).data();
                let csvContent = "Employee ID,Name,Biometric ID,Employment Type,Site Assignment,Vendor,Status\n";
                
                data.each(function(row) {
                    const profile = $(row[0]).text().trim().replace(/,/g, ';');
                    const biometricId = $(row[1]).text().trim().replace(/,/g, ';');
                    const employmentType = $(row[2]).text().trim().replace(/,/g, ';');
                    const site = $(row[3]).text().trim().replace(/,/g, ';');
                    const vendor = $(row[4]).text().trim().replace(/,/g, ';');
                    const status = $(row[5]).text().trim().replace(/,/g, ';');
                    csvContent += `"${profile}","${biometricId}","${employmentType}","${site}","${vendor}","${status}"\n`;
                });

                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                const url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', `employees-${new Date().toISOString().split('T')[0]}.csv`);
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });

            // Employee Detail Canvas Handler
            const employeeDetailCanvas = document.getElementById('employeeDetailCanvas');
            if (employeeDetailCanvas) {
                employeeDetailCanvas.addEventListener('show.bs.offcanvas', function(event) {
                    const button = event.relatedTarget;
                    if (!button || !button.classList.contains('view-employee-btn')) return;

                    // Extract data from button data attributes
                    const employeeData = {
                        employeeId: button.getAttribute('data-employee-id') || '-',
                        employeeName: button.getAttribute('data-employee-name') || '-',
                        employeeAvatar: button.getAttribute('data-employee-avatar') || '?',
                        employeeInfo: button.getAttribute('data-employee-info') || '-',
                        department: button.getAttribute('data-department') || '-',
                        employmentType: button.getAttribute('data-employment-type') || '-',
                        employeeType: button.getAttribute('data-employee-type') || '-',
                        biometricId: button.getAttribute('data-biometric-id') || '-',
                        syncStatus: button.getAttribute('data-sync-status') || '-',
                        siteAssignment: button.getAttribute('data-site-assignment') || '-',
                        vendor: button.getAttribute('data-vendor') || '-'
                    };

                    // Populate employee profile information
                    $('#detailEmployeeAvatar').text(employeeData.employeeAvatar);
                    $('#detailEmployeeName').text(employeeData.employeeName);
                    $('#detailEmployeeInfo').text(employeeData.employeeInfo);

                    // Populate employee details
                    $('#detailEmployeeId').text(employeeData.employeeId);
                    $('#detailDepartment').text(employeeData.department);
                    
                    // Employment Type Badge
                    let employmentTypeBadge = '';
                    if (employeeData.employmentType === 'Permanent') {
                        employmentTypeBadge = '<span class="badge bg-success">Permanent</span>';
                    } else if (employeeData.employmentType === 'Contract') {
                        employmentTypeBadge = '<span class="badge bg-info">Contract</span>';
                    } else if (employeeData.employmentType === 'Third-party') {
                        employmentTypeBadge = '<span class="badge" style="background-color: #9c27b0; color: white;">Third-party</span>';
                    } else {
                        employmentTypeBadge = '<span class="badge bg-secondary">' + employeeData.employmentType + '</span>';
                    }
                    $('#detailEmploymentType').html(employmentTypeBadge);

                    // Employee Type Badge
                    let employeeTypeBadge = '';
                    if (employeeData.employeeType === 'Internal') {
                        employeeTypeBadge = '<span class="badge bg-primary">Internal</span>';
                    } else {
                        employeeTypeBadge = '<span class="badge bg-warning text-dark">Third-party</span>';
                    }
                    $('#detailEmployeeType').html(employeeTypeBadge);

                    // Populate biometric information
                    if (employeeData.biometricId !== '-') {
                        $('#detailBiometricId').text(employeeData.biometricId);
                    } else {
                        $('#detailBiometricId').html('<span class="text-muted">Not Linked</span>');
                    }

                    // Sync Status Badge
                    let syncStatusBadge = '';
                    let syncStatusText = '';
                    if (employeeData.syncStatus === 'Synced') {
                        syncStatusBadge = '<span class="badge px-3 py-2 rounded-1 bg-success"><i class="bi bi-check-circle me-1"></i>Synced</span>';
                        syncStatusText = 'Successfully synced with biometric system';
                    } else if (employeeData.syncStatus === 'Pending') {
                        syncStatusBadge = '<span class="badge px-3 py-2 rounded-1 bg-warning text-dark"><i class="bi bi-clock-history me-1"></i>Pending</span>';
                        syncStatusText = 'Pending synchronization with biometric system';
                    } else if (employeeData.syncStatus === 'Failed') {
                        syncStatusBadge = '<span class="badge px-3 py-2 rounded-1 bg-danger"><i class="bi bi-x-circle me-1"></i>Failed</span>';
                        syncStatusText = 'Failed to sync with biometric system';
                    } else {
                        syncStatusBadge = '<span class="badge px-3 py-2 rounded-1 bg-secondary"><i class="bi bi-dash-circle me-1"></i>Not Linked</span>';
                        syncStatusText = 'No biometric device linked';
                    }
                    $('#detailBiometricStatus').html(syncStatusBadge);
                    $('#detailSyncStatusText').text(syncStatusText);

                    // Populate assignment information
                    $('#detailSiteAssignment').text(employeeData.siteAssignment);
                    
                    // Show/hide vendor section based on employee type
                    if (employeeData.employeeType === 'Third-party' && employeeData.vendor !== '-') {
                        $('#detailVendorContainer').show();
                        $('#detailVendor').text(employeeData.vendor);
                    } else {
                        $('#detailVendorContainer').hide();
                    }

                    // Populate status information
                    // Assuming all employees in the table are active
                    $('#detailCurrentStatus').html('<span class="badge px-3 py-2 rounded-1 bg-success"><i class="bi bi-check-circle me-1"></i>Active</span>');
                    
                    // Status info items
                    $('#detailStatusInfo1').text('Employee is active and working');
                    if (employeeData.syncStatus === 'Synced' && employeeData.biometricId !== '-') {
                        $('#detailStatusInfo2').html('<i class="bi bi-check-circle-fill text-success me-2"></i>Biometric device linked');
                    } else {
                        $('#detailStatusInfo2').html('<i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Biometric device not linked');
                    }

                    // Store employee ID for edit button (if needed)
                    $('#editEmployeeBtn').attr('data-employee-id', employeeData.employeeId);
                });
            }

            // Handle edit employee button click
            $('#editEmployeeBtn').on('click', function() {
                const employeeId = $(this).attr('data-employee-id');
                console.log('Edit employee:', employeeId);
                // Close canvas and navigate to edit page or open edit modal
                // You can implement edit functionality here
            });
        });
    </script>
@endpush

