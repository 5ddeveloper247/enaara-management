@extends('layouts.app')

@section('title', ($sbuFloor->name ?? 'Floor') . ' - SBU Floor')

@section('page-title', 'SBU Floor Details')

@section('content')
    <div class="container-fluid">
        <div class="row align-items-center mb-3">
            <div class="col-md-6">
                <h5 class="mb-0">SBU Floor Details</h5>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('admin.sbu.floor.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back to Floors
                </a>
            </div>
        </div>
        <div class="card border-0 rounded-4">
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border">
                            <small class="text-muted d-block mb-2">Name</small>
                            <div class="fw-semibold">{{ $sbuFloor->name }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border">
                            <small class="text-muted d-block mb-2">SBU</small>
                            <div class="fw-semibold">{{ $sbuFloor->sbu?->name ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border">
                            <small class="text-muted d-block mb-2">Floor Number</small>
                            <div class="fw-semibold">{{ $sbuFloor->floor_number ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border">
                            <small class="text-muted d-block mb-2">Floor Type</small>
                            <div class="fw-semibold">{{ ucfirst($sbuFloor->floor_type) }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border">
                            <small class="text-muted d-block mb-2">Restricted</small>
                            <div class="fw-semibold">{{ $sbuFloor->is_restricted ? 'Yes' : 'No' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border">
                            <small class="text-muted d-block mb-2">Status</small>
                            <div class="fw-semibold">
                                @if($sbuFloor->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
