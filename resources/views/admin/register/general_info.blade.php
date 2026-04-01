{{-- STEP 1: General Information --}}
<div class="step active" id="step-1">
    <div class="section-title">Section A — General Information</div>
    <div class="row g-3">

        <div class="col-md-4">
            <label class="form-label">TAS ID</label>
            <input type="text" name="biometric_id" class="form-control" placeholder="Biometric ID">
        </div>

        <div class="col-12">
            <div class="p-3 border rounded-3" style="border-color:#198754 !important; background:rgba(25,135,84,.05);">
                <label class="form-label fw-semibold mb-2">Category</label>

                <div class="d-flex flex-wrap gap-4 mb-3">
                    <div class="form-check d-flex align-items-center gap-1">
                        <input class="check-input form-check-input category-radio" type="radio" name="employment_category" id="catIntern" value="intern">
                        <label class="form-check-label" for="catIntern">Intern</label>
                    </div>
                    <div class="form-check d-flex align-items-center gap-1">
                        <input class="check-input form-check-input category-radio" type="radio" name="employment_category" id="catContractual" value="contractual">
                        <label class="form-check-label" for="catContractual">Contractual</label>
                    </div>
                    <div class="form-check d-flex align-items-center gap-1">
                        <input class="check-input form-check-input category-radio" type="radio" name="employment_category" id="catEngagement" value="engagement">
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

        {{-- Personal --}}
        <div class="d-flex align-items-end gap-2">
            <div class="col-2">
                <label class="form-label">Upload Image</label>
                <label for="uploadImage" class="d-flex flex-column align-items-center justify-content-center gap-2"
                    style="width:160px; height:160px; border: 2px dashed var(--main-color); border-radius: 10px; cursor:pointer; background:#f8f9fa1a;">
                    <i class="bi bi-cloud-arrow-up fs-1 text-secondary"></i>
                    <span style="font-size:.72rem; color:#6c757d;">Click to upload</span>
                    <input type="file" id="uploadImage" name="profile_photo" accept="image/*" class="d-none" onchange="previewImg(this)">
                </label>
                <img id="imgPreview" src="" alt=""
                    style="display:none; width:160px; height:160px; object-fit:cover; border-radius:10px; margin-top:6px; border:2px solid var(--main-color);">
            </div>

            <div class="col-md-5">
                <label class="form-label">Name</label>
                <input type="text" name="full_name" class="form-control">
            </div>

            <div class="col-md-5">
                <label class="form-label">Father Name</label>
                <input type="text" name="father_name" class="form-control">
            </div>
        </div>

        <div class="col-md-4">
            <label class="form-label">CNIC</label>
            <input type="text" name="cnic" class="form-control" placeholder="00000-0000000-0">
        </div>

        <div class="col-md-4">
            <label class="form-label">CNIC Expiry Date</label>
            <input type="date" name="cnic_expiry" class="form-control">
        </div>

        <div class="col-md-4">
            <label class="form-label">Father CNIC</label>
            <input type="text" name="father_cnic" class="form-control" placeholder="00000-0000000-0">
        </div>

        <div class="col-md-6">
            <label class="form-label">Nationality</label>
            <input type="text" name="nationality" class="form-control">
        </div>

        <div class="col-md-6">
            <label class="form-label">Date of Birth</label>
            <input type="date" name="dob" class="form-control">
        </div>

        <div class="col-md-6">
            <label class="form-label">NTN #</label>
            <input type="text" name="ntn" class="form-control">
        </div>

        <div class="col-md-6">
            <label class="form-label">Gender</label>
            <div class="d-flex gap-3 mt-1">
                <div class="form-check d-flex align-items-center gap-1">
                    <input class="check-input" type="radio" name="gender" value="Male">
                    <label class="form-check-label">Male</label>
                </div>
                <div class="form-check d-flex align-items-center gap-1">
                    <input class="check-input" type="radio" name="gender" value="Female">
                    <label class="form-check-label">Female</label>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <label class="form-label">Domicile (District)</label>
            <input type="text" name="domicile_district" class="form-control">
        </div>

        <div class="col-md-4">
            <label class="form-label">Domicile (Province)</label>
            <input type="text" name="domicile_province" class="form-control">
        </div>

        <div class="col-md-4">
            <label class="form-label">Town / City of Birth</label>
            <input type="text" name="city_of_birth" class="form-control">
        </div>

        <div class="col-md-6">
            <label class="form-label">Religion</label>
            <input type="text" name="religion" class="form-control">
        </div>

        <div class="col-md-6">
            <label class="form-label">Sect</label>
            <input type="text" name="sect" class="form-control">
        </div>

        <div class="col-md-6">
            <label class="form-label">Spouse Name & Nationality</label>
            <input type="text" name="spouse_name" class="form-control">
        </div>

        <div class="col-md-6">
            <label class="form-label">Marital Status</label>
            <div class="position-relative">
                <select name="marital_status" class="form-select pe-4"
                    style="background-color: transparent !important; border: 1px solid #012445; box-shadow: 0 0 4px 2px #5a59593d; appearance: none; -webkit-appearance: none;">
                    <option value="">Select</option>
                    <option>Single</option>
                    <option>Married</option>
                    <option>Separated</option>
                    <option>Divorced</option>
                    <option>Widowed</option>
                </select>
            </div>
        </div>

        {{-- NOK --}}
        <div class="col-md-3">
            <label class="form-label">Name of Next of Kin (NOK)</label>
            <input type="text" name="nok_name" class="form-control">
        </div>

        <div class="col-md-3">
            <label class="form-label">NOK CNIC & Date of Expiry</label>
            <input type="text" name="nok_cnic" class="form-control">
        </div>

        <div class="col-md-3">
            <label class="form-label">Relation with NOK</label>
            <input type="text" name="nok_relation" class="form-control">
        </div>

        <div class="col-md-3">
            <label class="form-label">NOK Date of Birth</label>
            <input type="date" name="nok_dob" class="form-control">
        </div>

        <div class="col-md-6">
            <label class="form-label">NOK Contact No</label>
            <input type="tel" name="nok_contact" class="form-control">
        </div>

        {{-- Organization / SBU / Department --}}
        <div class="col-md-4">
            <label class="form-label">Organization <span class="text-danger">*</span></label>
            <select name="organization_id" id="org_select" class="form-select"
                style="background-color:transparent !important; border:1px solid #012445; box-shadow:0 0 4px 2px #5a59593d; appearance:none; -webkit-appearance:none;"
                onchange="onOrgChange(this.value)">
                <option value="">— Select Organization —</option>
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label">SBU</label>
            <select name="sbu_id" id="sbu_select" class="form-select"
                style="background-color:transparent !important; border:1px solid #012445; box-shadow:0 0 4px 2px #5a59593d; appearance:none; -webkit-appearance:none;"
                onchange="onSbuChange(this.value)">
                <option value="">— Select SBU —</option>
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label">Department</label>
            <select name="department_id" id="dept_select" class="form-select"
                style="background-color:transparent !important; border:1px solid #012445; box-shadow:0 0 4px 2px #5a59593d; appearance:none; -webkit-appearance:none;">
                <option value="">— Select Department —</option>
            </select>
        </div>

        {{-- Employment --}}
        <div class="col-md-6">
            <label class="form-label">Date of Joining</label>
            <input type="date" name="join_date" class="form-control">
        </div>

        <div class="col-md-6">
            <label class="form-label">Designation</label>
            <input type="text" name="designation" class="form-control">
        </div>

        <div class="col-md-6">
            <label class="form-label">Grade</label>
            <input type="text" name="grade" class="form-control">
        </div>

        <div class="col-md-6">
            <label class="form-label">Branch</label>
            <input type="text" name="branch" class="form-control">
        </div>

        <div class="col-md-6">
            <label class="form-label">Location</label>
            <input type="text" name="location" class="form-control">
        </div>

    </div>
