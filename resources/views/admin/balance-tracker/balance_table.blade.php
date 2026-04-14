<table id="balanceTable" class="display nowrap table table-striped" style="width:100%">
    <thead>
        <tr>
            <th class="dt-control"></th>
            <th>Employee</th>
            <th>Organization</th>
            <th>Department</th>
            @foreach($leaveTypes as $type)
                <th>{{ $type->name }}</th>
            @endforeach
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody class="bg-transparent" id="balanceTableBody">
        <!-- Sample data will be populated by JavaScript -->
    </tbody>
</table>

