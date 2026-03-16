<table id="dailyLogsTable" class="display nowrap table table-striped" style="width:100%">
    <thead class="bg-main">
        <tr>
            <th>Employee</th>
            <th>Check-In</th>
            <th>Check-Out</th>
            <th>Duration</th>
            <th>Source</th>
            <th>Location</th>
            <th>Flag</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody class="bg-transparent">
        <!-- Sample data - Replace with dynamic data from backend -->
        @for ($i = 0; $i < 50; $i++)
        <tr>
            <td>
                <div class="d-flex align-items-center employee-cell" data-employee-id="{{ $i + 1 }}" style="cursor: pointer;">
                    <div class="user-avatar me-3">
                        @php
                            $names = ['JD', 'SM', 'RK', 'EW', 'MJ', 'LB', 'AB', 'CD'];
                            $fullNames = ['Ahmed Ali', 'Zainab Malik', 'Bilal Ahmed', 'Hira Ali', 'Hamza Khan', 'Sana Sheikh', 'Ali Raza', 'Khurram Ali'];
                            $departments = ['Sales', 'HR', 'IT', 'Legal', 'Operations', 'Finance'];
                            $nameIndex = $i % count($names);
                        @endphp
                        {{ $names[$nameIndex] }}
                    </div>
                    <div>
                        <div class="fw-semibold">{{ $fullNames[$nameIndex] }}</div>
                        <small class="text-muted">{{ $departments[$i % count($departments)] }} - EMP-{{ str_pad($i + 1, 3, '0', STR_PAD_LEFT) }}</small>
                    </div>
                </div>
            </td>
            <td>
                <div class="d-flex align-items-center">
                    <span class="status-dot {{ $i % 3 == 0 ? 'bg-warning' : 'bg-success' }} me-2"></span>
                    <div>
                        <div class="fw-semibold small">{{ $i % 3 == 0 ? '09:4' : '09:' }}{{ str_pad(($i * 2) % 60, 2, '0', STR_PAD_LEFT) }} AM</div>
                        <small class="{{ $i % 3 == 0 ? 'text-warning' : 'text-muted' }}">{{ $i % 3 == 0 ? 'Late (' . (($i * 2) % 30) . 'm)' : 'On-Time' }}</small>
                    </div>
                </div>
            </td>
            <td>
                <div>
                    <div class="fw-semibold small">{{ $i % 4 == 0 ? '05:3' : '06:' }}{{ str_pad(($i * 3) % 60, 2, '0', STR_PAD_LEFT) }} PM</div>
                    <small class="{{ $i % 4 == 0 ? 'text-danger' : 'text-muted' }}">{{ $i % 4 == 0 ? 'Early (' . (($i * 3) % 60) . 'm)' : 'Normal' }}</small>
                </div>
            </td>
            <td>
                <div class="fw-semibold">{{ 8 + ($i % 2) }}h {{ ($i * 5) % 60 }}m</div>
            </td>
            <td>
                <div class="d-flex align-items-center">
                    @if($i % 2 == 0)
                    <i class="bi bi-phone text-main me-1"></i>
                    <small>Mobile App</small>
                    @else
                    <i class="bi bi-laptop text-info me-1"></i>
                    <small>Web Portal</small>
                    @endif
                </div>
            </td>
            <td>
                @if($i % 2 == 0)
                <div class="location-cell" 
                     data-lat="33.5651" 
                     data-lng="73.0169" 
                     data-address="Property Site {{ $i + 1 }}, Rawalpindi, Pakistan"
                     data-type="field"
                     style="cursor: pointer;">
                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                    <small>GPS Location</small>
                </div>
                @else
                <div class="location-cell" 
                     data-ip="192.168.1.{{ 100 + ($i % 50) }}" 
                     data-type="office"
                     style="cursor: pointer;">
                    <i class="bi bi-hdd-network text-main me-1"></i>
                    <small>192.168.1.{{ 100 + ($i % 50) }}</small>
                </div>
                @endif
            </td>
            <td>
                @if($i % 7 == 0)
                <span class="badge px-2 rounded-1 bg-danger">
                    <i class="bi bi-flag-fill me-1"></i>Outside Zone
                </span>
                @elseif($i % 11 == 0)
                <span class="badge px-2 rounded-1 bg-warning">IP Mismatch</span>
                @else
                <span class="badge px-2 rounded-1 bg-success">Verified</span>
                @endif
            </td>
            <td class="text-end">
                <button type="button" 
                        class="action-btn border-0 text-white btn-primary view-log-btn" 
                        title="View Details"
                        data-bs-toggle="offcanvas"
                        data-bs-target="#dailyLogDetailCanvas"
                        data-log-id="{{ $i + 1 }}"
                        data-employee-name="{{ $fullNames[$nameIndex] }}"
                        data-employee-avatar="{{ $names[$nameIndex] }}"
                        data-employee-info="{{ $departments[$i % count($departments)] }} - EMP-{{ str_pad($i + 1, 3, '0', STR_PAD_LEFT) }}"
                        data-check-in-time="{{ $i % 3 == 0 ? '09:4' : '09:' }}{{ str_pad(($i * 2) % 60, 2, '0', STR_PAD_LEFT) }} AM"
                        data-check-in-status="{{ $i % 3 == 0 ? 'late' : 'on-time' }}"
                        data-check-in-status-text="{{ $i % 3 == 0 ? 'Late (' . (($i * 2) % 30) . 'm)' : 'On-Time' }}"
                        data-check-out-time="{{ $i % 4 == 0 ? '05:3' : '06:' }}{{ str_pad(($i * 3) % 60, 2, '0', STR_PAD_LEFT) }} PM"
                        data-check-out-status="{{ $i % 4 == 0 ? 'early' : 'normal' }}"
                        data-check-out-status-text="{{ $i % 4 == 0 ? 'Early (' . (($i * 3) % 60) . 'm)' : 'Normal' }}"
                        data-duration="{{ 8 + ($i % 2) }}h {{ ($i * 5) % 60 }}m"
                        data-source="{{ $i % 2 == 0 ? 'Mobile App' : 'Web Portal' }}"
                        data-source-icon="{{ $i % 2 == 0 ? 'phone' : 'laptop' }}"
                        data-location-type="{{ $i % 2 == 0 ? 'field' : 'office' }}"
                        data-location-lat="{{ $i % 2 == 0 ? '33.5651' : '' }}"
                        data-location-lng="{{ $i % 2 == 0 ? '73.0169' : '' }}"
                        data-location-address="{{ $i % 2 == 0 ? 'Property Site ' . ($i + 1) . ', Rawalpindi, Pakistan' : '' }}"
                        data-location-ip="{{ $i % 2 == 0 ? '' : '192.168.1.' . (100 + ($i % 50)) }}"
                        data-flag="{{ $i % 7 == 0 ? 'outside-zone' : ($i % 11 == 0 ? 'ip-mismatch' : 'verified') }}"
                        data-flag-text="{{ $i % 7 == 0 ? 'Outside Zone' : ($i % 11 == 0 ? 'IP Mismatch' : 'Verified') }}">
                    <i class="bi bi-eye"></i>
                </button>
            </td>
        </tr>
        @endfor
    </tbody>
</table>

