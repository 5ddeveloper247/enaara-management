{{-- STEP 1: General Information --}}
<div class="step active" id="step-1">
    <div class="section-title d-flex align-items-center justify-content-between">
        <span>Section A — General Information</span>
        <small class="text-muted">Basic profile, identity and family details</small>
    </div>
    <div class="row">

        {{-- Personal --}}
        <div class="d-flex align-items-end gap-2 mb-2 p-2 rounded-3">
            <div class="col-md-2 col-sm-3">
                <label class="form-label">Upload Image</label>

                <label for="uploadImage" id="uploadImageBox" class="d-flex flex-column align-items-center justify-content-center gap-2"
                    style="width:160px; height:100px; border:2px dashed var(--main-color); border-radius:10px; cursor:pointer; background:#f8f9fa1a;">
                    <i class="bi bi-cloud-arrow-up fs-1 text-secondary"></i>
                    <span style="font-size:.72rem; color:#6c757d;">Click to upload</span>
                    <input type="file" id="uploadImage" name="profile_photo" accept="image/*" class="d-none" onchange="openCropper(this)">
                </label>

                <div id="imgPreviewWrapper" style="display:none; position:relative; width:130px; height:130px; margin-top:6px;">
                    <img id="imgPreview" src="" alt=""
                        style="width:130px; height:130px; object-fit:cover; border-radius:10px; border:2px solid var(--main-color);">
                    <button type="button" id="removeImageBtn" onclick="removePreviewImg()" class="d-none" style="  position:absolute; top:6px; right:6px; width:28px; height:28px; border:none; border-radius:50%; background:#dc3545; color:#fff; font-size:18px; line-height:1; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 6px rgba(0,0,0,0.2); padding:0;">
                        &times; </button>
                </div>
                <div class="mt-1">
                    <small class="text-muted" style="font-size: 0.65rem;">Allowed: JPG, PNG, GIF, SVG. Max 2MB</small>
                </div>
            </div>

            <div class="col-md-5 col-sm-4">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" name="full_name" class="form-control">
            </div>

            <div class="col-md-5 col-sm-5">
                <label class="form-label">Father Name</label>
                <input type="text" name="father_name" class="form-control">
            </div>
        </div>

        <div class="col-md-3">
            <label class="form-label">CNIC <span class="text-danger">*</span></label>
            <input type="text" name="cnic" class="form-control cnic-mask" placeholder="XXXXX-XXXXXXX-X">
        </div>

        <div class="col-md-3">
            <label class="form-label">CNIC Expiry Date <span class="text-danger">*</span></label>
            <input type="date" name="cnic_expiry" class="form-control">
        </div>

        <div class="col-md-3">
            <label class="form-label">Father CNIC</label>
            <input type="text" name="father_cnic" class="form-control cnic-mask" placeholder="XXXXX-XXXXXXX-X">
        </div>

        <div class="col-md-3">
            <label class="form-label">Nationality <span class="text-danger">*</span></label>
            <select name="nationality" id="nationality_select" class="form-select"
                data-prefill="{{ $employee->nationality ?? '' }}"
                style="background-color:transparent !important; border:1px solid #012445; box-shadow:0 0 4px 2px #5a59593d; appearance:none; -webkit-appearance:none;">
                <option value="">— Select Nationality —</option>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
            <input type="date" name="dob" class="form-control">
        </div>

        <div class="col-md-3">
            <label class="form-label">NTN #</label>
            <input type="text" name="ntn" class="form-control">
        </div>

        <div class="col-md-3">
            <label class="form-label">Gender</label>
            <select name="gender" class="form-select"
                style="background-color:transparent !important; border:1px solid #012445; box-shadow:0 0 4px 2px #5a59593d; appearance:none; -webkit-appearance:none;">
                <option value="">— Select —</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Province</label>
            <div id="province_wrapper">
                <select name="domicile_province" id="province_select" class="form-select"
                    data-prefill="{{ $employee->domicile_province ?? '' }}"
                    style="background-color:transparent !important; border:1px solid #012445; box-shadow:0 0 4px 2px #5a59593d; appearance:none; -webkit-appearance:none;">
                    <option value="">— Select Province —</option>
                </select>
            </div>
        </div>

        <div class="col-md-3">
            <label class="form-label">District</label>
            <div id="district_wrapper">
                <select name="domicile_district" id="district_select" class="form-select"
                    data-prefill="{{ $employee->domicile_district ?? '' }}"
                    style="background-color:transparent !important; border:1px solid #012445; box-shadow:0 0 4px 2px #5a59593d; appearance:none; -webkit-appearance:none;">
                    <option value="">— Select District —</option>
                </select>
            </div>
        </div>

        <div class="col-md-3">
            <label class="form-label">Town / City of Birth</label>
            <input type="text" name="city_of_birth" class="form-control">
        </div>

        <div class="col-md-3">
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

        <div class="col-md-3">
            <label class="form-label">Sect</label>
            <input type="text" name="sect" class="form-control">
        </div>


        <div class="col-md-3">
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

        <div id="spouse_fields_wrapper" class="row col-md-6" style="display: none;">
            <div class="col-md-6">
                <label class="form-label">Spouse CNIC <span class="text-danger">*</span></label>
                <input type="text" name="spouse_cnic" id="spouse_cnic" class="form-control cnic-mask" placeholder="XXXXX-XXXXXXX-X" value="{{ $employee->spouse_cnic ?? '' }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Spouse Nationality <span class="text-danger">*</span></label>
                <select name="spouse_nationality" id="spouse_nationality" class="form-select"
                    data-prefill="{{ $employee->spouse_nationality ?? '' }}"
                    style="background-color:transparent !important; border:1px solid #012445; box-shadow:0 0 4px 2px #5a59593d; appearance:none; -webkit-appearance:none;">
                    <option value="">— Select Nationality —</option>
                </select>
            </div>
        </div>


        {{-- NOK --}}
        <div class="col-12 mt-2">
            <div class="small fw-semibold text-uppercase text-muted">Next of Kin (NOK)</div>
        </div>

        <div class="col-md-3">
            <label class="form-label">Name of Next of Kin (NOK) <span class="text-danger">*</span></label>
            <input type="text" name="nok_name" class="form-control" value="{{ $employee->nok_name ?? '' }}" required>
            @error('nok_name') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-3">
            <label class="form-label">NOK CNIC <span class="text-danger">*</span></label>
            <input type="text" name="nok_cnic" class="form-control cnic-mask" placeholder="XXXXX-XXXXXXX-X" value="{{ $employee->nok_cnic ?? '' }}" required>
            @error('nok_cnic') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-3">
            <label class="form-label">NOK Date of Expiry of CNIC <span class="text-danger">*</span></label>
            <input type="date" name="nok_cnic_expiry_date" class="form-control" value="{{ isset($employee->nok_cnic_expiry_date) ? (\Carbon\Carbon::parse($employee->nok_cnic_expiry_date)->format('Y-m-d')) : '' }}" required>
            @error('nok_cnic_expiry_date') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-3">
            <label class="form-label">Relation with NOK <span class="text-danger">*</span></label>
            <input type="text" name="nok_relation" class="form-control" value="{{ $employee->nok_relation ?? '' }}" required>
            @error('nok_relation') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-3">
            <label class="form-label">NOK Date of Birth <span class="text-danger">*</span></label>
            <input type="date" name="nok_dob" class="form-control" value="{{ isset($employee->nok_dob) ? (\Carbon\Carbon::parse($employee->nok_dob)->format('Y-m-d')) : '' }}" required>
            @error('nok_dob') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-3">
            <label class="form-label">NOK Contact No <span class="text-danger">*</span></label>
            <input type="tel" name="nok_contact" class="form-control" maxlength="15" value="{{ $employee->nok_contact ?? '' }}" required>
            @error('nok_contact') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // --- Location Dependency Logic ---
        const natSelect = document.getElementById('nationality_select');
        const provSelect = document.getElementById('province_select');
        const distSelect = document.getElementById('district_select');
        const spouseNatSelect = document.getElementById('spouse_nationality');

        function loadCountries() {
            fetch('{{ route('admin.locations.countries') }}')
                .then(r => r.json())
                .then(countries => {
                    countries.forEach(c => {
                        const opt1 = document.createElement('option');
                        opt1.value = c.name; opt1.textContent = c.name;
                        natSelect.appendChild(opt1);

                        const opt2 = document.createElement('option');
                        opt2.value = c.name; opt2.textContent = c.name;
                        spouseNatSelect.appendChild(opt2);
                    });
                    
                    if (natSelect.dataset.prefill) {
                        natSelect.value = natSelect.dataset.prefill;
                        loadProvinces(natSelect.dataset.prefill);
                    }
                    if (spouseNatSelect.dataset.prefill) {
                        spouseNatSelect.value = spouseNatSelect.dataset.prefill;
                    }
                });
        }

        function loadProvinces(countryName) {
            provSelect.innerHTML = '<option value="">— Select Province —</option>';
            distSelect.innerHTML = '<option value="">— Select District —</option>';
            if (!countryName) return;

            fetch(`/admin/locations/provinces/${encodeURIComponent(countryName)}`)
                .then(r => r.json())
                .then(provinces => {
                    provinces.forEach(p => {
                        const opt = document.createElement('option');
                        opt.value = p.name; opt.textContent = p.name;
                        provSelect.appendChild(opt);
                    });
                    if (provSelect.dataset.prefill) {
                        provSelect.value = provSelect.dataset.prefill;
                        loadDistricts(countryName, provSelect.dataset.prefill);
                    }
                });
        }

        function loadDistricts(countryName, provinceName) {
            distSelect.innerHTML = '<option value="">— Select District —</option>';
            if (!countryName || !provinceName) return;

            fetch(`/admin/locations/districts/${encodeURIComponent(countryName)}/${encodeURIComponent(provinceName)}`)
                .then(r => r.json())
                .then(districts => {
                    districts.forEach(d => {
                        const opt = document.createElement('option');
                        opt.value = d.name; opt.textContent = d.name;
                        distSelect.appendChild(opt);
                    });
                    if (distSelect.dataset.prefill) {
                        distSelect.value = distSelect.dataset.prefill;
                    }
                });
        }

        loadCountries();

        natSelect.addEventListener('change', function() { loadProvinces(this.value); });
        provSelect.addEventListener('change', function() { loadDistricts(natSelect.value, this.value); });

        // --- Spouse Logic ---
        const maritalStatusSelect = document.getElementById('marital_status');
        const spouseFieldsWrapper = document.getElementById('spouse_fields_wrapper');

        function toggleSpouseFields() {
            const isMarried = (maritalStatusSelect.value === 'Married');
            if (spouseFieldsWrapper) {
                spouseFieldsWrapper.style.display = isMarried ? 'flex' : 'none';
            }
            
            if (!isMarried) {
                if (document.getElementById('spouse_cnic')) document.getElementById('spouse_cnic').value = '';
                if (document.getElementById('spouse_nationality')) document.getElementById('spouse_nationality').value = '';
            }
        }

        maritalStatusSelect.addEventListener('change', toggleSpouseFields);
        toggleSpouseFields(); // Init
        
        // Initial mask application (if any values are pre-rendered by Laravel)
        if (typeof applyCnicMasks === 'function') {
            applyCnicMasks();
        }
    });

    // Profile Photo Logic (Vanilla)
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
        const employeeIdInput = document.querySelector('input[name="employee_id"]');
        const employeeId = employeeIdInput ? employeeIdInput.value : '';

        if (employeeId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to delete the profile photo.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#012445',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('id', employeeId);
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                    fetch('{{ route("admin.employee.delete_photo") }}', { method: 'POST', body: formData })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                resetPreviewUI(input, preview, previewWrapper, removeBtn, uploadBox);
                                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: data.message, showConfirmButton: false, timer: 3000 });
                            } else {
                                Swal.fire('Error', data.message || 'Error deleting photo.', 'error');
                            }
                        });
                }
            });
        } else {
            resetPreviewUI(input, preview, previewWrapper, removeBtn, uploadBox);
        }
    }

    function resetPreviewUI(input, preview, previewWrapper, removeBtn, uploadBox) {
        if (input) input.value = '';
        if (preview) preview.src = '';
        if (previewWrapper) previewWrapper.style.display = 'none';
        if (removeBtn) removeBtn.classList.add('d-none');
        if (uploadBox) uploadBox.classList.remove('d-none');
        if (typeof croppedImageBlob !== 'undefined') croppedImageBlob = null;
    }
</script>
@endpush