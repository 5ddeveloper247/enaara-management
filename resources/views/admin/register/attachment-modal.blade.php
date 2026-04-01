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
.form-select option{
    border: none !important;
}
</style>

<div class="modal fade" id="attachmentModal" tabindex="-1" aria-labelledby="deleteRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-main text-white">

            <div class="modal-header pb-0 border-bottom" style="border-color:#ffffff42 !important">
                <h5 class="modal-title" id="deleteRoleModalLabel">Attachments</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <div class="modal-body pt-2">

                <ul class="nav nav-tabs" id="attachmentTabs" style="border-color:#ffffff42;">
                    <li class="nav-item">
                        <button class="nav-link text-black active" data-bs-toggle="tab" data-bs-target="#tab-add"
                            style="border-color:#ffffff42 #ffffff42 transparent;">
                            <i class="bi bi-plus-circle me-1"></i>Add Attachment
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link text-white opacity-75" data-bs-toggle="tab" data-bs-target="#tab-list"
                            style="border-color:#ffffff42 #ffffff42 transparent;">
                            <i class="bi bi-list-ul me-1"></i>All Attachments
                            <span class="badge ms-1" id="attachmentCountBadge"
                                style="background:rgba(255,255,255,.2);color:#fff;">0</span>
                        </button>
                    </li>
                </ul>

                <div class="tab-content pt-3">

                    {{-- ADD TAB --}}
                    <div class="tab-pane fade show active" id="tab-add">
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
                                    <span class="small" style="color:rgba(255,255,255,.5);">Click to upload (multiple
                                        allowed)</span>
                                    <input type="file" id="attachmentUpload" accept="image/*,.pdf,.doc,.docx"
                                        class="d-none" multiple onchange="previewAttachmentUpload(this)">
                                </label>
                                <div id="attachmentUploadPreview" class="d-flex flex-wrap gap-2 mt-2"></div>
                            </div>

                        </div>

                        <div class="d-flex justify-content-end mt-4 gap-2 pt-3 border-top"
                            style="border-color:#ffffffab !important">
                            <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-outline-light" onclick="saveAttachment()">
                                <i class="bi bi-floppy me-1"></i>Save Attachment
                            </button>
                        </div>
                    </div>

                    {{-- LIST TAB --}}
                    <div class="tab-pane fade" id="tab-list">
                        <div id="attachmentListingEmpty" class="text-center py-4 small"
                            style="color:rgba(255,255,255,.5);">
                            No attachments added yet.
                        </div>
                        <div id="attachmentListing" class="row g-3"></div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
    let employeeAttachments = [];
    let attachmentUploadedFiles = [];

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
        const name = document.getElementById('attachmentName').value.trim();
        const type = document.getElementById('attachmentType').value;
        const desc = document.getElementById('attachmentDesc').value.trim();

        if (!name) {
            alert('Please enter an attachment name.');
            return;
        }

        const files = attachmentUploadedFiles.filter(Boolean);
        if (!files.length) {
            alert('Please select at least one file.');
            return;
        }

        const attachment = {
            localId: 'att-' + Date.now(),
            name,
            type,
            desc,
            files,
            existingId: null,
        };

        employeeAttachments.push(attachment);
        renderAttachmentListing();
        resetAttachmentForm();
        document.querySelector('[data-bs-target="#tab-list"]').click();
    }

    function resetAttachmentForm() {
        document.getElementById('attachmentName').value = '';
        document.getElementById('attachmentType').value = '';
        document.getElementById('attachmentDesc').value = '';
        document.getElementById('attachmentUpload').value = '';
        document.getElementById('attachmentUploadPreview').innerHTML = '';
        attachmentUploadedFiles = [];
    }

    function renderAttachmentListing() {
        const container = document.getElementById('attachmentListing');
        const empty = document.getElementById('attachmentListingEmpty');
        const badge = document.getElementById('attachmentCountBadge');

        badge.textContent = employeeAttachments.length;
        empty.classList.toggle('d-none', employeeAttachments.length > 0);
        container.innerHTML = '';

        employeeAttachments.forEach(a => {
            const files = (a.files || []).map(f => ({
                name: f.name,
                isImg: (f.type || '').startsWith('image/'),
                url: (f.type || '').startsWith('image/') ? URL.createObjectURL(f) : null,
            })).concat((a.existingFiles || []).map(f => ({
                name: f.name,
                isImg: (f.mime_type || '').startsWith('image/'),
                url: f.url || null,
            })));
            const filesHtml = files.length ?
                `<div class="mb-2 d-flex flex-wrap gap-1">
                ${files.map(f => f.isImg
                    ? `<img src="${f.url}"
                        style="height:40px;width:40px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6;">`
                    : `<span class="badge bg-light text-muted border" style="font-size:10px;">
                           <i class="bi bi-paperclip me-1"></i>${f.name}
                       </span>`
                ).join('')}
               </div>` :
                '';

            container.insertAdjacentHTML('beforeend', `
            <div class="col-md-6 col-lg-4" id="${a.localId}">
                   <div class="rounded-3 border p-3 h-100" style="border-color:#ffffff1a !important;background:rgba(255,255,255,.07);">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="mb-0 fw-semibold small text-white">${a.name}</h6>
                            <small style="color:rgba(255,255,255,.55);">${a.type || '—'}</small>
                        </div>
                        <span class="badge" style="background:rgba(255,255,255,.15);font-size:10px;">${a.type || '—'}</span>
                </div>
                 ${a.desc ? `
                 <div class="rounded-3 border p-2 mb-2" style="border-color:#ffffff1a !important;">
                     <small class="opacity-75 d-block mb-1">Description</small>
                     <div class="fw-semibold small">${a.desc}</div>
                 </div>` : ''}
                    ${filesHtml}
                    <div class="pt-3 mt-2 border-top d-flex gap-2 justify-content-end" style="border-color:#ffffff1a !important;">
                        <button class="btn btn-sm btn-outline-light" onclick="deleteAttachment('${a.localId}')">
                            <i class="bi bi-trash me-1"></i>Delete
                        </button>
                    </div>
                </div>
            </div>`);
        });
    }

    function deleteAttachment(localId) {
        employeeAttachments = employeeAttachments.filter(x => x.localId !== localId);
        renderAttachmentListing();
    }

    window.getAttachmentPayload = function () {
        const keptAttachmentIds = employeeAttachments
            .filter(a => !!a.existingId)
            .map(a => a.existingId);
        const newAttachments = employeeAttachments
            .filter(a => !a.existingId && Array.isArray(a.files) && a.files.length);

        return { keptAttachmentIds, newAttachments };
    };

    window.setExistingAttachments = function (attachments) {
        employeeAttachments = (attachments || []).map((a) => ({
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
</script>
