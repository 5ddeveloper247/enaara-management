<!-- Bulk Policy Update Modal -->
<div class="modal fade" id="bulkPolicyModal" tabindex="-1" aria-labelledby="bulkPolicyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="bulkPolicyModalLabel">Bulk Policy Update</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="bulkPolicyForm">
                    <!-- Select Departments -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Departments</label>
                        <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                            <div class="form-check mb-2 d-flex align-items-center gap-1">
                                <input class="form-check-input mb-1" type="checkbox" id="deptSales" value="sales">
                                <label class="form-check-label" for="deptSales">Sales</label>
                            </div>
                            <div class="form-check mb-2 d-flex align-items-center gap-1">
                                <input class="form-check-input mb-1" type="checkbox" id="deptHR" value="hr">
                                <label class="form-check-label" for="deptHR">Human Resources</label>
                            </div>
                            <div class="form-check mb-2 d-flex align-items-center gap-1">
                                <input class="form-check-input mb-1" type="checkbox" id="deptIT" value="it">
                                <label class="form-check-label" for="deptIT">IT & Technology</label>
                            </div>
                            <div class="form-check mb-2 d-flex align-items-center gap-1">
                                <input class="form-check-input mb-1" type="checkbox" id="deptLegal" value="legal">
                                <label class="form-check-label" for="deptLegal">Legal</label>
                            </div>
                            <div class="form-check mb-2 d-flex align-items-center gap-1">
                                <input class="form-check-input mb-1" type="checkbox" id="deptOps" value="operations">
                                <label class="form-check-label" for="deptOps">Operations</label>
                            </div>
                            <div class="form-check mb-2 d-flex align-items-center gap-1">
                                <input class="form-check-input mb-1" type="checkbox" id="deptFinance" value="finance">
                                <label class="form-check-label" for="deptFinance">Finance</label>
                            </div>
                        </div>
                    </div>

                    <!-- Policy Type -->
                    <div class="mb-3">
                        <label for="policyType" class="form-label fw-semibold">Policy Type</label>
                        <select class="form-select" id="policyType" required>
                            <option value="">Select Policy Type</option>
                            <option value="leave">Leave Policy</option>
                            <option value="shift">Shift Rules</option>
                            <option value="overtime">Overtime Policy</option>
                            <option value="attendance">Attendance Rules</option>
                        </select>
                    </div>

                    <!-- Policy Update Details -->
                    <div class="mb-3">
                        <label for="policyUpdate" class="form-label fw-semibold">Update Details</label>
                        <textarea class="form-control" id="policyUpdate" rows="4" placeholder="Describe the policy change..." required></textarea>
                    </div>

                    <!-- Effective Date -->
                    <div class="mb-3">
                        <label for="policyEffectiveDate" class="form-label fw-semibold">Effective Date</label>
                        <input type="date" class="form-control" id="policyEffectiveDate" required>
                    </div>

                    <!-- Notification -->
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="notifyEmployees" checked>
                        <label class="form-check-label" for="notifyEmployees">
                            Notify affected employees via email
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary bg-main border-0" id="confirmBulkPolicyBtn">
                    <i class="bi bi-check-lg me-1"></i>Apply Policy Update
                </button>
            </div>
        </div>
    </div>
</div>

