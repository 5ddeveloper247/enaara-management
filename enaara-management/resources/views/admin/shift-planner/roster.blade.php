<div class="row">
    <!-- Roster Calendar -->
    <div class="col-lg-9">
        <div class="border-0 rounded-4">
            <div class="card-body p-3 mb-4">
                <!-- Calendar Header Controls -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="border-0 bg-transparent p-0" id="prevMonthBtn">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <h6 class="mb-0" id="currentMonthYear"></h6>
                        <button type="button" class="border-0 bg-transparent p-0" id="nextMonthBtn">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                        <button style="font-size: 11px;" type="button" class="btn btn-sm btn-outline-primary ms-2" id="todayBtn">
                            <i class="bi bi-calendar-event me-1"></i>Today
                        </button>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <!-- View Switcher -->
                        <div class="btn-group" role="group">
                            <button style="font-size: 11px;" type="button" class="btn btn-sm d-flex align-items-center gap-1 btn-outline-secondary active" data-view="dayGridMonth" id="viewMonthBtn">
                                <i class="bi bi-calendar3 me-1 small"></i>Month
                            </button>
                            <button style="font-size: 11px;" type="button" class="btn btn-sm d-flex align-items-center gap-1 btn-outline-secondary" data-view="dayGridWeek" id="viewWeekBtn">
                                <i class="bi bi-calendar-week me-1 small"></i>Week
                            </button>
                            <button style="font-size: 11px;" type="button" class="btn btn-sm d-flex align-items-center gap-1 btn-outline-secondary" data-view="dayGridDay" id="viewDayBtn">
                                <i class="bi bi-calendar-day me-1 small"></i>Day
                            </button>
                        </div>
                        {{-- <button type="button" class="btn btn-sm d-flex align-items-center gap-1 btn-outline-primary" id="shiftRotationBtn">
                            <i class="bi bi-arrow-repeat me-1"></i>Shift Rotation
                        </button> --}}
                        <button style="font-size: 11px;" type="button" class="btn btn-sm d-flex align-items-center gap-1 btn-warning" id="conflictCheckBtn">
                            <i class="bi bi-exclamation-triangle me-1"></i>Check Conflicts
                        </button>
                    </div>
                </div>

                <!-- FullCalendar Container -->
                <div id="rosterCalendar"></div>

                <!-- Conflict Alerts -->
                <div id="conflictAlerts" class="mt-4" style="display: none;">
                    <div class="" style="background-color: rgba(255, 166, 0, 0.288); color: rgb(170, 111, 0)" id="conflictAlertBox">
                        <h6 class="alert-heading" style="color: #8c4f00;" id="conflictAlertHeading">
                            <i class="bi bi-exclamation-triangle me-2"></i>Shift Conflicts Detected
                        </h6>
                        <div id="conflictList">
                            <!-- Conflicts will be populated here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Sidebar -->
    <div class="col-lg-3">
        <div class="card border-0 rounded-4 mb-4">
            <div class="card-body p-4">
                <h6 class="fw-semibold mb-3">
                    <i class="bi bi-funnel me-2"></i>Filters
                </h6>

                <!-- Organization Filter -->
                <div class="mb-4">
                    <label class="form-label small fw-semibold text-muted mb-2">Organization</label>
                    <select class="form-select form-select-sm" id="filterOrganization">
                        <option value="">All Organizations</option>
                        <option value="1">Enaara Construction</option>
                        <option value="2">MSR Group</option>
                        <option value="3">Swiss Builders</option>
                    </select>
                </div>

                <!-- Site Location Filter -->
                <div class="mb-4">
                    <label class="form-label small fw-semibold text-muted mb-2">Site Location</label>
                    <select class="form-select form-select-sm" id="filterSiteLocation">
                        <option value="">All Sites</option>
                        <option value="head-office">Head Office</option>
                        <option value="site-1">Site 1</option>
                        <option value="site-2">Site 2</option>
                        <option value="branch-a">Branch A</option>
                        <option value="branch-b">Branch B</option>
                    </select>
                </div>

                <!-- Employee Type Filter -->
                <div class="mb-4">
                    <label class="form-label small fw-semibold text-muted mb-2">Employee Type</label>
                    <div class="bg-transparent">
                        <label class="list-group-item list-group-item-action border-0 px-0 py-2 cursor-pointer">
                            <input class="form-check-input me-2" type="checkbox" value="all" id="filterEmployeeTypeAll" checked>
                            All Types
                        </label>
                        <label class="list-group-item list-group-item-action border-0 px-0 py-2 cursor-pointer">
                            <input class="form-check-input me-2" type="checkbox" value="internal" id="filterEmployeeTypeInternal">
                            Internal
                        </label>
                        <label class="list-group-item list-group-item-action border-0 px-0 py-2 cursor-pointer">
                            <input class="form-check-input me-2" type="checkbox" value="third-party" id="filterEmployeeTypeThirdParty">
                            Third-party
                        </label>
                    </div>
                </div>

                <!-- Department Filter -->
                <div class="mb-4">
                    <label class="form-label small fw-semibold text-muted mb-2">Department</label>
                    <select class="form-select form-select-sm" id="filterDepartment">
                        <option value="">All Departments</option>
                        <option value="sales">Sales</option>
                        <option value="it">IT</option>
                        <option value="hr">HR</option>
                        <option value="operations">Operations</option>
                        <option value="finance">Finance</option>
                    </select>
                </div>

                <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="clearRosterFiltersBtn">
                    <i class="bi bi-x-circle me-1"></i>Clear Filters
                </button>
            </div>
        </div>

        <!-- Shift Legend -->
        <div class="card border-0 rounded-4">
            <div class="card-body p-4">
                <h6 class="fw-semibold mb-3">
                    <i class="bi bi-palette me-2"></i>Shift Legend
                </h6>
                <div id="shiftLegend">
                    <div class="d-flex align-items-center mb-2">
                        <div class="badge bg-primary me-2" style="width: 20px; height: 20px;"></div>
                        <small>Morning Shift</small>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <div class="badge bg-info me-2" style="width: 20px; height: 20px;"></div>
                        <small>Night Shift</small>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <div class="badge bg-success me-2" style="width: 20px; height: 20px;"></div>
                        <small>Site Sales - Weekend</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="badge bg-warning me-2" style="width: 20px; height: 20px;"></div>
                        <small>Other Shifts</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let rosterCalendar = null;
