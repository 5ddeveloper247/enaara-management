{{-- STEP 3: Police Verification --}}
<div class="step" id="step-3">
    <div class="section-title d-flex align-items-center justify-content-between">
        <span>Section C — Police Verification Information <small class="text-muted fw-normal">(Employee Only)</small></span>
        <small class="text-muted">Compliance and clearance details</small>
    </div>
    <div class="row g-2">
        <div class="col-12">
            <label class="form-label">Verification Status <span class="text-danger">*</span></label>
            <div class="d-flex flex-wrap gap-2 mt-1 p-2 rounded-3 border bg-light bg-opacity-25">
                <div class="form-check d-flex align-items-center gap-2 px-3 py-2 rounded-pill border bg-white">
                    <input class="check-input" type="radio" name="verification_status" value="Cleared">
                    <label class="form-check-label">Cleared</label>
                </div>
                <div class="form-check d-flex align-items-center gap-2 px-3 py-2 rounded-pill border bg-white">
                    <input class="check-input" type="radio" name="verification_status" value="Not Cleared">
                    <label class="form-check-label">Not Cleared</label>
                </div>
                <div class="form-check d-flex align-items-center gap-2 px-3 py-2 rounded-pill border bg-white">
                    <input class="check-input" type="radio" name="verification_status" value="In Process">
                    <label class="form-check-label">In Process</label>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label">MSR Letter No & Date</label>
            <input type="text" name="msr_letter_no" class="form-control">
        </div>
        <div class="col-md-4">
            <label class="form-label">Addressee</label>
            <input type="text" name="addressee" class="form-control">
        </div>
        <div class="col-md-4">
            <label class="form-label">Verifying Authority</label>
            <input type="text" name="verifying_authority" class="form-control">
        </div>
        <div class="col-md-4">
            <label class="form-label">Verification Letter No & Date</label>
            <input type="text" name="verification_letter_no" class="form-control">
        </div>
        <div class="col-md-4">
            <label class="form-label">Next Verification Date</label>
            <input type="date" name="next_verification_date" class="form-control">
        </div>
        <div class="col-md-4">
            <label class="form-label">Remarks</label>
            <textarea name="police_remarks" class="form-control" rows="1" style="min-height:38px;"></textarea>
        </div>
    </div>
</div>
