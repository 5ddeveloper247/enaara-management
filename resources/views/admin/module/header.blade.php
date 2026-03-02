<div class="row align-items-center p-4">
    <div class="col-md-6">
        <h5 class="mb-0">Manage Modules</h5>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('admin.module.add') }}" class="btn btn-primary bg-main border-0 me-2">
            <i class="bi bi-plus-circle me-1"></i>Add New
        </a>
        <button type="button" class="btn btn-outline-secondary me-2" id="exportBtn">
            <i class="bi bi-download me-1"></i>Export
        </button>
    </div>
</div>
@if(session('success'))
    <div class="px-4 pb-2">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
@endif
