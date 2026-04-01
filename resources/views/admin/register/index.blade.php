@extends('layouts.app')

@section('title', isset($employee) ? 'Edit Employee - Admin Panel' : 'Register - Admin Panel')

@section('page-title', isset($employee) ? 'Edit Employee' : 'Register')

@push('styles')
    <link href="{{ asset('css/users.css') }}" rel="stylesheet">
    <style>
        .table { --bs-table-bg: transparent !important; }
        th { padding: 1.3rem 2rem !important; color: var(--light-color) !important; white-space: nowrap !important; }
        td { padding: 1rem 2rem !important; }
        form input, textarea, select, option { background: transparent !important; border: 2px solid #012445; box-shadow: 0 0 7px 4px #5a59593d; }
        select { border: white !important; }
        .section-title { display: none; }
        .step { display: none; }
        .step.active { display: block; }
        .check-input { box-shadow: none; }
        #formToast { position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 320px; }
    </style>
@endpush

@section('content')
    @include('admin.register.attachment-modal')

    {{-- Toast --}}
    <div id="formToast" class="toast align-items-center border-0 hide" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body fw-semibold" id="toastMsg"></div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>

    <div class="container">
        <div class="d-flex justify-content-between mb-4 align-items-center">
            <h5 class="text-center">{{ isset($employee) ? 'Edit Employee — ' . $employee->full_name : 'Employee Information Form' }}</h5>
            <div class="d-flex gap-3 align-items-center">
                <a href="{{ route('admin.employee.index') }}" class="btn btn-secondary d-flex align-items-center border-0 px-3 ms-auto">
                    Go Back
                </a>
                <button class="btn btn-link text-decoration-none text-white bg-main d-flex align-items-center border-0 px-3 ms-auto"
                    type="button" data-bs-toggle="modal" data-bs-target="#attachmentModal">
                    Attachment
                </button>
            </div>
        </div>
        <div class="card shadow-sm p-4">

            @include('admin.register.header')

            <form id="employeeForm" method="POST"
                  action="{{ isset($employee) ? route('admin.employee.update', $employee->id) : route('admin.employee.store') }}"
                  enctype="multipart/form-data" novalidate>
                @csrf

                @include('admin.register.general_info')
                @include('admin.register.personal_info')
                @include('admin.register.ex_employment')
                @include('admin.register.contact')
                @include('admin.register.bankdetails')
                @include('admin.register.familydetails')
                @include('admin.register.academic')

                {{-- Hidden container for serialized array data --}}
                <div id="hiddenArrayInputs"></div>
            </form>

            {{-- Navigation --}}
            <div class="d-flex justify-content-between mt-4">
                <button class="btn btn-outline-secondary" id="prevBtn" onclick="changeStep(-1)" style="display:none">Back</button>
                <button class="btn ms-auto text-decoration-none text-white bg-main rounded-2 d-flex align-items-center border-0 px-3"
                    id="nextBtn" onclick="changeStep(1)">Next</button>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const isEditMode = {{ isset($employee) ? 'true' : 'false' }};
        const submitLabel = isEditMode ? 'Update Employee' : 'Create Employee';

        let current = 1;
        const total = 7;
        const icons = ['bi-person-fill', 'bi-shield-fill', 'bi-award-fill', 'bi-telephone-fill', 'bi-bank2', 'bi-people-fill', 'bi-plus'];

        function changeStep(dir) {
            if (dir === 1 && current === total) {
                submitEmployeeForm();
                return;
            }
            goToStep(current + dir);
        }

        function goToStep(target) {
            if (target < 1 || target > total) return;
            document.getElementById('step-' + current).classList.remove('active');
            for (let i = 1; i <= total; i++) {
                if (i < target) updateCircle(i, 'done');
                else if (i === target) updateCircle(i, 'active');
                else updateCircle(i, 'pending');
            }
            current = target;
            document.getElementById('step-' + current).classList.add('active');
            document.getElementById('prevBtn').style.display = current === 1 ? 'none' : 'inline-block';
            const nextBtn = document.getElementById('nextBtn');
            if (current === total) {
                nextBtn.textContent = submitLabel;
                nextBtn.className   = 'btn btn-success ms-auto px-4';
                nextBtn.onclick     = submitEmployeeForm;
            } else {
                nextBtn.textContent = 'Next';
                nextBtn.className   = 'btn ms-auto text-decoration-none text-white bg-main rounded-2 d-flex align-items-center border-0 px-3';
                nextBtn.onclick     = () => changeStep(1);
            }
        }

        function updateCircle(step, state) {
            const pill = document.getElementById('step-pill-' + step);
            const icon = document.getElementById('circle-' + step);
            const con  = document.getElementById('con-' + step);
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
                inp.type  = 'hidden';
                inp.name  = name;
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
                addHidden(`family[${familyIdx}][name]`,       name);
                addHidden(`family[${familyIdx}][gender]`,     row.querySelector('.fm-gender')?.value || '');
                addHidden(`family[${familyIdx}][dob]`,        row.querySelector('.fm-dob')?.value || '');
                addHidden(`family[${familyIdx}][relation]`,   row.querySelector('.fm-relation')?.value || '');
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
                addHidden(`academics[${acIdx}][degree]`,         degree);
                addHidden(`academics[${acIdx}][grade_cgpa]`,     row.querySelector('.ac-grade')?.value || '');
                addHidden(`academics[${acIdx}][start_date]`,     row.querySelector('.ac-start')?.value || '');
                addHidden(`academics[${acIdx}][end_date]`,       row.querySelector('.ac-end')?.value || '');
                addHidden(`academics[${acIdx}][field_of_study]`, row.querySelector('.ac-field')?.value || '');
                addHidden(`academics[${acIdx}][institute]`,      row.querySelector('.ac-institute')?.value || '');
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
                addHidden(`employments[${emIdx}][organization]`,       org);
                addHidden(`employments[${emIdx}][designation]`,        row.querySelector('.em-desig')?.value || '');
                addHidden(`employments[${emIdx}][from_date]`,          row.querySelector('.em-from')?.value || '');
                addHidden(`employments[${emIdx}][to_date]`,            row.querySelector('.em-to')?.value || '');
                addHidden(`employments[${emIdx}][salary]`,             row.querySelector('.em-salary')?.value || '');
                addHidden(`employments[${emIdx}][reason_for_leaving]`, row.querySelector('.em-reason')?.value || '');
                emIdx++;
            });
        }

        function showToast(type, message) {
            const toast = document.getElementById('formToast');
            const msg   = document.getElementById('toastMsg');
            toast.classList.remove('text-bg-success', 'text-bg-danger', 'text-bg-warning');
            toast.classList.add(type === 'success' ? 'text-bg-success' : 'text-bg-danger');
            msg.textContent = message;
            new bootstrap.Toast(toast, { delay: 5000 }).show();
        }

        function showFieldErrors(errors) {
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            document.querySelectorAll('.field-error-msg').forEach(el => el.remove());
            Object.entries(errors).forEach(([field, messages]) => {
                const input = document.querySelector(`[name="${field}"]`);
                if (input) {
                    input.classList.add('is-invalid');
                    const err = document.createElement('div');
                    err.className   = 'field-error-msg text-danger small mt-1';
                    err.textContent = messages[0];
                    input.insertAdjacentElement('afterend', err);
                }
            });
            const firstError = document.querySelector('.is-invalid');
            if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function submitEmployeeForm() {
            serializeArrayData();

            const form     = document.getElementById('employeeForm');
            const formData = new FormData(form);
            const nextBtn  = document.getElementById('nextBtn');
            const attachmentPayload = typeof window.getAttachmentPayload === 'function'
                ? window.getAttachmentPayload()
                : { keptAttachmentIds: [], newAttachments: [] };

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

            nextBtn.disabled    = true;
            nextBtn.textContent = 'Saving…';

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: formData,
            })
            .then(r => r.json())
            .then(data => {
                nextBtn.disabled    = false;
                nextBtn.textContent = submitLabel;

                if (data.success) {
                    showToast('success', data.message);
                    setTimeout(() => { window.location.href = data.redirect || '{{ route("admin.employee.index") }}'; }, 1800);
                } else if (data.errors) {
                    showFieldErrors(data.errors);
                    showToast('error', 'Please fix the highlighted errors.');
                } else {
                    showToast('error', data.message || 'Something went wrong.');
                }
            })
            .catch(() => {
                nextBtn.disabled    = false;
                nextBtn.textContent = submitLabel;
                showToast('error', 'Network error. Please try again.');
            });
        }

        @if(isset($employee) && isset($editData))
        document.addEventListener('DOMContentLoaded', function () {
            prefillForm(@json($editData));
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
                    onOrgChange(d.organization_id);
                }
            }
            if (d.sbu_id) {
                setTimeout(function () {
                    const sbuSel = document.getElementById('sbu_select');
                    if (sbuSel) {
                        sbuSel.value = d.sbu_id;
                        onSbuChange(d.sbu_id);
                    }
                    if (d.department_id) {
                        setTimeout(function () {
                            const deptSel = document.getElementById('dept_select');
                            if (deptSel) {
                                deptSel.value = d.department_id;
                                if (typeof onDeptChange === 'function') onDeptChange(d.department_id);
                                if (d.role_id) {
                                    setTimeout(function () { setSelect('role_id', d.role_id); }, 25);
                                }
                            }
                        }, 50);
                    }
                }, 50);
            }

            setVal('full_name',         d.full_name);
            setVal('father_name',       d.father_name);
            setVal('cnic',              d.cnic);
            setVal('cnic_expiry',       d.cnic_expiry);
            setVal('father_cnic',       d.father_cnic);
            setVal('nationality',       d.nationality);
            setVal('dob',               d.dob);
            setVal('ntn',               d.ntn);
            setRadio('gender',          d.gender);
            setVal('domicile_district', d.domicile_district);
            setVal('domicile_province', d.domicile_province);
            setVal('city_of_birth',     d.city_of_birth);
            setVal('religion',          d.religion);
            setVal('sect',              d.sect);
            setVal('spouse_name',       d.spouse_name);
            setSelect('marital_status', d.marital_status);
            setVal('nok_name',          d.nok_name);
            setVal('nok_cnic',          d.nok_cnic);
            setVal('nok_relation',      d.nok_relation);
            setVal('nok_dob',           d.nok_dob);
            setVal('nok_contact',       d.nok_contact);
            setVal('join_date',         d.join_date);
            setVal('designation',       d.designation);
            setVal('grade',             d.grade);
            setVal('branch',            d.branch);
            setVal('location',          d.location);
            if (d.role_id) setTimeout(function () { setSelect('role_id', d.role_id); }, 0);
            setVal('biometric_id',      d.biometric_id);
            setRadio('employment_category', d.employment_category);
            setSelect('intern_type',    d.intern_type);
            setVal('intern_duration',   d.intern_duration);
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
                const label   = document.querySelector('label[for="uploadImage"]');
                if (preview) { preview.src = d.photo_url; preview.style.display = 'block'; }
                if (label)   { label.style.display = 'none'; }
            }
            if (typeof window.setExistingAttachments === 'function') {
                window.setExistingAttachments(d.attachments || []);
            }

            if (d.police) {
                setRadio('verification_status',    d.police.verification_status);
                setVal('msr_letter_no',            d.police.msr_letter_no);
                setVal('addressee',                d.police.addressee);
                setVal('verifying_authority',      d.police.verifying_authority);
                setVal('verification_letter_no',   d.police.verification_letter_no);
                setVal('next_verification_date',   d.police.next_verification_date);
                setVal('police_remarks',           d.police.remarks);
            }

            if (d.armed_force) {
                setVal('service_no',            d.armed_force.service_no);
                setVal('rank',                  d.armed_force.rank);
                setVal('medical_category',      d.armed_force.medical_category);
                setVal('date_of_commissioning', d.armed_force.date_of_commissioning);
                setVal('date_of_retirement',    d.armed_force.date_of_retirement);
                setVal('reason_of_retirement',  d.armed_force.reason_of_retirement);
                setVal('corps_regiment',        d.armed_force.corps_regiment);
                setVal('ex_army_unit',          d.armed_force.ex_army_unit);
                setVal('trade',                 d.armed_force.trade);
                setVal('pma_lc_ots',            d.armed_force.pma_lc_ots);
            }

            if (d.contact) {
                setVal('residence_phone',   d.contact.residence_phone);
                setVal('emergency_contact', d.contact.emergency_contact);
                setVal('cell_no',           d.contact.cell_no);
                setVal('contact_email',     d.contact.email);
                setVal('present_address',   d.contact.present_address);
                setVal('permanent_address', d.contact.permanent_address);
            }

            if (d.bank) {
                setVal('account_title',  d.bank.account_title);
                setVal('account_no',     d.bank.account_no);
                setVal('bank_branch',    d.bank.bank_branch);
                setRadio('account_type', d.bank.account_type);
            }

            if (d.family && d.family.length) {
                const tbody = document.getElementById('familyTable');
                tbody.innerHTML = '';
                d.family.forEach((m, i) => {
                    const esc = v => String(v || '').replace(/"/g, '&quot;');
                    tbody.insertAdjacentHTML('beforeend', `
                    <tr>
                        <td>${i + 1}</td>
                        <td><input type="text" class="form-control form-control-sm fm-name" value="${esc(m.name)}"></td>
                        <td><select class="form-select form-select-sm fm-gender">
                            <option value="">Select</option>
                            <option value="Male"${m.gender==='Male'?' selected':''}>Male</option>
                            <option value="Female"${m.gender==='Female'?' selected':''}>Female</option>
                        </select></td>
                        <td><input type="date" class="form-control form-control-sm fm-dob" value="${esc(m.dob)}"></td>
                        <td><input type="text" class="form-control form-control-sm fm-relation" value="${esc(m.relation)}"></td>
                        <td><input type="text" class="form-control form-control-sm fm-occupation" value="${esc(m.occupation)}"></td>
                        <td class="d-flex gap-1">
                            <button type="button" class="action-btn border-0 text-success bg-success-subtle" onclick="saveFamilyRow(this)" title="Save"><i class="bi bi-floppy"></i></button>
                            <button type="button" class="action-btn border-0 text-danger bg-danger-subtle" onclick="removeRow(this)" title="Delete"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>`);
                });
            }

            if (d.academics && d.academics.length) {
                const tbody = document.getElementById('academicTable');
                tbody.innerHTML = '';
                d.academics.forEach((a, i) => {
                    const esc = v => String(v || '').replace(/"/g, '&quot;');
                    tbody.insertAdjacentHTML('beforeend', `
                    <tr>
                        <td>${i + 1}</td>
                        <td><input type="text" class="form-control form-control-sm ac-degree" value="${esc(a.degree)}"></td>
                        <td><input type="text" class="form-control form-control-sm ac-grade" value="${esc(a.grade_cgpa)}"></td>
                        <td><input type="date" class="form-control form-control-sm ac-start" value="${esc(a.start_date)}"></td>
                        <td><input type="date" class="form-control form-control-sm ac-end" value="${esc(a.end_date)}"></td>
                        <td><input type="text" class="form-control form-control-sm ac-field" value="${esc(a.field_of_study)}"></td>
                        <td><input type="text" class="form-control form-control-sm ac-institute" value="${esc(a.institute)}"></td>
                        <td class="d-flex gap-1">
                            <button type="button" class="action-btn border-0 text-success bg-success-subtle" onclick="saveAcademicRow(this)" title="Save"><i class="bi bi-floppy"></i></button>
                            <button type="button" class="action-btn border-0 text-danger bg-danger-subtle" onclick="removeRow(this)" title="Delete"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>`);
                });
            }

            if (d.employments && d.employments.length) {
                const tbody = document.getElementById('employmentTable');
                tbody.innerHTML = '';
                d.employments.forEach((e, i) => {
                    const esc = v => String(v || '').replace(/"/g, '&quot;');
                    tbody.insertAdjacentHTML('beforeend', `
                    <tr>
                        <td>${i + 1}</td>
                        <td><input type="text" class="form-control form-control-sm em-org" value="${esc(e.organization)}"></td>
                        <td><input type="text" class="form-control form-control-sm em-desig" value="${esc(e.designation)}"></td>
                        <td><input type="date" class="form-control form-control-sm em-from" value="${esc(e.from_date)}"></td>
                        <td><input type="date" class="form-control form-control-sm em-to" value="${esc(e.to_date)}"></td>
                        <td><input type="text" class="form-control form-control-sm em-salary" value="${esc(e.salary)}"></td>
                        <td><input type="text" class="form-control form-control-sm em-reason" value="${esc(e.reason_for_leaving)}"></td>
                        <td class="d-flex gap-1">
                            <button type="button" class="action-btn border-0 text-success bg-success-subtle" onclick="saveEmploymentRow(this)" title="Save"><i class="bi bi-floppy"></i></button>
                            <button type="button" class="action-btn border-0 text-danger bg-danger-subtle" onclick="removeRow(this)" title="Delete"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>`);
                });
            }

            if (d.medical) {
                setVal('last_fitness_test',      d.medical.last_fitness_test);
                setRadio('has_disability',        d.medical.has_disability);
                setVal('blood_group',            d.medical.blood_group);
                setSelect('disability_type',     d.medical.disability_type);
                setVal('disability_description', d.medical.disability_description);
            }

            if (d.references && d.references.length) {
                d.references.forEach(r => {
                    const n = r.ref_number;
                    setVal('ref' + n + '_name',         r.name);
                    setVal('ref' + n + '_designation',  r.designation);
                    setVal('ref' + n + '_organization', r.organization);
                    setVal('ref' + n + '_contact',      r.contact_no);
                    setSelect('ref' + n + '_relationship', r.relationship);
                });
            }
        }
        @endif
    </script>
@endpush
