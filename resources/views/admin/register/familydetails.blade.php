{{-- STEP 6: Family Details --}}
<div class="step" id="step-6">
    <div class="section-title">Section F — Family Details <small class="text-muted fw-normal">(Wife/Husband /
            Children / Parents / Brothers / Sisters)</small>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="bg-main">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Gender</th>
                    <th>Date of Birth</th>
                    <th>Relation</th>
                    <th>Occupation</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="familyTable">
                <tr>
                    <td>1</td>
                    <td><input type="text" class="form-control form-control-sm"></td>
                    <td>
                        <select class="form-select form-select-sm">
                            <option value="">Select</option>
                            <option>Male</option>
                            <option>Female</option>
                        </select>
                    </td>
                    <td><input type="date" class="form-control form-control-sm"></td>
                    <td><input type="text" class="form-control form-control-sm"></td>
                    <td><input type="text" class="form-control form-control-sm"></td>
                    <td class="d-flex gap-1">
                        <button type="button" class="action-btn border-0 text-success bg-success-subtle"
                            onclick="saveFamilyRow(this)" title="Save">
                            <i class="bi bi-floppy"></i>
                        </button>
                        <button type="button"
                            class="action-btn border-0 text-danger bg-danger-subtle delete-shift-type"
                            onclick="removeRow(this)" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addFamilyRow()">+ Add Member</button>

    {{-- Saved Members Listing --}}
    <div id="familyListing" class="row g-3 mt-3"></div>
</div>


<script>
    function saveFamilyRow(btn) {
        const row = btn.closest('tr');
        const cells = row.querySelectorAll('td');

        const name = cells[1].querySelector('input').value.trim();
        const gender = cells[2].querySelector('select').value;
        const dob = cells[3].querySelector('input').value;
        const relation = cells[4].querySelector('input').value.trim();
        const occupation = cells[5].querySelector('input').value.trim();

        if (!name) {
            alert('Please enter a name before saving.');
            return;
        }

        // Initials
        const initials = name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();

        // Format date
        const dobFormatted = dob ? new Date(dob).toLocaleDateString('en-GB', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        }) : '—';

        const id = 'family-card-' + Date.now();

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
                        <h6 class="mb-0 fw-semibold small">${name}</h6>
                        <small class="text-muted small">${relation || '—'}</small>
                    </div>
                </div>
                <span class="badge bg-primary" style="font-size:10px;padding:4px 6px;">${gender || '—'}</span>
            </div>
            <div class="mb-2">
                <i class="bi bi-calendar me-1 text-main small"></i>
                <small class="text-muted small"><strong>DOB:</strong> ${dobFormatted}</small>
            </div>
            <div class="mb-2">
                <i class="bi bi-person-lines-fill me-1 text-main small"></i>
                <small class="text-muted small"><strong>Relation:</strong> ${relation || '—'}</small>
            </div>
            <div class="mb-2">
                <i class="bi bi-venus-mars me-1 text-main small"></i>
                <small class="text-muted small"><strong>Gender:</strong> ${gender || '—'}</small>
            </div>
            <div class="mb-2">
                <i class="bi bi-briefcase me-1 text-main small"></i>
                <small class="text-muted small"><strong>Occupation:</strong> ${occupation || '—'}</small>
            </div>
            <div class="mt-3 pt-3 border-top d-flex justify-content-end">
                <button type="button" class="btn btn-sm btn-outline-danger"
                    onclick="removeFamilyCard('${id}')">
                    <i class="bi bi-trash me-1"></i>Remove
                </button>
            </div>
        </div>
                </div>
            </div>`;

        document.getElementById('familyListing').insertAdjacentHTML('beforeend', card);

        // Disable the row inputs after saving
        row.querySelectorAll('input, select').forEach(el => el.disabled = true);
        btn.disabled = true;
    }

    function removeFamilyCard(id) {
        document.getElementById(id)?.remove();
    }
</script>
