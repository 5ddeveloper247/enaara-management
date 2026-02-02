<!-- Transfer Employees Modal -->
<div class="modal fade" id="transferEmployeesModal" tabindex="-1" aria-labelledby="transferEmployeesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="transferEmployeesModalLabel">Transfer Employees</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="transferEmployeesForm">
                    <!-- From Department -->
                    <div class="mb-3">
                        <label for="fromDepartment" class="form-label fw-semibold">From Department</label>
                        <select class="form-select" id="fromDepartment" required>
                            <option value="">Select Source Department</option>
                            <option value="sales">Sales</option>
                            <option value="hr">Human Resources</option>
                            <option value="it">IT & Technology</option>
                        </select>
                    </div>

                    <!-- To Department -->
                    <div class="mb-3">
                        <label for="toDepartment" class="form-label fw-semibold">To Department</label>
                        <select class="form-select" id="toDepartment" required>
                            <option value="">Select Target Department</option>
                            <option value="sales">Sales</option>
                            <option value="hr">Human Resources</option>
                            <option value="it">IT & Technology</option>
                        </select>
                    </div>

                    <!-- Employee Selection -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Employees</label>
                        <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="emp1" value="1">
                                <label class="form-check-label" for="emp1">Ahmed Ali - EMP-001</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="emp2" value="2">
                                <label class="form-check-label" for="emp2">Fatima Khan - EMP-002</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="emp3" value="3">
                                <label class="form-check-label" for="emp3">Mike Wilson - EMP-003</label>
                            </div>
                        </div>
                    </div>

                    <!-- Effective Date -->
                    <div class="mb-3">
                        <label for="transferDate" class="form-label fw-semibold">Effective Date</label>
                        <input type="date" class="form-control" id="transferDate" required>
                    </div>

                    <!-- Notes -->
                    <div class="mb-3">
                        <label for="transferNotes" class="form-label fw-semibold">Notes</label>
                        <textarea class="form-control" id="transferNotes" rows="3" placeholder="Optional notes about this transfer..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary bg-main border-0" id="confirmTransferBtn">
                    <i class="bi bi-check-lg me-1"></i>Transfer Employees
                </button>
            </div>
        </div>
    </div>
</div>

