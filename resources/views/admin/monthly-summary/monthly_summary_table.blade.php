<table id="monthlySummaryTable" class="display nowrap table table-striped" style="width:100%">
    <thead class="bg-main">
        <tr>
            <th class="dt-control"></th>
            <th>Employee Info</th>
            <th>Total Days</th>
            <th>Present</th>
            <th>Absent</th>
            <th>Half-days</th>
            @foreach ($tableLeaveTypes ?? [] as $leaveType)
                <th>{{ $leaveType->name }}</th>
            @endforeach
            <th>Late Arrivals</th>
            <th>Early Departures</th>
            <th>Zone-2 Verification</th>
            <th>Regularization</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    
    <tbody class="bg-transparent" id="monthlySummaryTableBody">
        <!-- Data will be populated by JavaScript -->
    </tbody>
</table>