</div>

<script>
    window._orgsData = @json($orgsData ?? []);

    document.addEventListener('DOMContentLoaded', function () {
        const orgSel = document.getElementById('org_select');
        window._orgsData.forEach(function (o) {
            orgSel.insertAdjacentHTML('beforeend',
                '<option value="' + o.id + '">' + escHtmlBasic(o.name) + '</option>');
        });
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
    }

    function previewImg(input) {
        const preview = document.getElementById('imgPreview');
        if (input.files && input.files[0]) {
            preview.src = URL.createObjectURL(input.files[0]);
            preview.style.display = 'block';
        }
    }

    function toggleCategoryBlocks() {
        const selectedCategory = document.querySelector('input[name="employment_category"]:checked')?.value || '';
        const engagementMode = document.getElementById('engagementMode')?.value || '';

        const internFields      = document.getElementById('internFields');
        const contractualFields = document.getElementById('contractualFields');
        const engagementFields  = document.getElementById('engagementFields');
        const hybridDaysWrapper = document.getElementById('hybridDaysWrapper');

        if (internFields) internFields.style.display = selectedCategory === 'intern' ? '' : 'none';
        if (contractualFields) contractualFields.style.display = selectedCategory === 'contractual' ? '' : 'none';
        if (engagementFields) engagementFields.style.display = selectedCategory === 'engagement' ? '' : 'none';
        if (hybridDaysWrapper) hybridDaysWrapper.style.display = (selectedCategory === 'engagement' && engagementMode === 'hybrid') ? '' : 'none';
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.category-radio').forEach(function (el) {
            el.addEventListener('change', toggleCategoryBlocks);
        });
        const engagementModeEl = document.getElementById('engagementMode');
        if (engagementModeEl) engagementModeEl.addEventListener('change', toggleCategoryBlocks);
        toggleCategoryBlocks();
    });
</script>
