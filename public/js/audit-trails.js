/**
 * Audit Trails Module
 * System activity log and change tracking
 */

(function () {
    'use strict';

    // ============================================
    // GLOBAL VARIABLES
    // ============================================
    let auditTrailsTable;
    let auditTrailsData = [];
    let customFilterFunction = null;

    // ============================================
    // INITIALIZATION
    // ============================================
    $(document).ready(function () {
        loadAuditTrailsData();
        initializeDataTable();
        initializeEventHandlers();
        updateCounters();
    });

    // ============================================
    // DATA LOADING
    // ============================================
    function loadAuditTrailsData() {
        if (typeof ProjectData !== 'undefined' && ProjectData.auditTrails) {
            auditTrailsData = ProjectData.auditTrails.generateSampleData(100);
        } else {
            console.warn('ProjectData not found, using empty array');
            auditTrailsData = [];
        }
    }

    // ============================================
    // DATA TABLE INITIALIZATION
    // ============================================
    function initializeDataTable() {
        const tbody = $('#auditTrailsTableBody');
        tbody.empty();

        auditTrailsData.forEach(audit => {
            const row = buildTableRow(audit);
            tbody.append(row);
        });

        auditTrailsTable = initUserDataTable('#auditTrailsTable', {
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            order: [[1, 'desc']], // Sort by Timestamp descending
            scrollX: false,
            responsive: {
                details: {
                    type: 'column',
                    target: 0
                }
            },
            columnDefs: [
                {
                    targets: 0,
                    orderable: false,
                    className: 'dt-control',
                    responsivePriority: 0
                },
                {
                    targets: [1, 2, 3, 4, 5, 6],
                    visible: true
                },
                {
                    targets: 7,
                    orderable: false,
                    className: 'no-toggle',
                    responsivePriority: 1
                },
                {
                    targets: 1,
                    responsivePriority: 2
                },
                {
                    targets: [2, 3, 4],
                    responsivePriority: 3
                },
                {
                    targets: [5, 6],
                    responsivePriority: 4
                }
            ],
            language: {
                search: "",
                searchPlaceholder: "Search audit trails...",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ activities",
                infoEmpty: "No activities available",
                zeroRecords: "No matching activities found"
            },
            buttons: [{
                extend: 'colvis',
                text: 'Select Columns',
                className: 'btn btn-sm border-0 bg-main text-white',
                columns: [1, 2, 3, 4, 5, 6]
            }],
            drawCallback: function() {
                $('[data-bs-toggle="tooltip"]').tooltip();
            }
        });
    }

    // ============================================
    // TABLE ROW BUILDER
    // ============================================
    function buildTableRow(audit) {
        // Timestamp
        const timestamp = formatTimestamp(audit.timestamp);

        // User
        const user = `
            <div class="d-flex align-items-center">
                <div class="user-avatar me-2" style="width: 32px; height: 32px; font-size: 0.75rem;">${audit.user.avatar}</div>
                <div>
                    <div class="fw-semibold small">${audit.user.name}</div>
                    <small class="text-muted">${audit.user.role}</small>
                </div>
            </div>
        `;

        // Action Category Badge
        let categoryBadge = '';
        const categoryColors = {
            'Leave': 'bg-info',
            'Geofence': 'bg-success',
            'Shift': 'bg-primary',
            'Security': 'bg-danger',
            'Employee': 'bg-warning text-dark',
            'System': 'bg-secondary'
        };
        const categoryIcon = {
            'Leave': 'bi-calendar-event',
            'Geofence': 'bi-geo-alt-fill',
            'Shift': 'bi-calendar-week',
            'Security': 'bi-shield-lock',
            'Employee': 'bi-person',
            'System': 'bi-gear'
        };
        const colorClass = categoryColors[audit.category] || 'bg-secondary';
        categoryBadge = `<span class="badge px-2 rounded-1 ${colorClass}"><i class="bi ${categoryIcon[audit.category]} me-1"></i>${audit.category}</span>`;

        // Severity Badge
        let severityBadge = '';
        if (audit.severity === 'critical') {
            severityBadge = '<span class="badge px-2 rounded-1 bg-danger"><i class="bi bi-exclamation-triangle me-1"></i>Critical</span>';
        } else if (audit.severity === 'warning') {
            severityBadge = '<span class="badge px-2 rounded-1 bg-warning text-dark"><i class="bi bi-exclamation-circle me-1"></i>Warning</span>';
        } else if (audit.severity === 'info') {
            severityBadge = '<span class="badge px-2 rounded-1 bg-info"><i class="bi bi-info-circle me-1"></i>Info</span>';
        } else if (audit.severity === 'success') {
            severityBadge = '<span class="badge px-2 rounded-1 bg-success"><i class="bi bi-check-circle me-1"></i>Success</span>';
        }

        // IP/Device
        const ipDevice = `
            <div>
                <div class="small fw-semibold">${audit.ipAddress}</div>
                <small class="text-muted">${audit.device}</small>
            </div>
        `;

        // View Details Button
        const viewDetailsBtn = `
            <button type="button" 
                    class="btn btn-sm btn-primary view-detail-btn" 
                    data-bs-toggle="modal"
                    data-bs-target="#auditDetailModal"
                    data-audit-id="${audit.id}"
                    data-timestamp="${audit.timestamp}"
                    data-user-name="${audit.user.name}"
                    data-user-role="${audit.user.role}"
                    data-user-avatar="${audit.user.avatar}"
                    data-category="${audit.category}"
                    data-description="${audit.description}"
                    data-severity="${audit.severity}"
                    data-ip-address="${audit.ipAddress}"
                    data-device="${audit.device}"
                    data-organization="${audit.organization}"
                    data-branch="${audit.branch}"
                    data-has-changes="${audit.hasChanges}"
                    data-changes='${JSON.stringify(audit.changes)}'
                    data-context="${audit.context || ''}"
                    title="View Details">
                <i class="bi bi-eye"></i>
            </button>
        `;

        return `
            <tr 
                data-timestamp="${audit.timestamp}" 
                data-organization="${audit.organization.toLowerCase()}" 
                data-category="${audit.category.toLowerCase()}" 
                data-severity="${audit.severity}">
                <td class="dt-control"></td>
                <td>
                    <div class="small fw-semibold">${timestamp.date}</div>
                    <small class="text-muted">${timestamp.time}</small>
                </td>
                <td>${user}</td>
                <td>${categoryBadge}</td>
                <td>
                    <div class="small">${audit.description}</div>
                </td>
                <td>${severityBadge}</td>
                <td>${ipDevice}</td>
                <td class="text-end">${viewDetailsBtn}</td>
            </tr>
        `;
    }

    // ============================================
    // EVENT HANDLERS
    // ============================================
    function initializeEventHandlers() {
        // Apply filters
        $('#applyFiltersBtn').on('click', applyFilters);

        // Clear filters
        $('#clearFiltersBtn').on('click', clearFilters);

        // Export button
        $('#exportBtn').on('click', handleExport);

        // View Detail Modal
        $(document).on('click', '.view-detail-btn', function() {
            const auditId = parseInt($(this).data('audit-id'));
            const audit = auditTrailsData.find(a => a.id === auditId);
            if (audit) {
                populateDetailModal(audit);
            }
        });

        // Export Detail
        $('#exportDetailBtn').on('click', function() {
            alert('Export functionality will be implemented with backend integration.');
        });
    }

    // ============================================
    // FILTER FUNCTIONS
    // ============================================
    function applyFilters() {
        const dateFrom = $('#filterDateFrom').val();
        const dateTo = $('#filterDateTo').val();
        const organization = $('#filterOrganization').val();
        const category = $('#filterCategory').val();
        const severity = $('#filterSeverity').val();

        // Remove existing custom filter if any
        if (customFilterFunction) {
            $.fn.dataTable.ext.search.pop();
        }

        // Create custom filtering function
        customFilterFunction = function(settings, data, dataIndex) {
            // Only apply to audit trails table
            if (settings.nTable.id !== 'auditTrailsTable') {
                return true;
            }

            const row = auditTrailsTable.row(dataIndex).node();
            
            // Date filter
            if (dateFrom || dateTo) {
                const rowTimestamp = $(row).data('timestamp');
                if (dateFrom && rowTimestamp < dateFrom + 'T00:00:00') return false;
                if (dateTo && rowTimestamp > dateTo + 'T23:59:59') return false;
            }
            
            // Organization filter
            if (organization) {
                const rowOrg = $(row).data('organization');
                if (rowOrg !== organization) return false;
            }
            
            // Category filter
            if (category) {
                const rowCategory = $(row).data('category');
                if (rowCategory !== category.toLowerCase()) return false;
            }
            
            // Severity filter
            if (severity) {
                const rowSeverity = $(row).data('severity');
                if (rowSeverity !== severity) return false;
            }
            
            return true;
        };

        // Add custom filter
        $.fn.dataTable.ext.search.push(customFilterFunction);

        // Apply filters
        if (auditTrailsTable) {
            auditTrailsTable.draw();
        }

        // Update counters
        updateCounters();
    }

    function clearFilters() {
        $('#filterDateFrom').val('');
        $('#filterDateTo').val('');
        $('#filterOrganization').val('');
        $('#filterCategory').val('');
        $('#filterSeverity').val('');

        // Remove custom filter
        if (customFilterFunction) {
            $.fn.dataTable.ext.search.pop();
            customFilterFunction = null;
        }

        if (auditTrailsTable) {
            auditTrailsTable.draw();
        }

        updateCounters();
    }

    // ============================================
    // DETAIL MODAL POPULATION
    // ============================================
    function populateDetailModal(audit) {
        // Activity Information
        const timestamp = formatTimestamp(audit.timestamp);
        $('#detailTimestamp').text(`${timestamp.date} at ${timestamp.time}`);
        $('#detailUser').html(`
            <div class="d-flex align-items-center">
                <div class="user-avatar me-2" style="width: 32px; height: 32px; font-size: 0.75rem;">${audit.user.avatar}</div>
                <div>
                    <div class="fw-semibold">${audit.user.name}</div>
                    <small class="text-muted">${audit.user.role}</small>
                </div>
            </div>
        `);

        // Category Badge
        const categoryColors = {
            'Leave': 'bg-info',
            'Geofence': 'bg-success',
            'Shift': 'bg-primary',
            'Security': 'bg-danger',
            'Employee': 'bg-warning text-dark',
            'System': 'bg-secondary'
        };
        const categoryIcon = {
            'Leave': 'bi-calendar-event',
            'Geofence': 'bi-geo-alt-fill',
            'Shift': 'bi-calendar-week',
            'Security': 'bi-shield-lock',
            'Employee': 'bi-person',
            'System': 'bi-gear'
        };
        const colorClass = categoryColors[audit.category] || 'bg-secondary';
        $('#detailCategory').html(`<span class="badge px-2 py-1 ${colorClass}"><i class="bi ${categoryIcon[audit.category]} me-1"></i>${audit.category}</span>`);

        // Severity Badge
        let severityBadge = '';
        if (audit.severity === 'critical') {
            severityBadge = '<span class="badge px-2 py-1 bg-danger"><i class="bi bi-exclamation-triangle me-1"></i>Critical</span>';
        } else if (audit.severity === 'warning') {
            severityBadge = '<span class="badge px-2 py-1 bg-warning text-dark"><i class="bi bi-exclamation-circle me-1"></i>Warning</span>';
        } else if (audit.severity === 'info') {
            severityBadge = '<span class="badge px-2 py-1 bg-info"><i class="bi bi-info-circle me-1"></i>Info</span>';
        } else if (audit.severity === 'success') {
            severityBadge = '<span class="badge px-2 py-1 bg-success"><i class="bi bi-check-circle me-1"></i>Success</span>';
        }
        $('#detailSeverity').html(severityBadge);

        $('#detailDescription').text(audit.description);

        // Device & Network Information
        $('#detailIPAddress').text(audit.ipAddress);
        $('#detailDevice').text(audit.device);
        $('#detailLocation').text(audit.branch);
        $('#detailOrganization').text(audit.organization);

        // Before & After Changes
        if (audit.hasChanges && audit.changes && audit.changes.length > 0) {
            $('#changesSection').show();
            const changesBody = $('#changesTableBody');
            changesBody.empty();
            
            audit.changes.forEach(change => {
                const row = `
                    <tr>
                        <td><strong>${change.field}</strong></td>
                        <td><span class="badge bg-secondary">${change.before || '-'}</span></td>
                        <td><span class="badge bg-primary">${change.after || '-'}</span></td>
                    </tr>
                `;
                changesBody.append(row);
            });
        } else {
            $('#changesSection').hide();
        }

        // Additional Context
        if (audit.context) {
            $('#contextSection').show();
            $('#detailContext').html(`<div class="alert alert-warning mb-0"><small>${audit.context}</small></div>`);
        } else {
            $('#contextSection').hide();
        }
    }

    // ============================================
    // UTILITY FUNCTIONS
    // ============================================
    function formatTimestamp(timestamp) {
        const date = new Date(timestamp);
        const dateStr = date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
        const timeStr = date.toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit',
            hour12: true
        });
        return { date: dateStr, time: timeStr };
    }

    function handleExport() {
        alert('Export functionality will be implemented with backend integration.');
    }

    // ============================================
    // COUNTERS UPDATE
    // ============================================
    function updateCounters() {
        if (!auditTrailsTable) return;

        let totalActivities = 0;
        let criticalEvents = 0;
        let securityActions = 0;
        const activeUsers = new Set();

        auditTrailsTable.rows({ search: 'applied' }).every(function () {
            const row = this.node();
            const severity = $(row).data('severity');
            const category = $(row).data('category');
            const userName = $(row).find('.user-avatar').parent().next().find('.fw-semibold').text();

            totalActivities++;
            activeUsers.add(userName);

            if (severity === 'critical') {
                criticalEvents++;
            }

            if (category === 'security') {
                securityActions++;
            }
        });

        $('#totalActivities').text(totalActivities);
        $('#criticalEvents').text(criticalEvents);
        $('#securityActions').text(securityActions);
        $('#activeUsers').text(activeUsers.size);
    }

})();

