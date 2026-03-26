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

            <!-- Sidebar Stats -->
            <div class="col-md-3">
                @include('admin.leave-calendar.sidebar_stats')
            </div>
        </div>
    </div>

    <!-- Event Detail Canvas -->
    @include('admin.leave-calendar.event_detail_canvas')
    <!-- Add Holiday Canvas -->
    @include('admin.leave-calendar.add_holiday_canvas')
    <!-- All Holidays List Canvas -->
    @include('admin.leave-calendar.all_holidays_canvas')
@endsection

@push('scripts')
    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

    <script>
        let calendar;
        let calendarEl;

        // Comprehensive Sample Data - Shows everything in the calendar
        const publicHolidays = @json($publicHolidays);
        const deptLeaves = @json($deptLeaves);
        const blackoutDates = @json($blackoutDates);


        document.addEventListener('DOMContentLoaded', function() {
            initializeCalendar();
            populateSidebarStats();

            // Offcanvas Reset Listener
            const addHolidayCanvasEl = document.getElementById('addHolidayCanvas');
            if (addHolidayCanvasEl) {
                addHolidayCanvasEl.addEventListener('show.bs.offcanvas', function (event) {
                    if (event.relatedTarget) { // Opened via "Add" button
                        const form = document.getElementById('addHolidayForm');
                        form.reset();
                        form.dataset.mode = 'add';
                        document.getElementById('addHolidayCanvasLabel').textContent = 'Add Public Holiday';
                        form.action = "{{ route('admin.leave-calendar.store') }}";
                        const deleteBtn = document.getElementById('deleteHolidayBtn');
                        if (deleteBtn) deleteBtn.classList.add('d-none');
                        
                        // Reset organizations select if using select2 or similar, 
                        // but here we use native select. Just manual reset:
                        const orgSelect = document.getElementById('holidayOrganizations');
                        Array.from(orgSelect.options).forEach(opt => opt.selected = false);
                        document.getElementById('organizationSelectSection').style.display = 'none';
                    }
                });
            }
        });

        function initializeCalendar() {
            calendarEl = document.getElementById('leaveCalendar');
            
            // Prepare events for FullCalendar
            const events = [];

            // Add public holidays (blue dots)
            publicHolidays.forEach(holiday => {
                if (!holiday.is_blackout) {
                    events.push({
                        title: holiday.name,
                        start: holiday.start_date,
                        allDay: true,
                        classNames: ['holiday-event'],
                        extendedProps: {
                            id: holiday.id,
                            type: 'holiday',
                            organization: holiday.organization_scope
                        }
                    });
                }
            });

            // Add departmental leaves (heatmap bars)
            deptLeaves.forEach(leave => {
                const percentage = (leave.count / leave.total) * 100;
                let intensity = 'low';
                if (percentage >= 30) intensity = 'high';
                else if (percentage >= 20) intensity = 'medium';

                events.push({
                    title: `${leave.department}: ${leave.count} on leave`,
                    start: leave.date,
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

            // Add blackout dates (red border)
            blackoutDates.forEach(blackout => {
                events.push({
                    title: blackout.reason,
                    start: blackout.date,
                    allDay: true,
                    classNames: ['blackout-date'],
                    extendedProps: {
                        type: 'blackout',
                        reason: blackout.reason
                    }
                });
            });

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
                events: events,
                eventDisplay: 'block',
                dayMaxEvents: 5, // Show more events per day
                moreLinkClick: 'popover',
                eventDidMount: function(info) {
                    // Custom rendering for different event types
                    const eventType = info.event.extendedProps.type;
                    
                    if (eventType === 'holiday') {
                        // Add blue dot indicator
                        const dot = document.createElement('div');
                        dot.className = 'holiday-dot';
                        info.el.appendChild(dot);
                    } else if (eventType === 'blackout') {
                        // Add red border
                        info.el.style.border = '2px solid #dc3545';
                        info.el.style.borderRadius = '4px';
                    }
                },
                eventClick: function(info) {
                    info.jsEvent.preventDefault();
                    showEventDetails(info.event);
                },
                dateClick: function(info) {
                    // Optionally open add holiday form for clicked date
                }
            });

            calendar.render();

            // Today button (Top Right)
            document.getElementById('todayBtn').addEventListener('click', function() {
                calendar.changeView('timeGridDay');
                calendar.today();
            });
        }

        function showEventDetails(event) {
            const props = event.extendedProps;
            const canvasEl = document.getElementById('eventDetailCanvas');
            const canvas = bootstrap.Offcanvas.getOrCreateInstance(canvasEl);
            
            // Set title
            document.getElementById('eventTitle').textContent = event.title;
            document.getElementById('eventDate').textContent = event.start.toLocaleDateString('en-US', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });

            // Hide all sections first
            document.getElementById('eventOrganizationSection').style.display = 'none';
            document.getElementById('eventDepartmentSection').style.display = 'none';
            document.getElementById('eventReasonSection').style.display = 'none';
            document.getElementById('leaveStatsSection').style.display = 'none';
            document.getElementById('impactLevelSection').style.display = 'none';
            document.getElementById('affectedEmployeesSection').style.display = 'none';
            document.getElementById('holidayActions').style.display = 'none';

            // Show appropriate sections based on event type
            if (props.type === 'holiday') {
                // Public Holiday - Using Bootstrap info color
                document.getElementById('eventTypeBadge').innerHTML = 
                    '<span class="badge bg-info text-dark" style="padding: 0.5rem 1rem; font-size: 0.875rem;">Public Holiday</span>';
                
                document.getElementById('eventOrganizationSection').style.display = 'block';
                document.getElementById('eventOrganization').textContent = 
                    props.organization === 'all' ? 'All Organizations' : props.organization;

                // Show Edit/Delete for holidays
                document.getElementById('holidayActions').style.display = 'block';
                document.getElementById('detailEditBtn').onclick = () => editHoliday(props.id);
                document.getElementById('detailDeleteBtn').onclick = () => deleteHoliday(props.id);
            } 
            else if (props.type === 'department-leave') {
                // Departmental Leave - Using main color (#012445) variations only
                const percentage = props.percentage;
                let badgeColor = 'rgba(1, 36, 69, 0.4)'; // Low
                let intensity = 'Low';
                
                if (percentage >= 30) {
                    badgeColor = 'var(--main-color)'; // High - main color (#012445)
                    intensity = 'High';
                } else if (percentage >= 20) {
                    badgeColor = 'rgba(1, 36, 69, 0.7)'; // Medium
                    intensity = 'Medium';
                }
                
                document.getElementById('eventTypeBadge').innerHTML = 
                    `<span class="badge" style="background-color: ${badgeColor}; padding: 0.5rem 1rem; font-size: 0.875rem;">Departmental Leave - ${intensity} Impact</span>`;
                
                document.getElementById('eventDepartmentSection').style.display = 'block';
                document.getElementById('eventDepartment').textContent = props.department;
                
                // Show leave statistics
                document.getElementById('leaveStatsSection').style.display = 'block';
                document.getElementById('leaveCount').textContent = props.count;
                document.getElementById('totalStaff').textContent = props.total;
                document.getElementById('leavePercentage').textContent = percentage.toFixed(1) + '%';
                
                const progressBar = document.getElementById('leaveProgressBar');
                progressBar.style.width = percentage + '%';
                progressBar.style.backgroundColor = badgeColor;
                progressBar.setAttribute('aria-valuenow', percentage);
                progressBar.setAttribute('aria-valuemin', 0);
                progressBar.setAttribute('aria-valuemax', 100);
                
                // Show impact level
                document.getElementById('impactLevelSection').style.display = 'block';
                let impactDescription = '';
                if (percentage >= 30) {
                    impactDescription = 'Critical: More than 30% of department is on leave. Consider redistributing workload.';
                } else if (percentage >= 20) {
                    impactDescription = 'Warning: 20-30% of department is on leave. Monitor workload distribution.';
                } else {
                    impactDescription = 'Normal: Less than 20% of department is on leave. Standard operations expected.';
                }
                document.getElementById('impactLevelBadge').innerHTML = 
                    `<span class="badge" style="background-color: ${badgeColor}; padding: 0.5rem 1rem; font-size: 0.875rem;">${intensity} Impact</span>`;
                document.getElementById('impactLevelDescription').textContent = impactDescription;
                
                // Show affected employees (Real data via AJAX)
                document.getElementById('affectedEmployeesSection').style.display = 'block';
                const employeesList = document.getElementById('affectedEmployeesList');
                employeesList.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-light" role="status"></div><div class="small mt-2">Loading employees...</div></div>';
                
                // Format date as YYYY-MM-DD (Local Timezone safe)
                const d = event.start;
                const dateStr = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
                const fetchUrl = `{{ route('admin.leave-calendar.fetch-department-employees') }}?date=${dateStr}&department_id=${props.department_id}`;
                
                fetch(fetchUrl)
                    .then(res => res.json())
                    .then(data => {
                        employeesList.innerHTML = '';
                        if (data.success && data.employees.length > 0) {
                            data.employees.forEach(emp => {
                                const empItem = document.createElement('div');
                                empItem.className = 'd-flex align-items-center mb-2 pb-2 border-bottom';
                                empItem.style.borderColor = '#ffffff1a !important';
                                empItem.innerHTML = `
                                    <div class="user-avatar me-3">${emp.initials}</div>
                                    <div class="flex-grow-1">
                                        <div class="small fw-semibold">${emp.name}</div>
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
                        employeesList.innerHTML = '<div class="small text-warning text-center py-3">Error loading employee data.</div>';
                    });
            } 
            else if (props.type === 'blackout') {
                // Blackout Date - Full black background
                document.getElementById('eventTypeBadge').innerHTML = 
                    '<span class="badge" style="background-color: #000000; color: #ffffff; padding: 0.5rem 1rem; font-size: 0.875rem;">Blackout Date</span>';
                
                document.getElementById('eventReasonSection').style.display = 'block';
                document.getElementById('eventReason').textContent = props.reason;
            }

            // Show canvas
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
            // Today's Leaves
            const todayStr = new Date().toISOString().split('T')[0];
            const todayLeaves = deptLeaves.filter(l => l.date === todayStr);
            const todayContainer = document.getElementById('todayLeavesList');
            
            if (todayContainer) {
                todayContainer.innerHTML = '';
                if (todayLeaves.length === 0) {
                    todayContainer.innerHTML = '<div class="text-muted small py-3 text-center border rounded-3" style="border-style: dashed !important; opacity: 0.6;">No leaves scheduled for today</div>';
                } else {
                    todayLeaves.forEach(leave => {
                        const percentage = Math.round((leave.count / leave.total) * 100);
                        let badgeClass = 'bg-success';
                        if (percentage >= 30) badgeClass = 'bg-main';
                        else if (percentage >= 20) badgeClass = 'bg-primary';
                        
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

            // Upcoming Holidays
            const upcomingHolidaysContainer = document.getElementById('upcomingHolidaysList');
            if (upcomingHolidaysContainer) {
                upcomingHolidaysContainer.innerHTML = '';
                
                const today = new Date();
                const upcoming = publicHolidays
                    .filter(h => new Date(h.start_date) >= today)
                    .sort((a, b) => new Date(a.start_date) - new Date(b.start_date))
                    .slice(0, 5);

                if (upcoming.length === 0) {
                    upcomingHolidaysContainer.innerHTML = '<div class="text-muted small">No upcoming holidays</div>';
                } else {
                    upcoming.forEach(holiday => {
                        const item = document.createElement('div');
                        item.className = 'd-flex justify-content-between align-items-center p-2 border-bottom';
                        item.innerHTML = `
                            <div class="flex-grow-1">
                                <div class="fw-semibold" style="font-size: 13px; color: #333;">${holiday.name}</div>
                                <small class="text-muted" style="font-size: 11px;">${new Date(holiday.start_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}</small>
                            </div>
                            <button class="btn btn-primary btn-sm rounded-3" 
                                    style="font-size: 10px !important; padding: 4px 10px; height: 26px;"
                                    onclick="editHoliday(${holiday.id})">
                                Holiday
                            </button>
                        `;



                        upcomingHolidaysContainer.appendChild(item);
                    });
                }
            }

            // All Holidays History
            const allHolidaysContainer = document.getElementById('allHolidaysList');
            if (allHolidaysContainer) {
                allHolidaysContainer.innerHTML = '';
                
                const today = new Date();
                const twoYearsAgo = new Date();
                twoYearsAgo.setFullYear(today.getFullYear() - 2);

                const historical = publicHolidays
                    .filter(h => new Date(h.start_date) >= twoYearsAgo && new Date(h.start_date) <= today)
                    .sort((a, b) => new Date(b.start_date) - new Date(a.start_date));

                const previewList = historical.slice(0, 3);

                if (previewList.length === 0) {
                    allHolidaysContainer.innerHTML = '<div class="text-muted small">No holiday history found</div>';
                } else {
                    previewList.forEach(holiday => {
                        const item = document.createElement('div');
                        item.className = 'd-flex justify-content-between align-items-center p-2 border-bottom';
                        item.innerHTML = `
                            <div class="flex-grow-1">
                                <div class="fw-semibold" style="font-size: 13px; color: #333;">${holiday.name}</div>
                                <small class="text-muted" style="font-size: 11px;">${new Date(holiday.start_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</small>
                            </div>
                            <button class="btn btn-primary btn-sm rounded-3" 
                                    style="font-size: 10px !important; padding: 4px 10px; height: 26px;"
                                    onclick="editHoliday(${holiday.id})">
                                Holiday
                            </button>
                        `;
                        allHolidaysContainer.appendChild(item);
                    });

                    if (historical.length > 3) {
                        const viewAllContainer = document.createElement('div');
                        viewAllContainer.className = 'text-center mt-3';
                        viewAllContainer.innerHTML = `
                            <button class="btn btn-sm btn-outline-primary w-100 rounded-3" style="font-size: 12px; padding: 6px;" onclick="showAllHolidaysHistory()">
                                View All Holidays (${historical.length - 3} more)
                            </button>
                        `;
                        allHolidaysContainer.appendChild(viewAllContainer);
                    }
                }
            }
        }

    </script>

    <script>
        function editHoliday(id) {
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
                        } catch(e) {
                            // It's likely HTML error page
                            if (text.includes('403') || text.includes('Unauthorized')) errorMsg = 'Unauthorized action (403)';
                            if (text.includes('404') || text.includes('Not Found')) errorMsg = 'Holiday not found (404)';
                        }
                        throw new Error(errorMsg);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const holiday = data.holiday;
                    const offcanvasEl = document.getElementById('addHolidayCanvas');
                    const canvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
                    
                    // Update UI to "Edit" mode
                    document.getElementById('addHolidayCanvasLabel').textContent = 'Edit Holiday';
                    const form = document.getElementById('addHolidayForm');
                    
                    // Update form action for the POST update
                    form.action = "{{ route('admin.leave-calendar.update', ['id' => ':id']) }}".replace(':id', id);
                    
                    // Set field values (formatting to YYYY-MM-DD for HTML5 date inputs)
                    document.getElementById('holidayName').value = holiday.name;
                    document.getElementById('holidayStartDate').value = holiday.start_date.split('T')[0];
                    document.getElementById('holidayEndDate').value = holiday.end_date ? holiday.end_date.split('T')[0] : '';
                    document.getElementById('isRecurring').checked = holiday.is_recurring;
                    document.getElementById('isBlackout').checked = holiday.is_blackout;
                    document.getElementById('blackoutReason').value = holiday.reason || '';
                    
                    // Set scope and organizations
                    if (holiday.organization_scope === 'all') {
                        document.getElementById('scopeAll').checked = true;
                        document.getElementById('organizationSelectSection').style.display = 'none';
                    } else {
                        document.getElementById('scopeSpecific').checked = true;
                        document.getElementById('organizationSelectSection').style.display = 'block';
                        
                        // Select organizations
                        const orgIds = holiday.organizations.map(o => o.id);
                        const select = document.getElementById('holidayOrganizations');
                        Array.from(select.options).forEach(option => {
                            option.selected = orgIds.includes(parseInt(option.value));
                        });
                    }
                    
                    // Store edit mode status
                    form.dataset.mode = 'edit';
                    form.dataset.holidayId = id;
                    
                    // Show delete button
                    const deleteBtn = document.getElementById('deleteHolidayBtn');
                    if (deleteBtn) deleteBtn.classList.remove('d-none');

                    // Handle transition with a small delay to avoid backdrop conflicts
                    const detailCanvasEl = document.getElementById('eventDetailCanvas');
                    const detailInstance = detailCanvasEl ? bootstrap.Offcanvas.getInstance(detailCanvasEl) : null;
                    
                    const allHolidaysCanvasEl = document.getElementById('allHolidaysCanvas');
                    const allHolidaysInstance = allHolidaysCanvasEl ? bootstrap.Offcanvas.getInstance(allHolidaysCanvasEl) : null;
                    
                    if (detailInstance && detailCanvasEl.classList.contains('show')) {
                        detailInstance.hide();
                        setTimeout(() => canvas.show(), 350);
                    } else if (allHolidaysInstance && allHolidaysCanvasEl.classList.contains('show')) {
                        allHolidaysInstance.hide();
                        setTimeout(() => canvas.show(), 350);
                    } else {
                        canvas.show();
                    }
                } else {
                    Swal.fire('Error', data.message || 'Could not fetch holiday details.', 'error');
                }
            })
            .catch(err => {
                console.error('Edit fetch error:', err);
                Swal.fire('Error', 'Detail: ' + err.message, 'error');
            });
        }

        function deleteHoliday(id) {
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
                            Swal.fire('Deleted!', data.message, 'success').then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .catch(err => {
                        console.error('Delete error:', err);
                        Swal.fire('Error', 'An unexpected error occurred.', 'error');
                    });
                }
            });
        }

        // Make functions global for onclick handlers
        window.editHoliday = editHoliday;
        window.deleteHoliday = deleteHoliday;
        window.showAllHolidaysHistory = showAllHolidaysHistory;

        function showAllHolidaysHistory() {
            const today = new Date();
            const twoYearsAgo = new Date();
            twoYearsAgo.setFullYear(today.getFullYear() - 2);

            const historical = publicHolidays
                .filter(h => new Date(h.start_date) >= twoYearsAgo && new Date(h.start_date) <= today)
                .sort((a, b) => new Date(b.start_date) - new Date(a.start_date));

            const fullListContainer = document.getElementById('allHolidaysFullList');
            fullListContainer.innerHTML = '';

            if (historical.length === 0) {
                fullListContainer.innerHTML = '<div class="text-muted text-center py-4">No historical holidays found in the past 2 years.</div>';
                return;
            }

            historical.forEach(holiday => {
                const item = document.createElement('div');
                item.className = 'd-flex justify-content-between align-items-center mb-0 p-3 border-bottom';
                item.style.borderColor = '#ffffff42 !important';
                item.innerHTML = `
                    <div class="flex-grow-1">
                        <div class="fw-semibold text-white" style="font-size: 14px;">${holiday.name}</div>
                        <small class="text-white opacity-75">${new Date(holiday.start_date).toLocaleDateString('en-US', { weekday: 'short', month: 'long', day: 'numeric', year: 'numeric' })}</small>
                    </div>
                    <button class="btn btn-light btn-sm rounded-3 ms-2 text-dark fw-semibold border-0" 
                            style="font-size: 11px !important; padding: 6px 12px; height: 30px;"
                            onclick="editHoliday(${holiday.id})">
                        Holiday
                    </button>
                `;
                fullListContainer.appendChild(item);
            });

            const canvas = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('allHolidaysCanvas'));
            canvas.show();
        }
    </script>

@endpush

