                        <div class="wizard-pane" id="stepPane3">
                            <div class="d-grid gap-3">
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
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <input type="radio" class="btn-check" name="policeVerificationStatus"
                                                            id="policeVerificationStatusCleared" value="Cleared" required>
                                                        <label class="btn btn-outline-secondary rounded-pill px-3 py-1"
                                                            for="policeVerificationStatusCleared">Cleared</label>

                                                        <input type="radio" class="btn-check" name="policeVerificationStatus"
                                                            id="policeVerificationStatusNotCleared" value="Not Cleared">
                                                        <label class="btn btn-outline-secondary rounded-pill px-3 py-1"
                                                            for="policeVerificationStatusNotCleared">Not Cleared</label>

                                                        <input type="radio" class="btn-check" name="policeVerificationStatus"
                                                            id="policeVerificationStatusInProcess" value="In Process">
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
                                                        <label class="form-label">MSR Number</label>
                                                        <input type="text" class="form-control"
                                                            id="policeVerificationMsrNumberInput"
                                                            placeholder="Enter MSR number">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">MSR Date</label>
                                                        <input type="date" class="form-control"
                                                            id="policeVerificationMsrDateInput"
                                                            placeholder="yyyy-mm-dd">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Verification Letter Number</label>
                                                        <input type="text" class="form-control"
                                                            id="policeVerificationLetterNumberInput"
                                                            placeholder="Enter verification letter number">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Verification Letter Date</label>
                                                        <input type="date" class="form-control"
                                                            id="policeVerificationLetterDateInput"
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
                                                        <label class="form-label">Addressee</label>
                                                        <input type="text" class="form-control" id="policeVerificationAddresseeInput"
                                                            placeholder="Enter addressee">
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label">Verifying Authority</label>
                                                        <input type="text" class="form-control"
                                                            id="policeVerificationVerifyingAuthorityInput"
                                                            placeholder="Enter verifying authority">
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label">Next Verification Date</label>
                                                        <input type="date" class="form-control"
                                                            id="policeVerificationNextVerificationDateInput"
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
                                        <label class="form-label">Remarks</label>
                                        <textarea class="form-control" id="policeVerificationRemarksInput" rows="3"
                                            placeholder="Enter remarks"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
