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
        const floorLabel = employee.floor_name || 'N/A';
        const floorIds = Array.isArray(employee.sbu_floor_ids) ? employee.sbu_floor_ids.join(',') : '';
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

        const headers = ['Employee', 'Employee Code', 'Department', 'SBU', 'Total Days', 'Present', 'Absent', 'Half-days', 'Annual Leave', 'Sick Leave', 'Casual Leave', 'Late Arrivals', 'Early Departures', 'Zone-2 Verification', 'Regularization'];
        let csv = headers.join(',') + '\n';
        rows.forEach(function (row) {
            csv += row.map(csvEscape).join(',') + '\n';
        });
        downloadCsv(csv, `monthly_summary_${selectedMonth || 'report'}.csv`);
    }

    function handleExportPdf() {
        const rows = getExportRows();
        if (!rows.length) {
            if (window.Swal) Swal.fire('Info', 'No rows available to export.', 'info');
            return;
        }

        const html = buildPrintableHtml(rows);
        const printWindow = window.open('', '_blank');
        if (!printWindow) return;
        printWindow.document.write(html);
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
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
            floorNumber: button.getAttribute('data-floor-number'),
            branch: button.getAttribute('data-branch') || '-'
        };

        populateDetailCanvas(employeeData);
        generateMonthlyCalendar(employeeData);
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

            rows.push([
                employeeName,
                employeeCode,
                department,
                sbuFloor,
                $row.find('td:eq(2)').text().trim(),
                $row.find('td:eq(3)').text().trim(),
                $row.find('td:eq(4)').text().trim(),
                $row.find('td:eq(5)').text().trim(),
                $row.find('td:eq(6)').text().trim(),
                $row.find('td:eq(7)').text().trim(),
                $row.find('td:eq(8)').text().trim(),
                $row.find('td:eq(9)').text().trim(),
                $row.find('td:eq(10)').text().trim(),
                $row.find('td:eq(11)').text().trim(),
                $row.find('td:eq(12)').text().trim()
            ]);
        });
        return rows;
    }

    function buildPrintableHtml(rows) {
        const head = `
            <tr>
                <th>Employee</th><th>Code</th><th>Department</th><th>SBU/Floor</th>
                <th>Total</th><th>Present</th><th>Absent</th><th>Half</th>
                <th>Annual</th><th>Sick</th><th>Casual</th><th>Late</th>
                <th>Early</th><th>Zone-2</th><th>Regularization</th>
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

