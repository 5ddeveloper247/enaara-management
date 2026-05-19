(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('leaveTypeAddForm');
        if (!form) {
            return;
        }

        const config = window.leaveTypeFormConfig || {};
        const desc = document.getElementById('lt_description');
        const descCount = document.getElementById('lt_description_count');
        const saveBtn = document.getElementById('ltSaveBtn');
        let isSubmitting = false;

        function setSaveButtonLoading(loading) {
            if (!saveBtn) {
                return;
            }
            const label = saveBtn.querySelector('.lt-save-label');
            const icon = saveBtn.querySelector('.lt-save-icon');
            const defaultLabel = saveBtn.getAttribute('data-default-label') || 'Save Leave Type';
            const loadingLabel = saveBtn.getAttribute('data-loading-label') || 'Saving...';

            if (loading) {
                saveBtn.disabled = true;
                if (label) {
                    label.textContent = loadingLabel;
                }
                if (icon) {
                    icon.className = 'spinner-border spinner-border-sm me-1 lt-save-icon';
                    icon.setAttribute('role', 'status');
                    icon.setAttribute('aria-hidden', 'true');
                }
                return;
            }

            saveBtn.disabled = false;
            if (label) {
                label.textContent = defaultLabel;
            }
            if (icon) {
                icon.className = 'bi bi-check-lg me-1 lt-save-icon';
                icon.removeAttribute('role');
                icon.setAttribute('aria-hidden', 'true');
            }
        }

        const cascadeApi = initOrgSbuCascade(config);

        function bindCounter(textarea, counterEl) {
            if (!textarea || !counterEl) {
                return;
            }
            const update = function () {
                counterEl.textContent = String((textarea.value || '').length);
            };
            textarea.addEventListener('input', update);
            update();
        }

        bindCounter(desc, descCount);

        function labelForSelect(select) {
            if (!select || select.selectedIndex < 0) {
                return '—';
            }
            return select.options[select.selectedIndex].text || '—';
        }

        function updatePreview() {
            const name = (document.getElementById('lt_name')?.value || '').trim();
            const code = (document.getElementById('lt_code')?.value || '').trim();
            const category = labelForSelect(document.getElementById('lt_category'));
            const entitlement = document.getElementById('lt_entitlement_days')?.value || '0';
            const unit = labelForSelect(document.getElementById('lt_unit'));
            const carry = labelForSelect(document.getElementById('lt_carry_forward'));
            const encash = labelForSelect(document.getElementById('lt_encashment_allowed'));
            const active = document.getElementById('lt_is_active')?.checked;

            setText('preview_name', name || '—');
            setText('preview_code', code || '—');
            setText('preview_category', category);
            setText('preview_entitlement', entitlement + ' ' + unit);
            setText('preview_carry_forward', carry);
            setText('preview_encashment', encash);

            const statusEl = document.getElementById('preview_status');
            if (statusEl) {
                statusEl.innerHTML = active
                    ? '<span class="lt-preview-badge-active">Active</span>'
                    : '<span class="lt-preview-badge-inactive">Inactive</span>';
            }
        }

        function setText(id, value) {
            const el = document.getElementById(id);
            if (el) {
                el.textContent = value;
            }
        }

        form.querySelectorAll('input, select, textarea').forEach(function (el) {
            el.addEventListener('input', updatePreview);
            el.addEventListener('change', updatePreview);
        });

        updatePreview();

        function setFormFieldValue(name, value) {
            const el = form.querySelector('[name="' + name + '"]');
            if (!el) {
                return;
            }
            if (el.type === 'checkbox') {
                el.checked = !!value;
                return;
            }
            if (value === null || value === undefined) {
                el.value = '';
                return;
            }
            el.value = String(value);
        }

        function populateLeaveTypeForm(data, cascade) {
            if (!data) {
                return;
            }

            setFormFieldValue('name', data.name);
            setFormFieldValue('code', data.code);
            setFormFieldValue('leave_category', data.leave_category);
            setFormFieldValue('description', data.description);
            setFormFieldValue('annual_quota', data.annual_quota);
            setFormFieldValue('is_active', data.is_active);
            setFormFieldValue('employment_type', data.employment_type || 'all');
            setFormFieldValue('gender', data.gender || 'all');
            setFormFieldValue('min_service_months', data.min_service_months ?? 0);
            setFormFieldValue('eligible_from', data.eligible_from || 'doj');
            setFormFieldValue('probation_eligible', data.probation_eligible);
            setFormFieldValue('unit_of_leave', data.unit_of_leave || 'days');
            setFormFieldValue('accrual_frequency', data.accrual_frequency || '');
            setFormFieldValue('accrual_start_month', data.accrual_start_month || '');
            setFormFieldValue('carry_forward', data.carry_forward || 'no');
            setFormFieldValue('max_carry_forward_days', data.max_carry_forward_days ?? '');
            setFormFieldValue('encashment_allowed', data.encashment_allowed || 'no');
            setFormFieldValue('encashment_rule', data.encashment_rule || '');
            setFormFieldValue('max_consecutive_days', data.max_consecutive_days ?? '');
            setFormFieldValue('advance_notice_days', data.advance_notice_days ?? 0);
            setFormFieldValue('short_leave_applicable', data.short_leave_applicable);
            setFormFieldValue('short_leave_max_hours', data.short_leave_max_hours || '');

            if (desc && data.description) {
                const countEl = document.getElementById('lt_description_count');
                if (countEl) {
                    countEl.textContent = String(data.description).length;
                }
            }

            const orgSelect = document.getElementById('lt_organization_id');
            if (orgSelect && data.organization_id && cascade && cascade.loadSbus) {
                orgSelect.value = String(data.organization_id);
                cascade.loadSbus(data.organization_id, data.sbu_id || null);
            }

            updatePreview();
        }

        if (config.initialData) {
            populateLeaveTypeForm(config.initialData, cascadeApi);
        }

        const fieldIdMap = {
            annual_quota: 'lt_entitlement_days',
            min_service_months: 'lt_min_service',
            max_carry_forward_days: 'lt_max_carry_forward',
            max_consecutive_days: 'lt_max_consecutive',
            advance_notice_days: 'lt_advance_notice',
            short_leave_max_hours: 'lt_short_leave_max',
            short_leave_applicable: 'lt_short_leave',
            accrual_start_month: 'lt_accrual_start_month',
            accrual_frequency: 'lt_accrual_frequency',
            encashment_allowed: 'lt_encashment_allowed',
            encashment_rule: 'lt_encashment_rule',
            carry_forward: 'lt_carry_forward',
            leave_category: 'lt_category',
            organization_id: 'lt_organization_id',
            sbu_id: 'lt_sbu_id',
            employment_type: 'lt_employment_type',
            gender: 'lt_gender',
            eligible_from: 'lt_eligible_from',
            unit_of_leave: 'lt_unit',
            probation_eligible: 'lt_probation_eligible',
            is_active: 'lt_is_active',
            name: 'lt_name',
            code: 'lt_code',
            description: 'lt_description',
        };

        function resolveFieldElement(fieldName) {
            const elId = fieldIdMap[fieldName] || ('lt_' + fieldName);
            let el = document.getElementById(elId);
            if (!el) {
                el = form.querySelector('[name="' + fieldName + '"]');
            }
            return el;
        }

        function getErrorColumn(el) {
            if (!el) {
                return null;
            }
            const suffix = el.closest('.lt-suffix-field');
            if (suffix) {
                return suffix.closest('[class*="col-"]') || suffix.parentElement;
            }
            const switchWrap = el.closest('.lt-inline-switch');
            if (switchWrap) {
                return switchWrap.closest('[class*="col-"]') || switchWrap.parentElement;
            }
            return el.closest('[class*="col-"]') || el.parentElement;
        }

        function markInvalid(el) {
            if (!el) {
                return;
            }
            el.classList.add('is-invalid');
            const suffix = el.closest('.lt-suffix-field');
            if (suffix) {
                suffix.classList.add('is-invalid');
            }
            const switchWrap = el.closest('.lt-inline-switch');
            if (switchWrap) {
                switchWrap.classList.add('is-invalid');
            }
        }

        function clearFieldErrorForElement(el) {
            if (!el) {
                return;
            }
            el.classList.remove('is-invalid');
            const suffix = el.closest('.lt-suffix-field');
            if (suffix) {
                suffix.classList.remove('is-invalid');
            }
            const switchWrap = el.closest('.lt-inline-switch');
            if (switchWrap) {
                switchWrap.classList.remove('is-invalid');
            }
            const col = getErrorColumn(el);
            if (col) {
                col.querySelectorAll('.invalid-feedback.lt-field-error').forEach(function (node) {
                    node.remove();
                });
            }
        }

        function clearFieldErrors() {
            form.querySelectorAll('.is-invalid').forEach(function (el) {
                el.classList.remove('is-invalid');
            });
            form.querySelectorAll('.invalid-feedback.lt-field-error').forEach(function (el) {
                el.remove();
            });
        }

        function showFieldError(fieldName, message) {
            const el = resolveFieldElement(fieldName);
            if (!el) {
                return;
            }

            const col = getErrorColumn(el);
            if (!col) {
                return;
            }

            col.querySelectorAll('.invalid-feedback.lt-field-error').forEach(function (node) {
                node.remove();
            });

            markInvalid(el);

            const feedback = document.createElement('div');
            feedback.className = 'invalid-feedback d-block lt-field-error';
            feedback.setAttribute('role', 'alert');
            feedback.textContent = Array.isArray(message) ? message[0] : String(message);
            col.appendChild(feedback);
        }

        function showValidationErrors(errors) {
            clearFieldErrors();
            const seen = {};
            Object.keys(errors || {}).forEach(function (key) {
                const baseKey = key.split('.')[0];
                if (seen[baseKey]) {
                    return;
                }
                seen[baseKey] = true;
                showFieldError(baseKey, errors[key]);
            });

            const firstInvalid = form.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                if (typeof firstInvalid.focus === 'function') {
                    firstInvalid.focus({ preventScroll: true });
                }
            }
        }

        form.querySelectorAll('input, select, textarea').forEach(function (el) {
            const clearOnChange = function () {
                clearFieldErrorForElement(el);
            };
            el.addEventListener('input', clearOnChange);
            el.addEventListener('change', clearOnChange);
        });

        function submitForm() {
            if (isSubmitting) {
                return;
            }

            const submitUrl = config.submitUrl || config.storeUrl || form.getAttribute('action');
            if (!submitUrl) {
                return;
            }

            isSubmitting = true;
            setSaveButtonLoading(true);

            clearFieldErrors();
            const formData = new FormData(form);
            if (!formData.has('is_active')) {
                formData.append('is_active', '0');
            }
            if (!formData.has('probation_eligible')) {
                formData.append('probation_eligible', '0');
            }
            if (!formData.has('short_leave_applicable')) {
                formData.append('short_leave_applicable', '0');
            }

            let submitSucceeded = false;

            fetch(submitUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': config.csrfToken || '',
                },
                body: formData,
            })
                .then(function (res) {
                    return res.json().then(function (body) {
                        return { ok: res.ok, status: res.status, body: body };
                    });
                })
                .then(function (result) {
                    if (result.ok && result.body.success) {
                        submitSucceeded = true;
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: config.successTitle || 'Saved',
                                text: result.body.message || config.successMessage || 'Leave type saved successfully.',
                                confirmButtonColor: '#012445',
                            }).then(function () {
                                window.location.href = config.indexUrl || '/admin/leave-type';
                            });
                        } else {
                            window.location.href = config.indexUrl || '/admin/leave-type';
                        }
                        return;
                    }
                    if (result.status === 422 && result.body.errors) {
                        showValidationErrors(result.body.errors);
                        return;
                    }
                    const msg = result.body.message || 'Failed to save leave type.';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Error', text: msg, confirmButtonColor: '#012445' });
                    }
                })
                .catch(function () {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to save leave type. Please try again.',
                            confirmButtonColor: '#012445',
                        });
                    }
                })
                .finally(function () {
                    isSubmitting = false;
                    if (!submitSucceeded) {
                        setSaveButtonLoading(false);
                    }
                });
        }

        if (saveBtn) {
            saveBtn.addEventListener('click', function (e) {
                e.preventDefault();
                submitForm();
            });
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            submitForm();
        });
    });

    function initOrgSbuCascade(config) {
        const orgSelect = document.getElementById('lt_organization_id');
        const sbuSelect = document.getElementById('lt_sbu_id');
        if (!orgSelect || !sbuSelect) {
            return {};
        }

        function jsonHeaders() {
            return {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            };
        }

        function setSbuOptions(items, selectedId) {
            sbuSelect.innerHTML = '';
            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.hidden = true;
            placeholder.selected = !selectedId;
            placeholder.textContent = items.length ? 'Select SBU' : 'No SBU found';
            sbuSelect.appendChild(placeholder);

            items.forEach(function (sbu) {
                const opt = document.createElement('option');
                opt.value = String(sbu.id);
                opt.textContent = sbu.name;
                if (selectedId && String(sbu.id) === String(selectedId)) {
                    opt.selected = true;
                }
                sbuSelect.appendChild(opt);
            });

            sbuSelect.disabled = items.length === 0;
        }

        function loadSbus(organizationId, selectedSbuId) {
            if (!organizationId) {
                setSbuOptions([]);
                sbuSelect.disabled = true;
                return;
            }

            sbuSelect.disabled = true;
            setSbuOptions([], null);

            const url = new URL(config.sbuUrl, window.location.origin);
            url.searchParams.set('organization_id', organizationId);

            fetch(url.toString(), { headers: jsonHeaders() })
                .then(function (res) {
                    return res.json();
                })
                .then(function (data) {
                    const sbus = Array.isArray(data.sbus) ? data.sbus : [];
                    setSbuOptions(sbus, selectedSbuId);
                })
                .catch(function () {
                    setSbuOptions([]);
                });
        }

        orgSelect.addEventListener('change', function () {
            loadSbus(orgSelect.value, null);
        });

        return {
            loadSbus: loadSbus,
        };
    }

})();
