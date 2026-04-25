/**
 * Workflows Module — Full AJAX CRUD
 */

(function () {
    'use strict';

    // ─────────────────────────────────────────────
    // STATE
    // ─────────────────────────────────────────────
    let workflowsTable   = null;
    let workflowsData    = [];   // local cache from last fetch
    let activeWorkflowId = null; // detail canvas context
    let approvalLevelCounter = 0;
    let customFilterFn   = null;

    const workflowScopeTree = Array.isArray(window.workflowScopeTree) ? window.workflowScopeTree : [];
    const workflowRoleNames = Array.isArray(window.workflowRoleNames) ? window.workflowRoleNames : [];

    // ─────────────────────────────────────────────
    // INIT
    // ─────────────────────────────────────────────
    $(document).ready(function () {
        initDataTable();
        loadStats();
        initEventHandlers();
    });

    // ─────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────
    function escHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function csrfHeaders() {
        return {
            'X-CSRF-TOKEN': window.csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        };
    }

    function showFieldErrors(errors) {
        // Clear old errors first
        document.querySelectorAll('.field-error-msg').forEach(el => el.textContent = '');

        if (!errors) return;
        Object.entries(errors).forEach(([field, messages]) => {
            // Handle approval_levels.X.role → show in approval_levels-error
            const key = field.startsWith('approval_levels') ? 'approval_levels' : field;
            const el  = document.getElementById(key + '-error');
            if (el) el.textContent = Array.isArray(messages) ? messages[0] : messages;
        });
    }

    // ─────────────────────────────────────────────
    // BADGE HELPERS
    // ─────────────────────────────────────────────
    const requestTypeConfig = {
        leave:          { color: 'bg-info',              icon: 'bi-calendar-event',  label: 'Leave'          },
        overtime:       { color: 'bg-warning text-dark', icon: 'bi-hourglass-split', label: 'Overtime'       },
        regularization: { color: 'bg-primary',           icon: 'bi-patch-check',     label: 'Regularization' },
        shift:          { color: 'bg-success',           icon: 'bi-calendar-week',   label: 'Shift'          },
    };

    function requestTypeBadge(type) {
        const cfg = requestTypeConfig[type] || { color: 'bg-secondary', icon: 'bi-question', label: type };
        return `<span class="badge px-2 rounded-1 ${cfg.color}"><i class="bi ${cfg.icon} me-1"></i>${cfg.label}</span>`;
    }

    function statusBadge(status) {
        if (status === 'active') {
            return '<span class="badge px-2 rounded-1 bg-success"><i class="bi bi-check-circle me-1"></i>Active</span>';
        }
        return '<span class="badge px-2 rounded-1 bg-secondary"><i class="bi bi-x-circle me-1"></i>Inactive</span>';
    }

    function applicableBadge(wf) {
        if (wf.is_global) {
            return '<span class="badge px-2 rounded-1 bg-primary"><i class="bi bi-globe me-1"></i>Global</span>';
        }
        let text = escHtml(wf.organization);
        if (wf.sbu_name) {
            text += ` — ${escHtml(wf.sbu_name)}`;
        } else if (wf.branch) {
            text += ` — ${escHtml(wf.branch)}`;
        }
        return `<span class="badge px-2 rounded-1 bg-info"><i class="bi bi-building me-1"></i>${text}</span>`;
    }

    // ─────────────────────────────────────────────
    // BUILD TABLE ROW HTML
    // ─────────────────────────────────────────────
    function buildRow(wf) {
        const levels       = wf.approval_levels || [];
        const levelsCount  = levels.length;
        const levelsDisplay= levels.map(l => `L${l.level}: ${escHtml(l.role)}`).join(' → ');
        const slaDisplay   = `${wf.sla_hours} hrs${wf.escalate_to ? ' (Auto-escalate)' : ''}`;

        return `
            <tr data-request-type="${escHtml(wf.request_type)}"
                data-status="${escHtml(wf.status)}"
                data-org-id="${wf.organization_id || 'global'}"
                data-sbu-id="${wf.sbu_id != null ? wf.sbu_id : ''}">
                <td class="dt-control"></td>
                <td><div class="fw-semibold">${escHtml(wf.name)}</div></td>
                <td>${requestTypeBadge(wf.request_type)}</td>
                <td>
                    <div class="small fw-semibold">${levelsCount} Level${levelsCount !== 1 ? 's' : ''}</div>
                    <small class="text-muted" title="${levelsDisplay}">
                        ${levelsDisplay.length > 55 ? levelsDisplay.substring(0, 55) + '…' : levelsDisplay}
                    </small>
                </td>
                <td>${applicableBadge(wf)}</td>
                <td><div class="small fw-semibold">${slaDisplay}</div></td>
                <td>${statusBadge(wf.status)}</td>
                <td class="text-end">
                    <div class="d-flex gap-1 justify-content-end" style="white-space: nowrap;">
                        <button type="button"
                            class="btn btn-sm btn-primary view-workflow-btn"
                            data-bs-toggle="offcanvas"
                            data-bs-target="#workflowDetailCanvas"
                            data-workflow-id="${wf.id}"
                            title="View Details">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button type="button"
                            class="btn btn-sm btn-outline-secondary edit-workflow-btn"
                            data-workflow-id="${wf.id}"
                            title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button"
                            class="btn btn-sm btn-outline-danger delete-workflow-btn"
                            data-workflow-id="${wf.id}"
                            title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>`;
    }

    function destroyWorkflowsTable() {
        if (typeof $ !== 'undefined' && $.fn.DataTable && $.fn.DataTable.isDataTable('#workflowsTable')) {
            $('#workflowsTable').DataTable().destroy();
            workflowsTable = null;
        }
    }

    function workflowsDataTableOptions() {
        return {
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            order: [[0, 'asc']],
            scrollX: false,
            responsive: { details: { type: 'column', target: 0 } },
            columnDefs: [
                { targets: 0, orderable: false, className: 'dt-control', responsivePriority: 0 },
                { targets: [1, 2, 3, 4, 5, 6], visible: true },
                { targets: 7, orderable: false, className: 'no-toggle', responsivePriority: 1 },
                { targets: 1, responsivePriority: 2 },
                { targets: [2, 3, 4], responsivePriority: 3 },
                { targets: [5, 6], responsivePriority: 4 },
            ],
            language: {
                search: '',
                searchPlaceholder: 'Search workflows...',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ workflows',
                infoEmpty: 'No workflows available',
                zeroRecords: 'No matching workflows found',
            },
            buttons: [{
                extend: 'colvis',
                text: 'Select Columns',
                className: 'btn btn-sm border-0 bg-main text-white',
                columns: [1, 2, 3, 4, 5, 6],
            }],
            drawCallback: function () {
                $('[data-bs-toggle="tooltip"]').tooltip();
                updateCounters();
            },
        };
    }

    function mountWorkflowsTable() {
        destroyWorkflowsTable();
        const tbody = $('#workflowsTableBody');
        tbody.empty();
        workflowsData.forEach(wf => tbody.append(buildRow(wf)));
        workflowsTable = initUserDataTable('#workflowsTable', workflowsDataTableOptions());
        populateFilterSbuOptions();
    }

    // ─────────────────────────────────────────────
    // DATATABLE INIT
    // ─────────────────────────────────────────────
    function initDataTable() {
        fetch(window.workflowsDataUrl)
            .then(r => r.json())
            .then(res => {
                if (!res.success) return;
                workflowsData = res.data;
                mountWorkflowsTable();
            })
            .catch(err => console.error('Failed to load workflows:', err));
    }

    // ─────────────────────────────────────────────
    // RELOAD TABLE
    // ─────────────────────────────────────────────
    function reloadTable() {
        fetch(window.workflowsDataUrl)
            .then(r => r.json())
            .then(res => {
                if (!res.success) return;
                workflowsData = res.data;
                mountWorkflowsTable();
                loadStats();
            });
    }

    // ─────────────────────────────────────────────
    // STATS
    // ─────────────────────────────────────────────
    function loadStats() {
        fetch(window.workflowsStatsUrl)
            .then(r => r.json())
            .then(res => {
                if (!res.success) return;
                const d = res.data;
                $('#totalWorkflows').text(d.total);
                $('#activeWorkflows').text(d.active);
                $('#requestTypes').text(d.request_types);
                $('#avgApprovalTime').text(d.avg_approval_time + ' hrs');
            })
            .catch(() => {});
    }

    // ─────────────────────────────────────────────
    // COUNTERS (from filtered table rows)
    // ─────────────────────────────────────────────
    function updateCounters() {
        if (!workflowsTable) return;
        let total = 0, active = 0, totalSla = 0, slaCount = 0;
        const types = new Set();

        workflowsTable.rows({ search: 'applied' }).every(function () {
            const row    = this.node();
            const status = $(row).data('status');
            const rt     = $(row).find('td:eq(2)').text().trim();
            const slaText= $(row).find('td:eq(5)').text();
            const match  = slaText.match(/(\d+)\s*hrs/);

            total++;
            types.add(rt);
            if (status === 'active') active++;
            if (match) { totalSla += parseInt(match[1]); slaCount++; }
        });

        $('#totalWorkflows').text(total);
        $('#activeWorkflows').text(active);
        $('#requestTypes').text(types.size);
        $('#avgApprovalTime').text((slaCount ? Math.round(totalSla / slaCount) : 0) + ' hrs');
    }

    // ─────────────────────────────────────────────
    // FILTERS
    // ─────────────────────────────────────────────
    function applyFilters() {
        const type   = $('#filterRequestType').val();
        const status = $('#filterStatus').val();
        const org    = $('#filterOrganization').val();
        const sbu    = $('#filterSbu').val();

        if (customFilterFn) {
            $.fn.dataTable.ext.search.pop();
            customFilterFn = null;
        }

        customFilterFn = function (settings, data, idx) {
            if (settings.nTable.id !== 'workflowsTable') return true;
            const row = workflowsTable.row(idx).node();

            if (type   && $(row).data('request-type') !== type)            return false;
            if (status && $(row).data('status') !== status)                 return false;
            if (org) {
                const rowOrg = String($(row).data('org-id'));
                if (rowOrg !== org) return false;
            }
            if (sbu) {
                const rowSbu = String($(row).data('sbu-id') || '');
                if (rowSbu !== String(sbu)) return false;
            }
            return true;
        };

        $.fn.dataTable.ext.search.push(customFilterFn);
        if (workflowsTable) workflowsTable.draw();
    }

    function clearFilters() {
        $('#filterRequestType, #filterStatus, #filterOrganization, #filterSbu').val('');
        if (customFilterFn) {
            $.fn.dataTable.ext.search.pop();
            customFilterFn = null;
        }
        if (workflowsTable) workflowsTable.draw();
    }

    // ─────────────────────────────────────────────
    // DETAIL CANVAS POPULATION
    // ─────────────────────────────────────────────
    function populateDetailCanvas(wf) {
        activeWorkflowId = wf.id;

        $('#detailWorkflowName').text(wf.name);
        $('#detailRequestType').html(requestTypeBadge(wf.request_type));
        $('#detailStatus').html(statusBadge(wf.status));
        $('#detailOrganization').text(wf.organization || 'Global');
        if (wf.sbu_name) {
            $('#detailSbu').text(wf.sbu_name);
        } else if (wf.branch) {
            $('#detailSbu').text(wf.branch + ' (legacy)');
        } else {
            $('#detailSbu').text('All SBUs');
        }
        $('#detailSLA').text(wf.sla_hours + ' hours');

        // Escalate To
        let esc = '<span class="opacity-75 text-white">No auto-escalation</span>';
        if (wf.escalate_to) {
            esc = `<span class="badge px-2 py-1 bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>${escHtml(wf.escalate_to)}</span>`;
        }
        $('#detailEscalateTo').html(esc);

        // Approval chain visual
        const chain = $('#detailApprovalChain').empty();
        const levels = wf.approval_levels || [];
        levels.forEach((level, idx) => {
            const isLast = idx === levels.length - 1;
            chain.append(`
                <div class="approval-chain-item mb-3">
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <div class="approval-level-box p-3 rounded-3 border text-center" style="border-color: #ffffff1a !important; min-width: 160px;">
                            <div class="small opacity-75 text-white mb-1">Level ${level.level}</div>
                            <div class="fw-semibold text-white">${escHtml(level.role)}</div>
                        </div>
                        ${!isLast ? '<div><i class="bi bi-arrow-right text-white fs-4"></i></div>' : ''}
                    </div>
                </div>`);
        });
    }

    // ─────────────────────────────────────────────
    // APPROVAL LEVEL MANAGEMENT (form)
    // ─────────────────────────────────────────────
    function getApproverRoleOptions() {
        if (workflowRoleNames.length) {
            return workflowRoleNames;
        }
        return ['Supervisor', 'Department Head', 'Manager', 'HR Manager', 'Super Admin'];
    }

    function populateFilterSbuOptions() {
        const $sel = $('#filterSbu');
        if (!$sel.length) {
            return;
        }
        const current = $sel.val();
        $sel.find('option:not(:first)').remove();
        workflowScopeTree.forEach((org) => {
            (org.sbus || []).forEach((sbu) => {
                $sel.append($('<option>', { value: sbu.id, text: sbu.name }));
            });
        });
        if (current) {
            $sel.val(current);
        }
    }

    function repopulateWorkflowSbusForForm(orgId, preserveSbuId) {
        const $sel = $('#workflowSbu');
        if (!$sel.length) {
            return;
        }
        $sel.find('option:not(:first)').remove();
        const org = workflowScopeTree.find((o) => String(o.id) === String(orgId));
        if (org && org.sbus) {
            org.sbus.forEach((sbu) => {
                $sel.append($('<option>', { value: sbu.id, text: sbu.name }));
            });
        }
        if (preserveSbuId != null && preserveSbuId !== '') {
            $sel.val(String(preserveSbuId));
        } else {
            $sel.val('');
        }
    }

    function addApprovalLevel(roleValue = '') {
        approvalLevelCounter++;
        const levelNum = $('.approval-level-item').length + 1;
        const roles = getApproverRoleOptions();
        const options = roles.map((r) =>
            `<option value="${escHtml(r)}" ${r === roleValue ? 'selected' : ''}>${escHtml(r)}</option>`
        ).join('');

        const html = `
            <div class="approval-level-item mb-3 p-3 border rounded-3" style="border-color: #ffffff1a !important;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="mb-0 fw-semibold small"><i class="bi bi-person-check me-2"></i>Level ${levelNum}</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-level-btn">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="row g-2">
                    <div class="col-md-12">
                        <label class="form-label small fw-semibold">Approver Role</label>
                        <select class="form-select form-select-sm approval-role" required>
                            <option value="">Select Role</option>
                            ${options}
                        </select>
                    </div>
                </div>
            </div>`;
        $('#approvalChainContainer').append(html);
    }

    function updateLevelNumbers() {
        $('.approval-level-item').each(function (idx) {
            $(this).find('h6').html(`<i class="bi bi-person-check me-2"></i>Level ${idx + 1}`);
        });
    }

    function resetForm() {
        $('#createWorkflowForm')[0].reset();
        $('#approvalChainContainer').empty();
        $('#editWorkflowId').val('');
        approvalLevelCounter = 0;
        document.querySelectorAll('.field-error-msg').forEach(el => el.textContent = '');
        $('#workflowSLA').val(24);
        repopulateWorkflowSbusForForm('', null);
    }

    // ─────────────────────────────────────────────
    // OPEN EDIT MODE
    // ─────────────────────────────────────────────
    function openEditMode(wf) {
        resetForm();
        $('#canvasTitle').text('Edit Workflow');
        $('#submitBtnText').text('Update Workflow');
        $('#editWorkflowId').val(wf.id);

        // Prefill basic fields
        $('#workflowName').val(wf.name);
        $('#workflowRequestType').val(wf.request_type);
        $('#workflowStatus').val(wf.status);
        $('#workflowOrganization').val(wf.organization_id || '');
        repopulateWorkflowSbusForForm($('#workflowOrganization').val(), wf.sbu_id);
        $('#workflowSLA').val(wf.sla_hours);
        $('#workflowEscalateTo').val(wf.escalate_to || '');

        // Rebuild approval chain
        $('#approvalChainContainer').empty();
        (wf.approval_levels || []).forEach(l => addApprovalLevel(l.role));

        // Close detail canvas and open create canvas
        const detailOffcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('workflowDetailCanvas'));
        if (detailOffcanvas) detailOffcanvas.hide();
        setTimeout(() => {
            const createOffcanvas = new bootstrap.Offcanvas(document.getElementById('createWorkflowCanvas'));
            createOffcanvas.show();
        }, 300);
    }

    // ─────────────────────────────────────────────
    // SUBMIT FORM (Create / Update)
    // ─────────────────────────────────────────────
    function submitWorkflowForm(e) {
        e.preventDefault();
        document.querySelectorAll('.field-error-msg').forEach(el => el.textContent = '');

        const levels = [];
        let hasEmptyRole = false;
        $('.approval-level-item').each(function (idx) {
            const role = $(this).find('.approval-role').val();
            if (!role) { hasEmptyRole = true; return false; }
            levels.push({ level: idx + 1, role: role });
        });

        if (hasEmptyRole || levels.length === 0) {
            document.getElementById('approval_levels-error').textContent =
                'Please add at least one approval level and select a role for each level.';
            return;
        }

        const editId = $('#editWorkflowId').val();
        const isEdit = !!editId;
        const url    = isEdit
            ? window.workflowsUpdateUrl.replace(':id', editId)
            : window.workflowsStoreUrl;

        const payload = {
            name: $('#workflowName').val(),
            request_type: $('#workflowRequestType').val(),
            status: $('#workflowStatus').val(),
            organization_id: $('#workflowOrganization').val() || null,
            sbu_id: $('#workflowSbu').val() || null,
            approval_levels: levels,
            sla_hours: parseInt($('#workflowSLA').val(), 10),
            escalate_to: $('#workflowEscalateTo').val() || null,
        };

        const submitBtn = document.getElementById('workflowSubmitBtn');
        const btnDefaultHtml = `<i class="bi bi-check-circle me-1"></i><span id="submitBtnText">${isEdit ? 'Update Workflow' : 'Create Workflow'}</span>`;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';

        fetch(url, {
            method: 'POST',
            headers: csrfHeaders(),
            body: JSON.stringify(payload),
        })
            .then(async (r) => {
                const res = await r.json().catch(() => ({}));
                submitBtn.disabled = false;
                submitBtn.innerHTML = btnDefaultHtml;

                if (r.ok && res.success) {
                    const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('createWorkflowCanvas'));
                    if (offcanvas) {
                        offcanvas.hide();
                    }
                    if (typeof window.showSuccess === 'function') {
                        window.showSuccess(res.message || 'Workflow saved successfully.', 'Success');
                    }
                    reloadTable();
                } else if (res.errors) {
                    showFieldErrors(res.errors);
                } else {
                    const msg = res.message || (r.status === 422 ? 'Validation failed.' : 'Something went wrong.');
                    if (typeof window.showError === 'function') {
                        window.showError(msg, 'Error');
                    }
                }
            })
            .catch(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = btnDefaultHtml;
                if (typeof window.showError === 'function') {
                    window.showError('Network error. Please try again.', 'Error');
                }
            });
    }

    // ─────────────────────────────────────────────
    // DELETE
    // ─────────────────────────────────────────────
    let pendingDeleteId = null;

    function confirmDelete(wf) {
        pendingDeleteId = wf.id;
        document.getElementById('deleteWorkflowName').textContent = wf.name;
        const modal = new bootstrap.Modal(document.getElementById('deleteWorkflowModal'));
        modal.show();
    }

    function executeDelete() {
        if (!pendingDeleteId) return;
        const url = window.workflowsDeleteUrl.replace(':id', pendingDeleteId);

        fetch(url, {
            method: 'DELETE',
            headers: csrfHeaders(),
        })
            .then(async (r) => {
                const res = await r.json().catch(() => ({}));
                bootstrap.Modal.getInstance(document.getElementById('deleteWorkflowModal'))?.hide();
                const detailOffcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('workflowDetailCanvas'));
                if (detailOffcanvas) {
                    detailOffcanvas.hide();
                }

                if (r.ok && res.success) {
                    if (typeof window.showSuccess === 'function') {
                        window.showSuccess(res.message || 'Workflow deleted successfully.', 'Deleted');
                    }
                    reloadTable();
                } else if (typeof window.showError === 'function') {
                    window.showError(res.message || 'Delete failed.', 'Error');
                }
                pendingDeleteId = null;
            })
            .catch(() => {
                if (typeof window.showError === 'function') {
                    window.showError('Network error.', 'Error');
                }
                pendingDeleteId = null;
            });
    }

    // ─────────────────────────────────────────────
    // EVENT HANDLERS
    // ─────────────────────────────────────────────
    function initEventHandlers() {
        // Filters
        $('#applyFiltersBtn').on('click', applyFilters);
        $('#clearFiltersBtn').on('click', clearFilters);

        // Export
        $('#exportBtn').on('click', function () {
            if (workflowsTable) {
                workflowsTable.button('.buttons-csv')?.trigger();
            } else if (typeof window.showError === 'function') {
                window.showError('No data to export.', 'Export');
            }
        });

        // Create form submit
        $('#createWorkflowForm').on('submit', submitWorkflowForm);

        // Add approval level
        $('#addApprovalLevelBtn').on('click', () => addApprovalLevel());

        // Remove approval level
        $(document).on('click', '.remove-level-btn', function () {
            $(this).closest('.approval-level-item').remove();
            updateLevelNumbers();
        });

        // Reset canvas when closed
        $('#createWorkflowCanvas').on('hidden.bs.offcanvas', function () {
            resetForm();
            $('#canvasTitle').text('Create New Workflow');
            $('#submitBtnText').text('Create Workflow');
        });

        // When "Create Workflow" button is clicked (ensure add mode)
        $('#createWorkflowBtn').on('click', function () {
            resetForm();
            $('#canvasTitle').text('Create New Workflow');
            $('#submitBtnText').text('Create Workflow');
        });

        // Detail canvas: populate on show
        document.getElementById('workflowDetailCanvas')?.addEventListener('show.bs.offcanvas', function (event) {
            const btn = event.relatedTarget;
            if (btn && btn.classList.contains('view-workflow-btn')) {
                const id = parseInt($(btn).data('workflow-id'), 10);
                const wf = workflowsData.find(w => w.id === id);
                if (wf) populateDetailCanvas(wf);
            }
        });

        $(document).on('click', '.edit-workflow-btn', function (e) {
            e.stopPropagation();
            const id = parseInt($(this).data('workflow-id'), 10);
            const wf = workflowsData.find(w => w.id === id);
            if (wf) {
                openEditMode(wf);
            }
        });

        $(document).on('click', '.delete-workflow-btn', function (e) {
            e.stopPropagation();
            const id = parseInt($(this).data('workflow-id'), 10);
            const wf = workflowsData.find(w => w.id === id);
            if (wf) {
                confirmDelete(wf);
            }
        });

        $('#workflowOrganization').on('change', function () {
            repopulateWorkflowSbusForForm($(this).val(), null);
        });

        // Confirm delete button in modal
        $('#confirmDeleteBtn').on('click', executeDelete);
    }

})();
