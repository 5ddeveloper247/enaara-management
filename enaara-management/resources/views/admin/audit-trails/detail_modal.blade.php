<!-- Audit Detail Modal -->
<div class="modal fade" id="auditDetailModal" tabindex="-1" aria-labelledby="auditDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-main text-white">
                <h5 class="modal-title" id="auditDetailModalLabel">
                    <i class="bi bi-shield-check me-2"></i>Audit Trail Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Activity Information -->
                <div class="mb-4">
                    <h6 class="mb-3 fw-semibold small">
                        <i class="bi bi-info-circle me-2"></i>Activity Information
                    </h6>
                    <div class="p-3 rounded-3 border">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <small class="text-muted d-block mb-1">Timestamp</small>
                                <div class="fw-semibold" id="detailTimestamp">-</div>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block mb-1">User</small>
                                <div class="fw-semibold" id="detailUser">-</div>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block mb-1">Action Category</small>
                                <div id="detailCategory">-</div>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block mb-1">Severity</small>
                                <div id="detailSeverity">-</div>
                            </div>
                            <div class="col-12">
                                <small class="text-muted d-block mb-1">Description</small>
                                <div id="detailDescription">-</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Device & Network Information -->
                <div class="mb-4">
                    <h6 class="mb-3 fw-semibold small">
                        <i class="bi bi-laptop me-2"></i>Device & Network Information
                    </h6>
                    <div class="p-3 rounded-3 border">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <small class="text-muted d-block mb-1">IP Address</small>
                                <div class="fw-semibold" id="detailIPAddress">-</div>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block mb-1">Device/Browser</small>
                                <div class="fw-semibold" id="detailDevice">-</div>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block mb-1">Location</small>
                                <div id="detailLocation">-</div>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block mb-1">Organization</small>
                                <div id="detailOrganization">-</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Before & After Changes -->
                <div class="mb-4" id="changesSection" style="display: none;">
                    <h6 class="mb-3 fw-semibold small">
                        <i class="bi bi-arrow-left-right me-2"></i>Data Changes
                    </h6>
                    <div class="p-3 rounded-3 border">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 30%;">Field</th>
                                        <th style="width: 35%;">Before</th>
                                        <th style="width: 35%;">After</th>
                                    </tr>
                                </thead>
                                <tbody id="changesTableBody">
                                    <!-- Changes will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Additional Context -->
                <div class="mb-4" id="contextSection" style="display: none;">
                    <h6 class="mb-3 fw-semibold small">
                        <i class="bi bi-file-text me-2"></i>Additional Context
                    </h6>
                    <div class="p-3 rounded-3 border">
                        <div id="detailContext">-</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary bg-main border-0" id="exportDetailBtn">
                    <i class="bi bi-download me-1"></i>Export Details
                </button>
            </div>
        </div>
    </div>
</div>

