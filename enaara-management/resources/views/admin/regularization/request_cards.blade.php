<!-- Request Cards -->
@php
    // Dummy data for demo
    $requests = [
        [
            'id' => '1',
            'employeeName' => 'John Doe',
            'employeeId' => 'EMP-001',
            'date' => '2024-01-15',
            'conflict' => [
                'system' => 'Absent',
                'employee' => 'Worked 9 hours'
            ],
            'reason' => 'Forgot to punch',
            'category' => 'missed-punch',
            'hasEvidence' => true,
            'status' => 'pending',
            'department' => 'Security',
            'site' => 'Site B'
        ],
        [
            'id' => '2',
            'employeeName' => 'Sarah Miller',
            'employeeId' => 'EMP-002',
            'date' => '2024-01-15',
            'conflict' => [
                'system' => 'Absent',
                'employee' => 'On-site meeting with client'
            ],
            'reason' => 'On-site Meeting',
            'category' => 'on-duty',
            'hasEvidence' => true,
            'status' => 'pending',
            'department' => 'Sales',
            'site' => 'Head Office'
        ],
        [
            'id' => '3',
            'employeeName' => 'Robert Kim',
            'employeeId' => 'EMP-003',
            'date' => '2024-01-14',
            'conflict' => [
                'system' => 'No record',
                'employee' => 'Device was offline'
            ],
            'reason' => 'Device Error',
            'category' => 'technical-error',
            'hasEvidence' => false,
            'status' => 'pending',
            'department' => 'IT',
            'site' => 'Site A'
        ],
        [
            'id' => '4',
            'employeeName' => 'Emma Wilson',
            'employeeId' => 'EMP-004',
            'date' => '2024-01-15',
            'conflict' => [
                'system' => 'Late (10:30 AM)',
                'employee' => 'Client meeting ran long'
            ],
            'reason' => 'Late Regularization',
            'category' => 'late-regularization',
            'hasEvidence' => true,
            'status' => 'pending',
            'department' => 'Operations',
            'site' => 'Branch A'
        ],
        [
            'id' => '5',
            'employeeName' => 'Michael Johnson',
            'employeeId' => 'EMP-005',
            'date' => '2024-01-15',
            'conflict' => [
                'system' => 'Absent',
                'employee' => 'Worked 8 hours'
            ],
            'reason' => 'Forgot to punch',
            'category' => 'missed-punch',
            'hasEvidence' => false,
            'status' => 'pending',
            'department' => 'Security',
            'site' => 'Site B'
        ],
        [
            'id' => '6',
            'employeeName' => 'Lisa Anderson',
            'employeeId' => 'EMP-006',
            'date' => '2024-01-14',
            'conflict' => [
                'system' => 'No record',
                'employee' => 'Biometric device had no power'
            ],
            'reason' => 'Device Error',
            'category' => 'technical-error',
            'hasEvidence' => true,
            'status' => 'pending',
            'department' => 'Security',
            'site' => 'Site C'
        ]
    ];
@endphp

@foreach($requests as $request)
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 rounded-4 h-100 request-card" data-request-id="{{ $request['id'] }}">
            <div class="card-body p-4">
                <!-- Header with Checkbox -->
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="form-check">
                        <input class="form-check-input request-checkbox" type="checkbox" value="{{ $request['id'] }}" id="request{{ $request['id'] }}">
                    </div>
                    <span class="badge 
                        @if($request['status'] === 'pending') bg-warning
                        @elseif($request['status'] === 'approved') bg-success
                        @elseif($request['status'] === 'rejected') bg-danger
                        @else bg-info
                        @endif">
                        {{ ucfirst($request['status']) }}
                    </span>
                </div>

                <!-- Employee Info -->
                <div class="mb-3">
                    <h6 class="fw-semibold mb-1">{{ $request['employeeName'] }}</h6>
                    <small class="text-muted">ID: {{ $request['employeeId'] }}</small>
                    <div class="mt-2">
                        <small class="text-muted d-block">
                            <i class="bi bi-building me-1"></i>{{ $request['department'] }}
                        </small>
                        <small class="text-muted d-block">
                            <i class="bi bi-geo-alt me-1"></i>{{ $request['site'] }}
                        </small>
                    </div>
                </div>

                <hr class="my-3">

                <!-- The Conflict -->
                <div class="mb-3">
                    <small class="text-muted d-block mb-2 fw-semibold">The Conflict</small>
                    <div class="p-3 rounded-3 bg-light">
                        <div class="mb-2">
                            <small class="text-danger d-block">
                                <i class="bi bi-x-circle me-1"></i><strong>System says:</strong> {{ $request['conflict']['system'] }}
                            </small>
                        </div>
                        <div>
                            <small class="text-success d-block">
                                <i class="bi bi-check-circle me-1"></i><strong>Employee says:</strong> {{ $request['conflict']['employee'] }}
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Reason -->
                <div class="mb-3">
                    <small class="text-muted d-block mb-2 fw-semibold">Reason</small>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-primary">{{ $request['reason'] }}</span>
                        <span class="badge bg-secondary ms-2">
                            @if($request['category'] === 'missed-punch') Missed Punch
                            @elseif($request['category'] === 'on-duty') On-Duty
                            @elseif($request['category'] === 'technical-error') Technical Error
                            @else Late Regularization
                            @endif
                        </span>
                    </div>
                </div>

                <!-- Evidence -->
                <div class="mb-3">
                    <small class="text-muted d-block mb-2 fw-semibold">Evidence</small>
                    @if($request['hasEvidence'])
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="View evidence">
                            <i class="bi bi-paperclip me-1"></i>Attachment Available
                        </button>
                    @else
                        <small class="text-muted">
                            <i class="bi bi-dash-circle me-1"></i>No evidence provided
                        </small>
                    @endif
                </div>

                <!-- Date -->
                <div class="mb-3">
                    <small class="text-muted d-block mb-1">Date</small>
                    <small class="fw-semibold">{{ date('M d, Y', strtotime($request['date'])) }}</small>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex gap-2 mt-3 pt-3 border-top">
                    <button type="button" class="btn btn-sm btn-success flex-fill approve-btn" data-request-id="{{ $request['id'] }}">
                        <i class="bi bi-check-circle me-1"></i>Approve
                    </button>
                    <button type="button" class="btn btn-sm btn-danger flex-fill reject-btn" data-request-id="{{ $request['id'] }}">
                        <i class="bi bi-x-circle me-1"></i>Reject
                    </button>
                    <button type="button" class="btn btn-sm btn-warning flex-fill clarification-btn" data-request-id="{{ $request['id'] }}">
                        <i class="bi bi-question-circle me-1"></i>Clarify
                    </button>
                </div>
            </div>
        </div>
    </div>
@endforeach

<!-- Select All Checkbox (if needed for header) -->
<div class="col-12 mb-2" style="display: none;">
    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="selectAllCheckbox">
        <label class="form-check-label small" for="selectAllCheckbox">
            Select All
        </label>
    </div>
</div>

