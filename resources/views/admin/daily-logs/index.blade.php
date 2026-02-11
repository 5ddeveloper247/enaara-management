@extends('layouts.app')

@section('title', 'Daily Logs - Admin Panel')

@section('page-title', 'Daily Logs')

@push('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <!-- Daily Logs Module CSS -->
    <link href="{{ asset('css/daily-logs.css') }}" rel="stylesheet">
    <!-- Leaflet CSS for Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

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
        <!-- Top Header -->
        <div class="card border-0 rounded-4 mb-4">
            <div class="card-body p-0">
                <div class="row align-items-center p-4">
                    <div class="col-md-6">
                        <h5 class="mb-0">Daily Logs</h5>
                    </div>
                    
                    <div class="col-md-6 text-end">
                        <button type="button" class="btn btn-outline-secondary me-2" id="exportBtn">
                            <i class="bi bi-download me-1"></i>Export
                        </button>
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-secondary dropdown-toggle"
                                data-bs-toggle="dropdown" aria-expanded="false" id="filterDropdownBtn">
                                <i class="bi bi-funnel me-1"></i>Filter
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end p-3" style="min-width: 320px;">
                                <!-- Date Range -->
                                <li>
                                    <div class="mb-3">
                                        <label class="form-label small text-muted mb-1">Date Range</label>
                                        <div class="input-group input-group-sm">
                                            <input type="date" class="form-control" id="filterDateFrom" value="{{ date('Y-m-d') }}">
                                            <span class="input-group-text">to</span>
                                            <input type="date" class="form-control" id="filterDateTo" value="{{ date('Y-m-d') }}">
                                        </div>
                                    </div>
                                </li>
                                <!-- Quick Date Filters -->
                                <li>
                                    <div class="mb-3">
                                        <label class="form-label small text-muted mb-1">Quick Filter</label>
                                        <div class="btn-group w-100" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary active" data-quick-date="today">Today</button>
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-quick-date="yesterday">Yesterday</button>
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-quick-date="week">Week</button>
                                        </div>
                                    </div>
                                </li>
                                <!-- Department Filter -->
                                <li>
                                    <div class="mb-3">
                                        <label class="form-label small text-muted mb-1">Department</label>
                                        <select class="form-select form-select-sm" id="filterDepartment">
                                            <option value="">All Departments</option>
                                            <option value="Sales">Sales</option>
                                            <option value="IT">IT</option>
                                            <option value="HR">HR</option>
                                            <option value="Operations">Operations</option>
                                            <option value="Finance">Finance</option>
                                            <option value="Legal">Legal</option>
                                        </select>
                                    </div>
                                </li>
                                <!-- Status Filter -->
                                <li>
                                    <div class="mb-3">
                                        <label class="form-label small text-muted mb-1">Status</label>
                                        <select class="form-select form-select-sm" id="filterStatus">
                                            <option value="">All Status</option>
                                            <option value="On-Time">On-Time</option>
                                            <option value="Late">Late</option>
                                            <option value="Early">Early Departure</option>
                                        </select>
                                    </div>
                                </li>
                                <!-- Location Filter -->
                                <li>
                                    <div class="mb-3">
                                        <label class="form-label small text-muted mb-1">Location</label>
                                        <select class="form-select form-select-sm" id="filterLocation">
                                            <option value="">All Locations</option>
                                            <option value="office">Office</option>
                                            <option value="field">Field</option>
                                        </select>
                                    </div>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-secondary flex-fill"
                                            id="clearFiltersBtn">
                                            <i class="bi bi-x-circle me-1"></i>Clear
                                        </button>
                                        <button type="button" class="btn btn-sm btn-primary bg-main border-0 flex-fill"
                                            id="applyFiltersBtn">
                                            <i class="bi bi-check-lg me-1"></i>Apply
                                        </button>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                @include('admin.daily-logs.logs_table')
            </div>
        </div>
    </div>

    <!-- Location Map Tooltip -->
    <div id="locationMapTooltip" class="location-map-tooltip"></div>

    <!-- Daily Log Detail Offcanvas -->
    @include('admin.daily-logs.detail_canvas')
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
    <!-- Leaflet JS for Maps -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Common Helper Functions -->
    <script src="{{ asset('js/helpers.js') }}"></script>

    <script>
        // Global variables
        let dailyLogsTable;
        let liveStreamInterval = null;
        let locationTooltipMap = null;

        $(document).ready(function() {
            // Initialize DataTable with Responsive
            dailyLogsTable = initUserDataTable('#dailyLogsTable', {
                pageLength: 10,
                lengthMenu: [[25, 50, 100, 200], [25, 50, 100, 200]],
                order: [[1, 'desc']], // Sort by Check-In time descending
                scrollX: false,
                responsive: {
                    details: {
                        type: 'column',
                        target: 'tr'
                    }
                },
                columnDefs: [
                    {
                        targets: [0, 1, 2, 3, 4, 5, 6, 7], // All columns visible by default
                        visible: true
                    },
                    {
                        targets: 7, // Actions column
                        orderable: false,
                        className: 'no-toggle',
                        responsivePriority: 1 // Always show Actions
                    },
                    {
                        targets: 0, // Employee column
                        responsivePriority: 2 // High priority - always show
                    },
                    {
                        targets: 1, // Check-In column
                        responsivePriority: 3
                    },
                    {
                        targets: [3, 4, 5], // Duration, Source, Location - lower priority
                        responsivePriority: 4
                    },
                    {
                        targets: [2, 6], // Check-Out, Flag - can hide on small screens
                        responsivePriority: 5
                    }
                ],
                language: {
                    search: "",
                    searchPlaceholder: "Search logs...",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ logs",
                    infoEmpty: "No logs available",
                    zeroRecords: "No matching logs found"
                },
                buttons: [{
                    extend: 'colvis',
                    text: 'Select Columns',
                    className: 'btn btn-sm border-0 bg-main text-white',
                    columns: [0, 1, 2, 3, 4, 5, 6], // All columns except Actions
                    responsiveResize: true
                }],
                drawCallback: function() {
                    initializeLocationTooltips();
                }
            });


            // Initialize Live Stream
            $('#liveStreamToggle').on('change', function() {
                if ($(this).is(':checked')) {
                    startLiveStream();
                } else {
                    stopLiveStream();
                }
            });

            function startLiveStream() {
                if (liveStreamInterval) {
                    clearInterval(liveStreamInterval);
                }
                liveStreamInterval = setInterval(function() {
                    dailyLogsTable.ajax.reload(null, false);
                }, 5000);
            }

            function stopLiveStream() {
                if (liveStreamInterval) {
                    clearInterval(liveStreamInterval);
                    liveStreamInterval = null;
                }
            }

            // Manual Refresh
            $('#refreshBtn').on('click', function() {
                dailyLogsTable.ajax.reload(null, false);
                $(this).find('i').addClass('rotate');
                setTimeout(() => {
                    $(this).find('i').removeClass('rotate');
                }, 1000);
            });

            // Export functionality
            $('#exportBtn').on('click', function() {
                const data = dailyLogsTable.rows({search: 'applied'}).data();
                let csvContent = "Employee,Check-In,Check-Out,Duration,Status,Source,Location\n";
                
                data.each(function(row) {
                    const employee = $(row[0]).text().trim().replace(/,/g, ';');
                    const checkIn = $(row[1]).text().trim().replace(/,/g, ';');
                    const checkOut = $(row[2]).text().trim().replace(/,/g, ';');
                    const duration = $(row[3]).text().trim().replace(/,/g, ';');
                    const source = $(row[4]).text().trim().replace(/,/g, ';');
                    const location = $(row[5]).text().trim().replace(/,/g, ';');
                    csvContent += `"${employee}","${checkIn}","${checkOut}","${duration}","${source}","${location}"\n`;
                });

                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                const url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', `daily-logs-${new Date().toISOString().split('T')[0]}.csv`);
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });

            // Initialize location tooltips
            initializeLocationTooltips();

            // Employee click handler
            $(document).on('click', '.employee-cell', function() {
                const employeeId = $(this).data('employee-id');
                console.log('View attendance history for employee:', employeeId);
                // Open employee attendance history modal/offcanvas
            });

            // Daily Log Detail Offcanvas Handler
            let detailCanvasMap = null;
            const detailCanvas = document.getElementById('dailyLogDetailCanvas');
            if (detailCanvas) {
                detailCanvas.addEventListener('show.bs.offcanvas', function(event) {
                    const button = event.relatedTarget;
                    if (!button || !button.classList.contains('view-log-btn')) return;

                    // Extract data from button
                    const logData = {
                        logId: $(button).data('log-id'),
                        employeeName: $(button).data('employee-name'),
                        employeeAvatar: $(button).data('employee-avatar'),
                        employeeInfo: $(button).data('employee-info'),
                        checkInTime: $(button).data('check-in-time'),
                        checkInStatus: $(button).data('check-in-status'),
                        checkInStatusText: $(button).data('check-in-status-text'),
                        checkOutTime: $(button).data('check-out-time'),
                        checkOutStatus: $(button).data('check-out-status'),
                        checkOutStatusText: $(button).data('check-out-status-text'),
                        duration: $(button).data('duration'),
                        source: $(button).data('source'),
                        sourceIcon: $(button).data('source-icon'),
                        locationType: $(button).data('location-type'),
                        locationLat: $(button).data('location-lat'),
                        locationLng: $(button).data('location-lng'),
                        locationAddress: $(button).data('location-address'),
                        locationIP: $(button).data('location-ip'),
                        flag: $(button).data('flag'),
                        flagText: $(button).data('flag-text')
                    };

                    // Populate Employee Information
                    $('#detailEmployeeAvatar').text(logData.employeeAvatar);
                    $('#detailEmployeeName').text(logData.employeeName);
                    $('#detailEmployeeInfo').text(logData.employeeInfo);

                    // Populate Attendance Timeline
                    const checkInStatusClass = logData.checkInStatus === 'late' ? 'bg-warning' : 'bg-success';
                    const checkInStatusTextClass = logData.checkInStatus === 'late' ? 'text-warning' : 'opacity-50 text-white';
                    $('#detailCheckInStatus').removeClass('bg-success bg-warning').addClass(checkInStatusClass);
                    $('#detailCheckInTime').text(logData.checkInTime);
                    $('#detailCheckInStatusText').removeClass('text-warning opacity-50 text-white').addClass(checkInStatusTextClass).text(logData.checkInStatusText);

                    const checkOutStatusClass = logData.checkOutStatus === 'early' ? 'bg-danger' : 'bg-success';
                    const checkOutStatusTextClass = logData.checkOutStatus === 'early' ? 'text-danger' : 'opacity-50 text-white';
                    $('#detailCheckOutStatus').removeClass('bg-success bg-danger').addClass(checkOutStatusClass);
                    $('#detailCheckOutTime').text(logData.checkOutTime);
                    $('#detailCheckOutStatusText').removeClass('text-danger opacity-50 text-white').addClass(checkOutStatusTextClass).text(logData.checkOutStatusText);

                    $('#detailDuration').text(logData.duration);

                    // Populate Device & Source
                    const $sourceIcon = $('#detailSource').closest('.d-flex').find('i');
                    if (logData.sourceIcon === 'phone') {
                        $sourceIcon.removeClass('bi-laptop text-info').addClass('bi-phone text-primary fs-4 me-3');
                    } else {
                        $sourceIcon.removeClass('bi-phone text-primary').addClass('bi-laptop text-info fs-4 me-3');
                    }
                    $('#detailSource').text(logData.source);
                    $('#detailDeviceInfo').text(logData.sourceIcon === 'phone' ? 'iOS 16.5 • Safari 16.5' : 'Chrome 120.0 • Windows 11');
                    $('#detailBrowser').text(logData.sourceIcon === 'phone' ? 'Mobile Safari' : 'Google Chrome');

                    // Populate Location
                    if (logData.locationType === 'field' && logData.locationLat && logData.locationLng) {
                        $('#detailGPSLocation').show();
                        $('#detailIPLocation').hide();
                        $('#detailLocationAddress').text(logData.locationAddress);
                        $('#detailCoordinates').text(`${logData.locationLat}, ${logData.locationLng}`);
                        
                        // Initialize map in offcanvas
                        setTimeout(() => {
                            if (detailCanvasMap) {
                                detailCanvasMap.remove();
                            }
                            const mapContainer = document.getElementById('detailLocationMap');
                            if (mapContainer) {
                                detailCanvasMap = L.map('detailLocationMap').setView([parseFloat(logData.locationLat), parseFloat(logData.locationLng)], 15);
                                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                    attribution: '© OpenStreetMap contributors'
                                }).addTo(detailCanvasMap);
                                L.marker([parseFloat(logData.locationLat), parseFloat(logData.locationLng)]).addTo(detailCanvasMap)
                                    .bindPopup(logData.locationAddress || `Location: ${logData.locationLat}, ${logData.locationLng}`)
                                    .openPopup();
                            }
                        }, 300);
                    } else if (logData.locationType === 'office' && logData.locationIP) {
                        $('#detailGPSLocation').hide();
                        $('#detailIPLocation').show();
                        $('#detailIPAddress').text(logData.locationIP);
                        const isOfficeIP = logData.locationIP.startsWith('192.168.1.');
                        const ipStatusClass = isOfficeIP ? 'bg-success' : 'bg-danger';
                        const ipStatusText = isOfficeIP ? 'Office Network' : 'Security Exception';
                        $('#detailIPStatus').removeClass('bg-success bg-danger').addClass(ipStatusClass).text(ipStatusText);
                        if (detailCanvasMap) {
                            detailCanvasMap.remove();
                            detailCanvasMap = null;
                        }
                    }

                    // Populate Flag & Verification
                    let flagClass = 'bg-success';
                    if (logData.flag === 'outside-zone') flagClass = 'bg-danger';
                    else if (logData.flag === 'ip-mismatch') flagClass = 'bg-warning';
                    
                    $('#detailFlag').removeClass('bg-success bg-danger bg-warning').addClass(flagClass).text(logData.flagText);
                    
                    // Set verification items
                    $('#detailVerification1').text('Geofence verified');
                    $('#detailVerification2').text('Time verified');
                    
                    if (logData.flag !== 'verified') {
                        $('#detailVerification3Li').show();
                        if (logData.flag === 'outside-zone') {
                            $('#detailVerification3').text('Location outside geofence zone');
                        } else if (logData.flag === 'ip-mismatch') {
                            $('#detailVerification3').text('IP address mismatch detected');
                        }
                    } else {
                        $('#detailVerification3Li').hide();
                    }
                });

                detailCanvas.addEventListener('hidden.bs.offcanvas', function() {
                    // Cleanup map when offcanvas is closed
                    if (detailCanvasMap) {
                        detailCanvasMap.remove();
                        detailCanvasMap = null;
                    }
                });
            }
        });

        // Location Tooltip Functions
        function initializeLocationTooltips() {
            $('.location-cell').off('mouseenter mouseleave').on('mouseenter', function() {
                const $cell = $(this);
                const lat = $cell.data('lat');
                const lng = $cell.data('lng');
                const address = $cell.data('address');
                const ip = $cell.data('ip');
                const type = $cell.data('type');

                if (type === 'field' && lat && lng) {
                    showLocationMapTooltip($cell, lat, lng, address);
                } else if (type === 'office' && ip) {
                    showIPTooltip($cell, ip);
                }
            }).on('mouseleave', function() {
                hideLocationTooltip();
            });
        }

        function showLocationMapTooltip($cell, lat, lng, address) {
            const tooltip = $('#locationMapTooltip');
            
            // Clear any existing map
            if (locationTooltipMap) {
                locationTooltipMap.remove();
                locationTooltipMap = null;
            }
            
            // Position tooltip first
            const cellOffset = $cell.offset();
            const cellWidth = $cell.outerWidth();
            const cellTop = cellOffset.top;
            const cellLeft = cellOffset.left;
            
            tooltip.css({
                position: 'fixed',
                left: (cellLeft + (cellWidth / 2) - 150) + 'px',
                top: (cellTop - 230) + 'px',
                display: 'block',
                width: '300px',
                height: '200px',
                zIndex: 1060
            });

            // Create map after tooltip is visible
            setTimeout(() => {
                if (document.getElementById('locationMapTooltip')) {
                    locationTooltipMap = L.map('locationMapTooltip').setView([lat, lng], 15);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors'
                    }).addTo(locationTooltipMap);
                    L.marker([lat, lng]).addTo(locationTooltipMap)
                        .bindPopup(address || `Location: ${lat}, ${lng}`)
                        .openPopup();
                }
            }, 100);
        }

        function showIPTooltip($cell, ip) {
            const isOfficeIP = ip.startsWith('192.168.1.');
            const tooltip = $('#locationMapTooltip');
            
            // Clear any existing map
            if (locationTooltipMap) {
                locationTooltipMap.remove();
                locationTooltipMap = null;
            }
            
            // Set HTML content
            tooltip.html(`
                <div class="p-3 border rounded shadow-sm bg-white">
                    <h6 class="mb-2 fw-semibold">IP Address</h6>
                    <div class="fw-bold mb-2 text-primary">${ip}</div>
                    <span class="badge ${isOfficeIP ? 'bg-success' : 'bg-danger'}">
                        ${isOfficeIP ? 'Office Network' : 'Security Exception'}
                    </span>
                </div>
            `);

            // Position tooltip
            const cellOffset = $cell.offset();
            const cellWidth = $cell.outerWidth();
            const cellTop = cellOffset.top;
            const cellLeft = cellOffset.left;
            
            tooltip.css({
                position: 'fixed',
                left: (cellLeft + (cellWidth / 2) - 100) + 'px',
                top: (cellTop - 130) + 'px',
                display: 'block',
                width: 'auto',
                minWidth: '200px',
                height: 'auto',
                zIndex: 1060
            });
        }

        function hideLocationTooltip() {
            $('#locationMapTooltip').hide().html('');
            if (locationTooltipMap) {
                locationTooltipMap.remove();
                locationTooltipMap = null;
            }
        }

        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            if (liveStreamInterval) {
                clearInterval(liveStreamInterval);
            }
            if (locationTooltipMap) {
                locationTooltipMap.remove();
            }
        });
    </script>
@endpush
