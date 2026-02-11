@extends('layouts.app')

@section('title', 'Dashboard - Admin Panel')

@section('page-title', 'Dashboard')

{{-- CSS IS COMING FROM THIS FILE IN DASHBOARD FOLDER --}}
@push('styles')
    <!-- Dashboard Custom CSS -->
    <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="container-fluid">
        <div class="d-flex gap-2 w-100">
            <div class="row g-3 w-100">

                {{-- Main Counters Row --}}
                @include('admin.dashboard.counters')
                @include('admin.dashboard.geofence-compliance')

                {{-- Quick Stats Row --}}
                @include('admin.dashboard.quick-stats')

                {{-- Main Content Area --}}
                <div class="col-lg-9">
                    <div class="row g-3">
                        <div class="col-lg-5">
                            @include('admin.dashboard.attendance-overview')
                        </div>

                        <div class="col-lg-7">
                            @include('admin.dashboard.department-chart')
                        </div>

                        <div class="col-lg-7">
                            @include('admin.dashboard.pending-approvals')
                        </div>

                        <div class="col-lg-5">
                            @include('admin.dashboard.exceptions-table')
                        </div>

                        {{-- Regularization Pending --}}
                        @include('admin.dashboard.regularization-pending')

                        {{-- System Alerts --}}
                        @include('admin.dashboard.system-alerts')
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="col-lg-3">
                    <div class="d-flex flex-column gap-3">
                        @include('admin.dashboard.who-is-out')
                        @include('admin.dashboard.upcoming-holidays')
                        @include('admin.dashboard.departmental-quota')
                    </div>
                </div>

            </div>
        </div>
    </div>

    @include('admin.dashboard.slide-over-panel')
@endsection


@push('scripts')
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <!-- Project Dummy Data -->
    <script src="{{ asset('js/dummy-data.js') }}"></script>
    <!-- Dashboard JavaScript -->
    <script src="{{ asset('js/dashboard.js') }}"></script>
@endpush
