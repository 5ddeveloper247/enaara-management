@extends('layouts.app')

@section('title', 'Shift Planner - Admin Panel')

@section('page-title', 'Shift Planner')

@push('styles')
    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />
    <!-- Shift Planner CSS -->
    <link href="{{ asset('css/shift-planner.css') }}" rel="stylesheet">

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

        .badge {
            font-weight: 500 !important;
        }

        .nav-pills .nav-link {
            border-radius: 0.375rem;
            color: var(--dark-color);
            font-size: 13px;
            padding: 0.5rem 1rem;
        }

        .nav-pills .nav-link.active {
            background-color: var(--main-color) !important;
            color: white !important;
        }

        .shift-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer;
        }

        /* .shift-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
        } */

        .fc-col-header-cell {
            background-color: var(--main-color) !important;
        }

        .fc-col-header-cell a {
            color: white !important;
            font-size: 13px !important;
        }

        .fc .fc-scrollgrid-section-header.fc-scrollgrid-section-sticky>* {
            top: -24px !important;
        }

        .fc .fc-daygrid-day-number {
            font-size: 13px !important;
        }

        .fc-h-event .fc-event-main {
            font-size: 10px !important;
            color: var(--dark-color) !important;
        }

        .fc-theme-standard .fc-popover-header {
            background-color: #000 !important;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Header with Tabs -->
        <div class="card border-0 rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Shift Planner</h5>
                    <div>
                        <button type="button" class="btn btn-outline-secondary me-2" id="copyScheduleBtn"
                            style="display: none;">
                            <i class="bi bi-files me-1"></i>Copy Schedule
                        </button>
                        <button type="button" class="btn btn-outline-secondary me-2" id="bulkAssignBtn"
                            style="display: none;">
                            <i class="bi bi-people-fill me-1"></i>Bulk Assign
                        </button>
                        <button type="button" class="btn btn-primary bg-main border-0" id="addShiftBtn">
                            <i class="bi bi-plus-circle me-1"></i>Add New Shift
                        </button>
                    </div>
                </div>

                <ul class="nav nav-pills" id="shiftPlannerTabs" role="tablist">
                    <li class="nav-item me-2" role="presentation">
                        <button class="nav-link active" id="shift-management-tab" data-bs-toggle="pill"
                            data-bs-target="#shift-management" type="button" role="tab">
                            <i class="bi bi-clock-history me-2"></i>Shift Management
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="roster-tab" data-bs-toggle="pill" data-bs-target="#roster"
                            type="button" role="tab">
                            <i class="bi bi-calendar-week me-2"></i>Roster / Assignment
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Tab Content -->
        <div class="tab-content" id="shiftPlannerTabsContent">
            <!-- Shift Management Tab -->
            <div class="tab-pane fade show active" id="shift-management" role="tabpanel">
                @include('admin.shift-planner.shift_management')
            </div>

            <!-- Roster Tab -->
            <div class="tab-pane fade" id="roster" role="tabpanel">
                @include('admin.shift-planner.roster')
            </div>
        </div>
    </div>

    <!-- Shift Detail Canvas -->
    @include('admin.shift-planner.shift_detail_canvas')

    <!-- Roster Shift Canvas (add/edit single assignment per cell) -->
    @include('admin.shift-planner.roster_shift_canvas')

    <!-- Add/Edit Shift Canvas -->
    @include('admin.shift-planner.add_shift_canvas')

    <!-- Bulk Assign Canvas -->
    @include('admin.shift-planner.bulk_assign_canvas')
@endsection

@push('scripts')
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- FullCalendar JS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <script src="{{ asset('js/dummy-data.js') }}"></script>
    <script src="{{ asset('js/roster-render.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Tab switching logic
            $('#shift-management-tab').on('shown.bs.tab', function() {
                $('#addShiftBtn').show();
                $('#bulkAssignBtn').hide();
            });

            $('#roster-tab').on('shown.bs.tab', function() {
                $('#addShiftBtn').hide();
                $('#bulkAssignBtn').show();
                // Initialize calendar when roster tab is shown
                setTimeout(function() {
                    if (typeof initRosterCalendar === 'function') {
                        initRosterCalendar();
                    }
                }, 100);
            });

            // Add Shift Button
            $('#addShiftBtn').on('click', function() {
                const addShiftCanvas = new bootstrap.Offcanvas(document.getElementById('addShiftCanvas'));
                addShiftCanvas.show();
            });

            // Bulk Assign Button
            $('#bulkAssignBtn').on('click', function() {
                const bulkAssignCanvas = new bootstrap.Offcanvas(document.getElementById(
                    'bulkAssignCanvas'));
                bulkAssignCanvas.show();
            });

            // Conflict Check Button
            $('#conflictCheckBtn').on('click', function() {
                checkShiftConflicts();
            });
        });

        // Conflict checking function
        function checkShiftConflicts() {
            // TODO: Implement conflict checking logic
            const conflictAlerts = document.getElementById('conflictAlerts');
            const conflictList = document.getElementById('conflictList');

            // Sample conflicts
            conflictList.innerHTML = `
                <div class="conflict-item" style="font-size: 13px !important">
                    <strong>Ahmed Ali</strong> - Assigned to Night Shift ending at 06:00, but Morning Shift starts at 09:00 (only 3 hours gap)
                </div>
                <div class="conflict-item" style="font-size: 13px !important">
                    <strong>Zainab Malik</strong> - Double assignment on March 15th (Morning Shift and Night Shift)
                </div>
            `;

            conflictAlerts.style.display = 'block';
        }
    </script>
@endpush
