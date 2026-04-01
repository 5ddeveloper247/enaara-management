@extends('layouts.app')

@section('title', 'Monthly Summary - Admin Panel')

@section('page-title', 'Monthly Summary')

@push('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
<!-- Monthly Summary Module CSS -->
<link href="{{ asset('css/monthly-summary.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid">
    <!-- Main Card -->
    <div class="card border-0 rounded-4 mb-4">
        <div class="card-body p-0">
            <!-- Header with Filters -->
            @include('admin.monthly-summary.header')

            <!-- Monthly Aggregate Cards -->
            @include('admin.monthly-summary.counters')

            <!-- Data Table -->
            @include('admin.monthly-summary.monthly_summary_table')
        </div>
    </div>
</div>

<!-- Employee Monthly Detail Canvas -->
@include('admin.monthly-summary.detail_canvas')
@endsection

@push('scripts')
<!-- jQuery (required for DataTables) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<!-- DataTables Responsive Extension -->
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<!-- DataTables Buttons Extension -->
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<!-- Common Helper Functions -->
<script src="{{ asset('js/helpers.js') }}"></script>
<!-- Dummy Data -->

<!-- <script src="{{ asset('js/dummy-data.js') }}"></script> -->
<!-- Monthly Summary Module JavaScript -->
<script>
    window.monthlySummaryRows = @json($monthlySummary);
</script>
<script src="{{ asset('js/monthly-summary.js') }}"></script>
@endpush