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
                                        @php $deptCount = $lt->departments->count(); @endphp
                                        @if($deptCount == 0)
                                            <span class="text-muted small">—</span>
                                        @elseif($deptCount == 1)
                                            <span class="badge px-3 rounded-1 bg-info">{{ $lt->departments->first()->name }}</span>
                                        @else
                                            <button type="button" class="btn btn-sm btn-info text-white rounded-pill px-3 py-0 border-0 view-depts-btn" 
                                                    data-bs-toggle="modal" data-bs-target="#departmentsModal" 
                                                    data-leave-type-name="{{ $lt->name }}"
                                                    data-departments="{{ $lt->departments->pluck('name')->implode(',') }}">
                                                Multiple ({{ $deptCount }})
                                            </button>
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
                                        <button type="button" class="btn btn-sm btn-outline-danger delete-leave-type-btn ms-1" data-leave-type-id="{{ $lt->id }}">
                                            <i class="bi bi-trash me-1"></i>Delete
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
    
    <!-- Modal for viewing multiple departments -->
    <div class="modal fade" id="departmentsModal" tabindex="-1" aria-labelledby="departmentsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="departmentsModalLabel">Affected Departments</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">Leave Type: <span id="modalLeaveTypeName" class="fw-bold text-dark"></span></p>
                    <div id="modalDepartmentsList" class="d-flex flex-wrap gap-2">
                        <!-- Departments will be injected here -->
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Close</button>
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

            // Delete Leave Type Handler
            var leaveTypeDestroyUrl = '{{ route("admin.leave.type.destroy", ":id") }}';

            $(document).on('click', '.delete-leave-type-btn', function(e) {
                e.preventDefault();
                
                const leaveTypeId = $(this).data('leave-type-id');
                const deleteUrl = leaveTypeDestroyUrl.replace(':id', leaveTypeId);
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: deleteUrl,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: 'Deleted!',
                                        text: response.message || 'Leave type has been deleted successfully.',
                                        icon: 'success',
                                        confirmButtonColor: '#3085d6'
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Error!',
                                        text: response.message || 'An error occurred while deleting the leave type.',
                                        icon: 'error',
                                        confirmButtonColor: '#3085d6'
                                    });
                                }
                            },
                            error: function(xhr) {
                                let errorMessage = 'Failed to delete leave type.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }
                                Swal.fire({
                                    title: 'Error!',
                                    text: errorMessage,
                                    icon: 'error',
                                    confirmButtonColor: '#3085d6'
                                });
                            }
                        });
                    }
                });
            });
            
            // View Departments Modal Handler
            $(document).on('click', '.view-depts-btn', function() {
                const name = $(this).data('leave-type-name');
                const depts = $(this).data('departments').split(',');
                
                $('#modalLeaveTypeName').text(name);
                const list = $('#modalDepartmentsList');
                list.empty();
                depts.forEach(function(dept) {
                    list.append(`<span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2 rounded-pill">${dept}</span>`);
                });
            });

            function loadDepartments(organizationId, selectedDepartmentIds) {
                var container = $('#departmentCheckboxes');
                selectedDepartmentIds = selectedDepartmentIds || [];
                
                if (!organizationId) {
                    container.empty().append('<div class="text-muted small">Select an organization first...</div>');
                    return;
                }
                
                container.empty().append('<div class="text-muted small">Loading departments...</div>');
                
                $.ajax({
                    url: '{{ url("/admin/department") }}',
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        container.empty();
                        
                        if (response.departments && response.departments.length > 0) {
                            var anyAdded = false;
                            response.departments.forEach(function(dept) {
                                if (dept.organization_id == organizationId) {
                                    var checked = selectedDepartmentIds.includes(dept.id) ? 'checked' : '';
                                    var html = `
                                        <div class="form-check mb-1">
                                            <input class="form-check-input dept-checkbox" type="checkbox" value="${dept.id}" id="deptCheck_${dept.id}" ${checked}>
                                            <label class="form-check-label small text-dark" for="deptCheck_${dept.id}">
                                                ${dept.name}
                                            </label>
                                        </div>
                                    `;
                                    container.append(html);
                                    anyAdded = true;
                                }
                            });
                            if (!anyAdded) {
                                container.append('<div class="text-muted small">No departments found for this organization.</div>');
                            }
                        } else {
                            container.append('<div class="text-muted small">No departments available.</div>');
                        }
                    },
                    error: function() {
                        container.empty().append('<div class="text-danger small">Error loading departments.</div>');
                    }
                });
            }
            
            $('#editOrganizationId').on('change', function() {
                var orgId = $(this).val();
                $('#selectAllDepartments').prop('checked', false);
                loadDepartments(orgId, null);
            });

            // Select All Departments Handler
            $('#selectAllDepartments').on('change', function() {
                var isChecked = $(this).is(':checked');
                $('.dept-checkbox').prop('checked', isChecked);
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
                        $('#editDepartmentId').empty();
                        $('#departmentCheckboxes').empty().append('<div class="text-muted small">Select an organization first...</div>');
                        
                        $('.invalid-feedback').text('').hide();
                        $('.form-select, .form-control').removeClass('is-invalid');
                        $('#selectAllDepartments').prop('checked', false);
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
                            loadDepartments(response.leaveType.organization_id, response.department_ids);
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
                var selectedDepts = [];
                $('.dept-checkbox:checked').each(function() {
                    selectedDepts.push($(this).val());
                });

                var formData = {
                    organization_id: $('#editOrganizationId').val(),
                    department_ids: selectedDepts,
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
                            if (offcanvas) offcanvas.hide();

                            Swal.fire({
                                title: formMode === 'add' ? 'Created!' : 'Updated!',
                                text: response.message || (formMode === 'add' ? 'Leave type created successfully.' : 'Leave type updated successfully.'),
                                icon: 'success',
                                confirmButtonColor: '#012445',
                                timer: 2000,
                                timerProgressBar: true,
                            }).then(function() {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', response.message || 'Something went wrong.', 'error');
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            var errorList = '';
                            var fieldMap = {
                                'organization_id': 'editOrganizationId',
                                'department_id':   'editDepartmentId',
                                'name':            'editLeaveTypeName',
                                'code':            'editLeaveTypeCode',
                                'annual_quota':    'editAnnualQuota',
                                'is_active':       'editLeaveTypeIsActive'
                            };
                            $.each(errors, function(field, messages) {
                                var fieldId = '#' + (fieldMap[field] || 'edit' + field);
                                var errorId = fieldId + 'Error';
                                $(fieldId).addClass('is-invalid');
                                $(errorId).text(messages[0]).show();
                                errorList += '<li>' + messages[0] + '</li>';
                            });
                            Swal.fire({
                                title: 'Please check the following:',
                                html: '<ul class="text-start ps-3 mb-0">' + errorList + '</ul>',
                                icon: 'warning',
                                confirmButtonColor: '#012445',
                            });
                        } else {
                            var errorMsg = formMode === 'add' ? 'Failed to create leave type. Please try again.' : 'Failed to update leave type. Please try again.';
                            Swal.fire('Error', errorMsg, 'error');
                        }
                    }
                });
            });
        });
    </script>
@endpush
