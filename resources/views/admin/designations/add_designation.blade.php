<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="addDesignationCanvas"
    aria-labelledby="addDesignationCanvasLabel" style="width: 600px;">

    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="addDesignationCanvasLabel">
            <i class="bi bi-briefcase me-2"></i>Add New Designation
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
            aria-label="Close"></button>
    </div>

    <div class="offcanvas-body">
        <form id="addDesignationForm" data-store-url="{{ route('admin.designations.store') }}" novalidate>
            @csrf

            <div class="mb-3">
                <label for="ds_organization_id" class="form-label fw-semibold small text-white">
                    Organization <span class="text-danger">*</span>
                </label>
                <select class="form-select" id="ds_organization_id" name="organization_id" required>
                    <option value="" hidden selected>— Select Organization —</option>
                    @foreach($organizations ?? [] as $organization)
                    <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="ds_sbu_id" class="form-label fw-semibold small text-white">
                    SBU <span class="text-danger">*</span>
                </label>
                <select class="form-select" id="ds_sbu_id" name="sbu_id" disabled required>
                    <option value="" hidden selected>— Select SBU —</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="ds_name" class="form-label fw-semibold small text-white">
                    Name <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="ds_name" name="name" placeholder="Enter designation name" maxlength="100" required>
            </div>

            <div class="mb-3">
                <label for="ds_description" class="form-label fw-semibold small text-white">
                    Description
                </label>
                <textarea class="form-control" id="ds_description" name="description" rows="3" placeholder="Enter description" maxlength="500"></textarea>
            </div>

            <div class="mb-3">
                <label for="ds_is_active" class="form-label fw-semibold small text-white">
                    Status <span class="text-danger">*</span>
                </label>
                <select class="form-select" id="ds_is_active" name="is_active" required>
                    <option value="1" selected>Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
        </form>
    </div>

    <div class="offcanvas-footer border-top p-3" style="border-color: #ffffffab !important">
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Cancel</button>
            <button type="button" class="btn btn-light text-dark border-0" id="saveDesignationBtn">
                <i class="bi bi-check-lg me-1"></i>Create Designation
            </button>
        </div>
    </div>
</div>
