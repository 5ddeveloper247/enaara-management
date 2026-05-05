<div class="offcanvas offcanvas-end bg-main text-white roster-shift-offcanvas" tabindex="-1" id="rosterShiftCanvas" aria-labelledby="rosterShiftCanvasLabel" style="width: min(460px, 100vw);">
    <div class="offcanvas-header border-bottom roster-shift-offcanvas-header">
        <h5 class="offcanvas-title mb-0 d-flex align-items-center gap-2" id="rosterShiftCanvasLabel">
            <span id="rosterShiftCanvasIcon" class="roster-shift-header-icon d-inline-flex align-items-center justify-content-center rounded-3"><i class="bi bi-pencil-square"></i></span>
            <span id="rosterShiftCanvasTitle">Edit Shift</span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body pt-3 pb-4">
        <form id="rosterShiftForm">
            <input type="hidden" id="rosterShiftRosterId" name="roster_id" value="">
            <input type="hidden" id="rosterShiftEmployeeType" name="employee_type" value="employee">
            <input type="hidden" id="rosterShiftEmployeeId" name="employee_id" value="">
            <input type="hidden" id="rosterShiftDay" name="day" value="">

            <section class="roster-shift-section roster-shift-summary-card mb-4" aria-label="Roster context">
                <div class="d-flex gap-3 align-items-start">
                    <div class="roster-shift-avatar flex-shrink-0" id="rosterShiftEmployeeInitial" aria-hidden="true">?</div>
                    <div class="flex-grow-1 min-width-0">
                        <span class="roster-shift-section-label d-block mb-1">Employee</span>
                        <div class="roster-shift-summary-name text-truncate" id="rosterShiftEmployeeName">—</div>
                        <div id="rosterShiftDepartmentWrap" class="mt-1" style="display: none;">
                            <span class="roster-shift-summary-meta text-truncate d-block" id="rosterShiftDepartmentName"></span>
                        </div>
                    </div>
                    <div class="roster-shift-date-pill flex-shrink-0 text-end">
                        <span class="roster-shift-section-label d-block mb-1">Date</span>
                        <div class="roster-shift-date-value fw-semibold" id="rosterShiftDateLabel">—</div>
                    </div>
                </div>
            </section>

            <section class="roster-shift-section roster-shift-audit-card mb-4" id="rosterShiftAuditCard" style="display: none;" aria-label="Audit">
                <div class="roster-shift-section-label mb-2">Audit</div>
                <ul class="list-unstyled mb-0 roster-shift-audit-list">
                    <li class="roster-shift-audit-item">
                        <span class="roster-shift-audit-icon"><i class="bi bi-person-plus"></i></span>
                        <div class="roster-shift-audit-body">
                            <span class="roster-shift-audit-label">Created by</span>
                            <span class="roster-shift-audit-value" id="rosterShiftCreatedBy">—</span>
                        </div>
                    </li>
                    <li class="roster-shift-audit-item">
                        <span class="roster-shift-audit-icon"><i class="bi bi-arrow-repeat"></i></span>
                        <div class="roster-shift-audit-body">
                            <span class="roster-shift-audit-label">Updated by</span>
                            <span class="roster-shift-audit-value" id="rosterShiftUpdatedBy">—</span>
                        </div>
                    </li>
                    <li class="roster-shift-audit-item">
                        <span class="roster-shift-audit-icon"><i class="bi bi-person-check"></i></span>
                        <div class="roster-shift-audit-body">
                            <span class="roster-shift-audit-label">Assigned by</span>
                            <span class="roster-shift-audit-value" id="rosterShiftAssignedBy">—</span>
                        </div>
                    </li>
                    <li class="roster-shift-audit-item" id="rosterShiftDeletedWrap" style="display: none;">
                        <span class="roster-shift-audit-icon text-danger"><i class="bi bi-trash"></i></span>
                        <div class="roster-shift-audit-body">
                            <span class="roster-shift-audit-label">Deleted by</span>
                            <span class="roster-shift-audit-value text-danger" id="rosterShiftDeletedBy">—</span>
                        </div>
                    </li>
                </ul>
            </section>

            <div class="roster-shift-divider mb-4" role="presentation"></div>

            <section class="roster-shift-section mb-2" aria-label="Edit assignment">
                <div class="roster-shift-section-label mb-3">Assignment</div>

                <div class="mb-3">
                    <label for="rosterShiftPlannerId" class="form-label roster-shift-field-label">Shift <span class="text-danger">*</span></label>
                    <select class="form-select roster-shift-input" id="rosterShiftPlannerId" name="shift_planner_id" required>
                        <option value="">Select Shift</option>
                        @forelse($shifts ?? [] as $shift)
                            <option value="{{ $shift->id }}"
                                    data-start="{{ optional($shift->start_time)->format('H:i') }}"
                                    data-end="{{ optional($shift->end_time)->format('H:i') }}">
                                {{ $shift->name }} ({{ optional($shift->start_time)->format('h:i A') }} – {{ optional($shift->end_time)->format('h:i A') }})
                            </option>
                        @empty
                        @endforelse
                    </select>
                </div>

                <div style="display: none;">
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="rosterStartTime" class="form-label roster-shift-field-label">Start Time</label>
                            <input type="time" class="form-control roster-shift-input" id="rosterStartTime" name="start_time">
                        </div>
                        <div class="col-6">
                            <label for="rosterEndTime" class="form-label roster-shift-field-label">End Time</label>
                            <input type="time" class="form-control roster-shift-input" id="rosterEndTime" name="end_time">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="rosterCheckIn" class="form-label roster-shift-field-label">Check-in</label>
                            <input type="time" class="form-control roster-shift-input" id="rosterCheckIn" name="check_in">
                        </div>
                        <div class="col-6">
                            <label for="rosterCheckOut" class="form-label roster-shift-field-label">Check-out</label>
                            <input type="time" class="form-control roster-shift-input" id="rosterCheckOut" name="check_out">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="rosterFloor" class="form-label roster-shift-field-label">Floor / Location</label>
                    <input type="text" class="form-control roster-shift-input" id="rosterFloor" name="floor" placeholder="e.g. Ward A • 3rd Floor">
                </div>

                <div class="mb-0">
                    <label for="rosterShiftNotes" class="form-label roster-shift-field-label">Notes</label>
                    <textarea class="form-control roster-shift-input roster-shift-notes" id="rosterShiftNotes" name="notes" rows="3" placeholder="Optional"></textarea>
                </div>
            </section>
        </form>
    </div>
    <div class="offcanvas-footer border-top p-3 d-flex justify-content-between flex-wrap gap-2 roster-shift-footer">
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
