{{-- STEP 7: Academic Background
<div class="step" id="step-7">
    <div class="section-title">Section G — Academic Background / Professional Trainings / Certification
        <small class="text-muted fw-normal">(Start from Recent)</small>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="bg-main">
                <tr>
                    <th>#</th>
                    <th>Degree / Certificate</th>
                    <th>Grade / Div / CGPA</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Field of Study</th>
                    <th>University / Board / Institute</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="academicTable">
                <tr>
                    <td>1</td>
                    <td><input type="text" class="form-control form-control-sm"></td>
                    <td><input type="text" class="form-control form-control-sm"></td>
                    <td><input type="date" class="form-control form-control-sm"></td>
                    <td><input type="date" class="form-control form-control-sm"></td>
                    <td><input type="text" class="form-control form-control-sm"></td>
                    <td><input type="text" class="form-control form-control-sm"></td>
                    <td> <button
                            type="button"class="action-btn border-0 text-danger bg-danger-subtle delete-shift-type"
                            onclick="removeRow(this)" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
            </tbody>
        </table>
    </div>
    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addAcademicRow()">+ Add
        Row</button>
</div> --}}


{{-- STEP 7 layout with vertical left nav --}}
<div class="step" id="step-7">  
    <div class="d-flex gap-3">

        {{-- Left Vertical Nav --}}
        <div class="d-flex flex-column gap-1" style="min-width:160px">
            <button type="button" class="btn btn-primary btn-sm text-start sub-nav-btn active-sub"
                data-target="s7-academic" onclick="showSubSection(this, 's7-academic')">Academic</button>
            <button type="button" class="btn btn-outline-secondary btn-sm text-start sub-nav-btn"
                data-target="s7-employment" onclick="showSubSection(this, 's7-employment')">Employment</button>
            <button type="button" class="btn btn-outline-secondary btn-sm text-start sub-nav-btn"
                data-target="s7-medical" onclick="showSubSection(this, 's7-medical')">Medical</button>
            <button type="button" class="btn btn-outline-secondary btn-sm text-start sub-nav-btn"
                data-target="s7-references" onclick="showSubSection(this, 's7-references')">References</button>
            <button type="button" class="btn btn-outline-secondary btn-sm text-start sub-nav-btn"
                data-target="s7-declaration" onclick="showSubSection(this, 's7-declaration')">Declaration</button>
            <button type="button" class="btn btn-outline-secondary btn-sm text-start sub-nav-btn"
                data-target="s7-consent" onclick="showSubSection(this, 's7-consent')">Consent</button>
            <button type="button" class="btn btn-outline-secondary btn-sm text-start sub-nav-btn"
                data-target="s7-checklist" onclick="showSubSection(this, 's7-checklist')">Checklist</button>
            <button type="button" class="btn btn-outline-secondary btn-sm text-start sub-nav-btn"
                data-target="s7-history" onclick="showSubSection(this, 's7-history')">Service History</button>
        </div>

        {{-- Right Content --}}
        <div class="flex-grow-1">

            {{-- G: Academic --}}
            <div class="sub-section" id="s7-academic">
                <div class="section-title">Section G — Academic Background / Professional Trainings / Certification
                    <small class="text-muted fw-normal">(Start from Recent)</small>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="bg-main">
                            <tr>
                                <th>#</th>
                                <th>Degree / Certificate</th>
                                <th>Grade / Div / CGPA</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Field of Study</th>
                                <th>University / Board / Institute</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="academicTable">
                            <tr>
                                <td>1</td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                                <td><input type="date" class="form-control form-control-sm"></td>
                                <td><input type="date" class="form-control form-control-sm"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                                <td> <button
                                        type="button"class="action-btn border-0 text-danger bg-danger-subtle delete-shift-type"
                                        onclick="removeRow(this)" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addAcademicRow()">+ Add
                    Row</button>
            </div>

            {{-- H: Employment History --}}
            <div class="sub-section d-none" id="s7-employment">
                <div class="section-title">Section H — Employment History <small class="text-muted fw-normal">(Start
                        from Recent)</small></div>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="bg-main">
                            <tr>
                                <th>#</th>
                                <th>Organization</th>
                                <th>Designation</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Salary</th>
                                <th>Reason for Leaving</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="employmentTable">
                            <tr>
                                <td>1</td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                                <td><input type="date" class="form-control form-control-sm"></td>
                                <td><input type="date" class="form-control form-control-sm"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                                <td><button type="button" class="btn btn-sm btn-outline-danger"
                                        onclick="removeRow(this)">✕</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addEmploymentRow()">+ Add
                    Row</button>
            </div>

            {{-- I: Medical --}}
            <div class="sub-section d-none" id="s7-medical">
                <div class="section-title">Section I — Medical Ailment / History / Disability</div>
                <div class="row g-3">
                    <div class="col-12"><label class="form-label">Last Medical Fitness Test — Date & Results</label>
                        <textarea class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Do you have any disability?</label>
                        <div class="d-flex gap-3 mt-1">
                            <div class="form-check"><input class="check-input" type="radio"
                                    name="disability"><label class="form-check-label">Yes</label></div>
                            <div class="form-check"><input class="check-input" type="radio"
                                    name="disability"><label class="form-check-label">No</label></div>
                        </div>
                    </div>
                    <div class="col-md-4"><label class="form-label">Blood Group</label><input type="text"
                            class="form-control"></div>
                    <div class="col-md-4">
                        <label class="form-label">If Yes</label>
                        <select class="form-select">
                            <option value="">Select</option>
                            <option>Partially Disabled</option>
                            <option>Fully Disabled at Home</option>
                        </select>
                    </div>
                    <div class="col-12"><label class="form-label">Disease / Disability Description</label>
                        <textarea class="form-control" rows="2"></textarea>
                    </div>
                </div>
            </div>

            {{-- J: References --}}
            <div class="sub-section d-none" id="s7-references">
                <div class="section-title">Section J — References</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <p class="fw-semibold mb-2 text-primary">Reference 1</p>
                        <div class="row g-2">
                            <div class="col-12"><label class="form-label">Name</label><input type="text"
                                    class="form-control"></div>
                            <div class="col-12"><label class="form-label">Designation</label><input type="text"
                                    class="form-control"></div>
                            <div class="col-12"><label class="form-label">Organization</label><input type="text"
                                    class="form-control"></div>
                            <div class="col-12"><label class="form-label">Contact No</label><input type="tel"
                                    class="form-control"></div>
                            <div class="col-12">
                                <label class="form-label">Relationship</label>
                                <select class="form-select">
                                    <option value="">Select</option>
                                    <option>Family</option>
                                    <option>Friend</option>
                                    <option>Academic</option>
                                    <option>Professional</option>
                                    <option>Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <p class="fw-semibold mb-2 text-primary">Reference 2</p>
                        <div class="row g-2">
                            <div class="col-12"><label class="form-label">Name</label><input type="text"
                                    class="form-control"></div>
                            <div class="col-12"><label class="form-label">Designation</label><input type="text"
                                    class="form-control"></div>
                            <div class="col-12"><label class="form-label">Organization</label><input type="text"
                                    class="form-control"></div>
                            <div class="col-12"><label class="form-label">Contact No</label><input type="tel"
                                    class="form-control"></div>
                            <div class="col-12">
                                <label class="form-label">Relationship</label>
                                <select class="form-select">
                                    <option value="">Select</option>
                                    <option>Family</option>
                                    <option>Friend</option>
                                    <option>Academic</option>
                                    <option>Professional</option>
                                    <option>Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- K: Declaration --}}
            <div class="sub-section d-none" id="s7-declaration">
                <div class="section-title">Section K — Declarative Statement / Employee Declaration</div>
                <div class="alert alert-light border" style="font-size:.9rem">
                    I S/O, D/O, W/O, C/O, solemnly affirm in the presence of Almighty Allah that the information given
                    in this Personal Data Form is true and correct to the best of my knowledge and belief. I fully
                    understand that my false statement or material omission / suppression of any fact shall render me
                    liable to disciplinary action and / or dismissal from service. I authorize MSR to contact my
                    previous employers and references provided in this form / CV for further information / verification
                    as deemed necessary.
                </div>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Duty Station</label><input type="text"
                            class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Date</label><input type="date"
                            class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Signature of Employee / Thumb
                            Impression</label><input type="text" class="form-control" placeholder="Signature">
                    </div>
                </div>
            </div>

            {{-- L: Parental Consent --}}
            <div class="sub-section d-none" id="s7-consent">
                <div class="section-title">Section L — Parental Consent & Declaration <small
                        class="text-muted fw-normal">(Intern Only)</small></div>
                <div class="alert alert-light border" style="font-size:.9rem">
                    I, F/O, M/O, G/O hereby give my consent for their participation in the internship program at MSR. I
                    understand and accept that my child may be using their personal laptop for work purposes, and the
                    company is not liable for any damage to personal devices or equipment. My child will follow the
                    company's policies and will be working under structured supervision. I understand the nature,
                    duration, and expectations of this internship program.
                </div>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Duty Station</label><input type="text"
                            class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Date</label><input type="date"
                            class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Signature of Parent / Guardian</label><input
                            type="text" class="form-control" placeholder="Signature"></div>
                </div>
            </div>

            {{-- M: Attachment Checklist --}}
            <div class="sub-section d-none" id="s7-checklist">
                <div class="section-title">Section M — Attachment Checklist & Pre-Employment Status</div>

                <p class="fw-semibold mb-2">M-I: Document Attachment Checklist</p>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-sm align-middle">
                        <thead class="bg-main">
                            <tr>
                                <th>S.No</th>
                                <th>Document Description</th>
                                <th>Mandatory</th>
                                <th>Category Requirement</th>
                                <th>✓</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>Colored Passport Size Photo (Blue background)</td>
                                <td>Yes</td>
                                <td>All</td>
                                <td><input type="checkbox" class="check-input"></td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>Copy of CNIC - Front and Back</td>
                                <td>Yes</td>
                                <td>All</td>
                                <td><input type="checkbox" class="check-input"></td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td>Copy of Father's CNIC (If applicable)</td>
                                <td>Yes</td>
                                <td>All</td>
                                <td><input type="checkbox" class="check-input"></td>
                            </tr>
                            <tr>
                                <td>4</td>
                                <td>Copy of Domicile Certificate</td>
                                <td>Yes</td>
                                <td>All</td>
                                <td><input type="checkbox" class="check-input"></td>
                            </tr>
                            <tr>
                                <td>5</td>
                                <td>Copy of NTN Proof (If applicable)</td>
                                <td>Yes</td>
                                <td>All</td>
                                <td><input type="checkbox" class="check-input"></td>
                            </tr>
                            <tr>
                                <td>6</td>
                                <td>NOK CNIC Copy</td>
                                <td>Yes</td>
                                <td>All</td>
                                <td><input type="checkbox" class="check-input"></td>
                            </tr>
                            <tr>
                                <td>7</td>
                                <td>Proof of Marital Status</td>
                                <td>If applicable</td>
                                <td>Section A</td>
                                <td><input type="checkbox" class="check-input"></td>
                            </tr>
                            <tr>
                                <td>8</td>
                                <td>All Academic Degrees/Certificates</td>
                                <td>Yes</td>
                                <td>All</td>
                                <td><input type="checkbox" class="check-input"></td>
                            </tr>
                            <tr>
                                <td>9</td>
                                <td>Academic Transcripts for Most Recent Degree</td>
                                <td>Yes</td>
                                <td>All</td>
                                <td><input type="checkbox" class="check-input"></td>
                            </tr>
                            <tr>
                                <td>10</td>
                                <td>Professional Trainings/Certifications</td>
                                <td>Yes</td>
                                <td>All</td>
                                <td><input type="checkbox" class="check-input"></td>
                            </tr>
                            <tr>
                                <td>11</td>
                                <td>Detailed CV/Resume</td>
                                <td>Yes</td>
                                <td>All</td>
                                <td><input type="checkbox" class="check-input"></td>
                            </tr>
                            <tr>
                                <td>12</td>
                                <td>Experience Certificates from All Previous Employers</td>
                                <td>Yes</td>
                                <td>All</td>
                                <td><input type="checkbox" class="check-input"></td>
                            </tr>
                            <tr>
                                <td>13</td>
                                <td>Last Drawn Salary Slip</td>
                                <td>Yes</td>
                                <td>All</td>
                                <td><input type="checkbox" class="check-input"></td>
                            </tr>
                            <tr>
                                <td>14</td>
                                <td>Bank Account Verification Letter</td>
                                <td>Yes</td>
                                <td>All</td>
                                <td><input type="checkbox" class="check-input"></td>
                            </tr>
                            <tr>
                                <td>15</td>
                                <td>Offer Letter / Letter of Engagement (Signed)</td>
                                <td>If applicable</td>
                                <td>Intern/Contractual</td>
                                <td><input type="checkbox" class="check-input"></td>
                            </tr>
                            <tr>
                                <td>16</td>
                                <td>Consultancy Agreement/Contract (Signed)</td>
                                <td>If applicable</td>
                                <td>Consultants</td>
                                <td><input type="checkbox" class="check-input"></td>
                            </tr>
                            <tr>
                                <td>17</td>
                                <td>Parent/Guardian Consent Form (Signed)</td>
                                <td>If applicable</td>
                                <td>Interns</td>
                                <td><input type="checkbox" class="check-input"></td>
                            </tr>
                            <tr>
                                <td>18</td>
                                <td>Discharge/Retirement Order & Medical Category Certificate</td>
                                <td>If applicable</td>
                                <td>Ex-Armed Forces</td>
                                <td><input type="checkbox" class="check-input"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p class="fw-semibold mb-2">M-II: Before Employment Checklist <small class="text-muted fw-normal">(HR
                        / Recruitment Use Only)</small></p>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle">
                        <thead class="bg-main">
                            <tr>
                                <th>S.No</th>
                                <th>Pre-Employment Activity</th>
                                <th>Status ✓</th>
                                <th>Date Completed</th>
                                <th>HR Initial</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>Interview Assessment Form Received and Finalized</td>
                                <td><input type="checkbox" class="check-input"></td>
                                <td><input type="date" class="form-control form-control-sm"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>Offer Letter Issued</td>
                                <td><input type="checkbox" class="check-input"></td>
                                <td><input type="date" class="form-control form-control-sm"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td>Signed Offer Letter Received</td>
                                <td><input type="checkbox" class="check-input"></td>
                                <td><input type="date" class="form-control form-control-sm"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                            </tr>
                            <tr>
                                <td>4</td>
                                <td>Reference Checks Completed (Minimum 2)</td>
                                <td><input type="checkbox" class="check-input"></td>
                                <td><input type="date" class="form-control form-control-sm"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                            </tr>
                            <tr>
                                <td>5</td>
                                <td>Police Verification Request Initiated</td>
                                <td><input type="checkbox" class="check-input"></td>
                                <td><input type="date" class="form-control form-control-sm"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                            </tr>
                            <tr>
                                <td>6</td>
                                <td>Medical Fitness Test Cleared</td>
                                <td><input type="checkbox" class="check-input"></td>
                                <td><input type="date" class="form-control form-control-sm"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                            </tr>
                            <tr>
                                <td>7</td>
                                <td>Induction/Onboarding Form Completed by HR</td>
                                <td><input type="checkbox" class="check-input"></td>
                                <td><input type="date" class="form-control form-control-sm"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                            </tr>
                            <tr>
                                <td>8</td>
                                <td>IT Account/Email ID Creation Initiated</td>
                                <td><input type="checkbox" class="check-input"></td>
                                <td><input type="date" class="form-control form-control-sm"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                            </tr>
                            <tr>
                                <td>9</td>
                                <td>Workstation/Desk Allocated and Prepared</td>
                                <td><input type="checkbox" class="check-input"></td>
                                <td><input type="date" class="form-control form-control-sm"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                            </tr>
                            <tr>
                                <td>10</td>
                                <td>All Mandatory Documents Received</td>
                                <td><input type="checkbox" class="check-input"></td>
                                <td><input type="date" class="form-control form-control-sm"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                            </tr>
                            <tr>
                                <td>11</td>
                                <td>Probation Evaluation Form Signed and Attached</td>
                                <td><input type="checkbox" class="check-input"></td>
                                <td><input type="date" class="form-control form-control-sm"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Service History --}}
            <div class="sub-section d-none" id="s7-history">
                <div class="section-title">Service History</div>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="bg-main">
                            <tr>
                                <th>Sr.</th>
                                <th>Date</th>
                                <th>Category</th>
                                <th>Details / Description</th>
                                <th>Reference No</th>
                                <th>Approved By</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td><input type="date" class="form-control form-control-sm"></td>
                                <td><span class="badge bg-secondary">Appointment</span></td>
                                <td><input type="text" class="form-control form-control-sm"
                                        placeholder="Appointed as ___"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td><input type="date" class="form-control form-control-sm"></td>
                                <td><span class="badge bg-success">Confirmation</span></td>
                                <td><input type="text" class="form-control form-control-sm"
                                        placeholder="Probation completed"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td><input type="date" class="form-control form-control-sm"></td>
                                <td><span class="badge bg-info text-dark">Salary Increment</span></td>
                                <td><input type="text" class="form-control form-control-sm"
                                        placeholder="Revised salary"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                            </tr>
                            <tr>
                                <td>4</td>
                                <td><input type="date" class="form-control form-control-sm"></td>
                                <td><span class="badge bg-primary">Promotion/Transfer</span></td>
                                <td><input type="text" class="form-control form-control-sm"
                                        placeholder="From ___ to ___"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                            </tr>
                            <tr>
                                <td>5</td>
                                <td><input type="date" class="form-control form-control-sm"></td>
                                <td><span class="badge bg-warning text-dark">Warning/Discipline</span></td>
                                <td><input type="text" class="form-control form-control-sm"
                                        placeholder="Verbal / Written"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                            </tr>
                            <tr>
                                <td>6</td>
                                <td><input type="date" class="form-control form-control-sm"></td>
                                <td><span class="badge bg-secondary">Leave / LWP</span></td>
                                <td><input type="text" class="form-control form-control-sm"
                                        placeholder="Period ___"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                            </tr>
                            <tr>
                                <td>7</td>
                                <td><input type="date" class="form-control form-control-sm"></td>
                                <td><span class="badge bg-dark">Suspension</span></td>
                                <td><input type="text" class="form-control form-control-sm" placeholder="Reason">
                                </td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                            </tr>
                            <tr>
                                <td>8</td>
                                <td><input type="date" class="form-control form-control-sm"></td>
                                <td><span class="badge bg-danger">Termination/Resignation</span></td>
                                <td><input type="text" class="form-control form-control-sm"
                                        placeholder="Last working day"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                                <td><input type="text" class="form-control form-control-sm"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>{{-- end right content --}}
    </div>{{-- end d-flex --}}
</div>{{-- end step-7 --}}


<script>
    function showSubSection(btn, targetId) {
        document.querySelectorAll('.sub-section').forEach(s => s.classList.add('d-none'));
        document.querySelectorAll('.sub-nav-btn').forEach(b => {
            b.classList.remove('btn-primary');
            b.classList.add('btn-outline-secondary');
        });
        document.getElementById(targetId).classList.remove('d-none');
        btn.classList.remove('btn-outline-secondary');
        btn.classList.add('btn-primary');
    }

    function addEmploymentRow() {
        const tbody = document.getElementById('employmentTable');
        const count = tbody.rows.length + 1;
        tbody.insertAdjacentHTML('beforeend', `
            <tr>
                <td>${count}</td>
                <td><input type="text" class="form-control form-control-sm"></td>
                <td><input type="text" class="form-control form-control-sm"></td>
                <td><input type="date" class="form-control form-control-sm"></td>
                <td><input type="date" class="form-control form-control-sm"></td>
                <td><input type="text" class="form-control form-control-sm"></td>
                <td><input type="text" class="form-control form-control-sm"></td>
                <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)">✕</button></td>
            </tr>`);
    }
</script>
