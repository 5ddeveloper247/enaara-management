<!-- Employee Monthly Detail Canvas -->
<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="employeeMonthlyDetailCanvas" aria-labelledby="employeeMonthlyDetailCanvasLabel" style="width: 800px;">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="employeeMonthlyDetailCanvasLabel">
            <i class="bi bi-calendar3 me-2"></i>Monthly Calendar Details
        </h5>
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
                <div class="row g-2">
                    <div class="col-auto">
                        <span class="badge bg-success px-2 py-1">Present</span>
                    </div>
                    <div class="col-auto">
                        <span class="badge bg-primary px-2 py-1">WFH</span>
                    </div>
                    <div class="col-auto">
                        <span class="badge bg-warning text-dark px-2 py-1">Outstation</span>
                    </div>
                    <div class="col-auto">
                        <span class="badge bg-danger px-2 py-1">Absent</span>
                    </div>
                    <div class="col-auto">
                        <span class="badge bg-info text-dark px-2 py-1">Half-day</span>
                    </div>
                    <div class="col-auto">
                        <span class="badge bg-info px-2 py-1">Leave</span>
                    </div>
                    <div class="col-auto">
                        <span class="badge bg-secondary px-2 py-1">Off</span>
                    </div>
                    <div class="col-auto">
                        <span class="badge bg-secondary px-2 py-1">Holiday</span>
                    </div>
                    <div class="col-auto">
                        <span class="badge bg-warning px-2 py-1">Late</span>
                    </div>
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

