@extends('layouts.app')

@section('title', 'Modules - Admin Panel')

@section('page-title', 'Modules')

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
                @include('admin.module.header')
                @include('admin.module.counters')
                @include('admin.module.module_table')
            </div>
        </div>
    </div>
    @include('admin.module.delete-modal')
    @include('admin.module.detail_canvas')
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="{{ asset('js/helpers.js') }}"></script>
    <script>
        let moduleTable;
        let itemToDelete = null;
        const csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
        const moduleStatusUrl = "{{ url('/admin/module') }}";
        const moduleDestroyUrl = "{{ url('/admin/module') }}";

        $(document).ready(function() {
            moduleTable = $('#modulesTable').DataTable({
                order: [[6, 'desc']],
                pageLength: 10,
                responsive: true,
                dom: '<"row align-items-center mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
                language: { emptyTable: 'No modules found.' },
                columnDefs: [
                    { targets: 0, orderable: false },
                    { targets: 7, orderable: false }
                ]
            });

            $(document).on('click', '.view-module', function() {
                var btn = $(this);
                $('#detailModuleName').text(btn.data('module-name') || '—');
                $('#detailCategory').text(btn.data('category-name') || '—');
                $('#detailRoute').text(btn.data('route') || '—');
                $('#detailShowInMenu').text(btn.closest('tr').data('status') === 'active' ? 'Yes' : 'No');
                $('#detailDisplayOrder').text(btn.data('display-order') ?? '—');
                $('#detailCreatedDate').text(btn.data('created-date') ?? '—');
                new bootstrap.Offcanvas(document.getElementById('moduleDetailCanvas')).show();
            });

            $(document).on('change', '.status-toggle', function() {
                var checkbox    = $(this);
                var id          = checkbox.data('module-id');
                var showInMenu  = checkbox.is(':checked');
                var row         = checkbox.closest('tr');

                // Revert until confirmed
                checkbox.prop('checked', !showInMenu);

                Swal.fire({
                    title: showInMenu ? 'Show in Menu?' : 'Hide from Menu?',
                    text:  showInMenu ? 'This module will appear in the sidebar menu.' : 'This module will be hidden from the sidebar menu.',
                    icon:  'question',
                    showCancelButton: true,
                    confirmButtonColor: showInMenu ? '#012445' : '#dc3545',
                    cancelButtonColor:  '#6c757d',
                    confirmButtonText:  showInMenu ? 'Yes, show it!' : 'Yes, hide it!',
                }).then(function(result) {
                    if (!result.isConfirmed) return;

                    checkbox.prop('checked', showInMenu);

                    $.ajax({
                        url:    moduleStatusUrl + '/' + id + '/status',
                        method: 'PATCH',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                        data:   JSON.stringify({ show_in_menu: showInMenu }),
                        success: function() {
                            row.attr('data-status', showInMenu ? 'active' : 'inactive');
                            var totalActive   = parseInt($('#totalActive').text(),   10) || 0;
                            var totalInactive = parseInt($('#totalInactive').text(), 10) || 0;
                            if (showInMenu) { totalActive += 1; totalInactive -= 1; }
                            else            { totalActive -= 1; totalInactive += 1; }
                            $('#totalActive').text(Math.max(0, totalActive));
                            $('#totalInactive').text(Math.max(0, totalInactive));

                            showSuccess(showInMenu ? 'Module will now appear in the sidebar.' : 'Module is now hidden from the sidebar.', showInMenu ? 'Shown in Menu!' : 'Hidden from Menu!');
                        },
                        error: function() {
                            checkbox.prop('checked', !showInMenu);
                            showError('Status update failed. Please try again.');
                        }
                    });
                });
            });

            const deleteModal = document.getElementById('deleteModuleModal');
            if (deleteModal) {
                deleteModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    if (!button) return;
                    itemToDelete = button.getAttribute('data-module-id');
                    $('#deleteModuleName').text(button.getAttribute('data-module-name') || '');
                });
            }

            $('#confirmDeleteModuleBtn').on('click', function() {
                if (itemToDelete === null) return;
                var id = itemToDelete;
                itemToDelete = null;
                $.ajax({
                    url: moduleDestroyUrl + '/' + id,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    success: function() {
                        moduleTable.rows().every(function() {
                            if ($(this.node()).data('module-id') == id) {
                                moduleTable.row(this).remove().draw();
                                return false;
                            }
                        });
                        var total = parseInt($('#totalModules').text(), 10) || 0;
                        $('#totalModules').text(Math.max(0, total - 1));
                        var modalEl = document.getElementById('deleteModuleModal');
                        if (modalEl) bootstrap.Modal.getInstance(modalEl).hide();
                    }
                });
            });
        });
    </script>
@endpush
