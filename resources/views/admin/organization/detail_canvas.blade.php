<!-- Organization Detail Canvas -->
<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="organizationDetailCanvas" aria-labelledby="organizationDetailCanvasLabel" style="width: 600px;">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="organizationDetailCanvasLabel">
            <i class="bi bi-building me-2"></i>Organization Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body">
        <!-- Organization Identity -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-info-circle me-2"></i>Organization Identity
            </h6>

            <div class="d-flex align-items-center mb-3">
                <div class="me-3">
                    <div class="bg-light text-dark rounded-3 d-flex align-items-center justify-content-center fw-bold"
                         id="detailOrgInitials"
                         style="width: 60px; height: 60px; font-size: 1.25rem;">
                        —
                    </div>
                </div>
                <div class="flex-grow-1">
                    <h6 class="fw-semibold small mb-1" id="detailOrgName">—</h6>
                    <small class="opacity-75 text-white" id="detailOrgCode">Code: —</small>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Basic Information -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-file-text me-2"></i>Basic Information
            </h6>

            <div class="row g-3">
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Parent Organization</small>
                        <div class="fw-semibold small" id="detailOrgParent">—</div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Email</small>
                        <div class="fw-semibold small" id="detailOrgEmail">—</div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Tax Number</small>
                        <div class="fw-semibold small" id="detailOrgTaxNo">—</div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Address</small>
                        <div class="fw-semibold small" id="detailOrgAddress">—</div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Description</small>
                        <div class="fw-semibold small" id="detailOrgDescription">—</div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Working Schedule -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-clock me-2"></i>Working Schedule
            </h6>

            <div class="row g-3">
                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Working Days</small>
                        <div class="fw-semibold small" id="detailOrgWorkingDays">—</div>
                    </div>
                </div>

                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Start Time</small>
                        <div class="fw-semibold small" id="detailOrgStartTime">—</div>
                    </div>
                </div>

                <div class="col-6">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">End Time</small>
                        <div class="fw-semibold small" id="detailOrgEndTime">—</div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                        <small class="opacity-75 text-white d-block mb-2">Grace Period (min)</small>
                        <div class="fw-semibold small" id="detailOrgGracePeriod">—</div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: #ffffffab !important">

        <!-- Status -->
        <div class="mb-4">
            <h6 class="mb-3 fw-semibold small">
                <i class="bi bi-check-circle me-2"></i>Status
            </h6>

            <div class="p-3 rounded-3 border" style="border-color: #ffffff1a !important;">
                <small class="opacity-75 text-white d-block mb-2">Current Status</small>
                <div id="detailOrgStatus">—</div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top" style="border-color: #ffffffab !important">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Close</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.view-organization-btn');
        if (!btn) return;

        const name = btn.dataset.orgName || '—';
        const code = btn.dataset.orgCode || '—';
        const email = btn.dataset.orgEmail || '—';
        const taxNo = btn.dataset.orgTaxNo || '—';
        const address = btn.dataset.orgAddress || '—';
        const description = btn.dataset.orgDescription || '—';
        const parent = btn.dataset.orgParent || '—';
        const workingDays = btn.dataset.orgWorkingDays || '—';
        const startTime = btn.dataset.orgWorkingStartTime || '—';
        const endTime = btn.dataset.orgWorkingEndTime || '—';
        const gracePeriod = btn.dataset.orgGracePeriod || '—';
        const isActive = btn.dataset.orgActive === '1';

        const initials = name !== '—'
            ? name.split(' ').map(word => word.charAt(0)).join('').substring(0, 2).toUpperCase()
            : '—';

        document.getElementById('detailOrgInitials').textContent = initials;
        document.getElementById('detailOrgName').textContent = name;
        document.getElementById('detailOrgCode').textContent = 'Code: ' + code;
        document.getElementById('detailOrgEmail').textContent = email;
        document.getElementById('detailOrgTaxNo').textContent = taxNo;
        document.getElementById('detailOrgAddress').textContent = address;
        document.getElementById('detailOrgDescription').textContent = description;
        document.getElementById('detailOrgParent').textContent = parent;
        document.getElementById('detailOrgWorkingDays').textContent = workingDays;
        document.getElementById('detailOrgStartTime').textContent = startTime;
        document.getElementById('detailOrgEndTime').textContent = endTime;
        document.getElementById('detailOrgGracePeriod').textContent = gracePeriod;

        document.getElementById('detailOrgStatus').innerHTML = isActive
            ? '<span class="badge bg-success px-3 py-2 rounded-1">Active</span>'
            : '<span class="badge bg-secondary px-3 py-2 rounded-1">Inactive</span>';
    });
});
</script>
