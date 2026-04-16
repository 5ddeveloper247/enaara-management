@extends('layouts.app')

@section('title', 'Employee Registeration - Admin Panel')

@section('page-title', 'Employee Registeration')

@push('styles')
    @include('admin.employeeregisteration.partials.styles')
@endpush

@section('content')
    <div class="container-fluid py-2">
        @include('admin.employeeregisteration.partials.header-toolbar')

        <div class="row">
            @include('admin.employeeregisteration.partials.sidebar-summary')

            <div class="col-12 col-xl-9">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    @include('admin.employeeregisteration.partials.wizard-tabs')

                    <div class="card-body p-3">
                        <form id="employeeForm" enctype="multipart/form-data">
                            @csrf
                            @if(isset($employee))
                                <input type="hidden" name="employee_id" id="saved_employee_id" value="{{ $employee->id }}">
                            @endif

                            @include('admin.employeeregisteration.partials.steps.step-general')
                            @include('admin.employeeregisteration.partials.steps.step-employment')
                            @include('admin.employeeregisteration.partials.steps.step-police')
                            @include('admin.employeeregisteration.partials.steps.step-armed')
                            @include('admin.employeeregisteration.partials.steps.step-bank')
                            @include('admin.employeeregisteration.partials.steps.step-more')

                            @include('admin.employeeregisteration.partials.wizard-nav')
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Cropper.js Modal -->
    @include('admin.employeeregisteration.partials.cropper-modal')

@endsection


@push('scripts')
    <script>
        window.orgsData = @json($orgsData ?? []);
        window.rolesData = @json($rolesData ?? []);
    </script>
    <!-- Cropper.js Script -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    
    <script src="{{ asset('js/employeeregisteration/wizard.js') }}"></script>
    <script src="{{ asset('js/employeeregisteration/more-details.js') }}"></script>
@endpush

