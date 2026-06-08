<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        .header {
            background-color: #003366;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
        }
        .subheader {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        .text-center {
            text-align: center;
        }
        .text-bold {
            font-weight: bold;
        }
        .border {
            border: 1px solid #000000;
        }
        .annual { background-color: #e6f3ff; }
        .sick { background-color: #fff0f0; }
        .casual { background-color: #f0fff0; }
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th colspan="{{ 4 + (count($leaveTypes) * 3) }}" style="font-size: 16pt; font-weight: bold; text-align: center;">Employee Leave Balance Report</th>
            </tr>
            <tr>
                <th colspan="{{ 4 + (count($leaveTypes) * 3) }}" style="text-align: center;">Generated on: {{ now()->format('d M Y H:i') }}</th>
            </tr>
            <tr><th colspan="{{ 4 + (count($leaveTypes) * 3) }}"></th></tr>
            <tr>
                <th rowspan="2" class="header border">Employee Name</th>
                <th rowspan="2" class="header border">Employee ID</th>
                <th rowspan="2" class="header border">Organization</th>
                <th rowspan="2" class="header border">Department</th>
                @foreach($leaveTypes as $type)
                    <th class="bg-main text-white" colspan="3" style="border: 1px solid #012445; background-color: #012445; color: #ffffff;">{{ $type->name }}</th>
                @endforeach
            </tr>
            <tr style="background-color: #f8f9fa;">
                @foreach($leaveTypes as $type)
                    <th style="border: 1px solid #dee2e6;">Earned</th>
                    <th style="border: 1px solid #dee2e6;">Used</th>
                    <th style="border: 1px solid #dee2e6;">Remaining</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($balances as $employee)
                <tr>
                    <td style="border: 1px solid #dee2e6;"><strong>{{ $employee['employeeName'] }}</strong></td>
                    <td style="border: 1px solid #dee2e6;">{{ $employee['employeeId'] }}</td>
                    <td style="border: 1px solid #dee2e6;">{{ $employee['organization'] }}</td>
                    <td style="border: 1px solid #dee2e6;">{{ $employee['department'] }}</td>
                    
                    @foreach($leaveTypes as $type)
                        @php
                            $quota = $employee['quotas'][$type->id] ?? ['eligible' => true, 'earned' => 0, 'used' => 0, 'remaining' => 0];
                        @endphp
                        @if(($quota['eligible'] ?? true) === false)
                            <td style="border: 1px solid #dee2e6;" colspan="3" class="text-center text-muted">
                                {{ $quota['eligibilityMessage'] ?? 'Not eligible for this leave type.' }}
                            </td>
                        @else
                            <td style="border: 1px solid #dee2e6;">{{ $quota['earned'] }}</td>
                            <td style="border: 1px solid #dee2e6;">{{ $quota['used'] }}</td>
                            <td style="border: 1px solid #dee2e6; color: #198754; font-weight: bold;">{{ $quota['remaining'] }}</td>
                        @endif
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
