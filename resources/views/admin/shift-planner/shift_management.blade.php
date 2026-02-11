<div class="row">
    <!-- Shift List -->
    <div class="col-lg-9">
        <div class="row g-4" id="shiftsGrid">
            <!-- Sample Shift Cards -->
            <div class="col-md-6 col-lg-4">
                <div class="card shift-card border-1 rounded-4 h-100" 
                     data-bs-toggle="offcanvas"
                     data-bs-target="#shiftDetailCanvas"
                     data-shift-id="1"
                     data-shift-name="Morning Shift"
                     data-shift-start="09:00"
                     data-shift-end="18:00"
                     data-clock-in-window="08:30"
                     data-clock-out-window="18:30"
                     data-grace-period="15"
                     data-break-time="60"
                     data-overtime-allowed="true"
                     data-overtime-trigger="8">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h6 class="mb-1 fw-semibold">Morning Shift</h6>
                                <small class="text-muted">09:00 - 18:00</small>
                            </div>
                            <span class="badge bg-success" style="font-size: 10px !important; padding: 4px 6px !important;">Active</span>
                        </div>
                        
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-clock me-2 text-main small"></i>
                                <small class="fw-semibold small">Clock-in Window: 08:30 - 09:00</small>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-hourglass-split me-2 text-main small"></i>
                                <small class="fw-semibold small">Grace Period: 15 mins</small>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-cup-straw me-2 text-main small"></i>
                                <small class="fw-semibold small">Break Time: 60 mins</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-arrow-repeat me-2 text-main small"></i>
                                <small class="fw-semibold small">OT Allowed: After 8h</small>
                            </div>
                        </div>

                        <div class="d-flex gap-2 pt-3 border-top">
                            <button type="button" class="btn btn-sm btn-outline-primary flex-fill edit-shift-btn"
                                    data-bs-toggle="offcanvas"
                                    data-bs-target="#addShiftCanvas"
                                    data-mode="edit"
                                    data-shift-id="1"
                                    onclick="event.stopPropagation();">
                                <i class="bi bi-pencil me-1"></i>Edit
                            </button>
                            <button type="button" class="btn btn-sm btn-primary flex-fill"
                                    data-shift-id="1"
                                    onclick="event.stopPropagation();">
                                <i class="bi bi-eye me-1"></i>View
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card shift-card border-1 rounded-4 h-100"
                     data-bs-toggle="offcanvas"
                     data-bs-target="#shiftDetailCanvas"
                     data-shift-id="2"
                     data-shift-name="Night Shift"
                     data-shift-start="18:00"
                     data-shift-end="06:00"
                     data-clock-in-window="17:30"
                     data-clock-out-window="06:30"
                     data-grace-period="15"
                     data-break-time="60"
                     data-overtime-allowed="true"
                     data-overtime-trigger="8">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h6 class="mb-1 fw-semibold">Night Shift</h6>
                                <small class="text-muted">18:00 - 06:00</small>
                            </div>
                            <span class="badge bg-success" style="font-size: 10px !important; padding: 4px 6px !important;">Active</span>
                        </div>
                        
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-clock me-2 text-main small"></i>
                                <small class="fw-semibold small">Clock-in Window: 17:30 - 18:00</small>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-hourglass-split me-2 text-main small"></i>
                                <small class="fw-semibold small">Grace Period: 15 mins</small>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-cup-straw me-2 text-main small"></i>
                                <small class="fw-semibold small">Break Time: 60 mins</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-arrow-repeat me-2 text-main small"></i>
                                <small class="fw-semibold small">OT Allowed: After 8h</small>
                            </div>
                        </div>

                        <div class="d-flex gap-2 pt-3 border-top">
                            <button type="button" class="btn btn-sm btn-outline-primary flex-fill edit-shift-btn"
                                    data-bs-toggle="offcanvas"
                                    data-bs-target="#addShiftCanvas"
                                    data-mode="edit"
                                    data-shift-id="2"
                                    onclick="event.stopPropagation();">
                                <i class="bi bi-pencil me-1"></i>Edit
                            </button>
                            <button type="button" class="btn btn-sm btn-primary flex-fill"
                                    data-shift-id="2"
                                    onclick="event.stopPropagation();">
                                <i class="bi bi-eye me-1"></i>View
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card shift-card border-1 rounded-4 h-100"
                     data-bs-toggle="offcanvas"
                     data-bs-target="#shiftDetailCanvas"
                     data-shift-id="3"
                     data-shift-name="Site Sales - Weekend"
                     data-shift-start="10:00"
                     data-shift-end="16:00"
                     data-clock-in-window="09:45"
                     data-clock-out-window="16:15"
                     data-grace-period="10"
                     data-break-time="30"
                     data-overtime-allowed="false"
                     data-overtime-trigger="0">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h6 class="mb-1 fw-semibold">Site Sales - Weekend</h6>
                                <small class="text-muted">10:00 - 16:00</small>
                            </div>
                            <span class="badge bg-success" style="font-size: 10px !important; padding: 4px 6px !important;">Active</span>
                        </div>
                        
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-clock me-2 text-main small"></i>
                                <small class="fw-semibold small">Clock-in Window: 09:45 - 10:00</small>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-hourglass-split me-2 text-main small"></i>
                                <small class="fw-semibold small">Grace Period: 10 mins</small>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-cup-straw me-2 text-main small"></i>
                                <small class="fw-semibold small">Break Time: 30 mins</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-x-circle me-2 text-muted small"></i>
                                <small class="fw-semibold small text-muted">OT Not Allowed</small>
                            </div>
                        </div>

                        <div class="d-flex gap-2 pt-3 border-top">
                            <button type="button" class="btn btn-sm btn-outline-primary flex-fill edit-shift-btn"
                                    data-bs-toggle="offcanvas"
                                    data-bs-target="#addShiftCanvas"
                                    data-mode="edit"
                                    data-shift-id="3"
                                    onclick="event.stopPropagation();">
                                <i class="bi bi-pencil me-1"></i>Edit
                            </button>
                            <button type="button" class="btn btn-sm btn-primary flex-fill"
                                    data-shift-id="3"
                                    onclick="event.stopPropagation();">
                                <i class="bi bi-trash me-1"></i>View
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
                    <label class="form-label small fw-semibold text-muted mb-2">Status</label>
                    <div class="bg-transparent">
                        <label class="list-group-item list-group-item-action border-0 px-0 py-1 cursor-pointer">
                            <input class="form-check-input me-2" type="checkbox" value="all" id="filterShiftStatusAll" checked>
                            All Status
                        </label>
                        <label class="list-group-item list-group-item-action border-0 px-0 py-1 cursor-pointer">
                            <input class="form-check-input me-2" type="checkbox" value="active" id="filterShiftStatusActive">
                            Active
                        </label>
                        <label class="list-group-item list-group-item-action border-0 px-0 py-1 cursor-pointer">
                            <input class="form-check-input me-2" type="checkbox" value="inactive" id="filterShiftStatusInactive">
                            Inactive
                        </label>
                    </div>
                </div>

                <!-- Overtime Filter -->
                <div class="mb-4">
                    <label class="form-label small fw-semibold text-muted mb-2">Overtime</label>
                    <div class="bg-transparent">
                        <label class="list-group-item list-group-item-action border-0 px-0 py-1 cursor-pointer">
                            <input class="form-check-input me-2" type="checkbox" value="all" id="filterOTAll" checked>
                            All Shifts
                        </label>
                        <label class="list-group-item list-group-item-action border-0 px-0 py-1 cursor-pointer">
                            <input class="form-check-input me-2" type="checkbox" value="allowed" id="filterOTAllowed">
                            OT Allowed
                        </label>
                        <label class="list-group-item list-group-item-action border-0 px-0 py-1 cursor-pointer">
                            <input class="form-check-input me-2" type="checkbox" value="not-allowed" id="filterOTNotAllowed">
                            OT Not Allowed
                        </label>
                    </div>
                </div>

                <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="clearShiftFiltersBtn">
                    <i class="bi bi-x-circle me-1"></i>Clear Filters
                </button>
            </div>
        </div>
    </div>
</div>

