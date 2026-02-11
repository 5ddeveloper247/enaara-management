<!-- Policy Detail Canvas -->
<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="policyDetailCanvas" aria-labelledby="policyDetailCanvasLabel" style="width: 700px;">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="policyDetailCanvasLabel">
            <i class="bi bi-file-text me-2"></i>Policy Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
                <!-- Policy Information -->
                <div class="mb-4">
                    <h6 class="mb-3 fw-semibold small">
                        <i class="bi bi-info-circle me-2"></i>Policy Information
                    </h6>
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <small class="opacity-75 text-white d-block mb-1">Title</small>
                                <div class="fw-semibold h6 mb-0 text-white" id="detailTitle">-</div>
                            </div>
                            <div class="col-md-6">
                                <small class="opacity-75 text-white d-block mb-1">Category</small>
                                <div id="detailCategory">-</div>
                            </div>
                            <div class="col-md-6">
                                <small class="opacity-75 text-white d-block mb-1">Status</small>
                                <div id="detailStatus">-</div>
                            </div>
                            <div class="col-md-6">
                                <small class="opacity-75 text-white d-block mb-1">Effective Date</small>
                                <div class="fw-semibold text-white" id="detailEffectiveDate">-</div>
                            </div>
                            <div class="col-md-6">
                                <small class="opacity-75 text-white d-block mb-1">Last Updated</small>
                                <div class="fw-semibold text-white" id="detailLastUpdated">-</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Applicable To -->
                <div class="mb-4">
                    <h6 class="mb-3 fw-semibold small">
                        <i class="bi bi-diagram-3 me-2"></i>Applicable To
                    </h6>
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <div id="detailApplicableTo">-</div>
                    </div>
                </div>

                <!-- Policy Description -->
                <div class="mb-4" id="descriptionSection">
                    <h6 class="mb-3 fw-semibold small">
                        <i class="bi bi-file-text me-2"></i>Policy Description
                    </h6>
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <div id="detailDescription" class="opacity-75 text-white">-</div>
                    </div>
                </div>

                <!-- Policy Document -->
                <div class="mb-4" id="documentSection" style="display: none;">
                    <h6 class="mb-3 fw-semibold small">
                        <i class="bi bi-file-earmark-pdf me-2"></i>Policy Document
                    </h6>
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="fw-semibold text-white" id="detailDocumentName">-</div>
                                <small class="opacity-75 text-white" id="detailDocumentSize">-</small>
                            </div>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-light" id="viewDocumentBtn">
                                    <i class="bi bi-eye me-1"></i>View
                                </button>
                                <button type="button" class="btn btn-sm btn-light text-dark border-0" id="downloadDocumentBtn">
                                    <i class="bi bi-download me-1"></i>Download
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top" style="border-color: #ffffffab !important">
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Close</button>
                    <button type="button" class="btn btn-light text-dark border-0" id="editPolicyBtn">
                        <i class="bi bi-pencil me-1"></i>Edit Policy
                    </button>
                </div>
            </div>
</div>

