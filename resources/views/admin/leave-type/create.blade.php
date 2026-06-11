@extends('layouts.app')

@php
    $isEdit = !empty($isEdit) && isset($leaveType);
@endphp

@section('title', ($isEdit ? 'Edit' : 'Add') . ' Leave Type - Admin Panel')

@section('page-title', 'Leave Types')

@push('styles')
<link href="{{ asset('css/leave-type.css') }}?v={{ filemtime(public_path('css/leave-type.css')) }}" rel="stylesheet">
@endpush

@section('content')
<div class="lt-add-page">
    <div class="container-fluid py-2">
        <div class="lt-page-header">
            <div class="lt-page-header-main">
                <a href="{{ route('admin.leave.type.index') }}" class="lt-back-btn" aria-label="Back to leave types">
                    <i class="bi bi-arrow-left" aria-hidden="true"></i>
                </a>
                <div class="lt-page-header-text">
                    <div class="lt-page-title-row">
                        <span class="lt-page-icon" aria-hidden="true">
                            <i class="bi {{ $isEdit ? 'bi-pencil-square' : 'bi-calendar2-plus' }}"></i>
                        </span>
                        <h1 class="lt-page-title mb-0">{{ $isEdit ? 'Edit Leave Type' : 'Add Leave Type' }}</h1>
                    </div>
                    <p class="lt-page-subtitle mb-0">
                        {{ $isEdit ? 'Update leave policy, entitlement, and eligibility settings.' : 'Configure leave policy, entitlement, and eligibility settings.' }}
                    </p>
                </div>
            </div>
        </div>

        <form id="leaveTypeAddForm"
            action="{{ $isEdit ? route('admin.leave.type.update', $leaveType->id) : route('admin.leave.type.store') }}"
            method="post"
            novalidate>
            @csrf

            <div class="row g-3">
                <div class="col-xl-9">
                    <div class="card border-0 shadow-sm lt-form-shell">
                        <div class="card-body p-3">

                            <section class="lt-section-block">
                                <h2 class="lt-section-title">Basic Information</h2>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="lt_name" class="form-label">Leave Type Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="lt_name" name="name" placeholder="e.g. Annual Leave" maxlength="255">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="lt_leave_condition" class="form-label">Leave Type <span class="text-danger">*</span></label>
                                        <select class="form-select" id="lt_leave_condition" name="leave_condition">
                                            <option value="">Select leave type</option>
                                            <option value="conditional">Conditional leave</option>
                                            <option value="unconditional">Unconditional leave</option>
                                            <option value="general">General</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="lt_code" class="form-label">Leave Code <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control text-uppercase" id="lt_code" name="code" placeholder="E.G. AL" maxlength="5">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="lt_category" class="form-label">Leave Category <span class="text-danger">*</span></label>
                                        <select class="form-select" id="lt_category" name="leave_category">
                                            <option value="">Select category</option>
                                            <option value="paid">Paid</option>
                                            <option value="unpaid">Unpaid</option>
                                            <option value="special">Special</option>
                                            <option value="attendance">Attendance Related</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label d-block">Leave Type Status</label>
                                        <div class="lt-inline-switch">
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input" type="checkbox" id="lt_is_active" name="is_active" value="1" checked>
                                                <label class="form-check-label" for="lt_is_active">Active</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <label for="lt_description" class="form-label">Description</label>
                                        <textarea class="form-control" id="lt_description" name="description" rows="2" maxlength="250" placeholder="Optional"></textarea>
                                        <div class="lt-char-count"><span id="lt_description_count">0</span>/250</div>
                                    </div>
                                </div>
                            </section>

                            <section class="lt-section-block">
                                <h2 class="lt-section-title">Eligibility &amp; Applicable To</h2>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="lt_organization_id" class="form-label">Organization <span class="text-danger">*</span></label>
                                        <select class="form-select" id="lt_organization_id" name="organization_id" required>
                                            <option value="" hidden selected>Select Organization</option>
                                            @foreach($organizations ?? [] as $organization)
                                            <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label mb-0">SBU <span class="text-danger">*</span></label>
                                            <div class="form-check form-check-inline small mb-0">
                                                <input class="form-check-input" type="checkbox" id="lt_select_all_sbus">
                                                <label class="form-check-label text-muted" for="lt_select_all_sbus">Select All</label>
                                            </div>
                                        </div>
                                        <div id="lt_sbu_hidden_inputs"></div>
                                        <div id="lt_sbu_box" class="lt-dept-input-box" role="button" tabindex="0" aria-haspopup="listbox">
                                            <div id="lt_sbu_chips" style="display:contents"></div>
                                            <span class="lt-dept-ph" id="lt_sbu_placeholder">Select Organization first</span>
                                            <svg class="lt-dept-chevron" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                                <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </div>
                                        <div id="lt_sbu_dropdown" class="lt-dept-dropdown" style="display:none">
                                            <div class="lt-dept-search-row">
                                                <input type="text" id="lt_sbu_search" placeholder="Search SBU..." autocomplete="off">
                                            </div>
                                            <div id="lt_sbu_list" class="lt-dept-opt-list"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-3 gx-4 mt-1 lt-eligibility-row">
                                    <div class="col-md-3">
                                        <label for="lt_employment_type" class="form-label">Employment Type</label>
                                        <select class="form-select" id="lt_employment_type" name="employment_type">
                                            <option value="all" selected>All</option>
                                            <option value="permanent">Permanent</option>
                                            <option value="contract">Contract</option>
                                            <option value="probation">Probation</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="lt_gender" class="form-label">Gender</label>
                                        <select class="form-select" id="lt_gender" name="gender">
                                            <option value="all" selected>All</option>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="lt_min_service" class="form-label">Minimum Service</label>
                                        <div class="lt-suffix-field">
                                            <input type="number" class="form-control" id="lt_min_service" name="min_service_months" min="0" value="0">
                                            <span class="lt-suffix">months</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="lt_eligible_from" class="form-label">Eligible From</label>
                                        <select class="form-select" id="lt_eligible_from" name="eligible_from">
                                            <option value="doj" selected>Date of Joining</option>
                                            <option value="confirmation">Confirmation Date</option>
                                            <option value="custom">Custom Date</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row g-3 mt-2">
                                    <div class="col-md-6">
                                        <div class="lt-inline-switch">
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input" type="checkbox" id="lt_probation_eligible" name="probation_eligible" value="1" checked>
                                                <label class="form-check-label" for="lt_probation_eligible">Probation Employees Eligible</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="lt-section-block">
                                <h2 class="lt-section-title">Policy &amp; Entitlement</h2>
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label for="lt_entitlement_days" class="form-label">Days / Entitlement <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="lt_entitlement_days" name="annual_quota" step="any" min="0" max="999.99" value="0" data-preview="entitlement">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="lt_unit" class="form-label">Unit <span class="text-danger">*</span></label>
                                        <select class="form-select" id="lt_unit" name="unit_of_leave" data-preview="unit">
                                            <option value="days" selected>Day(s)</option>
                                            <option value="hours">Hour(s)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="lt_accrual_frequency" class="form-label">Accrual Frequency</label>
                                        <select class="form-select" id="lt_accrual_frequency" name="accrual_frequency" data-preview="accrual_frequency">
                                            <option value="">Select</option>
                                            <option value="monthly">Monthly</option>
                                            <option value="quarterly">Quarterly</option>
                                            <option value="yearly" selected>Yearly</option>
                                            <option value="once_in_tenure">Once in Tenure</option>
                                            <option value="none">None</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="lt_accrual_start_month" class="form-label">Accrual Start Month</label>
                                        <select class="form-select" id="lt_accrual_start_month" name="accrual_start_month">
                                            <option value="">Select month</option>
                                            @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $i => $month)
                                            <option value="{{ $i + 1 }}" {{ $i === 0 ? 'selected' : '' }}>{{ $month }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="lt_carry_forward" class="form-label">Carry Forward</label>
                                        <select class="form-select" id="lt_carry_forward" name="carry_forward" data-preview="carry_forward">
                                            <option value="no">No</option>
                                            <option value="yes">Yes</option>
                                            <option value="as_earned">As Earned</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 carry-forward-dependent">
                                        <label for="lt_max_carry_forward" class="form-label">Max Carry Forward (days)</label>
                                        <input type="number" class="form-control" id="lt_max_carry_forward" name="max_carry_forward_days" min="0" step="0.25" placeholder="0">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="lt_encashment_allowed" class="form-label">Encashment Allowed</label>
                                        <select class="form-select" id="lt_encashment_allowed" name="encashment_allowed" data-preview="encashment_allowed">
                                            <option value="no">No</option>
                                            <option value="yes">Yes</option>
                                            <option value="as_per_policy">As per Policy</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 encashment-dependent" id="encashment_rule_col" style="display: none;">
                                        <label for="lt_encashment_rule" class="form-label">Encashment Rule</label>
                                        <select class="form-select" id="lt_encashment_rule" name="encashment_rule">
                                            <option value="full">Full (all remaining)</option>
                                            <option value="partial">Partial (as per rules)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mt-3 encashment-dependent" id="encashment_rules_section" style="display: none;">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0 fw-semibold text-secondary">Encashment Rules</h6>
                                            <button type="button" class="btn btn-sm btn-outline-primary" id="add_encashment_rule_btn">
                                                <i class="bi bi-plus-circle me-1"></i> Add Rule
                                            </button>
                                        </div>
                                        <input type="hidden" id="lt_encashment_rules">
                                        <div class="border rounded-3" style="overflow: visible;">
                                            <table class="table table-sm table-borderless align-middle mb-0" id="encashment_rules_table">
                                                <thead class="border-bottom" style="background-color: #f8f9fa;">
                                                    <tr>
                                                        <th class="fw-semibold" style="width: 28%; color: #495057 !important; font-size: 0.85rem;">Minimum Service</th>
                                                        <th class="fw-semibold" style="width: 47%; color: #495057 !important; font-size: 0.85rem;">Role Level</th>
                                                        <th class="fw-semibold" style="width: 20%; color: #495057 !important; font-size: 0.85rem;">Max Encashment Days</th>
                                                        <th class="text-end" style="width: 5%;"></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="encashment_rules_tbody">
                                                    <tr id="no_rules_row">
                                                        <td colspan="4" class="text-center text-muted py-3">No rules added. Click "Add Rule" to configure.</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="lt-section-block lt-section-block-last">
                                <h2 class="lt-section-title">Usage Rules</h2>
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-3">
                                        <label for="lt_max_consecutive" class="form-label">Max Consecutive Days</label>
                                        <input type="number" class="form-control" id="lt_max_consecutive" name="max_consecutive_days" min="0" placeholder="e.g. 14">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="lt_advance_notice" class="form-label">Advance Notice</label>
                                        <div class="lt-suffix-field">
                                            <input type="number" class="form-control" id="lt_advance_notice" name="advance_notice_days" min="0" value="0">
                                            <span class="lt-suffix">days</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="lt_short_leave_max" class="form-label">Short Leave Max / Day</label>
                                        <select class="form-select" id="lt_short_leave_max" name="short_leave_max_hours">
                                            <option value="2">2 Hours</option>
                                            <option value="4" selected>4 Hours</option>
                                            <option value="6">6 Hours</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="lt-inline-switch">
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input" type="checkbox" id="lt_short_leave" name="short_leave_applicable" value="1" checked>
                                                <label class="form-check-label" for="lt_short_leave">Short Leave Applicable</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>

                        </div>
                    </div>

                    <div class="lt-form-footer mt-3">
                        <a href="{{ route('admin.leave.type.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="button" class="btn btn-primary bg-main border-0" id="ltSaveBtn"
                            data-default-label="{{ $isEdit ? 'Update Leave Type' : 'Save Leave Type' }}"
                            data-loading-label="{{ $isEdit ? 'Updating...' : 'Saving...' }}">
                            <i class="bi bi-check-lg me-1 lt-save-icon" aria-hidden="true"></i>
                            <span class="lt-save-label">{{ $isEdit ? 'Update Leave Type' : 'Save Leave Type' }}</span>
                        </button>
                    </div>
                </div>

                <div class="col-xl-3">
                    <div class="lt-sidebar-stack">
                        <div class="card border-0 shadow-sm lt-preview-card">
                            <div class="card-header bg-white border-bottom py-2 px-3">
                                <span class="fw-semibold small"><i class="bi bi-eye me-1"></i> Preview</span>
                            </div>
                            <div class="card-body p-3">
                                <div class="lt-preview-row">
                                    <span class="lt-preview-label">Name</span>
                                    <span class="lt-preview-value" id="preview_name">—</span>
                                </div>
                                <div class="lt-preview-row">
                                    <span class="lt-preview-label">Leave Type</span>
                                    <span class="lt-preview-value" id="preview_leave_condition">—</span>
                                </div>
                                <div class="lt-preview-row">
                                    <span class="lt-preview-label">Code</span>
                                    <span class="lt-preview-value" id="preview_code">—</span>
                                </div>
                                <div class="lt-preview-row">
                                    <span class="lt-preview-label">Category</span>
                                    <span class="lt-preview-value" id="preview_category">—</span>
                                </div>
                                <div class="lt-preview-row">
                                    <span class="lt-preview-label">Entitlement</span>
                                    <span class="lt-preview-value" id="preview_entitlement">0 Day(s)</span>
                                </div>
                                <div class="lt-preview-row">
                                    <span class="lt-preview-label">Carry Forward</span>
                                    <span class="lt-preview-value" id="preview_carry_forward">No</span>
                                </div>
                                <div class="lt-preview-row">
                                    <span class="lt-preview-label">Encashment</span>
                                    <span class="lt-preview-value" id="preview_encashment">No</span>
                                </div>
                                <div class="lt-preview-row mb-0">
                                    <span class="lt-preview-label">Status</span>
                                    <span class="lt-preview-value" id="preview_status"><span class="lt-preview-badge-active">Active</span></span>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-bottom py-2 px-3">
                                <span class="fw-semibold small">Entitlement Reference</span>
                            </div>
                            <div class="card-body p-0">
                                <table class="table lt-ref-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th class="text-end">Days</th>
                                        </tr>
                                    </thead>
                                    <tbody id="lt_entitlement_reference_body">
                                        <tr>
                                            <td colspan="2" class="text-muted small text-center py-3">
                                                Select an organization to see existing leave entitlements.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.leaveTypeFormConfig = {
        mode: @json($isEdit ? 'edit' : 'create'),
        leaveTypeId: @json($isEdit ? $leaveType->id : null),
        submitUrl: @json($isEdit ? route('admin.leave.type.update', $leaveType->id) : route('admin.leave.type.store')),
        indexUrl: @json(route('admin.leave.type.index')),
        sbuUrl: @json(route('admin.sbu.index')),
        entitlementReferenceUrl: @json(route('admin.leave.type.entitlement-reference')),
        csrfToken: @json(csrf_token()),
        initialData: @json($initialData ?? null),
        roleLevels: @json($roleLevels ?? []),
        successTitle: @json($isEdit ? 'Updated' : 'Saved'),
        successMessage: @json($isEdit ? 'Leave type updated successfully.' : 'Leave type created successfully.'),
    };
</script>
<script src="{{ asset('js/leave-type-form.js') }}?v={{ filemtime(public_path('js/leave-type-form.js')) }}"></script>
@endpush
