<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="sbuDetailCanvas" aria-labelledby="sbuDetailCanvasLabel">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="sbuDetailCanvasLabel">
            <i class="bi bi-building me-2"></i>SBU Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <!-- SBU Identity -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-info-circle me-2"></i>SBU Identity
            </h6>
            <div class="d-flex align-items-center mb-3">
                <div class="bg-light text-dark rounded-3 d-flex align-items-center justify-content-center fw-bold me-3" id="detailSbuLogoPlaceholder" style="width: 60px; height: 60px; font-size: 1.25rem;">—</div>
                <div class="flex-grow-1">
                    <h6 class="fw-semibold small mb-1" id="detailSbuName">—</h6>
                    <small class="opacity-75 text-white" id="detailSbuCity">—</small>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffff42 !important">

        <!-- Basic Information -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-file-text me-2"></i>Basic Information
            </h6>
            <div class="row g-3">
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Organization</small>
                        <div class="fw-semibold small" id="detailSbuOrganization">—</div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Address</small>
                        <div class="fw-semibold small" id="detailSbuAddress">—</div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Coordinates</small>
                        <div class="fw-semibold small">
                            Lat: <span id="detailSbuLatitude">—</span>, 
                            Long: <span id="detailSbuLongitude">—</span>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Status</small>
                        <div class="fw-semibold small" id="detailSbuStatus">—</div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffff42 !important">

        <!-- Working Schedule -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-clock me-2"></i>Working Schedule
            </h6>

            <div class="row g-3">
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Working Days</small>
                        <div class="fw-semibold small" id="detailSbuWorkingDays">—</div>
                    </div>
                </div>

                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Start Time</small>
                        <div class="fw-semibold small" id="detailSbuStartTime">—</div>
                    </div>
                </div>

                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">End Time</small>
                        <div class="fw-semibold small" id="detailSbuEndTime">—</div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Grace Period (min)</small>
                        <div class="fw-semibold small" id="detailSbuGracePeriod">—</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top" style="border-color: #ffffffab !important">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Close</button>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.view-sbu-btn');
        if (!btn) return;

        const name = btn.dataset.sbuName || '—';
        const city = btn.dataset.sbuCity || '—';
        const address = btn.dataset.sbuAddress || '—';
        const latitude = btn.dataset.sbuLatitude || '—';
        const longitude = btn.dataset.sbuLongitude || '—';
        const organization = btn.dataset.organizationName || '—';
        const workingDays = btn.dataset.sbuWorkingDays || '—';
        const startTime = btn.dataset.sbuWorkingStartTime || '—';
        const endTime = btn.dataset.sbuWorkingEndTime || '—';
        const gracePeriod = btn.dataset.sbuGracePeriod || '—';
        const isActive = btn.dataset.sbuActive === '1';

        const initials = name !== '—'
            ? name.split(' ').map(word => word.charAt(0)).join('').substring(0, 2).toUpperCase()
            : '—';

        document.getElementById('detailSbuLogoPlaceholder').textContent = initials;
        document.getElementById('detailSbuName').textContent = name;
        document.getElementById('detailSbuCity').textContent = city;
        document.getElementById('detailSbuOrganization').textContent = organization;
        document.getElementById('detailSbuAddress').textContent = address;
        document.getElementById('detailSbuLatitude').textContent = latitude;
        document.getElementById('detailSbuLongitude').textContent = longitude;
        document.getElementById('detailSbuWorkingDays').textContent = workingDays;
        document.getElementById('detailSbuStartTime').textContent = startTime;
        document.getElementById('detailSbuEndTime').textContent = endTime;
        document.getElementById('detailSbuGracePeriod').textContent = gracePeriod;

        document.getElementById('detailSbuStatus').innerHTML = isActive
            ? '<span class="badge bg-success px-3 py-2 rounded-1">Active</span>'
            : '<span class="badge bg-secondary px-3 py-2 rounded-1">Inactive</span>';
    });
});
</script>
