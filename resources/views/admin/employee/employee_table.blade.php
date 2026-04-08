<script>
    window.employeeDataUrl    = "{{ route('admin.employee.data') }}";
    window.employeeStatsUrl   = "{{ route('admin.employee.stats') }}";
    window.registerUrl        = "{{ route('admin.register.index') }}";
    window.employeeEditUrlBase = "{{ url('admin/employee') }}";
</script>

<div id="tableViewWrapper" class="row g-3">
    <div class="col-12">
        <table id="employeeTable" class="display nowrap table table-striped table-hover w-100 mb-0">
            <thead class="bg-main">
                <tr>
                    <th>Profile</th>
                    <th>TAS ID</th>
                    <th>Employee ID</th>
                    <th>Employee No</th>
                    <th>Organization</th>
                    <th>SBU</th>
                    <th>Department</th>
                    <th>Category</th>
                    <th>CNIC</th>
                    <th>Nationality</th>
                    <th>Gender</th>
                    <th>Date of Joining</th>
                    <th>Designation</th>
                    <th>Verification Status</th>
                    <th>Email</th>
                    <th>Cell Number</th>
                    <th>Summary</th>
                    <th>Employment Type</th>
                    <th>Site Assignment</th>
                    <th>Vendor</th>
                    <th>Sync Status</th>
                    <th>Floor Access</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-transparent"></tbody>
        </table>
    </div>
</div>

<div id="gridViewWrapper" class="d-none row g-4 p-3"></div>


