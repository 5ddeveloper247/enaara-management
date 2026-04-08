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
        const textSafe = (val) => String(val ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
        const norm = (val, fallback = '—') => {
            if (val === null || val === undefined) return fallback;
            const s = String(val).trim();
            return s === '' || s.toLowerCase() === 'null' || s.toLowerCase() === 'undefined' ? fallback : s;
        };

        const compactFields = [
            { label: 'Department', key: 'department', icon: 'bi-diagram-3' },
            { label: 'Designation', key: 'designation', icon: 'bi-briefcase' },
            { label: 'Joining', key: 'join_date', icon: 'bi-calendar3' },
        ];

        const tableApi = window.employeeTableRef;
        if (!tableApi) return;
        const rowsData = tableApi.rows({ search: 'applied' }).data().toArray();
        rowsData.forEach((row) => {
            const name = norm(row.full_name);
            const empNo = norm(row.employee_code);
            const orgName = norm(row.organization);
            const category = norm(row.employment_category);
            const employmentType = norm(row.employment_type);
            const department = norm(row.department);
            const sbu = norm(row.sbu);
            const designation = norm(row.designation);
            const joinDate = norm(row.join_date);
            const verification = norm(row.verification_status);
            const initials = norm(row.initials, '??');
            const photoUrl = norm(row.photo_url, '');
            const dbId = norm(row.id, '');
            const tasId = norm(row.biometric_id);
            const syncStatus = norm(row.sync_status, 'Not Linked');
            const site = norm(row.site);
            const vendor = norm(row.vendor);
            const cnic = norm(row.cnic);
            const nationality = norm(row.nationality);
            const gender = norm(row.gender);
            const email = norm(row.email);
            const cell = norm(row.cell_no);
            const employeeType = norm(row.employee_type);
            const summary = norm(row.summary, `${name} - ${empNo}`);
            const employeeInfo = [department !== '—' ? department : '', empNo !== '—' ? empNo : ''].filter(Boolean).join(' - ') || '—';

            const avatarHtml = photoUrl && photoUrl !== '—'
                ? `<img src="${attrSafe(photoUrl)}" alt="${attrSafe(name)}" class="rounded-circle flex-shrink-0" style="width:36px;height:36px;object-fit:cover;">`
                : `<div class="user-avatar flex-shrink-0 d-flex align-items-center justify-content-center" style="width:36px;height:36px;font-size:0.75rem;">${textSafe(initials)}</div>`;

            const verificationBadge = verification === '—'
                ? '<span class="text-muted small">—</span>'
                : `<span class="badge bg-main px-2 rounded-1">${textSafe(verification)}</span>`;

            const btnAttrs = `<button type="button"
                class="btn btn-sm btn-outline-secondary employee-grid-view-btn view-employee-btn"
                data-bs-toggle="offcanvas"
                data-bs-target="#employeeDetailCanvas"
                title="View Details"
                data-db-id="${attrSafe(dbId)}"
                data-tas-id="${attrSafe(tasId)}"
                data-employee-id="${attrSafe(empNo)}"
                data-employee-name="${attrSafe(name)}"
                data-employee-avatar="${attrSafe(initials)}"
                data-photo-url="${attrSafe(photoUrl)}"
                data-employee-info="${attrSafe(employeeInfo)}"
                data-organization="${attrSafe(orgName)}"
                data-sbu="${attrSafe(sbu)}"
                data-department="${attrSafe(department)}"
                data-employment-type="${attrSafe(employmentType)}"
                data-employment-category="${attrSafe(category)}"
                data-employee-type="${attrSafe(employeeType)}"
                data-biometric-id="${attrSafe(tasId)}"
                data-sync-status="${attrSafe(syncStatus)}"
                data-site-assignment="${attrSafe(site)}"
                data-vendor="${attrSafe(vendor)}"
                data-floor-access="${row.floor_access ? '1' : '0'}"
                data-verification-status="${attrSafe(verification)}"
                data-email="${attrSafe(email)}"
                data-cell="${attrSafe(cell)}"
                data-cnic="${attrSafe(cnic)}"
                data-nationality="${attrSafe(nationality)}"
                data-gender="${attrSafe(gender)}"
                data-join-date="${attrSafe(joinDate)}"
                data-designation="${attrSafe(designation)}"
                data-summary="${attrSafe(summary)}"
            ><i class="bi bi-eye"></i></button>`;

            let detailsHtml = '';
            compactFields.forEach(({ label, key, icon }) => {
                const val = norm(row[key]);
                detailsHtml += `
                    <div class="mb-1">
                        <i class="bi ${icon} me-1 text-main small"></i>
                        <small class="text-muted me-1">${label}:</small>
                        <small class="employee-grid-field-value text-break">${textSafe(val)}</small>
                    </div>`;
            });

            grid.insertAdjacentHTML('beforeend', `
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card employee-grid-card border-1 rounded-3 h-100 position-relative">
                    <div class="card-body p-3">
                        <!-- Absolute Status Badge -->
                        <div class="position-absolute top-0 end-0 m-1" style="z-index: 5;">
                            ${verificationBadge}
                        </div>

                        <div class="d-flex align-items-start gap-2 mb-3 pe-4">
                            <div class="flex-shrink-0">${avatarHtml}</div>
                            <div class="flex-grow-1 min-w-0">
                                <h6 class="mb-0 fw-semibold small" style="white-space: normal; word-break: break-all;" title="${attrSafe(name)}">${textSafe(name)}</h6>
                                <small class="text-muted small d-block">${textSafe(empNo)}</small>
                                <small class="text-muted small d-block" style="white-space: normal; word-break: break-all;" title="${attrSafe(orgName)}">${textSafe(orgName)}</small>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-1 mb-2">
                            ${sbu !== '—' && sbu !== '-' ? `<span class="badge bg-light text-dark border">${textSafe(sbu)}</span>` : ''}
                            ${category !== '—' && category !== '-' ? `<span class="badge bg-light text-dark border">${textSafe(category)}</span>` : ''}
                            ${employmentType !== '—' && employmentType !== '-' ? `<span class="badge bg-light text-dark border">${textSafe(employmentType)}</span>` : ''}
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
