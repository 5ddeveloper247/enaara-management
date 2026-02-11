<table id="rolesTable" class="display nowrap table table-striped" style="width:100%">
    <thead class="bg-main">
        <tr>
            <th>Role Name</th>
            <th>Type</th>
            <th>Users</th>
            <th>Permissions</th>
            <th>Status</th>
            <th>Last Updated</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody class="bg-transparent">
        <!-- Sample data - Replace with dynamic data from backend -->
        @php
            $roles = [
                ['name' => 'Super Admin', 'type' => 'System', 'users' => 2, 'permissions' => 'All', 'status' => 'Active', 'updated' => '2 days ago'],
                ['name' => 'Admin', 'type' => 'System', 'users' => 5, 'permissions' => '45', 'status' => 'Active', 'updated' => '1 week ago'],
                ['name' => 'Manager', 'type' => 'System', 'users' => 12, 'permissions' => '32', 'status' => 'Active', 'updated' => '3 days ago'],
                ['name' => 'HR Manager', 'type' => 'Custom', 'users' => 3, 'permissions' => '28', 'status' => 'Active', 'updated' => '5 days ago'],
                ['name' => 'Department Head', 'type' => 'Custom', 'users' => 8, 'permissions' => '24', 'status' => 'Active', 'updated' => '1 week ago'],
                ['name' => 'Agent', 'type' => 'System', 'users' => 25, 'permissions' => '18', 'status' => 'Active', 'updated' => '2 weeks ago'],
                ['name' => 'Employee', 'type' => 'System', 'users' => 150, 'permissions' => '12', 'status' => 'Active', 'updated' => '1 month ago'],
                ['name' => 'Guest', 'type' => 'Custom', 'users' => 0, 'permissions' => '5', 'status' => 'Inactive', 'updated' => '2 months ago'],
            ];
        @endphp
        @foreach($roles as $index => $role)
        <tr>
            <td>
                <div class="d-flex align-items-center">
                    <div class="role-icon me-3">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div>
                        <div class="fw-semibold">{{ $role['name'] }}</div>
                        <small class="text-muted">Role ID: ROL-{{ str_pad($index + 1, 3, '0', STR_PAD_LEFT) }}</small>
                    </div>
                </div>
            </td>
            <td>
                @if($role['type'] === 'System')
                    <span class="badge px-2 rounded-1 bg-primary">
                        <i class="bi bi-gear me-1"></i>{{ $role['type'] }}
                    </span>
                @else
                    <span class="badge px-2 rounded-1 bg-info">
                        <i class="bi bi-pencil-square me-1"></i>{{ $role['type'] }}
                    </span>
                @endif
            </td>
            <td>
                <div class="fw-semibold">{{ $role['users'] }}</div>
                <small class="text-muted">users assigned</small>
            </td>
            <td>
                @if($role['permissions'] === 'All')
                    <span class="badge px-2 rounded-1 bg-success">
                        <i class="bi bi-check-all me-1"></i>All Permissions
                    </span>
                @else
                    <span class="badge px-2 rounded-1 bg-secondary">
                        <i class="bi bi-list-check me-1"></i>{{ $role['permissions'] }} Permissions
                    </span>
                @endif
            </td>
            <td>
                @if($role['status'] === 'Active')
                    <span class="badge px-2 rounded-1 bg-success">
                        <i class="bi bi-check-circle me-1"></i>{{ $role['status'] }}
                    </span>
                @else
                    <span class="badge px-2 rounded-1 bg-secondary">
                        <i class="bi bi-x-circle me-1"></i>{{ $role['status'] }}
                    </span>
                @endif
            </td>
            <td>
                <small class="text-muted">{{ $role['updated'] }}</small>
            </td>
            <td class="text-end">
                <button type="button" 
                        class="action-btn border-0 text-white btn-primary view-role-btn me-1" 
                        title="View Details"
                        data-bs-toggle="offcanvas"
                        data-bs-target="#roleDetailCanvas"
                        data-role-id="ROL-{{ str_pad($index + 1, 3, '0', STR_PAD_LEFT) }}"
                        data-role-name="{{ $role['name'] }}"
                        data-role-type="{{ $role['type'] }}"
                        data-role-users="{{ $role['users'] }}"
                        data-role-permissions="{{ $role['permissions'] }}"
                        data-role-status="{{ $role['status'] }}"
                        data-role-updated="{{ $role['updated'] }}">
                    <i class="bi bi-eye"></i>
                </button>
                <button type="button" 
                        class="action-btn border-0 text-white btn-success edit-role-btn me-1" 
                        title="Edit Role"
                        data-bs-toggle="offcanvas"
                        data-bs-target="#addRoleCanvas"
                        data-mode="edit"
                        data-role-id="ROL-{{ str_pad($index + 1, 3, '0', STR_PAD_LEFT) }}"
                        data-role-name="{{ $role['name'] }}">
                    <i class="bi bi-pencil"></i>
                </button>
                @if($role['type'] === 'Custom')
                    <button type="button" 
                            class="action-btn border-0 text-white btn-danger delete-role-btn" 
                            title="Delete Role"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteRoleModal"
                            data-role-id="ROL-{{ str_pad($index + 1, 3, '0', STR_PAD_LEFT) }}"
                            data-role-name="{{ $role['name'] }}">
                        <i class="bi bi-trash"></i>
                    </button>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

