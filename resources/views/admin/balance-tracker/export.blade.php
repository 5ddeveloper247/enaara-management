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
                <th colspan="3" class="header border annual">Annual Leave</th>
                <th colspan="3" class="header border sick">Sick Leave</th>
                <th colspan="3" class="header border casual">Casual Leave</th>
            </tr>
            <tr>
                <th class="subheader border annual">Earned</th>
                <th class="subheader border annual">Used</th>
                <th class="subheader border annual">Remaining</th>
                <th class="subheader border sick">Earned</th>
                <th class="subheader border sick">Used</th>
                <th class="subheader border sick">Remaining</th>
                <th class="subheader border casual">Earned</th>
                <th class="subheader border casual">Used</th>
                <th class="subheader border casual">Remaining</th>
            </tr>
        </thead>
        <tbody>
            @foreach($balances as $employee)
                <tr>
                    <td class="border">{{ $employee['employeeName'] }}</td>
                    <td class="border text-center">{{ $employee['employeeId'] }}</td>
                    <td class="border">{{ $employee['organization'] }}</td>
                    <td class="border">{{ $employee['department'] }}</td>
                    
                    {{-- Annual --}}
                    <td class="border text-center annual">{{ $employee['annual']['earned'] }}</td>
                    <td class="border text-center annual">{{ $employee['annual']['used'] }}</td>
                    <td class="border text-center annual text-bold">{{ $employee['annual']['remaining'] }}</td>
                    
                    {{-- Sick --}}
                    <td class="border text-center sick">{{ $employee['sick']['earned'] }}</td>
                    <td class="border text-center sick">{{ $employee['sick']['used'] }}</td>
                    <td class="border text-center sick text-bold">{{ $employee['sick']['remaining'] }}</td>
                    
                    {{-- Casual --}}
                    <td class="border text-center casual">{{ $employee['casual']['earned'] }}</td>
                    <td class="border text-center casual">{{ $employee['casual']['used'] }}</td>
                    <td class="border text-center casual text-bold">{{ $employee['casual']['remaining'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
