<div class="offcanvas offcanvas-end" tabindex="-1" id="outsourcedEmployeeCanvas" aria-labelledby="outsourcedEmployeeCanvasLabel" style="width: 560px;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="outsourcedEmployeeCanvasLabel">Add Outsourced Employee</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="outsourcedEmployeeForm" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="outsourcedEmployeeId" name="id">
            <input type="file" class="d-none" name="photo" id="oePhoto" accept=".jpg,.jpeg,.png,.webp">

            <div class="d-flex justify-content-center mb-4">
                <div class="text-center">
                    <div id="oePhotoTrigger" class="position-relative d-inline-block" role="button" title="Click to upload profile photo">
                        <img id="oePhotoPreviewImage" src="" alt="Profile photo preview" class="rounded-circle d-none border border-light" style="width:88px;height:88px;object-fit:cover;">
                        <div id="oePhotoPlaceholderIcon" class="rounded-circle border d-flex align-items-center justify-content-center text-secondary bg-white" style="width:88px;height:88px;">
                            <i class="bi bi-person fs-2"></i>
                        </div>
                        <span class="position-absolute bottom-0 end-0 badge rounded-pill bg-primary border border-white">
                            <i class="bi bi-camera"></i>
                        </span>
                    </div>
                    <div class="small text-white-50 mt-2">JPG/JPEG/PNG/WEBP, max 2MB</div>
                    <button type="button" class="btn btn-sm btn-link text-danger p-0 mt-1 d-none" id="oeClearPhotoBtn">Remove photo</button>
                </div>
            </div>

            <div class="mb-3 fw-semibold">Basic Information</div>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="full_name" id="oeFullName" placeholder="Enter full name">
                </div>
                <div class="col-md-6">
                    <label class="form-label">CNIC Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control cnic-mask" name="cnic_number" id="oeCnic" placeholder="00000-0000000-0">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control contact-mask" name="mobile_number" id="oeMobile" placeholder="03XXXXXXXXX">
                </div>
            </div>

            <hr>
            <div class="mb-3 fw-semibold">Vendor Details</div>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Contractor Company Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="contractor_company_name" id="oeCompanyName" placeholder="Enter contractor company name">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Supervisor Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="supervisor_name" id="oeSupervisorName" placeholder="Enter supervisor name">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Supervisor Contact Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control contact-mask" name="supervisor_contact_number" id="oeSupervisorContact" placeholder="03XXXXXXXXX">
                </div>
            </div>

            <hr>
            <div class="mb-3 fw-semibold">Work Details</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Organization <span class="text-danger">*</span></label>
                    <select class="form-select" name="organization_id" id="oeOrganizationId">
                        <option value="">Select organization</option>
                        @foreach(($organizations ?? []) as $org)
                            <option value="{{ $org->id }}">{{ $org->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">SBU <span class="text-danger">*</span></label>
                    <select class="form-select" name="sbu_id" id="oeSbuId">
                        <option value="">Select SBU</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Department <span class="text-danger">*</span></label>
                    <select class="form-select" name="department_id" id="oeDepartmentId">
                        <option value="">Select department</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Job Role / Trade <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="job_role_trade" id="oeJobRole" placeholder="Enter job role / trade">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Placement (Floor) <span class="text-danger">*</span></label>
                    <select class="form-select" name="placement_floor" id="oePlacementFloor">
                        <option value="">Select floor</option>
                        @foreach(($floors ?? []) as $floor)
                            <option value="{{ $floor->name }}">{{ $floor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Date of Deployment <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="date_of_deployment" id="oeDeploymentDate">
                </div>
            </div>

            <hr>
            <div class="mb-3 fw-semibold">Attendance</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Biometric ID</label>
                    <input type="text" class="form-control" name="biometric_id" id="oeBiometricId" placeholder="Enter biometric ID">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Attendance Access <span class="text-danger">*</span></label>
                    <select class="form-select" name="attendance_access" id="oeAttendanceAccess">
                        <option value="1">Granted</option>
                        <option value="0">Not Granted</option>
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" class="btn bg-main text-white border-0" id="outsourcedEmployeeSubmitBtn">Save</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="oeCropperModal" tabindex="-1" aria-labelledby="oeCropperModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-main text-white">
                <h5 class="modal-title" id="oeCropperModalLabel"><i class="bi bi-crop me-2"></i>Crop Profile Photo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" id="oeCropperCloseBtn"></button>
            </div>
            <div class="modal-body p-0 bg-light">
                <div class="p-3">
                    <img id="oeCropperImage" src="" alt="Cropper source" style="max-width: 100%;">
                </div>
            </div>
            <div class="modal-footer bg-white border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn bg-main text-white" id="oeCropBtn">Crop & Save</button>
            </div>
        </div>
    </div>
</div>

<script>
    window.outsourcedOrganizations = @json(($organizations ?? collect())->values());
</script>

