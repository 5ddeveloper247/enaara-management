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
            @if(validatePermissions('admin/balance-tracker/update'))
            <th class="text-end">Actions</th>
            @endif
        </tr>
    </thead>
    <tbody class="bg-transparent" id="balanceTableBody">
        <!-- Sample data will be populated by JavaScript -->
    </tbody>
</table>

