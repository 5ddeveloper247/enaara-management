{{-- STEP 6: More (Contact, Family, Academic, Employment, Medical, References) --}}
<div class="step" id="step-6">
    <div class="d-flex gap-3">

        {{-- Left Vertical Nav --}}
        <div class="d-flex flex-column gap-1" style="min-width:160px">
            <button type="button" class="btn btn-primary btn-sm text-start sub-nav-btn active-sub"
                data-target="s6-contact" onclick="showSubSection(this, 's6-contact')">Contact</button>
            <button type="button" class="btn btn-outline-secondary btn-sm text-start sub-nav-btn"
                data-target="s6-family" onclick="showSubSection(this, 's6-family')">Family</button>
            <button type="button" class="btn btn-outline-secondary btn-sm text-start sub-nav-btn"
                data-target="s6-academic" onclick="showSubSection(this, 's6-academic')">Academic</button>
            <button type="button" class="btn btn-outline-secondary btn-sm text-start sub-nav-btn"
                data-target="s6-employment" onclick="showSubSection(this, 's6-employment')">Employment</button>
            <button type="button" class="btn btn-outline-secondary btn-sm text-start sub-nav-btn"
                data-target="s6-medical" onclick="showSubSection(this, 's6-medical')">Medical</button>
            <button type="button" class="btn btn-outline-secondary btn-sm text-start sub-nav-btn"
                data-target="s6-references" onclick="showSubSection(this, 's6-references')">References</button>
        </div>

        {{-- Right Content --}}
        <div class="flex-grow-1 min-w-0 overflow-auto">

            {{-- Contact --}}
            <div class="sub-section" id="s6-contact">
                <div class="section-title">Section D — Contact Details</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Residence Phone</label>
                        <input type="tel" name="residence_phone" class="form-control" maxlength="15">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">In Case of Emergency Contact No</label>
                        <input type="tel" name="emergency_contact" class="form-control" maxlength="15">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Cell No <span class="text-danger">*</span></label>
                        <input type="tel" name="cell_no" class="form-control" maxlength="15">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="contact_email" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Present Address <span class="text-danger">*</span></label>
                        <textarea name="present_address" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Permanent Address <span class="text-danger">*</span></label>
                        <textarea name="permanent_address" class="form-control" rows="2"></textarea>
                    </div>
                </div>
            </div>

            {{-- Family --}}
            <div class="sub-section d-none" id="s6-family">
                <div class="section-title">Section F — Family Details <small class="text-muted fw-normal">(Wife/Husband / Children / Parents / Brothers / Sisters)</small></div>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="bg-main">
                            <tr>
                                <th>#</th>
                                <th>Name <span class="text-danger">*</span></th>
                                <th>Gender <span class="text-danger">*</span></th>
                                <th>Date of Birth <span class="text-danger">*</span></th>
                                <th>Relation <span class="text-danger">*</span></th>
                                <th>Occupation</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="familyTable">
                            <tr>
                                <td>1</td>
                                <td><input type="text" class="form-control form-control-sm fm-name"></td>
                                <td>
                                    <select class="form-select form-select-sm fm-gender">
                                        <option value="">Select</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </td>
                                <td><input type="date" class="form-control form-control-sm fm-dob"></td>
                                <td><input type="text" class="form-control form-control-sm fm-relation"></td>
                                <td><input type="text" class="form-control form-control-sm fm-occupation"></td>
                                <td class="d-flex gap-1">
                                    <button type="button" class="action-btn border-0 text-success bg-success-subtle"
                                        onclick="saveFamilyRow(this)" title="Save">
                                        <i class="bi bi-floppy"></i>
                                    </button>
                                    <button type="button" class="action-btn border-0 text-danger bg-danger-subtle"
                                        onclick="removeRow(this)" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addFamilyRow()">+ Add Member</button>
                <div id="familyListing" class="row g-3 mt-3"></div>
            </div>

            {{-- Academic --}}
            <div class="sub-section d-none" id="s6-academic">
                <div class="section-title">Section G — Academic Background / Professional Trainings / Certification
                    <small class="text-muted fw-normal">(Start from Recent)</small>
                </div>
                <div class="table-responsive mb-2">
                    <table class="table table-bordered align-middle">
                        <thead class="bg-main">
                            <tr>
                                <th>#</th>
                                <th>Degree / Certificate <span class="text-danger">*</span></th>
                                <th>Grade / Div / CGPA <span class="text-danger">*</span></th>
                                <th>Start Date <span class="text-danger">*</span></th>
                                <th>End Date <span class="text-danger">*</span></th>
                                <th>Field of Study</th>
                                <th>University / Board / Institute</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="academicTable">
                            <tr>
                                <td>1</td>
                                <td><input type="text" class="form-control form-control-sm ac-degree"></td>
                                <td><input type="text" class="form-control form-control-sm ac-grade"></td>
                                <td><input type="date" class="form-control form-control-sm ac-start"></td>
                                <td><input type="date" class="form-control form-control-sm ac-end"></td>
                                <td><input type="text" class="form-control form-control-sm ac-field"></td>
                                <td><input type="text" class="form-control form-control-sm ac-institute"></td>
                                <td class="d-flex gap-1">
                                    <button type="button" class="action-btn border-0 text-success bg-success-subtle"
                                        onclick="saveAcademicRow(this)" title="Save">
                                        <i class="bi bi-floppy"></i>
                                    </button>
                                    <button type="button" class="action-btn border-0 text-danger bg-danger-subtle"
                                        onclick="removeRow(this)" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addAcademicRow()">+ Add Row</button>
                <div id="academicListing" class="row g-3 mt-3"></div>
            </div>

            {{-- Employment History --}}
            <div class="sub-section d-none" id="s6-employment">
                <div class="section-title">Section H — Employment History <small class="text-muted fw-normal">(Start from Recent)</small></div>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="bg-main">
                            <tr>
                                <th>#</th>
                                <th>Organization <span class="text-danger">*</span></th>
                                <th>Designation <span class="text-danger">*</span></th>
                                <th>From <span class="text-danger">*</span></th>
                                <th>To <span class="text-danger">*</span></th>
                                <th>Salary</th>
                                <th>Reason for Leaving</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="employmentTable">
                            <tr>
                                <td>1</td>
                                <td><input type="text" class="form-control form-control-sm em-org"></td>
                                <td><input type="text" class="form-control form-control-sm em-desig"></td>
                                <td><input type="date" class="form-control form-control-sm em-from"></td>
                                <td><input type="date" class="form-control form-control-sm em-to"></td>
                                <td><input type="text" class="form-control form-control-sm em-salary"></td>
                                <td><input type="text" class="form-control form-control-sm em-reason"></td>
                                <td class="d-flex gap-1">
                                    <button type="button" class="action-btn border-0 text-success bg-success-subtle"
                                        onclick="saveEmploymentRow(this)" title="Save">
                                        <i class="bi bi-floppy"></i>
                                    </button>
                                    <button type="button" class="action-btn border-0 text-danger bg-danger-subtle"
                                        onclick="removeRow(this)" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addEmploymentRow()">+ Add Row</button>
                <div id="employmentListing" class="row g-3 mt-3"></div>
            </div>

            {{-- Medical --}}
            <div class="sub-section px-3 pb-2 d-none" id="s6-medical">
                <div class="section-title">Section I — Medical Ailment / History / Disability</div>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Last Medical Fitness Test — Date & Results</label>
                        <textarea name="last_fitness_test" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Do you have any disability?</label>
                        <div class="d-flex gap-3 mt-1">
                            <div class="form-check">
                                <input class="check-input" type="radio" name="has_disability" value="yes">
                                <label class="form-check-label">Yes</label>
                            </div>
                            <div class="form-check">
                                <input class="check-input" type="radio" name="has_disability" value="no">
                                <label class="form-check-label">No</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Blood Group</label>
                        <input type="text" name="blood_group" class="form-control" maxlength="10">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">If Yes (Disability Type)</label>
                        <select name="disability_type" class="form-select">
                            <option value="">Select</option>
                            <option>Partially Disabled</option>
                            <option>Fully Disabled at Home</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Disease / Disability Description</label>
                        <textarea name="disability_description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
            </div>

            {{-- References --}}
            <div class="sub-section px-3 pb-2 d-none" id="s6-references">
                <div class="section-title">Section J — References</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <p class="fw-semibold mb-2 text-primary">Reference 1</p>
                        <div class="row g-2">
                            <div class="col-12"><label class="form-label">Name</label><input type="text" name="ref1_name" class="form-control"></div>
                            <div class="col-12"><label class="form-label">Designation</label><input type="text" name="ref1_designation" class="form-control"></div>
                            <div class="col-12"><label class="form-label">Organization</label><input type="text" name="ref1_organization" class="form-control"></div>
                            <div class="col-12"><label class="form-label">Contact No</label><input type="tel" name="ref1_contact" class="form-control" maxlength="15"></div>
                            <div class="col-12">
                                <label class="form-label">Relationship</label>
                                <select name="ref1_relationship" class="form-select">
                                    <option value="">Select</option>
                                    <option>Family</option>
                                    <option>Friend</option>
                                    <option>Academic</option>
                                    <option>Professional</option>
                                    <option>Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <p class="fw-semibold mb-2 text-primary">Reference 2</p>
                        <div class="row g-2">
                            <div class="col-12"><label class="form-label">Name</label><input type="text" name="ref2_name" class="form-control"></div>
                            <div class="col-12"><label class="form-label">Designation</label><input type="text" name="ref2_designation" class="form-control"></div>
                            <div class="col-12"><label class="form-label">Organization</label><input type="text" name="ref2_organization" class="form-control"></div>
                            <div class="col-12"><label class="form-label">Contact No</label><input type="tel" name="ref2_contact" class="form-control" maxlength="15"></div>
                            <div class="col-12">
                                <label class="form-label">Relationship</label>
                                <select name="ref2_relationship" class="form-select">
                                    <option value="">Select</option>
                                    <option>Family</option>
                                    <option>Friend</option>
                                    <option>Academic</option>
                                    <option>Professional</option>
                                    <option>Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    window.familyData      = window.familyData      || [];
    window.academicsData   = window.academicsData   || [];
    window.employmentsData = window.employmentsData || [];

    function showSubSection(btn, targetId) {
        document.querySelectorAll('#step-6 .sub-section').forEach(s => s.classList.add('d-none'));
        document.querySelectorAll('#step-6 .sub-nav-btn').forEach(b => {
            b.classList.remove('btn-primary');
            b.classList.add('btn-outline-secondary');
        });
        document.getElementById(targetId).classList.remove('d-none');
        btn.classList.remove('btn-outline-secondary');
        btn.classList.add('btn-primary');
    }

    function removeRow(btn) { btn.closest('tr').remove(); }

    function escCard(s) {
        return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;');
    }

    function nextSlot(arr) {
        for (let i = 0; i < arr.length; i++) {
            if (arr[i] == null) return i;
        }
        return arr.length;
    }

    function resetFamilyTableOneRow() {
        const tbody = document.getElementById('familyTable');
        if (!tbody) return;
        tbody.innerHTML = `
        <tr>
            <td>1</td>
            <td><input type="text" class="form-control form-control-sm fm-name"></td>
            <td><select class="form-select form-select-sm fm-gender"><option value="">Select</option><option value="Male">Male</option><option value="Female">Female</option></select></td>
            <td><input type="date" class="form-control form-control-sm fm-dob"></td>
            <td><input type="text" class="form-control form-control-sm fm-relation"></td>
            <td><input type="text" class="form-control form-control-sm fm-occupation"></td>
            <td class="d-flex gap-1">
                <button type="button" class="action-btn border-0 text-success bg-success-subtle" onclick="saveFamilyRow(this)" title="Save"><i class="bi bi-floppy"></i></button>
                <button type="button" class="action-btn border-0 text-danger bg-danger-subtle" onclick="removeRow(this)" title="Delete"><i class="bi bi-trash"></i></button>
            </td>
        </tr>`;
    }

    function resetAcademicTableOneRow() {
        const tbody = document.getElementById('academicTable');
        if (!tbody) return;
        tbody.innerHTML = `
        <tr>
            <td>1</td>
            <td><input type="text" class="form-control form-control-sm ac-degree"></td>
            <td><input type="text" class="form-control form-control-sm ac-grade"></td>
            <td><input type="date" class="form-control form-control-sm ac-start"></td>
            <td><input type="date" class="form-control form-control-sm ac-end"></td>
            <td><input type="text" class="form-control form-control-sm ac-field"></td>
            <td><input type="text" class="form-control form-control-sm ac-institute"></td>
            <td class="d-flex gap-1">
                <button type="button" class="action-btn border-0 text-success bg-success-subtle" onclick="saveAcademicRow(this)" title="Save"><i class="bi bi-floppy"></i></button>
                <button type="button" class="action-btn border-0 text-danger bg-danger-subtle" onclick="removeRow(this)" title="Delete"><i class="bi bi-trash"></i></button>
            </td>
        </tr>`;
    }

    function resetEmploymentTableOneRow() {
        const tbody = document.getElementById('employmentTable');
        if (!tbody) return;
        tbody.innerHTML = `
        <tr>
            <td>1</td>
            <td><input type="text" class="form-control form-control-sm em-org"></td>
            <td><input type="text" class="form-control form-control-sm em-desig"></td>
            <td><input type="date" class="form-control form-control-sm em-from"></td>
            <td><input type="date" class="form-control form-control-sm em-to"></td>
            <td><input type="text" class="form-control form-control-sm em-salary"></td>
            <td><input type="text" class="form-control form-control-sm em-reason"></td>
            <td class="d-flex gap-1">
                <button type="button" class="action-btn border-0 text-success bg-success-subtle" onclick="saveEmploymentRow(this)" title="Save"><i class="bi bi-floppy"></i></button>
                <button type="button" class="action-btn border-0 text-danger bg-danger-subtle" onclick="removeRow(this)" title="Delete"><i class="bi bi-trash"></i></button>
            </td>
        </tr>`;
    }

    function resetFamilyTableEmpty() {
        const tbody = document.getElementById('familyTable');
        if (tbody) tbody.innerHTML = '';
    }

    function resetAcademicTableEmpty() {
        const tbody = document.getElementById('academicTable');
        if (tbody) tbody.innerHTML = '';
    }

    function resetEmploymentTableEmpty() {
        const tbody = document.getElementById('employmentTable');
        if (tbody) tbody.innerHTML = '';
    }

    function appendFamilyCard(idx, m) {
        const name = m.name || '';
        const gender = m.gender || '';
        const dob = m.dob || '';
        const relation = m.relation || '';
        const occupation = m.occupation || '';
        const initials = name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase() || '—';
        const dobFormatted = dob ? new Date(dob).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '—';
        const id = 'family-card-' + idx;
        document.getElementById('familyListing').insertAdjacentHTML('beforeend', `
        <div class="col-md-6 col-lg-4" id="${id}">
            <div class="card border-1 rounded-3 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center">
                            <div class="me-3 bg-main text-white rounded-2 d-flex align-items-center justify-content-center fw-bold" style="width:45px;height:45px;font-size:1.1rem;">${escCard(initials)}</div>
                            <div><h6 class="mb-0 fw-semibold small">${escCard(name)}</h6><small class="text-muted small">${escCard(relation || '—')}</small></div>
                        </div>
                        <span class="badge bg-primary" style="font-size:10px;padding:4px 6px;">${escCard(gender || '—')}</span>
                    </div>
                    <div class="mb-2"><i class="bi bi-calendar me-1 text-main small"></i><small class="text-muted small"><strong>DOB:</strong> ${escCard(dobFormatted)}</small></div>
                    <div class="mb-2"><i class="bi bi-briefcase me-1 text-main small"></i><small class="text-muted small"><strong>Occupation:</strong> ${escCard(occupation || '—')}</small></div>
                    <div class="mt-3 pt-3 border-top d-flex justify-content-end gap-1">
                        <button type="button" class="btn btn-sm btn-outline-primary px-2" title="Edit" onclick="editFamilyCard('${id}', ${idx})"><i class="bi bi-pencil"></i></button>
                        <button type="button" class="btn btn-sm btn-outline-danger px-2" title="Remove" onclick="removeFamilyCard('${id}', ${idx})"><i class="bi bi-trash"></i></button>
                    </div>
                </div>
            </div>
        </div>`);
    }

    function appendAcademicCard(idx, a) {
        const degree = a.degree || '';
        const grade = a.grade_cgpa || '';
        const start = a.start_date || '';
        const end = a.end_date || '';
        const field = a.field_of_study || '';
        const inst = a.institute || '';
        const initials = degree.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase() || '—';
        const fmt = d => d ? new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '—';
        const id = 'academic-card-' + idx;
        document.getElementById('academicListing').insertAdjacentHTML('beforeend', `
        <div class="col-sm-6 col-xl-4" id="${id}">
            <div class="card border rounded-3 h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-start gap-2 mb-2">
                        <div class="bg-main text-white rounded-2 d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width:38px;height:38px;font-size:.9rem;">${escCard(initials)}</div>
                        <div class="min-w-0 flex-grow-1">
                            <h6 class="mb-0 fw-semibold small text-truncate" title="${escCard(degree)}">${escCard(degree)}</h6>
                            <small class="text-muted d-block text-truncate" title="${escCard(inst || '—')}">${escCard(inst || '—')}</small>
                        </div>
                        <span class="badge bg-primary flex-shrink-0" style="font-size:10px;">${escCard(grade || '—')}</span>
                    </div>
                    <div class="mb-1 text-truncate"><i class="bi bi-book me-1 text-muted small"></i><small class="text-muted"><strong>Field:</strong> ${escCard(field || '—')}</small></div>
                    <div class="mb-1"><i class="bi bi-calendar me-1 text-muted small"></i><small class="text-muted"><strong>Period:</strong> ${escCard(fmt(start))} – ${escCard(fmt(end))}</small></div>
                    <div class="mt-2 pt-2 border-top d-flex justify-content-end gap-1">
                        <button type="button" class="btn btn-sm btn-outline-primary px-2" title="Edit" onclick="editAcademicCard('${id}', ${idx})"><i class="bi bi-pencil"></i></button>
                        <button type="button" class="btn btn-sm btn-outline-danger px-2" title="Remove" onclick="removeAcademicCard('${id}', ${idx})"><i class="bi bi-trash"></i></button>
                    </div>
                </div>
            </div>
        </div>`);
    }

    function appendEmploymentCard(idx, e) {
        const org = e.organization || '';
        const desig = e.designation || '';
        const from = e.from_date || '';
        const to = e.to_date || '';
        const salary = e.salary || '';
        const reason = e.reason_for_leaving || '';
        const initials = org.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase() || '—';
        const fmt = d => d ? new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '—';
        const id = 'employment-card-' + idx;
        document.getElementById('employmentListing').insertAdjacentHTML('beforeend', `
        <div class="col-md-6 col-lg-4" id="${id}">
            <div class="card border-1 rounded-3 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center">
                            <div class="me-3 bg-main text-white rounded-2 d-flex align-items-center justify-content-center fw-bold" style="width:45px;height:45px;font-size:1.1rem;">${escCard(initials)}</div>
                            <div><h6 class="mb-0 fw-semibold small">${escCard(org)}</h6><small class="text-muted small">${escCard(desig || '—')}</small></div>
                        </div>
                        <span class="badge bg-success" style="font-size:10px;padding:4px 6px;">Past</span>
                    </div>
                    <div class="mb-2"><i class="bi bi-calendar me-1 text-main small"></i><small class="text-muted small"><strong>Period:</strong> ${escCard(fmt(from))} – ${escCard(fmt(to))}</small></div>
                    <div class="mb-2"><i class="bi bi-currency-dollar me-1 text-main small"></i><small class="text-muted small"><strong>Salary:</strong> ${escCard(salary || '—')}</small></div>
                    <div class="mb-2"><i class="bi bi-door-open me-1 text-main small"></i><small class="text-muted small"><strong>Reason:</strong> ${escCard(reason || '—')}</small></div>
                    <div class="mt-3 pt-3 border-top d-flex justify-content-end gap-1">
                        <button type="button" class="btn btn-sm btn-outline-primary px-2" title="Edit" onclick="editEmploymentCard('${id}', ${idx})"><i class="bi bi-pencil"></i></button>
                        <button type="button" class="btn btn-sm btn-outline-danger px-2" title="Remove" onclick="removeEmploymentCard('${id}', ${idx})"><i class="bi bi-trash"></i></button>
                    </div>
                </div>
            </div>
        </div>`);
    }

    // ── Family ──────────────────────────────────────────────────────────────
    function addFamilyRow() {
        const tbody = document.getElementById('familyTable');
        const count = tbody.rows.length + 1;
        tbody.insertAdjacentHTML('beforeend', `
        <tr>
            <td>${count}</td>
            <td><input type="text" class="form-control form-control-sm fm-name"></td>
            <td><select class="form-select form-select-sm fm-gender"><option value="">Select</option><option value="Male">Male</option><option value="Female">Female</option></select></td>
            <td><input type="date" class="form-control form-control-sm fm-dob"></td>
            <td><input type="text" class="form-control form-control-sm fm-relation"></td>
            <td><input type="text" class="form-control form-control-sm fm-occupation"></td>
            <td class="d-flex gap-1">
                <button type="button" class="action-btn border-0 text-success bg-success-subtle" onclick="saveFamilyRow(this)" title="Save"><i class="bi bi-floppy"></i></button>
                <button type="button" class="action-btn border-0 text-danger bg-danger-subtle" onclick="removeRow(this)" title="Delete"><i class="bi bi-trash"></i></button>
            </td>
        </tr>`);
    }

    function saveFamilyRow(btn) {
        const row        = btn.closest('tr');
        const name       = row.querySelector('.fm-name').value.trim();
        const gender     = row.querySelector('.fm-gender').value;
        const dob        = row.querySelector('.fm-dob').value;
        const relation   = row.querySelector('.fm-relation').value.trim();
        const occupation = row.querySelector('.fm-occupation').value.trim();

        const rowErrors = [];
        if (!name)     rowErrors.push('Name');
        if (!gender)   rowErrors.push('Gender');
        if (!dob)      rowErrors.push('Date of Birth');
        if (!relation) rowErrors.push('Relation');
        if (rowErrors.length) { alert('Please fill required fields: ' + rowErrors.join(', ')); return; }

        const idx = nextSlot(window.familyData);
        window.familyData[idx] = { name, gender, dob, relation, occupation };
        appendFamilyCard(idx, window.familyData[idx]);

        row.remove();
    }

    function editFamilyCard(id, idx) {
        const data = window.familyData[idx];
        if (!data) return;
        const esc = v => String(v || '').replace(/"/g, '&quot;');
        const tbody = document.getElementById('familyTable');
        const count = tbody.rows.length + 1;
        tbody.insertAdjacentHTML('beforeend', `
        <tr>
            <td>${count}</td>
            <td><input type="text" class="form-control form-control-sm fm-name" value="${esc(data.name)}"></td>
            <td><select class="form-select form-select-sm fm-gender">
                <option value="">Select</option>
                <option value="Male"${data.gender === 'Male' ? ' selected' : ''}>Male</option>
                <option value="Female"${data.gender === 'Female' ? ' selected' : ''}>Female</option>
            </select></td>
            <td><input type="date" class="form-control form-control-sm fm-dob" value="${esc(data.dob)}"></td>
            <td><input type="text" class="form-control form-control-sm fm-relation" value="${esc(data.relation)}"></td>
            <td><input type="text" class="form-control form-control-sm fm-occupation" value="${esc(data.occupation)}"></td>
            <td class="d-flex gap-1">
                <button type="button" class="action-btn border-0 text-success bg-success-subtle" onclick="saveFamilyRow(this)" title="Save"><i class="bi bi-floppy"></i></button>
                <button type="button" class="action-btn border-0 text-danger bg-danger-subtle" onclick="removeRow(this)" title="Delete"><i class="bi bi-trash"></i></button>
            </td>
        </tr>`);
        window.familyData[idx] = null;
        document.getElementById(id)?.remove();
        showSubSection(document.querySelector('[data-target="s6-family"]'), 's6-family');
    }

    function removeFamilyCard(id, idx) {
        document.getElementById(id)?.remove();
        window.familyData[idx] = null;
    }

    // ── Academic ─────────────────────────────────────────────────────────────
    function addAcademicRow() {
        const tbody = document.getElementById('academicTable');
        const count = tbody.rows.length + 1;
        tbody.insertAdjacentHTML('beforeend', `
        <tr>
            <td>${count}</td>
            <td><input type="text" class="form-control form-control-sm ac-degree"></td>
            <td><input type="text" class="form-control form-control-sm ac-grade"></td>
            <td><input type="date" class="form-control form-control-sm ac-start"></td>
            <td><input type="date" class="form-control form-control-sm ac-end"></td>
            <td><input type="text" class="form-control form-control-sm ac-field"></td>
            <td><input type="text" class="form-control form-control-sm ac-institute"></td>
            <td class="d-flex gap-1">
                <button type="button" class="action-btn border-0 text-success bg-success-subtle" onclick="saveAcademicRow(this)" title="Save"><i class="bi bi-floppy"></i></button>
                <button type="button" class="action-btn border-0 text-danger bg-danger-subtle" onclick="removeRow(this)" title="Delete"><i class="bi bi-trash"></i></button>
            </td>
        </tr>`);
    }

    function saveAcademicRow(btn) {
        const row    = btn.closest('tr');
        const degree = row.querySelector('.ac-degree').value.trim();
        const grade  = row.querySelector('.ac-grade').value.trim();
        const start  = row.querySelector('.ac-start').value;
        const end    = row.querySelector('.ac-end').value;
        const field  = row.querySelector('.ac-field').value.trim();
        const inst   = row.querySelector('.ac-institute').value.trim();

        const acErrors = [];
        if (!degree) acErrors.push('Degree');
        if (!grade)  acErrors.push('Grade / CGPA');
        if (!start)  acErrors.push('Start Date');
        if (!end)    acErrors.push('End Date');
        if (acErrors.length) { alert('Please fill required fields: ' + acErrors.join(', ')); return; }

        const idx = window.academicsData.length;
        window.academicsData.push({ degree, grade_cgpa: grade, start_date: start, end_date: end, field_of_study: field, institute: inst });

        const initials = degree.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
        const fmt = d => d ? new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '—';
        const id  = 'academic-card-' + idx;

        document.getElementById('academicListing').insertAdjacentHTML('beforeend', `
        <div class="col-sm-6 col-xl-4" id="${id}">
            <div class="card border rounded-3 h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-start gap-2 mb-2">
                        <div class="bg-main text-white rounded-2 d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width:38px;height:38px;font-size:.9rem;">${initials}</div>
                        <div class="min-w-0 flex-grow-1">
                            <h6 class="mb-0 fw-semibold small text-truncate" title="${degree}">${degree}</h6>
                            <small class="text-muted d-block text-truncate" title="${inst || '—'}">${inst || '—'}</small>
                        </div>
                        <span class="badge bg-primary flex-shrink-0" style="font-size:10px;">${grade || '—'}</span>
                    </div>
                    <div class="mb-1 text-truncate"><i class="bi bi-book me-1 text-muted small"></i><small class="text-muted"><strong>Field:</strong> ${field || '—'}</small></div>
                    <div class="mb-1"><i class="bi bi-calendar me-1 text-muted small"></i><small class="text-muted"><strong>Period:</strong> ${fmt(start)} – ${fmt(end)}</small></div>
                    <div class="mt-2 pt-2 border-top d-flex justify-content-end gap-1">
                        <button type="button" class="btn btn-sm btn-outline-primary px-2" title="Edit" onclick="editAcademicCard('${id}', ${idx})"><i class="bi bi-pencil"></i></button>
                        <button type="button" class="btn btn-sm btn-outline-danger px-2" title="Remove" onclick="removeAcademicCard('${id}', ${idx})"><i class="bi bi-trash"></i></button>
                    </div>
                </div>
            </div>
        </div>`);

        row.remove();
    }

    function editAcademicCard(id, idx) {
        const data = window.academicsData[idx];
        if (!data) return;
        const esc  = v => String(v || '').replace(/"/g, '&quot;');
        const tbody = document.getElementById('academicTable');
        const count = tbody.rows.length + 1;
        tbody.insertAdjacentHTML('beforeend', `
        <tr>
            <td>${count}</td>
            <td><input type="text" class="form-control form-control-sm ac-degree" value="${esc(data.degree)}"></td>
            <td><input type="text" class="form-control form-control-sm ac-grade" value="${esc(data.grade_cgpa)}"></td>
            <td><input type="date" class="form-control form-control-sm ac-start" value="${esc(data.start_date)}"></td>
            <td><input type="date" class="form-control form-control-sm ac-end" value="${esc(data.end_date)}"></td>
            <td><input type="text" class="form-control form-control-sm ac-field" value="${esc(data.field_of_study)}"></td>
            <td><input type="text" class="form-control form-control-sm ac-institute" value="${esc(data.institute)}"></td>
            <td class="d-flex gap-1">
                <button type="button" class="action-btn border-0 text-success bg-success-subtle" onclick="saveAcademicRow(this)" title="Save"><i class="bi bi-floppy"></i></button>
                <button type="button" class="action-btn border-0 text-danger bg-danger-subtle" onclick="removeRow(this)" title="Delete"><i class="bi bi-trash"></i></button>
            </td>
        </tr>`);
        window.academicsData[idx] = null;
        document.getElementById(id)?.remove();
        showSubSection(document.querySelector('[data-target="s6-academic"]'), 's6-academic');
    }

    function removeAcademicCard(id, idx) {
        document.getElementById(id)?.remove();
        window.academicsData[idx] = null;
    }

    // ── Employment History ───────────────────────────────────────────────────
    function addEmploymentRow() {
        const tbody = document.getElementById('employmentTable');
        const count = tbody.rows.length + 1;
        tbody.insertAdjacentHTML('beforeend', `
        <tr>
            <td>${count}</td>
            <td><input type="text" class="form-control form-control-sm em-org"></td>
            <td><input type="text" class="form-control form-control-sm em-desig"></td>
            <td><input type="date" class="form-control form-control-sm em-from"></td>
            <td><input type="date" class="form-control form-control-sm em-to"></td>
            <td><input type="text" class="form-control form-control-sm em-salary"></td>
            <td><input type="text" class="form-control form-control-sm em-reason"></td>
            <td class="d-flex gap-1">
                <button type="button" class="action-btn border-0 text-success bg-success-subtle" onclick="saveEmploymentRow(this)" title="Save"><i class="bi bi-floppy"></i></button>
                <button type="button" class="action-btn border-0 text-danger bg-danger-subtle" onclick="removeRow(this)" title="Delete"><i class="bi bi-trash"></i></button>
            </td>
        </tr>`);
    }

    function saveEmploymentRow(btn) {
        const row    = btn.closest('tr');
        const org    = row.querySelector('.em-org').value.trim();
        const desig  = row.querySelector('.em-desig').value.trim();
        const from   = row.querySelector('.em-from').value;
        const to     = row.querySelector('.em-to').value;
        const salary = row.querySelector('.em-salary').value.trim();
        const reason = row.querySelector('.em-reason').value.trim();

        const emErrors = [];
        if (!org)   emErrors.push('Organization');
        if (!desig) emErrors.push('Designation');
        if (!from)  emErrors.push('From Date');
        if (!to)    emErrors.push('To Date');
        if (emErrors.length) { alert('Please fill required fields: ' + emErrors.join(', ')); return; }

        const idx = nextSlot(window.employmentsData);
        window.employmentsData[idx] = { organization: org, designation: desig, from_date: from, to_date: to, salary, reason_for_leaving: reason };
        appendEmploymentCard(idx, window.employmentsData[idx]);

        row.remove();
    }

    function editEmploymentCard(id, idx) {
        const data = window.employmentsData[idx];
        if (!data) return;
        const esc = v => String(v || '').replace(/"/g, '&quot;');
        const tbody = document.getElementById('employmentTable');
        const count = tbody.rows.length + 1;
        tbody.insertAdjacentHTML('beforeend', `
        <tr>
            <td>${count}</td>
            <td><input type="text" class="form-control form-control-sm em-org" value="${esc(data.organization)}"></td>
            <td><input type="text" class="form-control form-control-sm em-desig" value="${esc(data.designation)}"></td>
            <td><input type="date" class="form-control form-control-sm em-from" value="${esc(data.from_date)}"></td>
            <td><input type="date" class="form-control form-control-sm em-to" value="${esc(data.to_date)}"></td>
            <td><input type="text" class="form-control form-control-sm em-salary" value="${esc(data.salary)}"></td>
            <td><input type="text" class="form-control form-control-sm em-reason" value="${esc(data.reason_for_leaving)}"></td>
            <td class="d-flex gap-1">
                <button type="button" class="action-btn border-0 text-success bg-success-subtle" onclick="saveEmploymentRow(this)" title="Save"><i class="bi bi-floppy"></i></button>
                <button type="button" class="action-btn border-0 text-danger bg-danger-subtle" onclick="removeRow(this)" title="Delete"><i class="bi bi-trash"></i></button>
            </td>
        </tr>`);
        window.employmentsData[idx] = null;
        document.getElementById(id)?.remove();
        showSubSection(document.querySelector('[data-target="s6-employment"]'), 's6-employment');
    }

    function removeEmploymentCard(id, idx) {
        document.getElementById(id)?.remove();
        window.employmentsData[idx] = null;
    }
</script>
