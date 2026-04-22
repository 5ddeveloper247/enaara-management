<script>
    window.employeeDataUrl    = "{{ route('admin.employee.data') }}";
    window.employeeStatsUrl   = "{{ route('admin.employee.stats') }}";
    window.registerUrl        = "{{ route('admin.register.index') }}";
    window.employeeEditUrlBase = "{{ url('admin/employees') }}";
    window.outsourcedEmployeeDataUrl = "{{ route('admin.outsourced_employee.data') }}";
    window.outsourcedEmployeeStoreUrl = "{{ route('admin.outsourced_employee.store') }}";
    window.outsourcedEmployeeShowUrlBase = "{{ url('admin/outsourced-employees') }}";
</script>

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
        // Initialize to Table view by default
        switchView('table');
    });

    function switchView(view) {
        const tableWrapper = document.getElementById('tableViewWrapper');
        const gridWrapper  = document.getElementById('gridViewWrapper');
        const btnTable     = document.getElementById('btnTableView');
        const btnGrid      = document.getElementById('btnGridView');
        const dtWrapper    = document.querySelector('#employeeTable_wrapper');

        if (view === 'grid') {
            if (tableWrapper) tableWrapper.classList.add('d-none');
            if (dtWrapper) dtWrapper.classList.add('d-none');
            if (gridWrapper) gridWrapper.classList.remove('d-none');
            
            if (btnGrid) btnGrid.classList.add('active');
            if (btnTable) btnTable.classList.remove('active');
            
            if (typeof window.buildEmployeeGrid === 'function') {
                window.buildEmployeeGrid();
            }
        } else {
            if (gridWrapper) gridWrapper.classList.add('d-none');
            if (tableWrapper) tableWrapper.classList.remove('d-none');
            if (dtWrapper) dtWrapper.classList.remove('d-none');
            
            if (btnTable) btnTable.classList.add('active');
            if (btnGrid) btnGrid.classList.remove('active');
            
            // Adjust DataTable columns when switching back
            if (window.employeeTableRef) {
                window.employeeTableRef.columns.adjust();
            }
        }
    }

    // Helper: Safe normalization
    function norm(val, fallback = '—') {
        if (val === null || val === undefined || val === '') return fallback;
        if (typeof val === 'string' && (val.trim() === '-' || val.trim() === '—')) return fallback;
        return val;
    }

    // Helper: Escape HTML
    function textSafe(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // Helper: Escape Attribute
    function attrSafe(str) {
        if (!str) return '';
        return String(str).replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    window.buildEmployeeGrid = function() {
        console.log("Building Grid View...");
        const grid = document.getElementById('gridViewWrapper');
        if (!grid) {
            console.error("Grid wrapper not found");
            return;
        }
        
        grid.innerHTML = '';

        const tableApi = window.employeeTableRef;
        if (!tableApi) {
            console.warn("Table API not initialized yet");
            return;
        }

        const dataCount = tableApi.rows({ search: 'applied' }).count();
        console.log("Records found for grid:", dataCount);

        if (dataCount === 0) {
            grid.innerHTML = '<div class="col-12 py-5 text-center text-muted">No matching employees found.</div>';
            return;
        }

        tableApi.rows({ search: 'applied' }).every(function() {
            const rowData = this.data();
            const rowNode = this.node();
            
            // If we have a node, we can scrape it for rendered HTML (to reuse badges/filters)
            // If not (e.g. server-side/deferRender), we fallback to raw data
            const cells = rowNode ? rowNode.cells : null;

            // Extract values/HTML
            let avatarHtml = '—';
            let verificationBadge = '—';
            let actionBtnHtml = '—';

            if (cells && cells.length >= 23) {
                avatarHtml = cells[0].querySelector('.employee-profile-cell')?.innerHTML || cells[0].innerHTML;
                verificationBadge = cells[13].innerHTML;
                // Re-use action button if node exists
                actionBtnHtml = cells[22].innerHTML;
            } else {
                // Total fallback: Construct button with full data attributes for extractEmployeeData
                const initials = (rowData.full_name || '??').split(' ').map(n => n[0]).join('').toUpperCase().substring(0,2);
                avatarHtml = `<div class="user-avatar flex-shrink-0 d-flex align-items-center justify-content-center" style="width:36px;height:36px;font-size:0.75rem;">${initials}</div>`;
                verificationBadge = `<span class="badge ${rowData.employee_status === 'Active' ? 'bg-success' : rowData.employee_status === 'Suspend' ? 'bg-warning text-dark' : rowData.employee_status === 'Terminated' ? 'bg-danger' : 'bg-secondary'}">${rowData.employee_status || '-'}</span>`;
                
                const dept = norm(rowData.department);
                const empNo = norm(rowData.employee_code);
                const info = [dept !== '—' ? dept : '', empNo !== '—' ? empNo : ''].filter(Boolean).join(' - ') || '—';

                actionBtnHtml = `<button type="button" class="btn btn-sm btn-primary view-employee-btn" title="View Details"
                    data-bs-toggle="offcanvas" data-bs-target="#employeeDetailCanvas"
                    data-db-id="${rowData.id || ''}"
                    data-employee-id="${empNo}"
                    data-employee-name="${attrSafe(rowData.full_name)}"
                    data-employee-info="${attrSafe(info)}"
                    data-organization="${attrSafe(rowData.organization)}"
                    data-sbu="${attrSafe(rowData.sbu)}"
                    data-department="${attrSafe(rowData.department)}"
                    data-employment-type="${attrSafe(rowData.employment_type)}"
                    data-employment-category="${attrSafe(rowData.employment_category)}"
                    data-employee-type="${attrSafe(rowData.employee_type)}"
                    data-biometric-id="${rowData.biometric_id || '-'}"
                    data-sync-status="${rowData.sync_status || 'Not Linked'}"
                    data-employee-status="${rowData.employee_status || '-'}"
                    data-assigned-floors="${attrSafe(Array.isArray(rowData.assigned_floor_names) ? rowData.assigned_floor_names.filter(Boolean).join('||') : '')}"
                    data-email="${attrSafe(rowData.email)}"
                    data-cell="${attrSafe(rowData.cell_no)}"
                    data-cnic="${attrSafe(rowData.cnic)}"
                    data-designation="${attrSafe(rowData.designation)}"
                    ><i class="bi bi-eye"></i></button>`;
            }

            const sbu      = norm(rowData.sbu);
            const dept     = norm(rowData.department);
            const category = norm(rowData.employment_category);
            const type     = norm(rowData.employment_type);

            const details = [
                { label: 'SBU', val: sbu },
                { label: 'Category', val: category },
                { label: 'Type', val: type },
                { label: 'TAS ID', val: norm(rowData.biometric_id) },
                { label: 'CNIC', val: norm(rowData.cnic) },
                { label: 'Email', val: norm(rowData.email) }
            ];

            let detailsHtml = '';
            details.forEach(d => {
                if (d.val === '—') return;
                detailsHtml += `
                    <div class="mb-2 pb-1 border-bottom border-light-subtle">
                        <div class="employee-grid-field-label small text-muted text-uppercase" style="font-size: 0.6rem;">${d.label}</div>
                        <div class="employee-grid-field-value small text-dark text-truncate" title="${attrSafe(d.val)}">${d.val}</div>
                    </div>`;
            });

            const cardHtml = `
                <div class="col-12 col-md-6 col-lg-4 col-xl-3 mb-4">
                    <div class="card h-100 border-0 shadow-sm rounded-4 position-relative overflow-hidden">
                        <div class="card-body p-3 d-flex flex-column">
                            <div class="position-absolute top-0 end-0 p-2" style="z-index: 5;">
                                ${verificationBadge}
                            </div>
                            <div class="mb-3">
                                ${avatarHtml}
                            </div>
                            <div class="employee-grid-card-scroll flex-grow-1 mb-3 px-1">
                                ${detailsHtml}
                            </div>
                            <div class="mt-auto pt-2 border-top d-flex justify-content-end align-items-center">
                                ${actionBtnHtml}
                            </div>
                        </div>
                    </div>
                </div>
            `;
            grid.insertAdjacentHTML('beforeend', cardHtml);
        });
    };
</script>
