<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $report_title }} — {{ $period_label }}</title>
    <style>
        @page {
            margin: 12px 8px 24px 8px;
            size: A4 landscape;
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
            padding: 16px 20px;
            margin: -12px -8px 0 -8px;
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
            text-transform: lowercase;
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
            font-size: 7px;
            color: #5c6b7a;
            margin: 6px 0 8px 0;
        }

        .stats-bar {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
        }

        .stats-bar td {
            text-align: center;
            padding: 8px 4px;
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
            margin-bottom: 3px;
        }

        .stat-value {
            font-size: 12px;
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
            padding: 6px 8px;
            margin-top: 8px;
            text-transform: uppercase;
        }

        .calendar-grid {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 8px;
        }

        .calendar-grid th,
        .calendar-grid td {
            border: 1px solid #e2e8f0;
            vertical-align: middle;
            text-align: center;
            padding: 0;
        }

        .calendar-grid thead th {
            background: #f8fafc;
            color: #334155;
            font-weight: bold;
            padding: 4px 1px;
        }

        .calendar-grid col.col-employee {
            width: 108px;
        }

        .col-employee {
            width: 108px;
            min-width: 108px;
            max-width: 108px;
            text-align: left;
            vertical-align: middle;
            padding: 6px 10px 6px 8px !important;
            font-size: 7.5px;
            font-weight: bold;
            color: #1a2b3c;
            background: #ffffff;
            word-break: normal;
            overflow-wrap: normal;
            line-height: 1.3;
        }

        .calendar-grid thead th.col-employee {
            width: 108px;
            min-width: 108px;
            max-width: 108px;
            text-align: left;
            white-space: nowrap;
            word-break: normal;
            line-height: 1.3;
            padding: 6px 10px 6px 8px !important;
            font-size: 7.5px;
            letter-spacing: 0;
        }

        .employee-name {
            display: block;
            white-space: nowrap;
            word-break: normal;
            line-height: 1.3;
        }

        .employee-name-long {
            white-space: normal;
            overflow-wrap: break-word;
            word-break: normal;
        }

        .col-day {
            width: auto;
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
            height: 48px;
            background: #ffffff;
            padding: 0;
        }

        .day-cell-deleted .shift-inner {
            opacity: 0.72;
        }

        .day-cell-deleted .shift-label {
            text-decoration: line-through;
        }

        .shift-inner {
            width: 100%;
            border-collapse: collapse;
        }

        .shift-inner td {
            text-align: center;
            vertical-align: middle;
            padding: 0;
            border: none;
        }

        .shift-label-row td {
            padding: 3px 2px 1px 2px;
        }

        .shift-dot-row td {
            padding: 1px 2px;
        }

        .shift-time-row td {
            padding: 0 2px;
        }

        .shift-time-row-last td {
            padding: 0 2px 3px 2px;
        }

        .shift-label {
            font-size: 5.5px;
            font-weight: bold;
            line-height: 1.15;
            margin: 0;
        }

        .shift-label-morning { color: #0369a1; }
        .shift-label-evening { color: #c2410c; }
        .shift-label-night { color: #6d28d9; }
        .shift-label-general { color: #475569; }

        .shift-dot {
            display: inline-block;
            width: 5px;
            height: 5px;
            border-radius: 50%;
        }

        .dot-morning { background: #0369a1; }
        .dot-evening { background: #c2410c; }
        .dot-night { background: #6d28d9; }
        .dot-general { background: #64748b; }

        .shift-time {
            font-size: 4.5px;
            line-height: 1.2;
            color: #64748b;
            margin: 0;
        }

        .legend-wrap {
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
        }

        .legend-title {
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 6px;
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

        .pdf-footer {
            position: fixed;
            bottom: -18px;
            left: 0;
            right: 0;
            font-size: 7px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
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
                <col class="col-employee" style="width: 108px;">
                @foreach($days as $day)
                    <col class="col-day">
                @endforeach
            </colgroup>
            <thead>
                <tr>
                    <th class="col-employee" style="width: 108px;">Employee</th>
                    @foreach($days as $day)
                        <th class="col-day">
                            <div class="day-head-num">{{ $day['day'] }}</div>
                            <div class="day-head-dow">{{ $day['dow'] }}</div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($department['employees'] as $employee)
                    <tr>
                        <td class="col-employee" style="width: 108px;">
                            <span class="employee-name {{ strlen($employee['name']) > 18 ? 'employee-name-long' : '' }}">{{ $employee['name'] }}</span>
                        </td>
                        @foreach($employee['cells'] as $cell)
                            <td class="day-cell{{ ($cell['is_deleted'] ?? false) ? ' day-cell-deleted' : '' }}">
                                @if($cell)
                                    @php
                                        $shiftType = $cell['shift_type'] ?? 'general';
                                        $shiftBackgrounds = [
                                            'morning' => '#e0f2fe',
                                            'evening' => '#ffedd5',
                                            'night' => '#ede9fe',
                                            'general' => '#f1f5f9',
                                        ];
                                        $shiftBg = $shiftBackgrounds[$shiftType] ?? '#f1f5f9';
                                        $timeStart = $cell['time_start'] ?? null;
                                        $timeEnd = $cell['time_end'] ?? null;
                                        if (! $timeStart && ! empty($cell['time_range'])) {
                                            $timeParts = preg_split('/\s*-\s*/', $cell['time_range'], 2);
                                            $timeStart = trim($timeParts[0] ?? '');
                                            $timeEnd = trim($timeParts[1] ?? '');
                                        }
                                    @endphp
                                    <table class="shift-inner" cellpadding="0" cellspacing="0" width="100%" style="background-color: {{ $shiftBg }};">
                                        <tr class="shift-label-row">
                                            <td bgcolor="{{ $shiftBg }}">
                                                <p class="shift-label shift-label-{{ $shiftType }}">{{ $cell['shift_label'] }}</p>
                                            </td>
                                        </tr>
                                        <tr class="shift-dot-row">
                                            <td bgcolor="{{ $shiftBg }}">
                                                <span class="shift-dot dot-{{ $shiftType }}"></span>
                                            </td>
                                        </tr>
                                        @if($include_shift_times && $timeStart && $timeEnd)
                                            <tr class="shift-time-row">
                                                <td bgcolor="{{ $shiftBg }}">
                                                    <p class="shift-time">{{ $timeStart }}</p>
                                                </td>
                                            </tr>
                                            <tr class="shift-time-row-last">
                                                <td bgcolor="{{ $shiftBg }}">
                                                    <p class="shift-time">{{ $timeEnd }}</p>
                                                </td>
                                            </tr>
                                        @elseif($include_shift_times && $timeStart)
                                            <tr class="shift-time-row-last">
                                                <td bgcolor="{{ $shiftBg }}">
                                                    <p class="shift-time">{{ $timeStart }}</p>
                                                </td>
                                            </tr>
                                        @endif
                                    </table>
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
                <td><span class="legend-dot dot-morning"></span> Morning</td>
                <td><span class="legend-dot dot-evening"></span> Evening</td>
                <td><span class="legend-dot dot-night"></span> Night</td>
                @if($include_deleted)
                    <td><span style="color: #dc2626; font-weight: bold;">×</span> Removed shift</td>
                @endif
            </tr>
        </table>
    </div>

    <div class="pdf-footer">
    <table class="footer-table">
        <tr>
            <td>
                EFM-HCM • Secure Internal Workforce Management Report
            </td>
            <td style="text-align: right;">
                Powered by 5D Solutions — Building smarter business systems
            </td>
        </tr>
    </table>
</div>
</body>
</html>
