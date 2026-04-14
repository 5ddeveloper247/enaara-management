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
                    <label for="orgName" class="form-label fw-semibold small text-white">Organization Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="orgName" name="name" value="{{ old('name') }}" placeholder="e.g., Enaara Construction" required>
                </div>

                <div class="mb-3">
                    <label for="orgCode" class="form-label fw-semibold small text-white">Organization Code</label>
                    <input type="text" class="form-control" id="orgCode" name="code" value="{{ old('code') }}" placeholder="e.g., ENR-001" maxlength="64">
                </div>

                <div class="mb-3">
                    <label for="orgEmail" class="form-label fw-semibold small text-white">Email</label>
                    <input type="email" class="form-control" id="orgEmail" name="email" value="{{ old('email') }}" placeholder="e.g., info@company.com">
                </div>

                <div class="mb-3">
                    <label for="orgTaxNo" class="form-label fw-semibold small text-white">Tax Number</label>
                    <input type="text" class="form-control" id="orgTaxNo" name="tax_no" value="{{ old('tax_no') }}" placeholder="e.g., TAX-123456" maxlength="64">
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-card-text me-2"></i>Additional Details
                </h6>

                <div class="mb-3">
                    <label for="orgDescription" class="form-label fw-semibold small text-white">Description</label>
                    <textarea class="form-control" id="orgDescription" name="description" rows="3" placeholder="Enter company description">{{ old('description') }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="orgAddress" class="form-label fw-semibold small text-white">Address</label>
                    <textarea class="form-control" id="orgAddress" name="address" rows="3" placeholder="Enter company address">{{ old('address') }}</textarea>
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

        if (addOrgCanvas) {
            addOrgCanvas.addEventListener('hidden.bs.offcanvas', function() {
                addOrgForm.reset();
                document.getElementById('orgStatus').value = '1';
                scheduleModeStandard.checked = true;
                toggleScheduleMode();
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
                        const offcanvas = bootstrap.Offcanvas.getInstance(addOrgCanvas);
                        if (offcanvas) offcanvas.hide();

                        showSuccess(data.message || 'Organization created successfully.').then(() => {
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
                });
            });
        }
        toggleScheduleMode();
    });


    
</script>