<!-- Department Detail Side Canvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="departmentDetailCanvas" aria-labelledby="departmentDetailCanvasLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold" id="departmentDetailCanvasLabel">Department Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <!-- Department Info -->
        <div class="mb-4">
            <h4 class="fw-bold mb-1" id="canvasDeptName">Sales</h4>
            <p class="opacity-50 text-white fw-light mb-0" id="canvasDeptLocation">Head Office</p>
        </div>

        <!-- Sub-Departments/Teams Section -->
        <div>
            <h6 class="fw-semibold mb-3">
                <i class="bi bi-diagram-3 me-2"></i>Sub-Departments / Teams
            </h6>
            <div class="">
                <div class="list-group-item border-0 px-0 py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold small">Property Sales</div>
                            <small class="opacity-50 text-white fw-light">180 Staff</small>
                        </div>
                        <span class="badge rounded-1 px-3 py-1 fw-light bg-success">Active</span>
                    </div>
                </div>
                <div class="list-group-item border-0 px-0 py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold small">Leasing</div>
                            <small class="opacity-50 text-white fw-light">150 Staff</small>
                        </div>
                        <span class="badge rounded-1 px-3 py-1 fw-light bg-success">Active</span>
                    </div>
                </div>
                <div class="list-group-item border-0 px-0 py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold small">After-Sales</div>
                            <small class="opacity-50 text-white fw-light">120 Staff</small>
                        </div>
                        <span class="badge rounded-1 px-3 py-1 fw-light bg-success">Active</span>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <!-- Team Lead List -->
        <div class="mb-4">
            <h6 class="fw-semibold mb-3">
                <i class="bi bi-person-badge me-2"></i>Team Leads
            </h6>
            <div class="">
                <div class="list-group-item border-0 px-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="user-avatar me-3">JD</div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold small">Ahmed Ali</div>
                            <small class="opacity-50 text-white fw-light">Property Sales Manager</small>
                        </div>
                        <span class="badge rounded-1 px-3 py-1 fw-light bg-success">Online</span>
                    </div>
                </div>
                <div class="list-group-item border-0 px-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="user-avatar me-3">JS</div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold small">Fatima Khan</div>
                            <small class="opacity-50 text-white fw-light">Leasing Manager</small>
                        </div>
                        <span class="badge rounded-1 px-3 py-1 fw-light bg-success">Online</span>
                    </div>
                </div>
                <div class="list-group-item border-0 px-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="user-avatar me-3">MW</div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold small">Mike Wilson</div>
                            <small class="opacity-50 text-white fw-light">After-Sales Manager</small>
                        </div>
                        <span class="badge rounded-1 px-3 py-1 fw-light bg-secondary">Offline</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Heatmap -->
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-calendar-heatmap me-2"></i>Attendance Heatmap
                </h6>
                <small class="opacity-50 text-white fw-light">Last 4 Weeks</small>
            </div>
            <div id="attendanceHeatmapChart" style="min-height: 200px;"></div>
        </div>

        <!-- Shift Rules -->
        <div class="mb-4">
            <h6 class="fw-semibold mb-3">
                <i class="bi bi-clock me-2"></i>Shift Rules
            </h6>
            <div class="card bg-light border-0">
                <div class="card-body p-3">
                    <div class="mb-2">
                        <small class="opacity-50 text-white fw-light d-block">Standard Shift</small>
                        <div class="fw-semibold small">9:00 AM - 6:00 PM</div>
                    </div>
                    <div class="mb-2">
                        <small class="opacity-50 text-white fw-light d-block">Working Days</small>
                        <div class="fw-semibold small">Monday - Friday</div>
                    </div>
                    <div>
                        <small class="opacity-50 text-white fw-light d-block">Overtime Policy</small>
                        <div class="fw-semibold small">Approved by Manager</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="border-top pt-3">
            <h6 class="fw-semibold mb-3">Quick Actions</h6>
            <div class="d-grid gap-2">
                <button type="button" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-pencil me-1"></i>Edit Department
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-people me-1"></i>Manage Team
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-trash me-1"></i>Delete Department
                </button>
            </div>
        </div>
    </div>
</div>

