(function() {
    var rosterViewDate = new Date();
    var rosterWeekIndex = 1;
    var rosterData = null;

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

    function rosterDateIso(day) {
        var y = rosterViewDate.getFullYear();
        var m = rosterViewDate.getMonth() + 1;
        var mm = m < 10 ? '0' + m : String(m);
        var dd = day < 10 ? '0' + day : String(day);
        return y + '-' + mm + '-' + dd;
    }

    function csrfToken() {
        var m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : '';
    }

    function pillHtml(s) {
        var lateClass = s.lateCheckIn ? ' shift-late' : '';
        var lateBlock = s.lateCheckIn ? '<span class="shift-status-late"><i class="bi bi-exclamation-circle-fill"></i> Late check-in</span>' : '';
        var floor = (s.floor && String(s.floor).trim()) ? s.floor : '—';
        return '<div class="shift-pill' + lateClass + '">' +
            '<div class="shift-pill-top">' +
            '<span class="shift-time">' + s.timeStart + ' – ' + s.timeEnd + '</span>' +
            '</div>' +
            '<div class="shift-pill-meta">' +
            '<span class="shift-check">Check-in ' + s.checkIn + ' • Check-out ' + s.checkOut + '</span>' +
            '<span class="shift-floor">' + floor + '</span>' + lateBlock +
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
            loadRosterGrid();
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
            loadRosterGrid();
        });
        if (todayBtn) todayBtn.addEventListener('click', function() {
            var now = new Date();
            rosterViewDate = new Date(now.getFullYear(), now.getMonth(), 1);
            var day = now.getDate();
            rosterWeekIndex = getWeekIndexForDay(day, now.getFullYear(), now.getMonth());
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
            var k = s.employeeId + '-' + s.day;
            if (!shiftsByEmpDay[k]) shiftsByEmpDay[k] = s;
        });

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
            deptTr.setAttribute('data-dept-id', String(dept.id));
            deptTr.innerHTML = '<td class="text-center">' +
                '<button type="button" class="btn btn-sm btn-link p-0 text-dark roster-dept-toggle" data-dept-id="' + dept.id + '" aria-expanded="true" aria-label="Collapse ' + dept.name + '">' +
                '<i class="bi bi-chevron-down"></i></button></td>' +
                '<td colspan="' + colspan + '" class="fw-semibold">' + dept.name + '</td>';
            tbody.appendChild(deptTr);

            employees.filter(function(e) { return Number(e.departmentId) === Number(dept.id); }).forEach(function(emp) {
                var tr = document.createElement('tr');
                tr.className = 'roster-emp-row';
                tr.setAttribute('data-dept-id', String(dept.id));
                tr.innerHTML = '<td></td><td class="text-muted">' + escapeHtml(emp.name) + '</td>';
                days.forEach(function(d) {
                    var k = emp.id + '-' + d;
                    var s = shiftsByEmpDay[k];
                    var td = document.createElement('td');
                    td.className = 'roster-day-cell shift-cell';
                    td.setAttribute('data-employee-id', String(emp.id));
                    td.setAttribute('data-day', String(d));
                    if (s) {
                        td.setAttribute('data-shift', JSON.stringify(s));
                        td.innerHTML = pillHtml(s);
                    } else {
                        td.classList.add('roster-day-cell-empty');
                        td.innerHTML = '<span class="text-muted d-inline-flex align-items-center justify-content-center w-100" style="min-height:2rem"><i class="bi bi-plus-lg"></i></span>';
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
        var url = gridUrl + '?year=' + year + '&month=' + month + '&week=' + rosterWeekIndex;

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

    function formatRosterDateLabel(day, year, month) {
        var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return padDay(day) + ' ' + months[month] + ' ' + year + ' (Day ' + day + ')';
    }

    function openRosterShiftCanvas(employeeId, employeeName, day, shift) {
        var year = rosterViewDate.getFullYear();
        var month = rosterViewDate.getMonth();
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

        if (!canvas) return;
        document.getElementById('rosterShiftEmployeeId').value = employeeId;
        document.getElementById('rosterShiftDay').value = day;
        document.getElementById('rosterShiftEmployeeName').textContent = employeeName;
        document.getElementById('rosterShiftDateLabel').textContent = formatRosterDateLabel(day, year, month);
        var iconEl = document.getElementById('rosterShiftCanvasIcon');
        if (shift) {
            if (rosterIdEl) rosterIdEl.value = shift.rosterId || '';
            if (iconEl) iconEl.innerHTML = '<i class="bi bi-pencil-square me-2"></i>';
            if (titleEl) titleEl.textContent = 'Edit Shift';
            if (saveBtnText) saveBtnText.textContent = 'Update';
            if (deleteWrap) deleteWrap.style.display = 'block';
            if (shiftSelect) shiftSelect.value = String(shift.shiftPlannerId || '');
            if (startTimeEl) startTimeEl.value = shift.timeStart || '';
            if (endTimeEl) endTimeEl.value = shift.timeEnd || '';
            if (checkInEl) checkInEl.value = shift.checkIn || '';
            if (checkOutEl) checkOutEl.value = shift.checkOut || '';
            if (floorEl) floorEl.value = shift.floor || '';
            if (lateCheckInEl) lateCheckInEl.checked = !!shift.lateCheckIn;
            if (notesEl) notesEl.value = shift.notes || '';
        } else {
            if (rosterIdEl) rosterIdEl.value = '';
            if (iconEl) iconEl.innerHTML = '<i class="bi bi-plus-circle me-2"></i>';
            if (titleEl) titleEl.textContent = 'Add Shift';
            if (saveBtnText) saveBtnText.textContent = 'Save';
            if (deleteWrap) deleteWrap.style.display = 'none';
            if (shiftSelect) shiftSelect.value = '';
            if (startTimeEl) startTimeEl.value = '';
            if (endTimeEl) endTimeEl.value = '';
            if (checkInEl) checkInEl.value = '';
            if (checkOutEl) checkOutEl.value = '';
            if (floorEl) floorEl.value = '';
            if (lateCheckInEl) lateCheckInEl.checked = false;
            if (notesEl) notesEl.value = '';
        }
        var offcanvas = bootstrap.Offcanvas.getOrCreateInstance(canvas);
        offcanvas.show();
    }

    function saveRosterAssignment() {
        var base = window.rosterUpdateUrlBase || '';
        var storeUrl = window.rosterStoreUrl || '';
        if (!base || !storeUrl) return;

        var employeeId = document.getElementById('rosterShiftEmployeeId').value;
        var day = parseInt(document.getElementById('rosterShiftDay').value, 10);
        var shiftPlannerId = document.getElementById('rosterShiftPlannerId').value;
        var rosterId = document.getElementById('rosterShiftRosterId').value;
        var notes = document.getElementById('rosterShiftNotes').value.trim();

        if (!employeeId || !day || !shiftPlannerId) return;

        var payload = {
            employee_id: parseInt(employeeId, 10),
            shift_planner_id: parseInt(shiftPlannerId, 10),
            roster_date: rosterDateIso(day),
            start_time: document.getElementById('rosterStartTime').value,
            end_time: document.getElementById('rosterEndTime').value,
            check_in: document.getElementById('rosterCheckIn').value,
            check_out: document.getElementById('rosterCheckOut').value,
            floor: document.getElementById('rosterFloor').value,
            late_check_in: document.getElementById('rosterLateCheckIn').checked ? 1 : 0
        };
        if (notes) payload.notes = notes;

        var url = rosterId ? (base + '/' + rosterId) : storeUrl;
        var saveBtn = document.getElementById('rosterShiftSaveBtn');
        if (saveBtn) saveBtn.disabled = true;

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
            .then(function(r) { return r.json().then(function(j) { return { ok: r.ok, status: r.status, body: j }; }); })
            .then(function(res) {
                if (saveBtn) saveBtn.disabled = false;
                if (res.ok && res.body.success) {
                    var canvas = document.getElementById('rosterShiftCanvas');
                    if (canvas) {
                        var o = bootstrap.Offcanvas.getInstance(canvas);
                        if (o) o.hide();
                    }
                    loadRosterGrid();
                } else {
                    var msg = (res.body && res.body.message) ? res.body.message : 'Could not save assignment.';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Error', text: msg });
                    } else {
                        alert(msg);
                    }
                }
            })
            .catch(function() {
                if (saveBtn) saveBtn.disabled = false;
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Network error.' });
                }
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
                    var canvas = document.getElementById('rosterShiftCanvas');
                    if (canvas) {
                        var o = bootstrap.Offcanvas.getInstance(canvas);
                        if (o) o.hide();
                    }
                    loadRosterGrid();
                } else {
                    var msg = (res.body && res.body.message) ? res.body.message : 'Could not remove assignment.';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Error', text: msg });
                    } else {
                        alert(msg);
                    }
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
                var day = parseInt(td.getAttribute('data-day'), 10);
                var shiftData = td.getAttribute('data-shift');
                var shift = null;
                if (shiftData) {
                    try { shift = JSON.parse(shiftData); } catch (err) { shift = null; }
                }
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
        var plannerSelect = document.getElementById('rosterShiftPlannerId');
        if (plannerSelect && !plannerSelect._rosterBound) {
            plannerSelect._rosterBound = true;
            plannerSelect.addEventListener('change', function() {
                var opt = this.options[this.selectedIndex];
                if (opt && opt.value) {
                    var start = opt.getAttribute('data-start');
                    var end = opt.getAttribute('data-end');
                    if (start) document.getElementById('rosterStartTime').value = start;
                    if (end) document.getElementById('rosterEndTime').value = end;
                    // Default check-in/out to start/end
                    if (start) document.getElementById('rosterCheckIn').value = start;
                    if (end) document.getElementById('rosterCheckOut').value = end;
                }
            });
        }
    }

    function renderRoster() {
        var now = new Date();
        rosterViewDate = new Date(now.getFullYear(), now.getMonth(), 1);
        rosterWeekIndex = getWeekIndexForDay(now.getDate(), now.getFullYear(), now.getMonth());
        if (!window._rosterToolbarBound) {
            bindRosterToolbar();
            window._rosterToolbarBound = true;
        }
        bindRosterCanvasAndCells();
        loadRosterGrid();
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
