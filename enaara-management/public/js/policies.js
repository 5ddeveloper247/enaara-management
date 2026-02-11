/**
 * Policy Management Module
 * Manage organization-wide rules and documentation
 */

(function () {
    'use strict';

    // ============================================
    // GLOBAL VARIABLES
    // ============================================
    let policiesTable;
    let policiesData = [];
    let customFilterFunction = null;

    // ============================================
    // INITIALIZATION
    // ============================================
    $(document).ready(function () {
        loadPoliciesData();
        initializeDataTable();
        initializeEventHandlers();
        updateCounters();
    });

    // ============================================
    // DATA LOADING
    // ============================================
    function loadPoliciesData() {
        if (typeof ProjectData !== 'undefined' && ProjectData.policies) {
            policiesData = ProjectData.policies.generateSampleData(30);
        } else {
            console.warn('ProjectData not found, using empty array');
            policiesData = [];
        }
    }

    // ============================================
    // DATA TABLE INITIALIZATION
    // ============================================
    function initializeDataTable() {
        const tbody = $('#policiesTableBody');
        tbody.empty();

        policiesData.forEach(policy => {
            const row = buildTableRow(policy);
            tbody.append(row);
        });

        policiesTable = initUserDataTable('#policiesTable', {
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            order: [[6, 'desc']], // Sort by Last Updated descending
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
                searchPlaceholder: "Search policies...",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ policies",
                infoEmpty: "No policies available",
                zeroRecords: "No matching policies found"
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
    function buildTableRow(policy) {
        // Category Badge
        let categoryBadge = '';
        const categoryColors = {
            'Leave Policy': 'bg-info',
            'Attendance Grace Period': 'bg-primary',
            'Geofencing Rules': 'bg-success',
            'Shift Rota Protocols': 'bg-warning text-dark',
            'Security Policy': 'bg-danger',
            'HR Policy': 'bg-secondary'
        };
        const categoryIcon = {
            'Leave Policy': 'bi-calendar-event',
            'Attendance Grace Period': 'bi-clock-history',
            'Geofencing Rules': 'bi-geo-alt-fill',
            'Shift Rota Protocols': 'bi-calendar-week',
            'Security Policy': 'bi-shield-lock',
            'HR Policy': 'bi-people'
        };
        const colorClass = categoryColors[policy.category] || 'bg-secondary';
        categoryBadge = `<span class="badge px-2 rounded-1 ${colorClass}"><i class="bi ${categoryIcon[policy.category]} me-1"></i>${policy.category}</span>`;

        // Applicable To
        let applicableToBadge = '';
        if (policy.applicableTo === 'global') {
            applicableToBadge = '<span class="badge px-2 rounded-1 bg-primary"><i class="bi bi-globe me-1"></i>Global</span>';
        } else if (policy.applicableTo === 'organization') {
            applicableToBadge = `<span class="badge px-2 rounded-1 bg-info"><i class="bi bi-building me-1"></i>${policy.applicableDetails}</span>`;
        } else if (policy.applicableTo === 'branch') {
            applicableToBadge = `<span class="badge px-2 rounded-1 bg-success"><i class="bi bi-geo-alt me-1"></i>${policy.applicableDetails}</span>`;
        } else if (policy.applicableTo === 'floor') {
            applicableToBadge = `<span class="badge px-2 rounded-1 bg-warning text-dark"><i class="bi bi-building me-1"></i>${policy.applicableDetails}</span>`;
        }

        // Status Badge
        let statusBadge = '';
        if (policy.status === 'active') {
            statusBadge = '<span class="badge px-2 rounded-1 bg-success"><i class="bi bi-check-circle me-1"></i>Active</span>';
        } else if (policy.status === 'draft') {
            statusBadge = '<span class="badge px-2 rounded-1 bg-warning text-dark"><i class="bi bi-file-earmark me-1"></i>Draft</span>';
        } else if (policy.status === 'archived') {
            statusBadge = '<span class="badge px-2 rounded-1 bg-secondary"><i class="bi bi-archive me-1"></i>Archived</span>';
        }

        // Dates
        const effectiveDate = formatDate(policy.effectiveDate);
        const lastUpdated = formatTimestamp(policy.lastUpdated);

        // Actions
        const viewBtn = `
            <button type="button" 
                    class="btn btn-sm btn-primary view-policy-btn" 
                    data-bs-toggle="offcanvas"
                    data-bs-target="#policyDetailCanvas"
                    data-policy-id="${policy.id}"
                    title="View Details">
                <i class="bi bi-eye"></i>
            </button>
        `;

        return `
            <tr data-category="${policy.category.toLowerCase().replace(/\s+/g, '-')}" 
                data-status="${policy.status}" 
                data-organization="${policy.organization ? policy.organization.toLowerCase().replace(/\s+/g, '-') : ''}" 
                data-applicable-to="${policy.applicableTo}">
                <td class="dt-control"></td>
                <td>
                    <div class="fw-semibold">${policy.title}</div>
                </td>
                <td>${categoryBadge}</td>
                <td>${applicableToBadge}</td>
                <td>
                    <div class="small fw-semibold">${effectiveDate}</div>
                </td>
                <td>${statusBadge}</td>
                <td>
                    <div class="small fw-semibold">${lastUpdated.date}</div>
                    <small class="text-muted">${lastUpdated.time}</small>
                </td>
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

        // Create Policy Form
        $('#createPolicyForm').on('submit', handleCreatePolicy);

        // Applicable To change handler
        $('#policyApplicableTo').on('change', function() {
            const value = $(this).val();
            $('#organizationSelect, #branchSelect, #floorSelect').hide();
            
            if (value === 'organization') {
                $('#organizationSelect').show();
            } else if (value === 'branch') {
                $('#branchSelect').show();
            } else if (value === 'floor') {
                $('#floorSelect').show();
            }
        });

        // View Policy Detail Canvas
        const detailCanvas = document.getElementById('policyDetailCanvas');
        if (detailCanvas) {
            detailCanvas.addEventListener('show.bs.offcanvas', function(event) {
                const button = event.relatedTarget;
                if (button && button.classList.contains('view-policy-btn')) {
                    const policyId = parseInt($(button).data('policy-id'));
                    const policy = policiesData.find(p => p.id === policyId);
                    if (policy) {
                        populateDetailCanvas(policy);
                    }
                }
            });
        }

        // View/Download Document
        $('#viewDocumentBtn, #downloadDocumentBtn').on('click', function() {
            const action = $(this).attr('id') === 'viewDocumentBtn' ? 'view' : 'download';
            alert(`${action === 'view' ? 'Viewing' : 'Downloading'} document functionality will be implemented with backend integration.`);
        });

        // Edit Policy
        $('#editPolicyBtn').on('click', function() {
            alert('Edit functionality will be implemented with backend integration.');
        });
    }

    // ============================================
    // FILTER FUNCTIONS
    // ============================================
    function applyFilters() {
        const category = $('#filterCategory').val();
        const status = $('#filterStatus').val();
        const organization = $('#filterOrganization').val();
        const applicableTo = $('#filterApplicableTo').val();

        // Remove existing custom filter if any
        if (customFilterFunction) {
            $.fn.dataTable.ext.search.pop();
        }

        // Create custom filtering function
        customFilterFunction = function(settings, data, dataIndex) {
            // Only apply to policies table
            if (settings.nTable.id !== 'policiesTable') {
                return true;
            }

            const row = policiesTable.row(dataIndex).node();
            
            // Category filter
            if (category) {
                const rowCategory = $(row).data('category');
                const filterCategory = category.toLowerCase().replace(/\s+/g, '-');
                if (rowCategory !== filterCategory) return false;
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
                    if ($(row).data('applicable-to') !== 'global') return false;
                } else {
                    if (rowOrg !== organization.toLowerCase().replace(/\s+/g, '-')) return false;
                }
            }
            
            // Applicable To filter
            if (applicableTo) {
                const rowApplicableTo = $(row).data('applicable-to');
                if (rowApplicableTo !== applicableTo) return false;
            }
            
            return true;
        };

        // Add custom filter
        $.fn.dataTable.ext.search.push(customFilterFunction);

        // Apply filters
        if (policiesTable) {
            policiesTable.draw();
        }

        // Update counters
        updateCounters();
    }

    function clearFilters() {
        $('#filterCategory').val('');
        $('#filterStatus').val('');
        $('#filterOrganization').val('');
        $('#filterApplicableTo').val('');

        // Remove custom filter
        if (customFilterFunction) {
            $.fn.dataTable.ext.search.pop();
            customFilterFunction = null;
        }

        if (policiesTable) {
            policiesTable.draw();
        }

        updateCounters();
    }

    // ============================================
    // POLICY CREATION
    // ============================================
    function handleCreatePolicy(e) {
        e.preventDefault();
        
        const policyData = {
            id: policiesData.length + 1,
            title: $('#policyTitle').val(),
            category: $('#policyCategory option:selected').text(),
            status: $('#policyStatus').val(),
            effectiveDate: $('#policyEffectiveDate').val(),
            lastUpdated: new Date().toISOString(),
            applicableTo: $('#policyApplicableTo').val(),
            organization: $('#policyOrganization').val() || null,
            branch: $('#policyBranch').val() || null,
            floor: $('#policyFloor').val() || null,
            description: $('#policyDescription').val() || '',
            hasDocument: $('#policyDocument')[0].files.length > 0,
            documentName: $('#policyDocument')[0].files.length > 0 ? $('#policyDocument')[0].files[0].name : null,
            documentSize: $('#policyDocument')[0].files.length > 0 ? (($('#policyDocument')[0].files[0].size / 1024 / 1024).toFixed(2)) + ' MB' : null
        };

        // Set applicable details
        if (policyData.applicableTo === 'global') {
            policyData.applicableDetails = 'Global (All Organizations)';
        } else if (policyData.applicableTo === 'organization') {
            policyData.applicableDetails = $('#policyOrganization option:selected').text();
        } else if (policyData.applicableTo === 'branch') {
            policyData.applicableDetails = $('#policyBranch option:selected').text() + ' Branch';
        } else if (policyData.applicableTo === 'floor') {
            policyData.applicableDetails = 'Floor ' + $('#policyFloor').val();
        }

        // Add to data array
        policiesData.unshift(policyData);

        // Rebuild table
        const tbody = $('#policiesTableBody');
        tbody.empty();
        policiesData.forEach(policy => {
            const row = buildTableRow(policy);
            tbody.append(row);
        });
        policiesTable.draw();

        // Close modal and reset form
        const modal = bootstrap.Modal.getInstance(document.getElementById('createPolicyModal'));
        modal.hide();
        $('#createPolicyForm')[0].reset();
        $('#organizationSelect, #branchSelect, #floorSelect').hide();

        // Update counters
        updateCounters();

        // Show success message
        alert('Policy created successfully!');
    }

    // ============================================
    // DETAIL CANVAS POPULATION
    // ============================================
    function populateDetailCanvas(policy) {
        // Policy Information
        $('#detailTitle').text(policy.title);
        
        // Category Badge
        const categoryColors = {
            'Leave Policy': 'bg-info',
            'Attendance Grace Period': 'bg-primary',
            'Geofencing Rules': 'bg-success',
            'Shift Rota Protocols': 'bg-warning text-dark',
            'Security Policy': 'bg-danger',
            'HR Policy': 'bg-secondary'
        };
        const categoryIcon = {
            'Leave Policy': 'bi-calendar-event',
            'Attendance Grace Period': 'bi-clock-history',
            'Geofencing Rules': 'bi-geo-alt-fill',
            'Shift Rota Protocols': 'bi-calendar-week',
            'Security Policy': 'bi-shield-lock',
            'HR Policy': 'bi-people'
        };
        const colorClass = categoryColors[policy.category] || 'bg-secondary';
        $('#detailCategory').html(`<span class="badge px-2 py-1 ${colorClass}"><i class="bi ${categoryIcon[policy.category]} me-1"></i>${policy.category}</span>`);

        // Status Badge
        let statusBadge = '';
        if (policy.status === 'active') {
            statusBadge = '<span class="badge px-2 py-1 bg-success"><i class="bi bi-check-circle me-1"></i>Active</span>';
        } else if (policy.status === 'draft') {
            statusBadge = '<span class="badge px-2 py-1 bg-warning text-dark"><i class="bi bi-file-earmark me-1"></i>Draft</span>';
        } else if (policy.status === 'archived') {
            statusBadge = '<span class="badge px-2 py-1 bg-secondary"><i class="bi bi-archive me-1"></i>Archived</span>';
        }
        $('#detailStatus').html(statusBadge);

        $('#detailEffectiveDate').text(formatDate(policy.effectiveDate));
        
        const lastUpdated = formatTimestamp(policy.lastUpdated);
        $('#detailLastUpdated').text(`${lastUpdated.date} at ${lastUpdated.time}`);

        // Applicable To
        let applicableToHtml = '';
        if (policy.applicableTo === 'global') {
            applicableToHtml = '<span class="badge px-2 py-1 bg-primary"><i class="bi bi-globe me-1"></i>Global (All Organizations)</span>';
        } else if (policy.applicableTo === 'organization') {
            applicableToHtml = `<span class="badge px-2 py-1 bg-info"><i class="bi bi-building me-1"></i>${policy.applicableDetails}</span>`;
        } else if (policy.applicableTo === 'branch') {
            applicableToHtml = `<span class="badge px-2 py-1 bg-success"><i class="bi bi-geo-alt me-1"></i>${policy.applicableDetails}</span>`;
        } else if (policy.applicableTo === 'floor') {
            applicableToHtml = `<span class="badge px-2 py-1 bg-warning text-dark"><i class="bi bi-building me-1"></i>${policy.applicableDetails}</span>`;
        }
        $('#detailApplicableTo').html(applicableToHtml);

        // Description
        if (policy.description) {
            $('#descriptionSection').show();
            $('#detailDescription').html(policy.description.replace(/\n/g, '<br>'));
        } else {
            $('#descriptionSection').hide();
        }

        // Document
        if (policy.hasDocument && policy.documentName) {
            $('#documentSection').show();
            $('#detailDocumentName').text(policy.documentName);
            $('#detailDocumentSize').text(policy.documentSize || '');
            $('#viewDocumentBtn, #downloadDocumentBtn').data('document-name', policy.documentName);
        } else {
            $('#documentSection').hide();
        }
    }

    // ============================================
    // UTILITY FUNCTIONS
    // ============================================
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
    }

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
        if (!policiesTable) return;

        let totalPolicies = 0;
        let activePolicies = 0;
        let draftPolicies = 0;
        let archivedPolicies = 0;

        policiesTable.rows({ search: 'applied' }).every(function () {
            const row = this.node();
            const status = $(row).data('status');

            totalPolicies++;
            
            if (status === 'active') {
                activePolicies++;
            } else if (status === 'draft') {
                draftPolicies++;
            } else if (status === 'archived') {
                archivedPolicies++;
            }
        });

        $('#totalPolicies').text(totalPolicies);
        $('#activePolicies').text(activePolicies);
        $('#draftPolicies').text(draftPolicies);
        $('#archivedPolicies').text(archivedPolicies);
    }

})();

