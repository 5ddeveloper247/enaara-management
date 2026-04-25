<!-- Geofencing Counters -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 rounded-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <small class="text-muted d-block mb-1">Total Fences</small>
                        <h3 class="mb-0 fw-bold" id="totalFencesCount">{{ $totalFences ?? 0 }}</h3>
                    </div>
                    <div>
                        <i class="bi bi-geo-alt-fill text-main fs-4"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <small class="text-muted">
                        <i class="bi bi-arrow-up text-success"></i>
                        <span class="text-success">0</span> vs last month
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 rounded-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <small class="text-muted d-block mb-1">Active Sites</small>
                        <h3 class="mb-0 fw-bold" id="activeSitesCount">{{ $activeSitesCount ?? 0 }}</h3>
                    </div>
                    <div>
                        <i class="bi bi-check-circle text-success fs-4"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <small class="text-muted">
                        <i class="bi bi-arrow-up text-success"></i>
                        <span class="text-success">0</span> vs last month
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 rounded-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <small class="text-muted d-block mb-1">Employees Inside</small>
                        <h3 class="mb-0 fw-bold text-success" id="employeesInsideCount">0</h3>
                    </div>
                    <div>
                        <i class="bi bi-people-fill text-success fs-4"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <small class="text-muted">
                        <i class="bi bi-arrow-up text-success"></i>
                        <span class="text-success">0</span> vs yesterday
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 rounded-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <small class="text-muted d-block mb-1">Location Violations</small>
                        <h3 class="mb-0 fw-bold text-danger" id="locationViolationsCount">0</h3>
                    </div>
                    <div>
                        <i class="bi bi-exclamation-triangle-fill text-danger fs-4"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <small class="text-muted">
                        <i class="bi bi-arrow-down text-danger"></i>
                        <span class="text-danger">0</span> vs yesterday
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

