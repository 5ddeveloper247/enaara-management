(function () {
    'use strict';

    var selectedExcelMonth = new Date().getMonth();

    var ROSTER_EXCEL_COLUMNS = [
        { header: 'Employee Name', key: 'employee_name' },
        { header: 'Employee Code', key: 'employee_code' },
        { header: 'Employee Type', key: 'employee_type' },
        { header: 'Department', key: 'department' },
        { header: 'Roster Date', key: 'roster_date' },
        { header: 'Day', key: 'day' },
        { header: 'Status', key: 'status' },
        { header: 'Shift Name', key: 'shift_name' },
        { header: 'Shift Type', key: 'shift_type' },
        { header: 'Start Time', key: 'start_time' },
        { header: 'End Time', key: 'end_time' },
        { header: 'Hours', key: 'hours' },
        { header: 'Floor', key: 'floor' },
        { header: 'Location', key: 'location' },
        { header: 'Notes', key: 'notes' },
        { header: 'Custom Time', key: 'custom_time' },
        { header: 'Compensatory', key: 'compensatory' },
        { header: 'Deleted', key: 'deleted' }
    ];

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function getExcelModalEl() {
        return document.getElementById('rosterExportExcelModal');
    }

    function populateYearOptions() {
        var yearSelect = document.getElementById('rosterExportExcelYear');
        if (!yearSelect) {
            return;
        }

        var currentYear = new Date().getFullYear();
        var html = '';
        for (var y = currentYear - 2; y <= currentYear + 2; y++) {
            html += '<option value="' + y + '">' + y + '</option>';
        }
        yearSelect.innerHTML = html;
    }

    function setSelectedExcelMonth(monthIndex) {
        selectedExcelMonth = monthIndex;
        document.querySelectorAll('.roster-export-excel-month-btn').forEach(function (btn) {
            var isActive = parseInt(btn.getAttribute('data-month'), 10) === monthIndex;
            btn.classList.toggle('is-active', isActive);
            btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
    }

    function syncExcelFromRosterView() {
        var ctx = typeof window.getRosterExportContext === 'function'
            ? window.getRosterExportContext()
            : null;

        var year = ctx ? ctx.year : new Date().getFullYear();
        var month = ctx ? ctx.month : new Date().getMonth();
        var filter = ctx ? ctx.personnelFilter : 'internal';
        var showDeleted = ctx ? !!ctx.showDeleted : false;

        var yearSelect = document.getElementById('rosterExportExcelYear');
        if (yearSelect) {
            yearSelect.value = String(year);
        }

        setSelectedExcelMonth(month);

        var groupSelect = document.getElementById('rosterExportExcelEmployeeGroup');
        if (groupSelect) {
            groupSelect.value = filter === 'third_party' ? 'third_party' : 'internal';
        }

        var deletedCb = document.getElementById('rosterExportExcelIncludeDeleted');
        if (deletedCb) {
            deletedCb.checked = showDeleted;
        }
    }

    function collectExcelExportOptions() {
        return {
            year: parseInt(document.getElementById('rosterExportExcelYear')?.value || '0', 10),
            month: selectedExcelMonth + 1,
            employee_group: document.getElementById('rosterExportExcelEmployeeGroup')?.value || 'internal',
            include_deleted: !!document.getElementById('rosterExportExcelIncludeDeleted')?.checked
        };
    }

    function openExcelExportModal() {
        var modalEl = getExcelModalEl();
        if (!modalEl || typeof bootstrap === 'undefined') {
            return;
        }
        syncExcelFromRosterView();
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    function hideExcelExportModal() {
        var modalEl = getExcelModalEl();
        if (!modalEl || typeof bootstrap === 'undefined') {
            return;
        }
        var instance = bootstrap.Modal.getInstance(modalEl);
        if (instance) {
            instance.hide();
        }
    }

    function normalizeValue(value, fallback) {
        var fb = fallback === undefined ? '' : fallback;
        if (value === null || value === undefined) {
            return fb;
        }
        var str = String(value).trim();
        if (str === '' || str.toLowerCase() === 'null' || str.toLowerCase() === 'undefined') {
            return fb;
        }
        return str;
    }

    function formatCellValue(row, col) {
        if (col.key === 'hours') {
            var hours = row[col.key];
            if (hours === null || hours === undefined || hours === '') {
                return '';
            }
            return String(hours);
        }
        return normalizeValue(row[col.key], '');
    }

    function buildSheetData(rows, columns) {
        var header = columns.map(function (col) {
            return col.header.toUpperCase();
        });
        var body = rows.map(function (row) {
            return columns.map(function (col) {
                return formatCellValue(row, col);
            });
        });
        return [header].concat(body);
    }

    function computeColumnWidths(data, columns) {
        var widths = columns.map(function (col) {
            return { wch: Math.max(18, col.header.length + 6) };
        });
        data.forEach(function (row) {
            row.forEach(function (cell, idx) {
                var len = String(cell === null || cell === undefined ? '' : cell).length;
                if (len + 6 > widths[idx].wch) {
                    widths[idx].wch = Math.min(80, len + 6);
                }
            });
        });
        return widths;
    }

    function excelFileName(prefix) {
        var now = new Date();
        var y = now.getFullYear();
        var m = String(now.getMonth() + 1).padStart(2, '0');
        var d = String(now.getDate()).padStart(2, '0');
        var hh = String(now.getHours()).padStart(2, '0');
        var mm = String(now.getMinutes()).padStart(2, '0');
        return prefix + '_' + y + m + d + '_' + hh + mm + '.xlsx';
    }

    function exportToExcel(rows, payload) {
        if (!window.XLSX) {
            throw new Error('XLSX library not loaded.');
        }

        var columns = Array.isArray(payload.columns) && payload.columns.length
            ? payload.columns
            : ROSTER_EXCEL_COLUMNS;
        var data = buildSheetData(rows, columns);
        var ws = XLSX.utils.aoa_to_sheet(data);
        ws['!cols'] = computeColumnWidths(data, columns);
        ws['!autofilter'] = {
            ref: XLSX.utils.encode_range({
                s: { c: 0, r: 0 },
                e: { c: columns.length - 1, r: data.length - 1 }
            })
        };

        var wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, payload.sheet_name || 'Shift Roster');
        XLSX.writeFile(wb, excelFileName(payload.file_prefix || 'shift-roster'));
    }

    function showSuccess(text) {
        if (typeof window.showSuccess === 'function') {
            window.showSuccess(text);
            return;
        }
        if (window.Swal) {
            Swal.fire({
                icon: 'success',
                title: 'Export Successful',
                text: text,
                confirmButtonColor: '#012445'
            });
            return;
        }
        alert(text);
    }

    function showError(text) {
        var message = text || 'Export failed. Please try again.';
        if (typeof window.showError === 'function') {
            window.showError(message);
            return;
        }
        if (window.Swal) {
            Swal.fire({
                icon: 'error',
                title: 'Export Failed',
                text: message,
                confirmButtonColor: '#dc3545'
            });
            return;
        }
        alert(message);
    }

    function parseErrorMessage(bodyText, fallbackMessage) {
        var fallback = fallbackMessage || 'Could not export roster.';
        if (!bodyText) {
            return fallback;
        }
        try {
            var body = JSON.parse(bodyText);
            if (body.message) {
                return body.message;
            }
            if (body.errors) {
                var messages = [];
                Object.keys(body.errors).forEach(function (key) {
                    var fieldErrors = body.errors[key];
                    if (Array.isArray(fieldErrors)) {
                        messages = messages.concat(fieldErrors);
                    }
                });
                if (messages.length) {
                    return messages.join('\n');
                }
            }
        } catch (e) {
            return fallback;
        }
        return fallback;
    }

    function setExcelExportButtonLoading(isLoading) {
        var submitBtn = document.getElementById('rosterExportExcelSubmitBtn');
        if (!submitBtn) {
            return;
        }

        submitBtn.disabled = !!isLoading;
        submitBtn.innerHTML = isLoading
            ? '<span class="spinner-border spinner-border-sm me-2"></span>Exporting...'
            : '<i class="bi bi-file-earmark-spreadsheet me-1"></i>Export Excel';
    }

    function validateExcelExportOptions(options) {
        if (!options.year || options.month < 1 || options.month > 12) {
            showError('Please select a valid month and year.');
            return false;
        }

        if (!['internal', 'third_party'].includes(options.employee_group)) {
            showError('Please select an employee group.');
            return false;
        }

        return true;
    }

    function handleExcelExportSubmit() {
        var options = collectExcelExportOptions();
        var exportUrl = window.rosterExportExcelUrl || '';

        if (!exportUrl) {
            showError('Excel export URL is not configured.');
            return;
        }

        if (!validateExcelExportOptions(options)) {
            return;
        }

        setExcelExportButtonLoading(true);

        fetch(exportUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(options),
            credentials: 'same-origin'
        })
            .then(function (response) {
                return response.text().then(function (bodyText) {
                    return {
                        ok: response.ok,
                        bodyText: bodyText
                    };
                });
            })
            .then(function (result) {
                var body = {};
                try {
                    body = JSON.parse(result.bodyText || '{}');
                } catch (e) {
                    body = {};
                }

                if (!result.ok || !body.success) {
                    throw new Error(parseErrorMessage(result.bodyText, 'Could not export roster.'));
                }

                var payload = body.data || {};
                var rows = Array.isArray(payload.rows) ? payload.rows : [];

                if (!rows.length) {
                    throw new Error('No roster records found for the selected period.');
                }

                exportToExcel(rows, payload);
                hideExcelExportModal();
                showSuccess(rows.length + ' roster records have been exported successfully.');
            })
            .catch(function (err) {
                hideExcelExportModal();
                showError(err.message || 'Could not export roster.');
            })
            .finally(function () {
                setExcelExportButtonLoading(false);
            });
    }

    function syncExcelToolbarVisibility() {
        var excelBtn = document.getElementById('rosterExportExcelBtn');
        var rosterTab = document.getElementById('roster-tab');
        if (!excelBtn) {
            return;
        }

        var showOnRoster = rosterTab && rosterTab.classList.contains('active');
        excelBtn.style.display = showOnRoster ? '' : 'none';
    }

    function bindEvents() {
        var openBtn = document.getElementById('rosterExportExcelBtn');
        if (openBtn) {
            openBtn.addEventListener('click', openExcelExportModal);
        }

        document.querySelectorAll('.roster-export-excel-month-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                setSelectedExcelMonth(parseInt(this.getAttribute('data-month'), 10));
            });
        });

        var submitBtn = document.getElementById('rosterExportExcelSubmitBtn');
        if (submitBtn) {
            submitBtn.addEventListener('click', handleExcelExportSubmit);
        }

        var managementTab = document.getElementById('shift-management-tab');
        var rosterTab = document.getElementById('roster-tab');
        if (managementTab) {
            managementTab.addEventListener('shown.bs.tab', syncExcelToolbarVisibility);
        }
        if (rosterTab) {
            rosterTab.addEventListener('shown.bs.tab', syncExcelToolbarVisibility);
        }
    }

    function init() {
        populateYearOptions();
        setSelectedExcelMonth(new Date().getMonth());
        bindEvents();
        syncExcelToolbarVisibility();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    window.openRosterExportExcelModal = openExcelExportModal;
})();
