@extends('layouts.app')

@section('title', 'Roles - Admin Panel')

@section('page-title', 'Roles')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link href="{{ asset('css/users.css') }}" rel="stylesheet">
    <style>
        .btn { font-size: 13px; }
        .table { --bs-table-bg: transparent !important; }
        th { padding: 1.3rem 2rem !important; color: var(--light-color) !important; white-space: nowrap !important; }
        td { padding: 1rem 2rem !important; }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="card border-0 rounded-4 mb-4">
            <div class="card-body p-0">
                @include('admin.role.header')
                @include('admin.role.counters')
                @include('admin.role.role_table')
            </div>
        </div>
    </div>
    @include('admin.role.delete-modal')
    @include('admin.role.detail_canvas')
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="{{ asset('js/helpers.js') }}"></script>
    <script>
        let roleTable;
        let itemToDelete = null;
        const csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
        const roleStatusUrl = "{{ url('/admin/role') }}";
        const roleDestroyUrl = "{{ url('/admin/role') }}";

        $(document).ready(function() {
            roleTable = $('#rolesTable').DataTable({
                order: [[1, 'asc']],
                pageLength: 10,
                responsive: true,
                dom: '<"row align-items-center mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
                language: {
                    emptyTable: 'No roles found.',
                    search: '',
                    searchPlaceholder: 'Search roles...'
                },
                columnDefs: [
                    { targets: 0, orderable: false },
                    { targets: 4, orderable: false }
                ]
            });

            const deleteModal = document.getElementById('deleteRoleModal');
            if (deleteModal) {
                deleteModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    if (!button) return;
                    itemToDelete = button.getAttribute('data-role-id');
                    $('#deleteRoleName').text(button.getAttribute('data-role-name') || '');
                });
            }

            $('#confirmDeleteRoleBtn').on('click', function() {
                if (itemToDelete === null) return;
                var id = itemToDelete;
                itemToDelete = null;
                $.ajax({
                    url: roleDestroyUrl + '/' + id,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    success: function() {
                        roleTable.rows().every(function() {
                            if ($(this.node()).data('role-id') == id) {
                                roleTable.row(this).remove().draw();
                                return false;
                            }
                        });
                        var total = parseInt($('#totalRoles').text(), 10) || 0;
                        $('#totalRoles').text(Math.max(0, total - 1));
                        var modalEl = document.getElementById('deleteRoleModal');
                        if (modalEl) bootstrap.Modal.getInstance(modalEl).hide();
                    }
                });
            });
        });
    </script>
@endpush
