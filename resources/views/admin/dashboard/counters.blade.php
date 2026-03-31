@php
    $totalDeltaUp   = str_starts_with($counterStats['totalDelta'], '+');
    $presentDeltaUp = str_starts_with($counterStats['presentDelta'], '+');
    $absentDeltaUp  = str_starts_with($counterStats['absentDelta'], '+');
    $lateDeltaUp    = str_starts_with($counterStats['lateDelta'], '+');
@endphp

<div class="col-8">
    <div class="card bg-main rounded-5 overflow-hidden">
        <div class="card-body p-0 d-flex justify-content-between">
            <div class="d-flex flex-column w-100 position-relative z-5 p-4">
                <div class="row g-2 mb-4 position-relative z-5">

                    {{-- Total Employees --}}
                    <div class="col-md-3">
                        <div class="card border border-white border-opacity-25 bg-transparent p-0 rounded-4 shadow-sm total-employees">
                            <div class="card-body p-2 rounded-2">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="text-white mb-2 text-uppercase small fw-medium">
                                            <i class="bi bi-people fs-6 me-1"></i> Total Employees
                                        </h6>
                                        <div class="fs-4 fw-semibold text-white">{{ $counterStats['totalEmployees'] }}</div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-1 small text-white">
                                    <i class="bi bi-arrow-{{ $totalDeltaUp ? 'up' : 'down' }}"></i>
                                    <span>{{ $counterStats['totalDelta'] }} vs yesterday</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Present Today --}}
                    <div class="col-md-3">
                        <div class="card border border-white border-opacity-25 bg-transparent p-0 rounded-4 shadow-sm present-today">
                            <div class="card-body p-2 rounded-2">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="text-white mb-2 text-uppercase small fw-medium">
                                            <i class="bi bi-check-circle fs-6 me-1"></i> Present Today
                                        </h6>
                                        <div class="fs-4 fw-semibold text-white">{{ $counterStats['presentToday'] }}</div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-1 small text-white">
                                    <i class="bi bi-arrow-{{ $presentDeltaUp ? 'up' : 'down' }}"></i>
                                    <span>{{ $counterStats['presentDelta'] }} vs yesterday</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Absent / On Leave --}}
                    <div class="col-md-3">
                        <div class="card border border-white border-opacity-25 bg-transparent p-0 rounded-4 shadow-sm absent-leave">
                            <div class="card-body p-2 rounded-2">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="text-white mb-2 text-uppercase small fw-medium">
                                            <i class="bi bi-x-circle fs-6 me-1"></i> Absent / On Leave
                                        </h6>
                                        <div class="fs-4 fw-semibold text-danger">{{ $counterStats['absentOnLeave'] }}</div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-1 small {{ $absentDeltaUp ? 'text-danger' : 'text-danger' }}">
                                    <i class="bi bi-arrow-{{ $absentDeltaUp ? 'up' : 'down' }}"></i>
                                    <span>{{ $counterStats['absentDelta'] }} vs yesterday</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Late Arrivals --}}
                    <div class="col-md-3">
                        <div class="card border border-white border-opacity-25 bg-transparent p-0 rounded-4 shadow-sm late-arrivals">
                            <div class="card-body p-2 rounded-2">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="text-white mb-2 text-uppercase small fw-medium">
                                            <i class="bi bi-clock-history fs-6 me-1"></i> Late Arrivals
                                        </h6>
                                        <div class="fs-4 fw-semibold text-warning">{{ $counterStats['lateArrivals'] }}</div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-1 small text-warning">
                                    <i class="bi bi-arrow-{{ $lateDeltaUp ? 'up' : 'down' }}"></i>
                                    <span>{{ $counterStats['lateDelta'] }} vs yesterday</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div>
                    <h6 class="text-primary fw-light small">The total attendence percentage of the entire company</h6>
                    <div class="workforce-percentage text-white" id="workforcePercentage">{{ $counterStats['workforcePercent'] }}%</div>
                    <div class="workforce-label text-white fw-lighter mt-0">Current Workforce Strength.</div>
                    <div class="workforce-subtext fw-lighter text-white opacity-50" id="workforceSubtext">
                        {{ $counterStats['activeEmployees'] }} Active / {{ $counterStats['totalEmployees'] }} Total.
                    </div>
                    <div class="workforce-progress bg-danger" style="max-width: 60%">
                        <div class="workforce-progress-bar" id="workforceProgressBar" style="width: {{ $counterStats['workforcePercent'] }}%"></div>
                    </div>
                </div>
            </div>
            <img src="{{ asset('images/building.png') }}" alt="Enaara Logo" width="40%"
                class="position-absolute end-0 bottom-0 z-1">
        </div>
    </div>
</div>
