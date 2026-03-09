@extends('layouts.app')

@section('title', 'Leave Types - Admin Panel')

@section('page-title', 'Leave Types')

@push('styles')
    <link href="{{ asset('css/users.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="container-fluid">
        <div class="card border-0 rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="row align-items-center mb-3">
                    <div class="col-md-6">
                        <h5 class="mb-0">Manage Leave Types</h5>
                    </div>
                    <div class="col-md-6 text-end">
                        <button type="button" class="btn btn-primary bg-main border-0" data-bs-toggle="offcanvas" data-bs-target="#leaveTypeEditCanvas" id="addLeaveTypeBtn">
                            <i class="bi bi-plus-circle me-1"></i>Add New Leave Type
                        </button>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="row g-3 px-1 pb-3">
                    <div class="col-md-4">
                        <div class="card bg-main border-0 rounded-3 shadow h-100">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="opacity-75 text-white mb-1 small fw-normal text-uppercase">
                                            <i class="bi bi-card-checklist me-1"></i>Total Leave Types
                                        </h6>
                                        <div class="h4 mb-0 fw-bold text-white">{{ $total ?? 0 }}</div>
                                    </div>
                                    <div class="text-white opacity-25">
                                        <i class="bi bi-card-checklist fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 rounded-3 shadow h-100">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="text-muted mb-1 small fw-normal text-uppercase">
                                            <i class="bi bi-check-circle me-1"></i>Active
                                        </h6>
                                        <div class="h4 mb-0 fw-bold text-success">{{ $active ?? 0 }}</div>
                                    </div>
                                    <div class="text-success opacity-25">
                                        <i class="bi bi-check-circle fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 rounded-3 shadow h-100">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="text-muted mb-1 small fw-normal text-uppercase">
                                            <i class="bi bi-x-circle me-1"></i>Inactive
                                        </h6>
                                        <div class="h4 mb-0 fw-bold text-secondary">{{ $inactive ?? 0 }}</div>
                                    </div>
                                    <div class="text-secondary opacity-25">
                                        <i class="bi bi-x-circle fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive mt-3">
                    <table class="table table-striped align-middle mb-0">
                        <thead class="bg-main text-white">
                            <tr>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Organization</th>
                                <th>Department</th>
                                <th>Annual Quota</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leaveTypes ?? [] as $lt)
                                <tr>
                                    <td>{{ $lt->name }}</td>
                                    <td>
                                        @if($lt->code)
                                            <span class="badge px-3 rounded-1 bg-light text-dark">{{ $lt->code }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($lt->organization)
                                            <span class="badge px-3 rounded-1 bg-primary">{{ $lt->organization->name }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($lt->department)
                                            <span class="badge px-3 rounded-1 bg-info">{{ $lt->department->name }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format((float) $lt->annual_quota, 2) }}</td>
                                    <td>
                                        @if($lt->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-primary edit-leave-type-btn" data-bs-toggle="offcanvas" data-bs-target="#leaveTypeEditCanvas" data-leave-type-id="{{ $lt->id }}">
                                            <i class="bi bi-pencil me-1"></i>Edit
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No leave types found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Leave Type Edit Side Canvas -->
    @include('admin.leave-type.edit_canvas')
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            var leaveTypeEditUrl = '{{ route("admin.leave.type.edit", ":id") }}';
            var leaveTypeUpdateUrl = '{{ route("admin.leave.type.update", ":id") }}';
            var leaveTypeStoreUrl = '{{ route("admin.leave.type.store") }}';
            var leaveTypeCreateUrl = '{{ route("admin.leave.type.add") }}';
            var departmentsUrl = '{{ route("admin.department.index") }}';
            
            var editCanvas = document.getElementById('leaveTypeEditCanvas');
            if (editCanvas) {
                editCanvas.addEventListener('show.bs.offcanvas', function(event) {
                    var button = event.relatedTarget;
                    if (button && button.id === 'addLeaveTypeBtn') {
                        loadLeaveTypeForAdd();
                    } else if (button && button.classList.contains('edit-leave-type-btn')) {
                        var leaveTypeId = button.getAttribute('data-leave-type-id');
                        if (leaveTypeId) {
                            loadLeaveTypeForEdit(leaveTypeId);
                        }
                    }
                });
            }
            
            function loadDepartments(organizationId, selectedDepartmentId) {
                if (!organizationId) {
                    $('#editDepartmentId').empty().append('<option value="">Select Department</option>');
                    return;
                }
                
                $.ajax({
                    url: '{{ url("/admin/department") }}',
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        var departmentSelect = $('#editDepartmentId');
                        departmentSelect.empty().append('<option value="">Select Department</option>');
                        
                        if (response.departments) {
                            response.departments.forEach(function(dept) {
                                if (dept.organization_id == organizationId) {
                                    var selected = dept.id == selectedDepartmentId ? 'selected' : '';
                                    departmentSelect.append('<option value="' + dept.id + '" ' + selected + '>' + dept.name + '</option>');
                                }
                            });
                        }
                    },
                    error: function() {
                        $('#editDepartmentId').empty().append('<option value="">Select Department</option>');
                    }
                });
            }
            
            $('#editOrganizationId').on('change', function() {
                var orgId = $(this).val();
                loadDepartments(orgId, null);
            });
            
            function loadLeaveTypeForAdd() {
                $('#editFormMode').val('add');
                $('#editLeaveTypeId').val('');
                $('#canvasTitleText').text('Add New Leave Type');
                $('#canvasEditIcon').removeClass('bi-pencil').addClass('bi-plus-circle');
                $('#submitBtnText').text('Create Leave Type');
                
                $.ajax({
                    url: leaveTypeCreateUrl,
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
                        
                        $('#editLeaveTypeName').val('');
                        $('#editLeaveTypeCode').val('');
                        $('#editAnnualQuota').val('0');
                        $('#editLeaveTypeIsActive').prop('checked', true);
                        $('#editDepartmentId').empty().append('<option value="">Select Department</option>');
                        
                        $('.invalid-feedback').text('').hide();
                        $('.form-select, .form-control').removeClass('is-invalid');
                    },
                    error: function(xhr) {
                        console.error('Error loading data for add:', xhr);
                        alert('Failed to load form data. Please try again.');
                    }
                });
            }
            
            function loadLeaveTypeForEdit(leaveTypeId) {
                $('#editFormMode').val('edit');
                $('#canvasTitleText').text('Edit Leave Type');
                $('#canvasEditIcon').removeClass('bi-plus-circle').addClass('bi-pencil');
                $('#submitBtnText').text('Update Leave Type');
                
                var url = leaveTypeEditUrl.replace(':id', leaveTypeId);
                $.ajax({
                    url: url,
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        $('#editLeaveTypeId').val(response.leaveType.id);
                        $('#editLeaveTypeName').val(response.leaveType.name);
                        $('#editLeaveTypeCode').val(response.leaveType.code || '');
                        $('#editAnnualQuota').val(response.leaveType.annual_quota);
                        $('#editLeaveTypeIsActive').prop('checked', response.leaveType.is_active);
                        
                        var orgSelect = $('#editOrganizationId');
                        orgSelect.empty().append('<option value="">Select Organization</option>');
                        response.organizations.forEach(function(org) {
                            var selected = org.id == response.leaveType.organization_id ? 'selected' : '';
                            orgSelect.append('<option value="' + org.id + '" ' + selected + '>' + org.name + '</option>');
                        });
                        
                        if (response.leaveType.organization_id) {
                            loadDepartments(response.leaveType.organization_id, response.leaveType.department_id);
                        } else {
                            $('#editDepartmentId').empty().append('<option value="">Select Department</option>');
                        }
                        
                        $('.invalid-feedback').text('').hide();
                        $('.form-select, .form-control').removeClass('is-invalid');
                    },
                    error: function(xhr) {
                        console.error('Error loading leave type:', xhr);
                        alert('Failed to load leave type data. Please try again.');
                    }
                });
            }
            
            $('#editLeaveTypeForm').on('submit', function(e) {
                e.preventDefault();
                
                var formMode = $('#editFormMode').val();
                var formData = {
                    organization_id: $('#editOrganizationId').val(),
                    department_id: $('#editDepartmentId').val() || null,
                    name: $('#editLeaveTypeName').val(),
                    code: $('#editLeaveTypeCode').val(),
                    annual_quota: $('#editAnnualQuota').val(),
                    is_active: $('#editLeaveTypeIsActive').is(':checked') ? 1 : 0
                };
                
                $('.invalid-feedback').text('').hide();
                $('.form-select, .form-control').removeClass('is-invalid');
                
                var url, method;
                if (formMode === 'add') {
                    url = leaveTypeStoreUrl;
                    method = 'POST';
                } else {
                    var leaveTypeId = $('#editLeaveTypeId').val();
                    url = leaveTypeUpdateUrl.replace(':id', leaveTypeId);
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
                            var offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('leaveTypeEditCanvas'));
                            if (offcanvas) {
                                offcanvas.hide();
                            }
                            location.reload();
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            var fieldMap = {
                                'organization_id': 'editOrganizationId',
                                'department_id': 'editDepartmentId',
                                'name': 'editLeaveTypeName',
                                'code': 'editLeaveTypeCode',
                                'annual_quota': 'editAnnualQuota',
                                'is_active': 'editLeaveTypeIsActive'
                            };
                            $.each(errors, function(field, messages) {
                                var fieldId = '#' + (fieldMap[field] || 'edit' + field);
                                var errorId = fieldId + 'Error';
                                $(fieldId).addClass('is-invalid');
                                $(errorId).text(messages[0]).show();
                            });
                        } else {
                            var errorMsg = formMode === 'add' ? 'Failed to create leave type. Please try again.' : 'Failed to update leave type. Please try again.';
                            alert(errorMsg);
                        }
                    }
                });
            });
        });
    </script>
@endpush
