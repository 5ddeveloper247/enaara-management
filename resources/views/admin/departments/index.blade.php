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
                <button type="button" class="btn btn-outline-secondary me-2" data-bs-toggle="modal"
                    data-bs-target="#transferEmployeesModal">
                    <i class="bi bi-arrow-left-right me-1"></i>Transfer Employees
                </button>
                <button type="button" class="btn btn-outline-secondary me-2" data-bs-toggle="modal"
                    data-bs-target="#bulkPolicyModal">
                    <i class="bi bi-clipboard-data me-1"></i>Bulk Policy Update
                </button>
                <button type="button" class="btn btn-primary bg-main border-0" data-bs-toggle="modal"
                    data-bs-target="#addDepartmentModal">
                    <i class="bi bi-building-add me-1"></i>Add New Department
                </button>
            </div>
        </div>

        <!-- Summary Metrics Row -->
        @include('admin.departments.counters')

        <!-- Main Content Area with Sidebar Filter -->
        @include('admin.departments.departments_cards') 
    </div>

    <!-- Department Detail Side Canvas -->
    @include('admin.departments.detail_canvas')

    <!-- Add Department Modal -->
    @include('admin.departments.add_department_modal')

    <!-- Transfer Employees Modal -->
    @include('admin.departments.transfer_modal')

    <!-- Bulk Policy Update Modal -->
    @include('admin.departments.bulk_policy_modal')
@endsection

@push('styles')
    <!-- ApexCharts CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.44.0/dist/apexcharts.css">
@endpush

@push('scripts')
    <!-- ApexCharts JS -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.44.0/dist/apexcharts.min.js"></script>
    
    <script>
        // Fix modal z-index issues by moving modals to body level
        document.addEventListener('DOMContentLoaded', function() {
            // Move all modals to body level to avoid stacking context issues
            const modals = document.querySelectorAll('.modal');
            modals.forEach(function(modal) {
                if (modal.parentElement !== document.body) {
                    document.body.appendChild(modal);
                }
            });

            // Fix z-index when modals are shown
            modals.forEach(function(modal) {
                modal.addEventListener('show.bs.modal', function() {
                    // Move to body if not already there
                    if (this.parentElement !== document.body) {
                        document.body.appendChild(this);
                    }
                });

                modal.addEventListener('shown.bs.modal', function() {
                    // Force z-index values
                    this.style.zIndex = '9999';
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) {
                        backdrop.style.zIndex = '9998';
                    }
                    const modalDialog = this.querySelector('.modal-dialog');
                    if (modalDialog) {
                        modalDialog.style.zIndex = '10000';
                    }
                });
            });
        });

        // ApexCharts Heatmap for Department Attendance
        let attendanceHeatmapChart = null;

        function initAttendanceHeatmap(departmentData) {
            const heatmapElement = document.getElementById('attendanceHeatmapChart');
            if (!heatmapElement) return;

            // Destroy existing chart if any
            if (attendanceHeatmapChart) {
                attendanceHeatmapChart.destroy();
            }

            // Sample data for 4 weeks (28 days)
            // ApexCharts heatmap data format: {name: 'Week X', data: [{x: 'Day', y: value}]}
            const heatmapData = departmentData || generateSampleHeatmapData();

            const options = {
                series: heatmapData,
                chart: {
                    type: 'heatmap',
                    height: 200,
                    toolbar: {
                        show: false
                    },
                    fontFamily: 'Roboto Flex, sans-serif'
                },
                plotOptions: {
                    heatmap: {
                        shadeIntensity: 0.5,
                        radius: 4,
                        colorScale: {
                            ranges: [{
                                from: 0,
                                to: 50,
                                name: 'Low',
                                color: '#dc3545' // Red/Danger
                            }, {
                                from: 51,
                                to: 75,
                                name: 'Medium',
                                color: '#ffc107' // Yellow/Warning
                            }, {
                                from: 76,
                                to: 100,
                                name: 'High',
                                color: '#198754' // Green/Success
                            }]
                        }
                    }
                },
                dataLabels: {
                    enabled: true,
                    style: {
                        fontSize: '10px',
                        fontWeight: 600,
                        colors: ['#fff']
                    },
                    formatter: function(val) {
                        return val + '%';
                    }
                },
                xaxis: {
                    type: 'category',
                    categories: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    labels: {
                        style: {
                            fontSize: '11px'
                        }
                    }
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return val + '% Attendance';
                        }
                    }
                },
                grid: {
                    padding: {
                        right: 20,
                        bottom: 10
                    }
                },
                legend: {
                    show: false
                }
            };

            attendanceHeatmapChart = new ApexCharts(heatmapElement, options);
            attendanceHeatmapChart.render();
        }

        // Generate sample heatmap data for 4 weeks
        function generateSampleHeatmapData() {
            const weeks = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
            const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            
            return weeks.map((week, weekIndex) => {
                const weekData = days.map((day, dayIndex) => {
                    let value;
                    if (dayIndex < 5) {
                        // Weekdays: 80-95% attendance
                        value = Math.floor(Math.random() * 15) + 80;
                    } else {
                        // Weekends: 20-60% attendance
                        value = Math.floor(Math.random() * 40) + 20;
                    }
                    return {
                        x: day,
                        y: value
                    };
                });
                
                return {
                    name: week,
                    data: weekData
                };
            });
        }

        // Initialize heatmap when offcanvas is shown
        const departmentCanvas = document.getElementById('departmentDetailCanvas');
        if (departmentCanvas) {
            departmentCanvas.addEventListener('show.bs.offcanvas', function() {
                // Wait a bit for the offcanvas to fully render
                setTimeout(function() {
                    initAttendanceHeatmap();
                }, 100);
            });

            departmentCanvas.addEventListener('hidden.bs.offcanvas', function() {
                // Destroy chart when offcanvas is hidden
                if (attendanceHeatmapChart) {
                    attendanceHeatmapChart.destroy();
                    attendanceHeatmapChart = null;
                }
            });
        }

        // Department management scripts will go here
    </script>
@endpush
