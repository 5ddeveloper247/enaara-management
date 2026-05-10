(function () {
    'use strict';

    const familyCertificateInput = document.getElementById('familyCertificateInput');
    const familyCertificateUploadBtn = document.getElementById('familyCertificateUploadBtn');
    const familyCertificateUploadView = document.getElementById('familyCertificateUploadView');
    const familyCertificateFileView = document.getElementById('familyCertificateFileView');
    const familyCertificateName = document.getElementById('familyCertificateName');
    const familyCertificateLink = document.getElementById('familyCertificateLink');
    const familyCertificateDeleteBtn = document.getElementById('familyCertificateDeleteBtn');
    const familyCertificateProgress = document.getElementById('familyCertificateProgress');
    const progressBar = familyCertificateProgress ? familyCertificateProgress.querySelector('.progress-bar') : null;

    const ATTACHMENT_TYPE = 'Family Character Certificate';
    let currentAttachmentId = null;

    if (familyCertificateUploadBtn && familyCertificateInput) {
        familyCertificateUploadBtn.addEventListener('click', () => familyCertificateInput.click());

        familyCertificateInput.addEventListener('change', function (e) {
            if (e.target.files.length) {
                uploadCertificate(e.target.files[0]);
            }
        });
    }

    async function uploadCertificate(file) {
        const employeeId = document.getElementById('saved_employee_id')?.value;
        if (!employeeId) {
            if (window.Swal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Employee Not Saved',
                    text: 'Please save the employee information first before uploading family documents.',
                    confirmButtonColor: '#1a237e'
                });
            } else {
                alert('Please save the employee first.');
            }
            familyCertificateInput.value = '';
            return;
        }

        const formData = new FormData();
        formData.append('employee_id', employeeId);
        formData.append('step', 6);
        formData.append('subsection', 'family_certificate');
        formData.append('attachments[0][name]', 'Family Character Certificate');
        formData.append('attachments[0][type]', ATTACHMENT_TYPE);
        formData.append('attachments[0][description]', '');
        formData.append('attachments[0][files][]', file);

        // Show progress
        if (familyCertificateProgress) familyCertificateProgress.classList.remove('d-none');
        if (familyCertificateUploadView) familyCertificateUploadView.classList.add('d-none');
        if (progressBar) progressBar.style.width = '0%';

        const xhr = new XMLHttpRequest();
        xhr.open('POST', window.saveAttachmentUrl || '/admin/employees/save-attachment', true);
        xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
        xhr.setRequestHeader('Accept', 'application/json');

        xhr.upload.onprogress = (e) => {
            if (e.lengthComputable && progressBar) {
                const percent = Math.round((e.loaded / e.total) * 100);
                progressBar.style.width = percent + '%';
            }
        };

        xhr.onload = function () {
            let data = {};
            try { data = JSON.parse(xhr.responseText); } catch (err) { }

            if (xhr.status >= 200 && xhr.status < 300 && data.success) {
                const savedFile = data.files[0];
                showFileView(savedFile.name, savedFile.url, data.attachment_id);
                if (window.Swal) Swal.fire({ icon: 'success', title: 'Uploaded', text: 'Family certificate uploaded successfully', timer: 1500, showConfirmButton: false });
                
                // Refresh global attachment list if possible
                if (window.fetchExistingAttachments) window.fetchExistingAttachments();
            } else {
                resetView();
                if (window.Swal) Swal.fire({ icon: 'error', title: 'Upload Failed', text: data.message || 'Error uploading file' });
            }
        };

        xhr.onerror = function () {
            resetView();
            if (window.Swal) Swal.fire({ icon: 'error', title: 'Error', text: 'Server error during upload' });
        };

        xhr.send(formData);
    }

    if (familyCertificateDeleteBtn) {
        familyCertificateDeleteBtn.addEventListener('click', async function () {
            if (!currentAttachmentId) return;

            const result = await (window.Swal ? Swal.fire({
                title: 'Are you sure?',
                text: "This will permanently remove the family character certificate.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }) : { isConfirmed: confirm('Are you sure you want to delete this certificate?') });

            if (result.isConfirmed) {
                try {
                    const response = await fetch('/admin/employees/delete-attachment', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ id: currentAttachmentId })
                    });

                    const data = await response.json();
                    if (data.success) {
                        resetView();
                        if (window.Swal) Swal.fire({ icon: 'success', title: 'Deleted', text: 'Certificate removed successfully', timer: 1500, showConfirmButton: false });
                        if (window.fetchExistingAttachments) window.fetchExistingAttachments();
                    } else {
                        if (window.Swal) Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to delete' });
                    }
                } catch (err) {
                    console.error(err);
                    if (window.Swal) Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong' });
                }
            }
        });
    }

    function showFileView(name, url, id) {
        currentAttachmentId = id;
        if (familyCertificateName) familyCertificateName.textContent = name;
        if (familyCertificateLink) familyCertificateLink.href = url;
        if (familyCertificateFileView) familyCertificateFileView.classList.remove('d-none');
        if (familyCertificateUploadView) familyCertificateUploadView.classList.add('d-none');
        if (familyCertificateProgress) familyCertificateProgress.classList.add('d-none');
    }

    function resetView() {
        currentAttachmentId = null;
        if (familyCertificateInput) familyCertificateInput.value = '';
        if (familyCertificateFileView) familyCertificateFileView.classList.add('d-none');
        if (familyCertificateUploadView) familyCertificateUploadView.classList.remove('d-none');
        if (familyCertificateProgress) familyCertificateProgress.classList.add('d-none');
    }

    // Initialize from global attachments if they exist
    function initFromGlobal() {
        const attachments = window.employeeAttachments || (window.editData ? window.editData.attachments : []);
        
        if (attachments && Array.isArray(attachments)) {
            const cert = attachments.find(a => a.type === ATTACHMENT_TYPE);
            if (cert && cert.existingFiles && cert.existingFiles.length > 0) {
                const file = cert.existingFiles[0];
                showFileView(file.name, file.url, cert.existingId || cert.id || file.id);
                return;
            } else if (cert && cert.files && cert.files.length > 0) {
                const file = cert.files[0];
                showFileView(file.name, file.url || '#', cert.existingId || cert.id || file.id);
                return;
            }
        }
        resetView();
    }

    // Hook into setExistingAttachments to ensure UI stays in sync
    const originalSetExisting = window.setExistingAttachments;
    window.setExistingAttachments = function(attachments) {
        if (typeof originalSetExisting === 'function') {
            originalSetExisting.apply(this, arguments);
        }
        initFromGlobal();
    };

    // Initial load
    setTimeout(initFromGlobal, 800);

})();
