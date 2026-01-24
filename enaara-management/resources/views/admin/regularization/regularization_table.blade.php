<table id="regularizationTable" class="display nowrap table table-striped" style="width:100%">
    <thead class="bg-main">
        <tr>
            <th class="dt-control"></th>
            <th>
                <input type="checkbox" class="form-check-input" id="selectAllCheckbox">
            </th>
            <th>Employee</th>
            <th>Date</th>
            <th>Conflict</th>
            <th>Reason</th>
            <th>Category</th>
            <th>Evidence</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody class="bg-transparent">
        @php
            $requests = [
                [
                    'id' => '1',
                    'employeeName' => 'John Doe',
                    'employeeId' => 'EMP-001',
                    'department' => 'Security',
                    'site' => 'Site B',
                    'date' => '2024-01-15',
                    'conflict' => [
                        'system' => 'Absent',
                        'employee' => 'Worked 9 hours'
                    ],
                    'reason' => 'Forgot to punch',
                    'category' => 'missed-punch',
                    'categoryLabel' => 'Missed Punch',
                    'hasEvidence' => true,
                    'status' => 'pending'
                ],
                [
                    'id' => '2',
                    'employeeName' => 'Sarah Miller',
                    'employeeId' => 'EMP-002',
                    'department' => 'Sales',
                    'site' => 'Head Office',
                    'date' => '2024-01-15',
                    'conflict' => [
                        'system' => 'Absent',
                        'employee' => 'On-site meeting with client'
                    ],
                    'reason' => 'On-site Meeting',
                    'category' => 'on-duty',
                    'categoryLabel' => 'On-Duty',
                    'hasEvidence' => true,
                    'status' => 'pending'
                ],
                [
                    'id' => '3',
                    'employeeName' => 'Robert Kim',
                    'employeeId' => 'EMP-003',
                    'department' => 'IT',
                    'site' => 'Site A',
                    'date' => '2024-01-14',
                    'conflict' => [
                        'system' => 'No record',
                        'employee' => 'Device was offline'
                    ],
                    'reason' => 'Device Error',
                    'category' => 'technical-error',
                    'categoryLabel' => 'Technical Error',
                    'hasEvidence' => false,
                    'status' => 'pending'
                ],
                [
                    'id' => '4',
                    'employeeName' => 'Emma Wilson',
                    'employeeId' => 'EMP-004',
                    'department' => 'Operations',
                    'site' => 'Branch A',
                    'date' => '2024-01-15',
                    'conflict' => [
                        'system' => 'Late (10:30 AM)',
                        'employee' => 'Client meeting ran long'
                    ],
                    'reason' => 'Late Regularization',
                    'category' => 'late-regularization',
                    'categoryLabel' => 'Late Regularization',
                    'hasEvidence' => true,
                    'status' => 'pending'
                ],
                [
                    'id' => '5',
                    'employeeName' => 'Michael Johnson',
                    'employeeId' => 'EMP-005',
                    'department' => 'Security',
                    'site' => 'Site B',
                    'date' => '2024-01-15',
                    'conflict' => [
                        'system' => 'Absent',
                        'employee' => 'Worked 8 hours'
                    ],
                    'reason' => 'Forgot to punch',
                    'category' => 'missed-punch',
                    'categoryLabel' => 'Missed Punch',
                    'hasEvidence' => false,
                    'status' => 'pending'
                ],
                [
                    'id' => '6',
                    'employeeName' => 'Lisa Anderson',
                    'employeeId' => 'EMP-006',
                    'department' => 'Security',
                    'site' => 'Site C',
                    'date' => '2024-01-14',
                    'conflict' => [
                        'system' => 'No record',
                        'employee' => 'Biometric device had no power'
                    ],
                    'reason' => 'Device Error',
                    'category' => 'technical-error',
                    'categoryLabel' => 'Technical Error',
                    'hasEvidence' => true,
                    'status' => 'pending'
                ],
                [
                    'id' => '7',
                    'employeeName' => 'David Brown',
                    'employeeId' => 'EMP-007',
                    'department' => 'HR',
                    'site' => 'Head Office',
                    'date' => '2024-01-13',
                    'conflict' => [
                        'system' => 'Absent',
                        'employee' => 'Worked 7.5 hours'
                    ],
                    'reason' => 'Forgot to punch',
                    'category' => 'missed-punch',
                    'categoryLabel' => 'Missed Punch',
                    'hasEvidence' => false,
                    'status' => 'approved'
                ],
                [
                    'id' => '8',
                    'employeeName' => 'Jennifer Lee',
                    'employeeId' => 'EMP-008',
                    'department' => 'Finance',
                    'site' => 'Branch B',
                    'date' => '2024-01-12',
                    'conflict' => [
                        'system' => 'Late (11:00 AM)',
                        'employee' => 'Personal emergency'
                    ],
                    'reason' => 'Personal Emergency',
                    'category' => 'late-regularization',
                    'categoryLabel' => 'Late Regularization',
                    'hasEvidence' => true,
                    'status' => 'rejected'
                ]
            ];
        @endphp

        @foreach($requests as $request)
        <tr data-request-id="{{ $request['id'] }}">
            <td class="dt-control"></td>
            <td>
                <input type="checkbox" class="form-check-input request-checkbox" value="{{ $request['id'] }}" 
                       id="request{{ $request['id'] }}" 
                       @if($request['status'] !== 'pending') disabled @endif>
            </td>
            <td>
                <div>
                    <div class="fw-semibold">{{ $request['employeeName'] }}</div>
                    <small class="text-muted">{{ $request['employeeId'] }} • {{ $request['department'] }}</small>
                    <div class="">
                        <small class="text-muted">
                            <i class="bi bi-geo-alt me-1"></i>{{ $request['site'] }}
                        </small>
                    </div>
                </div>
            </td>
            <td>
                <div class="fw-semibold small">{{ date('M d, Y', strtotime($request['date'])) }}</div>
            </td>
            <td>
                <div class="small">
                    <div class="mb-1">
                        <span class="text-danger">
                            <i class="bi bi-x-circle me-1"></i><strong>System:</strong> {{ $request['conflict']['system'] }}
                        </span>
                    </div>
                    <div>
                        <span class="text-success">
                            <i class="bi bi-check-circle me-1"></i><strong>Employee:</strong> {{ $request['conflict']['employee'] }}
                        </span>
                    </div>
                </div>
            </td>
            <td>
                <span class="badge bg-primary">{{ $request['reason'] }}</span>
            </td>
            <td>
                <span class="badge bg-secondary">{{ $request['categoryLabel'] }}</span>
            </td>
            <td>
                @if($request['hasEvidence'])
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="View evidence">
                        <i class="bi bi-paperclip me-1"></i>Available
                    </button>
                @else
                    <small class="text-muted">
                        <i class="bi bi-dash-circle me-1"></i>None
                    </small>
                @endif
            </td>
            <td>
                <span class="badge 
                    @if($request['status'] === 'pending') bg-primary-custom
                    @elseif($request['status'] === 'approved') bg-primary
                    @elseif($request['status'] === 'rejected') bg-danger
                    @else bg-info
                    @endif">
                    {{ ucfirst($request['status']) }}
                </span>
            </td>
            <td class="text-end">
                @if($request['status'] === 'pending')
                    <div class="d-flex gap-1 justify-content-end">
                        <button type="button" class="btn btn-sm px-1 py-0 btn-primary approve-btn" 
                                data-request-id="{{ $request['id'] }}"
                                data-bs-toggle="tooltip" title="Approve">
                            <i class="bi bi-check-circle"></i>
                        </button>
                        <button type="button" class="btn btn-sm px-1 py-0 btn-danger reject-btn" 
                                data-request-id="{{ $request['id'] }}"
                                data-bs-toggle="tooltip" title="Reject">
                            <i class="bi bi-x-circle"></i>
                        </button>
                        <button type="button" class="btn btn-sm px-1 py-0 bg-primary-custom clarification-btn" 
                                data-request-id="{{ $request['id'] }}"
                                data-bs-toggle="tooltip" title="Request Clarification">
                            <i class="bi bi-question-circle"></i>
                        </button>
                        <button type="button" class="btn btn-sm px-1 py-0 btn-outline-secondary view-audit-btn" 
                                data-request-id="{{ $request['id'] }}"
                                data-employee-name="{{ $request['employeeName'] }}"
                                data-date="{{ date('M d, Y', strtotime($request['date'])) }}"
                                data-bs-toggle="tooltip" title="View Audit Trail">
                            <i class="bi bi-clock-history"></i>
                        </button>
                    </div>
                @else
                    <button type="button" class="btn btn-sm px-1 py-0 btn-outline-secondary view-audit-btn" 
                            data-request-id="{{ $request['id'] }}"
                            data-employee-name="{{ $request['employeeName'] }}"
                            data-date="{{ date('M d, Y', strtotime($request['date'])) }}"
                            data-bs-toggle="tooltip" title="View Audit Trail">
                        <i class="bi bi-clock-history"></i>
                    </button>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

