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

            if (loading) {
                saveBtn.disabled = true;
                if (label) {
                    label.textContent = 'Saving...';
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

        initOrgSbuDepartmentCascade(config);

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

        const fieldIdMap = {
            department_ids: 'lt_dept_box',
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
            if (el.id === 'lt_dept_box') {
                return el.closest('.col-12') || el.parentElement;
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

            const storeUrl = config.storeUrl || form.getAttribute('action');
            if (!storeUrl) {
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

            fetch(storeUrl, {
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
                                title: 'Saved',
                                text: result.body.message || 'Leave type created successfully.',
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

    function initOrgSbuDepartmentCascade(config) {
        const orgSelect = document.getElementById('lt_organization_id');
        const sbuSelect = document.getElementById('lt_sbu_id');
        if (!orgSelect || !sbuSelect) {
            return;
        }

        let deptPool = [];
        let deptSelected = [];

        const deptBox = document.getElementById('lt_dept_box');
        const deptDropdown = document.getElementById('lt_dept_dropdown');
        const deptChips = document.getElementById('lt_dept_chips');
        const deptPlaceholder = document.getElementById('lt_dept_placeholder');
        const deptList = document.getElementById('lt_dept_list');
        const deptSearch = document.getElementById('lt_dept_search');
        const deptHiddenWrap = document.getElementById('lt_department_hidden_inputs');
        const selectAllDepts = document.getElementById('lt_select_all_departments');

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

        function resetDepartments(message) {
            deptPool = [];
            deptSelected = [];
            if (selectAllDepts) {
                selectAllDepts.checked = false;
                selectAllDepts.disabled = true;
            }
            if (deptPlaceholder) {
                deptPlaceholder.textContent = message || 'Select Organization and SBU first';
                deptPlaceholder.style.display = '';
            }
            if (deptDropdown) {
                deptDropdown.style.display = 'none';
            }
            if (deptBox) {
                deptBox.classList.remove('open');
            }
            if (deptSearch) {
                deptSearch.value = '';
            }
            if (deptList) {
                deptList.innerHTML = '';
            }
            syncDepartmentUi();
        }

        function renderDepartmentHiddenInputs() {
            if (!deptHiddenWrap) {
                return;
            }
            deptHiddenWrap.innerHTML = '';
            deptSelected.forEach(function (id) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'department_ids[]';
                input.value = id;
                deptHiddenWrap.appendChild(input);
            });
        }

        function renderDepartmentChips() {
            if (!deptChips || !deptPlaceholder) {
                return;
            }
            deptChips.innerHTML = '';
            if (!deptSelected.length) {
                deptPlaceholder.style.display = '';
                return;
            }
            deptPlaceholder.style.display = 'none';
            deptSelected.forEach(function (id) {
                const row = deptPool.find(function (d) {
                    return String(d.id) === String(id);
                });
                const name = row ? row.name : id;
                const chip = document.createElement('span');
                chip.className = 'lt-dept-chip';
                chip.textContent = name + ' ';
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'lt-dept-chip-x';
                removeBtn.setAttribute('aria-label', 'Remove ' + name);
                removeBtn.textContent = '×';
                removeBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    deptSelected = deptSelected.filter(function (v) {
                        return v !== id;
                    });
                    if (selectAllDepts) {
                        selectAllDepts.checked = false;
                    }
                    syncDepartmentUi();
                });
                chip.appendChild(removeBtn);
                deptChips.appendChild(chip);
            });
        }

        function renderDepartmentList() {
            if (!deptList) {
                return;
            }
            const q = (deptSearch?.value || '').toLowerCase().trim();
            const rows = deptPool.filter(function (d) {
                return !q || String(d.name || '').toLowerCase().includes(q);
            });
            if (!rows.length) {
                deptList.innerHTML = '<div class="lt-dept-no-result">No departments found</div>';
                return;
            }
            deptList.innerHTML = '';
            rows.forEach(function (d) {
                const id = String(d.id);
                const picked = deptSelected.includes(id);
                const opt = document.createElement('div');
                opt.className = 'lt-dept-opt' + (picked ? ' picked' : '');
                opt.innerHTML =
                    '<span class="lt-dept-opt-cb">' +
                    '<svg class="lt-dept-opt-ck" viewBox="0 0 16 16" fill="none">' +
                    '<path d="M3.5 8.2l3 3L12.5 5" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' +
                    '</svg></span>' +
                    '<span class="lt-dept-opt-name"></span>';
                opt.querySelector('.lt-dept-opt-name').textContent = d.name;
                opt.addEventListener('click', function () {
                    if (picked) {
                        deptSelected = deptSelected.filter(function (v) {
                            return v !== id;
                        });
                    } else {
                        deptSelected.push(id);
                    }
                    if (selectAllDepts) {
                        selectAllDepts.checked =
                            deptPool.length > 0 && deptSelected.length === deptPool.length;
                    }
                    syncDepartmentUi();
                });
                deptList.appendChild(opt);
            });
        }

        function syncDepartmentUi() {
            renderDepartmentHiddenInputs();
            renderDepartmentChips();
            renderDepartmentList();
        }

        function loadSbus(organizationId, selectedSbuId) {
            if (!organizationId) {
                setSbuOptions([]);
                sbuSelect.disabled = true;
                resetDepartments('Select Organization and SBU first');
                return;
            }

            sbuSelect.disabled = true;
            setSbuOptions([], null);
            resetDepartments('Select an SBU first');

            const url = new URL(config.sbuUrl, window.location.origin);
            url.searchParams.set('organization_id', organizationId);

            fetch(url.toString(), { headers: jsonHeaders() })
                .then(function (res) {
                    return res.json();
                })
                .then(function (data) {
                    const sbus = Array.isArray(data.sbus) ? data.sbus : [];
                    setSbuOptions(sbus, selectedSbuId);
                    if (selectedSbuId) {
                        loadDepartments(selectedSbuId, []);
                    }
                })
                .catch(function () {
                    setSbuOptions([]);
                    resetDepartments('Error loading SBUs');
                });
        }

        function loadDepartments(sbuId, selectedIds) {
            selectedIds = (selectedIds || []).map(String);
            if (!sbuId) {
                resetDepartments(orgSelect.value ? 'Select an SBU first' : 'Select Organization and SBU first');
                return;
            }

            if (deptPlaceholder) {
                deptPlaceholder.textContent = 'Loading departments...';
                deptPlaceholder.style.display = '';
            }
            if (deptList) {
                deptList.innerHTML = '';
            }

            const url = new URL(config.departmentUrl, window.location.origin);
            url.searchParams.set('sbu_id', sbuId);

            fetch(url.toString(), { headers: jsonHeaders() })
                .then(function (res) {
                    return res.json();
                })
                .then(function (data) {
                    const departments = Array.isArray(data.departments) ? data.departments : [];
                    deptPool = departments.map(function (dept) {
                        return { id: String(dept.id), name: dept.name };
                    });
                    deptSelected = selectedIds.filter(function (id) {
                        return deptPool.some(function (d) {
                            return d.id === id;
                        });
                    });
                    if (selectAllDepts) {
                        selectAllDepts.disabled = deptPool.length === 0;
                        selectAllDepts.checked =
                            deptPool.length > 0 && deptSelected.length === deptPool.length;
                    }
                    if (deptPlaceholder) {
                        deptPlaceholder.textContent = deptPool.length
                            ? 'Select Departments...'
                            : 'No departments found for this SBU';
                    }
                    syncDepartmentUi();
                })
                .catch(function () {
                    deptPool = [];
                    deptSelected = [];
                    if (selectAllDepts) {
                        selectAllDepts.checked = false;
                        selectAllDepts.disabled = true;
                    }
                    if (deptPlaceholder) {
                        deptPlaceholder.textContent = 'Error loading departments';
                    }
                    syncDepartmentUi();
                });
        }

        orgSelect.addEventListener('change', function () {
            const orgId = orgSelect.value;
            loadSbus(orgId, null);
        });

        sbuSelect.addEventListener('change', function () {
            if (selectAllDepts) {
                selectAllDepts.checked = false;
            }
            loadDepartments(sbuSelect.value, []);
        });

        if (selectAllDepts) {
            selectAllDepts.addEventListener('change', function () {
                if (selectAllDepts.checked) {
                    deptSelected = deptPool.map(function (d) {
                        return d.id;
                    });
                } else {
                    deptSelected = [];
                }
                syncDepartmentUi();
            });
        }

        if (deptBox && deptDropdown) {
            deptBox.addEventListener('click', function (e) {
                if (!deptPool.length) {
                    return;
                }
                e.stopPropagation();
                const isOpen = deptDropdown.style.display !== 'none';
                if (isOpen) {
                    deptDropdown.style.display = 'none';
                    deptBox.classList.remove('open');
                } else {
                    deptDropdown.style.display = 'block';
                    deptBox.classList.add('open');
                    renderDepartmentList();
                    if (deptSearch) {
                        deptSearch.focus();
                    }
                }
            });

            deptBox.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    deptBox.click();
                }
            });
        }

        if (deptSearch) {
            deptSearch.addEventListener('input', renderDepartmentList);
            deptSearch.addEventListener('click', function (e) {
                e.stopPropagation();
            });
        }

        document.addEventListener('click', function (e) {
            if (!deptBox || !deptDropdown) {
                return;
            }
            if (!deptDropdown.contains(e.target) && !deptBox.contains(e.target)) {
                deptDropdown.style.display = 'none';
                deptBox.classList.remove('open');
            }
        });

        resetDepartments('Select Organization and SBU first');
    }
})();
