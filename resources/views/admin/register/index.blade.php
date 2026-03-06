@extends('layouts.app')

@section('title', 'Roles - Admin Panel')

@section('page-title', 'Roles')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link href="{{ asset('css/users.css') }}" rel="stylesheet">
    <style>
        .btn {
            font-size: 13px;
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


        .step-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        form input,
        textarea,
        select,
        option {
            background: transparent !important;
            border: 1px solid #012445;
            box-shadow: 0 0 4px 2px #5a59593d;
        }

        .connector {
            height: 3px;
            flex: 1;
        }

        .step-content {
            min-height: 220px;
        }

        .step-circle {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .8rem;
            font-weight: 600;
        }

        .connector {
            height: 3px;
            flex: 1;
            margin-bottom: 18px;
        }

        .section-title {
            font-size: 1rem;
            font-weight: 600;
            color: #000;
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
            margin-bottom: 20px;
            display: none;
        }

        .step {
            display: none;
        }

        .step.active {
            display: block;
        }

        .check-input {
            box-shadow: none;
        }
    </style>
@endpush

@section('content')
    {{-- <div class="container-fluid">
        <div class="card border-0 rounded-4 mb-4">
            <div class="card-body p-0">
                @include('admin.role.header')
                @include('admin.role.counters')
                @include('admin.role.role_table')
            </div>
        </div>
    </div>
    @include('admin.role.delete-modal')
    @include('admin.role.detail_canvas') --}}


    <div class="container">
        <div class="d-flex justify-content-between mb-4 align-items-center">
            <h5 class="text-center">Employee Information Form</h5>
            <button class="btn btn-primary ms-auto" id="attachment">Attachment</button>
        </div>
        <div class="card shadow-sm p-4">

            @include('admin.register.header')

            <form>

                @include('admin.register.general_info')
                @include('admin.register.personal_info')
                @include('admin.register.ex_employment')
                @include('admin.register.contact')
                @include('admin.register.bankdetails')
                @include('admin.register.familydetails')
                @include('admin.register.academic')
            </form>

            {{-- Navigation --}}
            <div class="d-flex justify-content-between mt-4">
                <button class="btn btn-outline-secondary" id="prevBtn" onclick="changeStep(-1)"
                    style="display:none">Back</button>
                <button class="btn btn-primary ms-auto" id="nextBtn" onclick="changeStep(1)">Next</button>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <script>
        let current = 1;
        const total = 7;

        function changeStep(dir) {
            document.getElementById('step-' + current).classList.remove('active');
            updateCircle(current, dir === 1 ? 'done' : 'pending');

            current += dir;

            document.getElementById('step-' + current).classList.add('active');
            updateCircle(current, 'active');

            document.getElementById('prevBtn').style.display = current === 1 ? 'none' : 'inline-block';
            const nextBtn = document.getElementById('nextBtn');
            nextBtn.textContent = current === total ? 'Submit' : 'Next';
            nextBtn.className = current === total ? 'btn btn-success ms-auto' : 'btn btn-primary ms-auto';

            if (current === total && dir === 1 && document.getElementById('nextBtn').textContent === 'Submit') {
                document.getElementById('nextBtn').onclick = function() {
                    alert('Form submitted!');
                };
            } else {
                document.getElementById('nextBtn').onclick = function() {
                    changeStep(1);
                };
            }
        }

        function updateCircle(step, state) {
            const el = document.getElementById('circle-' + step);
            const con = document.getElementById('con-' + step);
            el.className = 'step-circle ';
            if (state === 'done') {
                el.className += 'bg-success text-white';
                el.textContent = '✓';
                if (con) con.className = 'connector bg-success';
            } else if (state === 'active') {
                el.className += 'bg-primary text-white';
                el.textContent = step;
            } else {
                el.className += 'bg-light border text-muted';
                el.textContent = step;
                if (con) con.className = 'connector bg-secondary bg-opacity-25';
            }
        }

        function addFamilyRow() {
            const tbody = document.getElementById('familyTable');
            const count = tbody.rows.length + 1;
            tbody.insertAdjacentHTML('beforeend', `
            <tr>
                <td>${count}</td>
                <td><input type="text" class="form-control form-control-sm"></td>
                <td><select class="form-select form-select-sm"><option value="">Select</option><option>Male</option><option>Female</option></select></td>
                <td><input type="date" class="form-control form-control-sm"></td>
                <td><input type="text" class="form-control form-control-sm"></td>
                <td><input type="text" class="form-control form-control-sm"></td>
                <td> <button type="button"
                            class="action-btn border-0 text-danger bg-danger-subtle delete-shift-type"
                            onclick="removeRow(this)" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button></td>
            </tr>`);
        }

        function addAcademicRow() {
            const tbody = document.getElementById('academicTable');
            const count = tbody.rows.length + 1;
            tbody.insertAdjacentHTML('beforeend', `
            <tr>
                <td>${count}</td>
                <td><input type="text" class="form-control form-control-sm"></td>
                <td><input type="text" class="form-control form-control-sm"></td>
                <td><input type="date" class="form-control form-control-sm"></td>
                <td><input type="date" class="form-control form-control-sm"></td>
                <td><input type="text" class="form-control form-control-sm"></td>
                <td><input type="text" class="form-control form-control-sm"></td>
                <td> <button type="button"
                            class="action-btn border-0 text-danger bg-danger-subtle delete-shift-type"
                            onclick="removeRow(this)" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button></td>
            </tr>`);
        }

        function removeRow(btn) {
            btn.closest('tr').remove();
        }
    </script>
@endpush
