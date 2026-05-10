                        <div class="wizard-pane px-3" id="stepPane6">
                            <div id="step-6">
                                <section class="d-grid gap-3">
                                    {{-- <div class="fw-bold text-dark mb-3">
                                        <span>More Details Information</span>
                                    </div> --}}
                                 <div class="d-flex gap-2 align-items-start">
                                        <div class="more-sub-nav d-flex flex-column gap-2 mb-3 flex-shrink-0">
                                            <button type="button" class="btn btn-sm btn-outline-secondary more-sub-tab active"
                                                data-more-step="1"><span class="more-step-index">1</span><span>Contact</span></button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary more-sub-tab"
                                                data-more-step="2"><span class="more-step-index">2</span><span>Family</span></button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary more-sub-tab"
                                                data-more-step="3"><span class="more-step-index">3</span><span>Academic</span></button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary more-sub-tab"
                                                data-more-step="4"><span class="more-step-index">4</span><span>Certificates</span></button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary more-sub-tab"
                                                data-more-step="5"><span class="more-step-index">5</span><span>Employment</span></button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary more-sub-tab"
                                                data-more-step="6"><span class="more-step-index">6</span><span>Medical</span></button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary more-sub-tab"
                                                data-more-step="7"><span class="more-step-index">7</span><span>Reference</span></button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary more-sub-tab"
                                                data-more-step="8"><span class="more-step-index">8</span><span>Attachments</span></button>
                                        </div>

                                        <div class="more-sub-pane flex-grow-1 min-width-0 active" id="moreStepPane1">
                                            <div class="card border-0 bg-light">
                                                <div class="card-body p-3">
                                                    <div class="fw-bold text-uppercase small mb-3">Contact</div>
                                                     <div class="row g-3">
                                                         <div class="col-md-4">
                                                             <label class="form-label">Residence Phone</label>
                                                             <input type="text" name="residence_phone" class="form-control contact-mask"
                                                                 id="moreContactResidencePhoneInput"
                                                                 value="{{ $employee?->contact?->residence_phone ?? '' }}"
                                                                 placeholder="Enter residence phone" maxlength="15" inputmode="numeric" autocomplete="tel">
                                                         </div>
                                                         <div class="col-md-4">
                                                             <label class="form-label">In Case of Emergency Contact No</label>
                                                             <input type="text" name="emergency_contact" class="form-control contact-mask"
                                                                 id="moreContactEmergencyContactInput"
                                                                 value="{{ $employee?->contact?->emergency_contact ?? '' }}"
                                                                 placeholder="Enter emergency contact number" maxlength="15" inputmode="numeric" autocomplete="tel">
                                                         </div>
                                                         <div class="col-md-4">
                                                             <label class="form-label">Cell No <span class="text-danger">*</span></label>
                                                             <input type="text" name="cell_no" class="form-control contact-mask"
                                                                 id="moreContactCellNoInput"
                                                                 value="{{ $employee?->contact?->cell_no ?? '' }}"
                                                                 placeholder="Enter cell number" maxlength="15" inputmode="numeric" autocomplete="tel">
                                                         </div>
                                                         <div class="col-md-4">
                                                             <label class="form-label">Email <span class="text-danger">*</span></label>
                                                             <input type="email" name="contact_email" class="form-control"
                                                                 id="moreContactEmailInput"
                                                                 value="{{ $employee?->contact?->email ?? '' }}"
                                                                 placeholder="Enter email address">
                                                         </div>
                                                         <div class="col-12 col-md-4">
    <label class="form-label">
        Present Address <span class="text-danger">*</span>
    </label>

    <textarea
        name="present_address"
        class="form-control"
        id="moreContactPresentAddressInput"
        rows="4"
        placeholder="Enter complete present address"
        style="resize: vertical;"
    >{{ $employee?->contact?->present_address ?? '' }}</textarea>
</div>

<div class="col-12 col-md-4">
    <label class="form-label">
        Permanent Address <span class="text-danger">*</span>
    </label>

    <textarea
        name="permanent_address"
        class="form-control"
        id="moreContactPermanentAddressInput"
        rows="4"
        placeholder="Enter complete permanent address"
        style="resize: vertical;"
    >{{ $employee?->contact?->permanent_address ?? '' }}</textarea>
