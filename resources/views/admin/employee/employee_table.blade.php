<script>
    window.employeeDataUrl = "{{ route('admin.employee.data') }}";
    window.employeeStatsUrl = "{{ route('admin.employee.stats') }}";
    window.registerUrl = "{{ route('admin.register.index') }}";
    window.employeeEditUrlBase = "{{ url('admin/employees') }}";
    window.outsourcedEmployeeDataUrl = "{{ route('admin.outsourced_employee.data') }}";
    window.outsourcedEmployeeStoreUrl = "{{ route('admin.outsourced_employee.store') }}";
    window.outsourcedEmployeeShowUrlBase = "{{ url('admin/outsourced-employees') }}";
    window.viewerEmployeeScope = @json($viewerEmployeeScope ?? ['restricted' => false]);
</script>

{{--
<div id="employeeListingTabsTemplate" class="d-none">
    <ul class="nav nav-pills employee-listing-tabs gap-2 mb-0" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link py-1 px-2" data-bs-toggle="pill" data-bs-target="#total-workforce" type="button" role="tab">
                <i class="bi bi-people-fill me-1"></i>Total Workforce
                <span class="badge bg-light text-dark ms-1" id="totalWorkforceBadge">0</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link py-1 px-2" data-bs-toggle="pill" data-bs-target="#biometric-sync" type="button" role="tab">
                <i class="bi bi-fingerprint me-1"></i>Biometric Sync Status
                <span class="badge bg-light text-dark ms-1" id="biometricSyncBadge">0</span>
            </button>
        </li>
    </ul>
</div>
--}}

{{-- <div id="tableViewWrapper" class="row g-3">
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
                <th>Status</th>
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
</div> --}}

@include('admin.outsourced-employee.table')

<div id="gridViewWrapper" class="d-none row g-3 p-3"></div>

<script>
    window.employeeDataUrl = "{{ route('admin.employee.data') }}";
    window.employeeStatsUrl = "{{ route('admin.employee.stats') }}";
    window.registerUrl = "{{ route('admin.register.index') }}";
    window.employeeEditUrlBase = "{{ url('admin/employees') }}";
    window.outsourcedEmployeeDataUrl = "{{ route('admin.outsourced_employee.data') }}";
    window.outsourcedEmployeeStoreUrl = "{{ route('admin.outsourced_employee.store') }}";
    window.outsourcedEmployeeShowUrlBase = "{{ url('admin/outsourced-employees') }}";
    window.viewerEmployeeScope = @json($viewerEmployeeScope ?? ['restricted' => false]);
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
                    <th>Role</th>
                    <th>Grade</th>
                    <th>Status</th>
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

@include('admin.outsourced-employee.table')

