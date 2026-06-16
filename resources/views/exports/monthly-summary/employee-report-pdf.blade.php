<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $report_title }} — {{ $employee['employee_name'] ?? 'Employee' }}</title>
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

        .employee-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 14px;
            margin: 12px 0;
            background: #f8fafc;
        }

        .employee-name {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            margin: 0 0 4px 0;
        }

        .employee-meta {
            font-size: 8px;
            color: #64748b;
            line-height: 1.5;
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

        .section-title {
            font-size: 10px;
            font-weight: bold;
            color: #012445;
            margin: 14px 0 8px 0;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .calendar-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .calendar-table th,
        .calendar-table td {
            border: 1px solid #d8e0ea;
            vertical-align: top;
            text-align: center;
            padding: 4px 2px;
            height: 52px;
        }

        .calendar-table thead th {
            background: #f1f5f9;
            color: #475569;
            font-size: 8px;
            height: auto;
            padding: 6px 2px;
        }

        .calendar-day-number {
            font-size: 9px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 3px;
        }

        .calendar-day-label {
            font-size: 6.5px;
            line-height: 1.25;
            color: #475569;
            word-break: break-word;
        }

        .cell-present { background: #ecfdf5; }
        .cell-absent { background: #fef2f2; }
        .cell-leave, .cell-half-day { background: #eff6ff; }
        .cell-off, .cell-holiday { background: #f1f5f9; }
        .cell-work-from-home { background: #dbeafe; }
        .cell-outstation { background: #fef9c3; }
        .cell-scheduled { background: #ffffff; }

        .legend-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .legend-table td {
            font-size: 7px;
            color: #475569;
            padding: 2px 8px 2px 0;
        }

        .legend-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 2px;
            margin-right: 4px;
            vertical-align: middle;
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
    @php
        $statusClassMap = [
            'present' => 'cell-present',
            'absent' => 'cell-absent',
            'leave' => 'cell-leave',
            'half-day' => 'cell-half-day',
            'off' => 'cell-off',
            'holiday' => 'cell-holiday',
            'work-from-home' => 'cell-work-from-home',
            'outstation' => 'cell-outstation',
            'scheduled' => 'cell-scheduled',
        ];

        $resolveCellLabel = static function (?array $day): string {
            if ($day === null) {
                return '';
            }

            $status = $day['status'] ?? 'present';

            if ($status === 'scheduled') {
                return '';
            }

            if ($status === 'absent') {
                return 'Absent';
            }

            if (in_array($status, ['leave', 'half-day', 'holiday'], true)) {
                return (string) ($day['detail'] ?? $day['label'] ?? ucfirst($status));
            }

            if ($status === 'work-from-home') {
                return 'WFH';
            }

            if ($status === 'outstation') {
                return 'Outstation';
            }

            if ($status === 'off') {
                return 'Off';
            }

            if (! empty($day['is_holiday_work'])) {
                return (string) ($day['detail'] ?? 'Working');
            }

            return (string) ($day['label'] ?? 'Present');
        };
    @endphp

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

    <div class="employee-card">
        <p class="employee-name">{{ $employee['employee_name'] ?? 'Employee' }}</p>
        <div class="employee-meta">
            {{ $employee['employee_code'] ?? 'N/A' }}
            • {{ $employee['department'] ?? 'N/A' }}
            • {{ $employee['sbu'] ?? 'N/A' }}
            • Floor: {{ $employee['floor_name'] ?? 'N/A' }}
        </div>
    </div>

    <table class="stats-bar">
        <tr>
            <td>
                <div class="stat-label">Total Days</div>
                <div class="stat-value">{{ $stats['total_days'] ?? 0 }}</div>
            </td>
            <td>
                <div class="stat-label">Present</div>
                <div class="stat-value">{{ $stats['present'] ?? 0 }}</div>
            </td>
            <td>
                <div class="stat-label">Absent</div>
                <div class="stat-value">{{ $stats['absent'] ?? 0 }}</div>
            </td>
            <td>
                <div class="stat-label">Half-days</div>
                <div class="stat-value">{{ $stats['half_days'] ?? 0 }}</div>
            </td>
            <td>
                <div class="stat-label">Leave Days</div>
                <div class="stat-value">{{ $stats['leave'] ?? 0 }}</div>
            </td>
            <td>
                <div class="stat-label">Attendance %</div>
                <div class="stat-value">{{ $stats['attendance_percentage'] ?? 0 }}%</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Daily Attendance Calendar</div>

    <table class="calendar-table">
        <thead>
            <tr>
                <th>Sun</th>
                <th>Mon</th>
                <th>Tue</th>
                <th>Wed</th>
                <th>Thu</th>
                <th>Fri</th>
                <th>Sat</th>
            </tr>
        </thead>
        <tbody>
            @foreach($calendar_weeks as $week)
                <tr>
                    @foreach($week as $day)
                        @php
                            $status = is_array($day) ? ($day['status'] ?? 'present') : null;
                            $cellClass = $status ? ($statusClassMap[$status] ?? 'cell-present') : '';
                            $label = $resolveCellLabel(is_array($day) ? $day : null);
                        @endphp
                        <td class="{{ $cellClass }}">
                            @if(is_array($day))
                                <div class="calendar-day-number">{{ $day['day'] ?? '' }}</div>
                                @if($label !== '')
                                    <div class="calendar-day-label">{{ $label }}</div>
                                @endif
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="legend-table">
        <tr>
            <td><span class="legend-dot cell-present"></span> Present</td>
            <td><span class="legend-dot cell-absent"></span> Absent</td>
            <td><span class="legend-dot cell-leave"></span> Leave</td>
            <td><span class="legend-dot cell-off"></span> Off</td>
            <td><span class="legend-dot cell-holiday"></span> Holiday</td>
            <td><span class="legend-dot cell-work-from-home"></span> WFH</td>
        </tr>
    </table>

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
