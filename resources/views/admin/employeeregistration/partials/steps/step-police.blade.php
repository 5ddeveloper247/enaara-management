                        <div class="wizard-pane px-3" id="stepPane3">
                            <div class="d-grid gap-3" id="step-3">
                                <div class="card bg-light border-0 shadow-sm">
                                    <div class="card-body p-3">
                                        <div class="fw-bold text-dark mb-3">
                                            <span>Police Verification Information</span>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <div class="border rounded p-3" style="background-color: #01244518">
                                                    <label class="form-label fw-semibold d-block mb-2">Verification Status <span
                                                            class="text-danger">*</span></label>
                                                    @php
                                                        $pv = $employee->policeVerification ?? null;
                                                        $status = $pv->verification_status ?? '';
                                                    @endphp
                                                    <div class="d-flex flex-wrap gap-2" id="policeVerificationStatusWrapper">
                                                        <input type="radio" class="btn-check" name="verification_status"
                                                            id="policeVerificationStatusCleared" value="Cleared" required {{ $status == 'Cleared' ? 'checked' : '' }}>
                                                        <label class="btn btn-outline-secondary rounded-pill px-3 py-1"
                                                            for="policeVerificationStatusCleared">Cleared</label>
 
                                                        <input type="radio" class="btn-check" name="verification_status"
                                                            id="policeVerificationStatusNotCleared" value="Not Cleared" {{ $status == 'Not Cleared' ? 'checked' : '' }}>
                                                        <label class="btn btn-outline-secondary rounded-pill px-3 py-1"
                                                            for="policeVerificationStatusNotCleared">Not Cleared</label>
 
                                                        <input type="radio" class="btn-check" name="verification_status"
                                                            id="policeVerificationStatusInProcess" value="In Process" {{ $status == 'In Process' ? 'checked' : '' }}>
                                                        <label class="btn btn-outline-secondary rounded-pill px-3 py-1"
                                                            for="policeVerificationStatusInProcess">In Process</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
 
                                <div class="row g-3">
                                    <div class="col-12 col-xl-6">
                                        <div class="card border-0 bg-light h-100">
                                            <div class="card-body p-3">
                                                <div class="fw-bold text-uppercase small mb-3">Verification Details</div>
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">MSR Number <span class="text-danger police-mandatory-star d-none">*</span></label>
                                                        <input type="text" name="msr_letter_no" class="form-control police-verification-field"
                                                            id="policeVerificationMsrNumberInput"
                                                            value="{{ $pv->msr_letter_no ?? '' }}"
                                                            placeholder="Enter MSR number">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">MSR Date <span class="text-danger police-mandatory-star d-none">*</span></label>
                                                        <input type="date" name="msr_date" class="form-control police-verification-field"
                                                            id="policeVerificationMsrDateInput"
                                                            value="{{ isset($pv->msr_date) ? $pv->msr_date->format('Y-m-d') : '' }}"
                                                            placeholder="yyyy-mm-dd">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Verification Letter Number <span class="text-danger police-mandatory-star d-none">*</span></label>
                                                        <input type="text" name="verification_letter_no" class="form-control police-verification-field"
                                                            id="policeVerificationLetterNumberInput"
                                                            value="{{ $pv->verification_letter_no ?? '' }}"
                                                            placeholder="Enter verification letter number">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Verification Letter Date <span class="text-danger police-mandatory-star d-none">*</span></label>
                                                        <input type="date" name="verification_letter_date" class="form-control police-verification-field"
                                                            id="policeVerificationLetterDateInput"
                                                            value="{{ isset($pv->verification_letter_date) ? $pv->verification_letter_date->format('Y-m-d') : '' }}"
                                                            placeholder="yyyy-mm-dd">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
 
                                    <div class="col-12 col-xl-6">
                                        <div class="card border-0 bg-light h-100">
                                            <div class="card-body p-3">
                                                <div class="fw-bold text-uppercase small mb-3">Authority and Follow-up</div>
                                                <div class="row g-3">
                                                    <div class="col-12">
                                                        <label class="form-label">Addressee <span class="text-danger police-mandatory-star d-none">*</span></label>
                                                        <input type="text" name="addressee" class="form-control police-verification-field" id="policeVerificationAddresseeInput"
                                                            value="{{ $pv->addressee ?? '' }}"
                                                            placeholder="Enter addressee">
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label">Verifying Authority <span class="text-danger police-mandatory-star d-none">*</span></label>
                                                        <input type="text" name="verifying_authority" class="form-control police-verification-field"
                                                            id="policeVerificationVerifyingAuthorityInput"
                                                            value="{{ $pv->verifying_authority ?? '' }}"
                                                            placeholder="Enter verifying authority">
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label">Next Verification Date <span class="text-danger police-mandatory-star d-none">*</span></label>
                                                        <input type="date" name="next_verification_date" class="form-control police-verification-field"
                                                            id="policeVerificationNextVerificationDateInput"
                                                            value="{{ isset($pv->next_verification_date) ? $pv->next_verification_date->format('Y-m-d') : '' }}"
                                                            placeholder="yyyy-mm-dd">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
 
                                <div class="card border-0 bg-light">
                                    <div class="card-body p-3">
                                        <div class="fw-bold text-uppercase small mb-3">Remarks</div>
                                        <label class="form-label">Remarks <span class="text-danger police-mandatory-star d-none">*</span></label>
                                        <textarea name="police_remarks" class="form-control police-verification-field" id="policeVerificationRemarksInput" rows="3"
                                            placeholder="Enter remarks">{{ $pv->remarks ?? '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
