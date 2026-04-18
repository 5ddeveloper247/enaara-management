@extends('layouts.app')

@section('title', 'Workflows - Admin Panel')

@section('page-title', 'Workflows')

@push('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <!-- Workflows Module CSS -->
    <link href="{{ asset('css/workflows.css') }}" rel="stylesheet">

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
                @include('admin.workflows.header')

                <!-- Workflow Summary Cards -->
                @include('admin.workflows.counters')

                <!-- Data Table -->
                @include('admin.workflows.workflows_table')
            </div>
        </div>
    </div>

    <!-- Create/Edit Workflow Canvas -->
    @include('admin.workflows.create_modal')

    <!-- Workflow Detail Canvas -->
    @include('admin.workflows.detail_canvas')

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteWorkflowModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-trash me-2 text-danger"></i>Delete Workflow</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete workflow <strong id="deleteWorkflowName"></strong>? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>
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
    <!-- URL Variables for JS -->
    <script>
        window.workflowsDataUrl   = "{{ route('admin.workflows.data') }}";
        window.workflowsStatsUrl  = "{{ route('admin.workflows.stats') }}";
        window.workflowsStoreUrl  = "{{ route('admin.workflows.store') }}";
        window.workflowsUpdateUrl = "{{ url('admin/workflows/:id/update') }}";
        window.workflowsStatusUrl = "{{ url('admin/workflows/:id/status') }}";
        window.workflowsDeleteUrl = "{{ url('admin/workflows/:id/delete') }}";
        window.csrfToken          = "{{ csrf_token() }}";
        window.workflowScopeTree = @json($workflowScopeTree ?? []);
        window.workflowRoleNames = @json($roleNames ?? []);
    </script>
    <!-- Workflows Module JavaScript -->
    <script src="{{ asset('js/workflows.js') }}"></script>
@endpush
