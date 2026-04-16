<div class="card border-0 rounded-4 mb-4">
    <div class="card-body p-0 position-relative">
        <div id="geofencingMap"></div>
        
        <!-- Map Controls -->
        <div class="map-controls">
            <div class="d-flex flex-column gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="centerMapBtn">
                    <i class="bi bi-crosshair"></i> Center
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="fitBoundsBtn">
                    <i class="bi bi-arrows-angle-contract"></i> Fit All
                </button>
            </div>
        </div>

        <!-- Legend -->
        <div class="fence-legend">
            <h6 class="fw-semibold mb-3 small">Legend</h6>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #28a745; border-color: #28a745;"></div>
                <span>Inside Fence</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #ffc107; border-color: #ffc107;"></div>
                <span>Outside Fence</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #dc3545; border-color: #dc3545;"></div>
                <span>Location Violation</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background-color: #6c757d; border-color: #6c757d;"></div>
                <span>Breadcrumb Trail</span>
            </div>
        </div>
    </div>
</div>
