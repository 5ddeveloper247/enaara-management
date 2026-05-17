(function() {
    var rosterViewDate = new Date();
    var rosterWeekIndex = 1;
    var rosterData = null;
    var rosterPersonnelFilter = 'internal';

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

    function pillHtml(s) {
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
        var iconBlock = shiftTypeIconHtml(shiftType);
        var lateBlock = s.lateCheckIn ? '<span class="shift-status-late"><i class="bi bi-exclamation-circle-fill"></i> Late check-in</span>' : '';
        var floorBlock = s.floor ? '<span class="shift-floor">' + escapeHtml(s.floor) + '</span>' : '';
        return '<div class="shift-pill' + typeClass + lateClass + deletedClass + '">' +
            iconBlock +
            '<div class="shift-pill-top">' +
            '<span class="shift-time">' + formatTimeAMPM(s.timeStart) + ' – ' + formatTimeAMPM(s.timeEnd) + '</span>' +
            '</div>' +
            '<div class="shift-pill-meta">' +
            lateBlock +
            floorBlock +
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

    function buildTheadRow(days) {
        var tr = document.getElementById('rosterTheadRow');
        if (!tr) return;
        var existing = tr.querySelectorAll('.roster-col-day');
        existing.forEach(function(el) { el.remove(); });
        var viewMonth = rosterViewDate.getMonth();
        var viewYear = rosterViewDate.getFullYear();
        days.forEach(function(d) {
            var th = document.createElement('th');
            th.className = 'roster-col-day';
            th.textContent = getDayName(d) + ' ' + d.getDate();
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
            if (!shiftsByEmpDay[k]) shiftsByEmpDay[k] = s;
        });

        var year = rosterViewDate.getFullYear();
        var month1 = rosterViewDate.getMonth() + 1;
        var maxWeek = getWeekMaxIndexForMonth(year, month1);
        if (rosterWeekIndex > maxWeek) rosterWeekIndex = maxWeek;
        if (rosterWeekIndex < 1) rosterWeekIndex = 1;

        var days = getWeekDays(year, month1, rosterWeekIndex);

        buildTheadRow(days);
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
                var deptEmployees = employees.filter(function(e) {
                    if (Number(e.departmentId) !== Number(dept.id)) return false;
                    if (rosterPersonnelFilter === 'deleted') {
                        // In deleted tab, show employee if they have at least one deleted shift in the current data
                        return shifts.some(function(s) {
                            return String(s.employeeId) === String(e.id) && s.deletedAt;
                        });
                    }
                    if (rosterPersonnelFilter === 'third_party') return String(e.sourceType || '') === 'outsourced';
                    return String(e.sourceType || '') !== 'outsourced';
                });
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
                    var isThirdPartyPersonnel = String(emp.sourceType || '') === 'outsourced';
                    var employeeNameHtml = escapeHtml(emp.name);
                    if (isThirdPartyPersonnel) {
                        employeeNameHtml += ' <span class="badge text-bg-info ms-1">Third-Party Personnel</span>';
                    }
                    var tr = document.createElement('tr');
                    tr.className = 'roster-emp-row';
                    tr.setAttribute('data-dept-id', String(dept.id));
                    tr.innerHTML = '<td></td><td class="text-muted">' + employeeNameHtml + '</td>';
                    days.forEach(function(d) {
                        var iso = dateToISO(d);
                        var k = empRef + '-' + iso;
                        var s = shiftsByEmpDay[k];
                        var today = stripTime(new Date());
                        var cellDate = stripTime(d);
                        var isPast = cellDate < today;

                        var td = document.createElement('td');
                        
                        /* Old Logic
                        td.className = 'roster-day-cell shift-cell';
                        */
                        // New Logic: Only add 'roster-day-cell' if it has a shift OR it's not in the past
                        td.className = 'shift-cell';
                        if (s || !isPast) {
                            td.classList.add('roster-day-cell');
                        }

                        td.setAttribute('data-employee-id', String(empRef));
                        td.setAttribute('data-roster-date', iso);
                        td.setAttribute('data-day', String(d.getDate()));
                        
                        if (d.getMonth() !== rosterViewDate.getMonth() || d.getFullYear() !== rosterViewDate.getFullYear()) {
                            td.style.opacity = '0.55';
                        }
                        
                        if (s) {
                            td.setAttribute('data-shift', JSON.stringify(s));
                            td.innerHTML = pillHtml(s);
                        } else {
                            /* Old Logic
                            td.classList.add('roster-day-cell-empty');
                            td.innerHTML = '<span class="text-muted d-inline-flex align-items-center justify-content-center w-100" style="min-height:2rem"><i class="bi bi-plus-lg"></i></span>';
                            */
                            // New Logic: Only show '+' icon if it's not in the past
                            if (!isPast) {
                                td.classList.add('roster-day-cell-empty');
                                td.innerHTML = '<span class="text-muted d-inline-flex align-items-center justify-content-center w-100" style="min-height:2rem"><i class="bi bi-plus-lg"></i></span>';
                            }
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
                } else if (rosterPersonnelFilter === 'deleted') {
                    emptyLabel = 'No deleted shifts found for this period.';
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
        var url = gridUrl + '?year=' + year + '&month=' + month + '&week=' + rosterWeekIndex + '&filter=' + rosterPersonnelFilter;

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

    function setRosterAuditField(nameEl, timeEl, name, timeValue) {
        if (nameEl) {
            nameEl.textContent = name || '—';
        }
        if (timeEl) {
            timeEl.textContent = name ? formatRosterAuditDateTime(timeValue) : '—';
        }
    }

    function resetRosterAuditFields() {
        setRosterAuditField(
            document.getElementById('rosterShiftCreatedBy'),
            document.getElementById('rosterShiftCreatedAt'),
            null,
            null
        );
        setRosterAuditField(
            document.getElementById('rosterShiftUpdatedBy'),
            document.getElementById('rosterShiftUpdatedAt'),
            null,
            null
        );
        setRosterAuditField(
            document.getElementById('rosterShiftAssignedBy'),
            document.getElementById('rosterShiftAssignedAt'),
            null,
            null
        );
        setRosterAuditField(
            document.getElementById('rosterShiftDeletedBy'),
            document.getElementById('rosterShiftDeletedAt'),
            null,
            null
        );
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
        var html = '<option value="">Select floor / location</option>';

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

    function openRosterShiftCanvas(employeeId, employeeName, deptName, rosterDate, shift) {
        var canvas = document.getElementById('rosterShiftCanvas');
        var titleEl = document.getElementById('rosterShiftCanvasTitle');
        var deleteWrap = document.getElementById('rosterShiftDeleteWrap');
        var saveBtnText = document.getElementById('rosterShiftSaveBtnText');
        var rosterIdEl = document.getElementById('rosterShiftRosterId');
        var shiftSelect = document.getElementById('rosterShiftPlannerId');
        var notesEl = document.getElementById('rosterShiftNotes');
        var startTimeEl = document.getElementById('rosterStartTime');
        var endTimeEl = document.getElementById('rosterEndTime');
        var checkInEl = document.getElementById('rosterCheckIn');
        var checkOutEl = document.getElementById('rosterCheckOut');
        var floorEl = document.getElementById('rosterFloor');
        var lateCheckInEl = document.getElementById('rosterLateCheckIn');
        var employeeTypeEl = document.getElementById('rosterShiftEmployeeType');
        var auditCard = document.getElementById('rosterShiftAuditCard');
        var createdByEl = document.getElementById('rosterShiftCreatedBy');
        var createdAtEl = document.getElementById('rosterShiftCreatedAt');
        var updatedByEl = document.getElementById('rosterShiftUpdatedBy');
        var updatedAtEl = document.getElementById('rosterShiftUpdatedAt');
        var assignedByEl = document.getElementById('rosterShiftAssignedBy');
        var assignedAtEl = document.getElementById('rosterShiftAssignedAt');
        var updatedWrap = document.getElementById('rosterShiftUpdatedWrap');
        var assignedWrap = document.getElementById('rosterShiftAssignedWrap');
        var deletedWrap = document.getElementById('rosterShiftDeletedWrap');
        var deletedByEl = document.getElementById('rosterShiftDeletedBy');
        var deletedAtEl = document.getElementById('rosterShiftDeletedAt');

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
        if (shift) {
            if (rosterIdEl) rosterIdEl.value = shift.rosterId || '';
            if (iconEl) iconEl.innerHTML = '<i class="bi bi-pencil-square me-2"></i>';
            if (titleEl) titleEl.textContent = 'Edit Shift';
            if (saveBtnText) saveBtnText.textContent = 'Update';
            if (deleteWrap) deleteWrap.style.display = 'block';
            var isCustomEntry = !!(shift.isCustomTime || shift.is_custom_time);
            if (shiftSelect) {
                if (isCustomEntry) {
                    shiftSelect.value = '';
                } else {
                    shiftSelect.value = String(shift.shiftPlannerId || shift.shift_planner_id || '');
                }
            }
            if (startTimeEl) startTimeEl.value = shift.timeStart || '';
            if (endTimeEl) endTimeEl.value = shift.timeEnd || '';
            if (checkInEl) checkInEl.value = shift.checkIn || '';
            if (checkOutEl) checkOutEl.value = shift.checkOut || '';
            if (lateCheckInEl) lateCheckInEl.checked = !!shift.lateCheckIn;
            if (notesEl) notesEl.value = shift.notes || '';

            setRosterAuditField(
                createdByEl,
                createdAtEl,
                shift.createdByName,
                shift.createdAt || shift.created_at
            );
            setRosterAuditField(
                updatedByEl,
                updatedAtEl,
                shift.updatedByName,
                shift.updatedAt || shift.updated_at
            );
            setRosterAuditField(
                assignedByEl,
                assignedAtEl,
                shift.assignedByName,
                shift.assignedAt || shift.assigned_at
            );
            setRosterAuditField(
                deletedByEl,
                deletedAtEl,
                shift.deletedByName,
                shift.deletedAt || shift.deleted_at
            );

            if (updatedWrap) {
                updatedWrap.style.display = shift.updatedByName ? '' : 'none';
            }
            if (assignedWrap) {
                assignedWrap.style.display = shift.assignedByName ? '' : 'none';
            }
            if (deletedWrap) {
                deletedWrap.style.display = shift.deletedByName ? '' : 'none';
            }

            if (auditCard) {
                var hasAnyAudit = !!(shift.createdByName || shift.updatedByName || shift.assignedByName || shift.deletedByName);
                auditCard.style.display = hasAnyAudit ? 'block' : 'none';
            }
        } else {
            if (rosterIdEl) rosterIdEl.value = '';
            if (iconEl) iconEl.innerHTML = '<i class="bi bi-plus-circle me-2"></i>';
            if (titleEl) titleEl.textContent = 'Add Shift';
            if (saveBtnText) saveBtnText.textContent = 'Save';
            if (deleteWrap) deleteWrap.style.display = 'none';
            if (shiftSelect) shiftSelect.value = '';
            if (startTimeEl) startTimeEl.value = '';
            if (endTimeEl) endTimeEl.value = '';
            var useCustomReset = document.getElementById('rosterUseCustomTime');
            if (useCustomReset) useCustomReset.checked = false;
            if (checkInEl) checkInEl.value = '';
            if (checkOutEl) checkOutEl.value = '';
            if (lateCheckInEl) lateCheckInEl.checked = false;
            if (notesEl) notesEl.value = '';
            if (auditCard) auditCard.style.display = 'none';
            resetRosterAuditFields();
            if (updatedWrap) updatedWrap.style.display = '';
            if (assignedWrap) assignedWrap.style.display = '';
            if (deletedWrap) deletedWrap.style.display = 'none';
        }

        var useCustomCb = document.getElementById('rosterUseCustomTime');
        if (useCustomCb) {
            useCustomCb.checked = !!(shift && (shift.isCustomTime || shift.is_custom_time));
        }
        toggleRosterCustomTimeUi();

        var saveBtn = document.getElementById('rosterShiftSaveBtn');
        if (shift && shift.deletedAt) {
            if (titleEl) titleEl.textContent = 'View Deleted Shift';
            if (saveBtn) saveBtn.style.display = 'none';
            if (deleteWrap) deleteWrap.style.display = 'none';
            if (shiftSelect) shiftSelect.disabled = true;
            if (useCustomCb) useCustomCb.disabled = true;
            if (startTimeEl) startTimeEl.disabled = true;
            if (endTimeEl) endTimeEl.disabled = true;
            if (notesEl) notesEl.disabled = true;
            if (floorEl) floorEl.disabled = true;
        } else {
            if (saveBtn) saveBtn.style.display = 'inline-block';
            if (shiftSelect) shiftSelect.disabled = false;
            if (useCustomCb) useCustomCb.disabled = false;
            if (startTimeEl) startTimeEl.disabled = false;
            if (endTimeEl) endTimeEl.disabled = false;
            if (notesEl) notesEl.disabled = false;
            if (floorEl) floorEl.disabled = false;
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
        var sbuFloorId = floorSelect && floorSelect.value ? parseInt(floorSelect.value, 10) : null;

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

        clearRosterFloorFieldError();

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
                var td = e.target.closest('.roster-day-cell');
                if (!td) return;
                e.preventDefault();
                var employeeId = td.getAttribute('data-employee-id');
                var rosterDate = td.getAttribute('data-roster-date');
                var shiftData = td.getAttribute('data-shift');
                var shift = null;
                if (shiftData) {
                    try { shift = JSON.parse(shiftData); } catch (err) { shift = null; }
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

    function renderRoster() {
        var now = new Date();
        rosterViewDate = new Date(now.getFullYear(), now.getMonth(), 1);
        rosterWeekIndex = getWeekIndexForDate(now, now.getFullYear(), now.getMonth() + 1);
        if (!window._rosterToolbarBound) {
            bindRosterToolbar();
            window._rosterToolbarBound = true;
        }
        bindRosterCanvasAndCells();
        bindRosterPersonnelTabs();
        loadRosterGrid();
    }

    function bindRosterPersonnelTabs() {
        if (window._rosterPersonnelTabsBound) return;
        window._rosterPersonnelTabsBound = true;
        var internalTab = document.getElementById('rosterInternalTab');
        var thirdPartyTab = document.getElementById('rosterThirdPartyTab');
        var deletedTab = document.getElementById('rosterDeletedTab');

        function setActiveTab(activeTabId) {
            [internalTab, thirdPartyTab, deletedTab].forEach(function(tab) {
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
        if (deletedTab) {
            deletedTab.addEventListener('click', function() {
                rosterPersonnelFilter = 'deleted';
                setActiveTab('rosterDeletedTab');
                loadRosterGrid();
            });
        }
    }

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
