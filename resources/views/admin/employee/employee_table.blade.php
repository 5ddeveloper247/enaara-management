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
                <th>Employee ID</th>
                <th>TAS ID</th>
                <th>Employee No</th>
                <th>Category</th>
                <th>Image</th>
                <th>Name</th>
                <th>CNIC</th>
                <th>Nationality</th>
                <th>Gender</th>
                <th>Organization</th>
                <th>SBU</th>
                <th>Department</th>
                <th>Role</th>
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

        document.querySelectorAll('#employeeTable tbody tr').forEach(row => {
            const cells = row.querySelectorAll('td');
            if (!cells.length) return;

            const imgEl = cells[4]?.querySelector('img, .user-avatar');
            const avatarHtml = imgEl ? imgEl.outerHTML : '<div class="user-avatar">??</div>';
            const name = cells[5]?.textContent.trim() || '—';
            const empNo = cells[2]?.textContent.trim() || '—';
            const dept = cells[11]?.textContent.trim() || '—';
            const org = cells[9]?.textContent.trim() || '—';
            const verEl = cells[15]?.querySelector('.badge');
            const ver = verEl ? verEl.outerHTML : '<span class="text-muted small">—</span>';
            const viewBtn = cells[24]?.querySelector('button');
            const btnAttrs = viewBtn ? viewBtn.outerHTML : '';

            grid.insertAdjacentHTML('beforeend', `
        <div class="col-md-6 col-lg-4 col-xl-3">
            <div class="card border rounded-3 h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        ${avatarHtml}
                        <div>
                            <div class="fw-semibold small">${name}</div>
                            <small class="text-muted">${empNo}</small>
                        </div>
                    </div>
                    <div class="d-flex flex-column gap-1 mb-3">
                        <small><i class="bi bi-building me-1 text-muted"></i>${org}</small>
                        <small><i class="bi bi-diagram-3 me-1 text-muted"></i>${dept}</small>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                        ${ver}
                        ${btnAttrs}
                    </div>
                </div>
            </div>
        </div>`);
        });
    }
</script>
