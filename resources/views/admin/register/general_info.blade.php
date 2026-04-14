{{-- STEP 1: General Information --}}
<style>
    .ex-armed-force-panel {
        border-color: rgba(1, 36, 69, 0.18) !important;
        background: linear-gradient(135deg, rgba(1, 36, 69, 0.04) 0%, rgba(1, 36, 69, 0.01) 100%);
        box-shadow: 0 1px 2px rgba(1, 36, 69, 0.06);
    }
    .ex-armed-force-panel__accent {
        width: 5px;
        min-height: 100%;
        background: linear-gradient(180deg, var(--bs-primary, #0d6efd) 0%, rgba(1, 36, 69, 0.85) 100%);
        flex-shrink: 0;
    }
    .ex-armed-force-panel__icon {
        width: 48px;
        height: 48px;
        background: rgba(1, 36, 69, 0.08);
        color: var(--main-color, #012445);
        font-size: 1.35rem;
    }
    .ex-armed-toggle {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        user-select: none;
        margin: 0;
    }
    .ex-armed-toggle__input:focus {
        outline: none;
    }
    .ex-armed-toggle:focus-within .ex-armed-toggle__track {
        box-shadow: 0 0 0 3px rgba(1, 36, 69, 0.22);
    }
    .ex-armed-toggle__state {
        font-size: 0.8125rem;
        font-weight: 600;
        min-width: 1.75rem;
        text-align: center;
        color: #adb5bd;
        transition: color 0.2s, opacity 0.2s;
    }
    .ex-armed-toggle__input:not(:checked) ~ .ex-armed-toggle__state--off {
        color: var(--main-color, #012445);
    }
    .ex-armed-toggle__input:checked ~ .ex-armed-toggle__state--off {
        color: #adb5bd;
        font-weight: 500;
    }
    .ex-armed-toggle__input:checked ~ .ex-armed-toggle__track ~ .ex-armed-toggle__state--on {
        color: #198754;
    }
    .ex-armed-toggle__input:not(:checked) ~ .ex-armed-toggle__track ~ .ex-armed-toggle__state--on {
        color: #ced4da;
        font-weight: 500;
    }
    .ex-armed-toggle__track {
        position: relative;
        width: 3.25rem;
        height: 1.75rem;
        border-radius: 999px;
        background: #e9ecef;
        border: 2px solid #adb5bd;
        transition: background 0.2s, border-color 0.2s;
        flex-shrink: 0;
    }
    .ex-armed-toggle__thumb {
        position: absolute;
        top: 2px;
        left: 2px;
        width: calc(1.75rem - 8px);
        height: calc(1.75rem - 8px);
        border-radius: 50%;
        background: #fff;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        transition: transform 0.22s ease;
    }
    .ex-armed-toggle__input:checked ~ .ex-armed-toggle__track {
        background: #198754;
        border-color: #157347;
    }
    .ex-armed-toggle__input:checked ~ .ex-armed-toggle__track .ex-armed-toggle__thumb {
        transform: translateX(calc(3.25rem - 1.75rem));
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
    }
</style>
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

                <div id="imgPreviewWrapper" class="employee-img-preview-wrap" style="display:none; position:relative; width:130px; height:130px; margin-top:6px;">
                    <img id="imgPreview" src="" alt=""
                        style="width:130px; height:130px; object-fit:cover; border-radius:10px; border:2px solid var(--main-color);">
                    <button type="button" id="removeImageBtn" onclick="removePreviewImg()" class="d-none employee-img-remove-btn" style="position:absolute; top:6px; right:6px; width:28px; height:28px; border:none; border-radius:50%; background:#dc3545; color:#fff; font-size:18px; line-height:1; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 6px rgba(0,0,0,0.2); padding:0; z-index:2;">
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
                <label class="form-label">Father Name <span class="text-danger">*</span></label>
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
            <label class="form-label">Father CNIC <span class="text-danger">*</span></label>
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
            <input type="text" name="ntn" class="form-control number-only" maxlength="13" inputmode="numeric" autocomplete="off">
        </div>

        <div class="col-md-3">
            <label class="form-label">Gender <span class="text-danger">*</span></label>
            <select name="gender" class="form-select"
                style="background-color:transparent !important; border:1px solid #012445; box-shadow:0 0 4px 2px #5a59593d; appearance:none; -webkit-appearance:none;">
                <option value="">— Select —</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Province <span class="text-danger">*</span></label>
            <div id="province_wrapper">
                <select name="domicile_province" id="province_select" class="form-select"
                    data-prefill="{{ $employee->domicile_province ?? '' }}"
                    style="background-color:transparent !important; border:1px solid #012445; box-shadow:0 0 4px 2px #5a59593d; appearance:none; -webkit-appearance:none;">
                    <option value="">— Select Province —</option>
                </select>
            </div>
        </div>

        <div class="col-md-3">
            <label class="form-label">District <span class="text-danger">*</span></label>
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
            <label class="form-label">Religion <span class="text-danger">*</span></label>
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
            <label class="form-label">Sect <span class="text-danger">*</span></label>
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

        <div id="spouse_fields_wrapper" class="row col-md-9" style="display: none;">
            <div class="col-md-4">
                <label class="form-label">Spouse Name <span class="text-danger">*</span></label>
                <input type="text" name="spouse_name" id="spouse_name" class="form-control" value="{{ $employee->spouse_name ?? '' }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Spouse CNIC <span class="text-danger">*</span></label>
                <input type="text" name="spouse_cnic" id="spouse_cnic" class="form-control cnic-mask" placeholder="XXXXX-XXXXXXX-X" value="{{ $employee->spouse_cnic ?? '' }}">
            </div>
            <div class="col-md-4">
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
            <select name="nok_relation_type" id="nok_relation_type" class="form-select"
                style="background-color:transparent !important; border:1px solid #012445; box-shadow:0 0 4px 2px #5a59593d; appearance:none; -webkit-appearance:none;">
                <option value="">— Select —</option>
                <option value="Father">Father</option>
                <option value="Mother">Mother</option>
                <option value="Husband">Husband</option>
                <option value="Wife">Wife</option>
                <option value="Son">Son</option>
                <option value="Daughter">Daughter</option>
                <option value="Brother">Brother</option>
                <option value="Sister">Sister</option>
                <option value="Other">Other</option>
            </select>
            @error('nok_relation_type') <div class="text-danger small">{{ $message }}</div> @enderror
            @error('nok_relation') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-3" id="nok_relation_other_wrapper" style="display:none;">
            <label class="form-label">Specify relation <span class="text-danger">*</span></label>
            <input type="text" name="nok_relation_other" id="nok_relation_other" class="form-control" maxlength="100" autocomplete="off"
                value="">
            @error('nok_relation_other') <div class="text-danger small">{{ $message }}</div> @enderror
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

        <div class="col-12 mt-3">
            <div class="ex-armed-force-panel border rounded-3 d-flex flex-md-row flex-column align-items-stretch overflow-hidden">
                <div class="ex-armed-force-panel__accent d-none d-md-block" aria-hidden="true"></div>
                <div class="d-flex flex-grow-1 align-items-center gap-3 flex-wrap p-3 p-md-4">
                    <div class="ex-armed-force-panel__icon rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" aria-hidden="true">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold text-body mb-1">Ex-armed forces employee</div>
                        <p class="small text-muted mb-0 lh-sm">Turn this on if the person has served or is associated with armed forces. The registration wizard will then include the armed forces details step.</p>
                    </div>
                    <div class="d-flex align-items-center gap-2 ms-md-auto flex-shrink-0">
                        <span class="small text-muted d-none d-sm-inline">Include armed forces step</span>
                        <label class="ex-armed-toggle" for="is_ex_armed_force">
                            <input class="ex-armed-toggle__input visually-hidden" type="checkbox" name="is_ex_armed_force" value="1" id="is_ex_armed_force" aria-label="Ex-armed forces employee — include armed forces step">
                            <span class="ex-armed-toggle__state ex-armed-toggle__state--off">Off</span>
                            <span class="ex-armed-toggle__track" aria-hidden="true"><span class="ex-armed-toggle__thumb"></span></span>
                            <span class="ex-armed-toggle__state ex-armed-toggle__state--on">On</span>
                        </label>
                    </div>
                </div>
            </div>
            @error('is_ex_armed_force') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
        </div>

    </div>
</div>

@push('scripts')
<script>
    window.__employeeRegisterGeneral = {
        countriesUrl: @json(route('admin.locations.countries')),
        provincesBase: @json(rtrim(url('/admin/locations/provinces'), '/')),
        districtsBase: @json(rtrim(url('/admin/locations/districts'), '/')),
        deletePhotoUrl: @json(route('admin.employee.delete_photo')),
    };
</script>
<script src="{{ asset('js/employee-register-general.js') }}" defer></script>
@endpush