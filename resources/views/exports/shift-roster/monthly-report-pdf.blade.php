<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $report_title }} — {{ $period_label }}</title>
    <style>
        @page {
            margin: 28px 32px 56px 32px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1a2b3c;
            margin: 0;
            padding: 0;
        }

        .pdf-header {
            background: #012445;
            color: #ffffff;
            padding: 18px 22px;
            margin: -28px -32px 0 -32px;
        }

        .pdf-header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .pdf-header-table td {
            vertical-align: top;
        }

        .org-name {
            font-size: 20px;
            font-weight: bold;
            margin: 0 0 4px 0;
        }

        .report-subtitle {
            font-size: 11px;
            margin: 0;
            opacity: 0.92;
        }

        .period-label {
            font-size: 18px;
            font-weight: bold;
            text-align: right;
            margin: 0 0 4px 0;
        }

        .generated-at {
            font-size: 9px;
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
            margin-bottom: 14px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
        }

        .stats-bar td {
            text-align: center;
            padding: 10px 6px;
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
            margin-bottom: 4px;
        }

        .stat-value {
            font-size: 14px;
            font-weight: bold;
            color: #012445;
        }

        .stat-morning { color: #0369a1; }
        .stat-evening { color: #c2410c; }
        .stat-night { color: #6d28d9; }

        .dept-header {
            background: #012445;
            color: #ffffff;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 0.06em;
            padding: 7px 10px;
            margin-top: 12px;
            text-transform: uppercase;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        .data-table thead th {
            background: #f1f5f9;
            color: #475569;
            font-size: 7px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            padding: 7px 8px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .data-table tbody td {
            padding: 8px;
            border-bottom: 1px solid #edf2f7;
            font-size: 9px;
            vertical-align: middle;
        }

        .data-table tbody tr:nth-child(even) td {
            background: #fafbfc;
        }

        .data-table tbody tr.row-deleted td {
            background: #fef2f2;
            color: #7f1d1d;
        }

        .pill {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 8px;
            font-weight: bold;
        }

        .pill-morning {
            background: #e0f2fe;
            color: #0369a1;
        }

        .pill-evening {
            background: #ffedd5;
            color: #c2410c;
        }

        .pill-night {
            background: #ede9fe;
            color: #6d28d9;
        }

        .pill-general {
            background: #f1f5f9;
            color: #475569;
        }

        .pill-off {
            background: #f8fafc;
            color: #94a3b8;
        }

        .pill-hours {
            background: #dcfce7;
            color: #15803d;
        }

        .data-table td.shift-type-col {
            white-space: nowrap;
        }

        .shift-type-wrap {
            white-space: nowrap;
            line-height: 1.2;
        }

        .shift-type-wrap .pill {
            vertical-align: middle;
        }

        .deleted-icon {
            display: inline-block;
            width: 10px;
            height: 10px;
            line-height: 10px;
            text-align: center;
            font-size: 7px;
            font-weight: bold;
            color: #ffffff;
            background: #dc2626;
            border-radius: 50%;
            margin-left: 5px;
            vertical-align: middle;
        }

        .legend-deleted-icon {
            margin-right: 5px;
        }

        .legend-wrap {
            margin-top: 18px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
        }

        .legend-title {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 8px;
        }

        .legend-table {
            width: 100%;
            border-collapse: collapse;
        }

        .legend-table td {
            font-size: 8px;
            color: #475569;
            padding: 3px 12px 3px 0;
        }

        .legend-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 6px;
            vertical-align: middle;
        }

        .dot-morning { background: #0369a1; }
        .dot-evening { background: #c2410c; }
        .dot-night { background: #6d28d9; }

        .pdf-footer {
            position: fixed;
            bottom: -24px;
            left: 0;
            right: 0;
            font-size: 8px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .signature-block {
            margin-top: 10px;
            margin-bottom: 8px;
            page-break-inside: avoid;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }

        .signature-cell {
            width: 42%;
            vertical-align: top;
        }

        .signature-cell-left {
            padding-right: 8px;
        }

        .signature-cell-right {
            padding-left: 8px;
        }

        .signature-gap {
            width: 16%;
        }

        .signature-heading {
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #475569;
            margin-bottom: 3px;
        }

        .signature-line {
            border-bottom: 1px solid #94a3b8;
            height: 18px;
            margin-bottom: 5px;
            max-width: 180px;
        }

        .signature-meta {
            font-size: 7px;
            color: #334155;
            margin-bottom: 2px;
            line-height: 1.35;
        }

        .signature-meta-label {
            font-weight: bold;
            color: #64748b;
        }

        .empty-note {
            font-size: 9px;
            color: #64748b;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 10px 12px;
            margin-bottom: 12px;
            border-radius: 4px;
            text-align: center;
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
        @if(!empty($department_label))
            • {{ $department_label }}
        @endif
        @if($include_deleted)
            • Including deleted shifts
        @endif
    </p>

    @if(($stats['shifts_scheduled'] ?? 0) === 0)
        <div class="empty-note">No shifts found for this month and filter. Adjust the month, employee group, or include deleted shifts.</div>
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
        @if(empty($department['rows']))
            @continue
        @endif
        @if($include_department_grouping && !empty($department['name']))
            <div class="dept-header">{{ $department['name'] }}</div>
        @endif

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 22%;">Employee</th>
                    <th style="width: 12%;">Date</th>
                    <th style="width: 8%;">Day</th>
                    @if($include_shift_times)
                        <th style="width: 14%;">Start time</th>
                        <th style="width: 14%;">End time</th>
                    @endif
                    <th style="width: 14%;">Shift type</th>
                    <th style="width: 10%;">Total hours</th>
                </tr>
            </thead>
            <tbody>
                @foreach($department['rows'] as $row)
                    <tr class="{{ !empty($row['is_deleted']) ? 'row-deleted' : '' }}">
                        <td>{{ $row['employee'] }}</td>
                        <td>{{ $row['date'] }}</td>
                        <td>{{ $row['day'] }}</td>
                        @if($include_shift_times)
                            <td>{{ $row['start_time'] }}</td>
                            <td>{{ $row['end_time'] }}</td>
                        @endif
                        <td class="shift-type-col">
                            <span class="shift-type-wrap">
                                <span class="pill pill-{{ $row['shift_type'] }}">{{ $row['shift_label'] }}</span>
                                @if($include_deleted && !empty($row['is_deleted']))
                                    <span class="deleted-icon" title="Deleted">×</span>
                                @endif
                            </span>
                        </td>
                        <td><span class="pill pill-hours">{{ $row['hours'] }} hrs</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach

    <div class="legend-wrap">
        <div class="legend-title">Shift legend</div>
        <table class="legend-table">
            <tr>
                <td><span class="legend-dot dot-morning"></span> Morning — 08:00 AM to 04:00 PM</td>
                <td><span class="legend-dot dot-evening"></span> Evening — 02:00 PM to 10:00 PM</td>
                <td><span class="legend-dot dot-night"></span> Night — 09:00 PM to 06:00 AM</td>
                @if($include_deleted)
                    <td><span class="deleted-icon legend-deleted-icon">×</span> Removed shift</td>
                @endif
            </tr>
        </table>
    </div>

    @include('exports.shift-roster.partials.signature-block-pdf')

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
