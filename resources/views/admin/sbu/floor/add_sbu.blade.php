<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="addSbuFloorCanvas"
    aria-labelledby="addSbuFloorCanvasLabel" style="width: 600px;">

    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="addSbuFloorCanvasLabel">
            <i class="bi bi-layers me-2"></i>Add New SBU Floor
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
            aria-label="Close"></button>
    </div>

    <div class="offcanvas-body">
        <form id="addSbuFloorForm" data-store-url="{{ route('admin.sbu.floor.store') }}">
            @csrf

            <div class="mb-3">
                <label for="sbu_id" class="form-label fw-semibold small text-white">
                    SBU <span class="text-danger">*</span>
                </label>
                <select class="form-select" id="sbu_id" name="sbu_id" required>
                    <option value="">Select SBU</option>
                    @foreach ($sbus as $sbu)
                        <option value="{{ $sbu->id }}">{{ $sbu->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="name" class="form-label fw-semibold small text-white">
                    Floor Name <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="name" name="name"
                    placeholder="Enter floor name" required>
            </div>

            <div class="mb-3">
                <label for="floor_number" class="form-label fw-semibold small text-white">
                    Floor Number
                </label>
                <input type="number" class="form-control" id="floor_number" name="floor_number"
                    placeholder="Enter floor number">
            </div>

            <div class="mb-3">
                <label for="floor_type" class="form-label fw-semibold small text-white">
                    Floor Type <span class="text-danger">*</span>
                </label>
                <select class="form-select" id="floor_type" name="floor_type" required>
                    <option value="operational" selected>Operational</option>
                    <option value="corporate">Corporate</option>
                    <option value="mixed">Mixed</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="is_restricted" class="form-label fw-semibold small text-white">
                    Restricted Access <span class="text-danger">*</span>
                </label>
                <select class="form-select" id="is_restricted" name="is_restricted" required>
                    <option value="0" selected>No</option>
                    <option value="1">Yes</option>
                </select>
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
            <button type="button" class="btn btn-light text-dark border-0" id="saveSbuFloorBtn">
                <i class="bi bi-check-lg me-1"></i>Create Floor
            </button>
        </div>
    </div>
</div>