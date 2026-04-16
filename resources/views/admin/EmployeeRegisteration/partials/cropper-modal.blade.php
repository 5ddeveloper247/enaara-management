<!-- Cropper Modal -->
<div class="modal fade" id="cropperModal" tabindex="-1" aria-labelledby="cropperModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-main text-white">
                <h5 class="modal-title" id="cropperModalLabel"><i class="bi bi-crop me-2"></i>Crop Your Profile Photo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" onclick="cancelCrop()"></button>
            </div>
            <div class="modal-body p-0 bg-light">
                <div class="cropper-container-wrapper p-3">
                    <img id="cropperImage" src="">
                </div>
            </div>
            <div class="modal-footer bg-white border-top">
                <button type="button" class="btn btn-outline-secondary rounded-2 px-4" data-bs-dismiss="modal" onclick="cancelCrop()">Cancel</button>
                <button type="button" class="btn bg-main text-white rounded-2 px-4" id="cropBtn">Crop & Save</button>
            </div>
        </div>
    </div>
</div>
