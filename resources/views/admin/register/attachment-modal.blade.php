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

                <ul class="nav nav-tabs" id="roleTabs" style="border-color:#ffffff42;">
                    <li class="nav-item">
                        <button class="nav-link text-black active" data-bs-toggle="tab" data-bs-target="#tab-add"
                            style="border-color:#ffffff42 #ffffff42 transparent;">
                            <i class="bi bi-plus-circle me-1"></i>Add Role
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link text-white opacity-75" data-bs-toggle="tab" data-bs-target="#tab-list"
                            style="border-color:#ffffff42 #ffffff42 transparent;">
                            <i class="bi bi-list-ul me-1"></i>All Roles
                            <span class="badge ms-1" id="roleCountBadge"
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
                                    id="roleName" placeholder="Enter role name">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label opacity-75 small">Type</label>
                                <select class="form-select text-white border" id="roleType"
                                    style="background:rgba(255,255,255,.07) !important;border-color:#ffffff1a !important;">
                                    <option value="" style="background:#012445 !important">Select type</option>
                                    <option style="background:#012445 !important">Admin</option>
                                    <option style="background:#012445 !important">Manager</option>
                                    <option style="background:#012445 !important">Supervisor</option>
                                    <option style="background:#012445 !important">Staff</option>
                                    <option style="background:#012445 !important">Guest</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label opacity-75 small">Description</label>
                                <textarea class="form-control text-white border" id="roleDesc" rows="3" placeholder="Enter description"
                                    style="background:rgba(255,255,255,.07) !important;border-color:#ffffff1a !important;"></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label opacity-75 small">Upload Files</label>
                                <label for="roleUpload"
                                    class="d-flex flex-column align-items-center justify-content-center gap-2 w-100"
                                    style="height:130px;border:2px dashed rgba(255,255,255,.3);border-radius:10px;cursor:pointer;background:rgba(255,255,255,.07);">
                                    <i class="bi bi-cloud-arrow-up fs-1" style="color:rgba(255,255,255,.4);"></i>
                                    <span class="small" style="color:rgba(255,255,255,.5);">Click to upload (multiple
                                        allowed)</span>
                                    <input type="file" id="roleUpload" accept="image/*,.pdf,.doc,.docx"
                                        class="d-none" multiple onchange="previewRoleUpload(this)">
                                </label>
                                <div id="roleUploadPreview" class="d-flex flex-wrap gap-2 mt-2"></div>
                            </div>

                        </div>

                        <div class="d-flex justify-content-end mt-4 gap-2 pt-3 border-top"
                            style="border-color:#ffffffab !important">
                            <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-outline-light" onclick="saveRole()">
                                <i class="bi bi-floppy me-1"></i>Save Role
                            </button>
                        </div>
                    </div>

                    {{-- LIST TAB --}}
                    <div class="tab-pane fade" id="tab-list">

                        <div id="roleEditForm" class="rounded-3 border p-3 mb-3 d-none"
                            style="border-color:#ffffff1a !important;background:rgba(255,255,255,.07);">
                            <h6 class="mb-3 fw-semibold small">
                                <i class="bi bi-pencil me-2"></i>Edit Role
                            </h6>
                            <div class="row g-2">
                                <input type="hidden" id="editRoleId">
                                <div class="col-md-6">
                                    <input type="text" class="form-control form-control-sm text-white border"
                                        id="editRoleName" placeholder="Name"
                                        style="background:rgba(255,255,255,.07);border-color:#ffffff1a !important;">
                                </div>
                                <div class="col-md-6">
                                    <select class="form-select form-select-sm text-white border" id="editRoleType"
                                        style="background:rgba(255,255,255,.07);border-color:#ffffff1a !important;">
                                        <option value="" style="background:#012445">Select type</option>
                                        <option style="background:#012445">Admin</option>
                                        <option style="background:#012445">Manager</option>
                                        <option style="background:#012445">Supervisor</option>
                                        <option style="background:#012445">Staff</option>
                                        <option style="background:#012445">Guest</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <textarea class="form-control form-control-sm text-white border" id="editRoleDesc" rows="2"
                                        placeholder="Description" style="background:rgba(255,255,255,.07);border-color:#ffffff1a !important;"></textarea>
                                </div>
                                <div class="col-12 d-flex justify-content-end gap-2 mt-1 pt-2 border-top"
                                    style="border-color:#ffffff1a !important;">
                                    <button class="btn btn-sm btn-outline-light"
                                        onclick="cancelRoleEdit()">Cancel</button>
                                    <button class="btn btn-sm btn-outline-light"
                                        onclick="updateRole()">Update</button>
                                </div>
                            </div>
                        </div>

                        <div id="roleListingEmpty" class="text-center py-4 small"
                            style="color:rgba(255,255,255,.5);">
                            No roles added yet.
                        </div>
                        <div id="roleListing" class="row g-3"></div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
    let roles = [];
    let roleUploadedFiles = [];

    function previewRoleUpload(input) {
        const preview = document.getElementById('roleUploadPreview');

        Array.from(input.files).forEach((file) => {
            roleUploadedFiles.push(file);
            const idx = roleUploadedFiles.length - 1;
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
                roleUploadedFiles[idx] = null;
                document.getElementById('upload-preview-' + idx)?.remove();
            };

            wrapper.appendChild(removeBtn);
            preview.appendChild(wrapper);
        });

        // Reset so same file can be picked again after removal
        input.value = '';
    }

    function saveRole() {
        const name = document.getElementById('roleName').value.trim();
        const type = document.getElementById('roleType').value;
        const desc = document.getElementById('roleDesc').value.trim();

        if (!name) {
            alert('Please enter a role name.');
            return;
        }

        const files = roleUploadedFiles.filter(Boolean);

        const role = {
            id: 'role-' + Date.now(),
            name,
            type,
            desc,
            files: files.map(f => ({
                name: f.name,
                isImg: f.type.startsWith('image/'),
                url: f.type.startsWith('image/') ? URL.createObjectURL(f) : null
            })),
            initials: name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase()
        };

        roles.push(role);
        renderRoleListing();
        resetRoleForm();
        document.querySelector('[data-bs-target="#tab-list"]').click();
    }

    function resetRoleForm() {
        document.getElementById('roleName').value = '';
        document.getElementById('roleType').value = '';
        document.getElementById('roleDesc').value = '';
        document.getElementById('roleUpload').value = '';
        document.getElementById('roleUploadPreview').innerHTML = '';
        roleUploadedFiles = [];
    }

    function renderRoleListing() {
        const container = document.getElementById('roleListing');
        const empty = document.getElementById('roleListingEmpty');
        const badge = document.getElementById('roleCountBadge');

        badge.textContent = roles.length;
        empty.classList.toggle('d-none', roles.length > 0);
        container.innerHTML = '';

        roles.forEach(r => {
            const filesHtml = r.files?.length ?
                `<div class="mb-2 d-flex flex-wrap gap-1">
                ${r.files.map(f => f.isImg
                    ? `<img src="${f.url}"
                        style="height:40px;width:40px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6;">`
                    : `<span class="badge bg-light text-muted border" style="font-size:10px;">
                           <i class="bi bi-paperclip me-1"></i>${f.name}
                       </span>`
                ).join('')}
               </div>` :
                '';

            container.insertAdjacentHTML('beforeend', `
            <div class="col-md-6 col-lg-4" id="${r.id}">
                   <div class="rounded-3 border p-3 h-100" style="border-color:#ffffff1a !important;background:rgba(255,255,255,.07);">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                          <div class="d-flex align-items-center gap-2">
                <div class="rounded-2 d-flex align-items-center justify-content-center fw-bold text-white"
                    style="width:45px;height:45px;font-size:1.1rem;background:rgba(255,255,255,.15);">
                    ${r.initials}
                </div>
                <div>
                    <h6 class="mb-0 fw-semibold small text-white">${r.name}</h6>
                    <small style="color:rgba(255,255,255,.55);">${r.type || '—'}</small>
                </div>
                        </div>
                            <span class="badge" style="background:rgba(255,255,255,.15);font-size:10px;">${r.type || '—'}</span>
                </div>
                 ${r.desc ? `
                 <div class="rounded-3 border p-2 mb-2" style="border-color:#ffffff1a !important;">
                     <small class="opacity-75 d-block mb-1">Description</small>
                     <div class="fw-semibold small">${r.desc}</div>
                 </div>` : ''}
                    ${filesHtml}
                    <div class="pt-3 mt-2 border-top d-flex gap-2 justify-content-end" style="border-color:#ffffff1a !important;">
                        <button class="btn btn-sm btn-outline-light" onclick="editRole('${r.id}')">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </button>
                        <button class="btn btn-sm btn-outline-light" onclick="deleteRole('${r.id}')">
                            <i class="bi bi-trash me-1"></i>Delete
                        </button>
                    </div>
                </div>
            </div>`);
        });
    }

    function editRole(id) {
        const r = roles.find(x => x.id === id);
        if (!r) return;
        document.getElementById('editRoleId').value = r.id;
        document.getElementById('editRoleName').value = r.name;
        document.getElementById('editRoleType').value = r.type;
        document.getElementById('editRoleDesc').value = r.desc;
        document.getElementById('roleEditForm').classList.remove('d-none');
        document.getElementById('roleEditForm').scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }

    function updateRole() {
        const id = document.getElementById('editRoleId').value;
        const role = roles.find(x => x.id === id);
        if (!role) return;
        role.name = document.getElementById('editRoleName').value.trim();
        role.type = document.getElementById('editRoleType').value;
        role.desc = document.getElementById('editRoleDesc').value.trim();
        role.initials = role.name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
        renderRoleListing();
        cancelRoleEdit();
    }

    function cancelRoleEdit() {
        document.getElementById('roleEditForm').classList.add('d-none');
    }

    function deleteRole(id) {
        roles = roles.filter(x => x.id !== id);
        renderRoleListing();
        cancelRoleEdit();
    }
</script>
