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

            @include('admin.employeeregisteration.partials.wizard-tabs')

                    <div class="card-body p-3">
                        @include('admin.employeeregisteration.partials.steps.step-general')
                        @include('admin.employeeregisteration.partials.steps.step-employment')
                        @include('admin.employeeregisteration.partials.steps.step-police')
                        @include('admin.employeeregisteration.partials.steps.step-armed')
                        @include('admin.employeeregisteration.partials.steps.step-bank')
                        @include('admin.employeeregisteration.partials.steps.step-more')

                        @include('admin.employeeregisteration.partials.wizard-nav')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/employeeregisteration/wizard.js') }}"></script>
    <script src="{{ asset('js/employeeregisteration/more-details.js') }}"></script>
@endpush
