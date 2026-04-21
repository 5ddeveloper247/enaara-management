@extends('layouts.app')

@section('title', 'Employee Management - Admin Panel')

@section('page-title', 'Employee Management')

@push('styles')
    <!-- Employee Module CSS -->
    <link href="{{ asset('css/employee.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Stats Card with Tabs -->
        <div class="card border-0 rounded-4 mb-4">
            <div class="card-body p-4">
                @include('admin.employee.stats-tabs')
                @include('admin.employee.counters')
            </div>
        </div>

        <!-- Main Content Card -->
        <div class="card border-0 rounded-4 mb-4">
            <div class="card-body p-0">
                <!-- Header with Actions and Filters -->
                @include('admin.employee.header')

                <!-- Data Table -->
                @include('admin.employee.employee_table')
            </div>
        </div>
    </div>

    <!-- Employee Detail Canvas -->
    @include('admin.employee.detail_canvas')

    <!-- Add Employee Canvas -->
    @include('admin.employee.add_employee_canvas')

    <!-- Create User Account Canvas -->
    @include('admin.employee.create_user_account_canvas')
    @include('admin.outsourced-employee.canvas')
    @include('admin.outsourced-employee.detail_canvas')
@endsection

@push('scripts')
    <!-- Common Helper Functions -->
    <script src="{{ asset('js/helpers.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    <!-- Employee Module JavaScript -->
    <script src="{{ asset('js/employee.js') }}"></script>
    <script src="{{ asset('js/outsourced-employee.js') }}"></script>
@endpush
