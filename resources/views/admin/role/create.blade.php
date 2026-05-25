@extends('layouts.app')

@section('title', 'Add Role - Admin Panel')

@section('page-title', 'Roles')

@push('styles')
<style>
    .sbu-input-box {
        background: #fff;
        border: 1.5px solid #ced4da;
        border-radius: 10px;
        padding: 8px 40px 8px 8px;
        min-height: 46px;
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        align-items: center;
        cursor: text;
        position: relative;
        transition: border-color .15s, box-shadow .15s;
    }
    .sbu-input-box:hover {
        border-color: #adb5bd;
    }
    .sbu-input-box.open,
    .sbu-input-box:focus-within {
        border-color: #86b7fe;
        box-shadow: 0 0 0 3px rgba(13,110,253,.12);
        outline: none;
    }
    .sbu-input-box.is-invalid {
        border-color: #dc3545;
    }
    .sbu-input-box.is-invalid.open {
        box-shadow: 0 0 0 3px rgba(220,53,69,.12);
    }
    .sbu-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #e9f2ff;
        border: 1px solid #b6d4fe;
        color: #0a3060;
        font-size: 12px;
        font-weight: 500;
        padding: 3px 6px 3px 10px;
        border-radius: 999px;
        cursor: default;
        transition: background .12s;
    }
    .sbu-chip:hover {
        background: #c8dffe;
    }
    .sbu-chip-x {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: #185FA5;
        flex-shrink: 0;
    }
    .sbu-chip-x:hover {
        background: #85B7EB;
        color: #042C53;
    }
    .sbu-ph {
        font-size: 14px;
        color: #adb5bd;
        padding: 2px 4px;
        pointer-events: none;
    }
    .sbu-chevron {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
        color: #adb5bd;
        transition: transform .18s;
    }
    .sbu-input-box.open .sbu-chevron {
        transform: translateY(-50%) rotate(180deg);
    }
    .sbu-dropdown {
        background: #fff;
        border: 1px solid #ced4da;
        border-radius: 10px;
        margin-top: 6px;
        overflow: hidden;
        z-index: 1050;
        position: relative;
    }
    .sbu-search-row {
        padding: 8px;
        border-bottom: 1px solid #f0f0f0;
    }
    .sbu-search-row input {
        width: 100%;
        border: 1px solid #ced4da;
        border-radius: 8px;
        padding: 7px 12px;
        font-size: 13px;
        background: #f8f9fa;
        color: #212529;
        outline: none;
        transition: border-color .15s, box-shadow .15s;
    }
    .sbu-search-row input:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 2px rgba(13,110,253,.1);
        background: #fff;
    }
    .sbu-opt-list {
        max-height: 210px;
        overflow-y: auto;
        padding: 4px 0;
    }
    .sbu-opt {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 14px;
        cursor: pointer;
        font-size: 14px;
        color: #212529;
        transition: background .1s;
        user-select: none;
    }
    .sbu-opt:hover {
        background: #f8f9fa;
    }
    .sbu-opt.picked {
        background: #e9f2ff;
    }
    .sbu-opt.picked .sbu-opt-name {
        color: #0a3060;
        font-weight: 500;
    }
    .sbu-opt-cb {
        width: 17px;
        height: 17px;
        border-radius: 5px;
        border: 1.5px solid #adb5bd;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all .12s;
    }
    .sbu-opt.picked .sbu-opt-cb {
        background: #0d6efd;
        border-color: #0d6efd;
    }
    .sbu-opt-ck {
        display: none;
        width: 10px;
        height: 10px;
    }
    .sbu-opt.picked .sbu-opt-ck {
        display: block;
    }
    .sbu-no-result {
        padding: 16px;
        font-size: 13px;
        color: #adb5bd;
        text-align: center;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row align-items-center mb-3">
        <div class="col-md-6">
            <h5 class="mb-0">Add New Role</h5>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('admin.role.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to Roles
            </a>
        </div>
    </div>

    <div class="card border-0 rounded-4">
        <div class="card-body p-4">
            <form action="{{ route('admin.role.store') }}" method="POST">
                @csrf
                <h6 class="mb-3 fw-semibold">
                    <i class="bi bi-shield-check me-2"></i>Role Information
                </h6>
                <div class="row g-3 mb-4">
                    {{-- <div class="col-md-4">
                        <label for="name" class="form-label">Role Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required maxlength="255" placeholder="Enter role name">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div> --}}
                    <div class="col-md-6">
                        <label for="level_id" class="form-label">
                            Role <span class="text-danger">*</span>
                        </label>

                        <select name="level_id" id="level_id"
                            class="form-select @error('level_id') is-invalid @enderror" required>

                            <option value="" hidden {{ old('level_id') ? '' : 'selected' }}>— Select Role Level —</option>

                            @foreach($levels as $level)
                            <option value="{{ $level->id }}"
                                {{ old('level_id') == $level->id ? 'selected' : '' }}>
                                {{ $level->name }}
                            </option>
                            @endforeach

                        </select>

                        <small class="text-muted">Select role level from available levels</small>

                        @error('level_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="slug" class="form-label">Slug</label>
                        <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug') }}" maxlength="255" placeholder="Auto-generated if empty">
                        @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3" maxlength="500" placeholder="Enter role description">{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="organization_id" class="form-label">Organization</label>
                        <select name="organization_id" id="organization_id" class="form-select @error('organization_id') is-invalid @enderror">
                            <option value="" hidden {{ old('organization_id') ? '' : 'selected' }}>Select Organization</option>
                            @foreach($organizations ?? [] as $organization)
                            <option value="{{ $organization->id }}" {{ old('organization_id') == $organization->id ? 'selected' : '' }}>
                                {{ $organization->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('organization_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="sbu_id" class="form-label">SBU</label>
                        <div id="sbu-hidden-inputs"></div>
                        <div class="sbu-input-box @error('sbu_ids') is-invalid @enderror @error('sbu_ids.*') is-invalid @enderror" id="sbu-box" onclick="sbuBoxClick(event)">
                            <div id="sbu-chips" style="display:contents"></div>
                            <span class="sbu-ph" id="sbu-ph">Select SBU...</span>
                            <svg class="sbu-chevron" id="sbu-chevron" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div class="sbu-dropdown" id="sbu-dd" style="display:none">
                            <div class="sbu-search-row">
                                <input id="sbu-search" placeholder="Search SBU..." oninput="sbuRenderList()" onclick="event.stopPropagation()" autocomplete="off">
                            </div>
                            <div class="sbu-opt-list" id="sbu-list"></div>
                        </div>
                        @error('sbu_ids')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        @error('sbu_ids.*')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="parent_role_id" class="form-label">Parent Role</label>
                        <select name="parent_role_id" id="parent_role_id" class="form-select @error('parent_role_id') is-invalid @enderror">
                            <option value="" hidden {{ old('parent_role_id') ? '' : 'selected' }}>Select Parent Role</option>
                            @foreach($parentRoles ?? [] as $parentRole)
                            <option value="{{ $parentRole->id }}" {{ old('parent_role_id') == $parentRole->id ? 'selected' : '' }}>
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
                            <option value="1" {{ old('is_active', true) ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('is_active') === false || old('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('is_active')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <hr class="my-4">
                <div class="d-flex align-items-center justify-content-between mb-3" id="modulePermissionsSection">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-grid me-2"></i>Module Permissions
                    </h6>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="selectAllModules">
                        <label class="form-check-label" for="selectAllModules">Select All Modules</label>
                    </div>
                </div>
                @foreach($moduleCategories ?? [] as $category)
                <div class="mb-4">
                    <h6 class="text-muted small mb-2">{{ $category->category_name ?? 'Uncategorized' }} <span class="fw-normal">({{ $category->modules->count() }} modules)</span></h6>
                    <div class="row g-2">
                        @foreach($category->modules as $module)
                        <div class="col-md-4 col-lg-3">
                            <div class="form-check">
                                <input class="form-check-input module-privilege-cb" type="checkbox" name="module_ids[]" value="{{ $module->id }}" id="module_{{ $module->id }}" {{ in_array($module->id, old('module_ids', [])) ? 'checked' : '' }}>
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
                    <button type="submit" class="btn btn-primary bg-main border-0">Create Role</button>
                    <a href="{{ route('admin.role.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const organizationSelect = document.getElementById('organization_id');
        const levelSelect = document.getElementById('level_id');
        const parentRoleSelect = document.getElementById('parent_role_id');
        const selectAllModules = document.getElementById('selectAllModules');
        const sbuHiddenInputs = document.getElementById('sbu-hidden-inputs');
        const sbuBox = document.getElementById('sbu-box');
        const sbuChips = document.getElementById('sbu-chips');
        const sbuPh = document.getElementById('sbu-ph');
        const sbuDd = document.getElementById('sbu-dd');
        const sbuSearch = document.getElementById('sbu-search');
        const sbuList = document.getElementById('sbu-list');
        let sbuAll = [];
        let sbuSelected = [];
        let parentRolesLoadToken = 0;

        function resetSelect(select, placeholder) {
            if (!select) return;
            select.innerHTML = `<option value="" hidden selected>${placeholder}</option>`;
        }

        function resetSbuSelect() {
            sbuAll = [];
            sbuSelected = [];
            renderSbuHiddenInputs();
            renderSbuChips();
            sbuRenderList();
        }

        function getSelectedSbuIds() {
            return sbuSelected.slice();
        }

        function renderSbuHiddenInputs() {
            if (!sbuHiddenInputs) return;
            sbuHiddenInputs.innerHTML = sbuSelected
                .map(function(id) { return `<input type="hidden" name="sbu_ids[]" value="${id}">`; })
                .join('');
        }

        function renderSbuChips() {
            if (!sbuChips) return;
            if (!sbuSelected.length) {
                sbuChips.innerHTML = '';
                if (sbuPh) sbuPh.style.display = '';
                return;
            }
            if (sbuPh) sbuPh.style.display = 'none';
            sbuChips.innerHTML = sbuSelected.map(function(id) {
                const row = sbuAll.find(function(x) { return String(x.id) === String(id); });
                const name = row ? row.name : id;
                return `<span class="sbu-chip">${name}<span class="sbu-chip-x" onclick="sbuRemoveId('${id}', event)">×</span></span>`;
            }).join('');
        }

        window.sbuRenderList = function sbuRenderList() {
            if (!sbuList) return;
            const q = (sbuSearch?.value || '').toLowerCase().trim();
            const filtered = sbuAll.filter(function(item) {
                return !q || String(item.name || '').toLowerCase().includes(q);
            });
            if (!filtered.length) {
                sbuList.innerHTML = '<div class="sbu-no-result">No SBU found</div>';
                return;
            }
            sbuList.innerHTML = filtered.map(function(item) {
                const picked = sbuSelected.includes(String(item.id));
                return `<div class="sbu-opt ${picked ? 'picked' : ''}" onclick="sbuToggleId('${item.id}')">
                            <span class="sbu-opt-cb">
                                <svg class="sbu-opt-ck" viewBox="0 0 16 16" fill="none">
                                    <path d="M3.5 8.2l3 3L12.5 5" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span class="sbu-opt-name">${item.name}</span>
                        </div>`;
            }).join('');
        };

        function syncSbuAndParentRoles() {
            renderSbuHiddenInputs();
            renderSbuChips();
            sbuRenderList();
            loadParentRoles(organizationSelect?.value || '', getSelectedSbuIds());
        }

        window.sbuToggleId = function sbuToggleId(id) {
            id = String(id);
            if (sbuSelected.includes(id)) {
                sbuSelected = sbuSelected.filter(function(v) { return v !== id; });
            } else {
                sbuSelected.push(id);
            }
            syncSbuAndParentRoles();
        };

        window.sbuRemoveId = function sbuRemoveId(id, e) {
            if (e) e.stopPropagation();
            id = String(id);
            sbuSelected = sbuSelected.filter(function(v) { return v !== id; });
            syncSbuAndParentRoles();
        };

        window.sbuBoxClick = function sbuBoxClick(e) {
            if (e) e.stopPropagation();
            if (!sbuAll.length) return;
            const isOpen = sbuDd && sbuDd.style.display !== 'none';
            if (!isOpen) {
                if (sbuDd) sbuDd.style.display = '';
                if (sbuBox) sbuBox.classList.add('open');
                if (sbuSearch) setTimeout(function() { sbuSearch.focus(); }, 0);
            } else {
                if (sbuDd) sbuDd.style.display = 'none';
                if (sbuBox) sbuBox.classList.remove('open');
            }
        };

        document.addEventListener('click', function(evt) {
            if (!sbuDd || !sbuBox) return;
            if (!sbuDd.contains(evt.target) && !sbuBox.contains(evt.target)) {
                sbuDd.style.display = 'none';
                sbuBox.classList.remove('open');
            }
        });

        if (sbuSearch) {
            sbuSearch.addEventListener('keydown', function(e) {
                e.stopPropagation();
            });
        }

        function loadSbus(organizationId, selectedSbuIds = []) {
            if (!organizationId) return;

            fetch(`{{ route('admin.role.departmentsByOrganization') }}?organization_id=${organizationId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && Array.isArray(data.sbus) && data.sbus.length > 0) {
                        sbuAll = data.sbus.map(function(sbu) {
                            return { id: String(sbu.id), name: sbu.name };
                        });
                    } else {
                        sbuAll = [];
                    }
                    const allowed = new Set(sbuAll.map(function(item) { return item.id; }));
                    sbuSelected = (selectedSbuIds || [])
                        .map(function(v) { return String(v); })
                        .filter(function(v) { return allowed.has(v); });
                    syncSbuAndParentRoles();
                })
                .catch(error => console.error('Error loading SBUs:', error));
        }

        function loadParentRoles(organizationId, sbuIds = [], selectedParentRoleId = '') {
            resetSelect(parentRoleSelect, 'Select Parent Role');

            if (!organizationId) return;

            let url = `{{ route('admin.role.parentRoles') }}?organization_id=${organizationId}`;
            const levelId = levelSelect?.value || '';
            if (!levelId) return;
            url += `&level_id=${encodeURIComponent(levelId)}`;

            if (sbuIds.length > 0) {
                url += `&sbu_ids=${encodeURIComponent(sbuIds.join(','))}`;
            }

            const token = ++parentRolesLoadToken;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (token !== parentRolesLoadToken) {
                        return;
                    }
                    if (data.success && Array.isArray(data.roles)) {
                        const seen = new Set();
                        data.roles.forEach(role => {
                            const idStr = String(role.id);
                            if (seen.has(idStr)) {
                                return;
                            }
                            seen.add(idStr);
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

            resetSbuSelect();
            resetSelect(parentRoleSelect, 'Select Parent Role');

            if (organizationId) {
                loadSbus(organizationId);
                loadParentRoles(organizationId);
            }
        });

        levelSelect?.addEventListener('change', function() {
            const organizationId = organizationSelect?.value || '';
            loadParentRoles(organizationId, getSelectedSbuIds());
        });

        selectAllModules?.addEventListener('change', function() {
            document.querySelectorAll('.module-privilege-cb').forEach(function(cb) {
                cb.checked = selectAllModules.checked;
            });
        });

        const oldOrganizationId = @json(old('organization_id'));
        const oldSbuIds = @json(old('sbu_ids', []));
        const oldParentRoleId = @json(old('parent_role_id'));

        if (oldOrganizationId) {
            loadSbus(oldOrganizationId, oldSbuIds.map(String));

            if (oldSbuIds.length > 0) {
                loadParentRoles(oldOrganizationId, oldSbuIds.map(String), oldParentRoleId);
            } else {
                loadParentRoles(oldOrganizationId, [], oldParentRoleId);
            }
        } else {
            resetSbuSelect();
        }

        syncSbuAndParentRoles();
    });
</script>
@push('scripts')
@endpush
@endsection
