(function () {
    'use strict';

    let employeeTable;
    const TABLE_CELL_WORD_LIMIT = 4;

    window.employeeFilters = window.employeeFilters || {
        employeeType: '',
        organization: '',
        sbu: '',
        department: '',
        name: '',
        cnic: '',
    };

    $(document).ready(function () {
        initializeDataTable();
        initializeEventHandlers();
        updateEmployeeStats();
    });

    function initializeDataTable() {
        employeeTable = initUserDataTable('#employeeTable', {
            pageLength: 10,
            lengthMenu: [
                [10, 25, 50, 100, 200],
                [10, 25, 50, 100, 200]
            ],
            processing: true,
            ajax: {
                url: window.employeeDataUrl,
                type: 'GET',
                data: function (d) {
                    d.filter_employee_type = window.employeeFilters.employeeType;
                    d.filter_organization  = window.employeeFilters.organization;
                    d.filter_sbu           = window.employeeFilters.sbu;
                    d.filter_department    = window.employeeFilters.department;
                    d.filter_name          = window.employeeFilters.name;
                    d.filter_cnic          = window.employeeFilters.cnic;
                },
                dataSrc: 'data',
            },
            columns: [{
                    data: 'full_name',
                    render: renderProfileColumn,
                    orderable: true
                }, // 0 Profile
                {
                    data: 'biometric_id',
                    render: renderBiometric,
                    orderable: true,
                    visible: false
                }, // 1 TAS ID
                {
                    data: 'id',
                    render: renderEmployeeId,
                    orderable: true,
                    visible: false
                }, // 2 Employee ID
                {
                    data: 'employee_code',
                    render: renderEmployeeNo,
                    orderable: true,
                    visible: false
                }, // 3 Employee No
                {
                    data: 'organization',
                    render: renderOrgStructure,
                    orderable: true
                }, // 4 Organization
                {
                    data: 'sbu',
                    render: renderSimple,
                    orderable: true,
                    visible: false
                }, // 5 SBU
                {
                    data: 'department',
                    render: renderSimple,
                    orderable: true,
                    visible: false
                }, // 6 Department
                {
                    data: 'employment_category',
                    render: renderCategory,
                    orderable: true
                }, // 7 Category
                {
                    data: 'cnic',
                    render: renderSimple,
                    orderable: true,
                    visible: false
                }, // 8 CNIC
                {
                    data: 'nationality',
                    render: renderSimple,
                    orderable: true,
                    visible: false
                }, // 9 Nationality
                {
                    data: 'gender',
                    render: renderGender,
                    orderable: true
                }, // 10 Gender
                {
                    data: 'join_date',
                    render: renderSimple,
                    orderable: false
                }, // 11 Date of Joining
                {
                    data: 'designation',
                    render: renderSimple,
                    orderable: true
                }, // 12 Designation
                {
                    data: 'verification_status',
                    render: renderVerificationStatus,
                    orderable: false
                }, // 13 Verification Status
                {
                    data: 'email',
                    render: renderEmail,
                    orderable: true,
                    visible: false
                }, // 14 Email
                {
                    data: 'cell_no',
                    render: renderSimple,
                    orderable: false,
                    visible: false
                }, // 15 Cell Number
                {
                    data: null,
                    render: renderSummary,
                    orderable: false,
                    visible: false
                }, // 16 Summary
                {
                    data: 'employment_type',
                    render: renderEmploymentType,
                    orderable: true,
                    visible: false
                }, // 17 Employment Type
                {
                    data: 'site',
                    render: renderSite,
                    orderable: true,
                    visible: false
                }, // 18 Site Assignment
                {
                    data: null,
                    render: renderVendor,
                    orderable: false,
                    visible: false
                }, // 19 Vendor
                {
                    data: 'sync_status',
                    render: renderSyncStatus,
                    orderable: true,
                    visible: false
                }, // 20 Sync Status
                {
                    data: 'floor_access',
                    render: renderFloorAccess,
                    orderable: true,
                    visible: false
                }, // 21 Floor Access
                {
                    data: null,
                    render: renderActions,
                    orderable: false,
                    className: 'text-end no-toggle'
                }, // 22 Actions
            ],
            order: [
                [2, 'desc']
            ],
            scrollX: false,
            responsive: false,
            columnDefs: [{
                    targets: [1, 2, 3],
                    responsivePriority: 1
                },
                {
                    targets: [0, 4, 6, 13],
                    responsivePriority: 2
                },
                {
                    targets: [5, 7, 8, 9, 10, 11, 12, 14, 15],
                    responsivePriority: 3
                },
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
                columns: [0, 4, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21],
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

    function normalizeValue(value, fallback) {
        fallback = fallback === undefined ? '-' : fallback;

        if (value === null || value === undefined) return fallback;

        if (typeof value === 'string') {
            var trimmed = value.trim();
            if (trimmed === '' || trimmed.toLowerCase() === 'null' || trimmed.toLowerCase() === 'undefined') {
                return fallback;
            }
            return trimmed;
        }

        return value;
    }

    function limitWords(value, maxWords) {
        var text = normalizeValue(value, '');
        if (!text) return '-';

        var words = String(text).trim().split(/\s+/);
        if (words.length <= maxWords) {
            return String(text).trim();
        }
        return words.slice(0, maxWords).join(' ') + '...';
    }

    function renderSimple(data) {
        data = normalizeValue(data);
        if (data === '-') return '<span class="text-muted">-</span>';
        var shortText = limitWords(data, TABLE_CELL_WORD_LIMIT);
        return '<span title="' + escAttr(data) + '">' + escHtml(shortText) + '</span>';
    }

    function renderOrgStructure(data, type, row) {
        var organization = normalizeValue(row.organization);
        var sbu = normalizeValue(row.sbu);
        var department = normalizeValue(row.department);
        var organizationShort = limitWords(organization, TABLE_CELL_WORD_LIMIT);
        var sbuShort = limitWords(sbu, TABLE_CELL_WORD_LIMIT);
        var departmentShort = limitWords(department, TABLE_CELL_WORD_LIMIT);

        if (organization === '-' && sbu === '-' && department === '-') {
            return '<span class="text-muted">-</span>';
        }

        return '<div class="small">' +
            '<div class="fw-semibold text-truncate" title="' + escAttr(organization === '-' ? '' : organization) + '">' + (organization === '-' ? '<span class="text-muted">-</span>' : escHtml(organizationShort)) + '</div>' +
            '<div class="text-muted text-truncate" title="' + escAttr(sbu === '-' ? '' : sbu) + '">SBU: ' + escHtml(sbuShort) + '</div>' +
            '<div class="text-muted text-truncate" title="' + escAttr(department === '-' ? '' : department) + '">Dept: ' + escHtml(departmentShort) + '</div>' +
            '</div>';
    }

    function renderEmployeeId(data) {
        data = normalizeValue(data);
        if (data === '-') return '<span class="text-muted">-</span>';
        return '<span class="badge bg-secondary px-2 rounded-1">#' + escHtml(data) + '</span>';
    }

    function renderEmployeeNo(data) {
        data = normalizeValue(data);
        if (data === '-') return '<span class="text-muted">-</span>';
        return '<span class="fw-semibold small text-primary">' + escHtml(data) + '</span>';
    }

    function renderCategory(data) {
        data = normalizeValue(data);
        if (data === '-') return '<span class="text-muted">-</span>';

        var colorMap = {
            'Permanent': 'bg-success',
            'Contract': 'bg-info',
            'Intern': 'bg-warning text-dark',
            'Third-party': 'bg-secondary',
            'Probation': 'bg-primary',
        };
        var cls = colorMap[data] || 'bg-secondary';

        return '<span class="badge px-2 rounded-1 ' + cls + '">' + escHtml(data) + '</span>';
    }

    function renderProfileColumn(data, type, row) {
        var fullName = normalizeValue(row.full_name);
        var role = normalizeValue(row.role);
        var employeeNo = normalizeValue(row.employee_code);
        var tasId = normalizeValue(row.biometric_id);
        var initials = normalizeValue(row.initials, '??');
        var photoUrl = normalizeValue(row.photo_url, '');

        var avatar = photoUrl && photoUrl !== '-' ?
            '<img src="' + escAttr(photoUrl) + '" alt="' + escAttr(fullName) + '" class="rounded-circle flex-shrink-0" style="width:36px;height:36px;object-fit:cover;">' :
            '<div class="user-avatar flex-shrink-0 d-flex align-items-center justify-content-center" style="width:36px;height:36px;font-size:0.75rem;">' + escHtml(initials) + '</div>';

        var nameHtml = fullName !== '-' ?
            escHtml(fullName) :
            '<span class="text-black font-bold">-</span>';

        var roleHtml = role !== '-' ?
            escHtml(role) :
            '<span class="text-muted">-</span>';

        var employeeNoHtml = employeeNo !== '-' ? escHtml(employeeNo) : '-';
        var tasIdHtml = tasId !== '-' ? escHtml(tasId) : '-';

        return '<div class="d-flex align-items-center gap-2 employee-profile-cell">' +
            avatar +
            '<div class="min-w-0 flex-grow-1">' +
            '<div class="employee-profile-name fw-semibold text-truncate" title="' + escAttr(fullName === '-' ? '' : fullName) + '">' + nameHtml + '</div>' +
            '<div class="employee-profile-role small text-muted text-truncate" title="' + escAttr(role === '-' ? '' : role) + '">' + roleHtml + '</div>' +
            '<div class="employee-profile-meta mt-1">' +
            '<span class="employee-meta-chip">' + employeeNoHtml + '</span>' +
            '<span class="employee-meta-chip">TAS: ' + tasIdHtml + '</span>' +
            '</div>' +
            '</div></div>';
    }

    function renderGender(data) {
        data = normalizeValue(data);
        if (data === '-') return '<span class="text-muted">-</span>';

        var icon = data === 'Male' ?
            'bi-gender-male text-primary' :
            data === 'Female' ?
            'bi-gender-female text-danger' :
            'bi-person';

        return '<span><i class="bi ' + icon + ' me-1"></i>' + escHtml(data) + '</span>';
    }

    function renderVerificationStatus(data) {
        data = normalizeValue(data);
        if (data === '-') return '<span class="text-muted">-</span>';

        var cls = data === 'Verified' ? 'bg-success' :
            data === 'Pending' ? 'bg-warning text-dark' :
            data === 'Rejected' ? 'bg-danger' :
            'bg-secondary';

        return '<span class="badge px-2 rounded-1 ' + cls + '">' + escHtml(data) + '</span>';
    }

    function renderEmail(data) {
        data = normalizeValue(data);
        if (data === '-') return '<span class="text-muted">-</span>';

        return '<a href="mailto:' + escAttr(data) + '" class="text-decoration-none small">' + escHtml(data) + '</a>';
    }

    function renderSummary(data, type, row) {
        var dept = normalizeValue(row.department, '');
        var code = normalizeValue(row.employee_code, '');
        var fullName = normalizeValue(row.full_name);
        var initials = normalizeValue(row.initials, '??');
        var photoUrl = normalizeValue(row.photo_url, '');
        var info = (dept && code) ? (dept + ' - ' + code) : (dept || code || '-');

        var avatar = photoUrl && photoUrl !== '-' ?
            '<img src="' + escAttr(photoUrl) + '" alt="' + escAttr(fullName) + '" class="user-avatar me-3" style="object-fit:cover;border-radius:50%;">' :
            '<div class="user-avatar me-3">' + escHtml(initials) + '</div>';

        return '<div class="d-flex align-items-center">' +
            avatar +
            '<div>' +
            '<div class="fw-semibold">' + escHtml(fullName) + '</div>' +
            '<small class="text-muted">' + escHtml(info) + '</small>' +
            '</div>' +
            '</div>';
    }

    function renderBiometric(data) {
        data = normalizeValue(data);
        if (data === '-') return '<span class="text-muted">-</span>';

        return '<span class="badge bg-info px-2 rounded-1">' + escHtml(data) + '</span>';
    }

    function renderEmploymentType(data) {
        data = normalizeValue(data);
        if (data === '-') return '<span class="text-muted">-</span>';

        if (data === 'Permanent') {
            return '<span class="badge px-2 rounded-1 bg-success">' + escHtml(data) + '</span>';
        }
        if (data === 'Contract') {
            return '<span class="badge px-2 rounded-1 bg-info">' + escHtml(data) + '</span>';
        }

        return '<span class="badge px-2 rounded-1" style="background-color:#9c27b0;color:white;">' + escHtml(data) + '</span>';
    }

    function renderSite(data) {
        data = normalizeValue(data);
        if (data === '-') return '<span class="text-muted">-</span>';

        return '<div class="fw-semibold small">' + escHtml(data) + '</div>';
    }

    function renderVendor(data, type, row) {
        var vendor = normalizeValue(row.vendor);
        if (vendor === '-') return '<span class="text-muted">-</span>';

        return '<span class="fw-semibold small">' + escHtml(vendor) + '</span>';
    }

    function renderSyncStatus(data) {
        data = normalizeValue(data);

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

    function renderActions(data, type, row) {
        var dept = normalizeValue(row.department, '');
        var code = normalizeValue(row.employee_code, '');
        var info = (dept && code) ? (dept + ' - ' + code) : (dept || code || '-');

        var summary = normalizeValue(row.summary, '');
        if (!summary) {
            summary = normalizeValue(row.full_name) !== '-' || code !== '' ?
                [normalizeValue(row.full_name, ''), code].filter(Boolean).join(' - ') || '-' :
                '-';
        }

        return '<button type="button"' +
            ' class="action-btn border-0 text-white btn-primary view-employee-btn"' +
            ' title="View Details"' +
            ' data-bs-toggle="offcanvas"' +
            ' data-bs-target="#employeeDetailCanvas"' +

            ' data-db-id="' + escAttr(normalizeValue(row.id, '')) + '"' +
            ' data-tas-id="' + escAttr(normalizeValue(row.biometric_id)) + '"' +
            ' data-employee-id="' + escAttr(normalizeValue(code)) + '"' +
            ' data-employee-name="' + escAttr(normalizeValue(row.full_name)) + '"' +
            ' data-employee-avatar="' + escAttr(normalizeValue(row.initials, '??')) + '"' +
            ' data-photo-url="' + escAttr(normalizeValue(row.photo_url, '')) + '"' +
            ' data-employee-info="' + escAttr(info) + '"' +
            ' data-organization="' + escAttr(normalizeValue(row.organization)) + '"' +
            ' data-sbu="' + escAttr(normalizeValue(row.sbu)) + '"' +
            ' data-department="' + escAttr(normalizeValue(row.department)) + '"' +
            ' data-employment-type="' + escAttr(normalizeValue(row.employment_type)) + '"' +
            ' data-employment-category="' + escAttr(normalizeValue(row.employment_category)) + '"' +
            ' data-employee-type="' + escAttr(normalizeValue(row.employee_type)) + '"' +
            ' data-biometric-id="' + escAttr(normalizeValue(row.biometric_id)) + '"' +
            ' data-sync-status="' + escAttr(normalizeValue(row.sync_status, 'Not Linked')) + '"' +
            ' data-site-assignment="' + escAttr(normalizeValue(row.site)) + '"' +
            ' data-vendor="' + escAttr(normalizeValue(row.vendor)) + '"' +
            ' data-floor-access="' + (row.floor_access ? '1' : '0') + '"' +
            ' data-verification-status="' + escAttr(normalizeValue(row.verification_status)) + '"' +
            ' data-email="' + escAttr(normalizeValue(row.email)) + '"' +
            ' data-cell="' + escAttr(normalizeValue(row.cell_no)) + '"' +
            ' data-cnic="' + escAttr(normalizeValue(row.cnic)) + '"' +
            ' data-nationality="' + escAttr(normalizeValue(row.nationality)) + '"' +
            ' data-gender="' + escAttr(normalizeValue(row.gender)) + '"' +
            ' data-join-date="' + escAttr(normalizeValue(row.join_date)) + '"' +
            ' data-designation="' + escAttr(normalizeValue(row.designation)) + '"' +
            ' data-summary="' + escAttr(summary) + '"' +
            '><i class="bi bi-eye"></i></button>';
    }

    function extractEmployeeData(button) {
        return {
            dbId: button.dataset.dbId || '',
            tasId: button.dataset.tasId || '-',
            id: button.dataset.employeeId || '-',
            name: button.dataset.employeeName || '-',
            avatar: button.dataset.employeeAvatar || '??',
            photoUrl: button.dataset.photoUrl || '',
            info: button.dataset.employeeInfo || '-',
            organization: button.dataset.organization || '-',
            sbu: button.dataset.sbu || '-',
            department: button.dataset.department || '-',
            employmentType: button.dataset.employmentType || '-',
            category: button.dataset.employmentCategory || '-',
            employeeType: button.dataset.employeeType || '-',
            biometricId: button.dataset.biometricId || '-',
            syncStatus: button.dataset.syncStatus || 'Not Linked',
            siteAssignment: button.dataset.siteAssignment || '-',
            vendor: button.dataset.vendor || '-',
            floorAccess: button.dataset.floorAccess === '1',
            verificationStatus: button.dataset.verificationStatus || '-',
            email: button.dataset.email || '-',
            cell: button.dataset.cell || '-',
            cnic: button.dataset.cnic || '-',
            nationality: button.dataset.nationality || '-',
            gender: button.dataset.gender || '-',
            joinDate: button.dataset.joinDate || '-',
            designation: button.dataset.designation || '-',
            summary: button.dataset.summary || '-',
        };
    }

    function populateEmployeeDetail(d) {
        var avatarEl = document.getElementById('detailEmployeeAvatar');
        if (avatarEl) {
            if (d.photoUrl && d.photoUrl !== '-') {
                avatarEl.innerHTML = '';
                avatarEl.style.backgroundImage = 'url(' + d.photoUrl + ')';
                avatarEl.style.backgroundSize = 'cover';
                avatarEl.style.backgroundPosition = 'center';
                avatarEl.style.color = 'transparent';
                avatarEl.textContent = '';
            } else {
                avatarEl.style.backgroundImage = '';
                avatarEl.style.backgroundSize = '';
                avatarEl.style.backgroundPosition = '';
                avatarEl.style.color = '';
                avatarEl.textContent = d.avatar || '??';
            }
        }

        $('#detailEmployeeName').text(normalizeValue(d.name));
        $('#detailEmployeeInfo').text(normalizeValue(d.info));
        $('#detailTasId').text(normalizeValue(d.tasId));
        $('#detailEmployeeId').text(normalizeValue(d.id));
        $('#detailEmployeeNo').text(normalizeValue(d.id));
        $('#detailOrganization').text(normalizeValue(d.organization));
        $('#detailSbu').text(normalizeValue(d.sbu));
        $('#detailDepartment').text(normalizeValue(d.department));
        $('#detailCnic').text(normalizeValue(d.cnic));
        $('#detailNationality').text(normalizeValue(d.nationality));
        $('#detailGender').text(normalizeValue(d.gender));
        $('#detailDateOfJoining').text(normalizeValue(d.joinDate));
        $('#detailDesignation').text(normalizeValue(d.designation));
        $('#detailEmail').text(normalizeValue(d.email));
        $('#detailCellNumber').text(normalizeValue(d.cell));
        $('#detailSummary').text(normalizeValue(d.summary));
        $('#editEmployeeBtn').attr('data-employee-id', d.dbId);

        var verification = normalizeValue(d.verificationStatus);
        var verificationCls = verification === 'Verified' ?
            'bg-success' :
            verification === 'Pending' ?
            'bg-warning text-dark' :
            verification === 'Rejected' ?
            'bg-danger' :
            'bg-secondary';

        $('#detailVerificationStatus').html(
            verification === '-' ?
            '<span class="text-muted small">-</span>' :
            '<span class="badge px-2 rounded-1 ' + verificationCls + '">' + escHtml(verification) + '</span>'
        );

        var catColorMap = {
            'Permanent': 'bg-success',
            'Contract': 'bg-info',
            'Intern': 'bg-warning text-dark',
            'Third-party': 'bg-secondary',
            'Probation': 'bg-primary',
        };
        var catCls = catColorMap[d.category] || 'bg-secondary';
        var catLabel = normalizeValue(d.category);

        $('#detailEmploymentType').html(
            catLabel === '-' ?
            '<span class="text-muted small">-</span>' :
            '<span class="badge px-2 rounded-1 ' + catCls + '">' + escHtml(catLabel) + '</span>'
        );

        var catColorMap = {
            'Permanent': 'bg-success',
            'Contract': 'bg-info',
            'Intern': 'bg-warning text-dark',
            'Third-party': 'bg-secondary',
            'Probation': 'bg-primary',
        };
        var catCls = catColorMap[d.category] || 'bg-secondary';
        var catLabel = normalizeValue(d.category);

        $('#detailCategory').html(
            catLabel === '-' ?
            '<span class="text-muted small">-</span>' :
            '<span class="badge px-2 rounded-1 ' + catCls + '">' + escHtml(catLabel) + '</span>'
        );

        var employeeType = normalizeValue(d.employeeType);
        var typeClass = employeeType === 'Internal' ? 'bg-primary' : employeeType === '-' ? 'bg-secondary' : 'bg-secondary';
        $('#detailEmployeeType').html(
            employeeType === '-' ?
            '<span class="text-muted small">-</span>' :
            '<span class="badge ' + typeClass + '">' + escHtml(employeeType) + '</span>'
        );

        if (d.biometricId && d.biometricId !== '-') {
            $('#detailBiometricId').text(d.biometricId);

            var syncClass = d.syncStatus === 'Synced' ?
                'bg-success' :
                d.syncStatus === 'Pending' ?
                'bg-warning' :
                d.syncStatus === 'Failed' ?
                'bg-danger' :
                'bg-secondary';

            var syncIcon = d.syncStatus === 'Synced' ?
                'bi-check-circle' :
                d.syncStatus === 'Pending' ?
                'bi-clock-history' :
                d.syncStatus === 'Failed' ?
                'bi-x-circle' :
                'bi-dash-circle';

            $('#detailBiometricStatus').html(
                '<span class="badge px-3 py-2 rounded-1 ' + syncClass + '"><i class="bi ' + syncIcon + ' me-1"></i>' + escHtml(d.syncStatus) + '</span>'
            );

            $('#detailSyncStatusText').text(
                d.syncStatus === 'Synced' ?
                'Successfully synced with biometric system' :
                d.syncStatus === 'Pending' ?
                'Sync pending — not yet synchronized' :
                d.syncStatus === 'Failed' ?
                'Sync failed — please check biometric device' :
                'No biometric device linked'
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
            $('#detailVendor').text('-');
        }

        if (d.floorAccess) {
            $('#detailFloorAccess').html('<span class="badge bg-primary"><i class="bi bi-building me-1"></i>10th Floor</span>');
        } else {
            $('#detailFloorAccess').html('<span class="badge bg-secondary">No Access</span>');
        }

        $('#detailCurrentStatus').html('<span class="badge px-3 py-2 rounded-1 bg-success"><i class="bi bi-check-circle me-1"></i>Active</span>');
        $('#detailStatusInfo1').text('Employee is active and working');
        $('#detailStatusInfo2').text(d.biometricId !== '-' ? 'Biometric device linked' : 'No biometric device');

        if ($('#userAccountEmail').length) {
            $('#userAccountEmail').text(normalizeValue(d.email));
        }
        if ($('#userAccountRole').length) {
            $('#userAccountRole').text('-');
        }
        if ($('#userAccountLastLogin').length) {
            $('#userAccountLastLogin').text('-');
        }

        if ($('#noUserAccountSection').length && $('#hasUserAccountSection').length) {
            $('#noUserAccountSection').show();
            $('#hasUserAccountSection').hide();
        }
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
            window.employeeFilters.organization = ($('#filterOrganization').val() || '').trim();
            window.employeeFilters.sbu          = ($('#filterSbu').val() || '').trim();
            window.employeeFilters.department   = ($('#filterDepartment').val() || '').trim();
            window.employeeFilters.name         = ($('#filterName').val() || '').trim();
            window.employeeFilters.cnic         = ($('#filterCnic').val() || '').trim();

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
            $('#filterOrganization').val('');
            $('#filterSbu').val('');
            $('#filterDepartment').val('');
            $('#filterName').val('');
            $('#filterCnic').val('');

            window.employeeFilters.employeeType = '';
            window.employeeFilters.organization = '';
            window.employeeFilters.sbu          = '';
            window.employeeFilters.department   = '';
            window.employeeFilters.name         = '';
            window.employeeFilters.cnic         = '';

            if (employeeTable) {
                employeeTable.ajax.reload(null, false);
            }
        });

        $('#exportBtn').on('click', function () {
            var f = window.employeeFilters || {};
            var params = {
                filter_employee_type: f.employeeType || '',
                filter_organization: f.organization || '',
                filter_sbu: f.sbu || '',
                filter_department: f.department || '',
                filter_name: f.name || '',
                filter_cnic: f.cnic || '',
            };

            $.get(window.employeeDataUrl, params, function (res) {
                if (!res || !res.success || !res.data || !res.data.length) {
                    alert('No employee data to export.');
                    return;
                }

                exportEmployeesToCsv(res.data);

                if (window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Export Successful',
                        text: 'Employee records have been exported successfully.',
                        confirmButtonColor: '#012445'
                    });
                } else {
                    alert('Employee records have been exported successfully.');
                }
            }).fail(function () {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Export Failed',
                        text: 'Could not export employees. Please try again.',
                        confirmButtonColor: '#dc3545'
                    });
                } else {
                    alert('Could not export employees. Please try again.');
                }
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
            'Vendor'
        ];

        var lines = [headers.join(',')];

        rows.forEach(function (r) {
            lines.push([
                csvEscape(normalizeValue(r.employee_code, '')),
                csvEscape(normalizeValue(r.full_name, '')),
                csvEscape(normalizeValue(r.organization, '')),
                csvEscape(normalizeValue(r.sbu, '')),
                csvEscape(normalizeValue(r.department, '')),
                csvEscape(normalizeValue(r.employment_category, '')),
                csvEscape(normalizeValue(r.cnic, '')),
                csvEscape(normalizeValue(r.nationality, '')),
                csvEscape(normalizeValue(r.gender, '')),
                csvEscape(normalizeValue(r.join_date, '')),
                csvEscape(normalizeValue(r.designation, '')),
                csvEscape(normalizeValue(r.verification_status, '')),
                csvEscape(normalizeValue(r.email, '')),
                csvEscape(normalizeValue(r.cell_no, '')),
                csvEscape(normalizeValue(r.employment_type, '')),
                csvEscape(normalizeValue(r.employee_type, '')),
                csvEscape(normalizeValue(r.biometric_id, '')),
                csvEscape(normalizeValue(r.sync_status, '')),
                csvEscape(normalizeValue(r.site, '')),
                csvEscape(normalizeValue(r.vendor, ''))
            ].join(','));
        });

        var blob = new Blob(['\ufeff' + lines.join('\r\n')], {
            type: 'text/csv;charset=utf-8;'
        });
        var a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'employees_' + new Date().toISOString().slice(0, 10) + '.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(a.href);
    }

    function initializeAddEmployeeCanvas() {}

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
        return String(str)
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

})();
