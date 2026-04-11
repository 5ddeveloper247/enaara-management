@push('scripts')
<script>
    window._orgsData  = @json($orgsData  ?? []);
    window._rolesData = @json($rolesData ?? []);
    window.__employeeEditMode = @json(isset($employee));
    window.__previewEmployeeCodeUrl = @json(route('admin.employee.preview_code'));

    // Helpers
    function escHtmlBasic(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function roleById(id) {
        return (window._rolesData || []).find(function (r) { return r && String(r.id) === String(id); });
    }

    function isOrgLevelRole(role) {
        if (!role) return false;
        if (typeof role.is_organization_level === 'boolean') return role.is_organization_level;
        return role.department_id === null || role.department_id === undefined || role.department_id === '';
    }

    // Expose to global scope for inline onchange
    window.onOrgChange = function(orgId) {
        const sbuSel  = document.getElementById('sbu_select');
        const deptSel = document.getElementById('dept_select');
        const roleSel = document.getElementById('role_select');
        const section = document.getElementById('sbuDeptSection');

        if (sbuSel) {
            sbuSel.innerHTML  = '<option value="">— Select SBU —</option>';
            sbuSel.setAttribute('title', 'Please select organization first');
        }
        if (deptSel) {
            deptSel.innerHTML = '<option value="">— Select Department —</option>';
            deptSel.setAttribute('title', 'Please select SBU first');
        }
        if (section) section.style.display = 'none';

        if (roleSel) {
            roleSel.disabled = !orgId;
            roleSel.innerHTML = orgId
                ? '<option value="">— Select Role —</option>'
                : '<option value="">— Select Organization first —</option>';
            if (orgId) {
                const addedNames = new Set();
                (window._rolesData || []).forEach(function (role) {
                    const ro = role.organization_id != null && role.organization_id !== '' ? String(role.organization_id) : '';
                    if (ro && String(ro) !== String(orgId)) return;
                    if (orgId && !ro) return;
                    if (addedNames.has(role.name)) return;
                    addedNames.add(role.name);
                    roleSel.insertAdjacentHTML('beforeend', '<option value="' + role.id + '">' + escHtmlBasic(role.name) + '</option>');
                });
            }
        }
        window.refreshEmployeeNumberPreview();
    };

    window.onSbuChange = function(sbuId) {
        const orgEl = document.getElementById('org_select');
        const orgId = orgEl ? orgEl.value : null;
        const deptSel = document.getElementById('dept_select');
        if (!deptSel) return;
        deptSel.innerHTML = '<option value="">— Select Department —</option>';
        if (!sbuId || !orgId) return;

        const org = (window._orgsData || []).find(function (o) { return String(o.id) === String(orgId); });
        if (!org || !org.sbus) return;
        const sbu = (org.sbus || []).find(function (s) { return String(s.id) === String(sbuId); });
        if (sbu && sbu.departments) {
            sbu.departments.forEach(function (d) {
                deptSel.insertAdjacentHTML('beforeend', '<option value="' + d.id + '">' + escHtmlBasic(d.name) + '</option>');
            });
        }
        window.refreshEmployeeNumberPreview();
    };

    window.onRoleChange = function() {
        const roleEl = document.getElementById('role_select');
        const roleId = roleEl ? roleEl.value : null;
        const section = document.getElementById('sbuDeptSection');
        const sbuSel  = document.getElementById('sbu_select');
        const deptSel = document.getElementById('dept_select');

        if (!roleId) {
            if (section) section.style.display = 'none';
            if (sbuSel) sbuSel.value = '';
            if (deptSel) deptSel.value = '';
            window.refreshEmployeeNumberPreview();
            return;
        }

        const role = roleById(roleId);
        if (!role) return;

        if (isOrgLevelRole(role)) {
            if (section) section.style.display = 'none';
            if (sbuSel) sbuSel.value = '';
            if (deptSel) deptSel.value = '';
        } else {
            if (section) section.style.display = '';
            // Populate SBUs for current Org
            const orgEl = document.getElementById('org_select');
            const orgId = orgEl ? orgEl.value : null;
            if (sbuSel && orgId) {
                sbuSel.innerHTML = '<option value="">— Select SBU —</option>';
                const org = (window._orgsData || []).find(function (o) { return String(o.id) === String(orgId); });
                if (org && org.sbus) {
                    org.sbus.forEach(function (s) {
                        sbuSel.insertAdjacentHTML('beforeend', '<option value="' + s.id + '">' + escHtmlBasic(s.name) + '</option>');
                    });
                }
                if (role.sbu_id) {
                    sbuSel.value = String(role.sbu_id);
                    window.onSbuChange(sbuSel.value);
                    if (role.department_id && deptSel) {
                        deptSel.value = String(role.department_id);
                    }
                }
            }
        }
        window.refreshEmployeeNumberPreview();
    };

    window.refreshEmployeeNumberPreview = function() {
        const el = document.getElementById('employee_number_display');
        if (!el || window.__employeeEditMode || !window.__previewEmployeeCodeUrl) return;

        const orgEl = document.getElementById('org_select');
        const orgId = orgEl ? orgEl.value : null;
        const roleEl = document.getElementById('role_select');
        const roleId = roleEl ? roleEl.value : null;
        const sbuEl = document.getElementById('sbu_select');
        const sbuId = sbuEl ? sbuEl.value : null;

        if (!orgId || !roleId) {
            el.value = ''; el.placeholder = '— Select Organization & Role —';
            return;
        }

        const role = roleById(roleId);
        if (role && !isOrgLevelRole(role) && !sbuId) {
            el.value = ''; el.placeholder = '— Select SBU —';
            return;
        }

        const params = new URLSearchParams({ organization_id: orgId, role_id: roleId });
        if (sbuId) params.set('sbu_id', sbuId);

        fetch(window.__previewEmployeeCodeUrl + '?' + params.toString(), { headers: { Accept: 'application/json' } })
            .then(r => r.json())
            .then(data => {
                if (data.success && data.code) {
                    el.value = data.code; el.placeholder = '';
                } else {
                    el.value = ''; el.placeholder = (data.message || '—').substring(0, 80);
                }
            }).catch(() => { el.value = ''; el.placeholder = '—'; });
    };

    function toggleCategoryBlocks() {
        const catEl = document.querySelector('input[name="employment_category"]:checked');
        const selectedCategory = catEl ? catEl.value : '';
        const modeEl = document.getElementById('engagementMode');
        const engagementMode = modeEl ? modeEl.value : '';

        const internFields      = document.getElementById('internFields');
        const contractualFields = document.getElementById('contractualFields');
        const engagementFields  = document.getElementById('engagementFields');
        const hybridDaysWrapper = document.getElementById('hybridDaysWrapper');

        if (internFields)      internFields.style.display      = selectedCategory === 'intern'       ? '' : 'none';
        if (contractualFields) contractualFields.style.display = selectedCategory === 'contractual'  ? '' : 'none';
        if (engagementFields)  engagementFields.style.display  = selectedCategory === 'engagement'   ? '' : 'none';
        if (hybridDaysWrapper) hybridDaysWrapper.style.display = (selectedCategory === 'engagement' && engagementMode === 'hybrid') ? '' : 'none';
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Populate Org Select
        const orgSel = document.getElementById('org_select');
        if (orgSel && window._orgsData) {
            window._orgsData.forEach(function (o) {
                orgSel.insertAdjacentHTML('beforeend', '<option value="' + o.id + '">' + escHtmlBasic(o.name) + '</option>');
            });
            if (orgSel.dataset.prefill) {
                orgSel.value = orgSel.dataset.prefill;
                window.onOrgChange(orgSel.value);
            }
        }

        // Event Listeners
        const roleSel = document.getElementById('role_select');
        if (roleSel) {
            roleSel.addEventListener('change', window.onRoleChange);
            if (roleSel.dataset.prefill) {
                roleSel.value = roleSel.dataset.prefill;
                window.onRoleChange();
            }
        }

        document.querySelectorAll('input[name="employment_category"]').forEach(el => el.addEventListener('change', toggleCategoryBlocks));
        const engagementModeEl = document.getElementById('engagementMode');
        if (engagementModeEl) engagementModeEl.addEventListener('change', toggleCategoryBlocks);
        
        toggleCategoryBlocks();
    });

    window.syncEmploymentRoleUI = window.onRoleChange;
</script>
@endpush
