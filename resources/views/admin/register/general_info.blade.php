{{-- STEP 1: General Information --}}
<div class="step active" id="step-1">
    <div class="section-title">Section A — General Information</div>
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
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" name="full_name" class="form-control">
            </div>

            <div class="col-md-5">
                <label class="form-label">Father Name</label>
                <input type="text" name="father_name" class="form-control">
            </div>
        </div>

        <div class="col-md-4">
            <label class="form-label">CNIC <span class="text-danger">*</span></label>
            <input type="text" name="cnic" class="form-control" placeholder="00000-0000000-0">
        </div>

        <div class="col-md-4">
            <label class="form-label">CNIC Expiry Date <span class="text-danger">*</span></label>
            <input type="date" name="cnic_expiry" class="form-control">
        </div>

        <div class="col-md-4">
            <label class="form-label">Father CNIC</label>
            <input type="text" name="father_cnic" class="form-control" placeholder="00000-0000000-0">
        </div>

        <div class="col-md-6">
            <label class="form-label">Nationality <span class="text-danger">*</span></label>
            <select name="nationality" id="nationality_select" class="form-select"
                style="background-color:transparent !important; border:1px solid #012445; box-shadow:0 0 4px 2px #5a59593d; appearance:none; -webkit-appearance:none;">
                <option value="">— Select Nationality —</option>
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
            <input type="date" name="dob" class="form-control">
        </div>

        <div class="col-md-6">
            <label class="form-label">NTN #</label>
            <input type="text" name="ntn" class="form-control">
        </div>

        <div class="col-md-6">
            <label class="form-label">Gender</label>
            <select name="gender" class="form-select"
                style="background-color:transparent !important; border:1px solid #012445; box-shadow:0 0 4px 2px #5a59593d; appearance:none; -webkit-appearance:none;">
                <option value="">— Select —</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
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
            <select name="religion" class="form-select"
                style="background-color:transparent !important; border:1px solid #012445; box-shadow:0 0 4px 2px #5a59593d; appearance:none; -webkit-appearance:none;">
                <option value="">— Select —</option>
                <option value="Islam">Islam</option>
                <option value="Christianity">Christianity</option>
                <option value="Hinduism">Hinduism</option>
                <option value="Buddhism">Buddhism</option>
                <option value="Sikhism">Sikhism</option>
                <option value="Judaism">Judaism</option>
                <option value="Other">Other</option>
            </select>
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
            <label class="form-label">Marital Status <span class="text-danger">*</span></label>
            <select name="marital_status" class="form-select"
                style="background-color:transparent !important; border:1px solid #012445; box-shadow:0 0 4px 2px #5a59593d; appearance:none; -webkit-appearance:none;">
                <option value="">Select</option>
                <option>Single</option>
                <option>Married</option>
                <option>Separated</option>
                <option>Divorced</option>
                <option>Widowed</option>
            </select>
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
            <input type="tel" name="nok_contact" class="form-control" maxlength="15">
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        fetch('https://cdn.jsdelivr.net/npm/country-list@2.2.0/data.json')
            .then(function (r) { return r.json(); })
            .then(function (countries) {
                countries.sort(function (a, b) { return a.name.localeCompare(b.name); });
                const sel = document.getElementById('nationality_select');
                countries.forEach(function (c) {
                    const opt = document.createElement('option');
                    opt.value = c.name;
                    opt.textContent = c.name;
                    sel.appendChild(opt);
                });
                if (sel.dataset.prefill) sel.value = sel.dataset.prefill;
            })
            .catch(function () {
                ['Pakistani', 'Indian', 'British', 'American', 'Other'].forEach(function (n) {
                    document.getElementById('nationality_select').insertAdjacentHTML('beforeend',
                        '<option value="' + n + '">' + n + '</option>');
                });
            });
    });

    function previewImg(input) {
        const preview = document.getElementById('imgPreview');
        if (input.files && input.files[0]) {
            preview.src = URL.createObjectURL(input.files[0]);
            preview.style.display = 'block';
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
