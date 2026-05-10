(function () {
    'use strict';

    const toggleAddDocType = document.getElementById('toggleAddDocType');
    const addDocTypeContainer = document.getElementById('addDocTypeContainer');
    const submitNewDocType = document.getElementById('submitNewDocType');
    const newDocTypeName = document.getElementById('newDocTypeName');
    const requiredDocumentsList = document.getElementById('requiredDocumentsList');
    const onPageAttachmentType = document.getElementById('onPageAttachmentType');
    const onPageAttachmentName = document.getElementById('onPageAttachmentName');
    const onPageDropzone = document.getElementById('onPageDropzone');
    const onPageAttachmentUpload = document.getElementById('onPageAttachmentUpload');

    // Toggle input field
    if (toggleAddDocType) {
        toggleAddDocType.addEventListener('click', function () {
            const isHidden = addDocTypeContainer.style.display === 'none';
            addDocTypeContainer.style.display = isHidden ? 'block' : 'none';
            if (isHidden) newDocTypeName.focus();
        });
    }

    // Submit new document type
    if (submitNewDocType) {
        submitNewDocType.addEventListener('click', function () {
            const name = newDocTypeName.value.trim();
            if (!name) return;

            submitNewDocType.disabled = true;
            submitNewDocType.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            fetch('/admin/register/add-document-type', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ name: name })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Add to list
                    const html = `
                        <div class="doc-item d-flex align-items-center justify-content-between p-3 rounded-3 mb-2" data-doc-type="${data.data.name}">
                            <div class="d-flex align-items-center gap-3">
                                <div class="form-check m-0 p-0 d-flex align-items-center">
                                    <input class="form-check-input m-0 doc-checkbox" type="checkbox" disabled style="width: 18px; height: 18px;">
                                </div>
                                <span class="text-white-50 small fw-semibold doc-name">${data.data.name}</span>
                            </div>
                            <span class="badge status-badge rounded-pill px-3 py-1" style="background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.4); font-size: 0.65rem;">Pending</span>
                        </div>
                    `;
                    requiredDocumentsList.insertAdjacentHTML('beforeend', html);

                    // Add to dropdown
                    const option = document.createElement('option');
                    option.value = data.data.name;
                    option.textContent = data.data.name;
                    onPageAttachmentType.appendChild(option);

                    // Clear input
                    newDocTypeName.value = '';
                    addDocTypeContainer.style.display = 'none';
                    
                    if (window.Swal) Swal.fire({ icon: 'success', title: 'Added', text: 'Document type added successfully', timer: 1500, showConfirmButton: false });
                } else {
                    if (window.Swal) Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to add document type' });
                }
            })
            .catch(err => {
                console.error(err);
                if (window.Swal) Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong' });
            })
            .finally(() => {
                submitNewDocType.disabled = false;
                submitNewDocType.textContent = 'Add';
            });
        });
    }

    // Handle On-Page Dropzone
    if (onPageDropzone && onPageAttachmentUpload) {
        onPageDropzone.addEventListener('click', () => onPageAttachmentUpload.click());

        onPageDropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            onPageDropzone.classList.add('border-warning');
        });

        onPageDropzone.addEventListener('dragleave', () => {
            onPageDropzone.classList.remove('border-warning');
        });

        onPageDropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            onPageDropzone.classList.remove('border-warning');
            const files = e.dataTransfer.files;
            if (files.length) handleOnPageUpload(files);
        });

        onPageAttachmentUpload.addEventListener('change', (e) => {
            if (e.target.files.length) handleOnPageUpload(e.target.files);
        });
    }

    function handleOnPageUpload(files) {
        console.log('handleOnPageUpload called with', files.length, 'files');
        try {
            const employeeId = document.getElementById('saved_employee_id')?.value;
            const nameInput = document.getElementById('onPageAttachmentName');
            const typeSelect = document.getElementById('onPageAttachmentType');
            
            const name = nameInput ? nameInput.value.trim() : '';
            const type = typeSelect ? typeSelect.value : '';

            console.log('Upload context:', { employeeId, name, type });

            if (!employeeId) {
                console.warn('Upload blocked: No employeeId');
                if (window.Swal) {
                    Swal.fire({ 
                        icon: 'warning', 
                        title: 'Employee Not Saved', 
                        text: 'Please complete step 1 and save the employee before uploading attachments.',
                        confirmButtonColor: '#1a237e'
                    });
                } else {
                    alert('Please save the employee first.');
                }
                return;
            }

            if (!type) {
                console.warn('Upload blocked: No type selected');
                if (typeSelect) typeSelect.classList.add('is-invalid');
                if (window.Swal) {
                    Swal.fire({ 
                        icon: 'warning', 
                        title: 'Type Required', 
                        text: 'Please select an attachment type from the dropdown before uploading.',
                        confirmButtonColor: '#1a237e'
                    });
                } else {
                    alert('Please select an attachment type.');
                }
                return;
            }
            
            if (typeSelect) typeSelect.classList.remove('is-invalid');

            const formData = new FormData();
            formData.append('employee_id', employeeId);
            formData.append('step', 6);
            formData.append('subsection', 'attachment');
            formData.append('attachments[0][name]', name || files[0].name);
            formData.append('attachments[0][type]', type);
            formData.append('attachments[0][description]', '');
            
            Array.from(files).forEach(f => {
                formData.append('attachments[0][files][]', f);
            });
            
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

            // Create temporary attachment for progress display
            const tempId = 'uploading-' + Date.now();
            const tempAttachment = {
                localId: tempId,
                name: name || files[0].name,
                type: type,
                desc: '',
                files: Array.from(files).map(f => ({
                    file: f,
                    name: f.name,
                    size: f.size,
                    progress: 0,
                    type: (f.name.split('.').pop() || '').toUpperCase()
                })),
                existingFiles: []
            };

        window.employeeAttachments = window.employeeAttachments || [];
        window.employeeAttachments.push(tempAttachment);
        if (window.renderAttachmentListing) window.renderAttachmentListing();

        const xhr = new XMLHttpRequest();
        const uploadUrl = window.saveAttachmentUrl || '/admin/employees/save-attachment';
        xhr.open('POST', uploadUrl, true);
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);

        xhr.upload.onprogress = (e) => {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                tempAttachment.files.forEach(f => {
                    f.progress = percent;
                });
                if (window.renderAttachmentListing) window.renderAttachmentListing();
            }
        };

        xhr.onload = function() {
            let data = {};
            try { data = JSON.parse(xhr.responseText); } catch(e) {}

            if (xhr.status >= 200 && xhr.status < 300 && data.success) {
                // Remove temp and fetch real
                window.employeeAttachments = window.employeeAttachments.filter(a => a.localId !== tempId);
                if (window.fetchExistingAttachments) window.fetchExistingAttachments();
                
                onPageAttachmentName.value = '';
                onPageAttachmentType.value = '';
                if (window.Swal) Swal.fire({ icon: 'success', title: 'Uploaded', text: 'Document uploaded successfully', timer: 1500, showConfirmButton: false });
            } else {
                window.employeeAttachments = window.employeeAttachments.filter(a => a.localId !== tempId);
                if (window.renderAttachmentListing) window.renderAttachmentListing();
                if (window.Swal) Swal.fire({ icon: 'error', title: 'Upload Failed', text: data.message || 'Error uploading file' });
            }
        };

        xhr.onerror = function() {
            window.employeeAttachments = window.employeeAttachments.filter(a => a.localId !== tempId);
            if (window.renderAttachmentListing) window.renderAttachmentListing();
            if (window.Swal) Swal.fire({ icon: 'error', title: 'Error', text: 'Server error during upload' });
        };

        xhr.send(formData);
        } catch (err) {
            console.error('Error in handleOnPageUpload:', err);
            if (window.Swal) Swal.fire({ icon: 'error', title: 'Upload Error', text: 'An unexpected error occurred during upload.' });
        }
    }

    // Function to update checklist status
    function updateChecklistStatus() {
        if (!requiredDocumentsList) return;
        const attachments = window.employeeAttachments || [];
        const uploadedTypes = new Set(attachments.map(a => a.type));

        const items = requiredDocumentsList.querySelectorAll('.doc-item');
        items.forEach(item => {
            const type = item.getAttribute('data-doc-type');
            const checkbox = item.querySelector('.doc-checkbox');
            const badge = item.querySelector('.status-badge');
            const nameSpan = item.querySelector('.doc-name');

            if (uploadedTypes.has(type)) {
                item.classList.add('active');
                checkbox.checked = true;
                badge.textContent = 'Done';
                badge.style.background = 'rgba(255, 193, 7, 0.15)';
                badge.style.color = '#ffc107';
                nameSpan.classList.remove('text-white-50');
                nameSpan.classList.add('text-white');
            } else {
                item.classList.remove('active');
                checkbox.checked = false;
                badge.textContent = 'Pending';
                badge.style.background = 'rgba(255,255,255,0.05)';
                badge.style.color = 'rgba(255,255,255,0.4)';
                nameSpan.classList.add('text-white-50');
                nameSpan.classList.remove('text-white');
            }
        });
    }

    // Hook into renderAttachmentListing to update checklist
    const hookRender = () => {
        if (typeof window.renderAttachmentListing === 'function') {
            const originalRenderAttachmentListing = window.renderAttachmentListing;
            window.renderAttachmentListing = function () {
                originalRenderAttachmentListing.apply(this, arguments);
                updateChecklistStatus();
            };
            // Initial run
            updateChecklistStatus();
        } else {
            // Wait for it to be defined
            setTimeout(hookRender, 100);
        }
    };

    hookRender();

})();
