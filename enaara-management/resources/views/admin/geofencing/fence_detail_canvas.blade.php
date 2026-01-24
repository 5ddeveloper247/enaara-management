<!-- Fence Detail Canvas -->
<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="fenceDetailCanvas" aria-labelledby="fenceDetailCanvasLabel" style="width: 600px;">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="fenceDetailCanvasLabel">
            <i class="bi bi-geo-alt-fill me-2"></i>Fence Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <!-- Site Information -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-info-circle me-2"></i>Site Information
            </h6>
            <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                <div class="mb-2">
                    <small class="opacity-75 text-white d-block mb-1">Site Name</small>
                    <div class="fw-semibold small" id="detailFenceName">Enaara Tower A</div>
                </div>
                <div class="mb-2">
                    <small class="opacity-75 text-white d-block mb-1">Address</small>
                    <div class="small" id="detailFenceAddress">Commercial Area, Rawalpindi, Pakistan</div>
                </div>
                <div>
                    <small class="opacity-75 text-white d-block mb-1">Coordinates</small>
                    <div class="small" id="detailFenceCoordinates">33.5651, 73.0169</div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Fence Configuration -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-gear me-2"></i>Configuration
            </h6>
            <div class="row g-3">
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Radius</small>
                        <div class="fw-semibold small" id="detailFenceRadius">200 Meters</div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Type</small>
                        <div id="detailFenceType">
                            <span class="badge bg-danger">Hard Lock</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Employee Status -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-people me-2"></i>Employee Status
            </h6>
            <div class="row g-3">
                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Inside Fence</small>
                        <div class="fw-bold fs-5" id="detailFenceInside">45</div>
                        <small class="opacity-50 text-white">Employees</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Outside Fence</small>
                        <div class="fw-bold fs-5" id="detailFenceOutside">12</div>
                        <small class="opacity-50 text-white">Employees</small>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Assigned Groups -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-people-fill me-2"></i>Assigned Groups
            </h6>
            <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                <div id="detailFenceGroups">
                    <span class="badge bg-secondary me-2 mb-2">Site Maintenance</span>
                    <span class="badge bg-secondary me-2 mb-2">Security Team</span>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Advanced Features -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-star me-2"></i>Advanced Features
            </h6>
            <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small">Anti-Spoofing</span>
                    <span class="badge bg-success" id="detailAntiSpoofing">Enabled</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small">Offline Sync</span>
                    <span class="badge bg-success" id="detailOfflineSync">Enabled</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="small">Auto Check-In</span>
                    <span class="badge bg-secondary" id="detailAutoCheckIn">Disabled</span>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Location Violations -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-exclamation-triangle me-2"></i>Recent Violations
            </h6>
            <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                <div id="detailFenceViolations">
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-top border-bottom" style="border-color: #ffffff1a !important;">
                        <div>
                            <div class="small fw-semibold">John Doe</div>
                            <small class="opacity-75 text-white">Attempted check-in outside fence</small>
                        </div>
                        <small class="opacity-75 text-white">2 hours ago</small>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small fw-semibold">Sarah Miller</div>
                            <small class="opacity-75 text-white">Location mismatch detected</small>
                        </div>
                        <small class="opacity-75 text-white">5 hours ago</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="offcanvas-footer border-top p-3" style="border-color: #ffffffab !important">
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Close</button>
            <button type="button" class="btn btn-light text-dark border-0" id="editFenceBtn">
                <i class="bi bi-pencil me-1"></i>Edit Fence
            </button>
        </div>
    </div>
</div>

<script>
function showFenceDetails(fenceData) {
    // Populate detail canvas
    document.getElementById('detailFenceName').textContent = fenceData.name;
    document.getElementById('detailFenceAddress').textContent = fenceData.address;
    document.getElementById('detailFenceCoordinates').textContent = `${fenceData.lat}, ${fenceData.lng}`;
    document.getElementById('detailFenceRadius').textContent = `${fenceData.radius} ${fenceData.radiusUnit}`;
    
    // Fence type badge
    const typeBadge = fenceData.type === 'hard-lock' 
        ? '<span class="badge bg-danger">Hard Lock</span>' 
        : '<span class="badge bg-warning text-dark">Soft Lock</span>';
    document.getElementById('detailFenceType').innerHTML = typeBadge;
    
    // Employee counts
    document.getElementById('detailFenceInside').textContent = fenceData.insideCount;
    document.getElementById('detailFenceOutside').textContent = fenceData.outsideCount;
    
    // Assigned groups
    const groupsHtml = fenceData.assignedGroups.map(group => 
        `<span class="badge bg-secondary me-2 mb-2">${group}</span>`
    ).join('');
    document.getElementById('detailFenceGroups').innerHTML = groupsHtml;
    
    // Open canvas
    const canvas = new bootstrap.Offcanvas(document.getElementById('fenceDetailCanvas'));
    canvas.show();
}
</script>

