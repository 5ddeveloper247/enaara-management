<!-- Upcoming Holidays -->
<div class="col-12">
    <div class="card rounded-5 border-0 overflow-hidden">
        <div class="card-header px-4 pt-4 pb-3 border-0 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 text-main">Upcoming Holidays</h5>
                <div class="d-flex gap-1 mt-1">
                    <button class="btn btn-xs btn-warning rounded-2 py-0 px-2 small holiday-period-btn active" data-period="7">7 Days</button>
                    <button class="btn btn-xs btn-outline-secondary rounded-2 py-0 px-2 small holiday-period-btn" data-period="14">14 Days</button>
                </div>
            </div>
            <span class="badge bg-info" id="holidaysBadge">0</span>
        </div>
        <div class="card-body p-0" id="holidaysList">
            <div class="text-center py-4 text-muted" id="holidaysLoader">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                Loading...
            </div>
        </div>
        <div id="holidaysEmpty" class="text-center py-4 text-muted d-none">
            <i class="bi bi-calendar-x fs-4 d-block mb-1"></i>
            <small>No upcoming holidays in this period.</small>
        </div>
        <div class="card-footer bg-transparent border-top">
            <a href="{{ url('/admin/leave-calendar') }}" class="btn btn-link text-decoration-none text-main p-0">
                View Full Calendar <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</div>
