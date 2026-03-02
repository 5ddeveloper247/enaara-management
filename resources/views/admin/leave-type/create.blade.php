@extends('layouts.app')

@section('title', 'Add Leave Type - Admin Panel')

@section('page-title', 'Leave Types')

@section('content')
    <div class="container-fluid">
        <div class="row align-items-center mb-3">
            <div class="col-md-6">
                <h5 class="mb-0">Add New Leave Type</h5>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('admin.leave.type.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back to Leave Types
                </a>
            </div>
        </div>

        <div class="card border-0 rounded-4">
            <div class="card-body p-4">
                <form action="{{ route('admin.leave.type.store') }}" method="POST">
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
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required maxlength="255">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="code" class="form-label">Code</label>
                            <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" maxlength="64">
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="annual_quota" class="form-label">Annual Quota <span class="text-danger">*</span></label>
                            <input type="number" name="annual_quota" id="annual_quota" step="0.25" min="0" max="999.99" class="form-control @error('annual_quota') is-invalid @enderror" value="{{ old('annual_quota', '0') }}" required>
                            @error('annual_quota')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                            <button type="submit" class="btn btn-primary bg-main border-0">Create Leave Type</button>
                            <a href="{{ route('admin.leave.type.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

