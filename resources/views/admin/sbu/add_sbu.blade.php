<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="addSbuCanvas"
    aria-labelledby="addSbuCanvasLabel" style="width: 600px;">

    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="addSbuCanvasLabel">
            <i class="bi bi-building-add me-2"></i>Add New SBU
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
            aria-label="Close"></button>
    </div>

    <div class="offcanvas-body">
        <form id="addSbuForm" data-store-url="{{ route('admin.sbu.store') }}" novalidate>
            @csrf

            <div class="mb-3">
                <label for="organization_id" class="form-label fw-semibold small text-white">
                    Organization <span class="text-danger">*</span>
                </label>
                <select class="form-select" id="organization_id" name="organization_id" required>
                    <option value="">Select Organization</option>
                    @foreach ($organizations as $org)
                        <option value="{{ $org->id }}">{{ $org->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="name" class="form-label fw-semibold small text-white">
                    SBU Name <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Enter SBU name" required>
            </div>

            <div class="mb-3">
                <label for="city" class="form-label fw-semibold small text-white">
                    City
                </label>
                <input type="text" class="form-control" id="city" name="city" placeholder="Enter city">
            </div>

            <div class="mb-3">
                <label for="address" class="form-label fw-semibold small text-white">
                    Address
                </label>
                <textarea class="form-control" id="address" name="address" rows="3" placeholder="Enter address"></textarea>
            </div>

            <div class="mb-3">
                <label for="latitude" class="form-label fw-semibold small text-white">
                    Latitude
                </label>
                <input type="number" step="0.00000001" class="form-control" id="latitude" name="latitude"
                    placeholder="e.g. 33.68442020">
            </div>

            <div class="mb-3">
                <label for="longitude" class="form-label fw-semibold small text-white">
                    Longitude
                </label>
                <input type="number" step="0.00000001" class="form-control" id="longitude" name="longitude"
                    placeholder="e.g. 73.04788480">
            </div>

            <div class="mb-3">
                <label for="is_active" class="form-label fw-semibold small text-white">
                    Status <span class="text-danger">*</span>
                </label>
                <select class="form-select" id="is_active" name="is_active" required>
                    <option value="1" selected>Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
        </form>
    </div>

    <div class="offcanvas-footer border-top p-3" style="border-color: #ffffffab !important">
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Cancel</button>
            <button type="button" class="btn btn-light text-dark border-0" id="saveSbuBtn">
                <i class="bi bi-check-lg me-1"></i>Create SBU
            </button>
        </div>
    </div>
</div>