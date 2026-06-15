<div class="row">
    <div class="col-12">
        <div class="card roster-card border-0 rounded-4 shadow-none">
            <div class="card-body p-0">
                <div class="roster-header d-flex flex-wrap align-items-center justify-content-between gap-2 gap-md-3 mb-4">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <h6 class="mb-0 fw-semibold text-main d-flex align-items-center">
                            <span class="roster-header-icon"><i class="bi bi-calendar3"></i></span>
                            Roster Calendar
                        </h6>
                        <div class="roster-toolbar d-flex align-items-center gap-2 flex-wrap">
                            <button type="button" class="btn btn-sm btn-outline-secondary roster-nav-btn border rounded-2" id="rosterPrevWeek" aria-label="Previous week">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <span class="roster-week-display fw-medium text-dark px-2 text-center" id="rosterWeekDisplay" style="min-width: 200px;"><span id="rosterWeekLabel">Week 1</span> <span class="text-muted fw-normal" id="rosterWeekDates">01 to 07</span></span>
                            <button type="button" class="btn btn-sm btn-outline-secondary roster-nav-btn border rounded-2" id="rosterNextWeek" aria-label="Next week">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                            <span class="roster-month-display text-muted small ms-1" id="rosterMonthYear">March 2025</span>
                            <button type="button" class="btn btn-sm btn-outline-primary ms-1 rounded-2" id="rosterTodayBtn">
                                <i class="bi bi-calendar-event me-1"></i>Today
                            </button>
                            <div id="rosterPersonnelSwitcher" class="d-flex align-items-center gap-1 ms-1 flex-wrap">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-2 roster-personnel-btn active" id="rosterInternalTab" role="tab" aria-selected="true">Internal Employees</button>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-2 roster-personnel-btn" id="rosterThirdPartyTab" role="tab" aria-selected="false">Third-Party Employees</button>
                            </div>
                            <div id="rosterDepartmentFilterWrap" class="d-flex align-items-center gap-1 ms-1">
                                <label for="rosterDepartmentFilter" class="small text-muted mb-0 text-nowrap">Department</label>
                                <select id="rosterDepartmentFilter" class="form-select form-select-sm roster-dept-filter-select rounded-2" aria-label="Filter by department">
                                    <option value="all">All</option>
                                </select>
                            </div>
                            <div class="form-check form-check-inline roster-show-deleted-wrap ms-1 mb-0">
                                <input class="form-check-input" type="checkbox" id="rosterShowDeletedShifts" value="1">
                                <label class="form-check-label small text-dark" for="rosterShowDeletedShifts">Show deleted shifts</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="roster-legend d-flex flex-wrap align-items-center gap-2 gap-md-3 ms-md-auto">
                        <span class="text-muted small me-1">Shifts:</span>
                        <span class="roster-legend-item roster-legend-morning"><i class="bi bi-sun-fill me-1"></i>Morning</span>
                        <span class="roster-legend-item roster-legend-evening"><i class="bi bi-cloud-sun-fill me-1"></i>Evening</span>
                        <span class="roster-legend-item roster-legend-night"><i class="bi bi-moon-stars-fill me-1"></i>Night</span>
                    </div>
                </div>
                <div class="roster-table-wrap rounded-3 overflow-hidden border">
                    <div class="table-responsive">
                        <table id="employeeTable" class="table table-hover roster-table align-middle mb-0">
                            <thead>
                                <tr class="roster-thead-row" id="rosterTheadRow">
                                    <th class="roster-col-toggle"> </th>
                                    <th class="roster-col-employee">Employee</th>
                                </tr>
                            </thead>
                            <tbody id="rosterTableBody">
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="rosterApplyApprovalBar" class="roster-apply-approval-bar d-none">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div>
                            <p class="mb-0 fw-semibold text-main">Draft roster ready for approval</p>
                            <p class="mb-0 small text-muted" id="rosterDraftPendingSummary">Pending shifts waiting to be submitted.</p>
                        </div>
                        <button type="button" class="btn btn-primary bg-main border-0 rounded-2" id="rosterApplyForApprovalBtn">
                            <i class="bi bi-send-check me-1"></i>Apply for Approval
                        </button>
                    </div>
                </div>

                <div id="rosterGmApprovalBar" class="roster-gm-approval-bar d-none">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div>
                            <p class="mb-0 fw-semibold text-main" id="rosterGmApprovalTitle">Review shift roster</p>
                            <p class="mb-0 small text-muted" id="rosterGmApprovalSummary">Approve or reject this roster request.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-danger rounded-2" id="rosterGmRejectBtn">
                                <i class="bi bi-x-lg me-1"></i>Reject
                            </button>
                            <button type="button" class="btn btn-success border-0 rounded-2" id="rosterGmApproveBtn">
                                <i class="bi bi-check-lg me-1"></i>Approve
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
