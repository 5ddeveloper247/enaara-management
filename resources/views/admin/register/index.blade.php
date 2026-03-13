@extends('layouts.app')

@section('title', 'Register - Admin Panel')

@section('page-title', 'Register')

@push('styles')
    <link href="{{ asset('css/users.css') }}" rel="stylesheet">
    <style>
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

        form input,
        textarea,
        select,
        option {
            background: transparent !important;
            border: 2px solid #012445;
            box-shadow: 0 0 7px 4px #5a59593d;
        }

        select {
            border: white !important;
        }


        .section-title {
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
    @include('admin.role.detail_canvas') --}}

    @include('admin.register.attachment-modal')

    <div class="container">
        <div class="d-flex justify-content-between mb-4 align-items-center">
            <h5 class="text-center">Employee Information Form</h5>
            <div class="d-flex gap-3 align-items-center">
                <a href="{{ route('admin.employee.index') }}" class="btn btn-secondary d-flex align-items-center border-0 px-3 ms-auto">
                    Go Back
                </a>
                <button
                    class=" btn btn-link text-decoration-none text-white bg-main d-flex align-items-center border-0 px-3 ms-auto"
                    data-bs-toggle="modal" data-bs-target="#attachmentModal">
                    Attachment
                </button>
            </div>
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
                <button
                    class="btn ms-auto text-decoration-none text-white bg-main rounded-2 d-flex align-items-center border-0 px-3"
                    id="nextBtn" onclick="changeStep(1)">Next</button>
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
        const icons = [
            'bi-person-fill', 'bi-shield-fill', 'bi-award-fill',
            'bi-telephone-fill', 'bi-bank2', 'bi-people-fill', 'bi-plus'
        ];

        function changeStep(dir) {
            goToStep(current + dir);
        }

        function goToStep(target) {
            if (target < 1 || target > total) return;

            document.getElementById('step-' + current).classList.remove('active');

            // Sync all step states relative to target
            for (let i = 1; i <= total; i++) {
                if (i < target) updateCircle(i, 'done');
                else if (i === target) updateCircle(i, 'active');
                else updateCircle(i, 'pending');
            }

            current = target;
            document.getElementById('step-' + current).classList.add('active');

            document.getElementById('prevBtn').style.display = current === 1 ? 'none' : 'inline-block';

            const nextBtn = document.getElementById('nextBtn');
            nextBtn.textContent = current === total ? 'Submit' : 'Next';
            nextBtn.className = current === total ? 'btn btn-success ms-auto' :
                'btn ms-auto text-decoration-none text-white bg-main rounded-2 d-flex align-items-center border-0 px-3';
            nextBtn.onclick = current === total ?
                () => alert('Form submitted!') :
                () => changeStep(1);
        }

        function updateCircle(step, state) {
            const pill = document.getElementById('step-pill-' + step);
            const icon = document.getElementById('circle-' + step);
            const con = document.getElementById('con-' + step);

            pill.classList.remove('is-active', 'is-done');

            if (state === 'done') {
                pill.classList.add('is-done');
                icon.innerHTML = '<i class="bi bi-check-lg"></i>';
                if (con) con.classList.add('is-done');
            } else if (state === 'active') {
                pill.classList.add('is-active');
                icon.innerHTML = `<i class="bi ${icons[step - 1]}"></i>`;
            } else {
                icon.innerHTML = `<i class="bi ${icons[step - 1]}"></i>`;
                if (con) con.classList.remove('is-done');
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
                 <td class="d-flex gap-1">
                        <button type="button" class="action-btn border-0 text-success bg-success-subtle"
                            onclick="saveFamilyRow(this)" title="Save">
                            <i class="bi bi-floppy"></i>
                        </button>
                        <button type="button"
                            class="action-btn border-0 text-danger bg-danger-subtle delete-shift-type"
                            onclick="removeRow(this)" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
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
               <td class="d-flex gap-1">
    <button type="button" class="action-btn border-0 text-success bg-success-subtle"
        onclick="saveAcademicRow(this)" title="Save">
        <i class="bi bi-floppy"></i>
    </button>
    <button type="button" class="action-btn border-0 text-danger bg-danger-subtle delete-shift-type"
        onclick="removeRow(this)" title="Delete">
        <i class="bi bi-trash"></i>
    </button>
</td>
            </tr>`);
        }

        function removeRow(btn) {
            btn.closest('tr').remove();
        }
    </script>
@endpush
