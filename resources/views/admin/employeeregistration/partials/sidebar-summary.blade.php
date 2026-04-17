            <div class="col-md-3">
                <aside class="card border-0 shadow-sm overflow-hidden sticky-top">
                    @php
                        $photoUrl = '';
                        if(isset($employee)) {
                            $photo = $employee->mediaFiles()->where('file_type', 'photo')->first();
                            if($photo) {
                                $photoUrl = asset('storage/' . $photo->file_path);
                            }
                        }
                    @endphp
                    <div class="bg-main text-center p-2">
                        <div class="position-relative mx-auto mt-2" style="width: 110px; height: 110px;" id="avatarPreviewContainer">
                            <div class="rounded-circle d-flex align-items-center justify-content-center position-relative overflow-hidden border-2 border-white shadow-sm bg-secondary-subtle text-secondary w-100 h-100"
                                id="avatarPreviewWrap">
                                
                                <img id="avatarPreviewImage" alt="Employee photo preview" class="w-100 h-100 object-fit-cover {{ empty($photoUrl) ? 'd-none' : '' }}" src="{{ $photoUrl }}">
                                <i class="bi bi-person-fill {{ !empty($photoUrl) ? 'd-none' : '' }}" id="avatarPlaceholderIcon" style="font-size: 3rem;"></i>
                                
                                <label class="avatar-upload-overlay position-absolute top-0 start-0 w-100 h-100 rounded-circle bg-dark bg-opacity-50 text-white d-flex flex-column align-items-center justify-content-center gap-1 small fw-semibold"
                                    for="profilePhotoInput">
                                    <i class="bi bi-cloud-arrow-up"></i>
                                    <span>Upload Photo</span>
                                </label>
                            </div>

                            <button type="button" class="btn btn-danger btn-sm position-absolute rounded-circle d-flex align-items-center justify-content-center remove-photo-btn {{ empty($photoUrl) ? 'd-none' : '' }}" 
                                id="removePhotoBtn" 
                                style="top: 5px; right: 5px; width: 18px; height: 18px; z-index: 10; padding: 0;" title="Delete Photo">
                                <i class="bi bi-x" style="font-size: 0.75rem;"></i>
                            </button>
                        </div>
                        <input type="file" id="profilePhotoInput" accept=".jpg,.jpeg,.png,.gif,.svg" class="d-none" onchange="openCropper(this)">
                        <div class="small fw-bold text-center text-white mt-2" id="sidebarEmployeeName">{{ $employee->full_name ?? 'New Employee' }}</div>
                        <div class="text-white small opacity-50" id="sidebarEmployeeCode">{{ $employee->employee_code ?? 'TBD' }}</div>
                    </div>
                    
                    <div class="card-body bg-white p-3">
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex align-items-center gap-2 py-2 small">
                                <i class="bi bi-person-fill text-secondary"></i>
                                <span class="text-secondary-emphasis fw-semibold opacity-75">Name</span>
                                <span class="ms-auto text-end fw-semibold text-dark" id="summaryName">{{ $employee->full_name ?? 'Not provided' }}</span>
                            </li>
                            <li class="d-flex align-items-center gap-2 py-2 small">
                                <i class="bi bi-credit-card-2-front-fill text-secondary"></i>
                                <span class="text-secondary-emphasis fw-semibold opacity-75">CNIC</span>
                                <span class="ms-auto text-end fw-semibold text-dark" id="summaryCnic">{{ $employee->cnic ?? 'Not provided' }}</span>
                            </li>
                            <li class="d-flex align-items-center gap-2 py-2 small">
                                <i class="bi bi-gender-ambiguous text-secondary"></i>
                                <span class="text-secondary-emphasis fw-semibold opacity-75">Gender</span>
                                <span class="ms-auto text-end fw-semibold text-dark" id="summaryGender">{{ $employee->gender ?? 'Not selected' }}</span>
                            </li>
                            <li class="d-flex align-items-center gap-2 py-2 small">
                                <i class="bi bi-star-fill text-secondary"></i>
                                <span class="text-secondary-emphasis fw-semibold opacity-75">Religion</span>
                                <span class="ms-auto text-end fw-semibold text-dark" id="summaryReligion">{{ $employee->religion ?? 'Not selected' }}</span>
                            </li>
                            <li class="d-flex align-items-center gap-2 py-2 small">
                                <i class="bi bi-globe-central-south-asia text-secondary"></i>
                                <span class="text-secondary-emphasis fw-semibold opacity-75">Nationality</span>
                                <span class="ms-auto text-end fw-semibold text-dark" id="summaryNationality">{{ $employee->nationality ?? 'Not selected' }}</span>
                            </li>
                        </ul>
                    </div>
                </aside>
            </div>
