{{-- STEP 2: Employment Information --}}
<div class="step" id="step-2">
    <div class="section-title">Section — Employment Information</div>
    <div class="row g-3">

        <div class="col-md-4">
            <label class="form-label">TAS ID</label>
            <input type="text" name="biometric_id" class="form-control" placeholder="Biometric ID">
        </div>

        <div class="col-12">
            <div class="p-3">
                <label class="form-label fw-semibold mb-2">Category <span class="text-danger">*</span></label>
                <div id="categoryRadioGroup" class="d-flex flex-wrap gap-4 mb-3">
                    <div class="form-check d-flex align-items-center gap-1">
                        <input class="check-input" type="radio" name="employment_category" id="catIntern" value="intern">
                        <label class="form-check-label" for="catIntern">Intern</label>
                    </div>
                    <div class="form-check d-flex align-items-center gap-1">
                        <input class="check-input" type="radio" name="employment_category" id="catContractual" value="contractual">
                        <label class="form-check-label" for="catContractual">Contractual</label>
                    </div>
                    <div class="form-check d-flex align-items-center gap-1">
                        <input class="check-input" type="radio" name="employment_category" id="catEngagement" value="engagement">
                        <label class="form-check-label" for="catEngagement">Engagement</label>
                    </div>
                </div>

                <div class="row g-3" id="internFields" style="display:none;">
                    <div class="col-md-4">
                        <label class="form-label">Intern Type</label>
                        <select name="intern_type" class="form-select">
                            <option value="">Select</option>
                            <option value="paid">Paid</option>
                            <option value="unpaid">Unpaid</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Intern Duration</label>
                        <input type="text" name="intern_duration" class="form-control" placeholder="e.g. 3 Months">
                    </div>
                </div>

                <div class="row g-3" id="contractualFields" style="display:none;">
                    <div class="col-md-4">
                        <label class="form-label">Contract Type</label>
                        <select name="contractual_type" class="form-select">
                            <option value="">Select</option>
                            <option value="time_bound">Time Bound</option>
                            <option value="open">Open</option>
                            <option value="project_based">Project-Based Consultants</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3" id="engagementFields" style="display:none;">
                    <div class="col-md-4">
                        <label class="form-label">Engagement Mode</label>
                        <select name="engagement_mode" id="engagementMode" class="form-select">
                            <option value="">Select</option>
                            <option value="on_site">On-site</option>
                            <option value="remote">Remote</option>
                            <option value="shifts">Shifts</option>
                            <option value="hybrid">Hybrid</option>
                        </select>
                    </div>
                    <div class="col-md-8" id="hybridDaysWrapper" style="display:none;">
                        <label class="form-label">Hybrid Days</label>
                        <div class="d-flex flex-wrap gap-3">
                            @foreach (['mon' => 'M', 'tue' => 'T', 'wed' => 'W', 'thu' => 'T', 'fri' => 'F', 'sat' => 'S', 'sun' => 'S'] as $dayKey => $dayLabel)
                                <div class="form-check d-flex align-items-center gap-1">
                                    <input class="form-check-input" type="checkbox" name="hybrid_days[]" id="hybrid_{{ $dayKey }}" value="{{ $dayKey }}">
                                    <label class="form-check-label" for="hybrid_{{ $dayKey }}">{{ $dayLabel }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <label class="form-label">Organization <span class="text-danger">*</span></label>
            <select name="organization_id" id="org_select" class="form-select"
                style="background-color:transparent !important; border:1px solid #012445; box-shadow:0 0 4px 2px #5a59593d; appearance:none; -webkit-appearance:none;"
                onchange="onOrgChange(this.value)">
                <option value="">— Select Organization —</option>
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label">SBU <span class="text-danger">*</span></label>
            <select name="sbu_id" id="sbu_select" class="form-select"
                style="background-color:transparent !important; border:1px solid #012445; box-shadow:0 0 4px 2px #5a59593d; appearance:none; -webkit-appearance:none;"
                onchange="onSbuChange(this.value)">
                <option value="">— Select SBU —</option>
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label">Department <span class="text-danger">*</span></label>
            <select name="department_id" id="dept_select" class="form-select"
                style="background-color:transparent !important; border:1px solid #012445; box-shadow:0 0 4px 2px #5a59593d; appearance:none; -webkit-appearance:none;"
                onchange="onDeptChange(this.value)">
                <option value="">— Select Department —</option>
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label">Role <span class="text-danger">*</span></label>
            <select name="role_id" id="role_select" class="form-select"
                style="background-color:transparent !important; border:1px solid #012445; box-shadow:0 0 4px 2px #5a59593d; appearance:none; -webkit-appearance:none;">
                <option value="">— Select Role —</option>
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label">Date of Joining <span class="text-danger">*</span></label>
            <input type="date" name="join_date" class="form-control">
        </div>

        <div class="col-md-4">
            <label class="form-label">Designation</label>
            <input type="text" name="designation" class="form-control">
        </div>

        <div class="col-md-4">
            <label class="form-label">Grade</label>
            <input type="text" name="grade" class="form-control">
        </div>

        <div class="col-md-4">
            <label class="form-label">Branch</label>
            <input type="text" name="branch" class="form-control">
        </div>

        <div class="col-md-4">
            <label class="form-label">Location</label>
            <input type="text" name="location" class="form-control">
        </div>

    </div>
</div>

<script>
    window._orgsData  = @json($orgsData  ?? []);
    window._rolesData = @json($rolesData ?? []);

    document.addEventListener('DOMContentLoaded', function () {
        const orgSel = document.getElementById('org_select');
        window._orgsData.forEach(function (o) {
            orgSel.insertAdjacentHTML('beforeend',
                '<option value="' + o.id + '">' + escHtmlBasic(o.name) + '</option>');
        });
        populateRoles();
    });

    function escHtmlBasic(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function onOrgChange(orgId) {
        const sbuSel  = document.getElementById('sbu_select');
        const deptSel = document.getElementById('dept_select');
        sbuSel.innerHTML  = '<option value="">— Select SBU —</option>';
        deptSel.innerHTML = '<option value="">— Select Department —</option>';

        if (!orgId) return;
        const org = (window._orgsData || []).find(o => String(o.id) === String(orgId));
        if (!org) return;
        (org.sbus || []).forEach(function (s) {
            sbuSel.insertAdjacentHTML('beforeend',
                '<option value="' + s.id + '">' + escHtmlBasic(s.name) + '</option>');
        });
        populateRoles();
    }

    function onSbuChange(sbuId) {
        const orgId   = document.getElementById('org_select').value;
        const deptSel = document.getElementById('dept_select');
        deptSel.innerHTML = '<option value="">— Select Department —</option>';

        if (!sbuId || !orgId) return;
        const org = (window._orgsData || []).find(o => String(o.id) === String(orgId));
        if (!org) return;
        const sbu = (org.sbus || []).find(s => String(s.id) === String(sbuId));
        if (!sbu) return;
        (sbu.departments || []).forEach(function (d) {
            deptSel.insertAdjacentHTML('beforeend',
                '<option value="' + d.id + '">' + escHtmlBasic(d.name) + '</option>');
        });
        populateRoles();
    }

    function onDeptChange() {
        populateRoles();
    }

    function populateRoles() {
        const roleSel = document.getElementById('role_select');
        if (!roleSel) return;

        const selectedRole = roleSel.value;
        const orgId  = document.getElementById('org_select')?.value  || '';
        const deptId = document.getElementById('dept_select')?.value || '';

        roleSel.innerHTML = '<option value="">— Select Role —</option>';

        (window._rolesData || []).forEach(function (role) {
            const roleOrg  = role.organization_id ? String(role.organization_id) : '';
            const roleDept = role.department_id   ? String(role.department_id)   : '';
            const orgMatch  = !roleOrg  || !orgId  || roleOrg  === String(orgId);
            const deptMatch = !roleDept || !deptId || roleDept === String(deptId);
            if (!orgMatch || !deptMatch) return;
            roleSel.insertAdjacentHTML('beforeend',
                '<option value="' + role.id + '">' + escHtmlBasic(role.name) + '</option>');
        });

        if (selectedRole && roleSel.querySelector('option[value="' + selectedRole + '"]')) {
            roleSel.value = selectedRole;
        }
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
</script>
