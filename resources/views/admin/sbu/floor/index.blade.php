@extends('layouts.app')

@section('title', 'SBU Floors - Admin Panel')

@section('page-title', 'SBU Floors')

@push('styles')
<link href="{{ asset('css/organization.css') }}" rel="stylesheet">
<style>
.floor-number-badge {
    width: 45px;
    height: 45px;
    font-size: 0.85rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    flex-shrink: 0;
    line-height: 1;
    padding: 0 4px;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row align-items-center mb-3">
        <div class="col-md-6">
            <h5 class="mb-0">SBU Floor Management</h5>
        </div>
        <div class="col-md-6 text-end">
            <button type="button" class="btn btn-primary bg-main border-0" data-bs-toggle="offcanvas"
                data-bs-target="#addSbuFloorCanvas" aria-controls="addSbuFloorCanvas">
                <i class="bi bi-building-add me-1"></i>Add New SBU Floor
            </button>
        </div>
    </div>
    @include('admin.sbu.floor.counters')
    @include('admin.sbu.floor.floor_cards')
</div>
@include('admin.sbu.floor.detail_canvas')
<!-- Add Sbu Canvas -->
@include('admin.sbu.floor.add_sbu')

<!-- Edit Sbu Canvas -->
@include('admin.sbu.floor.edit_sbu')
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="{{ asset('js/helpers.js') }}"></script>
@php
    $sbuFloorSbus = ($sbus ?? collect())->map(function ($sbu) {
        return [
            'id' => $sbu->id,
            'name' => $sbu->name,
            'organization_id' => $sbu->organization_id,
        ];
    })->values();
    $sbuFloorBiometricDevices = ($biometricDevicesForFloors ?? collect())->map(function ($d) {
        return [
            'id' => $d->id,
            'sbu_id' => $d->sbu_id,
            'device_name' => $d->device_name,
            'serial_number' => $d->serial_number,
            'sbu_floor_id' => $d->sbu_floor_id,
        ];
    })->values();
@endphp
<script>
    window.sbuFloorSbus = @json($sbuFloorSbus);
    window.sbuFloorBiometricDevices = @json($sbuFloorBiometricDevices);
    window.viewerEmployeeScope = @json($viewerEmployeeScope ?? []);
</script>
<script src="{{ asset('js/sbu-floor.js') }}"></script>
@endpush
