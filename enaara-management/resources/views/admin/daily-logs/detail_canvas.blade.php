<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="dailyLogDetailCanvas" aria-labelledby="dailyLogDetailCanvasLabel">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="dailyLogDetailCanvasLabel">
            <i class="bi bi-clock-history me-2"></i>Daily Log Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <!-- Employee Information -->
        <div class="mb-4">
            <h6 class="mb-3">
                <i class="bi bi-person-circle me-2"></i>Employee Information
            </h6>
            <div class="d-flex align-items-center mb-3">
                <div class="user-avatar me-3" id="detailEmployeeAvatar">JD</div>
                <div>
                    <div class="fw-semibold" id="detailEmployeeName">John Doe</div>
                    <small class="opacity-75 text-white" id="detailEmployeeInfo">Sales - EMP-001</small>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Attendance Timeline -->
        <div class="mb-4">
            <h6 class="mb-3">
                <i class="bi bi-calendar-check me-2"></i>Attendance Timeline
            </h6>
            <div class="row g-3">
                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Check-In</small>
                        <div class="d-flex align-items-center mb-2">
                            <span class="status-dot bg-success me-2" id="detailCheckInStatus"></span>
                            <div class="fw-semibold" id="detailCheckInTime">09:00 AM</div>
                        </div>
                        <small class="opacity-50 text-white" id="detailCheckInStatusText">On-Time</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Check-Out</small>
                        <div class="d-flex align-items-center mb-2">
                            <span class="status-dot bg-success me-2" id="detailCheckOutStatus"></span>
                            <div class="fw-semibold" id="detailCheckOutTime">06:00 PM</div>
                        </div>
                        <small class="opacity-50 text-white" id="detailCheckOutStatusText">Normal</small>
                    </div>
                </div>
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Total Duration</small>
                        <div class="fw-bold fs-5" id="detailDuration">9h 0m</div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Device & Source Information -->
        <div class="mb-4">
            <h6 class="mb-3">
                <i class="bi bi-device-hdd me-2"></i>Device & Source
            </h6>
            <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-phone fs-4 me-3 text-primary"></i>
                    <div>
                        <div class="fw-semibold" id="detailSource">Mobile App</div>
                        <small class="opacity-50 text-white" id="detailDeviceInfo">iOS 16.5 • Safari 16.5</small>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-top" style="border-color: #ffffff1a !important;">
                    <small class="opacity-75 text-white d-block mb-1">Browser/App</small>
                    <div class="fw-semibold small" id="detailBrowser">Mobile Safari</div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Location Information -->
        <div class="mb-4">
            <h6 class="mb-3">
                <i class="bi bi-geo-alt me-2"></i>Location Details
            </h6>
            <div class="p-3 rounded-3 border mb-3" style="border-color: #ffffff1a !important;">
                <div id="detailLocationType" class="mb-3">
                    <!-- GPS Location -->
                    <div id="detailGPSLocation" style="display: none;">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-geo-alt-fill text-danger fs-5 me-2"></i>
                            <div>
                                <div class="fw-semibold" id="detailLocationAddress">Property Site 1, Rawalpindi, Pakistan</div>
                                <small class="opacity-50 text-white" id="detailCoordinates">33.5651, 73.0169</small>
                            </div>
                        </div>
                        <div id="detailLocationMap" style="height: 200px; border-radius: 8px; margin-top: 1rem;"></div>
                    </div>
                    <!-- IP Location -->
                    <div id="detailIPLocation" style="display: none;">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-hdd-network text-primary fs-5 me-2"></i>
                            <div>
                                <div class="fw-semibold" id="detailIPAddress">192.168.1.100</div>
                                <small class="opacity-50 text-white">Office Network</small>
                            </div>
                        </div>
                        <div class="mt-2">
                            <span class="badge bg-success" id="detailIPStatus">Office Network</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Verification & Flags -->
        <div class="mb-4">
            <h6 class="mb-3">
                <i class="bi bi-shield-check me-2"></i>Verification & Flags
            </h6>
            <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                <div class="mb-3">
                    <small class="opacity-75 text-white d-block mb-2">Status</small>
                    <span class="badge px-3 py-2 rounded-1 bg-success" id="detailFlag">Verified</span>
                </div>
                <div class="mt-3 pt-3 border-top" style="border-color: #ffffff1a !important;">
                    <small class="opacity-75 text-white d-block mb-2">Additional Information</small>
                    <ul class="list-unstyled mb-0 small opacity-75">
                        <li class="mb-1">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span id="detailVerification1">Geofence verified</span>
                        </li>
                        <li class="mb-1">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span id="detailVerification2">Time verified</span>
                        </li>
                        <li id="detailVerification3Li" style="display: none;">
                            <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                            <span id="detailVerification3">Location exception</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top" style="border-color: #ffffffab !important">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Close</button>
            <button type="button" class="btn btn-primary border-0 bg-primary-custom" id="editLogBtn">
                <i class="bi bi-pencil me-1"></i>Edit Log
            </button>
        </div>
    </div>
</div>

