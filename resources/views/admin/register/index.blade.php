@extends('layouts.app')

@section('title', isset($employee) ? 'Edit Employee - Admin Panel' : 'Register - Admin Panel')

@section('page-title', isset($employee) ? 'Edit Employee' : 'Register')

@push('styles')
<link href="{{ asset('css/users.css') }}" rel="stylesheet">
<style>
    .table {
        --bs-table-bg: transparent !important;
    }

    th {
        padding: 0.75rem 1rem !important;
        color: var(--light-color) !important;
        white-space: nowrap !important;
        font-size: 0.85rem;
    }

    td {
        padding: 0.75rem 1rem !important;
    }

    form input,
    textarea,
    select,
    option {
        background: transparent !important;
        border: 2px solid #012445;
        box-shadow: 0 0 7px 4px #5a59593d;
    }

    select {
        border: white !important;
    }

    .section-title {
        display: none;
    }

    .step {
        display: none;
    }

    .step.active {
        display: block;
    }

    .check-input {
        box-shadow: none;
    }

    #formToast {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 320px;
    }
</style>
<style>
    .swal2-container {
        z-index: 999999 !important;
    }

    .swal2-popup {
        z-index: 999999 !important;
    }
    
    /* Compact error messages */
    .field-error-msg {
        font-size: 0.68rem;
        font-weight: 500;
        line-height: 1;
        margin-top: 2px;
        display: block;
        width: 100%;
        color: #dc3545;
    }
    .is-invalid-step { border-color: #dc3545 !important; }
    
    /* Cropper Styles */
    .cropper-container { max-height: 400px !important; }
    #cropperImage { max-width: 100%; display: block; }
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
@endpush

@section('content')
@include('admin.register.attachment-modal')
@include('admin.register.cropper-modal')

{{-- Toast --}}
<div id="formToast" class="toast align-items-center border-0 hide" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
        <div class="toast-body fw-semibold" id="toastMsg"></div>
        <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
</div>

<div class="container">
    <div class="d-flex justify-content-between mb-4 align-items-center">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('admin.employee.index') }}" class="btn btn-secondary d-flex align-items-center border-0 px-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h5 class="mb-0">{{ isset($employee) ? 'Edit Employee — ' . $employee->full_name : 'Employee Information Form' }}</h5>
        </div>
        <button class="btn btn-link text-decoration-none text-white bg-main d-flex align-items-center border-0 px-3"
            type="button" data-bs-toggle="modal" data-bs-target="#attachmentModal">
            Attachment
        </button>
    </div>
    
    <div class="card shadow-sm p-4">

        @include('admin.register.header')

        <form id="employeeForm" method="POST"
            action="{{ isset($employee) ? route('admin.employee.update', $employee->id) : route('admin.employee.store') }}"
            enctype="multipart/form-data" novalidate>
            @csrf
            
            @if(isset($employee))
                <input type="hidden" name="employee_id" id="saved_employee_id" value="{{ $employee->id }}">
            @endif

            @include('admin.register.general_info')
            @include('admin.register.employment_info')
            @include('admin.register.personal_info')
            @include('admin.register.ex_employment')
            @include('admin.register.bankdetails')
            @include('admin.register.academic')

            {{-- Hidden container for serialized array data --}}
            <div id="hiddenArrayInputs"></div>
        </form>

        {{-- Navigation --}}
        <div class="d-flex justify-content-between mt-4" id="wizard-navigation">
            <button class="btn btn-outline-secondary" id="prevBtn" onclick="changeStep(-1)" style="display:none">Back</button>
            <button class="btn ms-auto text-decoration-none text-white bg-main rounded-2 d-flex align-items-center border-0 px-3"
                id="nextBtn" onclick="changeStep(1)">Next</button>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
    window.onerror = function(msg, url, line, col, error) {
        if (typeof showError === 'function') showError('JS Error: ' + msg + ' at line ' + line);
        else console.error(msg, error);
    };

    const isEditMode = {{ isset($employee) ? 'true' : 'false' }};
    const submitLabel = isEditMode ? 'Update Employee' : 'Create Employee';

    let current = 1;
    const total = 6;
    const icons = ['bi-person-fill', 'bi-briefcase-fill', 'bi-shield-fill', 'bi-award-fill', 'bi-bank2', 'bi-plus'];
    let advancedUnlocked = isEditMode;

    // Global Toast helper
    window.showToast = function(message, icon = 'success') {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: icon,
            title: message,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    }
