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

        .ml-balance-card {
            border: 1px solid #e9ecef;
            border-radius: 1rem;
            padding: 1rem 0.75rem 1.25rem;
            height: 100%;
            background: #fff;
        }

        .ml-balance-summary {
            font-size: 0.875rem;
            font-weight: 600;
            color: #495057;
            margin-top: 0.25rem;
        }

        .ml-balance-rows {
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid #f1f3f5;
            text-align: left;
            font-size: 0.78rem;
        }

        .ml-balance-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            margin-bottom: 0.35rem;
        }

        .ml-balance-row:last-child {
            margin-bottom: 0;
        }

        .ml-dot {
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .ml-dot-applied {
            background: #fd7e14;
        }

        .ml-dot-approved {
            background: #0d6efd;
        }

        .ml-dot-claimed {
            background: #198754;
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
                            @foreach ($personalQuota as $quota)
                                @php
                                    $rem = $quota['remaining'] ?? 0;
                                    $tot = $quota['total'] ?? 0;
                                    $applied = $quota['applied'] ?? 0;
                                    $approved = $quota['approved'] ?? 0;
                                    $claimed = $quota['claimed'] ?? 0;
                                @endphp
                                <div class="col-md-3">
                                    <div class="ml-balance-card text-center">
                                        <div class="donut-chart-container">
                                            <canvas id="leaveChart_{{ $quota['id'] }}"></canvas>
                                            <div class="chart-center-text">
                                                <div class="chart-number">{{ $rem }}</div>
                                                <div class="chart-label">days left</div>
                                                <div class="chart-total">of {{ $tot }}</div>
                                            </div>
                                        </div>
                                        <h6 class="mt-3 mb-1 px-1">{{ $quota['type'] }}</h6>
                                        <!-- <div class="ml-balance-summary">{{ $rem }} of {{ $tot }} days left</div> -->
                                        <div class="ml-balance-rows px-2">
                                            <div class="ml-balance-row">
                                                <span class="d-flex align-items-center gap-2"><span class="ml-dot ml-dot-applied"></span> Applied</span>
                                                <strong>{{ $applied }}d</strong>
                                            </div>
                                            <div class="ml-balance-row">
                                                <span class="d-flex align-items-center gap-2"><span class="ml-dot ml-dot-approved"></span> Approved</span>
                                                <strong>{{ $approved }}d</strong>
                                            </div>
                                            <div class="ml-balance-row">
                                                <span class="d-flex align-items-center gap-2"><span class="ml-dot ml-dot-claimed"></span> Claimed</span>
                                                <strong>{{ $claimed }}d</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
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
            const segmentApplied = '#fd7e14';
            const segmentApproved = '#0d6efd';
            const segmentClaimed = '#198754';
            const segmentLeft = '#e9ecef';

            myLeaves.forEach(quota => {
                const ctx = document.getElementById(`leaveChart_${quota.id}`);
                if (!ctx) return;

                const applied = Number(quota.applied || 0);
                const approved = Number(quota.approved || 0);
                const claimed = Number(quota.claimed || 0);
                const remaining = Number(quota.remaining || 0);
                const total = Number(quota.total || 0);

                let data = [applied, approved, claimed, remaining];
                let colors = [segmentApplied, segmentApproved, segmentClaimed, segmentLeft];

                if (total <= 0) {
                    data = [1];
                    colors = ['#e9ecef'];
                }

                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data,
                            backgroundColor: colors,
                            borderWidth: 0
                        }]
                    },
                    options: {
                        cutout: '75%',
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label(ctx) {
                                        if (total <= 0) return '';
                                        const labels = ['Applied', 'Approved', 'Claimed', 'Remaining'];
                                        const i = ctx.dataIndex;
                                        const v = ctx.raw;
                                        return `${labels[i]}: ${v} day${v === 1 ? '' : 's'}`;
                                    }
                                }
                            }
                        }
                    }
                });
            });
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
                'rejected': '<span class="badge bg-danger">Rejected</span>',
                'recommended': '<span class="badge bg-info text-dark">Recommended</span>',
                'not_recommended': '<span class="badge bg-danger">Not Recommended</span>',
                'cancelled': '<span class="badge bg-secondary">Cancelled</span>'
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

