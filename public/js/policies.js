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
    const policyScopeTree = typeof window.policyScopeTree !== 'undefined' && Array.isArray(window.policyScopeTree)
        ? window.policyScopeTree
        : [];
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
                applicableTo: p.applicable_to === 'branch' ? 'sbu' : p.applicable_to,
                applicableDetails: p.applicable_details || '',
                organizationId: p.organization_id ?? null,
                sbuId: p.sbu_id ?? null,
                sbuFloorId: p.sbu_floor_id ?? null,
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

    function rebuildOrganizationOptions() {
        const $sel = $('#policyOrganization');
        if (!$sel.length) {
            return;
        }
        const current = $sel.val();
        $sel.find('option:not(:first)').remove();
        policyScopeTree.forEach((org) => {
            $sel.append($('<option>', { value: org.id, text: org.name }));
        });
        if (current) {
            $sel.val(current);
        }
    }

    function resetSbuAndFloorSelects() {
        $('#policySbu').empty().append($('<option>', { value: '', text: 'Select SBU' }));
        $('#policyFloor').empty().append($('<option>', { value: '', text: 'Select Floor' }));
    }

    function repopulateSbus(orgId) {
        const $sbu = $('#policySbu');
        $sbu.empty().append($('<option>', { value: '', text: 'Select SBU' }));
        $('#policyFloor').empty().append($('<option>', { value: '', text: 'Select Floor' }));
        const org = policyScopeTree.find((o) => String(o.id) === String(orgId));
        if (!org || !org.sbus) {
            return;
        }
        org.sbus.forEach((s) => {
            $sbu.append($('<option>', { value: s.id, text: s.name }));
        });
    }

    function repopulateFloors(sbuId) {
        const $fl = $('#policyFloor');
        $fl.empty().append($('<option>', { value: '', text: 'Select Floor' }));
        if (!sbuId) {
            return;
        }
        for (let i = 0; i < policyScopeTree.length; i++) {
            const org = policyScopeTree[i];
            if (!org.sbus) {
                continue;
            }
            const sbu = org.sbus.find((s) => String(s.id) === String(sbuId));
            if (sbu && sbu.floors) {
                sbu.floors.forEach((f) => {
                    const num = f.floor_number;
                    const suffix = num !== null && num !== undefined && num !== '' ? ` (#${num})` : '';
                    $fl.append($('<option>', { value: f.id, text: f.name + suffix }));
                });
                return;
            }
        }
    }

    function updateScopeFieldVisibility() {
        const v = $('#policyApplicableTo').val();
        $('#policyOrgWrap, #policySbuWrap, #policyFloorWrap').hide();
        $('.scope-org-req, .scope-sbu-req, .scope-floor-req').hide();
        if (v === 'organization' || v === 'sbu' || v === 'floor') {
            $('#policyOrgWrap').show();
            $('.scope-org-req').show();
        }
        if (v === 'sbu' || v === 'floor') {
            $('#policySbuWrap').show();
            $('.scope-sbu-req').show();
        }
        if (v === 'floor') {
            $('#policyFloorWrap').show();
            $('.scope-floor-req').show();
        }
    }

    function applyPolicyScopeToForm(p) {
        const atRaw = p.applicable_to === 'branch' ? 'sbu' : p.applicable_to;
        $('#policyApplicableTo').val(atRaw);
        updateScopeFieldVisibility();
        rebuildOrganizationOptions();

        if (atRaw === 'global') {
            return;
        }

        let orgId = p.organization_id;
        let sbuId = p.sbu_id;

        if (!orgId && sbuId) {
            for (let i = 0; i < policyScopeTree.length; i++) {
                const org = policyScopeTree[i];
                if (org.sbus && org.sbus.some((s) => String(s.id) === String(sbuId))) {
                    orgId = org.id;
                    break;
                }
            }
        }

        if (!sbuId && p.sbu_floor_id) {
            outer: for (let i = 0; i < policyScopeTree.length; i++) {
                const org = policyScopeTree[i];
                if (!org.sbus) {
                    continue;
                }
                for (let j = 0; j < org.sbus.length; j++) {
                    const s = org.sbus[j];
                    if (s.floors && s.floors.some((f) => String(f.id) === String(p.sbu_floor_id))) {
                        orgId = org.id;
                        sbuId = s.id;
                        break outer;
                    }
                }
            }
        }

        if (orgId) {
            $('#policyOrganization').val(String(orgId));
        }

        if (atRaw === 'sbu' || atRaw === 'floor') {
            repopulateSbus($('#policyOrganization').val());
        }
        if (sbuId) {
            $('#policySbu').val(String(sbuId));
        }
        if (atRaw === 'floor') {
            repopulateFloors($('#policySbu').val());
            if (p.sbu_floor_id) {
                $('#policyFloor').val(String(p.sbu_floor_id));
            }
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
        } else if (policy.applicableTo === 'sbu' || policy.applicableTo === 'branch') {
            applicableToBadge = `<span class="badge px-2 rounded-1 bg-success"><i class="bi bi-diagram-2 me-1"></i>${policy.applicableDetails}</span>`;
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
                data-applicable-to="${policy.applicableTo}"
                data-organization-id="${policy.organizationId != null ? policy.organizationId : ''}">
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

        rebuildOrganizationOptions();

        $('#policyApplicableTo').on('change', function () {
            updateScopeFieldVisibility();
            const value = $(this).val();
            if (value === 'global') {
                $('#policyOrganization').val('');
                resetSbuAndFloorSelects();
                return;
            }
            rebuildOrganizationOptions();
            if (value === 'organization') {
                resetSbuAndFloorSelects();
            }
            if (value === 'sbu') {
                repopulateSbus($('#policyOrganization').val());
                $('#policyFloor').html('<option value="">Select Floor</option>');
            }
            if (value === 'floor') {
                repopulateSbus($('#policyOrganization').val());
                repopulateFloors($('#policySbu').val());
            }
        });

        $('#policyOrganization').on('change', function () {
            const scope = $('#policyApplicableTo').val();
            if (scope === 'sbu' || scope === 'floor') {
                repopulateSbus($(this).val());
            }
            if (scope === 'floor') {
                $('#policyFloor').html('<option value="">Select Floor</option>');
            }
        });

        $('#policySbu').on('change', function () {
            if ($('#policyApplicableTo').val() === 'floor') {
                repopulateFloors($(this).val());
            }
        });

        const policyInputIdToFeedbackKey = {
            policyTitle: 'title',
            policyCategory: 'category',
            policyStatus: 'status',
            policyEffectiveDate: 'effective_date',
            policyApplicableTo: 'applicable_to',
            policyOrganization: 'organization_id',
            policySbu: 'sbu_id',
            policyFloor: 'sbu_floor_id',
            policyDescription: 'description',
            policyDocument: 'document'
        };
        $('#createPolicyForm').on('input change', 'input, select, textarea', function () {
            const id = $(this).attr('id');
            const feKey = policyInputIdToFeedbackKey[id];
            if (feKey) {
                $(this).removeClass('is-invalid');
                $('#policy-fe-' + feKey).addClass('d-none').text('');
            }
            $('#policyFormErrorSummary').addClass('d-none').empty();
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
            createModal.addEventListener('shown.bs.modal', function () {
                if (!isEditMode) {
                    clearPolicyFieldErrors();
                }
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
            
            // Organization filter (numeric organization id or "global")
            if (organization) {
                if (organization === 'global') {
                    if ($(row).data('applicable-to') !== 'global') return false;
                } else {
                    const rowOrgId = $(row).data('organization-id');
                    if (String(rowOrgId || '') !== String(organization)) return false;
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
    // POLICY FORM — INLINE VALIDATION
    // ============================================
    function clearPolicyFieldErrors() {
        $('#createPolicyForm .is-invalid').removeClass('is-invalid');
        $('#createPolicyForm .policy-field-feedback').addClass('d-none').text('');
        $('#policyFormErrorSummary').addClass('d-none').empty();
    }

    function displayPolicyValidationErrors(errors) {
        if (!errors || typeof errors !== 'object') {
            return;
        }
        const inputByKey = {
            title: '#policyTitle',
            category: '#policyCategory',
            status: '#policyStatus',
            effective_date: '#policyEffectiveDate',
            applicable_to: '#policyApplicableTo',
            organization_id: '#policyOrganization',
            sbu_id: '#policySbu',
            sbu_floor_id: '#policyFloor',
            description: '#policyDescription',
            document: '#policyDocument'
        };
        const summary = [];

        Object.keys(errors).sort().forEach((key) => {
            const msgs = errors[key];
            const msg = Array.isArray(msgs) ? msgs[0] : String(msgs);
            const rootKey = key.split('.')[0];
            const feedbackId = '#policy-fe-' + rootKey.replace(/\./g, '-');
            const $fb = $(feedbackId);

            if ($fb.length) {
                $fb.removeClass('d-none').text(msg);
                const inputSel = inputByKey[rootKey];
                if (inputSel) {
                    $(inputSel).addClass('is-invalid');
                }
            } else {
                summary.push(msg);
            }
        });

        if (summary.length) {
            $('#policyFormErrorSummary').removeClass('d-none').text(summary.join(' '));
        }
    }

    function validatePolicyClientScope() {
        const scope = $('#policyApplicableTo').val();
        const needOrg = ['organization', 'sbu', 'floor'].indexOf(scope) >= 0;
        const needSbu = ['sbu', 'floor'].indexOf(scope) >= 0;
        const needFloor = scope === 'floor';
        let ok = true;

        if (needOrg && policyScopeTree.length === 0) {
            $('#policy-fe-applicable_to').removeClass('d-none')
                .text('No active organizations are set up. Choose “Global (All Organizations)” or ask an administrator to add organizations and SBUs.');
            $('#policyApplicableTo').addClass('is-invalid');
            ok = false;
        } else if (needOrg && !$('#policyOrganization').val()) {
            $('#policy-fe-organization_id').removeClass('d-none').text('Select which organization this policy applies to.');
            $('#policyOrganization').addClass('is-invalid');
            ok = false;
        }
        if (needSbu && !$('#policySbu').val()) {
            $('#policy-fe-sbu_id').removeClass('d-none').text('Select an SBU, or change scope to “Organization” if the whole organization should apply.');
            $('#policySbu').addClass('is-invalid');
            ok = false;
        }
        if (needFloor && !$('#policyFloor').val()) {
            $('#policy-fe-sbu_floor_id').removeClass('d-none').text('Select a floor for this policy.');
            $('#policyFloor').addClass('is-invalid');
            ok = false;
        }

        if (!ok) {
            updateScopeFieldVisibility();
            const first = document.querySelector('#createPolicyModal .is-invalid');
            if (first) {
                first.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
        return ok;
    }

    // ============================================
    // POLICY CREATION / UPDATE
    // ============================================
    function handleCreatePolicy(e) {
        e.preventDefault();

        clearPolicyFieldErrors();
        updateScopeFieldVisibility();

        if (!validatePolicyClientScope()) {
            return;
        }

        const formData = new FormData();
        formData.append('title', $('#policyTitle').val());
        formData.append('category', $('#policyCategory option:selected').text());
        formData.append('status', $('#policyStatus').val());
        formData.append('effective_date', $('#policyEffectiveDate').val());
        formData.append('applicable_to', $('#policyApplicableTo').val());
        formData.append('organization_id', $('#policyOrganization').val() || '');
        formData.append('sbu_id', $('#policySbu').val() || '');
        formData.append('sbu_floor_id', $('#policyFloor').val() || '');

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
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response && response.success) {
                    clearPolicyFieldErrors();
                    const modal = bootstrap.Modal.getInstance(document.getElementById('createPolicyModal'));
                    if (modal) modal.hide();

                    showSuccess(response.message || 'Saved successfully.').then(() => {
                        window.location.reload();
                    });
                } else {
                    const msg = (response && response.message) ? response.message : 'Unable to save policy.';
                    $('#policyFormErrorSummary').removeClass('d-none').text(msg);
                    showError(msg);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    updateScopeFieldVisibility();
                    displayPolicyValidationErrors(xhr.responseJSON.errors);
                    const first = document.querySelector('#createPolicyModal .is-invalid');
                    if (first) {
                        first.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                    return;
                }
                let errorMsg = 'An error occurred. Please try again.';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.errors) {
                        errorMsg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                    } else if (xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                }
                $('#policyFormErrorSummary').removeClass('d-none').text(errorMsg);
                if (typeof showError === 'function') {
                    showError(errorMsg, xhr.status === 422 ? 'Validation' : 'Error');
                }
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
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success && response.policy) {
                    const p = response.policy;

                    clearPolicyFieldErrors();

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
                    $('#policyDescription').val(p.description || '');

                    applyPolicyScopeToForm(p);

                    // Open modal
                    const modal = new bootstrap.Modal(document.getElementById('createPolicyModal'));
                    modal.show();
                }
            },
            error: function() {
                showError('Unable to fetch policy details.');
            }
        });
    }

    function resetModal() {
        isEditMode = false;
        editPolicyId = null;
        $('#createPolicyModalLabel').html('<i class="bi bi-plus-circle me-2"></i>Create New Policy');
        $('#createPolicyForm button[type="submit"]').html('<i class="bi bi-check-circle me-1"></i>Create Policy');
        $('#createPolicyForm')[0].reset();
        $('#policyOrgWrap, #policySbuWrap, #policyFloorWrap').hide();
        $('.scope-org-req, .scope-sbu-req, .scope-floor-req').hide();
        resetSbuAndFloorSelects();
        rebuildOrganizationOptions();
        clearPolicyFieldErrors();
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
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    success: function(response) {
                        if (response && response.success) {
                            showSuccess(response.message || 'Policy has been deleted.').then(() => {
                                window.location.reload();
                            });
                        } else {
                            showError((response && response.message) ? response.message : 'Unable to delete policy.');
                        }
                    },
                    error: function(xhr) {
                        const err = xhr.responseJSON?.message || 'Error occurred while deleting the policy.';
                        showError(err);
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
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success && response.policy) {
                    populateDetailCanvas(response.policy);
                }
            },
            error: function() {
                showError('Unable to fetch policy details.');
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
        } else if (policy.applicable_to === 'sbu' || policy.applicable_to === 'branch') {
            applicableToHtml = `<span class="badge px-2 py-1 bg-success"><i class="bi bi-diagram-2 me-1"></i>${policy.applicable_details}</span>`;
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
