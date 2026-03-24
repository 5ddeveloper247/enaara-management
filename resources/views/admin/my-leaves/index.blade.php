@extends('layouts.app')

@section('title', 'My Leaves - Admin Panel')

@section('page-title', 'My Leaves')

@push('styles')
    <!-- My Leaves Module CSS -->
    <link href="{{ asset('css/my-leaves.css') }}" rel="stylesheet">

    <style>
        .btn {
            font-size: 13px;
        }

        .card {
            border-radius: 1rem;
        }

        .donut-chart-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h5 class="mb-0">My Leave Dashboard</h5>
                <small class="text-muted">Manage your personal leave requests and balance</small>
            </div>
            <div class="col-md-6 text-end">
                <button type="button" class="btn btn-primary bg-main border-0" data-bs-toggle="offcanvas"
                    data-bs-target="#addLeaveRequestCanvas">
                    <i class="bi bi-plus-circle me-1"></i>Quick Request
                </button>
            </div>
        </div>

        <!-- Balance Overview -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card border-0 rounded-4">
                    <div class="card-header bg-transparent border-0 p-4">
                        <h6 class="mb-0 fw-semibold">Leave Balance Overview</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <!-- Annual Leave -->
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="donut-chart-container">
                                        <canvas id="annualLeaveChart"></canvas>
                                        <div class="chart-center-text">
                                            <div class="chart-number" id="annualUsed">{{ $personalQuota['annual']['used'] }}</div>
                                            <div class="chart-label">Used</div>
                                            <div class="chart-total">of {{ $personalQuota['annual']['total'] }}</div>
                                        </div>
                                    </div>
                                    <h6 class="mt-3 mb-1">Annual Leave</h6>
                                    <small class="text-muted">{{ $personalQuota['annual']['remaining'] }} days remaining</small>
                                </div>
                            </div>

                            <!-- Sick Leave -->
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="donut-chart-container">
                                        <canvas id="sickLeaveChart"></canvas>
                                        <div class="chart-center-text">
                                            <div class="chart-number" id="sickUsed">{{ $personalQuota['sick']['used'] }}</div>
                                            <div class="chart-label">Used</div>
                                            <div class="chart-total">of {{ $personalQuota['sick']['total'] }}</div>
                                        </div>
                                    </div>
                                    <h6 class="mt-3 mb-1">Sick Leave</h6>
                                    <small class="text-muted">{{ $personalQuota['sick']['remaining'] }} days remaining</small>
                                </div>
                            </div>

                            <!-- Casual Leave -->
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="donut-chart-container">
                                        <canvas id="casualLeaveChart"></canvas>
                                        <div class="chart-center-text">
                                            <div class="chart-number" id="casualUsed">{{ $personalQuota['casual']['used'] }}</div>
                                            <div class="chart-label">Used</div>
                                            <div class="chart-total">of {{ $personalQuota['casual']['total'] }}</div>
                                        </div>
                                    </div>
                                    <h6 class="mt-3 mb-1">Casual Leave</h6>
                                    <small class="text-muted">{{ $personalQuota['casual']['remaining'] }} days remaining</small>
                                </div>
                            </div>

                            <!-- Compensatory Off -->
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="donut-chart-container">
                                        <canvas id="compOffChart"></canvas>
                                        <div class="chart-center-text">
                                            <div class="chart-number" id="compOffUsed">{{ $personalQuota['compOff']['used'] }}</div>
                                            <div class="chart-label">Used</div>
                                            <div class="chart-total">of {{ $personalQuota['compOff']['total'] }}</div>
                                        </div>
                                    </div>
                                    <h6 class="mt-3 mb-1">Compensatory Off</h6>
                                    <small class="text-muted">{{ $personalQuota['compOff']['remaining'] }} days available</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Leave Timeline -->
            <div class="col-md-12"> <!-- Expanded to full width if needed, or keep 8 -->
                <div class="card border-0 rounded-4">
                    <div class="card-header bg-transparent border-0 p-4">
                        <h6 class="mb-0 fw-semibold">Leave Timeline</h6>
                    </div>
                    <div class="card-body">
                        <div id="leaveTimeline"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Request Canvas -->
    @include('admin.leave-requests.add_leave_request_canvas')

    <!-- Leave Detail Canvas -->
    @include('admin.my-leaves.leave_detail_canvas')
