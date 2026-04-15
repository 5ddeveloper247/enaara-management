                        <div class="wizard-pane active" id="stepPane1">
                            <div>
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
                                                            <input class="form-check-input" type="checkbox" id="giExArmyRetiredCheckbox">
                                                            <label class="form-check-label" for="giExArmyRetiredCheckbox">Ex-Army Retired</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="giFatherDeceasedCheckbox">
                                                            <label class="form-check-label" for="giFatherDeceasedCheckbox">Father Deceased</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="giNameInput" placeholder="Enter full name">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Father Name <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" placeholder="Enter father name">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">CNIC <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="giCnicInput" placeholder="00000-0000000-0">
                                                </div>
                                                <div class="col">
                                                    <label class="form-label">CNIC Expiry Date <span class="text-danger">*</span></label>
                                                    <input type="date" class="form-control" placeholder="yyyy-mm-dd">
                                                </div>
                                                <div class="col" id="giFatherCnicField">
                                                    <label class="form-label">Father CNIC <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="giFatherCnicInput" placeholder="00000-0000000-0">
                                                </div>
                                                <div class="col">
                                                    <label class="form-label">NTN #</label>
                                                    <input type="text" class="form-control" placeholder="Enter NTN number">
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
                                                            <input type="date" class="form-control" placeholder="yyyy-mm-dd">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Town / City of Birth</label>
                                                            <input type="text" class="form-control" placeholder="Enter town or city">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Nationality <span class="text-danger">*</span></label>
                                                            <select class="form-select" id="giNationalityInput">
                                                                <option selected disabled>-- Select Nationality --</option>
                                                                <option>Pakistani</option>
                                                                <option>Other</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Gender <span class="text-danger">*</span></label>
                                                            <select class="form-select" id="giGenderInput">
                                                                <option selected disabled>-- Select --</option>
                                                                <option>Male</option>
                                                                <option>Female</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">District <span class="text-danger">*</span></label>
                                                            <select class="form-select">
                                                                <option value="" selected disabled>Select district</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Province <span class="text-danger">*</span></label>
                                                            <select class="form-select">
                                                                <option value="" selected disabled>Select province</option>
                                                                <option>Punjab</option>
                                                                <option>Sindh</option>
                                                                <option>Khyber Pakhtunkhwa</option>
                                                                <option>Balochistan</option>
                                                                <option>Islamabad Capital Territory</option>
                                                                <option>Gilgit-Baltistan</option>
                                                                <option>Azad Jammu and Kashmir</option>
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
                                                            <select class="form-select" id="giReligionInput">
                                                                <option selected disabled>-- Select --</option>
                                                                <option>Islam</option>
                                                                <option>Other</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Sect <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" placeholder="Enter sect">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Marital Status <span class="text-danger">*</span></label>
                                                            <select class="form-select">
                                                                <option selected disabled>Select</option>
                                                                <option>Single</option>
                                                                <option>Married</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Spouse Name <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" placeholder="Enter spouse name">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Spouse Nationality <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" placeholder="Enter spouse nationality">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Spouse CNIC <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" placeholder="00000-0000000-0">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Spouse CNIC Expiry <span class="text-danger">*</span></label>
                                                            <input type="date" class="form-control" placeholder="yyyy-mm-dd">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="card border-0 bg-light">
                                                <div class="card-body p-3">
                                                    <div class="fw-bold text-uppercase small mb-3">Next of Kin (NOK)</div>
                                                    <div class="row g-3">
                                                        <div class="col-md-4">
                                                            <label class="form-label">Name of Next of Kin <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" placeholder="Enter NOK name">
                                                        </div>

                                                        <div class="col-md-4">
                                                            <label class="form-label">NOK Contact No <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" placeholder="03XXXXXXXXX">
                                                        </div>


                                                        <div class="col-md-4">
                                                            <label class="form-label">NOK Date of Birth <span class="text-danger">*</span></label>
                                                            <input type="date" class="form-control" placeholder="yyyy-mm-dd">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">NOK CNIC <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" placeholder="00000-0000000-0">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">NOK CNIC Expiry <span class="text-danger">*</span></label>
                                                            <input type="date" class="form-control" placeholder="yyyy-mm-dd">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">Relation with NOK <span class="text-danger">*</span></label>
                                                            <select class="form-select" id="giNokRelationSelect">
                                                                <option value="" selected disabled>Select relation</option>
                                                                <option value="Son">Son</option>
                                                                <option value="Daughter">Daughter</option>
                                                                <option value="Spouse">Spouse</option>
                                                                <option value="Brother">Brother</option>
                                                                <option value="Sister">Sister</option>
                                                                <option value="Father">Father</option>
                                                                <option value="Mother">Mother</option>
                                                                <option value="Guardian">Guardian</option>
                                                                <option value="Other">Other</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4 d-none" id="giNokSpecifyRelationField">
                                                            <label class="form-label">Specify Relation <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" id="giNokSpecifyRelationInput" placeholder="Specify relation">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            </div>
                        </div>