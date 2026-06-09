<!-- Add Leave Request Canvas -->
<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="addLeaveRequestCanvas" aria-labelledby="addLeaveRequestCanvasLabel" style="width: 600px;">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="addLeaveRequestCanvasLabel">
            <i class="bi bi-plus-circle me-2"></i>
            @if(request()->routeIs('admin.my.leaves.index'))
                New Leave Request
            @else
                Apply Leave for Employee
            @endif
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
                    <label for="leaveEmployee" class="form-label fw-semibold small text-white">Employee <span class="text-danger">*</span></label>
                    <select class="form-select" id="leaveEmployee" name="employee_id" required>
                        <option value="">Select Employee</option>
                        @isset($employees)
                        @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">
                            {{ $employee->full_name }}@if(!empty($employee->employee_code)) ({{ $employee->employee_code }})@endif
                        </option>
                        @endforeach
                        @endisset
                    </select>
                    <small class="opacity-75 text-white d-block mt-1">Submitted by {{ Auth::user()->name }}</small>
                    @endif
                </div>

                <!-- Leave Balance Display -->
                <div class="p-3 rounded-3 border mb-3" style="border-color: #ffffff1a !important;">
                    <small class="opacity-75 text-white d-block mb-2">Current Leave Balance</small>
                    <div class="row g-2" id="leaveBalanceContainer">
                        @if(request()->routeIs('admin.my.leaves.index'))
                            @isset($personalQuota)
                                @include('admin.leave-requests.partials.leave_balance_groups', [
                                    'quotas' => $personalQuota,
                                    'emptyMessage' => 'No leave balances available',
                                ])
                            @else
                                <div class="col-12 text-center py-2 opacity-50 small">No leave balances available</div>
                            @endisset
                        @else
                            <div class="col-12 text-center py-2 opacity-50 small">Select an employee to see balances</div>
                        @endif
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
                    <select class="form-select" id="leaveType" name="leave_type_id" required
                        data-preloaded="{{ request()->routeIs('admin.my.leaves.index') ? '1' : '0' }}">
                        <option value="">Select Leave Type</option>
                        @isset($leaveTypes)
                        @foreach($leaveTypes as $leaveType)
                        <option value="{{ $leaveType->id }}"
                            data-leave-condition="{{ $leaveType->leave_condition ?? '' }}"
                            data-short-leave-applicable="{{ ($leaveType->setting?->short_leave_applicable ?? false) ? '1' : '0' }}">{{ $leaveType->name }}</option>
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

                <!-- Short Leave -->
                <div class="mb-3" id="halfDaySection" style="display: none;">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="leaveIsHalfDay" name="is_half_day" value="1">
                        <label class="form-check-label small text-white" for="leaveIsHalfDay">Short Leave (Half Day)</label>
                    </div>
                    <div id="halfDaySessionSection" style="display: none;">
                        <label for="leaveHalfDaySession" class="form-label fw-semibold small text-white">Session <span class="text-danger">*</span></label>
                        <select class="form-select" id="leaveHalfDaySession" name="half_day_session">
                            <option value="">Select session</option>
                            <option value="morning">Morning</option>
                            <option value="afternoon">Afternoon</option>
                        </select>
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
                    <textarea class="form-control" id="leaveReason" name="reason" rows="3" maxlength="600" placeholder="Enter reason for leave" required></textarea>
                </div>

                <!-- Supporting document (required for conditional leave types) -->
                <div class="mb-3" id="medicalCertSection" style="display: none;">
                    <label for="medical_report" class="form-label fw-semibold small text-white">
                        Supporting Document <span class="text-danger document-required-mark" style="display: none;">*</span>
                    </label>
                    <input
                        type="file"
                        class="form-control"
                        id="medical_report"
                        name="medical_report"
                        accept=".pdf,.jpg,.jpeg,.png">
                    <small class="opacity-75 text-white">
                        Required for conditional leave types (PDF, JPG, JPEG, or PNG)
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
                    <div class="small opacity-75 text-white mb-2" id="leaveApprovalWorkflowIntro">This request will be routed through:</div>
                    <div class="small" id="leaveApprovalWorkflowSteps">
                        @if(request()->routeIs('admin.my.leaves.index') && !empty($approvalWorkflowPreview))
                            @if(!empty($approvalWorkflowPreview['is_top_level']))
                                <div class="d-flex align-items-start gap-2 p-2 rounded-3 border border-warning border-opacity-50 bg-warning bg-opacity-10">
                                    <i class="bi bi-shield-exclamation text-warning fs-5 flex-shrink-0"></i>
                                    <div>
                                        <div class="fw-semibold text-warning mb-1">Top-Level Role — No Approval Route</div>
                                        <div class="opacity-90 text-white">{{ $approvalWorkflowPreview['top_level_message'] ?? \App\Services\leaverequestPrivatefunctions\LeaveRequestWorkflowPreviewService::TOP_LEVEL_MESSAGE }}</div>
                                    </div>
                                </div>
                            @else
                                @forelse($approvalWorkflowPreview['steps'] ?? [] as $step)
                                    <div class="mb-1">
                                        {{ $step['level'] }}.
                                        {{ $step['approver']['full_name'] ?? 'Unknown' }}
                                        ({{ $step['role_label'] ?? 'Approver' }})
                                        &rarr; {{ $step['action'] }}
                                    </div>
                                @empty
                                    <div class="opacity-50">No approval workflow could be resolved for this employee.</div>
                                @endforelse
                            @endif
                        @else
                            <div class="opacity-50">Select an employee to see approval workflow.</div>
                        @endif
                    </div>
                    @if(request()->routeIs('admin.my.leaves.index') && !empty($approvalWorkflowPreview['warning']) && empty($approvalWorkflowPreview['is_top_level']))
                        <div class="small text-warning mt-2" id="leaveApprovalWorkflowWarning">{{ $approvalWorkflowPreview['warning'] }}</div>
                    @else
                        <div class="small text-warning mt-2 d-none" id="leaveApprovalWorkflowWarning"></div>
                    @endif
                </div>
            </div>
        </form>
    </div>
    <div class="offcanvas-footer border-top p-3" style="border-color: #ffffffab !important">
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Cancel</button>
            <button type="submit" form="addLeaveRequestForm" class="btn btn-light text-dark border-0" id="submitLeaveRequestBtn"
                @if(request()->routeIs('admin.my.leaves.index') && !empty($approvalWorkflowPreview['is_top_level'])) disabled @endif>
                <i class="bi bi-check-lg me-1"></i>Submit Request
            </button>
        </div>
    </div>
</div>
@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<style>
    /* Fix for select2 search input focus */
    .select2-search__field {
        outline: none;
    }
    
    /* Make the select2 container match standard form-control height */
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
    }
</style>
@endpush
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    window.leaveApprovalWorkflowUrl = @json(route('admin.leave.request.approval-workflow'));
    @if(request()->routeIs('admin.my.leaves.index') && !empty($approvalWorkflowPreview))
    window.initialLeaveWorkflowPreview = @json($approvalWorkflowPreview);
    @endif
</script>
<script src="{{ asset('js/leave-request.js') }}?v={{ filemtime(public_path('js/leave-request.js')) }}"></script>
<script>
    $(document).ready(function() {
        if ($('select#leaveEmployee').length) {
            // Initialize select2
            $('select#leaveEmployee').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#addLeaveRequestCanvas'),
                width: '100%',
                placeholder: 'Search Employee...',
            }).on('change', function(e) {
                // Dispatch native event for leave-request.js
                var event = new Event('change');
                this.dispatchEvent(event);
            });
        }
    });
</script>
@endpush
