<table id="leaveRequestsTable" class="display nowrap table table-striped" style="width:100%">
    <thead class="bg-main">
        <tr>
            <th class="dt-control"></th>
            <th>Employee</th>
            <th>Department</th>
            <th>Leave Type</th>
            <th>Date Range</th>
            <th>Days</th>
            <th>Reason</th>
            <th>Balance/Level</th>
            <th>Status</th>
            <th>Pending Since</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody class="bg-transparent" id="leaveRequestsTableBody">
        @isset($mappedLeaveRequests)
            @foreach($mappedLeaveRequests as $request)
                @php
                    $initials = collect(explode(' ', $request['employeeName']))->map(fn($n) => mb_substr($n, 0, 1))->implode('');
                    
                    $statusBadges = [
                        0 => ['class' => 'bg-warning text-dark', 'label' => 'Pending'],
                        1 => ['class' => 'bg-info', 'label' => 'Recommended'],
                        2 => ['class' => 'bg-danger', 'label' => 'Not Recommended'],
                        3 => ['class' => 'bg-success', 'label' => 'Approved'],
                        4 => ['class' => 'bg-danger', 'label' => 'Rejected'],
                        5 => ['class' => 'bg-secondary', 'label' => 'Cancelled'],
                    ];
                    $status = $statusBadges[$request['statusCode']] ?? $statusBadges[0];

                    $typeBadges = [
                        'annual' => ['class' => 'bg-primary', 'label' => 'Annual Leave'],
                        'sick' => ['class' => 'bg-danger', 'label' => 'Sick Leave'],
                        'casual' => ['class' => 'bg-info text-dark', 'label' => 'Casual Leave'],
                        'comp-off' => ['class' => 'bg-warning text-dark', 'label' => 'Comp-Off'],
                    ];
                    $type = $typeBadges[$request['leaveType']] ?? ['class' => 'bg-secondary', 'label' => $request['leaveTypeLabel']];

                    $startDate = \Carbon\Carbon::parse($request['startDate']);
                    $endDate = \Carbon\Carbon::parse($request['endDate']);
                @endphp
                <tr>
                    <td class="dt-control"></td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="user-avatar me-3">{{ $initials }}</div>
                            <div>
                                <div class="fw-semibold">{{ $request['employeeName'] }}</div>
                                <small class="text-muted">{{ $request['employeeId'] }} • {{ $request['department'] }}</small>
                            </div>
                        </div>
                    </td>
                    <td>{{ $request['department'] }}</td>
                    <td>
                        <span class="badge {{ $type['class'] }} px-2 py-1 rounded-1">{{ $type['label'] }}</span>
                    </td>
                    <td>
                        <div class="fw-semibold small">{{ $startDate->format('M j, Y') }}</div>
                        <small class="text-muted">to {{ $endDate->format('M j, Y') }}</small>
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $request['days'] }} day{{ $request['days'] > 1 ? 's' : '' }}</div>
                    </td>
                    <td>
                        <div class="small">{{ $request['reason'] }}</div>
                    </td>
                    <td>
                        <div class="small">
                            <div>Balance: <strong>{{ $request['balance'] }}</strong></div>
                            <div class="text-muted">{{ $request['approvalLevel'] }}</div>
                        </div>
                    </td>
                    <td>
                        <span class="badge {{ $status['class'] }} px-2 py-1 rounded-1">{{ $status['label'] }}</span>
                    </td>
                    <td>
                        <div class="small text-muted">{{ $request['pendingSince'] }}</div>
                    </td>
                    <td class="text-end">
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item view-leave-btn" href="#" data-request-id="{{ $request['id'] }}">
                                        <i class="bi bi-eye text-secondary me-2"></i>View Details
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                
                                @if($request['isChild'])
                                    <li>
                                        <a class="dropdown-item action-leave-btn" href="#" data-request-id="{{ $request['id'] }}" data-action="1">
                                            <i class="bi bi-hand-thumbs-up text-info me-2"></i>Recommend
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item action-leave-btn" href="#" data-request-id="{{ $request['id'] }}" data-action="2">
                                            <i class="bi bi-hand-thumbs-down text-warning me-2"></i>Not Recommend
                                        </a>
                                    </li>
                                @endif

                                @if($request['isParent'] || (!$request['isChild'] && !$request['isParent']))
                                    <li>
                                        <a class="dropdown-item action-leave-btn" href="#" data-request-id="{{ $request['id'] }}" data-action="3">
                                            <i class="bi bi-check-circle text-success me-2"></i>Approve
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item action-leave-btn" href="#" data-request-id="{{ $request['id'] }}" data-action="4">
                                            <i class="bi bi-x-circle text-danger me-2"></i>Reject
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item action-leave-btn" href="#" data-request-id="{{ $request['id'] }}" data-action="5">
                                            <i class="bi bi-slash-circle text-secondary me-2"></i>Cancel
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </td>
                </tr>
            @endforeach
        @endisset
    </tbody>
</table>




