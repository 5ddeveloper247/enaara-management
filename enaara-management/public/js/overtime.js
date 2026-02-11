/**
 * Overtime Tracker Module
 * Monitor and manage employee overtime requests
 */

(function () {
    'use strict';

    // ============================================
    // GLOBAL VARIABLES
    // ============================================
    let overtimeTable;
    let overtimeData = [];

    // ============================================
    // INITIALIZATION
    // ============================================
    $(document).ready(function () {
        loadOvertimeData();
        initializeDataTable();
        initializeEventHandlers();
        updateCounters();
    });

    // ============================================
    // DATA LOADING
    // ============================================
    function loadOvertimeData() {
        if (typeof ProjectData !== 'undefined' && ProjectData.overtime) {
            overtimeData = ProjectData.overtime.generateSampleData(50);
        } else {
            console.warn('ProjectData not found, using empty array');
            overtimeData = [];
        }
    }

    // ============================================
    // DATA TABLE INITIALIZATION
    // ============================================
    function initializeDataTable() {
        const tbody = $('#overtimeTableBody');
        tbody.empty();

        overtimeData.forEach(ot => {
            const row = buildTableRow(ot);
            tbody.append(row);
        });

        overtimeTable = initUserDataTable('#overtimeTable', {
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            order: [[4, 'desc']], // Sort by OT Hours descending
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
                    targets: 8,
                    orderable: false,
                    className: 'no-toggle',
                    responsivePriority: 1
                },
                {
                    targets: 1,
                    responsivePriority: 2
                },
                {
                    targets: [2, 3, 4],
                    responsivePriority: 3
                },
                {
                    targets: [5, 6, 7],
                    responsivePriority: 4
                }
            ],
            language: {
                search: "",
                searchPlaceholder: "Search overtime records...",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ records",
                infoEmpty: "No records available",
                zeroRecords: "No matching records found"
            },
            buttons: [{
                extend: 'colvis',
                text: 'Select Columns',
                className: 'btn btn-sm border-0 bg-main text-white',
                columns: [1, 2, 3, 4, 5, 6, 7]
            }],
            drawCallback: function() {
                $('[data-bs-toggle="tooltip"]').tooltip();
            }
        });
    }

    // ============================================
    // TABLE ROW BUILDER
    // ============================================
    function buildTableRow(ot) {
        // Floor/Dept with Floor 9 highlighting
        const floorBadge = ot.floor === '9' 
            ? `<span class="badge bg-info px-2 rounded-1"><i class="bi bi-building me-1"></i>Floor ${ot.floor} (Corporate/HR)</span>`
            : `<span class="badge bg-secondary px-2 rounded-1">Floor ${ot.floor}</span>`;
        
        const floorDept = `
            <div>
                ${floorBadge}
                <div class="mt-1 small text-muted">${ot.department}</div>
            </div>
        `;

        // Shift vs Actual
        const shiftVsActual = `
            <div>
                <div class="small">
                    <span class="text-muted">Shift End:</span> 
                    <strong>${ot.shiftEnd}</strong>
                </div>
                <div class="small">
                    <span class="text-muted">Actual:</span> 
                    <strong class="text-primary">${ot.actualPunchOut}</strong>
                </div>
            </div>
        `;

        // OT Category Badge
        let categoryBadge = '';
        if (ot.otCategory === 'In-Office OT') {
            categoryBadge = '<span class="badge px-2 rounded-1 bg-primary">In-Office OT</span>';
        } else if (ot.otCategory === 'Field-Work OT') {
            categoryBadge = '<span class="badge px-2 rounded-1 bg-success">Field-Work OT</span>';
        } else if (ot.otCategory === 'Weekend OT') {
            categoryBadge = '<span class="badge px-2 rounded-1 bg-warning text-dark">Weekend OT</span>';
        }

        // Verification Status with Zone-2 indicator
        let verificationBadge = '';
        let zone2Icon = '';
        
        if (ot.floor === '9' && ot.zone2Verified !== null) {
            zone2Icon = ot.zone2Verified 
                ? '<i class="bi bi-shield-check-fill zone2-icon" title="Zone-2 Biometric Verified"></i>'
                : '<i class="bi bi-shield-exclamation zone2-icon text-warning" title="Zone-2 Verification Pending"></i>';
        }

        if (ot.verificationStatus === 'Biometric Verified') {
            verificationBadge = `<span class="badge px-2 rounded-1 bg-info position-relative">${zone2Icon}<i class="bi bi-check-circle me-1"></i>Biometric Verified</span>`;
        } else if (ot.verificationStatus === 'Geofence Verified') {
            verificationBadge = '<span class="badge px-2 rounded-1 bg-success"><i class="bi bi-geo-alt-fill me-1"></i>Geofence Verified</span>';
        } else if (ot.verificationStatus === 'Location Mismatch') {
            verificationBadge = '<span class="badge px-2 rounded-1 bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>Location Mismatch</span>';
        } else if (ot.verificationStatus === 'Pending Verification') {
            verificationBadge = '<span class="badge px-2 rounded-1 bg-warning text-dark"><i class="bi bi-clock-history me-1"></i>Pending Verification</span>';
        } else {
            verificationBadge = '<span class="badge px-2 rounded-1 bg-secondary">Not Required</span>';
        }

        // Status Badge
        let statusBadge = '';
        if (ot.status === 'pending') {
            statusBadge = '<span class="badge px-2 rounded-1 bg-warning text-dark">Pending</span>';
        } else if (ot.status === 'approved') {
            statusBadge = '<span class="badge px-2 rounded-1 bg-success">Approved</span>';
        } else if (ot.status === 'rejected') {
            statusBadge = '<span class="badge px-2 rounded-1 bg-danger">Rejected</span>';
        }

        // Row class for location mismatch
        const rowClass = ot.geofenceStatus === 'out-of-zone' ? 'location-mismatch' : '';
        const tooltipAttr = ot.geofenceStatus === 'out-of-zone' 
            ? 'data-bs-toggle="tooltip" data-bs-placement="left" title="Location Mismatch: Employee was out of geofence zone during OT"'
            : '';

        // Actions buttons
        const approveBtn = ot.status === 'pending' 
            ? `<button type="button" class="btn btn-sm btn-success approve-ot-btn" data-ot-id="${ot.id}" title="Approve">
                <i class="bi bi-check-circle"></i>
            </button>`
            : '';
        
        const rejectBtn = ot.status === 'pending'
            ? `<button type="button" class="btn btn-sm btn-danger reject-ot-btn" data-ot-id="${ot.id}" title="Reject">
                <i class="bi bi-x-circle"></i>
            </button>`
            : '';

        const viewDetailBtn = `
            <button type="button" 
                    class="btn btn-sm btn-primary view-ot-detail-btn" 
                    data-bs-toggle="offcanvas"
                    data-bs-target="#overtimeDetailCanvas"
                    data-ot-id="${ot.id}"
                    data-employee-id="${ot.employeeId}"
                    data-employee-name="${ot.employeeName}"
                    data-employee-avatar="${ot.employeeAvatar}"
                    data-employee-dept="${ot.department}"
                    data-branch="${ot.branch}"
                    data-floor="${ot.floor}"
                    data-date="${ot.date}"
                    data-shift-end="${ot.shiftEnd}"
                    data-actual-punch-out="${ot.actualPunchOut}"
                    data-ot-hours="${ot.otHours}"
                    data-ot-category="${ot.otCategory}"
                    data-status="${ot.status}"
                    data-verification-status="${ot.verificationStatus}"
                    data-zone2-verified="${ot.zone2Verified !== null ? String(ot.zone2Verified) : ''}"
                    data-geofence-status="${ot.geofenceStatus || ''}"
                    data-has-evidence="${String(ot.hasEvidence)}"
                    title="View Details">
                <i class="bi bi-eye"></i>
            </button>
        `;

        const actions = `
            <div class="btn-group">
                ${viewDetailBtn}
                ${approveBtn}
                ${rejectBtn}
            </div>
        `;

        return `
            <tr class="${rowClass}" ${tooltipAttr} data-date="${ot.date}" data-organization="${ot.organization.toLowerCase()}" data-branch="${ot.branch.toLowerCase()}" data-floor="${ot.floor.toLowerCase()}">
                <td class="dt-control"></td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="user-avatar me-3">${ot.employeeAvatar}</div>
                        <div>
                            <div class="fw-semibold">${ot.employeeName}</div>
                            <small class="text-muted">${ot.employeeId}</small>
                        </div>
                    </div>
                </td>
                <td>${floorDept}</td>
                <td>${shiftVsActual}</td>
                <td>
                    <div class="fw-semibold text-primary">${ot.otHours} hrs</div>
                </td>
                <td>${categoryBadge}</td>
                <td class="zone2-verified">${verificationBadge}</td>
                <td class="ot-status-cell" data-status="${ot.status}">${statusBadge}</td>
                <td class="text-end">${actions}</td>
            </tr>
        `;
    }

    // ============================================
    // EVENT HANDLERS
    // ============================================
    function initializeEventHandlers() {
        // Apply filters
        $('#applyFiltersBtn').on('click', applyFilters);

        // Clear filters
        $('#clearFiltersBtn').on('click', clearFilters);

        // Export button
        $('#exportBtn').on('click', handleExport);

        // Approve OT
        $(document).on('click', '.approve-ot-btn', function(e) {
            e.stopPropagation();
            const otId = parseInt($(this).data('ot-id'));
            handleApproveOT(otId);
        });

        // Reject OT
        $(document).on('click', '.reject-ot-btn', function(e) {
            e.stopPropagation();
            const otId = parseInt($(this).data('ot-id'));
            handleRejectOT(otId);
        });

        // View Evidence
        $(document).on('click', '.view-evidence-btn', function(e) {
            e.stopPropagation();
            const otId = parseInt($(this).data('ot-id'));
            handleViewEvidence(otId);
        });

        // Overtime Detail Canvas
        const detailCanvas = document.getElementById('overtimeDetailCanvas');
        if (detailCanvas) {
            detailCanvas.addEventListener('show.bs.offcanvas', handleDetailCanvasShow);
        }

        // Approve/Reject from canvas
        $('#approveOTBtn').on('click', function() {
            const otId = parseInt($(this).data('ot-id'));
            if (otId) {
                handleApproveOT(otId);
                const canvas = bootstrap.Offcanvas.getInstance(document.getElementById('overtimeDetailCanvas'));
                if (canvas) canvas.hide();
            }
        });

        $('#rejectOTBtn').on('click', function() {
            const otId = parseInt($(this).data('ot-id'));
            if (otId) {
                handleRejectOT(otId);
                const canvas = bootstrap.Offcanvas.getInstance(document.getElementById('overtimeDetailCanvas'));
                if (canvas) canvas.hide();
            }
        });
    }

    // ============================================
    // FILTER FUNCTIONS
    // ============================================
    let customFilterFunction = null;

    function applyFilters() {
        const dateFrom = $('#filterDateFrom').val();
        const dateTo = $('#filterDateTo').val();
        const organization = $('#filterOrganization').val();
        const branch = $('#filterBranch').val();
        const floor = $('#filterFloor').val();

        // Remove existing custom filter if any
        if (customFilterFunction) {
            $.fn.dataTable.ext.search.pop();
        }

        // Create custom filtering function
        customFilterFunction = function(settings, data, dataIndex) {
            // Only apply to overtime table
            if (settings.nTable.id !== 'overtimeTable') {
                return true;
            }

            const row = overtimeTable.row(dataIndex).node();
            
            // Date filter
            if (dateFrom || dateTo) {
                const rowDate = $(row).data('date');
                if (dateFrom && rowDate < dateFrom) return false;
                if (dateTo && rowDate > dateTo) return false;
            }
            
            // Organization filter
            if (organization) {
                const rowOrg = $(row).data('organization');
                if (rowOrg !== organization) return false;
            }
            
            // Branch filter
            if (branch) {
                const rowBranch = $(row).data('branch');
                if (rowBranch !== branch) return false;
            }
            
            // Floor filter
            if (floor) {
                const rowFloor = $(row).data('floor');
                if (rowFloor !== floor) return false;
            }
            
            return true;
        };

        // Add custom filter
        $.fn.dataTable.ext.search.push(customFilterFunction);

        // Apply filters
        if (overtimeTable) {
            overtimeTable.draw();
        }

        // Update counters
        updateCounters();
    }

    function clearFilters() {
        $('#filterDateFrom').val('');
        $('#filterDateTo').val('');
        $('#filterOrganization').val('');
        $('#filterBranch').val('');
        $('#filterFloor').val('');

        // Remove custom filter
        if (customFilterFunction) {
            $.fn.dataTable.ext.search.pop();
            customFilterFunction = null;
        }

        if (overtimeTable) {
            overtimeTable.draw();
        }

        updateCounters();
    }

    // ============================================
    // DETAIL CANVAS HANDLERS
    // ============================================
    function handleDetailCanvasShow(event) {
        const button = event.relatedTarget;
        if (!button || !button.classList.contains('view-ot-detail-btn')) return;

        const otData = {
            otId: parseInt(button.getAttribute('data-ot-id')) || 0,
            employeeId: button.getAttribute('data-employee-id') || '-',
            employeeName: button.getAttribute('data-employee-name') || '-',
            employeeAvatar: button.getAttribute('data-employee-avatar') || '?',
            employeeDept: button.getAttribute('data-employee-dept') || '-',
            branch: button.getAttribute('data-branch') || '-',
            floor: button.getAttribute('data-floor') || '-',
            date: button.getAttribute('data-date') || '-',
            shiftEnd: button.getAttribute('data-shift-end') || '-',
            actualPunchOut: button.getAttribute('data-actual-punch-out') || '-',
            otHours: parseFloat(button.getAttribute('data-ot-hours')) || 0,
            otCategory: button.getAttribute('data-ot-category') || '-',
            status: button.getAttribute('data-status') || 'pending',
            verificationStatus: button.getAttribute('data-verification-status') || '-',
            zone2Verified: button.getAttribute('data-zone2-verified') === 'true' || button.getAttribute('data-zone2-verified') === 'True',
            geofenceStatus: button.getAttribute('data-geofence-status') || null,
            hasEvidence: button.getAttribute('data-has-evidence') === 'true'
        };

        populateDetailCanvas(otData);
    }

    function populateDetailCanvas(data) {
        // Employee Information
        $('#detailEmployeeAvatar').text(data.employeeAvatar);
        $('#detailEmployeeName').text(data.employeeName);
        $('#detailEmployeeInfo').text(`${data.employeeId} | ${data.employeeDept}`);
        $('#detailEmployeeLocation').html(`<i class="bi bi-building me-1"></i>${data.branch} - Floor ${data.floor}`);

        // Overtime Information
        $('#detailOTDate').text(formatDate(data.date));
        $('#detailOTHours').text(`${data.otHours} hrs`);
        $('#detailShiftEnd').text(data.shiftEnd);
        $('#detailActualPunchOut').text(data.actualPunchOut);

        // OT Category
        let categoryBadge = '';
        if (data.otCategory === 'In-Office OT') {
            categoryBadge = '<span class="badge bg-primary px-2 py-1">In-Office OT</span>';
        } else if (data.otCategory === 'Field-Work OT') {
            categoryBadge = '<span class="badge bg-success px-2 py-1">Field-Work OT</span>';
        } else if (data.otCategory === 'Weekend OT') {
            categoryBadge = '<span class="badge bg-warning text-dark px-2 py-1">Weekend OT</span>';
        }
        $('#detailOTCategory').html(categoryBadge);

        // Verification Status
        let verificationBadge = '';
        let zone2Indicator = '';
        
        if (data.floor === '9' && data.zone2Verified !== null) {
            if (data.zone2Verified) {
                zone2Indicator = '<div class="mt-2"><small class="opacity-75 text-white"><i class="bi bi-shield-check-fill me-1 text-info"></i>Zone-2 Biometric Verified</small></div>';
                verificationBadge = '<span class="badge bg-info px-2 py-1"><i class="bi bi-check-circle me-1"></i>Biometric Verified</span>';
            } else {
                zone2Indicator = '<div class="mt-2"><small class="opacity-75 text-warning"><i class="bi bi-shield-exclamation me-1"></i>Zone-2 Verification Pending</small></div>';
                verificationBadge = '<span class="badge bg-warning text-dark px-2 py-1"><i class="bi bi-clock-history me-1"></i>Pending Verification</span>';
            }
        } else if (data.verificationStatus === 'Geofence Verified') {
            verificationBadge = '<span class="badge bg-success px-2 py-1"><i class="bi bi-geo-alt-fill me-1"></i>Geofence Verified</span>';
        } else if (data.verificationStatus === 'Location Mismatch') {
            verificationBadge = '<span class="badge bg-warning text-dark px-2 py-1"><i class="bi bi-exclamation-triangle me-1"></i>Location Mismatch</span>';
        } else {
            verificationBadge = '<span class="badge bg-secondary px-2 py-1">Not Required</span>';
        }

        $('#detailVerificationStatus').html(verificationBadge);
        $('#detailZone2Indicator').html(zone2Indicator);

        // Status
        let statusBadge = '';
        if (data.status === 'pending') {
            statusBadge = '<span class="badge bg-warning text-dark px-2 py-1">Pending</span>';
        } else if (data.status === 'approved') {
            statusBadge = '<span class="badge bg-success px-2 py-1">Approved</span>';
        } else if (data.status === 'rejected') {
            statusBadge = '<span class="badge bg-danger px-2 py-1">Rejected</span>';
        }
        $('#detailOTStatus').html(statusBadge);

        // Geofence Information
        if (data.geofenceStatus) {
            $('#geofenceSection').show();
            if (data.geofenceStatus === 'in-zone') {
                $('#detailGeofenceStatus').html('<span class="badge bg-success px-2 py-1"><i class="bi bi-geo-alt-fill me-1"></i>In Zone</span>');
                $('#geofenceWarning').hide();
            } else {
                $('#detailGeofenceStatus').html('<span class="badge bg-warning text-dark px-2 py-1"><i class="bi bi-exclamation-triangle me-1"></i>Out of Zone</span>');
                $('#geofenceWarning').show();
            }
        } else {
            $('#geofenceSection').hide();
        }

        // Action Buttons
        if (data.status === 'pending') {
            $('#approveOTBtn').show().data('ot-id', data.otId);
            $('#rejectOTBtn').show().data('ot-id', data.otId);
        } else {
            $('#approveOTBtn').hide();
            $('#rejectOTBtn').hide();
        }
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { 
            weekday: 'short', 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
    }

    // ============================================
    // ACTION HANDLERS
    // ============================================
    function handleApproveOT(otId) {
        const ot = overtimeData.find(o => o.id === otId);
        if (!ot) return;

        // Update data
        ot.status = 'approved';

        // Update UI
        const row = $(`tr:has(.approve-ot-btn[data-ot-id="${otId}"])`);
        if (row.length) {
            const statusCell = row.find('.ot-status-cell');
            statusCell.html('<span class="badge px-2 rounded-1 bg-success">Approved</span>');
            statusCell.attr('data-status', 'approved');
            
            // Remove action buttons
            row.find('td:last-child').html('');
        }

        // Update counters
        updateCounters();
    }

    function handleRejectOT(otId) {
        const ot = overtimeData.find(o => o.id === otId);
        if (!ot) return;

        // Update data
        ot.status = 'rejected';

        // Update UI
        const row = $(`tr:has(.reject-ot-btn[data-ot-id="${otId}"])`);
        if (row.length) {
            const statusCell = row.find('.ot-status-cell');
            statusCell.html('<span class="badge px-2 rounded-1 bg-danger">Rejected</span>');
            statusCell.attr('data-status', 'rejected');
            
            // Remove action buttons
            row.find('td:last-child').html('');
        }

        // Update counters
        updateCounters();
    }

    function handleViewEvidence(otId) {
        const ot = overtimeData.find(o => o.id === otId);
        if (!ot) return;

        alert(`Viewing evidence for ${ot.employeeName}'s OT on ${ot.date}.\n\nEvidence functionality will be implemented with backend integration.`);
    }

    function handleExport() {
        alert('Export functionality will be implemented with backend integration.');
    }

    // ============================================
    // COUNTERS UPDATE
    // ============================================
    function updateCounters() {
        if (!overtimeTable) return;

        let totalOTHours = 0;
        let pendingApprovals = 0;
        let floor9LateStays = 0;
        let geofenceVerifiedOT = 0;

        overtimeTable.rows({ search: 'applied' }).every(function () {
            const row = this.node();
            const otHours = parseFloat($(row).find('td:eq(4)').text().replace(' hrs', '')) || 0;
            const status = $(row).find('.ot-status-cell').attr('data-status');
            const floor = $(row).find('td:eq(2)').text();
            const verification = $(row).find('td:eq(6)').text();

            totalOTHours += otHours;

            if (status === 'pending') {
                pendingApprovals++;
            }

            if (floor.includes('Floor 9')) {
                floor9LateStays++;
            }

            if (verification.includes('Geofence Verified')) {
                geofenceVerifiedOT++;
            }
        });

        $('#totalOTHours').text(totalOTHours.toFixed(1));
        $('#pendingApprovals').text(pendingApprovals);
        $('#floor9LateStays').text(floor9LateStays);
        $('#geofenceVerifiedOT').text(geofenceVerifiedOT);
    }

})();

