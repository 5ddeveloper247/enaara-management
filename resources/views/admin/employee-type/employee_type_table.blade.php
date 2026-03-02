<table id="employeeTypesTable" class="display nowrap table table-striped" style="width:100%">
    <thead class="bg-main">
        <tr>
            <th>Name</th>
            <th>Code</th>
            <th>Organization</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody class="bg-transparent">
        @forelse($employeeTypes ?? [] as $et)
        <tr data-organization-id="{{ $et->organization_id }}" data-status="{{ $et->is_active ? 'active' : 'inactive' }}" data-employee-type-id="{{ $et->id }}">
            <td>
                <div class="fw-semibold">{{ $et->name }}</div>
            </td>
            <td>
                @if($et->code)
                    <span class="badge px-3 rounded-1 bg-light text-dark">{{ $et->code }}</span>
                @else
                    <span class="text-muted">—</span>
                @endif
            </td>
            <td>
                @if($et->organization)
                    <span class="badge px-3 rounded-1 bg-primary">{{ $et->organization->name }}</span>
                @else
                    <span class="text-muted">—</span>
                @endif
            </td>
            <td>
                <div class="form-check form-switch">
                    <input class="form-check-input status-toggle" type="checkbox" {{ $et->is_active ? 'checked' : '' }} data-employee-type-id="{{ $et->id }}">
                </div>
            </td>
            <td class="text-end">
                <div class="btn-group d-flex align-items-center gap-1">
                    <button type="button" class="action-btn border-0 text-white btn-primary view-employee-type"
                        data-employee-type-id="{{ $et->id }}"
                        data-employee-type-name="{{ e($et->name) }}"
                        data-employee-type-code="{{ e($et->code ?? '') }}"
                        data-organization-name="{{ $et->organization ? e($et->organization->name) : '' }}"
                        title="View">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button type="button" class="action-btn border-0 text-danger bg-danger-subtle delete-employee-type"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteEmployeeTypeModal"
                        data-employee-type-id="{{ $et->id }}"
                        data-employee-type-name="{{ e($et->name) }}"
                        data-employee-type-code="{{ e($et->code ?? '') }}"
                        title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="text-center text-muted py-4">No employee types found.</td>
        </tr>
        @endforelse
    </tbody>
</table>
