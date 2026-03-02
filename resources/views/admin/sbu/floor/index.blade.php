@extends('layouts.app')

@section('title', 'SBU Floors - Admin Panel')

@section('page-title', 'SBU Floors')

@push('styles')
    <link href="{{ asset('css/organization.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row align-items-center mb-3">
            <div class="col-md-6">
                <h5 class="mb-0">SBU Floor Management</h5>
            </div>
            <div class="col-md-6 text-end">
            </div>
        </div>
        @include('admin.sbu.floor.counters')
        @include('admin.sbu.floor.floor_cards')
    </div>
    @include('admin.sbu.floor.detail_canvas')
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('js/helpers.js') }}"></script>
    <script src="{{ asset('js/sbu-floor.js') }}"></script>
@endpush
