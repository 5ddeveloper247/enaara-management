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
                                id="attachmentName" placeholder="Enter attachment name">
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
                                style="background:rgba(255,255,255,.07) !important;border-color:#ffffff1a !important;"></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label opacity-75 small">Upload Files</label>
                            <label for="attachmentUpload"
                                class="d-flex flex-column align-items-center justify-content-center gap-2 w-100"
                                style="height:130px;border:2px dashed rgba(255,255,255,.3);border-radius:10px;cursor:pointer;background:rgba(255,255,255,.07);">
                                <i class="bi bi-cloud-arrow-up fs-1" style="color:rgba(255,255,255,.4);"></i>
                                <span class="small" style="color:rgba(255,255,255,.5);">Click to upload (multiple allowed)</span>
                                <span class="small" style="color:rgba(255,255,255,.5); font-size: 0.75rem;">(Allowed: JPG, PNG, PDF, DOC, DOCX up to 10MB)</span>
                                <input type="file" id="attachmentUpload" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
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

    function escAtt(s) {
        return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;');
    }

    function escAttrUrl(u) {
        return String(u ?? '').replace(/"/g, '&quot;');
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

        // Clear previous validation errors
        document.querySelectorAll('.attachment-error-msg').forEach(el => el.remove());
        document.querySelectorAll('#attachmentModal .is-invalid').forEach(el => el.classList.remove('is-invalid'));

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
        
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        fetch('{{ route("admin.employee.save_attachment") }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(async response => {
            const isJson = response.headers.get('content-type')?.includes('application/json');
            const data = isJson ? await response.json() : null;

            if (!response.ok) {
                if (data && data.errors) {
                    return { success: false, errors: data.errors };
                }
                if (data && data.message) {
                    return { success: false, message: data.message };
                }
                const errorText = !isJson ? await response.text() : 'Unknown server error';
                throw new Error(errorText.substring(0, 200));
            }
            return data;
        })
        .then(data => {
            if (data.success) {
                const attachment = {
                    localId: 'existing-' + data.attachment_id,
                    existingId: data.attachment_id,
                    name,
                    type,
                    desc,
                    files: [],
                    existingFiles: data.files || []
                };

                window.employeeAttachments.push(attachment);
                renderAttachmentListing();
                resetAttachmentForm();
                
                showToast(data.message || 'Attachment saved successfully', 'success');
                const modalEl = document.getElementById('attachmentModal');
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) modalInstance.hide();
            } else {
                if (data.errors) {
                    for (const key in data.errors) {
                        const errorText = data.errors[key][0];
                        let targetElement;
                        
                        if (key.includes('name')) {
                            targetElement = document.getElementById('attachmentName');
                        } else if (key.includes('type')) {
                            targetElement = document.getElementById('attachmentType');
                        } else if (key.includes('description')) {
                            targetElement = document.getElementById('attachmentDesc');
                        } else if (key.includes('files')) {
                            targetElement = document.getElementById('attachmentUpload').closest('.col-12');
                        }

                        if (targetElement) {
                            const errorSpan = document.createElement('span');
                            errorSpan.className = 'text-danger small attachment-error-msg d-block mt-1';
                            errorSpan.textContent = errorText;
                            
                            if (key.includes('files')) {
                                targetElement.appendChild(errorSpan);
                            } else {
                                targetElement.parentElement.appendChild(errorSpan);
                                targetElement.classList.add('is-invalid');
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Validation Error',
                                text: errorText,
                                confirmButtonColor: '#1a237e'
                            });
                        }
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to save attachment.',
                        confirmButtonColor: '#1a237e'
                    });
                }
            }
        })
        .catch(error => {
            console.error('Error saving attachment:', error);
            Swal.fire({
                icon: 'error',
                title: 'Server Error',
                text: 'Details: ' + error.message,
                confirmButtonColor: '#1a237e'
            });
        })
        .finally(() => {
            isAttachmentSaving = false;
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="bi bi-floppy me-1"></i>Save Attachment';
            }
        });
    }

    function resetAttachmentForm() {
        document.getElementById('attachmentName').value = '';
        document.getElementById('attachmentType').value = '';
        document.getElementById('attachmentDesc').value = '';
        document.getElementById('attachmentUpload').value = '';
        document.getElementById('attachmentUploadPreview').innerHTML = '';
        attachmentUploadedFiles = [];

        // Clear errors
        document.querySelectorAll('.attachment-error-msg').forEach(el => el.remove());
        document.querySelectorAll('#attachmentModal .is-invalid').forEach(el => el.classList.remove('is-invalid'));
    }

    function renderAttachmentListing() {
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
                isImg: (f.type || '').startsWith('image/'),
                url: URL.createObjectURL(f),
            })).concat((a.existingFiles || []).map(f => ({
                name: f.name,
                isImg: (f.mime_type || '').startsWith('image/'),
                url: f.url || null,
            })));

            const generateFilesHtml = (isDark = true) => {
                if (!files.length) return '';
                const btnClass = isDark ? 'btn-outline-light' : 'btn-outline-primary';
                const badgeClass = isDark ? 'bg-light text-muted' : 'bg-light text-dark border-secondary';
                
                return `<div class="mb-2 d-flex flex-column gap-2">
                    ${files.map(f => {
                        const preview = f.isImg && f.url
                            ? `<img src="${escAttrUrl(f.url)}"
                            style="height:40px;width:40px;object-fit:cover;border-radius:6px;border:1px solid ${isDark ? 'rgba(255,255,255,.25)' : '#dee2e6'};flex-shrink:0;">`
                            : `<span class="badge ${badgeClass} border px-2 py-1" style="font-size:10px;">
                               <i class="bi bi-paperclip me-1"></i>${escAtt(f.name)}
                           </span>`;
                        const downloadBtn = f.url
                            ? `<a href="${escAttrUrl(f.url)}" download="${escAtt(f.name)}" class="btn btn-sm ${btnClass} py-0 px-2 align-self-start" style="font-size:11px;">
                                <i class="bi bi-download me-1"></i>Download
                            </a>`
                            : '';
                        return `<div class="d-flex flex-wrap align-items-center gap-2">${preview}${downloadBtn}</div>`;
                    }).join('')}
                   </div>`;
            };

            // Card for Modal (Dark)
            if (modalContainer) {
                modalContainer.insertAdjacentHTML('beforeend', `
                <div class="col-md-6 col-lg-4" id="modal-${escAtt(a.localId)}">
                       <div class="rounded-3 border p-3 h-100" style="border-color:#ffffff1a !important;background:rgba(255,255,255,.07);">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h6 class="mb-0 fw-semibold small text-white">${nameSafe}</h6>
                                <small style="color:rgba(255,255,255,.55);">${typeSafe || '—'}</small>
                            </div>
                            <span class="badge" style="background:rgba(255,255,255,.15);font-size:10px;">${typeSafe || '—'}</span>
                    </div>
                     ${a.desc ? `<div class="rounded-3 border p-2 mb-2" style="border-color:#ffffff1a !important;"><small class="opacity-75 d-block mb-1 text-white-50">Description</small><div class="fw-semibold small text-white">${descSafe}</div></div>` : ''}
                        ${generateFilesHtml(true)}
                        <div class="pt-3 mt-2 border-top d-flex gap-2 justify-content-end" style="border-color:#ffffff1a !important;">
                           <button class="btn btn-sm btn-outline-light" onclick="deleteAttachment('${escAtt(a.localId)}')">
                                <i class="bi bi-trash me-1"></i>Delete
                            </button>
                        </div>
                    </div>
                </div>`);
            }

            // Card for On-Page (Light)
            if (onPageContainer) {
                onPageContainer.insertAdjacentHTML('beforeend', `
                <div class="col-md-6 col-lg-4" id="page-${escAtt(a.localId)}">
                       <div class="card border rounded-3 h-100 shadow-sm bg-white">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h6 class="mb-0 fw-bold small text-dark">${nameSafe}</h6>
                                    <small class="text-muted">${typeSafe || '—'}</small>
                                </div>
                                <span class="badge bg-light text-dark border" style="font-size:10px;">${typeSafe || '—'}</span>
                            </div>
                             ${a.desc ? `<div class="bg-light rounded-3 p-2 mb-2 border-0"><small class="text-muted d-block mb-1" style="font-size:0.7rem;">Description</small><div class="fw-semibold small text-dark" style="font-size:0.8rem;">${descSafe}</div></div>` : ''}
                            ${generateFilesHtml(false)}
                            <div class="pt-3 mt-auto border-top d-flex gap-2 justify-content-end">
                               <button class="btn btn-sm btn-outline-danger border-0" onclick="deleteAttachment('${escAtt(a.localId)}')">
                                    <i class="bi bi-trash me-1"></i>Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>`);
            }
        });
    }

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
            }],
        }));
        renderAttachmentListing();
    };

    window.fetchExistingAttachments = function() {
        const employeeId = document.getElementById('saved_employee_id')?.value;
        const fetchUrl = window.employeeAttachmentsFetchUrl;
        if (!employeeId || !fetchUrl) {
            return;
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