@extends('layouts.app')

@section('title', 'Departments - Admin Panel')

@section('page-title', 'Departments')

@push('styles')
    <!-- Departments Module CSS -->
    <link href="{{ asset('css/departments.css') }}" rel="stylesheet">

    <style>
        .btn {
            font-size: 13px;
        }

        .input-group {
            border: 1px solid var(--main-color) !important;
        }

        input:focus {
            box-shadow: none !important;
            border: 1px solid var(--main-color) !important;
        }

        .card .badge {
            font-weight: 500 !important;
            padding: .3rem .8rem !important;
            border-radius: 4px !important;
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

        .dt-buttons {
            margin-top: 2px;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Top Header with Actions -->
        <div class="row align-items-center mb-3">
            <div class="col-md-6">
                <h5 class="mb-0">Department Management</h5>
            </div>

            <div class="col-md-6 text-end">
                <a href="{{ route('admin.department.add') }}" class="btn btn-primary bg-main border-0">
                    <i class="bi bi-building-add me-1"></i>Add New Department
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Summary Metrics Row -->
        @include('admin.departments.counters')

        <!-- Main Content Area with Sidebar Filter -->
        @include('admin.departments.departments_cards')
    </div>

    <!-- Department Detail Side Canvas -->
    @include('admin.departments.detail_canvas')
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var departmentCanvas = document.getElementById('departmentDetailCanvas');
            if (departmentCanvas) {
                departmentCanvas.addEventListener('show.bs.offcanvas', function(event) {
                    var button = event.relatedTarget;
                    if (button && button.classList.contains('view-department-btn')) {
                        var get = function(attr, fallback) {
                            var v = button.getAttribute(attr);
                            return (v !== null && v !== '') ? v : (fallback || '—');
                        };
                        document.getElementById('canvasDeptName').textContent = get('data-department-name');
                        document.getElementById('canvasDeptCode').textContent = 'Code: ' + get('data-department-code');
                        document.getElementById('canvasDeptOrganization').textContent = get('data-organization-name');
                        document.getElementById('canvasDeptSbu').textContent = get('data-sbu-name');
                        document.getElementById('canvasDeptParent').textContent = get('data-parent-name');
                        document.getElementById('canvasDeptDescription').textContent = get('data-description');
                        document.getElementById('canvasDeptStatus').textContent = get('data-department-status');
                    }
                });
            }
            var filterStatus = document.querySelectorAll('.filter-status');
            filterStatus.forEach(function(radio) {
                radio.addEventListener('change', function() {
                    var status = document.querySelector('input[name="filterStatus"]:checked').value;
                    document.querySelectorAll('#departmentsGrid .col-md-6').forEach(function(col) {
                        var card = col.querySelector('.department-card');
                        var cardStatus = card ? card.getAttribute('data-department-status') : '';
                        col.style.display = (status === 'all' || cardStatus === status) ? '' : 'none';
                    });
                });
            });
            var clearBtn = document.getElementById('clearFiltersBtn');
            if (clearBtn) {
                clearBtn.addEventListener('click', function() {
                    var all = document.getElementById('filterStatusAll');
                    if (all) all.checked = true;
                    document.querySelectorAll('#departmentsGrid .col-md-6').forEach(function(col) {
                        col.style.display = '';
                    });
                });
            }
        });
    </script>
@endpush
