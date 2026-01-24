@extends('layouts.app')

@section('title', 'Organization - Admin Panel')

@section('page-title', 'Organization')

@push('styles')
    <!-- Organization Module CSS -->
    {{-- <link href="{{ asset('css/organization.css') }}" rel="stylesheet"> --}}

    <style>
        .btn {
            font-size: 13px;
        }

        .input-group {
            border: 1px solid var(--main-color) !important;
        }

        input:focus {
            box-shadow: none !important;
            border: 1px solid var(--main-color) !important;
        }

        .card .badge {
            font-weight: 500 !important;
            padding: .3rem .8rem !important;
            border-radius: 4px !important;
        }

        .table {
            --bs-table-bg: transparent !important;
        }

        th {
            padding: 1.3rem 2rem !important;
            color: var(--light-color) !important;
            white-space: nowrap !important;
        }

        td {
            padding: 1rem 2rem !important;
        }

        .dt-buttons {
            margin-top: 2px;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Top Header with Actions -->
        <div class="row align-items-center mb-3">
            <div class="col-md-6">
                <h5 class="mb-0">Organization Management</h5>
            </div>

            <div class="col-md-6 text-end">
                <button type="button" class="btn btn-outline-secondary me-2" data-bs-toggle="modal"
                    data-bs-target="#bulkPolicyModal">
                    <i class="bi bi-clipboard-data me-1"></i>Bulk Policy Update
                </button>
                <button type="button" class="btn btn-primary bg-main border-0" data-bs-toggle="offcanvas"
                    data-bs-target="#addOrganizationCanvas">
                    <i class="bi bi-building-add me-1"></i>Add New Organization
                </button>
            </div>
        </div>

        <!-- Summary Metrics Row -->
        @include('admin.organization.counters')

        <!-- Main Content Area with Sidebar Filter -->
        @include('admin.organization.organization_cards') 
    </div>

    <!-- Organization Detail Canvas -->
    @include('admin.organization.detail_canvas')

    <!-- Add Organization Canvas -->
    @include('admin.organization.add_organization_modal')

    <!-- Bulk Policy Update Modal -->
    @include('admin.organization.bulk_policy_modal')
@endsection

@push('styles')
    <!-- ApexCharts CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.44.0/dist/apexcharts.css">
@endpush

@push('scripts')
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- ApexCharts JS -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.44.0/dist/apexcharts.min.js"></script>
    
    <script>
        // Fix modal z-index issues by moving modals to body level
        document.addEventListener('DOMContentLoaded', function() {
            // Move all modals to body level to avoid stacking context issues
            const modals = document.querySelectorAll('.modal');
            modals.forEach(function(modal) {
                if (modal.parentElement !== document.body) {
                    document.body.appendChild(modal);
                }
            });

            // Fix z-index when modals are shown
            modals.forEach(function(modal) {
                modal.addEventListener('show.bs.modal', function() {
                    // Move to body if not already there
                    if (this.parentElement !== document.body) {
                        document.body.appendChild(this);
                    }
                });

                modal.addEventListener('shown.bs.modal', function() {
                    // Force z-index values
                    this.style.zIndex = '9999';
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) {
                        backdrop.style.zIndex = '9998';
                    }
                    const modalDialog = this.querySelector('.modal-dialog');
                    if (modalDialog) {
                        modalDialog.style.zIndex = '10000';
                    }
                });
            });
        });

        // Organization Detail Canvas Handler
        $(document).ready(function() {
            const organizationDetailCanvas = document.getElementById('organizationDetailCanvas');
            if (organizationDetailCanvas) {
                organizationDetailCanvas.addEventListener('show.bs.offcanvas', function(event) {
                    const button = event.relatedTarget;
                    if (!button || !button.classList.contains('view-organization-btn')) return;

                    // Extract data from button data attributes
                    const orgData = {
                        orgId: button.getAttribute('data-org-id') || '-',
                        orgName: button.getAttribute('data-org-name') || '-',
                        orgReg: button.getAttribute('data-org-reg') || '-',
                        orgLogo: button.getAttribute('data-org-logo') || '',
                        orgLogoPlaceholder: button.getAttribute('data-org-logo-placeholder') || '?',
                        orgAddress: button.getAttribute('data-org-address') || '-',
                        orgWebsite: button.getAttribute('data-org-website') || '-',
                        orgHeadcount: button.getAttribute('data-org-headcount') || '0',
                        orgDepartments: button.getAttribute('data-org-departments') || '0',
                        adminName: button.getAttribute('data-admin-name') || '-',
                        adminEmail: button.getAttribute('data-admin-email') || '-',
                        adminAvatar: button.getAttribute('data-admin-avatar') || '?',
                        adminStatus: button.getAttribute('data-admin-status') || 'Active',
                        timezone: button.getAttribute('data-timezone') || '-',
                        workWeek: button.getAttribute('data-work-week') || '-',
                        attendanceRadius: button.getAttribute('data-attendance-radius') || '100',
                        attendanceRadiusUnit: button.getAttribute('data-attendance-radius-unit') || 'meters',
                        authMethod: button.getAttribute('data-auth-method') || 'Email/Password',
                        ssoProvider: button.getAttribute('data-sso-provider') || '',
                        devicesCount: button.getAttribute('data-devices-count') || '0',
                        subscriptionStatus: button.getAttribute('data-subscription-status') || 'Active',
                        plan: button.getAttribute('data-plan') || '-',
                        expiryDate: button.getAttribute('data-expiry-date') || '-'
                    };

                    // Populate organization identity
                    $('#detailOrgName').text(orgData.orgName);
                    $('#detailOrgRegNumber').text('Reg. No: ' + orgData.orgReg);
                    
                    if (orgData.orgLogo) {
                        $('#detailOrgLogo').attr('src', orgData.orgLogo).show();
                        $('#detailOrgLogoPlaceholder').hide();
                    } else {
                        $('#detailOrgLogo').hide();
                        $('#detailOrgLogoPlaceholder').text(orgData.orgLogoPlaceholder).show();
                    }

                    // Populate basic information
                    $('#detailOrgAddress').text(orgData.orgAddress);
                    if (orgData.orgWebsite && orgData.orgWebsite !== '-') {
                        const websiteUrl = orgData.orgWebsite.startsWith('http') ? orgData.orgWebsite : 'https://' + orgData.orgWebsite;
                        $('#detailOrgWebsiteLink').attr('href', websiteUrl).text(orgData.orgWebsite);
                    } else {
                        $('#detailOrgWebsite').html('<span class="text-muted">Not provided</span>');
                    }

                    // Populate statistics
                    $('#detailOrgHeadcount').text(orgData.orgHeadcount);
                    $('#detailOrgDepartments').text(orgData.orgDepartments);

                    // Populate admin assigned
                    $('#detailAdminAvatar').text(orgData.adminAvatar);
                    $('#detailAdminName').text(orgData.adminName);
                    $('#detailAdminEmail').text(orgData.adminEmail);
                    
                    // Admin status badge
                    let adminStatusBadge = '';
                    if (orgData.adminStatus === 'Active') {
                        adminStatusBadge = '<span class="badge px-3 py-2 rounded-1 bg-success"><i class="bi bi-check-circle me-1"></i>Active</span>';
                    } else if (orgData.adminStatus === 'Pending') {
                        adminStatusBadge = '<span class="badge px-3 py-2 rounded-1 bg-warning text-dark"><i class="bi bi-clock-history me-1"></i>Pending</span>';
                    } else {
                        adminStatusBadge = '<span class="badge px-3 py-2 rounded-1 bg-secondary"><i class="bi bi-x-circle me-1"></i>Inactive</span>';
                    }
                    $('#detailAdminStatus').html(adminStatusBadge);

                    // Populate configuration
                    $('#detailOrgTimezone').text(orgData.timezone);
                    $('#detailOrgWorkWeek').text(orgData.workWeek);
                    
                    // Format attendance radius
                    const radiusValue = orgData.attendanceRadius || '100';
                    const radiusUnit = orgData.attendanceRadiusUnit || 'meters';
                    const radiusUnitLabel = radiusUnit === 'kilometers' ? 'Kilometers' : 'Meters';
                    $('#detailOrgAttendanceRadius').html(`${radiusValue} ${radiusUnitLabel}`);

                    // Populate authentication
                    $('#detailOrgAuthMethod').text(orgData.authMethod);
                    
                    if (orgData.authMethod === 'SSO' && orgData.ssoProvider) {
                        $('#detailOrgAuthBadge').html('<span class="badge px-3 py-2 rounded-1 bg-primary">SSO</span>');
                        $('#detailSSOInfo').show();
                        $('#detailSSOProvider').text(orgData.ssoProvider);
                    } else {
                        $('#detailOrgAuthBadge').html('<span class="badge px-3 py-2 rounded-1 bg-info">Standard</span>');
                        $('#detailSSOInfo').hide();
                    }

                    // Populate biometric devices
                    $('#detailOrgDevicesCount').text(orgData.devicesCount);
                    // TODO: Populate actual device list from API or data attribute
                    // For now, showing sample devices

                    // Populate subscription status
                    let subscriptionBadge = '';
                    if (orgData.subscriptionStatus === 'Active') {
                        subscriptionBadge = '<span class="badge px-3 py-2 rounded-1 bg-success"><i class="bi bi-check-circle me-1"></i>Active</span>';
                    } else if (orgData.subscriptionStatus === 'Pending') {
                        subscriptionBadge = '<span class="badge px-3 py-2 rounded-1 bg-warning text-dark"><i class="bi bi-clock-history me-1"></i>Pending</span>';
                    } else if (orgData.subscriptionStatus === 'Suspended') {
                        subscriptionBadge = '<span class="badge px-3 py-2 rounded-1 bg-danger"><i class="bi bi-x-circle me-1"></i>Suspended</span>';
                    } else {
                        subscriptionBadge = '<span class="badge px-3 py-2 rounded-1 bg-secondary"><i class="bi bi-dash-circle me-1"></i>Expired</span>';
                    }
                    $('#detailOrgSubscriptionStatus').html(subscriptionBadge);
                    $('#detailOrgPlan').text(orgData.plan);
                    $('#detailOrgExpiryDate').text(orgData.expiryDate);

                    // Store organization ID for edit button
                    $('#editOrganizationBtn').attr('data-org-id', orgData.orgId);
                });
            }

            // Handle edit organization button click
            $('#editOrganizationBtn').on('click', function() {
                const orgId = $(this).attr('data-org-id');
                console.log('Edit organization:', orgId);
                // Close canvas and open edit modal or navigate to edit page
                // You can implement edit functionality here
            });
        });

        // Organization management scripts
    </script>
@endpush
