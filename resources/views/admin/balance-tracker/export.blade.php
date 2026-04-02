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
                <th colspan="13" style="font-size: 16pt; font-weight: bold; text-align: center;">Employee Leave Balance Report</th>
            </tr>
            <tr>
                <th colspan="13" style="text-align: center;">Generated on: {{ now()->format('d M Y H:i') }}</th>
            </tr>
            <tr><th colspan="13"></th></tr>
            <tr>
                <th rowspan="2" class="header border">Employee Name</th>
                <th rowspan="2" class="header border">Employee ID</th>
                <th rowspan="2" class="header border">Organization</th>
                <th rowspan="2" class="header border">Department</th>
                <th class="bg-main text-white" colspan="3" style="border: 1px solid #012445; background-color: #012445; color: #ffffff;">Annual Leave</th>
                <th class="bg-main text-white" colspan="3" style="border: 1px solid #012445; background-color: #012445; color: #ffffff;">Sick Leave</th>
                <th class="bg-main text-white" colspan="3" style="border: 1px solid #012445; background-color: #012445; color: #ffffff;">Casual Leave</th>
            </tr>
            <tr style="background-color: #f8f9fa;">
                <th style="border: 1px solid #dee2e6;">Earned</th>
                <th style="border: 1px solid #dee2e6;">Used</th>
                <th style="border: 1px solid #dee2e6;">Remaining</th>
                <th style="border: 1px solid #dee2e6;">Earned</th>
                <th style="border: 1px solid #dee2e6;">Used</th>
                <th style="border: 1px solid #dee2e6;">Remaining</th>
                <th style="border: 1px solid #dee2e6;">Earned</th>
                <th style="border: 1px solid #dee2e6;">Used</th>
                <th style="border: 1px solid #dee2e6;">Remaining</th>
            </tr>
        </thead>
        <tbody>
            @foreach($balances as $employee)
                <tr>
                    <td style="border: 1px solid #dee2e6;">{{ $employee['employeeId'] }}</td>
                    <td style="border: 1px solid #dee2e6;"><strong>{{ $employee['employeeName'] }}</strong></td>
                    <td style="border: 1px solid #dee2e6;">{{ $employee['organization'] }}</td>
                    <td style="border: 1px solid #dee2e6;">{{ $employee['department'] }}</td>
                    
                    {{-- Annual --}}
                    <td style="border: 1px solid #dee2e6;">{{ $employee['annual']['earned'] }}</td>
                    <td style="border: 1px solid #dee2e6;">{{ $employee['annual']['used'] }}</td>
                    <td style="border: 1px solid #dee2e6; color: #198754; font-weight: bold;">{{ $employee['annual']['remaining'] }}</td>
                    
                    {{-- Sick --}}
                    <td style="border: 1px solid #dee2e6;">{{ $employee['sick']['earned'] }}</td>
                    <td style="border: 1px solid #dee2e6;">{{ $employee['sick']['used'] }}</td>
                    <td style="border: 1px solid #dee2e6; color: #198754; font-weight: bold;">{{ $employee['sick']['remaining'] }}</td>
                    
                    {{-- Casual --}}
                    <td style="border: 1px solid #dee2e6;">{{ $employee['casual']['earned'] }}</td>
                    <td style="border: 1px solid #dee2e6;">{{ $employee['casual']['used'] }}</td>
                    <td style="border: 1px solid #dee2e6; color: #198754; font-weight: bold;">{{ $employee['casual']['remaining'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
