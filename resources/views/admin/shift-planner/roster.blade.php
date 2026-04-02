<div class="row">
    <div class="col-12">
        <div class="card roster-card border-0 rounded-4 shadow-none">
            <div class="card-body p-0">
                <div class="roster-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <h6 class="mb-0 fw-semibold text-main d-flex align-items-center">
                            <span class="roster-header-icon"><i class="bi bi-calendar3"></i></span>
                            Roster Calendar
                        </h6>
                        <div class="roster-toolbar d-flex align-items-center gap-2">
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
                        </div>
                    </div>
                    
                    <div class="roster-legend d-flex flex-wrap align-items-center gap-3">
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
                                    <th class="roster-col-employee">Department / Employee</th>
                                </tr>
                            </thead>
                            <tbody id="rosterTableBody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
