@extends('layouts.app')

@section('title', 'SBU - Admin Panel')

@section('page-title', 'SBU')

@push('styles')
<link href="{{ asset('css/organization.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid">
    <div class="row align-items-center mb-3">
        <div class="col-md-6">
            <h5 class="mb-0">SBU Management</h5>
        </div>
        <div class="col-md-6 text-end">
            <button type="button" class="btn btn-primary bg-main border-0" data-bs-toggle="offcanvas"
                data-bs-target="#addSbuCanvas" aria-controls="addSbuCanvas">
                <i class="bi bi-building-add me-1"></i>Add New SBU
            </button>
        </div>
    </div>

    @include('admin.sbu.counters')
    @include('admin.sbu.sbu_cards')
</div>

@include('admin.sbu.detail_canvas')

<!-- Add SBU Canvas -->
@include('admin.sbu.add_sbu')
<!-- Edit SBU Canvas -->
 @include('admin.sbu.edit_sbu')
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="{{ asset('js/helpers.js') }}"></script>
<script src="{{ asset('js/sbu.js') }}"></script>
@endpush