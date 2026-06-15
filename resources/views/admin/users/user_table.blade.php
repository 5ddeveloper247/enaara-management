<script>
    window.usersDataUrl   = "{{ route('admin.users.data') }}";
    window.usersStatsUrl  = "{{ route('admin.users.stats') }}";
    window.usersStoreUrl  = "{{ route('admin.users.store') }}";
    window.usersUpdateUrl = "{{ url('admin/users') }}";
    window.usersStatusUrl = "{{ url('admin/users') }}";
    window.usersDeleteUrl = "{{ url('admin/users') }}";
    window.usersResetPasswordUrl = "{{ url('admin/users') }}";
    window.canResetUserPassword = @json(validatePermissions('admin/users/{id}/update'));
    window.csrfToken      = "{{ csrf_token() }}";
</script>

<div class="px-4 pb-4">
    <table id="usersTable" class="display nowrap table table-striped" style="width:100%">
        <thead class="bg-main">
            <tr>
                <th>User</th>
                <th>Employee ID</th>
                <th>SBU</th>
                <th>Department</th>
                <th>Role</th>
                <th>Last Login</th>
                <th>Status</th>
                <th class="text-end no-toggle">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-transparent"></tbody>
    </table>
</div>
