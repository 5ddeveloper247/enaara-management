<!-- Leave Requests Counters -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 rounded-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <small class="text-muted d-block mb-1">Pending Requests</small>
                        <h3 class="mb-0 fw-bold" id="pendingRequestsCount">{{ $pendingCount }}</h3>
                    </div>
                    <div>
                        <i class="bi bi-clock-history text-warning fs-4"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <small class="text-muted">
                        <i class="bi bi-arrow-up text-warning"></i>
                        <span class="text-warning">+3</span> vs last week
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
                        <small class="text-muted d-block mb-1">Approved Today</small>
                        <h3 class="mb-0 fw-bold" id="approvedTodayCount">{{ $approvedTodayCount }}</h3>
                    </div>
                    <div>
                        <i class="bi bi-check-circle text-success fs-4"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <small class="text-muted">
                        <i class="bi bi-arrow-up text-success"></i>
                        <span class="text-success">+2</span> vs yesterday
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
                        <small class="text-muted d-block mb-1">Away Today</small>
                        <h3 class="mb-0 fw-bold" id="awayTodayCount">{{ $awayTodayCount }}</h3>
                    </div>
                    <div>
                        <i class="bi bi-person-x text-info fs-4"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <small class="text-muted">
                        <i class="bi bi-arrow-down text-info"></i>
                        <span class="text-info">-2</span> vs yesterday
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
                        <small class="text-muted d-block mb-1">Overdue (>48hrs)</small>
                        <h3 class="mb-0 fw-bold text-danger" id="overdueRequestsCount">{{ $overdueCount }}</h3>
                    </div>
                    <div>
                        <i class="bi bi-exclamation-triangle-fill text-danger fs-4"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <small class="text-muted">
                        <i class="bi bi-arrow-up text-danger"></i>
                        <span class="text-danger">+1</span> vs last week
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>



