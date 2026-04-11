<script>
    window.processStepSave = function(step, onSuccess) {
        try {
            const form = document.getElementById('employeeForm');
            if (!form) {
                console.error('employeeForm not found');
                return;
            }
            const formData = new FormData();
            console.log('FormData initialized');
            formData.append('step', step);

            // 1. Always include the main form hidden fields (like _token)
            const mainHidden = form.querySelectorAll('input[type="hidden"]');
            mainHidden.forEach(inp => {
                if (inp.name && inp.name !== 'step') formData.append(inp.name, inp.value);
            });

            // 2. Add current step inputs
            const activeStepDiv = document.getElementById('step-' + step);
            if (activeStepDiv) {
                const inputs = activeStepDiv.querySelectorAll('input[name], select[name], textarea[name]');
                inputs.forEach(input => {
                    const name = input.getAttribute('name');
                    if (!name) return;
                    if (input.type === 'checkbox' || input.type === 'radio') {
                        if (input.checked) formData.append(name, input.value);
                    } else if (input.type === 'file') {
                        if (name === 'profile_photo' && (typeof croppedImageBlob !== 'undefined' && croppedImageBlob)) {
                            return;
                        }
                        if (input.files && input.files.length > 0) {
                            for(let i=0; i<input.files.length; i++) {
                                formData.append(name, input.files[i]);
                            }
                        }
                    } else {
                        formData.append(name, input.value);
                    }
                });
            }

            if (step === 1 && (typeof croppedImageBlob !== 'undefined' && croppedImageBlob)) {
                formData.append('profile_photo', croppedImageBlob, 'profile.png');
            }

            const nextBtn = document.getElementById('nextBtn');
            const prevBtn = document.getElementById('prevBtn');
            const originalText = nextBtn ? nextBtn.textContent : '';

            if (nextBtn) {
                nextBtn.disabled = true;
                nextBtn.textContent = 'Saving...';
            }
            if (prevBtn) prevBtn.disabled = true;

            console.log('Sending fetch request...');
            fetch('{{ route("admin.employee.save_step") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData,
            })
            .then(async r => {
                const contentType = r.headers.get('content-type');
                const isJson = contentType && contentType.includes('application/json');
                const data = isJson ? await r.json() : null;

                // Handle validation error (422) as a success-path for data processing, not a crash
                if (r.status === 422) {
                    return { success: false, errors: data.errors, message: data.message };
                }

                if (!r.ok) {
                    let errMsg = 'Server error: ' + r.status;
                    if (data && data.message) errMsg = data.message;
                    throw new Error(errMsg);
                }
                return data;
            })
            .then(data => {
                if (nextBtn) {
                    nextBtn.disabled = false;
                    nextBtn.textContent = originalText;
                }
                if (prevBtn) prevBtn.disabled = false;

                if (data.success) {
                    if (typeof clearStepErrors === 'function') clearStepErrors();

                    if (data.employee_id) {
                        let hiddenId = document.getElementById('saved_employee_id');
                        if (!hiddenId) {
                            hiddenId = document.createElement('input');
                            hiddenId.type = 'hidden';
                            hiddenId.id = 'saved_employee_id';
                            hiddenId.name = 'employee_id';
                            form.appendChild(hiddenId);
                        }
                        hiddenId.value = data.employee_id;
                    }
                    
                    if (typeof showSuccess === 'function') {
                        const total = 6; // Local ref
                        if (step === total) {
                            showSuccess('Employee registration completed successfully!', 'Completed').then(() => {
                                if (onSuccess) onSuccess();
                            });
                        } else {
                            showSuccess(data.message, 'Saved').then(() => {
                                if (onSuccess) onSuccess();
                            });
                        }
                    } else if (onSuccess) {
                        onSuccess();
                    }
                    
                } else if (data.errors) {
                    if (typeof showFieldErrors === 'function') showFieldErrors(data.errors);
                } else {
                    if (typeof showError === 'function') showError(data.message || 'Something went wrong.');
                }
            })
            .catch((e) => {
                if (nextBtn) {
                    nextBtn.disabled = false;
                    nextBtn.textContent = originalText;
                }
                if (prevBtn) prevBtn.disabled = false;
                console.error('Fetch error:', e);
                if (typeof showError === 'function') showError('Error: ' + e.message);
            });
        } catch (e) {
            console.error('processStepSave error:', e);
        }
    }
</script>
