<!-- Add Holiday Canvas -->
<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="addHolidayCanvas" aria-labelledby="addHolidayCanvasLabel" style="width: 500px;">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="addHolidayCanvasLabel">
            <i class="bi bi-calendar-plus me-2"></i>Add Public Holiday
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="addHolidayForm" action="{{ route('admin.leave-calendar.store') }}" method="POST">
            @csrf <!-- Include CSRF token for security -->
            <!-- Holiday Name -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-info-circle me-2"></i>Holiday Information
                </h6>

                <div class="mb-3">
                    <label for="holidayName" class="form-label fw-semibold small text-white">Holiday Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="holidayName" name="name" placeholder="e.g., Independence Day" required>
                </div>

                <!-- Date Selection -->
                <div class="mb-3">
                    <label for="holidayStartDate" class="form-label fw-semibold small text-white">Start Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="holidayStartDate" name="start_date" required>
                </div>

                <!-- End Date -->
                <div class="mb-3">
                    <label for="holidayEndDate" class="form-label fw-semibold small text-white">End Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="holidayEndDate" name="end_date" required>
                </div>


                <!-- Recurring Option -->
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="isRecurring" name="is_recurring" value="1">

                    <label class="form-check-label text-white" for="isRecurring">
                        <strong>Recurring Holiday</strong>
                        <small class="d-block opacity-75">Repeat annually</small>
                    </label>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Organization Selection -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-building me-2"></i>Organization Scope
                </h6>

                <div class="mb-3">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="organization_scope" id="scopeAll" value="all" checked>
                        <label class="form-check-label text-white" for="scopeAll">
                            <strong>All Organizations</strong>
                            <small class="d-block opacity-75">Apply to entire group</small>
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="organization_scope" id="scopeSpecific" value="specific">
                        <label class="form-check-label text-white" for="scopeSpecific">
                            <strong>Specific Organization</strong>
                            <small class="d-block opacity-75">Select organization(s)</small>
                        </label>
                    </div>
                </div>

                <!-- Organization Select (shown when specific is selected) -->
                <div class="mb-3" id="organizationSelectSection" style="display: none;">
                    <label for="holidayOrganizations" class="form-label fw-semibold small text-white">Select Organizations</label>
                    <select class="form-select" id="holidayOrganizations" name="organizations[]" multiple>
                        @foreach($organizations as $org)
                        <option value="{{ $org->id }}">{{ $org->name }}</option>
                        @endforeach
                    </select>

                    <small class="opacity-75 text-white">Hold Ctrl/Cmd to select multiple</small>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- department Select -->
            <hr class="my-4" style="border-color: #ffffffab !important">

            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-diagram-3 me-2"></i>Department Scope
                </h6>

                <div class="mb-3">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="department_scope" id="departmentScopeNone" value="none" checked>
                        <label class="form-check-label text-white" for="departmentScopeNone">
                            <strong>No Department Filter</strong>
                        </label>
                    </div>

                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="department_scope" id="departmentScopeAll" value="all">
                        <label class="form-check-label text-white" for="departmentScopeAll">
                            <strong>All Departments</strong>
                        </label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="department_scope" id="departmentScopeSpecific" value="specific">
                        <label class="form-check-label text-white" for="departmentScopeSpecific">
                            <strong>Specific Departments</strong>
                        </label>
                    </div>
                </div>

                <div class="mb-3" id="departmentSelectSection" style="display:none;">
                    <label class="form-label fw-semibold small text-white">Select Departments</label>
                    <select class="form-select" name="departments[]" id="holidayDepartments" multiple>
                        @foreach($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Select Sbu -->
            <hr class="my-4" style="border-color: #ffffffab !important">

            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-layers me-2"></i>SBU Scope
                </h6>

                <div class="mb-3">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="sbu_scope" id="sbuScopeNone" value="none" checked>
                        <label class="form-check-label text-white" for="sbuScopeNone">
                            <strong>No SBU Filter</strong>
                        </label>
                    </div>

                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="sbu_scope" id="sbuScopeAll" value="all">
                        <label class="form-check-label text-white" for="sbuScopeAll">
                            <strong>All SBUs</strong>
                        </label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="sbu_scope" id="sbuScopeSpecific" value="specific">
                        <label class="form-check-label text-white" for="sbuScopeSpecific">
                            <strong>Specific SBUs</strong>
                        </label>
                    </div>
                </div>

                <div class="mb-3" id="sbuSelectSection" style="display:none;">
                    <label class="form-label fw-semibold small text-white">Select SBUs</label>
                    <select class="form-select" name="sbus[]" id="holidaySbus" multiple>
                        @foreach($sbus as $sbu)
                        <option value="{{ $sbu->id }}">{{ $sbu->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <!-- Blackout Date Option -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-x-circle me-2"></i>Blackout Date
                </h6>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="isBlackout" name="is_blackout" value="1">

                    <label class="form-check-label text-white" for="isBlackout">
                        <strong>Mark as Blackout Date</strong>
                        <small class="d-block opacity-75">No leave requests allowed on this date</small>
                    </label>
                </div>

                <div class="mb-3" id="blackoutReasonSection" style="display: none;">
                    <label for="blackoutReason" class="form-label fw-semibold small text-white">Reason</label>
                    <input type="text" class="form-control" id="blackoutReason" name="reason" placeholder="e.g., Project Deadline, Quarter End">
                </div>
            </div>
        </form>
    </div>
    <div class="offcanvas-footer border-top p-3" style="border-color: #ffffffab !important">
        <div class="d-flex justify-content-between w-100">
            <button type="button" class="btn btn-outline-danger d-none" id="deleteHolidayBtn">
                <i class="bi bi-trash me-1"></i>Delete
            </button>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" class="btn btn-light text-dark border-0" id="saveHolidayBtn" form="addHolidayForm">
                    <i class="bi bi-check-lg me-1"></i>Save Holiday
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const scopeAll = document.getElementById('scopeAll');
        const scopeSpecific = document.getElementById('scopeSpecific');
        const organizationSelectSection = document.getElementById('organizationSelectSection');
        const isBlackout = document.getElementById('isBlackout');
        const blackoutReasonSection = document.getElementById('blackoutReasonSection');
        const addHolidayForm = document.getElementById('addHolidayForm');
        const deleteHolidayBtn = document.getElementById('deleteHolidayBtn');
        const offcanvasEl = document.getElementById('addHolidayCanvas');
        const departmentScopeSpecific = document.getElementById('departmentScopeSpecific');
        const departmentSelectSection = document.getElementById('departmentSelectSection');
        const departmentScopeAll = document.getElementById('departmentScopeAll');
        const departmentScopeNone = document.getElementById('departmentScopeNone');

        const sbuScopeSpecific = document.getElementById('sbuScopeSpecific');
        const sbuSelectSection = document.getElementById('sbuSelectSection');
        const sbuScopeAll = document.getElementById('sbuScopeAll');
        const sbuScopeNone = document.getElementById('sbuScopeNone');

        function toggleSection(radioSpecific, section, radiosToHide = []) {
            if (radioSpecific?.checked) {
                section.style.display = 'block';
            } else {
                section.style.display = 'none';
            }
        }

        [departmentScopeSpecific, departmentScopeAll, departmentScopeNone].forEach(radio => {
            radio?.addEventListener('change', () => toggleSection(departmentScopeSpecific, departmentSelectSection));
        });

        [sbuScopeSpecific, sbuScopeAll, sbuScopeNone].forEach(radio => {
            radio?.addEventListener('change', () => toggleSection(sbuScopeSpecific, sbuSelectSection));
        });
        // Show/hide organization select
        if (scopeAll) {
            scopeAll.addEventListener('change', function() {
                if (this.checked) organizationSelectSection.style.display = 'none';
            });
        }

        if (scopeSpecific) {
            scopeSpecific.addEventListener('change', function() {
                if (this.checked) organizationSelectSection.style.display = 'block';
            });
        }

        // Show/hide blackout reason
        if (isBlackout) {
            isBlackout.addEventListener('change', function() {
                blackoutReasonSection.style.display = this.checked ? 'block' : 'none';
            });
        }

        // Reset form when canvas is hidden - Consolidated
        if (offcanvasEl) {
            offcanvasEl.addEventListener('hidden.bs.offcanvas', function() {
                if (addHolidayForm) {
                    addHolidayForm.reset();
                    addHolidayForm.dataset.mode = 'add';
                    addHolidayForm.dataset.holidayId = '';
                    addHolidayForm.action = `{{ route('admin.leave-calendar.store') }}`;
                }

                const label = document.getElementById('addHolidayCanvasLabel');
                if (label) label.textContent = 'Add New Holiday';

                if (organizationSelectSection) organizationSelectSection.style.display = 'none';
                if (blackoutReasonSection) blackoutReasonSection.style.display = 'none';
                if (deleteHolidayBtn) deleteHolidayBtn.classList.add('d-none');

                // Clear errors
                document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
            });
        }

        // Handle form submission via AJAX
        if (addHolidayForm) {
            addHolidayForm.addEventListener('submit', function(e) {
                e.preventDefault();

                // Clear previous errors
                document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

                const formData = new FormData(this);
                const submitBtn = document.getElementById('saveHolidayBtn');
                if (submitBtn) submitBtn.disabled = true;

                fetch(this.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        },
                        body: formData,
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showSuccess(data.message).then(() => {
                                location.reload();
                            });
                        } else if (data.errors) {
                            Object.keys(data.errors).forEach(key => {
                                let input = document.getElementsByName(key)[0] || document.getElementById(key);
                                if (key === 'name') input = document.getElementById('holidayName');
                                if (key === 'start_date') input = document.getElementById('holidayStartDate');
                                if (key === 'end_date') input = document.getElementById('holidayEndDate');

                                if (input) {
                                    input.classList.add('is-invalid');
                                    const feedback = document.createElement('div');
                                    feedback.className = 'invalid-feedback d-block text-warning small mt-1';
                                    feedback.innerText = data.errors[key][0];
                                    input.closest('.mb-3')?.appendChild(feedback) || input.parentElement.appendChild(feedback);
                                }
                            });
                        } else {
                            showError(data.message || 'Something went wrong.');
                        }
                    })
                    .catch((error) => {
                        console.error('Error:', error);
                        showError('Please try again.', 'Network Error');
                    })
                    .finally(() => {
                        if (submitBtn) submitBtn.disabled = false;
                    });
            });
        }

        // Handle Delete
        if (deleteHolidayBtn) {
            deleteHolidayBtn.addEventListener('click', function() {
                const id = addHolidayForm.dataset.holidayId;
                if (!id) return;

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This will permanently delete this holiday.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const url = "{{ route('admin.leave-calendar.destroy', ['id' => ':id']) }}".replace(':id', id);
                        fetch(url, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Accept': 'application/json',
                                }
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    showSuccess(data.message, 'Deleted!').then(() => location.reload());
                                } else {
                                    showError(data.message || 'Failed to delete holiday.');
                                }
                            })
                            .catch(error => {
                                console.error('Delete error:', error);
                                showError('Network error. Please try again.');
                            });
                    }
                });
            });
        }
    });
</script>
