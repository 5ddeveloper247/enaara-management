<table id="employeeTable" class="display nowrap table table-striped" style="width:100%">
    <thead class="bg-main">
        <tr>
            <th>Profile</th>
            <th>Biometric ID</th>
            <th>Employment Type</th>
            <th>Site Assignment</th>
            <th>Vendor</th>
            <th>Sync Status</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody class="bg-transparent">
        <!-- Sample data - Replace with dynamic data from backend -->
        @for ($i = 0; $i < 75; $i++)
        <tr>
            @php
                $names = ['JD', 'SM', 'RK', 'EW', 'MJ', 'LB', 'AB', 'CD', 'EF', 'GH'];
                $fullNames = ['Ahmed Ali', 'Zainab Malik', 'Bilal Ahmed', 'Hira Ali', 'Hamza Khan', 'Sana Sheikh', 'Ali Raza', 'Khurram Ali', 'Hina Malik', 'Tariq Khan'];
                $departments = ['Sales', 'HR', 'IT', 'Legal', 'Operations', 'Finance'];
                $sites = ['Head Office', 'Branch A', 'Branch B', 'Site 1', 'Site 2'];
                $vendors = ['TechStaff Solutions', 'Global Workforce Inc', 'StaffPro Services', 'Manpower Group', 'Adecco', '-'];
                $nameIndex = $i % count($names);
                $isInternal = $i % 3 !== 0; // Every 3rd is third-party
                $employmentTypes = $isInternal ? ['Permanent', 'Contract'] : ['Third-party'];
                $employmentType = $isInternal ? $employmentTypes[$i % 2] : 'Third-party';
                $vendor = $isInternal ? '-' : $vendors[$i % (count($vendors) - 1)];
                $hasBiometric = $i % 4 !== 0; // Every 4th doesn't have biometric
                $biometricId = $hasBiometric ? 'BIO-' . str_pad($i + 1, 6, '0', STR_PAD_LEFT) : '-';
                $syncStatuses = ['Synced', 'Pending', 'Failed'];
                $syncStatus = $hasBiometric ? ($i % 10 === 0 ? 'Failed' : ($i % 7 === 0 ? 'Pending' : 'Synced')) : 'Not Linked';
            @endphp
            <td>
                <div class="d-flex align-items-center">
                    <div class="user-avatar me-3">
                        {{ $names[$nameIndex] }}
                    </div>
                    <div>
                        <div class="fw-semibold">{{ $fullNames[$nameIndex] }}</div>
                        <small class="text-muted">{{ $departments[$i % count($departments)] }} - EMP-{{ str_pad($i + 1, 4, '0', STR_PAD_LEFT) }}</small>
                    </div>
                </div>
            </td>
            <td>
                @if($biometricId !== '-')
                    <span class="badge bg-info px-2 rounded-1">{{ $biometricId }}</span>
                @else
                    <span class="text-muted">-</span>
                @endif
            </td>
            <td>
                @if($employmentType === 'Permanent')
                    <span class="badge px-2 rounded-1 bg-success">{{ $employmentType }}</span>
                @elseif($employmentType === 'Contract')
                    <span class="badge px-2 rounded-1 bg-info">{{ $employmentType }}</span>
                @else
                    <span class="badge px-2 rounded-1" style="background-color: #9c27b0; color: white;">{{ $employmentType }}</span>
                @endif
            </td>
            <td>
                <div class="fw-semibold small">{{ $sites[$i % count($sites)] }}</div>
            </td>
            <td>
                @if($vendor !== '-')
                    <small class="text-muted">{{ $vendor }}</small>
                @else
                    <span class="text-muted">-</span>
                @endif
            </td>
            <td>
                @if($syncStatus === 'Synced')
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
            <td class="text-end">
                <button type="button" 
                        class="action-btn border-0 text-white btn-primary view-employee-btn" 
                        title="View Details"
                        data-bs-toggle="offcanvas"
                        data-bs-target="#employeeDetailCanvas"
                        data-employee-id="EMP-{{ str_pad($i + 1, 4, '0', STR_PAD_LEFT) }}"
                        data-employee-name="{{ $fullNames[$nameIndex] }}"
                        data-employee-avatar="{{ $names[$nameIndex] }}"
                        data-employee-info="{{ $departments[$i % count($departments)] }} - EMP-{{ str_pad($i + 1, 4, '0', STR_PAD_LEFT) }}"
                        data-department="{{ $departments[$i % count($departments)] }}"
                        data-employment-type="{{ $employmentType }}"
                        data-employee-type="{{ $isInternal ? 'Internal' : 'Third-party' }}"
                        data-biometric-id="{{ $biometricId }}"
                        data-sync-status="{{ $syncStatus }}"
                        data-site-assignment="{{ $sites[$i % count($sites)] }}"
                        data-vendor="{{ $vendor }}">
                    <i class="bi bi-eye"></i>
                </button>
            </td>
        </tr>
        @endfor
    </tbody>
</table>

