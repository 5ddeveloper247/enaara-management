{{-- STEP 2: Employment Information --}}
<div class="step" id="step-2">
    <div class="section-title d-flex align-items-center justify-content-between">
        <span>Section B — Employment Information</span>
        <small class="text-muted">Job mapping, category and placement details</small>
    </div>
    <div class="row g-2">

        <div class="col-md-3">
            <label class="form-label">TAS ID</label>
            <input type="text" name="biometric_id" class="form-control" placeholder="Biometric ID">
        </div>

        <div class="col-md-3">
            <label class="form-label">Employee Number</label>
            <input type="text" id="employee_number_display" class="form-control" disabled readonly
                placeholder="— Select Organization &amp; Role —"
                style="opacity:1;background:rgba(255,255,255,.06)!important;cursor:not-allowed;">
        </div>

        <div class="col-12">
            <div class="p-3 rounded-3 border bg-light bg-opacity-25">
                <label class="form-label fw-semibold mb-2 d-block">Category <span class="text-danger">*</span></label>
                <div id="categoryRadioGroup" class="d-flex flex-wrap gap-2 mb-2">
                    <div class="form-check d-flex align-items-center gap-2 px-3 py-2 rounded-pill border bg-white">
                        <input class="check-input" type="radio" name="employment_category" id="catIntern" value="intern">
                        <label class="form-check-label" for="catIntern">Intern</label>
                    </div>
                    <div class="form-check d-flex align-items-center gap-2 px-3 py-2 rounded-pill border bg-white">
                        <input class="check-input" type="radio" name="employment_category" id="catContractual" value="contractual">
                        <label class="form-check-label" for="catContractual">Contractual</label>
                    </div>
                    <div class="form-check d-flex align-items-center gap-2 px-3 py-2 rounded-pill border bg-white">
                        <input class="check-input" type="radio" name="employment_category" id="catEngagement" value="engagement">
                        <label class="form-check-label" for="catEngagement">Engagement</label>
                    </div>
                </div>

                <div class="row g-2" id="internFields" style="display:none;">
                    <div class="col-md-3">
                        <label class="form-label">Intern Type</label>
                        <select name="intern_type" class="form-select">
                            <option value="">Select</option>
                            <option value="paid">Paid</option>
                            <option value="unpaid">Unpaid</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Intern Duration</label>
                        <input type="text" name="intern_duration" class="form-control" placeholder="e.g. 3 Months">
                    </div>
                </div>

                <div class="row g-2" id="contractualFields" style="display:none;">
                    <div class="col-md-3">
                        <label class="form-label">Contract Type</label>
                        <select name="contractual_type" class="form-select">
                            <option value="">Select</option>
                            <option value="time_bound">Time Bound</option>
                            <option value="open">Open</option>
                            <option value="project_based">Project-Based Consultants</option>
                        </select>
                    </div>
                </div>

                <div class="row g-2" id="engagementFields" style="display:none;">
                    <div class="col-md-3">
                        <label class="form-label">Engagement Mode</label>
                        <select name="engagement_mode" id="engagementMode" class="form-select">
                            <option value="">Select</option>
                            <option value="on_site">On-site</option>
                            <option value="remote">Remote</option>
                            <option value="shifts">Shifts</option>
                            <option value="hybrid">Hybrid</option>
                        </select>
                    </div>
                    <div class="col-md-9" id="hybridDaysWrapper" style="display:none;">
                        <label class="form-label">Hybrid Days</label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach (['mon' => 'M', 'tue' => 'T', 'wed' => 'W', 'thu' => 'T', 'fri' => 'F', 'sat' => 'S', 'sun' => 'S'] as $dayKey => $dayLabel)
                                <div class="form-check d-flex align-items-center gap-1 px-2 py-1 rounded-pill border bg-white">
                                    <input class="form-check-input" type="checkbox" name="hybrid_days[]" id="hybrid_{{ $dayKey }}" value="{{ $dayKey }}">
                                    <label class="form-check-label" for="hybrid_{{ $dayKey }}">{{ $dayLabel }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <label class="form-label">Organization <span class="text-danger">*</span></label>
            <select name="organization_id" id="org_select" class="form-select"
                style="background-color:transparent !important; border:1px solid #012445; box-shadow:0 0 4px 2px #5a59593d; appearance:none; -webkit-appearance:none;"
                onchange="onOrgChange(this.value)">
                <option value="">— Select Organization —</option>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Role <span class="text-danger">*</span></label>
            <select name="role_id" id="role_select" class="form-select" disabled
                style="background-color:transparent !important; border:1px solid #012445; box-shadow:0 0 4px 2px #5a59593d; appearance:none; -webkit-appearance:none;">
                <option value="">— Select Organization first —</option>
            </select>
        </div>

        <div class="col-12 mt-3" id="sbuDeptSection" style="display:none;">
            <div class="row g-2 p-2 rounded-3 border bg-light bg-opacity-25">
                <div class="col-12">
                    <div class="small fw-semibold text-uppercase text-muted">Role Placement</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">SBU <span class="text-danger sbu-dept-req">*</span></label>
                    <select name="sbu_id" id="sbu_select" class="form-select"
                        style="background-color:transparent !important; border:1px solid #012445; box-shadow:0 0 4px 2px #5a59593d; appearance:none; -webkit-appearance:none;"
                        onchange="onSbuChange(this.value)">
                        <option value="">— Select SBU —</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Department <span class="text-danger sbu-dept-req">*</span></label>
                    <select name="department_id" id="dept_select" class="form-select"
                        style="background-color:transparent !important; border:1px solid #012445; box-shadow:0 0 4px 2px #5a59593d; appearance:none; -webkit-appearance:none;">
                        <option value="">— Select Department —</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <label class="form-label">Date of Joining <span class="text-danger">*</span></label>
            <input type="date" name="join_date" class="form-control">
        </div>

        <div class="col-md-3">
            <label class="form-label">Designation</label>
            <input type="text" name="designation" class="form-control" placeholder="e.g. Software Engineer">
        </div>

        <div class="col-md-3">
            <label class="form-label">Grade</label>
            <input type="text" name="grade" class="form-control" placeholder="e.g. G1">
        </div>

        <div class="col-md-3">
            <label class="form-label">Branch</label>
            <input type="text" name="branch" class="form-control" placeholder="e.g. Islamabad">
        </div>

        <div class="col-md-3">
            <label class="form-label">Location</label>
            <input type="text" name="location" class="form-control" placeholder="e.g. Ground Floor">
        </div>

    </div>
</div>

<script>
    window._orgsData  = @json($orgsData  ?? []);
    window._rolesData = @json($rolesData ?? []);
    window.__employeeEditMode = @json(isset($employee));
    window.__previewEmployeeCodeUrl = @json(route('admin.employee.preview_code'));

    document.addEventListener('DOMContentLoaded', function () {
        const orgSel = document.getElementById('org_select');
        window._orgsData.forEach(function (o) {
            orgSel.insertAdjacentHTML('beforeend',
                '<option value="' + o.id + '">' + escHtmlBasic(o.name) + '</option>');
        });
        const roleSel = document.getElementById('role_select');
        if (roleSel) {
            roleSel.addEventListener('change', onRoleChange);
        }
    });

    function escHtmlBasic(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function roleById(id) {
        return (window._rolesData || []).find(function (r) { return String(r.id) === String(id); });
    }

    function isOrgLevelRole(role) {
        if (!role) return false;
        if (typeof role.is_organization_level === 'boolean') return role.is_organization_level;
        return role.department_id === null || role.department_id === undefined || role.department_id === '';
    }

    function refreshEmployeeNumberPreview() {
        const el = document.getElementById('employee_number_display');
        if (!el || window.__employeeEditMode || !window.__previewEmployeeCodeUrl) return;

        const orgId = document.getElementById('org_select') ? document.getElementById('org_select').value : '';
        const roleId = document.getElementById('role_select') ? document.getElementById('role_select').value : '';
        const sbuSel = document.getElementById('sbu_select');
        const sbuId = sbuSel ? sbuSel.value : '';

        if (!orgId || !roleId) {
            el.value = '';
            el.placeholder = '— Select Organization & Role —';
            return;
        }

        const role = roleById(roleId);
        const needSbu = role && !isOrgLevelRole(role);
        if (needSbu && !sbuId) {
            el.value = '';
            el.placeholder = '— Select SBU —';
            return;
        }

        const params = new URLSearchParams({ organization_id: orgId, role_id: roleId });
        if (sbuId) params.set('sbu_id', sbuId);

        fetch(window.__previewEmployeeCodeUrl + '?' + params.toString(), {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success && data.code) {
                    el.value = data.code;
                    el.placeholder = '';
                } else {
                    el.value = '';
                    el.placeholder = (data.message || '—').substring(0, 80);
                }
            })
            .catch(function () {
                el.value = '';
                el.placeholder = '—';
            });
    }

    function onOrgChange(orgId) {
        const sbuSel  = document.getElementById('sbu_select');
        const deptSel = document.getElementById('dept_select');
        const roleSel = document.getElementById('role_select');
        const section   = document.getElementById('sbuDeptSection');

        if (sbuSel) {
            sbuSel.innerHTML  = '<option value="">— Select SBU —</option>';
            sbuSel.setAttribute('title', 'Please select organization first');
        }
        if (deptSel) {
            deptSel.innerHTML = '<option value="">— Select Department —</option>';
            deptSel.setAttribute('title', 'Please select SBU first');
        }
        if (section) {
            section.style.display = 'none';
        }

        if (roleSel) {
            roleSel.disabled = !orgId;
            if (roleSel.disabled) {
                roleSel.setAttribute('title', 'Please select organization first');
            } else {
                roleSel.removeAttribute('title');
            }
            roleSel.innerHTML = orgId
                ? '<option value="">— Select Role —</option>'
                : '<option value="">— Select Organization first —</option>';
            if (orgId) {
                populateRolesForOrg(orgId);
            }
        }
        refreshEmployeeNumberPreview();
    }

    function populateRolesForOrg(orgId) {
        const roleSel = document.getElementById('role_select');
        if (!roleSel || !orgId) return;

        const addedNames = new Set();
        (window._rolesData || []).forEach(function (role) {
            const ro = role.organization_id != null && role.organization_id !== ''
                ? String(role.organization_id)
                : '';
            if (ro && String(ro) !== String(orgId)) return;
            if (orgId && !ro) return;

            if (addedNames.has(role.name)) return;
            addedNames.add(role.name);

            roleSel.insertAdjacentHTML('beforeend',
                '<option value="' + role.id + '">' + escHtmlBasic(role.name) + '</option>');
        });
    }

    function onSbuChange(sbuId) {
        const orgId   = document.getElementById('org_select').value;
        const deptSel = document.getElementById('dept_select');
        if (!deptSel) return;
        deptSel.innerHTML = '<option value="">— Select Department —</option>';

        if (!sbuId || !orgId) return;
        const org = (window._orgsData || []).find(function (o) { return String(o.id) === String(orgId); });
        if (!org) return;
        const sbu = (org.sbus || []).find(function (s) { return String(s.id) === String(sbuId); });
        if (!sbu) return;
        if (sbu) {
            deptSel.removeAttribute('title');
        }
        (sbu.departments || []).forEach(function (d) {
            deptSel.insertAdjacentHTML('beforeend',
                '<option value="' + d.id + '">' + escHtmlBasic(d.name) + '</option>');
        });
        refreshEmployeeNumberPreview();
    }

    function applySbuDeptFromRole(role) {
        const orgId   = document.getElementById('org_select').value;
        const sbuSel  = document.getElementById('sbu_select');
        const deptSel = document.getElementById('dept_select');
        if (!sbuSel || !deptSel || !orgId) return;

        sbuSel.innerHTML  = '<option value="">— Select SBU —</option>';
        deptSel.innerHTML = '<option value="">— Select Department —</option>';

        const org = (window._orgsData || []).find(function (o) { return String(o.id) === String(orgId); });
        if (!org) return;
        (org.sbus || []).forEach(function (s) {
            sbuSel.insertAdjacentHTML('beforeend',
                '<option value="' + s.id + '">' + escHtmlBasic(s.name) + '</option>');
        });

        if (role.sbu_id) {
            sbuSel.value = String(role.sbu_id);
            sbuSel.removeAttribute('title');
            onSbuChange(String(role.sbu_id));
            if (role.department_id) {
                deptSel.value = String(role.department_id);
                deptSel.removeAttribute('title');
            }
        }
    }

    function onRoleChange() {
        const roleId = document.getElementById('role_select').value;
        const section = document.getElementById('sbuDeptSection');
        const sbuSel  = document.getElementById('sbu_select');
        const deptSel = document.getElementById('dept_select');

        if (!roleId) {
            if (section) section.style.display = 'none';
            if (sbuSel) sbuSel.value = '';
            if (deptSel) deptSel.value = '';
            refreshEmployeeNumberPreview();
            return;
        }

        const role = roleById(roleId);
        if (!role) return;

        if (isOrgLevelRole(role)) {
            if (section) section.style.display = 'none';
            if (sbuSel) {
                sbuSel.innerHTML = '<option value="">— Select SBU —</option>';
                sbuSel.value = '';
            }
            if (deptSel) {
                deptSel.innerHTML = '<option value="">— Select Department —</option>';
                deptSel.value = '';
            }
            refreshEmployeeNumberPreview();
            return;
        }

        if (section) section.style.display = '';
        applySbuDeptFromRole(role);
        refreshEmployeeNumberPreview();
    }

    function toggleCategoryBlocks() {
        const selectedCategory = document.querySelector('input[name="employment_category"]:checked')?.value || '';
        const engagementMode   = document.getElementById('engagementMode')?.value || '';

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
        document.querySelectorAll('input[name="employment_category"]').forEach(function (el) {
            el.addEventListener('change', toggleCategoryBlocks);
        });
        const engagementModeEl = document.getElementById('engagementMode');
        if (engagementModeEl) engagementModeEl.addEventListener('change', toggleCategoryBlocks);
        toggleCategoryBlocks();
    });

    window.syncEmploymentRoleUI = onRoleChange;
    window.refreshEmployeeNumberPreview = refreshEmployeeNumberPreview;
</script>
