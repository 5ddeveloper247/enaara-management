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
        <tbody class="bg-transparent">
            <!-- Sample data - Replace with dynamic data from backend -->
            @for ($i = 0; $i < 75; $i++)
                <tr>
                    @php
                        $names = ['JD', 'SM', 'RK', 'EW', 'MJ', 'LB', 'AB', 'CD', 'EF', 'GH'];
                        $fullNames = [
                            'Ahmed Ali',
                            'Zainab Malik',
                            'Bilal Ahmed',
                            'Hira Ali',
                            'Hamza Khan',
                            'Sana Sheikh',
                            'Ali Raza',
                            'Khurram Ali',
                            'Hina Malik',
                            'Tariq Khan',
                        ];
                        $departments = ['Sales', 'HR', 'IT', 'Legal', 'Operations', 'Finance'];
                        $sites = ['Head Office', 'Branch A', 'Branch B', 'Site 1', 'Site 2'];
                        $vendors = [
                            'TechStaff Solutions',
                            'Global Workforce Inc',
                            'StaffPro Services',
                            'Manpower Group',
                            'Adecco',
                            '-',
                        ];
                        $nameIndex = $i % count($names);
                        $isInternal = $i % 3 !== 0; // Every 3rd is third-party
                        $employmentTypes = $isInternal ? ['Permanent', 'Contract'] : ['Third-party'];
                        $employmentType = $isInternal ? $employmentTypes[$i % 2] : 'Third-party';
                        $vendor = $isInternal ? '-' : $vendors[$i % (count($vendors) - 1)];
                        $hasBiometric = $i % 4 !== 0; // Every 4th doesn't have biometric
                        $biometricId = $hasBiometric ? 'BIO-' . str_pad($i + 1, 6, '0', STR_PAD_LEFT) : '-';
                        $syncStatuses = ['Synced', 'Pending', 'Failed'];
                        $syncStatus = $hasBiometric
                            ? ($i % 10 === 0
                                ? 'Failed'
                                : ($i % 7 === 0
                                    ? 'Pending'
                                    : 'Synced'))
                            : 'Not Linked';
                        // Floor access - some employees have access to 10th floor/corporate office
                        $hasFloorAccess = $i % 5 === 0; // Every 5th employee has floor access
                    @endphp
                    <td>
                        <div class="d-flex align-items-center">

                            <a href="{{ route('admin.register.index') }}">
                                <div class="user-avatar me-3">
                                    {{ $names[$nameIndex] }}
                                </div>

                            </a>
                            <div>
                                <div class="fw-semibold">{{ $fullNames[$nameIndex] }}</div>
                                <small class="text-muted">{{ $departments[$i % count($departments)] }} -
                                    EMP-{{ str_pad($i + 1, 4, '0', STR_PAD_LEFT) }}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if ($biometricId !== '-')
                            <span class="badge bg-info px-2 rounded-1">{{ $biometricId }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @if ($employmentType === 'Permanent')
                            <span class="badge px-2 rounded-1 bg-success">{{ $employmentType }}</span>
                        @elseif($employmentType === 'Contract')
                            <span class="badge px-2 rounded-1 bg-info">{{ $employmentType }}</span>
                        @else
                            <span class="badge px-2 rounded-1"
                                style="background-color: #9c27b0; color: white;">{{ $employmentType }}</span>
                        @endif
                    </td>
                    <td>
                        <div class="fw-semibold small">{{ $sites[$i % count($sites)] }}</div>
                    </td>
                    <td>
                        @if ($vendor !== '-')
                            <small class="text-muted">{{ $vendor }}</small>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @if ($syncStatus === 'Synced')
                            <span class="badge px-2 rounded-1 bg-success">
                                <i class="bi bi-check-circle me-1"></i>{{ $syncStatus }}
                            </span>
                        @elseif($syncStatus === 'Pending')
                            <span class="badge px-2 rounded-1 bg-warning">
                                <i class="bi bi-clock-history me-1"></i>{{ $syncStatus }}
                            </span>
                        @elseif($syncStatus === 'Failed')
                            <span class="badge px-2 rounded-1 bg-danger">
                                <i class="bi bi-x-circle me-1"></i>{{ $syncStatus }}
                            </span>
                        @else
                            <span class="badge px-2 rounded-1 bg-secondary">
                                <i class="bi bi-dash-circle me-1"></i>{{ $syncStatus }}
                            </span>
                        @endif
                    </td>
                    <td>
                        @if ($hasFloorAccess)
                            <span class="badge px-2 rounded-1 bg-primary">
                                <i class="bi bi-building me-1"></i>10th Floor
                            </span>
                        @else
                            <span class="text-muted small">-</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <button type="button" class="action-btn border-0 text-white btn-primary view-employee-btn"
                            title="View Details" data-bs-toggle="offcanvas" data-bs-target="#employeeDetailCanvas"
                            data-employee-id="EMP-{{ str_pad($i + 1, 4, '0', STR_PAD_LEFT) }}"
                            data-employee-name="{{ $fullNames[$nameIndex] }}"
                            data-employee-avatar="{{ $names[$nameIndex] }}"
                            data-employee-info="{{ $departments[$i % count($departments)] }} - EMP-{{ str_pad($i + 1, 4, '0', STR_PAD_LEFT) }}"
                            data-department="{{ $departments[$i % count($departments)] }}"
                            data-employment-type="{{ $employmentType }}"
                            data-employee-type="{{ $isInternal ? 'Internal' : 'Third-party' }}"
                            data-biometric-id="{{ $biometricId }}" data-sync-status="{{ $syncStatus }}"
                            data-site-assignment="{{ $sites[$i % count($sites)] }}" data-vendor="{{ $vendor }}"
                            data-floor-access="{{ $hasFloorAccess ? '1' : '0' }}">
                            <i class="bi bi-eye"></i>
                        </button>
                    </td>
                </tr>
            @endfor
        </tbody>
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

        // DataTables wraps the table in a container — target that too
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
