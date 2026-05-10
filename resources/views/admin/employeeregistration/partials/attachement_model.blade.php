<style>
    /* Active tab */
    #attachmentModal .nav-link.active {
        background: rgba(255, 255, 255, .12) !important;
        color: #fff !important;
    }

    #attachmentModal .nav-link:not(.active) {
        color: rgba(255, 255, 255, .6) !important;
    }

    /* Inputs, selects, textareas */
    #attachmentModal .form-control,
    #attachmentModal .form-select {
        color: #fff !important;
    }

    #attachmentModal .form-control::placeholder {
        color: rgba(255, 255, 255, .35) !important;
    }

    #attachmentModal .form-control:focus,
    #attachmentModal .form-select:focus {
        background: rgba(255, 255, 255, .12) !important;
        border-color: rgba(255, 255, 255, .35) !important;
        box-shadow: none !important;
        color: #fff !important;
    }

    .form-select option {
        border: none !important;
    }

    /* Ensure SweetAlert appears above Bootstrap modals */
    .swal2-container {
        z-index: 10000 !important;
    }
</style>

<div class="modal fade" id="attachmentModal" tabindex="-1" aria-labelledby="deleteRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-main text-white">

            <div class="modal-header pb-2 border-bottom" style="border-color:#ffffff42 !important">
                <h5 class="modal-title" id="attachmentModalLabel"><i class="bi bi-paperclip me-2"></i>Add Attachment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <div class="modal-body pt-2">

                <div class="pt-0">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label opacity-75 small">Name</label>
                            <input type="text" class="form-control border"
                                style="background:rgba(255,255,255,.07);border-color:#ffffff1a !important;"
                                id="attachmentName" placeholder="Enter attachment name" maxlength="255">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label opacity-75 small">Type</label>
                            <select class="form-select text-white border" id="attachmentType"
                                style="background:rgba(255,255,255,.07) !important;border-color:#ffffff1a !important;">
                                <option value="" style="background:#012445 !important">Select type</option>
                                <option style="background:#012445 !important">CNIC</option>
                                <option style="background:#012445 !important">Passport</option>
                                <option style="background:#012445 !important">Contract</option>
                                <option style="background:#012445 !important">Certificate</option>
                                <option style="background:#012445 !important">Other</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label opacity-75 small">Description</label>
                            <textarea class="form-control text-white border" id="attachmentDesc" rows="3" placeholder="Enter description"
                                style="background:rgba(255,255,255,.07) !important;border-color:#ffffff1a !important;" maxlength="1000"></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label opacity-75 small">Upload Files</label>
                            <label for="attachmentUpload"
                                class="d-flex flex-column align-items-center justify-content-center gap-2 w-100"
                                style="height:130px;border:2px dashed rgba(255,255,255,.3);border-radius:10px;cursor:pointer;background:rgba(255,255,255,.07);">
                                <i class="bi bi-cloud-arrow-up fs-1" style="color:rgba(255,255,255,.4);"></i>
                                <span class="small" style="color:rgba(255,255,255,.5);">Click to upload (multiple allowed)</span>
                                <span class="small" style="color:rgba(255,255,255,.5); font-size: 0.75rem;">(Allowed: JPG, PNG, PDF, DOC, DOCX, XLS, XLSX, ZIP, TXT up to 20MB)</span>
                                <input type="file" id="attachmentUpload" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.zip,.txt"
                                    class="d-none" multiple onchange="previewAttachmentUpload(this)">
                            </label>
                            <div id="attachmentUploadPreview" class="d-flex flex-wrap gap-2 mt-2"></div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4 gap-2 pt-3 border-top"
                        style="border-color:#ffffffab !important">
                        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-outline-light" id="attachmentSaveBtn" onclick="saveAttachment()">
                            <i class="bi bi-floppy me-1"></i>Save Attachment
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    window.employeeAttachments = window.employeeAttachments || [];
    let attachmentUploadedFiles = [];
    let isAttachmentSaving = false;
    const attachmentAllowedExtensions = new Set(['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xlsx', 'zip', 'xls', 'txt']);
    const attachmentMaxFileSizeBytes = 20 * 1024 * 1024;
    
    const escAtt = (str) => {
        if (!str) return '';
        return String(str).replace(/[&<>"']/g, (m) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
        })[m]);
    };

    const escAttrUrl = (url) => {
        if (!url) return 'javascript:void(0)';
        return String(url).replace(/[&<>"']/g, (m) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
        })[m]);
    };

    function attachmentShowInlineError(target, message) {
        if (!target) return;
        const parent = target.closest('.col-12, .col-md-6') || target.parentElement;
        if (!parent) return;
        target.classList.add('is-invalid');
        const err = document.createElement('span');
        err.className = 'text-danger small attachment-error-msg d-block mt-1';
        err.textContent = message;
        parent.appendChild(err);
    }

    function attachmentClearInlineErrors() {
        document.querySelectorAll('.attachment-error-msg').forEach(el => el.remove());
        document.querySelectorAll('#attachmentModal .is-invalid').forEach(el => el.classList.remove('is-invalid'));
    }

    function getAttachmentFileExtension(fileName) {
        const parts = String(fileName || '').toLowerCase().split('.');
        return parts.length > 1 ? parts.pop() : '';
    }

    function validateAttachmentForm(name, type, desc, files) {
        const errors = {};
        const nameInput = document.getElementById('attachmentName');
        const typeInput = document.getElementById('attachmentType');
        const descInput = document.getElementById('attachmentDesc');
        const uploadInput = document.getElementById('attachmentUpload');

        if (!name) {
            errors.name = 'Attachment name is required.';
        } else if (name.length > 255) {
            errors.name = 'Attachment name must not exceed 255 characters.';
        }

        if (type && type.length > 100) {
            errors.type = 'Attachment type must not exceed 100 characters.';
        }

        if (desc && desc.length > 1000) {
            errors.description = 'Attachment description must not exceed 1000 characters.';
        }

        if (!files.length) {
            errors.files = 'Please upload at least one valid file.';
        } else {
            const invalidType = files.find((f) => !attachmentAllowedExtensions.has(getAttachmentFileExtension(f.name)));
            if (invalidType) {
                errors.files = 'Attachment file must be of type: jpg, jpeg, png, pdf, doc, docx, xls, xlsx, zip, or txt.';
            } else {
                const oversize = files.find((f) => f.size > attachmentMaxFileSizeBytes);
                if (oversize) {
                    errors.files = 'Each attachment file must not exceed 20 MB.';
                }
            }
        }

        if (Object.keys(errors).length > 0) {
            if (errors.name) attachmentShowInlineError(nameInput, errors.name);
            if (errors.type) attachmentShowInlineError(typeInput, errors.type);
            if (errors.description) attachmentShowInlineError(descInput, errors.description);
            if (errors.files) {
                const uploadTarget = uploadInput ? uploadInput.closest('.col-12') : null;
                if (uploadTarget) {
                    const err = document.createElement('span');
                    err.className = 'text-danger small attachment-error-msg d-block mt-1';
                    err.textContent = errors.files;
                    uploadTarget.appendChild(err);
                }
            }
            return false;
        }

        return true;
    }


    function previewAttachmentUpload(input) {
        const preview = document.getElementById('attachmentUploadPreview');

        Array.from(input.files).forEach((file) => {
            attachmentUploadedFiles.push(file);
            const idx = attachmentUploadedFiles.length - 1;
            const isImage = file.type.startsWith('image/');

            const wrapper = document.createElement('div');
            wrapper.className = 'position-relative';
            wrapper.id = 'upload-preview-' + idx;
            wrapper.style.cssText = 'width:80px;height:80px;flex-shrink:0;';

            wrapper.innerHTML = isImage ?
                `<img src="${URL.createObjectURL(file)}"
                style="width:80px;height:80px;object-fit:cover;border-radius:8px;border:2px solid var(--main-color);">` :
                `<div class="d-flex flex-column align-items-center justify-content-center h-100 bg-light border text-muted text-center p-1"
                style="width:80px;height:80px;border:2px solid var(--main-color)!important;border-radius:8px;font-size:.6rem;">
                <i class="bi bi-file-earmark fs-4 d-block mb-1"></i>
                <span style="word-break:break-all;line-height:1.2;">${file.name.length > 12 ? file.name.substring(0, 12) + '...' : file.name}</span>
               </div>`;

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.innerHTML = '<i class="bi bi-x"></i>';
            removeBtn.style.cssText =
                'position:absolute;top:-6px;right:-6px;width:20px;height:20px;border-radius:50%;border:none;background:var(--main-color);color:#fff;font-size:.65rem;display:flex;align-items:center;justify-content:center;padding:0;cursor:pointer;line-height:1;';
            removeBtn.onclick = () => {
                attachmentUploadedFiles[idx] = null;
                document.getElementById('upload-preview-' + idx)?.remove();
            };

            wrapper.appendChild(removeBtn);
            preview.appendChild(wrapper);
        });

        // Reset so same file can be picked again after removal
        input.value = '';
    }

    function saveAttachment() {
        if (isAttachmentSaving) {
            return;
        }

        const saveBtn = document.getElementById('attachmentSaveBtn');
        const name = document.getElementById('attachmentName').value.trim();
        const type = document.getElementById('attachmentType').value;
        const desc = document.getElementById('attachmentDesc').value.trim();
        const employeeId = document.getElementById('saved_employee_id')?.value;

        attachmentClearInlineErrors();

        if (!employeeId) {
            Swal.fire({
                icon: 'warning',
                title: 'Employee Not Saved',
                text: 'Please complete step 1 and save the employee before uploading attachments.',
                confirmButtonColor: '#1a237e'
            });
            return;
        }

        const files = attachmentUploadedFiles.filter(Boolean);
        if (!validateAttachmentForm(name, type, desc, files)) {
            return;
        }

        isAttachmentSaving = true;
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
        }

        const formData = new FormData();
        formData.append('employee_id', employeeId);
        formData.append('step', 6);
        formData.append('subsection', 'attachment');
        formData.append('attachments[0][name]', name);
        formData.append('attachments[0][type]', type);
        formData.append('attachments[0][description]', desc);
        
        files.forEach(f => {
            formData.append('attachments[0][files][]', f);
        });
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Create temporary attachment for progress display
        const tempId = 'uploading-' + Date.now();
        const tempAttachment = {
            localId: tempId,
            name: name,
            type: type,
            desc: desc,
            files: files.map(f => ({
                file: f,
                name: f.name,
                size: f.size,
                progress: 0,
                type: getAttachmentFileExtension(f.name).toUpperCase()
            })),
            existingFiles: []
        };

        window.employeeAttachments.push(tempAttachment);
        renderAttachmentListing();

        // Close modal immediately or keep it open?
        // Usually better to keep it open until success, but let's hide it to show progress on page
        const modalEl = document.getElementById('attachmentModal');
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) modalInstance.hide();

        const xhr = new XMLHttpRequest();
        xhr.open('POST', '{{ route("admin.employee.save_attachment") }}', true);
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);

        xhr.upload.onprogress = (e) => {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                tempAttachment.files.forEach(f => {
                    f.progress = percent;
                });
                renderAttachmentListing();
            }
        };

        xhr.onload = function() {
            let data = {};
            try { data = JSON.parse(xhr.responseText); } catch(e) {}

            if (xhr.status >= 200 && xhr.status < 300 && data.success) {
                // Success
                window.employeeAttachments = window.employeeAttachments.filter(a => a.localId !== tempId);
                if (window.fetchExistingAttachments) window.fetchExistingAttachments();
                
                resetAttachmentForm();
                showToast(data.message || 'Attachment saved successfully', 'success');
            } else {
                // Error
                window.employeeAttachments = window.employeeAttachments.filter(a => a.localId !== tempId);
                renderAttachmentListing();
                
                if (data.errors) {
                    // Re-open modal if there are validation errors?
                    // For now just alert
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please check your inputs and try again.',
                        confirmButtonColor: '#1a237e'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to save attachment.',
                        confirmButtonColor: '#1a237e'
                    });
                }
            }
        };

        xhr.onerror = function() {
            window.employeeAttachments = window.employeeAttachments.filter(a => a.localId !== tempId);
            renderAttachmentListing();
            Swal.fire({
                icon: 'error',
                title: 'Server Error',
                text: 'Something went wrong on the server.',
                confirmButtonColor: '#1a237e'
            });
        };

        xhr.send(formData);
    }

    function resetAttachmentForm() {
        document.getElementById('attachmentName').value = '';
        document.getElementById('attachmentType').value = '';
        document.getElementById('attachmentDesc').value = '';
        document.getElementById('attachmentUpload').value = '';
        document.getElementById('attachmentUploadPreview').innerHTML = '';
        attachmentUploadedFiles = [];

        attachmentClearInlineErrors();
    }

    window.renderAttachmentListing = function() {
        try {
            const modalContainer = document.getElementById('attachmentListing');
        const modalEmpty = document.getElementById('attachmentListingEmpty');
        const badge = document.getElementById('attachmentCountBadge');
        
        const onPageContainer = document.getElementById('onPageAttachmentListing');
        const onPageEmpty = document.getElementById('onPageAttachmentListingEmpty');

        if (badge) badge.textContent = window.employeeAttachments.length;
        if (modalEmpty) modalEmpty.classList.toggle('d-none', window.employeeAttachments.length > 0);
        if (onPageEmpty) onPageEmpty.classList.toggle('d-none', window.employeeAttachments.length > 0);
        
        if (modalContainer) modalContainer.innerHTML = '';
        if (onPageContainer) onPageContainer.innerHTML = '';

        window.employeeAttachments.forEach(a => {
            const nameSafe = escAtt(a.name);
            const typeSafe = escAtt(a.type || '');
            const descSafe = escAtt(a.desc || '');
            const files = (a.files || []).map(f => ({
                name: f.name,
                size: f.size,
                type: f.type || getAttachmentFileExtension(f.name).toUpperCase(),
                url: f.file ? URL.createObjectURL(f.file) : null,
                progress: f.progress
            })).concat((a.existingFiles || []).map(f => ({
                name: f.name,
                size: f.size,
                type: getAttachmentFileExtension(f.name).toUpperCase(),
                url: f.url || null,
            })));

            const formatSize = (bytes) => {
                if (!bytes) return '';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
            };

            const getIconColor = (ext) => {
                const colors = {
                    'PDF': '#ff5252',
                    'DOC': '#2b579a',
                    'DOCX': '#2b579a',
                    'XLS': '#217346',
                    'XLSX': '#217346',
                    'ZIP': '#ffc107',
                    'PNG': '#4caf50',
                    'JPG': '#4caf50',
                    'JPEG': '#4caf50',
                    'TXT': '#607d8b'
                };
                return colors[ext] || '#9e9e9e';
            };

            const generateListHtml = (isDark = true) => {
                if (!files.length) return '';
                return files.map(f => {
                    const isUploading = f.progress !== undefined && f.progress < 100;
                    return `
                    <div class="d-flex align-items-center p-3 mb-3 rounded-4 bg-white shadow-sm position-relative" style="border: 1px solid rgba(0,0,0,0.05);">
                        <!-- File Icon -->
                        <div class="d-flex align-items-center justify-content-center rounded-3 me-3" 
                             style="width: 54px; height: 54px; background: ${getIconColor(f.type)}; color: #fff; font-size: 0.8rem; font-weight: 800; position: relative; overflow: hidden; flex-shrink: 0;">
                            <div style="position: absolute; top: 0; right: 0; width: 0; height: 0; border-style: solid; border-width: 0 14px 14px 0; border-color: transparent rgba(255,255,255,0.4) transparent transparent;"></div>
                            ${f.type}
                        </div>
                        
                        <!-- File Info -->
                        <div class="flex-grow-1 overflow-hidden me-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="mb-0 text-dark fw-bold text-truncate" style="font-size: 0.95rem;">${escAtt(f.name)}</h6>
                                ${isUploading ? `<span class="text-primary fw-bold" style="font-size: 0.75rem;">${f.progress}%</span>` : ''}
                            </div>
                            
                            ${isUploading ? `
                                <div class="progress mt-1" style="height: 4px; background: #eee; border-radius: 2px;">
                                    <div class="progress-bar" style="width: ${f.progress}%; background: #4a90e2; border-radius: 2px;"></div>
                                </div>
                            ` : `
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-muted" style="font-size: 0.75rem;">${formatSize(f.size) || '—'}</span>
                                    ${typeSafe ? `<span class="badge bg-light text-muted border-0 fw-normal" style="font-size: 0.65rem;">${typeSafe}</span>` : ''}
                                </div>
                            `}
                        </div>

                        <!-- Actions -->
                        <div class="d-flex align-items-center gap-2">
                            ${!isUploading && f.url ? `
                                <a href="${escAttrUrl(f.url)}" target="_blank" class="text-success text-decoration-none d-flex align-items-center justify-content-center rounded-circle" 
                                   style="width: 32px; height: 32px; background: rgba(40, 167, 69, 0.1);" title="View">
                                    <i class="bi bi-check-lg fs-5"></i>
                                </a>
                            ` : ''}
                            <button onclick="deleteAttachment('${escAtt(a.localId)}')" class="text-danger border-0 p-0 d-flex align-items-center justify-content-center rounded-circle" 
                                    style="width: 32px; height: 32px; background: rgba(220, 53, 69, 0.1); cursor: pointer;" title="Delete">
                                <i class="bi bi-x-lg" style="font-size: 0.85rem;"></i>
                            </button>
                        </div>
                    </div>
                `}).join('');
            };

            const html = generateListHtml();
            if (modalContainer) modalContainer.insertAdjacentHTML('beforeend', `<div class="col-12">${html}</div>`);
            if (onPageContainer) onPageContainer.insertAdjacentHTML('beforeend', `<div class="col-12">${html}</div>`);
        });
        } catch (error) {
            console.error('Error rendering attachment list:', error);
        }
    };

    function deleteAttachment(localId, dbId = null) {
        if (!dbId && !localId.startsWith('existing-')) {
            // It's a local un-saved attachment (should rarely happen now)
            window.employeeAttachments = window.employeeAttachments.filter(x => x.localId !== localId);
            renderAttachmentListing();
            return;
        }

        const actualDbId = dbId || localId.replace('existing-', '');

        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to permanently delete this attachment.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#012445',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('id', actualDbId);
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                fetch('{{ route("admin.employee.delete_attachment") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.employeeAttachments = window.employeeAttachments.filter(x => x.localId !== localId);
                        renderAttachmentListing();
                        showToast(data.message, 'success');
                    } else {
                        showToast(data.message || 'Error deleting attachment.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Server error while deleting attachment.', 'error');
                });
            }
        });
    }

    window.getAttachmentPayload = function() {
        const keptAttachmentIds = window.employeeAttachments
            .filter(a => !!a.existingId)
            .map(a => a.existingId);
        const newAttachments = window.employeeAttachments
            .filter(a => !a.existingId && Array.isArray(a.files) && a.files.length);

        return {
            keptAttachmentIds,
            newAttachments
        };
    };

    window.setExistingAttachments = function(attachments) {
        window.employeeAttachments = (attachments || []).map((a) => ({
            localId: 'existing-' + a.id,
            existingId: a.id,
            name: a.name || a.file_name || 'Attachment',
            type: a.type || '',
            desc: a.description || '',
            files: [],
            existingFiles: [{
                name: a.file_name || a.name || 'Attachment',
                mime_type: a.mime_type || '',
                url: a.url || '',
                size: a.file_size || 0,
            }],
        }));
        renderAttachmentListing();
    };

    window.fetchExistingAttachments = function() {
        const employeeId = document.getElementById('saved_employee_id')?.value;
        let fetchUrl = window.employeeAttachmentsFetchUrl;
        
        if (!employeeId) return;
        
        if (!fetchUrl) {
            fetchUrl = `/admin/employees/${employeeId}/attachments`;
        }

        fetch(fetchUrl, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data && data.success && Array.isArray(data.attachments)) {
                window.setExistingAttachments(data.attachments);
            }
        })
        .catch(() => {});
    };

    document.addEventListener('DOMContentLoaded', function() {
        window.fetchExistingAttachments();
    });
</script>