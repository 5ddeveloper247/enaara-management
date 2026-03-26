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
        offcanvasEl.addEventListener('hidden.bs.offcanvas', function () {
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
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
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
                    Swal.fire('Error', data.message || 'Something went wrong.', 'error');
                }
            })
            .catch((error) => {
                console.error('Error:', error);
                Swal.fire('Network Error', 'Please try again.', 'error');
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
                            Swal.fire('Deleted!', data.message, 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Error', data.message || 'Failed to delete holiday.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Delete error:', error);
                        Swal.fire('Error', 'Network error. Please try again.', 'error');
                    });
                }
            });
        });
    }
});
</script>




