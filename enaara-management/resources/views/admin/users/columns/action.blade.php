<div class="btn-group d-flex align-items-center gap-1">
    <button type="button" class="action-btn border-0 text-white btn-primary btn-sm" data-bs-toggle="offcanvas" data-bs-target="#userCanvas"
        data-mode="edit" data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}" data-user-email="{{ $user->email }}" title="Edit">
        <i class="bi bi-pencil"></i>
    </button>
    <button type="button" class="action-btn border-0 text-white btn-primary btn-sm" data-bs-toggle="offcanvas" data-bs-target="#userCanvas"
        data-mode="view" data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}" data-user-email="{{ $user->email }}" title="View">
        <i class="bi bi-eye"></i>
    </button>
    <button type="button" class="action-btn border-0 text-danger bg-danger-subtle btn-sm delete-user" data-bs-toggle="modal"
        data-bs-target="#deleteConfirmModal" data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}"
        data-user-email="{{ $user->email }}" title="Delete">
        <i class="bi bi-trash"></i>
    </button>
</div>
