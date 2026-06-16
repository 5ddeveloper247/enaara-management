<div class="modal fade" id="workAssignmentModal" tabindex="-1" aria-labelledby="workAssignmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered work-assignment-modal-dialog">
        <div class="modal-content work-assignment-modal border-0 rounded-4">
            <div class="modal-header work-assignment-modal-header border-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="work-assignment-title-icon">
                        <i class="bi bi-geo-alt"></i>
                    </span>
                    <h5 class="modal-title fw-bold mb-0" id="workAssignmentModalLabel">Assign work location</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body work-assignment-modal-body pt-0">
                <div class="work-assignment-employee-card">
                    <div class="work-assignment-employee-avatar" id="workAssignmentEmployeeAvatar">AW</div>
                    <div class="work-assignment-employee-info">
                        <div class="work-assignment-employee-name" id="workAssignmentEmployeeName">-</div>
                        <div class="work-assignment-employee-date" id="workAssignmentDateLabel">-</div>
                    </div>
                    <span class="work-assignment-status-badge" id="workAssignmentDayStatus">
                        <i class="bi bi-check-circle-fill"></i>
                        <span id="workAssignmentDayStatusText">Present</span>
                    </span>
                </div>

                <div id="workAssignmentBlockedNotice" class="work-assignment-blocked-notice d-none">
                    <span id="workAssignmentBlockedNoticeText">Work location cannot be assigned on this day.</span>
                </div>

                <form id="workAssignmentForm">
                    <div class="work-assignment-section-label">Work location</div>
                    <div class="work-assignment-location-grid" id="workAssignmentLocationGrid">
                        <label class="work-assignment-location-card" data-work-type="none">
                            <input class="work-assignment-location-input" type="radio" name="work_type" id="workTypeNone" value="none" checked>
                            <span class="work-assignment-location-icon">
                                <i class="bi bi-building"></i>
                            </span>
                            <span class="work-assignment-location-label">Office</span>
                            <span class="work-assignment-location-radio" aria-hidden="true"></span>
                        </label>

                        <label class="work-assignment-location-card" data-work-type="work_from_home">
                            <input class="work-assignment-location-input" type="radio" name="work_type" id="workTypeWfh" value="work_from_home">
                            <span class="work-assignment-location-icon">
                                <i class="bi bi-house-door"></i>
                            </span>
                            <span class="work-assignment-location-label">Work from home</span>
                            <span class="work-assignment-location-radio" aria-hidden="true"></span>
                        </label>

                        <label class="work-assignment-location-card" data-work-type="outstation">
                            <input class="work-assignment-location-input" type="radio" name="work_type" id="workTypeOutstation" value="outstation">
                            <span class="work-assignment-location-icon">
                                <i class="bi bi-airplane"></i>
                            </span>
                            <span class="work-assignment-location-label">Outstation</span>
                            <span class="work-assignment-location-radio" aria-hidden="true"></span>
                        </label>

                        <label class="work-assignment-location-card work-assignment-location-card-absent" data-work-type="absent" id="workTypeAbsentCard">
                            <input class="work-assignment-location-input" type="radio" name="work_type" id="workTypeAbsent" value="absent">
                            <span class="work-assignment-location-icon">
                                <i class="bi bi-person-x"></i>
                            </span>
                            <span class="work-assignment-location-label">Mark as absent</span>
                            <span class="work-assignment-location-radio" aria-hidden="true"></span>
                        </label>
                    </div>

                    <div class="work-assignment-section-label mt-4">Notes <span class="work-assignment-optional">(optional)</span></div>
                    <textarea class="form-control work-assignment-notes" id="workAssignmentNotes" rows="3" placeholder="e.g. client site visit, remote project work"></textarea>
                </form>
            </div>

            <div class="modal-footer work-assignment-modal-footer border-0">
                <button type="button" class="btn work-assignment-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn work-assignment-btn-save" id="saveWorkAssignmentBtn">
                    <i class="bi bi-check-lg"></i>
                    Save
                </button>
            </div>
        </div>
    </div>
</div>
