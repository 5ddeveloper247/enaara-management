<div class="modal fade" id="deleteWorkTypeModal" tabindex="-1" aria-labelledby="deleteWorkTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="deleteWorkTypeModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                </div>
                <p class="text-center mb-0">Are you sure you want to delete this work type?</p>
                <p class="text-center text-muted small mt-2" id="deleteWorkTypeName"></p>
                <p class="text-center text-danger small mt-2">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteWorkTypeBtn">Delete</button>
            </div>
        </div>
    </div>
</div>
