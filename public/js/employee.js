(function () {
    'use strict';

    let employeeTable;

    $(document).ready(function () {
        initializeDataTable();
        initializeEventHandlers();
        updateEmployeeStats();
    });

    function initializeDataTable() {
        employeeTable = initUserDataTable('#employeeTable', {
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, 200], [10, 25, 50, 100, 200]],
            processing: true,
            ajax: {
                url: window.employeeDataUrl,
                type: 'GET',
                dataSrc: 'data',
            },
            columns: [
                { data: 'full_name',           render: renderProfileColumn,     orderable: true  }, // 0 Profile
                { data: 'biometric_id',        render: renderBiometric,         orderable: true  }, // 1 TAS ID
                { data: 'id',                  render: renderEmployeeId,        orderable: true,  visible: false }, // 2 Employee ID
                { data: 'employee_code',       render: renderEmployeeNo,        orderable: true  }, // 3 Employee No
                { data: 'organization',        render: renderSimple,            orderable: true  }, // 4 Organization
                { data: 'sbu',                 render: renderSimple,            orderable: true  }, // 5 SBU
                { data: 'department',          render: renderSimple,            orderable: true  }, // 6 Department
                { data: 'employment_category', render: renderCategory,          orderable: true  }, // 7 Category
                { data: 'cnic',                render: renderSimple,            orderable: true  }, // 8 CNIC
                { data: 'nationality',         render: renderSimple,            orderable: true  }, // 9 Nationality
                { data: 'gender',              render: renderGender,            orderable: true  }, // 10 Gender
                { data: 'join_date',           render: renderSimple,            orderable: true  }, // 11 Date of Joining
                { data: 'designation',         render: renderSimple,            orderable: true  }, // 12 Designation
                { data: 'verification_status', render: renderVerificationStatus, orderable: true }, // 13 Verification Status
                { data: 'email',               render: renderEmail,             orderable: true  }, // 14 Email
                { data: 'cell_no',             render: renderSimple,            orderable: true  }, // 15 Cell Number
                { data: null,                  render: renderSummary,           orderable: false, visible: false }, // 16 Summary
                { data: 'employment_type',     render: renderEmploymentType,    orderable: true,  visible: false }, // 17 Employment Type
                { data: 'site',                render: renderSite,              orderable: true,  visible: false }, // 18 Site Assignment
                { data: null,                  render: renderVendor,            orderable: false, visible: false }, // 19 Vendor
                { data: 'sync_status',         render: renderSyncStatus,        orderable: true,  visible: false }, // 20 Sync Status
                { data: 'floor_access',        render: renderFloorAccess,       orderable: true,  visible: false }, // 21 Floor Access
                { data: null,                  render: renderActions,           orderable: false, className: 'text-end no-toggle' }, // 22 Actions
            ],
            order: [[2, 'desc']],
            scrollX: false,
            responsive: false,
            columnDefs: [
                { targets: [1, 2, 3], responsivePriority: 1 },
                { targets: [0, 4, 6, 13], responsivePriority: 2 },
                { targets: [5, 7, 8, 9, 10, 11, 12, 14, 15], responsivePriority: 3 },
            ],
            language: {
                search: '',
                searchPlaceholder: 'Search employees...',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ employees',
                infoEmpty: 'No employees available',
                zeroRecords: 'No matching employees found',
                processing: '<div class="spinner-border spinner-border-sm text-secondary" role="status"></div> Loading...',
            },
            buttons: [{
                extend: 'colvis',
                text: 'Select Columns',
                className: 'btn btn-sm border-0 bg-main text-black',
                columns: [0, 1, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21],
            }],
            dom: '<"row px-4 py-3"<"col-md-6"l><"col-md-6 d-flex justify-content-end gap-2"<B><f>>>r<"employee-datatable-scroll"t><"row px-4 py-2"<"col-md-5"i><"col-md-7"p>>',
            drawCallback: function () {
                var gridBtn = document.getElementById('btnGridView');
                if (gridBtn && gridBtn.classList.contains('active') && typeof window.buildEmployeeGrid === 'function') {
                    window.buildEmployeeGrid();
                }
            },
        });
    }

    function renderSimple(data) {
        if (!data || data === '-') return '<span class="text-muted">-</span>';
        return escHtml(data);
    }

    function renderEmployeeId(data) {
        if (!data) return '<span class="text-muted">-</span>';
        return '<span class="badge bg-secondary px-2 rounded-1">#' + escHtml(data) + '</span>';
    }

    function renderEmployeeNo(data) {
        if (!data || data === '-') return '<span class="text-muted">-</span>';
        return '<span class="fw-semibold small text-primary">' + escHtml(data) + '</span>';
    }

    function renderCategory(data) {
        if (!data || data === '-') return '<span class="text-muted">-</span>';
        var colorMap = {
            'Permanent':   'bg-success',
            'Contract':    'bg-info',
            'Intern':      'bg-warning text-dark',
            'Third-party': 'bg-secondary',
            'Probation':   'bg-primary',
        };
        var cls = colorMap[data] || 'bg-secondary';
        return '<span class="badge px-2 rounded-1 ' + cls + '">' + escHtml(data) + '</span>';
    }

    function renderProfileColumn(data, type, row) {
        var avatar = row.photo_url
            ? '<img src="' + escAttr(row.photo_url) + '" alt="' + escAttr(row.full_name) + '" class="rounded-circle flex-shrink-0" style="width:36px;height:36px;object-fit:cover;">'
            : '<div class="user-avatar flex-shrink-0" style="width:36px;height:36px;font-size:0.75rem;">' + escHtml(row.initials) + '</div>';
        var nameHtml = row.full_name && row.full_name !== '-'
            ? escHtml(row.full_name)
            : '<span class="text-muted">-</span>';
        var roleHtml = row.role && row.role !== '-'
            ? escHtml(row.role)
            : '<span class="text-muted">-</span>';
        return '<div class="d-flex align-items-center gap-2 employee-profile-cell">' +
            avatar +
            '<div class="min-w-0 flex-grow-1">' +
            '<div class="employee-profile-name fw-semibold small text-truncate" title="' + escAttr(row.full_name || '') + '">' + nameHtml + '</div>' +
            '<div class="employee-profile-role small text-muted text-truncate" title="' + escAttr(row.role || '') + '">' + roleHtml + '</div>' +
            '</div></div>';
    }

    function renderGender(data) {
        if (!data || data === '-') return '<span class="text-muted">-</span>';
        var icon = data === 'Male' ? 'bi-gender-male text-primary' : data === 'Female' ? 'bi-gender-female text-danger' : 'bi-person';
        return '<span><i class="bi ' + icon + ' me-1"></i>' + escHtml(data) + '</span>';
    }

    function renderVerificationStatus(data) {
        if (!data || data === '-') return '<span class="text-muted">-</span>';
        var cls = data === 'Verified'   ? 'bg-success' :
                  data === 'Pending'    ? 'bg-warning text-dark' :
                  data === 'Rejected'   ? 'bg-danger' : 'bg-secondary';
        return '<span class="badge px-2 rounded-1 ' + cls + '">' + escHtml(data) + '</span>';
    }

    function renderEmail(data) {
        if (!data || data === '-') return '<span class="text-muted">-</span>';
        return '<a href="mailto:' + escAttr(data) + '" class="text-decoration-none small">' + escHtml(data) + '</a>';
    }

    function renderSummary(row) {
        var dept    = row.department !== '-' ? row.department : '';
        var code    = row.employee_code !== '-' ? row.employee_code : '';
        var info    = (dept && code) ? (dept + ' - ' + code) : (dept || code || '-');
        var avatar  = row.photo_url
            ? '<img src="' + escAttr(row.photo_url) + '" alt="' + escAttr(row.full_name) + '" class="user-avatar me-3" style="object-fit:cover;border-radius:50%;">'
            : '<div class="user-avatar me-3">' + escHtml(row.initials) + '</div>';
        return '<div class="d-flex align-items-center">' +
                   avatar +
                   '<div>' +
                       '<div class="fw-semibold">' + escHtml(row.full_name) + '</div>' +
                       '<small class="text-muted">' + escHtml(info) + '</small>' +
                   '</div>' +
               '</div>';
    }

    function renderBiometric(data) {
        if (data) {
            return '<span class="badge bg-info px-2 rounded-1">' + escHtml(data) + '</span>';
        }
        return '<span class="text-muted">-</span>';
    }

    function renderEmploymentType(data) {
        if (!data || data === '-') return '<span class="text-muted">-</span>';
        if (data === 'Permanent') {
            return '<span class="badge px-2 rounded-1 bg-success">' + escHtml(data) + '</span>';
        }
        if (data === 'Contract') {
            return '<span class="badge px-2 rounded-1 bg-info">' + escHtml(data) + '</span>';
        }
        return '<span class="badge px-2 rounded-1" style="background-color:#9c27b0;color:white;">' + escHtml(data) + '</span>';
    }

    function renderSite(data) {
        if (!data || data === '-') return '<span class="text-muted">-</span>';
        return '<div class="fw-semibold small">' + escHtml(data) + '</div>';
    }

    function renderVendor() {
        return '<span class="text-muted">-</span>';
    }

    function renderSyncStatus(data) {
        if (data === 'Synced') {
            return '<span class="badge px-2 rounded-1 bg-success"><i class="bi bi-check-circle me-1"></i>Synced</span>';
        }
        if (data === 'Pending') {
            return '<span class="badge px-2 rounded-1 bg-warning"><i class="bi bi-clock-history me-1"></i>Pending</span>';
        }
        if (data === 'Failed') {
            return '<span class="badge px-2 rounded-1 bg-danger"><i class="bi bi-x-circle me-1"></i>Failed</span>';
        }
        return '<span class="badge px-2 rounded-1 bg-secondary"><i class="bi bi-dash-circle me-1"></i>Not Linked</span>';
    }

    function renderFloorAccess(data) {
        if (data) {
            return '<span class="badge px-2 rounded-1 bg-primary"><i class="bi bi-building me-1"></i>10th Floor</span>';
        }
        return '<span class="text-muted small">-</span>';
    }

    function renderActions(row) {
        var dept    = row.department !== '-' ? row.department : '';
        var code    = row.employee_code !== '-' ? row.employee_code : '';
        var info    = (dept && code) ? (dept + ' - ' + code) : (dept || code || '-');
        return '<button type="button"' +
            ' class="action-btn border-0 text-white btn-primary view-employee-btn"' +
            ' title="View Details"' +
            ' data-bs-toggle="offcanvas"' +
            ' data-bs-target="#employeeDetailCanvas"' +
            ' data-db-id="'            + row.id                          + '"' +
            ' data-employee-id="'      + escAttr(code)                   + '"' +
            ' data-employee-name="'    + escAttr(row.full_name)          + '"' +
            ' data-employee-avatar="'  + escAttr(row.initials)           + '"' +
            ' data-photo-url="'        + escAttr(row.photo_url || '')    + '"' +
            ' data-employee-info="'    + escAttr(info)                   + '"' +
            ' data-department="'       + escAttr(row.department)         + '"' +
            ' data-employment-type="'  + escAttr(row.employment_type)    + '"' +
            ' data-employment-category="' + escAttr(row.employment_category || '-') + '"' +
            ' data-employee-type="'    + escAttr(row.employee_type)      + '"' +
            ' data-biometric-id="'     + escAttr(row.biometric_id || '-') + '"' +
            ' data-sync-status="'      + escAttr(row.sync_status)        + '"' +
            ' data-site-assignment="'  + escAttr(row.site)               + '"' +
            ' data-vendor="-"' +
            ' data-floor-access="'     + (row.floor_access ? '1' : '0') + '"' +
            '><i class="bi bi-eye"></i></button>';
    }

    function extractEmployeeData(button) {
        return {
            id:             button.dataset.employeeId     || '-',
            name:           button.dataset.employeeName   || '-',
            avatar:         button.dataset.employeeAvatar || '??',
            photoUrl:       button.dataset.photoUrl       || '',
            info:           button.dataset.employeeInfo   || '-',
            department:     button.dataset.department     || '-',
            employmentType: button.dataset.employmentType || '-',
            category:       button.dataset.employmentCategory || '-',
            employeeType:   button.dataset.employeeType   || '-',
            biometricId:    button.dataset.biometricId    || '-',
            syncStatus:     button.dataset.syncStatus     || 'Not Linked',
            siteAssignment: button.dataset.siteAssignment || '-',
            vendor:         button.dataset.vendor         || '-',
            floorAccess:    button.dataset.floorAccess    === '1',
            dbId:           button.dataset.dbId           || '',
        };
    }

    function populateEmployeeDetail(d) {
        var avatarEl = document.getElementById('detailEmployeeAvatar');
        if (d.photoUrl) {
            avatarEl.innerHTML = '';
            avatarEl.style.backgroundImage  = 'url(' + d.photoUrl + ')';
            avatarEl.style.backgroundSize   = 'cover';
            avatarEl.style.backgroundPosition = 'center';
            avatarEl.style.color            = 'transparent';
        } else {
            avatarEl.style.backgroundImage  = '';
            avatarEl.style.backgroundSize   = '';
            avatarEl.style.backgroundPosition = '';
            avatarEl.style.color            = '';
            avatarEl.textContent            = d.avatar;
        }
        $('#detailEmployeeName').text(d.name);
        $('#detailEmployeeInfo').text(d.info);
        $('#detailEmployeeId').text(d.id);
        $('#detailDepartment').text(d.department);
        $('#editEmployeeBtn').attr('data-employee-id', d.dbId);

        var etClass = d.employmentType === 'Permanent' ? 'bg-success'
                    : d.employmentType === 'Contract'  ? 'bg-info'
                    : 'bg-warning';
        $('#detailEmploymentType').html('<span class="badge ' + etClass + '">' + escHtml(d.employmentType) + '</span>');

        var catColorMap = {
            'Permanent':   'bg-success',
            'Contract':    'bg-info',
            'Intern':      'bg-warning text-dark',
            'Third-party': 'bg-secondary',
            'Probation':   'bg-primary',
        };
        var catCls = catColorMap[d.category] || 'bg-secondary';
        var catLabel = d.category && d.category !== '-' ? d.category : '-';
        $('#detailCategory').html(
            catLabel === '-'
                ? '<span class="text-muted small">-</span>'
                : '<span class="badge px-2 rounded-1 ' + catCls + '">' + escHtml(catLabel) + '</span>'
        );

        var typeClass = d.employeeType === 'Internal' ? 'bg-primary' : 'bg-secondary';
        $('#detailEmployeeType').html('<span class="badge ' + typeClass + '">' + escHtml(d.employeeType) + '</span>');

        if (d.biometricId && d.biometricId !== '-') {
            $('#detailBiometricId').text(d.biometricId);
            var syncClass = d.syncStatus === 'Synced' ? 'bg-success'
                          : d.syncStatus === 'Pending' ? 'bg-warning' : 'bg-danger';
            $('#detailBiometricStatus').html('<span class="badge px-3 py-2 rounded-1 ' + syncClass + '"><i class="bi bi-check-circle me-1"></i>' + escHtml(d.syncStatus) + '</span>');
            $('#detailSyncStatusText').text(
                d.syncStatus === 'Synced'  ? 'Successfully synced with biometric system' :
                d.syncStatus === 'Pending' ? 'Sync pending — not yet synchronized' :
                                             'Sync failed — please check biometric device'
            );
        } else {
            $('#detailBiometricId').text('-');
            $('#detailBiometricStatus').html('<span class="badge px-3 py-2 rounded-1 bg-secondary"><i class="bi bi-dash-circle me-1"></i>Not Linked</span>');
            $('#detailSyncStatusText').text('No biometric device linked');
        }

        $('#detailSiteAssignment').text(d.siteAssignment !== '-' ? d.siteAssignment : 'Not assigned');

        if (d.vendor && d.vendor !== '-') {
            $('#detailVendorContainer').show();
            $('#detailVendor').text(d.vendor);
        } else {
            $('#detailVendorContainer').hide();
        }

        if (d.floorAccess) {
            $('#detailFloorAccess').html('<span class="badge bg-primary"><i class="bi bi-building me-1"></i>10th Floor</span>');
        } else {
            $('#detailFloorAccess').html('<span class="badge bg-secondary">No Access</span>');
        }

        $('#detailCurrentStatus').html('<span class="badge px-3 py-2 rounded-1 bg-success"><i class="bi bi-check-circle me-1"></i>Active</span>');
        $('#detailStatusInfo1').text('Employee is active and working');
        $('#detailStatusInfo2').text(d.biometricId !== '-' ? 'Biometric device linked' : 'No biometric device');
    }

    function initializeEventHandlers() {
        $('#employeeTable tbody').on('click', 'tr', function (e) {
            if ($(e.target).closest('a, button, input, select, textarea, label').length) {
                return;
            }
            var btn = $(this).find('.view-employee-btn')[0];
            if (btn) {
                btn.click();
            }
        });

        const detailCanvas = document.getElementById('employeeDetailCanvas');
        if (detailCanvas) {
            detailCanvas.addEventListener('show.bs.offcanvas', function (event) {
                var btn = event.relatedTarget;
                if (!btn || !btn.classList.contains('view-employee-btn')) return;
                populateEmployeeDetail(extractEmployeeData(btn));
            });
        }

        $('#editUserAccountBtn').on('click', function () {
            console.log('Edit user account:', $('#detailEmployeeId').text());
        });

        $('#deactivateUserAccountBtn').on('click', function () {
            var name = $('#detailEmployeeName').text();
            if (confirm('Are you sure you want to deactivate the user account for ' + name + '?')) {
                console.log('Deactivate:', $('#detailEmployeeId').text());
            }
        });

        $('#editEmployeeBtn').on('click', function () {
            var dbId = $(this).attr('data-employee-id');
            if (dbId) {
                window.location.href = (window.employeeEditUrlBase || '/admin/employee') + '/' + dbId + '/edit';
            }
        });

        var createCanvas = document.getElementById('createUserAccountCanvas');
        if (createCanvas) {
            createCanvas.addEventListener('show.bs.offcanvas', function () {
                $('#createUserEmployeeName').text($('#detailEmployeeName').text());
                $('#createUserEmployeeId').text($('#detailEmployeeId').text());
                $('#createUserDepartment').text($('#detailDepartment').text());
            });
            createCanvas.addEventListener('hidden.bs.offcanvas', function () {
                var form = document.getElementById('createUserAccountForm');
                if (form) form.reset();
            });
        }

        var createForm = document.getElementById('createUserAccountForm');
        if (createForm) {
            createForm.addEventListener('submit', function (e) {
                e.preventDefault();
                console.log('Create user account submitted');
            });
        }

        initializeAddEmployeeCanvas();

        $('#applyFiltersBtn').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            window.employeeFilters.employeeType = ($('#filterEmployeeType').val() || '').trim();
            window.employeeFilters.department = ($('#filterDepartment').val() || '').trim();
            window.employeeFilters.vendor = ($('#filterVendor').val() || '').trim();
            var dd = document.getElementById('filterDropdownBtn');
            if (dd && window.bootstrap && bootstrap.Dropdown) {
                bootstrap.Dropdown.getOrCreateInstance(dd).hide();
            }
            if (employeeTable) {
                employeeTable.ajax.reload(null, false);
            }
        });

        $('#clearFiltersBtn').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $('#filterEmployeeType').val('');
            $('#filterDepartment').val('');
            $('#filterVendor').val('');
            window.employeeFilters.employeeType = '';
            window.employeeFilters.department = '';
            window.employeeFilters.vendor = '';
            if (employeeTable) {
                employeeTable.ajax.reload(null, false);
            }
        });

        $('#exportBtn').on('click', function () {
            var params = {
                filter_employee_type: window.employeeFilters.employeeType || '',
                filter_department: window.employeeFilters.department || '',
                filter_vendor: window.employeeFilters.vendor || '',
            };
            $.get(window.employeeDataUrl, params, function (res) {
                if (!res || !res.success || !res.data || !res.data.length) {
                    alert('No employee data to export.');
                    return;
                }
                exportEmployeesToCsv(res.data);
            }).fail(function () {
                alert('Could not export employees. Please try again.');
            });
        });
    }

    function csvEscape(val) {
        if (val === null || val === undefined) {
            return '';
        }
        var s = String(val).replace(/"/g, '""');
        if (/[",\r\n]/.test(s)) {
            return '"' + s + '"';
        }
        return s;
    }

    function exportEmployeesToCsv(rows) {
        var headers = [
            'Employee No',
            'Full Name',
            'Organization',
            'SBU',
            'Department',
            'Category',
            'CNIC',
            'Nationality',
            'Gender',
            'Date of Joining',
            'Designation',
            'Verification Status',
            'Email',
            'Cell Number',
            'Employment Type',
            'Employee Type',
            'TAS ID',
            'Sync Status',
            'Site',
        ];
        var lines = [headers.join(',')];
        rows.forEach(function (r) {
            lines.push([
                csvEscape(r.employee_code),
                csvEscape(r.full_name),
                csvEscape(r.organization),
                csvEscape(r.sbu),
                csvEscape(r.department),
                csvEscape(r.employment_category),
                csvEscape(r.cnic),
                csvEscape(r.nationality),
                csvEscape(r.gender),
                csvEscape(r.join_date),
                csvEscape(r.designation),
                csvEscape(r.verification_status),
                csvEscape(r.email),
                csvEscape(r.cell_no),
                csvEscape(r.employment_type),
                csvEscape(r.employee_type),
                csvEscape(r.biometric_id),
                csvEscape(r.sync_status),
                csvEscape(r.site),
            ].join(','));
        });
        var blob = new Blob(['\ufeff' + lines.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
        var a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'employees_' + new Date().toISOString().slice(0, 10) + '.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(a.href);
    }

    function initializeAddEmployeeCanvas() {
    }

    function updateEmployeeStats() {
        $.get(window.employeeStatsUrl, function (res) {
            if (!res.success) return;
            var s = res.stats;

            $('#statTotalEmployees').text(s.total);
            $('#statActive').text(s.active);
            $('#statBiometricLinked').text(s.biometric_linked);
            $('#statPendingSync').text(s.pending_sync);
            $('#statInternal').text(s.internal);
            $('#statPermanent').text(s.permanent);
            $('#statContract').text(s.contract);
            $('#statOutsourced').text(s.outsourced);
            $('#statVendors').text(s.vendors);
            $('#statSynced').text(s.synced);
            $('#statPending').text(s.pending);
            $('#statFailed').text(s.failed);

            $('#totalWorkforceBadge').text(s.total);
            $('#internalStaffBadge').text(s.internal);
            $('#outsourcedStaffBadge').text(s.outsourced);
            $('#biometricSyncBadge').text(s.biometric_linked);
        });
    }

    function escHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function escAttr(str) {
        if (str === null || str === undefined) return '';
        return String(str).replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

})();
