<!-- Add/Edit Shift Canvas -->
<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="addShiftCanvas" aria-labelledby="addShiftCanvasLabel" style="width: 600px;">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="addShiftCanvasLabel">
            <i class="bi bi-plus-circle me-2"></i>Add New Shift
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="addShiftForm" method="POST" action="{{ route('admin.shift-planner.store') }}">
            @csrf
            <div id="shiftFormErrorBox" class="alert alert-danger py-2 px-3 small d-none" role="alert"></div>

            <!-- Hidden ID for update -->
            <input type="hidden" name="id" id="shiftId">

            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-building me-2"></i>Organization & SBU
                </h6>

                <div class="mb-3">
                    <label for="shift_organization_id" class="form-label fw-semibold small text-white">
                        Organization <span class="text-danger">*</span>
                    </label>
                    <select class="form-select" id="shift_organization_id" name="organization_id" required>
                        <option value="" hidden selected>— Select Organization —</option>
                        @foreach($organizations ?? [] as $organization)
                            <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="shift_sbu_id" class="form-label fw-semibold small text-white">
                        SBU <span class="text-danger">*</span>
                    </label>
                    <select class="form-select" id="shift_sbu_id" name="sbu_id" disabled required>
                        <option value="" hidden selected>— Select SBU —</option>
                    </select>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Shift Name -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-info-circle me-2"></i>Basic Information
                </h6>

                <div class="mb-3">
                    <label class="form-label fw-semibold small text-white">
                        Shift Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="name" id="shiftName" class="form-control"
                        placeholder="e.g., Morning Shift, Night Shift" required>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Shift Timing -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-clock me-2"></i>Shift Timing
                </h6>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold small text-white">
                            Start Time <span class="text-danger">*</span>
                        </label>
                        <input type="time" name="start_time" id="shiftStartTime"
                            class="form-control" value="09:00" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold small text-white">
                            End Time <span class="text-danger">*</span>
                        </label>
                        <input type="time" name="end_time" id="shiftEndTime"
                            class="form-control" value="18:00" required>
                    </div>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Clock-in/Out Window -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-calendar-check me-2"></i>Clock-in/Out Window
                </h6>

                <div class="mb-3">
                    <label class="form-label fw-semibold small text-white">
                        Clock-in Window (minutes before start) <span class="text-danger">*</span>
                    </label>
                    <input type="number" name="clock_in_window_minutes" id="clockInWindow"
                        class="form-control" min="0" max="120" value="30" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small text-white">
                        Clock-out Window (minutes after end) <span class="text-danger">*</span>
                    </label>
                    <input type="number" name="clock_out_window_minutes" id="clockOutWindow"
                        class="form-control" min="0" max="120" value="30" required>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Grace Period & Break Time -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-hourglass-split me-2"></i>Grace Period & Break
                </h6>

                <div class="mb-3">
                    <label class="form-label fw-semibold small text-white">
                        Grace Period (minutes) <span class="text-danger">*</span>
                    </label>
                    <input type="number" name="grace_period_minutes" id="gracePeriod"
                        class="form-control" min="0" max="60" value="15" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small text-white">
                        Break Time (minutes) <span class="text-danger">*</span>
                    </label>
                    <input type="number" name="break_time_minutes" id="breakTime"
                        class="form-control" min="0" max="180" value="60" required>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Overtime Settings -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-arrow-repeat me-2"></i>Overtime Settings
                </h6>

                <div class="mb-3">
                    <div class="form-check form-switch mb-3">
                        <!-- hidden for unchecked -->
                        <input type="hidden" name="overtime_allowed" value="0">

                        <input class="form-check-input" type="checkbox"
                            name="overtime_allowed" id="overtimeAllowed" value="1" checked>

                        <label class="form-check-label text-white">
                            Allow Overtime
                        </label>
                    </div>
                </div>

                <div id="overtimeTriggerSection">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-white">
                            Overtime Trigger (hours)
                        </label>
                        <input type="number" name="overtime_trigger_hours" id="overtimeTrigger"
                            class="form-control" min="1" max="12" value="8" step="0.5">
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="offcanvas-footer border-top p-3" style="border-color: #ffffffab !important">
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Cancel</button>
            @if(validatePermissions('admin/shift-planner/add') || validatePermissions('admin/shift-planner/edit'))
            <button type="button" class="btn btn-light text-dark border-0" id="saveShiftBtn">
                <i class="bi bi-check-lg me-1"></i>Save Shift
            </button>
            @endif
        </div>
    </div>
