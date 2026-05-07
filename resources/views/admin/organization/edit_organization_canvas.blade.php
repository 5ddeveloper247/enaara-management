<!-- Edit Organization Canvas -->
<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="organizationEditCanvas" aria-labelledby="organizationEditCanvasLabel" style="width: 600px;">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="organizationEditCanvasLabel">
            <i class="bi bi-pencil-square me-2"></i>Edit Organization
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body">
        <form id="editOrganizationForm" method="POST" action="javascript:void(0);" novalidate>
            @csrf
            <input type="hidden" name="id" id="editOrgId">

            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-info-circle me-2"></i>Basic Information
                </h6>

                <div class="mb-3">
                    <label for="editParentId" class="form-label fw-semibold small text-white">Parent Organization</label>
                    <select class="form-select" id="editParentId" name="parent_id">
                        <option value="">Select Parent Organization (Optional)</option>
                        @foreach($organizations as $organization)
                            <option value="{{ $organization->id }}" data-working-days="{{ implode(',', $organization->working_days ?? []) }}" data-working-start-time="{{ $organization->working_start_time ? substr((string) $organization->working_start_time, 0, 5) : '' }}" data-working-end-time="{{ $organization->working_end_time ? substr((string) $organization->working_end_time, 0, 5) : '' }}" data-opening-grace-period="{{ $organization->opening_grace_period ?? '' }}" data-closing-grace-period="{{ $organization->closing_grace_period ?? '' }}">
                                {{ $organization->name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="opacity-75 text-white">Leave empty if this is a top-level Organization</small>
                </div>

                <div class="mb-3">
                    <label for="editOrgName" class="form-label fw-semibold small text-white">Organization Name <span class="text-danger">*</span> <span class="text-white-50 fw-normal">(max 50)</span></label>
                    <input type="text" class="form-control" id="editOrgName" name="name" placeholder="e.g., Enaara Construction" maxlength="50" required>
                    <small class="d-block mt-1 text-white-50" id="editOrgNameMeta"><span id="editOrgNameLen">0</span> / 50</small>
                </div>

                <div class="mb-3">
                    <label for="editOrgCode" class="form-label fw-semibold small text-white">Organization Code <span class="text-white-50 fw-normal">(max 10)</span></label>
                    <input type="text" class="form-control" id="editOrgCode" name="code" placeholder="e.g., ENR-001" maxlength="10">
                    <small class="d-block mt-1 text-white-50" id="editOrgCodeMeta"><span id="editOrgCodeLen">0</span> / 10</small>
                </div>

                <div class="mb-3">
                    <label for="editOrgEmail" class="form-label fw-semibold small text-white">Email <span class="text-white-50 fw-normal">(max 255)</span></label>
                    <input type="email" class="form-control" id="editOrgEmail" name="email" placeholder="e.g., info@company.com" maxlength="255">
                    <small class="d-block mt-1 text-white-50" id="editOrgEmailMeta"><span id="editOrgEmailLen">0</span> / 255</small>
                </div>

                <div class="mb-3">
                    <label for="editOrgTaxNo" class="form-label fw-semibold small text-white">Tax Number <span class="text-white-50 fw-normal">(max 10)</span></label>
                    <input type="text" class="form-control" id="editOrgTaxNo" name="tax_no" placeholder="e.g., TAX-123456" maxlength="10">
                    <small class="d-block mt-1 text-white-50" id="editOrgTaxNoMeta"><span id="editOrgTaxNoLen">0</span> / 10</small>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-card-text me-2"></i>Additional Details
                </h6>

                <div class="mb-3">
                    <label for="editOrgDescription" class="form-label fw-semibold small text-white">Description <span class="text-white-50 fw-normal">(max 255)</span></label>
                    <textarea class="form-control" id="editOrgDescription" name="description" rows="3" maxlength="255" placeholder="Enter organization description"></textarea>
                    <small class="d-block mt-1 text-white-50" id="editOrgDescriptionMeta"><span id="editOrgDescriptionLen">0</span> / 255</small>
                </div>

                <div class="mb-3">
                    <label for="editOrgAddress" class="form-label fw-semibold small text-white">Address <span class="text-white-50 fw-normal">(max 255)</span></label>
                    <textarea class="form-control" id="editOrgAddress" name="address" rows="3" maxlength="255" placeholder="Enter organization address"></textarea>
                    <small class="d-block mt-1 text-white-50" id="editOrgAddressMeta"><span id="editOrgAddressLen">0</span> / 255</small>
                </div>

                <div id="editScheduleModeSection" class="mb-3 d-none">
                    <label class="form-label fw-semibold small text-white">Selection Mode</label>
                    <div class="btn-group w-100" role="group" aria-label="Selection Mode">
                        <input type="radio" class="btn-check" name="schedule_mode" id="editScheduleModeStandard" value="standard" checked>
                        <label class="btn btn-outline-light" for="editScheduleModeStandard">Standard</label>
                        <input type="radio" class="btn-check" name="schedule_mode" id="editScheduleModeCustom" value="custom">
                        <label class="btn btn-outline-light" for="editScheduleModeCustom">Custom</label>
                    </div>
                </div>

                <div id="editWorkingScheduleFields">
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-white">Working Days</label>
                    <div class="d-flex flex-wrap gap-3">
                        @php($days = ['monday' => 'Mon', 'tuesday' => 'Tue', 'wednesday' => 'Wed', 'thursday' => 'Thu', 'friday' => 'Fri', 'saturday' => 'Sat', 'sunday' => 'Sun'])
                        @foreach($days as $dayValue => $dayLabel)
                            <div class="form-check">
                                <input class="form-check-input edit-working-day" type="checkbox" id="editWorkingDay_{{ $dayValue }}" name="working_days[]" value="{{ $dayValue }}">
                                <label class="form-check-label small text-white" for="editWorkingDay_{{ $dayValue }}">{{ $dayLabel }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-4">
                        <label for="editOrgGracePeriod" class="form-label fw-semibold small text-white">Grace Period (min)</label>
                        <input type="number" min="0" max="600" class="form-control" id="editOrgGracePeriod" name="grace_period">
                    </div>
                    <div class="col-4">
                        <label for="editOrgWorkingStartTime" class="form-label fw-semibold small text-white">Working Start Time</label>
                        <input type="time" class="form-control" id="editOrgWorkingStartTime" name="working_start_time">
                    </div>
                    <div class="col-4">
                        <label for="editOrgWorkingEndTime" class="form-label fw-semibold small text-white">Working End Time</label>
                        <input type="time" class="form-control" id="editOrgWorkingEndTime" name="working_end_time">
                    </div>
                </div>
                </div>

                <div class="mb-3">
                    <label for="editOrgStatus" class="form-label fw-semibold small text-white">Status <span class="text-danger">*</span></label>
                    <select class="form-select" id="editOrgStatus" name="is_active" required>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
        </form>
    </div>

    <div class="offcanvas-footer border-top p-3" style="border-color: #ffffffab !important">
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Cancel</button>
            <button type="submit" form="editOrganizationForm" class="btn btn-light text-dark border-0" id="updateOrganizationBtn">
                <i class="bi bi-check-lg me-1"></i>Update Organization
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const editForm = document.getElementById('editOrganizationForm');
    const editCanvas = document.getElementById('organizationEditCanvas');
    const updateBtn = document.getElementById('updateOrganizationBtn');
    const editParentId = document.getElementById('editParentId');
    const editScheduleModeSection = document.getElementById('editScheduleModeSection');
    const editScheduleModeStandard = document.getElementById('editScheduleModeStandard');
    const editScheduleModeCustom = document.getElementById('editScheduleModeCustom');
    const editWorkingScheduleFields = document.getElementById('editWorkingScheduleFields');
    const editWorkingDayCheckboxes = document.querySelectorAll('.edit-working-day');
    const editWorkingStartTime = document.getElementById('editOrgWorkingStartTime');
    const editWorkingEndTime = document.getElementById('editOrgWorkingEndTime');
    const editOrgGracePeriod = document.getElementById('editOrgGracePeriod');

    const EDIT_ORG_LIMITED_FIELDS = [
        { fieldName: 'name', inputId: 'editOrgName', lenId: 'editOrgNameLen', metaId: 'editOrgNameMeta', max: 50 },
        { fieldName: 'code', inputId: 'editOrgCode', lenId: 'editOrgCodeLen', metaId: 'editOrgCodeMeta', max: 10 },
        { fieldName: 'email', inputId: 'editOrgEmail', lenId: 'editOrgEmailLen', metaId: 'editOrgEmailMeta', max: 255 },
        { fieldName: 'tax_no', inputId: 'editOrgTaxNo', lenId: 'editOrgTaxNoLen', metaId: 'editOrgTaxNoMeta', max: 10 },
        { fieldName: 'description', inputId: 'editOrgDescription', lenId: 'editOrgDescriptionLen', metaId: 'editOrgDescriptionMeta', max: 255 },
        { fieldName: 'address', inputId: 'editOrgAddress', lenId: 'editOrgAddressLen', metaId: 'editOrgAddressMeta', max: 255 },
    ];

    function clearValidationErrors(form) {
        form.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
        form.querySelectorAll('.validation-error-dynamic').forEach((el) => el.remove());
        EDIT_ORG_LIMITED_FIELDS.forEach(function (cfg) {
            const meta = document.getElementById(cfg.metaId);
            if (meta) meta.classList.remove('text-danger');
        });
    }

    function removeEditClientLengthFeedback(form, fieldName) {
        const fb = form.querySelector('[data-error-for="' + fieldName + '"][data-client-length="1"]');
        if (fb) fb.remove();
    }

    function syncEditOrgLimitedFieldsState() {
        if (!editForm) return;
        EDIT_ORG_LIMITED_FIELDS.forEach(function (cfg) {
            const el = document.getElementById(cfg.inputId);
            if (!el) return;
            const max = cfg.max;
            if (el.value.length > max) {
                el.value = el.value.substring(0, max);
            }
            const len = el.value.length;
            const lenEl = document.getElementById(cfg.lenId);
            const metaEl = document.getElementById(cfg.metaId);
            if (lenEl) lenEl.textContent = String(len);
            if (metaEl) metaEl.classList.toggle('text-danger', len >= max);
            removeEditClientLengthFeedback(editForm, cfg.fieldName);
            if (len >= max) {
                el.classList.add('is-invalid');
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback d-block validation-error-dynamic';
                feedback.dataset.errorFor = cfg.fieldName;
                feedback.dataset.clientLength = '1';
                feedback.textContent = 'Maximum length is ' + max + ' characters.';
                el.insertAdjacentElement('afterend', feedback);
            } else if (!editForm.querySelector('[data-error-for="' + cfg.fieldName + '"]:not([data-client-length])')) {
                el.classList.remove('is-invalid');
            }
        });
        syncUpdateOrganizationButtonState();
    }

    function syncUpdateOrganizationButtonState() {
        if (!updateBtn || !editForm) return;
        const ok = typeof editForm.checkValidity === 'function' ? editForm.checkValidity() : true;
        updateBtn.disabled = !ok;
    }

    function appendFieldError(form, fieldName, message) {
        const normalizedField = fieldName.replace(/\.\d+$/, '');
        if (normalizedField === 'working_days') {
            const checkboxes = form.querySelectorAll('input[name="working_days[]"]');
            checkboxes.forEach((checkbox) => checkbox.classList.add('is-invalid'));
            const wrapper = checkboxes.length ? checkboxes[0].closest('.mb-3') : null;
            if (wrapper && !wrapper.querySelector('[data-error-for="working_days"]')) {
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback d-block validation-error-dynamic';
                feedback.dataset.errorFor = 'working_days';
                feedback.textContent = message;
                wrapper.appendChild(feedback);
            }
            return;
        }

        if (normalizedField === 'grace_period' || normalizedField === 'opening_grace_period' || normalizedField === 'closing_grace_period') {
            const graceEl = form.querySelector('[name="grace_period"]');
            if (graceEl) {
                graceEl.classList.add('is-invalid');
                if (!form.querySelector('[data-error-for="grace_period"]:not([data-client-length])')) {
                    const feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback d-block validation-error-dynamic';
                    feedback.dataset.errorFor = 'grace_period';
                    feedback.textContent = message;
                    graceEl.insertAdjacentElement('afterend', feedback);
                }
            }
            return;
        }

        const fieldElement = form.querySelector(`[name="${normalizedField}"]`) || form.querySelector(`[name="${normalizedField}[]"]`);
        if (!fieldElement) return;
        fieldElement.classList.add('is-invalid');
        if (form.querySelector('[data-error-for="' + normalizedField + '"]:not([data-client-length])')) return;
        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback d-block validation-error-dynamic';
        feedback.dataset.errorFor = normalizedField;
        feedback.textContent = message;
        fieldElement.insertAdjacentElement('afterend', feedback);
    }

    function showValidationErrors(form, errors) {
        clearValidationErrors(form);
        Object.entries(errors || {}).forEach(([field, messages]) => {
            const firstMessage = Array.isArray(messages) ? messages[0] : messages;
            if (firstMessage) {
                appendFieldError(form, field, firstMessage);
            }
        });
        const firstInvalid = form.querySelector('.is-invalid');
        if (firstInvalid && typeof firstInvalid.focus === 'function') {
            firstInvalid.focus();
        }
    }

    const updateRouteTemplate = `{{ route('admin.organization.update', ['id' => '__id__']) }}`;
    const editRouteTemplate = `{{ route('admin.organization.edit', ['id' => '__id__']) }}`;

    function getSelectedParentOption() {
        if (!editParentId) return null;
        return editParentId.options[editParentId.selectedIndex] || null;
    }

    function getParentSchedule() {
        const option = getSelectedParentOption();
        if (!option || !option.value) {
            return {
                workingDays: [],
                workingStartTime: '',
                workingEndTime: '',
                gracePeriod: ''
            };
        }
        return {
            workingDays: (option.dataset.workingDays || '').split(',').filter(Boolean),
            workingStartTime: option.dataset.workingStartTime || '',
            workingEndTime: option.dataset.workingEndTime || '',
            gracePeriod: (option.dataset.openingGracePeriod || option.dataset.closingGracePeriod || '')
        };
    }

    function applyParentSchedule() {
        const schedule = getParentSchedule();
        editWorkingDayCheckboxes.forEach((checkbox) => {
            checkbox.checked = schedule.workingDays.includes(checkbox.value);
        });
        editWorkingStartTime.value = schedule.workingStartTime;
        editWorkingEndTime.value = schedule.workingEndTime;
        if (editOrgGracePeriod) {
            editOrgGracePeriod.value = schedule.gracePeriod;
        }
    }

    function schedulesMatchParent(currentWorkingDays, currentStartTime, currentEndTime, currentGracePeriod) {
        const parentSchedule = getParentSchedule();
        const normalizedCurrentDays = [...currentWorkingDays].sort().join(',');
        const normalizedParentDays = [...parentSchedule.workingDays].sort().join(',');
        return normalizedCurrentDays === normalizedParentDays
            && (currentStartTime || '') === parentSchedule.workingStartTime
            && (currentEndTime || '') === parentSchedule.workingEndTime
            && (currentGracePeriod || '') === (parentSchedule.gracePeriod || '');
    }

    function toggleEditScheduleMode() {
        const hasParent = editParentId && editParentId.value !== '';
        if (!hasParent) {
            editScheduleModeSection.classList.add('d-none');
            editWorkingScheduleFields.classList.remove('pe-none', 'opacity-50');
            return;
        }
        editScheduleModeSection.classList.remove('d-none');
        if (editScheduleModeStandard.checked) {
            applyParentSchedule();
            editWorkingScheduleFields.classList.add('pe-none', 'opacity-50');
        } else {
            editWorkingScheduleFields.classList.remove('pe-none', 'opacity-50');
        }
    }

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.edit-organization-btn');
        if (!btn) return;

        const orgId = btn.dataset.orgId;

        fetch(editRouteTemplate.replace('__id__', orgId), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(response => {
            if (!response.success) {
                showError(response.message || 'Failed to load organization data.');
                return;
            }
            clearValidationErrors(editForm);

            const org = response.data;

            editForm.action = updateRouteTemplate.replace('__id__', org.id);

            document.getElementById('editOrgId').value = org.id ?? '';
            document.getElementById('editOrgName').value = org.name ?? '';
            document.getElementById('editOrgCode').value = org.code ?? '';
            document.getElementById('editOrgEmail').value = org.email ?? '';
            document.getElementById('editOrgTaxNo').value = org.tax_no ?? '';
            document.getElementById('editOrgDescription').value = org.description ?? '';
            document.getElementById('editOrgAddress').value = org.address ?? '';
            document.getElementById('editOrgWorkingStartTime').value = (org.working_start_time ?? '').toString().slice(0, 5);
            document.getElementById('editOrgWorkingEndTime').value = (org.working_end_time ?? '').toString().slice(0, 5);
            const loadedGrace = (org.opening_grace_period ?? org.closing_grace_period ?? '').toString();
            document.getElementById('editOrgGracePeriod').value = loadedGrace;
            document.getElementById('editOrgStatus').value = org.is_active ? '1' : '0';
            document.getElementById('editParentId').value = org.parent_id ?? '';
            const workingDays = Array.isArray(org.working_days) ? org.working_days : [];
            document.querySelectorAll('.edit-working-day').forEach((checkbox) => {
                checkbox.checked = workingDays.includes(checkbox.value);
            });
            const currentStartTime = (org.working_start_time ?? '').toString().slice(0, 5);
            const currentEndTime = (org.working_end_time ?? '').toString().slice(0, 5);
            const currentGracePeriod = loadedGrace;
            if (org.parent_id) {
                if (schedulesMatchParent(workingDays, currentStartTime, currentEndTime, currentGracePeriod)) {
                    editScheduleModeStandard.checked = true;
                } else {
                    editScheduleModeCustom.checked = true;
                }
            } else {
                editScheduleModeCustom.checked = true;
            }
            // Hide the current organization from parent options to prevent self-parenting
            Array.from(editParentId.options).forEach(option => {
                if (option.value === org.id.toString()) {
                    option.style.display = 'none';
                    option.disabled = true;
                } else {
                    option.style.display = 'block';
                    option.disabled = false;
                }
            });

            toggleEditScheduleMode();
            syncEditOrgLimitedFieldsState();
        })
        .catch(error => {
            console.error('Edit fetch error:', error);
            showError('Something went wrong while loading organization data.');
        });
    });

    editParentId?.addEventListener('change', function () {
        if (editParentId.value) {
            editScheduleModeStandard.checked = true;
        } else {
            editScheduleModeCustom.checked = true;
        }
        toggleEditScheduleMode();
    });
    editScheduleModeStandard?.addEventListener('change', toggleEditScheduleMode);
    editScheduleModeCustom?.addEventListener('change', toggleEditScheduleMode);

    editForm.querySelectorAll('input, select, textarea').forEach(function (el) {
        el.addEventListener('input', syncEditOrgLimitedFieldsState);
        el.addEventListener('change', syncEditOrgLimitedFieldsState);
    });

    editForm.addEventListener('submit', function (e) {
        e.preventDefault();
        syncEditOrgLimitedFieldsState();
        if (!editForm.checkValidity()) {
            editForm.reportValidity();
            return;
        }

        if (!editForm.action || editForm.action.includes('javascript:void(0)')) {
            Swal.fire({
                icon: 'warning',
                title: 'No Organization Selected',
                text: 'Please select an organization first.',
                confirmButtonColor: '#1a237e'
            });
            return;
        }

        const formData = new FormData(editForm);
        const originalHtml = updateBtn.innerHTML;

        updateBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...';
        updateBtn.disabled = true;

        fetch(editForm.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json().then(data => ({ status: response.status, data })))
        .then(({ status, data }) => {
            if (status === 200 || data.success) {
                clearValidationErrors(editForm);
                const offcanvas = bootstrap.Offcanvas.getInstance(editCanvas);
                if (offcanvas) offcanvas.hide();

                showSuccess(data.message || 'Organization updated successfully.').then(() => {
                    window.location.reload();
                });
            } else if (status === 422) {
                showValidationErrors(editForm, data.errors || {});
                syncEditOrgLimitedFieldsState();
            } else {
                showError(data.message || 'Failed to update organization.');
            }
        })
        .catch(error => {
            console.error('Update catch error:', error);
            showError('Something went wrong while updating organization data.');
        })
        .finally(() => {
            updateBtn.innerHTML = originalHtml;
            updateBtn.disabled = false;
            syncEditOrgLimitedFieldsState();
        });
    });

    if (editCanvas) {
        editCanvas.addEventListener('hidden.bs.offcanvas', function () {
            editForm.reset();
            editForm.action = 'javascript:void(0);';
            clearValidationErrors(editForm);

            document.getElementById('editOrgId').value = '';
            document.getElementById('editOrgStatus').value = '1';
            document.getElementById('editParentId').value = '';
            document.getElementById('editOrgWorkingStartTime').value = '';
            document.getElementById('editOrgWorkingEndTime').value = '';
            document.getElementById('editOrgGracePeriod').value = '';
            document.querySelectorAll('.edit-working-day').forEach((checkbox) => {
                checkbox.checked = false;
            });
            editScheduleModeStandard.checked = true;
            // Reset parent dropdown options visibility
            Array.from(editParentId.options).forEach(option => {
                option.style.display = 'block';
                option.disabled = false;
            });
            toggleEditScheduleMode();
            syncEditOrgLimitedFieldsState();
        });
    }
    toggleEditScheduleMode();
    syncEditOrgLimitedFieldsState();
});
</script>
