@extends('layouts.app')

@section('title', 'Module Details - Admin Panel')

@section('page-title', 'Modules')

@section('content')
    <div class="container-fluid">
        <div class="row align-items-center mb-3">
            <div class="col-md-6">
                <h5 class="mb-0">Module Details</h5>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('admin.module.edit', $module->id) }}" class="btn btn-primary bg-main border-0 me-2">
                    <i class="bi bi-pencil me-1"></i>Edit
                </a>
                <a href="{{ route('admin.module.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back to Modules
                </a>
            </div>
        </div>

        <div class="card border-0 rounded-4">
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Module Name</label>
                        <div class="fw-semibold">{{ $module->module_name ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Category</label>
                        <div class="fw-semibold">{{ $module->moduleCategory ? $module->moduleCategory->category_name : '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Route</label>
                        <div class="fw-semibold">{{ $module->route ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">CSS Class</label>
                        <div class="fw-semibold">{{ $module->css_class ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Display Order</label>
                        <div class="fw-semibold">{{ $module->display_order ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Show in Menu</label>
                        <div>
                            @if($module->show_in_menu)
                                <span class="badge bg-success">Yes</span>
                            @else
                                <span class="badge bg-secondary">No</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Created Date</label>
                        <div class="fw-semibold">{{ $module->created_at ? $module->created_at->format('M d, Y') : '—' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
