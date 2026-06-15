@extends('layouts.app')

@section('title', 'Role Levels - Admin Panel')

@section('page-title', 'Role Levels')

@push('styles')
<link href="{{ asset('css/organization.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid">
    <div class="row align-items-center mb-3">
        <div class="col-md-6">
            <h5 class="mb-0">Role Level Management</h5>
        </div>
        @if(validatePermissions('admin/role-levels/add'))
        <div class="col-md-6 text-end">
            <button type="button" class="btn btn-primary bg-main border-0" data-bs-toggle="offcanvas"
                data-bs-target="#addRoleLevelCanvas" aria-controls="addRoleLevelCanvas">
                <i class="bi bi-layers me-1"></i>Add New Role Level
            </button>
        </div>
        @endif
    </div>

    @include('admin.rolelevels.counters')
    @include('admin.rolelevels.cards')
</div>

@include('admin.rolelevels.detail_canvas')

<!-- Add Role Level Canvas -->
@include('admin.rolelevels.add_level')
<!-- Edit Role Level Canvas -->
@include('admin.rolelevels.edit_level')
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="{{ asset('js/helpers.js') }}"></script>
<script src="{{ asset('js/rolelevel.js') }}?v={{ filemtime(public_path('js/rolelevel.js')) }}"></script>
@endpush
