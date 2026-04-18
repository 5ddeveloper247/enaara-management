@extends('layouts.app')

@section('title', 'Policy Management - Admin Panel')

@section('page-title', 'Policy Management')

@push('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <!-- Policies Module CSS -->
    <link href="{{ asset('css/policies.css') }}" rel="stylesheet">

    <style>
        .btn {
            font-size: 13px;
        }

        .input-group {
            border: 1px solid var(--main-color) !important;
        }

        input:focus, select:focus, textarea:focus {
            box-shadow: none !important;
            border: 1px solid var(--main-color) !important;
        }

        .table {
            --bs-table-bg: transparent !important;
        }

        th {
            padding: 1.3rem 2rem !important;
            color: var(--light-color) !important;
            white-space: nowrap !important;
        }

        td {
            padding: 1rem 2rem !important;
        }

        .dt-control {
            padding-left: 5px !important;
            padding-right: 0 !important;
        }

        .dt-buttons {
            margin-top: 2px;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Main Card -->
        <div class="card border-0 rounded-4 mb-4">
            <div class="card-body p-0">
                <!-- Header with Filters -->
                @include('admin.policies.header')

                <!-- Policy Summary Cards -->
                @include('admin.policies.counters')

                <!-- Data Table -->
                @include('admin.policies.policies_table')
            </div>
        </div>
    </div>

    <!-- Create/Edit Policy Modal -->
    @include('admin.policies.create_modal')

    <!-- Policy Detail Canvas -->
    @include('admin.policies.detail_modal')
@endsection

@push('scripts')
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <!-- DataTables Responsive Extension -->
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <!-- DataTables Buttons Extension -->
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
    <!-- Common Helper Functions -->
    <script src="{{ asset('js/helpers.js') }}"></script>
    <script>
        // Dynamic policies data from backend
        const dbPolicies = @json($policies ?? []);
        window.policyScopeTree = @json($policyScopeTree ?? []);
    </script>
    <!-- Policies Module JavaScript -->
    <script src="{{ asset('js/policies.js') }}"></script>
@endpush

