<div class="row">
    <!-- Organization Grid -->
    <div class="col-lg-9">
        <!-- Organization Grid -->
        <div class="row g-4" id="organizationsGrid">
            <!-- Sample Organization Cards -->
            <div class="col-md-6 col-lg-4">
                <div class="card organization-card border-1 rounded-3 h-100">
                    <div class="card-body p-4">
                        <!-- Organization Header -->
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3 bg-main rounded-2">
                                    <img src="{{asset('images/enaara-logo.png')}}" alt="Logo" class="" style="width: 45px; height: 45px; object-fit: contain;">
                                    {{-- <div class="bg-main text-white rounded-3 d-flex align-items-center justify-content-center fw-bold" style="width: 45px; height: 45px; font-size: 1.1rem;">EC</div> --}}
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-semibold small">Enaara Construction</h6>
                                    <small class="text-muted small">REG-2024-001</small>
                                </div>
                            </div>
                            <small style="font-size: 10px !important; padding: 4px 6px !important" class="badge px-0 bg-success">Active</small>
                        </div>

                        <!-- Organization Identity -->
                        <div class="mb-3">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-building me-1 text-main small"></i>
                                <small class="fw-semibold small">Construction & Real Estate</small>
                            </div>
                        </div>

                        <!-- Total Headcount -->
                        <div class="mb-2">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-people me-2 text-main"></i>
                                <span class="fw-semibold">450 Staff</span>
                            </div>
                            <small class="text-muted">12 Departments</small>
                        </div>

                        <!-- Admin Assigned -->
                        <div class="d-flex align-items-center pt-3 border-top">
                            <div class="user-avatar me-3">JD</div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold small" style="line-height: 100%;">John Doe</div>
                                <small class="text-muted">Admin / HR Manager</small>
                            </div>
                            <small style="font-size: 10px !important; padding: 4px 6px !important" class="badge px-0 bg-success small">Active</small>
                        </div>

                        <!-- Action Button -->
                        <div class="mt-3 pt-3 border-top">
                            <button type="button" 
                                    class="btn btn-sm btn-outline-primary w-100 view-organization-btn"
                                    data-bs-toggle="offcanvas"
                                    data-bs-target="#organizationDetailCanvas"
                                    data-org-id="1"
                                    data-org-name="Enaara Construction"
                                    data-org-reg="REG-2024-001"
                                    data-org-logo=""
                                    data-org-logo-placeholder="EC"
                                    data-org-address="123 Business District, Dubai, UAE"
                                    data-org-website="www.enaara.com"
                                    data-org-headcount="450"
                                    data-org-departments="12"
                                    data-admin-name="John Doe"
                                    data-admin-email="john.doe@enaara.com"
                                    data-admin-avatar="JD"
                                    data-admin-status="Active"
                                    data-timezone="Asia/Dubai (UTC+4)"
                                    data-work-week="Sunday - Thursday"
                                    data-auth-method="Email/Password"
                                    data-sso-provider=""
                                    data-devices-count="8"
                                    data-subscription-status="Active"
                                    data-plan="Enterprise"
                                    data-expiry-date="December 31, 2024">
                                <i class="bi bi-eye me-1"></i>View Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card organization-card border-1 rounded-3 h-100">
                    <div class="card-body p-4">
                        <!-- Organization Header -->
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3 bg-main rounded-2">
                                    <img src="{{asset('images/enaara-logo.png')}}" alt="Logo" class="" style="width: 45px; height: 45px; object-fit: contain;">
                                    {{-- <div class="bg-primary text-white rounded-3 d-flex align-items-center justify-content-center fw-bold" style="width: 45px; height: 45px; font-size: 1.1rem;">MSR</div> --}}
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-semibold small">MSR Group</h6>
                                    <small class="text-muted small">REG-2024-002</small>
                                </div>
                            </div>
                            <small style="font-size: 10px !important; padding: 4px 6px !important" class="badge px-0 bg-success">Active</small>
                        </div>

                        <!-- Organization Identity -->
                        <div class="mb-3">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-building me-1 text-main small"></i>
                                <small class="fw-semibold small">Property Development</small>
                            </div>
                        </div>

                        <!-- Total Headcount -->
                        <div class="mb-2">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-people me-2 text-main"></i>
                                <span class="fw-semibold">285 Staff</span>
                            </div>
                            <small class="text-muted">8 Departments</small>
                        </div>

                        <!-- Admin Assigned -->
                        <div class="d-flex align-items-center pt-3 border-top">
                            <div class="user-avatar me-3">SM</div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold small" style="line-height: 100%;">Sarah Miller</div>
                                <small class="text-muted">Admin / HR Manager</small>
                            </div>
                            <small style="font-size: 10px !important; padding: 4px 6px !important" class="badge px-0 bg-success small">Active</small>
                        </div>

                        <!-- Action Button -->
                        <div class="mt-3 pt-3 border-top">
                            <button type="button" 
                                    class="btn btn-sm btn-outline-primary w-100 view-organization-btn"
                                    data-bs-toggle="offcanvas"
                                    data-bs-target="#organizationDetailCanvas"
                                    data-org-id="2"
                                    data-org-name="MSR Group"
                                    data-org-reg="REG-2024-002"
                                    data-org-logo=""
                                    data-org-logo-placeholder="MSR"
                                    data-org-address="456 Commercial Avenue, Riyadh, Saudi Arabia"
                                    data-org-website="www.msrgroup.com"
                                    data-org-headcount="285"
                                    data-org-departments="8"
                                    data-admin-name="Sarah Miller"
                                    data-admin-email="sarah.miller@msrgroup.com"
                                    data-admin-avatar="SM"
                                    data-admin-status="Active"
                                    data-timezone="Asia/Riyadh (UTC+3)"
                                    data-work-week="Sunday - Thursday"
                                    data-auth-method="SSO"
                                    data-sso-provider="Azure AD"
                                    data-devices-count="5"
                                    data-subscription-status="Active"
                                    data-plan="Professional"
                                    data-expiry-date="November 30, 2024">
                                <i class="bi bi-eye me-1"></i>View Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card organization-card border-1 rounded-3 h-100">
                    <div class="card-body p-4">
                        <!-- Organization Header -->
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3 bg-main rounded-2">
                                    <img src="{{asset('images/enaara-logo.png')}}" alt="Logo" class="" style="width: 45px; height: 45px; object-fit: contain;">
                                    {{-- <div class="bg-info text-white rounded-3 d-flex align-items-center justify-content-center fw-bold" style="width: 45px; height: 45px; font-size: 1.1rem;">SW</div> --}}
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-semibold small">Swiss Builders</h6>
                                    <small class="text-muted small">REG-2024-003</small>
                                </div>
                            </div>
                            <small style="font-size: 10px !important; padding: 4px 6px !important" class="badge px-0 bg-warning text-dark">Pending</small>
                        </div>

                        <!-- Organization Identity -->
                        <div class="mb-3">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-building me-1 text-main small"></i>
                                <small class="fw-semibold small">Infrastructure</small>
                            </div>
                        </div>

                        <!-- Total Headcount -->
                        <div class="mb-2">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-people me-2 text-main"></i>
                                <span class="fw-semibold">120 Staff</span>
                            </div>
                            <small class="text-muted">5 Departments</small>
                        </div>

                        <!-- Admin Assigned -->
                        <div class="d-flex align-items-center pt-3 border-top">
                            <div class="user-avatar me-3">RK</div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold small" style="line-height: 100%;">Robert Kim</div>
                                <small class="text-muted">Admin / HR Manager</small>
                            </div>
                            <small style="font-size: 10px !important; padding: 4px 6px !important" class="badge px-0 bg-warning text-dark small">Pending</small>
                        </div>

                        <!-- Action Button -->
                        <div class="mt-3 pt-3 border-top">
                            <button type="button" 
                                    class="btn btn-sm btn-outline-primary w-100 view-organization-btn"
                                    data-bs-toggle="offcanvas"
                                    data-bs-target="#organizationDetailCanvas"
                                    data-org-id="3"
                                    data-org-name="Swiss Builders"
                                    data-org-reg="REG-2024-003"
                                    data-org-logo=""
                                    data-org-logo-placeholder="SW"
                                    data-org-address="789 Industrial Zone, Karachi, Pakistan"
                                    data-org-website="www.swissbuilders.com"
                                    data-org-headcount="120"
                                    data-org-departments="5"
                                    data-admin-name="Robert Kim"
                                    data-admin-email="robert.kim@swissbuilders.com"
                                    data-admin-avatar="RK"
                                    data-admin-status="Pending"
                                    data-timezone="Asia/Karachi (UTC+5)"
                                    data-work-week="Monday - Friday"
                                    data-auth-method="Email/Password"
                                    data-sso-provider=""
                                    data-devices-count="3"
                                    data-subscription-status="Pending"
                                    data-plan="Basic"
                                    data-expiry-date="October 31, 2024">
                                <i class="bi bi-eye me-1"></i>View Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Sidebar -->
    <div class="col-lg-3">
        <div class="card border-0 rounded-4">
            <div class="card-body p-4">
                <h6 class="fw-semibold mb-3">
                    <i class="bi bi-funnel me-2"></i>Filters
                </h6>

                <!-- Status Filter -->
                <div class="mb-4">
                    <label class="form-label small fw-semibold text-muted mb-2">Subscription Status</label>
                    <div class="bg-transparent">
                        <label class="list-group-item list-group-item-action border-0 px-0 py-2 cursor-pointer">
                            <input class="form-check-input me-2" type="checkbox" value="all" id="filterStatusAll" checked>
                            All Status
                        </label>
                        <label class="list-group-item list-group-item-action border-0 px-0 py-2 cursor-pointer">
                            <input class="form-check-input me-2" type="checkbox" value="active" id="filterStatusActive">
                            Active
                        </label>
                        <label class="list-group-item list-group-item-action border-0 px-0 py-2 cursor-pointer">
                            <input class="form-check-input me-2" type="checkbox" value="pending" id="filterStatusPending">
                            Pending
                        </label>
                        <label class="list-group-item list-group-item-action border-0 px-0 py-2 cursor-pointer">
                            <input class="form-check-input me-2" type="checkbox" value="suspended" id="filterStatusSuspended">
                            Suspended
                        </label>
                    </div>
                </div>

                <!-- Authentication Filter -->
                <div class="mb-4">
                    <label class="form-label small fw-semibold text-muted mb-2">Authentication Method</label>
                    <div class="bg-transparent">
                        <label class="list-group-item list-group-item-action border-0 px-0 py-2 cursor-pointer">
                            <input class="form-check-input me-2" type="checkbox" value="all" id="filterAuthAll" checked>
                            All Methods
                        </label>
                        <label class="list-group-item list-group-item-action border-0 px-0 py-2 cursor-pointer">
                            <input class="form-check-input me-2" type="checkbox" value="email_password" id="filterAuthEmail">
                            Email/Password
                        </label>
                        <label class="list-group-item list-group-item-action border-0 px-0 py-2 cursor-pointer">
                            <input class="form-check-input me-2" type="checkbox" value="sso" id="filterAuthSSO">
                            SSO
                        </label>
                    </div>
                </div>

                <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="clearFiltersBtn">
                    <i class="bi bi-x-circle me-1"></i>Clear Filters
                </button>
            </div>
        </div>
    </div>
</div>
