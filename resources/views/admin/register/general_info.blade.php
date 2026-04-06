{{-- STEP 1: General Information --}}
<div class="step active" id="step-1">
    <div class="section-title">Section A — General Information</div>
    <div class="row g-3">

        {{-- Personal --}}
        <div class="d-flex align-items-end gap-2">
            <div class="col-2">
                <label class="form-label">Upload Image</label>

                <label for="uploadImage" id="uploadImageBox" class="d-flex flex-column align-items-center justify-content-center gap-2"
                    style="width:160px; height:160px; border: 2px dashed var(--main-color); border-radius: 10px; cursor:pointer; background:#f8f9fa1a;">
                    <i class="bi bi-cloud-arrow-up fs-1 text-secondary"></i>
                    <span style="font-size:.72rem; color:#6c757d;">Click to upload</span>
                    <input type="file" id="uploadImage" name="profile_photo" accept="image/*" class="d-none" onchange="previewImg(this)">
                </label>

                <div id="imgPreviewWrapper" style="display:none; position:relative; width:160px; height:160px; margin-top:6px;">
                    <img id="imgPreview" src="" alt=""
                        style="width:160px; height:160px; object-fit:cover; border-radius:10px; border:2px solid var(--main-color);">
                    <button type="button" id="removeImageBtn" onclick="removePreviewImg()" class="d-none" style="  position:absolute; top:6px; right:6px; width:28px; height:28px; border:none; border-radius:50%; background:#dc3545; color:#fff; font-size:18px; line-height:1; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 6px rgba(0,0,0,0.2); padding:0;">
                        &times; </button>
                </div>
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
            <label class="form-label">Marital Status <span class="text-danger">*</span></label>
            <select name="marital_status" id="marital_status" class="form-select"
                style="background-color:transparent !important; border:1px solid #012445; box-shadow:0 0 4px 2px #5a59593d; appearance:none; -webkit-appearance:none;">
                <option value="">Select</option>
                <option>Single</option>
                <option>Married</option>
                <option>Separated</option>
                <option>Divorced</option>
                <option>Widowed</option>
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label">Spouse Name & Nationality</label>
            <input type="text" name="spouse_name" id="spouse_name" class="form-control">
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
    document.addEventListener('DOMContentLoaded', function() {
        fetch('https://cdn.jsdelivr.net/npm/country-list@2.2.0/data.json')
            .then(function(r) {
                return r.json();
            })
            .then(function(countries) {
                countries.sort(function(a, b) {
                    return a.name.localeCompare(b.name);
                });
                const sel = document.getElementById('nationality_select');
                countries.forEach(function(c) {
                    const opt = document.createElement('option');
                    opt.value = c.name;
                    opt.textContent = c.name;
                    sel.appendChild(opt);
                });
                if (sel.dataset.prefill) sel.value = sel.dataset.prefill;
            })
            .catch(function() {
                ['Pakistani', 'Indian', 'British', 'American', 'Other'].forEach(function(n) {
                    document.getElementById('nationality_select').insertAdjacentHTML('beforeend',
                        '<option value="' + n + '">' + n + '</option>');
                });
            });
    });
    // code related to image preview and removal
    function previewImg(input) {
        const preview = document.getElementById('imgPreview');
        const previewWrapper = document.getElementById('imgPreviewWrapper');
        const removeBtn = document.getElementById('removeImageBtn');
        const uploadBox = document.getElementById('uploadImageBox');

        if (input.files && input.files[0]) {
            preview.src = URL.createObjectURL(input.files[0]);

            previewWrapper.style.display = 'block';
            uploadBox.classList.add('d-none');
            removeBtn.classList.remove('d-none');
        }
    }

    function removePreviewImg() {
        const input = document.getElementById('uploadImage');
        const preview = document.getElementById('imgPreview');
        const previewWrapper = document.getElementById('imgPreviewWrapper');
        const removeBtn = document.getElementById('removeImageBtn');
        const uploadBox = document.getElementById('uploadImageBox');

        input.value = '';
        preview.src = '';
        previewWrapper.style.display = 'none';

        removeBtn.classList.add('d-none');
        uploadBox.classList.remove('d-none');
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const maritalStatus = document.getElementById('marital_status');
        const spouseField = document.getElementById('spouse_name');

        function toggleSpouseField() {
            const spouseContainer = spouseField.closest('.col-md-6');

            if (maritalStatus.value === 'Single') {
                spouseField.value = '';
                spouseField.disabled = true;
                spouseContainer.style.display = 'none';
            } else {
                spouseField.disabled = false;
                spouseContainer.style.display = 'block';
            }
        }

        maritalStatus.addEventListener('change', toggleSpouseField);

        // page load pe bhi run karo
        toggleSpouseField();
    });
</script>