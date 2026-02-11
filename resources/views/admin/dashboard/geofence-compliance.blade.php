<div class="col-4">
    <div class="card h-100 p-0 rounded-5 border-0 overflow-hidden geofence-compliance-card">
        {{-- <div class="card-header border-0 px-4 pt-4 pb-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1 text-main">Geofence & IP Compliance</h5>
                <small class="text-muted">Security & Audit Metrics</small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-shield-check text-success fs-4"></i>
                <span class="badge bg-success">Trust Score: 98.6%</span>
            </div>
        </div> --}}
        <div class="card-body p-0">
            <!-- Map Container -->
            <div class="compliance-map-container p-0 mb-0 rounded-0">
                <div id="geofenceMap" class="compliance-map"></div>
            </div>

            <!-- Compliance Metrics Summary -->
            {{-- <div class="compliance-summary mt-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="compliance-metric-card in-zone">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <i class="bi bi-geo-alt-fill text-success"></i>
                                        <span class="compliance-label">In-Zone</span>
                                    </div>
                                    <div class="compliance-value text-success">950</div>
                                </div>
                                <span class="compliance-status badge bg-success">Normal</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="compliance-metric-card out-zone">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <i class="bi bi-geo-alt text-warning"></i>
                                        <span class="compliance-label">Out-of-Zone</span>
                                    </div>
                                    <div class="compliance-value text-warning">12</div>
                                </div>
                                <span class="compliance-status badge bg-warning">Flagged</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="compliance-metric-card vpn-proxy">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <i class="bi bi-shield-exclamation text-danger"></i>
                                        <span class="compliance-label">VPN/Proxy</span>
                                    </div>
                                    <div class="compliance-value text-danger">2</div>
                                </div>
                                <span class="compliance-status badge bg-danger">Alert</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}

            <!-- Quick Actions -->
            {{-- <div class="compliance-actions mt-4 pt-3 border-top">
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-sm btn-outline-warning rounded-3" title="View Out-of-Zone Details">
                        <i class="bi bi-eye me-1"></i>Review Flagged (12)
                    </button>
                    <button class="btn btn-sm btn-outline-danger rounded-3" title="View VPN/Proxy Alerts">
                        <i class="bi bi-exclamation-triangle me-1"></i>View Alerts (2)
                    </button>
                    <button class="btn btn-sm btn-outline-primary rounded-3" title="Generate Compliance Report">
                        <i class="bi bi-file-earmark-text me-1"></i>Generate Report
                    </button>
                </div>
            </div> --}}
        </div>
    </div>
</div>

