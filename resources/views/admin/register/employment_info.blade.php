{{-- STEP 2: Employment Information --}}
<div class="step" id="step-2">
    <div class="section-title">Section — Employment Information</div>
    <div class="row g-3">

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
</script>
