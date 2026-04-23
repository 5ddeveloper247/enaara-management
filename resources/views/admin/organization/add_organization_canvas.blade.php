<!-- Add Company Canvas -->
<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="addOrganizationCanvas" aria-labelledby="addOrganizationCanvasLabel" style="width: 600px;">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="addOrganizationCanvasLabel">
            <i class="bi bi-building-add me-2"></i>Add New Organization
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body">
        <form id="addOrganizationForm" action="{{ route('admin.organization.store') }}" method="POST" novalidate>
            @csrf

            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-info-circle me-2"></i>Basic Information
                </h6>

                <div class="mb-3">
                    <label for="parentId" class="form-label fw-semibold small text-white">Parent Organization</label>
                    <select class="form-select" id="parentId" name="parent_id">
                        <option value="">Select Parent Organization (Optional)</option>
                        @foreach($organizations as $organization)
                        <option value="{{ $organization->id }}" data-working-days="{{ implode(',', $organization->working_days ?? []) }}" data-working-start-time="{{ $organization->working_start_time ? substr((string) $organization->working_start_time, 0, 5) : '' }}" data-working-end-time="{{ $organization->working_end_time ? substr((string) $organization->working_end_time, 0, 5) : '' }}" data-opening-grace-period="{{ $organization->opening_grace_period ?? '' }}" data-closing-grace-period="{{ $organization->closing_grace_period ?? '' }}" {{ old('parent_id') == $organization->id ? 'selected' : '' }}>
                            {{ $organization->name }}
                        </option>
                        @endforeach
                    </select>
                    <small class="opacity-75 text-white">Leave empty if this is a top-level Organization</small>
                </div>

                <div class="mb-3">
                    <label for="orgName" class="form-label fw-semibold small text-white">Organization Name <span class="text-danger">*</span> <span class="text-white-50 fw-normal">(max 50)</span></label>
                    <input type="text" class="form-control" id="orgName" name="name" value="{{ old('name') }}" placeholder="e.g., Enaara Construction" maxlength="50" required>
                    <small class="d-block mt-1 text-white-50" id="orgNameMeta"><span id="orgNameLen">0</span> / 50</small>
                </div>

                <div class="mb-3">
                    <label for="orgCode" class="form-label fw-semibold small text-white">Organization Code <span class="text-white-50 fw-normal">(max 10)</span></label>
                    <input type="text" class="form-control" id="orgCode" name="code" value="{{ old('code') }}" placeholder="e.g., ENR-001" maxlength="10">
                    <small class="d-block mt-1 text-white-50" id="orgCodeMeta"><span id="orgCodeLen">0</span> / 10</small>
                </div>

                <div class="mb-3">
                    <label for="orgEmail" class="form-label fw-semibold small text-white">Email <span class="text-white-50 fw-normal">(max 255)</span></label>
                    <input type="email" class="form-control" id="orgEmail" name="email" value="{{ old('email') }}" placeholder="e.g., info@company.com" maxlength="255">
                    <small class="d-block mt-1 text-white-50" id="orgEmailMeta"><span id="orgEmailLen">0</span> / 255</small>
                </div>

                <div class="mb-3">
                    <label for="orgTaxNo" class="form-label fw-semibold small text-white">Tax Number <span class="text-white-50 fw-normal">(max 10)</span></label>
                    <input type="text" class="form-control" id="orgTaxNo" name="tax_no" value="{{ old('tax_no') }}" placeholder="e.g., TAX-123456" maxlength="10">
                    <small class="d-block mt-1 text-white-50" id="orgTaxNoMeta"><span id="orgTaxNoLen">0</span> / 10</small>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-card-text me-2"></i>Additional Details
                </h6>

                <div class="mb-3">
                    <label for="orgDescription" class="form-label fw-semibold small text-white">Description <span class="text-white-50 fw-normal">(max 255)</span></label>
                    <textarea class="form-control" id="orgDescription" name="description" rows="3" maxlength="255" placeholder="Enter company description">{{ old('description') }}</textarea>
                    <small class="d-block mt-1 text-white-50" id="orgDescriptionMeta"><span id="orgDescriptionLen">0</span> / 255</small>
                </div>

                <div class="mb-3">
                    <label for="orgAddress" class="form-label fw-semibold small text-white">Address <span class="text-white-50 fw-normal">(max 255)</span></label>
                    <textarea class="form-control" id="orgAddress" name="address" rows="3" maxlength="255" placeholder="Enter company address">{{ old('address') }}</textarea>
                    <small class="d-block mt-1 text-white-50" id="orgAddressMeta"><span id="orgAddressLen">0</span> / 255</small>
                </div>

                <div id="scheduleModeSection" class="mb-3 d-none">
                    <label class="form-label fw-semibold small text-white">Selection Mode</label>
                    <div class="btn-group w-100" role="group" aria-label="Selection Mode">
                        <input type="radio" class="btn-check" name="schedule_mode" id="scheduleModeStandard" value="standard" {{ old('schedule_mode', 'standard') === 'standard' ? 'checked' : '' }}>
                        <label class="btn btn-outline-light" for="scheduleModeStandard">Standard</label>
                        <input type="radio" class="btn-check" name="schedule_mode" id="scheduleModeCustom" value="custom" {{ old('schedule_mode') === 'custom' ? 'checked' : '' }}>
                        <label class="btn btn-outline-light" for="scheduleModeCustom">Custom</label>
                    </div>
                </div>

                <div id="workingScheduleFields">
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-white">Working Days</label>
                    <div class="d-flex flex-wrap gap-3">
                        @php($days = ['monday' => 'Mon', 'tuesday' => 'Tue', 'wednesday' => 'Wed', 'thursday' => 'Thu', 'friday' => 'Fri', 'saturday' => 'Sat', 'sunday' => 'Sun'])
                        @foreach($days as $dayValue => $dayLabel)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="workingDay_{{ $dayValue }}" name="working_days[]" value="{{ $dayValue }}" {{ in_array($dayValue, old('working_days', [])) ? 'checked' : '' }}>
                                <label class="form-check-label small text-white" for="workingDay_{{ $dayValue }}">{{ $dayLabel }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label for="orgWorkingStartTime" class="form-label fw-semibold small text-white">Working Start Time</label>
                        <input type="time" class="form-control" id="orgWorkingStartTime" name="working_start_time" value="{{ old('working_start_time') }}">
                    </div>
                    <div class="col-6">
                        <label for="orgWorkingEndTime" class="form-label fw-semibold small text-white">Working End Time</label>
                        <input type="time" class="form-control" id="orgWorkingEndTime" name="working_end_time" value="{{ old('working_end_time') }}">
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label for="orgOpeningGracePeriod" class="form-label fw-semibold small text-white">Opening Grace Period (min)</label>
                        <input type="number" min="0" max="600" class="form-control" id="orgOpeningGracePeriod" name="opening_grace_period" value="{{ old('opening_grace_period') }}">
                    </div>
                    <div class="col-6">
                        <label for="orgClosingGracePeriod" class="form-label fw-semibold small text-white">Closing Grace Period (min)</label>
                        <input type="number" min="0" max="600" class="form-control" id="orgClosingGracePeriod" name="closing_grace_period" value="{{ old('closing_grace_period') }}">
                    </div>
                </div>
                </div>

                <div class="mb-3">
                    <label for="orgStatus" class="form-label fw-semibold small text-white">Status <span class="text-danger">*</span></label>
                    <select class="form-select" id="orgStatus" name="is_active" required>
                        <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="offcanvas-footer border-top p-3" style="border-color: #ffffffab !important">
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Cancel</button>
                    <button type="submit" class="btn btn-light text-dark border-0" id="saveOrganizationBtn">
                        <i class="bi bi-check-lg me-1"></i>Create Company
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const addOrgCanvas = document.getElementById('addOrganizationCanvas');
        const addOrgForm = document.getElementById('addOrganizationForm');
        const saveOrgBtn = document.getElementById('saveOrganizationBtn');
        const parentId = document.getElementById('parentId');
        const scheduleModeSection = document.getElementById('scheduleModeSection');
        const scheduleModeStandard = document.getElementById('scheduleModeStandard');
        const scheduleModeCustom = document.getElementById('scheduleModeCustom');
        const workingScheduleFields = document.getElementById('workingScheduleFields');
        const workingDayCheckboxes = addOrgForm.querySelectorAll('input[name="working_days[]"]');
        const workingStartTime = document.getElementById('orgWorkingStartTime');
        const workingEndTime = document.getElementById('orgWorkingEndTime');
        const openingGracePeriod = document.getElementById('orgOpeningGracePeriod');
        const closingGracePeriod = document.getElementById('orgClosingGracePeriod');

        const ORG_LIMITED_FIELDS = [
            { fieldName: 'name', inputId: 'orgName', lenId: 'orgNameLen', metaId: 'orgNameMeta', max: 50 },
            { fieldName: 'code', inputId: 'orgCode', lenId: 'orgCodeLen', metaId: 'orgCodeMeta', max: 10 },
            { fieldName: 'email', inputId: 'orgEmail', lenId: 'orgEmailLen', metaId: 'orgEmailMeta', max: 255 },
            { fieldName: 'tax_no', inputId: 'orgTaxNo', lenId: 'orgTaxNoLen', metaId: 'orgTaxNoMeta', max: 10 },
            { fieldName: 'description', inputId: 'orgDescription', lenId: 'orgDescriptionLen', metaId: 'orgDescriptionMeta', max: 255 },
            { fieldName: 'address', inputId: 'orgAddress', lenId: 'orgAddressLen', metaId: 'orgAddressMeta', max: 255 },
        ];

        function clearValidationErrors(form) {
            form.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
            form.querySelectorAll('.validation-error-dynamic').forEach((el) => el.remove());
            ORG_LIMITED_FIELDS.forEach(function (cfg) {
                const meta = document.getElementById(cfg.metaId);
                if (meta) meta.classList.remove('text-danger');
            });
        }

        function removeClientLengthFeedback(fieldName) {
            const fb = addOrgForm.querySelector('[data-error-for="' + fieldName + '"][data-client-length="1"]');
            if (fb) fb.remove();
        }

        function syncOrgLimitedFieldsState() {
            if (!addOrgForm) return;
            ORG_LIMITED_FIELDS.forEach(function (cfg) {
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
                removeClientLengthFeedback(cfg.fieldName);
                if (len >= max) {
                    el.classList.add('is-invalid');
                    const feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback d-block validation-error-dynamic';
                    feedback.dataset.errorFor = cfg.fieldName;
                    feedback.dataset.clientLength = '1';
                    feedback.textContent = 'Maximum length is ' + max + ' characters.';
                    el.insertAdjacentElement('afterend', feedback);
                } else if (!addOrgForm.querySelector('[data-error-for="' + cfg.fieldName + '"]:not([data-client-length])')) {
                    el.classList.remove('is-invalid');
                }
            });
            syncSaveOrganizationButtonState();
        }

        function syncSaveOrganizationButtonState() {
            if (!saveOrgBtn || !addOrgForm) return;
            const htmlValid = typeof addOrgForm.checkValidity === 'function' ? addOrgForm.checkValidity() : true;
            saveOrgBtn.disabled = !htmlValid;
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

        function getSelectedParentOption() {
            if (!parentId) return null;
            return parentId.options[parentId.selectedIndex] || null;
        }

        function applyParentSchedule() {
            const option = getSelectedParentOption();
            if (!option || !option.value) return;
            const workingDays = (option.dataset.workingDays || '').split(',').filter(Boolean);
            workingDayCheckboxes.forEach((checkbox) => {
                checkbox.checked = workingDays.includes(checkbox.value);
            });
            workingStartTime.value = option.dataset.workingStartTime || '';
            workingEndTime.value = option.dataset.workingEndTime || '';
            openingGracePeriod.value = option.dataset.openingGracePeriod || '';
            closingGracePeriod.value = option.dataset.closingGracePeriod || '';
        }

        function toggleScheduleMode() {
            const hasParent = parentId && parentId.value !== '';
            if (!hasParent) {
                scheduleModeSection.classList.add('d-none');
                workingScheduleFields.classList.remove('pe-none', 'opacity-50');
                return;
            }
            scheduleModeSection.classList.remove('d-none');
            if (scheduleModeStandard.checked) {
                applyParentSchedule();
                workingScheduleFields.classList.add('pe-none', 'opacity-50');
            } else {
                workingScheduleFields.classList.remove('pe-none', 'opacity-50');
            }
        }

        addOrgForm.querySelectorAll('input, select, textarea').forEach(function (el) {
            el.addEventListener('change', syncOrgLimitedFieldsState);
            el.addEventListener('input', syncOrgLimitedFieldsState);
        });

        if (addOrgCanvas) {
            addOrgCanvas.addEventListener('hidden.bs.offcanvas', function() {
                addOrgForm.reset();
                document.getElementById('orgStatus').value = '1';
                clearValidationErrors(addOrgForm);
                scheduleModeStandard.checked = true;
                toggleScheduleMode();
                syncOrgLimitedFieldsState();
            });
            addOrgCanvas.addEventListener('shown.bs.offcanvas', function () {
                syncOrgLimitedFieldsState();
            });
        }

        parentId?.addEventListener('change', function() {
            if (parentId.value) {
                scheduleModeStandard.checked = true;
            }
            toggleScheduleMode();
        });
        scheduleModeStandard?.addEventListener('change', toggleScheduleMode);
        scheduleModeCustom?.addEventListener('change', toggleScheduleMode);

        if (addOrgForm) {
            addOrgForm.addEventListener('submit', function(e) {
                e.preventDefault();
                syncOrgLimitedFieldsState();
                if (!addOrgForm.checkValidity()) {
                    addOrgForm.reportValidity();
                    return;
                }

                const formData = new FormData(addOrgForm);
                const originalHtml = saveOrgBtn.innerHTML;

                saveOrgBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
                saveOrgBtn.disabled = true;

                fetch(addOrgForm.action, {
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
                        clearValidationErrors(addOrgForm);
                        const offcanvas = bootstrap.Offcanvas.getInstance(addOrgCanvas);
                        if (offcanvas) offcanvas.hide();

                        showSuccess(data.message || 'Organization created successfully.').then(() => {
                            window.location.reload();
                        });
                    } else if (status === 422) {
                        showValidationErrors(addOrgForm, data.errors || {});
                        syncOrgLimitedFieldsState();
                    } else {
                        showError(data.message || 'Failed to create organization.', 'System Error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('Something went wrong. Please try again.');
                })
                .finally(() => {
                    saveOrgBtn.innerHTML = originalHtml;
                    saveOrgBtn.disabled = false;
                    syncSaveOrganizationButtonState();
                });
            });
        }
        toggleScheduleMode();
        syncOrgLimitedFieldsState();
    });


    
</script>
