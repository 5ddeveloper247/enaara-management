<!-- Create/Edit Workflow Canvas -->
<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="createWorkflowCanvas" aria-labelledby="createWorkflowCanvasLabel" style="width: 900px;">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="createWorkflowCanvasLabel">
            <i class="bi bi-plus-circle me-2"></i><span id="canvasTitle">Create New Workflow</span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <form id="createWorkflowForm" class="overflow-y-auto">
        <input type="hidden" id="editWorkflowId" value="">
        <div class="offcanvas-body">
            <!-- Basic Information -->
            <div class="mb-4">
                <h6 class="mb-3 fw-semibold small">
                    <i class="bi bi-info-circle me-2"></i>Basic Information
                </h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-white">Workflow Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="workflowName" name="name" required placeholder="e.g., Leave Approval Workflow">
                        <div class="field-error-msg text-danger small mt-1" id="name-error"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-white">Request Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="workflowRequestType" name="request_type" required>
                            <option value="">Select Request Type</option>
                            <option value="leave">Leave</option>
                            <option value="overtime">Overtime</option>
                            <option value="regularization">Regularization</option>
                            <option value="shift">Shift</option>
                        </select>
                        <div class="field-error-msg text-danger small mt-1" id="request_type-error"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-white">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="workflowStatus" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <div class="field-error-msg text-danger small mt-1" id="status-error"></div>
                    </div>
                </div>
            </div>

            <!-- Applicable To -->
            <div class="mb-4">
                <h6 class="mb-3 fw-semibold small">
                    <i class="bi bi-diagram-3 me-2"></i>Applicable To
                </h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-white">Organization</label>
                        <select class="form-select" id="workflowOrganization" name="organization_id">
                            <option value="">Global (All Organizations)</option>
                            @foreach ($organizations as $org)
                                <option value="{{ $org->id }}">{{ $org->name }}</option>
                            @endforeach
                        </select>
                        <div class="field-error-msg text-danger small mt-1" id="organization_id-error"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-white">Branch (Optional)</label>
                        <input type="text" class="form-control" id="workflowBranch" name="branch" placeholder="e.g., Karachi, Lahore">
                        <div class="field-error-msg text-danger small mt-1" id="branch-error"></div>
                    </div>
                </div>
            </div>

            <!-- Approval Chain Designer -->
            <div class="mb-4">
                <h6 class="mb-3 fw-semibold small">
                    <i class="bi bi-diagram-2 me-2"></i>Approval Chain
                </h6>
                <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important; background-color: rgba(255, 255, 255, 0.05);">
                    <div id="approvalChainContainer">
                        <!-- Approval levels will be added here -->
                    </div>
                    <div class="field-error-msg text-danger small mt-1" id="approval_levels-error"></div>
                    <button type="button" class="btn btn-sm btn-outline-light mt-3" id="addApprovalLevelBtn">
                        <i class="bi bi-plus-circle me-1"></i>Add Approval Level
                    </button>
                </div>
            </div>

            <!-- SLA & Escalation -->
            <div class="mb-4">
                <h6 class="mb-3 fw-semibold small">
                    <i class="bi bi-clock-history me-2"></i>SLA & Escalation Settings
                </h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-white">Time to Approve (Hours) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="workflowSLA" name="sla_hours" required min="1" placeholder="e.g., 24" value="24">
                        <small class="opacity-75 text-white">Auto-escalate if not approved within this time</small>
                        <div class="field-error-msg text-danger small mt-1" id="sla_hours-error"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-white">Escalate To</label>
                        <select class="form-select" id="workflowEscalateTo" name="escalate_to">
                            <option value="">No Auto-escalation</option>
                            <option value="HR Manager">HR Manager</option>
                            <option value="Super Admin">Super Admin</option>
                            <option value="Next Level Approver">Next Level Approver</option>
                        </select>
                        <div class="field-error-msg text-danger small mt-1" id="escalate_to-error"></div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top" style="border-color: #ffffffab !important">
                <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" class="btn btn-light text-dark border-0" id="workflowSubmitBtn">
                    <i class="bi bi-check-circle me-1"></i><span id="submitBtnText">Create Workflow</span>
                </button>
            </div>
        </div>
    </form>
</div>
