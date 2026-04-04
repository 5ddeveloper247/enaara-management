<script>
    window.employeeDataUrl    = "{{ route('admin.employee.data') }}";
    window.employeeStatsUrl   = "{{ route('admin.employee.stats') }}";
    window.registerUrl        = "{{ route('admin.register.index') }}";
    window.employeeEditUrlBase = "{{ url('admin/employee') }}";
</script>

<div id="tableViewWrapper" class="row g-3">
    <div class="col-12">
    <table id="employeeTable" class="display nowrap table table-striped w-100 mb-0">
        <thead class="bg-main">
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>TAS ID</th>
                <th>Employee ID</th>
                <th>Employee No</th>
                <th>Organization</th>
                <th>SBU</th>
                <th>Department</th>
                <th>Role</th>
                <th>Category</th>
                <th>CNIC</th>
                <th>Nationality</th>
                <th>Gender</th>
                <th>Date of Joining</th>
                <th>Designation</th>
                <th>Verification Status</th>
                <th>Email</th>
                <th>Cell Number</th>
                <th>Profile</th>
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
            buildGrid();
        } else {
            gridWrapper.classList.add('d-none');
            tableWrapper.classList.remove('d-none');
            if (dtWrapper) dtWrapper.classList.remove('d-none');
            btnTable.classList.add('active');
            btnGrid.classList.remove('active');
        }
    }   

    function buildGrid() {
        const grid = document.getElementById('gridViewWrapper');
        grid.innerHTML = '';

        const gridFields = [
            { label: 'TAS ID', idx: 2 },
            { label: 'Employee ID', idx: 3 },
            { label: 'Employee No', idx: 4 },
            { label: 'Organization', idx: 5 },
            { label: 'SBU', idx: 6 },
            { label: 'Department', idx: 7 },
            { label: 'Role', idx: 8 },
            { label: 'Category', idx: 9 },
            { label: 'CNIC', idx: 10 },
            { label: 'Nationality', idx: 11 },
            { label: 'Gender', idx: 12 },
            { label: 'Date of Joining', idx: 13 },
            { label: 'Designation', idx: 14 },
            { label: 'Email', idx: 16 },
            { label: 'Cell Number', idx: 17 },
            { label: 'Profile', idx: 18 },
            { label: 'Employment Type', idx: 19 },
            { label: 'Site Assignment', idx: 20 },
            { label: 'Vendor', idx: 21 },
            { label: 'Sync Status', idx: 22 },
            { label: 'Floor Access', idx: 23 },
        ];

        document.querySelectorAll('#employeeTable tbody tr').forEach(row => {
            const cells = row.querySelectorAll('td');
            if (!cells.length) return;

            const imgEl = cells[0]?.querySelector('img, .user-avatar');
            const avatarHtml = imgEl ? imgEl.outerHTML : '<div class="user-avatar">??</div>';
            const name = cells[1]?.textContent.trim() || '—';
            const empNo = cells[4]?.textContent.trim() || '—';
            const nameTitle = name.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;');

            const verEl = cells[15]?.querySelector('.badge');
            const ver = verEl ? verEl.outerHTML : '<span class="text-muted small">—</span>';
            const viewBtn = cells[24]?.querySelector('button');
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
            <div class="card border rounded-3 h-100">
                <div class="card-body p-3 d-flex flex-column">
                    <div class="d-flex align-items-start gap-2 mb-2">
                        ${avatarHtml}
                        <div class="min-w-0 flex-grow-1">
                            <div class="fw-semibold small text-truncate" title="${nameTitle}">${name}</div>
                            <small class="text-muted">${empNo}</small>
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
    }
</script>
