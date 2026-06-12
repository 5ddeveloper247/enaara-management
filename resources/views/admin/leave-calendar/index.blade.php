@extends('layouts.app')

@section('title', 'Leave Calendar - Admin Panel')

@section('page-title', 'Leave Calendar')

@push('styles')
<!-- FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
<!-- Leave Calendar Module CSS -->
<link href="{{ asset('css/leave-calendar.css') }}" rel="stylesheet">

<style>
    .btn {
        font-size: 12px !important;
    }

    .card {
        border-radius: 1rem;
    }

    /* Calendar text sizes */
    #leaveCalendar,
    #leaveCalendar * {
        font-size: 12px !important;
    }

    .fc-toolbar-title {
        font-size: 12px !important;
    }

    .fc-button-group button {
        font-size: 12px !important;
    }

    .fc-col-header-cell {
        background-color: var(--main-color) !important;
    }

    .fc-col-header-cell a {
        color: white !important;
        font-size: 13px !important;
    }

    /* Sidebar Tabs Styling */
    .sidebar-tabs .nav-pills {
        background-color: rgba(1, 36, 69, 0.05);
        padding: 4px;
        border-radius: 12px;
        margin-bottom: 20px;
    }

    .sidebar-tabs .nav-link {
        border-radius: 10px;
        color: var(--main-color);
        font-size: 11px;
        font-weight: 700;
        padding: 8px 4px;
        /* Reduced side padding to prevent wrap */
        text-align: center;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        flex-grow: 1;
        /* Use flex-grow instead of width: 50% for better sizing */
        white-space: nowrap;
        /* Prevent text wrap */
        letter-spacing: 0.2px;
    }

    .sidebar-tabs .nav-link.active {
        background-color: var(--main-color) !important;
        color: white !important;
        box-shadow: 0 4px 15px rgba(1, 36, 69, 0.25);
        transform: scale(1.02);
    }

    .holiday-list-item {
        transition: transform 0.2s ease;
        border-left: 3px solid transparent;
    }

    .holiday-list-item:hover {
        background-color: rgba(1, 36, 69, 0.02);
        transform: translateX(4px);
        border-left: 3px solid var(--main-color);
    }

    .year-filter-select {
        font-size: 11px;
        padding: 4px 8px;
        border-radius: 6px;
        border: 1px solid #eee;
        color: var(--main-color);
        font-weight: 500;
    }

    .year-filter-select:focus {
        outline: none;
        border-color: var(--main-color);
    }

    .holiday-single-day-event {
        border-radius: 6px !important;
        padding: 2px 8px !important;
        margin: 1px 0 !important;
        min-height: 22px;
        display: block;
    }

    .holiday-single-day-event .fc-event-main {
        padding-left: 2px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .holiday-single-day-event .fc-event-title {
        display: inline-flex;
        align-items: center;
        min-width: 0;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .holiday-single-day-event .holiday-dot {
        width: 7px;
        height: 7px;
        min-width: 7px;
        border-radius: 50%;
        margin-right: 6px;
        background: #0dcaf0;
        position: static !important;
        flex-shrink: 0;
    }

    .fc-daygrid-event {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-left: 2px !important;
        margin-right: 2px !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h5 class="mb-0">Leave Calendar</h5>
            <small class="text-muted">View and manage company holidays and leave schedules</small>
        </div>
        <div class="col-md-6 text-end">
            <button type="button" class="btn btn-outline-secondary me-2" id="todayBtn">
                <i class="bi bi-calendar-day me-1"></i>Today
            </button>
            <button type="button" class="btn btn-primary bg-main border-0" data-bs-toggle="offcanvas"
                data-bs-target="#addHolidayCanvas">
                <i class="bi bi-plus-circle me-1"></i>Add Holiday
            </button>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Calendar -->
        <div class="col-md-9">
            <div class="card border-0 rounded-4">
                <div class="card-header bg-transparent border-0 p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-semibold">Calendar View</h6>
                        @include('admin.leave-calendar.legend')
                    </div>
                </div>
                <div class="card-body">
                    <div id="leaveCalendar"></div>
                </div>
            </div>
        </div>

        <!-- Sidebar Tabs Section -->
        <div class="col-md-3">
            <div class="card border-0 rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="sidebar-tabs">
                        <ul class="nav nav-pills" id="sidebarHolidayTabs" role="tablist">
                            <li class="nav-item d-flex flex-fill" role="presentation">
                                <button class="nav-link active w-100" id="upcoming-tab" data-bs-toggle="pill"
                                    data-bs-target="#upcoming-content" type="button" role="tab">
                                    Upcoming Holidays
                                </button>
                            </li>
                            <li class="nav-item d-flex flex-fill" role="presentation">
                                <button class="nav-link w-100" id="all-tab" data-bs-toggle="pill"
                                    data-bs-target="#all-content" type="button" role="tab">
                                    All Holidays
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="tab-content" id="sidebarHolidayTabsContent">
                        <!-- Upcoming Holidays Tab -->
                        <div class="tab-pane fade show active" id="upcoming-content" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0 fw-bold" style="font-size: 14px; color: var(--main-color)">Upcoming Holidays</h6>
                            </div>
                            <div id="upcomingHolidaysList">
                                <!-- Populated by JS -->
                            </div>
                        </div>

                        <!-- All Holidays History Tab -->
                        <div class="tab-pane fade" id="all-content" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0 fw-bold" style="font-size: 14px; color: var(--main-color)">All Holidays</h6>
                                <select id="holidayYearFilter" class="year-filter-select">
                                    @php $currentYear = date('Y'); @endphp
                                    @for($y = $currentYear; $y >= $currentYear - 2; $y--)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div id="allHolidaysList">
                                <!-- Populated by JS -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Event Detail Canvas -->
@include('admin.leave-calendar.event_detail_canvas')
<!-- Add Holiday Canvas -->
@include('admin.leave-calendar.add_holiday_canvas')
@endsection

@push('scripts')
<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script>
    let calendar;
    let calendarEl;

    const publicHolidays = @json($publicHolidays);
    const deptLeaves = @json($deptLeaves);
    const blackoutDates = @json($blackoutDates);

    document.addEventListener('DOMContentLoaded', function() {
        initializeCalendar();
        populateSidebarStats();

        const yearFilter = document.getElementById('holidayYearFilter');
        if (yearFilter) {
            yearFilter.addEventListener('change', function() {
                populateHistoryByYear(this.value);
            });
        }

        const addHolidayCanvasEl = document.getElementById('addHolidayCanvas');
        if (addHolidayCanvasEl) {
            addHolidayCanvasEl.addEventListener('show.bs.offcanvas', function(event) {
                if (event.relatedTarget) {
                    const form = document.getElementById('addHolidayForm');
                    if (form) {
                        form.reset();
                        form.dataset.mode = 'add';
                        form.action = "{{ route('admin.leave-calendar.store') }}";
                    }

                    const label = document.getElementById('addHolidayCanvasLabel');
                    if (label) {
                        label.textContent = 'Add Public Holiday';
                    }

                    const deleteBtn = document.getElementById('deleteHolidayBtn');
                    if (deleteBtn) deleteBtn.classList.add('d-none');

                    const orgSelect = document.getElementById('holidayOrganizations');
                    if (orgSelect) {
                        Array.from(orgSelect.options).forEach(opt => opt.selected = false);
                    }

                    const orgSection = document.getElementById('organizationSelectSection');
                    if (orgSection) {
                        orgSection.style.display = 'none';
                    }
                }
            });
        }
    });

    function formatDateOnly(dateStr) {
        if (!dateStr) return '';
        return String(dateStr).split('T')[0];
    }

    function getDatesBetween(startDate, endDate) {
        const dates = [];

        const safeStart = formatDateOnly(startDate);
        const safeEnd = formatDateOnly(endDate || startDate);

        if (!safeStart) return dates;

        const current = new Date(safeStart + 'T00:00:00');
        const end = new Date(safeEnd + 'T00:00:00');

        if (isNaN(current.getTime()) || isNaN(end.getTime())) {
            console.error('Invalid holiday dates:', {
                startDate,
                endDate,
                safeStart,
                safeEnd
            });
            return dates;
        }

        while (current <= end) {
            const yyyy = current.getFullYear();
            const mm = String(current.getMonth() + 1).padStart(2, '0');
            const dd = String(current.getDate()).padStart(2, '0');
            dates.push(`${yyyy}-${mm}-${dd}`);
            current.setDate(current.getDate() + 1);
        }

        return dates;
    }

    function holidayOccursOnDate(holiday, dateStr) {
        const target = new Date(dateStr + 'T00:00:00');
        if (isNaN(target.getTime())) {
            return false;
        }

        if (holiday.is_recurring) {
            const startParts = formatDateOnly(holiday.start_date).split('-');
            const endParts = formatDateOnly(holiday.end_date || holiday.start_date).split('-');
            const year = target.getFullYear();

            const occurrenceStart = new Date(
                year,
                parseInt(startParts[1], 10) - 1,
                parseInt(startParts[2], 10)
            );
            const occurrenceEnd = new Date(
                year,
                parseInt(endParts[1], 10) - 1,
                parseInt(endParts[2], 10)
            );

            if (occurrenceEnd < occurrenceStart) {
                return target >= occurrenceStart || target <= occurrenceEnd;
            }

            return target >= occurrenceStart && target <= occurrenceEnd;
        }

        return getDatesBetween(holiday.start_date, holiday.end_date).includes(dateStr);
    }

    function eachDateInRange(rangeStart, rangeEnd, callback) {
        const current = new Date(rangeStart);
        current.setHours(0, 0, 0, 0);
        const end = new Date(rangeEnd);
        end.setHours(0, 0, 0, 0);

        while (current < end) {
            const yyyy = current.getFullYear();
            const mm = String(current.getMonth() + 1).padStart(2, '0');
            const dd = String(current.getDate()).padStart(2, '0');
            callback(`${yyyy}-${mm}-${dd}`);
            current.setDate(current.getDate() + 1);
        }
    }

    function createHolidayEvent(holiday, singleDate) {
        return {
            id: `holiday_${holiday.id}_${singleDate}`,
            title: holiday.name || 'Holiday',
            start: singleDate,
            allDay: true,
            display: 'block',
            classNames: ['holiday-event', 'holiday-single-day-event'],
            extendedProps: {
                id: holiday.id,
                type: 'holiday',
                clicked_date: singleDate,
                original_start_date: formatDateOnly(holiday.start_date),
                original_end_date: formatDateOnly(holiday.end_date || holiday.start_date),
                is_recurring: !!holiday.is_recurring,
                organization: holiday.organization_scope === 'all'
                    ? 'All Organizations'
                    : (holiday.organizations && holiday.organizations.length
                        ? holiday.organizations.map(o => o.name).join(', ')
                        : 'Specific Organization'),
                department: holiday.department_scope === 'all'
                    ? 'All Departments'
                    : (holiday.departments && holiday.departments.length
                        ? holiday.departments.map(d => d.name).join(', ')
                        : ''),
                sbu: holiday.sbu_scope === 'all'
                    ? 'All SBUs'
                    : (holiday.sbus && holiday.sbus.length
                        ? holiday.sbus.map(s => s.name).join(', ')
                        : ''),
                reason: holiday.reason || ''
            }
        };
    }

    function createBlackoutEvent(holiday, singleDate) {
        return {
            id: `blackout_${holiday.id}_${singleDate}`,
            title: holiday.reason || holiday.name || 'Blackout Date',
            start: singleDate,
            allDay: true,
            classNames: ['blackout-date'],
            extendedProps: {
                type: 'blackout',
                reason: holiday.reason || holiday.name || 'Blackout Date',
                clicked_date: singleDate
            }
        };
    }

    function buildCalendarEvents(rangeStart, rangeEnd) {
        const events = [];
        const seen = new Set();

        eachDateInRange(rangeStart, rangeEnd, function(dateStr) {
            publicHolidays.forEach(function(holiday) {
                if (!holidayOccursOnDate(holiday, dateStr)) {
                    return;
                }

                const eventKey = `${holiday.is_blackout ? 'blackout' : 'holiday'}_${holiday.id}_${dateStr}`;
                if (seen.has(eventKey)) {
                    return;
                }
                seen.add(eventKey);

                events.push(
                    holiday.is_blackout
                        ? createBlackoutEvent(holiday, dateStr)
                        : createHolidayEvent(holiday, dateStr)
                );
            });
        });

        deptLeaves.forEach(function(leave) {
            const leaveDate = formatDateOnly(leave.date);
            const leaveTime = new Date(leaveDate + 'T00:00:00').getTime();
            const rangeStartTime = new Date(rangeStart).setHours(0, 0, 0, 0);
            const rangeEndTime = new Date(rangeEnd).setHours(0, 0, 0, 0);

            if (leaveTime < rangeStartTime || leaveTime >= rangeEndTime) {
                return;
            }

            const percentage = leave.total > 0 ? (leave.count / leave.total) * 100 : 0;
            let intensity = 'low';

            if (percentage >= 50) intensity = 'high';
            else if (percentage >= 30) intensity = 'medium';

            events.push({
                title: `${leave.department}: ${leave.count} on leave`,
                start: leaveDate,
                allDay: true,
                classNames: [`department-leave-${intensity}`],
                extendedProps: {
                    type: 'department-leave',
                    department: leave.department,
                    department_id: leave.department_id,
                    count: leave.count,
                    total: leave.total,
                    percentage: percentage
                }
            });
        });

        return events;
    }

    function getHolidayDisplayDate(holiday, year) {
        if (holiday.is_recurring) {
            const parts = formatDateOnly(holiday.start_date).split('-');
            return `${year}-${parts[1]}-${parts[2]}`;
        }

        return formatDateOnly(holiday.start_date);
    }

    function holidayOccursInYear(holiday, year) {
        if (holiday.is_recurring) {
            return true;
        }

        const startYear = new Date(formatDateOnly(holiday.start_date) + 'T00:00:00').getFullYear();
        const endYear = new Date(formatDateOnly(holiday.end_date || holiday.start_date) + 'T00:00:00').getFullYear();

        return year >= startYear && year <= endYear;
    }

    function getNextOccurrenceTimestamp(holiday, fromDate) {
        const from = new Date(fromDate);
        from.setHours(0, 0, 0, 0);

        if (!holiday.is_recurring) {
            const singleDate = new Date(formatDateOnly(holiday.start_date) + 'T00:00:00');
            return singleDate >= from ? singleDate.getTime() : null;
        }

        const parts = formatDateOnly(holiday.start_date).split('-');
        const month = parseInt(parts[1], 10) - 1;
        const day = parseInt(parts[2], 10);
        let candidate = new Date(from.getFullYear(), month, day);

        if (candidate < from) {
            candidate = new Date(from.getFullYear() + 1, month, day);
        }

        return candidate.getTime();
    }

    function initializeCalendar() {
        calendarEl = document.getElementById('leaveCalendar');
        if (!calendarEl) return;

        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            initialDate: new Date(),

            headerToolbar: {
                left: 'prev,next myToday',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },

            customButtons: {
                myToday: {
                    text: 'Today',
                    click: function() {
                        calendar.changeView('timeGridDay');
                        calendar.today();
                    }
                }
            },

            events: function(info, successCallback) {
                successCallback(buildCalendarEvents(info.start, info.end));
            },
            eventDisplay: 'block',
            dayMaxEvents: 5,
            moreLinkClick: 'popover',

            eventDidMount: function(info) {
                const eventType = info.event.extendedProps.type;

                if (eventType === 'holiday') {
                    const titleEl = info.el.querySelector('.fc-event-title');
                    if (titleEl && !titleEl.querySelector('.holiday-dot')) {
                        const dot = document.createElement('span');
                        dot.className = 'holiday-dot';
                        titleEl.prepend(dot);
                    }
                } else if (eventType === 'blackout') {
                    info.el.style.border = '2px solid #000000';
                    info.el.style.backgroundColor = '#000000';
                    info.el.style.color = '#ffffff';
                    info.el.style.borderRadius = '4px';
                }
            },

            eventClick: function(info) {
                info.jsEvent.preventDefault();
                showEventDetails(info.event);
            },

            dateClick: function(info) {
                // optional
            }
        });

        calendar.render();

        const todayBtn = document.getElementById('todayBtn');
        if (todayBtn) {
            todayBtn.addEventListener('click', function() {
                calendar.changeView('timeGridDay');
                calendar.today();
            });
        }
    }

    function showEventDetails(event) {
        const props = event.extendedProps || {};
        const canvasEl = document.getElementById('eventDetailCanvas');
        if (!canvasEl) return;

        const canvas = bootstrap.Offcanvas.getOrCreateInstance(canvasEl);

        const clickedDate = props.clicked_date || event.startStr || '';

        const eventTitleEl = document.getElementById('eventTitle');
        if (eventTitleEl) {
            eventTitleEl.textContent = event.title || '-';
        }

        const eventDateEl = document.getElementById('eventDate');
        if (eventDateEl) {
            eventDateEl.textContent = clickedDate
                ? new Date(clickedDate + 'T00:00:00').toLocaleDateString('en-US', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                })
                : '-';
        }

        const sectionsToHide = [
            'eventOrganizationSection',
            'eventDepartmentSection',
            'eventSbuSection',
            'eventReasonSection',
            'leaveStatsSection',
            'impactLevelSection',
            'affectedEmployeesSection',
            'holidayActions'
        ];

        sectionsToHide.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.style.display = 'none';
        });

        if (props.type === 'holiday') {
            const badge = document.getElementById('eventTypeBadge');
            if (badge) {
                badge.innerHTML =
                    '<span class="badge bg-info text-dark" style="padding: 0.5rem 1rem; font-size: 0.875rem;">Public Holiday</span>';
            }

            if (props.organization) {
                const section = document.getElementById('eventOrganizationSection');
                const value = document.getElementById('eventOrganization');
                if (section) section.style.display = 'block';
                if (value) value.textContent = props.organization;
            }

            if (props.department) {
                const section = document.getElementById('eventDepartmentSection');
                const value = document.getElementById('eventDepartment');
                if (section) section.style.display = 'block';
                if (value) value.textContent = props.department;
            }

            if (props.sbu) {
                const section = document.getElementById('eventSbuSection');
                const value = document.getElementById('eventSbu');
                if (section) section.style.display = 'block';
                if (value) value.textContent = props.sbu;
            }

            if (props.reason) {
                const section = document.getElementById('eventReasonSection');
                const value = document.getElementById('eventReason');
                if (section) section.style.display = 'block';
                if (value) value.textContent = props.reason;
            }

            const holidayActions = document.getElementById('holidayActions');
            if (holidayActions) holidayActions.style.display = 'block';

            const editBtn = document.getElementById('detailEditBtn');
            const deleteBtn = document.getElementById('detailDeleteBtn');

            if (editBtn) editBtn.onclick = () => editHoliday(props.id);
            if (deleteBtn) deleteBtn.onclick = () => deleteHoliday(props.id);

        } else if (props.type === 'department-leave') {
            const percentage = Number(props.percentage || 0);
            let badgeColor = 'rgba(1, 36, 69, 0.4)';
            let intensity = 'Low';

            if (percentage >= 50) {
                badgeColor = 'var(--main-color)';
                intensity = 'High';
            } else if (percentage >= 30) {
                badgeColor = 'rgba(1, 36, 69, 0.7)';
                intensity = 'Medium';
            }

            const badge = document.getElementById('eventTypeBadge');
            if (badge) {
                badge.innerHTML =
                    `<span class="badge" style="background-color: ${badgeColor}; padding: 0.5rem 1rem; font-size: 0.875rem;">Departmental Leave - ${intensity} Impact</span>`;
            }

            const deptSection = document.getElementById('eventDepartmentSection');
            const deptValue = document.getElementById('eventDepartment');
            if (deptSection) deptSection.style.display = 'block';
            if (deptValue) deptValue.textContent = props.department || '-';

            const statsSection = document.getElementById('leaveStatsSection');
            if (statsSection) statsSection.style.display = 'block';

            const leaveCount = document.getElementById('leaveCount');
            const totalStaff = document.getElementById('totalStaff');
            const leavePercentage = document.getElementById('leavePercentage');

            if (leaveCount) leaveCount.textContent = props.count ?? 0;
            if (totalStaff) totalStaff.textContent = props.total ?? 0;
            if (leavePercentage) leavePercentage.textContent = percentage.toFixed(1) + '%';

            const progressBar = document.getElementById('leaveProgressBar');
            if (progressBar) {
                progressBar.style.width = percentage + '%';
                progressBar.setAttribute('aria-valuenow', percentage);
                progressBar.setAttribute('aria-valuemin', 0);
                progressBar.setAttribute('aria-valuemax', 100);
            }

            const impactSection = document.getElementById('impactLevelSection');
            if (impactSection) impactSection.style.display = 'block';

            let impactDescription = '';
            if (percentage >= 50) {
                impactDescription = 'Critical: 50% or more of department is on leave. Immediate workload redistribution required.';
            } else if (percentage >= 30) {
                impactDescription = 'Warning: 30-50% of department is on leave. Monitor workload distribution closely.';
            } else {
                impactDescription = 'Normal: Less than 30% of department is on leave. Standard operations expected.';
            }

            const impactBadge = document.getElementById('impactLevelBadge');
            const impactDesc = document.getElementById('impactLevelDescription');

            if (impactBadge) {
                impactBadge.innerHTML =
                    `<span class="badge" style="background-color: ${badgeColor}; padding: 0.5rem 1rem; font-size: 0.875rem;">${intensity} Impact</span>`;
            }
            if (impactDesc) impactDesc.textContent = impactDescription;

            const affectedSection = document.getElementById('affectedEmployeesSection');
            if (affectedSection) affectedSection.style.display = 'block';

            const employeesList = document.getElementById('affectedEmployeesList');
            if (employeesList) {
                employeesList.innerHTML =
                    '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-light" role="status"></div><div class="small mt-2">Loading employees...</div></div>';
            }

            const d = event.start;
            const dateStr = d.getFullYear() + '-' +
                String(d.getMonth() + 1).padStart(2, '0') + '-' +
                String(d.getDate()).padStart(2, '0');

            const fetchUrl = `{{ route('admin.leave-calendar.fetch-department-employees') }}?date=${dateStr}&department_id=${props.department_id}`;

            fetch(fetchUrl)
                .then(res => res.json())
                .then(data => {
                    if (!employeesList) return;

                    employeesList.innerHTML = '';

                    if (data.success && data.employees.length > 0) {
                        data.employees.forEach(emp => {
                            const empItem = document.createElement('div');
                            empItem.className = 'd-flex align-items-center mb-2 pb-2 border-bottom';
                            empItem.style.borderColor = '#ffffff1a';

                            empItem.innerHTML = `
                                <div class="user-avatar me-3">${emp.initials}</div>
                                <div class="flex-grow-1">
                                    <div class="small fw-semibold">${emp.full_name}</div>
                                    <small class="opacity-75 text-white">${emp.id} • ${emp.leaveType}${emp.quota_info ? ` • Quota: ${emp.quota_info}` : ''}</small>
                                </div>
                            `;
                            employeesList.appendChild(empItem);
                        });
                    } else {
                        employeesList.innerHTML = '<div class="small opacity-50 text-center py-3">No specific employee records found for this date.</div>';
                    }
                })
                .catch(err => {
                    console.error('Fetch error:', err);
                    if (employeesList) {
                        employeesList.innerHTML = '<div class="small text-warning text-center py-3">Error loading employee data.</div>';
                    }
                });

        } else if (props.type === 'blackout') {
            const badge = document.getElementById('eventTypeBadge');
            if (badge) {
                badge.innerHTML =
                    '<span class="badge" style="background-color: #000000; color: #ffffff; padding: 0.5rem 1rem; font-size: 0.875rem;">Blackout Date</span>';
            }

            const reasonSection = document.getElementById('eventReasonSection');
            const reasonValue = document.getElementById('eventReason');
            if (reasonSection) reasonSection.style.display = 'block';
            if (reasonValue) reasonValue.textContent = props.reason || '-';
        }

        canvas.show();
    }

    function generateSampleEmployees(department, count) {
        const names = [
            'Ahmed Ali', 'Zainab Malik', 'Bilal Ahmed', 'Hira Ali', 'Hamza Khan',
            'Sana Sheikh', 'Faisal Raza', 'Ayesha Malik', 'Khurram Raza', 'Amna Ali'
        ];
        const leaveTypes = ['Annual Leave', 'Sick Leave', 'Casual Leave', 'Comp-Off'];
        const employees = [];

        for (let i = 0; i < Math.min(count, 10); i++) {
            const name = names[i % names.length];
            const initials = name.split(' ').map(n => n[0]).join('').toUpperCase();
            employees.push({
                name: name,
                initials: initials,
                id: `EMP-${String(i + 1).padStart(3, '0')}`,
                leaveType: leaveTypes[i % leaveTypes.length]
            });
        }

        return employees;
    }

    function populateSidebarStats() {
        const todayStr = new Date().toISOString().split('T')[0];
        const todayLeaves = deptLeaves.filter(l => formatDateOnly(l.date) === todayStr);
        const todayContainer = document.getElementById('todayLeavesList');

        if (todayContainer) {
            todayContainer.innerHTML = '';

            if (todayLeaves.length === 0) {
                todayContainer.innerHTML =
                    '<div class="text-muted small py-3 text-center border rounded-3" style="border-style: dashed !important; opacity: 0.6;">No leaves scheduled for today</div>';
            } else {
                todayLeaves.forEach(leave => {
                    const percentage = leave.total > 0 ? Math.round((leave.count / leave.total) * 100) : 0;
                    let badgeClass = 'bg-success';

                    if (percentage >= 50) badgeClass = 'bg-main';
                    else if (percentage >= 30) badgeClass = 'bg-primary';

                    const item = document.createElement('div');
                    item.className = 'd-flex justify-content-between align-items-center p-2 mb-2 rounded-3';
                    item.style.backgroundColor = 'rgba(1, 36, 69, 0.05)';
                    item.innerHTML = `
                        <div class="flex-grow-1">
                            <div class="fw-semibold" style="font-size: 13px; color: #012445;">${leave.department}</div>
                            <small class="text-muted" style="font-size: 11px;">${leave.count} away out of ${leave.total}</small>
                        </div>
                        <span class="badge ${badgeClass}" style="font-size: 10px;">${percentage}%</span>
                    `;
                    todayContainer.appendChild(item);
                });
            }
        }

        const upcomingHolidaysContainer = document.getElementById('upcomingHolidaysList');
        if (upcomingHolidaysContainer) {
            upcomingHolidaysContainer.innerHTML = '';

            const today = new Date();
            today.setHours(0, 0, 0, 0);

            const upcoming = publicHolidays
                .map(function(holiday) {
                    return {
                        holiday: holiday,
                        nextTs: getNextOccurrenceTimestamp(holiday, today)
                    };
                })
                .filter(function(item) {
                    return item.nextTs !== null;
                })
                .sort(function(a, b) {
                    return a.nextTs - b.nextTs;
                });

            if (upcoming.length === 0) {
                upcomingHolidaysContainer.innerHTML =
                    '<div class="text-muted small py-4 text-center opacity-75">No upcoming holidays found</div>';
            } else {
                upcoming.forEach(function(entry) {
                    const holiday = entry.holiday;
                    const nextDate = new Date(entry.nextTs);
                    const item = document.createElement('div');
                    item.className = 'holiday-list-item d-flex justify-content-between align-items-center p-2 mb-2 rounded-3';
                    item.style.backgroundColor = 'rgba(1, 36, 69, 0.02)';
                    item.innerHTML = `
                        <div class="flex-grow-1">
                            <div class="fw-semibold" style="font-size: 13px; color: #333;">${holiday.name}</div>
                            <small class="text-muted" style="font-size: 11px;">${nextDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}</small>
                        </div>
                        <button class="btn btn-outline-primary btn-sm rounded-pill"
                                style="font-size: 9px !important; padding: 2px 8px; height: 22px;"
                                onclick="editHoliday(${holiday.id})">
                            Edit
                        </button>
                    `;
                    upcomingHolidaysContainer.appendChild(item);
                });
            }
        }

        populateHistoryByYear(new Date().getFullYear());
    }
