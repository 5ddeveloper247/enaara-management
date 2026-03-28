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
    let isEditMode = false;
    let editPolicyId = null;

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
        if (typeof dbPolicies !== 'undefined' && dbPolicies.length > 0) {
            policiesData = dbPolicies.map(p => ({
                id: p.id,
                title: p.title,
                category: p.category,
                status: p.status,
                effectiveDate: p.effective_date,
                lastUpdated: p.updated_at,
                applicableTo: p.applicable_to,
                applicableDetails: p.applicable_details || '',
                description: p.description || '',
                hasDocument: !!p.document_path,
                documentName: p.document_name || null,
                documentPath: p.document_path || null,
                documentSize: null
            }));
        } else {
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
        const actions = `
            <div class="d-flex gap-1 justify-content-end" style="white-space: nowrap;">
                <button type="button" 
                        class="btn btn-sm btn-primary view-policy-btn" 
                        data-bs-toggle="offcanvas"
                        data-bs-target="#policyDetailCanvas"
                        data-policy-id="${policy.id}"
                        title="View Details">
                    <i class="bi bi-eye"></i>
                </button>
                <button type="button" 
                        class="btn btn-sm btn-outline-secondary edit-policy-btn" 
                        data-policy-id="${policy.id}"
                        title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
                <button type="button" 
                        class="btn btn-sm btn-outline-danger delete-policy-btn" 
                        data-policy-id="${policy.id}"
                        title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;

        return `
            <tr data-category="${policy.category.toLowerCase().replace(/\s+/g, '-')}" 
                data-status="${policy.status}" 
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
                <td class="text-end">${actions}</td>
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
                    fetchAndPopulateDetailCanvas(policyId);
                }
            });
        }

        // View/Download Document
        $('#viewDocumentBtn').on('click', function() {
            const docPath = $(this).data('document-path');
            if (docPath) {
                window.open('/storage/' + docPath, '_blank');
            }
        });

        $('#downloadDocumentBtn').on('click', function() {
            const docPath = $(this).data('document-path');
            if (docPath) {
                const link = document.createElement('a');
                link.href = '/storage/' + docPath;
                link.download = $(this).data('document-name') || 'document';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        });

        // Edit Policy from detail canvas
        $('#editPolicyBtn').on('click', function() {
            const policyId = $(this).data('current-policy-id');
            if (policyId) {
                // Close offcanvas
                const offcanvasEl = document.getElementById('policyDetailCanvas');
                const offcanvasInstance = bootstrap.Offcanvas.getInstance(offcanvasEl);
                if (offcanvasInstance) offcanvasInstance.hide();
                
                // Trigger edit
                openEditModal(policyId);
            }
        });

        // Edit Policy from table
        $(document).on('click', '.edit-policy-btn', function() {
            const policyId = $(this).data('policy-id');
            openEditModal(policyId);
        });

        // Delete Policy from table
        $(document).on('click', '.delete-policy-btn', function() {
            const policyId = $(this).data('policy-id');
            handleDeletePolicy(policyId);
        });

        // Reset modal when closed
        const createModal = document.getElementById('createPolicyModal');
        if (createModal) {
            createModal.addEventListener('hidden.bs.modal', function () {
                resetModal();
            });
        }
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
    // POLICY CREATION / UPDATE
    // ============================================
    function handleCreatePolicy(e) {
        e.preventDefault();
        
        const formData = new FormData();
        formData.append('title', $('#policyTitle').val());
        formData.append('category', $('#policyCategory option:selected').text());
        formData.append('status', $('#policyStatus').val());
        formData.append('effective_date', $('#policyEffectiveDate').val());
        formData.append('applicable_to', $('#policyApplicableTo').val());

        // Set applicable details
        const applicableTo = $('#policyApplicableTo').val();
        let applicableDetails = '';
        if (applicableTo === 'global') {
            applicableDetails = 'Global (All Organizations)';
        } else if (applicableTo === 'organization') {
            applicableDetails = $('#policyOrganization option:selected').text();
        } else if (applicableTo === 'branch') {
            applicableDetails = $('#policyBranch option:selected').text() + ' Branch';
        } else if (applicableTo === 'floor') {
            applicableDetails = 'Floor ' + $('#policyFloor').val();
        }
        formData.append('applicable_details', applicableDetails);

        formData.append('description', $('#policyDescription').val() || '');

        // File upload
        const fileInput = $('#policyDocument')[0];
        if (fileInput.files.length > 0) {
            formData.append('document', fileInput.files[0]);
        }

        // Determine URL and method
        let url = '/admin/policies';
        if (isEditMode && editPolicyId) {
            url = `/admin/policies/${editPolicyId}`;
        }

        // Submit button state
        const submitBtn = $('#createPolicyForm button[type="submit"]');
        const originalBtnText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('createPolicyModal'));
                    if (modal) modal.hide();

                    Swal.fire({
                        icon: 'success',
                        title: isEditMode ? 'Updated!' : 'Created!',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                }
            },
            error: function(xhr) {
                let errorMsg = 'An error occurred. Please try again.';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.errors) {
                        errorMsg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                    } else if (xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                }
                Swal.fire('Error', errorMsg, 'error');
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalBtnText);
            }
        });
    }

    // ============================================
    // EDIT POLICY
    // ============================================
    function openEditModal(policyId) {
        // Fetch policy data
        $.ajax({
            url: `/admin/policies/${policyId}`,
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success && response.policy) {
                    const p = response.policy;
                    
                    isEditMode = true;
                    editPolicyId = p.id;

                    // Update modal title
                    $('#createPolicyModalLabel').html('<i class="bi bi-pencil me-2"></i>Edit Policy');
                    $('#createPolicyForm button[type="submit"]').html('<i class="bi bi-check-circle me-1"></i>Update Policy');

                    // Populate form fields
                    $('#policyTitle').val(p.title);

                    // Match category by text
                    const categoryMap = {
                        'Leave Policy': 'leave',
                        'Attendance Grace Period': 'attendance',
                        'Geofencing Rules': 'geofence',
                        'Shift Rota Protocols': 'shift',
                        'Security Policy': 'security',
                        'HR Policy': 'hr'
                    };
                    $('#policyCategory').val(categoryMap[p.category] || '');

                    $('#policyStatus').val(p.status);
                    $('#policyEffectiveDate').val(p.effective_date ? p.effective_date.split('T')[0] : '');
                    $('#policyApplicableTo').val(p.applicable_to).trigger('change');
                    $('#policyDescription').val(p.description || '');

                    // Open modal
                    const modal = new bootstrap.Modal(document.getElementById('createPolicyModal'));
                    modal.show();
                }
            },
            error: function() {
                Swal.fire('Error', 'Unable to fetch policy details.', 'error');
            }
        });
    }

    function resetModal() {
        isEditMode = false;
        editPolicyId = null;
        $('#createPolicyModalLabel').html('<i class="bi bi-plus-circle me-2"></i>Create New Policy');
        $('#createPolicyForm button[type="submit"]').html('<i class="bi bi-check-circle me-1"></i>Create Policy');
        $('#createPolicyForm')[0].reset();
        $('#organizationSelect, #branchSelect, #floorSelect').hide();
    }

    // ============================================
    // DELETE POLICY
    // ============================================
    function handleDeletePolicy(policyId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/policies/${policyId}`,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message || 'Policy has been deleted.',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        const err = xhr.responseJSON?.message || 'Error occurred while deleting the policy.';
                        Swal.fire('Error', err, 'error');
                    }
                });
            }
        });
    }

    // ============================================
    // DETAIL CANVAS POPULATION
    // ============================================
    function fetchAndPopulateDetailCanvas(policyId) {
        $.ajax({
            url: `/admin/policies/${policyId}`,
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success && response.policy) {
                    populateDetailCanvas(response.policy);
                }
            },
            error: function() {
                Swal.fire('Error', 'Unable to fetch policy details.', 'error');
            }
        });
    }

    function populateDetailCanvas(policy) {
        // Store current policy ID for edit button
        $('#editPolicyBtn').data('current-policy-id', policy.id);

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

        $('#detailEffectiveDate').text(formatDate(policy.effective_date));
        
        const lastUpdated = formatTimestamp(policy.updated_at);
        $('#detailLastUpdated').text(`${lastUpdated.date} at ${lastUpdated.time}`);

        // Applicable To
        let applicableToHtml = '';
        if (policy.applicable_to === 'global') {
            applicableToHtml = '<span class="badge px-2 py-1 bg-primary"><i class="bi bi-globe me-1"></i>Global (All Organizations)</span>';
        } else if (policy.applicable_to === 'organization') {
            applicableToHtml = `<span class="badge px-2 py-1 bg-info"><i class="bi bi-building me-1"></i>${policy.applicable_details}</span>`;
        } else if (policy.applicable_to === 'branch') {
            applicableToHtml = `<span class="badge px-2 py-1 bg-success"><i class="bi bi-geo-alt me-1"></i>${policy.applicable_details}</span>`;
        } else if (policy.applicable_to === 'floor') {
            applicableToHtml = `<span class="badge px-2 py-1 bg-warning text-dark"><i class="bi bi-building me-1"></i>${policy.applicable_details}</span>`;
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
        if (policy.document_path && policy.document_name) {
            $('#documentSection').show();
            $('#detailDocumentName').text(policy.document_name);
            $('#detailDocumentSize').text('');
            $('#viewDocumentBtn').data('document-path', policy.document_path);
            $('#downloadDocumentBtn').data('document-path', policy.document_path).data('document-name', policy.document_name);
        } else {
            $('#documentSection').hide();
        }
    }

    // ============================================
    // UTILITY FUNCTIONS
    // ============================================
    function formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
    }

    function formatTimestamp(timestamp) {
        if (!timestamp) return { date: '-', time: '' };
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
        if (!policiesTable) return;

        const data = policiesTable.rows({search: 'applied'}).data();
        let csvContent = "Title,Category,Applicable To,Effective Date,Status,Last Updated\n";
        
        data.each(function(row) {
            const title = $(row[1]).text().trim().replace(/,/g, ';');
            const category = $(row[2]).text().trim().replace(/,/g, ';');
            const applicableTo = $(row[3]).text().trim().replace(/,/g, ';');
            const effectiveDate = $(row[4]).text().trim().replace(/,/g, ';');
            const status = $(row[5]).text().trim().replace(/,/g, ';');
            const lastUpdated = $(row[6]).text().trim().replace(/,/g, ';');
            csvContent += `"${title}","${category}","${applicableTo}","${effectiveDate}","${status}","${lastUpdated}"\n`;
        });

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', 'policies_' + new Date().toISOString().split('T')[0] + '.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
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