let selectedDates = null;

// Shift color
const shiftColors = {
    '1': { bg: 'rgba(0, 57, 171, 0.3)', border: '#0a58ca', name: 'Morning Shift' },
    '2': { bg: 'rgba(13, 202, 240, 0.3)', border: '#0aa2c0', name: 'Night Shift' },
    '3': { bg: 'rgba(25, 135, 84, 0.3)', border: '#146c43', name: 'Site Sales - Weekend' }
};

// Generate dummy roster events
function generateDummyRosterEvents() {
    const today = new Date();
    const currentYear = today.getFullYear();
    const currentMonth = today.getMonth();
    const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
    
    const employees = [
        { id: '1', name: 'John Doe' },
        { id: '2', name: 'Sarah Miller' },
        { id: '3', name: 'Robert Kim' },
        { id: '4', name: 'Emma Wilson' },
        { id: '5', name: 'Michael Johnson' },
        { id: '6', name: 'Lisa Anderson' },
        { id: '7', name: 'David Brown' },
        { id: '8', name: 'Jennifer Lee' },
        { id: '9', name: 'James Taylor' },
        { id: '10', name: 'Maria Garcia' }
    ];
    
    const events = [];
    let eventId = 1;
    
    // Generate events for the current month
    for (let day = 1; day <= daysInMonth; day++) {
        const date = new Date(currentYear, currentMonth, day);
        const dayOfWeek = date.getDay(); // 0 = Sunday, 6 = Saturday
        
        // Skip weekends for most employees (except weekend shift workers)
        if (dayOfWeek === 0 || dayOfWeek === 6) {
            // Weekend shifts - assign to specific employees
            if (day % 2 === 0) {
                events.push({
                    id: String(eventId++),
                    employeeId: '4',
                    employeeName: 'Emma Wilson',
                    shiftId: '3',
                    shiftName: 'Site Sales - Weekend',
                    start: `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`,
                    end: `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`
                });
            }
            if (day % 3 === 0) {
                events.push({
                    id: String(eventId++),
                    employeeId: '6',
                    employeeName: 'Lisa Anderson',
                    shiftId: '3',
                    shiftName: 'Site Sales - Weekend',
                    start: `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`,
                    end: `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`
                });
            }
        } else {
            // Weekday shifts
            // Morning shifts - assign to multiple employees
            const morningEmployees = employees.slice(0, 6); // First 6 employees
            morningEmployees.forEach((emp, index) => {
                if ((day + index) % 2 === 0 || (day + index) % 3 === 0) {
                    events.push({
                        id: String(eventId++),
                        employeeId: emp.id,
                        employeeName: emp.name,
                        shiftId: '1',
                        shiftName: 'Morning Shift',
                        start: `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`,
                        end: `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`
                    });
                }
            });
            
            // Night shifts - assign to different employees
            const nightEmployees = employees.slice(2, 8); // Middle employees
            nightEmployees.forEach((emp, index) => {
                if ((day + index) % 3 === 0 || (day + index) % 4 === 0) {
                    events.push({
                        id: String(eventId++),
                        employeeId: emp.id,
                        employeeName: emp.name,
                        shiftId: '2',
                        shiftName: 'Night Shift',
                        start: `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`,
                        end: `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`
                    });
                }
            });
        }
    }
    
    return events;
}

