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
            <!-- SBU Selection Section -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-building me-2"></i>Unit Mapping
                </h6>
                <div class="mb-3">
                    <label for="fenceSbu" class="form-label fw-semibold small text-white">Select SBU <span class="text-danger">*</span></label>
                    <select class="form-select" id="fenceSbu" required>
                        <option value="">Choose an SBU...</option>
                        @foreach($sbus as $sbu)
                            <option value="{{ $sbu->id }}">{{ $sbu->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

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
                    <label for="fenceAddress" class="form-label fw-semibold small">
                        Address (Search for the address or press Enter to drop a pin) <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="fenceAddress" placeholder="Search address or drop pin on map" required>
                        <button type="button" class="btn btn-outline-secondary" id="searchAddressBtn">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                    <small class="text-muted">Coordinates are required to save. Search by Enter/Search or click "Drop Pin" on the map.</small>
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
            {{-- <div class="mb-4">
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
            </div> --}}

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
    let saveBtn = null;

    const formEl = document.getElementById('addFenceForm');
    // Prevent accidental form submission when user presses Enter in an input.
    formEl?.addEventListener('submit', function(e) {
        e.preventDefault();
    });

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
            reverseGeocode(lat, lng);
        });
    }

    function reverseGeocode(lat, lng) {
        // Use Nominatim API to get address from coordinates (Reverse Geocoding)
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
            .then(response => response.json())
            .then(data => {
                if (data && data.display_name) {
                    document.getElementById('fenceAddress').value = data.display_name;
                }
            })
            .catch(error => console.error('Error in reverse geocoding:', error));
    }

    function showValidationErrors(response) {
        const errors = response?.errors || {};
        const messages = [];

        for (const value of Object.values(errors)) {
            if (Array.isArray(value) && value.length > 0) {
                messages.push(value[0]);
            } else if (typeof value === 'string' && value.trim() !== '') {
                messages.push(value);
            }
        }

        if (messages.length === 0 && response?.message) {
            messages.push(response.message);
        }

        const uniqueMessages = [...new Set(messages)];
        Swal.fire({
            icon: 'warning',
            title: 'Validation Error',
            html: uniqueMessages.join('<br>')
        });
    }

    // Drop pin button
    document.getElementById('dropPinBtn')?.addEventListener('click', function() {
        if (mapContainer) {
            mapContainer.style.cursor = 'crosshair';
            // Also draw attention to the preview map
            mapContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Show a temporary tooltip or alert if needed
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="bi bi-geo-alt me-1"></i>Click Map Above';
            this.classList.replace('btn-outline-light', 'btn-light');
            this.classList.replace('text-white', 'text-dark');
            
            setTimeout(() => {
                this.innerHTML = originalText;
                this.classList.replace('btn-light', 'btn-outline-light');
                this.classList.add('text-white');
            }, 3000);
        } else {
            Swal.fire('Unavailable', 'Map is not available right now.', 'info');
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

    // Search address using OpenStreetMap Nominatim API (Free, no API key required)
    document.getElementById('searchAddressBtn')?.addEventListener('click', function() {
        const address = document.getElementById('fenceAddress').value;
        if (!address) {
            Swal.fire('Warning', 'Please enter an address to search.', 'warning');
            return;
        }

        const btn = this;
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
        btn.disabled = true;

        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`)
            .then(response => response.json())
            .then(data => {
                if (data && data.length > 0) {
                    const lat = parseFloat(data[0].lat);
                    const lng = parseFloat(data[0].lon);
                    updateLocation(lat, lng);
                    document.getElementById('fenceAddress').value = data[0].display_name;
                } else {
                    Swal.fire('Not Found', 'Location not found. Please try a different search term.', 'warning');
                }
            })
            .catch(error => {
                console.error('Error during geocoding:', error);
                Swal.fire('Error', 'Error searching for location. Please try again.', 'error');
            })
            .finally(() => {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            });
    });

    // Pressing Enter in the address field should behave like clicking "Search".
    const addressInput = document.getElementById('fenceAddress');
    const searchBtn = document.getElementById('searchAddressBtn');
    addressInput?.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            e.stopPropagation();
            if (searchBtn && !searchBtn.disabled) searchBtn.click();
        }
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
        addFenceCanvas.addEventListener('shown.bs.offcanvas', function() {
            if (previewMap) {
                previewMap.invalidateSize();
                const lat = parseFloat(document.getElementById('fenceLat').value);
                const lng = parseFloat(document.getElementById('fenceLng').value);
                if (!isNaN(lat) && !isNaN(lng)) {
                    previewMap.setView([lat, lng], 15);
                }
            }
        });

        addFenceCanvas.addEventListener('hidden.bs.offcanvas', function() {
            document.getElementById('addFenceForm').reset();
            if (previewMarker) {
                previewMap.removeLayer(previewMarker);
                previewMarker = null;
            }
        });
    }

    // Handle form submission
    saveBtn = document.getElementById('saveFenceBtn');
    if (saveBtn) {
        saveBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const lat = document.getElementById('fenceLat').value;
            const lng = document.getElementById('fenceLng').value;

            const hasValidLatLng = lat !== '' && lng !== '' && !isNaN(lat) && !isNaN(lng);

            if (!hasValidLatLng) {
                Swal.fire(
                    'Warning',
                    'Please set the map location first. Press Enter/Search for the address or click "Drop Pin" on the map.',
                    'warning'
                );
                document.getElementById('fenceAddress').focus();
                return;
            }

            const formData = {
                siteName: document.getElementById('fenceSiteName').value,
                address: document.getElementById('fenceAddress').value,
                lat: document.getElementById('fenceLat').value,
                lng: document.getElementById('fenceLng').value,
                radius: document.getElementById('fenceRadius').value,
                radiusUnit: document.getElementById('fenceRadiusUnit').value,
                type: document.getElementById('fenceType').value,
                sbu_id: document.getElementById('fenceSbu').value,
                antiSpoofing: document.getElementById('enableAntiSpoofing').checked ? 1 : 0,
                offlineSync: document.getElementById('enableOfflineSync').checked ? 1 : 0,
                autoCheckIn: document.getElementById('enableAutoCheckIn').checked ? 1 : 0,
                _token: '{{ csrf_token() }}'
            };
            
            const originalHtml = saveBtn.innerHTML;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
            saveBtn.disabled = true;

            $.ajax({
                url: '{{ route("admin.geofencing.store") }}',
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        // Close offcanvas
                        const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('addFenceCanvas'));
                        if (offcanvas) offcanvas.hide();

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message || 'Geofence created successfully.',
                                timer: 650,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    }
                },
                error: function(xhr) {
                    saveBtn.innerHTML = originalHtml;
                    saveBtn.disabled = false;
                    const response = xhr.responseJSON;
                    if (xhr.status === 422) {
                        showValidationErrors(response);
                        return;
                    }
                    const err = response?.message || 'Error occurred while saving the geofence.';
                    Swal.fire('Error', err, 'error');
                }
            });
        });
    }
});
</script>

