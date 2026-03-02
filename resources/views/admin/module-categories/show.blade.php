@extends('layouts.app')

@section('title', 'Module Category Details - Admin Panel')

@section('page-title', 'Module Categories')

@section('content')
    <div class="container-fluid">
        <div class="row align-items-center mb-3">
            <div class="col-md-6">
                <h5 class="mb-0">Module Category Details</h5>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('admin.module.category.edit', $moduleCategory->ID) }}" class="btn btn-primary bg-main border-0 me-2">
                    <i class="bi bi-pencil me-1"></i>Edit
                </a>
                <a href="{{ route('admin.module.category.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back to Module Categories
                </a>
            </div>
        </div>

        <div class="card border-0 rounded-4">
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Category Name</label>
                        <div class="fw-semibold">{{ $moduleCategory->category_name ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">CSS Class</label>
                        <div class="fw-semibold">{{ $moduleCategory->css_class ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Display Order</label>
                        <div class="fw-semibold">{{ $moduleCategory->display_order ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Status</label>
                        <div>
                            @if($moduleCategory->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Modules Count</label>
                        <div class="fw-semibold">{{ $moduleCategory->modules->count() }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Created Date</label>
                        <div class="fw-semibold">{{ $moduleCategory->created_at ? $moduleCategory->created_at->format('M d, Y') : '—' }}</div>
                    </div>
                    @if($moduleCategory->modules->isNotEmpty())
                    <div class="col-12">
                        <label class="form-label text-muted small">Modules</label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($moduleCategory->modules as $mod)
                                <span class="badge px-3 rounded-1 bg-light text-dark">{{ $mod->module_name }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
