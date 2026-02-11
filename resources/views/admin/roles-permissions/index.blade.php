@extends('layouts.app')

@section('title', 'Roles & Permissions - Admin Panel')

@section('page-title', 'Roles & Permissions')

@push('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <!-- jsTree CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.16/themes/default/style.min.css">
    <!-- Bootstrap Switch CSS (Bootstrap 5 compatible) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap5-toggle@5.0.4/css/bootstrap5-toggle.min.css">
    <!-- Roles & Permissions Module CSS -->
    <link href="{{ asset('css/roles-permissions.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Main Card -->
        <div class="card border-0 rounded-4 mb-4">
            <div class="card-body p-0">
                <!-- Header -->
                @include('admin.roles-permissions.header')

                <!-- Roles & Permissions Layout -->
                <div class="row g-0">
                    <!-- Left Sidebar: Organizational Tree -->
                    <div class="col-lg-4 col-xl-3 border-end">
                        @include('admin.roles-permissions.roles_tree')
                    </div>

                    <!-- Right Panel: Permission Matrix -->
                    <div class="col-lg-8 col-xl-9">
                        @include('admin.roles-permissions.permissions_panel')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- jQuery (required for DataTables and jsTree) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <!-- DataTables Buttons Extension -->
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
    <!-- jsTree JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.16/jstree.min.js"></script>
    <!-- Bootstrap Switch JS (Bootstrap 5 compatible) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap5-toggle@5.0.4/js/bootstrap5-toggle.min.js"></script>
    <!-- Common Helper Functions -->
    <script src="{{ asset('js/helpers.js') }}"></script>
    <!-- Dummy Data -->
    <script src="{{ asset('js/dummy-data.js') }}"></script>
    <!-- Roles & Permissions Module JavaScript -->
    <script src="{{ asset('js/roles-permissions.js') }}"></script>
@endpush

