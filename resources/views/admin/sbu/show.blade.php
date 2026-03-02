@extends('layouts.app')

@section('title', $sbu->name . ' - SBU')

@section('page-title', 'SBU Details')

@section('content')
    <div class="container-fluid">
        <div class="row align-items-center mb-3">
            <div class="col-md-6">
                <h5 class="mb-0">SBU Details</h5>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('admin.sbu.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back to SBUs
                </a>
            </div>
        </div>
        <div class="card border-0 rounded-4">
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border">
                            <small class="text-muted d-block mb-2">Name</small>
                            <div class="fw-semibold">{{ $sbu->name }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border">
                            <small class="text-muted d-block mb-2">Organization</small>
                            <div class="fw-semibold">{{ $sbu->organization?->name ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border">
                            <small class="text-muted d-block mb-2">City</small>
                            <div class="fw-semibold">{{ $sbu->city ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border">
                            <small class="text-muted d-block mb-2">Status</small>
                            <div class="fw-semibold">
                                @if($sbu->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="p-3 rounded-3 border">
                            <small class="text-muted d-block mb-2">Address</small>
                            <div class="fw-semibold">{{ $sbu->address ?? '—' }}</div>
                        </div>
                    </div>
                    @if($sbu->latitude !== null || $sbu->longitude !== null)
                    <div class="col-12">
                        <div class="p-3 rounded-3 border">
                            <small class="text-muted d-block mb-2">Coordinates</small>
                            <div class="fw-semibold">{{ $sbu->latitude }}, {{ $sbu->longitude }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
