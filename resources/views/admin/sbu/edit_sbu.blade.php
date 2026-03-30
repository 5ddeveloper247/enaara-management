<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="editSbuCanvas"
    aria-labelledby="editSbuCanvasLabel" style="width: 600px;">

    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="editSbuCanvasLabel">
            <i class="bi bi-pencil-square me-2"></i>Edit SBU
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
            aria-label="Close"></button>
    </div>

    <div class="offcanvas-body">
        <form id="editSbuForm" method="POST" action="javascript:void(0);">
            @csrf
            <input type="hidden" id="edit_id" name="id">

            <div class="mb-3">
                <label for="edit_organization_id" class="form-label fw-semibold small text-white">
                    Organization <span class="text-danger">*</span>
                </label>
                <select class="form-select" id="edit_organization_id" name="organization_id" required>
                    <option value="">Select Organization</option>
                    @foreach ($organizations as $org)
                        <option value="{{ $org->id }}">{{ $org->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="edit_name" class="form-label fw-semibold small text-white">
                    SBU Name <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="edit_name" name="name" placeholder="Enter SBU name" required>
            </div>

            <div class="mb-3">
                <label for="edit_city" class="form-label fw-semibold small text-white">
                    City
                </label>
                <input type="text" class="form-control" id="edit_city" name="city" placeholder="Enter city">
            </div>

            <div class="mb-3">
                <label for="edit_address" class="form-label fw-semibold small text-white">
                    Address
                </label>
                <textarea class="form-control" id="edit_address" name="address" rows="3" placeholder="Enter address"></textarea>
            </div>

            <div class="mb-3">
                <label for="edit_latitude" class="form-label fw-semibold small text-white">
                    Latitude
                </label>
                <input type="number" step="0.00000001" class="form-control" id="edit_latitude" name="latitude"
                    placeholder="e.g. 33.68442020">
            </div>

            <div class="mb-3">
                <label for="edit_longitude" class="form-label fw-semibold small text-white">
                    Longitude
                </label>
                <input type="number" step="0.00000001" class="form-control" id="edit_longitude" name="longitude"
                    placeholder="e.g. 73.04788480">
            </div>

            <div class="mb-3">
                <label for="edit_is_active" class="form-label fw-semibold small text-white">
                    Status <span class="text-danger">*</span>
                </label>
                <select class="form-select" id="edit_is_active" name="is_active" required>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
        </form>
    </div>

    <div class="offcanvas-footer border-top p-3" style="border-color: #ffffffab !important">
        <div class="d-flex justify-content-between align-items-center gap-2">
            <button type="button" class="btn btn-outline-danger delete-sbu-btn" id="deleteSbuBtn"
                data-delete-url="">
                <i class="bi bi-trash me-1"></i>Delete
            </button>

            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="button" class="btn btn-light text-dark border-0" id="updateSbuBtn">
                    <i class="bi bi-check-lg me-1"></i>Update SBU
                </button>
            </div>
        </div>
    </div>
</div>