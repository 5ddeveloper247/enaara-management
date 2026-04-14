<script>
    function clearFieldStatus(el) {
        if (!el) return;
        el.classList.remove('is-invalid', 'is-invalid-step');
        el.style.borderColor = '';
        el.style.paddingRight = '';
        el.style.backgroundImage = '';
        el.style.backgroundRepeat = '';
        el.style.backgroundPosition = '';
        el.style.backgroundSize = '';
        const col = el.closest('div[class*="col-"]');
        if (col) {
            col.querySelectorAll('.field-error-msg, .step-val-error').forEach(err => err.remove());
        } else {
            let sibling = el.nextElementSibling;
            while (sibling && (sibling.classList.contains('step-val-error') || sibling.classList.contains('field-error-msg'))) {
                const next = sibling.nextElementSibling;
                sibling.remove();
                sibling = next;
            }
            const parentGroup = el.closest('.d-flex, .form-check, td');
            if (parentGroup) {
                parentGroup.querySelectorAll('.step-val-error, .field-error-msg').forEach(err => err.remove());
            }
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
                if (e.target && e.target.name === 'hybrid_days[]') {
                    const chips = document.querySelector('#hybridDaysWrapper .hybrid-day-chips');
                    if (chips) {
                        clearFieldStatus(chips);
                    }
                }
            });
            // CNIC automatic masking
            form.addEventListener('input', function(e) {
                if (e.target.classList.contains('cnic-mask')) {
                    formatCNIC(e.target);
                }
            });
            form.addEventListener('paste', function(e) {
                const t = e.target;
                if (!t || !t.classList || !t.classList.contains('cnic-mask')) return;
                e.preventDefault();
                const text = (e.clipboardData || window.clipboardData).getData('text') || '';
                t.value = text.replace(/\D/g, '').substring(0, 13);
                formatCNIC(t);
            });
        }
    });

    function clearStepErrors() {
        document.querySelectorAll('.bank-row-error-banner').forEach(e => e.remove());
        document.querySelectorAll('#bankListing [data-bank-card]').forEach(card => {
            card.classList.remove('border-danger', 'border-2');
        });
        document.querySelectorAll('.step-val-error, .field-error-msg').forEach(e => e.remove());
        document.querySelectorAll('.is-invalid-step, .is-invalid').forEach(e => {
            e.classList.remove('is-invalid-step', 'is-invalid');
            e.style.borderColor = '';
            e.style.paddingRight = '';
            e.style.backgroundImage = '';
            e.style.backgroundRepeat = '';
            e.style.backgroundPosition = '';
            e.style.backgroundSize = '';
        });
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

    function resolveNamedFieldElement(fieldName, context) {
        const root =
            context && context.nodeType === 1 && context !== document
                ? context
                : (document.getElementById('employeeForm') || document.body);
        if (!fieldName || !root) return null;
        if (fieldName.indexOf('[') !== -1) {
            const all = root.querySelectorAll('input[name], select[name], textarea[name]');
            for (let i = 0; i < all.length; i++) {
                if (all[i].name === fieldName) return all[i];
            }
            return null;
        }
        if (typeof CSS !== 'undefined' && CSS.escape) {
            try {
                const el = root.querySelector('[name="' + CSS.escape(fieldName) + '"]');
                if (el) return el;
            } catch (e) {}
        }
        return root.querySelector('[name="' + String(fieldName).replace(/\\/g, '\\\\').replace(/"/g, '\\"') + '"]');
    }

    function resolveBankStepDraftElement(fieldKey) {
        const m = String(fieldKey).match(/^banks\.(\d+)\.(.+)$/);
        if (!m) return null;
        const sub = m[2];
        const draft = document.getElementById('bank-draft-form');
        if (!draft) return null;
        const bySub = {
            account_category: () => {
                const inp = draft.querySelector('input[name="draft_account_category"]');
                return inp ? inp.closest('.col-12') : null;
            },
            account_title: () => document.getElementById('draft_account_title'),
            account_no: () => document.getElementById('draft_account_no'),
            bank_name: () => document.getElementById('draft_bank_name'),
            branch_code: () => document.getElementById('draft_branch_code'),
            branch_address: () => document.getElementById('draft_branch_address'),
            iban: () => document.getElementById('draft_iban'),
            account_type: () => {
                const inp = draft.querySelector('input[name="draft_account_type"]');
                return inp ? inp.closest('.col-md-6') : null;
            },
            is_salary_account: () => {
                const inp = document.getElementById('draft_is_salary_account');
                return inp ? inp.closest('.col-12') : null;
            },
        };
        const fn = bySub[sub];
        return fn ? fn() : null;
    }

    function showFieldErrors(errors, context = document) {
        // Always clear ALL previous field errors document-wide to avoid stale red marks
        document.querySelectorAll('.bank-row-error-banner').forEach(el => el.remove());
        document.querySelectorAll('#bankListing [data-bank-card]').forEach(card => {
            card.classList.remove('border-danger', 'border-2');
        });
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
                let input =
                    resolveNamedFieldElement(fieldName, context) ||
                    (field.indexOf('department_ids') === 0 ? (context.querySelector('#dept-box') || context.querySelector('[name="department_ids[]"]')) : null) ||
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
            const bankRowMatch = String(field).match(/^banks\.(\d+)\.(.+)$/);
            if (bankRowMatch) {
                const idx = bankRowMatch[1];
                const card = document.querySelector('#bankListing [data-bank-card][data-index="' + idx + '"]');
                if (card) {
                    let banner = card.querySelector('.bank-row-error-banner');
                    if (!banner) {
                        banner = document.createElement('div');
                        banner.className = 'bank-row-error-banner alert alert-danger py-2 px-3 small mb-3';
                        banner.setAttribute('role', 'alert');
                        const body = card.querySelector('.card-body');
                        if (body) body.insertBefore(banner, body.firstChild);
                        else card.insertBefore(banner, card.firstChild);
                    }
                    let ul = banner.querySelector('ul');
                    if (!ul) {
                        ul = document.createElement('ul');
                        ul.className = 'mb-0 ps-3';
                        banner.appendChild(ul);
                    }
                    messages.forEach(function(msg) {
                        const li = document.createElement('li');
                        li.textContent = msg;
                        ul.appendChild(li);
                    });
                    card.classList.add('border-danger', 'border-2');
                    highlightedCount++;
                    return;
                }
            }

            let fieldName = field;
            if (field.includes('.')) {
                const parts = field.split('.');
                fieldName = parts[0] + parts.slice(1).map(p => `[${p}]`).join('');
            }

            let input =
                resolveNamedFieldElement(fieldName, context) ||
                (field.indexOf('department_ids') === 0 ? (context.querySelector('#dept-box') || context.querySelector('[name="department_ids[]"]')) : null) ||
                        context.querySelector(`[name="${fieldName}[]"]`) ||
                        context.querySelector(`.fm-${fieldName}`) ||
                        context.querySelector(`.ac-${fieldName}`) ||
                        context.querySelector(`.em-${fieldName}`) ||
                        (context === document ? document.getElementById(fieldName) : null);

            if (input && input.closest && input.closest('#bank-hidden-inputs')) {
                input = resolveBankStepDraftElement(field);
            }
            if (!input) {
                input = resolveBankStepDraftElement(field);
            }

            if (input) {
                highlightedCount++;
                let targetEl = input;
                if (field === 'profile_photo') {
                    const box = document.getElementById('uploadImageBox');
                    if (box) targetEl = box;
                }

                const isFormControl = targetEl.tagName === 'INPUT' || targetEl.tagName === 'SELECT' || targetEl.tagName === 'TEXTAREA';
                targetEl.classList.add('is-invalid', 'is-invalid-step');
                if (isFormControl) {
                    targetEl.style.borderColor = '#dc3545';
                    targetEl.style.paddingRight = 'calc(1.5em + 0.75rem)';
                    targetEl.style.backgroundImage = 'url("data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 12 12\' width=\'12\' height=\'12\' fill=\'none\' stroke=\'%23dc3545\'%3e%3ccircle cx=\'6\' cy=\'6\' r=\'4.5\'/%3e%3cpath stroke-linejoin=\'round\' d=\'M5.8 3.6h.4L6 6.5z\'/%3e%3ccircle cx=\'6\' cy=\'8.2\' r=\'.6\' fill=\'%23dc3545\' stroke=\'none\'/%3e%3c/svg%3e")';
                    targetEl.style.backgroundRepeat = 'no-repeat';
                    targetEl.style.backgroundPosition = 'right calc(0.375em + 0.1875rem) center';
                    targetEl.style.backgroundSize = 'calc(0.75em + 0.375rem) calc(0.75em + 0.375rem)';
                } else {
                    targetEl.style.borderColor = '#dc3545';
                }
                
                const err = document.createElement('div');
                err.className = 'field-error-msg text-danger small mt-1 fw-bold';
                err.style.display = 'block';
                err.textContent = messages[0];
                
                // Find best insertion parent: profile_photo box, radio group parent, or nearest col-* wrapper
                if (field === 'profile_photo' || fieldName === 'profile_photo') {
                    const col = targetEl.closest('div[class*="col-"]');
                    if (col) col.appendChild(err);
                    else targetEl.insertAdjacentElement('afterend', err);
                } else if (isFormControl && (targetEl.type === 'radio' || targetEl.type === 'checkbox')) {
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
        const firstBankErr = document.querySelector('#bankListing .bank-row-error-banner');
        const firstInv = document.querySelector('.is-invalid-step');
        const scrollEl = firstBankErr || firstInv;
        if (scrollEl) {
            scrollEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
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
                if (typeof window.validateEmployeeRegisterStep1 === 'function') {
                    const r = window.validateEmployeeRegisterStep1({
                        markFieldInvalid: markFieldInvalid,
                        markRadioInvalid: markRadioInvalid,
                    });
                    valid = r.valid;
                    if (r.firstEl && !firstEl) firstEl = r.firstEl;
                } else {
                    req('full_name', 'Name');
                    req('father_name', 'Father Name');
                    req('cnic', 'CNIC');
                    req('cnic_expiry', 'CNIC Expiry');
                    req('father_cnic', 'Father CNIC');
                    req('dob', 'Date of Birth');
                    req('nationality', 'Nationality');
                    req('gender', 'Gender');
                    req('domicile_province', 'Province');
                    req('domicile_district', 'District');
                    req('religion', 'Religion');
                    req('sect', 'Sect');
                    req('marital_status', 'Marital Status');
                    const maritalEl = document.querySelector('[name="marital_status"]');
                    if (maritalEl && maritalEl.value === 'Married') {
                        req('spouse_name', 'Spouse Name');
                        req('spouse_cnic', 'Spouse CNIC');
                        req('spouse_nationality', 'Spouse Nationality');
                    }
                    req('nok_name', 'NOK Name');
                    req('nok_cnic', 'NOK CNIC');
                    req('nok_cnic_expiry_date', 'NOK CNIC Expiry');
                    req('nok_relation_type', 'Relation with NOK');
                    const nokTypeEl = document.querySelector('[name="nok_relation_type"]');
                    if (nokTypeEl && nokTypeEl.value === 'Other') {
                        req('nok_relation_other', 'Specify relation with NOK');
                    }
                    req('nok_dob', 'NOK DOB');
                    req('nok_contact', 'NOK Contact');
                }
            } else if (step === 2) {
                reqRadio('employment_category', 'Resource Type');
                req('organization_id', 'Organization');
                req('role_id', 'Role');
                const rid = document.querySelector('[name="role_id"]');
                let role = null;
                if (rid && rid.value && Array.isArray(window._rolesData)) {
                    role = window._rolesData.find(r => r && String(r.id) === String(rid.value));
                }
                function isEmploymentOrgLevelRole(r) {
                    if (!r) return false;
                    if (typeof r.is_organization_level === 'boolean') return r.is_organization_level;
                    return r.department_id === null || r.department_id === undefined || r.department_id === '';
                }
                if (!isEmploymentOrgLevelRole(role)) {
                    req('sbu_id', 'SBU');
                }
                req('join_date', 'Date of Joining');
                req('engagement_mode', 'Work Arrangement');
                const modeEl = document.querySelector('[name="engagement_mode"]');
                if (modeEl && modeEl.value === 'hybrid') {
                    const hybridChecked = document.querySelectorAll('[name="hybrid_days[]"]:checked');
                    if (!hybridChecked.length) {
                        const chips = document.querySelector('#hybridDaysWrapper .hybrid-day-chips');
                        const wrap = document.getElementById('hybridDaysWrapper');
                        const target = chips || wrap || modeEl;
                        markFieldInvalid(target, 'Select at least one weekday for Hybrid.');
                        if (!firstEl) firstEl = target;
                        valid = false;
                    }
                }
                if (modeEl && modeEl.value === 'standard') {
                    reqRadio('standard_schedule_mode', 'Standard office hours');
                    const schedMode = document.querySelector('[name="standard_schedule_mode"]:checked');
                    const isCustom = schedMode && schedMode.value === 'custom';
                    if (isCustom) {
                        const wd = document.querySelectorAll('#standardScheduleCustomFields [name="working_days[]"]:checked');
                        if (!wd.length) {
                            const chipBox = document.querySelector('#standardScheduleCustomFields .d-flex.flex-wrap');
                            const target = chipBox || document.getElementById('standardScheduleCustomFields');
                            markFieldInvalid(target, 'Select at least one working day.');
                            if (!firstEl) firstEl = target;
                            valid = false;
                        }
                        const ws = document.querySelector('#step-2 [name="working_start_time"]');
                        const we = document.querySelector('#step-2 [name="working_end_time"]');
                        if (ws) {
                            const v = ws.value ? ws.value.trim() : '';
                            if (!v) {
                                markFieldInvalid(ws, 'Working start time is required.');
                                if (!firstEl) firstEl = ws;
                                valid = false;
                            }
                        }
                        if (we) {
                            const v = we.value ? we.value.trim() : '';
                            if (!v) {
                                markFieldInvalid(we, 'Working end time is required.');
                                if (!firstEl) firstEl = we;
                                valid = false;
                            }
                        }
                        if (ws && we && ws.value && we.value && we.value <= ws.value) {
                            markFieldInvalid(we, 'Working end time must be after start time.');
                            if (!firstEl) firstEl = we;
                            valid = false;
                        }
                    }
                }
                const catEl = document.querySelector('[name="employment_category"]:checked');
                const cat = catEl ? catEl.value : null;
                if (cat === 'intern') {
                    req('intern_type', 'Intern Type');
                    req('intern_duration', 'Intern Duration');
                } else if (cat === 'employee') {
                    req('employment_type', 'Permanent / Contractual');
                    const et = document.querySelector('[name="employment_type"]');
                    if (et && et.value === 'contractual') {
                        req('contractual_type', 'Contract type');
                        const ct = document.querySelector('[name="contractual_type"]');
                        if (ct && ct.value === 'time_bound') {
                            req('contract_start_date', 'Contract start date');
                            req('contract_end_date', 'Contract end date');
                        }
                    }
                }
            } else if (step === 3) {
                reqRadio('verification_status', 'Verification Status');
                var stEl = document.querySelector('#step-3 [name="verification_status"]:checked');
                var inProcess = stEl && stEl.value === 'In Process';
                if (!inProcess) {
                    req('msr_letter_no', 'MSR Letter No & Date');
                    req('addressee', 'Addressee');
                    req('verifying_authority', 'Verifying Authority');
                    req('verification_letter_no', 'Verification Letter No & Date');
                    req('next_verification_date', 'Next Verification Date');
                    req('police_remarks', 'Remarks');
                }
            } else if (step === 5) {
                if (typeof window.syncBankHiddenRowsFromCardSnapshots === 'function') {
                    window.syncBankHiddenRowsFromCardSnapshots();
                }
                var bankHidden = document.getElementById('bank-hidden-inputs');
                var bankListEl = document.getElementById('bankListing');
                var savedN = bankHidden ? bankHidden.querySelectorAll('[data-bank-saved-entry]').length : 0;
                if (savedN < 1) {
                    valid = false;
                    var draftCard = document.getElementById('bank-draft-form');
                    if (draftCard) {
                        markFieldInvalid(draftCard, 'Save at least one bank account with Save account.');
                        if (!firstEl) firstEl = draftCard;
                    }
                }
                if (savedN >= 1 && savedN < 2) {
                    valid = false;
                    if (bankListEl) {
                        markFieldInvalid(bankListEl, 'Save two bank accounts: one Personal and one Company operated.');
                        if (!firstEl) firstEl = bankListEl;
                    }
                }
                var salaryN = 0;
                var personalN = 0;
                var companyN = 0;
                if (bankHidden) {
                    bankHidden.querySelectorAll('[data-bank-saved-entry]').forEach(function (wrap) {
                        var inputs = wrap.querySelectorAll('input[type="hidden"]');
                        inputs.forEach(function (inp) {
                            var n = inp.name || '';
                            if (n.indexOf('is_salary_account') !== -1 && inp.value === '1') salaryN++;
                            if (n.indexOf('account_category') !== -1) {
                                var cv = String(inp.value || '').toLowerCase().trim();
                                if (cv === 'personal') personalN++;
                                if (cv === 'company_operated') companyN++;
                            }
                        });
                    });
                }
                if (savedN >= 2 && salaryN < 1) {
                    valid = false;
                    if (bankListEl) {
                        markFieldInvalid(bankListEl, 'Select at least one account for salary (payroll) using "Use for salary (payroll)" in the form above.');
                        if (!firstEl) firstEl = bankListEl;
                    }
                }
                if (savedN >= 2 && personalN < 1) {
                    valid = false;
                    if (bankListEl) {
                        markFieldInvalid(bankListEl, 'At least one saved account must be Personal.');
                        if (!firstEl) firstEl = bankListEl;
                    }
                }
                if (savedN >= 2 && companyN < 1) {
                    valid = false;
                    if (bankListEl) {
                        markFieldInvalid(bankListEl, 'At least one saved account must be Company operated.');
                        if (!firstEl) firstEl = bankListEl;
                    }
                }
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

    window.syncPoliceDetailRequiredUi = function () {
        var step3 = document.getElementById('step-3');
        if (!step3) return;
        var checked = step3.querySelector('[name="verification_status"]:checked');
        var optional = checked && checked.value === 'In Process';
        step3.querySelectorAll('.police-detail-req').forEach(function (span) {
            span.hidden = !!optional;
        });
    };

    document.addEventListener('DOMContentLoaded', function () {
        var step3 = document.getElementById('step-3');
        if (step3) {
            step3.addEventListener('change', function (e) {
                if (e.target && e.target.name === 'verification_status') {
                    window.syncPoliceDetailRequiredUi();
                }
            });
            window.syncPoliceDetailRequiredUi();
        }
    });
</script>
