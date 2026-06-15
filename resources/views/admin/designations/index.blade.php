@extends('layouts.app')

@section('title', 'Designations - Admin Panel')

@section('page-title', 'Designations')

@push('styles')
<link href="{{ asset('css/organization.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid">
    <div class="row align-items-center mb-3">
        <div class="col-md-6">
            <h5 class="mb-0">Designation Management</h5>
        </div>
        <div class="col-md-6 text-end">
            @if(validatePermissions('admin/designations/add'))
            <button type="button" class="btn btn-primary bg-main border-0" data-bs-toggle="offcanvas"
                data-bs-target="#addDesignationCanvas" aria-controls="addDesignationCanvas">
                <i class="bi bi-briefcase me-1"></i>Add New Designation
            </button>
            @endif
        </div>
    </div>

    @include('admin.designations.counters')
    @include('admin.designations.cards')
</div>

@include('admin.designations.detail_canvas')
@include('admin.designations.add_designation')
@include('admin.designations.edit_designation')
@endsection

@push('scripts')
<script>
    window.designationOrganizations = @json($organizations ?? []);
</script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="{{ asset('js/helpers.js') }}"></script>
<script src="{{ asset('js/designation.js') }}?v={{ filemtime(public_path('js/designation.js')) }}"></script>
@endpush
