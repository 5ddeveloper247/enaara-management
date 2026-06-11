(function() {
    var rosterViewDate = new Date();
    var rosterGmApprovalContext = null;
    var ROSTER_APPROVAL_REVIEW_STORAGE_KEY = 'rosterApprovalReviewId';

    function peekRosterApprovalReviewRequestId() {
        var fromStorage = parseInt(sessionStorage.getItem(ROSTER_APPROVAL_REVIEW_STORAGE_KEY) || '0', 10);
        if (fromStorage > 0) {
            return fromStorage;
        }
        var params = new URLSearchParams(window.location.search);
        return parseInt(params.get('roster_approval') || '0', 10);
    }

    function takeRosterApprovalReviewRequestId() {
        var fromStorage = parseInt(sessionStorage.getItem(ROSTER_APPROVAL_REVIEW_STORAGE_KEY) || '0', 10);
        if (fromStorage > 0) {
            sessionStorage.removeItem(ROSTER_APPROVAL_REVIEW_STORAGE_KEY);
            return fromStorage;
        }
        var fromUrl = peekRosterApprovalReviewRequestId();
        if (fromUrl > 0 && window.history && window.history.replaceState) {
            window.history.replaceState({}, '', window.shiftPlannerUrl || '/admin/shift-planner');
        }
        return fromUrl;
    }
    var rosterWeekIndex = 1;
    var rosterData = null;
    var rosterPersonnelFilter = 'internal';
    var rosterShowDeleted = false;

    function stripTime(d) {
        return new Date(d.getFullYear(), d.getMonth(), d.getDate());
    }

    function pad2(n) {
        return n < 10 ? '0' + n : String(n);
    }

    // Format date as YYYY-MM-DD using local date parts (avoids UTC date shifting).
    function dateToISO(date) {
        return date.getFullYear() + '-' + pad2(date.getMonth() + 1) + '-' + pad2(date.getDate());
    }

    function parseISODate(iso) {
        // iso: YYYY-MM-DD
        var parts = String(iso).split('-');
        if (parts.length !== 3) return new Date(NaN);
        var y = parseInt(parts[0], 10);
        var m = parseInt(parts[1], 10) - 1;
        var d = parseInt(parts[2], 10);
        return new Date(y, m, d);
    }

    // Calendar week range: Monday -> Sunday.
    // Week 1 = week containing the 1st day of the month (starting from Monday).
    function getFirstMondayOfMonth(year, month1) {
        // month1 is 1-based (1..12)
        var monthStart = new Date(year, month1 - 1, 1);
        // JS day: 0=Sun ... 6=Sat. Convert to "days since Monday".
        var offset = (monthStart.getDay() + 6) % 7; // Mon=0 ... Sun=6
        return new Date(year, month1 - 1, 1 - offset);
    }

    function getWeekStartDate(year, month1, weekIndex) {
        var firstMonday = getFirstMondayOfMonth(year, month1);
        return new Date(firstMonday.getFullYear(), firstMonday.getMonth(), firstMonday.getDate() + (weekIndex - 1) * 7);
    }

    function getWeekDays(year, month1, weekIndex) {
        var ws = getWeekStartDate(year, month1, weekIndex);
        var days = [];
        for (var i = 0; i < 7; i++) {
            var d = new Date(ws);
            d.setDate(ws.getDate() + i);
            days.push(d);
        }
        return days;
    }

    function getWeekIndexForDate(date, year, month1) {
        var firstMonday = getFirstMondayOfMonth(year, month1);
        var diffDays = Math.floor((stripTime(date).getTime() - stripTime(firstMonday).getTime()) / 86400000);
        return Math.floor(diffDays / 7) + 1;
    }

    function getWeekMaxIndexForMonth(year, month1) {
        var firstMonday = getFirstMondayOfMonth(year, month1);
        var monthEnd = new Date(year, month1, 0); // last day of month
        var diffDays = Math.floor((stripTime(monthEnd).getTime() - stripTime(firstMonday).getTime()) / 86400000);
        return Math.floor(diffDays / 7) + 1;
    }

    // Reorder the 7 consecutive days so that the table always shows:
    // Monday ... Sunday (based on actual weekday of each day-of-month).
    function orderDaysMondayFirst(days, year, month) {
        if (!Array.isArray(days) || days.length === 0) return [];
        return days.slice().sort(function(a, b) {
            // JS getDay(): 0=Sun ... 6=Sat
            var aKey = (new Date(year, month, a).getDay() + 6) % 7; // Mon=0 ... Sun=6
            var bKey = (new Date(year, month, b).getDay() + 6) % 7;
            return aKey - bKey;
        });
    }

    function padDay(n) {
        return n < 10 ? '0' + n : String(n);
    }

    // Backward compat (unused after Mon->Sun rewrite):
    function rosterDateIso(day) {
        var y = rosterViewDate.getFullYear();
        var m = rosterViewDate.getMonth() + 1;
        return y + '-' + pad2(m) + '-' + pad2(day);
    }

    function csrfToken() {
        var m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : '';
    }

    function formatTimeAMPM(timeString) {
        if (!timeString) return '';
        var parts = timeString.split(':');
        if (parts.length < 2) return timeString;
        var hours = parseInt(parts[0], 10);
        var minutes = parts[1];
        var ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12; // the hour '0' should be '12'
        hours = hours < 10 ? '0' + hours : hours;
        return hours + ':' + minutes + ' ' + ampm;
    }

    function shiftTypeIconHtml(shiftType) {
        var icon = '';
        if (shiftType === 'morning') {
            icon = 'bi-sun-fill';
        } else if (shiftType === 'evening') {
            icon = 'bi-cloud-sun-fill';
        } else if (shiftType === 'night') {
            icon = 'bi-moon-stars-fill';
        }
        if (!icon) {
            return '';
        }
        return '<span class="shift-pill-icon" aria-hidden="true"><i class="bi ' + icon + '"></i></span>';
    }

    function sortShiftsForCell(shiftList) {
        var holidays = [];
        var active = [];
        var deleted = [];
        (shiftList || []).forEach(function(s) {
            if (s.deletedAt) {
                deleted.push(s);
            } else if (s.isPublicHoliday) {
                holidays.push(s);
            } else {
                active.push(s);
            }
        });
        return holidays.concat(active).concat(deleted);
    }

    function renderCellShiftsHtml(shiftList) {
        var ordered = sortShiftsForCell(shiftList);
        if (!ordered.length) {
            return '';
        }
        var html = '<div class="shift-cell-stack">';
        ordered.forEach(function(s) {
            html += '<div class="shift-pill-hit" data-shift="' + encodeURIComponent(JSON.stringify(s)) + '">' + pillHtml(s) + '</div>';
        });
        html += '</div>';
        return html;
    }

    function shiftPlaceHtml(s) {
        var floor = s.floor ? String(s.floor).trim() : '';
        var location = s.location ? String(s.location).trim() : '';
        if (!floor && !location) {
            return '';
        }
        if (floor && location) {
            return '<span class="shift-place">' + escapeHtml(floor) +
                '<span class="shift-place-sep" aria-hidden="true"> | </span>' +
                escapeHtml(location) + '</span>';
        }
        return '<span class="shift-place">' + escapeHtml(floor || location) + '</span>';
    }

    function pillHtml(s) {
        if (s.isPublicHoliday || (s.status && String(s.status).toLowerCase() === 'holiday')) {
            var holidayLabel = s.holidayName ? String(s.holidayName) : 'Holiday';
            if (holidayLabel.length > 18) {
                holidayLabel = holidayLabel.substring(0, 16) + '…';
            }
            return '<div class="shift-pill shift-holiday" title="' + escapeHtml(s.holidayName || 'Public holiday') + '">' +
                '<span class="shift-pill-icon" aria-hidden="true"><i class="bi bi-calendar-event"></i></span>' +
                '<div class="shift-pill-top">' +
                '<span class="shift-time">' + escapeHtml(holidayLabel) + '</span>' +
                '</div>' +
                '</div>';
        }

        if (s.isLeave || (s.status && String(s.status).toLowerCase() === 'leave')) {
            var rawName = s.leaveName ? String(s.leaveName).trim() : 'leave';
            var typeName = rawName.replace(/\s*leaves?\s*$/i, '').trim() || rawName;
            if (typeName.length > 16) {
                typeName = typeName.substring(0, 14) + '…';
            }
            var isHalfDayLeave = !!(s.isHalfDayLeave || (s.leaveDuration && parseFloat(s.leaveDuration) < 1));
            var session = s.halfDaySession ? String(s.halfDaySession).toLowerCase() : '';
            var sessionLabel = session === 'morning'
                ? 'Morning'
                : (session === 'afternoon' ? 'Afternoon' : '');
            var leaveDisplayText = isHalfDayLeave
                ? 'Short Leave'
                : ('On ' + typeName + ' leave');
            var sessionMetaHtml = isHalfDayLeave && sessionLabel
                ? '<div class="shift-pill-meta"><span class="shift-half-session">' + escapeHtml(sessionLabel) + '</span></div>'
                : '';
            var pillClass = isHalfDayLeave ? 'shift-pill shift-half-leave' : 'shift-pill shift-holiday';
            var pillStyle = isHalfDayLeave
                ? ''
                : 'background-color: #fef08a; border-color: #facc15; color: #854d0e;';
            var iconStyle = isHalfDayLeave ? '' : 'color: #ca8a04;';
            var titleText = isHalfDayLeave
                ? ('Short Leave' + (sessionLabel ? ' — ' + sessionLabel : '') + (rawName ? ' (' + rawName + ')' : ''))
                : rawName;
            return '<div class="' + pillClass + '"' +
                (pillStyle ? ' style="' + pillStyle + '"' : '') +
                ' title="' + escapeHtml(titleText) + '">' +
                '<span class="shift-pill-icon" aria-hidden="true"' +
                (iconStyle ? ' style="' + iconStyle + '"' : '') +
                '><i class="bi bi-' + (isHalfDayLeave ? 'clock-history' : 'person-dash') + '"></i></span>' +
                '<div class="shift-pill-top">' +
                '<span class="shift-time">' + escapeHtml(leaveDisplayText) + '</span>' +
                '</div>' +
                sessionMetaHtml +
                '</div>';
        }

        if (s.isOffDay || (s.status && String(s.status).toLowerCase() === 'off')) {
            return '<div class="shift-pill shift-off">' +
                '<span class="shift-pill-icon" aria-hidden="true"><i class="bi bi-calendar-x"></i></span>' +
                '<div class="shift-pill-top">' +
                '<span class="shift-time">OFF</span>' +
                '</div>' +
                '</div>';
        }

        var shiftType = s.shiftType && s.shiftType !== 'general' ? s.shiftType : '';
        var typeClass = shiftType ? ' shift-' + shiftType : '';
        var lateClass = s.lateCheckIn ? ' shift-late' : '';
        var deletedClass = s.deletedAt ? ' shift-cancelled' : '';
        var pendingChangeClass = s.isPendingChangeHighlight ? ' shift-pending-change' : '';
        var iconBlock = shiftTypeIconHtml(shiftType);
        var lateBlock = s.lateCheckIn ? '<span class="shift-status-late"><i class="bi bi-exclamation-circle-fill"></i> Late check-in</span>' : '';
        var placeBlock = shiftPlaceHtml(s);
        return '<div class="shift-pill' + typeClass + lateClass + deletedClass + pendingChangeClass + '">' +
            iconBlock +
            '<div class="shift-pill-top">' +
            '<span class="shift-time">' + formatTimeAMPM(s.timeStart) + ' – ' + formatTimeAMPM(s.timeEnd) + '</span>' +
            '</div>' +
            '<div class="shift-pill-meta">' +
            lateBlock +
            placeBlock +
            '</div></div>';
    }

    function formatRosterMonthYear(date) {
        var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        return months[date.getMonth()] + ' ' + date.getFullYear();
    }

    var dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    function getDayName(date) {
        return dayNames[date.getDay()];
    }

    function syncRosterPersonnelTabUi() {
        var internalTab = document.getElementById('rosterInternalTab');
        var thirdPartyTab = document.getElementById('rosterThirdPartyTab');
        var activeId = rosterPersonnelFilter === 'third_party' ? 'rosterThirdPartyTab' : 'rosterInternalTab';

        [internalTab, thirdPartyTab].forEach(function(tab) {
            if (!tab) return;
            var isActive = tab.id === activeId;
            tab.classList.toggle('active', isActive);
            tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });
    }

    function applyRosterReviewNavigation(data) {
        if (!data || !data.review_year || !data.review_month) {
            return;
        }

        rosterViewDate = new Date(parseInt(data.review_year, 10), parseInt(data.review_month, 10) - 1, 1);
        rosterWeekIndex = parseInt(data.first_review_week || '1', 10) || 1;

        if (data.employee_group === 'third_party' || data.employee_group === 'internal') {
            rosterPersonnelFilter = data.employee_group;
            syncRosterPersonnelTabUi();
        }

        updateRosterWeekDisplay();
    }

    function updateRosterWeekDisplay() {
        var weekLabelEl = document.getElementById('rosterWeekLabel');
        var weekDatesEl = document.getElementById('rosterWeekDates');
        var monthEl = document.getElementById('rosterMonthYear');
        var year = rosterViewDate.getFullYear();
        var month1 = rosterViewDate.getMonth() + 1;
        var weekStart = getWeekStartDate(year, month1, rosterWeekIndex);
        var weekEnd = new Date(weekStart);
        weekEnd.setDate(weekStart.getDate() + 6);
        if (weekLabelEl) weekLabelEl.textContent = 'Week ' + rosterWeekIndex;
        if (weekDatesEl) {
            var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            if (weekStart.getFullYear() === weekEnd.getFullYear() && weekStart.getMonth() === weekEnd.getMonth()) {
                weekDatesEl.textContent = padDay(weekStart.getDate()) + ' to ' + padDay(weekEnd.getDate());
            } else {
                weekDatesEl.textContent = padDay(weekStart.getDate()) + ' ' + months[weekStart.getMonth()] +
                    ' to ' + padDay(weekEnd.getDate()) + ' ' + months[weekEnd.getMonth()];
            }
        }
        if (monthEl) monthEl.textContent = formatRosterMonthYear(rosterViewDate);
    }

    function collectHolidaysByDate(shifts) {
        var byDate = {};
        (shifts || []).forEach(function(s) {
            if (!s.isPublicHoliday || s.deletedAt) {
                return;
            }
            var iso = s.rosterDate ? String(s.rosterDate) : '';
            if (!iso) {
                return;
            }
            if (!byDate[iso]) {
                byDate[iso] = {};
            }
            var name = s.holidayName ? String(s.holidayName).trim() : 'Holiday';
            byDate[iso][name] = true;
        });
        var result = {};
        Object.keys(byDate).forEach(function(iso) {
            result[iso] = Object.keys(byDate[iso]);
        });
        return result;
    }

    function holidayHeaderBadgeHtml(names) {
        if (!names || !names.length) {
            return '';
        }
        var label = names.join(', ');
        return '<span class="roster-col-holiday-badge" title="' + escapeHtml(label) + '">' +
            '<i class="bi bi-calendar-event" aria-hidden="true"></i>' +
            '<span class="roster-col-holiday-text">' + escapeHtml(label) + '</span>' +
            '</span>';
    }

    function buildDisplayCellShifts(rawCellShifts) {
        var active = (rawCellShifts || []).filter(function(s) {
            return !s.deletedAt;
        });
        var holidayShifts = active.filter(function(s) {
            return s.isPublicHoliday;
        });
        var nonHoliday = active.filter(function(s) {
            return !s.isPublicHoliday;
        });
        var workingShifts = nonHoliday.filter(function(s) {
            return isRosterWorkingShift(s);
        });
        var leaveShifts = nonHoliday.filter(function(s) {
            return !!(s.isLeave || String(s.status || '').toLowerCase() === 'leave');
        });

        if (workingShifts.length) {
            return sortShiftsForCell(workingShifts);
        }

        if (leaveShifts.length) {
            return sortShiftsForCell(leaveShifts);
        }

        if (holidayShifts.length) {
            return sortShiftsForCell(holidayShifts);
        }

        return sortShiftsForCell(nonHoliday);
    }

    function isRosterHolidayDate(iso, holidaysByDate) {
        return !!(holidaysByDate && iso && holidaysByDate[iso] && holidaysByDate[iso].length);
    }

    function buildTheadRow(days, holidaysByDate) {
        var tr = document.getElementById('rosterTheadRow');
        if (!tr) return;
        var existing = tr.querySelectorAll('.roster-col-day');
        existing.forEach(function(el) { el.remove(); });
        var viewMonth = rosterViewDate.getMonth();
        var viewYear = rosterViewDate.getFullYear();
        days.forEach(function(d) {
            var iso = dateToISO(d);
            var holidayNames = (holidaysByDate && holidaysByDate[iso]) ? holidaysByDate[iso] : [];
            var th = document.createElement('th');
            th.className = 'roster-col-day';
            th.setAttribute('data-roster-date', iso);
            if (holidayNames.length) {
                th.classList.add('roster-col-holiday', 'roster-day-col-holiday');
            }
            th.innerHTML = '<div class="roster-col-day-inner">' +
                '<span class="roster-col-day-label">' + getDayName(d) + ' ' + d.getDate() + '</span>' +
                holidayHeaderBadgeHtml(holidayNames) +
                '</div>';
            if (d.getMonth() !== viewMonth || d.getFullYear() !== viewYear) {
                th.style.opacity = '0.55';
            }
            tr.appendChild(th);
        });
    }

    function bindRosterToolbar() {
        var prevBtn = document.getElementById('rosterPrevWeek');
        var nextBtn = document.getElementById('rosterNextWeek');
        var todayBtn = document.getElementById('rosterTodayBtn');
        if (prevBtn) prevBtn.addEventListener('click', function() {
            if (rosterWeekIndex > 1) rosterWeekIndex--;
            else {
                rosterViewDate.setMonth(rosterViewDate.getMonth() - 1);
                var year = rosterViewDate.getFullYear();
                var month1 = rosterViewDate.getMonth() + 1;
                rosterWeekIndex = getWeekMaxIndexForMonth(year, month1);
            }
            loadRosterGrid();
        });
        if (nextBtn) nextBtn.addEventListener('click', function() {
            var year = rosterViewDate.getFullYear();
            var month1 = rosterViewDate.getMonth() + 1;
            var maxWeek = getWeekMaxIndexForMonth(year, month1);
            if (rosterWeekIndex < maxWeek) rosterWeekIndex++;
            else {
                rosterViewDate.setMonth(rosterViewDate.getMonth() + 1);
                rosterWeekIndex = 1;
            }
            loadRosterGrid();
        });
        if (todayBtn) todayBtn.addEventListener('click', function() {
            var now = new Date();
            rosterViewDate = new Date(now.getFullYear(), now.getMonth(), 1);
            rosterWeekIndex = getWeekIndexForDate(now, now.getFullYear(), now.getMonth() + 1);
            loadRosterGrid();
        });
    }

    function sortRosterEmployeesByRoleLevel(employeeList) {
        return (employeeList || []).slice().sort(function(a, b) {
            var levelA = Number(a.roleLevel);
            var levelB = Number(b.roleLevel);
            if (!Number.isFinite(levelA)) levelA = 999999;
            if (!Number.isFinite(levelB)) levelB = 999999;
            if (levelA !== levelB) {
                return levelA - levelB;
            }
            return String(a.name || '').localeCompare(String(b.name || ''), undefined, { sensitivity: 'base' });
        });
    }

    function renderRosterEmployeeCellHtml(emp) {
        var isThirdPartyPersonnel = String(emp.sourceType || '') === 'outsourced';
        var employeeCode = String(emp.employeeCode || emp.employee_code || '').trim();
        var designation = String(emp.designation || '').trim();
        var nameHtml = escapeHtml(emp.name || '');
        if (isThirdPartyPersonnel) {
            nameHtml += ' <span class="badge text-bg-info ms-1 roster-emp-third-party-badge">Third-Party</span>';
        }
        var codeHtml = employeeCode
            ? '<span class="roster-emp-id" title="Employee ID">' + escapeHtml(employeeCode) + '</span>'
            : '<span class="roster-emp-id roster-emp-id-empty" title="Employee ID">—</span>';
        var designationHtml = designation
            ? '<span class="roster-emp-designation" title="Designation">' + escapeHtml(designation) + '</span>'
            : '<span class="roster-emp-designation roster-emp-designation-empty">—</span>';

        return [
            '<div class="roster-emp-cell">',
            '<span class="roster-emp-name">', nameHtml, '</span>',
            '<div class="roster-emp-subrow">',
            codeHtml,
            designationHtml,
            '</div>',
            '</div>'
        ].join('');
    }

    function renderRosterTableFromData() {
        var r = rosterData;
        if (!r || !r.departments || !r.employees) return;

        var depts = r.departments;
        var employees = r.employees;
        var shifts = r.shifts || [];
        var shiftsByEmpDay = {};
        shifts.forEach(function(s) {
            var dayKey = s.rosterDate ? s.rosterDate : rosterDateIso(s.day);
            var k = s.employeeId + '-' + dayKey;
            if (!shiftsByEmpDay[k]) {
                shiftsByEmpDay[k] = [];
            }
            shiftsByEmpDay[k].push(s);
        });

        var year = rosterViewDate.getFullYear();
        var month1 = rosterViewDate.getMonth() + 1;
        var maxWeek = getWeekMaxIndexForMonth(year, month1);
        if (rosterWeekIndex > maxWeek) rosterWeekIndex = maxWeek;
        if (rosterWeekIndex < 1) rosterWeekIndex = 1;

        var days = getWeekDays(year, month1, rosterWeekIndex);
        var holidaysByDate = collectHolidaysByDate(shifts);

        buildTheadRow(days, holidaysByDate);
        updateRosterWeekDisplay();

        var tbody = document.getElementById('rosterTableBody');
        if (!tbody) return;
        tbody.innerHTML = '';

        var dayCount = days.length;
        var colspan = 2 + dayCount;

        var anyDepartmentRendered = false;
        if (depts.length === 0) {
            var emptyTr = document.createElement('tr');
            emptyTr.innerHTML = '<td colspan="' + colspan + '" class="text-center py-5 text-muted">' +
                '<i class="bi bi-info-circle me-2"></i>No records found for this period.</td>';
            tbody.appendChild(emptyTr);
        } else {
            depts.forEach(function(dept) {
                var deptEmployees = sortRosterEmployeesByRoleLevel(employees.filter(function(e) {
                    if (Number(e.departmentId) !== Number(dept.id)) return false;
                    if (rosterPersonnelFilter === 'third_party') return String(e.sourceType || '') === 'outsourced';
                    return String(e.sourceType || '') !== 'outsourced';
                }));
                if (deptEmployees.length === 0) {
                    return;
                }
                anyDepartmentRendered = true;
                var deptTr = document.createElement('tr');
                deptTr.className = 'roster-dept-row';
                deptTr.setAttribute('data-dept-id', String(dept.id));
                deptTr.innerHTML = '<td class="text-center">' +
                    '<button type="button" class="btn btn-sm btn-link p-0 text-dark roster-dept-toggle" data-dept-id="' + dept.id + '" aria-expanded="true" aria-label="Collapse ' + dept.name + '">' +
                    '<i class="bi bi-chevron-down"></i></button></td>' +
                    '<td colspan="' + colspan + '" class="fw-semibold">' + dept.name + '</td>';
                tbody.appendChild(deptTr);

                deptEmployees.forEach(function(emp) {
                    var empRef = emp.id ?? emp.employeeId ?? emp.employee_id ?? '';
                    var tr = document.createElement('tr');
                    tr.className = 'roster-emp-row';
                    tr.setAttribute('data-dept-id', String(dept.id));
                    tr.innerHTML = '<td></td><td class="roster-col-employee-cell">' + renderRosterEmployeeCellHtml(emp) + '</td>';
                    days.forEach(function(d) {
                        var iso = dateToISO(d);
                        var k = empRef + '-' + iso;
                        var rawCellShifts = shiftsByEmpDay[k] || [];
                        var displayShifts = buildDisplayCellShifts(rawCellShifts);
                        var hasActiveShift = displayShifts.some(function(s) {
                            return isRosterWorkingShift(s);
                        });
                        var hasOffDayEntry = displayShifts.some(function(s) {
                            return isRosterOffDayShift(s) && !s.deletedAt;
                        });
                        var hasPublicHoliday = displayShifts.some(function(s) {
                            return !s.deletedAt && s.isPublicHoliday;
                        });

                        var td = document.createElement('td');
                        td.className = 'shift-cell roster-day-cell';
                        if (isRosterHolidayDate(iso, holidaysByDate)) {
                            td.classList.add('roster-day-col-holiday');
                        }

                        td.setAttribute('data-employee-id', String(empRef));
                        td.setAttribute('data-roster-date', iso);
                        td.setAttribute('data-day', String(d.getDate()));

                        if (d.getMonth() !== rosterViewDate.getMonth() || d.getFullYear() !== rosterViewDate.getFullYear()) {
                            td.style.opacity = '0.55';
                        }

                        if (displayShifts.length) {
                            td.setAttribute('data-shifts', JSON.stringify(displayShifts));
                            td.innerHTML = renderCellShiftsHtml(displayShifts);
                        }

                        if (!hasActiveShift && !hasOffDayEntry && !hasPublicHoliday) {
                            td.classList.add('roster-day-cell-empty');
                            td.innerHTML = '<span class="text-muted d-inline-flex align-items-center justify-content-center w-100 roster-day-add"><i class="bi bi-plus-lg"></i></span>';
                        } else if (hasActiveShift || hasOffDayEntry) {
                            td.insertAdjacentHTML('beforeend',
                                '<span class="text-muted d-inline-flex align-items-center justify-content-center w-100 roster-day-add roster-day-add-inline"><i class="bi bi-plus-lg"></i></span>');
                        }
                        tr.appendChild(td);
                    });
                    tbody.appendChild(tr);
                });
            });
            if (!anyDepartmentRendered) {
                var emptyByTabTr = document.createElement('tr');
                var emptyLabel = 'No internal employees found for this period.';
                if (rosterPersonnelFilter === 'third_party') {
                    emptyLabel = 'No third-party personnel found for this period.';
                }
                emptyByTabTr.innerHTML = '<td colspan="' + colspan + '" class="text-center py-5 text-muted">' +
                    '<i class="bi bi-info-circle me-2"></i>' + emptyLabel + '</td>';
                tbody.appendChild(emptyByTabTr);
            }
        }

        document.querySelectorAll('.roster-dept-toggle').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var deptId = this.getAttribute('data-dept-id');
                var expanded = this.getAttribute('aria-expanded') === 'true';
                var rows = document.querySelectorAll('#employeeTable tbody tr.roster-emp-row[data-dept-id="' + deptId + '"]');
                rows.forEach(function(row) { row.classList.toggle('d-none', expanded); });
                this.setAttribute('aria-expanded', !expanded);
                this.querySelector('i').className = expanded ? 'bi bi-chevron-right' : 'bi bi-chevron-down';
            });
        });
    }

    function escapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function loadRosterGrid() {
        var gridUrl = window.rosterGridUrl;
        if (!gridUrl) {
            return;
        }
        var year = rosterViewDate.getFullYear();
        var month = rosterViewDate.getMonth() + 1;
        var url = gridUrl + '?year=' + year + '&month=' + month + '&week=' + rosterWeekIndex
            + '&filter=' + encodeURIComponent(rosterPersonnelFilter)
            + '&include_deleted=' + (rosterShowDeleted ? '1' : '0');

        fetch(url, {
            method: 'GET',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
            .then(function(res) { return res.json(); })
            .then(function(json) {
                if (!json.success) {
                    rosterData = { departments: [], employees: [], shifts: [] };
                    renderRosterTableFromData();
                    return;
                }
                rosterData = json.data;
                renderRosterTableFromData();
                updateRosterApprovalBars(json.data && json.data.meta ? json.data.meta : null);
            })
            .catch(function() {
                rosterData = { departments: [], employees: [], shifts: [] };
                renderRosterTableFromData();
            });
    }

    function formatRosterDateLabel(dateObj) {
        var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return padDay(dateObj.getDate()) + ' ' + months[dateObj.getMonth()] + ' ' + dateObj.getFullYear();
    }

    function formatRosterAuditDateTime(value) {
        if (!value) {
            return '—';
        }
        var normalized = String(value).trim().replace(' ', 'T');
        var dateObj = new Date(normalized);
        if (isNaN(dateObj.getTime())) {
            return '—';
        }
        var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        var hours = dateObj.getHours();
        var minutes = pad2(dateObj.getMinutes());
        var ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12;
        return padDay(dateObj.getDate()) + ' ' + months[dateObj.getMonth()] + ' ' + dateObj.getFullYear()
            + ', ' + pad2(hours) + ':' + minutes + ' ' + ampm;
    }

    var rosterAuditPanelState = {
        events: [],
        filter: 'all',
        tab: 'timeline'
    };
    var rosterAuditControlsBound = false;

    function formatRosterAuditShortDateTime(value) {
        if (!value) {
            return '—';
        }
        var normalized = String(value).trim().replace(' ', 'T');
        var dateObj = new Date(normalized);
        if (isNaN(dateObj.getTime())) {
            return '—';
        }
        var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        var hours = dateObj.getHours();
        var minutes = pad2(dateObj.getMinutes());
        var ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12;
        return padDay(dateObj.getDate()) + ' ' + months[dateObj.getMonth()] + ' · ' + pad2(hours) + ':' + minutes + ' ' + ampm;
    }

    function rosterAuditDisplayValue(value, role) {
        var raw = value === null || value === undefined ? '' : String(value).trim();
        if (raw === '' || raw === '—') {
            return '<span class="roster-audit-value-empty">empty</span>';
        }
        var safe = escapeHtml(raw);
        if (role === 'old') {
            return '<span class="roster-audit-value-old">' + safe + '</span>';
        }
        if (role === 'new') {
            return '<span class="roster-audit-value-new">' + safe + '</span>';
        }
        return safe;
    }

    function rosterAuditEventIcon(type) {
        if (type === 'created') return 'bi-plus-lg';
        if (type === 'assigned') return 'bi-person-check';
        if (type === 'deleted') return 'bi-trash';
        return 'bi-pencil';
    }

    function rosterAuditBadgeLabel(event) {
        if (event.type === 'created') return '+ Created & Assigned';
        return escapeHtml(event.actionLabel || 'Updated');
    }

    function bindRosterAuditPanelControls() {
        if (rosterAuditControlsBound) return;
        var panel = document.getElementById('rosterShiftAuditCard');
        if (!panel) return;
        rosterAuditControlsBound = true;
        panel.addEventListener('click', function(e) {
            var tabBtn = e.target.closest('[data-audit-tab]');
            if (tabBtn) {
                rosterAuditPanelState.tab = tabBtn.getAttribute('data-audit-tab') || 'timeline';
                panel.querySelectorAll('.roster-audit-tab').forEach(function(btn) {
                    var active = btn === tabBtn;
                    btn.classList.toggle('active', active);
                    btn.setAttribute('aria-selected', active ? 'true' : 'false');
                });
                renderRosterAuditHistoryList();
                return;
            }
            var filterBtn = e.target.closest('[data-audit-filter]');
            if (filterBtn) {
                rosterAuditPanelState.filter = filterBtn.getAttribute('data-audit-filter') || 'all';
                panel.querySelectorAll('.roster-audit-filter').forEach(function(btn) {
                    btn.classList.toggle('active', btn === filterBtn);
                });
                renderRosterAuditHistoryList();
            }
        });
    }

    function resetRosterAuditPanel() {
        rosterAuditPanelState.events = [];
        rosterAuditPanelState.filter = 'all';
        rosterAuditPanelState.tab = 'timeline';
        var card = document.getElementById('rosterShiftAuditCard');
        var list = document.getElementById('rosterAuditHistoryList');
        var empty = document.getElementById('rosterAuditHistoryEmpty');
        var loading = document.getElementById('rosterAuditHistoryLoading');
        var scroll = document.getElementById('rosterAuditHistoryScroll');
        if (list) list.innerHTML = '';
        if (empty) {
            empty.style.display = 'none';
            empty.querySelector('span').textContent = 'No history recorded for this shift yet.';
        }
        if (loading) loading.style.display = 'none';
        if (scroll) scroll.style.display = 'none';
        if (card) card.style.display = 'none';
    }

    function updateRosterAuditStats(stats) {
        var s = stats || {};
        var createdEl = document.getElementById('rosterAuditStatCreated');
        var updatedEl = document.getElementById('rosterAuditStatUpdated');
        var removedEl = document.getElementById('rosterAuditStatRemoved');
        if (createdEl) createdEl.textContent = String(s.created || 0);
        if (updatedEl) updatedEl.textContent = String(s.updated || 0);
        if (removedEl) removedEl.textContent = String(s.removed || 0);
    }

    function getFilteredRosterAuditEvents() {
        var events = rosterAuditPanelState.events || [];
        var filter = rosterAuditPanelState.filter || 'all';
        var tab = rosterAuditPanelState.tab || 'timeline';
        return events.filter(function(event) {
            if (filter !== 'all' && event.type !== filter) {
                return false;
            }
            if (tab === 'changes') {
                return Array.isArray(event.changes) && event.changes.length > 0;
            }
            return true;
        });
    }

    function renderRosterAuditHistoryList() {
        var card = document.getElementById('rosterShiftAuditCard');
        var list = document.getElementById('rosterAuditHistoryList');
        var empty = document.getElementById('rosterAuditHistoryEmpty');
        var scroll = document.getElementById('rosterAuditHistoryScroll');
        if (!card || !list) return;

        var filtered = getFilteredRosterAuditEvents();
        list.innerHTML = '';

        if (!filtered.length) {
            if (empty) {
                empty.querySelector('span').textContent = rosterAuditPanelState.events.length
                    ? 'No events match the selected filter.'
                    : 'No history recorded for this shift yet.';
                empty.style.display = 'flex';
            }
            if (scroll) scroll.style.display = 'none';
            return;
        }

        if (empty) empty.style.display = 'none';
        if (scroll) scroll.style.display = 'block';

        filtered.forEach(function(event) {
            var type = event.type || 'updated';
            var li = document.createElement('li');
            li.className = 'roster-audit-event roster-audit-event--' + type;

            var changes = Array.isArray(event.changes) ? event.changes : [];
            var changesHtml = '';
            if (changes.length) {
                changesHtml = '<ul class="roster-audit-change-list">';
                changes.forEach(function(change) {
                    changesHtml += '<li class="roster-audit-change-row">' +
                        '<span class="roster-audit-change-field">' + escapeHtml((change.label || change.field || 'Field').toUpperCase()) + '</span>' +
                        '<div class="roster-audit-change-diff">' +
                        rosterAuditDisplayValue(change.before, 'old') +
                        '<i class="bi bi-arrow-right roster-audit-change-arrow" aria-hidden="true"></i>' +
                        rosterAuditDisplayValue(change.after, 'new') +
                        '</div>' +
                        '</li>';
                });
                changesHtml += '</ul>';
            }

            var summary = event.summary ? '<p class="roster-audit-event-summary">' + escapeHtml(event.summary) + '</p>' : '';

            li.innerHTML =
                '<div class="roster-audit-event-marker">' +
                '<span class="roster-audit-event-icon"><i class="bi ' + rosterAuditEventIcon(type) + '"></i></span>' +
                '</div>' +
                '<div class="roster-audit-event-body">' +
                '<div class="roster-audit-event-top">' +
                '<div class="roster-audit-event-top-main">' +
                '<span class="roster-audit-event-badge">' + rosterAuditBadgeLabel(event) + '</span>' +
                '<span class="roster-audit-event-user">' + escapeHtml(event.userName || 'System') + '</span>' +
                '</div>' +
                '<span class="roster-audit-event-time">' + escapeHtml(formatRosterAuditShortDateTime(event.at)) + '</span>' +
                '</div>' +
                summary +
                changesHtml +
                '</div>';

            list.appendChild(li);
        });
    }

    function applyRosterAuditPayload(payload) {
        if (Array.isArray(payload)) {
            rosterAuditPanelState.events = payload;
            updateRosterAuditStats({
                created: payload.filter(function(e) { return e.type === 'created' || e.action === 'created'; }).length,
                updated: payload.filter(function(e) { return e.type === 'updated' || e.action === 'updated'; }).length,
                removed: payload.filter(function(e) { return e.type === 'deleted' || e.action === 'deleted'; }).length
            });
            return;
        }
        rosterAuditPanelState.events = payload.events || [];
        updateRosterAuditStats(payload.stats || {});
        var subtitleEl = document.getElementById('rosterAuditHistorySubtitle');
        if (subtitleEl && payload.subtitle) {
            subtitleEl.textContent = payload.subtitle;
        }
    }

    function loadRosterAuditHistory(rosterId) {
        var base = window.rosterChangeHistoryUrlBase || window.rosterUpdateUrlBase || '';
        var card = document.getElementById('rosterShiftAuditCard');
        var list = document.getElementById('rosterAuditHistoryList');
        var empty = document.getElementById('rosterAuditHistoryEmpty');
        var loading = document.getElementById('rosterAuditHistoryLoading');
        var scroll = document.getElementById('rosterAuditHistoryScroll');

        bindRosterAuditPanelControls();
        resetRosterAuditPanel();

        if (!base || !rosterId || !card) {
            return;
        }

        card.style.display = 'block';
        if (loading) loading.style.display = 'flex';
        if (empty) empty.style.display = 'none';
        if (scroll) scroll.style.display = 'none';
        if (list) list.innerHTML = '';

        fetch(base + '/' + encodeURIComponent(rosterId) + '/change-history', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
            .then(function(r) {
                return r.json().then(function(body) {
                    return { ok: r.ok, body: body };
                });
            })
            .then(function(res) {
                if (loading) loading.style.display = 'none';
                if (res.ok && res.body && res.body.success) {
                    applyRosterAuditPayload(res.body.data || {});
                    renderRosterAuditHistoryList();
                    return;
                }
                if (empty) {
                    empty.querySelector('span').textContent = 'Could not load change history.';
                    empty.style.display = 'flex';
                }
            })
            .catch(function() {
                if (loading) loading.style.display = 'none';
                if (empty) {
                    empty.querySelector('span').textContent = 'Could not load change history.';
                    empty.style.display = 'flex';
                }
            });
    }

    function clearRosterFloorFieldError() {
        var floorEl = document.getElementById('rosterFloor');
        var errorEl = document.getElementById('rosterFloorError');
        if (errorEl) {
            errorEl.textContent = '';
            errorEl.classList.add('d-none');
        }
        if (floorEl) {
            floorEl.classList.remove('is-invalid');
        }
    }

    function clearRosterLocationFieldError() {
        var locationEl = document.getElementById('rosterLocation');
        var errorEl = document.getElementById('rosterLocationError');
        if (errorEl) {
            errorEl.textContent = '';
            errorEl.classList.add('d-none');
        }
        if (locationEl) {
            locationEl.classList.remove('is-invalid');
        }
    }

    function showRosterLocationFieldError(message) {
        var locationEl = document.getElementById('rosterLocation');
        var errorEl = document.getElementById('rosterLocationError');
        if (errorEl) {
            errorEl.textContent = message || 'Please enter a valid location.';
            errorEl.classList.remove('d-none');
        }
        if (locationEl) {
            locationEl.classList.add('is-invalid');
        }
    }

    function validateRosterLocationField() {
        var locationEl = document.getElementById('rosterLocation');
        if (!locationEl) {
            return true;
        }
        var value = (locationEl.value || '').trim();
        clearRosterLocationFieldError();
        if (value === '') {
            return true;
        }
        if (value.length < 3) {
            showRosterLocationFieldError('Location must be at least 3 characters.');
            return false;
        }
        if (value.length > 15) {
            showRosterLocationFieldError('Location may not be greater than 15 characters.');
            return false;
        }
        if (/^\d+$/.test(value)) {
            showRosterLocationFieldError('Location cannot contain only digits.');
            return false;
        }
        if (!/[A-Za-z]/.test(value)) {
            showRosterLocationFieldError('Location must contain at least one letter.');
            return false;
        }
        if (!/^[A-Za-z0-9\s\-'.]+$/.test(value)) {
            showRosterLocationFieldError('Location may only contain letters, numbers, spaces, hyphens, apostrophes, or periods.');
            return false;
        }
        return true;
    }

    function clearRosterShiftFieldErrors() {
        ['#rosterShiftPlannerId', '#rosterStartTime', '#rosterEndTime'].forEach(function(sel) {
            var el = document.querySelector(sel);
            if (el) el.classList.remove('is-invalid');
        });
        ['#rosterShiftPlannerError', '#rosterStartTimeError', '#rosterEndTimeError'].forEach(function(sel) {
            var el = document.querySelector(sel);
            if (el) {
                el.textContent = '';
                el.classList.add('d-none');
            }
        });
    }

    function showRosterShiftFieldError(fieldSel, errorSel, message) {
        var field = document.querySelector(fieldSel);
        var error = document.querySelector(errorSel);
        if (field) field.classList.add('is-invalid');
        if (error) {
            error.textContent = message;
            error.classList.remove('d-none');
        }
    }

    function rosterUsesCustomTime() {
        var cb = document.getElementById('rosterUseCustomTime');
        return cb ? cb.checked : false;
    }

    function toggleRosterCustomTimeUi() {
        var useCustom = rosterUsesCustomTime();
        var timeRow = document.getElementById('rosterShiftTimeRow');
        var shiftSelect = document.getElementById('rosterShiftPlannerId');
        var requiredMark = document.getElementById('rosterShiftRequiredMark');

        if (timeRow) {
            timeRow.style.display = useCustom ? '' : 'none';
        }
        if (shiftSelect) {
            shiftSelect.required = !useCustom;
        }
        if (requiredMark) {
            requiredMark.style.display = useCustom ? 'none' : '';
        }

        if (!useCustom) {
            var startEl = document.getElementById('rosterStartTime');
            var endEl = document.getElementById('rosterEndTime');
            if (startEl) startEl.value = '';
            if (endEl) endEl.value = '';
        }

        clearRosterShiftFieldErrors();
    }

    function validateRosterShiftFields() {
        clearRosterShiftFieldErrors();
        var shiftId = document.getElementById('rosterShiftPlannerId')?.value || '';
        var useCustom = rosterUsesCustomTime();
        var startTime = document.getElementById('rosterStartTime')?.value || '';
        var endTime = document.getElementById('rosterEndTime')?.value || '';
        var valid = true;

        if (useCustom) {
            if (!startTime) {
                showRosterShiftFieldError('#rosterStartTime', '#rosterStartTimeError', 'Start time is required.');
                valid = false;
            }
            if (!endTime) {
                showRosterShiftFieldError('#rosterEndTime', '#rosterEndTimeError', 'End time is required.');
                valid = false;
            }
            if (startTime && endTime && startTime === endTime) {
                showRosterShiftFieldError('#rosterEndTime', '#rosterEndTimeError', 'End time must be different from start time.');
                valid = false;
            }
        } else if (!shiftId) {
            showRosterShiftFieldError('#rosterShiftPlannerId', '#rosterShiftPlannerError', 'Please select a shift.');
            valid = false;
        }

        if (!validateRosterLocationField()) {
            valid = false;
        }

        return valid;
    }

    function showRosterFloorFieldError(message) {
        var floorEl = document.getElementById('rosterFloor');
        var errorEl = document.getElementById('rosterFloorError');
        if (errorEl) {
            errorEl.textContent = message || 'Please select a valid floor.';
            errorEl.classList.remove('d-none');
        }
        if (floorEl) {
            floorEl.classList.add('is-invalid');
        }
    }

    function populateRosterFloorOptions(options, selectedFloorId, legacyFloorLabel) {
        var floorEl = document.getElementById('rosterFloor');
        if (!floorEl) {
            return;
        }

        var selectedValue = selectedFloorId ? String(selectedFloorId) : '';
        var floorOptions = options || [];
        var html = '<option value="">Select floor (optional)</option>';

        floorOptions.forEach(function(option) {
            html += '<option value="' + String(option.id) + '">' + option.label + '</option>';
        });

        if (legacyFloorLabel && !selectedValue) {
            var matchingOption = floorOptions.find(function(option) {
                return option.label === legacyFloorLabel;
            });
            if (matchingOption) {
                selectedValue = String(matchingOption.id);
            }
        }

        floorEl.innerHTML = html;
        floorEl.value = selectedValue || '';
    }

    function loadRosterFloorOptions(employeeType, employeeSourceId, selectedFloorId, legacyFloorLabel) {
        var url = window.rosterFloorOptionsUrl || '';
        clearRosterFloorFieldError();

        if (!url) {
            populateRosterFloorOptions([], selectedFloorId, legacyFloorLabel);
            return Promise.resolve();
        }

        var params = new URLSearchParams({
            employee_type: employeeType,
            employee_id: String(employeeSourceId)
        });

        return fetch(url + '?' + params.toString(), {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
            .then(function(response) {
                return response.json().then(function(body) {
                    return { ok: response.ok, status: response.status, body: body };
                });
            })
            .then(function(result) {
                var options = (result.ok && result.body && result.body.success && Array.isArray(result.body.data))
                    ? result.body.data
                    : [];
                populateRosterFloorOptions(options, selectedFloorId, legacyFloorLabel);
                if (!result.ok) {
                    showRosterFloorFieldError((result.body && result.body.message) ? result.body.message : 'Could not load floor options.');
                }
            })
            .catch(function() {
                populateRosterFloorOptions([], selectedFloorId, legacyFloorLabel);
                showRosterFloorFieldError('Could not load floor options.');
            });
    }

    function isRosterOffDayShift(shift) {
        if (!shift) {
            return false;
        }
        if (shift.isLeave && shift.isHalfDayLeave) {
            return false;
        }
        if (shift.isOffDay) {
            return true;
        }
        var status = String(shift.status || '').toLowerCase();
        return status === 'off' || shift.shiftType === 'off';
    }

    function isRosterWorkingShift(s) {
        if (!s || s.deletedAt) {
            return false;
        }
        return !isRosterOffDayShift(s) && !s.isPublicHoliday;
    }

    function setRosterShiftCanvasViewMode(mode) {
        var assignmentSection = document.getElementById('rosterShiftAssignmentSection');
        var offDayPanel = document.getElementById('rosterShiftOffDayPanel');
        var pendingSyncPanel = document.getElementById('rosterShiftPendingSyncPanel');
        var saveBtn = document.getElementById('rosterShiftSaveBtn');
        var saveBtnText = document.getElementById('rosterShiftSaveBtnText');
        var shiftSelect = document.getElementById('rosterShiftPlannerId');
        var useCustomCb = document.getElementById('rosterUseCustomTime');
        var startTimeEl = document.getElementById('rosterStartTime');
        var endTimeEl = document.getElementById('rosterEndTime');
        var notesEl = document.getElementById('rosterShiftNotes');
        var floorEl = document.getElementById('rosterFloor');
        var locationEl = document.getElementById('rosterLocation');
        var isOffConvert = mode === 'off-convert';
        var isDeleted = mode === 'deleted';
        var isReview = mode === 'review';
        var disableFields = isDeleted || isReview;

        if (assignmentSection) {
            assignmentSection.style.display = isDeleted ? 'none' : '';
        }
        if (offDayPanel) {
            offDayPanel.style.display = isOffConvert ? '' : 'none';
        }
        if (pendingSyncPanel) {
            pendingSyncPanel.style.display = 'none';
        }
        if (saveBtn) {
            saveBtn.style.display = (isDeleted || isReview) ? 'none' : 'inline-block';
        }
        if (saveBtnText && mode === 'create') {
            saveBtnText.textContent = 'Save';
        } else if (saveBtnText && isOffConvert) {
            saveBtnText.textContent = 'Convert to Shift';
        } else if (saveBtnText && mode === 'edit') {
            saveBtnText.textContent = 'Update';
        }

        [shiftSelect, useCustomCb, startTimeEl, endTimeEl, notesEl, floorEl, locationEl].forEach(function(el) {
            if (el) {
                el.disabled = disableFields;
            }
        });
    }

    function openRosterShiftCanvas(employeeId, employeeName, deptName, rosterDate, shift) {
        if (rosterGmApprovalContext && !shift) {
            return;
        }
        var canvas = document.getElementById('rosterShiftCanvas');
        var titleEl = document.getElementById('rosterShiftCanvasTitle');
        var deleteWrap = document.getElementById('rosterShiftDeleteWrap');
        var markOffWrap = document.getElementById('rosterShiftMarkOffWrap');
        var saveBtnText = document.getElementById('rosterShiftSaveBtnText');
        var rosterIdEl = document.getElementById('rosterShiftRosterId');
        var shiftSelect = document.getElementById('rosterShiftPlannerId');
        var notesEl = document.getElementById('rosterShiftNotes');
        var startTimeEl = document.getElementById('rosterStartTime');
        var endTimeEl = document.getElementById('rosterEndTime');
        var checkInEl = document.getElementById('rosterCheckIn');
        var checkOutEl = document.getElementById('rosterCheckOut');
        var floorEl = document.getElementById('rosterFloor');
        var locationEl = document.getElementById('rosterLocation');
        var lateCheckInEl = document.getElementById('rosterLateCheckIn');
        var employeeTypeEl = document.getElementById('rosterShiftEmployeeType');
        var auditCard = document.getElementById('rosterShiftAuditCard');

        if (!canvas) return;
        var employeeRef = String(employeeId || '');
        var refParts = employeeRef.split(':');
        var employeeType = (refParts.length === 2) ? refParts[0] : 'employee';
        var employeeSourceId = (refParts.length === 2) ? refParts[1] : employeeRef;
        document.getElementById('rosterShiftEmployeeId').value = employeeSourceId;
        if (employeeTypeEl) employeeTypeEl.value = employeeType;
        document.getElementById('rosterShiftDay').value = rosterDate;
        document.getElementById('rosterShiftEmployeeName').textContent = employeeName;
        var deptNameStr = deptName || '';
        document.getElementById('rosterShiftDepartmentName').textContent = deptNameStr;
        var deptWrap = document.getElementById('rosterShiftDepartmentWrap');
        if (deptWrap) {
            deptWrap.style.display = String(deptNameStr).trim() ? 'block' : 'none';
        }
        var initialBox = document.getElementById('rosterShiftEmployeeInitial');
        if (initialBox) {
            var nm = String(employeeName || '').trim();
            initialBox.textContent = nm ? nm.charAt(0).toUpperCase() : '?';
        }
        var dateObj = parseISODate(rosterDate);
        if (!isNaN(dateObj.getTime())) {
            document.getElementById('rosterShiftDateLabel').textContent = formatRosterDateLabel(dateObj);
        } else {
            document.getElementById('rosterShiftDateLabel').textContent = rosterDate;
        }
        var iconEl = document.getElementById('rosterShiftCanvasIcon');
        var isOffDayEntry = isRosterOffDayShift(shift);
        var isAwaitingGmApproval = !!(shift && shift.isAwaitingGmApproval);
        if (shift) {
            if (rosterIdEl) rosterIdEl.value = shift.rosterId || '';
            if (isOffDayEntry) {
                if (iconEl) iconEl.innerHTML = '<i class="bi bi-calendar-x me-2"></i>';
                if (titleEl) titleEl.textContent = 'Off Day';
            } else {
                if (iconEl) iconEl.innerHTML = '<i class="bi bi-pencil-square me-2"></i>';
                if (titleEl) titleEl.textContent = 'Edit Shift';
            }
            if (saveBtnText && !isOffDayEntry) saveBtnText.textContent = 'Update';
            if (deleteWrap) deleteWrap.style.display = 'block';
            if (markOffWrap) markOffWrap.style.display = isOffDayEntry ? 'none' : 'block';
            var isCustomEntry = !!(shift.isCustomTime || shift.is_custom_time);
            if (shiftSelect) {
                if (isCustomEntry || isOffDayEntry) {
                    shiftSelect.value = '';
                } else {
                    shiftSelect.value = String(shift.shiftPlannerId || shift.shift_planner_id || '');
                }
            }
            if (startTimeEl) startTimeEl.value = isOffDayEntry ? '' : (shift.timeStart || '');
            if (endTimeEl) endTimeEl.value = isOffDayEntry ? '' : (shift.timeEnd || '');
            if (checkInEl) checkInEl.value = shift.checkIn || '';
            if (checkOutEl) checkOutEl.value = shift.checkOut || '';
            if (lateCheckInEl) lateCheckInEl.checked = !!shift.lateCheckIn;
            if (notesEl) notesEl.value = isOffDayEntry ? '' : (shift.notes || '');
            if (locationEl) locationEl.value = isOffDayEntry ? '' : (shift.location || '');

            if (shift.rosterId) {
                loadRosterAuditHistory(shift.rosterId);
            } else {
                resetRosterAuditPanel();
            }
        } else {
            if (rosterIdEl) rosterIdEl.value = '';
            if (iconEl) iconEl.innerHTML = '<i class="bi bi-plus-circle me-2"></i>';
            if (titleEl) titleEl.textContent = 'Add Shift';
            if (saveBtnText) saveBtnText.textContent = 'Save';
            if (deleteWrap) deleteWrap.style.display = 'none';
            if (markOffWrap) markOffWrap.style.display = 'none';
            if (shiftSelect) shiftSelect.value = '';
            if (startTimeEl) startTimeEl.value = '';
            if (endTimeEl) endTimeEl.value = '';
            var useCustomReset = document.getElementById('rosterUseCustomTime');
            if (useCustomReset) useCustomReset.checked = false;
            if (checkInEl) checkInEl.value = '';
            if (checkOutEl) checkOutEl.value = '';
            if (lateCheckInEl) lateCheckInEl.checked = false;
            if (notesEl) notesEl.value = '';
            if (locationEl) locationEl.value = '';
            resetRosterAuditPanel();
        }

        var useCustomCb = document.getElementById('rosterUseCustomTime');
        if (useCustomCb) {
            useCustomCb.checked = !!(shift && !isOffDayEntry && (shift.isCustomTime || shift.is_custom_time));
        }
        toggleRosterCustomTimeUi();

        if (rosterGmApprovalContext && shift) {
            if (isOffDayEntry) {
                if (titleEl) titleEl.textContent = 'Off Day';
                setRosterShiftCanvasViewMode('off-convert');
                if (markOffWrap) markOffWrap.style.display = 'none';
            } else {
                if (titleEl) titleEl.textContent = 'Edit Shift';
                if (iconEl) iconEl.innerHTML = '<i class="bi bi-pencil-square me-2"></i>';
                setRosterShiftCanvasViewMode('edit');
                if (deleteWrap) deleteWrap.style.display = 'block';
                if (markOffWrap) markOffWrap.style.display = 'block';
            }
            var gmPendingSyncPanel = document.getElementById('rosterShiftPendingSyncPanel');
            if (gmPendingSyncPanel) {
                gmPendingSyncPanel.style.display = '';
            }
        } else if (shift && shift.deletedAt) {
            if (titleEl) titleEl.textContent = 'View Deleted Shift';
            setRosterShiftCanvasViewMode('deleted');
            if (deleteWrap) deleteWrap.style.display = 'none';
            if (markOffWrap) markOffWrap.style.display = 'none';
        } else if (isOffDayEntry) {
            setRosterShiftCanvasViewMode('off-convert');
            if (markOffWrap) markOffWrap.style.display = 'none';
        } else {
            setRosterShiftCanvasViewMode(shift ? 'edit' : 'create');
            if (deleteWrap && shift) {
                deleteWrap.style.display = 'block';
            }
            if (markOffWrap) {
                markOffWrap.style.display = shift ? 'block' : 'none';
            }
            var pendingSyncPanel = document.getElementById('rosterShiftPendingSyncPanel');
            if (pendingSyncPanel) {
                pendingSyncPanel.style.display = isAwaitingGmApproval ? '' : 'none';
            }
        }

        var selectedFloorId = shift ? (shift.sbuFloorId || shift.sbu_floor_id || null) : null;
        var legacyFloorLabel = shift ? (shift.floor || '') : '';

        loadRosterFloorOptions(employeeType, employeeSourceId, selectedFloorId, legacyFloorLabel).then(function() {
            var offcanvas = bootstrap.Offcanvas.getOrCreateInstance(canvas);
            offcanvas.show();
        });
    }

    function saveRosterAssignment() {
        var base = window.rosterUpdateUrlBase || '';
        var storeUrl = window.rosterStoreUrl || '';
        if (!base || !storeUrl) return;

        var employeeId = document.getElementById('rosterShiftEmployeeId').value;
        var employeeType = document.getElementById('rosterShiftEmployeeType')?.value || 'employee';
        var rosterDate = document.getElementById('rosterShiftDay').value;
        var shiftPlannerId = document.getElementById('rosterShiftPlannerId').value;
        var rosterId = document.getElementById('rosterShiftRosterId').value;
        var notes = document.getElementById('rosterShiftNotes').value.trim();
        var floorSelect = document.getElementById('rosterFloor');
        var locationInput = document.getElementById('rosterLocation');
        var sbuFloorId = floorSelect && floorSelect.value ? parseInt(floorSelect.value, 10) : null;
        var locationText = locationInput ? locationInput.value.trim() : '';

        var parsedEmployeeId = parseInt(employeeId, 10);
        if (!employeeId || !rosterDate || !Number.isFinite(parsedEmployeeId) || parsedEmployeeId <= 0) {
            showError('Employee is required.');
            return;
        }

        if (!validateRosterShiftFields()) {
            return;
        }

        var useCustom = rosterUsesCustomTime();
        var startTimeVal = useCustom ? (document.getElementById('rosterStartTime')?.value || '') : '';
        var endTimeVal = useCustom ? (document.getElementById('rosterEndTime')?.value || '') : '';

        var payload = {
            employee_id: parsedEmployeeId,
            employee_type: employeeType,
            roster_date: rosterDate,
            sbu_floor_id: Number.isFinite(sbuFloorId) && sbuFloorId > 0 ? sbuFloorId : null,
            late_check_in: document.getElementById('rosterLateCheckIn')?.checked ? 1 : 0
        };

        payload.is_custom_time = useCustom ? 1 : 0;
        if (!useCustom && shiftPlannerId) {
            payload.shift_planner_id = parseInt(shiftPlannerId, 10);
        }
        if (startTimeVal) {
            payload.start_time = startTimeVal;
        }
        if (endTimeVal) {
            payload.end_time = endTimeVal;
        }
        payload.notes = notes;
        payload.location_text = locationText === '' ? null : locationText;

        clearRosterFloorFieldError();
        clearRosterLocationFieldError();

        var url = rosterId ? (base + '/' + rosterId) : storeUrl;
        var saveBtn = document.getElementById('rosterShiftSaveBtn');
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
        }

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload),
            credentials: 'same-origin'
        })
            .then(function(r) { 
                return r.text().then(function(t) {
                    var j = {};
                    try { j = JSON.parse(t); } catch(ex) { console.error("Invalid JSON response:", t); }
                    return { ok: r.ok, status: r.status, body: j };
                });
            })
            .then(function(res) {
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i><span id="rosterShiftSaveBtnText">' + (rosterId ? 'Update' : 'Save') + '</span>';
                }
                
                if (res.ok && res.body.success) {
                    showSuccess(res.body.message);
                    var canvas = document.getElementById('rosterShiftCanvas');
                    if (canvas) {
                        var o = bootstrap.Offcanvas.getInstance(canvas);
                        if (o) o.hide();
                    }
                    loadRosterGrid();
                } else {
                    var msg = 'Could not save assignment.';
                    if (res.body && res.body.message) msg = res.body.message;
                    if (res.status === 422 && res.body.errors) {
                        var floorErrors = res.body.errors.sbu_floor_id;
                        if (floorErrors && floorErrors.length) {
                            showRosterFloorFieldError(floorErrors[0]);
                        }
                        var locationErrors = res.body.errors.location_text;
                        if (locationErrors && locationErrors.length) {
                            showRosterLocationFieldError(locationErrors[0]);
                        }
                        if (res.body.errors.shift_planner_id) {
                            showRosterShiftFieldError('#rosterShiftPlannerId', '#rosterShiftPlannerError', res.body.errors.shift_planner_id[0]);
                        }
                        if (res.body.errors.start_time) {
                            showRosterShiftFieldError('#rosterStartTime', '#rosterStartTimeError', res.body.errors.start_time[0]);
                        }
                        if (res.body.errors.end_time) {
                            showRosterShiftFieldError('#rosterEndTime', '#rosterEndTimeError', res.body.errors.end_time[0]);
                        }
                        msg = Object.values(res.body.errors).flat().join('<br>');
                    }
                    
                    showError(msg);
                }
            })
            .catch(function(err) {
                console.error("AJAX Error:", err);
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i><span id="rosterShiftSaveBtnText">' + (rosterId ? 'Update' : 'Save') + '</span>';
                }
                showError('Network error or script failure.');
            });
    }

    function markRosterAsOff() {
        var base = window.rosterUpdateUrlBase || '';
        var rosterId = document.getElementById('rosterShiftRosterId').value;
        if (!base || !rosterId) return;

        var employeeId = document.getElementById('rosterShiftEmployeeId').value;
        var employeeType = document.getElementById('rosterShiftEmployeeType')?.value || 'employee';
        var rosterDate = document.getElementById('rosterShiftDay').value;
        var parsedEmployeeId = parseInt(employeeId, 10);

        if (!employeeId || !rosterDate || !Number.isFinite(parsedEmployeeId) || parsedEmployeeId <= 0) {
            showError('Employee is required.');
            return;
        }

        var url = base + '/' + rosterId;
        var markOffBtn = document.getElementById('rosterShiftMarkOffBtn');
        if (markOffBtn) markOffBtn.disabled = true;

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                employee_id: parsedEmployeeId,
                employee_type: employeeType,
                roster_date: rosterDate,
                mark_as_off: 1
            }),
            credentials: 'same-origin'
        })
            .then(function(r) {
                return r.text().then(function(t) {
                    var j = {};
                    try { j = JSON.parse(t || '{}'); } catch (ex) { j = {}; }
                    return { ok: r.ok, status: r.status, body: j };
                });
            })
            .then(function(res) {
                if (markOffBtn) markOffBtn.disabled = false;
                if (res.ok && res.body.success) {
                    showSuccess(res.body.message || 'Day marked as off.');
                    var canvas = document.getElementById('rosterShiftCanvas');
                    if (canvas) {
                        var o = bootstrap.Offcanvas.getInstance(canvas);
                        if (o) o.hide();
                    }
                    loadRosterGrid();
                } else {
                    var msg = (res.body && res.body.message) ? res.body.message : 'Could not mark day as off.';
                    showError(msg);
                }
            })
            .catch(function() {
                if (markOffBtn) markOffBtn.disabled = false;
                showError('Could not mark day as off.');
            });
    }

    function deleteRosterAssignment() {
        var base = window.rosterUpdateUrlBase || '';
        var rosterId = document.getElementById('rosterShiftRosterId').value;
        if (!base || !rosterId) return;

        var url = base + '/' + rosterId;
        var delBtn = document.getElementById('rosterShiftDeleteBtn');
        if (delBtn) delBtn.disabled = true;

        fetch(url, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
            .then(function(r) { return r.json().then(function(j) { return { ok: r.ok, body: j }; }); })
            .then(function(res) {
                if (delBtn) delBtn.disabled = false;
                if (res.ok && res.body.success) {
                    if (typeof showSuccess === 'function' && res.body.message) {
                        showSuccess(res.body.message);
                    }
                    var canvas = document.getElementById('rosterShiftCanvas');
                    if (canvas) {
                        var o = bootstrap.Offcanvas.getInstance(canvas);
                        if (o) o.hide();
                    }
                    loadRosterGrid();
                } else {
                    var msg = (res.body && res.body.message) ? res.body.message : 'Could not remove assignment.';
                    showError(msg);
                }
            })
            .catch(function() {
                if (delBtn) delBtn.disabled = false;
            });
    }

    function bindRosterCanvasAndCells() {
        var table = document.getElementById('employeeTable');
        if (table && !table._rosterCellBound) {
            table._rosterCellBound = true;
            table.addEventListener('click', function(e) {
                var pillHit = e.target.closest('.shift-pill-hit');
                var td = pillHit ? pillHit.closest('.roster-day-cell') : e.target.closest('.roster-day-cell');
                if (!td) return;
                e.preventDefault();
                var employeeId = td.getAttribute('data-employee-id');
                var rosterDate = td.getAttribute('data-roster-date');
                var shift = null;
                if (pillHit) {
                    var pillShiftData = pillHit.getAttribute('data-shift');
                    if (pillShiftData) {
                        try {
                            shift = JSON.parse(decodeURIComponent(pillShiftData));
                        } catch (err) {
                            try { shift = JSON.parse(pillShiftData); } catch (err2) { shift = null; }
                        }
                    }
                } else {
                    var shiftsJson = td.getAttribute('data-shifts');
                    if (shiftsJson) {
                        try {
                            var cellShiftList = JSON.parse(shiftsJson);
                            if (Array.isArray(cellShiftList) && cellShiftList.length === 1) {
                                shift = cellShiftList[0];
                            } else if (Array.isArray(cellShiftList) && cellShiftList.length > 1) {
                                shift = cellShiftList.find(function(s) {
                                    return isRosterOffDayShift(s) && !s.deletedAt;
                                }) || cellShiftList.find(function(s) {
                                    return !s.deletedAt && !s.isPublicHoliday;
                                }) || null;
                            }
                        } catch (ignoreShiftParse) {
                            shift = null;
                        }
                    }
                }

                if (shift && shift.isPublicHoliday) {
                    shift = null;
                }

                if (!pillHit && e.target.closest('.roster-day-add')) {
                    shift = null;
                }

                var empName = '';
                var deptName = '';
                if (rosterData && rosterData.employees) {
                    var emp = rosterData.employees.find(function(e) {
                        var ref = e.id ?? e.employeeId ?? e.employee_id ?? '';
                        return String(ref) === String(employeeId);
                    });
                    if (emp) {
                        empName = emp.name;
                        deptName = emp.departmentName;
                    }
                }
                
                if (!empName) {
                    var row = td.closest('tr');
                    if (row) {
                        var nameCell = row.querySelector('td:nth-child(2)');
                        if (nameCell) empName = nameCell.textContent.trim();
                    }
                }
                openRosterShiftCanvas(employeeId, empName, deptName, rosterDate, shift);
            });
        }
        var saveBtn = document.getElementById('rosterShiftSaveBtn');
        if (saveBtn && !saveBtn._rosterBound) {
            saveBtn._rosterBound = true;
            saveBtn.addEventListener('click', function() {
                var form = document.getElementById('rosterShiftForm');
                if (form && form.checkValidity()) saveRosterAssignment();
                else if (form) form.reportValidity();
            });
        }
        var deleteBtn = document.getElementById('rosterShiftDeleteBtn');
        if (deleteBtn && !deleteBtn._rosterBound) {
            deleteBtn._rosterBound = true;
            deleteBtn.addEventListener('click', function() {
                Swal.fire({
                    title: 'Remove shift?',
                    text: 'Are you sure?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, remove',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then(function(result) {
                    if (result.isConfirmed) {
                        deleteRosterAssignment();
                    }
                });
            });
        }
        var markOffBtn = document.getElementById('rosterShiftMarkOffBtn');
        if (markOffBtn && !markOffBtn._rosterBound) {
            markOffBtn._rosterBound = true;
            markOffBtn.addEventListener('click', function() {
                var dateLabel = document.getElementById('rosterShiftDateLabel');
                var employeeName = document.getElementById('rosterShiftEmployeeName');
                var dateText = dateLabel ? dateLabel.textContent.trim() : 'this date';
                var nameText = employeeName ? employeeName.textContent.trim() : 'this employee';
                Swal.fire({
                    title: 'Mark as off?',
                    text: 'Mark ' + dateText + ' as off for ' + nameText + '? The shift assignment will be replaced with an off day.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ffc107',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, mark as off',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then(function(result) {
                    if (result.isConfirmed) {
                        markRosterAsOff();
                    }
                });
            });
        }
        var plannerSelect = document.getElementById('rosterShiftPlannerId');
        if (plannerSelect && !plannerSelect._rosterBound) {
            plannerSelect._rosterBound = true;
            plannerSelect.addEventListener('change', function() {
                if (!rosterUsesCustomTime()) {
                    return;
                }
                var opt = this.options[this.selectedIndex];
                var startEl = document.getElementById('rosterStartTime');
                var endEl = document.getElementById('rosterEndTime');
                if (opt && opt.value) {
                    var start = opt.getAttribute('data-start');
                    var end = opt.getAttribute('data-end');
                    if (start && startEl) startEl.value = start;
                    if (end && endEl) endEl.value = end;
                }
            });
        }
        var rosterUseCustomTimeEl = document.getElementById('rosterUseCustomTime');
        if (rosterUseCustomTimeEl && !rosterUseCustomTimeEl._rosterBound) {
            rosterUseCustomTimeEl._rosterBound = true;
            rosterUseCustomTimeEl.addEventListener('change', toggleRosterCustomTimeUi);
        }
    }

    function updateRosterApprovalBars(meta) {
        var applyBar = document.getElementById('rosterApplyApprovalBar');
        var gmBar = document.getElementById('rosterGmApprovalBar');
        var summaryEl = document.getElementById('rosterDraftPendingSummary');
        var draftCount = meta && meta.draftPendingCount ? parseInt(meta.draftPendingCount, 10) : 0;
        var canApply = !!(meta && meta.canApplyForApproval);

        if (rosterGmApprovalContext) {
            if (applyBar) applyBar.classList.add('d-none');
            if (gmBar) gmBar.classList.remove('d-none');
            return;
        }

        if (gmBar) gmBar.classList.add('d-none');
        if (!applyBar) return;

        if (canApply && draftCount > 0) {
            applyBar.classList.remove('d-none');
            if (summaryEl) {
                summaryEl.textContent = draftCount + ' pending shift' + (draftCount === 1 ? '' : 's') + ' ready to submit for GM approval.';
            }
        } else {
            applyBar.classList.add('d-none');
        }
    }

    function applyForRosterApproval() {
        var applyUrl = window.rosterApplyForApprovalUrl || '';
        if (!applyUrl) {
            showError('Apply for approval URL is not configured.');
            return;
        }

        var btn = document.getElementById('rosterApplyForApprovalBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';
        }

        fetch(applyUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                year: rosterViewDate.getFullYear(),
                month: rosterViewDate.getMonth() + 1,
                employee_group: rosterPersonnelFilter === 'third_party' ? 'third_party' : 'internal'
            }),
            credentials: 'same-origin'
        })
            .then(function(res) {
                return res.text().then(function(bodyText) {
                    var body = {};
                    try { body = JSON.parse(bodyText || '{}'); } catch (e) { body = {}; }
                    return { ok: res.ok, body: body };
                });
            })
            .then(function(result) {
                if (result.ok && result.body.success) {
                    showSuccess(result.body.message || 'Roster submitted for approval.');
                    loadRosterGrid();
                } else {
                    showError((result.body && result.body.message) || 'Could not submit roster for approval.');
                }
            })
            .catch(function() {
                showError('Could not submit roster for approval.');
            })
            .finally(function() {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-send-check me-1"></i>Apply for Approval';
                }
            });
    }

    function showGmApprovalBar(data) {
        var gmBar = document.getElementById('rosterGmApprovalBar');
        var titleEl = document.getElementById('rosterGmApprovalTitle');
        var summaryEl = document.getElementById('rosterGmApprovalSummary');
        if (titleEl) {
            titleEl.textContent = 'Review roster: ' + (data.assignee_name || 'Employee');
        }
        if (summaryEl) {
            var scopeLabel = data.segment_scope_label ? data.segment_scope_label + ' • ' : '';
            summaryEl.textContent = scopeLabel + (data.period_label || '') + ' • ' + (data.duration_label || '') + ' • Submitted by ' + (data.requested_by || 'Unknown');
        }
        if (gmBar) gmBar.classList.remove('d-none');
        var applyBar = document.getElementById('rosterApplyApprovalBar');
        if (applyBar) applyBar.classList.add('d-none');
    }

    function performGmRosterApprove() {
        if (!rosterGmApprovalContext || !rosterGmApprovalContext.id) return;
        var baseUrl = window.rosterApprovalApproveUrl || '';
        var btn = document.getElementById('rosterGmApproveBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Approving...';
        }

        fetch(baseUrl + '/' + rosterGmApprovalContext.id + '/approve', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
            .then(function(res) { return res.json(); })
            .then(function(body) {
                if (body.success) {
                    showSuccess(body.message || 'Roster approved.');
                    rosterGmApprovalContext = null;
                    if (window.history && window.history.replaceState) {
                        window.history.replaceState({}, '', window.shiftPlannerUrl || '/admin/shift-planner');
                    }
                    document.getElementById('rosterGmApprovalBar')?.classList.add('d-none');
                    loadRosterGrid();
                } else {
                    showError(body.message || 'Approval failed.');
                }
            })
            .catch(function() { showError('Approval failed.'); })
            .finally(function() {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Approve';
                }
            });
    }

    function performGmRosterReject(reason) {
        if (!rosterGmApprovalContext || !rosterGmApprovalContext.id) return;
        var baseUrl = window.rosterApprovalRejectUrl || '';
        var btn = document.getElementById('rosterGmRejectBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Rejecting...';
        }

        fetch(baseUrl + '/' + rosterGmApprovalContext.id + '/reject', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ reason: reason || '' }),
            credentials: 'same-origin'
        })
            .then(function(res) { return res.json(); })
            .then(function(body) {
                if (body.success) {
                    showSuccess(body.message || 'Roster rejected.');
                    rosterGmApprovalContext = null;
                    if (window.history && window.history.replaceState) {
                        window.history.replaceState({}, '', window.shiftPlannerUrl || '/admin/shift-planner');
                    }
                    document.getElementById('rosterGmApprovalBar')?.classList.add('d-none');
                    loadRosterGrid();
                } else {
                    showError(body.message || 'Reject failed.');
                }
            })
            .catch(function() { showError('Reject failed.'); })
            .finally(function() {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-x-lg me-1"></i>Reject';
                }
            });
    }

    function initRosterGmApprovalMode() {
        var requestId = takeRosterApprovalReviewRequestId();
        if (!requestId) return;

        var showUrl = (window.rosterApprovalShowUrl || '/admin/shift-roster/approvals') + '/' + requestId;
        fetch(showUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
            .then(function(res) { return res.json(); })
            .then(function(json) {
                if (!json.success || !json.data) {
                    showError('Could not load roster approval request.');
                    return;
                }

                rosterGmApprovalContext = json.data;
                applyRosterReviewNavigation(json.data);

                var rosterTab = document.getElementById('roster-tab');
                if (rosterTab && typeof bootstrap !== 'undefined') {
                    bootstrap.Tab.getOrCreateInstance(rosterTab).show();
                }

                if (json.data.approval_status !== 'pending') {
                    rosterGmApprovalContext = null;
                    if (window.history && window.history.replaceState) {
                        window.history.replaceState({}, '', window.shiftPlannerUrl || '/admin/shift-planner');
                    }
                    if (typeof showSuccess === 'function') {
                        showSuccess('This roster request has already been ' + json.data.approval_status + '.');
                    }
                    loadRosterGrid();
                    return;
                }

                showGmApprovalBar(json.data);
                loadRosterGrid();
            })
            .catch(function() {
                showError('Could not load roster approval request.');
            });
    }

    function bindRosterApprovalActions() {
        if (window._rosterApprovalActionsBound) return;
        window._rosterApprovalActionsBound = true;

        var applyBtn = document.getElementById('rosterApplyForApprovalBtn');
        if (applyBtn) applyBtn.addEventListener('click', applyForRosterApproval);

        var approveBtn = document.getElementById('rosterGmApproveBtn');
        if (approveBtn) approveBtn.addEventListener('click', performGmRosterApprove);

        var rejectBtn = document.getElementById('rosterGmRejectBtn');
        if (rejectBtn) {
            rejectBtn.addEventListener('click', function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Reject roster?',
                        input: 'textarea',
                        inputPlaceholder: 'Optional reason...',
                        showCancelButton: true,
                        confirmButtonText: 'Reject',
                        confirmButtonColor: '#dc3545'
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            performGmRosterReject(result.value || '');
                        }
                    });
                } else {
                    performGmRosterReject('');
                }
            });
        }
    }

    function renderRoster() {
        var now = new Date();
        var hasApprovalReview = peekRosterApprovalReviewRequestId() > 0;
        rosterViewDate = new Date(now.getFullYear(), now.getMonth(), 1);
        rosterWeekIndex = getWeekIndexForDate(now, now.getFullYear(), now.getMonth() + 1);
        if (!window._rosterToolbarBound) {
            bindRosterToolbar();
            window._rosterToolbarBound = true;
        }
        bindRosterCanvasAndCells();
        bindRosterPersonnelTabs();
        bindRosterApprovalActions();
        if (hasApprovalReview) {
            initRosterGmApprovalMode();
        } else {
            loadRosterGrid();
        }
    }

    function bindRosterPersonnelTabs() {
        if (window._rosterPersonnelTabsBound) return;
        window._rosterPersonnelTabsBound = true;
        var internalTab = document.getElementById('rosterInternalTab');
        var thirdPartyTab = document.getElementById('rosterThirdPartyTab');
        var showDeletedCb = document.getElementById('rosterShowDeletedShifts');

        function setActiveTab(activeTabId) {
            [internalTab, thirdPartyTab].forEach(function(tab) {
                if (!tab) return;
                if (tab.id === activeTabId) {
                    tab.classList.add('active');
                    tab.setAttribute('aria-selected', 'true');
                } else {
                    tab.classList.remove('active');
                    tab.setAttribute('aria-selected', 'false');
                }
            });
        }

        if (internalTab) {
            internalTab.addEventListener('click', function() {
                rosterPersonnelFilter = 'internal';
                setActiveTab('rosterInternalTab');
                loadRosterGrid();
            });
        }
        if (thirdPartyTab) {
            thirdPartyTab.addEventListener('click', function() {
                rosterPersonnelFilter = 'third_party';
                setActiveTab('rosterThirdPartyTab');
                loadRosterGrid();
            });
        }
        if (showDeletedCb) {
            showDeletedCb.addEventListener('change', function() {
                rosterShowDeleted = !!showDeletedCb.checked;
                loadRosterGrid();
            });
        }
    }

    window.getRosterExportContext = function() {
        return {
            year: rosterViewDate.getFullYear(),
            month: rosterViewDate.getMonth(),
            personnelFilter: rosterPersonnelFilter,
            showDeleted: !!rosterShowDeleted
        };
    };

    window.loadRosterGrid = loadRosterGrid;
    window.reloadRosterGrid = loadRosterGrid;
    window.initRosterCalendar = loadRosterGrid;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', renderRoster);
    } else {
        renderRoster();
    }
    window.renderRoster = renderRoster;
})();