</script>
<script>
    window.editHoliday = function(id) {
        const url = "{{ route('admin.leave-calendar.show', ['id' => ':id']) }}".replace(':id', id);

        fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        let errorMsg = `Server error ${response.status}`;
                        try {
                            const errJson = JSON.parse(text);
                            errorMsg = errJson.message || errorMsg;
                        } catch (e) {
                            if (text.includes('403') || text.includes('Unauthorized')) errorMsg = 'Unauthorized action (403)';
                            if (text.includes('404') || text.includes('Not Found')) errorMsg = 'Holiday not found (404)';
                        }
                        throw new Error(errorMsg);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (!data.success) {
                    showError(data.message || 'Could not fetch holiday details.', 'Error');
                    return;
                }

                const holiday = data.holiday;
                const offcanvasEl = document.getElementById('addHolidayCanvas');
                const canvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);

                document.getElementById('addHolidayCanvasLabel').textContent = 'Edit Holiday';
                const form = document.getElementById('addHolidayForm');

                form.action = "{{ route('admin.leave-calendar.update', ['id' => ':id']) }}".replace(':id', id);

                document.getElementById('holidayName').value = holiday.name || '';
                document.getElementById('holidayStartDate').value = (holiday.start_date || '').split('T')[0];
                document.getElementById('holidayEndDate').value = holiday.end_date ? holiday.end_date.split('T')[0] : '';
                document.getElementById('isRecurring').checked = !!holiday.is_recurring;
                document.getElementById('isBlackout').checked = !!holiday.is_blackout;
                document.getElementById('blackoutReason').value = holiday.reason || '';

                // Organization
                if (holiday.organization_scope === 'all') {
                    document.getElementById('scopeAll').checked = true;
                    document.getElementById('organizationSelectSection').style.display = 'none';
                } else {
                    document.getElementById('scopeSpecific').checked = true;
                    document.getElementById('organizationSelectSection').style.display = 'block';

                    const orgIds = (holiday.organizations || []).map(o => parseInt(o.id));
                    const select = document.getElementById('holidayOrganizations');
                    Array.from(select.options).forEach(option => {
                        option.selected = orgIds.includes(parseInt(option.value));
                    });
                }

                // Department
                if (holiday.department_scope === 'all') {
                    document.getElementById('departmentScopeAll').checked = true;
                    document.getElementById('departmentSelectSection').style.display = 'none';
                } else if (holiday.department_scope === 'specific') {
                    document.getElementById('departmentScopeSpecific').checked = true;
                    document.getElementById('departmentSelectSection').style.display = 'block';

                    const departmentIds = (holiday.departments || []).map(d => parseInt(d.id));
                    const deptSelect = document.getElementById('holidayDepartments');
                    Array.from(deptSelect.options).forEach(option => {
                        option.selected = departmentIds.includes(parseInt(option.value));
                    });
                } else {
                    document.getElementById('departmentScopeNone').checked = true;
                    document.getElementById('departmentSelectSection').style.display = 'none';

                    const deptSelect = document.getElementById('holidayDepartments');
                    Array.from(deptSelect.options).forEach(option => {
                        option.selected = false;
                    });
                }

                // SBU
                if (holiday.sbu_scope === 'all') {
                    document.getElementById('sbuScopeAll').checked = true;
                    document.getElementById('sbuSelectSection').style.display = 'none';
                } else if (holiday.sbu_scope === 'specific') {
                    document.getElementById('sbuScopeSpecific').checked = true;
                    document.getElementById('sbuSelectSection').style.display = 'block';

                    const sbuIds = (holiday.sbus || []).map(s => parseInt(s.id));
                    const sbuSelect = document.getElementById('holidaySbus');
                    Array.from(sbuSelect.options).forEach(option => {
                        option.selected = sbuIds.includes(parseInt(option.value));
                    });
                } else {
                    document.getElementById('sbuScopeNone').checked = true;
                    document.getElementById('sbuSelectSection').style.display = 'none';

                    const sbuSelect = document.getElementById('holidaySbus');
                    Array.from(sbuSelect.options).forEach(option => {
                        option.selected = false;
                    });
                }

                form.dataset.mode = 'edit';
                form.dataset.holidayId = id;

                const deleteBtn = document.getElementById('deleteHolidayBtn');
                if (deleteBtn) deleteBtn.classList.remove('d-none');

                const detailCanvasEl = document.getElementById('eventDetailCanvas');
                const detailInstance = detailCanvasEl ? bootstrap.Offcanvas.getInstance(detailCanvasEl) : null;

                if (detailInstance && detailCanvasEl.classList.contains('show')) {
                    detailInstance.hide();
                    setTimeout(() => canvas.show(), 350);
                } else {
                    canvas.show();
                }
            })
            .catch(err => {
                console.error('Edit fetch error:', err);
                showError('Detail: ' + err.message, 'Error');
            });
    };

    window.deleteHoliday = function(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This holiday will be permanently deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const url = "{{ route('admin.leave-calendar.destroy', ['id' => ':id']) }}".replace(':id', id);

                fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => {
                        if (!res.ok) throw new Error('Delete failed');
                        return res.json();
                    })
                    .then(data => {
                        if (data.success) {
                            showSuccess(data.message, 'Deleted!').then(() => {
                                window.location.reload();
                            });
                        } else {
                            showError(data.message);
                        }
                    })
                    .catch(err => {
                        console.error('Delete error:', err);
                        showError('An unexpected error occurred.');
                    });
            }
        });
    };

    window.populateHistoryByYear = function(year) {
        const container = document.getElementById('allHolidaysList');
        if (!container) return;

        const yearNumber = parseInt(year, 10);

        const filtered = publicHolidays
            .filter(function(h) {
                return holidayOccursInYear(h, yearNumber);
            })
            .sort(function(a, b) {
                const dateA = new Date(getHolidayDisplayDate(a, yearNumber) + 'T00:00:00');
                const dateB = new Date(getHolidayDisplayDate(b, yearNumber) + 'T00:00:00');
                return dateB - dateA;
            });

        container.innerHTML = '';

        if (filtered.length === 0) {
            container.innerHTML = '<div class="text-muted text-center py-4 small opacity-75">No holidays found for ' + year + '</div>';
            return;
        }

        filtered.forEach(holiday => {
            const item = document.createElement('div');
            item.className = 'holiday-list-item d-flex justify-content-between align-items-center p-2 mb-2 rounded-3';
            item.style.backgroundColor = 'rgba(1, 36, 69, 0.02)';
            item.innerHTML = `
                <div class="flex-grow-1">
                    <div class="fw-semibold" style="font-size: 13px; color: #333;">${holiday.name}</div>
                    <small class="text-muted" style="font-size: 11px;">
                        ${new Date(getHolidayDisplayDate(holiday, yearNumber) + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}
                    </small>
                </div>
                <button class="btn btn-outline-primary btn-sm rounded-pill"
                        style="font-size: 9px !important; padding: 2px 8px; height: 22px;"
                        onclick="window.editHoliday(${holiday.id})">
                    Edit
                </button>
            `;
            container.appendChild(item);
        });
    };
</script>
@endpush
