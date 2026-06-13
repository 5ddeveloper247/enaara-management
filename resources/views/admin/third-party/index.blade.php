@extends('layouts.app')

@section('title', 'Third Party - Admin Panel')

@section('page-title', 'Third Party')

@push('styles')
<link href="{{ asset('css/organization.css') }}" rel="stylesheet">
<style>
    .tp-ms-box {
        min-height: 46px;
        border: 1px solid #ced4da;
        border-radius: .5rem;
        background: #fff;
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
        padding: 6px 10px;
        cursor: pointer;
        position: relative;
    }
    .tp-ms-box:hover { border-color: #9aa6b2; }
    .tp-ms-box.open, .tp-ms-box:focus-within {
        border-color: #86b7fe;
        box-shadow: 0 0 0 .2rem rgba(13,110,253,.15);
    }
    .tp-ms-box.is-invalid { border-color: #dc3545; }
    .tp-ms-box.disabled { background: #eef0f2; cursor: not-allowed; opacity: .85; }
    .tp-ms-box.disabled .tp-ms-chevron { opacity: .5; }
    .tp-ms-chips { display: contents; }
    .tp-ms-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        line-height: 1;
        border-radius: 999px;
        background: #eef2ff;
        color: #1f2a44;
        border: 1px solid #d9e0ff;
        padding: 6px 9px;
        margin: 2px 0;
    }
    .tp-ms-chip-x { cursor: pointer; font-weight: 700; }
    .tp-ms-ph { color: #6c757d; font-size: 14px; }
    .tp-ms-chevron { margin-left: auto; color: #6c757d; transition: transform .2s; }
    .tp-ms-box.open .tp-ms-chevron { transform: rotate(180deg); }
    .tp-ms-dropdown {
        margin-top: 6px;
        border: 1px solid #ced4da;
        border-radius: .5rem;
        background: #fff;
        color: #212529;
        overflow: hidden;
        display: none;
    }
    .tp-ms-search-row { padding: 8px; border-bottom: 1px solid #e9ecef; }
    .tp-ms-search-row input {
        width: 100%;
        color: #212529;
        background: #fff;
        border: 1px solid #ced4da;
        border-radius: .45rem;
        padding: 6px 10px;
        outline: none;
    }
    .tp-ms-opt-list { max-height: 210px; overflow: auto; }
    .tp-ms-opt { padding: 9px 11px; color: #212529; cursor: pointer; display: flex; align-items: center; gap: 8px; }
    .tp-ms-opt:hover { background: #f5f7fb; }
    .tp-ms-opt.picked { background: #eef2ff; }
    .tp-ms-opt-cb {
        width: 14px; height: 14px; border: 1px solid #adb5bd; border-radius: .2rem;
        display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .tp-ms-opt-ck { display: none; }
    .tp-ms-opt.picked .tp-ms-opt-cb { background: #0d6efd; border-color: #0d6efd; }
    .tp-ms-opt.picked .tp-ms-opt-ck { display: block; }
    .tp-ms-no-result { padding: 10px 12px; color: #6c757d; font-size: 13px; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row align-items-center mb-3">
        <div class="col-md-6">
            <h5 class="mb-0">Third Party Management</h5>
        </div>
        <div class="col-md-6 text-end">
            <button type="button" class="btn btn-primary bg-main border-0" data-bs-toggle="offcanvas"
                data-bs-target="#addThirdPartyCanvas" aria-controls="addThirdPartyCanvas">
                <i class="bi bi-building-add me-1"></i>Add New Third Party
            </button>
        </div>
    </div>

    @include('admin.third-party.counters')
    @include('admin.third-party.third_party_cards')
</div>

@include('admin.third-party.detail_canvas')

@include('admin.third-party.add_third_party')
@include('admin.third-party.edit_third_party')
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/validate.js@0.13.1/validate.min.js"></script>
<script src="{{ asset('js/helpers.js') }}"></script>
@php
    $thirdPartyOrganizations = ($organizations ?? collect())->map(function ($organization) {
        return [
            'id' => $organization->id,
            'name' => $organization->name,
        ];
    })->values();
    $thirdPartySbus = ($sbus ?? collect())->map(function ($sbu) {
        return [
            'id' => $sbu->id,
            'name' => $sbu->name,
            'organization_id' => $sbu->organization_id,
        ];
    })->values();
@endphp
<script>
    window.thirdPartyOrganizations = @json($thirdPartyOrganizations);
    window.thirdPartySbus = @json($thirdPartySbus);
    window.viewerEmployeeScope = @json($viewerEmployeeScope ?? []);
</script>
<script src="{{ asset('js/third-party.js') }}"></script>
@endpush
