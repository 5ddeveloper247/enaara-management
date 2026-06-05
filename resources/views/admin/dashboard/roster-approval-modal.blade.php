<div class="modal fade" id="rosterApprovalModal" tabindex="-1" aria-labelledby="rosterApprovalModalLabel" aria-hidden="true" data-bs-focus="false">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg roster-approval-modal">
            <div class="roster-approval-modal__header">
                <div class="d-flex align-items-center gap-3 flex-grow-1 min-w-0">
                    <div class="roster-approval-avatar" id="rosterApprovalAvatar">--</div>
                    <div class="min-w-0">
                        <h5 class="modal-title text-main mb-0 text-truncate" id="rosterApprovalAssignee">-</h5>
                        <p class="text-muted small mb-0">Shift Roster Approval Request</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body pt-0">
                <div id="rosterApprovalModalLoader" class="text-center py-5 text-muted">
                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                    Loading roster details...
                </div>

                <div id="rosterApprovalModalContent" class="d-none">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="roster-approval-info-card">
                                <div class="roster-approval-info-card__icon">
                                    <i class="bi bi-building"></i>
                                </div>
                                <div>
                                    <div class="roster-approval-info-card__label">Department</div>
                                    <div class="roster-approval-info-card__value" id="rosterApprovalDepartment">-</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="roster-approval-info-card">
                                <div class="roster-approval-info-card__icon">
                                    <i class="bi bi-person"></i>
                                </div>
                                <div>
                                    <div class="roster-approval-info-card__label">Submitted by</div>
                                    <div class="roster-approval-info-card__value" id="rosterApprovalRequestedBy">-</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="roster-approval-info-card roster-approval-info-card--compact">
                                <div class="roster-approval-info-card__icon roster-approval-info-card__icon--shift">
                                    <i class="bi bi-sun"></i>
                                </div>
                                <div>
                                    <div class="roster-approval-info-card__label">Shift type</div>
                                    <div class="roster-approval-info-card__value" id="rosterApprovalShiftLabel">-</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="roster-approval-info-card roster-approval-info-card--compact">
                                <div class="roster-approval-info-card__icon roster-approval-info-card__icon--period">
                                    <i class="bi bi-calendar3"></i>
                                </div>
                                <div>
                                    <div class="roster-approval-info-card__label">Period</div>
                                    <div class="roster-approval-info-card__value" id="rosterApprovalPeriod">-</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="roster-approval-info-card roster-approval-info-card--compact">
                                <div class="roster-approval-info-card__icon roster-approval-info-card__icon--duration">
                                    <i class="bi bi-clock"></i>
                                </div>
                                <div>
                                    <div class="roster-approval-info-card__label">Duration</div>
                                    <div class="roster-approval-info-card__value" id="rosterApprovalDuration">-</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="roster-approval-stepper mb-4" id="rosterApprovalStepper">
                        <div class="roster-approval-step roster-approval-step--done" data-step="submitted">
                            <div class="roster-approval-step__icon"><i class="bi bi-check-lg"></i></div>
                            <div class="roster-approval-step__label">Submitted</div>
                            <div class="roster-approval-step__status">Done</div>
                        </div>
                        <div class="roster-approval-step__line"></div>
                        <div class="roster-approval-step roster-approval-step--active" data-step="review">
                            <div class="roster-approval-step__icon"><i class="bi bi-building"></i></div>
                            <div class="roster-approval-step__label">GM Review</div>
                            <div class="roster-approval-step__status">Pending</div>
                        </div>
                        <div class="roster-approval-step__line"></div>
                        <div class="roster-approval-step roster-approval-step--awaiting" data-step="approved">
                            <div class="roster-approval-step__icon"><i class="bi bi-check-circle"></i></div>
                            <div class="roster-approval-step__label">Approved</div>
                            <div class="roster-approval-step__status">Awaiting</div>
                        </div>
                    </div>

                    <div class="roster-approval-schedule">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="text-main mb-0">Schedule</h6>
                            <span class="small text-muted" id="rosterApprovalPreviewCount">0 days</span>
                        </div>

                        <div class="table-responsive roster-approval-schedule__table-wrap">
                            <table class="table table-sm align-middle mb-0 roster-approval-schedule__table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody id="rosterApprovalItemsBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 roster-approval-modal__footer">
                <button type="button" class="btn btn-outline-secondary rounded-3 px-4" data-bs-dismiss="modal">Close</button>
                <div class="d-flex gap-2" id="rosterApprovalActionButtons">
                    <button type="button" class="btn btn-outline-danger rounded-3 px-4" id="rosterApprovalRejectBtn">
                        Reject
                    </button>
                    <button type="button" class="btn btn-success rounded-3 px-4 border-0" id="rosterApprovalApproveBtn">
                        <i class="bi bi-check-lg me-1"></i>Approve Roster
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
