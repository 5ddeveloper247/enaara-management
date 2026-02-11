/**
 * Organization Module
 * Manage SBU (Strategic Business Units) and organizations
 */

(function() {
    'use strict';

    // ============================================
    // GLOBAL VARIABLES
    // ============================================
    let organizationsData = [];

    // ============================================
    // INITIALIZATION
    // ============================================
    $(document).ready(function() {
        loadOrganizationsData();
        renderOrganizations();
        updateCounters();
        initializeEventHandlers();
    });

    // ============================================
    // DATA LOADING
    // ============================================
    function loadOrganizationsData() {
        if (typeof ProjectData !== 'undefined' && ProjectData.organizations) {
            organizationsData = ProjectData.organizations.generateSampleData();
        } else {
            organizationsData = [];
        }
    }

    // ============================================
    // RENDER ORGANIZATIONS
    // ============================================
    function renderOrganizations() {
        const grid = $('#organizationsGrid');
        if (!grid.length) return;

        grid.empty();

        if (organizationsData.length === 0) {
            grid.html('<div class="col-12"><p class="text-center text-muted">No organizations found</p></div>');
            return;
        }

        organizationsData.forEach(org => {
            const card = buildOrganizationCard(org);
            grid.append(card);
        });
    }

    // ============================================
    // BUILD ORGANIZATION CARD
    // ============================================
    function buildOrganizationCard(org) {
        const statusBadge = getStatusBadge(org.subscriptionStatus);
        const adminStatusBadge = getStatusBadge(org.adminStatus, true);
        const logoHtml = org.logo 
            ? `<img src="${org.logo}" alt="Logo" class="" style="width: 45px; height: 45px; object-fit: contain;">`
            : `<div class="bg-main text-white rounded-3 d-flex align-items-center justify-content-center fw-bold" style="width: 45px; height: 45px; font-size: 1.1rem;">${org.logoPlaceholder}</div>`;

        return `
            <div class="col-md-6 col-lg-4">
                <div class="card organization-card border-1 rounded-3 h-100">
                    <div class="card-body p-4">
                        <!-- Organization Header -->
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3 bg-main rounded-2">
                                    ${logoHtml}
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-semibold small">${org.name}</h6>
                                    <small class="text-muted small">${org.registrationNumber}</small>
                                </div>
                            </div>
                            ${statusBadge}
                        </div>

                        <!-- Organization Identity -->
                        <div class="mb-3">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-building me-1 text-main small"></i>
                                <small class="fw-semibold small">${org.industry}</small>
                            </div>
                        </div>

                        <!-- Total Headcount -->
                        <div class="mb-2">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-people me-2 text-main"></i>
                                <span class="fw-semibold">${org.headcount} Staff</span>
                            </div>
                            <small class="text-muted">${org.departments} Departments</small>
                        </div>

                        <!-- Floors Information -->
                        <div class="mb-2">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-layers me-2 text-main"></i>
                                <span class="fw-semibold">${org.floors} Floors</span>
                            </div>
                            <small class="text-muted">
                                <span class="badge bg-info-subtle text-info px-2 py-1 rounded-1" style="font-size: 0.7rem;">
                                    <i class="bi bi-building me-1"></i>${org.floorsInfo}
                                </span>
                            </small>
                        </div>

                        <!-- Admin Assigned -->
                        <div class="d-flex align-items-center pt-3 border-top">
                            <div class="user-avatar me-3">${org.adminAvatar}</div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold small" style="line-height: 100%;">${org.adminName}</div>
                                <small class="text-muted">${org.adminRole}</small>
                            </div>
                            ${adminStatusBadge}
                        </div>

                        <!-- Action Button -->
                        <div class="mt-3 pt-3 border-top">
                            <button type="button" 
                                    class="btn btn-sm btn-outline-primary w-100 view-organization-btn"
                                    data-bs-toggle="offcanvas"
                                    data-bs-target="#organizationDetailCanvas"
                                    data-org-id="${org.id}"
                                    data-org-name="${org.name}"
                                    data-org-reg="${org.registrationNumber}"
                                    data-org-logo="${org.logo || ''}"
                                    data-org-logo-placeholder="${org.logoPlaceholder}"
                                    data-org-address="${org.address}"
                                    data-org-website="${org.website || ''}"
                                    data-org-headcount="${org.headcount}"
                                    data-org-departments="${org.departments}"
                                    data-admin-name="${org.adminName}"
                                    data-admin-email="${org.adminEmail}"
                                    data-admin-avatar="${org.adminAvatar}"
                                    data-admin-status="${org.adminStatus}"
                                    data-timezone="${org.timezone}"
                                    data-work-week="${org.workWeek}"
                                    data-attendance-radius="${org.attendanceRadius}"
                                    data-attendance-radius-unit="${org.attendanceRadiusUnit}"
                                    data-auth-method="${org.authMethod}"
                                    data-sso-provider="${org.ssoProvider || ''}"
                                    data-devices-count="${org.devicesCount}"
                                    data-subscription-status="${org.subscriptionStatus}"
                                    data-plan="${org.plan}"
                                    data-expiry-date="${org.expiryDate}">
                                <i class="bi bi-eye me-1"></i>View Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // ============================================
    // GET STATUS BADGE
    // ============================================
    function getStatusBadge(status, isSmall = false) {
        const sizeClass = isSmall ? 'small' : '';
        let badgeClass = 'bg-success';
        let icon = 'bi-check-circle';
        let text = 'Active';

        if (status === 'Pending') {
            badgeClass = 'bg-warning text-dark';
            icon = 'bi-clock-history';
            text = 'Pending';
        } else if (status === 'Suspended') {
            badgeClass = 'bg-danger';
            icon = 'bi-x-circle';
            text = 'Suspended';
        } else if (status === 'Expired') {
            badgeClass = 'bg-secondary';
            icon = 'bi-dash-circle';
            text = 'Expired';
        } else if (status === 'Inactive') {
            badgeClass = 'bg-secondary';
            icon = 'bi-x-circle';
            text = 'Inactive';
        }

        return `<small style="font-size: 10px !important; padding: 4px 6px !important" class="badge px-0 ${badgeClass} ${sizeClass}">${text}</small>`;
    }

    // ============================================
    // UPDATE COUNTERS
    // ============================================
    function updateCounters() {
        const totalOrgs = organizationsData.length;
        const globalHeadcount = organizationsData.reduce((sum, org) => sum + parseInt(org.headcount), 0);
        const totalDevices = organizationsData.reduce((sum, org) => sum + parseInt(org.devicesCount), 0);
        const activeOrgs = organizationsData.filter(org => org.subscriptionStatus === 'Active').length;
        const attendancePulse = activeOrgs > 0 ? Math.round((activeOrgs / totalOrgs) * 100) : 0;

        $('#totalOrganizations').text(totalOrgs);
        $('#globalHeadcount').text(globalHeadcount.toLocaleString());
        $('#biometricStatus').text(totalDevices);
        $('#attendencePulse').text(attendancePulse + '%');
    }

    // ============================================
    // EVENT HANDLERS
    // ============================================
    function initializeEventHandlers() {
        // Detail canvas handler
        const organizationDetailCanvas = document.getElementById('organizationDetailCanvas');
        if (organizationDetailCanvas) {
            organizationDetailCanvas.addEventListener('show.bs.offcanvas', function(event) {
                const button = event.relatedTarget;
                if (!button || !button.classList.contains('view-organization-btn')) return;

                // populateDetailCanvas(button);
            });
        }

        // Filter handlers
        $('.form-check-input[type="checkbox"]').on('change', function() {
            applyFilters();
        });

        $('#clearFiltersBtn').on('click', function() {
            clearFilters();
        });

        // Edit organization button
        $('#editOrganizationBtn').on('click', function() {
            const orgId = $(this).attr('data-org-id');
            // Placeholder for edit functionality
        });
    }

    // ============================================
    // POPULATE DETAIL CANVAS
    // ============================================
    // function populateDetailCanvas(button) {
    //     const orgData = {
    //         orgId: button.getAttribute('data-org-id') || '-',
    //         orgName: button.getAttribute('data-org-name') || '-',
    //         orgReg: button.getAttribute('data-org-reg') || '-',
    //         orgLogo: button.getAttribute('data-org-logo') || '',
    //         orgLogoPlaceholder: button.getAttribute('data-org-logo-placeholder') || '?',
    //         orgAddress: button.getAttribute('data-org-address') || '-',
    //         orgWebsite: button.getAttribute('data-org-website') || '-',
    //         orgHeadcount: button.getAttribute('data-org-headcount') || '0',
    //         orgDepartments: button.getAttribute('data-org-departments') || '0',
    //         adminName: button.getAttribute('data-admin-name') || '-',
    //         adminEmail: button.getAttribute('data-admin-email') || '-',
    //         adminAvatar: button.getAttribute('data-admin-avatar') || '?',
    //         adminStatus: button.getAttribute('data-admin-status') || 'Active',
    //         timezone: button.getAttribute('data-timezone') || '-',
    //         workWeek: button.getAttribute('data-work-week') || '-',
    //         attendanceRadius: button.getAttribute('data-attendance-radius') || '100',
    //         attendanceRadiusUnit: button.getAttribute('data-attendance-radius-unit') || 'meters',
    //         authMethod: button.getAttribute('data-auth-method') || 'Email/Password',
    //         ssoProvider: button.getAttribute('data-sso-provider') || '',
    //         devicesCount: button.getAttribute('data-devices-count') || '0',
    //         subscriptionStatus: button.getAttribute('data-subscription-status') || 'Active',
    //         plan: button.getAttribute('data-plan') || '-',
    //         expiryDate: button.getAttribute('data-expiry-date') || '-'
    //     };

    //     // Populate SBU identity
    //     $('#detailOrgName').text(orgData.orgName);
    //     $('#detailOrgRegNumber').text('Reg. No: ' + orgData.orgReg);
        
    //     if (orgData.orgLogo) {
    //         $('#detailOrgLogo').attr('src', orgData.orgLogo).show();
    //         $('#detailOrgLogoPlaceholder').hide();
    //     } else {
    //         $('#detailOrgLogo').hide();
    //         $('#detailOrgLogoPlaceholder').text(orgData.orgLogoPlaceholder).show();
    //     }

    //     // Populate basic information
    //     $('#detailOrgAddress').text(orgData.orgAddress);
    //     if (orgData.orgWebsite && orgData.orgWebsite !== '-') {
    //         const websiteUrl = orgData.orgWebsite.startsWith('http') ? orgData.orgWebsite : 'https://' + orgData.orgWebsite;
    //         $('#detailOrgWebsiteLink').attr('href', websiteUrl).text(orgData.orgWebsite);
    //     } else {
    //         $('#detailOrgWebsite').html('<span class="text-muted">Not provided</span>');
    //     }

    //     // Populate statistics
    //     $('#detailOrgHeadcount').text(orgData.orgHeadcount);
    //     $('#detailOrgDepartments').text(orgData.orgDepartments);

    //     // Populate admin assigned
    //     $('#detailAdminAvatar').text(orgData.adminAvatar);
    //     $('#detailAdminName').text(orgData.adminName);
    //     $('#detailAdminEmail').text(orgData.adminEmail);
        
    //     // Admin status badge
    //     const adminStatusBadge = getAdminStatusBadge(orgData.adminStatus);
    //     $('#detailAdminStatus').html(adminStatusBadge);

    //     // Populate configuration
    //     $('#detailOrgTimezone').text(orgData.timezone);
    //     $('#detailOrgWorkWeek').text(orgData.workWeek);
        
    //     // Format attendance radius
    //     const radiusValue = orgData.attendanceRadius || '100';
    //     const radiusUnit = orgData.attendanceRadiusUnit || 'meters';
    //     const radiusUnitLabel = radiusUnit === 'kilometers' ? 'Kilometers' : 'Meters';
    //     $('#detailOrgAttendanceRadius').html(`${radiusValue} ${radiusUnitLabel}`);

    //     // Populate authentication
    //     $('#detailOrgAuthMethod').text(orgData.authMethod);
        
    //     if (orgData.authMethod === 'SSO' && orgData.ssoProvider) {
    //         $('#detailOrgAuthBadge').html('<span class="badge px-3 py-2 rounded-1 bg-primary">SSO</span>');
    //         $('#detailSSOInfo').show();
    //         $('#detailSSOProvider').text(orgData.ssoProvider);
    //     } else {
    //         $('#detailOrgAuthBadge').html('<span class="badge px-3 py-2 rounded-1 bg-info">Standard</span>');
    //         $('#detailSSOInfo').hide();
    //     }

    //     // Populate biometric devices
    //     $('#detailOrgDevicesCount').text(orgData.devicesCount);

    //     // Populate subscription status
    //     const subscriptionBadge = getSubscriptionBadge(orgData.subscriptionStatus);
    //     $('#detailOrgSubscriptionStatus').html(subscriptionBadge);
    //     $('#detailOrgPlan').text(orgData.plan);
    //     $('#detailOrgExpiryDate').text(orgData.expiryDate);

    //     // Store SBU ID for edit button
    //     $('#editOrganizationBtn').attr('data-org-id', orgData.orgId);
    // }

    // ============================================
    // GET ADMIN STATUS BADGE
    // ============================================
    function getAdminStatusBadge(status) {
        if (status === 'Active') {
            return '<span class="badge px-3 py-2 rounded-1 bg-success"><i class="bi bi-check-circle me-1"></i>Active</span>';
        } else if (status === 'Pending') {
            return '<span class="badge px-3 py-2 rounded-1 bg-warning text-dark"><i class="bi bi-clock-history me-1"></i>Pending</span>';
        } else {
            return '<span class="badge px-3 py-2 rounded-1 bg-secondary"><i class="bi bi-x-circle me-1"></i>Inactive</span>';
        }
    }

    // ============================================
    // GET SUBSCRIPTION BADGE
    // ============================================
    function getSubscriptionBadge(status) {
        if (status === 'Active') {
            return '<span class="badge px-3 py-2 rounded-1 bg-success"><i class="bi bi-check-circle me-1"></i>Active</span>';
        } else if (status === 'Pending') {
            return '<span class="badge px-3 py-2 rounded-1 bg-warning text-dark"><i class="bi bi-clock-history me-1"></i>Pending</span>';
        } else if (status === 'Suspended') {
            return '<span class="badge px-3 py-2 rounded-1 bg-danger"><i class="bi bi-x-circle me-1"></i>Suspended</span>';
        } else {
            return '<span class="badge px-3 py-2 rounded-1 bg-secondary"><i class="bi bi-dash-circle me-1"></i>Expired</span>';
        }
    }
})();

