@extends('layouts.app')

@section('title', 'Departments - Admin Panel')

@section('page-title', 'Departments')

@push('styles')
    <!-- Departments Module CSS -->
    <link href="{{ asset('css/departments.css') }}" rel="stylesheet">

    <style>
        .btn {
            font-size: 13px;
        }

        .input-group {
            border: 1px solid var(--main-color) !important;
        }

        input:focus {
            box-shadow: none !important;
            border: 1px solid var(--main-color) !important;
        }

        .card .badge {
            font-weight: 500 !important;
            padding: .3rem .8rem !important;
            border-radius: 4px !important;
        }

        .table {
            --bs-table-bg: transparent !important;
        }

        th {
            padding: 1.3rem 2rem !important;
            color: var(--light-color) !important;
            white-space: nowrap !important;
        }

        td {
            padding: 1rem 2rem !important;
        }

        .dt-buttons {
            margin-top: 2px;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Top Header with Actions -->
        <div class="row align-items-center mb-3">
            <div class="col-md-6">
                <h5 class="mb-0">Department Management</h5>
            </div>

            <div class="col-md-6 text-end">
                <button type="button" class="btn btn-primary bg-main border-0" data-bs-toggle="offcanvas" data-bs-target="#departmentEditCanvas" id="addDepartmentBtn">
                    <i class="bi bi-building-add me-1"></i>Add New Department
                </button>
            </div>
        </div>


        <!-- Summary Metrics Row -->
        @include('admin.departments.counters')

        <!-- Main Content Area with Sidebar Filter -->
        @include('admin.departments.departments_cards')
    </div>

    <!-- Department Detail Side Canvas -->
    @include('admin.departments.detail_canvas')
    
    <!-- Department Edit Side Canvas -->
    @include('admin.departments.edit_canvas')
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            var departmentEditUrl = '{{ route("admin.department.edit", ":id") }}';
            var departmentUpdateUrl = '{{ route("admin.department.update", ":id") }}';
            var departmentStoreUrl = '{{ route("admin.department.store") }}';
            var departmentCreateUrl = '{{ route("admin.department.add") }}';
            
            var departmentCanvas = document.getElementById('departmentDetailCanvas');
            if (departmentCanvas) {
                departmentCanvas.addEventListener('show.bs.offcanvas', function(event) {
                    var button = event.relatedTarget;
                    if (button && button.classList.contains('view-department-btn')) {
                        var get = function(attr, fallback) {
                            var v = button.getAttribute(attr);
                            return (v !== null && v !== '') ? v : (fallback || '—');
                        };
                        document.getElementById('canvasDeptName').textContent = get('data-department-name');
                        document.getElementById('canvasDeptCode').textContent = 'Code: ' + get('data-department-code');
                        document.getElementById('canvasDeptOrganization').textContent = get('data-organization-name');
                        document.getElementById('canvasDeptSbu').textContent = get('data-sbu-name');
                        document.getElementById('canvasDeptParent').textContent = get('data-parent-name');
                        document.getElementById('canvasDeptDescription').textContent = get('data-description');
                        document.getElementById('canvasDeptStatus').textContent = get('data-department-status');
                    }
                });
            }
            
            var editCanvas = document.getElementById('departmentEditCanvas');
            if (editCanvas) {
                editCanvas.addEventListener('show.bs.offcanvas', function(event) {
                    var button = event.relatedTarget;
                    if (button && button.id === 'addDepartmentBtn') {
                        loadDepartmentForAdd();
                    } else if (button && button.classList.contains('edit-department-btn')) {
                        var departmentId = button.getAttribute('data-department-id');
                        if (departmentId) {
                            loadDepartmentForEdit(departmentId);
                        }
                    }
                });
            }
            
            var allSbus = [];
            var allParentDepartments = [];
            var DEPT_LIMITED_FIELDS = [
                { fieldName: 'name', inputId: 'editDepartmentName', errorId: 'editDepartmentNameError', lenId: 'editDepartmentNameLen', metaId: 'editDepartmentNameMeta', max: 50 },
                { fieldName: 'code', inputId: 'editDepartmentCode', errorId: 'editDepartmentCodeError', lenId: 'editDepartmentCodeLen', metaId: 'editDepartmentCodeMeta', max: 10 },
                { fieldName: 'description', inputId: 'editDepartmentDescription', errorId: 'editDepartmentDescriptionError', lenId: 'editDepartmentDescriptionLen', metaId: 'editDepartmentDescriptionMeta', max: 255 }
            ];

            function clearDepartmentValidationErrors() {
                $('#editDepartmentForm .invalid-feedback').text('').hide().removeAttr('data-max-reached');
                $('#editDepartmentForm .form-select, #editDepartmentForm .form-control').removeClass('is-invalid');
            }

            function syncDepartmentLimitedFieldsState() {
                DEPT_LIMITED_FIELDS.forEach(function(cfg) {
                    var el = document.getElementById(cfg.inputId);
                    if (!el) return;
                    var max = cfg.max;
                    if (el.value.length > max) {
                        el.value = el.value.substring(0, max);
                    }
                    var len = el.value.length;
                    var lenEl = document.getElementById(cfg.lenId);
                    var metaEl = document.getElementById(cfg.metaId);
                    var errorEl = document.getElementById(cfg.errorId);
                    if (lenEl) lenEl.textContent = String(len);
                    if (metaEl) metaEl.classList.toggle('text-danger', len >= max);
                    if (errorEl && errorEl.dataset.maxReached === '1') {
                        errorEl.textContent = '';
                        errorEl.style.display = 'none';
                        errorEl.removeAttribute('data-max-reached');
                    }
                    if (len === max && errorEl && (!errorEl.textContent || errorEl.dataset.maxReached === '1')) {
                        el.classList.add('is-invalid');
                        errorEl.textContent = 'You cannot enter more than ' + max + ' characters.';
                        errorEl.style.display = 'block';
                        errorEl.dataset.maxReached = '1';
                    } else if (errorEl && errorEl.dataset.maxReached !== '1') {
                        if (!errorEl.textContent) {
                            el.classList.remove('is-invalid');
                        }
                    } else if (len < max) {
                        el.classList.remove('is-invalid');
                    }
                });
            }
            
            function getSelectedSbuData() {
                var sbuId = $('#editSbuId').val();
                if (!sbuId) return null;
                return allSbus.find(function(sbu) {
                    return String(sbu.id) === String(sbuId);
                }) || null;
            }

            function getScheduleFromSbu(sbuData) {
                if (!sbuData) {
                    return {
                        workingDays: [],
                        workingStartTime: '',
                        workingEndTime: '',
                        gracePeriod: ''
                    };
                }
                var g = sbuData.opening_grace_period != null && sbuData.opening_grace_period !== ''
                    ? sbuData.opening_grace_period
                    : sbuData.closing_grace_period;
                return {
                    workingDays: Array.isArray(sbuData.working_days) ? sbuData.working_days : [],
                    workingStartTime: (sbuData.working_start_time || '').toString().slice(0, 5),
                    workingEndTime: (sbuData.working_end_time || '').toString().slice(0, 5),
                    gracePeriod: (g != null ? g : '').toString()
                };
            }

            function applySbuScheduleToDepartment() {
                var sbuData = getSelectedSbuData();
                var schedule = getScheduleFromSbu(sbuData);
                $('.dept-working-day').each(function() {
                    this.checked = schedule.workingDays.includes(this.value);
                });
                $('#editWorkingStartTime').val(schedule.workingStartTime);
                $('#editWorkingEndTime').val(schedule.workingEndTime);
                $('#editGracePeriod').val(schedule.gracePeriod);
            }

            function schedulesMatchSbu(workingDays, startTime, endTime, gracePeriod) {
                var sbuData = getSelectedSbuData();
                var schedule = getScheduleFromSbu(sbuData);
                var current = (workingDays || []).slice().sort().join(',');
                var sbu = (schedule.workingDays || []).slice().sort().join(',');
                return current === sbu
                    && (startTime || '') === (schedule.workingStartTime || '')
                    && (endTime || '') === (schedule.workingEndTime || '')
                    && (gracePeriod || '') === (schedule.gracePeriod || '');
            }

            function toggleDepartmentScheduleMode() {
                var hasSbu = ($('#editSbuId').val() || '') !== '';
                if (!hasSbu) {
                    $('#deptScheduleModeSection').addClass('d-none');
                    $('#deptWorkingScheduleFields').removeClass('pe-none opacity-50');
                    return;
                }
                $('#deptScheduleModeSection').removeClass('d-none');
                if ($('#deptScheduleModeStandard').is(':checked')) {
                    applySbuScheduleToDepartment();
                    $('#deptWorkingScheduleFields').addClass('pe-none opacity-50');
                } else {
                    $('#deptWorkingScheduleFields').removeClass('pe-none opacity-50');
                }
            }

            $('#editOrganizationId').on('change', function() {
                updateSbuDropdown($(this).val());
                toggleDepartmentScheduleMode();
            });

            $('#editSbuId').on('change', function() {
                updateParentDepartmentDropdown($(this).val());
                if ($(this).val()) {
                    $('#deptScheduleModeStandard').prop('checked', true);
                } else {
                    $('#deptScheduleModeCustom').prop('checked', true);
                }
                toggleDepartmentScheduleMode();
            });

            $('#deptScheduleModeStandard, #deptScheduleModeCustom').on('change', function() {
                toggleDepartmentScheduleMode();
            });

            function updateSbuDropdown(orgId, selectedSbuId = null) {
                var sbuSelect = $('#editSbuId');
                sbuSelect.empty().append('<option value="" hidden selected>Please select Organization first...</option>');
                
                if (orgId) {
                    sbuSelect.empty().append('<option value="" hidden selected>Select SBU</option>');
                    var filteredSbus = allSbus.filter(function(sbu) {
                        return sbu.organization_id == orgId;
                    });
                    
                    filteredSbus.forEach(function(sbu) {
                        var isSelected = (selectedSbuId != null && String(sbu.id) === String(selectedSbuId));
                        var selectedAttr = isSelected ? 'selected="selected"' : '';
                        sbuSelect.append('<option value="' + sbu.id + '" ' + selectedAttr + '>' + sbu.name + '</option>');
                    });
                    sbuSelect.prop('disabled', false).attr('title', '');
                    if (selectedSbuId != null) {
                        sbuSelect.val(selectedSbuId);
                    }
                } else {
                    sbuSelect.prop('disabled', true);
                }
                
                updateParentDepartmentDropdown('');
            }

            function updateParentDepartmentDropdown(sbuId, selectedParentId = null) {
                var parentSelect = $('#editParentDepartmentId');
                var currentDeptId = $('#editDepartmentId').val(); // Don't show current dept as its own parent

                parentSelect.empty().append('<option value="" hidden selected>Please select SBU first...</option>');
                
                if (sbuId) {
                    parentSelect.empty().append('<option value="" hidden selected>None</option>');
                    var filteredDepts = allParentDepartments.filter(function(dept) {
                        return dept.sbu_id == sbuId && dept.id != currentDeptId;
                    });
                    
                    filteredDepts.forEach(function(dept) {
                        var isSelected = (selectedParentId != null && String(dept.id) === String(selectedParentId));
                        var selectedAttr = isSelected ? 'selected="selected"' : '';
                        parentSelect.append('<option value="' + dept.id + '" ' + selectedAttr + '>' + dept.name + '</option>');
                    });
                    parentSelect.prop('disabled', false);
                } else {
                    parentSelect.prop('disabled', true);
                }
            }
            
            function loadDepartmentForAdd() {
                $('#editFormMode').val('add');
                $('#editDepartmentId').val('');
                $('#canvasTitleText').text('Add New Department');
                $('#canvasEditIcon').removeClass('bi-pencil').addClass('bi-building-add');
                $('#submitBtnText').text('Create Department');
                
                $.ajax({
                    url: departmentCreateUrl,
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        var orgSelect = $('#editOrganizationId');
                        orgSelect.empty().append('<option value="" hidden selected>Select Organization</option>');
                        response.organizations.forEach(function(org) {
                            orgSelect.append('<option value="' + org.id + '">' + org.name + '</option>');
                        });
                        
                        allSbus = response.sbus;
                        allParentDepartments = response.parentDepartments;
                        
                        updateSbuDropdown('');
                        updateParentDepartmentDropdown('');
                        
                        $('#editDepartmentName').val('');
                        $('#editDepartmentCode').val('');
                        $('#editDepartmentDescription').val('');
                        $('.dept-working-day').prop('checked', false);
                        $('#editWorkingStartTime').val('');
                        $('#editWorkingEndTime').val('');
                        $('#editGracePeriod').val('');
                        $('#deptScheduleModeStandard').prop('checked', true);
                        $('#editDepartmentIsActive').prop('checked', true);
                        
                        clearDepartmentValidationErrors();
                        syncDepartmentLimitedFieldsState();
                        toggleDepartmentScheduleMode();
                    },
                    error: function(xhr) {
                        console.error('Error loading data for add:', xhr);
                        alert('Failed to load form data. Please try again.');
                    }
                });
            }
            
            function loadDepartmentForEdit(departmentId) {
                $('#editFormMode').val('edit');
                $('#canvasTitleText').text('Edit Department');
                $('#canvasEditIcon').removeClass('bi-building-add').addClass('bi-pencil');
                $('#submitBtnText').text('Update Department');
                
                var url = departmentEditUrl.replace(':id', departmentId);
                $.ajax({
                    url: url,
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        $('#editDepartmentId').val(response.department.id);
                        $('#editDepartmentName').val(response.department.name);
                        $('#editDepartmentCode').val(response.department.code || '');
                        $('#editDepartmentDescription').val(response.department.description || '');
                        var deptWorkingDays = Array.isArray(response.department.working_days) ? response.department.working_days : [];
                        $('.dept-working-day').each(function() {
                            this.checked = deptWorkingDays.includes(this.value);
                        });
                        var deptStartTime = (response.department.working_start_time || '').toString().slice(0, 5);
                        var deptEndTime = (response.department.working_end_time || '').toString().slice(0, 5);
                        $('#editWorkingStartTime').val(deptStartTime);
                        $('#editWorkingEndTime').val(deptEndTime);
                        var deptGracePeriod = (response.department.opening_grace_period != null && response.department.opening_grace_period !== ''
                            ? response.department.opening_grace_period
                            : response.department.closing_grace_period);
                        deptGracePeriod = (deptGracePeriod != null ? deptGracePeriod : '').toString();
                        $('#editGracePeriod').val(deptGracePeriod);
                        $('#editDepartmentIsActive').prop('checked', response.department.is_active);
                        
                        var orgSelect = $('#editOrganizationId');
                        orgSelect.empty().append('<option value="" hidden selected>Select Organization</option>');
                        response.organizations.forEach(function(org) {
                            var selected = org.id == response.department.organization_id ? 'selected' : '';
                            orgSelect.append('<option value="' + org.id + '" ' + selected + '>' + org.name + '</option>');
                        });
                        
                        allSbus = response.sbus;
                        allParentDepartments = response.parentDepartments;
                        
                        updateSbuDropdown(response.department.organization_id, response.department.sbu_id);
                        updateParentDepartmentDropdown(response.department.sbu_id, response.department.parent_department_id);
                        if (response.department.sbu_id) {
                            if (schedulesMatchSbu(deptWorkingDays, deptStartTime, deptEndTime, deptGracePeriod)) {
                                $('#deptScheduleModeStandard').prop('checked', true);
                            } else {
                                $('#deptScheduleModeCustom').prop('checked', true);
                            }
                        } else {
                            $('#deptScheduleModeCustom').prop('checked', true);
                        }
                        toggleDepartmentScheduleMode();
                        
                        clearDepartmentValidationErrors();
                        syncDepartmentLimitedFieldsState();
                    },
                    error: function(xhr) {
                        console.error('Error loading department:', xhr);
                        alert('Failed to load department data. Please try again.');
                    }
                });
            }
            
            $('#editDepartmentForm').on('submit', function(e) {
                e.preventDefault();
                
                var formMode = $('#editFormMode').val();
                var formData = {
                    id: $('#editDepartmentId').val(),
                    organization_id: $('#editOrganizationId').val(),
                    sbu_id: $('#editSbuId').val(),
                    name: $('#editDepartmentName').val(),
                    code: $('#editDepartmentCode').val(),
                    parent_department_id: $('#editParentDepartmentId').val() || null,
                    description: $('#editDepartmentDescription').val(),
                    working_days: $('.dept-working-day:checked').map(function() { return this.value; }).get(),
                    working_start_time: $('#editWorkingStartTime').val() || null,
                    working_end_time: $('#editWorkingEndTime').val() || null,
                    grace_period: $('#editGracePeriod').val() === '' ? null : $('#editGracePeriod').val(),
                    is_active: $('#editDepartmentIsActive').is(':checked') ? 1 : 0
                };
                
                clearDepartmentValidationErrors();
                
                var url, method;
                if (formMode === 'add') {
                    url = departmentStoreUrl;
                    method = 'POST';
                } else {
                    var departmentId = $('#editDepartmentId').val();
                    url = departmentUpdateUrl.replace(':id', departmentId);
                    method = 'POST';
                }
                
                $.ajax({
                    url: url,
                    method: method,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    data: JSON.stringify(formData),
                    success: function(response) {
                        if (response.success) {
                            var offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('departmentEditCanvas'));
                            if (offcanvas) {
                                offcanvas.hide();
                            }
                            showSuccess(response.message || 'Department saved successfully.').then(() => {
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            var firstInvalid = null;
                            $.each(errors, function(field, messages) {
                                var fieldMap = {
                                    'organization_id': 'editOrganizationId',
                                    'sbu_id': 'editSbuId',
                                    'name': 'editDepartmentName',
                                    'code': 'editDepartmentCode',
                                    'parent_department_id': 'editParentDepartmentId',
                                    'description': 'editDepartmentDescription',
                                    'working_start_time': 'editWorkingStartTime',
                                    'working_end_time': 'editWorkingEndTime',
                                    'grace_period': 'editGracePeriod',
                                    'opening_grace_period': 'editGracePeriod',
                                    'closing_grace_period': 'editGracePeriod',
                                    'is_active': 'editDepartmentIsActive'
                                };
                                if (field === 'working_days' || field.indexOf('working_days.') === 0) {
                                    $('#editWorkingDaysError').text(messages[0]).show();
                                    if (!firstInvalid) {
                                        firstInvalid = $('.dept-working-day').first();
                                    }
                                    return;
                                }
                                var fieldId = '#' + (fieldMap[field] || 'edit' + field);
                                var errorId = fieldId + 'Error';
                                $(fieldId).addClass('is-invalid');
                                $(errorId).text(messages[0]).show();
                                if (!firstInvalid) {
                                    firstInvalid = $(fieldId).first();
                                }
                            });
                            syncDepartmentLimitedFieldsState();
                            if (firstInvalid && firstInvalid.length) {
                                firstInvalid.trigger('focus');
                            }
                        } else {
                            showError((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Something went wrong. Please try again.', 'System Error');
                        }
                    }
                });
            });

            $(document).on('click', '.delete-department-btn', function(e) {
                e.preventDefault();
                var button = this;
                var deleteUrl = $(button).data('delete-url');

                if (!deleteUrl) {
                    showError('Delete URL not found.');
                    return;
                }

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This department will be permanently deleted!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (!result.isConfirmed) {
                        return;
                    }

                    $.ajax({
                        url: deleteUrl,
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: csrfToken
                        },
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        beforeSend: function() {
                            $(button).prop('disabled', true).html('<i class="bi bi-trash"></i>...');
                        },
                        success: function(response) {
                            if (response.success) {
                                showSuccess(response.message || 'Department deleted successfully.', 'Deleted').then(() => {
                                    location.reload();
                                });
                            } else {
                                showError(response.message || 'Failed to delete department.');
                            }
                        },
                        error: function(xhr) {
                            let msg = 'Failed to delete department.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            showError(msg);
                        },
                        complete: function() {
                            $(button).prop('disabled', false).html('<i class="bi bi-trash"></i>');
                        }
                    });
                });
            });
            
            var filterStatus = document.querySelectorAll('.filter-status');
            filterStatus.forEach(function(radio) {
                radio.addEventListener('change', function() {
                    var status = document.querySelector('input[name="filterStatus"]:checked').value;
                    document.querySelectorAll('#departmentsGrid .col-md-6').forEach(function(col) {
                        var card = col.querySelector('.department-card');
                        var cardStatus = card ? card.getAttribute('data-department-status') : '';
                        col.style.display = (status === 'all' || cardStatus === status) ? '' : 'none';
                    });
                });
            });
            var clearBtn = document.getElementById('clearFiltersBtn');
            if (clearBtn) {
                clearBtn.addEventListener('click', function() {
                    var all = document.getElementById('filterStatusAll');
                    if (all) all.checked = true;
                    document.querySelectorAll('#departmentsGrid .col-md-6').forEach(function(col) {
                        col.style.display = '';
                    });
                });
            }
            var editDepartmentForm = document.getElementById('editDepartmentForm');
            if (editDepartmentForm) {
                editDepartmentForm.querySelectorAll('input, select, textarea').forEach(function(el) {
                    el.addEventListener('input', syncDepartmentLimitedFieldsState);
                    el.addEventListener('change', syncDepartmentLimitedFieldsState);
                });
            }
            toggleDepartmentScheduleMode();
            syncDepartmentLimitedFieldsState();
        });
    </script>
@endpush
