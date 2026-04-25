<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="addRoleLevelCanvas"
    aria-labelledby="addRoleLevelCanvasLabel" style="width: 600px;">

    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="addRoleLevelCanvasLabel">
            <i class="bi bi-layers me-2"></i>Add New Role Level
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
            aria-label="Close"></button>
    </div>

    <div class="offcanvas-body">
        <form id="addRoleLevelForm" data-store-url="{{ route('admin.role-levels.store') }}" novalidate>
            @csrf

            <div class="mb-3">
                <label for="rl_name" class="form-label fw-semibold small text-white">
                    Name <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="rl_name" name="name" placeholder="Enter role level name" maxlength="50" required>
            </div>

            <div class="mb-3">
                <label for="rl_level" class="form-label fw-semibold small text-white">
                    Level <span class="text-danger">*</span>
                </label>
                <input type="number" class="form-control" id="rl_level" name="level" placeholder="Enter priority level (max 10 digits)" min="1" max="9999999999" required>
            </div>

            <div class="mb-3">
                <label for="rl_description" class="form-label fw-semibold small text-white">
                    Description
                </label>
                <textarea class="form-control" id="rl_description" name="description" rows="3" placeholder="Enter description" maxlength="500"></textarea>
            </div>

            <div class="mb-3">
                <label for="rl_is_active" class="form-label fw-semibold small text-white">
                    Status <span class="text-danger">*</span>
                </label>
                <select class="form-select" id="rl_is_active" name="is_active" required>
                    <option value="1" selected>Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
        </form>
    </div>

    <div class="offcanvas-footer border-top p-3" style="border-color: #ffffffab !important">
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Cancel</button>
            <button type="button" class="btn btn-light text-dark border-0" id="saveRoleLevelBtn">
                <i class="bi bi-check-lg me-1"></i>Create Role Level
            </button>
        </div>
    </div>
</div>
