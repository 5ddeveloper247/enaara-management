@extends('layouts.app')

@section('title', 'Shift Type - Admin Panel')

@section('page-title', 'Shift Type')

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
                @include('admin.shift-type.header')
                @include('admin.shift-type.counters')
                @include('admin.shift-type.shift_type_table')
            </div>
        </div>
    </div>
    @include('admin.shift-type.delete-modal')
    @include('admin.shift-type.detail_canvas')
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
        let shiftTypeTable;
        let itemToDelete = null;
        const csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
        const shiftTypeStatusUrl = "{{ url('/admin/shift-type') }}";
        const shiftTypeDestroyUrl = "{{ url('/admin/shift-type') }}";

        $(document).ready(function() {
            shiftTypeTable = $('#shiftTypesTable').DataTable({
                order: [[0, 'desc']],
                pageLength: 10,
                responsive: true,
                dom: '<"row align-items-center mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
                language: { emptyTable: 'No shift types found.' },
                columnDefs: [
                    { targets: 7, orderable: false, className: 'no-toggle' }
                ]
            });

            $(document).on('click', '.view-shift-type', function() {
                var btn = $(this);
                $('#detailShiftTypeName').text(btn.data('shift-type-name') || '—');
                $('#detailShiftTypeCode').text(btn.data('shift-type-code') || '—');
                $('#detailShiftTypeTime').text((btn.data('start-time') || '—') + ' – ' + (btn.data('end-time') || '—'));
                $('#detailShiftTypeBreak').text(btn.data('break-minutes') !== undefined ? btn.data('break-minutes') + ' min' : '—');
                $('#detailShiftTypeNight').text(btn.data('night-shift') === '1' ? 'Yes' : 'No');
                $('#detailShiftTypeOrganization').text(btn.data('organization-name') || '—');
                $('#detailShiftTypeStatus').text(btn.closest('tr').data('status') === 'active' ? 'Active' : 'Inactive');
                new bootstrap.Offcanvas(document.getElementById('shiftTypeDetailCanvas')).show();
            });

            $(document).on('change', '.status-toggle', function() {
                var checkbox = $(this);
                var id = checkbox.data('shift-type-id');
                var isActive = checkbox.is(':checked');
                var row = checkbox.closest('tr');
                $.ajax({
                    url: shiftTypeStatusUrl + '/' + id + '/status',
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

            const deleteModal = document.getElementById('deleteShiftTypeModal');
            if (deleteModal) {
                deleteModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    if (!button) return;
                    const name = button.getAttribute('data-shift-type-name');
                    const code = button.getAttribute('data-shift-type-code');
                    $('#deleteShiftTypeName').text(name + (code ? ' (' + code + ')' : ''));
                    itemToDelete = button.getAttribute('data-shift-type-id');
                });
            }

            $('#confirmDeleteShiftTypeBtn').on('click', function() {
                if (itemToDelete === null) return;
                var id = itemToDelete;
                itemToDelete = null;
                $.ajax({
                    url: shiftTypeDestroyUrl + '/' + id,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    success: function() {
                        shiftTypeTable.rows().every(function() {
                            if ($(this.node()).data('shift-type-id') == id) {
                                shiftTypeTable.row(this).remove().draw();
                                return false;
                            }
                        });
                        var total = parseInt($('#totalShiftTypes').text(), 10) || 0;
                        $('#totalShiftTypes').text(Math.max(0, total - 1));
                        var modalEl = document.getElementById('deleteShiftTypeModal');
                        if (modalEl) bootstrap.Modal.getInstance(modalEl).hide();
                    }
                });
            });

            $('#applyFiltersBtn').on('click', function() {
                var orgId = $('#filterOrganization').val();
                var status = $('#filterStatus').val();
                $.fn.dataTable.ext.search = [];
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    var row = shiftTypeTable.row(dataIndex).node();
                    var rowOrgId = $(row).data('organization-id').toString();
                    var rowStatus = $(row).data('status');
                    var matchOrg = !orgId || rowOrgId === orgId;
                    var matchStatus = !status || rowStatus === status;
                    return matchOrg && matchStatus;
                });
                shiftTypeTable.draw();
            });

            $('#clearFiltersBtn').on('click', function() {
                $('#filterOrganization').val('');
                $('#filterStatus').val('');
                $.fn.dataTable.ext.search = [];
                shiftTypeTable.draw();
            });
        });
    </script>
@endpush
