<!-- Top Row: 4 Key Metric Cards (KPIs) -->

<div class="col-8">
    <div class="card bg-main rounded-5 overflow-hidden">
        <div class="card-body p-0 d-flex justify-content-between">
            <div class="d-flex flex-column w-100 position-relative z-5 p-4">
                {{-- @include('admin.dashboard.counters') --}}
                <div class="row g-2 mb-4 position-relative z-5">
                    <div class="col-md-3">
                        <div class="card border border-white border-opacity-25 bg-transparent p-0 rounded-4 shadow-sm total-employees">
                            <div class="card-body p-2 rounded-2">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="text-white mb-2 text-uppercase small fw-medium"><i
                                                class="bi bi-people fs-6 me-1"></i> Total
                                            Employees</h6>
                                        <div class="fs-4 fw-semibold text-white">150</div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-1 small text-white">
                                    <i class="bi bi-arrow-up"></i>
                                    <span>+5% vs yesterday</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border border-white border-opacity-25 bg-transparent p-0 rounded-4 shadow-sm present-today">
                            <div class="card-body p-2 rounded-2">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="text-white mb-2 text-uppercase small fw-medium"> <i
                                                class="bi bi-check-circle fs-6 me-1"></i> Present Today</h6>
                                        <div class="fs-4 fw-semibold text-white">125</div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-1 small text-white">
                                    <i class="bi bi-arrow-up"></i>
                                    <span>+2% vs yesterday</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border border-white border-opacity-25 bg-transparent p-0 rounded-4 shadow-sm absent-leave">
                            <div class="card-body p-2 rounded-2">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="text-white mb-2 text-uppercase small fw-medium"> <i
                                                class="bi bi-x-circle fs-6 me-1"></i> Absent / On Leave</h6>
                                        <div class="fs-4 fw-semibold text-danger">12</div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-1 small text-danger">
                                    <i class="bi bi-arrow-down"></i>
                                    <span>-3% vs yesterday</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border border-white border-opacity-25 bg-transparent p-0 rounded-4 shadow-sm late-arrivals">
                            <div class="card-body p-2 rounded-2">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="text-white mb-2 text-uppercase small fw-medium"> <i
                                                class="bi bi-clock-history fs-6 me-1"></i> Late Arrivals</h6>
                                        <div class="fs-4 fw-semibold text-warning">5</div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-1 small text-warning">
                                    <i class="bi bi-arrow-down"></i>
                                    <span>-2% vs yesterday</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div>
                    <h6 class="text-primary fw-light small">The total attendence percentage of the entire company</h6>
                    <div class="workforce-percentage text-white" id="workforcePercentage">0%</div>
                    <div class="workforce-label text-white fw-lighter mt-0">Current Workforce Strength.</div>
                    <div class="workforce-subtext fw-lighter text-white opacity-50" id="workforceSubtext">0 Active / 0
                        Total.</div>
                    <div class="workforce-progress bg-danger" style="max-width: 60%">
                        <div class="workforce-progress-bar" id="workforceProgressBar" style="width: 0%"></div>
                    </div>
                </div>
            </div>
            <img src="{{ asset('images/building.png') }}" alt="Enaara Logo" width="40%"
                class="position-absolute end-0 bottom-0 z-1">
        </div>
    </div>
</div>
