                        <div class="wizard-pane" id="stepPane6">
                            <div>
                                <section class="d-grid gap-3">
                                    <div>
                                        <div class="fw-bold text-dark mb-3">
                                            <span>More Details Information</span>
                                        </div>
                                        <div class="more-sub-nav d-flex flex-wrap gap-2 mb-3">
                                            <button type="button" class="btn btn-sm btn-outline-secondary more-sub-tab active"
                                                data-more-step="1"><span class="more-step-index">1</span><span>Contact</span></button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary more-sub-tab"
                                                data-more-step="2"><span class="more-step-index">2</span><span>Family</span></button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary more-sub-tab"
                                                data-more-step="3"><span class="more-step-index">3</span><span>Academic</span></button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary more-sub-tab"
                                                data-more-step="4"><span class="more-step-index">4</span><span>Employement</span></button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary more-sub-tab"
                                                data-more-step="5"><span class="more-step-index">5</span><span>Medical</span></button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary more-sub-tab"
                                                data-more-step="6"><span class="more-step-index">6</span><span>Reference</span></button>
                                        </div>

                                        <div class="more-sub-pane active" id="moreStepPane1">
                                            <div class="card border-0 bg-light">
                                                <div class="card-body p-3">
                                                    <div class="fw-bold text-uppercase small mb-3">Contact</div>
                                                    <div class="row g-3">
                                                        <div class="col-md-4">
                                                            <label class="form-label">Residence Phone</label>
                                                            <input type="text" class="form-control"
                                                                id="moreContactResidencePhoneInput"
                                                                placeholder="Enter residence phone">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">In Case of Emergency Contact No</label>
                                                            <input type="text" class="form-control"
                                                                id="moreContactEmergencyContactInput"
                                                                placeholder="Enter emergency contact number">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">Cell No <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control"
                                                                id="moreContactCellNoInput"
                                                                placeholder="Enter cell number">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">Email <span class="text-danger">*</span></label>
                                                            <input type="email" class="form-control"
                                                                id="moreContactEmailInput"
                                                                placeholder="Enter email address">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">Present Address <span class="text-danger">*</span></label>
                                                            <textarea class="form-control" id="moreContactPresentAddressInput" rows="1"
                                                                placeholder="Enter present address"></textarea>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">Permanent Address <span class="text-danger">*</span></label>
                                                            <textarea class="form-control" id="moreContactPermanentAddressInput" rows="1"
                                                                placeholder="Enter permanent address"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="more-sub-pane" id="moreStepPane2">
                                            <div>
                                                <div class="fw-bold text-uppercase small mb-3">Family</div>
                                                <div class="family-members-wrap bg-white">
                                                    <div class="family-members-toolbar">
                                                        <div class="small text-secondary">Add each family member as a separate card.</div>
                                                        <span class="family-members-count" id="moreFamilyMemberCount">0 Members</span>
                                                    </div>
                                                    <div id="moreFamilyMembersContainer"></div>
                                                </div>
                                                <div class="mt-3">
                                                    <button type="button" class="btn btn-sm text-white bg-main border-0"
                                                        id="moreFamilyAddMemberBtn">
                                                        <i class="bi bi-plus-lg me-1"></i>Add Member
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <template id="moreFamilyMemberTemplate">
                                            <div class="family-member-row mb-2 bg-light" data-family-row>
                                                <div class="family-member-header">
                                                    <span class="family-member-index" data-family-index>Member 1</span>
                                                    <div class="family-member-actions">
                                                        <button type="button" class="btn btn-sm btn-outline-primary" data-family-save title="Save member">
                                                            <i class="bi bi-floppy"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" data-family-remove>
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="row g-2">
                                                    <div class="col-12 col-md-6 col-xl-3">
                                                        <label class="form-label">Name <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control family-field-input" name="familyMembers[][name]"
                                                            data-family-name placeholder="Enter name" required>
                                                        <div class="family-field-preview" data-family-preview-name>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-2">
                                                        <label class="form-label">Gender <span class="text-danger">*</span></label>
                                                        <select class="form-select family-field-input" name="familyMembers[][gender]" data-family-gender required>
                                                            <option value="" selected disabled>Select</option>
                                                            <option value="Male">Male</option>
                                                            <option value="Female">Female</option>
                                                            <option value="Other">Other</option>
                                                        </select>
                                                        <div class="family-field-preview" data-family-preview-gender>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-2">
                                                        <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                                        <input type="date" class="form-control family-field-input" name="familyMembers[][date_of_birth]"
                                                            data-family-date-of-birth placeholder="yyyy-mm-dd" required>
                                                        <div class="family-field-preview" data-family-preview-date-of-birth>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-2">
                                                        <label class="form-label">Relation <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control family-field-input" name="familyMembers[][relation]"
                                                            data-family-relation placeholder="Enter relation" required>
                                                        <div class="family-field-preview" data-family-preview-relation>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-3">
                                                        <label class="form-label">Occupation</label>
                                                        <input type="text" class="form-control family-field-input" name="familyMembers[][occupation]"
                                                            data-family-occupation placeholder="Enter occupation">
                                                        <div class="family-field-preview" data-family-preview-occupation>-</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>

                                        <div class="more-sub-pane" id="moreStepPane3">
                                            <div>
                                                <div class="fw-bold text-uppercase small mb-3">Academic</div>
                                                <div class="academic-records-wrap bg-white">
                                                    <div class="academic-records-toolbar">
                                                        <div class="small text-secondary">Add each academic record as a separate row.</div>
                                                        <span class="academic-records-count" id="moreAcademicRecordCount">0 Records</span>
                                                    </div>
                                                    <div id="moreAcademicRecordsContainer"></div>
                                                </div>
                                                <div class="mt-3">
                                                    <button type="button" class="btn btn-sm text-white bg-main border-0"
                                                        id="moreAcademicAddRecordBtn">
                                                        <i class="bi bi-plus-lg me-1"></i>Add Academic Record
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <template id="moreAcademicRecordTemplate">
                                            <div class="academic-record-row mb-2 bg-light" data-academic-row>
                                                <div class="academic-record-header">
                                                    <span class="academic-record-index" data-academic-index>Record 1</span>
                                                    <div class="academic-record-actions">
                                                        <button type="button" class="btn btn-sm btn-outline-primary" data-academic-save title="Save record">
                                                            <i class="bi bi-floppy"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" data-academic-remove>
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="row g-2">
                                                    <div class="col-12 col-md-6 col-xl-3">
                                                        <label class="form-label">Degree / Certificate <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control academic-field-input" name="academicRecords[][degree_certificate]"
                                                            data-academic-degree placeholder="Enter degree or certificate" required>
                                                        <div class="academic-field-preview" data-academic-preview-degree>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-3">
                                                        <label class="form-label">Grade / Div / CGPA <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control academic-field-input" name="academicRecords[][grade_div_cgpa]"
                                                            data-academic-grade placeholder="Enter grade, division, or CGPA" required>
                                                        <div class="academic-field-preview" data-academic-preview-grade>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-2">
                                                        <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                                        <input type="date" class="form-control academic-field-input" name="academicRecords[][start_date]"
                                                            data-academic-start-date placeholder="yyyy-mm-dd" required>
                                                        <div class="academic-field-preview" data-academic-preview-start-date>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-2">
                                                        <label class="form-label">End Date <span class="text-danger">*</span></label>
                                                        <input type="date" class="form-control academic-field-input" name="academicRecords[][end_date]"
                                                            data-academic-end-date placeholder="yyyy-mm-dd" required>
                                                        <div class="academic-field-preview" data-academic-preview-end-date>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-2">
                                                        <label class="form-label">Field of Study</label>
                                                        <input type="text" class="form-control academic-field-input" name="academicRecords[][field_of_study]"
                                                            data-academic-field-of-study placeholder="Enter field of study">
                                                        <div class="academic-field-preview" data-academic-preview-field-of-study>-</div>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">University / Board / Institute</label>
                                                        <input type="text" class="form-control academic-field-input" name="academicRecords[][institute]"
                                                            data-academic-institute placeholder="Enter university, board, or institute">
                                                        <div class="academic-field-preview" data-academic-preview-institute>-</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>

                                        <div class="more-sub-pane" id="moreStepPane4">
                                            <div>
                                                <div class="fw-bold text-uppercase small mb-3">Employement</div>
                                                <div class="employment-records-wrap bg-white">
                                                    <div class="employment-records-toolbar">
                                                        <div class="small text-secondary">Add each employement record as a separate row.</div>
                                                        <span class="employment-records-count" id="moreEmployementRecordCount">0 Records</span>
                                                    </div>
                                                    <div id="moreEmployementRecordsContainer"></div>
                                                </div>
                                                <div class="mt-3">
                                                    <button type="button" class="btn btn-sm text-white bg-main border-0"
                                                        id="moreEmployementAddRecordBtn">
                                                        <i class="bi bi-plus-lg me-1"></i>Add Employement Record
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <template id="moreEmployementRecordTemplate">
                                            <div class="employment-record-row mb-2 bg-light" data-employement-row>
                                                <div class="employment-record-header">
                                                    <span class="employment-record-index" data-employement-index>Record 1</span>
                                                    <div class="employment-record-actions">
                                                        <button type="button" class="btn btn-sm btn-outline-primary" data-employement-save title="Save record">
                                                            <i class="bi bi-floppy"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" data-employement-remove>
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="row g-2">
                                                    <div class="col-12 col-md-6 col-xl-3">
                                                        <label class="form-label">Organization <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control employment-field-input" name="employementRecords[][organization]"
                                                            data-employement-organization placeholder="Enter organization" required>
                                                        <div class="employment-field-preview" data-employement-preview-organization>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-3">
                                                        <label class="form-label">Designation <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control employment-field-input" name="employementRecords[][designation]"
                                                            data-employement-designation placeholder="Enter designation" required>
                                                        <div class="employment-field-preview" data-employement-preview-designation>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-2">
                                                        <label class="form-label">From <span class="text-danger">*</span></label>
                                                        <input type="date" class="form-control employment-field-input" name="employementRecords[][from_date]"
                                                            data-employement-from-date placeholder="yyyy-mm-dd" required>
                                                        <div class="employment-field-preview" data-employement-preview-from-date>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-2">
                                                        <label class="form-label">To <span class="text-danger">*</span></label>
                                                        <input type="date" class="form-control employment-field-input" name="employementRecords[][to_date]"
                                                            data-employement-to-date placeholder="yyyy-mm-dd" required>
                                                        <div class="employment-field-preview" data-employement-preview-to-date>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-2">
                                                        <label class="form-label">Salary</label>
                                                        <input type="text" class="form-control employment-field-input" name="employementRecords[][salary]"
                                                            data-employement-salary placeholder="Enter salary">
                                                        <div class="employment-field-preview" data-employement-preview-salary>-</div>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Reason for Leaving</label>
                                                        <input type="text" class="form-control employment-field-input" name="employementRecords[][reason_for_leaving]"
                                                            data-employement-reason placeholder="Enter reason for leaving">
                                                        <div class="employment-field-preview" data-employement-preview-reason>-</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>

                                        <div class="more-sub-pane" id="moreStepPane5">
                                            <div>
                                                <div class="fw-bold text-uppercase small mb-3">Medical</div>
                                                <div class="card border-0 bg-light">
                                                    <div class="card-body p-3">
                                                        <div class="row g-3">
                                                            <div class="col-12">
                                                                <label class="form-label">Last Medical Fitness Test - Date & Results</label>
                                                                <textarea class="form-control" id="moreMedicalLastFitnessTestInput" rows="2"
                                                                    placeholder="Enter date and results of last medical fitness test"></textarea>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label class="form-label d-block">Do you have any disability?</label>
                                                                <div class="d-flex gap-4 pt-1">
                                                                    <input class="btn-check" type="radio" name="moreMedicalHasDisability"
                                                                        id="moreMedicalHasDisabilityYes" value="Yes">
                                                                    <label class="btn btn-outline-secondary option-chip"
                                                                        for="moreMedicalHasDisabilityYes">Yes</label>

                                                                    <input class="btn-check" type="radio" name="moreMedicalHasDisability"
                                                                        id="moreMedicalHasDisabilityNo" value="No">
                                                                    <label class="btn btn-outline-secondary option-chip"
                                                                        for="moreMedicalHasDisabilityNo">No</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label class="form-label">Blood Group</label>
                                                                <input type="text" class="form-control" id="moreMedicalBloodGroupInput"
                                                                    placeholder="Enter blood group">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label class="form-label">If Yes (Disability Type)</label>
                                                                <select class="form-select" id="moreMedicalDisabilityTypeInput">
                                                                    <option value="" selected disabled>Select</option>
                                                                    <option value="Physical">Physical</option>
                                                                    <option value="Visual">Visual</option>
                                                                    <option value="Hearing">Hearing</option>
                                                                    <option value="Speech">Speech</option>
                                                                    <option value="Other">Other</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-12">
                                                                <label class="form-label">Disease / Disability Description</label>
                                                                <textarea class="form-control" id="moreMedicalDisabilityDescriptionInput" rows="2"
                                                                    placeholder="Enter disease or disability description"></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="more-sub-pane" id="moreStepPane6">
                                            <div>
                                                <div class="fw-bold text-uppercase small mb-3">Reference</div>
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <div class="card border-0 bg-light h-100">
                                                            <div class="card-body p-3">
                                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                                    <div class="fw-bold text-dark">Reference 1</div>
                                                                    <span class="badge text-bg-light border">Primary</span>
                                                                </div>
                                                                <div class="row g-3">
                                                                    <div class="col-6">
                                                                        <label class="form-label">Name</label>
                                                                        <input type="text" class="form-control" id="moreReferenceOneNameInput"
                                                                            placeholder="Enter name">
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <label class="form-label">Designation</label>
                                                                        <input type="text" class="form-control" id="moreReferenceOneDesignationInput"
                                                                            placeholder="Enter designation">
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <label class="form-label">Organization</label>
                                                                        <input type="text" class="form-control" id="moreReferenceOneOrganizationInput"
                                                                            placeholder="Enter organization">
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <label class="form-label">Contact No</label>
                                                                        <input type="text" class="form-control" id="moreReferenceOneContactNoInput"
                                                                            placeholder="Enter contact number">
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <label class="form-label">Relationship</label>
                                                                        <select class="form-select" id="moreReferenceOneRelationshipInput">
                                                                            <option value="" selected disabled>Select</option>
                                                                            <option value="Family">Family</option>
                                                                            <option value="Friend">Friend</option>
                                                                            <option value="Colleague">Colleague</option>
                                                                            <option value="Professional">Professional</option>
                                                                            <option value="Other">Other</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="card border-0 bg-light h-100">
                                                            <div class="card-body p-3">
                                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                                    <div class="fw-bold text-dark">Reference 2</div>
                                                                    <span class="badge text-bg-light border">Secondary</span>
                                                                </div>
                                                                <div class="row g-3">
                                                                    <div class="col-6">
                                                                        <label class="form-label">Name</label>
                                                                        <input type="text" class="form-control" id="moreReferenceTwoNameInput"
                                                                            placeholder="Enter name">
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <label class="form-label">Designation</label>
                                                                        <input type="text" class="form-control" id="moreReferenceTwoDesignationInput"
                                                                            placeholder="Enter designation">
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <label class="form-label">Organization</label>
                                                                        <input type="text" class="form-control" id="moreReferenceTwoOrganizationInput"
                                                                            placeholder="Enter organization">
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <label class="form-label">Contact No</label>
                                                                        <input type="text" class="form-control" id="moreReferenceTwoContactNoInput"
                                                                            placeholder="Enter contact number">
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <label class="form-label">Relationship</label>
                                                                        <select class="form-select" id="moreReferenceTwoRelationshipInput">
                                                                            <option value="" selected disabled>Select</option>
                                                                            <option value="Family">Family</option>
                                                                            <option value="Friend">Friend</option>
                                                                            <option value="Colleague">Colleague</option>
                                                                            <option value="Professional">Professional</option>
                                                                            <option value="Other">Other</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
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
