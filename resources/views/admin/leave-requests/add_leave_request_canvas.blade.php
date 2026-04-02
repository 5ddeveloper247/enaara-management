<!-- Add Leave Request Canvas -->
<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="addLeaveRequestCanvas" aria-labelledby="addLeaveRequestCanvasLabel" style="width: 600px;">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="addLeaveRequestCanvasLabel">
            <i class="bi bi-plus-circle me-2"></i>New Leave Request
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="addLeaveRequestForm" method="POST" action="{{ route('admin.leave.request.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="alert alert-danger d-none mb-3" data-form-errors role="alert"></div>
            <!-- Employee Selection -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-person me-2"></i>Employee
                </h6>

                <div class="mb-3">
                    @if(request()->routeIs('admin.my.leaves.index'))
                    <label class="form-label fw-semibold small text-white">Employee</label>
                    <div class="form-control-plaintext text-white border-bottom pb-2" style="border-color: #ffffff1a !important;">
                        {{ Auth::user()->name }}
                    </div>
                    <input type="hidden" id="leaveEmployee" name="employee_id" value="{{ Auth::user()->employee_id }}">
                    @else
                    <label for="leaveEmployee" class="form-label fw-semibold small text-white">Select Employee <span class="text-danger">*</span></label>
                    <select class="form-select" id="leaveEmployee" name="employee_id" required>
                        <option value="">Select Employee</option>
                        @isset($employees)
                        @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">
                            {{ $employee->full_name }}
                        </option>
                        @endforeach
                        @endisset
                    </select>
                    @endif
                </div>

                <!-- Leave Balance Display -->
                <div class="p-3 rounded-3 border mb-3" style="border-color: #ffffff1a !important;">
                    <small class="opacity-75 text-white d-block mb-2">Current Leave Balance</small>
                    <div class="row g-2" id="leaveBalanceContainer">
                        @isset($personalQuota)
                            @foreach($personalQuota as $quota)
                                <div class="col-6">
                                    <div class="small">{{ $quota['type'] }}: <strong>{{ $quota['remaining'] }}</strong> days</div>
                                </div>
                            @endforeach
                        @else
                            <div class="col-12 text-center py-2 opacity-50 small">Select an employee to see balances</div>
                        @endisset
                    </div>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Leave Details -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-calendar me-2"></i>Leave Details
                </h6>

                <!-- Leave Type -->
                <div class="mb-3">
                    <label for="leaveType" class="form-label fw-semibold small text-white">Leave Type <span class="text-danger">*</span></label>
                    <select class="form-select" id="leaveType" name="leave_type_id" required>
                        <option value="">Select Leave Type</option>
                        @isset($leaveTypes)
                        @foreach($leaveTypes as $leaveType)
                        <option value="{{ $leaveType->id }}">{{ $leaveType->name }}</option>
                        @endforeach
                        @endisset
                    </select>
                </div>

                <!-- Date Range -->
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label for="leaveStartDate" class="form-label fw-semibold small text-white">Start Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="leaveStartDate" name="start_date" required>
                    </div>
                    <div class="col-6">
                        <label for="leaveEndDate" class="form-label fw-semibold small text-white">End Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="leaveEndDate" name="end_date" required>
                    </div>
                </div>

                <!-- Calculated Days -->
                <div class="mb-3 p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small opacity-75 text-white">Total Days:</span>
                        <strong class="fs-5" id="calculatedDays">0</strong>
                    </div>
                </div>

                <!-- Reason -->
                <div class="mb-3">
                    <label for="leaveReason" class="form-label fw-semibold small text-white">Reason <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="leaveReason" name="reason" rows="3" placeholder="Enter reason for leave" required></textarea>
                </div>

                <!-- Medical Certificate (for Sick Leave) -->
                <div class="mb-3" id="medicalCertSection" style="display: none;">
                    <label for="medical_report" class="form-label fw-semibold small text-white">
                        Medical Certificate
                    </label>
                    <input
                        type="file"
                        class="form-control"
                        id="medical_report"
                        name="medical_report"
                        accept=".pdf,.jpg,.jpeg,.png">
                    <small class="opacity-75 text-white">
                        Required for sick leave exceeding 2 days
                    </small>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Approval Level -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-diagram-3 me-2"></i>Approval Workflow
                </h6>

                <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                    <div class="small opacity-75 text-white mb-2">This request will be routed through:</div>
                    <div class="small">
                        <div class="mb-1">1. Supervisor → Team workload check</div>
                        <div class="mb-1">2. HR/Dept Head → Leave balance verification</div>
                        <div>3. Super Admin → Final approval (if required)</div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="offcanvas-footer border-top p-3" style="border-color: #ffffffab !important">
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Cancel</button>
            <button type="submit" form="addLeaveRequestForm" class="btn btn-light text-dark border-0" id="submitLeaveRequestBtn">
                <i class="bi bi-check-lg me-1"></i>Submit Request
            </button>
        </div>
    </div>
</div>
@push('scripts')
<script src="{{ asset('js/leave-request.js') }}"></script>
@endpush