<!-- Add Fence Canvas -->
<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="addFenceCanvas" aria-labelledby="addFenceCanvasLabel" style="width: 600px;">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="addFenceCanvasLabel">
            <i class="bi bi-plus-circle me-2"></i>Add New Geofence
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="addFenceForm">
            <!-- Site Information Section -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-info-circle me-2"></i>Site Information
                </h6>
                
                <!-- Site Name -->
                <div class="mb-3">
                    <label for="fenceSiteName" class="form-label fw-semibold small">Site Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="fenceSiteName" placeholder="e.g., Enaara Tower A" required>
                </div>

                <!-- Address Search -->
                <div class="mb-3">
                    <label for="fenceAddress" class="form-label fw-semibold small">Address <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="fenceAddress" placeholder="Search address or drop pin on map" required>
                        <button type="button" class="btn btn-outline-secondary" id="searchAddressBtn">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                    <small class="text-muted">Search for address or click "Drop Pin" to place on map</small>
                </div>

                <!-- Map Preview -->
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-white">Location Preview</label>
                    <div id="fenceMapPreview" style="height: 200px; border-radius: 8px; overflow: hidden; border: 1px solid #ffffff1a;"></div>
                    <div class="d-flex gap-2 mt-2">
                        <button type="button" class="btn btn-sm btn-outline-light" id="dropPinBtn">
                            <i class="bi bi-geo-alt me-1"></i>Drop Pin on Map
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-light" id="useCurrentLocationBtn">
                            <i class="bi bi-crosshair me-1"></i>Use Current Location
                        </button>
                    </div>
                </div>

                <!-- Hidden coordinates -->
                <input type="hidden" id="fenceLat">
                <input type="hidden" id="fenceLng">
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Fence Configuration Section -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-gear me-2"></i>Fence Configuration
                </h6>

                <!-- Radius -->
                <div class="mb-3">
                    <label for="fenceRadius" class="form-label fw-semibold small text-white">Radius <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="fenceRadius" placeholder="100" min="1" max="10000" step="1" value="100" required>
                        <select class="form-select" id="fenceRadiusUnit" style="max-width: 120px;">
                            <option value="meters">Meters</option>
                            <option value="kilometers">Kilometers</option>
                        </select>
                    </div>
                    <small class="opacity-75 text-white">Define the geofencing radius around the site</small>
                </div>

                <!-- Fence Type -->
                <div class="mb-3">
                    <label for="fenceType" class="form-label fw-semibold small text-white">Fence Type <span class="text-danger">*</span></label>
                    <select class="form-select" id="fenceType" required>
                        <option value="">Select Type</option>
                        <option value="hard-lock">Hard Lock - Employee cannot punch in if outside</option>
                        <option value="soft-lock">Soft Lock - Employee can punch in, but Admin gets alert</option>
                    </select>
                    <small class="opacity-75 text-white">Hard Lock prevents check-in outside fence. Soft Lock allows but flags violation.</small>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Assignment Section -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-people me-2"></i>Assign to Groups
                </h6>

                <!-- Organization -->
                <div class="mb-3">
                    <label for="fenceOrganization" class="form-label fw-semibold small text-white">Organization</label>
                    <select class="form-select" id="fenceOrganization">
                        <option value="">Select Organization (Optional)</option>
                        <option value="1">Enaara Construction</option>
                        <option value="2">Enaara Properties</option>
                        <option value="3">Enaara Services</option>
                    </select>
                </div>

                <!-- Departments -->
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-white">Departments</label>
                    <div class="border rounded p-3" style="max-height: 150px; overflow-y: auto; border-color: #ffffff1a !important;">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="deptSiteMaintenance" name="fenceDepartments" value="Site Maintenance">
                            <label class="form-check-label text-white" for="deptSiteMaintenance">Site Maintenance</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="deptSecurity" name="fenceDepartments" value="Security Team">
                            <label class="form-check-label text-white" for="deptSecurity">Security Team</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="deptSales" name="fenceDepartments" value="Sales Team">
                            <label class="form-check-label text-white" for="deptSales">Sales Team</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="deptFieldAgents" name="fenceDepartments" value="Field Agents">
                            <label class="form-check-label text-white" for="deptFieldAgents">Field Agents</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="deptConstruction" name="fenceDepartments" value="Construction Team">
                            <label class="form-check-label text-white" for="deptConstruction">Construction Team</label>
                        </div>
                    </div>
                    <small class="opacity-75 text-white">Select which departments are restricted to this fence</small>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Advanced Features -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-star me-2"></i>Advanced Features
                </h6>

                <!-- Anti-Spoofing -->
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="enableAntiSpoofing">
                    <label class="form-check-label text-white" for="enableAntiSpoofing">
                        <strong>Anti-Spoofing Detection</strong>
                        <small class="d-block opacity-75">Detect and flag fake GPS apps</small>
                    </label>
                </div>

                <!-- Offline Sync -->
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="enableOfflineSync" checked>
                    <label class="form-check-label text-white" for="enableOfflineSync">
                        <strong>Offline Sync</strong>
                        <small class="d-block opacity-75">Cache location punches when signal is poor</small>
                    </label>
                </div>

                <!-- Auto Check-In -->
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="enableAutoCheckIn">
                    <label class="form-check-label text-white" for="enableAutoCheckIn">
                        <strong>Auto Check-In</strong>
                        <small class="d-block opacity-75">Automatically clock in when employee enters fence radius</small>
                    </label>
                </div>
            </div>
        </form>
    </div>
    <div class="offcanvas-footer border-top p-3" style="border-color: #ffffffab !important">
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Cancel</button>
            <button type="button" class="btn btn-light text-dark border-0" id="saveFenceBtn">
                <i class="bi bi-check-lg me-1"></i>Create Fence
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let previewMap = null;
    let previewMarker = null;

    // Initialize preview map - Rawalpindi, Pakistan
    const mapContainer = document.getElementById('fenceMapPreview');
    if (mapContainer) {
        previewMap = L.map('fenceMapPreview').setView([33.5651, 73.0169], 13);
        // Dark mode tile layer
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
            subdomains: 'abcd',
            maxZoom: 19
        }).addTo(previewMap);

        // Handle map clicks
        previewMap.on('click', function(e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;
            updateLocation(lat, lng);
        });
    }

    // Drop pin button
    document.getElementById('dropPinBtn')?.addEventListener('click', function() {
        // Focus on main map for pin dropping
        if (window.geofencingMap) {
            alert('Click on the main map to drop a pin');
        }
    });

    // Use current location
    document.getElementById('useCurrentLocationBtn')?.addEventListener('click', function() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                updateLocation(lat, lng);
            });
        } else {
            alert('Geolocation is not supported by your browser');
        }
    });

    // Search address (simplified - would use geocoding API in production)
    document.getElementById('searchAddressBtn')?.addEventListener('click', function() {
        const address = document.getElementById('fenceAddress').value;
        // In production, use a geocoding service like Nominatim or Google Geocoding API
        console.log('Searching for:', address);
    });

    function updateLocation(lat, lng) {
        document.getElementById('fenceLat').value = lat;
        document.getElementById('fenceLng').value = lng;

        // Update preview map
        if (previewMap) {
            previewMap.setView([lat, lng], 15);
            if (previewMarker) {
                previewMap.removeLayer(previewMarker);
            }
            previewMarker = L.marker([lat, lng]).addTo(previewMap);
        }
    }

    // Reset form when offcanvas is hidden
    const addFenceCanvas = document.getElementById('addFenceCanvas');
    if (addFenceCanvas) {
        addFenceCanvas.addEventListener('hidden.bs.offcanvas', function() {
            document.getElementById('addFenceForm').reset();
            if (previewMarker) {
                previewMap.removeLayer(previewMarker);
                previewMarker = null;
            }
        });
    }

    // Handle form submission
    const saveBtn = document.getElementById('saveFenceBtn');
    if (saveBtn) {
        saveBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const form = document.getElementById('addFenceForm');
            if (form && form.checkValidity()) {
                const formData = {
                    siteName: document.getElementById('fenceSiteName').value,
                    address: document.getElementById('fenceAddress').value,
                    lat: document.getElementById('fenceLat').value,
                    lng: document.getElementById('fenceLng').value,
                    radius: document.getElementById('fenceRadius').value,
                    radiusUnit: document.getElementById('fenceRadiusUnit').value,
                    type: document.getElementById('fenceType').value,
                    organization: document.getElementById('fenceOrganization').value,
                    departments: Array.from(document.querySelectorAll('input[name="fenceDepartments"]:checked')).map(cb => cb.value),
                    antiSpoofing: document.getElementById('enableAntiSpoofing').checked,
                    offlineSync: document.getElementById('enableOfflineSync').checked,
                    autoCheckIn: document.getElementById('enableAutoCheckIn').checked
                };
                
                console.log('Fence data:', formData);
                // TODO: Implement API call to save fence
                
                // Close offcanvas
                const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('addFenceCanvas'));
                if (offcanvas) {
                    offcanvas.hide();
                }
            } else if (form) {
                form.reportValidity();
            }
        });
    }
});
</script>