const rosterEvents = generateDummyRosterEvents();

function initRosterCalendar() {
    const calendarEl = document.getElementById('rosterCalendar');
    if (!calendarEl) return;
    
    // Destroy existing calendar if any
    if (rosterCalendar) {
        rosterCalendar.destroy();
        rosterCalendar = null;
    }

    rosterCalendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: false,
        height: 'auto',
        editable: false,
        selectable: false,
        selectMirror: false,
        dayMaxEvents: 3,
        moreLinkClick: 'popover',
        weekends: true,
        initialDate: new Date(),
        locale: 'en',
        firstDay: 0, // Sunday
        dayHeaderFormat: { weekday: 'short' },
        dayMaxEventRows: 3,
        moreLinkText: 'more',
        
        // Date selection - disabled for frontend view only
        select: false,
        
        // Event click handler - view only
        eventClick: function(arg) {
            // Frontend view only - no action needed
        },
        
        // Event drag and drop - disabled for frontend view
        eventDrop: false,
        
        // Event resize - disabled for frontend view
        eventResize: false,
        
        // Load events
        events: function(fetchInfo, successCallback, failureCallback) {
            // Filter events based on current view dates
            const filteredEvents = rosterEvents
                .filter(event => {
                    const eventDate = new Date(event.start);
                    return eventDate >= fetchInfo.start && eventDate <= fetchInfo.end;
                })
                .map(event => {
                    const shiftColor = shiftColors[event.shiftId] || { bg: '#6c757d', border: '#5c636a', name: 'Unknown' };
                    return {
                        id: event.id,
                        title: `${event.shiftName} - ${event.employeeName}`,
                        start: event.start,
                        end: event.end,
                        backgroundColor: shiftColor.bg,
                        borderColor: shiftColor.border,
                        textColor: '#ffffff',
                        extendedProps: {
                            employeeId: event.employeeId,
                            employeeName: event.employeeName,
                            shiftId: event.shiftId,
                            shiftName: event.shiftName
                        },
                        classNames: ['roster-event']
                    };
                });
            
            successCallback(filteredEvents);
        },
        
        // Customize event rendering
        eventDidMount: function(arg) {
            const event = arg.event;
            const extendedProps = event.extendedProps;
            
            // Add tooltip
            arg.el.setAttribute('data-bs-toggle', 'tooltip');
            arg.el.setAttribute('title', `${extendedProps.employeeName} - ${extendedProps.shiftName}`);
            
            // Add custom styling
            arg.el.style.fontSize = '0.75rem';
            arg.el.style.padding = '2px 6px';
            arg.el.style.cursor = 'pointer';
            
            // Initialize Bootstrap tooltip
            if (typeof bootstrap !== 'undefined') {
                new bootstrap.Tooltip(arg.el);
            }
        },
        
        // Day click handler - disabled for frontend view
        dateClick: false,
        
        // View change handler
        datesSet: function(arg) {
            updateMonthYear(arg.start, arg.end, arg.view.type);
        }
    });

    rosterCalendar.render();
    updateMonthYear();
}

