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
        padding: 1.3rem 2rem !important;
        color: var(--light-color) !important;
        white-space: nowrap !important;
    }

    td {
        padding: 1rem 2rem !important;
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
        font-size: 0.72rem;
        font-weight: 500;
        line-height: 1.1;
        margin-top: 4px;
        display: block;
        width: 100%;
        color: #dc3545; /* Bootstrap danger red */
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
<script>
   const isEditMode = {{ isset($employee) ? 'true' : 'false' }};
    const submitLabel = isEditMode ? 'Update Employee' : 'Create Employee';

    let current = 1;
    const total = 6;
    const icons = ['bi-person-fill', 'bi-briefcase-fill', 'bi-shield-fill', 'bi-award-fill', 'bi-bank2', 'bi-plus'];
    let advancedUnlocked = isEditMode;

    function updateStepGateStyles() {
        for (let s = 3; s <= 6; s++) {
            const pill = document.getElementById('step-pill-' + s);
            if (!pill) continue;
            if (!isEditMode && !advancedUnlocked) {
                pill.classList.add('step-pill-locked');
                pill.setAttribute('title', 'Complete General and Employment steps first');
            } else {
                pill.classList.remove('step-pill-locked');
                pill.removeAttribute('title');
            }
        }
    }

    function changeStep(dir) {
        if (dir === 1) {
            if (!validateStep(current)) return;
            
            // --- Step 6 Subsection Logic ---
            if (current === 6) {
                const subSections = ['s6-contact', 's6-family', 's6-academic', 's6-employment', 's6-medical', 's6-references'];
                const activeSub = document.querySelector('#step-6 .sub-section:not(.d-none)')?.id;
                const activeIdx = subSections.indexOf(activeSub);

                if (activeIdx !== -1 && activeIdx < subSections.length - 1) {
                    const nextSubId = subSections[activeIdx + 1];
                    const nextBtnInSidebar = document.querySelector(`[data-target="${nextSubId}"]`);

                    // Trigger save for current subsection if needed
                    if (activeSub === 's6-contact') {
                        saveContactSubsection(() => { if(nextBtnInSidebar) nextBtnInSidebar.click(); });
                    } else if (activeSub === 's6-medical') {
                        saveMedicalSubsection(() => { if(nextBtnInSidebar) nextBtnInSidebar.click(); });
                    } else {
                        // Table-based sections just move forward as rows are saved individually
                        if(nextBtnInSidebar) nextBtnInSidebar.click();
                    }
                    return;
                } else if (activeSub === 's6-references') {
                    serializeArrayData();
                    saveReferencesSubsection();
                    return;
                }
            }
            // -------------------------------

            if (current === 6) {
                serializeArrayData();
            }
            
            processStepSave(current, function() {
                if (current === total) {
                    window.location.href = '{{ route("admin.employee.index") }}';
                    return;
                }
                if (current === 2) {
                    advancedUnlocked = true;
                }
                goToStep(current + dir);
            });
            return;
        }

        // --- Step 6 Subsection Back Logic ---
        if (dir === -1 && current === 6) {
            const subSections = ['s6-contact', 's6-family', 's6-academic', 's6-employment', 's6-medical', 's6-references'];
            const activeSub = document.querySelector('#step-6 .sub-section:not(.d-none)')?.id;
            const activeIdx = subSections.indexOf(activeSub);
            if (activeIdx > 0) {
                const prevSubId = subSections[activeIdx - 1];
                document.querySelector(`[data-target="${prevSubId}"]`)?.click();
                return;
            }
        }
        // ------------------------------------

        goToStep(current + dir);
    }

    function processStepSave(step, onSuccess) {
        const form = document.getElementById('employeeForm');
        const formData = new FormData();
        formData.append('step', step);

        const activeStepDiv = document.getElementById('step-' + step);
        if (activeStepDiv) {
            const inputs = activeStepDiv.querySelectorAll('input[name], select[name], textarea[name]');
            inputs.forEach(input => {
                const name = input.getAttribute('name');
                if (input.type === 'checkbox' || input.type === 'radio') {
                    if (input.checked) formData.append(name, input.value);
                } else if (input.type === 'file') {
                    // Handled locally or separately, but included for completeness
                    // If it's profile_photo and we have a cropped blob, we SKIP it here
                    // because we'll add the cropped version later.
                    if (name === 'profile_photo' && (typeof croppedImageBlob !== 'undefined' && croppedImageBlob)) {
                        return;
                    }

                    if (input.files && input.files.length > 0) {
                        for(let i=0; i<input.files.length; i++) {
                            formData.append(name, input.files[i]);
                        }
                    }
                } else {
                    formData.append(name, input.value);
                }
            });
        }

        // Add cropped profile photo if exists
        if (typeof croppedImageBlob !== 'undefined' && croppedImageBlob) {
            formData.append('profile_photo', croppedImageBlob, originalFileName || 'profile_photo.jpg');
        }

        // Include any dynamically serialized arrays (Family, Academics, Employments)
        const hiddenArrayContainer = document.getElementById('hiddenArrayInputs');
        if (hiddenArrayContainer) {
            const hiddenInputs = hiddenArrayContainer.querySelectorAll('input[name]');
            hiddenInputs.forEach(input => {
                formData.append(input.name, input.value);
            });
        }

        const storedEmployeeId = document.getElementById('saved_employee_id')?.value;
        if (storedEmployeeId) {
            formData.append('employee_id', storedEmployeeId);
        }

        const nextBtn = document.getElementById('nextBtn');
        const prevBtn = document.getElementById('prevBtn');
        const originalText = nextBtn ? nextBtn.textContent : 'Save';
        if (nextBtn) {
            nextBtn.disabled = true;
            nextBtn.textContent = 'Saving…';
        }
        if (prevBtn) prevBtn.disabled = true;

        const attachmentPayload = typeof window.getAttachmentPayload === 'function' ?
            window.getAttachmentPayload() :
            {
                keptAttachmentIds: [],
                newAttachments: []
            };

        (attachmentPayload.keptAttachmentIds || []).forEach((id) => {
            formData.append('kept_attachment_ids[]', id);
        });

        (attachmentPayload.newAttachments || []).forEach((a, idx) => {
            formData.append(`attachments[${idx}][name]`, a.name || '');
            formData.append(`attachments[${idx}][type]`, a.type || '');
            formData.append(`attachments[${idx}][description]`, a.desc || '');
            (a.files || []).forEach((file) => {
                formData.append(`attachments[${idx}][files][]`, file);
            });
        });

        fetch('{{ route("admin.employee.save_step") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
            body: formData,
        })
        .then(async r => {
            const isJson = r.headers.get('content-type')?.includes('application/json');
            const data = isJson ? await r.json() : null;

            if (!r.ok) {
                if (r.status === 422 && data && data.errors) {
                    return { success: false, errors: data.errors };
                }
                throw new Error(data?.message || `Server error: ${r.status}`);
            }
            return data;
        })
        .then(data => {
            if (nextBtn) {
                nextBtn.disabled = false;
                nextBtn.textContent = originalText;
            }
            if (prevBtn) prevBtn.disabled = false;

            if (data.success) {
                clearStepErrors(); // Clear errors on success

                if (data.employee_id) {
                    let hiddenId = document.getElementById('saved_employee_id');
                    if (!hiddenId) {
                        hiddenId = document.createElement('input');
                        hiddenId.type = 'hidden';
                        hiddenId.id = 'saved_employee_id';
                        hiddenId.name = 'employee_id';
                        form.appendChild(hiddenId);
                    }
                    hiddenId.value = data.employee_id;
                }
                
                if (step === total) {
                    showSuccess('Employee registration completed successfully!', 'Completed').then(() => {
                        if (onSuccess) onSuccess();
                    });
                } else {
                    showSuccess(data.message, 'Saved').then(() => {
                        if (onSuccess) onSuccess();
                    });
                }
                
            } else if (data.errors) {
                showFieldErrors(data.errors);
            } else {
                showError(data.message || 'Something went wrong.');
            }
        })
        .catch((e) => {
            nextBtn.disabled = false;
            prevBtn.disabled = false;
            nextBtn.textContent = originalText;
            showError('Network error or server error occurred.');
        });
    }

    function clearStepErrors() {
        document.querySelectorAll('.step-val-error, .field-error-msg').forEach(e => e.remove());
        document.querySelectorAll('.is-invalid-step, .is-invalid').forEach(e => e.classList.remove('is-invalid-step', 'is-invalid'));
    }

    function markFieldInvalid(el, msg) {
        if (!el) return;
        el.classList.add('is-invalid', 'is-invalid-step');
        const div = document.createElement('div');
        div.className = 'step-val-error invalid-feedback d-block';
        div.textContent = msg;
        el.insertAdjacentElement('afterend', div);
    }

    function markRadioInvalid(name, msg) {
        const group = document.querySelectorAll('[name="' + name + '"]');
        if (!group.length) return;
        const wrapper = group[group.length - 1].closest('.d-flex, .form-check, div');
        if (wrapper) {
            const div = document.createElement('div');
            div.className = 'step-val-error text-danger small mt-1';
            div.textContent = msg;
            wrapper.insertAdjacentElement('afterend', div);
        }
    }

    function validateStep(step) {
        clearStepErrors();
        let valid = true;
        let firstEl = null;

        function req(name, label) {
            const el = document.querySelector('[name="' + name + '"]');
            if (!el) return;
            const val = el.value ? el.value.trim() : '';
            if (!val) {
                markFieldInvalid(el, label + ' is required.');
                if (!firstEl) firstEl = el;
                valid = false;
            }
        }

        function reqRadio(name, label) {
            const checked = document.querySelector('[name="' + name + '"]:checked');
            if (!checked) {
                markRadioInvalid(name, label + ' is required.');
                const first = document.querySelector('[name="' + name + '"]');
                if (!firstEl && first) firstEl = first;
                valid = false;
            }
        }

        if (step === 1) {
            req('full_name', 'Name');
            req('cnic', 'CNIC');
            req('cnic_expiry', 'CNIC Expiry Date');
            req('dob', 'Date of Birth');
            req('nationality', 'Nationality');
            req('marital_status', 'Marital Status');
        } else if (step === 2) {
            reqRadio('employment_category', 'Category');
            req('organization_id', 'Organization');
            req('role_id', 'Role');
            var rid = document.querySelector('[name="role_id"]');
            var role = null;
            if (rid && rid.value && window._rolesData) {
                role = window._rolesData.find(function(r) {
                    return String(r.id) === String(rid.value);
                });
            }
            var needSbuDept = role && role.department_id !== null && role.department_id !== undefined && role.department_id !== '';
            if (needSbuDept) {
                req('sbu_id', 'SBU');
                req('department_id', 'Department');
            }
            req('join_date', 'Date of Joining');
        } else if (step === 3) {
            reqRadio('verification_status', 'Verification Status');
        } else if (step === 5) {
            req('account_title', 'Account Title');
            req('account_no', 'Account No');
            req('bank_branch', 'Bank & Branch');
            reqRadio('account_type', 'A/C Type');
        } else if (step === 6) {
            req('cell_no', 'Cell Number');
            req('contact_email', 'Email');
            req('present_address', 'Present Address');
            req('permanent_address', 'Permanent Address');
            if (!valid) {
                const contactBtn = document.querySelector('[data-target="s6-contact"]');
                if (contactBtn) showSubSection(contactBtn, 's6-contact');
            }
        }

        if (!valid && firstEl) {
            firstEl.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }

        return valid;
    }

    function applyStepNavigation(target) {
        clearStepErrors(); // Always clear errors when switching steps
        document.getElementById('step-' + current).classList.remove('active');
        for (let i = 1; i <= total; i++) {
            if (i < target) updateCircle(i, 'done');
            else if (i === target) updateCircle(i, 'active');
            else updateCircle(i, 'pending');
        }
        current = target;
        document.getElementById('step-' + current).classList.add('active');
        
        // Hide Back button on Step 6 (user preference)
        document.getElementById('prevBtn').style.display = (current === 1 || current === 6) ? 'none' : 'inline-block';
        
        const nextBtn = document.getElementById('nextBtn');
        const navContainer = document.getElementById('wizard-navigation');
        if (navContainer) navContainer.style.display = 'flex';

        if (current === total) {
            const activeSub = document.querySelector('#step-6 .sub-section:not(.d-none)')?.id;
            if (activeSub === 's6-references') {
                nextBtn.style.display = 'inline-block';
                nextBtn.textContent = 'Submit Registration';
                nextBtn.className = 'btn ms-auto text-decoration-none text-white btn-success rounded-2 d-flex align-items-center border-0 px-3';
                nextBtn.onclick = () => changeStep(1);
            } else {
                nextBtn.style.display = 'inline-block';
                nextBtn.textContent = 'Next Section';
                nextBtn.className = 'btn ms-auto text-decoration-none text-white bg-main rounded-2 d-flex align-items-center border-0 px-3';
                nextBtn.onclick = () => changeStep(1);
            }
        } else {
            if (navContainer) navContainer.style.display = 'flex';
            if (nextBtn) {
                nextBtn.style.display = 'inline-block';
                nextBtn.textContent = 'Next';
                nextBtn.className = 'btn ms-auto text-decoration-none text-white bg-main rounded-2 d-flex align-items-center border-0 px-3';
                nextBtn.onclick = () => changeStep(1);
            }
        }
        updateStepGateStyles();
    }

    function goToStep(target) {
        if (target < 1 || target > total) return;

        if (!isEditMode) {
            if (target === 2 && current === 1) {
                if (!validateStep(1)) return;
            }
            if (target >= 3 && !advancedUnlocked) {
                if (!validateStep(1)) {
                    applyStepNavigation(1);
                    return;
                }
                if (!validateStep(2)) {
                    applyStepNavigation(2);
                    return;
                }
                advancedUnlocked = true;
            }
        }

        applyStepNavigation(target);
    }

    function updateCircle(step, state) {
        const pill = document.getElementById('step-pill-' + step);
        const icon = document.getElementById('circle-' + step);
        const con = document.getElementById('con-' + step);
        pill.classList.remove('is-active', 'is-done');
        if (state === 'done') {
            pill.classList.add('is-done');
            icon.innerHTML = '<i class="bi bi-check-lg"></i>';
            if (con) con.classList.add('is-done');
        } else if (state === 'active') {
            pill.classList.add('is-active');
            icon.innerHTML = `<i class="bi ${icons[step - 1]}"></i>`;
        } else {
            icon.innerHTML = `<i class="bi ${icons[step - 1]}"></i>`;
            if (con) con.classList.remove('is-done');
        }
    }

    function serializeArrayData() {
        const container = document.getElementById('hiddenArrayInputs');
        container.innerHTML = '';

        function addHidden(name, value) {
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = name;
            inp.value = value ?? '';
            container.appendChild(inp);
        }

        let familyIdx = 0;
        (window.familyData || []).forEach((m) => {
            if (!m) return;
            Object.entries(m).forEach(([k, v]) => addHidden(`family[${familyIdx}][${k}]`, v));
            familyIdx++;
        });
        document.querySelectorAll('#familyTable tr').forEach((row) => {
            const name = row.querySelector('.fm-name')?.value?.trim();
            if (!name) return;
            addHidden(`family[${familyIdx}][name]`, name);
            addHidden(`family[${familyIdx}][gender]`, row.querySelector('.fm-gender')?.value || '');
            addHidden(`family[${familyIdx}][dob]`, row.querySelector('.fm-dob')?.value || '');
            addHidden(`family[${familyIdx}][relation]`, row.querySelector('.fm-relation')?.value || '');
            addHidden(`family[${familyIdx}][occupation]`, row.querySelector('.fm-occupation')?.value || '');
            familyIdx++;
        });

        let acIdx = 0;
        (window.academicsData || []).forEach((a) => {
            if (!a) return;
            Object.entries(a).forEach(([k, v]) => addHidden(`academics[${acIdx}][${k}]`, v));
            acIdx++;
        });
        document.querySelectorAll('#academicTable tr').forEach((row) => {
            const degree = row.querySelector('.ac-degree')?.value?.trim();
            if (!degree) return;
            addHidden(`academics[${acIdx}][degree]`, degree);
            addHidden(`academics[${acIdx}][grade_cgpa]`, row.querySelector('.ac-grade')?.value || '');
            addHidden(`academics[${acIdx}][start_date]`, row.querySelector('.ac-start')?.value || '');
            addHidden(`academics[${acIdx}][end_date]`, row.querySelector('.ac-end')?.value || '');
            addHidden(`academics[${acIdx}][field_of_study]`, row.querySelector('.ac-field')?.value || '');
            addHidden(`academics[${acIdx}][institute]`, row.querySelector('.ac-institute')?.value || '');
            acIdx++;
        });

        let emIdx = 0;
        (window.employmentsData || []).forEach((e) => {
            if (!e) return;
            Object.entries(e).forEach(([k, v]) => addHidden(`employments[${emIdx}][${k}]`, v));
            emIdx++;
        });
        document.querySelectorAll('#employmentTable tr').forEach((row) => {
            const org = row.querySelector('.em-org')?.value?.trim();
            if (!org) return;
            addHidden(`employments[${emIdx}][organization]`, org);
            addHidden(`employments[${emIdx}][designation]`, row.querySelector('.em-desig')?.value || '');
            addHidden(`employments[${emIdx}][from_date]`, row.querySelector('.em-from')?.value || '');
            addHidden(`employments[${emIdx}][to_date]`, row.querySelector('.em-to')?.value || '');
            addHidden(`employments[${emIdx}][salary]`, row.querySelector('.em-salary')?.value || '');
            addHidden(`employments[${emIdx}][reason_for_leaving]`, row.querySelector('.em-reason')?.value || '');
            emIdx++;
        });
    }

    function showToast(type, message) {
        const toast = document.getElementById('formToast');
        const msg = document.getElementById('toastMsg');
        toast.classList.remove('text-bg-success', 'text-bg-danger', 'text-bg-warning');
        toast.classList.add(type === 'success' ? 'text-bg-success' : 'text-bg-danger');
        msg.textContent = message;
        new bootstrap.Toast(toast, {
            delay: 5000
        }).show();
    }

    function showFieldErrors(errors, context = document) {
        context.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid', 'is-invalid-step'));
        context.querySelectorAll('.field-error-msg, .step-val-error').forEach(el => el.remove());

        let highlightedCount = 0;
        const errorEntries = Object.entries(errors);

        errorEntries.forEach(([field, messages]) => {
            let fieldName = field;
            if (field.includes('.')) {
                const parts = field.split('.');
                fieldName = parts[0] + parts.slice(1).map(p => `[${p}]`).join('');
            }

            // Try exact name, then name with [], then class-based (fm-, ac-, em-), then ID
            let input = context.querySelector(`[name="${fieldName}"]`) || 
                        context.querySelector(`[name="${fieldName}[]"]`) ||
                        context.querySelector(`.fm-${fieldName}`) ||
                        context.querySelector(`.ac-${fieldName}`) ||
                        context.querySelector(`.em-${fieldName}`) ||
                        (context === document ? document.getElementById(fieldName) : null);

            if (input) {
                highlightedCount++;
                let targetEl = input;
                if (field === 'profile_photo') {
                    const box = document.getElementById('uploadImageBox');
                    if (box) targetEl = box;
                }

                targetEl.classList.add('is-invalid', 'is-invalid-step');
                const err = document.createElement('div');
                err.className = 'field-error-msg text-danger small';
                err.textContent = messages[0];
                
                if (field === 'profile_photo' || fieldName === 'profile_photo') {
                    const col = targetEl.closest('div[class*="col-"]');
                    if (col) {
                        col.appendChild(err);
                    } else {
                        targetEl.insertAdjacentElement('afterend', err);
                    }
                } else if (targetEl.type === 'radio' || targetEl.type === 'checkbox') {
                    const parentGroup = targetEl.closest('.d-flex, .form-check, div');
                    if (parentGroup) {
                        parentGroup.insertAdjacentElement('afterend', err);
                    } else {
                        targetEl.insertAdjacentElement('afterend', err);
                    }
                } else {
                    // If it's a table input, ensure it doesn't break the cell layout
                    const parentTd = targetEl.closest('td');
                    if (parentTd) {
                        parentTd.appendChild(err);
                    } else {
                        targetEl.insertAdjacentElement('afterend', err);
                    }
                }
            }
        });

        // Fallback to Swal if no fields were highlighted or if requested
        if (highlightedCount === 0 && errorEntries.length > 0) {
            let errorHtml = '<div class="text-danger text-start mt-2"><ul>';
            Object.values(errors).flat().forEach(msg => {
                errorHtml += `<li>${msg}</li>`;
            });
            errorHtml += '</ul></div>';

            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                html: errorHtml,
                confirmButtonColor: '#1a237e'
            });
        }

        const firstError = document.querySelector('.is-invalid');
        if (firstError) {
            firstError.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }
    }

    function submitEmployeeForm() {
        // Obsolete, handled by processStepSave
    }

    @if(isset($employee) && isset($editData))
    document.addEventListener('DOMContentLoaded', function() {
        // Small timeout ensures that all blade partial scripts (academic.blade.php,
        // familydetails.blade.php, etc.) have registered their JS helper functions
        // before prefillForm attempts to call appendFamilyCard, appendAcademicCard etc.
        setTimeout(function() {
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
        setVal('domicile_district', d.domicile_district);
        setVal('domicile_province', d.domicile_province);
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
        (function() {
            const empNumEl = document.getElementById('employee_number_display');
            if (!empNumEl) return;
            if (d.employee_code) {
                empNumEl.value = d.employee_code;
                empNumEl.placeholder = '';
            }
        })();
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
            const removeBtn = document.getElementById('removeImageBtn');
            const uploadBox = document.getElementById('uploadImageBox');

            if (preview && wrapper && uploadBox) {
                preview.src = d.photo_url;
                wrapper.style.display = 'block';
                uploadBox.classList.add('d-none');
                if (removeBtn) removeBtn.classList.remove('d-none');
            }
        }

        if (Array.isArray(d.attachments) && d.attachments.length && typeof window.setExistingAttachments === 'function') {
            window.setExistingAttachments(d.attachments);
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
            window.familyData = d.family.map(function(m) {
                return {
                    id: m.id || null,
                    name: m.name || '',
                    gender: m.gender || '',
                    dob: m.dob || '',
                    relation: m.relation || '',
                    occupation: m.occupation || '',
                };
            });
            const famList = document.getElementById('familyListing');
            if (famList) famList.innerHTML = '';
            if (typeof resetFamilyTableEmpty === 'function') resetFamilyTableEmpty();
            window.familyData.forEach(function(m, idx) {
                if (m && typeof appendFamilyCard === 'function') appendFamilyCard(idx, m, m.id);
            });
        }

        if (d.academics && d.academics.length) {
            window.academicsData = d.academics.map(function(a) {
                return {
                    id: a.id || null,
                    degree: a.degree || '',
                    grade_cgpa: a.grade_cgpa || '',
                    start_date: a.start_date || '',
                    end_date: a.end_date || '',
                    field_of_study: a.field_of_study || '',
                    institute: a.institute || '',
                };
            });
            const acList = document.getElementById('academicListing');
            if (acList) acList.innerHTML = '';
            if (typeof resetAcademicTableEmpty === 'function') resetAcademicTableEmpty();
            window.academicsData.forEach(function(a, idx) {
                if (a && typeof appendAcademicCard === 'function') appendAcademicCard(idx, a, a.id);
            });
        }

        if (d.employments && d.employments.length) {
            window.employmentsData = d.employments.map(function(e) {
                return {
                    id: e.id || null,
                    organization: e.organization || '',
                    designation: e.designation || '',
                    from_date: e.from_date || '',
                    to_date: e.to_date || '',
                    salary: e.salary || '',
                    reason_for_leaving: e.reason_for_leaving || '',
                };
            });
            const emList = document.getElementById('employmentListing');
            if (emList) emList.innerHTML = '';
            if (typeof resetEmploymentTableEmpty === 'function') resetEmploymentTableEmpty();
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
    }
    @endif

    updateStepGateStyles();
    applyStepNavigation(current);
    // Real-time error clearing
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('is-invalid') || e.target.classList.contains('is-invalid-step')) {
            e.target.classList.remove('is-invalid', 'is-invalid-step');
            
            // Clear standard errors
            let err = e.target.nextElementSibling;
            if (err && (err.classList.contains('field-error-msg') || err.classList.contains('step-val-error') || err.classList.contains('invalid-feedback'))) {
                err.remove();
            }
            
            // Handle radio/checkbox group errors
            const parent = e.target.closest('.d-flex, .form-check, div');
            if (parent && parent.nextElementSibling && (parent.nextElementSibling.classList.contains('field-error-msg') || parent.nextElementSibling.classList.contains('step-val-error'))) {
                parent.nextElementSibling.remove();
            }
        }
    });
</script>
@endpush