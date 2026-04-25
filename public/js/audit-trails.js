/**
 * Audit Trails Module
 * System activity log and change tracking
 */

(function () {
    'use strict';

    let auditTrailsTable = null;
    let auditTrailsData = [];
    let isLoading = false;

    $(document).ready(async function () {
        initializeEventHandlers();
        await refreshAuditTrailsTable();
    });

    /**
     * ============================================
     * MAIN LOAD / REFRESH
     * ============================================
     */
    async function refreshAuditTrailsTable() {
        if (isLoading) return;

        isLoading = true;

        try {
            await loadAuditTrailsData();
            destroyDataTable();
            initializeDataTable();
            updateCounters();
        } catch (error) {
            console.error('Failed to refresh audit trails table:', error);
            showTableError('Unable to load audit trails at the moment.');
        } finally {
            isLoading = false;
        }
    }

    /**
     * ============================================
     * DATA LOADING
     * ============================================
     */
    async function loadAuditTrailsData() {
        const params = new URLSearchParams({
            date_from: $('#filterDateFrom').val() || '',
            date_to: $('#filterDateTo').val() || '',
            organization_id: $('#filterOrganization').val() || '',
            action_category: $('#filterCategory').val() || '',
            severity: $('#filterSeverity').val() || ''
        });

        const response = await fetch(`/admin/audit-trails/data?${params.toString()}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }

        const result = await response.json();
        auditTrailsData = Array.isArray(result.data) ? result.data : [];
    }

    /**
     * ============================================
     * DATATABLE SETUP
     * ============================================
     */
    function initializeDataTable() {
        const tbody = $('#auditTrailsTableBody');
        tbody.empty();

        if (auditTrailsData.length) {
            auditTrailsData.forEach(function (audit) {
                tbody.append(buildTableRow(audit));
            });
        }

        auditTrailsTable = initUserDataTable('#auditTrailsTable', {
            pageLength: 25,
            lengthMenu: [
                [10, 25, 50, 100],
                [10, 25, 50, 100]
            ],
            order: [[1, 'desc']],
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
                    className: 'no-toggle text-end',
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
            buttons: [
                {
                    extend: 'colvis',
                    text: 'Select Columns',
                    className: 'btn btn-sm border-0 bg-main text-white',
                    columns: [1, 2, 3, 4, 5, 6]
                }
            ],
            drawCallback: function () {
                initializeTooltips();
            }
        });

        initializeTooltips();
    }

    function destroyDataTable() {
        if ($.fn.DataTable.isDataTable('#auditTrailsTable')) {
            $('#auditTrailsTable').DataTable().destroy();
        }
    }

    /**
     * ============================================
     * BUILD TABLE ROW
     * ============================================
     */
    function buildTableRow(audit) {
        const timestamp = formatTimestamp(audit.timestamp);
        const category = audit.category || 'System';
        const severity = (audit.severity || 'info').toLowerCase();

        const userName = escapeHtml(audit?.user?.name || 'System');
        const userRole = escapeHtml(audit?.user?.role || 'N/A');
        const userAvatar = escapeHtml(audit?.user?.avatar || 'S');
        const description = escapeHtml(audit.description || '-');
        const ipAddress = escapeHtml(audit.ipAddress || '-');
        const device = escapeHtml(audit.device || '-');
        const organization = escapeHtml(audit.organization || 'N/A');
        const branch = escapeHtml(audit.branch || 'N/A');

        const categoryColors = {
            Leave: 'bg-info',
            Geofence: 'bg-success',
            Shift: 'bg-primary',
            Security: 'bg-danger',
            Employee: 'bg-warning text-dark',
            System: 'bg-secondary'
        };

        const categoryIcons = {
            Leave: 'bi-calendar-event',
            Geofence: 'bi-geo-alt-fill',
            Shift: 'bi-calendar-week',
            Security: 'bi-shield-lock',
            Employee: 'bi-person',
            System: 'bi-gear'
        };

        const colorClass = categoryColors[category] || 'bg-secondary';
        const categoryIcon = categoryIcons[category] || 'bi-gear';

        const categoryBadge = `
            <span class="badge px-2 rounded-1 ${colorClass}">
                <i class="bi ${categoryIcon} me-1"></i>${escapeHtml(category)}
            </span>
        `;

        const severityBadge = getSeverityBadge(severity);

        const userBlock = `
            <div class="d-flex align-items-center">
                <div class="user-avatar me-2" style="width: 32px; height: 32px; font-size: 0.75rem;">
                    ${userAvatar}
                </div>
                <div>
                    <div class="fw-semibold small">${userName}</div>
                    <small class="text-muted">${userRole}</small>
                </div>
            </div>
        `;

        const ipDevice = `
            <div>
                <div class="small fw-semibold">${ipAddress}</div>
                <small class="text-muted">${device}</small>
            </div>
        `;

        const viewDetailsBtn = `
            <button type="button"
                    class="btn btn-sm btn-primary view-detail-btn"
                    data-bs-toggle="modal"
                    data-bs-target="#auditDetailModal"
                    data-audit-id="${audit.id}"
                    title="View Details">
                <i class="bi bi-eye"></i>
            </button>
        `;

        return `
            <tr
                data-id="${audit.id}"
                data-timestamp="${escapeHtml(audit.timestamp || '')}"
                data-organization="${organization.toLowerCase()}"
                data-category="${String(category).toLowerCase()}"
                data-severity="${severity}">
                <td class="dt-control"></td>
                <td>
                    <div class="small fw-semibold">${timestamp.date}</div>
                    <small class="text-muted">${timestamp.time}</small>
                </td>
                <td>${userBlock}</td>
                <td>${categoryBadge}</td>
                <td>
                    <div class="small">${description}</div>
                </td>
                <td>${severityBadge}</td>
                <td>${ipDevice}</td>
                <td class="text-end">${viewDetailsBtn}</td>
            </tr>
        `;
    }

    /**
     * ============================================
     * EVENT HANDLERS
     * ============================================
     */
    function initializeEventHandlers() {
        $('#applyFiltersBtn').on('click', async function () {
            await refreshAuditTrailsTable();
        });

        $('#clearFiltersBtn').on('click', async function () {
            $('#filterDateFrom').val('');
            $('#filterDateTo').val('');
            $('#filterOrganization').val('');
            $('#filterCategory').val('');
            $('#filterSeverity').val('');

            await refreshAuditTrailsTable();
        });

        $('#exportBtn').on('click', handleExport);

        $(document).on('click', '.view-detail-btn', function () {
            const auditId = parseInt($(this).data('audit-id'), 10);
            const audit = auditTrailsData.find(function (item) {
                return Number(item.id) === auditId;
            });

            if (audit) {
                populateDetailModal(audit);
            }
        });

        $('#exportDetailBtn').on('click', function () {
            showSuccess('Export detail functionality will be implemented with backend integration.');
        });
    }

    /**
     * ============================================
     * DETAIL MODAL
     * ============================================
     */
    function populateDetailModal(audit) {
        const timestamp = formatTimestamp(audit.timestamp);
        const category = audit.category || 'System';
        const severity = (audit.severity || 'info').toLowerCase();

        $('#detailTimestamp').text(`${timestamp.date} at ${timestamp.time}`);

        $('#detailUser').html(`
            <div class="d-flex align-items-center">
                <div class="user-avatar me-2" style="width: 32px; height: 32px; font-size: 0.75rem;">
                    ${escapeHtml(audit?.user?.avatar || 'S')}
                </div>
                <div>
                    <div class="fw-semibold">${escapeHtml(audit?.user?.name || 'System')}</div>
                    <small class="text-muted">${escapeHtml(audit?.user?.role || 'N/A')}</small>
                </div>
            </div>
        `);

        const categoryColors = {
            Leave: 'bg-info',
            Geofence: 'bg-success',
            Shift: 'bg-primary',
            Security: 'bg-danger',
            Employee: 'bg-warning text-dark',
            System: 'bg-secondary'
        };

        const categoryIcons = {
            Leave: 'bi-calendar-event',
            Geofence: 'bi-geo-alt-fill',
            Shift: 'bi-calendar-week',
            Security: 'bi-shield-lock',
            Employee: 'bi-person',
            System: 'bi-gear'
        };

        const colorClass = categoryColors[category] || 'bg-secondary';
        const categoryIcon = categoryIcons[category] || 'bi-gear';

        $('#detailCategory').html(`
            <span class="badge px-2 py-1 ${colorClass}">
                <i class="bi ${categoryIcon} me-1"></i>${escapeHtml(category)}
            </span>
        `);

        $('#detailSeverity').html(getSeverityBadge(severity, true));
        $('#detailDescription').text(audit.description || '-');
        $('#detailIPAddress').text(audit.ipAddress || '-');
        $('#detailDevice').text(audit.device || '-');
        $('#detailLocation').text(audit.branch || '-');
        $('#detailOrganization').text(audit.organization || '-');

        const changesBody = $('#changesTableBody');
        changesBody.empty();

        if (audit.hasChanges && Array.isArray(audit.changes) && audit.changes.length > 0) {
            $('#changesSection').show();

            audit.changes.forEach(function (change) {
                const beforeValue = change.before ?? '-';
                const afterValue = change.after ?? '-';
                const actionBadge = getChangeActionBadge(beforeValue, afterValue);
                changesBody.append(`
                    <tr>
                        <td><strong>${escapeHtml(change.field || '-')}</strong></td>
                        <td><span class="badge bg-secondary">${escapeHtml(beforeValue)}</span></td>
                        <td><span class="badge bg-primary">${escapeHtml(afterValue)}</span></td>
                        <td class="text-center">${actionBadge}</td>
                    </tr>
                `);
            });
        } else {
            $('#changesSection').hide();
        }

        if (audit.context) {
            $('#contextSection').show();
            $('#detailContext').html(`
                <div class="alert alert-warning mb-0">
                    <small>${escapeHtml(audit.context)}</small>
                </div>
            `);
        } else {
            $('#contextSection').hide();
            $('#detailContext').html('-');
        }
    }

    /**
     * ============================================
     * COUNTERS
     * ============================================
     */
    function updateCounters() {
        let totalActivities = 0;
        let criticalEvents = 0;
        let securityActions = 0;
        const activeUsers = new Set();

        auditTrailsData.forEach(function (audit) {
            totalActivities++;

            const severity = (audit.severity || '').toLowerCase();
            const category = (audit.category || '').toLowerCase();
            const userName = audit?.user?.name || 'System';

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

    /**
     * ============================================
     * EXPORT
     * ============================================
     */
    function handleExport() {
        if (!auditTrailsData.length) {
            showError('No audit trail records to export.', 'Info');
            return;
        }

        const headers = ['Timestamp', 'User', 'Role', 'Category', 'Description', 'Severity', 'IP Address', 'Device', 'Organization', 'Branch'];
        let csv = headers.join(',') + '\n';

        auditTrailsData.forEach(function (audit) {
            const row = [
                audit.timestamp || '',
                audit?.user?.name || 'System',
                audit?.user?.role || 'N/A',
                audit.category || '',
                audit.description || '',
                audit.severity || '',
                audit.ipAddress || '',
                audit.device || '',
                audit.organization || '',
                audit.branch || ''
            ].map(csvEscape);

            csv += row.join(',') + '\n';
        });

        downloadCsv(csv, 'audit_trails_' + new Date().toISOString().split('T')[0] + '.csv');
    }

    /**
     * ============================================
     * HELPERS
     * ============================================
     */
    function formatTimestamp(timestamp) {
        if (!timestamp) {
            return {
                date: '-',
                time: '-'
            };
        }

        const date = new Date(timestamp);

        if (Number.isNaN(date.getTime())) {
            return {
                date: '-',
                time: '-'
            };
        }

        return {
            date: date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            }),
            time: date.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            })
        };
    }

    function getSeverityBadge(severity, compact = false) {
        const cls = compact ? 'px-2 py-1' : 'px-2 rounded-1';

        switch (severity) {
            case 'critical':
                return `<span class="badge ${cls} bg-danger"><i class="bi bi-exclamation-triangle me-1"></i>Critical</span>`;
            case 'warning':
                return `<span class="badge ${cls} bg-warning text-dark"><i class="bi bi-exclamation-circle me-1"></i>Warning</span>`;
            case 'success':
                return `<span class="badge ${cls} bg-success"><i class="bi bi-check-circle me-1"></i>Success</span>`;
            case 'info':
            default:
                return `<span class="badge ${cls} bg-info"><i class="bi bi-info-circle me-1"></i>Info</span>`;
        }
    }

    function getChangeActionBadge(beforeValue, afterValue) {
        const before = String(beforeValue ?? '').trim();
        const after = String(afterValue ?? '').trim();
        const beforeEmpty = before === '' || before === '-' || before.toLowerCase() === 'null';
        const afterEmpty = after === '' || after === '-' || after.toLowerCase() === 'null';

        if (beforeEmpty && !afterEmpty) {
            return '<span class="badge bg-success">Added</span>';
        }
        if (!beforeEmpty && afterEmpty) {
            return '<span class="badge bg-danger">Removed</span>';
        }
        if (before === after) {
            return '<span class="badge bg-secondary">No Change</span>';
        }

        return '<span class="badge bg-warning text-dark">Updated</span>';
    }

    function initializeTooltips() {
        if (typeof bootstrap === 'undefined') return;

        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (element) {
            bootstrap.Tooltip.getOrCreateInstance(element);
        });
    }

    function showTableError(message) {
        destroyDataTable();

        $('#auditTrailsTableBody').empty();

        // Optionally show an alert or toast with the error message
        console.error('Table Error:', message);

        $('#totalActivities').text(0);
        $('#criticalEvents').text(0);
        $('#securityActions').text(0);
        $('#activeUsers').text(0);
    }

    function escapeHtml(value) {
        if (value === null || value === undefined) return '';

        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function csvEscape(value) {
        const text = String(value ?? '');
        return '"' + text.replace(/"/g, '""') + '"';
    }

    function downloadCsv(csv, fileName) {
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.setAttribute('href', url);
        link.setAttribute('download', fileName);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    }
})();