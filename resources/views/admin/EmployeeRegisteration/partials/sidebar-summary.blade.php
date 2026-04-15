            <div class="col-md-3">
                <aside class="card border-0 shadow-sm overflow-hidden sticky-top">
                    <div class="bg-main text-center p-2">
                        <div class="rounded-circle mx-auto mt-2 d-flex align-items-center justify-content-center position-relative overflow-hidden border-2 border-white shadow-sm bg-secondary-subtle text-secondary"
                            id="avatarPreviewWrap" style="width: 110px; height: 110px;">
                            <img id="avatarPreviewImage" alt="Employee photo preview" class="w-100 h-100 object-fit-cover d-none">
                            <i class="bi bi-person-fill" id="avatarPlaceholderIcon"></i>
                            <label class="avatar-upload-overlay position-absolute top-0 start-0 w-100 h-100 rounded-circle bg-dark bg-opacity-50 text-white d-flex flex-column align-items-center justify-content-center gap-1 small fw-semibold"
                                for="profilePhotoInput">
                                <i class="bi bi-cloud-arrow-up"></i>
                                <span>Upload Photo</span>
                            </label>
                        </div>
                        <input type="file" id="profilePhotoInput" accept="image/*" class="d-none">
                        <div class="small fw-bold text-center text-white mt-2">Shehryar Shahid</div>
                        <div class="text-white small opacity-50">nag837</div>
                    </div>
                    
                    <div class="card-body bg-white p-3">
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex align-items-center gap-2 py-2 small">
                                <i class="bi bi-person-fill text-secondary"></i>
                                <span class="text-secondary-emphasis fw-semibold opacity-75">Name</span>
                                <span class="ms-auto text-end fw-semibold text-dark" id="summaryName">Not provided</span>
                            </li>
                            <li class="d-flex align-items-center gap-2 py-2 small">
                                <i class="bi bi-credit-card-2-front-fill text-secondary"></i>
                                <span class="text-secondary-emphasis fw-semibold opacity-75">CNIC</span>
                                <span class="ms-auto text-end fw-semibold text-dark" id="summaryCnic">Not provided</span>
                            </li>
                            <li class="d-flex align-items-center gap-2 py-2 small">
                                <i class="bi bi-gender-ambiguous text-secondary"></i>
                                <span class="text-secondary-emphasis fw-semibold opacity-75">Gender</span>
                                <span class="ms-auto text-end fw-semibold text-dark" id="summaryGender">Not selected</span>
                            </li>
                            <li class="d-flex align-items-center gap-2 py-2 small">
                                <i class="bi bi-star-fill text-secondary"></i>
                                <span class="text-secondary-emphasis fw-semibold opacity-75">Religion</span>
                                <span class="ms-auto text-end fw-semibold text-dark" id="summaryReligion">Not selected</span>
                            </li>
                            <li class="d-flex align-items-center gap-2 py-2 small">
                                <i class="bi bi-globe-central-south-asia text-secondary"></i>
                                <span class="text-secondary-emphasis fw-semibold opacity-75">Nationality</span>
                                <span class="ms-auto text-end fw-semibold text-dark" id="summaryNationality">Not selected</span>
                            </li>
                        </ul>
                    </div>
                </aside>
            </div>