<div id="gridViewWrapper" class="d-none row g-3 p-3"></div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        switchView('table');
    });

    function switchView(view) {
        const tableWrapper = document.getElementById('tableViewWrapper');
        const gridWrapper = document.getElementById('gridViewWrapper');
        const btnTable = document.getElementById('btnTableView');
        const btnGrid = document.getElementById('btnGridView');
        const dtWrapper = document.querySelector('#employeeTable_wrapper');

        if (view === 'grid') {
            if (tableWrapper) tableWrapper.classList.add('d-none');
            if (dtWrapper) dtWrapper.classList.add('d-none');
            if (gridWrapper) gridWrapper.classList.remove('d-none');
            if (btnGrid) btnGrid.classList.add('active');
            if (btnTable) btnTable.classList.remove('active');
            if (typeof window.buildEmployeeGrid === 'function') window.buildEmployeeGrid();
        } else {
            if (gridWrapper) gridWrapper.classList.add('d-none');
            if (tableWrapper) tableWrapper.classList.remove('d-none');
            if (dtWrapper) dtWrapper.classList.remove('d-none');
            if (btnTable) btnTable.classList.add('active');
            if (btnGrid) btnGrid.classList.remove('active');
            if (window.employeeTableRef) window.employeeTableRef.columns.adjust();
        }
    }

    function norm(val, fallback = '—') {
        if (val === null || val === undefined || val === '') return fallback;
        if (typeof val === 'string' && (val.trim() === '-' || val.trim() === '—')) return fallback;
        return val;
    }

    function textSafe(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function attrSafe(str) {
        if (!str) return '';
        return String(str).replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    window.buildEmployeeGrid = function() {
        const grid = document.getElementById('gridViewWrapper');
        if (!grid) return;

        grid.innerHTML = '';

        const tableApi = window.employeeTableRef;
        if (!tableApi) {
            console.warn("Table API not initialized yet");
            return;
        }

        const dataCount = tableApi.rows({
            search: 'applied'
        }).count();

        if (dataCount === 0) {
            grid.innerHTML = `
                <div class="col-12">
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-people display-4 opacity-25 d-block mb-3"></i>
                        <div class="fw-semibold">No matching employees found.</div>
                    </div>
                </div>`;
            return;
        }

        tableApi.rows({
            search: 'applied'
        }).every(function() {
            const rowData = this.data();
            const rowNode = this.node();
            const cells = rowNode ? rowNode.cells : null;

            // ── Core values ──────────────────────────────────────────
            const fullName = norm(rowData.full_name, 'Unknown');
            const employeeId = norm(rowData.employee_code, '—');
            const role = norm(rowData.role, '—');
            const sbu = norm(rowData.sbu);
            const dept = norm(rowData.department);
            const category = norm(rowData.employment_category);
            const type = norm(rowData.employment_type);
            const tasId = norm(rowData.biometric_id);
            const cnic = norm(rowData.cnic);
            const email = norm(rowData.email);
            const floors = Array.isArray(rowData.assigned_floor_names) ?
                rowData.assigned_floor_names.filter(Boolean).join('||') : '';
            const info = [dept !== '—' ? dept : '', employeeId !== '—' ? employeeId : '']
                .filter(Boolean).join(' - ') || '—';

            // ── Initials ─────────────────────────────────────────────
            const initials = (rowData.full_name || '??')
                .split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);

            // ── Status badge ─────────────────────────────────────────
            let verificationBadge;
            if (cells && cells.length >= 24) {
                verificationBadge = cells[14].innerHTML;
            } else {
                const status = rowData.employee_status || '—';
                const bgClass = status === 'Active' ? 'bg-success' :
                    status === 'Suspend' ? 'bg-warning text-dark' :
                    status === 'Terminated' ? 'bg-danger' :
                    'bg-secondary';
                verificationBadge =
                    `<span class="badge ${bgClass}" style="font-size:10px;padding:4px 8px;">${status}</span>`;
            }

            // ── Action button ─────────────────────────────────────────
            let actionBtnHtml;
            const summary = norm(rowData.summary, '') || [norm(rowData.full_name, ''), employeeId !== '—' ? employeeId : '']
                .filter(Boolean).join(' - ') || '-';
            if (cells && cells.length >= 24) {
                // Restyle the existing button to outline + full width
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = cells[23].innerHTML;
                const existingBtn = tempDiv.querySelector('button');
                if (existingBtn) {
                    existingBtn.classList.remove('btn-primary');
                    existingBtn.classList.add('btn-outline-primary', 'flex-grow-1');
                    existingBtn.innerHTML = '<i class="bi bi-eye me-1"></i> View Details';
                }
                actionBtnHtml = tempDiv.innerHTML;
            } else {
                actionBtnHtml = `
                    <button type="button"
                        class="btn btn-sm btn-outline-primary flex-grow-1 view-employee-btn"
                        title="View Details"
                        data-bs-toggle="offcanvas"
                        data-bs-target="#employeeDetailCanvas"
                        data-db-id="${rowData.id || ''}"
                        data-tas-id="${attrSafe(norm(rowData.biometric_id))}"
                        data-employee-id="${attrSafe(employeeId)}"
                        data-employee-name="${attrSafe(rowData.full_name)}"
                        data-employee-avatar="${attrSafe(rowData.initials || '??')}"
                        data-photo-url="${attrSafe(rowData.photo_url || '')}"
                        data-employee-info="${attrSafe(info)}"
                        data-organization="${attrSafe(rowData.organization)}"
                        data-sbu="${attrSafe(rowData.sbu)}"
                        data-department="${attrSafe(rowData.department)}"
                        data-employment-type="${attrSafe(rowData.employment_type)}"
                        data-employment-category="${attrSafe(rowData.employment_category)}"
                        data-employee-type="${attrSafe(rowData.employee_type)}"
                        data-biometric-id="${attrSafe(norm(rowData.biometric_id))}"
                        data-sync-status="${attrSafe(norm(rowData.sync_status, 'Not Linked'))}"
                        data-site-assignment="${attrSafe(norm(rowData.site))}"
                        data-vendor="${attrSafe(norm(rowData.vendor))}"
                        data-floor-access="${rowData.floor_access ? '1' : '0'}"
                        data-employee-status="${rowData.employee_status || '-'}"
                        data-assigned-floors="${attrSafe(floors)}"
                        data-email="${attrSafe(rowData.email)}"
                        data-cell="${attrSafe(rowData.cell_no)}"
                        data-cnic="${attrSafe(rowData.cnic)}"
                        data-nationality="${attrSafe(norm(rowData.nationality))}"
                        data-gender="${attrSafe(norm(rowData.gender))}"
                        data-join-date="${attrSafe(norm(rowData.join_date))}"
                        data-designation="${attrSafe(rowData.designation)}"
                        data-summary="${attrSafe(summary)}">
                        <i class="bi bi-eye me-1"></i> View Details
                    </button>`;
            }

           // ── Detail chips ──────────────────────────────────────────
const details = [
    { icon: 'bi-building',    label: 'SBU',        val: sbu },
    { icon: 'bi-diagram-3',   label: 'Department', val: dept },
    { icon: 'bi-person-badge',label: 'Category',   val: category },
    { icon: 'bi-briefcase',   label: 'Type',       val: type },
    { icon: 'bi-upc-scan',    label: 'TAS ID',     val: tasId },
    { icon: 'bi-credit-card', label: 'CNIC',       val: cnic },
    { icon: 'bi-envelope',    label: 'Email',      val: email },
];

let detailsHtml = '<div class="d-flex flex-wrap gap-1 mt-1">';
details.forEach(d => {
    if (d.val === '—') return;
    detailsHtml += `
        <div class="d-flex align-items-center gap-1 rounded-pill px-2 py-1"
            style="background:rgba(1,45,90,0.06);max-width:100%;overflow:hidden;"
            title="${d.label}: ${attrSafe(d.val)}">
            <i class="bi ${d.icon} text-main flex-shrink-0" style="font-size:0.65rem;"></i>
            <span class="text-muted text-truncate" style="font-size:0.68rem;max-width:160px;">${textSafe(d.val)}</span>
        </div>`;
});
detailsHtml += '</div>';

// ── Card ──────────────────────────────────────────────────
const cardHtml = `
    <div class="col-12 col-md-6 col-lg-4 col-xl-3 mb-4">
        <div class="card border rounded-3 h-100  overflow-hidden">

            <div class="card-body p-3 d-flex flex-column">

                {{-- Header --}}
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-2 d-flex align-items-center justify-content-center fw-bold flex-shrink-0 bg-main text-white overflow-hidden"
                            style="width:42px;height:42px;font-size:1rem;">
                            ${rowData.photo_url ? `<img src="${attrSafe(rowData.photo_url)}" alt="${attrSafe(fullName)}" style="width:100%;height:100%;object-fit:cover;">` : initials}
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="mb-0 fw-semibold text-truncate"
                                style="font-size:0.85rem;max-width:140px;"
                                title="${attrSafe(fullName)}">${textSafe(fullName)}</h6>
                            <small class="text-muted d-block text-truncate"
                                style="font-size:0.7rem;max-width:140px;"
                                title="${attrSafe(role)}">${textSafe(employeeId)} &mdash; ${textSafe(role)}</small>
                        </div>
                    </div>
                    ${verificationBadge}
                </div>

                {{-- Chips --}}
                <div class="flex-grow-1 py-2">
                    ${detailsHtml}
                </div>

                {{-- Footer --}}
                <div class="pt-2 border-top d-flex gap-1">
                    ${actionBtnHtml}
                </div>

            </div>
        </div>
    </div>`;

            grid.insertAdjacentHTML('beforeend', cardHtml);
        });
    };
</script>