</div>
<script>
    window.shiftPlannerOrganizations = @json($organizations ?? []);
    window.viewerEmployeeScope = @json($viewerEmployeeScope ?? []);

    document.addEventListener('DOMContentLoaded', function() {
        const organizations = Array.isArray(window.shiftPlannerOrganizations) ? window.shiftPlannerOrganizations : [];
        const viewerScope = window.viewerEmployeeScope || {};
        const addShiftCanvas = document.getElementById('addShiftCanvas');
        const addShiftForm = document.getElementById('addShiftForm');
        const saveBtn = document.getElementById('saveShiftBtn');
        const canvasLabel = document.getElementById('addShiftCanvasLabel');
        let isShiftSaving = false;

        const shiftIdInput = document.getElementById('shiftId');
        const shiftOrganization = document.getElementById('shift_organization_id');
        const shiftSbu = document.getElementById('shift_sbu_id');
        const shiftName = document.getElementById('shiftName');
        const shiftStartTime = document.getElementById('shiftStartTime');
        const shiftEndTime = document.getElementById('shiftEndTime');
        const clockInWindow = document.getElementById('clockInWindow');
        const clockOutWindow = document.getElementById('clockOutWindow');
        const gracePeriod = document.getElementById('gracePeriod');
        const breakTime = document.getElementById('breakTime');
        const overtimeAllowed = document.getElementById('overtimeAllowed');
        const overtimeTriggerSection = document.getElementById('overtimeTriggerSection');
        const overtimeTrigger = document.getElementById('overtimeTrigger');

        function findOrganization(orgId) {
            return organizations.find(function(org) {
                return String(org.id) === String(orgId);
            }) || null;
        }

        function setSelectOptions(select, placeholder, items, selectedValue) {
            if (!select) {
                return;
            }

            select.innerHTML = '';

            if (placeholder) {
                const placeholderOption = document.createElement('option');
                placeholderOption.value = '';
                placeholderOption.hidden = true;
                placeholderOption.textContent = placeholder;
                if (!selectedValue) {
                    placeholderOption.selected = true;
                }
                select.appendChild(placeholderOption);
            }

            (items || []).forEach(function(item) {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.name;
                if (selectedValue !== null && selectedValue !== undefined && String(item.id) === String(selectedValue)) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
        }

        function populateSbuOptions(selectedSbuId) {
            const orgId = shiftOrganization ? shiftOrganization.value : '';
            const organization = findOrganization(orgId);
            setSelectOptions(shiftSbu, '— Select SBU —', organization ? organization.sbus : [], selectedSbuId);
            if (shiftSbu) {
                shiftSbu.disabled = !organization;
            }
        }

        function setScopeCascade(organizationId, sbuId) {
            if (shiftOrganization) {
                shiftOrganization.value = organizationId || '';
            }
            populateSbuOptions(sbuId || null);
            if (shiftSbu && sbuId) {
                shiftSbu.value = sbuId;
            }
        }

        function resetScopeFields() {
            if (shiftOrganization) {
                shiftOrganization.value = '';
            }
            setSelectOptions(shiftSbu, '— Select SBU —', []);
            if (shiftSbu) {
                shiftSbu.disabled = true;
            }

            if (viewerScope.restricted && viewerScope.organization_id && viewerScope.sbu_id) {
                setScopeCascade(viewerScope.organization_id, viewerScope.sbu_id);
            } else if (organizations.length === 1 && organizations[0].sbus && organizations[0].sbus.length === 1) {
                setScopeCascade(organizations[0].id, organizations[0].sbus[0].id);
            }
        }

        if (shiftOrganization) {
            shiftOrganization.addEventListener('change', function() {
                populateSbuOptions(null);
            });
        }

        function toggleOvertimeSection() {
            if (overtimeAllowed.checked) {
                overtimeTriggerSection.style.display = 'block';
                overtimeTrigger.required = true;
            } else {
                overtimeTriggerSection.style.display = 'none';
                overtimeTrigger.required = false;
                overtimeTrigger.value = '';
            }
        }

        function resetShiftForm() {
            addShiftForm.reset();

            if (shiftIdInput) {
                shiftIdInput.value = '';
            }

            addShiftForm.action = "{{ route('admin.shift-planner.store') }}";

            canvasLabel.innerHTML = '<i class="bi bi-plus-circle me-2"></i>Add New Shift';
            saveBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Save Shift';

            if (overtimeAllowed) {
                overtimeAllowed.checked = true;
            }

            if (overtimeTrigger) {
                overtimeTrigger.value = 8;
            }

            if (shiftStartTime) {
                shiftStartTime.value = '09:00';
            }

            if (shiftEndTime) {
                shiftEndTime.value = '18:00';
            }

            if (clockInWindow) {
                clockInWindow.value = 30;
            }

            if (clockOutWindow) {
                clockOutWindow.value = 30;
            }

            if (gracePeriod) {
                gracePeriod.value = 15;
            }

            if (breakTime) {
                breakTime.value = 60;
            }

            resetScopeFields();
            toggleOvertimeSection();
        }

        if (overtimeAllowed && overtimeTriggerSection && overtimeTrigger) {
            overtimeAllowed.addEventListener('change', toggleOvertimeSection);
            toggleOvertimeSection();
        }

        if (addShiftCanvas) {
            addShiftCanvas.addEventListener('show.bs.offcanvas', function(event) {
                const button = event.relatedTarget;

                if (button && button.classList.contains('edit-shift-btn')) {
                    const mode = button.getAttribute('data-mode');
                    const shiftId = button.getAttribute('data-shift-id');

                    if (mode === 'edit' && shiftId) {
                        canvasLabel.innerHTML = '<i class="bi bi-pencil me-2"></i>Edit Shift';
                        saveBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Update Shift';

                        if (shiftIdInput) {
                            shiftIdInput.value = shiftId;
                        }

                        addShiftForm.action = "{{ url('/admin/shift-planner') }}/" + shiftId;

                        fetch("{{ url('/admin/shift-planner') }}/" + shiftId)
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Failed to fetch shift data.');
                                }
                                return response.json();
                            })
                            .then(response => {
                                if (response.success && response.shift) {
                                    const data = response.shift;

                                    setScopeCascade(data.organization_id, data.sbu_id);
                                    shiftName.value = data.name ?? '';
                                    shiftStartTime.value = data.start_time ?? '';
                                    shiftEndTime.value = data.end_time ?? '';
                                    clockInWindow.value = data.clock_in_window_minutes ?? 30;
                                    clockOutWindow.value = data.clock_out_window_minutes ?? 30;
                                    gracePeriod.value = data.grace_period_minutes ?? 15;
                                    breakTime.value = data.break_time_minutes ?? 60;

                                    overtimeAllowed.checked = Boolean(data.overtime_allowed);
                                    overtimeTrigger.value = data.overtime_trigger_hours ?? '';

                                    toggleOvertimeSection();
                                } else {
                                    showError(response.message || 'Unable to load shift data.');
                                }
                            })
                            .catch(error => {
                                console.error(error);
                                showError('Failed to load shift data.');
                            });
                    } else {
                        resetShiftForm();
                    }
                } else {
                    resetShiftForm();
                }
            });

            addShiftCanvas.addEventListener('hidden.bs.offcanvas', function() {
                resetShiftForm();
            });
        }

        if (saveBtn) {
            saveBtn.addEventListener('click', function() {
                if (addShiftForm.checkValidity()) {
                    submitShiftForm();
                } else {
                    addShiftForm.reportValidity();
                }
            });
        }

        if (addShiftForm) {
            addShiftForm.addEventListener('submit', function(e) {
                e.preventDefault();
                if (addShiftForm.checkValidity()) {
                    submitShiftForm();
                } else {
                    addShiftForm.reportValidity();
                }
            });
        }

        function submitShiftForm() {
            if (isShiftSaving) {
                return;
            }
            clearFormErrors();

            const actionUrl = addShiftForm.getAttribute('action');
            if (!actionUrl) {
                showError('Unable to submit form.');
                return;
            }

            const originalBtnHtml = saveBtn ? saveBtn.innerHTML : '';
            isShiftSaving = true;
            if (saveBtn) {
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
            }

            const formData = new FormData(addShiftForm);

            fetch(actionUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData,
                credentials: 'same-origin'
            })
                .then(async (response) => {
                    const data = await response.json().catch(() => ({}));
                    return {
                        ok: response.ok,
                        status: response.status,
                        data
                    };
                })
                .then((result) => {
                    if (result.ok && result.data && result.data.success) {
                        showSuccess(result.data.message || 'Shift saved successfully.');
                        const offcanvas = bootstrap.Offcanvas.getInstance(addShiftCanvas);
                        if (offcanvas) {
                            offcanvas.hide();
                        }
                        window.location.reload();
                        return;
                    }

                    if (result.status === 422 && result.data && result.data.errors) {
                        applyFormErrors(result.data.errors);
                        return;
                    }

                    showError((result.data && result.data.message) ? result.data.message : 'Failed to save shift.');
                })
                .catch(() => {
                    showError('Failed to save shift.');
                })
                .finally(() => {
                    isShiftSaving = false;
                    if (saveBtn) {
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = originalBtnHtml;
                    }
                });
        }

        function clearFormErrors() {
            const errorBox = document.getElementById('shiftFormErrorBox');
            if (errorBox) {
                errorBox.classList.add('d-none');
                errorBox.innerHTML = '';
            }
            if (!addShiftForm) {
                return;
            }
            addShiftForm.querySelectorAll('.is-invalid').forEach((field) => {
                field.classList.remove('is-invalid');
            });
            addShiftForm.querySelectorAll('.invalid-feedback.dynamic-error').forEach((node) => {
                node.remove();
            });
        }

        function applyFormErrors(errors) {
            clearFormErrors();
            const errorBox = document.getElementById('shiftFormErrorBox');
            const generalMessages = [];

            Object.entries(errors || {}).forEach(([field, messages]) => {
                const list = Array.isArray(messages) ? messages : [messages];
                const message = String(list[0] || '').trim();

                const input = addShiftForm.querySelector('[name="' + field + '"]');
                if (!message) {
                    return;
                }

                if (!input) {
                    generalMessages.push(message);
                    return;
                }

                input.classList.add('is-invalid');
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback dynamic-error';
                feedback.textContent = message;
                input.insertAdjacentElement('afterend', feedback);
            });

            if (errorBox && generalMessages.length) {
                errorBox.classList.remove('d-none');
                errorBox.innerHTML = generalMessages.join('<br>');
            }
        }
    });
</script>
