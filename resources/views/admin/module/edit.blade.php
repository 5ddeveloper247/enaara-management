@extends('layouts.app')

@section('title', 'Edit Module - Admin Panel')

@section('page-title', 'Modules')

@section('content')
    <div class="container-fluid">
        <div class="row align-items-center mb-3">
            <div class="col-md-6">
                <h5 class="mb-0">Edit Module</h5>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('admin.module.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back to Modules
                </a>
            </div>
        </div>


        <div class="card border-0 rounded-4">
            <div class="card-body p-4">
                <form action="{{ route('admin.module.update', $module->id) }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="module_category_id" class="form-label">Category</label>
                            <select name="module_category_id" id="module_category_id" class="form-select @error('module_category_id') is-invalid @enderror">
                                <option value="">Select Category</option>
                                @foreach($moduleCategories ?? [] as $cat)
                                    <option value="{{ $cat->ID }}" {{ old('module_category_id', $module->module_category_id) == $cat->ID ? 'selected' : '' }}>{{ $cat->category_name }}</option>
                                @endforeach
                            </select>
                            @error('module_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="module_name" class="form-label">Module Name <span class="text-danger">*</span></label>
                            <input type="text" name="module_name" id="module_name" class="form-control @error('module_name') is-invalid @enderror" value="{{ old('module_name', $module->module_name) }}" required maxlength="155">
                            @error('module_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="route" class="form-label">Route</label>
                            <input type="text" name="route" id="route" class="form-control @error('route') is-invalid @enderror" value="{{ old('route', $module->route) }}" maxlength="155">
                            @error('route')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="css_class" class="form-label">CSS Class</label>
                            <input type="text" name="css_class" id="css_class" class="form-control @error('css_class') is-invalid @enderror" value="{{ old('css_class', $module->css_class) }}" maxlength="100">
                            @error('css_class')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="display_order" class="form-label">Display Order <span class="text-danger">*</span></label>
                            <input type="number" name="display_order" id="display_order" min="0" class="form-control @error('display_order') is-invalid @enderror" value="{{ old('display_order', $module->display_order) }}" required>
                            @error('display_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="show_in_menu" class="form-label">Show in Menu</label>
                            <div class="form-check form-switch mt-2">
                                <input type="hidden" name="show_in_menu" value="0">
                                <input type="checkbox" name="show_in_menu" id="show_in_menu" class="form-check-input" value="1" {{ old('show_in_menu', $module->show_in_menu) ? 'checked' : '' }}>
                                <label class="form-check-label" for="show_in_menu">Yes</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary bg-main border-0">Update Module</button>
                            <a href="{{ route('admin.module.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
