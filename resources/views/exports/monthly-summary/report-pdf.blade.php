<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $report_title }} — {{ $period_label }}</title>
    <style>
        @page {
            margin: 24px 28px 48px 28px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #1a2b3c;
            margin: 0;
            padding: 0;
        }

        .pdf-header {
            background: #012445;
            color: #ffffff;
            padding: 16px 20px;
            margin: -24px -28px 0 -28px;
        }

        .pdf-header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .pdf-header-table td {
            vertical-align: top;
        }

        .org-name {
            font-size: 18px;
            font-weight: bold;
            margin: 0 0 4px 0;
        }

        .report-subtitle {
            font-size: 10px;
            margin: 0;
            opacity: 0.92;
        }

        .period-label {
            font-size: 16px;
            font-weight: bold;
            text-align: right;
            margin: 0 0 4px 0;
        }

        .generated-at {
            font-size: 8px;
            text-align: right;
            margin: 0;
            opacity: 0.88;
        }

        .meta-line {
            font-size: 8px;
            color: #5c6b7a;
            margin: 8px 0 10px 0;
        }

        .stats-bar {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
        }

        .stats-bar td {
            text-align: center;
            padding: 8px 6px;
            border-right: 1px solid #e8edf2;
            vertical-align: middle;
        }

        .stats-bar td:last-child {
            border-right: none;
        }

        .stat-label {
            font-size: 7px;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #6b7c8f;
            margin-bottom: 2px;
        }

        .stat-value {
            font-size: 12px;
            font-weight: bold;
            color: #012445;
        }

        .stat-present { color: #15803d; }
        .stat-absent { color: #dc2626; }
        .stat-attendance { color: #0369a1; }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #d8e0ea;
            padding: 5px 4px;
            vertical-align: middle;
            text-align: center;
        }

        .data-table thead th {
            background: #012445;
            color: #ffffff;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .data-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .data-table td.text-left {
            text-align: left;
        }

        .employee-name {
            font-weight: bold;
            color: #0f172a;
        }

        .employee-meta {
            display: block;
            font-size: 7px;
            color: #64748b;
            margin-top: 1px;
        }

        .pill {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
        }

        .pill-present { background: #dcfce7; color: #15803d; }
        .pill-absent { background: #fee2e2; color: #dc2626; }
        .pill-half { background: #fef9c3; color: #a16207; }

        .empty-note {
            font-size: 10px;
            color: #64748b;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 12px;
            text-align: center;
            margin-bottom: 12px;
        }

        .pdf-footer {
            position: fixed;
            bottom: -12px;
            left: 0;
            right: 0;
            font-size: 7px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }
    </style>
</head>
<body>
    <div class="pdf-header">
        <table class="pdf-header-table">
            <tr>
                <td style="width: 58%;">
                    <div class="org-name">{{ $organization_name }}</div>
                    <p class="report-subtitle">{{ $report_title }} — {{ $report_subtitle }}</p>
                </td>
                <td style="width: 42%;">
                    <p class="period-label">{{ $period_label }}</p>
                    <p class="generated-at">Generated: {{ $generated_at }}</p>
                </td>
            </tr>
        </table>
    </div>

    <p class="meta-line">
        Monthly attendance and leave summary
        @if(!empty($filter_labels))
            • {{ implode(' • ', $filter_labels) }}
        @endif
    </p>

    <table class="stats-bar">
        <tr>
            <td>
                <div class="stat-label">Employees</div>
                <div class="stat-value">{{ $stats['total_employees'] }}</div>
            </td>
            <td>
                <div class="stat-label">Present</div>
                <div class="stat-value stat-present">{{ $stats['total_present'] }}</div>
            </td>
            <td>
                <div class="stat-label">Absent</div>
                <div class="stat-value stat-absent">{{ $stats['total_absent'] }}</div>
            </td>
            <td>
                <div class="stat-label">Half-days</div>
                <div class="stat-value">{{ $stats['total_half_days'] }}</div>
            </td>
            <td>
                <div class="stat-label">Attendance %</div>
                <div class="stat-value stat-attendance">{{ $stats['attendance_percentage'] }}%</div>
            </td>
        </tr>
    </table>

    @if(($stats['total_employees'] ?? 0) === 0)
        <div class="empty-note">No employees found for this period and filter selection.</div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 3%;">#</th>
                    <th style="width: 18%;">Employee</th>
                    <th style="width: 7%;">Code</th>
                    <th style="width: 12%;">Department</th>
                    <th style="width: 10%;">SBU</th>
                    <th style="width: 8%;">Floor</th>
                    <th style="width: 5%;">Days</th>
                    <th style="width: 6%;">Present</th>
                    <th style="width: 6%;">Absent</th>
                    <th style="width: 6%;">Half</th>
                    <th style="width: 6%;">Leaves</th>
                    <th style="width: 5%;">Late</th>
                    <th style="width: 5%;">Early</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employees as $index => $employee)
                    @php
                        $totalLeaves = array_sum($employee['leave_usage'] ?? []);
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="text-left">
                            <span class="employee-name">{{ $employee['employee_name'] }}</span>
                        </td>
                        <td>{{ $employee['employee_code'] }}</td>
                        <td class="text-left">{{ $employee['department'] }}</td>
                        <td class="text-left">{{ $employee['sbu'] }}</td>
                        <td class="text-left">{{ $employee['floor_name'] }}</td>
                        <td>{{ $employee['total_days'] }}</td>
                        <td><span class="pill pill-present">{{ $employee['present'] }}</span></td>
                        <td><span class="pill pill-absent">{{ $employee['absent'] }}</span></td>
                        <td>
                            @if(($employee['half_days'] ?? 0) > 0)
                                <span class="pill pill-half">{{ $employee['half_days'] }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $totalLeaves > 0 ? $totalLeaves : '-' }}</td>
                        <td>{{ ($employee['late_arrivals'] ?? 0) > 0 ? $employee['late_arrivals'] : '-' }}</td>
                        <td>{{ ($employee['early_departures'] ?? 0) > 0 ? $employee['early_departures'] : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="pdf-footer">
        <table class="footer-table">
            <tr>
                <td>EFM-HCM • Secure Internal Workforce Management Report</td>
                <td style="text-align: right;">Powered by 5D Solutions</td>
            </tr>
        </table>
    </div>
</body>
</html>
