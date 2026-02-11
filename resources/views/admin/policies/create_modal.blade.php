<!-- Create/Edit Policy Modal -->
<div class="modal fade" id="createPolicyModal" tabindex="-1" aria-labelledby="createPolicyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-main text-white">
                <h5 class="modal-title" id="createPolicyModalLabel">
                    <i class="bi bi-plus-circle me-2"></i>Create New Policy
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createPolicyForm">
                <div class="modal-body">
                    <!-- Basic Information -->
                    <div class="mb-4">
                        <h6 class="mb-3 fw-semibold small">
                            <i class="bi bi-info-circle me-2"></i>Basic Information
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label small fw-semibold">Policy Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="policyTitle" required placeholder="Enter policy title">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Category <span class="text-danger">*</span></label>
                                <select class="form-select" id="policyCategory" required>
                                    <option value="">Select Category</option>
                                    <option value="leave">Leave Policy</option>
                                    <option value="attendance">Attendance Grace Period</option>
                                    <option value="geofence">Geofencing Rules</option>
                                    <option value="shift">Shift Rota Protocols</option>
                                    <option value="security">Security Policy</option>
                                    <option value="hr">HR Policy</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="policyStatus" required>
                                    <option value="draft">Draft</option>
                                    <option value="active">Active</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Effective Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="policyEffectiveDate" required>
                            </div>
                        </div>
                    </div>

                    <!-- Applicable To -->
                    <div class="mb-4">
                        <h6 class="mb-3 fw-semibold small">
                            <i class="bi bi-diagram-3 me-2"></i>Applicable To
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label small fw-semibold">Scope <span class="text-danger">*</span></label>
                                <select class="form-select" id="policyApplicableTo" required>
                                    <option value="global">Global (All Organizations)</option>
                                    <option value="organization">Organization Specific</option>
                                    <option value="branch">Branch Specific</option>
                                    <option value="floor">Floor Specific</option>
                                </select>
                            </div>
                            <div class="col-md-6" id="organizationSelect" style="display: none;">
                                <label class="form-label small fw-semibold">Organization</label>
                                <select class="form-select" id="policyOrganization">
                                    <option value="">Select Organization</option>
                                    <option value="enaara">Enaara Developers</option>
                                    <option value="msr-rawalpindi">Madison Square Mall Rawalpindi</option>
                                    <option value="msr-lahore">Madison Square Mall Lahore</option>
                                    <option value="royal-swiss">Royal Swiss Lahore</option>
                                </select>
                            </div>
                            <div class="col-md-6" id="branchSelect" style="display: none;">
                                <label class="form-label small fw-semibold">Branch</label>
                                <select class="form-select" id="policyBranch">
                                    <option value="">Select Branch</option>
                                    <option value="rawalpindi">Rawalpindi</option>
                                    <option value="lahore">Lahore</option>
                                    <option value="karachi">Karachi</option>
                                </select>
                            </div>
                            <div class="col-md-6" id="floorSelect" style="display: none;">
                                <label class="form-label small fw-semibold">Floor</label>
                                <select class="form-select" id="policyFloor">
                                    <option value="">Select Floor</option>
                                    <option value="ground">Ground Floor</option>
                                    <option value="1">Floor 1</option>
                                    <option value="2">Floor 2</option>
                                    <option value="3">Floor 3</option>
                                    <option value="4">Floor 4</option>
                                    <option value="5">Floor 5</option>
                                    <option value="6">Floor 6</option>
                                    <option value="7">Floor 7</option>
                                    <option value="8">Floor 8</option>
                                    <option value="9">Floor 9 (Corporate/HR Zone)</option>
                                    <option value="10">Floor 10</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Policy Content -->
                    <div class="mb-4">
                        <h6 class="mb-3 fw-semibold small">
                            <i class="bi bi-file-text me-2"></i>Policy Content
                        </h6>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Description</label>
                            <textarea class="form-control" id="policyDescription" rows="4" placeholder="Enter policy description"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Policy Document (Optional)</label>
                            <input type="file" class="form-control" id="policyDocument" accept=".pdf,.doc,.docx">
                            <small class="text-muted">Upload PDF or Word document (Max 10MB)</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary bg-main border-0">
                        <i class="bi bi-check-circle me-1"></i>Create Policy
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

