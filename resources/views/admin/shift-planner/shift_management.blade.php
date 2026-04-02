<div class="row">
    <div class="col-lg-9">
        <div class="row g-4" id="shiftsGrid">
            @forelse($shifts as $shift)
                @php
                    $startTime = \Carbon\Carbon::parse($shift->start_time)->format('H:i');
                    $endTime = \Carbon\Carbon::parse($shift->end_time)->format('H:i');

                    $clockInStart = \Carbon\Carbon::parse($shift->start_time)
                        ->subMinutes((int) $shift->clock_in_window_minutes)
                        ->format('H:i');

                    $clockOutEnd = \Carbon\Carbon::parse($shift->end_time)
                        ->addMinutes((int) $shift->clock_out_window_minutes)
                        ->format('H:i');
                @endphp

                <div class="col-md-6 col-lg-4">
                    <div class="card shift-card border-1 rounded-4 h-100"
                         data-bs-toggle="offcanvas"
                         data-bs-target="#shiftDetailCanvas"
                         data-shift-id="{{ $shift->id }}"
                         data-shift-name="{{ $shift->name }}"
                         data-shift-start="{{ $startTime }}"
                         data-shift-end="{{ $endTime }}"
                         data-clock-in-window="{{ $clockInStart }} - {{ $startTime }}"
                         data-clock-out-window="{{ $endTime }} - {{ $clockOutEnd }}"
                         data-grace-period="{{ $shift->grace_period_minutes }}"
                         data-break-time="{{ $shift->break_time_minutes }}"
                         data-overtime-allowed="{{ $shift->overtime_allowed ? 'true' : 'false' }}"
                         data-overtime-trigger="{{ $shift->overtime_trigger_hours ?? 0 }}"
                         data-is-active="{{ $shift->is_active ? 'active' : 'inactive' }}">

                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h6 class="mb-1 fw-semibold">{{ $shift->name }}</h6>
                                    <small class="text-muted">{{ $startTime }} - {{ $endTime }}</small>
                                </div>

                                @if($shift->is_active)
                                    <span class="badge bg-success" style="font-size: 10px !important; padding: 4px 6px !important;">
                                        Active
                                    </span>
                                @else
                                    <span class="badge bg-secondary" style="font-size: 10px !important; padding: 4px 6px !important;">
                                        Inactive
                                    </span>
                                @endif
                            </div>

                            <div class="mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-clock me-2 text-main small"></i>
                                    <small class="fw-semibold small">
                                        Clock-in Window: {{ $clockInStart }} - {{ $startTime }}
                                    </small>
                                </div>

                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-hourglass-split me-2 text-main small"></i>
                                    <small class="fw-semibold small">
                                        Grace Period: {{ $shift->grace_period_minutes }} mins
                                    </small>
                                </div>

                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-cup-straw me-2 text-main small"></i>
                                    <small class="fw-semibold small">
                                        Break Time: {{ $shift->break_time_minutes }} mins
                                    </small>
                                </div>

                                <div class="d-flex align-items-center">
                                    @if($shift->overtime_allowed)
                                        <i class="bi bi-arrow-repeat me-2 text-main small"></i>
                                        <small class="fw-semibold small">
                                            OT Allowed: After {{ $shift->overtime_trigger_hours }}h
                                        </small>
                                    @else
                                        <i class="bi bi-x-circle me-2 text-muted small"></i>
                                        <small class="fw-semibold small text-muted">
                                            OT Not Allowed
                                        </small>
                                    @endif
                                </div>
                            </div>

                            <div class="d-flex gap-2 pt-3 border-top">
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary flex-fill edit-shift-btn"
                                        data-bs-toggle="offcanvas"
                                        data-bs-target="#addShiftCanvas"
                                        data-mode="edit"
                                        data-shift-id="{{ $shift->id }}"
                                        onclick="event.stopPropagation();">
                                    <i class="bi bi-pencil me-1"></i>Edit
                                </button>

                                <button type="button"
                                        class="btn btn-sm btn-primary flex-fill view-shift-btn"
                                        data-bs-toggle="offcanvas"
                                        data-bs-target="#shiftDetailCanvas"
                                        data-shift-id="{{ $shift->id }}"
                                        onclick="event.stopPropagation();">
                                    <i class="bi bi-eye me-1"></i>View
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card border-0 rounded-4">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-calendar-x fs-1 text-muted mb-3 d-block"></i>
                            <h6 class="mb-1">No shifts found</h6>
                            <small class="text-muted">Create your first shift to show data here.</small>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <div class="col-lg-3">
        <div class="card border-0 rounded-4">
            <div class="card-body p-4">
                <h6 class="fw-semibold mb-3">
                    <i class="bi bi-funnel me-2"></i>Filters
                </h6>

                <div class="mb-4">
                    <label class="form-label small fw-semibold text-muted mb-2">Status</label>
                    <div class="bg-transparent">
                        <label class="list-group-item list-group-item-action border-0 px-0 py-1 cursor-pointer">
                            <input class="form-check-input me-2" type="checkbox" value="all" id="filterShiftStatusAll" checked>
                            All Status
                        </label>
                        <label class="list-group-item list-group-item-action border-0 px-0 py-1 cursor-pointer">
                            <input class="form-check-input me-2" type="checkbox" value="active" id="filterShiftStatusActive">
                            Active
                        </label>
                        <label class="list-group-item list-group-item-action border-0 px-0 py-1 cursor-pointer">
                            <input class="form-check-input me-2" type="checkbox" value="inactive" id="filterShiftStatusInactive">
                            Inactive
                        </label>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold text-muted mb-2">Overtime</label>
                    <div class="bg-transparent">
                        <label class="list-group-item list-group-item-action border-0 px-0 py-1 cursor-pointer">
                            <input class="form-check-input me-2" type="checkbox" value="all" id="filterOTAll" checked>
                            All Shifts
                        </label>
                        <label class="list-group-item list-group-item-action border-0 px-0 py-1 cursor-pointer">
                            <input class="form-check-input me-2" type="checkbox" value="allowed" id="filterOTAllowed">
                            OT Allowed
                        </label>
                        <label class="list-group-item list-group-item-action border-0 px-0 py-1 cursor-pointer">
                            <input class="form-check-input me-2" type="checkbox" value="not-allowed" id="filterOTNotAllowed">
                            OT Not Allowed
                        </label>
                    </div>
                </div>

                <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="clearShiftFiltersBtn">
                    <i class="bi bi-x-circle me-1"></i>Clear Filters
                </button>
            </div>
        </div>
    </div>
</div>