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
                        <option value="{{ $organization->id }}" {{ old('parent_id') == $organization->id ? 'selected' : '' }}>
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

        if (addOrgCanvas) {
            addOrgCanvas.addEventListener('hidden.bs.offcanvas', function() {
                addOrgForm.reset();
                document.getElementById('orgStatus').value = '1';
            });
        }

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
    });


    
</script>