<!-- Add SBU Canvas -->
<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="addOrganizationCanvas" aria-labelledby="addOrganizationCanvasLabel" style="width: 600px;">
    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="addOrganizationCanvasLabel">
            <i class="bi bi-building-add me-2"></i>Add New SBU
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="addOrganizationForm">
            <!-- Basic Information Section -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-info-circle me-2"></i>Basic Information
                </h6>
                
                <!-- SBU Name -->
                <div class="mb-3">
                    <label for="orgName" class="form-label fw-semibold small text-white">SBU Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="orgName" placeholder="e.g., Enaara Construction" required>
                </div>

                <!-- Registration Number -->
                <div class="mb-3">
                    <label for="orgRegNumber" class="form-label fw-semibold small text-white">Registration Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="orgRegNumber" placeholder="e.g., REG-2024-001" required>
                </div>

                <!-- Logo Upload -->
                <div class="mb-3">
                    <label for="orgLogo" class="form-label fw-semibold small text-white">SBU Logo</label>
                    <input type="file" class="form-control" id="orgLogo" accept="image/*">
                    <small class="opacity-75 text-white">Recommended size: 200x200px (PNG, JPG)</small>
                    <div class="mt-2" id="logoPreview" style="display: none;">
                        <img src="" alt="Logo Preview" class="rounded-3 border" style="width: 100px; height: 100px; object-fit: cover; border-color: #ffffff1a !important;">
                    </div>
                </div>

                <!-- Address -->
                <div class="mb-3">
                    <label for="orgAddress" class="form-label fw-semibold small text-white">Address <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="orgAddress" rows="2" placeholder="Enter SBU address" required></textarea>
                </div>

                <!-- Website -->
                <div class="mb-3">
                    <label for="orgWebsite" class="form-label fw-semibold small text-white">Website</label>
                    <input type="url" class="form-control" id="orgWebsite" placeholder="https://www.example.com">
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Configuration Section -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-gear me-2"></i>Configuration
                </h6>

                <!-- Timezone -->
                <div class="mb-3">
                    <label for="orgTimezone" class="form-label fw-semibold small text-white">Timezone <span class="text-danger">*</span></label>
                    <select class="form-select" id="orgTimezone" required>
                        <option value="">Select Timezone</option>
                        <option value="Asia/Dubai">Asia/Dubai (UTC+4)</option>
                        <option value="Asia/Riyadh">Asia/Riyadh (UTC+3)</option>
                        <option value="Asia/Karachi">Asia/Karachi (UTC+5)</option>
                        <option value="Asia/Kolkata">Asia/Kolkata (UTC+5:30)</option>
                        <option value="Europe/London">Europe/London (UTC+0)</option>
                        <option value="America/New_York">America/New_York (UTC-5)</option>
                        <option value="Asia/Singapore">Asia/Singapore (UTC+8)</option>
                        <option value="Asia/Tokyo">Asia/Tokyo (UTC+9)</option>
                    </select>
                    <small class="opacity-75 text-white">Critical for accurate attendance tracking across regions</small>
                </div>

                <!-- Work Week -->
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-white">Work Week <span class="text-danger">*</span></label>
                    <div class="row g-2">
                        <div class="col-6 col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="workSunday" name="workWeek">
                                <label class="form-check-label text-white" for="workSunday">Sunday</label>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="workMonday" name="workWeek" checked>
                                <label class="form-check-label text-white" for="workMonday">Monday</label>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="workTuesday" name="workWeek" checked>
                                <label class="form-check-label text-white" for="workTuesday">Tuesday</label>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="workWednesday" name="workWeek" checked>
                                <label class="form-check-label text-white" for="workWednesday">Wednesday</label>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="workThursday" name="workWeek" checked>
                                <label class="form-check-label text-white" for="workThursday">Thursday</label>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="workFriday" name="workWeek" checked>
                                <label class="form-check-label text-white" for="workFriday">Friday</label>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="workSaturday" name="workWeek">
                                <label class="form-check-label text-white" for="workSaturday">Saturday</label>
                            </div>
                        </div>
                    </div>
                    <small class="opacity-75 text-white d-block mt-2">Select the working days for this SBU</small>
                </div>

                <!-- Attendance Radius -->
                <div class="mb-3">
                    <label for="orgAttendanceRadius" class="form-label fw-semibold small text-white">Attendance Radius <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="orgAttendanceRadius" placeholder="100" min="1" max="10000" step="1" value="100" required>
                        <select class="form-select" id="orgAttendanceRadiusUnit" style="max-width: 120px;">
                            <option value="meters">Meters</option>
                            <option value="kilometers">Kilometers</option>
                        </select>
                    </div>
                    <small class="opacity-75 text-white">Define the geofencing radius for attendance check-in/out. Employees must be within this radius to mark attendance.</small>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Authentication Section -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-shield-lock me-2"></i>Authentication
                </h6>

                <!-- Authentication Method -->
                <div class="mb-3">
                    <label for="orgAuthMethod" class="form-label fw-semibold small text-white">Authentication Method <span class="text-danger">*</span></label>
                    <select class="form-select" id="orgAuthMethod" required>
                        <option value="email_password">Email/Password (Standard)</option>
                        <option value="sso">Single Sign-On (SSO)</option>
                    </select>
                </div>

                <!-- SSO Configuration (shown when SSO is selected) -->
                <div id="ssoConfigSection" style="display: none;">
                    <div class="mb-3">
                        <label for="ssoProvider" class="form-label fw-semibold small text-white">SSO Provider</label>
                        <select class="form-select" id="ssoProvider">
                            <option value="">Select Provider</option>
                            <option value="azure_ad">Azure Active Directory</option>
                            <option value="google_workspace">Google Workspace</option>
                            <option value="okta">Okta</option>
                            <option value="auth0">Auth0</option>
                            <option value="saml">SAML 2.0</option>
                            <option value="oauth2">OAuth 2.0</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="ssoDomain" class="form-label fw-semibold small text-white">SSO Domain</label>
                        <input type="text" class="form-control" id="ssoDomain" placeholder="e.g., enaara.com">
                    </div>
                    <div class="alert alert-info small mb-0" style="background-color: rgba(13, 110, 253, 0.2); border-color: rgba(13, 110, 253, 0.3); color: white;">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Note:</strong> SSO configuration will require additional setup after SBU creation.
                    </div>
                </div>
            </div>

            <hr class="my-4" style="border-color: #ffffffab !important">

            <!-- Hardware Link Section -->
            <div class="mb-4">
                <h6 class="fw-semibold mb-3 small">
                    <i class="bi bi-fingerprint me-2"></i>Biometric Devices
                </h6>

                <!-- Device Serial Numbers -->
                <div class="mb-3">
                    <label for="biometricDevices" class="form-label fw-semibold small text-white">Biometric Device Serial Numbers</label>
                    <textarea class="form-control" id="biometricDevices" rows="4" placeholder="Enter device serial numbers, one per line (e.g., BIO-001-2024)"></textarea>
                    <small class="opacity-75 text-white">Enter one serial number per line. Devices can be added later.</small>
                </div>

                <!-- Available Devices (Optional - for selection) -->
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-white">Available Devices</label>
                    <div class="border rounded p-3" style="max-height: 150px; overflow-y: auto; border-color: #ffffff1a !important;">
                        <div id="availableDevicesList">
                            <small class="opacity-75 text-white">No unassigned devices available</small>
                        </div>
                    </div>
                    <small class="opacity-75 text-white">Select devices from unassigned pool or enter new serial numbers above</small>
                </div>
            </div>

            <!-- Admin Assignment -->
            <div class="mb-4">
                <label for="orgAdmin" class="form-label fw-semibold small text-white">Assign Admin/HR Manager</label>
                <select class="form-select" id="orgAdmin">
                    <option value="">Select Admin (Optional - can assign later)</option>
                    <option value="1">Ahmed Ali (ahmed.ali@enaara.com)</option>
                    <option value="2">Zainab Malik (zainab.malik@enaara.com)</option>
                    <option value="3">Bilal Ahmed (bilal.ahmed@enaara.com)</option>
                </select>
                <small class="opacity-75 text-white">The assigned admin will have full access to manage this SBU</small>
            </div>
        </form>
    </div>
    <div class="offcanvas-footer border-top p-3" style="border-color: #ffffffab !important">
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Cancel</button>
            <button type="button" class="btn btn-light text-dark border-0" id="saveOrganizationBtn">
                <i class="bi bi-check-lg me-1"></i>Create SBU
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle authentication method change
    const authMethodSelect = document.getElementById('orgAuthMethod');
    const ssoConfigSection = document.getElementById('ssoConfigSection');
    
    if (authMethodSelect && ssoConfigSection) {
        authMethodSelect.addEventListener('change', function() {
            if (this.value === 'sso') {
                ssoConfigSection.style.display = 'block';
                document.getElementById('ssoProvider').required = true;
            } else {
                ssoConfigSection.style.display = 'none';
                document.getElementById('ssoProvider').required = false;
            }
        });
    }

    // Handle logo preview
    const logoInput = document.getElementById('orgLogo');
    const logoPreview = document.getElementById('logoPreview');
    
    if (logoInput && logoPreview) {
        logoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = logoPreview.querySelector('img');
                    img.src = e.target.result;
                    logoPreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Reset form when offcanvas is hidden
    const addOrgCanvas = document.getElementById('addOrganizationCanvas');
    if (addOrgCanvas) {
        addOrgCanvas.addEventListener('hidden.bs.offcanvas', function() {
            document.getElementById('addOrganizationForm').reset();
            if (logoPreview) {
                logoPreview.style.display = 'none';
            }
            if (ssoConfigSection) {
                ssoConfigSection.style.display = 'none';
            }
        });
    }

    // Prevent form from submitting on Enter key
    const form = document.getElementById('addOrganizationForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            return false;
        });
    }

    // Handle form submission
    const saveBtn = document.getElementById('saveOrganizationBtn');
    if (saveBtn) {
        saveBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const form = document.getElementById('addOrganizationForm');
            if (form && form.checkValidity()) {
                // Collect form data
                const formData = {
                    name: document.getElementById('orgName').value,
                    regNumber: document.getElementById('orgRegNumber').value,
                    logo: document.getElementById('orgLogo').files[0],
                    address: document.getElementById('orgAddress').value,
                    website: document.getElementById('orgWebsite').value,
                    timezone: document.getElementById('orgTimezone').value,
                    workWeek: Array.from(document.querySelectorAll('input[name="workWeek"]:checked')).map(cb => cb.id.replace('work', '')),
                    attendanceRadius: document.getElementById('orgAttendanceRadius').value,
                    attendanceRadiusUnit: document.getElementById('orgAttendanceRadiusUnit').value,
                    authMethod: document.getElementById('orgAuthMethod').value,
                    ssoProvider: document.getElementById('ssoProvider').value,
                    ssoDomain: document.getElementById('ssoDomain').value,
                    biometricDevices: document.getElementById('biometricDevices').value.split('\n').filter(d => d.trim()),
                    admin: document.getElementById('orgAdmin').value
                };
                
                console.log('Organization data:', formData);
                // TODO: Implement API call to save organization
                
                // Show success message and close offcanvas
                // const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('addOrganizationCanvas'));
                // if (offcanvas) {
                //     offcanvas.hide();
                // }
            } else if (form) {
                form.reportValidity();
            }
        });
    }
});
</script>
