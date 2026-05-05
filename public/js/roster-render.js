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

    function pillHtml(s) {
        var typeClass = s.shiftType && s.shiftType !== 'general' ? ' shift-' + s.shiftType : '';
        var lateClass = s.lateCheckIn ? ' shift-late' : '';
        var deletedClass = s.deletedAt ? ' shift-cancelled' : '';
        var lateBlock = s.lateCheckIn ? '<span class="shift-status-late"><i class="bi bi-exclamation-circle-fill"></i> Late check-in</span>' : '';
        var floorBlock = (s.floor && String(s.floor).trim()) ? '<span class="shift-floor">' + s.floor + '</span>' : '';
        return '<div class="shift-pill' + typeClass + lateClass + deletedClass + '">' +
            '<div class="shift-pill-top">' +
            '<span class="shift-time">' + s.timeStart + ' – ' + s.timeEnd + '</span>' +
            '</div>' +
            '<div class="shift-pill-meta">' +
            // '<span class="shift-check"><i class="bi bi-box-arrow-in-right shift-check-icon me-1"></i>' + checkIn + ' <span class="mx-1">•</span><i class="bi bi-box-arrow-right shift-check-icon ms-1 me-1"></i>' + checkOut + '</span>' +
            floorBlock + lateBlock +
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
                        var td = document.createElement('td');
                        td.className = 'roster-day-cell shift-cell';
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
                            td.classList.add('roster-day-cell-empty');
                            td.innerHTML = '<span class="text-muted d-inline-flex align-items-center justify-content-center w-100" style="min-height:2rem"><i class="bi bi-plus-lg"></i></span>';
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
        var updatedByEl = document.getElementById('rosterShiftUpdatedBy');
        var assignedByEl = document.getElementById('rosterShiftAssignedBy');
        var deletedWrap = document.getElementById('rosterShiftDeletedWrap');
        var deletedByEl = document.getElementById('rosterShiftDeletedBy');

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
            if (shiftSelect) shiftSelect.value = String(shift.shiftPlannerId || '');
            if (startTimeEl) startTimeEl.value = shift.timeStart || '';
            if (endTimeEl) endTimeEl.value = shift.timeEnd || '';
            if (checkInEl) checkInEl.value = shift.checkIn || '';
            if (checkOutEl) checkOutEl.value = shift.checkOut || '';
            if (floorEl) floorEl.value = shift.floor || '';
            if (lateCheckInEl) lateCheckInEl.checked = !!shift.lateCheckIn;
            if (notesEl) notesEl.value = shift.notes || '';

            if (createdByEl) createdByEl.textContent = shift.createdByName || '—';
            if (updatedByEl) updatedByEl.textContent = shift.updatedByName || '—';
            if (assignedByEl) assignedByEl.textContent = shift.assignedByName || '—';

            if (deletedWrap) {
                deletedWrap.style.display = shift.deletedByName ? 'flex' : 'none';
            }
            if (deletedByEl) deletedByEl.textContent = shift.deletedByName || '—';

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
            if (checkInEl) checkInEl.value = '';
            if (checkOutEl) checkOutEl.value = '';
            if (floorEl) floorEl.value = '';
            if (lateCheckInEl) lateCheckInEl.checked = false;
            if (notesEl) notesEl.value = '';
            if (auditCard) auditCard.style.display = 'none';
            if (createdByEl) createdByEl.textContent = '—';
            if (updatedByEl) updatedByEl.textContent = '—';
            if (assignedByEl) assignedByEl.textContent = '—';
            if (deletedWrap) deletedWrap.style.display = 'none';
            if (deletedByEl) deletedByEl.textContent = '—';
        }

        var saveBtn = document.getElementById('rosterShiftSaveBtn');
        if (shift && shift.deletedAt) {
            if (titleEl) titleEl.textContent = 'View Deleted Shift';
            if (saveBtn) saveBtn.style.display = 'none';
            if (deleteWrap) deleteWrap.style.display = 'none';
            if (shiftSelect) shiftSelect.disabled = true;
            if (notesEl) notesEl.disabled = true;
            if (floorEl) floorEl.disabled = true;
        } else {
            if (saveBtn) saveBtn.style.display = 'inline-block';
            if (shiftSelect) shiftSelect.disabled = false;
            if (notesEl) notesEl.disabled = false;
            if (floorEl) floorEl.disabled = false;
        }

        var offcanvas = bootstrap.Offcanvas.getOrCreateInstance(canvas);
        offcanvas.show();
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

        var parsedEmployeeId = parseInt(employeeId, 10);
        if (!employeeId || !rosterDate || !shiftPlannerId || !Number.isFinite(parsedEmployeeId) || parsedEmployeeId <= 0) {
            showError('Employee is required.');
            return;
        }

        var payload = {
            employee_id: parsedEmployeeId,
            employee_type: employeeType,
            shift_planner_id: parseInt(shiftPlannerId, 10),
            roster_date: rosterDate,
            start_time: document.getElementById('rosterStartTime').value,
            end_time: document.getElementById('rosterEndTime').value,
            check_in: document.getElementById('rosterCheckIn').value,
            check_out: document.getElementById('rosterCheckOut').value,
            floor: document.getElementById('rosterFloor').value,
            late_check_in: document.getElementById('rosterLateCheckIn')?.checked ? 1 : 0
        };
        if (notes) payload.notes = notes;

        console.log("Submitting Roster Assignment:", payload);

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
                console.log("Roster Save Response:", res);
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
                        msg = Object.values(res.body.errors).flat().join('<br>'); // showSuccess/showError supports HTML
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
                var opt = this.options[this.selectedIndex];
                if (opt && opt.value) {
                    var start = opt.getAttribute('data-start');
                    var end = opt.getAttribute('data-end');
                    if (start) document.getElementById('rosterStartTime').value = start;
                    if (end) document.getElementById('rosterEndTime').value = end;
                    // Default check-in/out to start/end
                    if (start) document.getElementById('rosterCheckIn').value = start;
                    if (end) document.getElementById('rosterCheckOut').value = end;
                } else {
                    document.getElementById('rosterStartTime').value = '';
                    document.getElementById('rosterEndTime').value = '';
                    document.getElementById('rosterCheckIn').value = '';
                    document.getElementById('rosterCheckOut').value = '';
                }
            });
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
