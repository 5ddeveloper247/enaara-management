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
                    <a href="{{ route('admin.leave.type.add') }}" class="btn btn-primary bg-main border-0">
                        <i class="bi bi-plus-circle me-1"></i>Add New Leave Type
                    </a>
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
                                <span class="text-muted">__</span>
                                @endif
                            </td>
                            <td>
                                @if($lt->organization)
                                <span class="badge px-3 rounded-1 bg-primary">{{ $lt->organization->name }}</span>
                                @else
                                <span class="text-muted">__</span>
                                @endif
                            </td>
                            <td>
                                @if($lt->sbu)
                                <span class="badge px-3 rounded-1 bg-secondary">{{ $lt->sbu->name }}</span>
                                @else
                                <span class="text-muted">__</span>
                                @endif
                            </td>
                            <td>
                                @php $deptCount = $lt->departments->count(); @endphp
                                @if($deptCount == 0)
                                <span class="text-muted small">__</span>
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
                                <a href="{{ route('admin.leave.type.edit', $lt->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil me-1"></i>Edit
                                </a>
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

@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
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
    });
</script>
@endpush
