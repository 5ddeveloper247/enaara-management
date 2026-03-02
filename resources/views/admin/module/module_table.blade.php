<table id="modulesTable" class="display nowrap table table-striped" style="width:100%">
    <thead class="bg-main">
        <tr>
            <th>#</th>
            <th>Module Name</th>
            <th>Category</th>
            <th>Route</th>
            <th>Show in Menu</th>
            <th>Display Order</th>
            <th>Created Date</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody class="bg-transparent">
        @forelse($modules ?? [] as $index => $m)
        <tr data-status="{{ $m->show_in_menu ? 'active' : 'inactive' }}" data-module-id="{{ $m->id }}">
            <td>{{ $index + 1 }}</td>
            <td>
                <div class="fw-semibold">{{ $m->module_name ?? '—' }}</div>
            </td>
            <td>
                @if($m->moduleCategory)
                    <span class="badge px-3 rounded-1 bg-primary">{{ $m->moduleCategory->category_name }}</span>
                @else
                    <span class="text-muted">—</span>
                @endif
            </td>
            <td>{{ $m->route ?? '—' }}</td>
            <td>
                <div class="form-check form-switch">
                    <input class="form-check-input status-toggle" type="checkbox" {{ $m->show_in_menu ? 'checked' : '' }} data-module-id="{{ $m->id }}">
                </div>
            </td>
            <td>{{ $m->display_order ?? '—' }}</td>
            <td>{{ $m->created_at ? $m->created_at->format('M d, Y') : '—' }}</td>
            <td class="text-end">
                <div class="btn-group d-flex align-items-center gap-1">
                    <button type="button" class="action-btn border-0 text-white btn-primary view-module"
                        data-module-id="{{ $m->id }}"
                        data-module-name="{{ e($m->module_name ?? '') }}"
                        data-category-name="{{ $m->moduleCategory ? e($m->moduleCategory->category_name) : '' }}"
                        data-route="{{ e($m->route ?? '') }}"
                        data-display-order="{{ $m->display_order ?? '' }}"
                        data-created-date="{{ $m->created_at ? $m->created_at->format('M d, Y') : '' }}"
                        title="View">
                        <i class="bi bi-eye"></i>
                    </button>
                    <a href="{{ route('admin.module.edit', $m->id) }}" class="action-btn border-0 text-primary bg-primary-subtle" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <button type="button" class="action-btn border-0 text-danger bg-danger-subtle delete-module"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteModuleModal"
                        data-module-id="{{ $m->id }}"
                        data-module-name="{{ e($m->module_name ?? '') }}"
                        title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="text-center text-muted py-4">No modules found.</td>
        </tr>
        @endforelse
    </tbody>
</table>
