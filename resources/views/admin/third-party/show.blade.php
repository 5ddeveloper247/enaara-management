@extends('layouts.app')

@section('title', $thirdParty->name . ' - Third Party')

@section('page-title', 'Third Party Details')

@section('content')
    <div class="container-fluid">
        <div class="row align-items-center mb-3">
            <div class="col-md-6">
                <h5 class="mb-0">Third Party Details</h5>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('admin.third-party.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back to list
                </a>
            </div>
        </div>
        <div class="card border-0 rounded-4">
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border">
                            <small class="text-muted d-block mb-2">SBU name</small>
                            <div class="fw-semibold">{{ $thirdParty->name }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border">
                            <small class="text-muted d-block mb-2">Third party name</small>
                            <div class="fw-semibold">{{ $thirdParty->third_party_name }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border">
                            <small class="text-muted d-block mb-2">Organization</small>
                            <div class="fw-semibold">{{ $thirdParty->organization?->name ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border">
                            <small class="text-muted d-block mb-2">City</small>
                            <div class="fw-semibold">{{ $thirdParty->city ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border">
                            <small class="text-muted d-block mb-2">Status</small>
                            <div class="fw-semibold">
                                @if($thirdParty->is_active)
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
                            <div class="fw-semibold">{{ $thirdParty->address ?? '—' }}</div>
                        </div>
                    </div>
                    @if($thirdParty->latitude !== null || $thirdParty->longitude !== null)
                    <div class="col-12">
                        <div class="p-3 rounded-3 border">
                            <small class="text-muted d-block mb-2">Coordinates</small>
                            <div class="fw-semibold">{{ $thirdParty->latitude }}, {{ $thirdParty->longitude }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
