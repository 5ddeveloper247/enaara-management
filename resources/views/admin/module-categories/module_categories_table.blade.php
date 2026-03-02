<table id="moduleCategoriesTable" class="display nowrap table table-striped" style="width:100%">
    <thead class="bg-main">
        <tr>
            <th>#</th>
            <th>Category Name</th>
            <th>Modules Count</th>
            <th>Status</th>
            <th>Display Order</th>
            <th>Created Date</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody class="bg-transparent">
        @forelse($moduleCategories ?? [] as $index => $mc)
        <tr data-status="{{ $mc->is_active ? 'active' : 'inactive' }}" data-module-category-id="{{ $mc->ID }}">
            <td>{{ $index + 1 }}</td>
            <td>
                <div class="d-flex align-items-center">
                    <i class="bi bi-folder me-2 text-secondary"></i>
                    <span class="fw-semibold">{{ $mc->category_name ?? '—' }}</span>
                </div>
            </td>
            <td>
                <span class="text-success fw-medium">{{ $mc->modules_count ?? 0 }} Modules</span>
                @if(isset($mc->modules) && $mc->modules->isNotEmpty())
                    <div class="mt-1 d-flex flex-wrap gap-1">
                        @foreach($mc->modules->take(3) as $mod)
                            <span class="badge px-2 rounded-1 bg-light text-dark">{{ $mod->module_name }}</span>
                        @endforeach
                        @if($mc->modules_count > 3)
                            <span class="badge px-2 rounded-1 bg-light text-muted">+{{ $mc->modules_count - 3 }} more</span>
                        @endif
                    </div>
                @endif
            </td>
            <td>
                <div class="form-check form-switch">
                    <input class="form-check-input status-toggle" type="checkbox" {{ $mc->is_active ? 'checked' : '' }} data-module-category-id="{{ $mc->ID }}">
                </div>
            </td>
            <td>{{ $mc->display_order ?? '—' }}</td>
            <td>{{ $mc->created_at ? $mc->created_at->format('M d, Y') : '—' }}</td>
            <td class="text-end">
                <div class="btn-group d-flex align-items-center gap-1">
                    <button type="button" class="action-btn border-0 text-white btn-primary view-module-category"
                        data-module-category-id="{{ $mc->ID }}"
                        data-category-name="{{ e($mc->category_name ?? '') }}"
                        data-modules-count="{{ $mc->modules_count ?? 0 }}"
                        data-display-order="{{ $mc->display_order ?? '' }}"
                        data-created-date="{{ $mc->created_at ? $mc->created_at->format('M d, Y') : '' }}"
                        title="View">
                        <i class="bi bi-eye"></i>
                    </button>
                    <a href="{{ route('admin.module.category.edit', $mc->ID) }}" class="action-btn btn btn-sm btn-outline-primary" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>

                    
                    <button type="button" class="action-btn border-0 text-danger bg-danger-subtle delete-module-category"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteModuleCategoryModal"
                        data-module-category-id="{{ $mc->ID }}"
                        data-module-category-name="{{ e($mc->category_name ?? '') }}"
                        title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="text-center text-muted py-4">No module categories found.</td>
        </tr>
        @endforelse
    </tbody>
</table>
