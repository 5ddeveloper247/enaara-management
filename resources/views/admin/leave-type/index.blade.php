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
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No leave types found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

