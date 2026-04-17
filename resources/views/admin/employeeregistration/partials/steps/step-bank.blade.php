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

            <!-- SAVED ACCOUNTS LIST (Card style matching Dept/Org) -->
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
                            <div class="card shadow-sm border position-relative" style="border-radius: 12px; background: #fff;">
                                <div class="card-body p-2">
                                    <div class="d-flex align-items-center gap-2 mb-2 pe-5">
                                        <!-- Initial Box -->
                                        <div class="d-flex align-items-center justify-content-center text-white fw-bold rounded bank-initial-box" 
                                             style="width:40px;height:40px;background:#012d5a;font-size:16px;">
                                            {{ strtoupper(substr($bank['bank_name'] ?? 'B', 0, 1)) }}
                                        </div>
                                        <div class="overflow-hidden">
                                            <div class="fw-bold text-dark text-truncate small">{{ $bank['account_title'] ?? 'Account' }}</div>
                                            <div class="text-muted text-truncate" style="font-size: 10px;">
                                                Source: {{ $bank['bank_name'] ?? 'N/A' }} - ({{ $bank['account_category'] ?? '-' }})
                                            </div>
                                        </div>
                                        
                                        @if($bank['is_salary_account'])
                                            <div class="position-absolute" style="top: 10px; right: 10px;">
                                                <span class="badge border text-dark bg-light px-2 py-1" style="font-size: 9px; border-color: #012d5a !important;">
                                                    Primary
                                                </span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="px-1 mb-2" style="font-size: 11px;">
                                        <div class="d-flex gap-2 text-muted">
                                            <span class="fw-bold text-dark">A/C No:</span> <span>{{ $bank['account_no'] ?? 'N/A' }}</span>
                                        </div>
                                        <div class="d-flex gap-2 text-muted">
                                            <span class="fw-bold text-dark">IBAN:</span> <span class="text-truncate">{{ $bank['iban'] ?? 'N/A' }}</span>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2 border-top pt-2 mt-1">
                                        <button type="button" class="btn btn-sm btn-outline-primary flex-grow-1 d-flex align-items-center justify-content-center gap-1" onclick="editBankDetail({{ $bank['id'] }})" style="font-size: 11px; padding: 4px;">
                                            Edit
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center" onclick="deleteBankDetail({{ $bank['id'] }})" style="padding: 4px 8px; border-color: #ff0000 !important; color: #ff0000 !important;">
                                            <i class='bx bx-trash'></i>
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

<!-- Template for Compact Bank Card -->
<template id="bankCardTemplate">
    <div class="col-12 col-md-6 col-xl-4 bank-card-item" data-id="">
        <div class="card shadow-sm border position-relative" style="border-radius: 12px; background: #fff;">
            <div class="card-body p-2">
                <div class="d-flex align-items-center gap-2 mb-2 pe-5">
                    <div class="d-flex align-items-center justify-content-center text-white fw-bold rounded bank-initial-icon" 
                         style="width:40px;height:40px;background:#012d5a;font-size:16px;">B</div>
                    <div class="overflow-hidden">
                        <div class="bank-title-label fw-bold text-dark text-truncate small">Account Title</div>
                        <div class="bank-sub-label text-muted text-truncate" style="font-size: 10px;">Source: Bank Name - (Category)</div>
                    </div>
                    <div class="salary-account-badge position-absolute d-none" style="top: 10px; right: 10px;">
                        <span class="badge border text-dark bg-light px-2 py-1" style="font-size: 9px; border-color: #012d5a !important;">
                            Primary
                        </span>
                    </div>
                </div>

                <div class="px-1 mb-2" style="font-size: 11px;">
                    <div class="d-flex gap-2 text-muted">
                        <span class="fw-bold text-dark">A/C No:</span> <span class="bank-no-label">N/A</span>
                    </div>
                    <div class="d-flex gap-2 text-muted">
                        <span class="fw-bold text-dark">IBAN:</span> <span class="bank-iban-label text-truncate">N/A</span>
                    </div>
                </div>

                <div class="d-flex gap-2 border-top pt-2 mt-1">
                    <button type="button" class="btn btn-sm btn-outline-primary flex-grow-1 d-flex align-items-center justify-content-center gap-1 edit-bank-btn" style="font-size: 11px; padding: 4px;">
                        Edit
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center delete-bank-btn" style="padding: 4px 8px; border-color: #ff0000 !important; color: #ff0000 !important;">
                        <i class='bx bx-trash'></i>
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
