<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="biometricDeviceDetailCanvas" aria-labelledby="biometricDeviceDetailCanvasLabel">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="biometricDeviceDetailCanvasLabel">
            <i class="bi bi-fingerprint me-2"></i>Device Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small"><i class="bi bi-cpu me-2"></i>Core</h6>
            <div class="p-3 rounded-3 border mb-2" style="border-color: #ffffff1a !important;">
                <small class="opacity-75 text-white d-block mb-1">Device name</small>
                <div class="fw-semibold small" id="detailBdDeviceName">—</div>
            </div>
            <div class="p-3 rounded-3 border mb-2" style="border-color: #ffffff1a !important;">
                <small class="opacity-75 text-white d-block mb-1">Serial number</small>
                <div class="fw-semibold small" id="detailBdSerial">—</div>
            </div>
            <div class="p-3 rounded-3 border mb-2" style="border-color: #ffffff1a !important;">
                <small class="opacity-75 text-white d-block mb-1">Device type</small>
                <div class="fw-semibold small" id="detailBdType">—</div>
            </div>
            <div class="p-3 rounded-3 border mb-2" style="border-color: #ffffff1a !important;">
                <small class="opacity-75 text-white d-block mb-1">Brand / model</small>
                <div class="fw-semibold small" id="detailBdBrand">—</div>
            </div>
        </div>
        <hr class="my-4" style="border-color: #ffffffab !important">
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small"><i class="bi bi-diagram-3 me-2"></i>Mapping</h6>
            <div class="p-3 rounded-3 border mb-2" style="border-color: #ffffff1a !important;">
                <small class="opacity-75 text-white d-block mb-1">Organisation</small>
                <div class="fw-semibold small" id="detailBdOrg">—</div>
            </div>
            <div class="p-3 rounded-3 border mb-2" style="border-color: #ffffff1a !important;">
                <small class="opacity-75 text-white d-block mb-1">SBU</small>
                <div class="fw-semibold small" id="detailBdSbu">—</div>
            </div>
        </div>
        <hr class="my-4" style="border-color: #ffffffab !important">
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small"><i class="bi bi-hdd-network me-2"></i>Connectivity</h6>
            <div class="p-3 rounded-3 border mb-2" style="border-color: #ffffff1a !important;">
                <small class="opacity-75 text-white d-block mb-1">IP address · Port</small>
                <div class="fw-semibold small" id="detailBdIpPort">—</div>
            </div>
            <div class="p-3 rounded-3 border mb-2" style="border-color: #ffffff1a !important;">
                <small class="opacity-75 text-white d-block mb-1">Connection type</small>
                <div class="fw-semibold small" id="detailBdConn">—</div>
            </div>
        </div>
        <hr class="my-4" style="border-color: #ffffffab !important">
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small"><i class="bi bi-activity me-2"></i>Status</h6>
            <div class="p-3 rounded-3 border mb-2" style="border-color: #ffffff1a !important;">
                <small class="opacity-75 text-white d-block mb-1">Device status</small>
                <div class="fw-semibold small" id="detailBdDeviceStatus">—</div>
            </div>
            {{--
            <div class="p-3 rounded-3 border mb-2" style="border-color: #ffffff1a !important;">
                <small class="opacity-75 text-white d-block mb-1">Online status (auto)</small>
                <div class="fw-semibold small" id="detailBdOnline">—</div>
            </div>
            <div class="p-3 rounded-3 border mb-2" style="border-color: #ffffff1a !important;">
                <small class="opacity-75 text-white d-block mb-1">Last sync time</small>
                <div class="fw-semibold small" id="detailBdLastSync">—</div>
            </div>
            --}}
        </div>
        <hr class="my-4" style="border-color: #ffffffab !important">
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small"><i class="bi bi-calendar-event me-2"></i>Control &amp; audit</h6>
            <div class="p-3 rounded-3 border mb-2" style="border-color: #ffffff1a !important;">
                <small class="opacity-75 text-white d-block mb-1">Installation date</small>
                <div class="fw-semibold small" id="detailBdInstall">—</div>
            </div>
            <div class="p-3 rounded-3 border mb-2" style="border-color: #ffffff1a !important;">
                <small class="opacity-75 text-white d-block mb-1">Created by</small>
                <div class="fw-semibold small" id="detailBdCreatedBy">—</div>
            </div>
            {{--
            <div class="p-3 rounded-3 border mb-2" style="border-color: #ffffff1a !important;">
                <small class="opacity-75 text-white d-block mb-1">Created date</small>
                <div class="fw-semibold small" id="detailBdCreatedAt">—</div>
            </div>
            <div class="p-3 rounded-3 border mb-2" style="border-color: #ffffff1a !important;">
                <small class="opacity-75 text-white d-block mb-1">Updated date</small>
                <div class="fw-semibold small" id="detailBdUpdatedAt">—</div>
            </div>
            --}}
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top" style="border-color: #ffffffab !important">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Close</button>
        </div>
    </div>
</div>
