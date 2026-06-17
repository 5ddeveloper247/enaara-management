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

        .stats-bar {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
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

        .stat-morning {
            color: #0369a1;
        }

        .stat-evening {
            color: #c2410c;
        }

        .stat-night {
            color: #6d28d9;
        }

        .stat-hours-total {
            color: #15803d;
        }

        .employee-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 14px 14px 10px;
            margin-bottom: 16px;
            page-break-inside: avoid;
        }

        .employee-card-header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .employee-card-header td {
            vertical-align: middle;
        }

        .avatar-wrap {
            width: 34px;
            height: 34px;
            padding: 0;
            vertical-align: middle;
        }

        .avatar-table {
            width: 34px;
            height: 34px;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .avatar-cell {
            width: 34px;
            height: 34px;
            background-color: #0369a1;
            color: #ffffff;
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            padding: 0;
            margin: 0;
            line-height: 1.1;
            letter-spacing: 0.02em;
            border-radius: 17px;
        }

        .employee-name {
            font-size: 12px;
            font-weight: bold;
            color: #0f172a;
            margin: 0 0 2px 0;
        }

        .employee-designation {
            font-size: 8px;
            color: #475569;
            margin: 0 0 2px 0;
        }

        .employee-dept {
            font-size: 8px;
            color: #64748b;
            margin: 0;
        }

        .employee-badges {
            text-align: right;
            white-space: nowrap;
        }

        .badge-pill {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 8px;
            font-weight: bold;
            margin-left: 6px;
            vertical-align: middle;
        }

        .badge-shifts {
            background: #e0f2fe;
            color: #0369a1;
        }

        .badge-hours {
            background: #dcfce7;
            color: #15803d;
        }

        .shift-table {
            width: 100%;
            border-collapse: collapse;
            background: #ffffff;
            border-radius: 6px;
            overflow: hidden;
        }

        .shift-table thead th {
            background: #f1f5f9;
            color: #64748b;
            font-size: 7px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            padding: 8px 10px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .shift-table tbody td {
            padding: 9px 10px;
            border-bottom: 1px solid #edf2f7;
            font-size: 9px;
            vertical-align: middle;
        }

        .shift-table tbody tr:last-child td {
            border-bottom: none;
        }

        .shift-table tbody tr:nth-child(even) td {
            background: #fafbfc;
        }

        .shift-table tbody tr.row-deleted td {
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

        .pill-hours {
            background: #dcfce7;
            color: #15803d;
        }

        .employee-card-footer {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
        }

        .employee-card-footer td {
            font-size: 8px;
            color: #64748b;
            padding-top: 6px;
        }

        .employee-card-footer .total-hours {
            text-align: right;
            font-size: 9px;
            font-weight: bold;
            color: #0f172a;
        }

        .legend-wrap {
            margin-top: 10px;
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

        .dot-morning {
            background: #0369a1;
        }

        .dot-evening {
            background: #c2410c;
        }

        .dot-night {
            background: #6d28d9;
        }

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

        .print-info {
            font-size: 6.5px;
            color: #94a3b8;
            text-align: right;
            margin-bottom: 3px;
            line-height: 1.2;
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
    </style>
</head>

<body>
    <div class="pdf-header">
        <table class="pdf-header-table">
            <tr>
                <td style="width: 58%;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 50px; vertical-align: middle; padding-right: 12px;">
                                <img src="{{ public_path('images/enaara-logo.png') }}" alt="Logo" style="height: 38px; width: auto;">
                            </td>
                            <td style="vertical-align: middle;">
                                <div class="org-name">Madison Square Mall Rawalpindi</div>
                                <p class="report-subtitle">{{ $report_title }}</p>
                            </td>
                        </tr>
                    </table>
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
    </p>

    @if(($stats['shifts_scheduled'] ?? 0) === 0)
    <div class="empty-note">No shifts found for this period and filter. Adjust the period, employee group, or include deleted shifts.</div>
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
                <div class="stat-value stat-hours-total">{{ $stats['total_hours'] }} hrs</div>
            </td>
        </tr>
    </table>

    @foreach($employees as $employee)
    <div class="employee-card">
        <table class="employee-card-header">
            <tr>
                <td class="avatar-wrap">
                    <table class="avatar-table" cellpadding="0" cellspacing="0" role="presentation">
                        <tr>
                            <td class="avatar-cell">{{ $employee['initials'] }}</td>
                        </tr>
                    </table>
                </td>
                <td>
                    <p class="employee-name">{{ $employee['name'] }}</p>
                    @if(!empty($employee['designation']))
                        <p class="employee-designation">{{ $employee['designation'] }}</p>
                    @endif
                    <p class="employee-dept">{{ $employee['department'] }}</p>
                </td>
                <td class="employee-badges">
                    <span class="badge-pill badge-shifts">{{ $employee['shift_count'] }} shifts</span>
                    <span class="badge-pill badge-hours">{{ $employee['total_hours'] }} hrs</span>
                </td>
            </tr>
        </table>

        <table class="shift-table">
            <thead>
                <tr>
                    <th style="width: 14%;">Date</th>
                    <th style="width: 10%;">Day</th>
                    @if($include_shift_times)
                    <th style="width: 16%;">Start time</th>
                    <th style="width: 16%;">End time</th>
                    @endif
                    <th style="width: 18%;">Shift type</th>
                    <th style="width: 12%;">Total hours</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employee['shifts'] as $shift)
                <tr class="{{ !empty($shift['is_deleted']) ? 'row-deleted' : '' }}">
                    <td>{{ $shift['date'] }}</td>
                    <td>{{ $shift['day'] }}</td>
                    @if($include_shift_times)
                    <td>{{ $shift['start_time'] }}</td>
                    <td>{{ $shift['end_time'] }}</td>
                    @endif
                    <td>
                        <span class="pill pill-{{ $shift['shift_type'] }}">{{ $shift['shift_label'] }}</span>
                    </td>
                    <td><span class="pill pill-hours">{{ $shift['hours'] }} hrs</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $include_shift_times ? 6 : 4 }}" style="text-align: center; color: #64748b;">
                        No shifts in this period.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <table class="employee-card-footer">
            <tr>
                <td>{{ $employee['shift_count'] }} shifts this month</td>
                <td class="total-hours">Total: {{ $employee['total_hours'] }} hrs</td>
            </tr>
        </table>
    </div>
    @endforeach

    <div class="legend-wrap">
        <div class="legend-title">Shift legend</div>
        <table class="legend-table">
            <tr>
                <td><span class="legend-dot dot-morning"></span> Morning — 08:00 AM to 04:00 PM</td>
                <td><span class="legend-dot dot-evening"></span> Evening — 02:00 PM to 10:00 PM</td>
                <td><span class="legend-dot dot-night"></span> Night — 09:00 PM to 06:00 AM</td>
            </tr>
        </table>
    </div>

    @include('exports.shift-roster.partials.signature-block-pdf')

    <div class="pdf-footer">
        @include('exports.shift-roster.partials.print-info-pdf')
        <table class="footer-table">
            <tr>
                <td>EFM-HCM • Secure Internal Workforce Management Report</td>
                <td style="text-align: right;">Powered by 5D Solutions — Building smarter business systems</td>
            </tr>
        </table>
    </div>
</body>

</html>