<!-- Employee Monthly Detail Canvas -->
<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="employeeMonthlyDetailCanvas" aria-labelledby="employeeMonthlyDetailCanvasLabel" style="width: 800px;">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <div class="flex-grow-1 me-3">
            <h5 class="offcanvas-title mb-2" id="employeeMonthlyDetailCanvasLabel">
                <i class="bi bi-calendar3 me-2"></i>Monthly Calendar Details
            </h5>
            <div class="detail-month-nav d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-light detail-month-nav-btn" id="detailMonthPrevBtn" aria-label="Previous month">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <span class="detail-month-label fw-semibold" id="detailMonthLabel">June 2026</span>
                <button type="button" class="btn btn-sm btn-outline-light detail-month-nav-btn" id="detailMonthNextBtn" aria-label="Next month">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <!-- Employee Summary -->
        <div class="mb-4">
            <div class="d-flex align-items-center mb-3">
                <div class="user-avatar me-3" id="detailEmployeeAvatar" style="width: 50px; height: 50px; font-size: 1.1rem;">AA</div>
                <div class="flex-grow-1">
                    <h5 class="fw-semibold mb-0" id="detailEmployeeName">Ahmed Ali</h5>
                    <small class="opacity-75 text-white" id="detailEmployeeInfo">EMP-0001 | Sales</small>
                    <div class="mt-1">
                        <span class="badge bg-info-subtle text-info px-2 py-1 rounded-1" id="detailEmployeeLocation" style="font-size: 0.7rem;">
                            <i class="bi bi-building me-1"></i>Rawalpindi - Floor 1
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Monthly Statistics -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-bar-chart me-2"></i>Monthly Statistics
            </h6>
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="p-3 rounded-3 border text-center" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-1">Total Days</small>
                        <div class="h5 mb-0 fw-bold" id="detailTotalDays">30</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 rounded-3 border text-center" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-1">Present</small>
                        <div class="h5 mb-0 fw-bold text-white" id="detailPresent">25</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 rounded-3 border text-center" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-1">Absent</small>
                        <div class="h5 mb-0 fw-bold text-danger" id="detailAbsent">3</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 rounded-3 border text-center" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-1">Half-days</small>
                        <div class="h5 mb-0 fw-bold text-warning" id="detailHalfDays">2</div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Monthly Calendar Grid -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-calendar-check me-2"></i>Daily Attendance Calendar
            </h6>
            <div class="calendar-legend mb-3 p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                <div class="d-flex flex-wrap align-items-center gap-3">
                    <span class="calendar-legend-item">
                        <span class="calendar-legend-swatch present" aria-hidden="true"></span>
                        <span>Present</span>
                    </span>
                    <span class="calendar-legend-item">
                        <span class="calendar-legend-swatch work-from-home" aria-hidden="true"></span>
                        <span>WFH</span>
                    </span>
                    <span class="calendar-legend-item">
                        <span class="calendar-legend-swatch outstation" aria-hidden="true"></span>
                        <span>Outstation</span>
                    </span>
                    <span class="calendar-legend-item">
                        <span class="calendar-legend-swatch scheduled" aria-hidden="true"></span>
                        <span>Upcoming</span>
                    </span>
                    <span class="calendar-legend-item">
                        <span class="calendar-legend-swatch absent" aria-hidden="true"></span>
                        <span>Absent</span>
                    </span>
                    <span class="calendar-legend-item">
                        <span class="calendar-legend-swatch half-day" aria-hidden="true"></span>
                        <span>Half-day</span>
                    </span>
                    <span class="calendar-legend-item">
                        <span class="calendar-legend-swatch leave" aria-hidden="true"></span>
                        <span>Leave</span>
                    </span>
                    <span class="calendar-legend-item">
                        <span class="calendar-legend-swatch off" aria-hidden="true"></span>
                        <span>Off</span>
                    </span>
                    <span class="calendar-legend-item">
                        <span class="calendar-legend-swatch holiday" aria-hidden="true"></span>
                        <span>Holiday</span>
                    </span>
                </div>
            </div>
            <div class="calendar-grid" id="monthlyCalendarGrid">
                <!-- Calendar will be populated via JavaScript -->
                <div class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-white" role="status">
                        <span class="visually-hidden">Loading calendar...</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Exceptions & Regularization -->
        <div class="mb-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <h6 class="mb-3 fw-semibold small">
                        <i class="bi bi-exclamation-triangle me-2"></i>Exceptions
                    </h6>
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <div class="mb-2">
                            <small class="opacity-75 text-white d-block mb-1">Late Arrivals</small>
                            <div class="fw-semibold" id="detailLateArrivals">5</div>
                        </div>
                        <div>
                            <small class="opacity-75 text-white d-block mb-1">Early Departures</small>
                            <div class="fw-semibold" id="detailEarlyDepartures">2</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <h6 class="mb-3 fw-semibold small">
                        <i class="bi bi-patch-check me-2"></i>Verification & Regularization
                    </h6>
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <div class="mb-2">
                            <small class="opacity-75 text-white d-block mb-1">Zone-2 Verification</small>
                            <div id="detailZone2Verification">
                                <span class="badge bg-info text-white">Verified</span>
                            </div>
                        </div>
                        <div>
                            <small class="opacity-75 text-white d-block mb-1">Regularization Records</small>
                            <div class="fw-semibold" id="detailRegularization">3</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top" style="border-color: #ffffffab !important">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Close</button>
            <button type="button" class="btn btn-light text-dark border-0" id="exportEmployeeReportBtn">
                <i class="bi bi-download me-1"></i>Export Report
            </button>
        </div>
    </div>
</div>

