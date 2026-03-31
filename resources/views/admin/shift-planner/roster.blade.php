<div class="row">
    <div class="col-12">
        <div class="card roster-card border-0 rounded-4 shadow-none">
            <div class="card-body p-0">
                <div class="roster-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <h6 class="mb-0 fw-semibold text-main d-flex align-items-center">
                            <span class="roster-header-icon"><i class="bi bi-calendar3"></i></span>
                            Roster Calendar
                        </h6>

                        <div class="roster-toolbar d-flex align-items-center gap-2 flex-wrap">
                            <button type="button" class="btn btn-sm btn-outline-secondary roster-nav-btn border rounded-2" id="rosterPrevWeek" aria-label="Previous week">
                                <i class="bi bi-chevron-left"></i>
                            </button>

                            <span class="roster-week-display fw-medium text-dark px-2 text-center" id="rosterWeekDisplay" style="min-width: 200px;">
                                <span id="rosterWeekLabel">Current Week</span>
                                <span class="text-muted fw-normal" id="rosterWeekDates"></span>
                            </span>

                            <button type="button" class="btn btn-sm btn-outline-secondary roster-nav-btn border rounded-2" id="rosterNextWeek" aria-label="Next week">
                                <i class="bi bi-chevron-right"></i>
                            </button>

                            <span class="roster-month-display text-muted small ms-1" id="rosterMonthYear"></span>

                            <button type="button" class="btn btn-sm btn-outline-primary ms-1 rounded-2" id="rosterTodayBtn">
                                <i class="bi bi-calendar-event me-1"></i>Today
                            </button>
                        </div>
                    </div>

                    <div class="roster-legend d-flex flex-wrap align-items-center gap-3">
                        <span class="text-muted small me-1">Shifts:</span>
                        <span class="roster-legend-item roster-legend-morning">
                            <i class="bi bi-sun-fill me-1"></i>Morning
                        </span>
                        <span class="roster-legend-item roster-legend-evening">
                            <i class="bi bi-cloud-sun-fill me-1"></i>Evening
                        </span>
                        <span class="roster-legend-item roster-legend-night">
                            <i class="bi bi-moon-stars-fill me-1"></i>Night
                        </span>
                    </div>
                </div>

                <div class="roster-table-wrap rounded-3 overflow-hidden border">
                    <div class="table-responsive">
                        <table id="employeeTable" class="table table-hover roster-table align-middle mb-0">
                            <thead>
                                <tr class="roster-thead-row" id="rosterTheadRow">
                                    <th class="roster-col-toggle"></th>
                                    <th class="roster-col-employee">Department / Employee</th>
                                </tr>
                            </thead>
                            <tbody id="rosterTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let rosterCalendar = null;
    let selectedDates = null;

    const employees = @json($employees ?? []);
    const shifts = @json($shifts ?? []);
    const rosters = @json($rosters ?? []);

    const shiftColors = {
        '1': {
            bg: 'rgba(0, 57, 171, 0.3)',
            border: '#0a58ca',
            name: 'Morning Shift'
        },
        '2': {
            bg: 'rgba(13, 202, 240, 0.3)',
            border: '#0aa2c0',
            name: 'Night Shift'
        },
        '3': {
            bg: 'rgba(25, 135, 84, 0.3)',
            border: '#146c43',
            name: 'Site Sales - Weekend'
        }
    };

    let currentWeekStart = getStartOfWeek(new Date());

    function getStartOfWeek(date) {
        const d = new Date(date);
        d.setHours(0, 0, 0, 0);

        const day = d.getDay(); // 0 = Sunday
        const diff = d.getDate() - day + (day === 0 ? -6 : 1); // Monday start

        const start = new Date(d);
        start.setDate(diff);
        start.setHours(0, 0, 0, 0);

        return start;
    }

    function formatDate(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    function normalizeRosterDate(value) {
        if (!value) return '';

        if (typeof value === 'string') {
            // Handles YYYY-MM-DD and YYYY-MM-DD HH:mm:ss
            const directMatch = value.match(/^(\d{4})-(\d{2})-(\d{2})/);
            if (directMatch) {
                return `${directMatch[1]}-${directMatch[2]}-${directMatch[3]}`;
            }

            // Handles DD-MM-YYYY
            const dmyMatch = value.match(/^(\d{2})-(\d{2})-(\d{4})$/);
            if (dmyMatch) {
                return `${dmyMatch[3]}-${dmyMatch[2]}-${dmyMatch[1]}`;
            }
        }

        const parsed = new Date(value);
        if (!isNaN(parsed.getTime())) {
            return formatDate(parsed);
        }

        return '';
    }

    function escapeHtml(value) {
        if (value === null || value === undefined) return '';
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function getWeekDates(startDate) {
        const dates = [];

        for (let i = 0; i < 7; i++) {
            const d = new Date(startDate);
            d.setDate(startDate.getDate() + i);
            d.setHours(0, 0, 0, 0);
            dates.push(d);
        }

        return dates;
    }

    function updateWeekHeader(weekDates) {
        const theadRow = document.getElementById('rosterTheadRow');

        if (!theadRow) return;

        let headerHtml = `
            <th class="roster-col-toggle"></th>
            <th class="roster-col-employee">Department / Employee</th>
        `;

        weekDates.forEach(date => {
            headerHtml += `
                <th class="text-center">
                    <div class="fw-semibold">${date.toLocaleDateString('en-US', { weekday: 'short' })}</div>
                    <div class="small text-muted">${date.toLocaleDateString('en-US', { day: '2-digit', month: 'short' })}</div>
                </th>
            `;
        });

        theadRow.innerHTML = headerHtml;

        const weekLabel = document.getElementById('rosterWeekLabel');
        const weekDatesLabel = document.getElementById('rosterWeekDates');
        const monthYearLabel = document.getElementById('rosterMonthYear');

        if (weekLabel) weekLabel.innerText = 'Current Week';
        if (weekDatesLabel) {
            weekDatesLabel.innerText = `${weekDates[0].toLocaleDateString('en-GB')} to ${weekDates[6].toLocaleDateString('en-GB')}`;
        }
        if (monthYearLabel) {
            monthYearLabel.innerText = weekDates[0].toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
        }
    }

    function getShiftById(shiftId) {
        return shifts.find(shift => String(shift.id) === String(shiftId)) || null;
    }

    function getRosterForEmployeeAndDate(employeeId, dateStr) {
        return rosters.find(roster =>
            String(roster.employee_id) === String(employeeId) &&
            normalizeRosterDate(roster.roster_date) === dateStr
        ) || null;
    }

    function getEmployeeName(employee) {
        return employee.full_name || employee.name || employee.employee_name || '-';
    }

    function getDepartmentName(employee) {
        return employee.department?.name || employee.department_name || 'No Department';
    }

    function getShiftDisplayColor(shiftId, shift) {
        return shiftColors[String(shiftId)] || {
            bg: 'rgba(108, 117, 125, 0.15)',
            border: '#6c757d',
            name: shift?.name || 'Assigned Shift'
        };
    }

    function getShiftTimeText(shift) {
        if (!shift) return '';

        const start = shift.start_time || '';
        const end = shift.end_time || '';

        if (start && end) return `${start} - ${end}`;
        if (start) return start;
        if (end) return end;

        return '';
    }

    function renderRosterTable() {
        const tbody = document.getElementById('rosterTableBody');
        if (!tbody) return;

        const weekDates = getWeekDates(currentWeekStart);
        updateWeekHeader(weekDates);

        if (!Array.isArray(employees) || employees.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">No employees found.</td>
                </tr>
            `;
            return;
        }

        let html = '';

        employees.forEach(employee => {
            html += `
                <tr>
                    <td></td>
                    <td>
                        <div class="fw-semibold">${escapeHtml(getEmployeeName(employee))}</div>
                        <div class="small text-muted">${escapeHtml(getDepartmentName(employee))}</div>
                    </td>
            `;

            weekDates.forEach(date => {
                const dateStr = formatDate(date);
                const roster = getRosterForEmployeeAndDate(employee.id, dateStr);

                if (roster) {
                    const shift = getShiftById(roster.shift_id);
                    const color = getShiftDisplayColor(roster.shift_id, shift);
                    const shiftName = shift?.name || color.name || 'Shift';
                    const shiftTime = getShiftTimeText(shift);

                    html += `
                        <td class="text-center">
                            <div class="px-2 py-1 rounded-2 border" style="background: ${color.bg}; border-color: ${color.border} !important;">
                                <div class="fw-semibold small">${escapeHtml(shiftName)}</div>
                                <div class="text-muted" style="font-size: 11px;">
                                    ${escapeHtml(shiftTime)}
                                </div>
                            </div>
                        </td>
                    `;
                } else {
                    html += `
                        <td class="text-center">
                            <span class="text-muted small">—</span>
                        </td>
                    `;
                }
            });

            html += `</tr>`;
        });

        tbody.innerHTML = html;
    }

    document.addEventListener('DOMContentLoaded', function () {
        renderRosterTable();

        const prevBtn = document.getElementById('rosterPrevWeek');
        const nextBtn = document.getElementById('rosterNextWeek');
        const todayBtn = document.getElementById('rosterTodayBtn');

        if (prevBtn) {
            prevBtn.addEventListener('click', function () {
                const newDate = new Date(currentWeekStart);
                newDate.setDate(newDate.getDate() - 7);
                currentWeekStart = getStartOfWeek(newDate);
                renderRosterTable();
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function () {
                const newDate = new Date(currentWeekStart);
                newDate.setDate(newDate.getDate() + 7);
                currentWeekStart = getStartOfWeek(newDate);
                renderRosterTable();
            });
        }

        if (todayBtn) {
            todayBtn.addEventListener('click', function () {
                currentWeekStart = getStartOfWeek(new Date());
                renderRosterTable();
            });
        }
    });
</script>