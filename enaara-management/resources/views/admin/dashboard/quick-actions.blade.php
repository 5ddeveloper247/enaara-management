<!-- Quick Actions Panel -->
<div class="col-12">
    <div class="card rounded-5 border-0 overflow-hidden">
        <div class="card-header px-4 pt-4 pb-3 border-0">
            <h5 class="mb-0 text-main">Quick Actions</h5>
            <small class="text-muted">Fast access to common tasks</small>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-6">
                    <a href="{{ url('/admin/regularization') }}" class="btn btn-outline-primary w-100 rounded-3 d-flex flex-column align-items-center py-3">
                        <i class="bi bi-clock-history fs-4 mb-2"></i>
                        <span class="small">Regularization</span>
                    </a>
                </div>
                <div class="col-6">
                    <a href="{{ url('/admin/leave-requests') }}" class="btn btn-outline-success w-100 rounded-3 d-flex flex-column align-items-center py-3">
                        <i class="bi bi-envelope-paper fs-4 mb-2"></i>
                        <span class="small">Leave Requests</span>
                    </a>
                </div>
                <div class="col-6">
                    <a href="{{ url('/admin/geofencing') }}" class="btn btn-outline-info w-100 rounded-3 d-flex flex-column align-items-center py-3">
                        <i class="bi bi-geo-alt-fill fs-4 mb-2"></i>
                        <span class="small">Geofencing</span>
                    </a>
                </div>
                <div class="col-6">
                    <a href="{{ url('/admin/organization') }}" class="btn btn-outline-warning w-100 rounded-3 d-flex flex-column align-items-center py-3">
                        <i class="bi bi-building fs-4 mb-2"></i>
                        <span class="small">Organizations</span>
                    </a>
                </div>
                <div class="col-6">
                    <a href="{{ url('/admin/balance-tracker') }}" class="btn btn-outline-danger w-100 rounded-3 d-flex flex-column align-items-center py-3">
                        <i class="bi bi-graph-up fs-4 mb-2"></i>
                        <span class="small">Balance Tracker</span>
                    </a>
                </div>
                <div class="col-6">
                    <a href="{{ url('/admin/shift-planner') }}" class="btn btn-outline-secondary w-100 rounded-3 d-flex flex-column align-items-center py-3">
                        <i class="bi bi-calendar-week fs-4 mb-2"></i>
                        <span class="small">Shift Planner</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

