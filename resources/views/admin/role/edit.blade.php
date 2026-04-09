@extends('layouts.app')

@section('title', 'Edit Role - Admin Panel')

@section('page-title', 'Roles')

@section('content')
<div class="container-fluid">
    <div class="row align-items-center mb-3">
        <div class="col-md-6">
            <h5 class="mb-0">Edit Role</h5>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('admin.role.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to Roles
            </a>
        </div>
    </div>


    <div class="card border-0 rounded-4">
        <div class="card-body p-4">
            <form action="{{ route('admin.role.update', $role->id) }}" method="POST">
                @csrf
                <h6 class="mb-3 fw-semibold">
                    <i class="bi bi-shield-check me-2"></i>Role Information
                </h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Role Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $role->name) }}" required maxlength="255" placeholder="Enter role name">
                        <small class="text-muted">Role name should be unique and descriptive</small>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="slug" class="form-label">Slug</label>
                        <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $role->slug) }}" maxlength="255">
                        @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3" maxlength="500">{{ old('description', $role->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="organization_id" class="form-label">Organization</label>
                        <select name="organization_id" id="organization_id" class="form-select @error('organization_id') is-invalid @enderror">
                            <option value="">Select Organization</option>
                            @foreach($organizations ?? [] as $organization)
                            <option value="{{ $organization->id }}"
                                {{ old('organization_id', $role->organization_id) == $organization->id ? 'selected' : '' }}>
                                {{ $organization->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('organization_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="department_id" class="form-label">Department</label>
                        <select name="department_id" id="department_id" class="form-select @error('department_id') is-invalid @enderror">
                            <option value="">Select Department</option>
                            @foreach($departments ?? [] as $department)
                            <option value="{{ $department->id }}"
                                {{ old('department_id', $role->department_id) == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('department_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="parent_role_id" class="form-label">Parent Role</label>
                        <select name="parent_role_id" id="parent_role_id" class="form-select @error('parent_role_id') is-invalid @enderror">
                            <option value="">Select Parent Role</option>
                            @foreach($parentRoles ?? [] as $parentRole)
                            <option value="{{ $parentRole->id }}"
                                {{ old('parent_role_id', $role->parent_role_id) == $parentRole->id ? 'selected' : '' }}>
                                {{ $parentRole->name }}
                            </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Optional. Used for approval hierarchy.</small>
                        @error('parent_role_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="is_active" class="form-label">Status</label>
                        <select name="is_active" id="is_active" class="form-select @error('is_active') is-invalid @enderror">
                            <option value="1" {{ old('is_active', isset($role->is_active) ? $role->is_active : true) ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('is_active') === false || old('is_active') === '0' ? 'selected' : (isset($role->is_active) && !$role->is_active ? 'selected' : '') }}>Inactive</option>
                        </select>
                        @error('is_active')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="is_primary" class="form-label">Primary Role</label>
                        <div class="form-check form-switch mt-2">
                            <input type="hidden" name="is_primary" value="0">
                            <input type="checkbox" name="is_primary" id="is_primary" class="form-check-input" value="1" {{ old('is_primary', isset($role->is_primary) ? $role->is_primary : false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_primary">Mark as primary role</label>
                        </div>
                        @error('is_primary')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>

                <hr class="my-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-grid me-2"></i>Module Permissions
                    </h6>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="selectAllModules">
                        <label class="form-check-label" for="selectAllModules">Select All Modules</label>
                    </div>
                </div>
                @php
                $selectedModuleIds = old('module_ids', $role->modules->pluck('id')->toArray());
                @endphp
                @foreach($moduleCategories ?? [] as $category)
                <div class="mb-4">
                    <h6 class="text-muted small mb-2">{{ $category->category_name ?? 'Uncategorized' }} <span class="fw-normal">({{ $category->modules->count() }} modules)</span></h6>
                    <div class="row g-2">
                        @foreach($category->modules as $module)
                        <div class="col-md-4 col-lg-3">
                            <div class="form-check">
                                <input class="form-check-input module-privilege-cb" type="checkbox" name="module_ids[]" value="{{ $module->id }}" id="module_{{ $module->id }}" {{ in_array($module->id, $selectedModuleIds) ? 'checked' : '' }}>
                                <label class="form-check-label" for="module_{{ $module->id }}">{{ $module->module_name ?? $module->id }}</label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
                @if(empty($moduleCategories) || $moduleCategories->isEmpty())
                <p class="text-muted small">No modules available. Add modules first.</p>
                @endif

                <hr class="my-4">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary bg-main border-0">Update Role</button>
                    <a href="{{ route('admin.role.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const organizationSelect = document.getElementById('organization_id');
        const departmentSelect = document.getElementById('department_id');
        const parentRoleSelect = document.getElementById('parent_role_id');
        const selectAllModules = document.getElementById('selectAllModules');
        const currentRoleId = "{{ $role->id }}";

        function resetSelect(select, placeholder) {
            if (!select) return;
            select.innerHTML = `<option value="">${placeholder}</option>`;
        }

        function loadDepartments(organizationId, selectedDepartmentId = '') {
            resetSelect(departmentSelect, 'Select Department');

            if (!organizationId) {
                return;
            }

            fetch(`{{ route('admin.role.departmentsByOrganization') }}?organization_id=${organizationId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && Array.isArray(data.departments)) {
                        data.departments.forEach(department => {
                            const option = document.createElement('option');
                            option.value = department.id;
                            option.textContent = department.name;

                            if (selectedDepartmentId && String(selectedDepartmentId) === String(department.id)) {
                                option.selected = true;
                            }

                            departmentSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => console.error('Error loading departments:', error));
        }

        function loadParentRoles(organizationId, departmentId = '', selectedParentRoleId = '') {
            resetSelect(parentRoleSelect, 'Select Parent Role');

            if (!organizationId) {
                return;
            }

            let url = `{{ route('admin.role.parentRoles') }}?organization_id=${organizationId}&exclude_role_id=${currentRoleId}`;

            if (departmentId) {
                url += `&department_id=${departmentId}`;
            }

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success && Array.isArray(data.roles)) {
                        data.roles.forEach(role => {
                            const option = document.createElement('option');
                            option.value = role.id;
                            option.textContent = role.name;

                            if (selectedParentRoleId && String(selectedParentRoleId) === String(role.id)) {
                                option.selected = true;
                            }

                            parentRoleSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => console.error('Error loading parent roles:', error));
        }

        organizationSelect?.addEventListener('change', function() {
            const organizationId = this.value;

            resetSelect(departmentSelect, 'Select Department');
            resetSelect(parentRoleSelect, 'Select Parent Role');

            if (organizationId) {
                loadDepartments(organizationId);
                loadParentRoles(organizationId);
            }
        });

        departmentSelect?.addEventListener('change', function() {
            const organizationId = organizationSelect?.value || '';
            const departmentId = this.value;

            loadParentRoles(organizationId, departmentId);
        });

        selectAllModules?.addEventListener('change', function() {
            document.querySelectorAll('.module-privilege-cb').forEach(function(cb) {
                cb.checked = selectAllModules.checked;
            });
        });

        const currentOrganizationId = @json(old('organization_id', $role->organization_id));
        const currentDepartmentId = @json(old('department_id', $role->department_id));
        const currentParentRoleId = @json(old('parent_role_id', $role->parent_role_id));

        if (currentOrganizationId) {
            loadDepartments(currentOrganizationId, currentDepartmentId);

            if (currentDepartmentId) {
                loadParentRoles(currentOrganizationId, currentDepartmentId, currentParentRoleId);
            } else {
                loadParentRoles(currentOrganizationId, '', currentParentRoleId);
            }
        }
    });
</script>
@endsection