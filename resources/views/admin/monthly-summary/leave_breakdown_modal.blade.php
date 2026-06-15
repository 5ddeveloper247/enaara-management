<!-- Leave Breakdown Modal -->
<div class="modal fade" id="leaveBreakdownModal" tabindex="-1" aria-labelledby="leaveBreakdownModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-main text-white border-bottom-0">
                <h5 class="modal-title" id="leaveBreakdownModalLabel">
                    <i class="bi bi-person-lines-fill me-2"></i>Leave Breakdown
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="d-flex align-items-center mb-4">
                    <div class="user-avatar user-avatar-lg me-3" id="leaveBreakdownAvatar">E</div>
                    <div>
                        <h6 class="mb-0 fw-bold" id="leaveBreakdownName">Employee Name</h6>
                        <small class="text-muted" id="leaveBreakdownInfo">EMP001 | IT Department</small>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                        <h6 class="mb-0 fw-semibold text-secondary">Leaves Taken</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush" id="leaveBreakdownList">
                            <!-- Populated via JavaScript -->
                        </ul>
                    </div>
                    <div class="card-footer bg-white border-top text-end py-3">
                        <span class="fw-bold me-2">Total Leaves:</span>
                        <span class="badge bg-main fs-6 px-3 rounded-pill" id="leaveBreakdownTotal">0</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
