<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="departmentDetailCanvas" aria-labelledby="departmentDetailCanvasLabel">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="departmentDetailCanvasLabel">
            <i class="bi bi-building me-2"></i>Department Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="mb-4">
            <h6 class="fw-semibold small mb-1" id="canvasDeptName">—</h6>
            <p class="opacity-75 mb-0" id="canvasDeptCode">—</p>
        </div>
        <hr class="my-4" style="border-color: #ffffffab !important">
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-info-circle me-2"></i>Details
            </h6>
            <div class="row g-3">
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Organization</small>
                        <div class="fw-semibold small" id="canvasDeptOrganization">—</div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">SBU</small>
                        <div class="fw-semibold small" id="canvasDeptSbu">—</div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Parent Department</small>
                        <div class="fw-semibold small" id="canvasDeptParent">—</div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Description</small>
                        <div class="fw-semibold small" id="canvasDeptDescription">—</div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Status</small>
                        <div class="fw-semibold small" id="canvasDeptStatus">—</div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Working Schedule -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-clock me-2"></i>Working Schedule
            </h6>

            <div class="row g-3">
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Working Days</small>
                        <div class="fw-semibold small" id="canvasDeptWorkingDays">—</div>
                    </div>
                </div>

                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Start Time</small>
                        <div class="fw-semibold small" id="canvasDeptStartTime">—</div>
                    </div>
                </div>

                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">End Time</small>
                        <div class="fw-semibold small" id="canvasDeptEndTime">—</div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Grace Period (min)</small>
                        <div class="fw-semibold small" id="canvasDeptGracePeriod">—</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top" style="border-color: #ffffffab !important">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Close</button>
        </div>
    </div>
</div>
