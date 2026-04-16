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
                    <label for="editOrgName" class="form-label fw-semibold small text-white">Organization Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="editOrgName" name="name" placeholder="e.g., Enaara Construction" required>
                </div>

                <div class="mb-3">
                    <label for="editOrgCode" class="form-label fw-semibold small text-white">Organization Code</label>
                    <input type="text" class="form-control" id="editOrgCode" name="code" placeholder="e.g., ENR-001" maxlength="64">
                </div>

                <div class="mb-3">
                    <label for="editOrgEmail" class="form-label fw-semibold small text-white">Email</label>
                    <input type="email" class="form-control" id="editOrgEmail" name="email" placeholder="e.g., info@company.com">
                </div>

                <div class="mb-3">
                    <label for="editOrgTaxNo" class="form-label fw-semibold small text-white">Tax Number</label>
                    <input type="text" class="form-control" id="editOrgTaxNo" name="tax_no" placeholder="e.g., TAX-123456" maxlength="64">
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-card-text me-2"></i>Additional Details
                </h6>

                <div class="mb-3">
                    <label for="editOrgDescription" class="form-label fw-semibold small text-white">Description</label>
                    <textarea class="form-control" id="editOrgDescription" name="description" rows="3" placeholder="Enter organization description"></textarea>
                </div>

                <div class="mb-3">
                    <label for="editOrgAddress" class="form-label fw-semibold small text-white">Address</label>
                    <textarea class="form-control" id="editOrgAddress" name="address" rows="3" placeholder="Enter organization address"></textarea>
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
                    <div class="col-6">
                        <label for="editOrgWorkingStartTime" class="form-label fw-semibold small text-white">Working Start Time</label>
                        <input type="time" class="form-control" id="editOrgWorkingStartTime" name="working_start_time">
                    </div>
                    <div class="col-6">
                        <label for="editOrgWorkingEndTime" class="form-label fw-semibold small text-white">Working End Time</label>
                        <input type="time" class="form-control" id="editOrgWorkingEndTime" name="working_end_time">
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label for="editOrgOpeningGracePeriod" class="form-label fw-semibold small text-white">Opening Grace Period (min)</label>
                        <input type="number" min="0" max="600" class="form-control" id="editOrgOpeningGracePeriod" name="opening_grace_period">
                    </div>
                    <div class="col-6">
                        <label for="editOrgClosingGracePeriod" class="form-label fw-semibold small text-white">Closing Grace Period (min)</label>
                        <input type="number" min="0" max="600" class="form-control" id="editOrgClosingGracePeriod" name="closing_grace_period">
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
        <div class="d-flex justify-content-end align-items-center gap-2">
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" form="editOrganizationForm" class="btn btn-light text-dark border-0" id="updateOrganizationBtn">
                    <i class="bi bi-check-lg me-1"></i>Update Organization
                </button>
            </div>
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
    const editOpeningGracePeriod = document.getElementById('editOrgOpeningGracePeriod');
    const editClosingGracePeriod = document.getElementById('editOrgClosingGracePeriod');

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
                openingGracePeriod: '',
                closingGracePeriod: ''
            };
        }
        return {
            workingDays: (option.dataset.workingDays || '').split(',').filter(Boolean),
            workingStartTime: option.dataset.workingStartTime || '',
            workingEndTime: option.dataset.workingEndTime || '',
            openingGracePeriod: option.dataset.openingGracePeriod || '',
            closingGracePeriod: option.dataset.closingGracePeriod || ''
        };
    }

    function applyParentSchedule() {
        const schedule = getParentSchedule();
        editWorkingDayCheckboxes.forEach((checkbox) => {
            checkbox.checked = schedule.workingDays.includes(checkbox.value);
        });
        editWorkingStartTime.value = schedule.workingStartTime;
        editWorkingEndTime.value = schedule.workingEndTime;
        editOpeningGracePeriod.value = schedule.openingGracePeriod;
        editClosingGracePeriod.value = schedule.closingGracePeriod;
    }

    function schedulesMatchParent(currentWorkingDays, currentStartTime, currentEndTime, currentOpeningGracePeriod, currentClosingGracePeriod) {
        const parentSchedule = getParentSchedule();
        const normalizedCurrentDays = [...currentWorkingDays].sort().join(',');
        const normalizedParentDays = [...parentSchedule.workingDays].sort().join(',');
        return normalizedCurrentDays === normalizedParentDays
            && (currentStartTime || '') === parentSchedule.workingStartTime
            && (currentEndTime || '') === parentSchedule.workingEndTime
            && (currentOpeningGracePeriod || '') === parentSchedule.openingGracePeriod
            && (currentClosingGracePeriod || '') === parentSchedule.closingGracePeriod;
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
            document.getElementById('editOrgOpeningGracePeriod').value = org.opening_grace_period ?? '';
            document.getElementById('editOrgClosingGracePeriod').value = org.closing_grace_period ?? '';
            document.getElementById('editOrgStatus').value = org.is_active ? '1' : '0';
            document.getElementById('editParentId').value = org.parent_id ?? '';
            const workingDays = Array.isArray(org.working_days) ? org.working_days : [];
            document.querySelectorAll('.edit-working-day').forEach((checkbox) => {
                checkbox.checked = workingDays.includes(checkbox.value);
            });
            const currentStartTime = (org.working_start_time ?? '').toString().slice(0, 5);
            const currentEndTime = (org.working_end_time ?? '').toString().slice(0, 5);
            const currentOpeningGracePeriod = (org.opening_grace_period ?? '').toString();
            const currentClosingGracePeriod = (org.closing_grace_period ?? '').toString();
            if (org.parent_id) {
                if (schedulesMatchParent(workingDays, currentStartTime, currentEndTime, currentOpeningGracePeriod, currentClosingGracePeriod)) {
                    editScheduleModeStandard.checked = true;
                } else {
                    editScheduleModeCustom.checked = true;
                }
            } else {
                editScheduleModeCustom.checked = true;
            }
            toggleEditScheduleMode();
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

    editForm.addEventListener('submit', function (e) {
        e.preventDefault();

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
                const offcanvas = bootstrap.Offcanvas.getInstance(editCanvas);
                if (offcanvas) offcanvas.hide();

                showSuccess(data.message || 'Organization updated successfully.').then(() => {
                    window.location.reload();
                });
            } else if (status === 422) {
                // Validation errors
                let errorMessage = '';
                if (data.errors) {
                    errorMessage = '<div class="text-start mt-2">';
                    errorMessage += '<ul class="mb-0">';
                    Object.values(data.errors).flat().forEach(err => {
                        errorMessage += `<li>${err}</li>`;
                    });
                    errorMessage += '</ul></div>';
                } else {
                    errorMessage = data.message || 'Validation failed.';
                }
                
                Swal.fire({
                    icon: 'warning',
                    title: 'Please check the following:',
                    html: errorMessage,
                    confirmButtonColor: '#1a237e',
                    confirmButtonText: 'Dismiss'
                });
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
        });
    });

    if (editCanvas) {
        editCanvas.addEventListener('hidden.bs.offcanvas', function () {
            editForm.reset();
            editForm.action = 'javascript:void(0);';

            document.getElementById('editOrgId').value = '';
            document.getElementById('editOrgStatus').value = '1';
            document.getElementById('editParentId').value = '';
            document.getElementById('editOrgWorkingStartTime').value = '';
            document.getElementById('editOrgWorkingEndTime').value = '';
            document.getElementById('editOrgOpeningGracePeriod').value = '';
            document.getElementById('editOrgClosingGracePeriod').value = '';
            document.querySelectorAll('.edit-working-day').forEach((checkbox) => {
                checkbox.checked = false;
            });
            editScheduleModeStandard.checked = true;
            toggleEditScheduleMode();
        });
    }
    toggleEditScheduleMode();
});
</script>
