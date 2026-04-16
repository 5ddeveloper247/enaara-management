                        <div class="wizard-pane" id="stepPane4">
                            <div id="step-4">
                                <section class="d-grid gap-3">
                                    <div class="card bg-light border-0 shadow-sm">
                                        <div class="card-body p-3">
                                            <div class="fw-bold text-dark mb-3">
                                                <span>Armed Forces Details</span>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Service No</label>
                                                    <input type="text" name="service_no" class="form-control" id="armedDetailsServiceNoInput"
                                                        value="{{ $employee->service_no ?? '' }}"
                                                        placeholder="Enter service number">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Rank</label>
                                                    <input type="text" name="rank" class="form-control" id="armedDetailsRankInput"
                                                        value="{{ $employee->rank ?? '' }}"
                                                        placeholder="Enter rank">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-12 col-xl-6">
                                            <div class="card border-0 bg-light h-100">
                                                <div class="card-body p-3">
                                                    <div class="fw-bold text-uppercase small mb-3">Service and Retirement</div>
                                                    <div class="row g-3">
                                                        <div class="col-12">
                                                            <label class="form-label">Medical Category</label>
                                                            <input type="text" name="medical_category" class="form-control"
                                                                id="armedDetailsMedicalCategoryInput"
                                                                value="{{ $employee->medical_category ?? '' }}"
                                                                placeholder="Enter medical category">
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label">Date of Commissioning / Enrollment</label>
                                                            <input type="date" name="commissioning_enrollment_date" class="form-control"
                                                                id="armedDetailsCommissioningEnrollmentDateInput"
                                                                value="{{ $employee->commissioning_enrollment_date ?? '' }}"
                                                                placeholder="yyyy-mm-dd">
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label">Date of Retirement</label>
                                                            <input type="date" name="retirement_date" class="form-control" id="armedDetailsRetirementDateInput"
                                                                value="{{ $employee->retirement_date ?? '' }}"
                                                                placeholder="yyyy-mm-dd">
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label">Reason of Retirement</label>
                                                            <input type="text" name="retirement_reason" class="form-control" id="armedDetailsRetirementReasonInput"
                                                                value="{{ $employee->retirement_reason ?? '' }}"
                                                                placeholder="Enter reason of retirement">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-xl-6">
                                            <div class="card border-0 bg-light h-100">
                                                <div class="card-body p-3">
                                                    <div class="fw-bold text-uppercase small mb-3">Unit and Officer Details</div>
                                                    <div class="row g-3">
                                                        <div class="col-12">
                                                            <label class="form-label">Corps / Regiment / Squadron</label>
                                                            <input type="text" name="corps_regiment_squadron" class="form-control"
                                                                id="armedDetailsCorpsRegimentSquadronInput"
                                                                value="{{ $employee->corps_regiment_squadron ?? '' }}"
                                                                placeholder="Enter corps, regiment, or squadron">
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label">Ex Army Unit</label>
                                                            <input type="text" name="ex_army_unit" class="form-control" id="armedDetailsExArmyUnitInput"
                                                                value="{{ $employee->ex_army_unit ?? '' }}"
                                                                placeholder="Enter ex army unit">
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label">Trade</label>
                                                            <input type="text" name="trade" class="form-control" id="armedDetailsTradeInput"
                                                                value="{{ $employee->trade ?? '' }}"
                                                                placeholder="Enter trade">
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label">PMA L/C & OTS (For Army Officers)</label>
                                                            <input type="text" name="pma_lc_ots" class="form-control" id="armedDetailsPmaLcOtsInput"
                                                                value="{{ $employee->pma_lc_ots ?? '' }}"
                                                                placeholder="Enter PMA L/C & OTS details">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            </div>
                        </div>
