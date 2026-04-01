/**
 * Monthly Summary Module
 * Pre-payroll attendance and leave report
 */

(function () {
    'use strict';

    // ============================================
    // GLOBAL VARIABLES
    // ============================================
    let monthlySummaryTable;
    let monthlySummaryData = [];
    const currentDate = new Date();
    let selectedMonth = currentDate.getFullYear() + '-' + String(currentDate.getMonth() + 1).padStart(2, '0');

    // ============================================
    // INITIALIZATION
    // ============================================
    $(document).ready(function () {
        loadMonthlySummaryData();
        initializeDataTable();
        initializeEventHandlers();
        updateCounters();
    });

    // ============================================
    // DATA LOADING
    // ============================================
    function loadMonthlySummaryData() {
        if (typeof window.monthlySummaryRows !== 'undefined' && Array.isArray(window.monthlySummaryRows)) {
            monthlySummaryData = window.monthlySummaryRows;
        } else {
            console.warn('monthlySummaryRows not found, using empty array');
            monthlySummaryData = [];
        }
    }

    // ============================================
    // DATA TABLE INITIALIZATION
    // ============================================
    function initializeDataTable() {
        const tbody = $('#monthlySummaryTableBody');
        tbody.empty();

        monthlySummaryData.forEach(employee => {
            const row = buildTableRow(employee);
            tbody.append(row);
        });
        monthlySummaryTable = initUserDataTable('#monthlySummaryTable', {
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
                    targets: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
                    visible: true
                },
                {
                    targets: 13,
                    orderable: false,
                    className: 'no-toggle',
                    responsivePriority: 1
                },
                {
                    targets: 1,
                    responsivePriority: 2
                },
                {
                    targets: [2, 3, 4, 5],
                    responsivePriority: 3
                },
                {
                    targets: [6, 7, 8, 9, 10, 11, 12],
                    responsivePriority: 4
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
                className: 'btn btn-sm border-0 bg-main text-white',
                columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
            }],
            drawCallback: function() {
                $('[data-bs-toggle="tooltip"]').tooltip();
            }
        });
    }

    // ============================================
    // TABLE ROW BUILDER
    // ============================================
    function buildTableRow(employee) {
        const halfDaysBadge = employee.half_days > 0
            ? `<span class="badge px-2 rounded-1 bg-warning text-dark">${employee.half_days}</span>`
            : '<span class="text-muted">-</span>';
    
        const annualLeaveBadge = employee.annual_leave > 0
            ? `<span class="badge px-2 rounded-1 bg-info">${employee.annual_leave}</span>`
            : '<span class="text-muted">-</span>';
    
        const sickLeaveBadge = employee.sick_leave > 0
            ? `<span class="badge px-2 rounded-1 bg-primary">${employee.sick_leave}</span>`
            : '<span class="text-muted">-</span>';
    
        const casualLeaveBadge = employee.casual_leave > 0
            ? `<span class="badge px-2 rounded-1 bg-secondary">${employee.casual_leave}</span>`
            : '<span class="text-muted">-</span>';
    
        const lateArrivalsBadge = employee.late_arrivals > 0
            ? `<span class="badge px-2 rounded-1 bg-warning text-dark">${employee.late_arrivals}</span>`
            : '<span class="text-muted">-</span>';
    
        const earlyDeparturesBadge = employee.early_departures > 0
            ? `<span class="badge px-2 rounded-1 bg-warning text-dark">${employee.early_departures}</span>`
            : '<span class="text-muted">-</span>';
    
        return `
            <tr>
                <td class="dt-control"></td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="user-avatar me-3">${employee.employee_avatar ?? 'E'}</div>
                        <div>
                            <div class="fw-semibold">${employee.employee_name}</div>
                            <small class="text-muted">${employee.employee_code} | ${employee.department}</small>
                            <div class="mt-1">
                                <span class="badge bg-info-subtle text-info px-2 py-1 rounded-1" style="font-size: 0.7rem;">
                                    <i class="bi bi-building me-1"></i>${employee.sbu} - ${employee.floor_name}
                                </span>
                            </div>
                        </div>
                    </div>
                </td>
                <td><div class="fw-semibold">${employee.total_days}</div></td>
                <td><span class="badge px-2 rounded-1 bg-success">${employee.present}</span></td>
                <td><span class="badge px-2 rounded-1 bg-danger">${employee.absent}</span></td>
                <td>${halfDaysBadge}</td>
                <td>${annualLeaveBadge}</td>
                <td>${sickLeaveBadge}</td>
                <td>${casualLeaveBadge}</td>
                <td>${lateArrivalsBadge}</td>
                <td>${earlyDeparturesBadge}</td>
                <td>${employee.zone2_verification}</td>
                <td>${employee.regularization}</td>
                <td class="text-end">
                    <button type="button"
                            class="action-btn border-0 text-white btn-primary view-detail-btn"
                            data-bs-toggle="offcanvas"
                            data-bs-target="#employeeMonthlyDetailCanvas"
                            data-employee-id="${employee.employee_id}"
                            data-employee-name="${employee.employee_name}"
                            data-employee-avatar="${employee.employee_avatar}"
                            data-employee-dept="${employee.department}"
                            data-total-days="${employee.total_days}"
                            data-present="${employee.present}"
                            data-absent="${employee.absent}"
                            data-half-days="${employee.half_days}"
                            data-annual-leave="${employee.annual_leave}"
                            data-sick-leave="${employee.sick_leave}"
                            data-casual-leave="${employee.casual_leave}"
                            data-late-arrivals="${employee.late_arrivals}"
                            data-early-departures="${employee.early_departures}"
                            data-zone2-verification="${employee.zone2_verification}"
                            data-regularization="${employee.regularization}"
                            data-floor="${employee.floor_name}"
                            data-branch="${employee.sbu}">
                        <i class="bi bi-calendar3"></i>
                    </button>
                </td>
            </tr>
        `;
    }

    // ============================================
    // EVENT HANDLERS
    // ============================================
    function initializeEventHandlers() {
        // Month filter
        $('#filterMonth').on('change', function () {
            selectedMonth = $(this).val();
            applyFilters();
        });

        // Apply filters
        $('#applyFiltersBtn').on('click', applyFilters);

        // Clear filters
        $('#clearFiltersBtn').on('click', clearFilters);

        // Export buttons
        $('#exportExcelBtn').on('click', handleExportExcel);
        $('#exportPdfBtn').on('click', handleExportPdf);

        // Employee Detail Canvas
        const detailCanvas = document.getElementById('employeeMonthlyDetailCanvas');
        if (detailCanvas) {
            detailCanvas.addEventListener('show.bs.offcanvas', handleDetailCanvasShow);
        }

        // Export employee report
        $('#exportEmployeeReportBtn').on('click', handleExportEmployeeReport);
    }

    // ============================================
    // FILTER FUNCTIONS
    // ============================================
    function applyFilters() {
        const sbu = $('#filterSBU').val();
        const branch = $('#filterBranch').val();
        const floor = $('#filterFloor').val();

        // Build filter string
        let filterString = '';
        if (sbu) filterString += sbu;
        if (branch) filterString += (filterString ? ' ' : '') + branch;
        if (floor) filterString += (filterString ? ' ' : '') + floor;

        // Apply DataTable search
        if (monthlySummaryTable) {
            monthlySummaryTable.search(filterString).draw();
        }

        // Update counters
        updateCounters();
    }

    function clearFilters() {
        $('#filterSBU').val('');
        $('#filterBranch').val('');
        $('#filterFloor').val('');
        $('#filterMonth').val(selectedMonth);

        if (monthlySummaryTable) {
            monthlySummaryTable.search('').draw();
        }

        updateCounters();
    }

    // ============================================
    // EXPORT FUNCTIONS (Prototype - Placeholder)
    // ============================================
    function handleExportExcel() {
        alert('Excel export functionality will be implemented with backend integration.');
    }

    function handleExportPdf() {
        alert('PDF export functionality will be implemented with backend integration.');
    }

    function handleExportEmployeeReport() {
        alert('Employee report export will be implemented with backend integration.');
    }

    // ============================================
    // DETAIL CANVAS HANDLERS
    // ============================================
    function handleDetailCanvasShow(event) {
        const button = event.relatedTarget;
        if (!button || !button.classList.contains('view-detail-btn')) return;

        const employeeData = {
            employeeId: button.getAttribute('data-employee-id') || '-',
            employeeName: button.getAttribute('data-employee-name') || '-',
            employeeAvatar: button.getAttribute('data-employee-avatar') || '?',
            employeeDept: button.getAttribute('data-employee-dept') || '-',
            totalDays: parseInt(button.getAttribute('data-total-days')) || 0,
            present: parseInt(button.getAttribute('data-present')) || 0,
            absent: parseInt(button.getAttribute('data-absent')) || 0,
            halfDays: parseInt(button.getAttribute('data-half-days')) || 0,
            annualLeave: parseInt(button.getAttribute('data-annual-leave')) || 0,
            sickLeave: parseInt(button.getAttribute('data-sick-leave')) || 0,
            casualLeave: parseInt(button.getAttribute('data-casual-leave')) || 0,
            lateArrivals: parseInt(button.getAttribute('data-late-arrivals')) || 0,
            earlyDepartures: parseInt(button.getAttribute('data-early-departures')) || 0,
            zone2Verification: button.getAttribute('data-zone2-verification') || 'N/A',
            regularization: parseInt(button.getAttribute('data-regularization')) || 0,
            floor: button.getAttribute('data-floor') || '-',
            branch: button.getAttribute('data-branch') || '-'
        };

        populateDetailCanvas(employeeData);
        generateMonthlyCalendar(employeeData);
    }

    function populateDetailCanvas(data) {
        $('#detailEmployeeAvatar').text(data.employeeAvatar);
        $('#detailEmployeeName').text(data.employeeName);
        $('#detailEmployeeInfo').text(`${data.employeeId} | ${data.employeeDept}`);
        $('#detailEmployeeLocation').html(`<i class="bi bi-building me-1"></i>${data.branch} - Floor ${data.floor}`);

        $('#detailTotalDays').text(data.totalDays);
        $('#detailPresent').text(data.present);
        $('#detailAbsent').text(data.absent);
        $('#detailHalfDays').text(data.halfDays);

        $('#detailLateArrivals').text(data.lateArrivals);
        $('#detailEarlyDepartures').text(data.earlyDepartures);

        // Zone-2 Verification
        if (data.floor === '9') {
            if (data.zone2Verification === 'Verified') {
                $('#detailZone2Verification').html('<span class="badge bg-info"><i class="bi bi-check-circle me-1"></i>Verified</span>');
            } else {
                $('#detailZone2Verification').html('<span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>Pending</span>');
            }
        } else {
            $('#detailZone2Verification').html('<span class="text-white small">N/A</span>');
        }

        $('#detailRegularization').text(data.regularization);
    }

    function generateMonthlyCalendar(data) {
        const calendarGrid = $('#monthlyCalendarGrid');
        calendarGrid.empty();

        // Get year and month from selectedMonth
        const [year, month] = selectedMonth.split('-').map(Number);
        const firstDay = new Date(year, month - 1, 1);
        const lastDay = new Date(year, month, 0);
        const daysInMonth = lastDay.getDate();
        const startDayOfWeek = firstDay.getDay();

        // Day headers
        const dayHeaders = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        dayHeaders.forEach(day => {
            calendarGrid.append(`<div class="calendar-day-header text-center fw-bold small opacity-75">${day}</div>`);
        });

        // Empty cells for days before month starts
        for (let i = 0; i < startDayOfWeek; i++) {
            calendarGrid.append('<div class="calendar-day"></div>');
        }

        // Generate calendar days (simplified for prototype)
        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(year, month - 1, day);
            const dayOfWeek = date.getDay();
            const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;

            // Simple status assignment for prototype
            let statusClass = 'present';
            let statusText = 'Present';

            if (isWeekend) {
                statusClass = 'holiday';
                statusText = 'Holiday';
            }

            const dayElement = $(`
                <div class="calendar-day ${statusClass}" title="${statusText}">
                    <div class="calendar-day-number">${day}</div>
                    <div class="calendar-day-status">${statusText}</div>
                </div>
            `);

            calendarGrid.append(dayElement);
        }
    }

    // ============================================
    // COUNTERS UPDATE (Simplified for Prototype)
    // ============================================
    function updateCounters() {
        if (!monthlySummaryTable) return;

        let totalPresent = 0;
        let totalAbsent = 0;
        let totalLate = 0;
        let totalEmployees = 0;

        monthlySummaryTable.rows({ search: 'applied' }).every(function () {
            const row = this.node();
            const present = parseInt($(row).find('td:eq(3)').text()) || 0;
            const absent = parseInt($(row).find('td:eq(4)').text()) || 0;
            const lateText = $(row).find('td:eq(9)').text();
            const late = lateText !== '-' ? parseInt(lateText) || 0 : 0;

            totalPresent += present;
            totalAbsent += absent;
            totalLate += late;
            totalEmployees++;
        });

        const totalDays = totalEmployees * 30;
        const attendancePercentage = totalDays > 0 ? ((totalPresent / totalDays) * 100).toFixed(1) : 0;

        $('#attendancePercentage').text(attendancePercentage + '%');
        $('#totalAbsents').text(totalAbsent);
        $('#totalLateArrivals').text(totalLate);
        $('#totalGeofenceViolations').text('0'); // Placeholder for prototype
    }

})();

