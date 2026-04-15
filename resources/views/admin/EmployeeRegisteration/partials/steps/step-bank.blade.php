<div class="wizard-pane" id="stepPane5">
    <div>
        <section class="d-grid gap-3">

            <!-- Account Category + Salary Account -->
            <div class="col-12">
                <div class="card border-0 bg-light h-100">
                    <div class="card-body p-3">

                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                            <div>
                                <div class="fw-bold text-uppercase small">Account Category</div>
                            </div>

                            <div class="text-md-end">
                                <div class="fw-bold text-uppercase small mb-2">Salary Account</div>
                                <div class="d-flex flex-wrap justify-content-md-end gap-2 mt-4">
                                    <input class="btn-check" type="radio" name="is_salary_account"
                                        id="salaryAccountYes" value="1">
                                    <label class="btn btn-outline-secondary option-chip"
                                        for="salaryAccountYes">Yes</label>

                                    <input class="btn-check" type="radio" name="is_salary_account"
                                        id="salaryAccountNo" value="0" checked>
                                    <label class="btn btn-outline-secondary option-chip"
                                        for="salaryAccountNo">No</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-3">
                            <input class="btn-check" type="radio" name="bank_details_account_category"
                                id="accountCategoryPersonal" value="Personal" required>
                            <label class="btn btn-outline-secondary option-chip"
                                for="accountCategoryPersonal">Personal Account</label>

                            <input class="btn-check" type="radio" name="bank_details_account_category"
                                id="accountCategoryCompany" value="Company">
                            <label class="btn btn-outline-secondary option-chip"
                                for="accountCategoryCompany">Company Operated Account</label>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Bank Details Information -->
            <div class="card bg-light border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="fw-bold text-dark mb-3">
                        <span>Bank Details Information</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Account Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="bankDetailsAccountTitleInput"
                                name="bank_details_account_title"
                                placeholder="Enter account title">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Account No <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="bankDetailsAccountNumberInput"
                                name="bank_details_account_number"
                                placeholder="Enter account number">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">IBAN <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="bankDetailsIbanInput"
                                name="bank_details_iban"
                                placeholder="Enter IBAN">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Branch Details + Account Type -->
            <div class="row g-3">
                <div class="col-12 col-xl-6">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-body p-3">
                            <div class="fw-bold text-uppercase small mb-3">Branch Details</div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Branch Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control"
                                        id="bankDetailsBranchNameInput"
                                        name="bank_details_branch_name"
                                        placeholder="Enter branch name">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Branch Address <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control"
                                        id="bankDetailsBranchAddressInput"
                                        name="bank_details_branch_address"
                                        placeholder="Enter branch address">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Branch Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control"
                                        id="bankDetailsBranchCodeInput"
                                        name="bank_details_branch_code"
                                        placeholder="Enter branch code">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-6">
                    <div class="card border-0 bg-light h-100">
                        <div class="card-body p-3">
                            <div class="fw-bold text-uppercase small mb-3">Account Type</div>
                            <label class="form-label fw-semibold d-block mb-2">
                                A/C Type <span class="text-danger">*</span>
                            </label>

                            <div class="d-flex flex-wrap gap-3">
                                <input class="btn-check" type="radio" name="bank_details_account_type"
                                    id="bankDetailsAccountTypeSaving" value="Saving" required>
                                <label class="btn btn-outline-secondary option-chip"
                                    for="bankDetailsAccountTypeSaving">Saving</label>

                                <input class="btn-check" type="radio" name="bank_details_account_type"
                                    id="bankDetailsAccountTypeCurrent" value="Current">
                                <label class="btn btn-outline-secondary option-chip"
                                    for="bankDetailsAccountTypeCurrent">Current</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </section>
    </div>
</div>