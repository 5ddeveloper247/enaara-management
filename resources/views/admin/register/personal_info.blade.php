{{-- STEP 2: Police Verification --}}
<div class="step" id="step-2">
    <div class="section-title">Section B — Police Verification Information <small class="text-muted fw-normal">(Employee Only)</small></div>
    <div class="row g-3">
        <div class="col-12">
            <label class="form-label">Verification Status</label>
            <div class="d-flex gap-3 mt-1">
                <div class="form-check">
                    <input class="check-input" type="radio" name="verification_status" value="Cleared">
                    <label class="form-check-label">Cleared</label>
                </div>
                <div class="form-check">
                    <input class="check-input" type="radio" name="verification_status" value="Not Cleared">
                    <label class="form-check-label">Not Cleared</label>
                </div>
                <div class="form-check">
                    <input class="check-input" type="radio" name="verification_status" value="In Process">
                    <label class="form-check-label">In Process</label>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label">MSR Letter No & Date</label>
            <input type="text" name="msr_letter_no" class="form-control">
        </div>
        <div class="col-md-6">
            <label class="form-label">Addressee</label>
            <input type="text" name="addressee" class="form-control">
        </div>
        <div class="col-md-6">
            <label class="form-label">Verifying Authority</label>
            <input type="text" name="verifying_authority" class="form-control">
        </div>
        <div class="col-md-6">
            <label class="form-label">Verification Letter No & Date</label>
            <input type="text" name="verification_letter_no" class="form-control">
        </div>
        <div class="col-md-6">
            <label class="form-label">Next Verification Date</label>
            <input type="date" name="next_verification_date" class="form-control">
        </div>
        <div class="col-12">
            <label class="form-label">Remarks</label>
            <textarea name="police_remarks" class="form-control" rows="3"></textarea>
        </div>
    </div>
</div>
