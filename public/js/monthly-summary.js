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
    let currentCalendarContext = null;
    let currentCalendarDays = [];
    let detailViewMonth = null;

    function getLeaveTypes() {
        return Array.isArray(window.monthlySummaryLeaveTypes) ? window.monthlySummaryLeaveTypes : [];
    }

    function getTableColumnLayout() {
        const leaveTypeCount = 1; // Only 1 column for "Leaves" now
        const halfDaysCol = 5;
        const firstLeaveCol = 6;
        const lateCol = firstLeaveCol + leaveTypeCount;
        const earlyCol = lateCol + 1;
        const zoneCol = earlyCol + 1;
        const regularizationCol = zoneCol + 1;
        const actionsCol = regularizationCol + 1;

        return {
            leaveTypeCount,
            halfDaysCol,
            firstLeaveCol,
            lateCol,
            earlyCol,
            zoneCol,
            regularizationCol,
            actionsCol,
            totalColumns: actionsCol + 1,
        };
    }

    // ============================================
    // INITIALIZATION
    // ============================================
    $(document).ready(function () {
        applyViewerEmployeeScopeFilters();
        loadMonthlySummaryData();
        initializeDataTable();
        const selectedSbuId = $('#filterSBU').val();
        populateDepartmentOptions(selectedSbuId);
        populateFloorOptions(selectedSbuId);
        const queryDepartmentId = new URLSearchParams(window.location.search).get('department_id');
        if (queryDepartmentId && $('#filterBranch option[value="' + queryDepartmentId + '"]').length) {
            $('#filterBranch').val(queryDepartmentId);
        }
        const queryFloorId = new URLSearchParams(window.location.search).get('floor_id');
        if (queryFloorId && $('#filterFloor option[value="' + queryFloorId + '"]').length) {
            $('#filterFloor').val(queryFloorId);
        }
        selectedMonth = $('#filterMonth').val() || selectedMonth;
        initializeEventHandlers();
        initializeFloorFilter();
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

    function applyViewerEmployeeScopeFilters() {
        const scope = window.viewerEmployeeScope || {};
        if (!scope.restricted || !scope.sbu_id) {
            return;
        }

        const sbuSelect = document.getElementById('filterSBU');
        if (!sbuSelect) {
            return;
        }

        const sbuKey = String(scope.sbu_id);
        const hasOption = Array.from(sbuSelect.options).some(function (option) {
            return option.value === sbuKey;
        });

        if (hasOption) {
            sbuSelect.value = sbuKey;
            sbuSelect.disabled = true;
        }
    }

    function populateFloorOptions(sbuId) {
        const $floor = $('#filterFloor');
        if (!$floor.length) {
            return;
        }
        const prev = $floor.val();
        const floors = window.monthlySummaryFloors || [];
        const sbuKey = sbuId ? String(sbuId) : '';
        $floor.find('option').not(':first').remove();
        const list = sbuKey
            ? floors.filter((f) => String(f.sbu_id) === sbuKey)
            : floors.slice();
        list.forEach((f) => {
            const numPart = f.floor_number != null && f.floor_number !== '' ? ' (' + f.floor_number + ')' : '';
            $floor.append($('<option>', { value: String(f.id), text: (f.name || 'Floor') + numPart }));
        });
        if (prev && $floor.find('option[value="' + prev + '"]').length) {
            $floor.val(prev);
        } else {
            $floor.val('');
        }
    }

    function populateDepartmentOptions(sbuId) {
        const $dept = $('#filterBranch');
        if (!$dept.length) {
            return;
        }
        const prev = $dept.val();
        const sbuKey = sbuId ? String(sbuId) : '';
        $dept.find('option').not(':first').each(function () {
            const optionSbuId = String($(this).data('sbu-id') || '');
            const shouldShow = !sbuKey || optionSbuId === sbuKey;
            $(this).prop('hidden', !shouldShow).prop('disabled', !shouldShow);
        });
        if (prev && $dept.find('option[value="' + prev + '"]:not([disabled])').length) {
            $dept.val(prev);
        } else {
            $dept.val('');
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
        const layout = getTableColumnLayout();
        const leaveColumnTargets = Array.from(
            { length: layout.leaveTypeCount },
            (_, index) => layout.firstLeaveCol + index
        );
        const detailColumnTargets = [
            layout.lateCol,
            layout.earlyCol,
            layout.zoneCol,
            layout.regularizationCol,
        ];
        const colvisTargets = [1, 2, 3, 4, 5]
            .concat(leaveColumnTargets)
            .concat(detailColumnTargets);

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
                    targets: layout.actionsCol,
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
                    targets: leaveColumnTargets.concat(detailColumnTargets),
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
                columns: colvisTargets
            }],
            drawCallback: function() {
                $('[data-bs-toggle="tooltip"]').tooltip();
            }
        });
    }

    // ============================================
    // TABLE ROW BUILDER
    // ============================================
    function buildTotalLeavesCell(employee) {
        const usage = employee.leave_usage || {};
        let totalLeaves = 0;
        
        // Sum all leaves
        getLeaveTypes().forEach(function (leaveType) {
            totalLeaves += parseFloat(usage[String(leaveType.id)] ?? 0) || 0;
        });

        // Store breakdown in data attribute as JSON
        const breakdownData = escapeHtml(JSON.stringify(usage));

        if (totalLeaves > 0) {
            return `<td>
                        <button type="button" class="btn btn-sm btn-light border-0 badge bg-primary fs-6 px-3 rounded-pill leave-breakdown-btn" 
                                data-employee-name="${escapeHtml(employee.employee_name)}" 
                                data-employee-avatar="${escapeHtml(employee.employee_avatar || 'E')}"
                                data-employee-info="${escapeHtml(employee.employee_code)} | ${escapeHtml(employee.department)}"
                                data-total="${totalLeaves}" 
                                data-breakdown="${breakdownData}">
                            ${totalLeaves}
                        </button>
                    </td>`;
        }

        return '<td><span class="text-muted">-</span></td>';
    }

    function buildTableRow(employee) {
        const floorLabel = employee.floor_name || 'N/A';
        const floorIds = Array.isArray(employee.sbu_floor_ids) ? employee.sbu_floor_ids.join(',') : '';
        const halfDaysBadge = employee.half_days > 0
            ? `<span class="badge px-2 rounded-1 bg-warning text-dark">${employee.half_days}</span>`
            : '<span class="text-muted">-</span>';
        const totalLeavesCell = buildTotalLeavesCell(employee);

        const lateArrivalsBadge = employee.late_arrivals > 0
            ? `<span class="badge px-2 rounded-1 bg-warning text-dark">${employee.late_arrivals}</span>`
            : '<span class="text-muted">-</span>';
    
        const earlyDeparturesBadge = employee.early_departures > 0
            ? `<span class="badge px-2 rounded-1 bg-warning text-dark">${employee.early_departures}</span>`
            : '<span class="text-muted">-</span>';
    
        return `
            <tr data-sbu-floor-ids="${floorIds}">
                <td class="dt-control"></td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="user-avatar me-3">${employee.employee_avatar ?? 'E'}</div>
                        <div>
                            <div class="fw-semibold">${employee.employee_name}</div>
                            <small class="text-muted">${employee.employee_code} | ${employee.department}</small>
                            <div class="mt-1">
                                <span class="badge bg-info-subtle text-info px-2 py-1 rounded-1" style="font-size: 0.7rem;">
                                    <i class="bi bi-building me-1"></i>${escapeHtml(String(employee.sbu ?? ''))} — ${escapeHtml(String(floorLabel))}
                                </span>
                            </div>
                        </div>
                    </div>
                </td>
                <td><div class="fw-semibold">${employee.total_days}</div></td>
                <td><span class="badge px-2 rounded-1 bg-success">${employee.present}</span></td>
                <td><span class="badge px-2 rounded-1 bg-danger">${employee.absent}</span></td>
                <td>${halfDaysBadge}</td>
                ${totalLeavesCell}
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
                            data-late-arrivals="${employee.late_arrivals}"
                            data-early-departures="${employee.early_departures}"
                            data-zone2-verification="${employee.zone2_verification}"
                            data-regularization="${employee.regularization}"
                            data-floor="${escapeHtml(String(floorLabel))}"
                            data-floor-number="${employee.floor_number != null && employee.floor_number !== '' ? String(employee.floor_number) : ''}"
                            data-branch="${escapeHtml(String(employee.sbu ?? ''))}">
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
        $('#filterSBU').on('change', function () {
            const sbuId = $(this).val();
            populateDepartmentOptions(sbuId);
            populateFloorOptions(sbuId);
            if (monthlySummaryTable) {
                monthlySummaryTable.draw();
                updateCounters();
            }
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

        $('#monthlyCalendarGrid').on('click', '.calendar-day[data-date]', handleCalendarDayClick);
        $('#saveWorkAssignmentBtn').on('click', handleSaveWorkAssignment);
        $('#workAssignmentLocationGrid').on('change', 'input[name="work_type"]', function () {
            setWorkAssignmentSelection($(this).val());
        });

        $('#detailMonthPrevBtn').on('click', function () {
            navigateDetailMonth(-1);
        });

        $('#detailMonthNextBtn').on('click', function () {
            navigateDetailMonth(1);
        });

        // Leave Breakdown Modal Event
        $('#monthlySummaryTableBody').on('click', '.leave-breakdown-btn', function() {
            const btn = $(this);
            $('#leaveBreakdownName').text(btn.data('employee-name'));
            $('#leaveBreakdownAvatar').text(btn.data('employee-avatar'));
            $('#leaveBreakdownInfo').text(btn.data('employee-info'));
            $('#leaveBreakdownTotal').text(btn.data('total'));

            const breakdown = btn.data('breakdown');
            const list = $('#leaveBreakdownList');
            list.empty();

            const badgeClasses = ['bg-info', 'bg-primary', 'bg-secondary', 'bg-success', 'bg-warning text-dark', 'bg-danger'];
            
            getLeaveTypes().forEach(function (leaveType, index) {
                const value = parseFloat(breakdown[String(leaveType.id)] ?? 0) || 0;
                if (value > 0) {
                    const badgeClass = badgeClasses[index % badgeClasses.length];
                    list.append(`
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                            <span><i class="bi bi-dot text-muted me-1"></i>${leaveType.name}</span>
                            <span class="badge ${badgeClass} rounded-pill px-2">${value}</span>
                        </li>
                    `);
                }
            });

            if (list.children().length === 0) {
                list.append('<li class="list-group-item text-muted px-0 bg-transparent">No leaves taken.</li>');
            }

            const modal = new bootstrap.Modal(document.getElementById('leaveBreakdownModal'));
            modal.show();
        });
    }

    // ============================================
    // FILTER FUNCTIONS
    // ============================================
    function applyFilters() {
        selectedMonth = ($('#filterMonth').val() || selectedMonth);
        const sbu = $('#filterSBU').val();
        const branch = $('#filterBranch').val();
        const floorId = ($('#filterFloor').val() || '').trim();

        // Server-backed filters (month, sbu, department, floor) to keep results accurate.
        const params = new URLSearchParams();
        if (selectedMonth) params.set('month', selectedMonth);
        if (sbu) params.set('sbu_id', sbu);
        if (branch) params.set('department_id', branch);
        if (floorId) params.set('floor_id', floorId);

        const target = `${window.location.pathname}?${params.toString()}`;
        window.location.href = target;
    }

    function clearFilters() {
        window.location.href = window.location.pathname;
    }

    // ============================================
    // EXPORT FUNCTIONS (Prototype - Placeholder)
    // ============================================
    function handleExportExcel() {
        const rows = getExportRows();
        if (!rows.length) {
            if (window.Swal) Swal.fire('Info', 'No rows available to export.', 'info');
            return;
        }

        const headers = ['Employee', 'Employee Code', 'Department', 'SBU', 'Total Days', 'Present', 'Absent', 'Half-days', 'Total Leaves', 'Late Arrivals', 'Early Departures', 'Zone-2 Verification', 'Regularization'];
        let csv = headers.join(',') + '\n';
        rows.forEach(function (row) {
            csv += row.map(csvEscape).join(',') + '\n';
        });
        downloadCsv(csv, `monthly_summary_${selectedMonth || 'report'}.csv`);
    }

    function handleExportPdf() {
        const params = new URLSearchParams();
        const month = $('#filterMonth').val() || selectedMonth;
        const sbu = $('#filterSBU').val();
        const department = $('#filterBranch').val();
        const floor = ($('#filterFloor').val() || '').trim();
        const exportUrl = window.monthlySummaryExportPdfUrl || '';

        if (!exportUrl) {
            if (window.Swal) Swal.fire('Error', 'PDF export is not configured.', 'error');
            return;
        }

        if (month) params.set('month', month);
        if (sbu) params.set('sbu_id', sbu);
        if (department) params.set('department_id', department);
        if (floor) params.set('floor_id', floor);

        window.location.href = `${exportUrl}?${params.toString()}`;
    }

    function handleExportEmployeeReport() {
        if (!currentCalendarContext || !currentCalendarContext.employeeId) {
            if (window.Swal) Swal.fire('Info', 'Open an employee calendar first.', 'info');
            return;
        }

        const urlTemplate = window.monthlySummaryEmployeeExportPdfUrl || '';
        if (!urlTemplate) {
            if (window.Swal) Swal.fire('Error', 'Employee PDF export is not configured.', 'error');
            return;
        }

        const month = detailViewMonth || $('#filterMonth').val() || selectedMonth;
        const exportUrl = urlTemplate.replace('__ID__', String(currentCalendarContext.employeeId));
        window.location.href = `${exportUrl}?month=${encodeURIComponent(month)}`;
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
            lateArrivals: parseInt(button.getAttribute('data-late-arrivals')) || 0,
            earlyDepartures: parseInt(button.getAttribute('data-early-departures')) || 0,
            zone2Verification: button.getAttribute('data-zone2-verification') || 'N/A',
            regularization: parseInt(button.getAttribute('data-regularization')) || 0,
            floor: button.getAttribute('data-floor') || '-',
            floorNumber: button.getAttribute('data-floor-number'),
            branch: button.getAttribute('data-branch') || '-'
        };

        currentCalendarContext = {
            employeeId: employeeData.employeeId,
            employeeName: employeeData.employeeName,
            employeeAvatar: employeeData.employeeAvatar,
        };

        detailViewMonth = $('#filterMonth').val() || selectedMonth;
        updateDetailMonthHeading(detailViewMonth);

        populateDetailCanvas(employeeData);
        loadMonthlyCalendar(employeeData, detailViewMonth);
    }

    function populateDetailCanvas(data) {
        $('#detailEmployeeAvatar').text(data.employeeAvatar);
        $('#detailEmployeeName').text(data.employeeName);
        $('#detailEmployeeInfo').text(`${data.employeeId} | ${data.employeeDept}`);
        $('#detailEmployeeLocation').html(`<i class="bi bi-building me-1"></i>${escapeHtml(data.branch)} — ${escapeHtml(data.floor)}`);

        $('#detailTotalDays').text(data.totalDays);
        $('#detailPresent').text(data.present);
        $('#detailAbsent').text(data.absent);
        $('#detailHalfDays').text(data.halfDays);

        $('#detailLateArrivals').text(data.lateArrivals);
        $('#detailEarlyDepartures').text(data.earlyDepartures);

        const floorNum = data.floorNumber !== '' && data.floorNumber != null ? parseInt(data.floorNumber, 10) : NaN;
        if (floorNum === 9) {
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

    async function loadMonthlyCalendar(data, monthOverride) {
        const calendarGrid = $('#monthlyCalendarGrid');
        calendarGrid.html(`
            <div class="col-12 text-center py-4">
                <div class="spinner-border spinner-border-sm text-white" role="status">
                    <span class="visually-hidden">Loading calendar...</span>
                </div>
            </div>
        `);

        const month = monthOverride || detailViewMonth || $('#filterMonth').val() || selectedMonth;
        detailViewMonth = month;
        updateDetailMonthHeading(month);

        const calendarUrlTemplate = window.monthlySummaryCalendarUrl || '';
        const calendarUrl = calendarUrlTemplate.replace('__ID__', String(data.employeeId));

        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        const token = tokenMeta ? tokenMeta.getAttribute('content') : '';

        setDetailMonthNavLoading(true);

        try {
            const response = await fetch(`${calendarUrl}?month=${encodeURIComponent(month)}`, {
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': token,
                },
            });
            const payload = await response.json().catch(function () { return { success: false }; });

            if (!response.ok || !payload.success || !payload.data) {
                calendarGrid.html('<div class="col-12 text-center text-white-50 py-4">Unable to load calendar.</div>');
                return;
            }

            renderMonthlyCalendar(payload.data);
            updateDetailStatsFromCalendar(payload.data.stats || {}, payload.data.month || month);
        } catch (e) {
            calendarGrid.html('<div class="col-12 text-center text-white-50 py-4">Unable to load calendar.</div>');
        } finally {
            setDetailMonthNavLoading(false);
        }
    }

    function navigateDetailMonth(delta) {
        if (!currentCalendarContext || !detailViewMonth) {
            return;
        }

        const nextMonth = shiftMonth(detailViewMonth, delta);
        detailViewMonth = nextMonth;
        updateDetailMonthHeading(nextMonth);
        loadMonthlyCalendar(currentCalendarContext, nextMonth);
    }

    function updateDetailMonthHeading(monthStr) {
        $('#detailMonthLabel').text(formatMonthYear(monthStr));
    }

    function setDetailMonthNavLoading(isLoading) {
        $('#detailMonthPrevBtn, #detailMonthNextBtn').prop('disabled', !!isLoading);
    }

    function formatMonthYear(monthStr) {
        const parts = String(monthStr || '').split('-').map(Number);
        if (parts.length !== 2 || !parts[0] || !parts[1]) {
            return monthStr || '-';
        }

        const date = new Date(parts[0], parts[1] - 1, 1);
        return date.toLocaleDateString(undefined, {
            month: 'long',
            year: 'numeric',
        });
    }

    function shiftMonth(monthStr, delta) {
        const parts = String(monthStr || '').split('-').map(Number);
        const date = new Date(parts[0], (parts[1] - 1) + delta, 1);

        return date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0');
    }

    function getDaysInMonth(monthStr) {
        const parts = String(monthStr || '').split('-').map(Number);
        if (parts.length !== 2 || !parts[0] || !parts[1]) {
            return 0;
        }

        return new Date(parts[0], parts[1], 0).getDate();
    }

    function renderMonthlyCalendar(calendarData) {
        const calendarGrid = $('#monthlyCalendarGrid');
        calendarGrid.empty();

        const days = Array.isArray(calendarData.days) ? calendarData.days : [];
        currentCalendarDays = days;

        if (!days.length) {
            calendarGrid.html('<div class="col-12 text-center text-white-50 py-4">No calendar data.</div>');
            return;
        }

        const [year, month] = (calendarData.month || selectedMonth).split('-').map(Number);
        const firstDay = new Date(year, month - 1, 1);
        const startDayOfWeek = firstDay.getDay();

        const dayHeaders = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        dayHeaders.forEach(function (day) {
            calendarGrid.append(`<div class="calendar-day-header text-center fw-bold small opacity-75">${day}</div>`);
        });

        for (let i = 0; i < startDayOfWeek; i++) {
            calendarGrid.append('<div class="calendar-day"></div>');
        }

        days.forEach(function (dayData) {
            const statusClass = dayData.status || 'present';
            const label = dayData.label || statusClass;
            const displayText = getCalendarDisplayText(dayData, statusClass, label);
            const title = getCalendarTooltip(dayData, label);

            const statusHtml = displayText
                ? `<div class="calendar-day-status">${escapeHtml(displayText)}</div>`
                : '';

            calendarGrid.append(`
                <div class="calendar-day ${escapeHtml(statusClass)}"
                     data-date="${escapeAttr(dayData.date)}"
                     title="${escapeAttr(title)}">
                    <div class="calendar-day-number">${dayData.day}</div>
                    ${statusHtml}
                </div>
            `);
        });
    }

    function getCalendarDisplayText(dayData, statusClass, label) {
        if (statusClass === 'scheduled') {
            return '';
        }

        if (dayData.is_holiday_work && dayData.detail) {
            return dayData.detail;
        }

        if (statusClass === 'leave' || statusClass === 'half-day' || statusClass === 'holiday') {
            return dayData.detail || label;
        }

        if (statusClass === 'absent') {
            return 'Absent';
        }

        return label;
    }

    function getCalendarTooltip(dayData, label) {
        if (dayData.status === 'scheduled') {
            return '';
        }

        const notes = dayData.notes ? String(dayData.notes).trim() : '';
        if (notes) {
            return `${label} — ${notes}`;
        }

        if (dayData.is_holiday_work && dayData.detail) {
            return dayData.detail;
        }

        if (dayData.status === 'holiday' && dayData.detail) {
            return dayData.detail;
        }

        if ((dayData.status === 'leave' || dayData.status === 'half-day') && dayData.detail) {
            return `${label} — ${dayData.detail}`;
        }

        return label;
    }

    function handleCalendarDayClick(event) {
        const date = event.currentTarget.getAttribute('data-date');
        if (!date || !currentCalendarContext) {
            return;
        }

        const dayData = currentCalendarDays.find(function (day) {
            return day.date === date;
        });

        if (!dayData) {
            return;
        }

        openWorkAssignmentModal(dayData);
    }

    function openWorkAssignmentModal(dayData) {
        const modalEl = document.getElementById('workAssignmentModal');
        if (!modalEl || !currentCalendarContext) {
            return;
        }

        const formattedDate = formatDisplayDate(dayData.date);
        const avatar = currentCalendarContext.employeeAvatar
            || getEmployeeInitials(currentCalendarContext.employeeName);

        $('#workAssignmentEmployeeAvatar').text(avatar);
        $('#workAssignmentEmployeeName').text(currentCalendarContext.employeeName || '-');
        $('#workAssignmentDateLabel').text(formattedDate);
        updateWorkAssignmentStatusBadge(dayData);

        const isAssignable = !!dayData.is_assignable;
        const isAbsentMarkable = !!dayData.is_absent_markable;
        const blockedNotice = $('#workAssignmentBlockedNotice');
        const form = $('#workAssignmentForm');
        const saveBtn = $('#saveWorkAssignmentBtn');
        const locationCards = $('#workAssignmentLocationGrid .work-assignment-location-card');
        const absentCard = $('#workTypeAbsentCard');
        const absentInput = $('#workTypeAbsent');

        blockedNotice.toggleClass('d-none', isAssignable);
        if (!isAssignable) {
            $('#workAssignmentBlockedNoticeText').html(resolveWorkAssignmentBlockedMessage(dayData));
        }
        form.find('textarea').prop('disabled', !isAssignable);
        form.find('input[name="work_type"]').prop('disabled', !isAssignable);
        locationCards.toggleClass('is-disabled', !isAssignable);
        absentCard.toggleClass('is-disabled', !isAssignable || !isAbsentMarkable);
        absentInput.prop('disabled', !isAssignable || !isAbsentMarkable);
        saveBtn.prop('disabled', !isAssignable);

        const workType = dayData.work_type || 'none';
        setWorkAssignmentSelection(workType);

        $('#workAssignmentNotes').val(dayData.notes || '');

        modalEl.dataset.assignmentDate = dayData.date;

        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }

    function setWorkAssignmentSelection(workType) {
        const normalized = workType || 'none';
        const input = $(`#workAssignmentLocationGrid input[name="work_type"][value="${normalized}"]`);
        if (input.length) {
            input.prop('checked', true);
        }

        $('#workAssignmentLocationGrid .work-assignment-location-card').each(function () {
            const cardType = String($(this).data('work-type') || 'none');
            $(this).toggleClass('is-selected', cardType === normalized);
        });
    }

    function resolveWorkAssignmentBlockedMessage(dayData) {
        if (dayData.assignment_blocked_message) {
            return escapeHtml(dayData.assignment_blocked_message);
        }

        if (dayData.assignment_block_reason === 'shift_planner') {
            return buildShiftPlannerBlockedMessage();
        }

        if (['leave', 'half-day'].includes(dayData.status)) {
            return 'Work location cannot be assigned on leave days.';
        }

        return 'Work location cannot be assigned on this day.';
    }

    function buildShiftPlannerBlockedMessage() {
        const shiftPlannerUrl = window.monthlySummaryShiftPlannerUrl || '';
        const message = 'This employee uses shift-based scheduling. Update off days and holidays from Shift Planner.';

        if (!shiftPlannerUrl) {
            return escapeHtml(message);
        }

        return `${escapeHtml(message)} <a href="${escapeHtml(shiftPlannerUrl)}" class="work-assignment-blocked-link">Open Shift Planner</a>`;
    }

    function updateWorkAssignmentStatusBadge(dayData) {
        const badge = $('#workAssignmentDayStatus');
        const label = dayData.label || dayData.status || '-';
        const status = dayData.status || 'present';

        badge.removeClass('is-warning is-muted is-primary is-danger');

        if (status === 'absent') {
            badge.addClass('is-danger');
        } else if (['leave', 'half-day', 'outstation'].includes(status)) {
            badge.addClass('is-warning');
        } else if (['off', 'holiday'].includes(status)) {
            badge.addClass('is-muted');
        } else if (status === 'work-from-home') {
            badge.addClass('is-primary');
        }

        $('#workAssignmentDayStatusText').text(label);
    }

    function getEmployeeInitials(name) {
        const parts = String(name || '')
            .trim()
            .split(/\s+/)
            .filter(Boolean);

        if (!parts.length) {
            return 'E';
        }

        if (parts.length === 1) {
            return parts[0].slice(0, 2).toUpperCase();
        }

        return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    }

    async function handleSaveWorkAssignment() {
        const modalEl = document.getElementById('workAssignmentModal');
        if (!modalEl || !currentCalendarContext) {
            return;
        }

        const assignmentDate = modalEl.dataset.assignmentDate;
        if (!assignmentDate) {
            return;
        }

        const workType = $('input[name="work_type"]:checked').val() || 'none';
        const notes = ($('#workAssignmentNotes').val() || '').trim();
        const urlTemplate = window.monthlySummaryWorkAssignmentUrl || '';
        const saveUrl = urlTemplate.replace('__ID__', String(currentCalendarContext.employeeId));

        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        const token = tokenMeta ? tokenMeta.getAttribute('content') : '';
        const saveBtn = $('#saveWorkAssignmentBtn');

        saveBtn.prop('disabled', true);

        try {
            const response = await fetch(saveUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                },
                body: JSON.stringify({
                    assignment_date: assignmentDate,
                    work_type: workType,
                    notes: notes,
                }),
            });

            const payload = await response.json().catch(function () { return { success: false }; });

            if (!response.ok || !payload.success) {
                const message = payload.message || 'Unable to save work assignment.';
                if (window.Swal) {
                    Swal.fire('Error', message, 'error');
                } else {
                    alert(message);
                }
                return;
            }

            if (payload.data && payload.data.calendar) {
                renderMonthlyCalendar(payload.data.calendar);
                updateDetailStatsFromCalendar(
                    payload.data.calendar.stats || {},
                    payload.data.calendar.month || detailViewMonth,
                );
                syncEmployeeSummaryRow(
                    currentCalendarContext.employeeId,
                    payload.data.calendar.stats || {},
                );
            }

            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) {
                modal.hide();
            }

            if (window.Swal) {
                Swal.fire({
                    icon: 'success',
                    title: 'Saved',
                    text: payload.message || 'Work assignment saved.',
                    timer: 1600,
                    showConfirmButton: false,
                });
            }
        } catch (e) {
            if (window.Swal) {
                Swal.fire('Error', 'Unable to save work assignment.', 'error');
            } else {
                alert('Unable to save work assignment.');
            }
        } finally {
            saveBtn.prop('disabled', false);
        }
    }

    function formatDisplayDate(dateStr) {
        const parts = String(dateStr).split('-').map(Number);
        if (parts.length !== 3) {
            return dateStr;
        }

        const date = new Date(parts[0], parts[1] - 1, parts[2]);
        return date.toLocaleDateString(undefined, {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    }

    function updateDetailStatsFromCalendar(stats, monthStr) {
        if (!stats) return;

        const month = monthStr || detailViewMonth || selectedMonth;
        $('#detailTotalDays').text(getDaysInMonth(month));
        $('#detailPresent').text(stats.present ?? 0);
        $('#detailAbsent').text(stats.absent ?? 0);
        $('#detailHalfDays').text(stats.half_days ?? 0);
        $('#detailLateArrivals').text(stats.late ?? 0);
    }

    function syncEmployeeSummaryRow(employeeId, stats) {
        if (!employeeId || !stats) {
            return;
        }

        const rowButton = document.querySelector(
            `.view-detail-btn[data-employee-id="${String(employeeId)}"]`,
        );

        if (!rowButton) {
            return;
        }

        const present = stats.present ?? 0;
        const absent = stats.absent ?? 0;
        const halfDays = stats.half_days ?? 0;

        rowButton.setAttribute('data-present', String(present));
        rowButton.setAttribute('data-absent', String(absent));
        rowButton.setAttribute('data-half-days', String(halfDays));

        const row = rowButton.closest('tr');
        if (!row) {
            return;
        }

        const presentCell = row.querySelector('td:nth-child(4) .badge, td:nth-child(4) .fw-semibold');
        const absentCell = row.querySelector('td:nth-child(5) .badge');
        const halfDayCell = row.querySelector('td:nth-child(6) .badge, td:nth-child(6) span');

        if (presentCell) {
            presentCell.textContent = String(present);
        }

        if (absentCell) {
            absentCell.textContent = String(absent);
        }

        if (halfDayCell) {
            halfDayCell.textContent = halfDays > 0 ? String(halfDays) : '-';
        }

        updateCounters();
    }

    function escapeAttr(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
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

    function initializeFloorFilter() {
        $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            if (!settings.nTable || settings.nTable.id !== 'monthlySummaryTable') return true;

            const selectedFloorId = ($('#filterFloor').val() || '').trim();
            if (!selectedFloorId) return true;

            const rowNode = settings.aoData[dataIndex]?.nTr;
            const idsAttr = rowNode?.getAttribute('data-sbu-floor-ids') || '';
            const ids = idsAttr.split(',').map((s) => s.trim()).filter(Boolean);
            return ids.includes(selectedFloorId);
        });

        $('#filterFloor').on('change', function () {
            if (monthlySummaryTable) {
                monthlySummaryTable.draw();
                updateCounters();
            }
        });
    }

    function getExportRows() {
        if (!monthlySummaryTable) return [];
        const layout = getTableColumnLayout();
        const rows = [];
        monthlySummaryTable.rows({ search: 'applied' }).every(function () {
            const rowNode = this.node();
            const $row = $(rowNode);
            const employeeInfo = $row.find('td:eq(1)');
            const employeeName = employeeInfo.find('.fw-semibold').first().text().trim();
            const metaText = employeeInfo.find('small.text-muted').first().text().trim();
            const parts = metaText.split('|').map(s => s.trim());
            const employeeCode = parts[0] || '';
            const department = parts[1] || '';
            const sbuFloor = employeeInfo.find('.badge').first().text().trim();

            const row = [
                employeeName,
                employeeCode,
                department,
                sbuFloor,
                $row.find('td:eq(2)').text().trim(),
                $row.find('td:eq(3)').text().trim(),
                $row.find('td:eq(4)').text().trim(),
                $row.find('td:eq(5)').text().trim(),
            ];

            for (let col = layout.firstLeaveCol; col < layout.lateCol; col += 1) {
                row.push($row.find(`td:eq(${col})`).text().trim());
            }

            row.push(
                $row.find(`td:eq(${layout.lateCol})`).text().trim(),
                $row.find(`td:eq(${layout.earlyCol})`).text().trim(),
                $row.find(`td:eq(${layout.zoneCol})`).text().trim(),
                $row.find(`td:eq(${layout.regularizationCol})`).text().trim()
            );

            rows.push(row);
        });
        return rows;
    }

    function buildPrintableHtml(rows) {
        const head = `
            <tr>
                <th>Employee</th><th>Code</th><th>Department</th><th>SBU/Floor</th>
                <th>Total</th><th>Present</th><th>Absent</th><th>Half</th>
                <th>Leaves</th>
                <th>Late</th><th>Early</th><th>Zone-2</th><th>Regularization</th>
            </tr>`;
        const body = rows.map(function (r) {
            return '<tr>' + r.map(function (c) { return `<td>${escapeHtml(c)}</td>`; }).join('') + '</tr>';
        }).join('');

        return `
            <html>
            <head>
                <title>Monthly Summary ${escapeHtml(selectedMonth || '')}</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 16px; }
                    table { width: 100%; border-collapse: collapse; font-size: 12px; }
                    th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
                    th { background: #f3f4f6; }
                </style>
            </head>
            <body>
                <h3>Monthly Summary (${escapeHtml(selectedMonth || '')})</h3>
                <table><thead>${head}</thead><tbody>${body}</tbody></table>
            </body>
            </html>
        `;
    }

    function csvEscape(value) {
        const text = String(value ?? '');
        return '"' + text.replace(/"/g, '""') + '"';
    }

    function downloadCsv(csv, fileName) {
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.setAttribute('href', url);
        link.setAttribute('download', fileName);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

})();

