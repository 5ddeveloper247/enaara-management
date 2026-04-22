@extends('layouts.app')

@section('title', $device->device_name . ' - Biometric Device')

@section('page-title', 'Biometric Device Details')

@section('content')
    <div class="container-fluid">
        <div class="row align-items-center mb-3">
            <div class="col-md-6">
                <h5 class="mb-0">Biometric Device</h5>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('admin.biometric-device.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back to list
                </a>
            </div>
        </div>
        <div class="card border-0 rounded-4">
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border">
                            <small class="text-muted d-block mb-2">Device name</small>
                            <div class="fw-semibold">{{ $device->device_name }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border">
                            <small class="text-muted d-block mb-2">Serial number</small>
                            <div class="fw-semibold">{{ $device->serial_number }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border">
                            <small class="text-muted d-block mb-2">Organisation</small>
                            <div class="fw-semibold">{{ $device->organization?->name ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border">
                            <small class="text-muted d-block mb-2">SBU</small>
                            <div class="fw-semibold">{{ $device->sbu?->name ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border">
                            <small class="text-muted d-block mb-2">IP · Port · Connection</small>
                            <div class="fw-semibold">{{ $device->ip_address }}:{{ $device->port }} ({{ strtoupper($device->connection_type) }})</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border">
                            <small class="text-muted d-block mb-2">Device status</small>
                            <div class="fw-semibold text-capitalize">{{ $device->device_status }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border">
                            <small class="text-muted d-block mb-2">Online status (auto)</small>
                            <div class="fw-semibold text-capitalize">{{ $device->online_status }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border">
                            <small class="text-muted d-block mb-2">Last sync</small>
                            <div class="fw-semibold">{{ $device->last_sync_time ? $device->last_sync_time->format('Y-m-d H:i') : '—' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border">
                            <small class="text-muted d-block mb-2">Installation date</small>
                            <div class="fw-semibold">{{ $device->installation_date?->format('Y-m-d') ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border">
                            <small class="text-muted d-block mb-2">Created by</small>
                            <div class="fw-semibold">{{ $device->creator?->name ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border">
                            <small class="text-muted d-block mb-2">Created · Updated</small>
                            <div class="fw-semibold">{{ $device->created_at?->format('Y-m-d H:i') }} · {{ $device->updated_at?->format('Y-m-d H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
