@extends('layouts.app')

@section('title', 'Module Categories - Admin Panel')

@section('page-title', 'Module Categories')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <link href="{{ asset('css/users.css') }}" rel="stylesheet">
    <style>
        .btn { font-size: 13px; }
        .input-group { border: 1px solid var(--main-color) !important; }
        input:focus { box-shadow: none !important; border: 1px solid var(--main-color) !important; }
        .badge { font-weight: 500 !important; }
        .table { --bs-table-bg: transparent !important; }
        th { padding: 1.3rem 2rem !important; color: var(--light-color) !important; white-space: nowrap !important; }
        td { padding: 1rem 2rem !important; }
        .dt-buttons { margin-top: 2px; }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="card border-0 rounded-4 mb-4">
            <div class="card-body p-0">
                @include('admin.module-categories.header')
                @include('admin.module-categories.counters')
                @include('admin.module-categories.module_categories_table')
            </div>
        </div>
    </div>
    @include('admin.module-categories.delete-modal')
    @include('admin.module-categories.detail_canvas')
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
    <script src="{{ asset('js/helpers.js') }}"></script>
    <script>
        let moduleCategoryTable;
        let itemToDelete = null;
        const csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
        const moduleCategoryStatusUrl = "{{ url('/admin/module-categories') }}";
        const moduleCategoryDestroyUrl = "{{ url('/admin/module-categories') }}";

        $(document).ready(function() {
            moduleCategoryTable = $('#moduleCategoriesTable').DataTable({
                order: [[5, 'desc']],
                pageLength: 10,
                responsive: true,
                dom: '<"row align-items-center mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
                language: { emptyTable: 'No module categories found.' },
                columnDefs: [
                    { targets: 0, orderable: false },
                    { targets: 6, orderable: false, className: 'no-toggle' }
                ]
            });

            $(document).on('click', '.view-module-category', function() {
                var btn = $(this);
                $('#detailCategoryName').text(btn.data('category-name') || '—');
                $('#detailModulesCount').text(btn.data('modules-count') ?? '—');
                $('#detailDisplayOrder').text(btn.data('display-order') ?? '—');
                $('#detailStatus').text(btn.closest('tr').data('status') === 'active' ? 'Active' : 'Inactive');
                $('#detailCreatedDate').text(btn.data('created-date') ?? '—');
                new bootstrap.Offcanvas(document.getElementById('moduleCategoryDetailCanvas')).show();
            });

            $(document).on('change', '.status-toggle', function() {
                var checkbox = $(this);
                var id = checkbox.data('module-category-id');
                var isActive = checkbox.is(':checked');
                var row = checkbox.closest('tr');
                $.ajax({
                    url: moduleCategoryStatusUrl + '/' + id + '/status',
                    method: 'PATCH',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    data: JSON.stringify({ is_active: isActive }),
                    success: function() {
                        row.attr('data-status', isActive ? 'active' : 'inactive');
                        var totalActive = parseInt($('#totalActive').text(), 10) || 0;
                        var totalInactive = parseInt($('#totalInactive').text(), 10) || 0;
                        if (isActive) { totalActive += 1; totalInactive -= 1; } else { totalActive -= 1; totalInactive += 1; }
                        $('#totalActive').text(Math.max(0, totalActive));
                        $('#totalInactive').text(Math.max(0, totalInactive));
                    },
                    error: function() {
                        checkbox.prop('checked', !isActive);
                    }
                });
            });

            const deleteModal = document.getElementById('deleteModuleCategoryModal');
            if (deleteModal) {
                deleteModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    if (!button) return;
                    const id = button.getAttribute('data-module-category-id');
                    const name = button.getAttribute('data-module-category-name');
                    $('#deleteModuleCategoryName').text(name || '');
                    itemToDelete = id;
                });
            }

            $('#confirmDeleteModuleCategoryBtn').on('click', function() {
                if (itemToDelete === null) return;
                var id = itemToDelete;
                itemToDelete = null;
                $.ajax({
                    url: moduleCategoryDestroyUrl + '/' + id,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    success: function() {
                        moduleCategoryTable.rows().every(function() {
                            if ($(this.node()).data('module-category-id') == id) {
                                moduleCategoryTable.row(this).remove().draw();
                                return false;
                            }
                        });
                        var total = parseInt($('#totalModuleCategories').text(), 10) || 0;
                        $('#totalModuleCategories').text(Math.max(0, total - 1));
                        var modalEl = document.getElementById('deleteModuleCategoryModal');
                        if (modalEl) bootstrap.Modal.getInstance(modalEl).hide();
                    }
                });
            });
        });
    </script>
@endpush
