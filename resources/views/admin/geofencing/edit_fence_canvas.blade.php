<!-- Edit Fence Canvas -->
<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="editFenceCanvas" aria-labelledby="editFenceCanvasLabel" style="width: 600px;">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="editFenceCanvasLabel">
            <i class="bi bi-pencil-square me-2"></i>Edit Geofence
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="editFenceForm">
            <input type="hidden" id="editFenceId">
            
            <!-- SBU Selection Section -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-building me-2"></i>Unit Mapping
                </h6>
                <div class="mb-3">
                    <label for="editFenceSbu" class="form-label fw-semibold small text-white">Select SBU <span class="text-danger">*</span></label>
                    <select class="form-select" id="editFenceSbu" required>
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
                    <label for="editFenceSiteName" class="form-label fw-semibold small">Site Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="editFenceSiteName" required>
                </div>

                <!-- Address Search -->
                <div class="mb-3">
                    <label for="editFenceAddress" class="form-label fw-semibold small">
                        Address (Search for the address or press Enter to drop a pin) <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="editFenceAddress" required>
                        <button type="button" class="btn btn-outline-secondary" id="editSearchAddressBtn">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                    <small class="text-muted">Coordinates are required to save. Search by Enter/Search or click "Drop Pin" on the map.</small>
                </div>

                <!-- Map Preview -->
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-white">Location Preview</label>
                    <div id="editFenceMapPreview" style="height: 200px; border-radius: 8px; overflow: hidden; border: 1px solid #ffffff1a;"></div>
                    <div class="d-flex gap-2 mt-2">
                        <button type="button" class="btn btn-sm btn-outline-light" id="editDropPinBtn">
                            <i class="bi bi-geo-alt me-1"></i>Drop Pin on Map
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-light" id="editUseCurrentLocationBtn">
                            <i class="bi bi-crosshair me-1"></i>Use Current Location
                        </button>
                    </div>
                </div>

                <!-- Hidden coordinates -->
                <input type="hidden" id="editFenceLat">
                <input type="hidden" id="editFenceLng">
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Fence Configuration Section -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-gear me-2"></i>Fence Configuration
                </h6>

                <!-- Radius -->
                <div class="mb-3">
                    <label for="editFenceRadius" class="form-label fw-semibold small text-white">Radius <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="editFenceRadius" min="1" max="10000" step="1" required>
                        <select class="form-select" id="editFenceRadiusUnit" style="max-width: 120px;">
                            <option value="meters">Meters</option>
                            <option value="kilometers">Kilometers</option>
                        </select>
                    </div>
                </div>

                <!-- Fence Type -->
                <div class="mb-3">
                    <label for="editFenceType" class="form-label fw-semibold small text-white">Fence Type <span class="text-danger">*</span></label>
                    <select class="form-select" id="editFenceType" required>
                        <option value="">Select Type</option>
                        <option value="hard-lock">Hard Lock</option>
                        <option value="soft-lock">Soft Lock</option>
                    </select>
                </div>

                <!-- Status -->
                <div class="mb-3">
                    <label for="editFenceStatus" class="form-label fw-semibold small text-white">Status <span class="text-danger">*</span></label>
                    <select class="form-select" id="editFenceStatus" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
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
                    <input class="form-check-input" type="checkbox" id="editEnableAntiSpoofing">
                    <label class="form-check-label text-white" for="editEnableAntiSpoofing">
                        <strong>Anti-Spoofing Detection</strong>
                    </label>
                </div>

                <!-- Offline Sync -->
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="editEnableOfflineSync">
                    <label class="form-check-label text-white" for="editEnableOfflineSync">
                        <strong>Offline Sync</strong>
                    </label>
                </div>

                <!-- Auto Check-In -->
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="editEnableAutoCheckIn">
                    <label class="form-check-label text-white" for="editEnableAutoCheckIn">
                        <strong>Auto Check-In</strong>
                    </label>
                </div>
            </div>
        </form>
    </div>
    <div class="offcanvas-footer border-top p-3" style="border-color: #ffffffab !important">
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Cancel</button>
            <button type="button" class="btn btn-light text-dark border-0" id="updateFenceBtn">
                <i class="bi bi-check-lg me-1"></i>Update Fence
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let editPreviewMap = null;
    let editPreviewMarker = null;

    const editFormEl = document.getElementById('editFenceForm');
    // Prevent accidental form submission when user presses Enter in an input.
    editFormEl?.addEventListener('submit', function(e) {
        e.preventDefault();
    });

    const editMapContainer = document.getElementById('editFenceMapPreview');
    const editFenceCanvasEl = document.getElementById('editFenceCanvas');

    if (editFenceCanvasEl && editMapContainer) {
        // We delay map initialization until the canvas is shown, 
        // because Leaflet needs the container to have actual width/height.
        editFenceCanvasEl.addEventListener('shown.bs.offcanvas', function () {
            if (!editPreviewMap) {
                const lat = parseFloat(document.getElementById('editFenceLat').value) || 33.5651;
                const lng = parseFloat(document.getElementById('editFenceLng').value) || 73.0169;
                
                editPreviewMap = L.map('editFenceMapPreview').setView([lat, lng], 15);
                L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                    attribution: '&copy; OpenStreetMap &copy; CARTO',
                    subdomains: 'abcd',
                    maxZoom: 19
                }).addTo(editPreviewMap);

                editPreviewMarker = L.marker([lat, lng]).addTo(editPreviewMap);

                editPreviewMap.on('click', function(e) {
                    const clat = e.latlng.lat;
                    const clng = e.latlng.lng;
                    editUpdateLocation(clat, clng);
                    editReverseGeocode(clat, clng);
                });
            } else {
                const lat = parseFloat(document.getElementById('editFenceLat').value);
                const lng = parseFloat(document.getElementById('editFenceLng').value);
                if (!isNaN(lat) && !isNaN(lng)) {
                    editPreviewMap.invalidateSize();
                    editPreviewMap.setView([lat, lng], 15);
                    if (editPreviewMarker) editPreviewMap.removeLayer(editPreviewMarker);
                    editPreviewMarker = L.marker([lat, lng]).addTo(editPreviewMap);
                }
            }
        });

        editFenceCanvasEl.addEventListener('hidden.bs.offcanvas', function () {
            document.getElementById('editFenceForm').reset();
            document.getElementById('editFenceId').value = '';
        });
    }

    function editReverseGeocode(lat, lng) {
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
            .then(response => response.json())
            .then(data => {
                if (data && data.display_name) {
                    document.getElementById('editFenceAddress').value = data.display_name;
                }
            })
            .catch(error => console.error('Error:', error));
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

    document.getElementById('editDropPinBtn')?.addEventListener('click', function() {
        if (editMapContainer) {
            editMapContainer.style.cursor = 'crosshair';
            editMapContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
            const btn = this;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-geo-alt me-1"></i>Click Map Above';
            btn.classList.replace('btn-outline-light', 'btn-light');
            btn.classList.replace('text-white', 'text-dark');
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.classList.replace('btn-light', 'btn-outline-light');
                btn.classList.add('text-white');
            }, 3000);
        }
    });

    document.getElementById('editUseCurrentLocationBtn')?.addEventListener('click', function() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                editUpdateLocation(position.coords.latitude, position.coords.longitude);
            });
        }
    });

    document.getElementById('editSearchAddressBtn')?.addEventListener('click', function() {
        const address = document.getElementById('editFenceAddress').value;
        if (!address) return;

        const btn = this;
        const orgHtml = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
        btn.disabled = true;

        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`)
            .then(response => response.json())
            .then(data => {
                if (data && data.length > 0) {
                    editUpdateLocation(parseFloat(data[0].lat), parseFloat(data[0].lon));
                    document.getElementById('editFenceAddress').value = data[0].display_name;
                }
            })
            .finally(() => {
                btn.innerHTML = orgHtml;
                btn.disabled = false;
            });
    });

    // Pressing Enter in the address field should behave like clicking "Search".
    const editAddressInput = document.getElementById('editFenceAddress');
    const editSearchBtn = document.getElementById('editSearchAddressBtn');
    editAddressInput?.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            e.stopPropagation();
            if (editSearchBtn && !editSearchBtn.disabled) editSearchBtn.click();
        }
    });

    function editUpdateLocation(lat, lng) {
        document.getElementById('editFenceLat').value = lat;
        document.getElementById('editFenceLng').value = lng;
        if (editPreviewMap) {
            editPreviewMap.setView([lat, lng], 15);
            if (editPreviewMarker) editPreviewMap.removeLayer(editPreviewMarker);
            editPreviewMarker = L.marker([lat, lng]).addTo(editPreviewMap);
        }
    }

    const updateBtn = document.getElementById('updateFenceBtn');
    if (updateBtn) {
        updateBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const lat = document.getElementById('editFenceLat').value;
            const lng = document.getElementById('editFenceLng').value;

            const hasValidLatLng = lat !== '' && lng !== '' && !isNaN(lat) && !isNaN(lng);

            if (!hasValidLatLng) {
                Swal.fire(
                    'Warning',
                    'Please set the map location first. Press Enter/Search for the address or click "Drop Pin" on the map.',
                    'warning'
                );
                document.getElementById('editFenceAddress').focus();
                return;
            }

            const id = document.getElementById('editFenceId').value;
            const formData = {
                siteName: document.getElementById('editFenceSiteName').value,
                address: document.getElementById('editFenceAddress').value,
                lat: document.getElementById('editFenceLat').value,
                lng: document.getElementById('editFenceLng').value,
                radius: document.getElementById('editFenceRadius').value,
                radiusUnit: document.getElementById('editFenceRadiusUnit').value,
                type: document.getElementById('editFenceType').value,
                sbu_id: document.getElementById('editFenceSbu').value,
                status: document.getElementById('editFenceStatus').value,
                antiSpoofing: document.getElementById('editEnableAntiSpoofing').checked ? 1 : 0,
                offlineSync: document.getElementById('editEnableOfflineSync').checked ? 1 : 0,
                autoCheckIn: document.getElementById('editEnableAutoCheckIn').checked ? 1 : 0,
                _token: '{{ csrf_token() }}'
            };
            
            const originalHtml = updateBtn.innerHTML;
            updateBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...';
            updateBtn.disabled = true;

            $.ajax({
                url: `/admin/geofencing/${id}`,
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('editFenceCanvas'));
                        if (offcanvas) offcanvas.hide();

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message || 'Geofence updated successfully.',
                            timer: 650,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    }
                },
                error: function(xhr) {
                    updateBtn.innerHTML = originalHtml;
                    updateBtn.disabled = false;
                    const response = xhr.responseJSON;
                    if (xhr.status === 422) {
                        showValidationErrors(response);
                        return;
                    }
                    const err = response?.message || 'Error occurred while updating the geofence.';
                    Swal.fire('Error', err, 'error');
                }
            });
        });
    }
});
</script>
