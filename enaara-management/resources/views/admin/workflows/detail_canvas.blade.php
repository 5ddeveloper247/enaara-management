<!-- Workflow Detail Canvas -->
<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="workflowDetailCanvas" aria-labelledby="workflowDetailCanvasLabel" style="width: 800px;">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="workflowDetailCanvasLabel">
            <i class="bi bi-diagram-2 me-2"></i>Workflow Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <!-- Workflow Information -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-info-circle me-2"></i>Workflow Information
            </h6>
            <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                <div class="row g-3">
                    <div class="col-md-12">
                        <small class="opacity-75 text-white d-block mb-1">Workflow Name</small>
                        <div class="fw-semibold h6 mb-0 text-white" id="detailWorkflowName">-</div>
                    </div>
                    <div class="col-md-6">
                        <small class="opacity-75 text-white d-block mb-1">Request Type</small>
                        <div id="detailRequestType">-</div>
                    </div>
                    <div class="col-md-6">
                        <small class="opacity-75 text-white d-block mb-1">Status</small>
                        <div id="detailStatus">-</div>
                    </div>
                    <div class="col-md-6">
                        <small class="opacity-75 text-white d-block mb-1">Organization</small>
                        <div class="fw-semibold text-white" id="detailOrganization">-</div>
                    </div>
                    <div class="col-md-6">
                        <small class="opacity-75 text-white d-block mb-1">Branch</small>
                        <div class="fw-semibold text-white" id="detailBranch">-</div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Approval Chain Visualization -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-diagram-2 me-2"></i>Approval Chain
            </h6>
            <div id="detailApprovalChain">
                <!-- Approval chain will be populated by JavaScript -->
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- SLA & Escalation -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-clock-history me-2"></i>SLA & Escalation
            </h6>
            <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                <div class="row g-3">
                    <div class="col-md-6">
                        <small class="opacity-75 text-white d-block mb-1">Time to Approve</small>
                        <div class="fw-semibold text-white" id="detailSLA">-</div>
                    </div>
                    <div class="col-md-6">
                        <small class="opacity-75 text-white d-block mb-1">Escalate To</small>
                        <div id="detailEscalateTo">-</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top" style="border-color: #ffffffab !important">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Close</button>
            <button type="button" class="btn btn-light text-dark border-0" id="editWorkflowBtn">
                <i class="bi bi-pencil me-1"></i>Edit Workflow
            </button>
        </div>
    </div>
</div>

