<div class="mb-3">
    <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" id="proxyToggle">
        <label class="form-check-label fw-semibold" for="proxyToggle">
            Enable Proxy Assignment
        </label>
    </div>
    <small class="text-muted d-block mb-3">
        When you're on leave, assign a proxy admin to handle urgent approval requests.
    </small>

    <div class="mb-3">
        <label for="proxySelect" class="form-label small fw-semibold">Select Proxy Admin</label>
        <select class="form-select form-select-sm" id="proxySelect" disabled>
            <option value="">Select a proxy admin...</option>
            <option value="1">Sarah Miller (HR Manager)</option>
            <option value="2">Robert Kim (IT Director)</option>
            <option value="3">Emma Wilson (Operations Head)</option>
        </select>
    </div>

    <button type="button" class="btn btn-sm btn-primary bg-main border-0 w-100" id="assignProxyBtn" disabled>
        <i class="bi bi-check-lg me-1"></i>Assign Proxy
    </button>

    <!-- Current Proxy Status -->
    <div class="mt-4 p-3 rounded-3 border bg-light">
        <small class="text-muted d-block mb-2">Current Status</small>
        <div class="small">
            <strong>No active proxy assigned</strong>
            <div class="text-muted mt-1">You are handling all approvals</div>
        </div>
    </div>
</div>

