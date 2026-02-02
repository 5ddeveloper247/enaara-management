@extends('layouts.app')

@section('title', 'Geofencing - Admin Panel')

@section('page-title', 'Geofencing Command Center')

@push('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- Geofencing Module CSS -->
    <link href="{{ asset('css/geofencing.css') }}" rel="stylesheet">

    <style>
        .btn {
            font-size: 13px;
        }

        #geofencingMap {
            height: calc(100vh - 200px);
            min-height: 600px;
            border-radius: 12px;
            overflow: hidden;
        }

        /* White icons for Leaflet toolbar */
        .leaflet-control-zoom-in,
        .leaflet-control-zoom-out {
            color: #ffffff !important;
            background-color: #2d2d2d !important;
            border-color: #444 !important;
            font-size: 18px;
            line-height: 30px;
        }

        .leaflet-control-zoom-in:hover,
        .leaflet-control-zoom-out:hover {
            background-color: #3d3d3d !important;
            color: #ffffff !important;
        }

        /* Draw control icons white */
        .leaflet-draw-toolbar a {
            background-color: #2d2d2d !important;
            color: #ffffff !important;
            border-color: #444 !important;
        }

        .leaflet-draw-toolbar a:hover {
            background-color: #3d3d3d !important;
        }

        /* Popup text white */
        .leaflet-popup-content-wrapper {
            background: #2d2d2d !important;
        }

        .leaflet-popup-content {
            color: #ffffff !important;
        }

        .leaflet-popup-content * {
            color: #ffffff !important;
        }

        .leaflet-popup-content .text-success {
            color: #28a745 !important;
        }

        .leaflet-popup-content .text-warning {
            color: #ffc107 !important;
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

        .dt-control {
            padding-left: 5px !important;
            padding-right: 0 !important;
        }

        .dt-buttons {
            margin-top: 2px;
        }

        .map-controls {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1000;
            background: white;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .fence-legend {
            position: absolute;
            bottom: 30px;
            left: 10px;
            z-index: 1000;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            min-width: 200px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
            font-size: 12px;
        }

        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            border: 2px solid #ddd;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h5 class="mb-0">Geofencing Command Center</h5>
                <small class="text-muted">Monitor and manage location-based attendance boundaries</small>
            </div>
            <div class="col-md-6 text-end">
                <button type="button" class="btn btn-outline-secondary me-2" id="refreshMapBtn">
                    <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                </button>
                <button type="button" class="btn btn-outline-secondary me-2" id="toggleBreadcrumbsBtn">
                    <i class="bi bi-map me-1"></i>Toggle Breadcrumbs
                </button>
                <button type="button" class="btn btn-primary bg-main border-0" data-bs-toggle="offcanvas"
                    data-bs-target="#addFenceCanvas">
                    <i class="bi bi-plus-circle me-1"></i>Add New Fence
                </button>
            </div>
        </div>

        <!-- Counters -->
        @include('admin.geofencing.counters')

        <!-- Map Container -->
        @include('admin.geofencing.map')

        <!-- Fences List -->
        @include('admin.geofencing.fence_table')
    </div>

    <!-- Add Fence Canvas -->
    @include('admin.geofencing.add_fence_canvas')

    <!-- Fence Detail Canvas -->
    @include('admin.geofencing.fence_detail_canvas')
@endsection

@push('scripts')
    <!-- jQuery -->
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
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Leaflet Draw Plugin for drawing fences -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
    <!-- Common Helper Functions -->
    <script src="{{ asset('js/helpers.js') }}"></script>

    <script>
        // Global variables
        let geofencingMap;
        let drawnItems = new L.FeatureGroup();
        let fences = [];
        let breadcrumbsEnabled = true;
        let employeeMarkers = [];
        let breadcrumbTrails = [];
        let fencesTable;

        // Sample fence data - Rawalpindi, Pakistan
        const sampleFences = [
            {
                id: 1,
                name: 'Enaara Tower A',
                address: 'Commercial Area, Rawalpindi, Pakistan',
                lat: 33.5651,
                lng: 73.0169,
                radius: 200,
                radiusUnit: 'meters',
                type: 'hard-lock',
                assignedGroups: ['Site Maintenance', 'Security Team'],
                insideCount: 45,
                outsideCount: 12,
                status: 'active'
            },
            {
                id: 2,
                name: 'Downtown Site',
                address: 'Saddar, Rawalpindi, Pakistan',
                lat: 33.6000,
                lng: 73.0500,
                radius: 150,
                radiusUnit: 'meters',
                type: 'soft-lock',
                assignedGroups: ['Sales Team', 'Field Agents'],
                insideCount: 28,
                outsideCount: 5,
                status: 'active'
            },
            {
                id: 3,
                name: 'Construction Site B',
                address: 'DHA Phase 1, Rawalpindi, Pakistan',
                lat: 33.5500,
                lng: 73.0000,
                radius: 300,
                radiusUnit: 'meters',
                type: 'hard-lock',
                assignedGroups: ['Construction Team'],
                insideCount: 69,
                outsideCount: 0,
                status: 'active'
            }
        ];

        $(document).ready(function() {
            initializeMap();
            initializeFencesDataTable();
            addSampleFencesToMap();
            addSampleEmployees();
            addSampleBreadcrumbs();

            // Refresh map button
            $('#refreshMapBtn').on('click', function() {
                location.reload();
            });

            // Toggle breadcrumbs
            $('#toggleBreadcrumbsBtn').on('click', function() {
                breadcrumbsEnabled = !breadcrumbsEnabled;
                breadcrumbTrails.forEach(trail => {
                    if (breadcrumbsEnabled) {
                        trail.addTo(geofencingMap);
                    } else {
                        geofencingMap.removeLayer(trail);
                    }
                });
                $(this).html(breadcrumbsEnabled ? 
                    '<i class="bi bi-map me-1"></i>Hide Breadcrumbs' : 
                    '<i class="bi bi-map me-1"></i>Show Breadcrumbs');
            });

            // Center map button
            $('#centerMapBtn').on('click', function() {
                geofencingMap.setView([33.5651, 73.0169], 12);
            });

            // Fit bounds button
            $('#fitBoundsBtn').on('click', function() {
                if (fences.length > 0) {
                    const group = new L.featureGroup(fences.map(f => f.circle));
                    geofencingMap.fitBounds(group.getBounds().pad(0.1));
                }
            });
        });

        function initializeMap() {
            // Initialize map centered on Rawalpindi, Pakistan
            geofencingMap = L.map('geofencingMap').setView([33.5651, 73.0169], 12);

            // Add Dark Mode tile layer (CartoDB Dark Matter)
            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
                subdomains: 'abcd',
                maxZoom: 19
            }).addTo(geofencingMap);

            // Add draw controls
            // const drawControl = new L.Control.Draw({
            //     draw: {
            //         circle: true,
            //         circlemarker: false,
            //         rectangle: false,
            //         polygon: true,
            //         polyline: false,
            //         marker: true
            //     },
            //     edit: {
            //         featureGroup: drawnItems,
            //         remove: true
            //     }
            // });

            // geofencingMap.addControl(drawControl);

            // Handle draw events
            geofencingMap.on(L.Draw.Event.CREATED, function(e) {
                const layer = e.layer;
                drawnItems.addLayer(layer);
                
                // Open add fence form with pre-filled location
                if (layer instanceof L.Circle) {
                    const center = layer.getLatLng();
                    const radius = layer.getRadius();
                    openAddFenceCanvas(center.lat, center.lng, radius);
                } else if (layer instanceof L.Polygon) {
                    const bounds = layer.getBounds();
                    const center = bounds.getCenter();
                    openAddFenceCanvas(center.lat, center.lng, null, layer);
                }
            });
        }

        function addSampleFencesToMap() {
            sampleFences.forEach(fenceData => {
                const fence = createFence(fenceData);
                fences.push(fence);
            });
        }

        function createFence(fenceData) {
            const circle = L.circle([fenceData.lat, fenceData.lng], {
                radius: fenceData.radius,
                color: fenceData.type === 'hard-lock' ? '#dc3545' : '#ffc107',
                fillColor: fenceData.type === 'hard-lock' ? '#dc3545' : '#ffc107',
                fillOpacity: 0.2,
                weight: 2
            }).addTo(geofencingMap);

            // Create custom marker for site
            const marker = L.marker([fenceData.lat, fenceData.lng], {
                icon: L.divIcon({
                    className: 'custom-site-marker',
                    html: `<div class="site-marker-pin">
                        <i class="bi bi-building"></i>
                    </div>`,
                    iconSize: [30, 30],
                    iconAnchor: [15, 30]
                })
            }).addTo(geofencingMap);

            // Popup with site info
            const popupContent = `
                <div class="fence-popup">
                    <h6 class="fw-semibold mb-2">${fenceData.name}</h6>
                    <p class="small mb-2">${fenceData.address}</p>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="small">Inside: <strong class="text-success">${fenceData.insideCount}</strong></span>
                        <span class="small">Outside: <strong class="text-warning">${fenceData.outsideCount}</strong></span>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-primary view-fence-btn" data-fence-id="${fenceData.id}">
                            <i class="bi bi-eye"></i> View Details
                        </button>
                    </div>
                </div>
            `;

            marker.bindPopup(popupContent);
            circle.bindPopup(popupContent);

            // Store fence data
            return {
                id: fenceData.id,
                name: fenceData.name,
                circle: circle,
                marker: marker,
                data: fenceData
            };
        }

        function addSampleEmployees() {
            // Sample employee locations - Rawalpindi, Pakistan
            const employees = [
                { name: 'Ahmed Ali', lat: 33.5655, lng: 73.0170, status: 'inside', fenceId: 1 },
                { name: 'Zainab Malik', lat: 33.6005, lng: 73.0505, status: 'inside', fenceId: 2 },
                { name: 'Bilal Ahmed', lat: 33.5505, lng: 73.0005, status: 'inside', fenceId: 3 },
                { name: 'Hira Ali', lat: 33.5658, lng: 73.0175, status: 'outside', fenceId: 1 },
            ];

            employees.forEach(emp => {
                const iconColor = emp.status === 'inside' ? '#28a745' : '#ffc107';
                const marker = L.marker([emp.lat, emp.lng], {
                    icon: L.divIcon({
                        className: 'employee-marker',
                        html: `<div class="employee-marker-pin" style="background-color: ${iconColor};">
                            <i class="bi bi-person-fill"></i>
                        </div>`,
                        iconSize: [24, 24],
                        iconAnchor: [12, 24]
                    })
                }).addTo(geofencingMap);

                marker.bindPopup(`
                    <div>
                        <strong>${emp.name}</strong><br>
                        Status: <span class="badge bg-${emp.status === 'inside' ? 'success' : 'warning'}">${emp.status === 'inside' ? 'Inside' : 'Outside'}</span>
                    </div>
                `);

                employeeMarkers.push(marker);
            });
        }

        function addSampleBreadcrumbs() {
            // Sample breadcrumb trail for a field agent - Rawalpindi, Pakistan
            const trailCoordinates = [
                [33.5651, 73.0169],
                [33.5653, 73.0171],
                [33.5655, 73.0173],
                [33.5657, 73.0175],
                [33.5659, 73.0177]
            ];

            const polyline = L.polyline(trailCoordinates, {
                color: '#6c757d',
                weight: 3,
                opacity: 0.6
            }).addTo(geofencingMap);

            breadcrumbTrails.push(polyline);
        }

        function initializeFencesDataTable() {
            // Populate table body first
            const tbody = $('#fencesTableBody');
            tbody.empty();

            sampleFences.forEach(fence => {
                const typeBadge = fence.type === 'hard-lock' 
                    ? '<span class="badge bg-danger">Hard Lock</span>' 
                    : '<span class="badge bg-warning text-dark">Soft Lock</span>';
                
                const statusBadge = fence.status === 'active'
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>';

                const row = `
                    <tr>
                        <td class="dt-control"></td>
                        <td><strong>${fence.name}</strong></td>
                        <td><small class="text-muted">${fence.address}</small></td>
                        <td>${fence.radius} ${fence.radiusUnit}</td>
                        <td>${typeBadge}</td>
                        <td><small>${fence.assignedGroups.join(', ')}</small></td>
                        <td>
                            <span class="text-success">${fence.insideCount}</span> / 
                            <span class="text-warning">${fence.outsideCount}</span>
                        </td>
                        <td>${statusBadge}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary view-fence-btn" data-fence-id="${fence.id}" data-bs-toggle="tooltip" title="View Details">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-secondary edit-fence-btn" data-fence-id="${fence.id}" data-bs-toggle="tooltip" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                        </td>
                    </tr>
                `;
                tbody.append(row);
            });

            // Initialize DataTable
            fencesTable = initUserDataTable('#fencesTable', {
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                order: [[1, 'asc']], // Sort by site name
                scrollX: false,
                responsive: {
                    details: {
                        type: 'column',
                        target: 0
                    }
                },
                columnDefs: [
                    {
                        targets: 0, // dt-control column
                        orderable: false,
                        className: 'dt-control',
                        responsivePriority: 0
                    },
                    {
                        targets: [1, 2, 3, 4, 5, 6, 7, 8], // All columns visible by default
                        visible: true
                    },
                    {
                        targets: 8, // Actions column
                        orderable: false,
                        className: 'no-toggle',
                        responsivePriority: 1
                    },
                    {
                        targets: 1, // Site Name column
                        responsivePriority: 2
                    },
                    {
                        targets: [4, 5, 6], // Type, Groups, Inside/Outside - lower priority
                        responsivePriority: 4
                    },
                    {
                        targets: [2, 3, 7], // Address, Radius, Status - can hide on small screens
                        responsivePriority: 5
                    }
                ],
                language: {
                    search: "",
                    searchPlaceholder: "Search fences...",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ fences",
                    infoEmpty: "No fences available",
                    zeroRecords: "No matching fences found"
                },
                buttons: [{
                    extend: 'colvis',
                    text: 'Select Columns',
                    className: 'btn btn-sm border-0 bg-main text-white',
                    columns: [1, 2, 3, 4, 5, 6, 7] // Exclude dt-control and actions
                }],
                drawCallback: function() {
                    // Re-initialize tooltips after table redraw
                    $('[data-bs-toggle="tooltip"]').tooltip();
                }
            });

            // Filter functionality
            $('#applyFiltersBtn').on('click', function() {
                const type = $('#filterType').val();
                const status = $('#filterStatus').val();
                
                // Apply filters to DataTable
                fencesTable.column(4).search(type); // Type column
                fencesTable.column(7).search(status); // Status column
                
                fencesTable.draw();
                
                // Close dropdown
                $('.dropdown-toggle').dropdown('hide');
            });

            $('#clearFiltersBtn').on('click', function() {
                $('#filterType').val('');
                $('#filterStatus').val('');
                
                // Clear filters from DataTable
                fencesTable.columns().search('');
                fencesTable.draw();
            });

            // Export functionality
            $('#exportFencesBtn').on('click', function() {
                const data = fencesTable.rows({search: 'applied'}).data();
                let csvContent = "Site Name,Address,Radius,Type,Assigned Groups,Inside,Outside,Status\n";
                
                data.each(function(row) {
                    const siteName = $(row[1]).text().trim().replace(/,/g, ';');
                    const address = $(row[2]).text().trim().replace(/,/g, ';');
                    const radius = $(row[3]).text().trim().replace(/,/g, ';');
                    const type = $(row[4]).text().trim().replace(/,/g, ';');
                    const groups = $(row[5]).text().trim().replace(/,/g, ';');
                    const insideOutside = $(row[6]).text().trim().replace(/,/g, ';');
                    const status = $(row[7]).text().trim().replace(/,/g, ';');
                    csvContent += `"${siteName}","${address}","${radius}","${type}","${groups}","${insideOutside}","${status}"\n`;
                });

                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                const url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', 'geofences_' + new Date().toISOString().split('T')[0] + '.csv');
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });

            // Initialize tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();
        }

        function openAddFenceCanvas(lat, lng, radius, polygon = null) {
            // Pre-fill form if drawing on map
            if (lat && lng) {
                $('#fenceLat').val(lat);
                $('#fenceLng').val(lng);
                if (radius) {
                    $('#fenceRadius').val(Math.round(radius));
                }
            }
            
            const canvas = new bootstrap.Offcanvas(document.getElementById('addFenceCanvas'));
            canvas.show();
        }

        // Handle view fence button clicks
        $(document).on('click', '.view-fence-btn', function() {
            const fenceId = $(this).data('fence-id');
            const fence = sampleFences.find(f => f.id === fenceId);
            if (fence) {
                showFenceDetails(fence);
            }
        });

        // Function to show fence details
        function showFenceDetails(fenceData) {
            // Populate detail canvas
            $('#detailFenceName').text(fenceData.name);
            $('#detailFenceAddress').text(fenceData.address);
            $('#detailFenceCoordinates').text(`${fenceData.lat}, ${fenceData.lng}`);
            $('#detailFenceRadius').text(`${fenceData.radius} ${fenceData.radiusUnit}`);
            
            // Fence type badge
            const typeBadge = fenceData.type === 'hard-lock' 
                ? '<span class="badge bg-danger">Hard Lock</span>' 
                : '<span class="badge bg-warning text-dark">Soft Lock</span>';
            $('#detailFenceType').html(typeBadge);
            
            // Employee counts
            $('#detailFenceInside').text(fenceData.insideCount);
            $('#detailFenceOutside').text(fenceData.outsideCount);
            
            // Assigned groups
            const groupsHtml = fenceData.assignedGroups.map(group => 
                `<span class="badge bg-secondary me-2 mb-2">${group}</span>`
            ).join('');
            $('#detailFenceGroups').html(groupsHtml);
            
            // Open canvas
            const canvas = new bootstrap.Offcanvas(document.getElementById('fenceDetailCanvas'));
            canvas.show();
        }
    </script>
@endpush

