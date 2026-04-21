@extends('layouts.app')

@section('title', 'Biometric Devices - Admin Panel')

@section('page-title', 'Biometric Devices')

@section('content')
<div class="container-fluid">
    <div class="row align-items-center mb-3">
        <div class="col-md-6">
            <h5 class="mb-0">Biometric Device Registration</h5>
        </div>
        <div class="col-md-6 text-end">
            @if(validatePermissions('admin/biometric-device/add'))
            <button type="button" class="btn btn-primary bg-main border-0" data-bs-toggle="offcanvas"
                data-bs-target="#addBiometricDeviceCanvas" aria-controls="addBiometricDeviceCanvas">
                <i class="bi bi-fingerprint me-1"></i>Register Device
            </button>
            @endif
        </div>
    </div>

    @include('admin.biometric-device.counters')
    @include('admin.biometric-device.device_cards')
</div>

@include('admin.biometric-device.detail_canvas')

@if(validatePermissions('admin/biometric-device/add'))
@include('admin.biometric-device.add_biometric_device')
@endif
@if(validatePermissions('admin/biometric-device/edit'))
@include('admin.biometric-device.edit_biometric_device')
@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/validate.js@0.13.1/validate.min.js"></script>
<script src="{{ asset('js/helpers.js') }}"></script>
@php
    $bioOrgs = ($organizations ?? collect())->map(fn ($o) => ['id' => $o->id, 'name' => $o->name])->values();
    $bioSbus = ($sbus ?? collect())->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'organization_id' => $s->organization_id])->values();
    $bioFloors = ($floors ?? collect())->map(fn ($f) => [
        'id' => $f->id,
        'sbu_id' => $f->sbu_id,
        'name' => $f->name,
        'floor_number' => $f->floor_number,
    ])->values();
@endphp
<script>
    window.biometricOrganizations = @json($bioOrgs);
    window.biometricSbus = @json($bioSbus);
    window.biometricFloors = @json($bioFloors);
</script>
<script src="{{ asset('js/biometric-device.js') }}"></script>
@endpush
