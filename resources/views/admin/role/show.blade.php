@extends('layouts.app')

@section('title', 'Role Details - Admin Panel')

@section('page-title', 'Roles')

@section('content')
    <div class="container-fluid">
        <div class="row align-items-center mb-3">
            <div class="col-md-6">
                <h5 class="mb-0">Role Details</h5>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('admin.role.edit', $role->id) }}" class="btn btn-primary bg-main border-0 me-2">
                    <i class="bi bi-pencil me-1"></i>Edit
                </a>
                <a href="{{ route('admin.role.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back to Roles
                </a>
            </div>
        </div>

        <div class="card border-0 rounded-4">
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Name</label>
                        <div class="fw-semibold">{{ $role->name ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Slug</label>
                        <div class="fw-semibold">{{ $role->slug ?? '—' }}</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small">Description</label>
                        <div class="fw-semibold">{{ $role->description ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Status</label>
                        <div>
                            @if(isset($role->is_active) && $role->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                            @if(!empty($role->is_system_admin))
                                <span class="badge bg-warning text-dark ms-1">System Administrator</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Created</label>
                        <div class="fw-semibold">{{ $role->created_at ? $role->created_at->format('M d, Y') : '—' }}</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small">Module Permissions</label>
                        @if(!empty($role->is_system_admin))
                            <div class="text-muted">All modules (permission checks bypassed for users with this role).</div>
                        @elseif($role->relationLoaded('modules') && $role->modules->isNotEmpty())
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($role->modules as $module)
                                    <span class="badge px-3 rounded-1 bg-primary">{{ $module->module_name ?? 'Module #'.$module->id }}</span>
                                @endforeach
                            </div>
                        @else
                            <div class="text-muted">—</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
