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

            $('#editOrganizationId').on('change', function() {
                updateSbuDropdown($(this).val());
            });

            $('#editSbuId').on('change', function() {
                updateParentDepartmentDropdown($(this).val());
            });

            function updateSbuDropdown(orgId, selectedSbuId = null) {
                var sbuSelect = $('#editSbuId');
                sbuSelect.empty().append('<option value="">Please select Organization first...</option>');
                
                if (orgId) {
                    sbuSelect.empty().append('<option value="">Select SBU</option>');
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
                
                // When Organization changes, reset Parent Department dropdown
                updateParentDepartmentDropdown('');
            }

            function updateParentDepartmentDropdown(sbuId, selectedParentId = null) {
                var parentSelect = $('#editParentDepartmentId');
                var currentDeptId = $('#editDepartmentId').val(); // Don't show current dept as its own parent

                parentSelect.empty().append('<option value="">Please select SBU first...</option>');
                
                if (sbuId) {
                    parentSelect.empty().append('<option value="">None</option>');
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
                        orgSelect.empty().append('<option value="">Select Organization</option>');
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
                        $('#editDepartmentIsActive').prop('checked', true);
                        
                        $('.invalid-feedback').text('').hide();
                        $('.form-select, .form-control').removeClass('is-invalid');
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
                        $('#editDepartmentIsActive').prop('checked', response.department.is_active);
                        
                        var orgSelect = $('#editOrganizationId');
                        orgSelect.empty().append('<option value="">Select Organization</option>');
                        response.organizations.forEach(function(org) {
                            var selected = org.id == response.department.organization_id ? 'selected' : '';
                            orgSelect.append('<option value="' + org.id + '" ' + selected + '>' + org.name + '</option>');
                        });
                        
                        allSbus = response.sbus;
                        allParentDepartments = response.parentDepartments;
                        
                        updateSbuDropdown(response.department.organization_id, response.department.sbu_id);
                        updateParentDepartmentDropdown(response.department.sbu_id, response.department.parent_department_id);
                        
                        $('.invalid-feedback').text('').hide();
                        $('.form-select, .form-control').removeClass('is-invalid');
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
                    is_active: $('#editDepartmentIsActive').is(':checked') ? 1 : 0
                };
                
                $('.invalid-feedback').text('').hide();
                $('.form-select, .form-control').removeClass('is-invalid');
                
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
                            let errorMessage = '<div class="text-start mt-2"><ul class="mb-0">';
                            Object.values(errors).flat().forEach(err => {
                                errorMessage += `<li>${err}</li>`;
                            });
                            errorMessage += '</ul></div>';

                            Swal.fire({
                                icon: 'warning',
                                title: 'Please check the following:',
                                html: errorMessage,
                                confirmButtonColor: '#1a237e',
                                confirmButtonText: 'Dismiss'
                            });

                            // Also highlight fields and show error text
                            $.each(errors, function(field, messages) {
                                var fieldMap = {
                                    'organization_id': 'editOrganizationId',
                                    'sbu_id': 'editSbuId',
                                    'name': 'editDepartmentName',
                                    'code': 'editDepartmentCode',
                                    'parent_department_id': 'editParentDepartmentId',
                                    'description': 'editDepartmentDescription',
                                    'is_active': 'editDepartmentIsActive'
                                };
                                var fieldId = '#' + (fieldMap[field] || 'edit' + field);
                                var errorId = fieldId + 'Error';
                                $(fieldId).addClass('is-invalid');
                                $(errorId).text(messages[0]).show();
                            });
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
        });
    </script>
@endpush
