<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="editRoleLevelCanvas"
    aria-labelledby="editRoleLevelCanvasLabel" style="width: 600px;">

    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="editRoleLevelCanvasLabel">
            <i class="bi bi-pencil-square me-2"></i>Edit Role Level
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
            aria-label="Close"></button>
    </div>

    <div class="offcanvas-body">
        <form id="editRoleLevelForm" method="POST" action="javascript:void(0);" novalidate>
            @csrf
            <input type="hidden" id="edit_rl_id" name="id">

            <div class="mb-3">
                <label for="edit_rl_name" class="form-label fw-semibold small text-white">
                    Name <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="edit_rl_name" name="name" placeholder="Enter role level name" required>
            </div>

            <div class="mb-3">
                <label for="edit_rl_level" class="form-label fw-semibold small text-white">
                    Level <span class="text-danger">*</span>
                </label>
                <input type="number" class="form-control" id="edit_rl_level" name="level" placeholder="Enter priority level (e.g. 1, 2, 3)" min="1" required>
            </div>

            <div class="mb-3">
                <label for="edit_rl_description" class="form-label fw-semibold small text-white">
                    Description
                </label>
                <textarea class="form-control" id="edit_rl_description" name="description" rows="3" placeholder="Enter description"></textarea>
            </div>

            <div class="mb-3">
                <label for="edit_rl_is_active" class="form-label fw-semibold small text-white">
                    Status <span class="text-danger">*</span>
                </label>
                <select class="form-select" id="edit_rl_is_active" name="is_active" required>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
        </form>
    </div>

    <div class="offcanvas-footer border-top p-3" style="border-color: #ffffffab !important">
        <div class="d-flex justify-content-end align-items-center gap-2">
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="button" class="btn btn-light text-dark border-0" id="updateRoleLevelBtn">
                    <i class="bi bi-check-lg me-1"></i>Update Role Level
                </button>
            </div>
        </div>
    </div>
</div>
