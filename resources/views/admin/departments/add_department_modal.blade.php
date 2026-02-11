<!-- Add Department Modal -->
<div class="modal fade" id="addDepartmentModal" tabindex="-1" aria-labelledby="addDepartmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="addDepartmentModalLabel">Add New Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addDepartmentForm">
                    <!-- Department Name -->
                    <div class="mb-3">
                        <label for="departmentName" class="form-label fw-semibold">Department Name</label>
                        <input type="text" class="form-control" id="departmentName" placeholder="e.g., Marketing" required>
                    </div>

                    <!-- Location -->
                    <div class="mb-3">
                        <label for="departmentLocation" class="form-label fw-semibold">Location</label>
                        <select class="form-select" id="departmentLocation" required>
                            <option value="">Select Location</option>
                            <option value="head-office">Head Office</option>
                            <option value="site-office">Site Office</option>
                        </select>
                    </div>

                    <!-- Department Head -->
                    <div class="mb-3">
                        <label for="departmentHead" class="form-label fw-semibold">Department Head</label>
                        <select class="form-select" id="departmentHead" required>
                            <option value="">Select Department Head</option>
                            <option value="1">Ahmed Ali</option>
                            <option value="2">Zainab Malik</option>
                            <option value="3">Bilal Ahmed</option>
                        </select>
                    </div>

                    <!-- Shift Rules -->
                    <div class="mb-3">
                        <label for="shiftStart" class="form-label fw-semibold">Shift Time</label>
                        <div class="row">
                            <div class="col-6">
                                <input type="time" class="form-control" id="shiftStart" value="09:00" required>
                                <small class="text-muted">Start Time</small>
                            </div>
                            <div class="col-6">
                                <input type="time" class="form-control" id="shiftEnd" value="18:00" required>
                                <small class="text-muted">End Time</small>
                            </div>
                        </div>
                    </div>

                    <!-- Working Days -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Working Days</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="dayMonday" checked>
                                    <label class="form-check-label" for="dayMonday">Monday</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="dayTuesday" checked>
                                    <label class="form-check-label" for="dayTuesday">Tuesday</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="dayWednesday" checked>
                                    <label class="form-check-label" for="dayWednesday">Wednesday</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="dayThursday" checked>
                                    <label class="form-check-label" for="dayThursday">Thursday</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="dayFriday" checked>
                                    <label class="form-check-label" for="dayFriday">Friday</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="daySaturday">
                                    <label class="form-check-label" for="daySaturday">Saturday</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary bg-main border-0" id="saveDepartmentBtn">
                    <i class="bi bi-check-lg me-1"></i>Create Department
                </button>
            </div>
        </div>
    </div>
</div>

