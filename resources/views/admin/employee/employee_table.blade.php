<script>
    window.employeeDataUrl    = "{{ route('admin.employee.data') }}";
    window.employeeStatsUrl   = "{{ route('admin.employee.stats') }}";
    window.registerUrl        = "{{ route('admin.register.index') }}";
    window.employeeEditUrlBase = "{{ url('admin/employees') }}";
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

<div id="gridViewWrapper" class="d-none row g-3 p-3"></div>


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

        const gridFields = [
            { label: 'TAS ID', idx: 1 },
            { label: 'Employee No', idx: 3 },
            { label: 'Organization', idx: 4 },
            { label: 'SBU', idx: 5 },
            { label: 'Department', idx: 6 },
            { label: 'Category', idx: 7 },
            { label: 'CNIC', idx: 8 },
            { label: 'Nationality', idx: 9 },
            { label: 'Gender', idx: 10 },
            { label: 'Date of Joining', idx: 11 },
            { label: 'Designation', idx: 12 },
            { label: 'Email', idx: 14 },
            { label: 'Cell Number', idx: 15 },
            { label: 'Summary', idx: 16 },
            { label: 'Employment Type', idx: 17 },
            { label: 'Site Assignment', idx: 18 },
            { label: 'Vendor', idx: 19 },
            { label: 'Sync Status', idx: 20 },
            { label: 'Floor Access', idx: 21 },
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

            const imgEl = cells[0]?.querySelector('img, .user-avatar');
            const avatarHtml = imgEl ? imgEl.outerHTML : '<div class="user-avatar">??</div>';
            const nameEl = cells[0]?.querySelector('.employee-profile-name');
            const name = (nameEl && nameEl.textContent.trim()) || cells[0]?.textContent.trim().split(/\s+/).slice(0, 3).join(' ') || '—';
            const empNo = cells[3]?.textContent.trim() || '—';
            const nameTitle = name.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;');

            const verEl = cells[13]?.querySelector('.badge');
            const ver = verEl ? verEl.outerHTML : '<span class="text-muted small">—</span>';
            const viewBtn = cells[22]?.querySelector('button');
            const btnAttrs = viewBtn ? viewBtn.outerHTML : '';

            let detailsHtml = '';
            gridFields.forEach(({ label, idx }) => {
                const td = cells[idx];
                const val = td && td.innerHTML.trim()
                    ? td.innerHTML.trim()
                    : '<span class="text-muted">—</span>';
                detailsHtml += `
                    <div class="employee-grid-field mb-2 pb-2 border-bottom border-light">
                        <div class="employee-grid-field-label">${label}</div>
                        <div class="employee-grid-field-value text-break">${val}</div>
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
                    <div class="employee-grid-card-scroll flex-grow-1 mb-2">
                        ${detailsHtml}
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top flex-wrap gap-2 mt-auto">
                        <div class="d-flex align-items-center gap-1 flex-wrap">${ver}</div>
                        <div>${btnAttrs}</div>
                    </div>
                </div>
            </div>
        </div>`);
        });
    };
</script>
