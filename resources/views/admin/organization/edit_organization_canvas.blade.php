<!-- Edit Organization Canvas -->
<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="organizationEditCanvas" aria-labelledby="organizationEditCanvasLabel" style="width: 600px;">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="organizationEditCanvasLabel">
            <i class="bi bi-pencil-square me-2"></i>Edit Organization
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body">
        <form id="editOrganizationForm" method="POST" action="javascript:void(0);">
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
                            <option value="{{ $organization->id }}">
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

    const updateRouteTemplate = `{{ route('admin.organization.update', ['id' => '__id__']) }}`;
    const editRouteTemplate = `{{ route('admin.organization.edit', ['id' => '__id__']) }}`;

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
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Failed to load organization data.'
                });
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
            document.getElementById('editOrgStatus').value = org.is_active ? '1' : '0';
            document.getElementById('editParentId').value = org.parent_id ?? '';
        })
        .catch(error => {
            console.error('Edit fetch error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Something went wrong while loading organization data.'
            });
        });
    });

    editForm.addEventListener('submit', function (e) {
        if (!editForm.action || editForm.action.includes('javascript:void(0)')) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'No Organization Selected',
                text: 'Please select an organization first.'
            });
        }
    });

    if (editCanvas) {
        editCanvas.addEventListener('hidden.bs.offcanvas', function () {
            editForm.reset();
            editForm.action = 'javascript:void(0);';

            document.getElementById('editOrgId').value = '';
            document.getElementById('editOrgStatus').value = '1';
            document.getElementById('editParentId').value = '';
        });
    }
});
</script>