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
            <section class="roster-shift-section roster-audit-history-panel mb-4" id="rosterShiftAuditCard" style="display: none;" aria-label="Audit history">
                <h6 class="roster-audit-history-title mb-1">Audit history</h6>
                <p class="roster-audit-history-subtitle mb-3" id="rosterAuditHistorySubtitle">Shift roster entry</p>
                <div class="roster-audit-stats row g-0 mb-3" id="rosterAuditStats">
                    <div class="col-4 roster-audit-stat roster-audit-stat--created">
                        <span class="roster-audit-stat-value" id="rosterAuditStatCreated">0</span>
                        <span class="roster-audit-stat-label">Created</span>
                    </div>
                    <div class="col-4 roster-audit-stat roster-audit-stat--updated">
                        <span class="roster-audit-stat-value" id="rosterAuditStatUpdated">0</span>
                        <span class="roster-audit-stat-label">Updates</span>
                    </div>
                    <div class="col-4 roster-audit-stat roster-audit-stat--removed">
                        <span class="roster-audit-stat-value" id="rosterAuditStatRemoved">0</span>
                        <span class="roster-audit-stat-label">Removed</span>
                    </div>
                </div>
                <div class="roster-audit-tabs mb-2" role="tablist">
                    <button type="button" class="roster-audit-tab active" data-audit-tab="timeline" role="tab" aria-selected="true">Timeline</button>
                    <button type="button" class="roster-audit-tab" data-audit-tab="changes" role="tab" aria-selected="false">What changed</button>
                </div>
                <div class="roster-audit-filters mb-3" id="rosterAuditFilters" role="group" aria-label="Filter history">
                    <button type="button" class="roster-audit-filter active" data-audit-filter="all">All</button>
                    <button type="button" class="roster-audit-filter roster-audit-filter--created" data-audit-filter="created">+ Created</button>
                    <button type="button" class="roster-audit-filter roster-audit-filter--updated" data-audit-filter="updated">Updated</button>
                    <button type="button" class="roster-audit-filter roster-audit-filter--assigned" data-audit-filter="assigned">Assigned</button>
                    <button type="button" class="roster-audit-filter roster-audit-filter--deleted" data-audit-filter="deleted">Removed</button>
                </div>
                <div id="rosterAuditHistoryLoading" class="roster-audit-history-loading" style="display: none;" aria-live="polite">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    <span>Loading history…</span>
                </div>
                <div id="rosterAuditHistoryEmpty" class="roster-audit-history-empty" style="display: none;">
                    <i class="bi bi-clock-history" aria-hidden="true"></i>
                    <span>No history recorded for this shift yet.</span>
                </div>
                <div class="roster-audit-history-scroll" id="rosterAuditHistoryScroll">
                    <ul class="list-unstyled mb-0 roster-audit-timeline" id="rosterAuditHistoryList"></ul>
                </div>
            </section>
            <div class="roster-shift-divider mb-4" role="presentation"></div>
            <section class="roster-shift-section mb-2" aria-label="Edit assignment">
                <div class="roster-shift-section-label mb-3">Assignment</div>
                <div class="mb-3">
                    <label for="rosterShiftPlannerId" class="form-label roster-shift-field-label">
                        Shift <span id="rosterShiftRequiredMark" class="text-danger">*</span>
                    </label>
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
                    <div id="rosterShiftPlannerError" class="invalid-feedback d-block d-none"></div>
                </div>
                <div class="form-check form-switch mb-3 roster-shift-custom-toggle">
                    <input class="form-check-input" type="checkbox" id="rosterUseCustomTime" value="1">
                    <label class="form-check-label roster-shift-field-label" for="rosterUseCustomTime">Use custom start and end time</label>
                </div>
                <div class="row g-2 mb-3" id="rosterShiftTimeRow" style="display: none;">
                    <div class="col-6">
                        <label for="rosterStartTime" class="form-label roster-shift-field-label">Start Time <span class="text-danger">*</span></label>
                        <input type="time" class="form-control roster-shift-input roster-shift-time-input" id="rosterStartTime" name="start_time">
                        <div id="rosterStartTimeError" class="invalid-feedback d-block d-none"></div>
                    </div>
                    <div class="col-6">
                        <label for="rosterEndTime" class="form-label roster-shift-field-label">End Time <span class="text-danger">*</span></label>
                        <input type="time" class="form-control roster-shift-input roster-shift-time-input" id="rosterEndTime" name="end_time">
                        <div id="rosterEndTimeError" class="invalid-feedback d-block d-none"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="rosterFloor" class="form-label roster-shift-field-label">Floor <span class="text-danger">*</span></label>
                    <select class="form-select roster-shift-input" id="rosterFloor" name="sbu_floor_id" required>
                        <option value="">Select floor</option>
                    </select>
                    <div class="invalid-feedback d-block d-none" id="rosterFloorError"></div>
                </div>
                <div class="mb-3">
                    <label for="rosterLocation" class="form-label roster-shift-field-label">Location</label>
                    <input type="text" class="form-control roster-shift-input" id="rosterLocation" name="location_text" maxlength="15" placeholder="Optional (3–15 characters)" autocomplete="off">
                    <div class="invalid-feedback d-block d-none" id="rosterLocationError"></div>
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
