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
                            <th>SBU</th>
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
                                @if($lt->sbu)
                                <span class="badge px-3 rounded-1 bg-secondary">{{ $lt->sbu->name }}</span>
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
                                <button type="button" class="badge bg-info text-white rounded-1 border-0 px-3 view-depts-btn"
                                    style="font-size: inherit; vertical-align: baseline;"
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
        var leaveDeptPool = [];
        var leaveDeptSelected = [];

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
                                showSuccess(response.message || 'Leave type has been deleted successfully.', 'Deleted').then(() => {
                                    location.reload();
                                });
                            } else {
                                showError(response.message || 'An error occurred while deleting the leave type.');
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

        function renderLeaveDeptHiddenInputs() {
            var wrap = $('#departmentHiddenInputs');
            wrap.empty();
            leaveDeptSelected.forEach(function(id) {
                wrap.append(`<input type="hidden" name="department_ids[]" value="${id}">`);
            });
        }

        function renderLeaveDeptChips() {
            var chips = $('#leaveDeptChips');
            var placeholder = $('#leaveDeptPlaceholder');
            chips.empty();
            if (!leaveDeptSelected.length) {
                placeholder.show();
                return;
            }
            placeholder.hide();
            leaveDeptSelected.forEach(function(id) {
                var row = leaveDeptPool.find(function(d) { return String(d.id) === String(id); });
                var name = row ? row.name : id;
                chips.append(
                    `<span class="lt-dept-chip">${name}<span class="lt-dept-chip-x" onclick="leaveDeptRemove('${id}', event)">×</span></span>`
                );
            });
        }

        function syncLeaveDeptState() {
            renderLeaveDeptHiddenInputs();
            renderLeaveDeptChips();
            window.leaveDeptRenderList();
        }

        window.leaveDeptRenderList = function() {
            var list = $('#leaveDeptList');
            var q = ($('#leaveDeptSearch').val() || '').toLowerCase().trim();
            var rows = leaveDeptPool.filter(function(d) {
                return !q || String(d.name || '').toLowerCase().includes(q);
            });
            if (!rows.length) {
                list.html('<div class="lt-dept-no-result">No departments found</div>');
                return;
            }
            list.html(rows.map(function(d) {
                var picked = leaveDeptSelected.includes(String(d.id));
                return `<div class="lt-dept-opt ${picked ? 'picked' : ''}" onclick="leaveDeptToggle('${d.id}')">
                    <span class="lt-dept-opt-cb">
                        <svg class="lt-dept-opt-ck" viewBox="0 0 16 16" fill="none">
                            <path d="M3.5 8.2l3 3L12.5 5" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span class="lt-dept-opt-name">${d.name}</span>
                </div>`;
            }).join(''));
        };

        window.leaveDeptToggle = function(id) {
            id = String(id);
            if (leaveDeptSelected.includes(id)) {
                leaveDeptSelected = leaveDeptSelected.filter(function(v) { return v !== id; });
            } else {
                leaveDeptSelected.push(id);
            }
            $('#selectAllDepartments').prop('checked', leaveDeptPool.length > 0 && leaveDeptSelected.length === leaveDeptPool.length);
            syncLeaveDeptState();
        };

        window.leaveDeptRemove = function(id, e) {
            if (e) e.stopPropagation();
            id = String(id);
            leaveDeptSelected = leaveDeptSelected.filter(function(v) { return v !== id; });
            $('#selectAllDepartments').prop('checked', false);
            syncLeaveDeptState();
        };

        window.leaveDeptBoxClick = function(e) {
            if (e) e.stopPropagation();
            if (!leaveDeptPool.length) return;
            var box = $('#leaveDeptBox');
            var dd = $('#leaveDeptDropdown');
            var isOpen = dd.is(':visible');
            if (isOpen) {
                dd.hide();
                box.removeClass('open');
            } else {
                dd.show();
                box.addClass('open');
                window.leaveDeptRenderList();
                $('#leaveDeptSearch').trigger('focus');
            }
        };

        $(document).on('click', function(e) {
            var dd = document.getElementById('leaveDeptDropdown');
            var box = document.getElementById('leaveDeptBox');
            if (!dd || !box) return;
            if (!dd.contains(e.target) && !box.contains(e.target)) {
                $('#leaveDeptDropdown').hide();
                $('#leaveDeptBox').removeClass('open');
            }
        });

        function loadSbus(organizationId, selectedSbuId) {
            var sbuSelect = $('#editSbuId');

            if (!organizationId) {
                sbuSelect.empty().append('<option value="">Please select Organization first...</option>');
                return;
            }

            sbuSelect.empty().append('<option value="">Loading SBUs...</option>');

            $.ajax({
                url: '{{ route("admin.sbu.index") }}',
                method: 'GET',
                data: {
                    organization_id: organizationId
                },
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    sbuSelect.empty().append('<option value="">Select SBU</option>');
                    if (response.sbus) {
                        response.sbus.forEach(function(sbu) {
                            var selected = sbu.id == selectedSbuId ? 'selected' : '';
                            sbuSelect.append('<option value="' + sbu.id + '" ' + selected + '>' + sbu.name + '</option>');
                        });
                    }
                }
            });
        }

        function loadDepartments(sbuId, selectedDepartmentIds) {
            selectedDepartmentIds = selectedDepartmentIds || [];

            if (!sbuId) {
                var orgId = $('#editOrganizationId').val();
                var msg = orgId ? 'Select an SBU first...' : 'Select an organization first...';
                leaveDeptPool = [];
                leaveDeptSelected = [];
                $('#leaveDeptPlaceholder').text(msg).show();
                $('#selectAllDepartments').prop('checked', false);
                syncLeaveDeptState();
                return;
            }
            $('#leaveDeptList').html('<div class="text-muted small p-2">Loading departments...</div>');

            $.ajax({
                url: '{{ url("/admin/department") }}',
                method: 'GET',
                data: {
                    sbu_id: sbuId
                },
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.departments && response.departments.length > 0) {
                        leaveDeptPool = response.departments.map(function(dept) {
                            return { id: String(dept.id), name: dept.name };
                        });
                        var allowed = new Set(leaveDeptPool.map(function(d) { return d.id; }));
                        leaveDeptSelected = (selectedDepartmentIds || [])
                            .map(function(v) { return String(v); })
                            .filter(function(v) { return allowed.has(v); });
                        $('#leaveDeptPlaceholder').text('Select Departments...');
                    } else {
                        leaveDeptPool = [];
                        leaveDeptSelected = [];
                        $('#leaveDeptPlaceholder').text('No departments found for this SBU.');
                    }
                    $('#selectAllDepartments').prop('checked', leaveDeptPool.length > 0 && leaveDeptSelected.length === leaveDeptPool.length);
                    syncLeaveDeptState();
                },
                error: function() {
                    leaveDeptPool = [];
                    leaveDeptSelected = [];
                    $('#leaveDeptPlaceholder').text('Error loading departments.');
                    $('#selectAllDepartments').prop('checked', false);
                    syncLeaveDeptState();
                }
            });
        }

        $('#editOrganizationId').on('change', function() {
            var orgId = $(this).val();
            loadSbus(orgId, null);
            $('#editSbuId').empty().append('<option value="">Select SBU</option>');
            leaveDeptPool = [];
            leaveDeptSelected = [];
            $('#leaveDeptPlaceholder').text(orgId ? 'Select an SBU first...' : 'Select an organization first...');
            $('#selectAllDepartments').prop('checked', false);
            syncLeaveDeptState();
        });

        $('#editSbuId').on('change', function() {
            var sbuId = $(this).val();
            $('#selectAllDepartments').prop('checked', false);
            loadDepartments(sbuId, null);
        });

        // Select All Departments Handler
        $('#selectAllDepartments').on('change', function() {
            var isChecked = $(this).is(':checked');
            leaveDeptSelected = isChecked ? leaveDeptPool.map(function(d) { return String(d.id); }) : [];
            syncLeaveDeptState();
        });

        $('#leaveDeptSearch').on('keydown', function(e) {
            e.stopPropagation();
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
                    $('#editSbuId').empty().append('<option value="">Please select Organization first...</option>');
                    leaveDeptPool = [];
                    leaveDeptSelected = [];
                    $('#leaveDeptPlaceholder').text('Select an organization first...').show();
                    syncLeaveDeptState();

                    $('.invalid-feedback').text('').hide();
                    $('.form-select, .form-control').removeClass('is-invalid');
                    $('#leaveDeptBox').removeClass('is-invalid');
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
                        loadSbus(response.leaveType.organization_id, response.leaveType.sbu_id);
                    }
                    if (response.leaveType.sbu_id) {
                        loadDepartments(response.leaveType.sbu_id, response.department_ids);
                    } else {
                        leaveDeptPool = [];
                        leaveDeptSelected = [];
                        $('#leaveDeptPlaceholder').text('Select an SBU first...').show();
                        syncLeaveDeptState();
                    }

                    $('.invalid-feedback').text('').hide();
                    $('.form-select, .form-control').removeClass('is-invalid');
                    $('#leaveDeptBox').removeClass('is-invalid');
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
            var selectedDepts = leaveDeptSelected.slice();

            var formData = {
                organization_id: $('#editOrganizationId').val(),
                sbu_id: $('#editSbuId').val(),
                department_ids: selectedDepts,
                name: $('#editLeaveTypeName').val(),
                code: $('#editLeaveTypeCode').val(),
                annual_quota: $('#editAnnualQuota').val(),
                is_active: $('#editLeaveTypeIsActive').is(':checked') ? 1 : 0
            };

            $('.invalid-feedback').text('').hide();
            $('.form-select, .form-control').removeClass('is-invalid');
            $('#leaveDeptBox').removeClass('is-invalid');

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

                        showSuccess(response.message || (formMode === 'add' ? 'Leave type created successfully.' : 'Leave type updated successfully.'), formMode === 'add' ? 'Created!' : 'Updated!').then(function() {
                            location.reload();
                        });
                    } else {
                        showError(response.message || 'Something went wrong.');
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        var errorList = '';
                        var fieldMap = {
                            'organization_id': 'editOrganizationId',
                            'sbu_id': 'editSbuId',
                            'department_ids': 'leaveDeptBox',
                            'name': 'editLeaveTypeName',
                            'code': 'editLeaveTypeCode',
                            'annual_quota': 'editAnnualQuota',
                            'is_active': 'editLeaveTypeIsActive'
                        };
                        $.each(errors, function(field, messages) {
                            var fieldId = '#' + (fieldMap[field] || 'edit' + field);
                            var errorId = field === 'department_ids' ? '#editDepartmentIdError' : (fieldId + 'Error');
                            $(fieldId).addClass('is-invalid');
                            $(errorId).text(messages[0]).show();
                            errorList += '<li>' + messages[0] + '</li>';
                        });
                        Swal.fire({
                            title: 'Please check the following:',
                            html: '<ul class="text-start ps-3 mb-0">' + errorList + '</ul>',
                            icon: 'warning',
                            confirmButtonColor: '#1a237e',
                        });
                    } else {
                        var errorMsg = formMode === 'add' ? 'Failed to create leave type. Please try again.' : 'Failed to update leave type. Please try again.';
                        showError(errorMsg);
                    }
                }
            });
        });

        syncLeaveDeptState();
    });
</script>
@endpush