@extends('layouts.app')

@section('title', 'Roles & Permissions - Admin Panel')

@section('page-title', 'Roles & Permissions')

@push('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <!-- Roles Module CSS -->
    <link href="{{ asset('css/roles.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Main Card -->
        <div class="card border-0 rounded-4 mb-4">
            <div class="card-body p-0">
                <!-- Header with Actions and Filters -->
                @include('admin.roles.header')

                <!-- Counters -->
                @include('admin.roles.counters')

                <!-- Data Table -->
                @include('admin.roles.roles_table')
            </div>
        </div>
    </div>

    <!-- Role Detail Canvas -->
    @include('admin.roles.detail_canvas')

    <!-- Add/Edit Role Canvas -->
    @include('admin.roles.add_role_canvas')

    <!-- Delete Confirmation Modal -->
    @include('admin.roles.delete-modal')
@endsection

@push('scripts')
    <!-- jQuery (required for DataTables) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <!-- DataTables Buttons Extension -->
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
    <!-- Common Helper Functions -->
    <script src="{{ asset('js/helpers.js') }}"></script>
    <!-- Roles Module JavaScript -->
    <script src="{{ asset('js/roles.js') }}"></script>
@endpush

