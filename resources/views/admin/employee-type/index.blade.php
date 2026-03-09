@extends('layouts.app')

@section('title', 'Employee Type - Admin Panel')

@section('page-title', 'Employee Type')

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
                @include('admin.employee-type.header')
                @include('admin.employee-type.counters')
                @include('admin.employee-type.employee_type_table')
            </div>
        </div>
    </div>
    @include('admin.employee-type.delete-modal')
    @include('admin.employee-type.detail_canvas')
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
        let employeeTypeTable;
        let itemToDelete = null;
        const csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
        const employeeTypeStatusUrl = "{{ url('/admin/employee-type') }}";
        const employeeTypeDestroyUrl = "{{ url('/admin/employee-type') }}";

        $(document).ready(function() {
            employeeTypeTable = $('#employeeTypesTable').DataTable({
                order: [[0, 'desc']],
                pageLength: 10,
                responsive: true,
                dom: '<"row align-items-center mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
                language: { emptyTable: 'No employee types found.' },
                columnDefs: [
                    { targets: 5, orderable: false, className: 'no-toggle' }
                ]
            });

            $(document).on('click', '.view-employee-type', function() {
                var btn = $(this);
                $('#detailEmployeeTypeName').text(btn.data('employee-type-name') || '—');
                $('#detailEmployeeTypeCode').text(btn.data('employee-type-code') || '—');
                $('#detailEmployeeTypeOrganization').text(btn.data('organization-name') || '—');
                $('#detailEmployeeTypeDepartment').text(btn.data('department-name') || '—');
                $('#detailEmployeeTypeStatus').text(btn.closest('tr').data('status') === 'active' ? 'Active' : 'Inactive');
                new bootstrap.Offcanvas(document.getElementById('employeeTypeDetailCanvas')).show();
            });

            $(document).on('change', '.status-toggle', function() {
                var checkbox = $(this);
                var id = checkbox.data('employee-type-id');
                var isActive = checkbox.is(':checked');
                var row = checkbox.closest('tr');
                $.ajax({
                    url: employeeTypeStatusUrl + '/' + id + '/status',
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

            const deleteModal = document.getElementById('deleteEmployeeTypeModal');
            if (deleteModal) {
                deleteModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    if (!button) return;
                    const id = button.getAttribute('data-employee-type-id');
                    const name = button.getAttribute('data-employee-type-name');
                    const code = button.getAttribute('data-employee-type-code');
                    $('#deleteEmployeeTypeName').text(name + (code ? ' (' + code + ')' : ''));
                    itemToDelete = id;
                });
            }

            $('#confirmDeleteEmployeeTypeBtn').on('click', function() {
                if (itemToDelete === null) return;
                var id = itemToDelete;
                itemToDelete = null;
                $.ajax({
                    url: employeeTypeDestroyUrl + '/' + id,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    success: function() {
                        employeeTypeTable.rows().every(function() {
                            if ($(this.node()).data('employee-type-id') == id) {
                                employeeTypeTable.row(this).remove().draw();
                                return false;
                            }
                        });
                        var total = parseInt($('#totalEmployeeTypes').text(), 10) || 0;
                        $('#totalEmployeeTypes').text(Math.max(0, total - 1));
                        var modalEl = document.getElementById('deleteEmployeeTypeModal');
                        if (modalEl) bootstrap.Modal.getInstance(modalEl).hide();
                    }
                });
            });

            $('#applyFiltersBtn').on('click', function() {
                var orgId = $('#filterOrganization').val();
                var status = $('#filterStatus').val();
                $.fn.dataTable.ext.search = [];
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    var row = employeeTypeTable.row(dataIndex).node();
                    var rowOrgId = $(row).data('organization-id').toString();
                    var rowStatus = $(row).data('status');
                    var matchOrg = !orgId || rowOrgId === orgId;
                    var matchStatus = !status || rowStatus === status;
                    return matchOrg && matchStatus;
                });
                employeeTypeTable.draw();
            });

            $('#clearFiltersBtn').on('click', function() {
                $('#filterOrganization').val('');
                $('#filterStatus').val('');
                $.fn.dataTable.ext.search = [];
                employeeTypeTable.draw();
            });
        });
    </script>
@endpush
