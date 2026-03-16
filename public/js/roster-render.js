(function() {
    var shiftIcons = {
        morning: { icon: 'bi-sun-fill', iconClass: 'text-warning' },
        evening: { icon: 'bi-cloud-sun-fill', iconClass: 'text-primary' },
        night: { icon: 'bi-moon-stars-fill', iconClass: 'text-info' }
    };

    var rosterViewDate = new Date();
    var rosterWeekIndex = 1;
    var assignmentOverrides = {};

    function getDaysInMonth(year, month) {
        return new Date(year, month + 1, 0).getDate();
    }

    function getWeekRange(weekIndex, year, month) {
        var daysInMonth = getDaysInMonth(year, month);
        var start = (weekIndex - 1) * 7 + 1;
        var end = Math.min(start + 6, daysInMonth);
        if (start > daysInMonth) return { start: 1, end: 0, days: [] };
        var days = [];
        for (var d = start; d <= end; d++) days.push(d);
        return { start: start, end: end, days: days };
    }

    function getWeekIndexForDay(day, year, month) {
        return Math.ceil(day / 7);
    }

    function padDay(n) {
        return n < 10 ? '0' + n : String(n);
    }

    function pillHtml(s) {
        var type = s.shiftType in shiftIcons ? s.shiftType : 'morning';
        var opt = shiftIcons[type];
        var lateClass = s.lateCheckIn ? ' shift-late' : '';
        var lateBlock = s.lateCheckIn ? '<span class="shift-status-late"><i class="bi bi-exclamation-circle-fill"></i> Late check-in</span>' : '';
        return '<div class="shift-pill shift-' + type + lateClass + '">' +
            '<div class="shift-pill-top">' +
            '<span class="shift-time">' + s.timeStart + ' – ' + s.timeEnd + '</span>' +
            '<span class="shift-icon ' + opt.iconClass + '"><i class="bi ' + opt.icon + '"></i></span>' +
            '</div>' +
            '<div class="shift-pill-meta">' +
            '<span class="shift-check">Check-in ' + s.checkIn + ' • Check-out ' + s.checkOut + '</span>' +
            '<span class="shift-floor">' + s.floor + '</span>' + lateBlock +
            '</div></div>';
    }

    function formatRosterMonthYear(date) {
        var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        return months[date.getMonth()] + ' ' + date.getFullYear();
    }

    var dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    function getDayName(year, month, day) {
        var d = new Date(year, month, day);
        return dayNames[d.getDay()];
    }

    function updateRosterWeekDisplay() {
        var weekLabelEl = document.getElementById('rosterWeekLabel');
        var weekDatesEl = document.getElementById('rosterWeekDates');
        var monthEl = document.getElementById('rosterMonthYear');
        var year = rosterViewDate.getFullYear();
        var month = rosterViewDate.getMonth();
        var range = getWeekRange(rosterWeekIndex, year, month);
        if (weekLabelEl) weekLabelEl.textContent = 'Week ' + rosterWeekIndex;
        if (weekDatesEl) {
            if (range.days.length === 0) {
                weekDatesEl.textContent = '';
            } else {
                weekDatesEl.textContent = padDay(range.start) + ' to ' + padDay(range.end);
            }
        }
        if (monthEl) monthEl.textContent = formatRosterMonthYear(rosterViewDate);
    }

    function buildTheadRow(days, year, month) {
        var tr = document.getElementById('rosterTheadRow');
        if (!tr) return;
        var existing = tr.querySelectorAll('.roster-col-day');
        existing.forEach(function(el) { el.remove(); });
        days.forEach(function(d) {
            var th = document.createElement('th');
            th.className = 'roster-col-day';
            th.textContent = getDayName(year, month, d) + ' ' + d;
            tr.appendChild(th);
        });
    }

    function bindRosterToolbar() {
        var prevBtn = document.getElementById('rosterPrevWeek');
        var nextBtn = document.getElementById('rosterNextWeek');
        var todayBtn = document.getElementById('rosterTodayBtn');
        if (prevBtn) prevBtn.addEventListener('click', function() {
            if (rosterWeekIndex > 1) {
                rosterWeekIndex--;
            } else {
                rosterViewDate.setMonth(rosterViewDate.getMonth() - 1);
                var year = rosterViewDate.getFullYear();
                var month = rosterViewDate.getMonth();
                var daysInMonth = getDaysInMonth(year, month);
                rosterWeekIndex = Math.ceil(daysInMonth / 7);
            }
            renderRosterTable();
        });
        if (nextBtn) nextBtn.addEventListener('click', function() {
            var year = rosterViewDate.getFullYear();
            var month = rosterViewDate.getMonth();
            var daysInMonth = getDaysInMonth(year, month);
            var maxWeek = Math.ceil(daysInMonth / 7);
            if (rosterWeekIndex < maxWeek) {
                rosterWeekIndex++;
            } else {
                rosterViewDate.setMonth(rosterViewDate.getMonth() + 1);
                rosterWeekIndex = 1;
            }
            renderRosterTable();
        });
        if (todayBtn) todayBtn.addEventListener('click', function() {
            var now = new Date();
            rosterViewDate = new Date(now.getFullYear(), now.getMonth(), 1);
            var day = now.getDate();
            rosterWeekIndex = getWeekIndexForDay(day, now.getFullYear(), now.getMonth());
            renderRosterTable();
        });
    }

    function renderRosterTable() {
        if (typeof ProjectData === 'undefined' || !ProjectData.roster) return;
        var r = ProjectData.roster;
        var depts = r.departments;
        var employees = r.employees;
        var shifts = (typeof r.getShifts === 'function' ? r.getShifts() : r.shifts) || [];
        var shiftsByEmpDay = {};
        shifts.forEach(function(s) {
            var k = s.employeeId + '-' + s.day;
            if (!shiftsByEmpDay[k]) shiftsByEmpDay[k] = s;
        });
        for (var ok in assignmentOverrides) shiftsByEmpDay[ok] = assignmentOverrides[ok];

        var year = rosterViewDate.getFullYear();
        var month = rosterViewDate.getMonth();
        var daysInMonth = getDaysInMonth(year, month);
        var maxWeek = Math.ceil(daysInMonth / 7);
        if (rosterWeekIndex > maxWeek) rosterWeekIndex = maxWeek;
        if (rosterWeekIndex < 1) rosterWeekIndex = 1;

        var range = getWeekRange(rosterWeekIndex, year, month);
        var days = range.days;

        buildTheadRow(days.length ? days : [1, 2, 3, 4, 5, 6, 7], year, month);
        updateRosterWeekDisplay();

        var tbody = document.getElementById('rosterTableBody');
        if (!tbody) return;
        tbody.innerHTML = '';

        var dayCount = days.length;
        var colspan = 2 + dayCount;

        depts.forEach(function(dept) {
            var deptTr = document.createElement('tr');
            deptTr.className = 'roster-dept-row';
            deptTr.setAttribute('data-dept-id', dept.id);
            deptTr.innerHTML = '<td class="text-center">' +
                '<button type="button" class="btn btn-sm btn-link p-0 text-dark roster-dept-toggle" data-dept-id="' + dept.id + '" aria-expanded="true" aria-label="Collapse ' + dept.name + '">' +
                '<i class="bi bi-chevron-down"></i></button></td>' +
                '<td colspan="' + colspan + '" class="fw-semibold">' + dept.name + '</td>';
            tbody.appendChild(deptTr);

            employees.filter(function(e) { return e.departmentId === dept.id; }).forEach(function(emp) {
                var tr = document.createElement('tr');
                tr.className = 'roster-emp-row';
                tr.setAttribute('data-dept-id', dept.id);
                tr.innerHTML = '<td></td><td class="text-muted">' + emp.name + '</td>';
                days.forEach(function(d) {
                    var k = emp.id + '-' + d;
                    var s = shiftsByEmpDay[k];
                    var td = document.createElement('td');
                    td.className = 'roster-day-cell shift-cell';
                    td.setAttribute('data-employee-id', emp.id);
                    td.setAttribute('data-day', d);
                    if (s) {
                        td.setAttribute('data-shift', JSON.stringify(s));
                        td.innerHTML = pillHtml(s);
                    } else {
                        td.classList.add('roster-day-cell-empty');
                    }
                    tr.appendChild(td);
                });
                tbody.appendChild(tr);
            });
        });

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

    function formatRosterDateLabel(day, year, month) {
        var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return padDay(day) + ' ' + months[month] + ' ' + year + ' (Day ' + day + ')';
    }

    function openRosterShiftCanvas(employeeId, employeeName, day, shift) {
        var year = rosterViewDate.getFullYear();
        var month = rosterViewDate.getMonth();
        var canvas = document.getElementById('rosterShiftCanvas');
        var titleEl = document.getElementById('rosterShiftCanvasTitle');
        var editModeEl = document.getElementById('rosterShiftEditMode');
        var deleteWrap = document.getElementById('rosterShiftDeleteWrap');
        var saveBtnText = document.getElementById('rosterShiftSaveBtnText');
        if (!canvas) return;
        document.getElementById('rosterShiftEmployeeId').value = employeeId;
        document.getElementById('rosterShiftDay').value = day;
        document.getElementById('rosterShiftEmployeeName').textContent = employeeName;
        document.getElementById('rosterShiftDateLabel').textContent = formatRosterDateLabel(day, year, month);
        var iconEl = document.getElementById('rosterShiftCanvasIcon');
        if (shift) {
            editModeEl.value = '1';
            if (iconEl) iconEl.innerHTML = '<i class="bi bi-pencil-square me-2"></i>';
            if (titleEl) titleEl.textContent = 'Edit Shift';
            if (saveBtnText) saveBtnText.textContent = 'Update';
            if (deleteWrap) deleteWrap.style.display = 'block';
            document.getElementById('rosterShiftType').value = shift.shiftType || 'general';
            document.getElementById('rosterShiftStartTime').value = shift.timeStart || '09:00';
            document.getElementById('rosterShiftEndTime').value = shift.timeEnd || '17:00';
            document.getElementById('rosterShiftCheckIn').value = shift.checkIn || '';
            document.getElementById('rosterShiftCheckOut').value = shift.checkOut || '';
            document.getElementById('rosterShiftFloor').value = shift.floor || '';
            document.getElementById('rosterShiftLateCheckIn').checked = !!shift.lateCheckIn;
        } else {
            editModeEl.value = '0';
            if (iconEl) iconEl.innerHTML = '<i class="bi bi-plus-circle me-2"></i>';
            if (titleEl) titleEl.textContent = 'Add Shift';
            if (saveBtnText) saveBtnText.textContent = 'Save';
            if (deleteWrap) deleteWrap.style.display = 'none';
            var r = typeof ProjectData !== 'undefined' && ProjectData.roster ? ProjectData.roster : null;
            var t = r && r.shiftTemplates && r.shiftTemplates.general ? r.shiftTemplates.general : { timeStart: '09:00', timeEnd: '17:00', checkInEarly: '08:55', checkInLate: '09:10', checkOutEarly: '17:00', checkOutLate: '17:05' };
            document.getElementById('rosterShiftType').value = 'general';
            document.getElementById('rosterShiftStartTime').value = t.timeStart;
            document.getElementById('rosterShiftEndTime').value = t.timeEnd;
            document.getElementById('rosterShiftCheckIn').value = t.checkInEarly || '';
            document.getElementById('rosterShiftCheckOut').value = t.checkOutEarly || '';
            document.getElementById('rosterShiftFloor').value = '';
            document.getElementById('rosterShiftLateCheckIn').checked = false;
        }
        var offcanvas = bootstrap.Offcanvas.getOrCreateInstance(canvas);
        offcanvas.show();
    }

    function saveRosterAssignment() {
        var employeeId = document.getElementById('rosterShiftEmployeeId').value;
        var day = parseInt(document.getElementById('rosterShiftDay').value, 10);
        if (!employeeId || !day) return;
        var key = employeeId + '-' + day;
        assignmentOverrides[key] = {
            employeeId: employeeId,
            day: day,
            shiftType: document.getElementById('rosterShiftType').value,
            timeStart: document.getElementById('rosterShiftStartTime').value,
            timeEnd: document.getElementById('rosterShiftEndTime').value,
            checkIn: document.getElementById('rosterShiftCheckIn').value || document.getElementById('rosterShiftStartTime').value,
            checkOut: document.getElementById('rosterShiftCheckOut').value || document.getElementById('rosterShiftEndTime').value,
            floor: document.getElementById('rosterShiftFloor').value || '',
            lateCheckIn: document.getElementById('rosterShiftLateCheckIn').checked
        };
        renderRosterTable();
        var canvas = document.getElementById('rosterShiftCanvas');
        if (canvas) { var o = bootstrap.Offcanvas.getInstance(canvas); if (o) o.hide(); }
    }

    function deleteRosterAssignment() {
        var employeeId = document.getElementById('rosterShiftEmployeeId').value;
        var day = document.getElementById('rosterShiftDay').value;
        if (!employeeId || !day) return;
        var key = employeeId + '-' + day;
        delete assignmentOverrides[key];
        renderRosterTable();
        var canvas = document.getElementById('rosterShiftCanvas');
        if (canvas) { var o = bootstrap.Offcanvas.getInstance(canvas); if (o) o.hide(); }
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
                var day = parseInt(td.getAttribute('data-day'), 10);
                var shiftData = td.getAttribute('data-shift');
                var shift = shiftData ? JSON.parse(shiftData) : null;
                var empName = '';
                var row = td.closest('tr');
                if (row) {
                    var nameCell = row.querySelector('td:nth-child(2)');
                    if (nameCell) empName = nameCell.textContent.trim();
                }
                openRosterShiftCanvas(employeeId, empName, day, shift);
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
            deleteBtn.addEventListener('click', function() { deleteRosterAssignment(); });
        }
    }

    function renderRoster() {
        var now = new Date();
        rosterViewDate = new Date(now.getFullYear(), now.getMonth(), 1);
        rosterWeekIndex = getWeekIndexForDay(now.getDate(), now.getFullYear(), now.getMonth());
        bindRosterToolbar();
        renderRosterTable();
        bindRosterCanvasAndCells();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', renderRoster);
    } else {
        renderRoster();
    }
    window.renderRoster = renderRoster;
})();
