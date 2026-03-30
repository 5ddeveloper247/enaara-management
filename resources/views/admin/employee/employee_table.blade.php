<script>
    window.employeeDataUrl    = "{{ route('admin.employee.data') }}";
    window.employeeStatsUrl   = "{{ route('admin.employee.stats') }}";
    window.registerUrl        = "{{ route('admin.register.index') }}";
    window.employeeEditUrlBase = "{{ url('admin/employee') }}";
</script>

<div id="tableViewWrapper" class=" row g-3">
    <table id="employeeTable" class="display nowrap table table-striped" style="width:100%">
        <thead class="bg-main">
            <tr>
                <th>Profile</th>
                <th>Biometric ID</th>
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
            const avatarEl = row.querySelector('.user-avatar');
            const avatar = avatarEl ? avatarEl.textContent.trim() : '??';
            const name = cells[0].querySelector('.fw-semibold')?.textContent.trim() || '—';
            const info = cells[0].querySelector('small')?.textContent.trim() || '—';
            const bioEl = cells[1].querySelector('.badge');
            const bio = bioEl ? bioEl.textContent.trim() : '—';
            const typeEl = cells[2].querySelector('.badge');
            const type = typeEl ? typeEl.textContent.trim() : '—';
            const site = cells[3].textContent.trim();
            const vendor = cells[4].textContent.trim();
            const syncEl = cells[5].querySelector('.badge');
            const sync = syncEl ? syncEl.innerHTML : '—';
            const syncCls = syncEl ? syncEl.className : 'badge';
            const floorEl = cells[6].querySelector('.badge');
            const floor = floorEl ? floorEl.innerHTML : '<span class="text-muted small">—</span>';
            const viewBtn = cells[7].querySelector('button');
            const btnAttrs = viewBtn ? viewBtn.outerHTML : '';

            grid.insertAdjacentHTML('beforeend', `
        <div class="col-md-6 col-lg-4 col-xl-3">
            <div class="card border rounded-3 h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="user-avatar">${avatar}</div>
                        <div>
                            <div class="fw-semibold small">${name}</div>
                            <small class="text-muted">${info}</small>
                        </div>
                    </div>
                    <div class="d-flex flex-column gap-1 mb-3">
                        <small><i class="bi bi-fingerprint me-1 text-muted"></i><span class="badge bg-info rounded-1 px-2">${bio}</span></small>
                        <small><i class="bi bi-person-badge me-1 text-muted"></i>${type}</small>
                        <small><i class="bi bi-geo-alt me-1 text-muted"></i>${site}</small>
                        ${vendor !== '-' ? `<small><i class="bi bi-building me-1 text-muted"></i>${vendor}</small>` : ''}
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                        <span class="${syncCls}">${sync}</span>
                        ${floor}
                        ${btnAttrs}
                    </div>
                </div>
            </div>
        </div>`);
        });
    }
</script>