<script>
    document.addEventListener('DOMContentLoaded', () => switchView('buildgrid'));
    function switchView(view) {
        const tableWrapper = document.getElementById('tableViewWrapper');
        const gridWrapper = document.getElementById('gridViewWrapper');
        const btnTable = document.getElementById('btnTableView');
        const btnGrid = document.getElementById('btnGridView');

        const dtWrapper = document.querySelector('#employeeTable_wrapper');

        if (view === 'grid') {
            tableWrapper.classList.add('d-none');
            if (dtWrapper) dtWrapper.classList.add('d-none');
            gridWrapper.classList.remove('d-none');
            btnGrid.classList.add('active');
            btnTable.classList.remove('active');
            if (typeof window.buildEmployeeGrid === 'function') window.buildEmployeeGrid();
        } else {
            gridWrapper.classList.add('d-none');
            tableWrapper.classList.remove('d-none');
            if (dtWrapper) dtWrapper.classList.remove('d-none');
            btnTable.classList.add('active');
            btnGrid.classList.remove('active');
        }
    }   

    window.buildEmployeeGrid = function buildGrid() {
        const grid = document.getElementById('gridViewWrapper');
        grid.innerHTML = '';
        const attrSafe = (val) => String(val ?? '')
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;');

        const compactFields = [
            { label: 'Department', idx: 6, icon: 'bi-diagram-3' },
            { label: 'Designation', idx: 12, icon: 'bi-briefcase' },
            { label: 'Joining', idx: 11, icon: 'bi-calendar3' },
        ];

        document.querySelectorAll('#employeeTable tbody tr').forEach(row => {
            const cells = row.querySelectorAll('td');
            if (!cells.length) return;

            const imgEl = cells[0]?.querySelector('img, .user-avatar');
            const avatarHtml = imgEl ? imgEl.outerHTML : '<div class="user-avatar">??</div>';
            const nameEl = cells[0]?.querySelector('.employee-profile-name');
            const name = (nameEl && nameEl.textContent.trim()) || cells[0]?.textContent.trim().split(/\s+/).slice(0, 3).join(' ') || '—';
            const empNo = cells[3]?.textContent.trim() || '—';
            const nameTitle = name.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;');
            const orgName = cells[4]?.textContent.trim() || '—';
            const category = cells[7]?.textContent.trim() || '—';
            const employmentType = cells[17]?.textContent.trim() || '—';

            const verEl = cells[13]?.querySelector('.badge');
            const ver = verEl ? verEl.outerHTML : '<span class="text-muted small">—</span>';
            const viewBtn = cells[22]?.querySelector('.view-employee-btn, [data-bs-target="#employeeDetailCanvas"]');
            let btnAttrs = '';
            if (viewBtn) {
                const clonedBtn = viewBtn.cloneNode(true);
                // Ensure same detail canvas behavior in grid view.
                clonedBtn.setAttribute('type', 'button');
                clonedBtn.setAttribute('data-bs-toggle', 'offcanvas');
                clonedBtn.setAttribute('data-bs-target', '#employeeDetailCanvas');
                clonedBtn.classList.add('view-employee-btn');
                clonedBtn.classList.remove('border-0', 'text-white', 'btn-primary', 'btn-link');
                clonedBtn.classList.add('btn', 'btn-sm', 'btn-outline-secondary', 'employee-grid-view-btn');
                clonedBtn.innerHTML = '<i class="bi bi-eye"></i>';
                btnAttrs = clonedBtn.outerHTML;
            } else {
                const fallbackDepartment = cells[6]?.textContent.trim() || '—';
                const fallbackSbu = cells[5]?.textContent.trim() || '—';
                const fallbackCell = cells[15]?.textContent.trim() || '—';
                const fallbackEmail = cells[14]?.textContent.trim() || '—';
                const fallbackCnic = cells[8]?.textContent.trim() || '—';
                const fallbackNationality = cells[9]?.textContent.trim() || '—';
                const fallbackGender = cells[10]?.textContent.trim() || '—';
                const fallbackJoin = cells[11]?.textContent.trim() || '—';
                const fallbackDesignation = cells[12]?.textContent.trim() || '—';
                const fallbackVerification = cells[13]?.textContent.trim() || '—';
                const fallbackFloor = cells[21]?.textContent.trim() && cells[21]?.textContent.trim() !== '—' ? '1' : '0';
                btnAttrs = `<button type="button"
                    class="btn btn-sm btn-outline-secondary employee-grid-view-btn view-employee-btn"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#employeeDetailCanvas"
                    title="View Details"
                    data-db-id=""
                    data-tas-id="${attrSafe(cells[1]?.textContent.trim() || '—')}"
                    data-employee-id="${attrSafe(empNo)}"
                    data-employee-name="${attrSafe(name)}"
                    data-employee-avatar="${attrSafe((name || '??').split(/\s+/).slice(0, 2).map(s => s[0] || '').join('').toUpperCase() || '??')}"
                    data-photo-url=""
                    data-employee-info="${attrSafe([fallbackDepartment, empNo].filter(Boolean).join(' - '))}"
                    data-organization="${attrSafe(orgName)}"
                    data-sbu="${attrSafe(fallbackSbu)}"
                    data-department="${attrSafe(fallbackDepartment)}"
                    data-employment-type="${attrSafe(employmentType)}"
                    data-employment-category="${attrSafe(category)}"
                    data-employee-type="-"
                    data-biometric-id="${attrSafe(cells[1]?.textContent.trim() || '—')}"
                    data-sync-status="${attrSafe(cells[20]?.textContent.trim() || 'Not Linked')}"
                    data-site-assignment="${attrSafe(cells[18]?.textContent.trim() || '—')}"
                    data-vendor="${attrSafe(cells[19]?.textContent.trim() || '—')}"
                    data-floor-access="${fallbackFloor}"
                    data-verification-status="${attrSafe(fallbackVerification)}"
                    data-email="${attrSafe(fallbackEmail)}"
                    data-cell="${attrSafe(fallbackCell)}"
                    data-cnic="${attrSafe(fallbackCnic)}"
                    data-nationality="${attrSafe(fallbackNationality)}"
                    data-gender="${attrSafe(fallbackGender)}"
                    data-join-date="${attrSafe(fallbackJoin)}"
                    data-designation="${attrSafe(fallbackDesignation)}"
                    data-summary="${attrSafe(`${name} - ${empNo}`)}"
                ><i class="bi bi-eye"></i></button>`;
            }

            let detailsHtml = '';
            compactFields.forEach(({ label, idx, icon }) => {
                const td = cells[idx];
                const val = td && td.innerHTML.trim()
                    ? td.innerHTML.trim()
                    : '<span class="text-muted">—</span>';
                detailsHtml += `
                    <div class="mb-1">
                        <i class="bi ${icon} me-1 text-main small"></i>
                        <small class="text-muted me-1">${label}:</small>
                        <small class="employee-grid-field-value text-break">${val}</small>
                    </div>`;
            });

            grid.insertAdjacentHTML('beforeend', `
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card employee-grid-card border-1 rounded-3 h-100">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center min-w-0">
                                <div class="me-2 employee-grid-avatar-wrap">${avatarHtml}</div>
                                <div class="min-w-0">
                                    <h6 class="mb-0 fw-semibold small text-truncate" title="${nameTitle}">${name}</h6>
                                    <small class="text-muted small d-block">${empNo}</small>
                                    <small class="text-muted small d-block text-truncate">${orgName}</small>
                                </div>
                            </div>
                            <div>${ver}</div>
                        </div>

                        <div class="d-flex flex-wrap gap-1 mb-2">
                            <span class="badge bg-light text-dark border">${category}</span>
                            <span class="badge bg-light text-dark border">${employmentType}</span>
                        </div>

                        <div class="employee-grid-card-scroll">
                            ${detailsHtml}
                        </div>

                        <div class="mt-2 pt-2 border-top d-flex justify-content-end">
                            <div>${btnAttrs}</div>
                        </div>
                    </div>
                </div>
            </div>`);
        });
    };
</script>
