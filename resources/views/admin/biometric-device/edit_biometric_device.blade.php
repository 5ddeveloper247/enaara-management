<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="editBiometricDeviceCanvas"
    aria-labelledby="editBiometricDeviceCanvasLabel" style="width: 640px;">

    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="editBiometricDeviceCanvasLabel">
            <i class="bi bi-pencil-square me-2"></i>Edit Biometric Device
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
            aria-label="Close"></button>
    </div>

    <div class="offcanvas-body">
        <form id="editBiometricDeviceForm" method="POST" action="javascript:void(0);">
            @csrf
            <input type="hidden" id="edit_bd_id" name="id">

            <h6 class="text-white-50 small text-uppercase mb-3">Core</h6>
            <div class="mb-3">
                <label for="edit_bd_device_name" class="form-label fw-semibold small text-white">Device name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="edit_bd_device_name" name="device_name" maxlength="255" autocomplete="off">
            </div>
            <div class="mb-3">
                <label for="edit_bd_serial_number" class="form-label fw-semibold small text-white">Serial number <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="edit_bd_serial_number" name="serial_number" maxlength="100" autocomplete="off">
            </div>
            <div class="mb-3">
                <label for="edit_bd_device_type" class="form-label fw-semibold small text-white">Device type <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="edit_bd_device_type" name="device_type" maxlength="100" autocomplete="off">
            </div>
            <div class="mb-3">
                <label for="edit_bd_brand_model" class="form-label fw-semibold small text-white">Brand / model <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="edit_bd_brand_model" name="brand_model" maxlength="255" autocomplete="off">
            </div>

            <h6 class="text-white-50 small text-uppercase mb-3 mt-4">Mapping</h6>
            <div class="mb-3">
                <label for="edit_bd_organization_id" class="form-label fw-semibold small text-white">Organisation <span class="text-danger">*</span></label>
                <select class="form-select" id="edit_bd_organization_id" name="organization_id">
                    <option value="">Select organisation</option>
                    @foreach($organizations ?? [] as $org)
                    <option value="{{ $org->id }}">{{ $org->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="edit_bd_sbu_id" class="form-label fw-semibold small text-white">SBU <span class="text-danger">*</span></label>
                <select class="form-select" id="edit_bd_sbu_id" name="sbu_id" disabled>
                    <option value="">Select organisation first</option>
                </select>
            </div>

            <h6 class="text-white-50 small text-uppercase mb-3 mt-4">Connectivity</h6>
            <div class="mb-3">
                <label for="edit_bd_ip_address" class="form-label fw-semibold small text-white">IP address <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="edit_bd_ip_address" name="ip_address" maxlength="45" autocomplete="off">
            </div>
            <div class="mb-3">
                <label for="edit_bd_port" class="form-label fw-semibold small text-white">Port <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="edit_bd_port" name="port" min="1" max="65535" step="1">
            </div>
            <div class="mb-3">
                <label for="edit_bd_connection_type" class="form-label fw-semibold small text-white">Connection type <span class="text-danger">*</span></label>
                <select class="form-select" id="edit_bd_connection_type" name="connection_type">
                    <option value="">Select connection type</option>
                    <option value="lan">LAN</option>
                    <option value="wifi">WiFi</option>
                </select>
            </div>

            <h6 class="text-white-50 small text-uppercase mb-3 mt-4">Status</h6>
            <div class="mb-3">
                <label for="edit_bd_device_status" class="form-label fw-semibold small text-white">Device status <span class="text-danger">*</span></label>
                <select class="form-select" id="edit_bd_device_status" name="device_status">
                    <option value="">Select status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="faulty">Faulty</option>
                </select>
            </div>

            <h6 class="text-white-50 small text-uppercase mb-3 mt-4">Control</h6>
            <div class="mb-3">
                <label for="edit_bd_installation_date" class="form-label fw-semibold small text-white">Installation date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="edit_bd_installation_date" name="installation_date">
            </div>
        </form>
    </div>

    <div class="offcanvas-footer border-top p-3" style="border-color: #ffffffab !important">
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Cancel</button>
            <button type="button" class="btn btn-light text-dark border-0" id="updateBiometricDeviceBtn">
                <i class="bi bi-check-lg me-1"></i>Update
            </button>
        </div>
    </div>
</div>
