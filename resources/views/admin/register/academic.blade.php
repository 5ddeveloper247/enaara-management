{{-- STEP 7 layout with vertical left nav --}}
<div class="step" id="step-7">
    <div class="d-flex gap-3">

        {{-- Left Vertical Nav --}}
        <div class="d-flex flex-column gap-1" style="min-width:160px">
            <button type="button" class="btn btn-primary btn-sm text-start sub-nav-btn active-sub"
                data-target="s7-academic" onclick="showSubSection(this, 's7-academic')">Academic</button>
            <button type="button" class="btn btn-outline-secondary btn-sm text-start sub-nav-btn"
                data-target="s7-employment" onclick="showSubSection(this, 's7-employment')">Employment</button>
            <button type="button" class="btn btn-outline-secondary btn-sm text-start sub-nav-btn"
                data-target="s7-medical" onclick="showSubSection(this, 's7-medical')">Medical</button>
            <button type="button" class="btn btn-outline-secondary btn-sm text-start sub-nav-btn"
                data-target="s7-references" onclick="showSubSection(this, 's7-references')">References</button>

        </div>

        {{-- Right Content --}}
        <div class="flex-grow-1 min-w-0 overflow-auto">

            {{-- G: Academic --}}
            <div class="sub-section overflow-hidden" id="s7-academic">
                <div class="section-title">Section G — Academic Background / Professional Trainings / Certification
                    <small class="text-muted fw-normal">(Start from Recent)</small>
                </div>
                <div class="table-responsive mb-2">
                    <table class="table table-bordered align-middle">
                        <thead class="bg-main">
                            <tr>
                                <th>#</th>
                                <th>Degree / Certificate</th>
                                <th>Grade / Div / CGPA</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Field of Study</th>
                                <th>University / Board / Institute</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="academicTable">
                            <tr>
                                <td>1</td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                                <td><input type="date" class="form-control form-control-sm"></td>
                                <td><input type="date" class="form-control form-control-sm"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                                <td class="d-flex gap-1">
                                    <button type="button" class="action-btn border-0 text-success bg-success-subtle"
                                        onclick="saveAcademicRow(this)" title="Save">
                                        <i class="bi bi-floppy"></i>
                                    </button>
                                    <button type="button"
                                        class="action-btn border-0 text-danger bg-danger-subtle delete-shift-type"
                                        onclick="removeRow(this)" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addAcademicRow()">+ Add
                    Row</button>
                <div id="academicListing" class="row g-3 mt-3"></div>
            </div>


            {{-- H: Employment History --}}
            <div class="sub-section  d-none" id="s7-employment">
                <div class="section-title">Section H — Employment History <small class="text-muted fw-normal">(Start
                        from Recent)</small></div>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="bg-main">
                            <tr>
                                <th>#</th>
                                <th>Organization</th>
                                <th>Designation</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Salary</th>
                                <th>Reason for Leaving</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="employmentTable">
                            <tr>
                                <td>1</td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                                <td><input type="date" class="form-control form-control-sm"></td>
                                <td><input type="date" class="form-control form-control-sm"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
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
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addEmploymentRow()">+ Add
                    Row</button>

                <div id="employmentListing" class="row g-3 mt-3"></div>
            </div>

            {{-- I: Medical --}}
            <div class="sub-section px-3 pb-2 d-none" id="s7-medical">
                <div class="section-title">Section I — Medical Ailment / History / Disability</div>
                <div class="row g-3">
                    <div class="col-12"><label class="form-label">Last Medical Fitness Test — Date & Results</label>
                        <textarea class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Do you have any disability?</label>
                        <div class="d-flex gap-3 mt-1">
                            <div class="form-check"><input class="check-input" type="radio"
                                    name="disability"><label class="form-check-label">Yes</label></div>
                            <div class="form-check"><input class="check-input" type="radio"
                                    name="disability"><label class="form-check-label">No</label></div>
                        </div>
                    </div>
                    <div class="col-md-4"><label class="form-label">Blood Group</label><input type="text"
                            class="form-control"></div>
                    <div class="col-md-4">
                        <label class="form-label">If Yes</label>
                        <select class="form-select">
                            <option value="">Select</option>
                            <option>Partially Disabled</option>
                            <option>Fully Disabled at Home</option>
                        </select>
                    </div>
                    <div class="col-12"><label class="form-label">Disease / Disability Description</label>
                        <textarea class="form-control" rows="2"></textarea>
                    </div>
                </div>
            </div>

            {{-- J: References --}}
            <div class="sub-section px-3 pb-2 d-none" id="s7-references">
                <div class="section-title">Section J — References</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <p class="fw-semibold mb-2 text-primary">Reference 1</p>
                        <div class="row g-2">
                            <div class="col-12"><label class="form-label">Name</label><input type="text"
                                    class="form-control"></div>
                            <div class="col-12"><label class="form-label">Designation</label><input type="text"
                                    class="form-control"></div>
                            <div class="col-12"><label class="form-label">Organization</label><input type="text"
                                    class="form-control"></div>
                            <div class="col-12"><label class="form-label">Contact No</label><input type="tel"
                                    class="form-control"></div>
                            <div class="col-12">
                                <label class="form-label">Relationship</label>
                                <select class="form-select">
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
                            <div class="col-12"><label class="form-label">Name</label><input type="text"
                                    class="form-control"></div>
                            <div class="col-12"><label class="form-label">Designation</label><input type="text"
                                    class="form-control"></div>
                            <div class="col-12"><label class="form-label">Organization</label><input type="text"
                                    class="form-control"></div>
                            <div class="col-12"><label class="form-label">Contact No</label><input type="tel"
                                    class="form-control"></div>
                            <div class="col-12">
                                <label class="form-label">Relationship</label>
                                <select class="form-select">
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
    function showSubSection(btn, targetId) {
        document.querySelectorAll('.sub-section').forEach(s => s.classList.add('d-none'));
        document.querySelectorAll('.sub-nav-btn').forEach(b => {
            b.classList.remove('btn-primary');
            b.classList.add('btn-outline-secondary');
        });
        document.getElementById(targetId).classList.remove('d-none');
        btn.classList.remove('btn-outline-secondary');
        btn.classList.add('btn-primary');
    }

    function addEmploymentRow() {
        const tbody = document.getElementById('employmentTable');
        const count = tbody.rows.length + 1;
        tbody.insertAdjacentHTML('beforeend', `
            <tr>
                <td>${count}</td>
                <td><input type="text" class="form-control form-control-sm"></td>
                <td><input type="text" class="form-control form-control-sm"></td>
                <td><input type="date" class="form-control form-control-sm"></td>
                <td><input type="date" class="form-control form-control-sm"></td>
                <td><input type="text" class="form-control form-control-sm"></td>
                <td><input type="text" class="form-control form-control-sm"></td>
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
            </tr>`);
    }

    function saveAcademicRow(btn) {
        const row = btn.closest('tr');
        const cells = row.querySelectorAll('td');

        const degree = cells[1].querySelector('input').value.trim();
        const grade = cells[2].querySelector('input').value.trim();
        const startDate = cells[3].querySelector('input').value;
        const endDate = cells[4].querySelector('input').value;
        const field = cells[5].querySelector('input').value.trim();
        const institute = cells[6].querySelector('input').value.trim();

        if (!degree) {
            alert('Please enter a degree before saving.');
            return;
        }

        const initials = degree.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
        const fmt = d => d ? new Date(d).toLocaleDateString('en-GB', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        }) : '—';
        const id = 'academic-card-' + Date.now();

        const card = `
    <div class="col-md-6 col-lg-4" id="${id}">
        <div class="card border-1 rounded-3 h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center">
                        <div class="me-3 bg-main text-white rounded-2 d-flex align-items-center justify-content-center fw-bold"
                            style="width:45px;height:45px;font-size:1.1rem;">
                            ${initials}
                        </div>
                        <div>
                            <h6 class="mb-0 fw-semibold small">${degree}</h6>
                            <small class="text-muted small">${institute || '—'}</small>
                        </div>
                    </div>
                    <span class="badge bg-primary" style="font-size:10px;padding:4px 6px;">${grade || '—'}</span>
                </div>
                <div class="mb-2">
                    <i class="bi bi-book me-1 text-main small"></i>
                    <small class="text-muted small"><strong>Field:</strong> ${field || '—'}</small>
                </div>
                <div class="mb-2">
                    <i class="bi bi-calendar me-1 text-main small"></i>
                    <small class="text-muted small"><strong>Start:</strong> ${fmt(startDate)}</small>
                </div>
                <div class="mb-2">
                    <i class="bi bi-calendar-check me-1 text-main small"></i>
                    <small class="text-muted small"><strong>End:</strong> ${fmt(endDate)}</small>
                </div>
                <div class="mt-3 pt-3 border-top d-flex justify-content-end">
                    <button type="button" class="btn btn-sm btn-outline-danger"
                        onclick="removeAcademicCard('${id}')">
                        <i class="bi bi-trash me-1"></i>Remove
                    </button>
                </div>
            </div>
        </div>
    </div>`;

        document.getElementById('academicListing').insertAdjacentHTML('beforeend', card);
        row.querySelectorAll('input, select').forEach(el => el.disabled = true);
        btn.disabled = true;
    }

    function removeAcademicCard(id) {
        document.getElementById(id)?.remove();
    }

    function saveEmploymentRow(btn) {
        const row = btn.closest('tr');
        const cells = row.querySelectorAll('td');

        const org = cells[1].querySelector('input').value.trim();
        const desig = cells[2].querySelector('input').value.trim();
        const from = cells[3].querySelector('input').value;
        const to = cells[4].querySelector('input').value;
        const salary = cells[5].querySelector('input').value.trim();
        const reason = cells[6].querySelector('input').value.trim();

        if (!org) {
            alert('Please enter an organization before saving.');
            return;
        }

        const initials = org.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
        const fmt = d => d ? new Date(d).toLocaleDateString('en-GB', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        }) : '—';
        const id = 'employment-card-' + Date.now();

        const card = `
    <div class="col-md-6 col-lg-4" id="${id}">
        <div class="card border-1 rounded-3 h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center">
                        <div class="me-3 bg-main text-white rounded-2 d-flex align-items-center justify-content-center fw-bold"
                            style="width:45px;height:45px;font-size:1.1rem;">
                            ${initials}
                        </div>
                        <div>
                            <h6 class="mb-0 fw-semibold small">${org}</h6>
                            <small class="text-muted small">${desig || '—'}</small>
                        </div>
                    </div>
                    <span class="badge bg-success" style="font-size:10px;padding:4px 6px;">Past</span>
                </div>
                <div class="mb-2">
                    <i class="bi bi-calendar me-1 text-main small"></i>
                    <small class="text-muted small"><strong>From:</strong> ${fmt(from)}</small>
                </div>
                <div class="mb-2">
                    <i class="bi bi-calendar-check me-1 text-main small"></i>
                    <small class="text-muted small"><strong>To:</strong> ${fmt(to)}</small>
                </div>
                <div class="mb-2">
                    <i class="bi bi-currency-dollar me-1 text-main small"></i>
                    <small class="text-muted small"><strong>Salary:</strong> ${salary || '—'}</small>
                </div>
                <div class="mb-2">
                    <i class="bi bi-door-open me-1 text-main small"></i>
                    <small class="text-muted small"><strong>Reason:</strong> ${reason || '—'}</small>
                </div>
                <div class="mt-3 pt-3 border-top d-flex justify-content-end">
                    <button type="button" class="btn btn-sm btn-outline-danger"
                        onclick="removeEmploymentCard('${id}')">
                        <i class="bi bi-trash me-1"></i>Remove
                    </button>
                </div>
            </div>
        </div>
    </div>`;

        document.getElementById('employmentListing').insertAdjacentHTML('beforeend', card);
        row.querySelectorAll('input, select').forEach(el => el.disabled = true);
        btn.disabled = true;
    }

    function removeEmploymentCard(id) {
        document.getElementById(id)?.remove();
    }
</script>