function updateMonthYear(startDate = null, endDate = null, viewType = null) {
    if (rosterCalendar) {
        const date = startDate || rosterCalendar.getDate();
        const view = viewType || rosterCalendar.view.type;
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'];
        const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        
        let displayText = '';
        
        if (view === 'dayGridDay') {
            displayText = dayNames[date.getDay()] + ', ' + monthNames[date.getMonth()] + ' ' + date.getDate() + ', ' + date.getFullYear();
        } else if (view === 'dayGridWeek') {
            const weekStart = new Date(date);
            weekStart.setDate(date.getDate() - date.getDay());
            const weekEnd = new Date(weekStart);
            weekEnd.setDate(weekStart.getDate() + 6);
            
            if (weekStart.getMonth() === weekEnd.getMonth()) {
                displayText = monthNames[weekStart.getMonth()] + ' ' + weekStart.getDate() + ' - ' + weekEnd.getDate() + ', ' + weekStart.getFullYear();
            } else {
                displayText = monthNames[weekStart.getMonth()] + ' ' + weekStart.getDate() + ' - ' + monthNames[weekEnd.getMonth()] + ' ' + weekEnd.getDate() + ', ' + weekStart.getFullYear();
            }
        } else {
            displayText = monthNames[date.getMonth()] + ' ' + date.getFullYear();
        }
        
        const monthYearEl = document.getElementById('currentMonthYear');
        if (monthYearEl) {
            monthYearEl.textContent = displayText;
        }
    }
}

// Calendar navigation
document.addEventListener('DOMContentLoaded', function() {
    // Previous month button
    const prevMonthBtn = document.getElementById('prevMonthBtn');
    if (prevMonthBtn) {
        prevMonthBtn.addEventListener('click', function() {
            if (rosterCalendar) {
                rosterCalendar.prev();
                updateMonthYear();
            }
        });
    }

    // Next month button
    const nextMonthBtn = document.getElementById('nextMonthBtn');
    if (nextMonthBtn) {
        nextMonthBtn.addEventListener('click', function() {
            if (rosterCalendar) {
                rosterCalendar.next();
                updateMonthYear();
            }
        });
    }

    // Today button
    const todayBtn = document.getElementById('todayBtn');
    if (todayBtn) {
        todayBtn.addEventListener('click', function() {
            if (rosterCalendar) {
                rosterCalendar.today();
                updateMonthYear();
            }
        });
    }

    // Filter handlers - reload calendar when filters change
    const filterOrganization = document.getElementById('filterOrganization');
    const filterSiteLocation = document.getElementById('filterSiteLocation');
    const filterDepartment = document.getElementById('filterDepartment');
    
    [filterOrganization, filterSiteLocation, filterDepartment].forEach(function(filter) {
        if (filter) {
            filter.addEventListener('change', function() {
                if (rosterCalendar) {
                    rosterCalendar.refetchEvents();
                }
            });
        }
    });

    const filterEmployeeTypeAll = document.getElementById('filterEmployeeTypeAll');
    const filterEmployeeTypeInternal = document.getElementById('filterEmployeeTypeInternal');
    const filterEmployeeTypeThirdParty = document.getElementById('filterEmployeeTypeThirdParty');
    
    [filterEmployeeTypeAll, filterEmployeeTypeInternal, filterEmployeeTypeThirdParty].forEach(function(filter) {
        if (filter) {
            filter.addEventListener('change', function() {
                if (rosterCalendar) {
                    rosterCalendar.refetchEvents();
                }
            });
        }
    });

    // Clear filters button
    const clearRosterFiltersBtn = document.getElementById('clearRosterFiltersBtn');
    if (clearRosterFiltersBtn) {
        clearRosterFiltersBtn.addEventListener('click', function() {
            if (filterOrganization) filterOrganization.value = '';
            if (filterSiteLocation) filterSiteLocation.value = '';
            if (filterDepartment) filterDepartment.value = '';
            if (filterEmployeeTypeAll) filterEmployeeTypeAll.checked = true;
            if (filterEmployeeTypeInternal) filterEmployeeTypeInternal.checked = false;
            if (filterEmployeeTypeThirdParty) filterEmployeeTypeThirdParty.checked = false;
            
            if (rosterCalendar) {
                rosterCalendar.refetchEvents();
            }
        });
    }

    // View switcher
    const viewMonthBtn = document.getElementById('viewMonthBtn');
    const viewWeekBtn = document.getElementById('viewWeekBtn');
    const viewDayBtn = document.getElementById('viewDayBtn');
    
    [viewMonthBtn, viewWeekBtn, viewDayBtn].forEach(function(btn) {
        if (btn) {
            btn.addEventListener('click', function() {
                const view = this.getAttribute('data-view');
                if (rosterCalendar) {
                    rosterCalendar.changeView(view);
                    // Update active button
                    [viewMonthBtn, viewWeekBtn, viewDayBtn].forEach(function(b) {
                        if (b) b.classList.remove('active');
                    });
                    this.classList.add('active');
                    // Update display text
                    updateMonthYear();
                }
            });
        }
    });

    // Conflict check button
    const conflictCheckBtn = document.getElementById('conflictCheckBtn');
    if (conflictCheckBtn) {
        conflictCheckBtn.addEventListener('click', function() {
            if (rosterCalendar) {
                checkRosterConflicts();
            }
        });
    }

    // Shift rotation button - disabled for frontend view
    const shiftRotationBtn = document.getElementById('shiftRotationBtn');
    if (shiftRotationBtn) {
        shiftRotationBtn.addEventListener('click', function() {
            // Frontend view only - no action
        });
    }
});

