<!-- Departmental Quota Warnings -->
<div class="col-12">
    <div class="card rounded-5 border-0 overflow-hidden">
        <div class="card-header px-4 pt-4 pb-3 border-0 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 text-main">Departmental Quota Warnings</h5>
                <small class="text-muted">High Leave Concentration (Next {{ $quotaWarningDays ?? 14 }} Days)</small>
            </div>
            @if(count($quotaWarnings ?? []) > 0)
                <span class="badge bg-warning text-dark">{{ count($quotaWarnings) }}</span>
            @else
                <span class="badge bg-success">All Clear</span>
            @endif
        </div>

        <div class="card-body p-0">
            @forelse($quotaWarnings ?? [] as $warning)
                <div class="quota-warning-item {{ !$loop->last ? 'border-bottom' : '' }} p-3">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-exclamation-triangle-fill text-{{ $warning['progress_color'] }} me-2"></i>
                                <h6 class="mb-0 small fw-semibold">{{ $warning['department_name'] }}</h6>
                            </div>
                            <p class="mb-1 small text-muted">
                                {{ $warning['percent'] }}% of staff will be on leave {{ $warning['date_label'] }}
                            </p>
                            <div class="progress mb-1" style="height: 6px;">
                                <div class="progress-bar bg-{{ $warning['progress_color'] }}"
                                     role="progressbar"
                                     style="width: {{ $warning['percent'] }}%"
                                     aria-valuenow="{{ $warning['percent'] }}"
                                     aria-valuemin="0"
                                     aria-valuemax="100">
                                </div>
                            </div>
                            <small class="text-muted">
                                {{ $warning['on_leave_count'] }} out of {{ $warning['total_count'] }} employees
                            </small>
                        </div>
                        <button class="btn btn-sm btn-outline-{{ $warning['badge_color'] }} rounded-3 ms-2"
                                title="View Details"
                                onclick="window.location='{{ route('admin.leave.request.index', ['department' => $warning['department_id'], 'date' => $warning['date']]) }}'">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-check-circle-fill text-success fs-4 d-block mb-2"></i>
                    <small>No department reached the {{ $quotaWarningThreshold ?? 20 }}% leave threshold in the next {{ $quotaWarningDays ?? 14 }} days.</small>
                </div>
            @endforelse
        </div>

        <div class="card-footer bg-transparent border-top">
            <a href="{{ route('admin.leave.request.index') }}"
               class="btn btn-link text-decoration-none text-main p-0 small">
                View All Warnings <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</div>
