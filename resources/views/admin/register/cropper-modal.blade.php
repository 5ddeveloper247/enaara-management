<!-- Cropper Modal -->
<div class="modal fade" id="cropperModal" tabindex="-1" aria-labelledby="cropperModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-main text-white">
                <h5 class="modal-title" id="cropperModalLabel"><i class="bi bi-crop me-2"></i>Crop Your Profile Photo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" onclick="cancelCrop()"></button>
            </div>
            <div class="modal-body p-0 bg-light">
                <div class="img-container p-3" style="max-height: 500px; display: flex; justify-content: center; align-items: center; overflow: hidden;">
                    <img id="cropperImage" src="" style="max-width: 100%;">
                </div>
            </div>
            <div class="modal-footer bg-white border-top">
                <button type="button" class="btn btn-outline-secondary rounded-2 px-4" data-bs-dismiss="modal" onclick="cancelCrop()">Cancel</button>
                <button type="button" class="btn bg-main text-white rounded-2 px-4" id="cropBtn">Crop & Save</button>
            </div>
        </div>
    </div>
</div>

<script>
    let cropper = null;
    let croppedImageBlob = null;
    let originalFileName = "";

    function openCropper(inputFile) {
        if (!inputFile.files || !inputFile.files[0]) return;
        
        const file = inputFile.files[0];
        
        // Basic validation before opening
        const allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
        const extension = file.name.split('.').pop().toLowerCase();
        
        if (!allowedExtensions.includes(extension)) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid File Type',
                text: 'Only JPG, PNG, GIF, and SVG files are allowed.',
                confirmButtonColor: '#012445'
            });
            inputFile.value = '';
            return;
        }
        
        const maxSize = 2 * 1024 * 1024; // 2MB
        if (file.size > maxSize) {
            Swal.fire({
                icon: 'error',
                title: 'File Too Large',
                text: 'Maximum allowed file size is 2MB.',
                confirmButtonColor: '#012445'
            });
            inputFile.value = '';
            return;
        }

        originalFileName = file.name;
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const cropperImage = document.getElementById('cropperImage');
            cropperImage.src = e.target.result;
            
            const modal = new bootstrap.Modal(document.getElementById('cropperModal'));
            modal.show();
            
            // Wait for modal transition then init cropper
            document.getElementById('cropperModal').addEventListener('shown.bs.modal', function onShown() {
                if (cropper) cropper.destroy();
                cropper = new Cropper(cropperImage, {
                    aspectRatio: 1, // Square for profile photo
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 0.8,
                    restore: false,
                    guides: true,
                    center: true,
                    highlight: false,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    toggleDragModeOnDblclick: false,
                });
                document.getElementById('cropperModal').removeEventListener('shown.bs.modal', onShown);
            });
        };
        reader.readAsDataURL(file);
    }

    function cancelCrop() {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        // If we cancel, we might want to clear the input if no image was previously cropped
        if (!croppedImageBlob) {
            const inp = document.getElementById('uploadImage');
            if(inp) inp.value = '';
        }
    }

    document.getElementById('cropBtn').addEventListener('click', function() {
        if (!cropper) return;
        
        // Get cropped canvas
        const canvas = cropper.getCroppedCanvas({
            width: 500,  // Higher res for quality
            height: 500,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        });
        
        canvas.toBlob(function(blob) {
            croppedImageBlob = blob;
            
            // Update preview
            const preview = document.getElementById('imgPreview');
            const previewWrapper = document.getElementById('imgPreviewWrapper');
            const removeBtn = document.getElementById('removeImageBtn');
            const uploadBox = document.getElementById('uploadImageBox');
            
            preview.src = URL.createObjectURL(blob);
            previewWrapper.style.display = 'block';
            uploadBox.classList.add('d-none');
            removeBtn.classList.remove('d-none');
            
            // Close modal
            const modalEl = document.getElementById('cropperModal');
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            modalInstance.hide();
            
            cropper.destroy();
            cropper = null;

            // If Employee ID exists, save the photo instantly
            const employeeId = document.querySelector('input[name="employee_id"]')?.value;
            if (employeeId) {
                const formData = new FormData();
                formData.append('employee_id', employeeId);
                formData.append('subsection', 'photo');
                formData.append('profile_photo', blob, originalFileName);
                formData.append('step', document.getElementById('stepInput')?.value || 1);
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                fetch('{{ route("admin.employee.save_subsection") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (typeof showToast === 'function') {
                            showToast(data.message || 'Profile photo saved successfully.', 'success');
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to save photo.'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error saving photo:', error);
                    if (typeof showToast === 'function') showToast('Error saving profile photo.', 'error');
                });
            }

        }, 'image/jpeg', 0.9);
    });
</script>
