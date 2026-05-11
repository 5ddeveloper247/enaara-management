<?php
$file = 'd:/enaara-management/resources/views/admin/employeeregistration/partials/steps/step-more.blade.php';
$content = file_get_contents($file);

// Find the template part
$templateStart = strpos($content, '<template id="moreEmploymentRecordTemplate">');
$templateEnd = strpos($content, '</template>', $templateStart);

$newTemplate = '<template id="moreEmploymentRecordTemplate">
                                            <div class="employment-record-row mb-2 bg-light" data-employment-row>
                                                <div class="employment-record-header">
                                                    <span class="employment-record-index" data-employment-index>Record 1</span>
                                                    <div class="employment-record-actions">
                                                        <button type="button" class="btn btn-sm btn-outline-primary" data-employment-save title="Save record">
                                                            <i class="bi bi-floppy"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" data-employment-remove>
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="row g-2">
                                                    <div class="col-12 col-md-6 col-xl-3">
                                                        <label class="form-label">Organization <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control employment-field-input" name="employments[][organization]"
                                                            data-employment-organization placeholder="Enter organization" maxlength="100" required>
                                                        <div class="employment-field-preview" data-employment-preview-organization>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-3">
                                                        <label class="form-label">Designation <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control employment-field-input" name="employments[][designation]"
                                                            data-employment-designation placeholder="Enter designation" maxlength="50" required>
                                                        <div class="employment-field-preview" data-employment-preview-designation>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-2">
                                                        <label class="form-label">From <span class="text-danger">*</span></label>
                                                        <input type="date" class="form-control employment-field-input" name="employments[][from_date]"
                                                            data-employment-from-date placeholder="yyyy-mm-dd" required>
                                                        <div class="employment-field-preview" data-employment-preview-from-date>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-2">
                                                        <label class="form-label">To <span class="text-danger">*</span></label>
                                                        <input type="date" class="form-control employment-field-input" name="employments[][to_date]"
                                                            data-employment-to-date placeholder="yyyy-mm-dd" required>
                                                        <div class="employment-field-preview" data-employment-preview-to-date>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-xl-2">
                                                        <label class="form-label">Salary</label>
                                                        <input type="number" class="form-control employment-field-input" name="employments[][salary]"
                                                            data-employment-salary placeholder="Enter salary" maxlength="20" step="1">
                                                        <div class="employment-field-preview" data-employment-preview-salary>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-6">
                                                        <label class="form-label">Reason for Leaving</label>
                                                        <input type="text" class="form-control employment-field-input" name="employments[][reason_for_leaving]"
                                                            data-employment-reason placeholder="Enter reason for leaving" maxlength="200">
                                                        <div class="employment-field-preview" data-employment-preview-reason-for-leaving>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-3">
                                                        <label class="form-label">HR Contact Number</label>
                                                        <input type="text" class="form-control employment-field-input" name="employments[][hr_contact]"
                                                            data-employment-hr-contact placeholder="Enter HR contact" maxlength="15">
                                                        <div class="employment-field-preview" data-employment-preview-hr-contact>-</div>
                                                    </div>
                                                    <div class="col-12 col-md-3">
                                                        <label class="form-label">HR Email</label>
                                                        <input type="email" class="form-control employment-field-input" name="employments[][hr_email]"
                                                            data-employment-hr-email placeholder="Enter HR email" maxlength="100">
                                                        <div class="employment-field-preview" data-employment-preview-hr-email>-</div>
                                                    </div>

                                                    <!-- Employment Documents -->
                                                    <div class="col-12 mt-2 academic-certificate-section">
                                                        <div class="row g-2">
                                                            <!-- Experience Letter (Required) -->
                                                            <div class="col-12 col-md-6">
                                                                <label class="form-label small text-secondary fw-bold mb-1">Experience Letter <span class="text-danger">*</span></label>
                                                                <div class="academic-upload-placeholder" data-employment-exp-upload-container onclick="this.querySelector(\'input\').click()">
                                                                    <div class="d-flex align-items-center gap-2 text-secondary">
                                                                        <i class="bi bi-upload"></i>
                                                                        <span class="small">No file chosen</span>
                                                                    </div>
                                                                    <div class="text-secondary small" style="font-size: 0.65rem; letter-spacing: 0.5px;">JPG • PNG • PDF • 20MB</div>
                                                                    <input type="file" class="d-none employment-field-input" 
                                                                        name="experience_letter"
                                                                        data-employment-exp-file accept=".jpg,.jpeg,.png,.pdf">
                                                                </div>

                                                                <div class="academic-uploaded-file-item mt-2 d-none" data-employment-exp-view-container>
                                                                    <div class="d-flex align-items-center justify-content-between bg-light p-2 rounded-2 border" style="background-color: #f7f7ee !important;">
                                                                        <div class="d-flex align-items-center gap-2 overflow-hidden">
                                                                            <i class="bi bi-file-earmark-check text-success"></i>
                                                                            <span class="small text-dark text-truncate" data-employment-exp-filename style="max-width: 150px;">Experience_Letter.pdf</span>
                                                                            <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.6rem;">Uploaded</span>
                                                                        </div>
                                                                        <div class="d-flex gap-1">
                                                                            <a href="#" target="_blank" class="btn btn-sm btn-white shadow-sm py-0 px-2" data-employment-exp-link title="View Document">
                                                                                <i class="bi bi-eye" style="font-size: 0.8rem;"></i>
                                                                            </a>
                                                                            <button type="button" class="btn btn-sm btn-white shadow-sm py-0 px-2 btn-icon-delete" data-employment-exp-remove title="Remove Document">
                                                                                <i class="bi bi-trash" style="font-size: 0.8rem;"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Salary Slip (Optional) -->
                                                            <div class="col-12 col-md-6">
                                                                <label class="form-label small text-secondary fw-bold mb-1">Salary Slip (Optional)</label>
                                                                <div class="academic-upload-placeholder" data-employment-salary-upload-container onclick="this.querySelector(\'input\').click()">
                                                                    <div class="d-flex align-items-center gap-2 text-secondary">
                                                                        <i class="bi bi-upload"></i>
                                                                        <span class="small">No file chosen</span>
                                                                    </div>
                                                                    <div class="text-secondary small" style="font-size: 0.65rem; letter-spacing: 0.5px;">JPG • PNG • PDF • 20MB</div>
                                                                    <input type="file" class="d-none employment-field-input" 
                                                                        name="salary_slip"
                                                                        data-employment-salary-file accept=".jpg,.jpeg,.png,.pdf">
                                                                </div>

                                                                <div class="academic-uploaded-file-item mt-2 d-none" data-employment-salary-view-container>
                                                                    <div class="d-flex align-items-center justify-content-between bg-light p-2 rounded-2 border" style="background-color: #f7f7ee !important;">
                                                                        <div class="d-flex align-items-center gap-2 overflow-hidden">
                                                                            <i class="bi bi-file-earmark-check text-success"></i>
                                                                            <span class="small text-dark text-truncate" data-employment-salary-filename style="max-width: 150px;">Salary_Slip.pdf</span>
                                                                            <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.6rem;">Uploaded</span>
                                                                        </div>
                                                                        <div class="d-flex gap-1">
                                                                            <a href="#" target="_blank" class="btn btn-sm btn-white shadow-sm py-0 px-2" data-employment-salary-link title="View Document">
                                                                                <i class="bi bi-eye" style="font-size: 0.8rem;"></i>
                                                                            </a>
                                                                            <button type="button" class="btn btn-sm btn-white shadow-sm py-0 px-2 btn-icon-delete" data-employment-salary-remove title="Remove Document">
                                                                                <i class="bi bi-trash" style="font-size: 0.8rem;"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>';

$content = substr_replace($content, $newTemplate, $templateStart, $templateEnd - $templateStart);

file_put_contents($file, $content);
echo "Template fixed successfully";
