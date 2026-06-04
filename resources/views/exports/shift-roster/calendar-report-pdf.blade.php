<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $report_title }} — {{ $period_label }}</title>
    <style>
        @page {
            margin: 10px 8px 24px 8px;
            size: A3 landscape;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
            color: #1a2b3c;
            margin: 0;
            padding: 0;
        }

        .pdf-header {
            background: #012445;
            color: #ffffff;
            padding: 12px 16px;
            margin: -10px -8px 0 -8px;
        }

        .pdf-header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .pdf-header-table td {
            vertical-align: top;
        }

        .org-name {
            font-size: 17px;
            font-weight: bold;
            margin: 0 0 3px 0;
            text-transform: lowercase;
        }

        .report-subtitle {
            font-size: 9px;
            margin: 0;
            opacity: 0.92;
        }

        .period-label {
            font-size: 15px;
            font-weight: bold;
            text-align: right;
            margin: 0 0 3px 0;
        }

        .generated-at {
            font-size: 7px;
            text-align: right;
            margin: 0;
            opacity: 0.88;
        }

        .meta-line {
            font-size: 7px;
            color: #5c6b7a;
            margin: 5px 0 7px 0;
        }

        .stats-bar {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
        }

        .stats-bar td {
            text-align: center;
            padding: 7px 4px;
            border-right: 1px solid #e8edf2;
            vertical-align: middle;
        }

        .stats-bar td:last-child {
            border-right: none;
        }

        .stat-label {
            font-size: 6px;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #6b7c8f;
            margin-bottom: 2px;
        }

        .stat-value {
            font-size: 11px;
            font-weight: bold;
            color: #012445;
        }

        .stat-morning { color: #0369a1; }
        .stat-evening { color: #c2410c; }
        .stat-night { color: #6d28d9; }

        .dept-header {
            background: #012445;
            color: #ffffff;
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 0.06em;
            padding: 5px 8px;
            margin-top: 6px;
            text-transform: uppercase;
        }

        .calendar-grid {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 6px;
        }

        .calendar-grid th,
        .calendar-grid td {
            border: 1px solid #d8e0ea;
            vertical-align: middle;
            text-align: center;
            padding: 0;
        }

        .calendar-grid thead th {
            background: #f1f5f9;
            color: #334155;
            font-weight: bold;
            padding: 4px 1px;
        }

        .calendar-grid col.col-employee {
            width: 13%;
        }

        .calendar-grid col.col-day {
            width: {{ count($days) > 0 ? round(87 / count($days), 3) : 2.8 }}%;
        }

        .col-employee {
            text-align: left;
            vertical-align: middle;
            padding: 4px 6px !important;
            font-size: 7px;
            font-weight: bold;
            color: #1a2b3c;
            background: #ffffff;
            white-space: nowrap;
        }

        .calendar-grid thead th.col-employee {
            text-align: left;
            font-size: 6.5px;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.03em;
            padding: 4px 6px !important;
        }

        .day-head-num {
            font-size: 6.5px;
            line-height: 1.15;
            color: #1e293b;
            font-weight: bold;
        }

        .day-head-dow {
            font-size: 5px;
            line-height: 1.15;
            color: #94a3b8;
            font-weight: normal;
        }

        .day-cell {
            height: 36px;
            padding: 2px 1px !important;
            background: #ffffff;
        }

        .day-cell-morning { background: #e0f2fe; }
        .day-cell-evening { background: #ffedd5; }
        .day-cell-night { background: #ede9fe; }
        .day-cell-general { background: #f1f5f9; }

        .day-cell-deleted {
            opacity: 0.72;
        }

        .day-cell-deleted .shift-short-label,
        .day-cell-deleted .shift-time-stack {
            text-decoration: line-through;
        }

        .shift-short-label {
            display: block;
            font-size: 6.5px;
            font-weight: bold;
            line-height: 1.15;
            margin: 0;
        }

        .shift-short-morning { color: #0369a1; }
        .shift-short-evening { color: #c2410c; }
        .shift-short-night { color: #6d28d9; }
        .shift-short-general { color: #475569; }

        .shift-time-stack {
            display: block;
            font-size: 5px;
            line-height: 1.2;
            color: #475569;
            margin: 0;
            white-space: nowrap;
        }

        .legend-wrap {
            margin-top: 8px;
            padding-top: 6px;
            border-top: 1px solid #e2e8f0;
        }

        .legend-title {
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 5px;
        }

        .legend-table {
            width: 100%;
            border-collapse: collapse;
        }

        .legend-table td {
            font-size: 7px;
            color: #475569;
            padding: 2px 10px 2px 0;
        }

        .legend-dot {
            display: inline-block;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            margin-right: 5px;
            vertical-align: middle;
        }

        .dot-morning { background: #0369a1; }
        .dot-evening { background: #c2410c; }
        .dot-night { background: #6d28d9; }

        .pdf-footer {
            position: fixed;
            bottom: -16px;
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
        $shiftBackgrounds = [
            'morning' => 'day-cell-morning',
            'evening' => 'day-cell-evening',
            'night' => 'day-cell-night',
            'general' => 'day-cell-general',
        ];
    @endphp

    <div class="pdf-header">
        <table class="pdf-header-table">
            <tr>
                <td style="width: 58%;">
                    <div class="org-name">{{ $organization_name }}</div>
                    <p class="report-subtitle">{{ $report_title }}</p>
                </td>
                <td style="width: 42%;">
                    <p class="period-label">{{ $period_label }}</p>
                    <p class="generated-at">Generated: {{ $generated_at }}</p>
                </td>
            </tr>
        </table>
    </div>

    <p class="meta-line">
        {{ $employee_group_label }}
        @if($include_deleted)
            • Including deleted shifts
        @endif
    </p>

    @if(($stats['shifts_scheduled'] ?? 0) === 0)
        <div class="empty-note" style="font-size: 9px; color: #64748b; background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px 12px; margin-bottom: 12px; border-radius: 4px; text-align: center;">
            No shifts found for this period and filter. Adjust the period, employee group, or include deleted shifts.
        </div>
    @endif

    <table class="stats-bar">
        <tr>
            <td>
                <div class="stat-label">Total employees</div>
                <div class="stat-value">{{ $stats['total_employees'] }}</div>
            </td>
            <td>
                <div class="stat-label">Shifts scheduled</div>
                <div class="stat-value">{{ $stats['shifts_scheduled'] }}</div>
            </td>
            <td>
                <div class="stat-label">Morning</div>
                <div class="stat-value stat-morning">{{ $stats['morning'] }}</div>
            </td>
            <td>
                <div class="stat-label">Evening</div>
                <div class="stat-value stat-evening">{{ $stats['evening'] }}</div>
            </td>
            <td>
                <div class="stat-label">Night</div>
                <div class="stat-value stat-night">{{ $stats['night'] }}</div>
            </td>
            <td>
                <div class="stat-label">Total hours</div>
                <div class="stat-value">{{ $stats['total_hours'] }} hrs</div>
            </td>
        </tr>
    </table>

    @foreach($departments as $department)
        @if(!empty($department['name']))
            <div class="dept-header">{{ $department['name'] }}</div>
        @endif

        <table class="calendar-grid">
            <colgroup>
                <col class="col-employee">
                @foreach($days as $day)
                    <col class="col-day">
                @endforeach
            </colgroup>
            <thead>
                <tr>
                    <th class="col-employee">Employee</th>
                    @foreach($days as $day)
                        <th>
                            <div class="day-head-num">{{ $day['day'] }}</div>
                            <div class="day-head-dow">{{ $day['dow'] }}</div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($department['employees'] as $employee)
                    <tr>
                        <td class="col-employee">{{ $employee['name'] }}</td>
                        @foreach($employee['cells'] as $cell)
                            @php
                                $shiftType = is_array($cell) ? ($cell['shift_type'] ?? 'general') : null;
                                $cellClass = $shiftType ? ($shiftBackgrounds[$shiftType] ?? 'day-cell-general') : '';
                            @endphp
                            <td class="day-cell{{ $cellClass ? ' ' . $cellClass : '' }}{{ is_array($cell) && !empty($cell['is_deleted']) ? ' day-cell-deleted' : '' }}">
                                @if(is_array($cell))
                                    <span class="shift-short-label shift-short-{{ $shiftType }}">{{ $cell['shift_short'] ?? '•' }}</span>
                                    @if($include_shift_times)
                                        @if(!empty($cell['time_start_short']))
                                            <span class="shift-time-stack">{{ $cell['time_start_short'] }}</span>
                                        @endif
                                        @if(!empty($cell['time_end_short']))
                                            <span class="shift-time-stack">{{ $cell['time_end_short'] }}</span>
                                        @endif
                                    @endif
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach

    <div class="legend-wrap">
        <div class="legend-title">Shift legend</div>
        <table class="legend-table">
            <tr>
                <td><span class="legend-dot dot-morning"></span> M — Morning</td>
                <td><span class="legend-dot dot-evening"></span> E — Evening</td>
                <td><span class="legend-dot dot-night"></span> N — Night</td>
                @if($include_deleted)
                    <td><span style="color: #dc2626; font-weight: bold;">×</span> Removed shift</td>
                @endif
            </tr>
        </table>
    </div>

    <div class="pdf-footer">
        <table class="footer-table">
            <tr>
                <td>EFM-HCM • Secure Internal Workforce Management Report</td>
                <td style="text-align: right;">Powered by 5D Solutions — Building smarter business systems</td>
            </tr>
        </table>
    </div>
</body>
</html>