// Function to check for conflicts in roster
function checkRosterConflicts() {
    if (!rosterCalendar) return;
    
    const events = rosterCalendar.getEvents();
    const conflicts = [];
    const employeeDates = {};
    
    // Group events by employee and date
    events.forEach(function(event) {
        const employeeId = event.extendedProps.employeeId;
        const date = event.startStr;
        
        if (!employeeDates[employeeId]) {
            employeeDates[employeeId] = {};
        }
        if (!employeeDates[employeeId][date]) {
            employeeDates[employeeId][date] = [];
        }
        employeeDates[employeeId][date].push(event);
    });
    
    // Check for double assignments (same employee, same date)
    Object.keys(employeeDates).forEach(function(employeeId) {
        Object.keys(employeeDates[employeeId]).forEach(function(date) {
            if (employeeDates[employeeId][date].length > 1) {
                const employeeName = employeeDates[employeeId][date][0].extendedProps.employeeName;
                const shiftNames = employeeDates[employeeId][date].map(e => e.extendedProps.shiftName).join(', ');
                conflicts.push({
                    type: 'double',
                    employee: employeeName,
                    date: date,
                    shifts: shiftNames
                });
            }
        });
    });
    
    // Display conflicts
    const conflictAlerts = document.getElementById('conflictAlerts');
    const conflictList = document.getElementById('conflictList');
    const conflictAlertBox = document.getElementById('conflictAlertBox');
    const conflictAlertHeading = document.getElementById('conflictAlertHeading');
    
    if (conflicts.length > 0) {
        conflictList.innerHTML = conflicts.map(function(conflict) {
            if (conflict.type === 'double') {
                return `
                    <div class="conflict-item" style="border: 1px solid #ffc107;">
                        <strong class="small">${conflict.employee}</strong> - Multiple shifts assigned on ${conflict.date}: ${conflict.shifts}
                    </div>
                `;
            }
            return '';
        }).join('');
        conflictAlertBox.className = 'alert alert-warning small';
        conflictAlertHeading.innerHTML = '<i class="bi bi-exclamation-triangle me-2 small"></i>Shift Conflicts Detected';
        conflictAlerts.style.display = 'block';
    } else {
        conflictList.innerHTML = '<div class="text-success"><i class="bi bi-check-circle me-2"></i>No conflicts detected!</div>';
        conflictAlertBox.className = 'alert alert-success';
        conflictAlertHeading.innerHTML = '<i class="bi bi-check-circle me-2"></i>No Conflicts';
        conflictAlerts.style.display = 'block';
        
        // Reset to warning after 3 seconds
        setTimeout(function() {
            conflictAlerts.style.display = 'none';
            conflictAlertBox.className = 'alert alert-warning small';
            conflictAlertHeading.innerHTML = '<i class="bi bi-exclamation-triangle me-2 small"></i>Shift Conflicts Detected';
        }, 3000);
    }
}

// Function to add event to calendar (called after bulk assign)
function addRosterEvent(eventData) {
    if (rosterCalendar) {
        const shiftColor = shiftColors[eventData.shiftId] || { bg: '#6c757d', border: '#5c636a', name: 'Unknown' };
        
        rosterCalendar.addEvent({
            id: eventData.id || 'event-' + Date.now(),
            title: `${eventData.shiftName} - ${eventData.employeeName}`,
            start: eventData.start,
            end: eventData.end || eventData.start,
            backgroundColor: shiftColor.bg,
            borderColor: shiftColor.border,
            textColor: '#ffffff',
            extendedProps: {
                employeeId: eventData.employeeId,
                employeeName: eventData.employeeName,
                shiftId: eventData.shiftId,
                shiftName: eventData.shiftName
            }
        });
    }
}

// Function to refresh calendar events
function refreshRosterCalendar() {
    if (rosterCalendar) {
        rosterCalendar.refetchEvents();
    }
}
</script>

