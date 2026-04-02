{{-- STEP 5: Bank Details --}}
<div class="step" id="step-5">
    <div class="section-title">Section E — Bank Details</div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Account Title</label>
            <input type="text" name="account_title" class="form-control">
        </div>
        <div class="col-md-6">
            <label class="form-label">Account No</label>
            <input type="text" name="account_no" class="form-control">
        </div>
        <div class="col-md-6">
            <label class="form-label">Bank & Branch / Branch Code</label>
            <input type="text" name="bank_branch" class="form-control">
        </div>
        <div class="col-md-6">
            <label class="form-label">A/C Type</label>
            <div class="d-flex gap-3 mt-1">
                <div class="form-check d-flex align-items-center gap-1">
                    <input class="check-input" type="radio" name="account_type" value="Saving">
                    <label class="form-check-label">Saving</label>
                </div>
                <div class="form-check d-flex align-items-center gap-1">
                    <input class="check-input" type="radio" name="account_type" value="Current">
                    <label class="form-check-label">Current</label>
                </div>
            </div>
        </div>
    </div>
</div>
