<div class="row">
    <!-- Organization Grid -->
    <div class="col-lg-9">
        <!-- Organization Grid -->
        <div class="row g-4" id="organizationsGrid">
            <!-- Organization cards will be dynamically populated here -->
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
