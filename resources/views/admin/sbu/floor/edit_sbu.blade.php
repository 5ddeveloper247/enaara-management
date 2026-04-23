<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="editSbuFloorCanvas"
    aria-labelledby="editSbuFloorCanvasLabel" style="width: 640px;">

    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="editSbuFloorCanvasLabel">
            <i class="bi bi-pencil-square me-2"></i>Edit SBU Floor
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
            aria-label="Close"></button>
    </div>

    <div class="offcanvas-body">
        <form id="editSbuFloorForm" method="POST" action="javascript:void(0);" novalidate>
            @csrf
            <input type="hidden" id="edit_id" name="id">

            <div class="mb-3">
                <label for="edit_organization_id" class="form-label fw-semibold small text-white">
                    Organization <span class="text-danger">*</span>
                </label>
                <select class="form-select" id="edit_organization_id" name="organization_id" required>
                    <option value="" hidden selected>Select Organization</option>
                    @foreach ($organizations as $organization)
                        <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="edit_sbu_id" class="form-label fw-semibold small text-white">
                    SBU <span class="text-danger">*</span>
                </label>
                <select class="form-select" id="edit_sbu_id" name="sbu_id" required>
                    <option value="" hidden selected>First select organization</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="edit_name" class="form-label fw-semibold small text-white">
                    Floor Name <span class="text-danger">*</span> <span class="text-white-50 fw-normal">(max 50)</span>
                </label>
                <input type="text" class="form-control" id="edit_name" name="name"
                    placeholder="Enter floor name" maxlength="50" required>
                <small class="d-block mt-1 text-white-50" id="editFloorNameMeta"><span id="editFloorNameLen">0</span> / 50</small>
            </div>

            <div class="mb-3">
                <label for="edit_floor_number" class="form-label fw-semibold small text-white">
                    Floor Number <span class="text-white-50 fw-normal">(max 50)</span>
                </label>
                <input type="text" class="form-control" id="edit_floor_number" name="floor_number"
                    placeholder="Enter floor number" maxlength="50">
                <small class="d-block mt-1 text-white-50" id="editFloorNumberMeta"><span id="editFloorNumberLen">0</span> / 50</small>
            </div>

            <div class="mb-3">
                <label for="edit_floor_type" class="form-label fw-semibold small text-white">
                    Floor Type <span class="text-danger">*</span>
                </label>
                <select class="form-select" id="edit_floor_type" name="floor_type" required>
                    <option value="operational">Operational</option>
                    <option value="corporate">Corporate</option>
                    <option value="mixed">Mixed</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="edit_is_restricted" class="form-label fw-semibold small text-white">
                    Restricted Access <span class="text-danger">*</span>
                </label>
                <select class="form-select" id="edit_is_restricted" name="is_restricted" required>
                    <option value="0">No</option>
                    <option value="1">Yes</option>
                </select>
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

            <div class="mb-3 d-none border rounded p-3" id="edit_floor_biometric_section" style="border-color: #ffffff33 !important;">
                <label class="form-label fw-semibold small text-white d-block mb-2">
                    Biometric machines on this floor
                </label>
                <p class="small text-white-50 mb-2 mb-0">Devices ticked here are assigned to this floor. Untick to clear floor on that device (same SBU only).</p>
                <div id="edit_floor_biometric_list" class="text-white mt-2"></div>
            </div>
        </form>
    </div>

    <div class="offcanvas-footer border-top p-3" style="border-color: #ffffffab !important">
        <div class="d-flex justify-content-end align-items-center gap-2">
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="button" class="btn btn-light text-dark border-0" id="updateSbuFloorBtn">
                    <i class="bi bi-check-lg me-1"></i>Update Floor
                </button>
            </div>
        </div>
    </div>
</div>
