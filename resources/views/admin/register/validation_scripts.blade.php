<script>
    function clearFieldStatus(el) {
        if (!el) return;
        el.classList.remove('is-invalid', 'is-invalid-step');
        // Handle input siblings
        let sibling = el.nextElementSibling;
        while (sibling && (sibling.classList.contains('step-val-error') || sibling.classList.contains('field-error-msg'))) {
            let next = sibling.nextElementSibling;
            sibling.remove();
            sibling = next;
        }
        // Handle parent-level errors (for radios/checkboxes)
        const parentGroup = el.closest('.d-flex, .form-check, td, div');
        if (parentGroup) {
            parentGroup.querySelectorAll('.step-val-error, .field-error-msg').forEach(err => err.remove());
        }
    }

    function formatCNIC(input) {
        if (!input) return;
        let val = input.value.replace(/\D/g, ''); 
        if (val.length > 13) val = val.substring(0, 13);
        let formatted = '';
        if (val.length > 0) {
            formatted = val.substring(0, 5);
            if (val.length > 5) {
                formatted += '-' + val.substring(5, 12);
                if (val.length > 12) formatted += '-' + val.substring(12, 13);
            }
        }
        input.value = formatted;
    }

    window.applyCnicMasks = function(context = document) {
        context.querySelectorAll('.cnic-mask').forEach(el => formatCNIC(el));
    };

    // Global listener for immediate error clearing
    document.addEventListener('DOMContentLoaded', function() {
        // Allow digits only for number-only class
        document.addEventListener('input', function(e) {
            if (e.target && e.target.classList.contains('number-only')) {
                let val = String(e.target.value);
                let cleaned = val.replace(/\D/g, '');
                if (val !== cleaned) {
                    e.target.value = cleaned;
                }
            }
        });

        const form = document.getElementById('employeeForm');
        if (form) {
            form.addEventListener('input', function(e) {
                clearFieldStatus(e.target);
            });
            form.addEventListener('change', function(e) {
                clearFieldStatus(e.target);
            });
            // CNIC automatic masking
            form.addEventListener('input', function(e) {
                if (e.target.classList.contains('cnic-mask')) {
                    formatCNIC(e.target);
                }
            });
        }
    });

    function clearStepErrors() {
        document.querySelectorAll('.step-val-error, .field-error-msg').forEach(e => e.remove());
        document.querySelectorAll('.is-invalid-step, .is-invalid').forEach(e => e.classList.remove('is-invalid-step', 'is-invalid'));
    }

    function markFieldInvalid(el, msg) {
        if (!el) return;
        el.classList.add('is-invalid', 'is-invalid-step');
        const div = document.createElement('div');
        div.className = 'step-val-error invalid-feedback d-block';
        div.textContent = msg;
        el.insertAdjacentElement('afterend', div);
    }

    function markRadioInvalid(name, msg) {
        const group = document.querySelectorAll('[name="' + name + '"]');
        if (!group.length) return;
        const lastInput = group[group.length - 1];
        const wrapper = lastInput.closest('.d-flex, .form-check, div');
        if (wrapper) {
            const div = document.createElement('div');
            div.className = 'step-val-error text-danger small mt-1';
            div.textContent = msg;
            wrapper.insertAdjacentElement('afterend', div);
        }
    }

    function showFieldErrors(errors, context = document) {
        // Always clear ALL previous field errors document-wide to avoid stale red marks
        document.querySelectorAll('.is-invalid-step').forEach(el => {
            el.classList.remove('is-invalid', 'is-invalid-step');
            el.style.borderColor = '';
            el.style.paddingRight = '';
            el.style.backgroundImage = '';
            el.style.backgroundRepeat = '';
            el.style.backgroundPosition = '';
            el.style.backgroundSize = '';
        });
        document.querySelectorAll('.field-error-msg, .step-val-error').forEach(el => el.remove());

        const errorEntries = Object.entries(errors);
        if (errorEntries.length === 0) return;

        // If the context is a table row (TR), the user requested SweetAlert to avoid UI breakage
        const isTableContext = context && context.nodeType === 1 && (context.tagName === 'TR' || context.closest('table'));

        if (isTableContext) {
            let errorHtml = '<div class="text-danger text-start mt-2"><ul>';
            errorEntries.forEach(entry => {
                entry[1].forEach(msg => { errorHtml += `<li>${msg}</li>`; });
            });
            errorHtml += '</ul></div>';

            Swal.fire({
                icon: 'error',
                title: 'Row Validation Error',
                html: errorHtml,
                confirmButtonColor: '#1a237e'
            });
            
            // Still highlight fields for visual feedback
            errorEntries.forEach(entry => {
                const field = entry[0];
                const parts = field.split('.');
                const fieldName = parts[0] + parts.slice(1).map(p => `[${p}]`).join('');
                let input = context.querySelector(`[name="${fieldName}"]`) || 
                            context.querySelector(`[name="${fieldName}[]"]`) ||
                            context.querySelector(`.fm-${fieldName}`) ||
                            context.querySelector(`.ac-${fieldName}`) ||
                            context.querySelector(`.em-${fieldName}`);
                if (input) input.classList.add('is-invalid', 'is-invalid-step');
            });
            return;
        }

        let highlightedCount = 0;
        errorEntries.forEach(function(entry) {
            const field = entry[0];
            const messages = entry[1];
            let fieldName = field;
            if (field.includes('.')) {
                const parts = field.split('.');
                fieldName = parts[0] + parts.slice(1).map(p => `[${p}]`).join('');
            }

            let input = context.querySelector(`[name="${fieldName}"]`) || 
                        context.querySelector(`[name="${fieldName}[]"]`) ||
                        context.querySelector(`.fm-${fieldName}`) ||
                        context.querySelector(`.ac-${fieldName}`) ||
                        context.querySelector(`.em-${fieldName}`) ||
                        (context === document ? document.getElementById(fieldName) : null);

            if (input) {
                highlightedCount++;
                let targetEl = input;
                if (field === 'profile_photo') {
                    const box = document.getElementById('uploadImageBox');
                    if (box) targetEl = box;
                }

                targetEl.classList.add('is-invalid', 'is-invalid-step');
                targetEl.style.borderColor = '#dc3545';
                targetEl.style.paddingRight = 'calc(1.5em + 0.75rem)';
                targetEl.style.backgroundImage = 'url("data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 12 12\' width=\'12\' height=\'12\' fill=\'none\' stroke=\'%23dc3545\'%3e%3ccircle cx=\'6\' cy=\'6\' r=\'4.5\'/%3e%3cpath stroke-linejoin=\'round\' d=\'M5.8 3.6h.4L6 6.5z\'/%3e%3ccircle cx=\'6\' cy=\'8.2\' r=\'.6\' fill=\'%23dc3545\' stroke=\'none\'/%3e%3c/svg%3e")';
                targetEl.style.backgroundRepeat = 'no-repeat';
                targetEl.style.backgroundPosition = 'right calc(0.375em + 0.1875rem) center';
                targetEl.style.backgroundSize = 'calc(0.75em + 0.375rem) calc(0.75em + 0.375rem)';
                
                const err = document.createElement('div');
                err.className = 'field-error-msg text-danger small mt-1 fw-bold';
                err.style.display = 'block';
                err.textContent = messages[0];
                
                // Find best insertion parent: profile_photo box, radio group parent, or nearest col-* wrapper
                if (field === 'profile_photo' || fieldName === 'profile_photo') {
                    const col = targetEl.closest('div[class*="col-"]');
                    if (col) col.appendChild(err);
                    else targetEl.insertAdjacentElement('afterend', err);
                } else if (targetEl.type === 'radio' || targetEl.type === 'checkbox') {
                    const parentGroup = targetEl.closest('.d-flex, .form-check, div');
                    if (parentGroup) parentGroup.insertAdjacentElement('afterend', err);
                    else targetEl.insertAdjacentElement('afterend', err);
                } else {
                    // Append inside the nearest col-* wrapper so it stays visible in the layout
                    const col = targetEl.closest('div[class*="col-"]');
                    if (col) col.appendChild(err);
                    else targetEl.insertAdjacentElement('afterend', err);
                }
            }
        });
        
        // Scroll to the first error so it is impossible to miss
        const firstError = context.querySelector('.is-invalid-step');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        if (highlightedCount === 0 && errorEntries.length > 0) {
            let errorHtml = '<div class="text-danger text-start mt-2"><ul>';
            Object.values(errors).flat().forEach(msg => {
                errorHtml += `<li>${msg}</li>`;
            });
            errorHtml += '</ul></div>';

            Swal.fire({
                icon: 'error',
                title: 'Validation Errors',
                html: errorHtml,
                confirmButtonColor: '#3085d6'
            });
        }
    }

    window.validateStep = function(step) {
        try {
            clearStepErrors();
            let valid = true;
            let firstEl = null;

            function req(name, label) {
                const el = document.querySelector('[name="' + name + '"]');
                if (!el) return;
                const val = el.value ? el.value.trim() : '';
                if (!val) {
                    markFieldInvalid(el, label + ' is required.');
                    if (!firstEl) firstEl = el;
                    valid = false;
                }
            }

            function reqRadio(name, label) {
                const checked = document.querySelector('[name="' + name + '"]:checked');
                if (!checked) {
                    markRadioInvalid(name, label + ' is required.');
                    const first = document.querySelector('[name="' + name + '"]');
                    if (!firstEl && first) firstEl = first;
                    valid = false;
                }
            }

            if (step === 1) {
                req('full_name', 'Name');
                req('cnic', 'CNIC');
                req('cnic_expiry', 'CNIC Expiry');
                req('dob', 'Date of Birth');
                req('nationality', 'Nationality');
                req('marital_status', 'Marital Status');
                const maritalEl = document.querySelector('[name="marital_status"]');
                if (maritalEl && maritalEl.value === 'Married') {
                    req('spouse_cnic', 'Spouse CNIC');
                    req('spouse_nationality', 'Spouse Nationality');
                }
                req('nok_name', 'NOK Name');
                req('nok_cnic', 'NOK CNIC');
                req('nok_cnic_expiry_date', 'NOK CNIC Expiry');
                req('nok_relation', 'NOK Relation');
                req('nok_dob', 'NOK DOB');
                req('nok_contact', 'NOK Contact');
            } else if (step === 2) {
                reqRadio('employment_category', 'Category');
                req('organization_id', 'Organization');
                req('role_id', 'Role');
                const rid = document.querySelector('[name="role_id"]');
                let role = null;
                if (rid && rid.value && Array.isArray(window._rolesData)) {
                    role = window._rolesData.find(r => r && String(r.id) === String(rid.value));
                }
                if (role && role.department_id) {
                    req('sbu_id', 'SBU');
                    req('department_id', 'Department');
                }
                req('join_date', 'Date of Joining');
                const catEl = document.querySelector('[name="employment_category"]:checked');
                const cat = catEl ? catEl.value : null;
                if (cat === 'intern') {
                    req('intern_type', 'Intern Type');
                    req('intern_duration', 'Intern Duration');
                } else if (cat === 'contractual') {
                    req('contractual_type', 'Contract Type');
                } else if (cat === 'engagement') {
                    req('engagement_mode', 'Engagement Mode');
                }
            } else if (step === 3) {
                reqRadio('verification_status', 'Verification Status');
            } else if (step === 5) {
                req('account_title', 'Account Title');
                req('account_no', 'Account No');
                req('bank_branch', 'Bank & Branch');
                reqRadio('account_type', 'A/C Type');
            } else if (step === 6) {
                req('cell_no', 'Cell Number');
                req('contact_email', 'Email');
                req('present_address', 'Present Address');
                req('permanent_address', 'Permanent Address');
            }

            if (!valid && firstEl) {
                firstEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return valid;
        } catch (e) {
            console.error('validateStep failed:', e);
            return false;
        }
    };
</script>
