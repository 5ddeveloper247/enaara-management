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

        const cascadeApi = initOrgSbuMultiPicker(config);

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

        function preventWheelChangingNumberInput(el) {
            if (!el) {
                return;
            }
            el.addEventListener(
                'wheel',
                function (e) {
                    if (document.activeElement === el) {
                        e.preventDefault();
                    }
                },
                { passive: false }
            );
        }

        preventWheelChangingNumberInput(document.getElementById('lt_entitlement_days'));

        function labelForSelect(select) {
            if (!select || select.selectedIndex < 0) {
                return '—';
            }
            return select.options[select.selectedIndex].text || '—';
        }

        function updatePreview() {
            const name = (document.getElementById('lt_name')?.value || '').trim();
            const leaveCondition = labelForSelect(document.getElementById('lt_leave_condition'));
            const code = (document.getElementById('lt_code')?.value || '').trim();
            const category = labelForSelect(document.getElementById('lt_category'));
            const entitlement = document.getElementById('lt_entitlement_days')?.value || '0';
            const unit = labelForSelect(document.getElementById('lt_unit'));
            const carry = labelForSelect(document.getElementById('lt_carry_forward'));
            const encash = labelForSelect(document.getElementById('lt_encashment_allowed'));
            const active = document.getElementById('lt_is_active')?.checked;

            setText('preview_name', name || '—');
            setText('preview_leave_condition', leaveCondition);
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

            // Hide/Show Accrual Start Month when frequency is 'once_in_tenure'
            const freqEl = document.getElementById('lt_accrual_frequency');
            const startMonthEl = document.getElementById('lt_accrual_start_month');
            if (freqEl && startMonthEl) {
                const col = startMonthEl.closest('.col-md-3');
                if (col) {
                    if (freqEl.value === 'once_in_tenure') {
                        col.style.display = 'none';
                    } else {
                        col.style.display = '';
                    }
                }
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
        
        // Carry Forward + Encashment field visibility
        const carryForwardEl = document.getElementById('lt_carry_forward');
        const encashmentAllowedEl = document.getElementById('lt_encashment_allowed');
        const encashmentRuleEl = document.getElementById('lt_encashment_rule');
        const encashmentRuleCol = document.getElementById('encashment_rule_col');
        const encashmentRulesSection = document.getElementById('encashment_rules_section');
        const addEncashmentRuleBtn = document.getElementById('add_encashment_rule_btn');
        const encashmentRulesTbody = document.getElementById('encashment_rules_tbody');
        const noRulesRow = document.getElementById('no_rules_row');
        let encashmentRuleIndex = 0;

        function toggleCarryForwardFields() {
            if (!carryForwardEl) return;
            const showMax = carryForwardEl.value === 'yes';
            document.querySelectorAll('.carry-forward-dependent').forEach(el => {
                el.style.display = showMax ? '' : 'none';
            });
        }

        function toggleEncashmentFields() {
            if (!encashmentAllowedEl) return;
            const allowed = encashmentAllowedEl.value;
            const isAllowed = allowed !== 'no';
            const showRuleType = allowed === 'yes';
            const showRules = allowed === 'as_per_policy'
                || (allowed === 'yes' && encashmentRuleEl && encashmentRuleEl.value === 'partial');

            if (encashmentRuleCol) {
                encashmentRuleCol.style.display = showRuleType ? '' : 'none';
            }
            if (encashmentRulesSection) {
                encashmentRulesSection.style.display = showRules ? '' : 'none';
            }
            document.querySelectorAll('.encashment-dependent').forEach(el => {
                if (el.id === 'encashment_rules_section') return;
                el.style.display = isAllowed ? '' : 'none';
            });
        }

        if (carryForwardEl) {
            carryForwardEl.addEventListener('change', toggleCarryForwardFields);
            toggleCarryForwardFields();
        }

        if (encashmentAllowedEl) {
            encashmentAllowedEl.addEventListener('change', toggleEncashmentFields);
            toggleEncashmentFields();
        }
        if (encashmentRuleEl) {
            encashmentRuleEl.addEventListener('change', toggleEncashmentFields);
        }

        function addEncashmentRuleRow(data = {}) {
            if (noRulesRow) noRulesRow.style.display = 'none';

            const ruleIndex = encashmentRuleIndex;
            const ruleIndexRef = { current: ruleIndex };

            let savedRoleIds = data.role_level_ids || [];
            if (!Array.isArray(savedRoleIds)) {
                savedRoleIds = savedRoleIds ? [savedRoleIds] : [];
            }
            savedRoleIds = savedRoleIds.map(id => parseInt(id));

            // Build distinct level options sorted by level number
            // Group all role_level rows that share the same level number
            let roleLevelOptions = '';
            const levelMap = new Map(); // level -> [ids]
            const sortedLevels = (config.roleLevels && Array.isArray(config.roleLevels))
                ? [...config.roleLevels].sort((a, b) => a.level - b.level)
                : [];

            sortedLevels.forEach(rl => {
                if (!levelMap.has(rl.level)) {
                    levelMap.set(rl.level, []);
                }
                levelMap.get(rl.level).push(parseInt(rl.id));
            });

            levelMap.forEach((ids, level) => {
                // A level is "checked" if any of its IDs are in savedRoleIds
                const isChecked = ids.some(id => savedRoleIds.includes(id)) ? 'checked' : '';
                // Store all IDs as a JSON data attribute; actual hidden inputs generated on Apply
                roleLevelOptions += `
                    <div class="role-item d-flex align-items-center gap-3 px-3 py-2 border-bottom" style="cursor:pointer;" data-level="${level}" data-ids="${ids.join(',')}">
                        <input class="form-check-input role-level-checkbox flex-shrink-0 mt-0 shadow-none" type="checkbox"
                            id="rl_${encashmentRuleIndex}_${level}"
                            data-name="Level ${level}"
                            data-ids="${ids.join(',')}"
                            ${isChecked}
                            style="width:1.1em;height:1.1em;cursor:pointer;">
                        <label class="flex-grow-1 mb-0 fw-medium" for="rl_${encashmentRuleIndex}_${level}" style="cursor:pointer; font-size:0.875rem;">
                            Level ${level}
                        </label>
                    </div>
                `;
            });

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="align-top pt-2">
                    <div class="d-flex align-items-center gap-2">
                        <input type="number" class="form-control text-center px-1" name="encashment_rules[${encashmentRuleIndex}][service_years]" min="0" value="${data.service_years || '0'}" style="width:60px;" required>
                        <span class="text-secondary small">Yrs</span>
                        <input type="number" class="form-control text-center px-1" name="encashment_rules[${encashmentRuleIndex}][service_months]" min="0" max="11" value="${data.service_months || '0'}" style="width:60px;" required>
                        <span class="text-secondary small">Mos</span>
                    </div>
                </td>
                <td class="align-top pt-2">
                    <div class="custom-role-picker position-relative">
                        <button type="button" class="btn bg-white border w-100 d-flex justify-content-between align-items-center role-dropdown-btn shadow-sm py-2 px-3">
                            <span class="role-btn-text text-dark" style="font-size:0.9rem;">Select Level(s)</span>
                            <i class="bi bi-chevron-down text-secondary" style="font-size:0.75rem;"></i>
                        </button>
                        <div class="d-flex flex-wrap gap-1 mt-1 role-badges-container"></div>
                        <div class="rule-hidden-ids"></div>
                        <div class="role-dropdown-panel border rounded-3 bg-white shadow position-absolute w-100 d-none" style="z-index:9999; top:calc(100% + 4px); left:0; min-width:220px;">
                            <div class="role-list-container" style="max-height:220px;overflow-y:auto;">
                                ${roleLevelOptions}
                            </div>
                            <div class="p-2 border-top d-flex justify-content-end gap-2 bg-white rounded-bottom-3">
                                <button type="button" class="btn btn-sm btn-light border role-cancel-btn px-3">Cancel</button>
                                <button type="button" class="btn btn-sm btn-dark role-apply-btn px-3">Apply</button>
                            </div>
                        </div>
                    </div>
                </td>
                <td class="align-top pt-2">
                    <div class="d-flex align-items-center gap-2">
                        <input type="number" class="form-control text-center px-1" name="encashment_rules[${encashmentRuleIndex}][max_forward_days]" min="0" step="0.25" value="${data.max_forward_days || ''}" style="width:80px;" required>
                        <span class="text-secondary small">Days</span>
                    </div>
                </td>
                <td class="align-top pt-2 text-end">
                    <button type="button" class="btn btn-light border remove-rule-btn px-2 py-1 text-secondary" title="Remove">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;

            // --- Manual dropdown toggle ---
            const dropdownBtn  = tr.querySelector('.role-dropdown-btn');
            const dropdownPanel = tr.querySelector('.role-dropdown-panel');
            const checkboxes   = tr.querySelectorAll('.role-level-checkbox');
            const applyBtn     = tr.querySelector('.role-apply-btn');
            const cancelBtn    = tr.querySelector('.role-cancel-btn');
            const btnText      = tr.querySelector('.role-btn-text');
            const badgesContainer = tr.querySelector('.role-badges-container');
            const roleItems    = tr.querySelectorAll('.role-item');

            let currentlySavedLevels = new Set(
                Array.from(checkboxes)
                    .filter(cb => cb.checked)
                    .map(cb => cb.closest('.role-item').getAttribute('data-level'))
            );

            dropdownBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                document.querySelectorAll('.role-dropdown-panel').forEach(p => {
                    if (p !== dropdownPanel) p.classList.add('d-none');
                });
                dropdownPanel.classList.toggle('d-none');
            });

            document.addEventListener('click', (e) => {
                if (!tr.contains(e.target)) {
                    dropdownPanel.classList.add('d-none');
                }
            });

            roleItems.forEach(item => {
                item.addEventListener('click', (e) => {
                    if (e.target.type !== 'checkbox' && e.target.tagName !== 'LABEL') {
                        const cb = item.querySelector('.role-level-checkbox');
                        if (cb) cb.checked = !cb.checked;
                    }
                });
            });

            function getHiddenInputsContainer() {
                return tr.querySelector('.rule-hidden-ids');
            }

            function reindexRuleInputs(newIndex) {
                ruleIndexRef.current = newIndex;
                tr.querySelectorAll('input[name^="encashment_rules["]').forEach(function (input) {
                    input.name = input.name.replace(
                        /encashment_rules\[\d+\]/,
                        'encashment_rules[' + newIndex + ']'
                    );
                });
            }

            function applyHiddenInputs() {
                const container = getHiddenInputsContainer();
                if (!container) {
                    return;
                }
                container.innerHTML = '';
                checkboxes.forEach(cb => {
                    if (currentlySavedLevels.has(cb.closest('.role-item').getAttribute('data-level'))) {
                        const ids = (cb.getAttribute('data-ids') || '').split(',').filter(Boolean);
                        ids.forEach(id => {
                            const inp = document.createElement('input');
                            inp.type = 'hidden';
                            inp.name = 'encashment_rules[' + ruleIndexRef.current + '][role_level_ids][]';
                            inp.value = id.trim();
                            container.appendChild(inp);
                        });
                    }
                });
            }

            tr.syncEncashmentRuleRow = function (newIndex) {
                reindexRuleInputs(newIndex);
                applyHiddenInputs();
            };

            function renderBadges() {
                const savedCheckboxes = Array.from(checkboxes).filter(cb =>
                    currentlySavedLevels.has(cb.closest('.role-item').getAttribute('data-level'))
                );

                if (savedCheckboxes.length === 0) {
                    btnText.textContent = 'Select Level(s)';
                } else if (savedCheckboxes.length === 1) {
                    btnText.textContent = savedCheckboxes[0].getAttribute('data-name');
                } else {
                    btnText.textContent = savedCheckboxes.length + ' levels selected';
                }

                badgesContainer.innerHTML = '';
                savedCheckboxes.forEach(cb => {
                    const level = cb.closest('.role-item').getAttribute('data-level');
                    const badge = document.createElement('span');
                    badge.style.cssText = 'display:inline-flex;align-items:center;gap:4px;background:#dbeafe;color:#1d4ed8;border:1px solid #bfdbfe;border-radius:6px;padding:2px 8px;font-size:0.78rem;font-weight:500;';
                    const xBtn = document.createElement('i');
                    xBtn.className = 'bi bi-x';
                    xBtn.style.cursor = 'pointer';
                    xBtn.dataset.level = level;
                    badge.appendChild(xBtn);
                    badge.append(' ' + cb.getAttribute('data-name'));
                    badgesContainer.appendChild(badge);

                    xBtn.addEventListener('click', () => {
                        currentlySavedLevels.delete(xBtn.dataset.level);
                        const targetCb = Array.from(checkboxes).find(c =>
                            c.closest('.role-item').getAttribute('data-level') === xBtn.dataset.level
                        );
                        if (targetCb) targetCb.checked = false;
                        applyHiddenInputs();
                        renderBadges();
                    });
                });
            }

            function syncCheckboxesToSaved() {
                checkboxes.forEach(cb => {
                    const lvl = cb.closest('.role-item').getAttribute('data-level');
                    cb.checked = currentlySavedLevels.has(lvl);
                });
            }

            applyBtn.addEventListener('click', () => {
                currentlySavedLevels = new Set(
                    Array.from(checkboxes)
                        .filter(cb => cb.checked)
                        .map(cb => cb.closest('.role-item').getAttribute('data-level'))
                );
                applyHiddenInputs();
                renderBadges();
                dropdownPanel.classList.add('d-none');
            });

            cancelBtn.addEventListener('click', () => {
                syncCheckboxesToSaved();
                dropdownPanel.classList.add('d-none');
            });

            applyHiddenInputs();
            renderBadges();
            syncCheckboxesToSaved();

            tr.querySelector('.remove-rule-btn').addEventListener('click', function () {
                Swal.fire({
                    title: 'Remove Rule?',
                    text: 'Are you sure you want to remove this encashment rule?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, remove it',
                    cancelButtonText: 'Cancel',
                    buttonsStyling: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        tr.remove();
                        if (encashmentRulesTbody.querySelectorAll('tr:not(#no_rules_row)').length === 0) {
                            if (noRulesRow) noRulesRow.style.display = '';
                        }
                    }
                });
            });

            encashmentRulesTbody.appendChild(tr);
            encashmentRuleIndex++;
        }

        if (addEncashmentRuleBtn) {
            addEncashmentRuleBtn.addEventListener('click', () => addEncashmentRuleRow());
        }

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
            setFormFieldValue('leave_condition', data.leave_condition);
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
            setFormFieldValue('encashment_rule', data.encashment_rule || 'partial');
            setFormFieldValue('max_consecutive_days', data.max_consecutive_days ?? '');
            setFormFieldValue('advance_notice_days', data.advance_notice_days ?? 0);
            setFormFieldValue('short_leave_applicable', data.short_leave_applicable);
            setFormFieldValue('short_leave_max_hours', data.short_leave_max_hours || '');
            
            // Populate encashment rules if any
            if (data.encashment_rules && Array.isArray(data.encashment_rules)) {
                data.encashment_rules.forEach(rule => {
                    if (typeof addEncashmentRuleRow === 'function') {
                        addEncashmentRuleRow(rule);
                    }
                });
            }

            if (desc && data.description) {
                const countEl = document.getElementById('lt_description_count');
                if (countEl) {
                    countEl.textContent = String(data.description).length;
                }
            }

            const orgSelect = document.getElementById('lt_organization_id');
            if (orgSelect && data.organization_id && cascade && cascade.loadSbus) {
                orgSelect.value = String(data.organization_id);
                cascade.loadSbus(data.organization_id, data.sbu_ids || (data.sbu_id ? [data.sbu_id] : []), function () {
                    loadEntitlementReference(config, cascade);
                });
            }

            toggleCarryForwardFields();
            toggleEncashmentFields();
            updatePreview();
        }

        if (config.initialData) {
            populateLeaveTypeForm(config.initialData, cascadeApi);
        } else {
            loadEntitlementReference(config, cascadeApi);
        }

        const fieldIdMap = {
            sbu_ids: 'lt_sbu_box',
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
            leave_condition: 'lt_leave_condition',
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
            const encashmentRoleMatch = fieldName.match(/^encashment_rules\.(\d+)\.role_level_ids$/);
            if (encashmentRoleMatch && encashmentRulesTbody) {
                const rows = encashmentRulesTbody.querySelectorAll('tr:not(#no_rules_row)');
                const row = rows[parseInt(encashmentRoleMatch[1], 10)];
                if (row) {
                    return row.querySelector('.role-dropdown-btn');
                }
            }

            const elId = fieldIdMap[fieldName] || ('lt_' + fieldName);
            let el = document.getElementById(elId);
            if (!el) {
                // Try exact name match (e.g. for array fields like encashment_rules[0][service_years])
                el = form.querySelector(`[name="${fieldName}"]`);
            }
            if (!el) {
                // Try converting dot notation to bracket notation: encashment_rules.0.service_years -> encashment_rules[0][service_years]
                const parts = fieldName.split('.');
                if (parts.length > 1) {
                    const bracketName = parts[0] + parts.slice(1).map(p => `[${p}]`).join('');
                    el = form.querySelector(`[name="${bracketName}"]`);
                }
            }
            if (!el) {
                // Fallback to base field name if available
                const baseKey = fieldName.split('.')[0];
                el = form.querySelector(`[name="${baseKey}"]`) || document.getElementById(fieldIdMap[baseKey] || ('lt_' + baseKey));
            }
            return el;
        }

        function getErrorColumn(el) {
            if (!el) {
                return null;
            }
            if (el.id === 'lt_sbu_box') {
                return el.closest('.col-md-6') || el.parentElement;
            }
            if (el.closest('td')) {
                return el.closest('td');
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
            if (el.closest('.custom-role-picker')) {
                const btn = el.closest('.custom-role-picker').querySelector('.role-dropdown-btn');
                if (btn) btn.classList.add('is-invalid', 'border-danger');
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
            if (el.closest('.custom-role-picker')) {
                const btn = el.closest('.custom-role-picker').querySelector('.role-dropdown-btn');
                if (btn) btn.classList.remove('is-invalid', 'border-danger');
            }
            const col = getErrorColumn(el);
            if (col) {
                col.querySelectorAll('.invalid-feedback.lt-field-error').forEach(function (node) {
                    node.remove();
                });
            }
        }

        function clearFieldErrors() {
            form.querySelectorAll('.is-invalid, .border-danger').forEach(function (el) {
                el.classList.remove('is-invalid', 'border-danger');
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

            // Only remove existing errors if not in a table cell (table cells can have multiple errors for grouped inputs)
            if (col.tagName !== 'TD') {
                col.querySelectorAll('.invalid-feedback.lt-field-error').forEach(function (node) {
                    node.remove();
                });
            }

            markInvalid(el);

            const feedback = document.createElement('div');
            feedback.className = 'invalid-feedback d-block lt-field-error';
            feedback.setAttribute('role', 'alert');
            // Make error text nicer for array fields
            let errorText = Array.isArray(message) ? message[0] : String(message);
            errorText = errorText.replace(/encashment_rules\.\d+\./g, '');
            errorText = errorText.replace(/_/g, ' ');
            errorText = errorText.charAt(0).toUpperCase() + errorText.slice(1);
            feedback.textContent = errorText;
            
            if (col.tagName === 'TD') {
                feedback.style.fontSize = '0.75rem';
                feedback.style.marginTop = '2px';
            }
            col.appendChild(feedback);
        }

        function showValidationErrors(errors) {
            clearFieldErrors();
            Object.keys(errors || {}).forEach(function (key) {
                showFieldError(key, errors[key]);
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
            if (cascadeApi && typeof cascadeApi.refreshHiddenInputs === 'function') {
                cascadeApi.refreshHiddenInputs();
            }

            // Normalize encashment rule fields BEFORE FormData snapshot
            const encashmentRuleRows = encashmentRulesTbody
                ? Array.from(encashmentRulesTbody.querySelectorAll('tr:not(#no_rules_row)'))
                : [];
            encashmentRuleRows.forEach((rowTr, index) => {
                if (typeof rowTr.syncEncashmentRuleRow === 'function') {
                    rowTr.syncEncashmentRuleRow(index);
                }

                ['service_years', 'service_months'].forEach(field => {
                    const input = rowTr.querySelector(`input[name*="[${field}]"]`);
                    if (input) {
                        if (input.value === '' || input.value === null || isNaN(input.value)) {
                            input.value = '0';
                        } else {
                            // Strip leading zeros by parsing as int
                            input.value = parseInt(input.value, 10).toString();
                        }
                    }
                });
                const maxDaysInput = rowTr.querySelector(`input[name*="[max_forward_days]"]`);
                if (maxDaysInput) {
                    if (maxDaysInput.value === '' || maxDaysInput.value === null || isNaN(maxDaysInput.value)) {
                        maxDaysInput.value = '0';
                    } else {
                        maxDaysInput.value = parseFloat(maxDaysInput.value).toString();
                    }
                }
            });

            const formData = new FormData(form);

            encashmentRuleRows.forEach((rowTr, index) => {
                Array.from(formData.keys()).forEach(function (key) {
                    if (key.indexOf('encashment_rules[' + index + '][role_level_ids]') === 0) {
                        formData.delete(key);
                    }
                });
                rowTr.querySelectorAll('.rule-hidden-ids input[type="hidden"]').forEach(function (input) {
                    if (input.value) {
                        formData.append('encashment_rules[' + index + '][role_level_ids][]', input.value);
                    }
                });
            });

            if (cascadeApi && typeof cascadeApi.getSelectedSbuIds === 'function') {
                const selectedSbuIds = cascadeApi.getSelectedSbuIds();
                if (selectedSbuIds.length) {
                    formData.delete('sbu_ids[]');
                    selectedSbuIds.forEach(function (sbuId) {
                        formData.append('sbu_ids[]', sbuId);
                    });
                }
            }
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

    function escapeHtml(text) {
        return String(text ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function renderEntitlementReferenceRows(rows) {
        const tbody = document.getElementById('lt_entitlement_reference_body');
        if (!tbody) {
            return;
        }

        if (!rows || !rows.length) {
            tbody.innerHTML =
                '<tr><td colspan="2" class="text-muted small text-center py-3">No other leave types found for this organization and SBU selection.</td></tr>';
            return;
        }

        tbody.innerHTML = rows
            .map(function (row) {
                const label = row.code
                    ? escapeHtml(row.name) + ' <span class="text-muted">(' + escapeHtml(row.code) + ')</span>'
                    : escapeHtml(row.name);
                return (
                    '<tr><td>' +
                    label +
                    '</td><td class="text-end fw-semibold">' +
                    escapeHtml(row.days) +
                    '</td></tr>'
                );
            })
            .join('');
    }

    function loadEntitlementReference(config, cascadeApi) {
        const tbody = document.getElementById('lt_entitlement_reference_body');
        const orgSelect = document.getElementById('lt_organization_id');
        if (!tbody || !orgSelect || !config.entitlementReferenceUrl) {
            return;
        }

        const organizationId = orgSelect.value;
        if (!organizationId) {
            tbody.innerHTML =
                '<tr><td colspan="2" class="text-muted small text-center py-3">Select an organization to see existing leave entitlements.</td></tr>';
            return;
        }

        const url = new URL(config.entitlementReferenceUrl, window.location.origin);
        url.searchParams.set('organization_id', organizationId);

        const sbuIds =
            cascadeApi && typeof cascadeApi.getSelectedSbuIds === 'function'
                ? cascadeApi.getSelectedSbuIds()
                : [];
        sbuIds.forEach(function (id) {
            url.searchParams.append('sbu_ids[]', id);
        });

        if (config.leaveTypeId) {
            url.searchParams.set('exclude_id', String(config.leaveTypeId));
        }

        tbody.innerHTML =
            '<tr><td colspan="2" class="text-muted small text-center py-3">Loading reference...</td></tr>';

        fetch(url.toString(), {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then(function (res) {
                return res.json();
            })
            .then(function (data) {
                if (!data || !data.success) {
                    renderEntitlementReferenceRows([]);
                    return;
                }
                renderEntitlementReferenceRows(data.rows || []);
            })
            .catch(function () {
                tbody.innerHTML =
                    '<tr><td colspan="2" class="text-muted small text-center py-3">Unable to load entitlement reference.</td></tr>';
            });
    }

    function initOrgSbuMultiPicker(config) {
        const orgSelect = document.getElementById('lt_organization_id');
        if (!orgSelect) {
            return {};
        }

        let sbuPool = [];
        let sbuSelected = [];

        function notifyEntitlementContextChange() {
            if (typeof config.onEntitlementContextChange === 'function') {
                config.onEntitlementContextChange();
            }
        }

        config.onEntitlementContextChange = function () {
            loadEntitlementReference(config, {
                getSelectedSbuIds: function () {
                    return sbuSelected.slice();
                },
            });
        };

        const sbuBox = document.getElementById('lt_sbu_box');
        const sbuDropdown = document.getElementById('lt_sbu_dropdown');
        const sbuChips = document.getElementById('lt_sbu_chips');
        const sbuPlaceholder = document.getElementById('lt_sbu_placeholder');
        const sbuList = document.getElementById('lt_sbu_list');
        const sbuSearch = document.getElementById('lt_sbu_search');
        const sbuHiddenWrap = document.getElementById('lt_sbu_hidden_inputs');
        const selectAllSbus = document.getElementById('lt_select_all_sbus');

        function jsonHeaders() {
            return {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            };
        }

        function resetSbus(message) {
            sbuPool = [];
            sbuSelected = [];
            if (selectAllSbus) {
                selectAllSbus.checked = false;
                selectAllSbus.disabled = true;
            }
            if (sbuPlaceholder) {
                sbuPlaceholder.textContent = message || 'Select Organization first';
                sbuPlaceholder.style.display = '';
            }
            if (sbuDropdown) {
                sbuDropdown.style.display = 'none';
            }
            if (sbuBox) {
                sbuBox.classList.remove('open');
            }
            if (sbuSearch) {
                sbuSearch.value = '';
            }
            if (sbuList) {
                sbuList.innerHTML = '';
            }
            syncSbuUi();
        }

        function renderSbuHiddenInputs() {
            if (!sbuHiddenWrap) {
                return;
            }
            sbuHiddenWrap.innerHTML = '';
            sbuSelected.forEach(function (id) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'sbu_ids[]';
                input.value = id;
                sbuHiddenWrap.appendChild(input);
            });
        }

        function renderSbuChips() {
            if (!sbuChips || !sbuPlaceholder) {
                return;
            }
            sbuChips.innerHTML = '';
            if (!sbuSelected.length) {
                sbuPlaceholder.style.display = '';
                return;
            }
            sbuPlaceholder.style.display = 'none';
            sbuSelected.forEach(function (id) {
                const row = sbuPool.find(function (s) {
                    return String(s.id) === String(id);
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
                    sbuSelected = sbuSelected.filter(function (v) {
                        return v !== id;
                    });
                    if (selectAllSbus) {
                        selectAllSbus.checked = false;
                    }
                    syncSbuUi();
                });
                chip.appendChild(removeBtn);
                sbuChips.appendChild(chip);
            });
        }

        function renderSbuList() {
            if (!sbuList) {
                return;
            }
            const q = (sbuSearch?.value || '').toLowerCase().trim();
            const rows = sbuPool.filter(function (s) {
                return !q || String(s.name || '').toLowerCase().includes(q);
            });
            if (!rows.length) {
                sbuList.innerHTML = '<div class="lt-dept-no-result">No SBUs found</div>';
                return;
            }
            sbuList.innerHTML = '';
            rows.forEach(function (s) {
                const id = String(s.id);
                const picked = sbuSelected.includes(id);
                const opt = document.createElement('div');
                opt.className = 'lt-dept-opt' + (picked ? ' picked' : '');
                opt.innerHTML =
                    '<span class="lt-dept-opt-cb">' +
                    '<svg class="lt-dept-opt-ck" viewBox="0 0 16 16" fill="none">' +
                    '<path d="M3.5 8.2l3 3L12.5 5" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' +
                    '</svg></span>' +
                    '<span class="lt-dept-opt-name"></span>';
                opt.querySelector('.lt-dept-opt-name').textContent = s.name;
                opt.addEventListener('click', function () {
                    if (picked) {
                        sbuSelected = sbuSelected.filter(function (v) {
                            return v !== id;
                        });
                    } else {
                        sbuSelected.push(id);
                    }
                    if (selectAllSbus) {
                        selectAllSbus.checked =
                            sbuPool.length > 0 && sbuSelected.length === sbuPool.length;
                    }
                    syncSbuUi();
                });
                sbuList.appendChild(opt);
            });
        }

        function syncSbuUi() {
            renderSbuHiddenInputs();
            renderSbuChips();
            renderSbuList();
            notifyEntitlementContextChange();
        }

        function loadSbus(organizationId, selectedIds, onLoaded) {
            selectedIds = (selectedIds || []).map(String);
            if (!organizationId) {
                resetSbus('Select Organization first');
                notifyEntitlementContextChange();
                if (typeof onLoaded === 'function') {
                    onLoaded();
                }
                return;
            }

            if (sbuPlaceholder) {
                sbuPlaceholder.textContent = 'Loading SBUs...';
                sbuPlaceholder.style.display = '';
            }
            if (sbuList) {
                sbuList.innerHTML = '';
            }

            const url = new URL(config.sbuUrl, window.location.origin);
            url.searchParams.set('organization_id', organizationId);

            fetch(url.toString(), { headers: jsonHeaders() })
                .then(function (res) {
                    return res.json();
                })
                .then(function (data) {
                    const sbus = Array.isArray(data.sbus) ? data.sbus : [];
                    sbuPool = sbus.map(function (sbu) {
                        return { id: String(sbu.id), name: sbu.name };
                    });
                    sbuSelected = selectedIds.filter(function (id) {
                        return sbuPool.some(function (s) {
                            return s.id === id;
                        });
                    });
                    if (selectAllSbus) {
                        selectAllSbus.disabled = sbuPool.length === 0;
                        selectAllSbus.checked =
                            sbuPool.length > 0 && sbuSelected.length === sbuPool.length;
                    }
                    if (sbuPlaceholder) {
                        sbuPlaceholder.textContent = sbuPool.length
                            ? 'Select SBUs...'
                            : 'No SBUs found for this organization';
                    }
                    syncSbuUi();
                    if (typeof onLoaded === 'function') {
                        onLoaded();
                    }
                })
                .catch(function () {
                    resetSbus('Error loading SBUs');
                    notifyEntitlementContextChange();
                    if (typeof onLoaded === 'function') {
                        onLoaded();
                    }
                });
        }

        orgSelect.addEventListener('change', function () {
            loadSbus(orgSelect.value, []);
            notifyEntitlementContextChange();
        });

        if (selectAllSbus) {
            selectAllSbus.addEventListener('change', function () {
                if (selectAllSbus.checked) {
                    sbuSelected = sbuPool.map(function (s) {
                        return s.id;
                    });
                } else {
                    sbuSelected = [];
                }
                syncSbuUi();
            });
        }

        if (sbuBox && sbuDropdown) {
            sbuBox.addEventListener('click', function (e) {
                if (!sbuPool.length) {
                    return;
                }
                e.stopPropagation();
                const isOpen = sbuDropdown.style.display !== 'none';
                if (isOpen) {
                    sbuDropdown.style.display = 'none';
                    sbuBox.classList.remove('open');
                } else {
                    sbuDropdown.style.display = 'block';
                    sbuBox.classList.add('open');
                    renderSbuList();
                    if (sbuSearch) {
                        sbuSearch.focus();
                    }
                }
            });

            sbuBox.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    sbuBox.click();
                }
            });
        }

        if (sbuSearch) {
            sbuSearch.addEventListener('input', renderSbuList);
            sbuSearch.addEventListener('click', function (e) {
                e.stopPropagation();
            });
        }

        document.addEventListener('click', function (e) {
            if (!sbuBox || !sbuDropdown) {
                return;
            }
            if (!sbuDropdown.contains(e.target) && !sbuBox.contains(e.target)) {
                sbuDropdown.style.display = 'none';
                sbuBox.classList.remove('open');
            }
        });

        resetSbus('Select Organization first');

        return {
            loadSbus: loadSbus,
            getSelectedSbuIds: function () {
                return sbuSelected.slice();
            },
            refreshHiddenInputs: function () {
                renderSbuHiddenInputs();
            },
        };
    }
})();