</script>

@include('admin.register.validation_scripts')
@include('admin.register.submission_scripts')
@include('admin.register.navigation_scripts')

<script>
    @if(isset($employee) && isset($editData))
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            // Prefill form in edit mode
            prefillForm(@json($editData));
        }, 150);
    });

    function prefillForm(d) {
        function setVal(name, val) {
            if (val === null || val === undefined || val === '') return;
            const el = document.querySelector('[name="' + name + '"]');
            if (el) el.value = val;
        }

        function setRadio(name, val) {
            if (!val) return;
            const el = document.querySelector('[name="' + name + '"][value="' + val + '"]');
            if (el) el.checked = true;
        }

        function setSelect(name, val) {
            if (!val) return;
            const el = document.querySelector('[name="' + name + '"]');
            if (el) el.value = val;
        }

        if (d.organization_id) {
            const orgSel = document.getElementById('org_select');
            if (orgSel) {
                orgSel.value = d.organization_id;
                if (typeof onOrgChange === 'function') onOrgChange(d.organization_id);
            }
        }
        if (d.role_id) {
            setTimeout(function() {
                setSelect('role_id', d.role_id);
                if (typeof window.syncEmploymentRoleUI === 'function') {
                    window.syncEmploymentRoleUI();
                }
                if (d.sbu_id) {
                    const sbuSel = document.getElementById('sbu_select');
                    if (sbuSel) {
                        sbuSel.value = d.sbu_id;
                        if (typeof onSbuChange === 'function') onSbuChange(d.sbu_id);
                    }
                }
                if (d.department_id) {
                    const deptSel = document.getElementById('dept_select');
                    if (deptSel) deptSel.value = d.department_id;
                }
            }, 80);
        }

        setVal('full_name', d.full_name);
        setVal('father_name', d.father_name);
        setVal('cnic', d.cnic);
        setVal('cnic_expiry', d.cnic_expiry);
        setVal('father_cnic', d.father_cnic);
        if (d.nationality) {
            const natSel = document.getElementById('nationality_select');
            if (natSel) {
                natSel.dataset.prefill = d.nationality;
                natSel.value = d.nationality;
            }
        }
        setVal('dob', d.dob);
        setVal('ntn', d.ntn);
        setSelect('gender', d.gender);
        setVal('city_of_birth', d.city_of_birth);
        setSelect('religion', d.religion);
        setVal('sect', d.sect);
        setVal('spouse_name', d.spouse_name);
        setSelect('marital_status', d.marital_status);
        setVal('nok_name', d.nok_name);
        setVal('nok_cnic', d.nok_cnic);
        setVal('nok_relation', d.nok_relation);
        setVal('nok_dob', d.nok_dob);
        setVal('nok_contact', d.nok_contact);
        setVal('join_date', d.join_date);
        setVal('designation', d.designation);
        setVal('grade', d.grade);
        setVal('branch', d.branch);
        setVal('location', d.location);
        setVal('biometric_id', d.biometric_id);

        setRadio('employment_category', d.employment_category);
        setSelect('intern_type', d.intern_type);
        setVal('intern_duration', d.intern_duration);
        setSelect('contractual_type', d.contractual_type);
        setSelect('engagement_mode', d.engagement_mode);

        if (Array.isArray(d.hybrid_days)) {
            d.hybrid_days.forEach((day) => {
                const checkbox = document.querySelector('[name="hybrid_days[]"][value="' + day + '"]');
                if (checkbox) checkbox.checked = true;
            });
        }

        if (typeof toggleCategoryBlocks === 'function') {
            toggleCategoryBlocks();
        }

        if (d.photo_url) {
            const preview = document.getElementById('imgPreview');
            const wrapper = document.getElementById('imgPreviewWrapper');
            const uploadBox = document.getElementById('uploadImageBox');
            const removeBtn = document.getElementById('removeImageBtn');
            if (preview && wrapper && uploadBox) {
                preview.src = d.photo_url;
                wrapper.style.display = 'block';
                uploadBox.classList.add('d-none');
                if (removeBtn) removeBtn.classList.remove('d-none');
            }
        }

        if (d.police) {
            setRadio('verification_status', d.police.verification_status);
            setVal('msr_letter_no', d.police.msr_letter_no);
            setVal('addressee', d.police.addressee);
            setVal('verifying_authority', d.police.verifying_authority);
            setVal('verification_letter_no', d.police.verification_letter_no);
            setVal('next_verification_date', d.police.next_verification_date);
            setVal('police_remarks', d.police.remarks);
        }

        if (d.armed_force) {
            setVal('service_no', d.armed_force.service_no);
            setVal('rank', d.armed_force.rank);
            setVal('medical_category', d.armed_force.medical_category);
            setVal('date_of_commissioning', d.armed_force.date_of_commissioning);
            setVal('date_of_retirement', d.armed_force.date_of_retirement);
            setVal('reason_of_retirement', d.armed_force.reason_of_retirement);
            setVal('corps_regiment', d.armed_force.corps_regiment);
            setVal('ex_army_unit', d.armed_force.ex_army_unit);
            setVal('trade', d.armed_force.trade);
            setVal('pma_lc_ots', d.armed_force.pma_lc_ots);
        }

        if (d.contact) {
            setVal('residence_phone', d.contact.residence_phone);
            setVal('emergency_contact', d.contact.emergency_contact);
            setVal('cell_no', d.contact.cell_no);
            setVal('contact_email', d.contact.email);
            setVal('present_address', d.contact.present_address);
            setVal('permanent_address', d.contact.permanent_address);
        }

        if (d.bank) {
            setVal('account_title', d.bank.account_title);
            setVal('account_no', d.bank.account_no);
            setVal('bank_branch', d.bank.bank_branch);
            setRadio('account_type', d.bank.account_type);
        }

        if (d.family && d.family.length) {
            window.familyData = d.family;
            const famList = document.getElementById('familyListing');
            if (famList) famList.innerHTML = '';
            window.familyData.forEach(function(m, idx) {
                if (m && typeof appendFamilyCard === 'function') appendFamilyCard(idx, m, m.id);
            });
        }

        if (d.academics && d.academics.length) {
            window.academicsData = d.academics;
            const acList = document.getElementById('academicListing');
            if (acList) acList.innerHTML = '';
            window.academicsData.forEach(function(a, idx) {
                if (a && typeof appendAcademicCard === 'function') appendAcademicCard(idx, a, a.id);
            });
        }

        if (d.employments && d.employments.length) {
            window.employmentsData = d.employments;
            const emList = document.getElementById('employmentListing');
            if (emList) emList.innerHTML = '';
            window.employmentsData.forEach(function(e, idx) {
                if (e && typeof appendEmploymentCard === 'function') appendEmploymentCard(idx, e, e.id);
            });
        }

        if (d.medical) {
            setVal('last_fitness_test', d.medical.last_fitness_test);
            setRadio('has_disability', d.medical.has_disability);
            setVal('blood_group', d.medical.blood_group);
            setSelect('disability_type', d.medical.disability_type);
            setVal('disability_description', d.medical.disability_description);
        }

        if (d.references && d.references.length) {
            d.references.forEach(r => {
                const n = r.ref_number;
                setVal('ref' + n + '_name', r.name);
                setVal('ref' + n + '_designation', r.designation);
                setVal('ref' + n + '_organization', r.organization);
                setVal('ref' + n + '_contact', r.contact_no);
                setSelect('ref' + n + '_relationship', r.relationship);
            });
        }

        if (d.attachments && d.attachments.length) {
            if (typeof window.setExistingAttachments === 'function') {
                window.setExistingAttachments(d.attachments);
            }
        }

        if (typeof window.applyCnicMasks === 'function') {
            window.applyCnicMasks();
        }
    }
    @endif

    // Initial load
    updateStepGateStyles();
    applyStepNavigation(current);
</script>
@endpush