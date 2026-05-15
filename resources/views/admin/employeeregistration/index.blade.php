@extends('layouts.app')

@section('title', 'Employee Registeration - Admin Panel')

@section('page-title', 'Employee Registeration')

@push('styles')
    @include('admin.employeeregistration.partials.styles')
@endpush

@section('content')
    <div class="container-fluid py-2">
        @include('admin.employeeregistration.partials.header-toolbar')

        <div class="row">
            {{-- @include('admin.employeeregistration.partials.sidebar-summary') --}}

            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    @include('admin.employeeregistration.partials.wizard-tabs')

                    <div class="card-body p-3">
                        <form id="employeeForm" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="employee_id" id="saved_employee_id" value="{{ $employee->id ?? '' }}">

                            @include('admin.employeeregistration.partials.steps.step-general')
                            @include('admin.employeeregistration.partials.steps.step-employment')
                            @include('admin.employeeregistration.partials.steps.step-police')
                            @include('admin.employeeregistration.partials.steps.step-armed')
                            @include('admin.employeeregistration.partials.steps.step-bank')
                            @include('admin.employeeregistration.partials.steps.step-more')

                            @include('admin.employeeregistration.partials.wizard-nav')
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Cropper.js Modal -->
    @include('admin.employeeregistration.partials.cropper-modal')
    @include('admin.employeeregistration.partials.attachement_model')

@endsection


@push('scripts')
    <script>
        window.orgsData = @json($orgsData ?? []);
        window.rolesData = @json($rolesData ?? []);
        window.editData = {
            bankDetails: @json($employee?->bankDetails ?? []),
            familyMembers: @json($employee?->familyMembers ?? []),
            academics: @json($employee?->academics ?? []),
            exEmployments: @json($employee?->exEmployments ?? []),
            attachments: @json($editData['attachments'] ?? [])
        };
        window.isEditMode = @json(isset($employee));
        window.employeeId = @json(optional($employee)->id);
        window.employeeDesignationId = @json(optional($employee)->designation_id);
        window.previewEmployeeCodeUrl = @json(route('admin.employee.preview_code'));
        window.employeeDesignationsUrl = @json(route('admin.employee.designations_for_employment'));
        window.universitiesDirectoryUrl = @json(route('admin.employee.universities'));
        window.employeeAttachmentsFetchUrl = @json(isset($employee) && $employee?->id ? url('/admin/employees/' . $employee->id . '/attachments') : null);
        window.saveAttachmentUrl = @json(route('admin.employee.save_attachment'));
        if (typeof window.setExistingAttachments === 'function') {
            window.setExistingAttachments(window.editData.attachments || []);
        }
    </script>
    <!-- Cropper.js Script -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    
    <script src="{{ asset('js/employeeregistration/wizard.js') }}?v={{ filemtime(public_path('js/employeeregistration/wizard.js')) }}"></script>
    <script src="{{ asset('js/employeeregistration/attachments-dynamic.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/employeeregistration/family-attachments.js') }}?v={{ time() }}"></script>
@endpush

