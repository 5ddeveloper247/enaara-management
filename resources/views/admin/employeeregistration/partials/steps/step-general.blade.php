                        <div class="wizard-pane active" id="stepPane1">
                            <div id="step-1">
                                <section class="d-grid gap-3">
                                    <div class="card border-0 shadow-sm bg-light">
                                        <div class="card-body p-3">
                                            <div class="fw-bold text-dark mb-3">
                                                <span>Personal Information</span>
                                            </div>
                                            <div class="row g-3 w-100">
                                                <div class="col-12">
                                                    <div class="d-flex flex-wrap gap-4 justify-content-end">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="is_ex_armed_force" id="giExArmyRetiredCheckbox" value="1" {{ ($employee->is_ex_armed_force ?? false) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="giExArmyRetiredCheckbox">Ex-Army Retired</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="is_father_deceased" id="giFatherDeceasedCheckbox" value="1" {{ ($employee->is_father_deceased ?? false) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="giFatherDeceasedCheckbox">Father Deceased</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                                    <input type="text" name="full_name" class="form-control" id="giNameInput" value="{{ $employee->full_name ?? '' }}" placeholder="Enter full name">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Father Name <span class="text-danger">*</span></label>
                                                    <input type="text" name="father_name" class="form-control" value="{{ $employee->father_name ?? '' }}" placeholder="Enter father name">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">CNIC <span class="text-danger">*</span></label>
                                                    <input type="text" name="cnic" class="form-control cnic-mask" id="giCnicInput" value="{{ $employee->cnic ?? '' }}" placeholder="00000-0000000-0">
                                                </div>
                                                <div class="col">
                                                    <label class="form-label">CNIC Expiry Date <span class="text-danger">*</span></label>
                                                    <input type="date" name="cnic_expiry" class="form-control" value="{{ isset($employee->cnic_expiry) && $employee->cnic_expiry ? (is_string($employee->cnic_expiry) ? date('Y-m-d', strtotime($employee->cnic_expiry)) : $employee->cnic_expiry->format('Y-m-d')) : '' }}" placeholder="yyyy-mm-dd">
                                                </div>
                                                <div class="col" id="giFatherCnicField">
                                                    <label class="form-label">Father CNIC <span class="text-danger">*</span></label>
                                                    <input type="text" name="father_cnic" class="form-control cnic-mask" id="giFatherCnicInput" value="{{ $employee->father_cnic ?? '' }}" placeholder="00000-0000000-0">
                                                </div>
                                                <div class="col">
                                                    <label class="form-label">NTN #</label>
                                                    <input type="text" name="ntn" class="form-control" value="{{ $employee->ntn ?? '' }}" placeholder="Enter NTN number">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-12 col-xl-6">
                                            <div class="card border-0 bg-light h-100">
                                                <div class="card-body p-3">
                                                    <div class="fw-bold text-uppercase small mb-3">Birth and Domicile</div>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                                            <input type="date" name="dob" class="form-control" value="{{ isset($employee->dob) && $employee->dob ? (is_string($employee->dob) ? date('Y-m-d', strtotime($employee->dob)) : $employee->dob->format('Y-m-d')) : '' }}" placeholder="yyyy-mm-dd">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Town / City of Birth</label>
                                                            <input type="text" name="city_of_birth" class="form-control" value="{{ $employee->city_of_birth ?? '' }}" placeholder="Enter town or city">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Nationality <span class="text-danger">*</span></label>
                                                            <select name="nationality" class="form-select location-select" id="giNationalityInput" 
                                                                data-type="country"
                                                                data-current-value="{{ $employee->nationality ?? '' }}">
                                                                <option value="" disabled selected>Select nationality</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Gender <span class="text-danger">*</span></label>
                                                            <select name="gender" class="form-select" id="giGenderInput">
                                                                <option value="" {{ !isset($employee->gender) ? 'selected' : '' }} disabled>-- Select --</option>
                                                                <option value="Male" {{ ($employee->gender ?? '') == 'Male' ? 'selected' : '' }}>Male</option>
                                                                <option value="Female" {{ ($employee->gender ?? '') == 'Female' ? 'selected' : '' }}>Female</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Province <span class="text-danger">*</span></label>
                                                        <select name="domicile_province" class="form-select location-select" id="giProvinceSelect"
                                                            data-type="province"
                                                            data-dependent-on="#giNationalityInput"
                                                            data-current-value="{{ $employee->domicile_province ?? '' }}">
                                                                <option value="" disabled selected>Select province</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">District <span class="text-danger">*</span></label>
                                                        <select name="domicile_district" class="form-select location-select" id="giDistrictSelect"
                                                            data-type="district"
                                                            data-dependent-on="#giProvinceSelect"
                                                            data-current-value="{{ $employee->domicile_district ?? '' }}">
                                                                <option value="" disabled selected>Select district</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-xl-6">
                                            <div class="card border-0 bg-light h-100">
                                                <div class="card-body p-3">
                                                    <div class="fw-bold text-uppercase small mb-3">Religion and Marital</div>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Religion <span class="text-danger">*</span></label>
                                                            <select name="religion" class="form-select" id="giReligionInput">
                                                                <option value="" {{ !isset($employee->religion) ? 'selected' : '' }} disabled>-- Select --</option>
                                                                <option value="Islam" {{ ($employee->religion ?? '') == 'Islam' ? 'selected' : '' }}>Islam</option>
                                                                <option value="Hinduism" {{ ($employee->religion ?? '') == 'Hinduism' ? 'selected' : '' }}>Hinduism</option>
                                                                <option value="Christianity" {{ ($employee->religion ?? '') == 'Christianity' ? 'selected' : '' }}>Christianity</option>
                                                                <option value="Sikhism" {{ ($employee->religion ?? '') == 'Sikhism' ? 'selected' : '' }}>Sikhism</option>
                                                                <option value="Other" {{ ($employee->religion ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Sect <span class="text-danger">*</span></label>
                                                            <input type="text" name="sect" class="form-control" value="{{ $employee->sect ?? '' }}" placeholder="Enter sect">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Marital Status <span class="text-danger">*</span></label>
                                                            <select name="marital_status" class="form-select" id="giMaritalStatusSelect">
                                                                <option value="" {{ !isset($employee->marital_status) ? 'selected' : '' }} disabled>Select</option>
                                                                <option value="Single" {{ ($employee->marital_status ?? '') == 'Single' ? 'selected' : '' }}>Single</option>
                                                                <option value="Married" {{ ($employee->marital_status ?? '') == 'Married' ? 'selected' : '' }}>Married</option>
                                                                <option value="Divorced" {{ ($employee->marital_status ?? '') == 'Divorced' ? 'selected' : '' }}>Divorced</option>
                                                                <option value="Widowed" {{ ($employee->marital_status ?? '') == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6 {{ ($employee->marital_status ?? '') == 'Married' ? '' : 'd-none' }}" id="giSpouseNameField">
                                                            <label class="form-label">Spouse Name <span class="text-danger">*</span></label>
                                                            <input type="text" name="spouse_name" class="form-control" value="{{ $employee->spouse_name ?? '' }}" placeholder="Enter spouse name">
                                                        </div>
                                                        <div class="col-md-6 {{ ($employee->marital_status ?? '') == 'Married' ? '' : 'd-none' }}" id="giSpouseNationalityField">
                                                            <label class="form-label">Spouse Nationality <span class="text-danger">*</span></label>
                                                            <select name="spouse_nationality" class="form-select location-select" id="giSpouseNationalityInput"
                                                                data-type="country"
                                                                data-current-value="{{ $employee->spouse_nationality ?? '' }}">
                                                                <option value="" disabled selected>Select nationality</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6 {{ ($employee->marital_status ?? '') == 'Married' ? '' : 'd-none' }}" id="giSpouseCnicField">
                                                            <label class="form-label">Spouse CNIC <span class="text-danger">*</span></label>
                                                            <input type="text" name="spouse_cnic" class="form-control cnic-mask" value="{{ $employee->spouse_cnic ?? '' }}" placeholder="00000-0000000-0">
                                                        </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </section>
                            </div>
                        </div>
