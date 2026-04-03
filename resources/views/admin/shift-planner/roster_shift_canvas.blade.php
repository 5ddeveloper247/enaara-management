<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="rosterShiftCanvas" aria-labelledby="rosterShiftCanvasLabel" style="width: 420px;">
    <div class="offcanvas-header border-bottom" style="border-color: rgba(255,255,255,0.26) !important">
        <h5 class="offcanvas-title" id="rosterShiftCanvasLabel">
            <span id="rosterShiftCanvasIcon"><i class="bi bi-pencil-square me-2"></i></span><span id="rosterShiftCanvasTitle">Edit Shift</span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="rosterShiftForm">
            <input type="hidden" id="rosterShiftRosterId" name="roster_id" value="">
            <input type="hidden" id="rosterShiftEmployeeId" name="employee_id" value="">
            <input type="hidden" id="rosterShiftDay" name="day" value="">

            <div class="mb-3 p-3 rounded-3 border" style="border-color: rgba(255,255,255,0.2) !important">
                <small class="opacity-75 text-white d-block mb-1">Employee</small>
                <div class="fw-semibold" id="rosterShiftEmployeeName">—</div>
            </div>
            <div class="mb-3 p-3 rounded-3 border" style="border-color: rgba(255,255,255,0.2) !important">
                <small class="opacity-75 text-white d-block mb-1">Date</small>
                <div class="fw-semibold" id="rosterShiftDateLabel">—</div>
            </div>

            <div class="mb-3">
                <label for="rosterShiftPlannerId" class="form-label fw-semibold small text-white">Shift <span class="text-danger">*</span></label>
                <select class="form-select" id="rosterShiftPlannerId" name="shift_planner_id" required>
                    <option value="">Select Shift</option>
                    @forelse($shifts ?? [] as $shift)
                        <option value="{{ $shift->id }}" 
                                data-start="{{ optional($shift->start_time)->format('H:i') }}" 
                                data-end="{{ optional($shift->end_time)->format('H:i') }}">
                            {{ $shift->name }} ({{ optional($shift->start_time)->format('H:i') }} – {{ optional($shift->end_time)->format('H:i') }})
                        </option>
                    @empty
                    @endforelse
                </select>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label for="rosterStartTime" class="form-label fw-semibold small text-white">Start Time <span class="text-danger">*</span></label>
                    <input type="time" class="form-control" id="rosterStartTime" name="start_time" required>
                </div>
                <div class="col-6">
                    <label for="rosterEndTime" class="form-label fw-semibold small text-white">End Time <span class="text-danger">*</span></label>
                    <input type="time" class="form-control" id="rosterEndTime" name="end_time" required>
                </div>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label for="rosterCheckIn" class="form-label fw-semibold small text-white">Check-in</label>
                    <input type="time" class="form-control" id="rosterCheckIn" name="check_in">
                </div>
                <div class="col-6">
                    <label for="rosterCheckOut" class="form-label fw-semibold small text-white">Check-out</label>
                    <input type="time" class="form-control" id="rosterCheckOut" name="check_out">
                </div>
            </div>

            <div class="mb-3">
                <label for="rosterFloor" class="form-label fw-semibold small text-white">Floor / Location</label>
                <input type="text" class="form-control" id="rosterFloor" name="floor" placeholder="e.g. Ward A • 3rd Floor">
            </div>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="rosterLateCheckIn" name="late_check_in" value="1">
                    <label class="form-check-label text-white" for="rosterLateCheckIn">Late check-in</label>
                </div>
            </div>

            <div class="mb-3">
                <label for="rosterShiftNotes" class="form-label fw-semibold small text-white">Notes</label>
                <textarea class="form-control" id="rosterShiftNotes" name="notes" rows="2" placeholder="Optional"></textarea>
            </div>
        </form>
    </div>
    <div class="offcanvas-footer border-top p-3 d-flex justify-content-between flex-wrap gap-2" style="border-color: rgba(255,255,255,0.4) !important">
        <div id="rosterShiftDeleteWrap" style="display: none;">
            <button type="button" class="btn btn-outline-danger border-danger text-danger" id="rosterShiftDeleteBtn">
                <i class="bi bi-trash me-1"></i>Remove
            </button>
        </div>
        <div class="d-flex gap-2 ms-auto">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Cancel</button>
            <button type="button" class="btn btn-light text-dark border-0" id="rosterShiftSaveBtn">
                <i class="bi bi-check-lg me-1"></i><span id="rosterShiftSaveBtnText">Save</span>
            </button>
        </div>
    </div>
</div>
