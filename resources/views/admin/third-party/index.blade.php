@extends('layouts.app')

@section('title', 'Third Party - Admin Panel')

@section('page-title', 'Third Party')

@push('styles')
<link href="{{ asset('css/organization.css') }}" rel="stylesheet">
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
<script src="{{ asset('js/helpers.js') }}"></script>
<script src="{{ asset('js/third-party.js') }}"></script>
@endpush
