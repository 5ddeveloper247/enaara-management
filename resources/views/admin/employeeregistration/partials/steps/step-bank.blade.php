<div class="wizard-pane" id="stepPane5">
    <div id="step-5">
        <section class="d-grid gap-3">

            <!-- FORM SECTION (Original Design preserved) -->
            <div id="bankEntryForm">
                <input type="hidden" id="bank_detail_id" name="bank_detail_id">
                
                <div class="row g-3 mb-3">
                    <!-- Account Category + Salary Account -->
                    <div class="col-12">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body p-3">

                                <div class="row g-3">
                                    <div class="col-md-6" id="bankAccountCategoryWrapper">
                                        <div class="fw-bold text-uppercase small mb-2">Account Category <span class="text-danger">*</span></div>
                                        <div class="d-flex flex-wrap gap-3">
                                             <input class="btn-check" type="radio" name="account_category"
                                                 id="accountCategoryPersonal" value="Personal" checked>
                                             <label class="btn btn-outline-secondary option-chip"
                                                 for="accountCategoryPersonal">Personal Account</label>

                                             <input class="btn-check" type="radio" name="account_category"
                                                 id="accountCategoryCompany" value="Company">
                                             <label class="btn btn-outline-secondary option-chip"
                                                 for="accountCategoryCompany">Company Operated Account</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6" id="isSalaryAccountWrapper">
                                        <div class="fw-bold text-uppercase small mb-2">Salary Account <span class="text-danger">*</span></div>
                                        <div class="d-flex flex-wrap gap-2">
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

                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bank Details Information -->
                <div class="card bg-light border-0 shadow-sm mb-3">
                    <div class="card-body p-3">
                        <div class="fw-bold text-dark mb-3 small text-uppercase ls-1">
                            <span>Bank Details Information</span>
                        </div>

                        <div class="row g-3">
                             <div class="col-md-4">
                                 <label class="form-label opacity-75 small fw-bold">Account Title <span class="text-danger">*</span></label>
                                 <input type="text" name="account_title" class="form-control" id="bankDetailsAccountTitleInput"
                                     placeholder="Enter account title">
                             </div>

                             <div class="col-md-4">
                                 <label class="form-label opacity-75 small fw-bold">Account No <span class="text-danger">*</span></label>
                                 <input type="text" name="account_no" class="form-control" id="bankDetailsAccountNumberInput"
                                     placeholder="Enter account number">
                             </div>

                             <div class="col-md-4">
                                 <label class="form-label opacity-75 small fw-bold">IBAN <span class="text-danger">*</span></label>
                                 <input type="text" name="iban" class="form-control" id="bankDetailsIbanInput"
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
                                         <label class="form-label opacity-75 small fw-bold">Branch Name <span class="text-danger">*</span></label>
                                         <input type="text" name="bank_name" class="form-control"
                                             id="bankDetailsBranchNameInput"
                                             placeholder="Enter branch name">
                                     </div>

                                     <div class="col-md-6">
                                         <label class="form-label opacity-75 small fw-bold">Branch Address <span class="text-danger">*</span></label>
                                         <input type="text" name="branch_address" class="form-control"
                                             id="bankDetailsBranchAddressInput"
                                             placeholder="Enter branch address">
                                     </div>

                                     <div class="col-md-6">
                                         <label class="form-label opacity-75 small fw-bold">Branch Code <span class="text-danger">*</span></label>
                                         <input type="text" name="branch_code" class="form-control"
                                             id="bankDetailsBranchCodeInput"
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
                                <div id="bankAccountTypeWrapper">
                                    <label class="form-label opacity-75 small fw-bold d-block mb-2">
                                        A/C Type <span class="text-danger">*</span>
                                    </label>

                                     <div class="d-flex flex-wrap gap-3">
                                         <input class="btn-check" type="radio" name="account_type"
                                             id="bankDetailsAccountTypeSaving" value="Saving" checked>
                                         <label class="btn btn-outline-secondary option-chip"
                                             for="bankDetailsAccountTypeSaving">Saving</label>

                                         <input class="btn-check" type="radio" name="account_type"
                                             id="bankDetailsAccountTypeCurrent" value="Current">
                                         <label class="btn btn-outline-secondary option-chip"
                                             for="bankDetailsAccountTypeCurrent">Current</label>
                                     </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FORM ACTIONS -->
                <div class="col-12 mt-3 text-end d-flex justify-content-start gap-2">
                    <button type="button" class="btn btn-primary px-4 d-flex align-items-center gap-2" onclick="saveBankDetail()" style="border-radius: 8px;">
                        <i class='bx bx-plus-circle'></i> Save Account
                    </button>
                    <button type="button" id="bankResetBtn" class="btn btn-light px-4 d-none" onclick="resetBankForm()" style="border-radius: 8px;">
                        Cancel
                    </button>
                </div>
            </div>

            <hr class="my-4">

            <!-- SAVED ACCOUNTS LIST (Card style matching SBU/Role Levels) -->
            <div id="bankDetailsList" class="row g-3 px-1">
                @php 
                    $savedBanks = $editData['bank_details'] ?? []; 
                @endphp
                @if(empty($savedBanks))
                    <div class="col-12" id="bankEmptyState">
                        <div class="text-center py-3 bg-light rounded text-muted small border" style="border-style: dotted !important;">
                            No bank accounts saved yet.
                        </div>
                    </div>
                @else
                    @foreach($savedBanks as $bank)
                        <div class="col-12 col-md-6 col-xl-4 bank-card-item" data-id="{{ $bank['id'] }}">
                            <div class="card sbu-card border-1 rounded-3 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3 bg-main text-white rounded-2 d-flex align-items-center justify-content-center fw-bold" style="width: 45px; height: 45px; font-size: 1.1rem;">
                                                {{ strtoupper(substr($bank['bank_name'] ?? 'B', 0, 1)) }}
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-semibold small">{{ $bank['account_title'] ?? 'Account' }}</h6>
                                                <small class="text-muted small">{{ $bank['bank_name'] ?? 'N/A' }} - ({{ $bank['account_category'] ?? '-' }})</small>
                                            </div>
                                        </div>
                                        @if($bank['is_salary_account'])
                                        <span class="badge bg-success" style="font-size: 10px; padding: 4px 6px;">Primary</span>
                                        @else
                                        <span class="badge bg-secondary" style="font-size: 10px; padding: 4px 6px;">{{ $bank['account_type'] ?? 'Saving' }}</span>
                                        @endif
                                    </div>
                                    <div class="mb-2">
                                        <i class="bi bi-credit-card me-1 text-main small"></i>
                                        <small class="text-muted small"><strong>A/C No:</strong> {{ $bank['account_no'] ?? 'N/A' }}</small>
                                    </div>
                                    <div class="mb-2">
                                        <i class="bi bi-bank me-1 text-main small"></i>
                                        <small class="text-muted small"><strong>IBAN:</strong> {{ Str::limit($bank['iban'] ?? 'N/A', 30) }}</small>
                                    </div>
                                    @if(!empty($bank['branch_code']))
                                    <div class="mb-2">
                                        <i class="bi bi-hash me-1 text-main small"></i>
                                        <small class="text-muted small"><strong>Branch Code:</strong> {{ $bank['branch_code'] }}</small>
                                    </div>
                                    @endif
                                    <div class="mt-3 pt-3 border-top d-flex gap-1">
                                        <button type="button"
                                            class="btn btn-sm btn-outline-primary flex-grow-1"
                                            onclick="editBankDetail({{ $bank['id'] }})">
                                            <i class="bi bi-pencil me-1"></i>Edit
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteBankDetail({{ $bank['id'] }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

        </section>
    </div>
</div>

<!-- Template for Bank Card (SBU/Role Level style) -->
<template id="bankCardTemplate">
    <div class="col-12 col-md-6 col-xl-4 bank-card-item" data-id="">
        <div class="card sbu-card border-1 rounded-3 h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center">
                        <div class="me-3 bg-main text-white rounded-2 d-flex align-items-center justify-content-center fw-bold bank-initial-icon" style="width: 45px; height: 45px; font-size: 1.1rem;">B</div>
                        <div>
                            <h6 class="mb-0 fw-semibold small bank-title-label">Account Title</h6>
                            <small class="text-muted small bank-sub-label">Bank Name - (Category)</small>
                        </div>
                    </div>
                    <div class="salary-account-badge d-none">
                        <span class="badge bg-success" style="font-size: 10px; padding: 4px 6px;">Primary</span>
                    </div>
                    <div class="account-type-badge d-none">
                        <span class="badge bg-secondary" style="font-size: 10px; padding: 4px 6px;">Saving</span>
                    </div>
                </div>
                <div class="mb-2">
                    <i class="bi bi-credit-card me-1 text-main small"></i>
                    <small class="text-muted small"><strong>A/C No:</strong> <span class="bank-no-label">N/A</span></small>
                </div>
                <div class="mb-2">
                    <i class="bi bi-bank me-1 text-main small"></i>
                    <small class="text-muted small"><strong>IBAN:</strong> <span class="bank-iban-label">N/A</span></small>
                </div>
                <div class="mb-2 bank-branch-code-row d-none">
                    <i class="bi bi-hash me-1 text-main small"></i>
                    <small class="text-muted small"><strong>Branch Code:</strong> <span class="bank-branch-code-label">N/A</span></small>
                </div>
                <div class="mt-3 pt-3 border-top d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-outline-primary flex-grow-1 edit-bank-btn">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger delete-bank-btn">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
.bank-card-item .card {
    transition: all 0.2s ease;
}
.bank-card-item .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
}
</style>

