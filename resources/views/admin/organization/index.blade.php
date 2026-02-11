@extends('layouts.app')

@section('title', 'SBU - Admin Panel')

@section('page-title', 'SBU')

@push('styles')
    <!-- Organization Module CSS -->
    <link href="{{ asset('css/organization.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Top Header with Actions -->
        <div class="row align-items-center mb-3">
            <div class="col-md-6">
                <h5 class="mb-0">SBU Management</h5>
            </div>

            <div class="col-md-6 text-end">
                <button type="button" class="btn btn-outline-secondary me-2" data-bs-toggle="modal"
                    data-bs-target="#bulkPolicyModal">
                    <i class="bi bi-clipboard-data me-1"></i>Bulk Policy Update
                </button>
                <button type="button" class="btn btn-primary bg-main border-0" data-bs-toggle="offcanvas"
                    data-bs-target="#addOrganizationCanvas">
                    <i class="bi bi-building-add me-1"></i>Add New SBU
                </button>
            </div>
        </div>

        <!-- Summary Metrics Row -->
        @include('admin.organization.counters')

        <!-- Main Content Area with Sidebar Filter -->
        @include('admin.organization.organization_cards') 
    </div>

    <!-- SBU Detail Canvas -->
    @include('admin.organization.detail_canvas')

    <!-- Add SBU Canvas -->
    @include('admin.organization.add_organization_canvas')

    <!-- Bulk Policy Update Modal -->
    @include('admin.organization.bulk_policy_modal')
@endsection

@push('styles')
    <!-- ApexCharts CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.44.0/dist/apexcharts.css">
@endpush

@push('scripts')
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Common Helper Functions -->
    <script src="{{ asset('js/helpers.js') }}"></script>
    <!-- Dummy Data -->
    <script src="{{ asset('js/dummy-data.js') }}"></script>
    <!-- Organization Module JavaScript -->
    <script src="{{ asset('js/organization.js') }}"></script>
@endpush
