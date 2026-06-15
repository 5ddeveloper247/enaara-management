<table id="rolesTable" class="display nowrap table table-striped" style="width:100%">
    <thead class="bg-main">
        <tr>
            <th>#</th>
            <th>Role Name</th>
            <th>Modules Count</th>
            <th>Assigned Modules</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody class="bg-transparent">
        @forelse($roles ?? [] as $index => $r)
        <tr data-role-id="{{ $r->id }}">
            <td>{{ $index + 1 }}</td>
            <td>
                <div class="d-flex align-items-center">
                    <i class="bi bi-shield-check me-2 text-secondary"></i>
                    <span class="fw-semibold">{{ $r->name ?? '—' }}</span>
                </div>
            </td>
            <td>
                @php $modulesCount = $r->modules_count ?? ($r->modules->count() ?? 0); @endphp
                <span class="text-success fw-medium">{{ $modulesCount }} Modules</span>
            </td>
            <td>
                @if(isset($r->modules) && $r->modules->isNotEmpty())
                    <div class="d-flex flex-wrap gap-1 align-items-center">
                        @foreach($r->modules->take(3) as $mod)
                            <span class="badge px-2 py-1 rounded-1 bg-light text-dark border">{{ $mod->module_name ?? 'Module' }}</span>
                        @endforeach
                        @if($modulesCount > 3)
                            <span class="badge px-2 py-1 rounded-1 bg-primary text-white">+{{ $modulesCount - 3 }}</span>
                        @endif
                    </div>
                @else
                    <span class="text-muted">No modules assigned</span>
                @endif
            </td>
            <td class="text-end">
                <div class="btn-group d-flex align-items-center gap-1">
                    @if(validatePermissions('admin/role/edit'))
                    <a href="{{ route('admin.role.edit', $r->id) }}" class="action-btn border-0 btn btn-success btn-sm" title="Edit">
                        <i class="bi bi-pencil text-white"></i>
                    </a>
                    @endif
                    @if(validatePermissions('admin/role/delete'))
                    <!-- <button type="button" class="action-btn border-0 btn btn-danger btn-sm delete-role"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteRoleModal"
                        data-role-id="{{ $r->id }}"
                        data-role-name="{{ e($r->name ?? '') }}"
                        title="Delete">
                        <i class="bi bi-trash text-white"></i>
                    </button> -->
                    @endif
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="text-center text-muted py-4">No roles found.</td>
        </tr>
        @endforelse
    </tbody>
</table>
