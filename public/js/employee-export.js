(function () {
    'use strict';

    var INTERNAL_COLUMNS = [
        /* 1  */ { header: 'Employee Number',        key: 'employee_code' },
        /* 2  */ { header: 'Organization',           key: 'organization' },
        /* 3  */ { header: 'SBU',                    key: 'sbu' },
        /* 4  */ { header: 'Department',             key: 'department' },
        /* 5  */ { header: 'Role',                   key: 'role' },
        /* 6  */ { header: 'Grade',                  key: 'grade' },
        /* 7  */ { header: 'Designation',            key: 'designation' },
        /* 8  */ { header: 'Full Name',              key: 'full_name' },
        /* 9  */ { header: 'Date of Joining',        key: 'join_date' },
        /* 10 */ { header: 'CNIC',                   key: 'cnic' },
        /* 11 */ { header: 'CNIC Issue Date',        key: 'cnic_issue_date' },
        /* 12 */ { header: 'CNIC Expiry',            key: 'cnic_expiry' },
        /* 13 */ { header: 'Father Name',            key: 'father_name' },
        /* 14 */ { header: 'Father Deceased',        key: 'is_father_deceased' },
        /* 15 */ { header: 'Father CNIC',            key: 'father_cnic' },
        /* 16 */ { header: 'NTN',                    key: 'ntn' },
        /* 17 */ { header: 'Nationality',            key: 'nationality' },
        /* 18 */ { header: 'Gender',                 key: 'gender' },
        /* 19 */ { header: 'Date of Birth',          key: 'dob' },
        /* 20 */ { header: 'Religion',               key: 'religion' },
        /* 21 */ { header: 'Sect',                   key: 'sect' },
        /* 22 */ { header: 'Marital Status',         key: 'marital_status' },
        /* 23 */ { header: 'Spouse Name',            key: 'spouse_name' },
        /* 24 */ { header: 'Spouse CNIC',            key: 'spouse_cnic' },
        /* 25 */ { header: 'Spouse Nationality',     key: 'spouse_nationality' },
        /* 26 */ { header: 'Domicile District',      key: 'domicile_district' },
        /* 27 */ { header: 'Domicile Province',      key: 'domicile_province' },
        /* 28 */ { header: 'City of Birth',          key: 'city_of_birth' },
        /* 29 */ { header: 'Branch',                 key: 'branch' },
        /* 30 */ { header: 'Location',               key: 'location' },
        /* 31 */ { header: 'Employee Status',        key: 'employee_status' },
        /* 32 */ { header: 'Termination Reason',     key: 'termination_reason' },
        /* 33 */ { header: 'Termination Date',       key: 'termination_date' },
        /* 34 */ { header: 'Category',               key: 'employment_category' },
        /* 35 */ { header: 'Intern Type',            key: 'intern_type' },
        /* 36 */ { header: 'Intern Duration',        key: 'intern_duration' },
        /* 37 */ { header: 'Contractual Type',       key: 'contractual_type' },
        /* 38 */ { header: 'Contract Start Date',    key: 'contract_start_date' },
        /* 39 */ { header: 'Contract End Date',      key: 'contract_end_date' },
        /* 40 */ { header: 'Probation Start Date',   key: 'probation_start_date' },
        /* 41 */ { header: 'Probation End Date',     key: 'probation_end_date' },
        /* 42 */ { header: 'Engagement Mode',        key: 'engagement_mode' },
        /* 43 */ { header: 'Hybrid Days',            key: 'hybrid_days' },
        /* 44 */ { header: 'TAS ID',                 key: 'biometric_id' },
        /* 45 */ { header: 'Floor Access',           key: 'floor_access' },
        /* 46 */ { header: 'Police Verification',    key: 'verification_status' },
        /* 47 */ { header: 'MSR Letter No',          key: 'msr_letter_no' },
        /* 48 */ { header: 'MSR Date',               key: 'msr_date' },
        /* 49 */ { header: 'Armed Rank',             key: 'armed_rank' },
        /* 50 */ { header: 'Armed Joining Date',     key: 'armed_joining_date' },
        /* 51 */ { header: 'Armed Retirement Date',  key: 'armed_retirement_date' },
        /* 52 */ { header: 'Bank Name',              key: 'bank_name' },
        /* 53 */ { header: 'Account Title',          key: 'account_title' },
        /* 54 */ { header: 'Account No',             key: 'account_no' },
        /* 55 */ { header: 'IBAN',                   key: 'iban' },
        /* 56 */ { header: 'Branch Code',            key: 'branch_code' },
        /* 57 */ { header: 'Branch Address',         key: 'branch_address' },
        /* 58 */ { header: 'Account Category',       key: 'account_category' },
        /* 59 */ { header: 'Account Type',           key: 'account_type' },
        /* 60 */ { header: 'Email',                  key: 'email' },
        /* 61 */ { header: 'Cell Number',            key: 'cell_no' },
        /* 62 */ { header: 'Residence Phone',        key: 'residence_phone' },
        /* 63 */ { header: 'Emergency Contact',      key: 'emergency_contact' },
        /* 64 */ { header: 'Present Address',        key: 'present_address' },
        /* 65 */ { header: 'Permanent Address',      key: 'permanent_address' },
        /* 66 */ { header: 'Family Members',         key: 'family_count' },
        /* 67 */ { header: 'Parents',                key: 'parents_count' },
        /* 68 */ { header: 'Kids',                   key: 'kids_count' },
        /* 69 */ { header: 'Son',                    key: 'son_count' },
        /* 70 */ { header: 'Daughter',               key: 'daughter_count' },
        /* 71 */ { header: 'NOK Name',               key: 'nok_name' },
        /* 72 */ { header: 'NOK Relation',           key: 'nok_relation' },
        /* 73 */ { header: 'NOK CNIC',               key: 'nok_cnic' },
        /* 74 */ { header: 'Latest Qualification',   key: 'latest_degree' },
        /* 75 */ { header: 'Institute',              key: 'latest_institute' },
        /* 76 */ { header: 'Has Certificates',       key: 'has_certificates' },
        /* 77 */ { header: 'Employee CNIC (Front)',            key: 'has_cnic_front' },
        /* 78 */ { header: 'Employee CNIC (Back)',             key: 'has_cnic_back' },
        /* 79 */ { header: "Father's CNIC",                   key: 'has_father_cnic' },
        /* 80 */ { header: 'NOK CNIC',                        key: 'has_nok_cnic' },
        /* 81 */ { header: 'CV / Resume',                     key: 'has_cv_resume' },
        /* 82 */ { header: 'Offer / Appointment Letter',      key: 'has_offer_letter' },
        /* 83 */ { header: 'Police Verification Letter',      key: 'has_police_verification' },
        /* 84 */ { header: 'Probation Evaluation Report',     key: 'has_probation_report' },
        /* 85 */ { header: 'Consultancy Agreement/Contract',  key: 'has_consultancy_agreement' },
        /* 86 */ { header: 'Parent/Guardian Consent Form',    key: 'has_guardian_consent' },
        /* 87 */ { header: 'Discharge/Retirement Order',      key: 'has_discharge_order' },
        /* 88 */ { header: 'Family Registration Certificate', key: 'has_family_certificate' },
        /* 89 */ { header: 'Academic Transcript',             key: 'has_academic_transcript' },
        /* 90 */ { header: 'Academic Degree',                 key: 'has_academic_degree' },
        /* 91 */ { header: 'Professional Certificate',        key: 'has_professional_cert' },
        /* 92 */ { header: 'Experience Letter',               key: 'has_experience_letter' },
        /* 93 */ { header: 'Salary Slip',                     key: 'has_salary_slip' },
        /* 94 */ { header: 'Medical Report/Certificate',      key: 'has_medical_report' },
        /* 95 */ { header: 'Previous Organization',  key: 'last_organization' },
        /* 96 */ { header: 'Previous Monthly Salary',key: 'last_salary' },
        /* 97 */ { header: 'Disability',             key: 'has_disability' },
        /* 98 */ { header: 'Disability Type',        key: 'disability_type' },
        /* 99 */ { header: 'Chronic Disease',        key: 'has_chronic_disease' },
        /* 100 */ { header: 'Reference Name',        key: 'ref_name' },
        /* 101 */ { header: 'Reference Contact',     key: 'ref_contact' },
        /* 102 */ { header: 'Employment Type',       key: 'employment_type' },
        /* 103 */ { header: 'Employee Type',         key: 'employee_type' }
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

    function formatCnic(value) {
        var raw = normalizeValue(value, '');
        if (!raw) return '';
        var digits = raw.replace(/\D/g, '');
        if (digits.length >= 13) {
            return digits.slice(0, 5) + '-' + digits.slice(5, 12) + '-' + digits.slice(12);
        }
        return raw;
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
            var floorNames = Array.isArray(row.assigned_floor_names)
                ? row.assigned_floor_names.filter(function (name) { return !!normalizeValue(name, ''); })
                : [];
            if (floorNames.length) {
                return floorNames.join(', ');
            }
            return normalizeValue(row.floor_access, 'No');
        }
        if (col.key === 'combined_grace_period') {
            var open = normalizeValue(row.opening_grace_period, '0');
            var close = normalizeValue(row.closing_grace_period, '0');
            return 'In: ' + open + 'm, Out: ' + close + 'm';
        }
        if (mode === 'outsourced' && col.key === 'attendance_access') {
            return row.attendance_access ? 'Granted' : 'Not Granted';
        }
        if (['cnic', 'cnic_number', 'nok_cnic', 'father_cnic', 'spouse_cnic'].indexOf(col.key) !== -1) {
            return formatCnic(row[col.key]);
        }
        return normalizeValue(row[col.key], '');
    }

    function buildSheetData(mode, rows, columns) {
        var header = columns.map(function (col) { return col.header.toUpperCase(); });
        var body = rows.map(function (row) {
            return columns.map(function (col) { return formatCellValue(mode, row, col); });
        });
        return [header].concat(body);
    }

    function computeColumnWidths(data, columns) {
        var widths = columns.map(function (col) { return { wch: Math.max(18, col.header.length + 6) }; });
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
