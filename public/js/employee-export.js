(function () {
    'use strict';

    var INTERNAL_COLUMNS = [
        { header: 'Employee No', key: 'employee_code' },
        { header: 'Full Name', key: 'full_name' },
        { header: 'Organization', key: 'organization' },
        { header: 'SBU', key: 'sbu' },
        { header: 'Department', key: 'department' },
        { header: 'Category', key: 'employment_category' },
        { header: 'CNIC', key: 'cnic' },
        { header: 'Nationality', key: 'nationality' },
        { header: 'Gender', key: 'gender' },
        { header: 'Date of Joining', key: 'join_date' },
        { header: 'Designation', key: 'designation' },
        { header: 'Verification Status', key: 'verification_status' },
        { header: 'Email', key: 'email' },
        { header: 'Cell Number', key: 'cell_no' },
        { header: 'Employment Type', key: 'employment_type' },
        { header: 'Employee Type', key: 'employee_type' },
        { header: 'TAS ID', key: 'biometric_id' },
        { header: 'Sync Status', key: 'sync_status' },
        { header: 'Site', key: 'site' },
        { header: 'Vendor', key: 'vendor' },
        { header: 'Floor Access', key: 'floor_access' }
    ];

    var OUTSOURCED_COLUMNS = [
        { header: 'Full Name', key: 'full_name' },
        { header: 'CNIC Number', key: 'cnic_number' },
        { header: 'Mobile Number', key: 'mobile_number' },
        { header: 'Contractor Company', key: 'contractor_company_name' },
        { header: 'Supervisor Name', key: 'supervisor_name' },
        { header: 'Supervisor Contact', key: 'supervisor_contact_number' },
        { header: 'Organization', key: 'organization' },
        { header: 'SBU', key: 'sbu' },
        { header: 'Department', key: 'department' },
        { header: 'Job Role / Trade', key: 'job_role_trade' },
        { header: 'Placement (Floor)', key: 'placement_floor' },
        { header: 'Date of Deployment', key: 'date_of_deployment' },
        { header: 'Biometric ID', key: 'biometric_id' },
        { header: 'Attendance Access', key: 'attendance_access' }
    ];

    function normalizeValue(value, fallback) {
        var fb = fallback === undefined ? '' : fallback;
        if (value === null || value === undefined) return fb;
        var str = String(value).trim();
        if (str === '' || str.toLowerCase() === 'null' || str.toLowerCase() === 'undefined') return fb;
        return str;
    }

    function currentMode() {
        if (typeof window.getEmployeeDirectoryMode === 'function') {
            return window.getEmployeeDirectoryMode() === 'outsourced' ? 'outsourced' : 'internal';
        }
        return 'internal';
    }

    function buildQueryParams(mode) {
        var f = window.employeeFilters || {};
        var common = {
            filter_organization: f.organization || '',
            filter_sbu: f.sbu || '',
            filter_department: f.department || '',
            filter_name: f.name || '',
            filter_cnic: f.cnic || ''
        };
        if (mode === 'internal') {
            common.filter_employee_type = f.employeeType || '';
        }
        return common;
    }

    function exportConfig(mode) {
        if (mode === 'outsourced') {
            return {
                url: window.outsourcedEmployeeDataUrl,
                columns: OUTSOURCED_COLUMNS,
                filePrefix: 'outsourced_employees',
                sheetName: 'Outsourced Employees',
                confirmText: function (count) { return 'Do you want to export all (' + count + ') outsourced employees?'; },
                successText: function (count) { return count + ' outsourced employee records have been exported successfully.'; },
                emptyText: 'No outsourced employee data to export.',
                failText: 'Could not export outsourced employees. Please try again.'
            };
        }
        return {
            url: window.employeeDataUrl,
            columns: INTERNAL_COLUMNS,
            filePrefix: 'employees',
            sheetName: 'Employees',
            confirmText: function (count) { return 'Do you want to export all (' + count + ') employees?'; },
            successText: function (count) { return count + ' employee records have been exported successfully.'; },
            emptyText: 'No employee data to export.',
            failText: 'Could not export employees. Please try again.'
        };
    }

    function formatCellValue(mode, row, col) {
        if (col.key === 'floor_access') {
            return row.floor_access ? 'Yes' : 'No';
        }
        if (mode === 'outsourced' && col.key === 'attendance_access') {
            return row.attendance_access ? 'Granted' : 'Not Granted';
        }
        return normalizeValue(row[col.key], '');
    }

    function buildSheetData(mode, rows, columns) {
        var header = columns.map(function (col) { return col.header; });
        var body = rows.map(function (row) {
            return columns.map(function (col) { return formatCellValue(mode, row, col); });
        });
        return [header].concat(body);
    }

    function computeColumnWidths(data, columns) {
        var widths = columns.map(function (col) { return { wch: Math.max(14, col.header.length + 2) }; });
        data.forEach(function (row) {
            row.forEach(function (cell, idx) {
                var len = String(cell === null || cell === undefined ? '' : cell).length;
                if (len + 2 > widths[idx].wch) {
                    widths[idx].wch = Math.min(48, len + 2);
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

    function exportToExcel(mode, rows, config) {
        if (!window.XLSX) {
            throw new Error('XLSX library not loaded.');
        }

        var data = buildSheetData(mode, rows, config.columns);
        var ws = XLSX.utils.aoa_to_sheet(data);
        ws['!cols'] = computeColumnWidths(data, config.columns);
        ws['!autofilter'] = { ref: XLSX.utils.encode_range({ s: { c: 0, r: 0 }, e: { c: config.columns.length - 1, r: data.length - 1 } }) };

        var wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, config.sheetName);
        XLSX.writeFile(wb, excelFileName(config.filePrefix));
    }

    function showSuccess(text) {
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

    function askConfirmation(text, onConfirm) {
        if (window.Swal) {
            Swal.fire({
                icon: 'question',
                title: 'Confirm Excel Export',
                text: text,
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
                confirmButtonColor: '#012445',
                cancelButtonColor: '#6c757d'
            }).then(function (result) {
                if (result.isConfirmed) onConfirm();
            });
            return;
        }
        if (window.confirm(text)) onConfirm();
    }

    function bindExportButton() {
        $('#exportBtn').off('click.employeeExport').on('click.employeeExport', function () {
            var mode = currentMode();
            var config = exportConfig(mode);

            $.get(config.url, buildQueryParams(mode), function (res) {
                if (!res || !res.success || !Array.isArray(res.data) || !res.data.length) {
                    showError(config.emptyText);
                    return;
                }

                var rows = res.data;
                var count = rows.length;
                askConfirmation(config.confirmText(count), function () {
                    try {
                        exportToExcel(mode, rows, config);
                        showSuccess(config.successText(count));
                    } catch (error) {
                        showError(error && error.message ? error.message : config.failText);
                    }
                });
            }).fail(function () {
                showError(config.failText);
            });
        });
    }

    $(document).ready(function () {
        bindExportButton();
    });
})();
