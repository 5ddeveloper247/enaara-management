<table id="workTypesTable" class="display nowrap table table-striped" style="width:100%">
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
        @forelse($workTypes ?? [] as $wt)
        <tr data-organization-id="{{ $wt->organization_id }}" data-status="{{ $wt->is_active ? 'active' : 'inactive' }}" data-work-type-id="{{ $wt->id }}">
            <td>
                <div class="fw-semibold">{{ $wt->name }}</div>
            </td>
            <td>
                @if($wt->code)
                    <span class="badge px-3 rounded-1 bg-light text-dark">{{ $wt->code }}</span>
                @else
                    <span class="text-muted">—</span>
                @endif
            </td>
            <td>
                @if($wt->organization)
                    <span class="badge px-3 rounded-1 bg-primary">{{ $wt->organization->name }}</span>
                @else
                    <span class="text-muted">—</span>
                @endif
            </td>
            <td>
                <div class="form-check form-switch">
                    <input class="form-check-input status-toggle" type="checkbox" {{ $wt->is_active ? 'checked' : '' }} data-work-type-id="{{ $wt->id }}">
                </div>
            </td>
            <td class="text-end">
                <div class="btn-group d-flex align-items-center gap-1">
                    <button type="button" class="action-btn border-0 text-white btn-primary view-work-type"
                        data-work-type-id="{{ $wt->id }}"
                        data-work-type-name="{{ e($wt->name) }}"
                        data-work-type-code="{{ e($wt->code ?? '') }}"
                        data-organization-name="{{ $wt->organization ? e($wt->organization->name) : '' }}"
                        title="View">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button type="button" class="action-btn border-0 text-danger bg-danger-subtle delete-work-type"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteWorkTypeModal"
                        data-work-type-id="{{ $wt->id }}"
                        data-work-type-name="{{ e($wt->name) }}"
                        data-work-type-code="{{ e($wt->code ?? '') }}"
                        title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="text-center text-muted py-4">No work types found.</td>
        </tr>
        @endforelse
    </tbody>
</table>
