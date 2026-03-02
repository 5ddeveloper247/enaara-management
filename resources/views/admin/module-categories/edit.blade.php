@extends('layouts.app')

@section('title', 'Edit Module Category - Admin Panel')

@section('page-title', 'Module Categories')

@section('content')
    <div class="container-fluid">
        <div class="row align-items-center mb-3">
            <div class="col-md-6">
                <h5 class="mb-0">Edit Module Category</h5>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('admin.module.category.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back to Module Categories
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card border-0 rounded-4">
            <div class="card-body p-4">
                <form action="{{ route('admin.module.category.update', $moduleCategory->ID) }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="category_name" class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" name="category_name" id="category_name" class="form-control @error('category_name') is-invalid @enderror" value="{{ old('category_name', $moduleCategory->category_name) }}" required maxlength="155">
                            @error('category_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="css_class" class="form-label">CSS Class</label>
                            <input type="text" name="css_class" id="css_class" class="form-control @error('css_class') is-invalid @enderror" value="{{ old('css_class', $moduleCategory->css_class) }}" maxlength="100">
                            @error('css_class')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="display_order" class="form-label">Display Order</label>
                            <input type="number" name="display_order" id="display_order" min="0" class="form-control @error('display_order') is-invalid @enderror" value="{{ old('display_order', $moduleCategory->display_order) }}">
                            @error('display_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="is_active" class="form-label">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ old('is_active', $moduleCategory->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary bg-main border-0">Update Module Category</button>
                            <a href="{{ route('admin.module.category.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