@endsection

@push('scripts')
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <script>
        // Real leave data from controller
        const myLeaves = @json($personalQuota);
        const leaveHistory = @json($personalHistory);

        const upcomingHolidays = [
            { date: '2024-02-14', name: 'Valentine\'s Day', type: 'public' },
            { date: '2024-03-23', name: 'Pakistan Day', type: 'public' },
            { date: '2024-05-01', name: 'Labour Day', type: 'public' },
            { date: '2024-08-14', name: 'Independence Day', type: 'public' }
        ];

        // Chart instances
        let annualLeaveChart, sickLeaveChart, casualLeaveChart, compOffChart;

        document.addEventListener('DOMContentLoaded', function() {
            initializeDonutCharts();
            populateLeaveTimeline();
            populateUpcomingHolidays();
            initializeProxyAssignment();
        });

        function initializeDonutCharts() {
            // Annual Leave Chart
            const annualCtx = document.getElementById('annualLeaveChart');
            if (annualCtx) {
                annualLeaveChart = new Chart(annualCtx, {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data: [myLeaves.annual.used, myLeaves.annual.remaining], 
                            backgroundColor: ['#e6c673', '#e9ecef'],
                            borderWidth: 0
                        }]
                    },
                    options: { cutout: '75%', responsive: true, maintainAspectRatio: true, plugins: { legend: { display: false }, tooltip: { enabled: false } } }
                });
            }

            // Sick Leave Chart
            const sickCtx = document.getElementById('sickLeaveChart');
            if (sickCtx) {
                sickLeaveChart = new Chart(sickCtx, {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data: [myLeaves.sick.used, myLeaves.sick.remaining],
                            backgroundColor: ['#dc3545', '#e9ecef'],
                            borderWidth: 0
                        }]
                    },
                    options: { cutout: '75%', responsive: true, maintainAspectRatio: true, plugins: { legend: { display: false }, tooltip: { enabled: false } } }
                });
            }

            // Casual Leave Chart
            const casualCtx = document.getElementById('casualLeaveChart');
            if (casualCtx) {
                casualLeaveChart = new Chart(casualCtx, {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data: [myLeaves.casual.used, myLeaves.casual.remaining],
                            backgroundColor: ['#0dcaf0', '#e9ecef'],
                            borderWidth: 0
                        }]
                    },
                    options: { cutout: '75%', responsive: true, maintainAspectRatio: true, plugins: { legend: { display: false }, tooltip: { enabled: false } } }
                });
            }

            // Compensatory Off Chart
            const compOffCtx = document.getElementById('compOffChart');
            if (compOffCtx) {
                compOffChart = new Chart(compOffCtx, {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data: [myLeaves.compOff.used, myLeaves.compOff.remaining],
                            backgroundColor: ['#ffc107', '#e9ecef'],
                            borderWidth: 0
                        }]
                    },
                    options: { cutout: '75%', responsive: true, maintainAspectRatio: true, plugins: { legend: { display: false }, tooltip: { enabled: false } } }
                });
            }
        }

        function populateLeaveTimeline() {
            const timelineContainer = document.getElementById('leaveTimeline');
            if (!timelineContainer) return;

            timelineContainer.innerHTML = '';

            // Group leaves by category
            const pastLeaves = leaveHistory.filter(l => l.category === 'past');
            const activeLeaves = leaveHistory.filter(l => l.category === 'active');
            const upcomingLeaves = leaveHistory.filter(l => l.category === 'upcoming');

            // Past Leaves
            if (pastLeaves.length > 0) {
                const pastSection = createTimelineSection('Past Leaves', pastLeaves, 'past');
                timelineContainer.appendChild(pastSection);
            }

            // Active Leaves
            if (activeLeaves.length > 0) {
                const activeSection = createTimelineSection('Active Leaves', activeLeaves, 'active');
                timelineContainer.appendChild(activeSection);
            }

            // Upcoming Leaves
            if (upcomingLeaves.length > 0) {
                const upcomingSection = createTimelineSection('Upcoming Leaves', upcomingLeaves, 'upcoming');
                timelineContainer.appendChild(upcomingSection);
            }

            if (leaveHistory.length === 0) {
                timelineContainer.innerHTML = '<div class="text-center text-muted py-5">No leave requests found</div>';
            }
        }

        function createTimelineSection(title, leaves, category) {
            const section = document.createElement('div');
            section.className = 'timeline-section mb-4';

            const header = document.createElement('div');
            header.className = 'd-flex align-items-center mb-3';
            header.innerHTML = `
                <h6 class="mb-0 fw-semibold me-2">${title}</h6>
                <span class="badge bg-secondary">${leaves.length}</span>
            `;
            section.appendChild(header);

            leaves.forEach(leave => {
                const item = createTimelineItem(leave);
                section.appendChild(item);
            });

            return section;
        }

        function createTimelineItem(leave) {
            const item = document.createElement('div');
            item.className = 'timeline-item mb-3 p-3 border rounded-3';
            item.style.cursor = 'pointer';
            item.addEventListener('click', () => showLeaveDetails(leave));

            const statusBadge = getStatusBadge(leave.status);
            const typeBadge = getLeaveTypeBadge(leave.type, leave.typeLabel);

            item.innerHTML = `
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        ${typeBadge}
                        <div class="fw-semibold small mt-2">${formatDate(leave.startDate)} - ${formatDate(leave.endDate)}</div>
                        <div class="small text-muted">${leave.days} day${leave.days > 1 ? 's' : ''}</div>
                    </div>
                    ${statusBadge}
                </div>
                <div class="small mt-2">${leave.reason}</div>
            `;

            return item;
        }

        function populateUpcomingHolidays() {
            const container = document.getElementById('upcomingHolidays');
            if (!container) return;

            container.innerHTML = '';

            upcomingHolidays.forEach(holiday => {
                const item = document.createElement('div');
                item.className = 'd-flex justify-content-between align-items-center p-2 border-bottom';
                item.innerHTML = `
                    <div>
                        <div class="small fw-semibold">${holiday.name}</div>
                        <small class="text-muted">${formatDate(holiday.date)}</small>
                    </div>
                    <span class="badge bg-info text-dark">Public Holiday</span>
                `;
                container.appendChild(item);
            });
        }

        function initializeProxyAssignment() {
            const proxyToggle = document.getElementById('proxyToggle');
            const proxySelect = document.getElementById('proxySelect');
            const assignProxyBtn = document.getElementById('assignProxyBtn');

            if (proxyToggle) {
                proxyToggle.addEventListener('change', function() {
                    if (this.checked) {
                        proxySelect.disabled = false;
                        assignProxyBtn.disabled = false;
                    } else {
                        proxySelect.disabled = true;
                        assignProxyBtn.disabled = true;
                    }
                });
            }

            if (assignProxyBtn) {
                assignProxyBtn.addEventListener('click', function() {
                    const selectedProxy = proxySelect.value;
                    if (selectedProxy) {
                        console.log('Assigning proxy:', selectedProxy);
                        // TODO: Implement API call
                        alert('Proxy assigned successfully!');
                    }
                });
            }
        }

        function getStatusBadge(status) {
            const badges = {
                'approved': '<span class="badge bg-success">Approved</span>',
                'pending': '<span class="badge bg-warning text-dark">Pending</span>',
                'rejected': '<span class="badge bg-danger">Rejected</span>'
            };
            return badges[status] || badges['pending'];
        }

        function getLeaveTypeBadge(type, label) {
            const badges = {
                'annual': 'bg-primary',
                'sick': 'bg-danger',
                'casual': 'bg-info text-dark',
                'comp-off': 'bg-warning text-dark'
            };
            const cls = badges[type] || 'bg-secondary';
            return `<span class="badge ${cls}">${label || 'Other'}</span>`;
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        }

        function showLeaveDetails(leave) {
            // Populate detail canvas
            document.getElementById('detailLeaveType').innerHTML = getLeaveTypeBadge(leave.type, leave.typeLabel);
            document.getElementById('detailStartDate').textContent = formatDate(leave.startDate);
            document.getElementById('detailEndDate').textContent = formatDate(leave.endDate);
            document.getElementById('detailDays').textContent = leave.days;
            document.getElementById('detailReason').textContent = leave.reason;
            document.getElementById('detailStatus').innerHTML = getStatusBadge(leave.status);

            // Show canvas
            const canvas = new bootstrap.Offcanvas(document.getElementById('leaveDetailCanvas'));
            canvas.show();
        }
    </script>
@endpush

