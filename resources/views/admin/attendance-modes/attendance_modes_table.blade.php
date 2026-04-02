<table id="attendanceModesTable" class="display nowrap table table-striped" style="width:100%">
    <thead class="bg-main">
        <tr>
            <th>Name</th>
            <th>Grace (min)</th>
            <th>Organization</th>
            <th>Department</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody class="bg-transparent">
        @forelse($attendanceModes ?? [] as $am)
        <tr data-organization-id="{{ $am->organization_id }}" data-status="{{ $am->is_active ? 'active' : 'inactive' }}" data-attendance-mode-id="{{ $am->id }}">
            <td>
                <div class="fw-semibold">{{ $am->name }}</div>
            </td>
            <td>{{ $am->grace_minutes }}</td>
            <td>
                @if($am->organization)
                    <span class="badge px-3 rounded-1 bg-primary">{{ $am->organization->name }}</span>
                @else
                    <span class="text-muted">—</span>
                @endif
            </td>
            <td>
                @if($am->department)
                    <span class="badge px-3 rounded-1 bg-info">{{ $am->department->name }}</span>
                @else
                    <span class="text-muted">—</span>
                @endif
            </td>
            <td>
                <div class="form-check form-switch">
                    <input class="form-check-input status-toggle" type="checkbox" {{ $am->is_active ? 'checked' : '' }} data-attendance-mode-id="{{ $am->id }}">
                </div>
            </td>
            <td class="text-end">
                <div class="btn-group d-flex align-items-center gap-1">
                    <button type="button" class="action-btn border-0 text-white btn-primary view-attendance-mode"
                        data-attendance-mode-id="{{ $am->id }}"
                        data-attendance-mode-name="{{ e($am->name) }}"
                        data-grace-minutes="{{ $am->grace_minutes }}"
                        data-organization-name="{{ $am->organization ? e($am->organization->name) : '' }}"
                        data-department-name="{{ $am->department ? e($am->department->name) : '' }}"
                        title="View">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button type="button" class="action-btn border-0 text-danger bg-danger-subtle delete-attendance-mode"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteAttendanceModeModal"
                        data-attendance-mode-id="{{ $am->id }}"
                        data-attendance-mode-name="{{ e($am->name) }}"
                        title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="text-center text-muted py-4">No attendance modes found.</td>
        </tr>
        @endforelse
    </tbody>
</table>
