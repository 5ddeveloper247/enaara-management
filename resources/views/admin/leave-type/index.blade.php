@extends('layouts.app')

@section('title', 'Leave Types - Admin Panel')

@section('page-title', 'Leave Types')

@push('styles')
<link href="{{ asset('css/users.css') }}" rel="stylesheet">
<style>
    .lt-sbu-chip-wrap {
        display: inline-flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.35rem;
        max-width: 280px;
    }
    .lt-sbu-list-chip {
        display: inline-block;
        font-size: 0.7rem;
        font-weight: 600;
        line-height: 1.2;
        padding: 0.25rem 0.55rem;
        border-radius: 0.35rem;
        background: #f1f5f9;
        color: #012445;
        border: 1px solid #e2e8f0;
        white-space: nowrap;
        max-width: 120px;
        overflow: hidden;
        text-overflow: ellipsis;
        vertical-align: middle;
    }
    .lt-sbu-list-chip-more {
        background: var(--main-color, #012445);
        color: #fff;
        border-color: var(--main-color, #012445);
        cursor: default;
    }
</style>
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
                                        <i class="bi bi-file-earmark-medical me-1"></i>Total Conditional Leaves
                                    </h6>
                                    <div class="h4 mb-0 fw-bold text-info">{{ $conditionalTotal ?? 0 }}</div>
                                </div>
                                <div class="text-info opacity-25">
                                    <i class="bi bi-file-earmark-medical fs-1"></i>
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
                                        <i class="bi bi-calculator me-1"></i>Non-Conditional Leaves Quota
                                    </h6>
                                    <div class="h4 mb-0 fw-bold text-warning">{{ number_format((float) ($unconditionalQuotaSum ?? 0), 2) }}</div>
                                </div>
                                <div class="text-warning opacity-25">
                                    <i class="bi bi-calculator fs-1"></i>
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
                                @php
                                    $assignedSbus = $lt->sbus->isNotEmpty()
                                        ? $lt->sbus
                                        : ($lt->sbu ? collect([$lt->sbu]) : collect());
                                    $sbuTotal = $assignedSbus->count();
                                    $sbuNamesAll = $assignedSbus->pluck('name')->implode(', ');
                                @endphp
                                @if($sbuTotal === 0)
                                <span class="text-muted small">__</span>
                                @else
                                <div class="lt-sbu-chip-wrap" title="{{ $sbuNamesAll }}">
                                    @foreach($assignedSbus->take(2) as $sbu)
                                    <span class="lt-sbu-list-chip">{{ $sbu->name }}</span>
                                    @endforeach
                                    @if($sbuTotal > 2)
                                    <span class="lt-sbu-list-chip lt-sbu-list-chip-more">+{{ $sbuTotal - 2 }} more</span>
                                    @endif
                                </div>
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
    });
</script>
@endpush
