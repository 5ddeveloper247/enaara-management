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

    <!-- Add Holiday Canvas -->
    @include('admin.leave-calendar.add_holiday_canvas')

    <!-- Event Detail Canvas -->
    @include('admin.leave-calendar.event_detail_canvas')
@endsection

@push('scripts')
    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

    <script>
        let calendar;
        let calendarEl;

        // Comprehensive Sample Data - Shows everything in the calendar
        const publicHolidays = [
            // February 2026
            { date: '2026-02-05', name: 'Kashmir Day', organization: 'all' },
            { date: '2026-02-14', name: 'Valentine\'s Day', organization: 'all' },
            // March 2026
            { date: '2026-03-23', name: 'Pakistan Day', organization: 'all' },
            // April 2026
            { date: '2026-04-10', name: 'Eid-ul-Fitr', organization: 'all' },
            { date: '2026-04-11', name: 'Eid-ul-Fitr', organization: 'all' },
            { date: '2026-04-21', name: 'Eid Milad-un-Nabi', organization: 'all' },
            // May 2026
            { date: '2026-05-01', name: 'Labour Day', organization: 'all' },
            { date: '2026-05-14', name: 'Youm-e-Takbir', organization: 'all' },
            // June 2026
            { date: '2026-06-16', name: 'Eid-ul-Adha', organization: 'all' },
            { date: '2026-06-17', name: 'Eid-ul-Adha', organization: 'all' },
            // July 2026
            { date: '2026-07-09', name: 'Ashura', organization: 'all' },
            { date: '2026-07-10', name: 'Ashura', organization: 'all' },
            // August 2026
            { date: '2026-08-14', name: 'Independence Day', organization: 'all' },
            // September 2026
            { date: '2026-09-06', name: 'Defence Day', organization: 'all' },
            // December 2026
            { date: '2026-12-25', name: 'Christmas', organization: 'all' },
            { date: '2026-12-31', name: 'New Year\'s Eve', organization: 'all' }
        ];

        // Comprehensive fixed departmental leave data - ensures calendar is always populated
        const departmentalLeaves = [
            // February 2026 - Week 1
            { date: '2026-02-01', department: 'Sales', count: 3, total: 20 },
            { date: '2026-02-01', department: 'HR', count: 1, total: 10 },
            { date: '2026-02-01', department: 'IT', count: 2, total: 15 },
            { date: '2026-02-02', department: 'Operations', count: 2, total: 18 },
            { date: '2026-02-02', department: 'Finance', count: 1, total: 12 },
            { date: '2026-02-05', department: 'Sales', count: 8, total: 20 },
            { date: '2026-02-05', department: 'IT', count: 6, total: 15 },
            { date: '2026-02-05', department: 'Marketing', count: 3, total: 14 },
            { date: '2026-02-06', department: 'HR', count: 2, total: 10 },
            { date: '2026-02-06', department: 'Operations', count: 3, total: 18 },
            { date: '2026-02-07', department: 'Finance', count: 2, total: 12 },
            { date: '2026-02-07', department: 'Sales', count: 4, total: 20 },
            { date: '2026-02-08', department: 'IT', count: 3, total: 15 },
            { date: '2026-02-08', department: 'Operations', count: 2, total: 18 },
            { date: '2026-02-09', department: 'HR', count: 1, total: 10 },
            
            // February 2026 - Week 2
            { date: '2026-02-12', department: 'Operations', count: 7, total: 18 },
            { date: '2026-02-12', department: 'IT', count: 4, total: 15 },
            { date: '2026-02-13', department: 'Sales', count: 5, total: 20 },
            { date: '2026-02-13', department: 'Finance', count: 2, total: 12 },
            { date: '2026-02-14', department: 'Sales', count: 4, total: 20 },
            { date: '2026-02-14', department: 'HR', count: 2, total: 10 },
            { date: '2026-02-15', department: 'Sales', count: 9, total: 20 },
            { date: '2026-02-15', department: 'HR', count: 4, total: 10 },
            { date: '2026-02-15', department: 'IT', count: 5, total: 15 },
            { date: '2026-02-16', department: 'Operations', count: 4, total: 18 },
            { date: '2026-02-16', department: 'Marketing', count: 3, total: 14 },
            
            // February 2026 - Week 3
            { date: '2026-02-19', department: 'IT', count: 3, total: 15 },
            { date: '2026-02-19', department: 'Finance', count: 2, total: 12 },
            { date: '2026-02-20', department: 'IT', count: 7, total: 15 },
            { date: '2026-02-20', department: 'HR', count: 3, total: 10 },
            { date: '2026-02-20', department: 'Operations', count: 3, total: 18 },
            { date: '2026-02-21', department: 'Sales', count: 7, total: 20 },
            { date: '2026-02-21', department: 'Marketing', count: 4, total: 14 },
            { date: '2026-02-22', department: 'IT', count: 4, total: 15 },
            { date: '2026-02-22', department: 'Finance', count: 2, total: 12 },
            { date: '2026-02-23', department: 'Operations', count: 3, total: 18 },
            { date: '2026-02-23', department: 'Sales', count: 3, total: 20 },
            
            // February 2026 - Week 4
            { date: '2026-02-26', department: 'Operations', count: 6, total: 18 },
            { date: '2026-02-26', department: 'Sales', count: 5, total: 20 },
            { date: '2026-02-27', department: 'HR', count: 2, total: 10 },
            { date: '2026-02-27', department: 'IT', count: 3, total: 15 },
            { date: '2026-02-28', department: 'Finance', count: 2, total: 12 },
            { date: '2026-02-28', department: 'Marketing', count: 3, total: 14 },
            { date: '2026-02-29', department: 'Operations', count: 4, total: 18 },
            { date: '2026-02-29', department: 'Sales', count: 3, total: 20 },
            
            // March 2026 - Week 1
            { date: '2026-03-01', department: 'IT', count: 2, total: 15 },
            { date: '2026-03-01', department: 'HR', count: 1, total: 10 },
            { date: '2026-03-04', department: 'IT', count: 8, total: 15 },
            { date: '2026-03-04', department: 'Sales', count: 5, total: 20 },
            { date: '2026-03-05', department: 'HR', count: 3, total: 10 },
            { date: '2026-03-05', department: 'Operations', count: 3, total: 18 },
            { date: '2026-03-06', department: 'Finance', count: 2, total: 12 },
            { date: '2026-03-06', department: 'Marketing', count: 3, total: 14 },
            { date: '2026-03-07', department: 'Sales', count: 8, total: 20 },
            { date: '2026-03-07', department: 'Marketing', count: 5, total: 14 },
            { date: '2026-03-08', department: 'Operations', count: 5, total: 18 },
            { date: '2026-03-08', department: 'IT', count: 3, total: 15 },
            
            // March 2026 - Week 2
            { date: '2026-03-11', department: 'Finance', count: 5, total: 12 },
            { date: '2026-03-11', department: 'Sales', count: 4, total: 20 },
            { date: '2026-03-12', department: 'HR', count: 2, total: 10 },
            { date: '2026-03-12', department: 'Operations', count: 4, total: 18 },
            { date: '2026-03-13', department: 'IT', count: 3, total: 15 },
            { date: '2026-03-13', department: 'Marketing', count: 2, total: 14 },
            { date: '2026-03-14', department: 'Sales', count: 3, total: 20 },
            { date: '2026-03-15', department: 'HR', count: 5, total: 10 },
            { date: '2026-03-15', department: 'IT', count: 4, total: 15 },
            
            // March 2026 - Week 3
            { date: '2026-03-18', department: 'Operations', count: 6, total: 18 },
            { date: '2026-03-18', department: 'Sales', count: 4, total: 20 },
            { date: '2026-03-19', department: 'Sales', count: 7, total: 20 },
            { date: '2026-03-19', department: 'Finance', count: 3, total: 12 },
            { date: '2026-03-20', department: 'IT', count: 4, total: 15 },
            { date: '2026-03-20', department: 'Marketing', count: 3, total: 14 },
            { date: '2026-03-21', department: 'HR', count: 2, total: 10 },
            { date: '2026-03-21', department: 'Operations', count: 3, total: 18 },
            { date: '2026-03-22', department: 'IT', count: 6, total: 15 },
            { date: '2026-03-22', department: 'Sales', count: 4, total: 20 },
            
            // March 2026 - Week 4
            { date: '2026-03-25', department: 'Sales', count: 6, total: 20 },
            { date: '2026-03-25', department: 'Operations', count: 4, total: 18 },
            { date: '2026-03-26', department: 'HR', count: 3, total: 10 },
            { date: '2026-03-26', department: 'Finance', count: 2, total: 12 },
            { date: '2026-03-27', department: 'IT', count: 3, total: 15 },
            { date: '2026-03-27', department: 'Marketing', count: 3, total: 14 },
            { date: '2026-03-28', department: 'Operations', count: 5, total: 18 },
            { date: '2026-03-28', department: 'Sales', count: 4, total: 20 },
            { date: '2026-03-29', department: 'IT', count: 2, total: 15 }
        ];


        const blackoutDates = [
            { date: '2026-02-12', reason: 'Project Deadline - Q1 Release' },
            { date: '2026-02-13', reason: 'Project Deadline - Q1 Release' },
            { date: '2026-02-29', reason: 'Month End Closing - Finance' },
            { date: '2026-03-01', reason: 'Quarter End Closing' },
            { date: '2026-03-02', reason: 'Quarter End Closing' },
            { date: '2026-03-15', reason: 'Audit Period - No Leave' },
            { date: '2026-03-16', reason: 'Audit Period - No Leave' },
            { date: '2026-03-31', reason: 'Month End Closing - Finance' }
        ];


        document.addEventListener('DOMContentLoaded', function() {
            initializeCalendar();
            populateSidebarStats();
        });

        function initializeCalendar() {
            calendarEl = document.getElementById('leaveCalendar');
            
            // Prepare events for FullCalendar
            const events = [];

            // Add public holidays (blue dots)
            publicHolidays.forEach(holiday => {
                events.push({
                    title: holiday.name,
                    start: holiday.date,
                    allDay: true,
                    classNames: ['holiday-event'],
                    extendedProps: {
                        type: 'holiday',
                        organization: holiday.organization
                    }
                });
            });

            // Add departmental leaves (heatmap bars)
            departmentalLeaves.forEach(leave => {
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
                initialDate: '2026-02-01', // Set to February 2026 where we have data
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
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

            // Today button
            document.getElementById('todayBtn').addEventListener('click', function() {
                calendar.today();
            });
        }

        function showEventDetails(event) {
            const props = event.extendedProps;
            const canvas = new bootstrap.Offcanvas(document.getElementById('eventDetailCanvas'));
            
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

            // Show appropriate sections based on event type
            if (props.type === 'holiday') {
                // Public Holiday - Using Bootstrap info color
                document.getElementById('eventTypeBadge').innerHTML = 
                    '<span class="badge bg-info text-dark" style="padding: 0.5rem 1rem; font-size: 0.875rem;">Public Holiday</span>';
                
                document.getElementById('eventOrganizationSection').style.display = 'block';
                document.getElementById('eventOrganization').textContent = 
                    props.organization === 'all' ? 'All Organizations' : props.organization;
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
                
                // Show affected employees (sample data)
                document.getElementById('affectedEmployeesSection').style.display = 'block';
                const sampleEmployees = generateSampleEmployees(props.department, props.count);
                const employeesList = document.getElementById('affectedEmployeesList');
                employeesList.innerHTML = '';
                sampleEmployees.forEach(emp => {
                    const empItem = document.createElement('div');
                    empItem.className = 'd-flex align-items-center mb-2 pb-2 border-bottom';
                    empItem.style.borderColor = '#ffffff1a !important';
                    empItem.innerHTML = `
                        <div class="user-avatar me-3">${emp.initials}</div>
                        <div class="flex-grow-1">
                            <div class="small fw-semibold">${emp.name}</div>
                            <small class="opacity-75 text-white">${emp.id} • ${emp.leaveType}</small>
                        </div>
                    `;
                    employeesList.appendChild(empItem);
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
                'John Doe', 'Sarah Miller', 'Robert Kim', 'Emma Wilson', 'Michael Johnson',
                'Lisa Anderson', 'David Brown', 'Jennifer Lee', 'Chris Taylor', 'Amanda White'
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
            // Upcoming Holidays
            const upcomingHolidaysContainer = document.getElementById('upcomingHolidaysList');
            if (upcomingHolidaysContainer) {
                upcomingHolidaysContainer.innerHTML = '';
                
                const today = new Date();
                const upcoming = publicHolidays
                    .filter(h => new Date(h.date) >= today)
                    .sort((a, b) => new Date(a.date) - new Date(b.date))
                    .slice(0, 5);

                if (upcoming.length === 0) {
                    upcomingHolidaysContainer.innerHTML = '<div class="text-muted small">No upcoming holidays</div>';
                } else {
                    upcoming.forEach(holiday => {
                        const item = document.createElement('div');
                        item.className = 'd-flex justify-content-between align-items-center p-2 border-bottom';
                        item.innerHTML = `
                            <div>
                                <div class="small fw-semibold">${holiday.name}</div>
                                <small class="text-muted">${new Date(holiday.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}</small>
                            </div>
                            <span class="badge bg-info text-dark">Holiday</span>
                        `;
                        upcomingHolidaysContainer.appendChild(item);
                    });
                }
            }
        }
    </script>
@endpush