</div>
                                                     </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="more-sub-pane flex-grow-1 min-width-0" id="moreStepPane2">
                                            <div>
                                                <div class="fw-bold text-uppercase small mb-3">Family</div>
                                                <div class="family-members-wrap bg-white">
                                                    <div class="family-members-toolbar">
                                                        <div class="small text-secondary">Add each family member as a separate card.</div>
                                                        <span class="family-members-count" id="moreFamilyMemberCount">0 Members</span>
                                                    </div>
                                                     <div id="moreFamilyMembersContainer">
                                                     </div>
                                                     <script>
                                                         document.addEventListener('DOMContentLoaded', function() {
                                                              @if(isset($employee) && $employee?->familyMembers?->count() > 0)
                                                                 @foreach(($employee?->familyMembers ?? []) as $member)
                                                                     if (typeof window.addFamilyMember === 'function') {
                                                                         window.addFamilyMember({
                                                                             id: @json($member->id),
                                                                             name: @json($member?->name),
                                                                             gender: @json($member?->gender),
                                                                             dateOfBirth: @json(isset($member->dob) && $member->dob ? (is_string($member->dob) ? date('Y-m-d', strtotime($member->dob)) : $member->dob->format('Y-m-d')) : ''),
                                                                             relation: @json($member?->relation),
                                                                             occupation: @json($member?->occupation),
                                                                             is_next_of_kin: @json((bool) ($member?->is_next_of_kin ?? false)),
                                                                             nok_cnic: @json($member?->nok_cnic),
                                                                             nok_cnic_expiry_date: @json(isset($member->nok_cnic_expiry_date) && $member->nok_cnic_expiry_date ? (is_string($member->nok_cnic_expiry_date) ? date('Y-m-d', strtotime($member->nok_cnic_expiry_date)) : $member->nok_cnic_expiry_date->format('Y-m-d')) : ''),
                                                                             nok_contact: @json($member?->nok_contact),
                                                                         });
                                                                     }
                                                                 @endforeach
                                                             @endif
                                                         });
                                                     </script>
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
                                                        <div class="d-flex align-items-center">
                                                            <span class="family-member-index" data-family-index>Member 1</span>
                                                            <span class="badge rounded-pill text-bg-success ms-2 d-none" data-family-nok-badge>
                                                                <i class="bi bi-people-fill me-1"></i>Next of Kin
                                                            </span>
                                                        </div>
                                                        <div class="family-member-actions">
                                                            <button type="button" class="btn btn-sm btn-light border-success family-nok-toggle" data-family-nok-toggle title="Set as Next of Kin">
                                                                <i class="bi bi-people text-success"></i>
                                                            </button>
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
                                                            <input type="text" class="form-control family-field-input" name="family[][name]"
                                                                data-family-name placeholder="Enter name" required maxlength="50">
                                                            <div class="family-field-preview" data-family-preview-name>-</div>
                                                        </div>
                                                        <div class="col-12 col-md-6 col-xl-2">
                                                            <label class="form-label">Gender <span class="text-danger">*</span></label>
                                                            <select class="form-select family-field-input" name="family[][gender]" data-family-gender required>
                                                                <option value="" selected disabled>Select</option>
                                                                <option value="Male">Male</option>
                                                                <option value="Female">Female</option>
                                                            </select>
                                                            <div class="family-field-preview" data-family-preview-gender>-</div>
                                                        </div>
                                                        <div class="col-12 col-md-6 col-xl-2">
                                                            <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                                            <input type="date" class="form-control family-field-input" name="family[][dob]"
                                                                data-family-date-of-birth placeholder="yyyy-mm-dd" required>
                                                            <div class="family-field-preview" data-family-preview-dob>-</div>
                                                        </div>
                                                        <div class="col-12 col-md-6 col-xl-2">
                                                            <label class="form-label">Relation <span class="text-danger">*</span></label>
                                                            <select class="form-select family-field-input" name="family[][relation]" data-family-relation required>
                                                                <option value="" selected disabled>Select</option>
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
                                                            <div class="family-field-preview" data-family-preview-relation>-</div>
                                                        </div>
                                                        <div class="col-12 d-none" data-family-relation-other-wrapper>
                                                            <label class="form-label">Specify Relation <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control family-field-input" name="family[][relation_other]"
                                                                data-family-relation-other placeholder="Specify relation">
                                                        </div>
                                                        <div class="col-12 col-md-6 col-xl-3">
                                                            <label class="form-label">Occupation</label>
                                                            <input type="text" class="form-control family-field-input" name="family[][occupation]"
                                                                data-family-occupation placeholder="Enter occupation">
                                                            <div class="family-field-preview" data-family-preview-occupation>-</div>
                                                        </div>
                                                        <div class="col-12 d-none" data-family-nok-member-indicator>
                                                            <div class="small text-success fw-semibold">This member is selected as Next of Kin.</div>
                                                        </div>
                                                        <div class="col-12">
                                                            <hr class="text-secondary opacity-25 my-1">
                                                        </div>
                                                        <div class="col-12">
                                                            <input type="hidden" name="family[][is_next_of_kin]" value="0" data-family-is-nok-hidden>
                                                            <input type="radio" name="family_nok_selector" class="form-check-input family-nok-selector visually-hidden" title="Mark as Next of Kin" aria-label="Mark as Next of Kin">
                                                        </div>
                                                    <div class="col-12">
                                                        <div class="row g-2 border rounded p-2 mt-1 bg-white d-none" data-family-nok-fields>
                                                            <div class="col-12 small text-secondary">NOK identity and contact (required when this member is Next of Kin)</div>
                                                            <div class="col-12 col-md-6 col-xl-4">
                                                                <label class="form-label">NOK CNIC <span class="text-danger">*</span></label>
                                                                <input type="text" class="form-control family-field-input cnic-mask" name="family[][nok_cnic]"
                                                                    data-family-nok-input data-family-nok-cnic placeholder="00000-0000000-0" maxlength="15">
                                                                <div class="family-field-preview" data-family-preview-nok-cnic>-</div>
                                                            </div>
                                                            <div class="col-12 col-md-6 col-xl-4">
                                                                <label class="form-label">NOK CNIC expiry <span class="text-danger">*</span></label>
                                                                <input type="date" class="form-control family-field-input" name="family[][nok_cnic_expiry_date]"
                                                                    data-family-nok-input data-family-nok-cnic-expiry placeholder="yyyy-mm-dd">
                                                                <div class="family-field-preview" data-family-preview-nok-cnic-expiry-date>-</div>
                                                            </div>
                                                            <div class="col-12 col-md-6 col-xl-4">
                                                                <label class="form-label">NOK contact <span class="text-danger">*</span></label>
                                                                <input type="text" class="form-control family-field-input contact-mask" name="family[][nok_contact]"
                                                                    data-family-nok-input data-family-nok-contact placeholder="03XXXXXXXXX" maxlength="15" inputmode="numeric" autocomplete="tel">
                                                                <div class="family-field-preview" data-family-preview-nok-contact>-</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>

                                        <div class="more-sub-pane flex-grow-1 min-width-0" id="moreStepPane3">
                                            <div>
                                                <div class="fw-bold text-uppercase small mb-3">Academic</div>
                                                <div class="academic-records-wrap bg-white">
                                                    <div class="academic-records-toolbar">
                                                        <div class="small text-secondary">Add each academic record as a separate row.</div>
                                                        <span class="academic-records-count" id="moreAcademicRecordCount">0 Records</span>
                                                    </div>
                                                     <div id="moreAcademicRecordsContainer"></div>
                                                     <script>
                                                         document.addEventListener('DOMContentLoaded', function() {
                                                              @if(isset($employee) && $employee?->academics?->count() > 0)
                                                                 @foreach(($employee?->academics ?? []) as $record)
                                                                         window.addAcademicRecord({
                                                                             id: @json($record->id),
                                                                             degree: @json($record?->degree),
                                                                             degree_title: @json($record?->degree_title),
                                                                             grade_cgpa: @json($record?->grade_cgpa),
                                                                             start_date: @json(isset($record->start_date) && $record->start_date ? (is_string($record->start_date) ? date('Y-m-d', strtotime($record->start_date)) : $record->start_date->format('Y-m-d')) : ''),
                                                                             end_date: @json(isset($record->end_date) && $record->end_date ? (is_string($record->end_date) ? date('Y-m-d', strtotime($record->end_date)) : $record->end_date->format('Y-m-d')) : ''),
                                                                             fieldOfStudy: @json($record?->field_of_study),
                                                                             institute: @json($record?->institute)
                                                                         });
                                                                 @endforeach
                                                             @endif
                                                         });
                                                     </script>
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
                                                        <label class="form-label">Degree Type <span class="text-danger">*</span></label>
                                                        <select class="form-select academic-field-input" name="academics[][degree]"
                                                            data-academic-degree required>
                                                            <option value="" selected disabled>Select degree type</option>
                                                            <option value="Under Matric">Under Matric</option>
                                                            <option value="Matric">Matric</option>
                                                            <option value="Intermediate / Diploma">Intermediate / Diploma</option>
                                                            <option value="Associate Degree Program">Associate Degree Program</option>
                                                            <option value="Bachelor (2 Years)">Bachelor (2 Years)</option>
                                                            <option value="Bachelor (4 Years / BS)">Bachelor (4 Years / BS)</option>
                                                            <option value="Master (2 Years)">Master (2 Years)</option>
                                                            <option value="MS / MPhil">Master (4 Years MS / MPhil)</option>
                                                            <option value="Doctorate (PhD)">Doctorate (PhD)</option>
                                                        </select>
                                                        <div class="academic-field-preview" data-academic-preview-degree>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-3">
                                                        <label class="form-label">Degree Title <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control academic-field-input" name="academics[][degree_title]"
                                                            data-academic-degree-title placeholder="Enter degree title" required maxlength="100">
                                                        <div class="academic-field-preview" data-academic-preview-degree-title>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-3">
                                                        <label class="form-label">Field of Study</label>
                                                        <input type="text" class="form-control academic-field-input" name="academics[][field_of_study]"
                                                            data-academic-field-of-study placeholder="Enter field of study" maxlength="50">
                                                        <div class="academic-field-preview" data-academic-preview-field-of-study>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-3">
                                                        <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                                        <input type="date" class="form-control academic-field-input" name="academics[][start_date]"
                                                            data-academic-start-date placeholder="yyyy-mm-dd" required>
                                                        <div class="academic-field-preview" data-academic-preview-start-date>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-3">
                                                        <label class="form-label">End Date <span class="text-danger">*</span></label>
                                                        <input type="date" class="form-control academic-field-input" name="academics[][end_date]"
                                                            data-academic-end-date placeholder="yyyy-mm-dd" required>
                                                        <div class="academic-field-preview" data-academic-preview-end-date>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-3">
                                                        <label class="form-label">Grade / Div / CGPA <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control academic-field-input" name="academics[][grade_cgpa]"
                                                            data-academic-grade placeholder="Enter grade, division, or CGPA" required maxlength="20">
                                                        <div class="academic-field-preview" data-academic-preview-grade-cgpa>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-3 d-none" data-academic-board-wrap>
                                                        <label class="form-label">Board <span class="text-danger">*</span></label>
                                                        <select class="form-select academic-field-input" data-academic-board>
                                                            <option value="" selected disabled>Select board</option>
                                                            <option value="BISE Lahore">BISE Lahore</option>
                                                            <option value="BISE Rawalpindi">BISE Rawalpindi</option>
                                                            <option value="BISE Faisalabad">BISE Faisalabad</option>
                                                            <option value="BISE Gujranwala">BISE Gujranwala</option>
                                                            <option value="BISE Multan">BISE Multan</option>
                                                            <option value="BISE Sargodha">BISE Sargodha</option>
                                                            <option value="BISE Bahawalpur">BISE Bahawalpur</option>
                                                            <option value="BISE DG Khan">BISE DG Khan</option>
                                                            <option value="BISE Sahiwal">BISE Sahiwal</option>
                                                            <option value="BISE Karachi">BISE Karachi</option>
                                                            <option value="BISE Hyderabad">BISE Hyderabad</option>
                                                            <option value="BISE Sukkur">BISE Sukkur</option>
                                                            <option value="BISE Larkana">BISE Larkana</option>
                                                            <option value="BISE Mirpurkhas">BISE Mirpurkhas</option>
                                                            <option value="Aga Khan Board (AKU-EB)">Aga Khan Board (AKU-EB)</option>
                                                            <option value="BISE Peshawar">BISE Peshawar</option>
                                                            <option value="BISE Abbottabad">BISE Abbottabad</option>
                                                            <option value="BISE Kohat">BISE Kohat</option>
                                                            <option value="BISE Mardan">BISE Mardan</option>
                                                            <option value="BISE Bannu">BISE Bannu</option>
                                                            <option value="BISE Malakand">BISE Malakand</option>
                                                            <option value="BISE DI Khan">BISE DI Khan</option>
                                                            <option value="BISE Swat">BISE Swat</option>
                                                            <option value="BISE Quetta">BISE Quetta</option>
                                                            <option value="BISE Mirpur">BISE Mirpur</option>
                                                            <option value="BISE Gilgit-Baltistan">BISE Gilgit-Baltistan</option>
                                                            <option value="FBISE Islamabad">FBISE Islamabad</option>
                                                        </select>
                                                        <div class="academic-field-preview" data-academic-preview-board>-</div>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">University / Board / Institute</label>
                                                        <input type="text" class="form-control academic-field-input" name="academics[][institute]"
                                                            data-academic-institute list="academicUniversityList"
                                                            placeholder="Enter university, board, or institute">
                                                        <div class="academic-field-preview" data-academic-preview-institute>-</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>

                                        <datalist id="academicUniversityList">
                                            <option value="Quaid-i-Azam University"></option>
                                            <option value="National University of Sciences and Technology (NUST)"></option>
                                            <option value="University of the Punjab"></option>
                                            <option value="Lahore University of Management Sciences (LUMS)"></option>
                                            <option value="University of Karachi"></option>
                                            <option value="COMSATS University Islamabad"></option>
                                            <option value="University of Engineering and Technology (UET) Lahore"></option>
                                            <option value="University of Peshawar"></option>
                                            <option value="Bahauddin Zakariya University"></option>
                                            <option value="Government College University Lahore"></option>
                                        </datalist>

                                        <div class="more-sub-pane flex-grow-1 min-width-0" id="moreStepPane4">
                                            <div>
                                                <div class="fw-bold text-uppercase small mb-3">Certificates</div>
                                                <div class="academic-records-wrap bg-white">
                                                    <div class="academic-records-toolbar">
                                                        <div class="small text-secondary">Add each certificate as a separate row.</div>
                                                        <span class="academic-records-count" id="moreCertificateRecordCount">0 Records</span>
                                                    </div>
                                                    <div id="moreCertificateRecordsContainer"></div>
                                                    <script>
                                                        document.addEventListener('DOMContentLoaded', function() {
                                                            @if(isset($employee) && $employee?->certificates?->count() > 0)
                                                                @foreach(($employee?->certificates ?? []) as $record)
                                                                    if (typeof window.addCertificateRecord === 'function') {
                                                                        window.addCertificateRecord({
                                                                            id: @json($record->id),
                                                                            certificate_name: @json($record?->certificate_name),
                                                                            start_date: @json(isset($record->start_date) && $record->start_date ? (is_string($record->start_date) ? date('Y-m-d', strtotime($record->start_date)) : $record->start_date->format('Y-m-d')) : ''),
                                                                            end_date: @json(isset($record->end_date) && $record->end_date ? (is_string($record->end_date) ? date('Y-m-d', strtotime($record->end_date)) : $record->end_date->format('Y-m-d')) : ''),
                                                                            institute: @json($record?->institute)
                                                                        });
                                                                    }
                                                                @endforeach
                                                            @endif
                                                        });
                                                    </script>
                                                </div>
                                                <div class="mt-3">
                                                    <button type="button" class="btn btn-sm text-white bg-main border-0"
                                                        id="moreCertificateAddRecordBtn">
                                                        <i class="bi bi-plus-lg me-1"></i>Add Certificate
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <template id="moreCertificateRecordTemplate">
                                            <div class="certificate-record-row mb-2 bg-light" data-certificate-row>
                                                <div class="academic-record-header">
                                                    <span class="academic-record-index" data-certificate-index>Record 1</span>
                                                    <div class="academic-record-actions">
                                                        <button type="button" class="btn btn-sm btn-outline-primary" data-certificate-save title="Save record">
                                                            <i class="bi bi-floppy"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" data-certificate-remove>
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="row g-2">
                                                    <div class="col-12 col-md-6 col-xl-4">
                                                        <label class="form-label">Certificate Name <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control certificate-field-input" name="certificates[][certificate_name]"
                                                            data-certificate-name placeholder="Enter certificate name" maxlength="150" required>
                                                        <div class="certificate-field-preview" data-certificate-preview-certificate-name>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-2">
                                                        <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                                        <input type="date" class="form-control certificate-field-input" name="certificates[][start_date]"
                                                            data-certificate-start-date placeholder="yyyy-mm-dd" required>
                                                        <div class="certificate-field-preview" data-certificate-preview-start-date>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-2">
                                                        <label class="form-label">End Date <span class="text-danger">*</span></label>
                                                        <input type="date" class="form-control certificate-field-input" name="certificates[][end_date]"
                                                            data-certificate-end-date placeholder="yyyy-mm-dd" required>
                                                        <div class="certificate-field-preview" data-certificate-preview-end-date>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-4">
                                                        <label class="form-label">Institute <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control certificate-field-input" name="certificates[][institute]"
                                                            data-certificate-institute placeholder="Enter institute" maxlength="255" required>
                                                        <div class="certificate-field-preview" data-certificate-preview-institute>-</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>

                                        <div class="more-sub-pane flex-grow-1 min-width-0" id="moreStepPane5">
                                            <div>
                                                <div class="fw-bold text-uppercase small mb-3">Employment</div>
                                                <div class="employment-records-wrap bg-white">
                                                    <div class="employment-records-toolbar">
                                                        <div class="small text-secondary">Add each employment record as a separate row.</div>
                                                        <span class="employment-records-count" id="moreEmploymentRecordCount">0 Records</span>
                                                    </div>
                                                     <div id="moreEmploymentRecordsContainer"></div>
                                                     <script>
                                                         document.addEventListener('DOMContentLoaded', function() {
                                                              @if(isset($employee) && $employee?->exEmployments?->count() > 0)
                                                                 @foreach(($employee?->exEmployments ?? []) as $record)
                                                                     if (typeof window.addEmploymentRecord === 'function') {
                                                                         window.addEmploymentRecord({
                                                                             id: @json($record->id),
                                                                             organization: @json($record?->organization),
                                                                             designation: @json($record?->designation),
                                                                             from_date: @json(isset($record->from_date) && $record->from_date ? (is_string($record->from_date) ? date('Y-m-d', strtotime($record->from_date)) : $record->from_date->format('Y-m-d')) : ''),
                                                                             to_date: @json(isset($record->to_date) && $record->to_date ? (is_string($record->to_date) ? date('Y-m-d', strtotime($record->to_date)) : $record->to_date->format('Y-m-d')) : ''),
                                                                             salary: @json($record?->salary),
                                                                             reason_for_leaving: @json($record?->reason_for_leaving)
                                                                         });
                                                                     }
                                                                 @endforeach
                                                             @endif
                                                         });
                                                     </script>
                                                </div>
                                                <div class="mt-3">
                                                    <button type="button" class="btn btn-sm text-white bg-main border-0"
                                                        id="moreEmploymentAddRecordBtn">
                                                        <i class="bi bi-plus-lg me-1"></i>Add Employment Record
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <template id="moreEmploymentRecordTemplate">
                                            <div class="employment-record-row mb-2 bg-light" data-employment-row>
                                                <div class="employment-record-header">
                                                    <span class="employment-record-index" data-employment-index>Record 1</span>
                                                    <div class="employment-record-actions">
                                                        <button type="button" class="btn btn-sm btn-outline-primary" data-employment-save title="Save record">
                                                            <i class="bi bi-floppy"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" data-employment-remove>
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="row g-2">
                                                    <div class="col-12 col-md-6 col-xl-3">
                                                        <label class="form-label">Organization <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control employment-field-input" name="employments[][organization]"
                                                            data-employment-organization placeholder="Enter organization" maxlength="100" required>
                                                        <div class="employment-field-preview" data-employment-preview-organization>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-3">
                                                        <label class="form-label">Designation <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control employment-field-input" name="employments[][designation]"
                                                            data-employment-designation placeholder="Enter designation" maxlength="50" required>
                                                        <div class="employment-field-preview" data-employment-preview-designation>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-2">
                                                        <label class="form-label">From <span class="text-danger">*</span></label>
                                                        <input type="date" class="form-control employment-field-input" name="employments[][from_date]"
                                                            data-employment-from-date placeholder="yyyy-mm-dd" required>
                                                        <div class="employment-field-preview" data-employment-preview-from-date>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-2">
                                                        <label class="form-label">To <span class="text-danger">*</span></label>
                                                        <input type="date" class="form-control employment-field-input" name="employments[][to_date]"
                                                            data-employment-to-date placeholder="yyyy-mm-dd" required>
                                                        <div class="employment-field-preview" data-employment-preview-to-date>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-2">
                                                        <label class="form-label">Salary</label>
                                                        <input type="number" class="form-control employment-field-input" name="employments[][salary]"
                                                            data-employment-salary placeholder="Enter salary" maxlength="20" step="1">
                                                        <div class="employment-field-preview" data-employment-preview-salary>-</div>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Reason for Leaving</label>
                                                        <input type="text" class="form-control employment-field-input" name="employments[][reason_for_leaving]"
                                                            data-employment-reason placeholder="Enter reason for leaving" maxlength="200">
                                                        <div class="employment-field-preview" data-employment-preview-reason-for-leaving>-</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>

                                        <div class="more-sub-pane flex-grow-1 min-width-0" id="moreStepPane6">
                                            <div>
                                                <div class="fw-bold text-uppercase small mb-3">Medical</div>
                                                <div class="card border-0 bg-light">
                                                    <div class="card-body p-3">
                                                        @php
                                                            $hasChronicValMedical = strtolower((string) ($employee?->medical?->has_chronic_disease ?? ''));
                                                        @endphp  
                                                        <div class="row g-3">
                                                            <div class="col-12 col-md-6">
                                                                <label class="form-label">Last Medical Test Date</label>
                                                                <input type="date" name="last_fitness_test_date" class="form-control" id="moreMedicalLastFitnessTestDateInput"
                                                                    max="{{ date('Y-m-d') }}"
                                                                     value="{{ isset($employee->medical->last_fitness_test_date) && $employee->medical->last_fitness_test_date ? (is_string($employee->medical->last_fitness_test_date) ? date('Y-m-d', strtotime($employee->medical->last_fitness_test_date)) : $employee->medical->last_fitness_test_date->format('Y-m-d')) : '' }}">
                                                            </div>
                                                            <div class="col-12 col-md-6">
                                                                <label class="form-label">Result</label>
                                                                <select name="last_fitness_test_result" class="form-select" id="moreMedicalLastFitnessTestResultInput">
                                                                    <option value="" {{ empty($employee?->medical?->last_fitness_test_result) ? 'selected' : '' }} disabled>Select</option>
                                                                    <option value="Positive" {{ ($employee?->medical?->last_fitness_test_result ?? '') === 'Positive' ? 'selected' : '' }}>Positive</option>
                                                                    <option value="Negative" {{ ($employee?->medical?->last_fitness_test_result ?? '') === 'Negative' ? 'selected' : '' }}>Negative</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-12">
                                                                <label class="form-label">Medical Remarks</label>
                                                                <textarea name="last_fitness_test" class="form-control" id="moreMedicalLastFitnessTestInput" rows="2" maxlength="500"
                                                                    placeholder="Enter Medical Remarks">{{ $employee?->medical?->last_fitness_test ?? '' }}</textarea>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label">Do you have any disability? <span class="text-danger">*</span></label>
                                                                <div class="d-flex gap-2">
                                                                    <input class="btn-check" type="radio" name="has_disability"
                                                                        id="moreMedicalHasDisabilityYes" value="yes" {{ strtolower($employee?->medical?->has_disability ?? '') == 'yes' ? 'checked' : '' }}>
                                                                    <label class="btn btn-outline-secondary option-chip m-0 flex-grow-1"
                                                                        for="moreMedicalHasDisabilityYes">Yes</label>

                                                                    <input class="btn-check" type="radio" name="has_disability"
                                                                        id="moreMedicalHasDisabilityNo" value="no" {{ strtolower($employee?->medical?->has_disability ?? '') == 'no' ? 'checked' : '' }}>
                                                                    <label class="btn btn-outline-secondary option-chip m-0 flex-grow-1"
                                                                        for="moreMedicalHasDisabilityNo">No</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4" id="moreMedicalDisabilityTypeContainer" style="{{ strtolower($employee?->medical?->has_disability ?? '') == 'yes' ? '' : 'display:none;' }}">
                                                                <label class="form-label">If Yes (Disability Type) <span class="text-danger">*</span></label>
                                                                <select name="disability_type" class="form-select" id="moreMedicalDisabilityTypeInput">
                                                                    <option value="" selected disabled>Select</option>
                                                                    <option value="Physical" {{ ($employee?->medical?->disability_type ?? '') == 'Physical' ? 'selected' : '' }}>Physical</option>
                                                                    <option value="Visual" {{ ($employee?->medical?->disability_type ?? '') == 'Visual' ? 'selected' : '' }}>Visual</option>
                                                                    <option value="Hearing" {{ ($employee?->medical?->disability_type ?? '') == 'Hearing' ? 'selected' : '' }}>Hearing</option>
                                                                    <option value="Speech" {{ ($employee?->medical?->disability_type ?? '') == 'Speech' ? 'selected' : '' }}>Speech</option>
                                                                    <option value="Other" {{ ($employee?->medical?->disability_type ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
                                                                </select>
                                                            </div>
                                                        
                                                            <div class="col-md-4">
                                                                <label class="form-label">Blood Group</label>
                                                                <select name="blood_group" class="form-select" id="moreMedicalBloodGroupInput">
                                                                    <option value="" selected disabled>Select</option>
                                                                    <option value="A+" {{ ($employee?->medical?->blood_group ?? '') == 'A+' ? 'selected' : '' }}>A+</option>
                                                                    <option value="A-" {{ ($employee?->medical?->blood_group ?? '') == 'A-' ? 'selected' : '' }}>A-</option>
                                                                    <option value="B+" {{ ($employee?->medical?->blood_group ?? '') == 'B+' ? 'selected' : '' }}>B+</option>
                                                                    <option value="B-" {{ ($employee?->medical?->blood_group ?? '') == 'B-' ? 'selected' : '' }}>B-</option>
                                                                    <option value="AB+" {{ ($employee?->medical?->blood_group ?? '') == 'AB+' ? 'selected' : '' }}>AB+</option>
                                                                    <option value="AB-" {{ ($employee?->medical?->blood_group ?? '') == 'AB-' ? 'selected' : '' }}>AB-</option>
                                                                    <option value="O+" {{ ($employee?->medical?->blood_group ?? '') == 'O+' ? 'selected' : '' }}>O+</option>
                                                                    <option value="O-" {{ ($employee?->medical?->blood_group ?? '') == 'O-' ? 'selected' : '' }}>O-</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-12" id="moreMedicalDisabilityDescriptionContainer" style="{{ ($employee?->medical?->disability_type ?? '') === 'Other' ? '' : 'display:none;' }}">
                                                                <label class="form-label">Specify disability details <span class="text-danger">*</span></label>
                                                                <textarea name="disability_description" class="form-control" id="moreMedicalDisabilityDescriptionInput" rows="2" maxlength="1000"
                                                                    placeholder="Enter disability details">{{ $employee?->medical?->disability_description ?? '' }}</textarea>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label">Do you have any chronic disease? <span class="text-danger">*</span></label>
                                                                <div class="d-flex gap-2">
                                                                    <input class="btn-check" type="radio" name="has_chronic_disease"
                                                                        id="moreMedicalHasChronicDiseaseYes" value="yes" {{ $hasChronicValMedical === 'yes' ? 'checked' : '' }}>
                                                                    <label class="btn btn-outline-secondary option-chip m-0 flex-grow-1"
                                                                        for="moreMedicalHasChronicDiseaseYes">Yes</label>
                                                                    <input class="btn-check" type="radio" name="has_chronic_disease"
                                                                        id="moreMedicalHasChronicDiseaseNo" value="no" {{ $hasChronicValMedical === 'no' ? 'checked' : '' }}>
                                                                    <label class="btn btn-outline-secondary option-chip m-0 flex-grow-1"
                                                                        for="moreMedicalHasChronicDiseaseNo">No</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 mt-2" id="moreMedicalChronicDiseaseDescriptionContainer"
                                                                style="{{ $hasChronicValMedical === 'yes' ? '' : 'display:none;' }}">
                                                                <label class="form-label">Specify chronic disease <span class="text-danger">*</span></label>
                                                                <textarea name="chronic_disease_description" class="form-control" id="moreMedicalChronicDiseaseDescriptionInput"
                                                                    rows="2" maxlength="1000"
                                                                    placeholder="Describe the chronic condition">{{ $employee?->medical?->chronic_disease_description ?? '' }}</textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="more-sub-pane flex-grow-1 min-width-0" id="moreStepPane7">
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
                                                                         <input type="text" name="ref1_name" class="form-control" id="moreReferenceOneNameInput"
                                                                             value="{{ $employee?->references?->where('ref_number', 1)?->first()?->name ?? '' }}"
                                                                            placeholder="Enter name" maxlength="50">
                                                                     </div>
                                                                     <div class="col-6">
                                                                         <label class="form-label">Designation</label>
                                                                         <input type="text" name="ref1_designation" class="form-control" id="moreReferenceOneDesignationInput"
                                                                             value="{{ $employee?->references?->where('ref_number', 1)?->first()?->designation ?? '' }}"
                                                                            placeholder="Enter designation" maxlength="50">
                                                                     </div>
                                                                     <div class="col-6">
                                                                         <label class="form-label">Organization</label>
                                                                         <input type="text" name="ref1_organization" class="form-control" id="moreReferenceOneOrganizationInput"
                                                                             value="{{ $employee?->references?->where('ref_number', 1)?->first()?->organization ?? '' }}"
                                                                            placeholder="Enter organization" maxlength="100">
                                                                     </div>
                                                                     <div class="col-6">
                                                                         <label class="form-label">Contact No</label>
                                                                         <input type="text" name="ref1_contact" class="form-control contact-mask" id="moreReferenceOneContactNoInput"
                                                                             value="{{ $employee?->references?->where('ref_number', 1)?->first()?->contact_no ?? '' }}"
                                                                             placeholder="Enter contact number" maxlength="15" inputmode="numeric" autocomplete="tel">
                                                                     </div>
                                                                     <div class="col-6">
                                                                         <label class="form-label">Relationship</label>
                                                                         <select name="ref1_relationship" class="form-select" id="moreReferenceOneRelationshipInput">
                                                                             <option value="" selected disabled>Select</option>
                                                                             <option value="Family" {{ ($employee?->references?->where('ref_number', 1)?->first()?->relationship ?? '') == 'Family' ? 'selected' : '' }}>Family</option>
                                                                             <option value="Friend" {{ ($employee?->references?->where('ref_number', 1)?->first()?->relationship ?? '') == 'Friend' ? 'selected' : '' }}>Friend</option>
                                                                             <option value="Colleague" {{ ($employee?->references?->where('ref_number', 1)?->first()?->relationship ?? '') == 'Colleague' ? 'selected' : '' }}>Colleague</option>
                                                                            <option value="Academic" {{ ($employee?->references?->where('ref_number', 1)?->first()?->relationship ?? '') == 'Academic' ? 'selected' : '' }}>Academic</option>
                                                                             <option value="Professional" {{ ($employee?->references?->where('ref_number', 1)?->first()?->relationship ?? '') == 'Professional' ? 'selected' : '' }}>Professional</option>
                                                                             <option value="Other" {{ ($employee?->references?->where('ref_number', 1)?->first()?->relationship ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
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
                                                                         <input type="text" name="ref2_name" class="form-control" id="moreReferenceTwoNameInput"
                                                                             value="{{ $employee?->references?->where('ref_number', 2)?->first()?->name ?? '' }}"
                                                                            placeholder="Enter name" maxlength="50">
                                                                     </div>
                                                                     <div class="col-6">
                                                                         <label class="form-label">Designation</label>
                                                                         <input type="text" name="ref2_designation" class="form-control" id="moreReferenceTwoDesignationInput"
                                                                             value="{{ $employee?->references?->where('ref_number', 2)?->first()?->designation ?? '' }}"
                                                                            placeholder="Enter designation" maxlength="50">
                                                                     </div>
                                                                     <div class="col-6">
                                                                         <label class="form-label">Organization</label>
                                                                         <input type="text" name="ref2_organization" class="form-control" id="moreReferenceTwoOrganizationInput"
                                                                             value="{{ $employee?->references?->where('ref_number', 2)?->first()?->organization ?? '' }}"
                                                                            placeholder="Enter organization" maxlength="100">
                                                                     </div>
                                                                     <div class="col-6">
                                                                         <label class="form-label">Contact No</label>
                                                                         <input type="text" name="ref2_contact" class="form-control contact-mask" id="moreReferenceTwoContactNoInput"
                                                                             value="{{ $employee?->references?->where('ref_number', 2)?->first()?->contact_no ?? '' }}"
                                                                             placeholder="Enter contact number" maxlength="15" inputmode="numeric" autocomplete="tel">
                                                                     </div>
                                                                     <div class="col-6">
                                                                         <label class="form-label">Relationship</label>
                                                                         <select name="ref2_relationship" class="form-select" id="moreReferenceTwoRelationshipInput">
                                                                             <option value="" selected disabled>Select</option>
                                                                             <option value="Family" {{ ($employee?->references?->where('ref_number', 2)?->first()?->relationship ?? '') == 'Family' ? 'selected' : '' }}>Family</option>
                                                                             <option value="Friend" {{ ($employee?->references?->where('ref_number', 2)?->first()?->relationship ?? '') == 'Friend' ? 'selected' : '' }}>Friend</option>
                                                                             <option value="Colleague" {{ ($employee?->references?->where('ref_number', 2)?->first()?->relationship ?? '') == 'Colleague' ? 'selected' : '' }}>Colleague</option>
                                                                            <option value="Academic" {{ ($employee?->references?->where('ref_number', 2)?->first()?->relationship ?? '') == 'Academic' ? 'selected' : '' }}>Academic</option>
                                                                             <option value="Professional" {{ ($employee?->references?->where('ref_number', 2)?->first()?->relationship ?? '') == 'Professional' ? 'selected' : '' }}>Professional</option>
                                                                             <option value="Other" {{ ($employee?->references?->where('ref_number', 2)?->first()?->relationship ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
                                                                         </select>
                                                                     </div>
                                                                 </div>
                                                             </div>
                                                         </div>
                                                     </div>
                                                 </div>
                                            </div>
                                        </div>

                                        <div class="more-sub-pane flex-grow-1 min-width-0" id="moreStepPane8">
                                            @include('admin.employeeregistration.partials.steps.step-attachments')
                                        </div>
                                    </div>
                                </section>
                            </div>
                        </div>
