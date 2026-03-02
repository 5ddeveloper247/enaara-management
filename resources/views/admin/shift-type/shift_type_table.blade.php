<table id="shiftTypesTable" class="display nowrap table table-striped" style="width:100%">
    <thead class="bg-main">
        <tr>
            <th>Name</th>
            <th>Code</th>
            <th>Start – End</th>
            <th>Break (min)</th>
            <th>Night</th>
            <th>Organization</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody class="bg-transparent">
        @forelse($shiftTypes ?? [] as $st)
        <tr data-organization-id="{{ $st->organization_id }}" data-status="{{ $st->is_active ? 'active' : 'inactive' }}" data-shift-type-id="{{ $st->id }}">
            <td>
                <div class="fw-semibold">{{ $st->name }}</div>
            </td>
            <td>
                @if($st->code)
                    <span class="badge px-3 rounded-1 bg-light text-dark">{{ $st->code }}</span>
                @else
                    <span class="text-muted">—</span>
                @endif
            </td>
            <td>{{ \Carbon\Carbon::parse($st->start_time)->format('H:i') }} – {{ \Carbon\Carbon::parse($st->end_time)->format('H:i') }}</td>
            <td>{{ $st->break_duration_minutes }}</td>
            <td>
                @if($st->is_night_shift)
                    <span class="badge bg-dark">Yes</span>
                @else
                    <span class="text-muted">No</span>
                @endif
            </td>
            <td>
                @if($st->organization)
                    <span class="badge px-3 rounded-1 bg-primary">{{ $st->organization->name }}</span>
                @else
                    <span class="text-muted">—</span>
                @endif
            </td>
            <td>
                <div class="form-check form-switch">
                    <input class="form-check-input status-toggle" type="checkbox" {{ $st->is_active ? 'checked' : '' }} data-shift-type-id="{{ $st->id }}">
                </div>
            </td>
            <td class="text-end">
                <div class="btn-group d-flex align-items-center gap-1">
                    <button type="button" class="action-btn border-0 text-white btn-primary view-shift-type"
                        data-shift-type-id="{{ $st->id }}"
                        data-shift-type-name="{{ e($st->name) }}"
                        data-shift-type-code="{{ e($st->code ?? '') }}"
                        data-start-time="{{ \Carbon\Carbon::parse($st->start_time)->format('H:i') }}"
                        data-end-time="{{ \Carbon\Carbon::parse($st->end_time)->format('H:i') }}"
                        data-break-minutes="{{ $st->break_duration_minutes }}"
                        data-night-shift="{{ $st->is_night_shift ? '1' : '0' }}"
                        data-organization-name="{{ $st->organization ? e($st->organization->name) : '' }}"
                        title="View">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button type="button" class="action-btn border-0 text-danger bg-danger-subtle delete-shift-type"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteShiftTypeModal"
                        data-shift-type-id="{{ $st->id }}"
                        data-shift-type-name="{{ e($st->name) }}"
                        data-shift-type-code="{{ e($st->code ?? '') }}"
                        title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="text-center text-muted py-4">No shift types found.</td>
        </tr>
        @endforelse
    </tbody>
</table>
