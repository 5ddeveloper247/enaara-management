<!-- Delete Role Confirmation Modal -->
<div class="modal fade" id="deleteRoleModal" tabindex="-1" aria-labelledby="deleteRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="deleteRoleModalLabel">
                    <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Delete Role
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Are you sure you want to delete the role <strong id="deleteRoleName">-</strong>?</p>
                <div class="alert alert-warning mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    <small>This action cannot be undone. Users assigned to this role will need to be reassigned.</small>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteRoleBtn">
                    <i class="bi bi-trash me-1"></i>Delete Role
                </button>
            </div>
        </div>
    </div>
</div>

