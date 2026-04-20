@extends('layouts.app')

@section('title', 'Add Department - Admin Panel')

@section('page-title', 'Departments')

@section('content')
    <div class="container-fluid">
        <div class="row align-items-center mb-3">
            <div class="col-md-6">
                <h5 class="mb-0">Add New Department</h5>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('admin.department.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back to Departments
                </a>
            </div>
        </div>
        <div class="card border-0 rounded-4">
            <div class="card-body p-4">
                <form action="{{ route('admin.department.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="organization_id" class="form-label">Organization <span class="text-danger">*</span></label>
                            <select name="organization_id" id="organization_id" class="form-select @error('organization_id') is-invalid @enderror" required>
                                <option value="">Select Organization</option>
                                @foreach($organizations ?? [] as $org)
                                    <option value="{{ $org->id }}" {{ old('organization_id') == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
                                @endforeach
                            </select>
                            @error('organization_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="sbu_id" class="form-label">SBU <span class="text-danger">*</span></label>
                            <select name="sbu_id" id="sbu_id" class="form-select @error('sbu_id') is-invalid @enderror" required>
                                <option value="">Select SBU</option>
                                @foreach($sbus ?? [] as $sbu)
                                    <option value="{{ $sbu->id }}" {{ old('sbu_id') == $sbu->id ? 'selected' : '' }}>{{ $sbu->name }}</option>
                                @endforeach
                            </select>
                            @error('sbu_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required maxlength="50">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="code" class="form-label">Code</label>
                            <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" maxlength="10">
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="parent_department_id" class="form-label">Parent Department</label>
                            <select name="parent_department_id" id="parent_department_id" class="form-select @error('parent_department_id') is-invalid @enderror">
                                <option value="">None</option>
                                @foreach($parentDepartments ?? [] as $parent)
                                    <option value="{{ $parent->id }}" {{ old('parent_department_id') == $parent->id ? 'selected' : '' }}>{{ $parent->name }} {{ $parent->organization ? '(' . $parent->organization->name . ')' : '' }}</option>
                                @endforeach
                            </select>
                            @error('parent_department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="is_active" class="form-label">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary bg-main border-0">Create Department</button>
                            <a href="{{ route('admin.department.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
