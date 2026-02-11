/**
 * Workflows Module
 * Manage approval chains and request routing
 */

(function () {
    'use strict';

    // ============================================
    // GLOBAL VARIABLES
    // ============================================
    let workflowsTable;
    let workflowsData = [];
    let customFilterFunction = null;
    let approvalLevelCounter = 0;

    // ============================================
    // INITIALIZATION
    // ============================================
    $(document).ready(function () {
        loadWorkflowsData();
        initializeDataTable();
        initializeEventHandlers();
        updateCounters();
    });

    // ============================================
    // DATA LOADING
    // ============================================
    function loadWorkflowsData() {
        if (typeof ProjectData !== 'undefined' && ProjectData.workflows) {
            workflowsData = ProjectData.workflows.generateSampleData(20);
        } else {
            console.warn('ProjectData not found, using empty array');
            workflowsData = [];
        }
    }

    // ============================================
    // DATA TABLE INITIALIZATION
    // ============================================
    function initializeDataTable() {
        const tbody = $('#workflowsTableBody');
        tbody.empty();

        workflowsData.forEach(workflow => {
            const row = buildTableRow(workflow);
            tbody.append(row);
        });

        workflowsTable = initUserDataTable('#workflowsTable', {
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            order: [[0, 'asc']],
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
                searchPlaceholder: "Search workflows...",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ workflows",
                infoEmpty: "No workflows available",
                zeroRecords: "No matching workflows found"
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
    function buildTableRow(workflow) {
        // Request Type Badge
        let requestTypeBadge = '';
        const requestTypeColors = {
            'Leave': 'bg-info',
            'Overtime': 'bg-warning text-dark',
            'Regularization': 'bg-primary',
            'Shift': 'bg-success'
        };
        const requestTypeIcon = {
            'Leave': 'bi-calendar-event',
            'Overtime': 'bi-hourglass-split',
            'Regularization': 'bi-patch-check',
            'Shift': 'bi-calendar-week'
        };
        const colorClass = requestTypeColors[workflow.requestType] || 'bg-secondary';
        requestTypeBadge = `<span class="badge px-2 rounded-1 ${colorClass}"><i class="bi ${requestTypeIcon[workflow.requestType]} me-1"></i>${workflow.requestType}</span>`;

        // Approval Levels
        const levelsCount = workflow.approvalLevels.length;
        const levelsDisplay = workflow.approvalLevels.map(level => `L${level.level}: ${level.role}`).join(' → ');

        // Applicable To
        let applicableToBadge = '';
        if (workflow.organization === 'Global') {
            applicableToBadge = '<span class="badge px-2 rounded-1 bg-primary"><i class="bi bi-globe me-1"></i>Global</span>';
        } else {
            let badgeText = workflow.organization;
            if (workflow.branch) {
                badgeText += ` - ${workflow.branch}`;
            }
            applicableToBadge = `<span class="badge px-2 rounded-1 bg-info"><i class="bi bi-building me-1"></i>${badgeText}</span>`;
        }

        // SLA
        const slaDisplay = `${workflow.slaHours} hrs${workflow.escalateTo ? ' (Auto-escalate)' : ''}`;

        // Status Badge
        let statusBadge = '';
        if (workflow.status === 'active') {
            statusBadge = '<span class="badge px-2 rounded-1 bg-success"><i class="bi bi-check-circle me-1"></i>Active</span>';
        } else {
            statusBadge = '<span class="badge px-2 rounded-1 bg-secondary"><i class="bi bi-x-circle me-1"></i>Inactive</span>';
        }

        // View Details Button
        const viewBtn = `
            <button type="button" 
                    class="btn btn-sm btn-primary view-workflow-btn" 
                    data-bs-toggle="offcanvas"
                    data-bs-target="#workflowDetailCanvas"
                    data-workflow-id="${workflow.id}"
                    title="View Details">
                <i class="bi bi-eye"></i>
            </button>
        `;

        return `
            <tr data-request-type="${workflow.requestType.toLowerCase()}" 
                data-status="${workflow.status}" 
                data-organization="${workflow.organization.toLowerCase().replace(/\s+/g, '-')}" 
                data-branch="${workflow.branch ? workflow.branch.toLowerCase() : ''}">
                <td class="dt-control"></td>
                <td>
                    <div class="fw-semibold">${workflow.name}</div>
                </td>
                <td>${requestTypeBadge}</td>
                <td>
                    <div class="small fw-semibold">${levelsCount} Level${levelsCount > 1 ? 's' : ''}</div>
                    <small class="text-muted" title="${levelsDisplay}">${levelsDisplay.length > 50 ? levelsDisplay.substring(0, 50) + '...' : levelsDisplay}</small>
                </td>
                <td>${applicableToBadge}</td>
                <td>
                    <div class="small fw-semibold">${slaDisplay}</div>
                </td>
                <td>${statusBadge}</td>
                <td class="text-end">${viewBtn}</td>
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

        // Create Workflow Form
        $('#createWorkflowForm').on('submit', handleCreateWorkflow);

        // Add Approval Level
        $('#addApprovalLevelBtn').on('click', addApprovalLevel);

        // Remove Approval Level
        $(document).on('click', '.remove-level-btn', function() {
            $(this).closest('.approval-level-item').remove();
            updateLevelNumbers();
        });

        // View Workflow Detail Canvas
        const detailCanvas = document.getElementById('workflowDetailCanvas');
        if (detailCanvas) {
            detailCanvas.addEventListener('show.bs.offcanvas', function(event) {
                const button = event.relatedTarget;
                if (button && button.classList.contains('view-workflow-btn')) {
                    const workflowId = parseInt($(button).data('workflow-id'));
                    const workflow = workflowsData.find(w => w.id === workflowId);
                    if (workflow) {
                        populateDetailCanvas(workflow);
                    }
                }
            });
        }

        // Edit Workflow
        $('#editWorkflowBtn').on('click', function() {
            alert('Edit functionality will be implemented with backend integration.');
        });

        // Reset form when canvas is closed
        $('#createWorkflowCanvas').on('hidden.bs.offcanvas', function() {
            $('#createWorkflowForm')[0].reset();
            $('#approvalChainContainer').empty();
            approvalLevelCounter = 0;
        });
    }

    // ============================================
    // FILTER FUNCTIONS
    // ============================================
    function applyFilters() {
        const requestType = $('#filterRequestType').val();
        const status = $('#filterStatus').val();
        const organization = $('#filterOrganization').val();
        const branch = $('#filterBranch').val();

        // Remove existing custom filter if any
        if (customFilterFunction) {
            $.fn.dataTable.ext.search.pop();
        }

        // Create custom filtering function
        customFilterFunction = function(settings, data, dataIndex) {
            // Only apply to workflows table
            if (settings.nTable.id !== 'workflowsTable') {
                return true;
            }

            const row = workflowsTable.row(dataIndex).node();
            
            // Request Type filter
            if (requestType) {
                const rowRequestType = $(row).data('request-type');
                if (rowRequestType !== requestType.toLowerCase()) return false;
            }
            
            // Status filter
            if (status) {
                const rowStatus = $(row).data('status');
                if (rowStatus !== status) return false;
            }
            
            // Organization filter
            if (organization) {
                const rowOrg = $(row).data('organization');
                if (organization === 'global') {
                    if (rowOrg !== 'global') return false;
                } else {
                    if (rowOrg !== organization.toLowerCase().replace(/\s+/g, '-')) return false;
                }
            }
            
            // Branch filter
            if (branch) {
                const rowBranch = $(row).data('branch');
                if (rowBranch !== branch.toLowerCase()) return false;
            }
            
            return true;
        };

        // Add custom filter
        $.fn.dataTable.ext.search.push(customFilterFunction);

        // Apply filters
        if (workflowsTable) {
            workflowsTable.draw();
        }

        // Update counters
        updateCounters();
    }

    function clearFilters() {
        $('#filterRequestType').val('');
        $('#filterStatus').val('');
        $('#filterOrganization').val('');
        $('#filterBranch').val('');

        // Remove custom filter
        if (customFilterFunction) {
            $.fn.dataTable.ext.search.pop();
            customFilterFunction = null;
        }

        if (workflowsTable) {
            workflowsTable.draw();
        }

        updateCounters();
    }

    // ============================================
    // WORKFLOW CREATION
    // ============================================
    function handleCreateWorkflow(e) {
        e.preventDefault();
        
        // Get approval levels
        const approvalLevels = [];
        $('.approval-level-item').each(function(index) {
            const role = $(this).find('.approval-role').val();
            if (role) {
                approvalLevels.push({
                    level: index + 1,
                    role: role,
                    approverType: role.toLowerCase().replace(/\s+/g, '-')
                });
            }
        });

        if (approvalLevels.length === 0) {
            alert('Please add at least one approval level.');
            return;
        }

        const workflowData = {
            id: workflowsData.length + 1,
            name: $('#workflowName').val(),
            requestType: $('#workflowRequestType option:selected').text(),
            status: $('#workflowStatus').val(),
            organization: $('#workflowOrganization option:selected').text(),
            branch: $('#workflowBranch').val() || null,
            approvalLevels: approvalLevels,
            slaHours: parseInt($('#workflowSLA').val()),
            escalateTo: $('#workflowEscalateTo').val() || null,
            createdAt: new Date().toISOString()
        };

        // Add to data array
        workflowsData.unshift(workflowData);

        // Rebuild table
        const tbody = $('#workflowsTableBody');
        tbody.empty();
        workflowsData.forEach(workflow => {
            const row = buildTableRow(workflow);
            tbody.append(row);
        });
        workflowsTable.draw();

        // Close canvas and reset form
        const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('createWorkflowCanvas'));
        offcanvas.hide();
        $('#createWorkflowForm')[0].reset();
        $('#approvalChainContainer').empty();
        approvalLevelCounter = 0;

        // Update counters
        updateCounters();

        // Show success message
        alert('Workflow created successfully!');
    }

    // ============================================
    // APPROVAL LEVEL MANAGEMENT
    // ============================================
    function addApprovalLevel() {
        approvalLevelCounter++;
        const levelNumber = $('.approval-level-item').length + 1;
        
        const levelHtml = `
            <div class="approval-level-item mb-3 p-3 border rounded-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="mb-0 fw-semibold small">
                        <i class="bi bi-person-check me-2"></i>Level ${levelNumber}
                    </h6>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-level-btn">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="row g-2">
                    <div class="col-md-12">
                        <label class="form-label small fw-semibold">Approver Role</label>
                        <select class="form-select form-select-sm approval-role" required>
                            <option value="">Select Role</option>
                            <option value="Supervisor">Supervisor</option>
                            <option value="Department Head">Department Head</option>
                            <option value="Manager">Manager</option>
                            <option value="HR Manager">HR Manager</option>
                            <option value="Super Admin">Super Admin</option>
                        </select>
                    </div>
                </div>
            </div>
        `;
        
        $('#approvalChainContainer').append(levelHtml);
    }

    function updateLevelNumbers() {
        $('.approval-level-item').each(function(index) {
            $(this).find('h6').html(`<i class="bi bi-person-check me-2"></i>Level ${index + 1}`);
        });
    }

    // ============================================
    // DETAIL CANVAS POPULATION
    // ============================================
    function populateDetailCanvas(workflow) {
        // Workflow Information
        $('#detailWorkflowName').text(workflow.name);
        
        // Request Type Badge
        const requestTypeColors = {
            'Leave': 'bg-info',
            'Overtime': 'bg-warning text-dark',
            'Regularization': 'bg-primary',
            'Shift': 'bg-success'
        };
        const requestTypeIcon = {
            'Leave': 'bi-calendar-event',
            'Overtime': 'bi-hourglass-split',
            'Regularization': 'bi-patch-check',
            'Shift': 'bi-calendar-week'
        };
        const colorClass = requestTypeColors[workflow.requestType] || 'bg-secondary';
        $('#detailRequestType').html(`<span class="badge px-2 py-1 ${colorClass}"><i class="bi ${requestTypeIcon[workflow.requestType]} me-1"></i>${workflow.requestType}</span>`);

        // Status Badge
        let statusBadge = '';
        if (workflow.status === 'active') {
            statusBadge = '<span class="badge px-2 py-1 bg-success"><i class="bi bi-check-circle me-1"></i>Active</span>';
        } else {
            statusBadge = '<span class="badge px-2 py-1 bg-secondary"><i class="bi bi-x-circle me-1"></i>Inactive</span>';
        }
        $('#detailStatus').html(statusBadge);

        $('#detailOrganization').text(workflow.organization);
        $('#detailBranch').text(workflow.branch || 'All Branches');

        // Approval Chain Visualization
        const chainContainer = $('#detailApprovalChain');
        chainContainer.empty();
        
        workflow.approvalLevels.forEach((level, index) => {
            const isLast = index === workflow.approvalLevels.length - 1;
            const levelHtml = `
                <div class="approval-chain-item mb-3">
                    <div class="d-flex align-items-center">
                        <div class="approval-level-box p-3 rounded-3 border text-center" style="border-color: #ffffff1a !important; min-width: 200px;">
                            <div class="small opacity-75 text-white mb-1">Level ${level.level}</div>
                            <div class="fw-semibold text-white">${level.role}</div>
                        </div>
                        ${!isLast ? '<div class="mx-3"><i class="bi bi-arrow-right text-white fs-4"></i></div>' : ''}
                    </div>
                </div>
            `;
            chainContainer.append(levelHtml);
        });

        // SLA & Escalation
        $('#detailSLA').text(`${workflow.slaHours} hours`);
        
        let escalateToHtml = '<span class="text-muted">No auto-escalation</span>';
        if (workflow.escalateTo) {
            escalateToHtml = `<span class="badge px-2 py-1 bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>${workflow.escalateTo}</span>`;
        }
        $('#detailEscalateTo').html(escalateToHtml);
    }

    // ============================================
    // UTILITY FUNCTIONS
    // ============================================
    function handleExport() {
        alert('Export functionality will be implemented with backend integration.');
    }

    // ============================================
    // COUNTERS UPDATE
    // ============================================
    function updateCounters() {
        if (!workflowsTable) return;

        let totalWorkflows = 0;
        let activeWorkflows = 0;
        const requestTypes = new Set();
        let totalSLAHours = 0;
        let slaCount = 0;

        workflowsTable.rows({ search: 'applied' }).every(function () {
            const row = this.node();
            const status = $(row).data('status');
            const requestType = $(row).find('td:eq(2)').text().trim();
            
            totalWorkflows++;
            requestTypes.add(requestType);
            
            if (status === 'active') {
                activeWorkflows++;
            }

            // Extract SLA hours from the SLA column
            const slaText = $(row).find('td:eq(5)').text();
            const slaMatch = slaText.match(/(\d+)\s*hrs/);
            if (slaMatch) {
                totalSLAHours += parseInt(slaMatch[1]);
                slaCount++;
            }
        });

        const avgSLA = slaCount > 0 ? Math.round(totalSLAHours / slaCount) : 0;

        $('#totalWorkflows').text(totalWorkflows);
        $('#activeWorkflows').text(activeWorkflows);
        $('#requestTypes').text(requestTypes.size);
        $('#avgApprovalTime').text(avgSLA + ' hrs');
    }

})();

