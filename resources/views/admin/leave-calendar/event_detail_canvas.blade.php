<!-- Event Detail Canvas -->
<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="eventDetailCanvas" aria-labelledby="eventDetailCanvasLabel" style="width: 500px;">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="eventDetailCanvasLabel">
            <i class="bi bi-calendar-event me-2"></i>Event Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <!-- Event Type Badge -->
        <div class="mb-4">
            <div id="eventTypeBadge" class="d-inline-block">
                <!-- Will be populated by JavaScript -->
            </div>
        </div>

        <!-- Event Information -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-info-circle me-2"></i>Event Information
            </h6>
            <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                <div class="mb-2">
                    <small class="opacity-75 text-white d-block mb-1">Event Title</small>
                    <div class="fw-semibold small" id="eventTitle">-</div>
                </div>
                <div class="mb-2">
                    <small class="opacity-75 text-white d-block mb-1">Date</small>
                    <div class="small" id="eventDate">-</div>
                </div>
                <div id="eventOrganizationSection" style="display: none;">
                    <small class="opacity-75 text-white d-block mb-1">Organization</small>
                    <div class="small" id="eventOrganization">-</div>
                </div>
                <div id="eventDepartmentSection" style="display: none;">
                    <small class="opacity-75 text-white d-block mb-1">Department</small>
                    <div class="small" id="eventDepartment">-</div>
                </div>
                <div id="eventReasonSection" style="display: none;">
                    <small class="opacity-75 text-white d-block mb-1">Reason</small>
                    <div class="small" id="eventReason">-</div>
                </div>
            </div>
        </div>

        <!-- Leave Statistics (for Departmental Leave) -->
        <div class="mb-4" id="leaveStatsSection" style="display: none;">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-people me-2"></i>Leave Statistics
            </h6>
            <div class="row g-3">
                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">On Leave</small>
                        <div class="fw-bold fs-5" id="leaveCount">-</div>
                        <small class="opacity-50 text-white">employees</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Total Staff</small>
                        <div class="fw-bold fs-5" id="totalStaff">-</div>
                        <small class="opacity-50 text-white">employees</small>
                    </div>
                </div>
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Percentage</small>
                        <div class="d-flex align-items-center">
                            <div class="progress flex-grow-1 me-2" style="height: 20px;">
                                <div class="progress-bar" id="leaveProgressBar" role="progressbar" style="width: 0%"></div>
                            </div>
                            <div class="fw-bold" id="leavePercentage">0%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Impact Level (for Departmental Leave) -->
        <div class="mb-4" id="impactLevelSection" style="display: none;">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-exclamation-triangle me-2"></i>Impact Level
            </h6>
            <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                <div id="impactLevelBadge">
                    <!-- Will be populated by JavaScript -->
                </div>
                <small class="opacity-75 text-white d-block mt-2" id="impactLevelDescription">-</small>
            </div>
        </div>

        <!-- Affected Employees List (for Departmental Leave) -->
        <div class="mb-4" id="affectedEmployeesSection" style="display: none;">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-person-check me-2"></i>Affected Employees
            </h6>
            <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                <div id="affectedEmployeesList">
                    <!-- Will be populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>
    <div class="offcanvas-footer border-top p-3" style="border-color: #ffffffab !important">
        <div class="d-flex justify-content-between w-100">
            <div id="holidayActions" style="display: none;">
                <button type="button" class="btn btn-outline-warning me-2" id="detailEditBtn">
                    <i class="bi bi-pencil me-1"></i>Edit
                </button>
                <button type="button" class="btn btn-outline-danger" id="detailDeleteBtn">
                    <i class="bi bi-trash me-1"></i>Delete
                </button>
            </div>
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Close</button>
        </div>
    </div>
</div>

